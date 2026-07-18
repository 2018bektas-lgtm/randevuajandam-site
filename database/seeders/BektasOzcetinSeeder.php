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
use App\Models\Yonetici;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
/**
 * Sistem yöneticisi + tam dolu demo hekim (Bektaş Özçetin).
 *
 *   php artisan db:seed --class=BektasOzcetinSeeder
 *
 * Giriş (yönetici / hekim paneli):
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

        // ── Yönetici ─────────────────────────────────────────────
        $yonetici = Yonetici::query()->updateOrCreate(
            ['e_posta' => $email],
            [
                'ad_soyad' => $adSoyad,
                'sifre' => $sifre, // model cast: hashed
                'telefon' => $telefon,
                'aktif_mi' => true,
            ]
        );

        // ── Hekim ────────────────────────────────────────────────
        $istanbul = Il::query()->where('ad', 'İstanbul')->first()
            ?? Il::query()->orderBy('id')->first();
        $ilce = $istanbul
            ? (Ilce::query()->where('il_id', $istanbul->id)->where('ad', 'Kadıköy')->first()
                ?? Ilce::query()->where('il_id', $istanbul->id)->orderBy('ad')->first())
            : null;

        $paket = Paket::query()->where('tur', 'bireysel')->where('ad', 'like', '%VIP%')->first()
            ?? Paket::query()->where('tur', 'bireysel')->where('ad', 'like', '%Profesyonel%')->first()
            ?? Paket::query()->where('tur', 'bireysel')->orderByDesc('id')->first();

        $profilPath = $this->ensureImage('uploads/profil/bektas_ozcetin_profil.jpg', [201, 106, 43], 'Bektaş Özçetin');

        $doktor = Doktor::query()->updateOrCreate(
            ['e_posta' => $email],
            [
                'ad_soyad' => $adSoyad,
                'sifre' => $sifre, // model cast: hashed
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

        // Branşlar
        $bransIds = [];
        foreach (['Aile Hekimliği', 'Dahiliye (İç Hastalıkları)', 'Genel Pratisyen'] as $bransAd) {
            $b = Brans::query()->firstOrCreate(['ad' => $bransAd]);
            $bransIds[] = $b->id;
        }
        $doktor->branslar()->sync($bransIds);

        // Randevu ayarları
        RandevuAyari::query()->updateOrCreate(
            ['doktor_id' => $doktor->id],
            [
                'randevu_onay_tipi' => 'manuel',
                'randevu_periyodu' => 30,
                'en_erken_randevu_saati' => 1,
                'en_gec_randevu_gunu' => 45,
                'randevu_iptal_aktif_mi' => true,
                'iptal_saat_limiti' => 12,
                'gunluk_maksimum_randevu' => 0,
                'email_bildirimleri' => true,
                'sms_bildirimleri' => false,
                'aktif_mi' => true,
            ]
        );

        // Çalışma saatleri (Pzt–Cmt)
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

        // Hizmetler
        $hizmetler = [
            [
                'ad' => 'Genel Muayene',
                'sure' => 30,
                'fiyat' => 1200,
                'aciklama' => 'Kapsamlı şikayet dinleme, fizik muayene ve ilk değerlendirme. Gerekirse ileri tetkik planı oluşturulur.',
                'img' => 'uploads/hizmet/bektas_hizmet_1.jpg',
                'color' => [201, 106, 43],
            ],
            [
                'ad' => 'Check-up Danışmanlığı',
                'sure' => 45,
                'fiyat' => 1800,
                'aciklama' => 'Yaşa ve risk faktörlerine uygun check-up paketinin planlanması ve sonuçların hekimce yorumlanması.',
                'img' => 'uploads/hizmet/bektas_hizmet_2.jpg',
                'color' => [31, 41, 55],
            ],
            [
                'ad' => 'Kronik Hastalık Takibi',
                'sure' => 40,
                'fiyat' => 1500,
                'aciklama' => 'Hipertansiyon, diyabet ve metabolik sendrom gibi kronik durumların düzenli izlemi ve tedavi uyumu.',
                'img' => 'uploads/hizmet/bektas_hizmet_3.jpg',
                'color' => [14, 116, 144],
            ],
            [
                'ad' => 'Online Görüşme',
                'sure' => 20,
                'fiyat' => 900,
                'aciklama' => 'Video üzerinden kontrol, reçete ve takip görüşmesi. Yüz yüze muayene gerektirmeyen durumlarda uygundur.',
                'img' => 'uploads/hizmet/bektas_hizmet_4.jpg',
                'color' => [124, 58, 237],
            ],
            [
                'ad' => 'Sağlıklı Yaşam Planı',
                'sure' => 50,
                'fiyat' => 1600,
                'aciklama' => 'Beslenme, hareket ve uyku düzeni odaklı kişiye özel yaşam tarzı önerileri ve takip planı.',
                'img' => 'uploads/hizmet/bektas_hizmet_5.jpg',
                'color' => [5, 150, 105],
            ],
            [
                'ad' => 'Laboratuvar Sonuç Değerlendirme',
                'sure' => 25,
                'fiyat' => 800,
                'aciklama' => 'Yapılmış kan tahlili ve görüntüleme sonuçlarının detaylı hekim değerlendirmesi.',
                'img' => 'uploads/hizmet/bektas_hizmet_6.jpg',
                'color' => [220, 38, 38],
            ],
        ];

        foreach ($hizmetler as $h) {
            $resim = $this->ensureImage($h['img'], $h['color'], $h['ad']);
            Hizmet::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'ad' => $h['ad']],
                [
                    'aciklama' => $h['aciklama'],
                    'resim' => $resim,
                    'sure' => $h['sure'],
                    'fiyat' => $h['fiyat'],
                    'aktif_mi' => true,
                    'meta_baslik' => $h['ad'].' | Uzm. Dr. '.$adSoyad,
                    'meta_aciklama' => $h['aciklama'],
                    'meta_anahtar_kelimeler' => 'aile hekimi, muayene, '.$h['ad'].', bektaş özçetin',
                ]
            );
        }

        // Bloglar
        $bloglar = [
            [
                'baslik' => 'Check-up Ne Sıklıkla Yaptırılmalı?',
                'icerik' => '<p>Yaş, aile öyküsü ve mevcut hastalıklara göre check-up sıklığı değişir. 40 yaş sonrası yıllık değerlendirme çoğu kişide uygun bir başlangıçtır.</p><p>Rutin kontroller, sessiz seyreden risk faktörlerinin erken fark edilmesine yardımcı olur.</p>',
                'img' => 'uploads/blog/bektas_blog_1.jpg',
            ],
            [
                'baslik' => 'Hipertansiyon: Evde Tansiyon Ölçümü İpuçları',
                'icerik' => '<p>Doğru ölçüm için en az 5 dakika dinlenin, sırtınız destekli oturun ve kolunuz kalp hizasında olsun.</p><p>Sabah ve akşam ölçümlerinizi not ederek hekiminize götürmek tedavi planını netleştirir.</p>',
                'img' => 'uploads/blog/bektas_blog_2.jpg',
            ],
            [
                'baslik' => 'Online Görüşme Ne Zaman Tercih Edilir?',
                'icerik' => '<p>İlaç ayarı, laboratuvar sonucu yorumu ve genel danışmanlık için online görüşme pratik bir seçenektir.</p><p>Acil belirtilerde ise yüz yüze değerlendirme veya acil servis tercih edilmelidir.</p>',
                'img' => 'uploads/blog/bektas_blog_3.jpg',
            ],
            [
                'baslik' => 'Uyku Düzeni ve Bağışıklık',
                'icerik' => '<p>Düzenli uyku, bağışıklık ve metabolizma için temel bir ihtiyaçtır. Her gece benzer saatte yatmak ritmi güçlendirir.</p>',
                'img' => 'uploads/blog/bektas_blog_4.jpg',
            ],
        ];
        foreach ($bloglar as $i => $blog) {
            $resim = $this->ensureImage($blog['img'], [30 + $i * 40, 90, 120], $blog['baslik']);
            Blog::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'baslik' => $blog['baslik']],
                [
                    'icerik' => $blog['icerik'],
                    'resim' => $resim,
                    'aktif_mi' => true,
                    'meta_baslik' => $blog['baslik'].' | Uzm. Dr. '.$adSoyad,
                    'meta_aciklama' => strip_tags($blog['icerik']),
                    'meta_anahtar_kelimeler' => 'sağlık blogu, aile hekimliği, '.$blog['baslik'],
                ]
            );
        }

        // Galeri
        for ($i = 1; $i <= 6; $i++) {
            $path = $this->ensureImage(
                'uploads/galeri/bektas_galeri_'.$i.'.jpg',
                [40 + $i * 20, 70 + $i * 10, 90],
                'Galeri '.$i
            );
            DoktorGaleri::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'resim_yolu' => $path],
                [
                    'baslik' => 'Muayenehane görseli '.$i,
                    'sira' => $i,
                ]
            );
        }

        // SSS
        $faqs = [
            ['soru' => 'Randevu nasıl alabilirim?', 'cevap' => 'Profil sayfasındaki adım adım randevu alanından hizmet, tarih ve saat seçerek talep oluşturabilirsiniz.'],
            ['soru' => 'Online görüşme yapıyor musunuz?', 'cevap' => 'Evet. Paket özelliği açıksa randevu adımında “Online” görüşme türünü seçebilirsiniz.'],
            ['soru' => 'Randevumu iptal edebilir miyim?', 'cevap' => 'Evet. Randevu başlangıcına en az 12 saat kala iptal hakkınız vardır.'],
            ['soru' => 'İlk muayenede ne getirmeliyim?', 'cevap' => 'Varsa önceki tahlil sonuçları, kullandığınız ilaç listesi ve kimliğinizi getirmeniz yeterlidir.'],
            ['soru' => 'Check-up sonuçlarını kim yorumlar?', 'cevap' => 'Tüm sonuçlar bizzat hekim tarafından değerlendirilir; gerekirse ek tetkik planlanır.'],
        ];
        foreach ($faqs as $i => $faq) {
            Faq::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'soru' => $faq['soru']],
                [
                    'cevap' => $faq['cevap'],
                    'sira' => $i + 1,
                    'aktif' => true,
                ]
            );
        }

        // Eğitim (örnek)
        Egitim::query()->updateOrCreate(
            ['doktor_id' => $doktor->id, 'baslik' => 'Hasta Bilgilendirme Semineri: Kronik Hastalık Yönetimi'],
            [
                'ozet' => 'Diyabet ve hipertansiyon takibinde günlük pratik öneriler.',
                'icerik' => '<p>Bu seminerde kronik hastalıkların evde izlenmesi, ilaç uyumu ve yaşam tarzı değişiklikleri ele alınır.</p>',
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

        $this->command?->info('✓ Yönetici: '.$yonetici->e_posta.' / sifre123');
        $this->command?->info('✓ Hekim: '.$doktor->e_posta.' (id='.$doktor->id.') — hizmet, blog, galeri, SSS, eğitim, çalışma saatleri eklendi.');
        $this->command?->info('  Profil: '.($doktor->profil_url ?? ''));
    }

    /**
     * Ensure a public image exists; generate placeholder or copy logo.
     *
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

        // Prefer existing seeder stock images when available
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
        if (! file_exists($full)) {
            $copied = false;
            foreach ($stock as $s) {
                $src = public_path($s);
                if (is_file($src) && str_contains($relativePath, explode('/', $s)[1] ?? 'x')) {
                    // loose match by folder type
                }
                if (is_file($src) && ! $copied && (
                    (str_contains($relativePath, '/hizmet/') && str_contains($s, '/hizmet/'))
                    || (str_contains($relativePath, '/blog/') && str_contains($s, '/blog/'))
                    || (str_contains($relativePath, '/galeri/') && str_contains($s, '/galeri/'))
                    || (str_contains($relativePath, '/profil/') && str_contains($s, 'logo'))
                    || (str_contains($relativePath, '/egitim/') && str_contains($s, 'logo'))
                )) {
                    // pick rotating stock by basename hash
                    continue;
                }
            }

            // Rotate stock by hash of path
            $pool = array_values(array_filter($stock, function ($s) use ($relativePath) {
                if (str_contains($relativePath, '/hizmet/')) {
                    return str_contains($s, '/hizmet/');
                }
                if (str_contains($relativePath, '/blog/')) {
                    return str_contains($s, '/blog/');
                }
                if (str_contains($relativePath, '/galeri/')) {
                    return str_contains($s, '/galeri/') || str_contains($s, 'logo');
                }

                return str_contains($s, 'logo') || str_contains($s, '/hizmet/');
            }));
            if ($pool === []) {
                $pool = array_values(array_filter($stock, fn ($s) => is_file(public_path($s))));
            }
            if ($pool !== []) {
                $pick = $pool[crc32($relativePath) % count($pool)];
                $src = public_path($pick);
                if (is_file($src)) {
                    @copy($src, $full);
                    $copied = true;
                }
            }

            if (! $copied && function_exists('imagecreatetruecolor')) {
                $im = @imagecreatetruecolor(800, 600);
                if ($im) {
                    $bg = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
                    imagefill($im, 0, 0, $bg);
                    $white = imagecolorallocate($im, 255, 255, 255);
                    $text = mb_substr($label, 0, 40);
                    imagestring($im, 5, 40, 280, $text, $white);
                    imagejpeg($im, $full, 85);
                    imagedestroy($im);
                    $copied = true;
                }
            }

            if (! $copied && is_file(public_path('assets/images/logo.png'))) {
                @copy(public_path('assets/images/logo.png'), $full);
            }
        }

        return $relativePath;
    }
}
