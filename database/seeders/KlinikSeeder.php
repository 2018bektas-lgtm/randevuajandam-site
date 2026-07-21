<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Models\PaketOzelligi;
use Illuminate\Database\Seeder;

class KlinikSeeder extends Seeder
{
    /**
     * Klinik paketleri (updateOrCreate / isim alias).
     *
     * Muhasebeci personel girişi: merkezi_finans_mi = true paketlerde
     * (Klinik Plus, Profesyonel, Özel Web). Başlangıç’ta yok.
     */
    public function run(): void
    {
        $webOzellik = PaketOzelligi::updateOrCreate(
            ['kod' => 'klinik_web_sitesi'],
            [
                'ad' => 'Klinik Web Sitesi',
                'aciklama' => 'Klinik markalı özel web sitesi, çok hekim vitrini ve online randevu.',
            ]
        );

        // 1) Klinik Başlangıç — 3 hekim, 1 personel — finans/muhasebeci YOK
        $baslangic = $this->upsertKlinik(
            preferredName: 'Klinik Başlangıç',
            aliases: ['%Başlangıç%'],
            attrs: [
                'aciklama' => 'Küçük klinikler ve muayenehaneler için ideal başlangıç paketi.',
                'aylik_fiyat' => 3600.00,
                'aylik_indirimli_fiyat' => 3000.00,
                'yillik_fiyat' => 35990.00,
                'yillik_indirimli_fiyat' => 29990.00,
                'ozellikler' => [
                    'Ortak Hasta Havuzu ve CRM',
                    'Maksimum 3 Aktif Hekim Tanımlama',
                    '1 Sekreter / Personel Hesabı',
                    'Hekim Çalışma Saatleri ve Takvim Yönetimi',
                    'Temel Raporlama Modülü',
                    'Klinik Duyuru Sistemi',
                ],
                'max_doktor_sayisi' => 3,
                'max_personel_sayisi' => 1,
                'merkezi_finans_mi' => false,
                'toplu_randevu_mi' => false,
                'raporlama_mi' => false,
                'hasta_havuzu_mi' => true,
                'domain_dahil_mi' => false,
                'sira' => 1,
                'one_cikan_mi' => false,
                'etiket' => null,
                'etiket_stil' => null,
                'aktif_mi' => true,
            ],
            featureIds: []
        );

        // 2) Klinik Plus (YENİ) — 6 hekim, 2 sekreter — muhasebeci VAR
        $plus = $this->upsertKlinik(
            preferredName: 'Klinik Plus',
            aliases: ['%Klinik Plus%', '%Plus%'],
            attrs: [
                'aciklama' => 'Büyüyen klinikler için 6 hekim ve 2 sekreter kapasiteli paket.',
                'aylik_fiyat' => 5400.00,
                'aylik_indirimli_fiyat' => 4500.00,
                'yillik_fiyat' => 53990.00,
                'yillik_indirimli_fiyat' => 44990.00,
                'ozellikler' => [
                    'Klinik Başlangıç Özelliklerinin Tümü',
                    'Maksimum 6 Aktif Hekim Tanımlama',
                    '2 Sekreter / Personel Hesabı',
                    'Muhasebeci personel girişi (finans yetkisi)',
                    'Merkezi Finans & Gelir-Gider Takibi',
                    'Toplu Randevu Yönetimi',
                    'Ortak Hasta Havuzu',
                ],
                'max_doktor_sayisi' => 6,
                'max_personel_sayisi' => 2,
                'merkezi_finans_mi' => true,
                'toplu_randevu_mi' => true,
                'raporlama_mi' => false,
                'hasta_havuzu_mi' => true,
                'domain_dahil_mi' => false,
                'sira' => 2,
                'one_cikan_mi' => false,
                'etiket' => null,
                'etiket_stil' => null,
                'aktif_mi' => true,
            ],
            featureIds: []
        );

        // 3) Klinik Profesyonel — 10 hekim, 5 personel — muhasebeci VAR
        $profesyonel = $this->upsertKlinik(
            preferredName: 'Klinik Profesyonel',
            aliases: ['%Profesyonel%'],
            attrs: [
                'aciklama' => 'Orta ölçekli klinikler ve tıp merkezleri için gelişmiş özellikler.',
                'aylik_fiyat' => 7200.00,
                'aylik_indirimli_fiyat' => 6000.00,
                'yillik_fiyat' => 71990.00,
                'yillik_indirimli_fiyat' => 59990.00,
                'ozellikler' => [
                    'Klinik Plus Özelliklerinin Tümü',
                    'Maksimum 10 Aktif Hekim Tanımlama',
                    '5 Sekreter / Personel Hesabı',
                    'Muhasebeci personel girişi (finans yetkisi)',
                    'Merkezi Finans & Gelir-Gider Muhasebesi',
                    'Gelişmiş Hakediş ve Komisyon Hesaplama',
                    'Detaylı Klinik Performans Raporlama',
                    'Toplu Randevu Yönetimi ve Bloklama',
                ],
                'max_doktor_sayisi' => 10,
                'max_personel_sayisi' => 5,
                'merkezi_finans_mi' => true,
                'toplu_randevu_mi' => true,
                'raporlama_mi' => true,
                'hasta_havuzu_mi' => true,
                'domain_dahil_mi' => false,
                'sira' => 3,
                'one_cikan_mi' => true,
                'etiket' => 'Önerilen',
                'etiket_stil' => 'popular',
                'aktif_mi' => true,
            ],
            featureIds: []
        );

        // 4) Klinik Özel Web Sitesi — sınırsız — muhasebeci + web
        // Aylık tasarruf ₺2.000 / yıllık tasarruf ₺20.000 → 10.000/12.000 ve 99.990/119.990
        $web = $this->upsertKlinik(
            preferredName: 'Klinik Özel Web Sitesi',
            aliases: [
                '%Özel Web%',
                '%Kurumsal%',
                '%Web Sitesi%',
            ],
            attrs: [
                'aciklama' => 'Sınırsız hekim/personel + özel klinik web sitesi: 1 yıl domain (.com/.net) pakete dahil, çok hekimli vitrin, CMS ve online randevu.',
                'aylik_fiyat' => 12000.00,
                'aylik_indirimli_fiyat' => 10000.00,
                'yillik_fiyat' => 119990.00,
                'yillik_indirimli_fiyat' => 99990.00,
                'ozellikler' => [
                    'Klinik Profesyonel Özelliklerinin Tümü',
                    'Sınırsız Hekim Ekleme Yetkisi',
                    'Sınırsız Sekreter / Personel Tanımlama',
                    'Muhasebeci personel girişi (finans yetkisi)',
                    'Merkezi Finans & PDF Rapor Çıktıları',
                    'Tüm Şubeler İçin Ortak Hasta Havuzu',
                    '1 yıl domain dahil (.com / .net) — ek ücret yok',
                    'Özel Klinik Web Sitesi (CMS + hosting + SSL)',
                    'Premium site temaları (Sıcak, Ocean) + 3 ücretsiz tema',
                    'Çok hekimli vitrin ve hekim seçimli online randevu',
                    'Öncelikli Canlı Destek & Sekreterya Eğitimi',
                    '7/24 Teknik Altyapı ve Sunucu Desteği',
                ],
                'max_doktor_sayisi' => 999,
                'max_personel_sayisi' => 999,
                'merkezi_finans_mi' => true,
                'toplu_randevu_mi' => true,
                'raporlama_mi' => true,
                'hasta_havuzu_mi' => true,
                'domain_dahil_mi' => true,
                'domain_dahil_yil' => 1,
                'domain_dahil_tlds' => ['com', 'net'],
                'sira' => 4,
                'one_cikan_mi' => true,
                'etiket' => 'Web sitesi dahil',
                'etiket_stil' => 'web',
                'aktif_mi' => true,
            ],
            featureIds: [$webOzellik->id]
        );

        // Touch so static analysis / unused warnings stay quiet in some IDEs
        unset($baslangic, $plus, $profesyonel, $web);
    }

    /**
     * @param  list<string>  $aliases  LIKE patterns (tur=klinik)
     * @param  array<string, mixed>  $attrs
     * @param  list<int>  $featureIds
     */
    private function upsertKlinik(string $preferredName, array $aliases, array $attrs, array $featureIds): Paket
    {
        $paket = Paket::query()
            ->where('tur', 'klinik')
            ->where('ad', $preferredName)
            ->first();

        if (! $paket) {
            foreach ($aliases as $like) {
                // Plus alias must not steal Profesyonel (contains "Plus" nowhere) or Başlangıç
                $q = Paket::query()->where('tur', 'klinik')->where('ad', 'like', $like);
                if ($preferredName === 'Klinik Plus') {
                    $q->where('ad', 'not like', '%Profesyonel%')
                        ->where('ad', 'not like', '%Başlangıç%')
                        ->where('ad', 'not like', '%Web%')
                        ->where('ad', 'not like', '%Kurumsal%');
                }
                if (str_contains($preferredName, 'Web') || str_contains($preferredName, 'Kurumsal')) {
                    $q->where(function ($w) {
                        $w->where('ad', 'like', '%Web%')
                            ->orWhere('ad', 'like', '%Kurumsal%');
                    });
                }
                if ($preferredName === 'Klinik Profesyonel') {
                    $q->where('ad', 'not like', '%Plus%');
                }
                $paket = $q->orderBy('id')->first();
                if ($paket) {
                    break;
                }
            }
        }

        $payload = array_merge($attrs, [
            'ad' => $preferredName,
            'tur' => 'klinik',
            'max_hasta_sayisi' => $attrs['max_hasta_sayisi'] ?? null,
            'max_randevu_sayisi' => $attrs['max_randevu_sayisi'] ?? null,
        ]);

        if ($paket) {
            $paket->fill($payload);
            $paket->save();
        } else {
            $paket = Paket::create($payload);
        }

        $paket->sistemOzellikleri()->sync($featureIds);

        return $paket->fresh();
    }
}
