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
use App\Models\Klinik;
use App\Models\KlinikWebSitesi;
use App\Models\Paket;
use App\Models\RandevuAyari;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Klinik Özel Web Sitesi paketi + dolu içerik.
 * platformda_gorunur = false → ana sitede hekim/klinik GÖRÜNMEZ.
 *
 *   php artisan db:seed --class=GizliKlinikWebDemoSeeder
 *
 * Giriş:
 *   E-posta : demo.klinik@randevuajandam.com
 *   Şifre   : DemoKlinik2026!
 */
class GizliKlinikWebDemoSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'demo.klinik@randevuajandam.com';
        $sifre = 'DemoKlinik2026!';
        $telefon = '0 (212) 555 01 01';
        $adSoyad = 'Ayşe Yılmaz';
        $klinikAd = 'Demo Sağlık Polikliniği';

        $istanbul = Il::query()->where('ad', 'İstanbul')->first()
            ?? Il::query()->where('slug', 'istanbul')->first()
            ?? Il::query()->orderBy('id')->first();
        $ilce = $istanbul
            ? (Ilce::query()->where('il_id', $istanbul->id)->where('ad', 'like', '%Kadıköy%')->first()
                ?? Ilce::query()->where('il_id', $istanbul->id)->where('ad', 'like', '%Besiktas%')->orWhere(function ($q) use ($istanbul) {
                    $q->where('il_id', $istanbul->id)->where('ad', 'like', '%Beşiktaş%');
                })->first()
                ?? Ilce::query()->where('il_id', $istanbul->id)->orderBy('ad')->first())
            : null;

        // En üst klinik web paketi
        $paket = Paket::query()
            ->where('tur', 'klinik')
            ->where(function ($q) {
                $q->where('ad', 'like', '%Özel Web%')
                    ->orWhere('ad', 'like', '%Ozel Web%')
                    ->orWhere('ad', 'like', '%Web Sitesi%');
            })
            ->orderByDesc('aylik_fiyat')
            ->first()
            ?? Paket::query()->where('tur', 'klinik')->orderByDesc('aylik_fiyat')->first();

        if (! $paket) {
            $this->command?->error('Klinik web paketi bulunamadı. Önce KlinikSeeder çalıştırın.');

            return;
        }

        $img = [
            'profil' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=800&q=80',
            'logo' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=600&q=80',
            'hizmet' => [
                'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1551076805-e1869033e561?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1582719471384-894fbb16e074?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?auto=format&fit=crop&w=800&q=80',
            ],
            'blog' => [
                'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1559757175-5700dde675bc?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?auto=format&fit=crop&w=1000&q=80',
            ],
            'galeri' => [
                'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1666214280557-f1b5022eb634?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1538108149393-fbbd81895907?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=1000&q=80',
            ],
            'egitim' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1000&q=80',
        ];

        $profilPath = $this->downloadImage('uploads/profil/demo_klinik_sahip.jpg', $img['profil']);
        $logoPath = $this->downloadImage('uploads/klinik/demo_klinik_logo.jpg', $img['logo']);

        $payload = [
            'ad_soyad' => $adSoyad,
            'sifre' => $sifre,
            'telefon' => $telefon,
            'il_id' => $istanbul?->id,
            'ilce_id' => $ilce?->id,
            'tur' => 'klinik',
            'klinik_rolu' => 'sahip',
            'klinik_adi' => $klinikAd,
            'paket_id' => $paket->id,
            'odeme_periyodu' => 'yillik',
            'uyelik_baslangic' => now()->subDays(7),
            'uyelik_bitis' => now()->addYear(),
            'aktif_mi' => true,
            // Ana site vitrininde GÖRÜNMEZ
            'platformda_gorunur' => false,
            'meslek_dogrulama_durumu' => 'onaylandi',
            'meslek_dogrulandi_at' => now(),
            'unvan' => 'Uzm. Dr.',
            'uzmanlik_alani' => 'Dermatoloji, İç Hastalıkları',
            'mezuniyet' => [
                'İstanbul Üniversitesi Cerrahpaşa Tıp Fakültesi (2010)',
                'Dermatoloji Uzmanlığı - İstanbul (2016)',
            ],
            'biyografi' => '<p><strong>Uzm. Dr. Ayşe Yılmaz</strong>, '.$klinikAd.' kurucu hekimidir. '
                .'Cilt sağlığı ve genel dahili değerlendirme alanlarında hasta odaklı hizmet sunar.</p>'
                .'<p>Klinik web sitesi ve randevu paneli Demo paketinde tam aktiftir; '
                .'<em>ana Randevu Ajandam vitrininde listelenmez</em> (platformda gizli).</p>',
            'adres' => 'Bağdat Cad. No:120, Kadıköy / İstanbul',
            'enlem' => 40.9632,
            'boylam' => 29.0634,
            'profil_resmi' => $profilPath,
            'instagram' => 'demoklinik',
            'facebook' => 'demoklinik',
            'web_sitesi' => 'https://demo-klinik.example',
            'iyzico_subscription_status' => 'ACTIVE',
            'iyzico_subscription_reference_code' => 'demo_klinik_web_'.Str::random(8),
        ];

        $doktor = Doktor::withTrashed()->where('e_posta', $email)->first();
        if ($doktor) {
            if ($doktor->trashed()) {
                $doktor->restore();
            }
            $doktor->fill($payload)->save();
        } else {
            $doktor = Doktor::query()->create(array_merge(['e_posta' => $email], $payload));
        }

        $bransIds = [];
        foreach (['Dermatoloji ve Venereoloji', 'İç Hastalıkları', 'Aile Hekimliği'] as $bransAd) {
            $b = Brans::query()->where('ad', $bransAd)->first()
                ?? Brans::query()->where('ad', 'like', '%'.mb_substr($bransAd, 0, 8).'%')->first()
                ?? Brans::query()->firstOrCreate(
                    ['ad' => $bransAd],
                    ['slug' => Str::slug($bransAd)]
                );
            $bransIds[] = $b->id;
        }
        $doktor->branslar()->sync($bransIds);

        $klinik = Klinik::query()->where('sahip_doktor_id', $doktor->id)->first()
            ?? Klinik::query()->where('e_posta', 'info@demo-klinik.example')->first();

        $klinikData = [
            'ad' => $klinikAd,
            'sahip_doktor_id' => $doktor->id,
            'paket_id' => $paket->id,
            'logo' => $logoPath,
            'telefon' => $telefon,
            'e_posta' => 'info@demo-klinik.example',
            'adres' => 'Bağdat Cad. No:120, Kadıköy / İstanbul',
            'il_id' => $istanbul?->id,
            'ilce_id' => $ilce?->id,
            'enlem' => 40.9632,
            'boylam' => 29.0634,
            'web_sitesi' => 'https://demo-klinik.example',
            'aciklama' => 'Demo Sağlık Polikliniği — Randevu Ajandam Klinik Özel Web Sitesi paketi ile kurulmuş örnek klinik. '
                .'Ana platform vitrininde gizli; panel ve özel web sitesi aktiftir.',
            'odeme_periyodu' => 'yillik',
            'uyelik_baslangic' => now()->subDays(7),
            'uyelik_bitis' => now()->addYear(),
            'max_doktor_sayisi' => $paket->max_doktor_sayisi ?: 999,
            'aktif_mi' => true,
            // Ana site vitrininde GÖRÜNMEZ
            'platformda_gorunur' => false,
            'iyzico_subscription_status' => 'ACTIVE',
            'meta_baslik' => $klinikAd.' | Özel Klinik',
            'meta_aciklama' => 'Demo klinik profili (vitrinde gizli).',
            'calisma_saatleri' => [
                'pazartesi' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
                'sali' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
                'carsamba' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
                'persembe' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
                'cuma' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
                'cumartesi' => ['acilis' => '09:00', 'kapanis' => '14:00', 'kapali' => false],
                'pazar' => ['acilis' => '00:00', 'kapanis' => '00:00', 'kapali' => true],
            ],
            'sosyal_medya' => [
                'instagram' => 'demoklinik',
                'facebook' => 'demoklinik',
            ],
        ];

        if ($klinik) {
            $klinik->fill($klinikData)->save();
        } else {
            $klinik = Klinik::query()->create($klinikData);
        }

        $doktor->forceFill([
            'klinik_id' => $klinik->id,
            'klinik_rolu' => 'sahip',
            'klinik_katilma_tarihi' => now()->subDays(7),
            'klinik_aktif_mi' => true,
            'tur' => 'klinik',
            'platformda_gorunur' => false,
        ])->save();

        RandevuAyari::query()->updateOrCreate(
            ['doktor_id' => $doktor->id],
            [
                'randevu_onay_tipi' => 'otomatik',
                'randevu_periyodu' => 20,
                'en_erken_randevu_saati' => 1,
                'en_gec_randevu_gunu' => 45,
                'randevu_iptal_aktif_mi' => true,
                'iptal_saat_limiti' => 6,
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
            ['ad' => 'Dermatoloji Muayenesi', 'sure' => 20, 'fiyat' => 1500, 'aciklama' => 'Cilt, saç ve tırnak hastalıkları değerlendirmesi.'],
            ['ad' => 'Genel Dahili Muayene', 'sure' => 30, 'fiyat' => 1200, 'aciklama' => 'Şikayet dinleme ve fizik muayene.'],
            ['ad' => 'Check-up Planlama', 'sure' => 40, 'fiyat' => 2000, 'aciklama' => 'Yaşa uygun check-up ve sonuç yorumu.'],
            ['ad' => 'Online Kontrol', 'sure' => 15, 'fiyat' => 800, 'aciklama' => 'Video ile kısa takip görüşmesi.'],
            ['ad' => 'Cilt Bakım Danışmanlığı', 'sure' => 25, 'fiyat' => 1100, 'aciklama' => 'Rutin cilt bakımı ve ürün yönlendirmesi.'],
            ['ad' => 'Laboratuvar Değerlendirme', 'sure' => 20, 'fiyat' => 900, 'aciklama' => 'Tahlil sonuçlarının hekimce yorumlanması.'],
        ];
        foreach ($hizmetler as $i => $h) {
            Hizmet::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'ad' => $h['ad']],
                [
                    'aciklama' => $h['aciklama'],
                    'resim' => $this->downloadImage('uploads/hizmet/demo_klinik_hizmet_'.($i + 1).'.jpg', $img['hizmet'][$i % count($img['hizmet'])]),
                    'sure' => $h['sure'],
                    'fiyat' => $h['fiyat'],
                    'aktif_mi' => true,
                    'meta_baslik' => $h['ad'].' | '.$adSoyad,
                    'meta_aciklama' => $h['aciklama'],
                ]
            );
        }

        $bloglar = [
            ['baslik' => 'Yaz Aylarında Cilt Koruma', 'icerik' => '<p>Güneş koruyucu kullanımı ve nem dengesi cilt sağlığının temelidir.</p><p>SPF 30+ ürünleri günlük rutine ekleyin.</p>'],
            ['baslik' => 'Check-up Ne Zaman Yapılmalı?', 'icerik' => '<p>Risk faktörlerine göre yıllık veya iki yılda bir check-up planlanabilir.</p>'],
            ['baslik' => 'Online Görüşmenin Avantajları', 'icerik' => '<p>İlaç ayarı ve tahlil yorumu için online kontrol pratik bir seçenektir.</p>'],
            ['baslik' => 'Kronik Hastalıkta Düzenli Takip', 'icerik' => '<p>Hipertansiyon ve diyabette düzenli izlem komplikasyon riskini azaltır.</p>'],
        ];
        foreach ($bloglar as $i => $blog) {
            Blog::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'baslik' => $blog['baslik']],
                [
                    'icerik' => $blog['icerik'],
                    'resim' => $this->downloadImage('uploads/blog/demo_klinik_blog_'.($i + 1).'.jpg', $img['blog'][$i % count($img['blog'])]),
                    'aktif_mi' => true,
                    'meta_baslik' => $blog['baslik'],
                    'meta_aciklama' => strip_tags($blog['icerik']),
                ]
            );
        }

        foreach ($img['galeri'] as $i => $url) {
            $n = $i + 1;
            $path = $this->downloadImage('uploads/galeri/demo_klinik_galeri_'.$n.'.jpg', $url);
            DoktorGaleri::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'resim_yolu' => $path],
                ['baslik' => 'Klinik görseli '.$n, 'sira' => $n]
            );
        }

        $faqs = [
            ['soru' => 'Randevu nasıl alırım?', 'cevap' => 'Klinik web sitesi veya panel üzerinden randevu oluşturabilirsiniz. Ana sitede listelenmiyoruz.'],
            ['soru' => 'Online görüşme var mı?', 'cevap' => 'Evet, Online Kontrol hizmeti ile video görüşme planlanabilir.'],
            ['soru' => 'İptal politikası nedir?', 'cevap' => 'Randevu saatine en az 6 saat kala iptal edebilirsiniz.'],
            ['soru' => 'Park yeri var mı?', 'cevap' => 'Klinik yakınında ücretli otopark imkânı vardır.'],
            ['soru' => 'Sigorta geçerli mi?', 'cevap' => 'Anlaşmalı kurumlar için resepsiyondan bilgi alabilirsiniz.'],
        ];
        foreach ($faqs as $i => $faq) {
            Faq::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'soru' => $faq['soru']],
                ['cevap' => $faq['cevap'], 'sira' => $i + 1, 'aktif' => true]
            );
        }

        Egitim::query()->updateOrCreate(
            ['doktor_id' => $doktor->id, 'baslik' => 'Cilt Sağlığı Hasta Semineri'],
            [
                'ozet' => 'Güneş koruması ve cilt bakımı hakkında bilgilendirme.',
                'icerik' => '<p>Yaz ve kış cilt bakımı, SPF kullanımı ve sık cilt sorunları ele alınır.</p>',
                'kapak' => $this->downloadImage('uploads/egitim/demo_klinik_egitim.jpg', $img['egitim']),
                'tip' => 'yuz_yuze',
                'baslangic_at' => now()->addWeeks(4)->setTime(15, 0),
                'bitis_at' => now()->addWeeks(4)->setTime(17, 0),
                'mekan' => $klinikAd.' — Kadıköy',
                'fiyat' => 0,
                'kontenjan' => 30,
                'basvuru_acik_mi' => true,
                'basvuru_bitis_at' => now()->addWeeks(3),
                'durum' => 'yayinda',
                'sira' => 1,
                'meta_baslik' => 'Cilt Sağlığı Semineri',
            ]
        );

        KlinikWebSitesi::query()->updateOrCreate(
            ['klinik_id' => $klinik->id],
            [
                'domain' => 'demo-klinik.randevuajandam.local',
                'tema' => 'custom',
                'durum' => 'aktif',
            ]
        );

        $this->command?->info('✓ Gizli klinik web demo seed tamam.');
        $this->command?->info('  Paket: '.$paket->ad.' (id='.$paket->id.')');
        $this->command?->info('  Klinik: '.$klinik->ad.' id='.$klinik->id.' platformda_gorunur=0');
        $this->command?->info('  Hekim: '.$email.' / '.$sifre.' platformda_gorunur=0');
        $this->command?->info('  İçerik: hizmet, blog, galeri, SSS, eğitim, çalışma saatleri');
        $this->command?->warn('  Ana sitede listelenmez (gizli). Panel + klinik web paketi aktif.');
    }

    protected function downloadImage(string $relativePath, string $url): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $full = public_path($relativePath);
        $dir = dirname($full);
        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        try {
            $response = Http::timeout(50)
                ->withHeaders([
                    'User-Agent' => 'RandevuAjandamSeeder/1.0',
                    'Accept' => 'image/*',
                ])
                ->get($url);

            if ($response->successful() && strlen($response->body()) > 1000) {
                File::put($full, $response->body());
                $this->command?->line('  ↓ '.$relativePath);

                return $relativePath;
            }
            $this->command?->warn('  ! HTTP '.$response->status().' '.$relativePath);
        } catch (\Throwable $e) {
            $this->command?->warn('  ! '.$relativePath.': '.$e->getMessage());
        }

        if (! is_file($full) && is_file(public_path('assets/images/logo.png'))) {
            @copy(public_path('assets/images/logo.png'), $full);
        }

        return $relativePath;
    }
}
