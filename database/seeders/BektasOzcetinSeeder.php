<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\DoktorCalismaSaati;
use App\Models\DoktorGaleri;
use App\Models\Egitim;
use App\Models\Faq;
use App\Models\Hizmet;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Paket;
use App\Models\RandevuAyari;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Yalnızca hekim: Bektaş Özçetin — en üst paket + tam içerik.
 * Yönetici hesabına dokunmaz.
 *
 *   php artisan db:seed --class=BektasOzcetinSeeder
 *
 * Hekim paneli girişi:
 *   E-posta : ozcetinbektas@gmail.com
 *   Şifre   : sifre123
 *   Telefon : 05319912427
 */
class BektasOzcetinSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'ozcetinbektas@gmail.com';
        $telefon = '05319912427';
        $sifre = 'sifre123';
        $adSoyad = 'Bektaş Özçetin';

        $istanbul = Il::query()->where('ad', 'İstanbul')->first()
            ?? Il::query()->orderBy('id')->first();
        $ilce = $istanbul
            ? (Ilce::query()->where('il_id', $istanbul->id)->where('ad', 'Kadıköy')->first()
                ?? Ilce::query()->where('il_id', $istanbul->id)->orderBy('ad')->first())
            : null;

        // En üst bireysel paket: Web Sitesi Entegrasyon → yoksa VIP → en pahalı
        $paket = Paket::query()
            ->where('tur', 'bireysel')
            ->where(function ($q) {
                $q->where('ad', 'like', '%Web Sitesi%')
                    ->orWhere('ad', 'like', '%Entegrasyon%');
            })
            ->first()
            ?? Paket::query()->where('tur', 'bireysel')->where('ad', 'like', '%VIP%')->first()
            ?? Paket::query()->where('tur', 'bireysel')->orderByDesc('aylik_fiyat')->first();

        $profilPath = $this->ensureImage('uploads/profil/bektas_ozcetin_profil.jpg', [201, 106, 43], 'Bektaş Özçetin');

        $doktor = Doktor::query()->updateOrCreate(
            ['e_posta' => $email],
            [
                'ad_soyad' => $adSoyad,
                'sifre' => $sifre,
                'telefon' => $telefon,
                'il_id' => $istanbul?->id,
                'ilce_id' => $ilce?->id,
                'tur' => 'bireysel',
                'klinik_adi' => 'Özçetin Sağlık Danışmanlığı',
                'paket_id' => $paket?->id,
                'odeme_periyodu' => 'yillik',
                'uyelik_baslangic' => now()->subMonth(),
                'uyelik_bitis' => now()->addYear(),
                'aktif_mi' => true,
                'platformda_gorunur' => true,
                'unvan' => 'Uzm. Dr.',
                'uzmanlik_alani' => 'Aile Hekimliği, İç Hastalıkları',
                'mezuniyet' => [
                    'İstanbul Üniversitesi İstanbul Tıp Fakültesi (2008)',
                    'Aile Hekimliği Uzmanlığı - İstanbul (2013)',
                    'İç Hastalıkları Yan Dal Programı (2016)',
                ],
                'biyografi' => '<p><strong>Uzm. Dr. Bektaş Özçetin</strong>, koruyucu hekimlik ve hasta odaklı yaklaşımıyla bireysel sağlık takibinde hizmet verir.</p>'
                    .'<p>Kronik hastalık yönetimi, check-up planlaması ve yaşam tarzı danışmanlığı alanlarında kapsamlı değerlendirme sunar. '
                    .'Randevu Ajandam üzerinden online randevu alabilir, yüz yüze veya online görüşme tercih edebilirsiniz.</p>'
                    .'<p>Her hastanın ihtiyacını dinlemek, anlaşılır bilgilendirme yapmak ve sürdürülebilir bir sağlık planı oluşturmak önceliğimizdir.</p>',
                'adres' => 'Caferağa Mah. Moda Cad. No:42, Kadıköy / İstanbul',
                'enlem' => 40.9878,
                'boylam' => 29.0255,
                'profil_resmi' => $profilPath,
                'instagram' => 'bektasozcetin',
                'facebook' => 'bektasozcetin',
                'twitter' => 'bektasozcetin',
                'linkedin' => 'bektas-ozcetin',
                'youtube' => '',
                'web_sitesi' => 'https://randevuajandam.com',
            ]
        );

        $bransIds = [];
        foreach (['Aile Hekimliği', 'Dahiliye (İç Hastalıkları)', 'Genel Pratisyen'] as $bransAd) {
            $b = Brans::query()->firstOrCreate(['ad' => $bransAd]);
            $bransIds[] = $b->id;
        }
        $doktor->branslar()->sync($bransIds);

        RandevuAyari::query()->updateOrCreate(
            ['doktor_id' => $doktor->id],
            [
                'randevu_onay_tipi' => 'manuel',
                'randevu_periyodu' => 30,
                'en_erken_randevu_saati' => 1,
                'en_gec_randevu_gunu' => 60,
                'randevu_iptal_aktif_mi' => true,
                'iptal_saat_limiti' => 12,
                'gunluk_maksimum_randevu' => 0,
                'email_bildirimleri' => true,
                'sms_bildirimleri' => false,
                'aktif_mi' => true,
            ]
        );

        foreach (range(1, 7) as $gun) {
            DoktorCalismaSaati::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'gun' => $gun],
                [
                    'aktif_mi' => $gun <= 6,
                    'mesai_baslangic' => '09:00:00',
                    'mesai_bitis' => $gun === 6 ? '14:00:00' : '18:00:00',
                    'ogle_arasi_aktif_mi' => $gun <= 5,
                    'ogle_baslangic' => '12:30:00',
                    'ogle_bitis' => '13:30:00',
                ]
            );
        }

        $hizmetler = [
            ['ad' => 'Genel Muayene', 'sure' => 30, 'fiyat' => 1200, 'aciklama' => 'Kapsamlı şikayet dinleme, fizik muayene ve ilk değerlendirme.', 'img' => 'uploads/hizmet/bektas_hizmet_1.jpg', 'color' => [201, 106, 43]],
            ['ad' => 'Check-up Danışmanlığı', 'sure' => 45, 'fiyat' => 1800, 'aciklama' => 'Yaşa uygun check-up planı ve sonuçların hekimce yorumlanması.', 'img' => 'uploads/hizmet/bektas_hizmet_2.jpg', 'color' => [31, 41, 55]],
            ['ad' => 'Kronik Hastalık Takibi', 'sure' => 40, 'fiyat' => 1500, 'aciklama' => 'Hipertansiyon, diyabet ve metabolik sendrom izlemi.', 'img' => 'uploads/hizmet/bektas_hizmet_3.jpg', 'color' => [14, 116, 144]],
            ['ad' => 'Online Görüşme', 'sure' => 20, 'fiyat' => 900, 'aciklama' => 'Video kontrol, reçete ve takip görüşmesi.', 'img' => 'uploads/hizmet/bektas_hizmet_4.jpg', 'color' => [124, 58, 237]],
            ['ad' => 'Sağlıklı Yaşam Planı', 'sure' => 50, 'fiyat' => 1600, 'aciklama' => 'Beslenme, hareket ve uyku düzeni odaklı plan.', 'img' => 'uploads/hizmet/bektas_hizmet_5.jpg', 'color' => [5, 150, 105]],
            ['ad' => 'Laboratuvar Sonuç Değerlendirme', 'sure' => 25, 'fiyat' => 800, 'aciklama' => 'Kan tahlili ve görüntüleme sonuçlarının detaylı değerlendirilmesi.', 'img' => 'uploads/hizmet/bektas_hizmet_6.jpg', 'color' => [220, 38, 38]],
        ];

        foreach ($hizmetler as $h) {
            Hizmet::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'ad' => $h['ad']],
                [
                    'aciklama' => $h['aciklama'],
                    'resim' => $this->ensureImage($h['img'], $h['color'], $h['ad']),
                    'sure' => $h['sure'],
                    'fiyat' => $h['fiyat'],
                    'aktif_mi' => true,
                    'meta_baslik' => $h['ad'].' | Uzm. Dr. '.$adSoyad,
                    'meta_aciklama' => $h['aciklama'],
                    'meta_anahtar_kelimeler' => 'aile hekimi, '.$h['ad'].', bektaş özçetin',
                ]
            );
        }

        $bloglar = [
            ['baslik' => 'Check-up Ne Sıklıkla Yaptırılmalı?', 'icerik' => '<p>Yaş ve risk faktörlerine göre check-up sıklığı değişir. 40 yaş sonrası yıllık değerlendirme iyi bir başlangıçtır.</p>', 'img' => 'uploads/blog/bektas_blog_1.jpg'],
            ['baslik' => 'Hipertansiyon: Evde Tansiyon Ölçümü', 'icerik' => '<p>Doğru ölçüm için 5 dakika dinlenin, sırtınız destekli oturun, kolunuz kalp hizasında olsun.</p>', 'img' => 'uploads/blog/bektas_blog_2.jpg'],
            ['baslik' => 'Online Görüşme Ne Zaman Tercih Edilir?', 'icerik' => '<p>İlaç ayarı ve laboratuvar yorumu için online görüşme pratiktir; acilde yüz yüze değerlendirme gerekir.</p>', 'img' => 'uploads/blog/bektas_blog_3.jpg'],
            ['baslik' => 'Uyku Düzeni ve Bağışıklık', 'icerik' => '<p>Düzenli uyku, bağışıklık ve metabolizma için temeldir. Benzer saatte yatmak ritmi güçlendirir.</p>', 'img' => 'uploads/blog/bektas_blog_4.jpg'],
        ];
        foreach ($bloglar as $i => $blog) {
            Blog::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'baslik' => $blog['baslik']],
                [
                    'icerik' => $blog['icerik'],
                    'resim' => $this->ensureImage($blog['img'], [30 + $i * 40, 90, 120], $blog['baslik']),
                    'aktif_mi' => true,
                    'meta_baslik' => $blog['baslik'].' | Uzm. Dr. '.$adSoyad,
                    'meta_aciklama' => strip_tags($blog['icerik']),
                    'meta_anahtar_kelimeler' => 'sağlık blogu, '.$blog['baslik'],
                ]
            );
        }

        for ($i = 1; $i <= 6; $i++) {
            $path = $this->ensureImage('uploads/galeri/bektas_galeri_'.$i.'.jpg', [40 + $i * 20, 70 + $i * 10, 90], 'Galeri '.$i);
            DoktorGaleri::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'resim_yolu' => $path],
                ['baslik' => 'Muayenehane görseli '.$i, 'sira' => $i]
            );
        }

        $faqs = [
            ['soru' => 'Randevu nasıl alabilirim?', 'cevap' => 'Profildeki adım adım randevu alanından hizmet, tarih ve saat seçerek talep oluşturabilirsiniz.'],
            ['soru' => 'Online görüşme yapıyor musunuz?', 'cevap' => 'Evet. Randevu adımında “Online” görüşme türünü seçebilirsiniz.'],
            ['soru' => 'Randevumu iptal edebilir miyim?', 'cevap' => 'Evet. Randevu başlangıcına en az 12 saat kala iptal hakkınız vardır.'],
            ['soru' => 'İlk muayenede ne getirmeliyim?', 'cevap' => 'Önceki tahliller, ilaç listesi ve kimliğiniz yeterlidir.'],
            ['soru' => 'Check-up sonuçlarını kim yorumlar?', 'cevap' => 'Tüm sonuçlar bizzat hekim tarafından değerlendirilir.'],
        ];
        foreach ($faqs as $i => $faq) {
            Faq::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'soru' => $faq['soru']],
                ['cevap' => $faq['cevap'], 'sira' => $i + 1, 'aktif' => true]
            );
        }

        Egitim::query()->updateOrCreate(
            ['doktor_id' => $doktor->id, 'baslik' => 'Hasta Bilgilendirme Semineri: Kronik Hastalık Yönetimi'],
            [
                'ozet' => 'Diyabet ve hipertansiyon takibinde günlük pratik öneriler.',
                'icerik' => '<p>Kronik hastalıkların evde izlenmesi, ilaç uyumu ve yaşam tarzı değişiklikleri ele alınır.</p>',
                'kapak' => $this->ensureImage('uploads/egitim/bektas_egitim_1.jpg', [201, 106, 43], 'Eğitim'),
                'tip' => 'yuz_yuze',
                'baslangic_at' => now()->addWeeks(3)->setTime(14, 0),
                'bitis_at' => now()->addWeeks(3)->setTime(16, 0),
                'mekan' => 'Kadıköy — Özçetin Sağlık Danışmanlığı',
                'fiyat' => 0,
                'kontenjan' => 25,
                'basvuru_acik_mi' => true,
                'basvuru_bitis_at' => now()->addWeeks(2),
                'durum' => 'yayinda',
                'sira' => 1,
                'meta_baslik' => 'Kronik Hastalık Yönetimi Semineri',
            ]
        );

        $this->command?->info('✓ Hekim seed tamam (yöneticiye dokunulmadı).');
        $this->command?->info('  E-posta: '.$email.' | Şifre: sifre123');
        $this->command?->info('  Paket: '.($paket?->ad ?? 'yok').' (id='.($paket?->id ?? '-').')');
        $this->command?->info('  Doktor id='.$doktor->id);
        $this->command?->info('  Profil: '.($doktor->fresh()->profil_url ?? ''));
    }

    /**
     * @param  array{0:int,1:int,2:int}  $rgb
     */
    protected function ensureImage(string $relativePath, array $rgb, string $label): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $full = public_path($relativePath);
        $dir = dirname($full);
        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if (file_exists($full)) {
            return $relativePath;
        }

        $stock = [
            'uploads/hizmet/seeder_hizmet_1.jpg',
            'uploads/hizmet/seeder_hizmet_2.jpg',
            'uploads/hizmet/seeder_hizmet_3.jpg',
            'uploads/blog/seeder_blog_1.jpg',
            'uploads/blog/seeder_blog_2.jpg',
            'uploads/blog/seeder_blog_3.jpg',
            'uploads/galeri/seeder_galeri_1.jpg',
            'uploads/galeri/seeder_galeri_2.jpg',
            'uploads/galeri/seeder_galeri_3.jpg',
            'assets/images/logo.png',
        ];

        $pool = array_values(array_filter($stock, function ($s) use ($relativePath) {
            if (! is_file(public_path($s))) {
                return false;
            }
            if (str_contains($relativePath, '/hizmet/')) {
                return str_contains($s, '/hizmet/');
            }
            if (str_contains($relativePath, '/blog/')) {
                return str_contains($s, '/blog/');
            }
            if (str_contains($relativePath, '/galeri/')) {
                return str_contains($s, '/galeri/') || str_contains($s, 'logo');
            }

            return true;
        }));

        if ($pool !== []) {
            $pick = $pool[crc32($relativePath) % count($pool)];
            @copy(public_path($pick), $full);
        } elseif (function_exists('imagecreatetruecolor')) {
            $im = @imagecreatetruecolor(800, 600);
            if ($im) {
                $bg = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
                imagefill($im, 0, 0, $bg);
                $white = imagecolorallocate($im, 255, 255, 255);
                imagestring($im, 5, 40, 280, mb_substr($label, 0, 40), $white);
                imagejpeg($im, $full, 85);
                imagedestroy($im);
            }
        } elseif (is_file(public_path('assets/images/logo.png'))) {
            @copy(public_path('assets/images/logo.png'), $full);
        }

        return $relativePath;
    }
}
