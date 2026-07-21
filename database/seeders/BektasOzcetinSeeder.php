<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\DoktorCalismaSaati;
use App\Models\DoktorGaleri;
use App\Models\Egitim;
use App\Models\Faq;
use App\Models\HekimWebSitesi;
use App\Models\Hizmet;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Paket;
use App\Models\RandevuAyari;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Yalnızca hekim: Bektaş Özçetin — en üst paket + tam içerik.
 * Görseller Unsplash’ten indirilir (public/uploads/...).
 * Yönetici hesabına dokunmaz.
 *
 *   php artisan db:seed --class=BektasOzcetinSeeder
 *
 * Hekim paneli:
 *   E-posta : ozcetinbektas@gmail.com
 *   Şifre   : sifre123
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

        $paket = Paket::query()
            ->where('tur', 'bireysel')
            ->where(function ($q) {
                $q->where('ad', 'like', '%Web Sitesi%')
                    ->orWhere('ad', 'like', '%Entegrasyon%');
            })
            ->first()
            ?? Paket::query()->where('tur', 'bireysel')->where('ad', 'like', '%VIP%')->first()
            ?? Paket::query()->where('tur', 'bireysel')->orderByDesc('aylik_fiyat')->first();

        // Unsplash — doktor / sağlık temalı (ücretsiz, hotlink indirilir)
        $img = [
            'profil' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=800&q=80',
            'hizmet' => [
                'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80', // stethoscope / muayene
                'https://images.unsplash.com/photo-1551076805-e1869033e561?auto=format&fit=crop&w=800&q=80', // medical desk
                'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=800&q=80', // hospital
                'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=800&q=80', // telemedicine
                'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=800&q=80', // healthy lifestyle
                'https://images.unsplash.com/photo-1582719471384-894fbb16e074?auto=format&fit=crop&w=800&q=80', // laboratory
            ],
            'blog' => [
                'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1559757175-5700dde675bc?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?auto=format&fit=crop&w=1000&q=80',
            ],
            'galeri' => [
                'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1666214280557-f1b5022eb634?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1538108149393-fbbd81895907?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?auto=format&fit=crop&w=1000&q=80',
            ],
            'egitim' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1000&q=80',
        ];

        $profilPath = $this->downloadImage('uploads/profil/bektas_ozcetin_profil.jpg', $img['profil']);

        $payload = [
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
        ];

        $doktor = Doktor::withTrashed()->where('e_posta', $email)->first();
        if ($doktor) {
            if ($doktor->trashed()) {
                $doktor->restore();
            }
            $doktor->fill($payload);
            $doktor->save();
        } else {
            $doktor = Doktor::query()->create(array_merge(['e_posta' => $email], $payload));
        }

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
            ['ad' => 'Genel Muayene', 'sure' => 30, 'fiyat' => 1200, 'aciklama' => 'Kapsamlı şikayet dinleme, fizik muayene ve ilk değerlendirme.'],
            ['ad' => 'Check-up Danışmanlığı', 'sure' => 45, 'fiyat' => 1800, 'aciklama' => 'Yaşa uygun check-up planı ve sonuçların hekimce yorumlanması.'],
            ['ad' => 'Kronik Hastalık Takibi', 'sure' => 40, 'fiyat' => 1500, 'aciklama' => 'Hipertansiyon, diyabet ve metabolik sendrom izlemi.'],
            ['ad' => 'Online Görüşme', 'sure' => 20, 'fiyat' => 900, 'aciklama' => 'Video kontrol, reçete ve takip görüşmesi.'],
            ['ad' => 'Sağlıklı Yaşam Planı', 'sure' => 50, 'fiyat' => 1600, 'aciklama' => 'Beslenme, hareket ve uyku düzeni odaklı plan.'],
            ['ad' => 'Laboratuvar Sonuç Değerlendirme', 'sure' => 25, 'fiyat' => 800, 'aciklama' => 'Kan tahlili ve görüntüleme sonuçlarının detaylı değerlendirilmesi.'],
        ];

        foreach ($hizmetler as $i => $h) {
            $url = $img['hizmet'][$i % count($img['hizmet'])];
            Hizmet::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'ad' => $h['ad']],
                [
                    'aciklama' => $h['aciklama'],
                    'resim' => $this->downloadImage('uploads/hizmet/bektas_hizmet_'.($i + 1).'.jpg', $url),
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
            ['baslik' => 'Check-up Ne Sıklıkla Yaptırılmalı?', 'icerik' => '<p>Yaş ve risk faktörlerine göre check-up sıklığı değişir. 40 yaş sonrası yıllık değerlendirme iyi bir başlangıçtır.</p>'],
            ['baslik' => 'Hipertansiyon: Evde Tansiyon Ölçümü', 'icerik' => '<p>Doğru ölçüm için 5 dakika dinlenin, sırtınız destekli oturun, kolunuz kalp hizasında olsun.</p>'],
            ['baslik' => 'Online Görüşme Ne Zaman Tercih Edilir?', 'icerik' => '<p>İlaç ayarı ve laboratuvar yorumu için online görüşme pratiktir; acilde yüz yüze değerlendirme gerekir.</p>'],
            ['baslik' => 'Uyku Düzeni ve Bağışıklık', 'icerik' => '<p>Düzenli uyku, bağışıklık ve metabolizma için temeldir. Benzer saatte yatmak ritmi güçlendirir.</p>'],
        ];
        foreach ($bloglar as $i => $blog) {
            $url = $img['blog'][$i % count($img['blog'])];
            Blog::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'baslik' => $blog['baslik']],
                [
                    'icerik' => $blog['icerik'],
                    'resim' => $this->downloadImage('uploads/blog/bektas_blog_'.($i + 1).'.jpg', $url),
                    'aktif_mi' => true,
                    'meta_baslik' => $blog['baslik'].' | Uzm. Dr. '.$adSoyad,
                    'meta_aciklama' => strip_tags($blog['icerik']),
                    'meta_anahtar_kelimeler' => 'sağlık blogu, '.$blog['baslik'],
                ]
            );
        }

        foreach ($img['galeri'] as $i => $url) {
            $n = $i + 1;
            $path = $this->downloadImage('uploads/galeri/bektas_galeri_'.$n.'.jpg', $url);
            DoktorGaleri::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'resim_yolu' => $path],
                ['baslik' => 'Muayenehane görseli '.$n, 'sira' => $n]
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
                'kapak' => $this->downloadImage('uploads/egitim/bektas_egitim_1.jpg', $img['egitim']),
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

        // Bireysel özel web sitesi kaydı (paket web_sitesi özelliği)
        HekimWebSitesi::query()->updateOrCreate(
            ['doktor_id' => $doktor->id],
            [
                'domain' => 'ozcetinbektas.local',
                'tema' => 'custom',
                'durum' => 'aktif',
            ]
        );

        $this->command?->info('✓ Hekim seed + Unsplash görselleri tamam.');
        $this->command?->info('  E-posta: '.$email.' | Şifre: sifre123');
        $this->command?->info('  Paket: '.($paket?->ad ?? 'yok'));
        $this->command?->info('  Web sitesi: ozcetinbektas.local (aktif)');
        $this->command?->info('  Doktor id='.$doktor->id);
        $this->command?->info('  Profil: '.($doktor->fresh()->profil_url ?? ''));
    }

    /**
     * İnternetten indir → public/{relativePath}. Her seferinde günceller.
     */
    protected function downloadImage(string $relativePath, string $url): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $full = public_path($relativePath);
        $dir = dirname($full);
        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        try {
            $response = Http::timeout(45)
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

            $this->command?->warn('  ! İndirilemedi (HTTP '.$response->status().'): '.$relativePath);
        } catch (\Throwable $e) {
            $this->command?->warn('  ! İndirme hatası '.$relativePath.': '.$e->getMessage());
        }

        // Fallback: var olan dosya kalsın veya logo
        if (! is_file($full) && is_file(public_path('assets/images/logo.png'))) {
            @copy(public_path('assets/images/logo.png'), $full);
        }

        return $relativePath;
    }
}
