<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Blog;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\DoktorCalismaSaati;
use App\Models\DoktorGaleri;
use App\Models\Egitim;
use App\Models\Faq;
use App\Models\FinansKategori;
use App\Models\Gider;
use App\Models\Hasta;
use App\Models\Hizmet;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Odeme;
use App\Models\Paket;
use App\Models\Randevu;
use App\Models\RandevuAyari;
use App\Models\Yorum;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Bireysel psikolog — Özel Web paketi, dolu panel + vitrin içeriği.
 *
 *   php artisan db:seed --class=PsikologDemoSeeder
 *
 * Giriş (site / mobil / hekim paneli):
 *   E-posta : demo.psikolog@randevuajandam.com
 *   Şifre   : DemoPsk2026!
 *
 * Hekim sitesi API:
 *   RANDEVU_API_KEY=ra-psk-demo
 *   RANDEVU_API_SECRET=psk-demo-secret
 */
class PsikologDemoSeeder extends Seeder
{
    public const EMAIL = 'demo.psikolog@randevuajandam.com';

    public const SIFRE = 'DemoPsk2026!';

    public const API_KEY = 'ra-psk-demo';

    public const API_SECRET = 'psk-demo-secret';

    public function run(): void
    {
        $adSoyad = 'Elif Kara';
        $unvan = 'Uzm. Psk.';
        $telefon = '0 (312) 555 18 18';

        $ankara = Il::query()->where('ad', 'Ankara')->first()
            ?? Il::query()->where('slug', 'ankara')->first();
        $ilce = $ankara
            ? (Ilce::query()->where('il_id', $ankara->id)->where('ad', 'like', '%Çankaya%')->first()
                ?? Ilce::query()->where('il_id', $ankara->id)->where('ad', 'like', '%Cankaya%')->first()
                ?? Ilce::query()->where('il_id', $ankara->id)->orderBy('ad')->first())
            : null;

        $paket = Paket::query()
            ->where('tur', 'bireysel')
            ->whereHas('sistemOzellikleri', fn ($q) => $q->where('kod', 'web_sitesi'))
            ->orderByDesc('aylik_fiyat')
            ->first()
            ?? Paket::query()
                ->where('tur', 'bireysel')
                ->whereHas('sistemOzellikleri', fn ($q) => $q->where('kod', 'ai_asistan'))
                ->orderByDesc('aylik_fiyat')
                ->first();

        if (! $paket) {
            $this->command?->error('Bireysel web / Profesyonel paket bulunamadı. Önce PaketSeeder çalıştırın.');

            return;
        }

        $img = [
            'profil' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=800&q=80',
            'hizmet' => [
                'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1544027993-37dbfe43562a?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1516302752625-fcc3c50ae61f?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?auto=format&fit=crop&w=800&q=80',
            ],
            'blog' => [
                'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1516302752625-fcc3c50ae61f?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1474418397713-7ede21d49118?auto=format&fit=crop&w=1000&q=80',
            ],
            'galeri' => [
                'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=1000&q=80',
            ],
            'egitim' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1000&q=80',
        ];

        $payload = [
            'ad_soyad' => $adSoyad,
            'sifre' => self::SIFRE,
            'telefon' => $telefon,
            'il_id' => $ankara?->id,
            'ilce_id' => $ilce?->id,
            'tur' => 'bireysel',
            'klinik_adi' => 'Elif Kara Psikoloji',
            'paket_id' => $paket->id,
            'kayit_paket_id' => $paket->id,
            'odeme_periyodu' => 'yillik',
            'uyelik_baslangic' => now()->subMonths(3),
            'uyelik_bitis' => now()->addYear(),
            'aktif_mi' => true,
            'platformda_gorunur' => true,
            'meslek_dogrulama_durumu' => 'onaylandi',
            'meslek_dogrulandi_at' => now()->subMonths(3),
            'unvan' => $unvan,
            'uzmanlik_alani' => 'Klinik Psikoloji',
            'mezuniyet' => [
                'Hacettepe Üniversitesi Psikoloji (2014)',
                'Klinik Psikoloji Yüksek Lisans — Ankara Üniversitesi (2017)',
            ],
            'biyografi' => '<p><strong>Uzm. Psk. Elif Kara</strong>, kaygı, depresyon, ilişki sorunları ve yaşam geçişlerinde '
                .'kanıta dayalı psikoterapi sunar. Bilişsel davranışçı terapi (BDT) ve şema terapi ekollerinde çalışır.</p>'
                .'<p>Yüz yüze ve online seanslar Ankara Çankaya ofisinde ve güvenli görüntülü görüşme ile yürütülür. '
                .'İlk görüşmede ihtiyaçlar birlikte netleştirilir; süreç şeffaf ve danışan odaklı ilerler.</p>',
            'adres' => 'Tunalı Hilmi Cad. No:86 D:4, Çankaya / Ankara',
            'enlem' => 39.9066,
            'boylam' => 32.8597,
            'profil_resmi' => $this->downloadImage('uploads/profil/demo_psikolog.jpg', $img['profil']),
            'instagram' => 'elifkarapsikoloji',
            'linkedin' => 'elif-kara-psikolog',
            'web_sitesi' => 'https://elifkara.example',
            'iyzico_subscription_status' => 'ACTIVE',
            'iyzico_subscription_reference_code' => 'demo_psk_'.Str::random(8),
        ];

        $doktor = Doktor::withTrashed()->where('e_posta', self::EMAIL)->first();
        if ($doktor) {
            if ($doktor->trashed()) {
                $doktor->restore();
            }
            $doktor->fill($payload)->save();
        } else {
            $doktor = Doktor::query()->create(array_merge(['e_posta' => self::EMAIL], $payload));
        }

        $brans = Brans::query()->where('ad', 'Psikoloji')->first()
            ?? Brans::query()->firstOrCreate(
                ['ad' => 'Psikoloji'],
                ['slug' => 'psikoloji', 'aciklama' => 'Psikolojik değerlendirme, danışmanlık ve destek süreçleri.']
            );
        $doktor->branslar()->sync([$brans->id]);

        ApiKey::query()->where('doktor_id', $doktor->id)->where('api_key', self::API_KEY)->delete();
        ApiKey::query()->create([
            'doktor_id' => $doktor->id,
            'api_key' => self::API_KEY,
            'secret_key' => ApiKey::hashSecret(self::API_SECRET),
            'durum' => true,
        ]);

        RandevuAyari::query()->updateOrCreate(
            ['doktor_id' => $doktor->id],
            [
                'randevu_onay_tipi' => 'manuel',
                'randevu_periyodu' => 50,
                'en_erken_randevu_saati' => 2,
                'en_gec_randevu_gunu' => 30,
                'randevu_iptal_aktif_mi' => true,
                'iptal_saat_limiti' => 24,
                'gunluk_maksimum_randevu' => 8,
                'email_bildirimleri' => true,
                'sms_bildirimleri' => true,
                'online_randevu_aktif' => true,
                'yuzyuze_randevu_aktif' => true,
                'aktif_mi' => true,
            ]
        );

        foreach (range(1, 7) as $gun) {
            DoktorCalismaSaati::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'gun' => $gun],
                [
                    'aktif_mi' => $gun <= 5,
                    'mesai_baslangic' => '10:00:00',
                    'mesai_bitis' => '19:00:00',
                    'ogle_arasi_aktif_mi' => $gun <= 5,
                    'ogle_baslangic' => '13:00:00',
                    'ogle_bitis' => '14:00:00',
                ]
            );
        }

        $hizmetler = [
            ['ad' => 'İlk görüşme / değerlendirme', 'sure' => 50, 'fiyat' => 1800, 'aciklama' => 'Şikayet, öykü ve hedeflerin birlikte netleştirildiği ilk seans.'],
            ['ad' => 'Bireysel psikoterapi', 'sure' => 50, 'fiyat' => 2000, 'aciklama' => 'Kaygı, depresyon, öfke ve yaşam geçişlerinde BDT temelli bireysel seans.'],
            ['ad' => 'Online psikoterapi', 'sure' => 50, 'fiyat' => 1800, 'aciklama' => 'Güvenli görüntülü görüşme ile bireysel terapi.'],
            ['ad' => 'Çift terapisi', 'sure' => 80, 'fiyat' => 2800, 'aciklama' => 'İletişim, çatışma ve bağlanma temalı çift seansı.'],
            ['ad' => 'Ergen danışmanlığı', 'sure' => 45, 'fiyat' => 1700, 'aciklama' => 'Ergenlik dönemi kaygı, sınav stresi ve aile iletişimi.'],
            ['ad' => 'Kısa takip seansı', 'sure' => 25, 'fiyat' => 900, 'aciklama' => 'İlaçsız kısa kontrol ve ev ödevi değerlendirmesi.'],
        ];
        foreach ($hizmetler as $i => $h) {
            Hizmet::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'ad' => $h['ad']],
                [
                    'aciklama' => $h['aciklama'],
                    'resim' => $this->downloadImage('uploads/hizmet/demo_psk_hizmet_'.($i + 1).'.jpg', $img['hizmet'][$i % count($img['hizmet'])]),
                    'sure' => $h['sure'],
                    'fiyat' => $h['fiyat'],
                    'aktif_mi' => true,
                    'meta_baslik' => $h['ad'].' | '.$unvan.' '.$adSoyad,
                    'meta_aciklama' => $h['aciklama'],
                ]
            );
        }

        $bloglar = [
            [
                'baslik' => 'Kaygıyla baş etmek: nefes ve düşünce farkındalığı',
                'icerik' => '<p>Kaygı bedende hızlanan kalp, gergin omuz ve kaçınma davranışlarıyla kendini gösterir. '
                    .'4-6-8 nefes rutini ve düşünce kaydı, ilk adım olarak evde uygulanabilir.</p>'
                    .'<p>Belirtiler günlük işleri aksatıyorsa bireysel psikoterapi planı netleştirilmelidir.</p>',
            ],
            [
                'baslik' => 'İlk terapi seansında neler olur?',
                'icerik' => '<p>İlk görüşmede gizlilik çerçevesi, hedefler ve seans sıklığı konuşulur. '
                    .'Doğru teşhis koymak değil, birlikte anlaşılır bir yol haritası çıkarmak amaçtır.</p>',
            ],
            [
                'baslik' => 'Online terapi yüz yüze kadar etkili midir?',
                'icerik' => '<p>Araştırmalar birçok kaygı ve depresyon tablosunda online BDT’nin yüz yüze seanslara yakın etki gösterdiğini söyler. '
                    .'Sessiz oda, kulaklık ve sabit saat ritmi başarıyı artırır.</p>',
            ],
            [
                'baslik' => 'Çift ilişkisinde tekrarlayan tartışmalar',
                'icerik' => '<p>Aynı konunun farklı kelimelerle dönmesi çoğu zaman ihtiyaçların duyulmamasından kaynaklanır. '
                    .'Çift terapisinde eleştiri yerine ihtiyaç dili çalışılır.</p>',
            ],
            [
                'baslik' => 'Ergende sınav kaygısı ve ebeveyn tutumu',
                'icerik' => '<p>Performans baskısı kaygıyı büyütür. Ebeveynin dinleyen duruşu, sonuç değil süreç odaklı geri bildirim kaygıyı yumuşatır.</p>',
            ],
            [
                'baslik' => 'Uyku hijyeni ve ruh hali',
                'icerik' => '<p>Düzensiz uyku kaygı ve çökkünlüğü besler. Sabit uyanma saati, kafein sınırı ve yatak odasını ekrandan ayırmak ilk müdahaledir.</p>',
            ],
        ];
        foreach ($bloglar as $i => $blog) {
            Blog::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'baslik' => $blog['baslik']],
                [
                    'icerik' => $blog['icerik'],
                    'resim' => $this->downloadImage('uploads/blog/demo_psk_blog_'.($i + 1).'.jpg', $img['blog'][$i % count($img['blog'])]),
                    'aktif_mi' => true,
                    'meta_baslik' => $blog['baslik'],
                    'meta_aciklama' => Str::limit(strip_tags($blog['icerik']), 160),
                ]
            );
        }

        foreach ($img['galeri'] as $i => $url) {
            $n = $i + 1;
            $path = $this->downloadImage('uploads/galeri/demo_psk_galeri_'.$n.'.jpg', $url);
            DoktorGaleri::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'resim_yolu' => $path],
                ['baslik' => 'Danışmanlık ofisi '.$n, 'sira' => $n]
            );
        }

        $faqs = [
            ['soru' => 'Seans süresi ne kadar?', 'cevap' => 'Bireysel seanslar 50 dakika, çift terapisi 80 dakikadır. İlk görüşme de 50 dakikadır.'],
            ['soru' => 'Online randevu nasıl işler?', 'cevap' => 'Onaylanan saatte güvenli görüntülü görüşme linki ile bağlanırsınız. Sessiz bir oda yeterlidir.'],
            ['soru' => 'İptal politikası nedir?', 'cevap' => 'Seansa 24 saat kala ücretsiz iptal / erteleme yapılabilir.'],
            ['soru' => 'Kayıt tutulur mu?', 'cevap' => 'Seanslar gizli tutulur. Yasal zorunluluklar dışında üçüncü kişilerle paylaşılmaz.'],
            ['soru' => 'İlaç yazar mısınız?', 'cevap' => 'Hayır. Psikologlar ilaç yazmaz; gerekirse psikiyatri yönlendirmesi yapılır.'],
        ];
        foreach ($faqs as $i => $faq) {
            Faq::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'soru' => $faq['soru']],
                ['cevap' => $faq['cevap'], 'sira' => $i + 1, 'aktif' => true]
            );
        }

        Egitim::query()->updateOrCreate(
            ['doktor_id' => $doktor->id, 'baslik' => 'Kaygıyla baş etme atölyesi'],
            [
                'ozet' => 'Nefes, düşünce kaydı ve kaçınma döngüsünü tanıma üzerine 2 saatlik açık atölye.',
                'icerik' => '<p>Günlük hayatta uygulanabilir BDT araçları pratik edilir. Kontenjan sınırlıdır.</p>',
                'kapak' => $this->downloadImage('uploads/egitim/demo_psk_egitim.jpg', $img['egitim']),
                'tip' => 'yuz_yuze',
                'baslangic_at' => now()->addWeeks(3)->setTime(18, 30),
                'bitis_at' => now()->addWeeks(3)->setTime(20, 30),
                'mekan' => 'Elif Kara Psikoloji — Çankaya',
                'fiyat' => 450,
                'kontenjan' => 16,
                'basvuru_acik_mi' => true,
                'basvuru_bitis_at' => now()->addWeeks(2),
                'durum' => 'yayinda',
                'sira' => 1,
                'meta_baslik' => 'Kaygıyla baş etme atölyesi',
            ]
        );

        $stats = $this->seedPanelDemoData($doktor);

        $this->command?->info('✓ Psikolog demo seed tamam.');
        $this->command?->info('  Paket : '.$paket->ad.' (id='.$paket->id.')');
        $this->command?->info('  Hekim : '.self::EMAIL.' / '.self::SIFRE);
        $this->command?->info('  Panel : hasta='.$stats['hasta'].' randevu='.$stats['randevu']
            .' gelir='.$stats['odeme'].' gider='.$stats['gider'].' yorum='.$stats['yorum']);
        $this->command?->info('  API   : RANDEVU_API_KEY='.self::API_KEY);
        $this->command?->info('           RANDEVU_API_SECRET='.self::API_SECRET);
    }

    /**
     * @return array{hasta:int,randevu:int,odeme:int,gider:int,yorum:int}
     */
    protected function seedPanelDemoData(Doktor $doktor): array
    {
        $hizmetler = Hizmet::query()->where('doktor_id', $doktor->id)->where('aktif_mi', true)->orderBy('id')->get();
        if ($hizmetler->isEmpty()) {
            return ['hasta' => 0, 'randevu' => 0, 'odeme' => 0, 'gider' => 0, 'yorum' => 0];
        }

        Odeme::query()->where('doktor_id', $doktor->id)->forceDelete();
        Gider::query()->where('doktor_id', $doktor->id)->forceDelete();
        Yorum::query()->where('doktor_id', $doktor->id)->forceDelete();
        Randevu::query()->where('doktor_id', $doktor->id)->forceDelete();

        $hastaDefs = [
            ['ad' => 'Ayşe', 'soyad' => 'Demir', 'e_posta' => 'demo.psk.hasta1@randevuajandam.com', 'telefon' => '05331110001'],
            ['ad' => 'Mert', 'soyad' => 'Yılmaz', 'e_posta' => 'demo.psk.hasta2@randevuajandam.com', 'telefon' => '05331110002'],
            ['ad' => 'Selin', 'soyad' => 'Kaya', 'e_posta' => 'demo.psk.hasta3@randevuajandam.com', 'telefon' => '05331110003'],
            ['ad' => 'Burak', 'soyad' => 'Şahin', 'e_posta' => 'demo.psk.hasta4@randevuajandam.com', 'telefon' => '05331110004'],
            ['ad' => 'Deniz', 'soyad' => 'Acar', 'e_posta' => 'demo.psk.hasta5@randevuajandam.com', 'telefon' => '05331110005'],
            ['ad' => 'Gizem', 'soyad' => 'Çetin', 'e_posta' => 'demo.psk.hasta6@randevuajandam.com', 'telefon' => '05331110006'],
            ['ad' => 'Emre', 'soyad' => 'Koç', 'e_posta' => 'demo.psk.hasta7@randevuajandam.com', 'telefon' => '05331110007'],
            ['ad' => 'İrem', 'soyad' => 'Arslan', 'e_posta' => 'demo.psk.hasta8@randevuajandam.com', 'telefon' => '05331110008'],
        ];

        $hastalar = collect();
        foreach ($hastaDefs as $h) {
            $hasta = Hasta::withTrashed()->where('e_posta', $h['e_posta'])->first();
            $payload = [
                'ad' => $h['ad'],
                'soyad' => $h['soyad'],
                'telefon' => $h['telefon'],
                'sifre' => 'DemoHasta2026!',
                'aktif_mi' => true,
            ];
            if ($hasta) {
                if ($hasta->trashed()) {
                    $hasta->restore();
                }
                $hasta->fill($payload)->save();
            } else {
                $hasta = Hasta::query()->create(array_merge(['e_posta' => $h['e_posta']], $payload));
            }
            $hastalar->push($hasta);
        }

        $gelirSeans = FinansKategori::query()->firstOrCreate(
            ['doktor_id' => $doktor->id, 'ad' => 'Seans geliri', 'tur' => 'gelir'],
            ['renk' => '#10b981', 'aktif' => true]
        );
        $gelirOnline = FinansKategori::query()->firstOrCreate(
            ['doktor_id' => $doktor->id, 'ad' => 'Online seans', 'tur' => 'gelir'],
            ['renk' => '#3b82f6', 'aktif' => true]
        );
        $gelirAtolye = FinansKategori::query()->firstOrCreate(
            ['doktor_id' => $doktor->id, 'ad' => 'Atölye', 'tur' => 'gelir'],
            ['renk' => '#8b5cf6', 'aktif' => true]
        );
        $giderKira = FinansKategori::query()->firstOrCreate(
            ['doktor_id' => $doktor->id, 'ad' => 'Ofis kira', 'tur' => 'gider'],
            ['renk' => '#ef4444', 'aktif' => true]
        );
        $giderDiger = FinansKategori::query()->firstOrCreate(
            ['doktor_id' => $doktor->id, 'ad' => 'Ofis gideri', 'tur' => 'gider'],
            ['renk' => '#64748b', 'aktif' => true]
        );

        $yontemler = ['nakit', 'havale', 'kredi_karti', 'online'];
        $saatler = ['10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
        $randevuSay = 0;
        $odemeSay = 0;

        for ($i = 0; $i < 16; $i++) {
            $gun = Carbon::today()->subDays(3 + ($i * 2));
            if ($gun->isWeekend()) {
                $gun->subDays(2);
            }
            $hasta = $hastalar[$i % $hastalar->count()];
            $hizmet = $hizmetler[$i % $hizmetler->count()];
            $saat = $saatler[$i % count($saatler)];
            $online = str_contains(mb_strtolower((string) $hizmet->ad), 'online') || $i % 4 === 0;
            $fiyat = (float) ($hizmet->fiyat ?: 1800);

            $randevu = null;
            Randevu::withoutEvents(function () use ($doktor, $hizmet, $hasta, $gun, $saat, $online, $i, &$randevu) {
                $randevu = Randevu::query()->create([
                    'doktor_id' => $doktor->id,
                    'hizmet_id' => $hizmet->id,
                    'hasta_id' => $hasta->id,
                    'ad' => $hasta->ad,
                    'soyad' => $hasta->soyad,
                    'telefon' => $hasta->telefon,
                    'e_posta' => $hasta->e_posta,
                    'tarih' => $gun->toDateString(),
                    'saat' => $saat,
                    'not' => 'Düzenli seans #'.($i + 1),
                    'durum' => 'tamamlandi',
                    'gorusme_tipi' => $online ? 'online' : 'yuz_yuze',
                ]);
            });
            $randevuSay++;

            Odeme::query()->create([
                'doktor_id' => $doktor->id,
                'randevu_id' => $randevu->id,
                'hasta_id' => $hasta->id,
                'hizmet_id' => $hizmet->id,
                'finans_kategori_id' => $online ? $gelirOnline->id : $gelirSeans->id,
                'tutar' => $fiyat,
                'odenen_tutar' => $fiyat,
                'odeme_yontemi' => $yontemler[$i % count($yontemler)],
                'durum' => 'odendi',
                'aciklama' => $hizmet->ad.' — '.$hasta->ad.' '.$hasta->soyad,
                'odeme_tarihi' => $gun->toDateString(),
            ]);
            $odemeSay++;
        }

        Odeme::query()->create([
            'doktor_id' => $doktor->id,
            'finans_kategori_id' => $gelirAtolye->id,
            'tutar' => 3600,
            'odenen_tutar' => 3600,
            'odeme_yontemi' => 'havale',
            'durum' => 'odendi',
            'aciklama' => 'Kaygı atölyesi katılım (demo)',
            'odeme_tarihi' => Carbon::today()->subDays(8)->toDateString(),
        ]);
        $odemeSay++;

        $gelecek = [
            ['offset' => 0, 'saat' => '11:00', 'durum' => 'onaylandi', 'not' => 'Bugün bireysel seans'],
            ['offset' => 0, 'saat' => '15:00', 'durum' => 'onaylandi', 'not' => 'Bugün online seans'],
            ['offset' => 0, 'saat' => '17:00', 'durum' => 'beklemede', 'not' => 'Onay bekleyen ilk görüşme'],
            ['offset' => 1, 'saat' => '10:00', 'durum' => 'onaylandi', 'not' => 'Yarın sabah'],
            ['offset' => 1, 'saat' => '16:00', 'durum' => 'onaylandi', 'not' => 'Yarın çift terapisi'],
            ['offset' => 2, 'saat' => '14:00', 'durum' => 'beklemede', 'not' => 'Yeni danışan talebi'],
            ['offset' => 4, 'saat' => '11:00', 'durum' => 'onaylandi', 'not' => 'Ergen seansı'],
            ['offset' => 7, 'saat' => '18:00', 'durum' => 'onaylandi', 'not' => 'Gelecek hafta akşam'],
            ['offset' => -1, 'saat' => '12:00', 'durum' => 'iptal', 'not' => 'Danışan iptal etti'],
        ];

        foreach ($gelecek as $gi => $g) {
            $gun = Carbon::today()->addDays($g['offset']);
            if ($gun->isWeekend()) {
                $gun->addDays($gun->isSaturday() ? 2 : 1);
            }
            $hasta = $hastalar[($gi + 2) % $hastalar->count()];
            $hizmet = $hizmetler[$gi % $hizmetler->count()];
            $online = str_contains(mb_strtolower($g['not']), 'online');

            Randevu::withoutEvents(function () use ($doktor, $hizmet, $hasta, $gun, $g, $online, &$randevuSay) {
                Randevu::query()->create([
                    'doktor_id' => $doktor->id,
                    'hizmet_id' => $hizmet->id,
                    'hasta_id' => $hasta->id,
                    'ad' => $hasta->ad,
                    'soyad' => $hasta->soyad,
                    'telefon' => $hasta->telefon,
                    'e_posta' => $hasta->e_posta,
                    'tarih' => $gun->toDateString(),
                    'saat' => $g['saat'],
                    'not' => $g['not'],
                    'durum' => $g['durum'],
                    'gorusme_tipi' => $online ? 'online' : 'yuz_yuze',
                ]);
                $randevuSay++;
            });
        }

        $giderler = [
            ['baslik' => 'Ofis kira', 'tutar' => 22000, 'ay' => 0, 'kat' => $giderKira],
            ['baslik' => 'Ofis kira', 'tutar' => 22000, 'ay' => 1, 'kat' => $giderKira],
            ['baslik' => 'Ofis kira', 'tutar' => 21000, 'ay' => 2, 'kat' => $giderKira],
            ['baslik' => 'Elektrik + internet', 'tutar' => 2800, 'ay' => 0, 'kat' => $giderDiger],
            ['baslik' => 'Süpervizyon', 'tutar' => 4000, 'ay' => 0, 'kat' => $giderDiger],
            ['baslik' => 'Yazılım abonelikleri', 'tutar' => 890, 'ay' => 0, 'kat' => $giderDiger],
            ['baslik' => 'Kırtasiye / test formu', 'tutar' => 650, 'ay' => 1, 'kat' => $giderDiger],
        ];
        $giderSay = 0;
        foreach ($giderler as $g) {
            $tarih = Carbon::now()->subMonths($g['ay'])->startOfMonth()->addDays(3);
            Gider::query()->create([
                'doktor_id' => $doktor->id,
                'finans_kategori_id' => $g['kat']->id,
                'kategori' => str_contains(mb_strtolower($g['baslik']), 'kira') ? 'kira' : 'diger',
                'baslik' => $g['baslik'],
                'tutar' => $g['tutar'],
                'tarih' => $tarih->toDateString(),
                'aciklama' => 'Demo finans — '.$g['baslik'],
            ]);
            $giderSay++;
        }

        $yorumlar = [
            ['puan' => 5, 'yorum' => 'İlk seanstan itibaren kendimi duyulmuş hissettim.', 'yanit' => 'Güvenli bir alan oluşturmak benim için önemli. Teşekkürler.'],
            ['puan' => 5, 'yorum' => 'Online seanslar yüz yüze kadar düzenliydi.', 'yanit' => 'Süreklilik kaygıyı yumuşatır; devam edelim.'],
            ['puan' => 4, 'yorum' => 'Randevu hatırlatmaları çok işime yaradı.', 'yanit' => null],
            ['puan' => 5, 'yorum' => 'Çift seansında iletişimimiz netleşti.', 'yanit' => 'Emeğinize sağlık.'],
        ];
        $yorumSay = 0;
        $tamamlanmis = Randevu::query()
            ->where('doktor_id', $doktor->id)
            ->where('durum', 'tamamlandi')
            ->orderBy('id')
            ->take(count($yorumlar))
            ->get();

        foreach ($yorumlar as $yi => $y) {
            $r = $tamamlanmis[$yi] ?? null;
            $hasta = $hastalar[$yi % $hastalar->count()];
            Yorum::query()->create([
                'hasta_id' => $r?->hasta_id ?? $hasta->id,
                'doktor_id' => $doktor->id,
                'randevu_id' => $r?->id,
                'puan' => $y['puan'],
                'yorum' => $y['yorum'],
                'doktor_yaniti' => $y['yanit'],
                'onay_durumu' => 'onaylandi',
            ]);
            $yorumSay++;
        }

        return [
            'hasta' => $hastalar->count(),
            'randevu' => $randevuSay,
            'odeme' => $odemeSay,
            'gider' => $giderSay,
            'yorum' => $yorumSay,
        ];
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
            $response = Http::timeout(40)
                ->withHeaders([
                    'User-Agent' => 'RandevuAjandamSeeder/1.0',
                    'Accept' => 'image/*',
                ])
                ->get($url);

            if ($response->successful() && strlen($response->body()) > 1000) {
                File::put($full, $response->body());

                return $relativePath;
            }
        } catch (\Throwable) {
            // yerelde logo yedek
        }

        if (! is_file($full) && is_file(public_path('assets/images/logo.png'))) {
            @copy(public_path('assets/images/logo.png'), $full);
        }

        return $relativePath;
    }
}
