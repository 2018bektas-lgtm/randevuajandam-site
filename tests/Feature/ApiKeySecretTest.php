<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * API secret saklama biçimi.
 *
 * Regresyon P2-2: `verifySecret()` eski (hash'lenmemiş) düz metin secret'ları
 * `hash_equals` ile kabul etmeye devam ediyordu. Veritabanı sızarsa bu
 * kayıtlardaki secret doğrudan kullanılabilir durumdaydı.
 *
 * Çözüm: doğrulama başarılı olduğunda aynı değer hash'lenip yerine yazılıyor.
 * Hekimin sitesi aynı secret'ı göndermeye devam ettiği için kesinti olmaz;
 * kayıt bir sonraki istekten itibaren hash'li olur.
 */
class ApiKeySecretTest extends TestCase
{
    use RefreshDatabase;

    private function anahtarOlustur(string $secret): ApiKey
    {
        return ApiKey::query()->create([
            'api_key' => 'test-key-'.uniqid(),
            'secret_key' => $secret,
            'durum' => true,
        ]);
    }

    public function test_hashli_secret_dogrulanir(): void
    {
        $anahtar = $this->anahtarOlustur(ApiKey::hashSecret('gizli-deger'));

        $this->assertTrue($anahtar->verifySecret('gizli-deger'));
        $this->assertFalse($anahtar->verifySecret('yanlis'));
    }

    public function test_duz_metin_secret_dogrulandiginda_hashe_tasinir(): void
    {
        $anahtar = $this->anahtarOlustur('eski-duz-metin-secret');
        $this->assertFalse($anahtar->secretIsHashed(), 'Baslangicta duz metin olmali.');

        $this->assertTrue($anahtar->verifySecret('eski-duz-metin-secret'));

        $tazelenmis = $anahtar->fresh();
        $this->assertTrue($tazelenmis->secretIsHashed(), 'Secret hash e tasinmadi.');
        $this->assertNotSame('eski-duz-metin-secret', $tazelenmis->secret_key);
        $this->assertTrue(Hash::check('eski-duz-metin-secret', $tazelenmis->secret_key));
    }

    public function test_tasima_sonrasi_ayni_secret_calismaya_devam_eder(): void
    {
        // Kesinti olmamali: hekimin sitesi ayni secret'i gonderiyor
        $anahtar = $this->anahtarOlustur('degismeyen-secret');

        $this->assertTrue($anahtar->verifySecret('degismeyen-secret'));
        $this->assertTrue($anahtar->fresh()->verifySecret('degismeyen-secret'));
        $this->assertTrue($anahtar->fresh()->verifySecret('degismeyen-secret'));
    }

    public function test_yanlis_duz_metin_secret_tasinmaz(): void
    {
        $anahtar = $this->anahtarOlustur('dogru-secret');

        $this->assertFalse($anahtar->verifySecret('yanlis-secret'));
        $this->assertFalse($anahtar->fresh()->secretIsHashed(), 'Basarisiz denemede tasima olmamali.');
    }

    public function test_bos_secret_asla_kabul_edilmez(): void
    {
        $anahtar = $this->anahtarOlustur('');

        $this->assertFalse($anahtar->verifySecret(''));
        $this->assertFalse($anahtar->verifySecret('herhangi'));
        $this->assertFalse($anahtar->verifySecret(null));
    }

    public function test_kalan_duz_metin_kayit_sayisi_raporlanabilir(): void
    {
        $this->anahtarOlustur('duz-1');
        $this->anahtarOlustur('duz-2');
        $this->anahtarOlustur(ApiKey::hashSecret('hashli'));

        $this->assertSame(2, ApiKey::legacyPlainSecretCount());
    }
}
