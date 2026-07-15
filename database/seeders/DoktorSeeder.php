<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\DoktorCalismaSaati;
use App\Models\DoktorGaleri;
use App\Models\Hizmet;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Paket;
use App\Models\RandevuAyari;
use App\Models\Klinik;
use App\Models\KlinikDavetiye;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoktorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Temizleme işlemleri (Pivot tablo ve ana tablo)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('doktor_brans')->truncate();
        DB::table('bloglar')->truncate();
        DB::table('hizmetler')->truncate();
        DB::table('randevu_ayarlari')->truncate();
        DB::table('doktor_calisma_saatleri')->truncate();
        DB::table('doktor_izinleri')->truncate();
        DB::table('randevular')->truncate();
        DB::table('doktor_galerileri')->truncate();
        DB::table('klinikler')->truncate();
        DB::table('klinik_davetiyeleri')->truncate();
        Doktor::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Paketleri çekelim
        $bireyselBaslangic = Paket::where('tur', 'bireysel')->where('ad', 'like', '%Başlangıç%')->first();
        $bireyselPlus = Paket::where('tur', 'bireysel')->where('ad', 'like', '%Profesyonel%')->first();
        $bireyselElite = Paket::where('tur', 'bireysel')->where('ad', 'like', '%VIP%')->first();

        // İl ve İlçeleri bulalım
        $istanbul = Il::where('ad', 'İstanbul')->first();
        $izmir = Il::where('ad', 'İzmir')->first();
        $ankara = Il::where('ad', 'Ankara')->first();

        $sisli = Ilce::where('ad', 'Şişli')->first();
        $konak = Ilce::where('ad', 'Konak')->first();
        $cankaya = Ilce::where('ad', 'Çankaya')->first();
        $besiktas = Ilce::where('ad', 'Beşiktaş')->first();
        $yenimahalle = Ilce::where('ad', 'Yenimahalle')->first();

        // 1. Doktor: Ahmet Yılmaz
        $doktor1 = Doktor::create([
            'ad_soyad' => 'Ahmet Yılmaz',
            'e_posta' => 'ahmet@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '0 (555) 123 45 67',
            'il_id' => $istanbul?->id,
            'ilce_id' => $sisli?->id,
            'tur' => 'bireysel',
            'klinik_adi' => null,
            'paket_id' => $bireyselPlus ? $bireyselPlus->id : null,
            'odeme_periyodu' => 'yillik',
            'uyelik_baslangic' => now(),
            'uyelik_bitis' => now()->addYear(),
            'aktif_mi' => true,
            'unvan' => 'Prof. Dr.',
            'uzmanlik_alani' => 'Kardiyoloji, Dahiliye (İç Hastalıkları)',
            'mezuniyet' => [
                'Hacettepe Üniversitesi Tıp Fakültesi (2000)',
                'Kardiyoloji İhtisası - Ankara Üniversitesi (2005)',
            ],
            'biyografi' => '15 yılı aşkın süredir kardiyoloji alanında uzman hekim olarak hizmet vermektedir. Kalp sağlığı ve damar hastalıkları alanında çeşitli akademik çalışmaları mevcuttur.',
            'enlem' => 41.0602,
            'boylam' => 28.9878,
            'adres' => 'Halaskargazi Cd. No:120, Şişli/İstanbul',
        ]);

        $bransKardiyoloji = Brans::where('ad', 'Kardiyoloji')->first();
        $bransDahiliye = Brans::where('ad', 'Dahiliye (İç Hastalıkları)')->first();
        if ($bransKardiyoloji) {
            $doktor1->branslar()->attach($bransKardiyoloji->id);
        }
        if ($bransDahiliye) {
            $doktor1->branslar()->attach($bransDahiliye->id);
        }

        // 2. Doktor: Elif Kaya
        $doktor2 = Doktor::create([
            'ad_soyad' => 'Elif Kaya',
            'e_posta' => 'elif@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '0 (532) 987 65 43',
            'il_id' => $izmir?->id,
            'ilce_id' => $konak?->id,
            'tur' => 'bireysel',
            'klinik_adi' => null,
            'paket_id' => $bireyselPlus ? $bireyselPlus->id : null,
            'odeme_periyodu' => 'aylik',
            'uyelik_baslangic' => now(),
            'uyelik_bitis' => now()->addMonth(),
            'aktif_mi' => true,
            'unvan' => 'Op. Dr.',
            'uzmanlik_alani' => 'Kadın Hastalıkları ve Doğum, Genel Cerrahi',
            'mezuniyet' => [
                'Ege Üniversitesi Tıp Fakültesi (2005)',
                'Uzmanlık - Dokuz Eylül Üniversitesi (2010)',
            ],
            'biyografi' => 'Kadın sağlığı, doğum, gebelik takibi ve jinekolojik cerrahi alanlarında hizmet vermektedir. Kliniğinde modern tıbbi cihazlar ile hasta kabulü yapmaktadır.',
            'enlem' => 38.4189,
            'boylam' => 27.1287,
            'adres' => 'Atatürk Cd. No:82, Konak/İzmir',
        ]);

        $bransKadinDogum = Brans::where('ad', 'Kadın Hastalıkları ve Doğum')->first();
        $bransGenelCerrahi = Brans::where('ad', 'Genel Cerrahi')->first();
        if ($bransKadinDogum) {
            $doktor2->branslar()->attach($bransKadinDogum->id);
        }
        if ($bransGenelCerrahi) {
            $doktor2->branslar()->attach($bransGenelCerrahi->id);
        }

        // 3. Doktor: Can Demir
        $doktor3 = Doktor::create([
            'ad_soyad' => 'Can Demir',
            'e_posta' => 'can@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '0 (544) 111 22 33',
            'il_id' => $ankara?->id,
            'ilce_id' => $cankaya?->id,
            'tur' => 'bireysel',
            'klinik_adi' => null,
            'paket_id' => $bireyselBaslangic ? $bireyselBaslangic->id : null,
            'odeme_periyodu' => 'yillik',
            'uyelik_baslangic' => now(),
            'uyelik_bitis' => now()->addYear(),
            'aktif_mi' => true,
            'unvan' => 'Uzm. Dr.',
            'uzmanlik_alani' => 'Çocuk Sağlığı ve Hastalıkları, Pedodonti (Çocuk Diş Hekimliği)',
            'mezuniyet' => [
                'İstanbul Üniversitesi Cerrahpaşa Tıp Fakültesi (2010)',
            ],
            'biyografi' => 'Çocuk sağlığı ve gelişimi takibi, çocukluk çağı hastalıkları ve yenidoğan takibi konularında hizmet vermektedir.',
            'enlem' => 39.9033,
            'boylam' => 32.8596,
            'adres' => 'Tunalı Hilmi Cd. No:95, Çankaya/Ankara',
        ]);

        $bransCocukSagligi = Brans::where('ad', 'Çocuk Sağlığı ve Hastalıkları')->first();
        $bransPedodonti = Brans::where('ad', 'Pedodonti (Çocuk Diş Hekimliği)')->first();
        if ($bransCocukSagligi) {
            $doktor3->branslar()->attach($bransCocukSagligi->id);
        }
        if ($bransPedodonti) {
            $doktor3->branslar()->attach($bransPedodonti->id);
        }

        // 4. Doktor: Merve Aslan
        $doktor4 = Doktor::create([
            'ad_soyad' => 'Merve Aslan',
            'e_posta' => 'merve@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '0 (505) 555 44 33',
            'il_id' => $istanbul?->id,
            'ilce_id' => $besiktas?->id,
            'tur' => 'bireysel',
            'klinik_adi' => null,
            'paket_id' => $bireyselElite ? $bireyselElite->id : null,
            'odeme_periyodu' => 'aylik',
            'uyelik_baslangic' => now(),
            'uyelik_bitis' => now()->addMonth(),
            'aktif_mi' => true,
            'unvan' => 'Klinik Psikolog',
            'uzmanlik_alani' => 'Psikoloji',
            'mezuniyet' => [
                'Boğaziçi Üniversitesi Psikoloji (2012)',
                'Klinik Psikoloji Yüksek Lisansı - Bilgi Üniversitesi (2015)',
            ],
            'biyografi' => 'Yetişkin terapisi, aile danışmanlığı, bilişsel davranışçı terapi and EMDR terapisi uygulamaktadır. Bireysel psikolojik danışmanlık hizmeti sunmaktadır.',
            'enlem' => 41.0428,
            'boylam' => 29.0075,
            'adres' => 'Barbaros Blv. No:44, Beşiktaş/İstanbul',
        ]);

        $bransPsikoloji = Brans::where('ad', 'Psikoloji')->first();
        if ($bransPsikoloji) {
            $doktor4->branslar()->attach($bransPsikoloji->id);
        }

        // 5. Doktor: Melis Şen
        $doktor5 = Doktor::create([
            'ad_soyad' => 'Melis Şen',
            'e_posta' => 'melis@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '0 (533) 222 33 44',
            'il_id' => $ankara?->id,
            'ilce_id' => $yenimahalle?->id,
            'tur' => 'bireysel',
            'klinik_adi' => null,
            'paket_id' => $bireyselPlus ? $bireyselPlus->id : null,
            'odeme_periyodu' => 'yillik',
            'uyelik_baslangic' => now(),
            'uyelik_bitis' => now()->addYear(),
            'aktif_mi' => true,
            'unvan' => 'Diyetisyen (Dyt.)',
            'uzmanlik_alani' => 'Diyetisyen (Beslenme ve Diyetetik)',
            'mezuniyet' => [
                'Hacettepe Üniversitesi Beslenme ve Diyetetik (2014)',
            ],
            'biyografi' => 'Kişiye özel beslenme programları, kilo yönetimi, hastalıklarda beslenme tedavisi ve sporcu beslenmesi alanında çalışmaktadır.',
            'enlem' => 39.9688,
            'boylam' => 32.7981,
            'adres' => 'Bağdat Cd. No:150, Yenimahalle/Ankara',
        ]);

        $bransDiyetisyen = Brans::where('ad', 'Diyetisyen (Beslenme ve Diyetetik)')->first();
        if ($bransDiyetisyen) {
            $doktor5->branslar()->attach($bransDiyetisyen->id);
        }

        // Seeder images → storage/app/public (served via public/storage symlink)
        $blogDiskDir = storage_path('app/public/uploads/blog');
        if (! is_dir($blogDiskDir)) {
            mkdir($blogDiskDir, 0777, true);
        }

        // Generate dummy images if GD exists, otherwise fallback to copying logo
        $dummyImages = [];
        for ($i = 1; $i <= 3; $i++) {
            $imagePath = 'uploads/blog/seeder_blog_'.$i.'.jpg';
            $fullPath = storage_path('app/public/'.$imagePath);
            if (! file_exists($fullPath)) {
                $created = false;
                if (function_exists('imagecreate')) {
                    $im = @imagecreate(800, 500);
                    if ($im) {
                        // Generate a nice warm color palette
                        $colors = [
                            [201, 106, 43], // Copper (#C96A2B)
                            [31, 41, 55],   // Dark Gray (#1F2937)
                            [231, 181, 138], // Light Copper (#E7B58A)
                        ];
                        $bg = imagecolorallocate($im, $colors[$i - 1][0], $colors[$i - 1][1], $colors[$i - 1][2]);
                        $textColor = ($i == 3) ? imagecolorallocate($im, 31, 41, 55) : imagecolorallocate($im, 255, 255, 255);
                        imagestring($im, 5, 250, 240, 'Randevu Ajandam Blog Gorseli '.$i, $textColor);
                        imagejpeg($im, $fullPath);
                        imagedestroy($im);
                        $created = true;
                    }
                }
                if (! $created && file_exists(public_path('assets/images/logo.png'))) {
                    copy(public_path('assets/images/logo.png'), $fullPath);
                }
            }
            // Legacy: also keep copy under public/uploads for old links
            $legacy = public_path($imagePath);
            if (file_exists($fullPath) && ! file_exists($legacy)) {
                $legacyDir = dirname($legacy);
                if (! is_dir($legacyDir)) {
                    mkdir($legacyDir, 0777, true);
                }
                @copy($fullPath, $legacy);
            }
            $dummyImages[] = $imagePath;
        }

        $hizmetDiskDir = storage_path('app/public/uploads/hizmet');
        if (! is_dir($hizmetDiskDir)) {
            mkdir($hizmetDiskDir, 0777, true);
        }

        // Generate dummy service images
        $dummyHizmetImages = [];
        for ($i = 1; $i <= 3; $i++) {
            $imagePath = 'uploads/hizmet/seeder_hizmet_'.$i.'.jpg';
            $fullPath = storage_path('app/public/'.$imagePath);
            if (! file_exists($fullPath)) {
                $created = false;
                if (function_exists('imagecreate')) {
                    $im = @imagecreate(400, 400);
                    if ($im) {
                        $colors = [
                            [231, 181, 138], // Light Copper (#E7B58A)
                            [201, 106, 43],  // Copper (#C96A2B)
                            [31, 41, 55],    // Dark Gray (#1F2937)
                        ];
                        $bg = imagecolorallocate($im, $colors[$i - 1][0], $colors[$i - 1][1], $colors[$i - 1][2]);
                        $textColor = ($i == 1) ? imagecolorallocate($im, 31, 41, 55) : imagecolorallocate($im, 255, 255, 255);
                        imagestring($im, 4, 100, 190, 'Hizmet Gorseli '.$i, $textColor);
                        imagejpeg($im, $fullPath);
                        imagedestroy($im);
                        $created = true;
                    }
                }
                if (! $created && file_exists(public_path('assets/images/logo.png'))) {
                    copy(public_path('assets/images/logo.png'), $fullPath);
                }
            }
            $legacy = public_path($imagePath);
            if (file_exists($fullPath) && ! file_exists($legacy)) {
                $legacyDir = dirname($legacy);
                if (! is_dir($legacyDir)) {
                    mkdir($legacyDir, 0777, true);
                }
                @copy($fullPath, $legacy);
            }
            $dummyHizmetImages[] = $imagePath;
        }

        // 1. Doktor Ahmet Yılmaz Blogları
        Blog::create([
            'doktor_id' => $doktor1->id,
            'baslik' => 'Sağlıklı Bir Kalp İçin 5 Altın Öneri',
            'icerik' => '<h2>Kalp Sağlığınızı Korumanın Yolları</h2><p>Kalp ve damar hastalıkları, dünya genelinde en sık karşılaşılan sağlık sorunlarının başında gelmektedir. Ancak günlük hayatımızda yapacağımız küçük değişikliklerle kalp sağlığımızı korumak ve riskleri azaltmak mümkündür.</p><h3>1. Düzenli Egzersiz Yapın</h3><p>Haftada en az 150 dakika orta şiddette kardiyo egzersizleri (yürüyüş, bisiklet, yüzme) kalbinizi güçlendirir.</p><h3>2. Akdeniz Tipi Beslenin</h3><p>Zeytinyağı, taze sebze-meyve, tam tahıllar ve balık ağırlıklı beslenme kalp dostudur.</p><h3>3. Strese Karşı Önlem Alın</h3><p>Kronik stres tansiyonu yükseltir ve kalp krizini tetikleyebilir. Meditasyon veya hobilerle stresinizi yönetin.</p><h3>4. Düzenli Kontrolleri İhmal Etmeyin</h3><p>Yılda en az bir kez kardiyolojik taramadan geçmek erken teşhis için hayati önem taşır.</p>',
            'resim' => isset($dummyImages[0]) ? $dummyImages[0] : null,
            'meta_baslik' => 'Sağlıklı Bir Kalp İçin Öneriler - Prof. Dr. Ahmet Yılmaz',
            'meta_aciklama' => 'Kalp sağlığınızı korumak için uygulayabileceğiniz 5 basit ama etkili altın öneri.',
            'meta_anahtar_kelimeler' => 'kalp sagligi, saglikli yasam, egzersiz, beslenme',
            'aktif_mi' => true,
            'okunma_sayisi' => 145,
        ]);

        Blog::create([
            'doktor_id' => $doktor1->id,
            'baslik' => 'Akdeniz Tipi Beslenmenin Kalp Sağlığına Etkileri',
            'icerik' => '<h2>Akdeniz Diyeti ve Kalbimiz</h2><p>Akdeniz tipi beslenme modeli, sadece bir diyet değil, aynı zamanda sağlıklı bir yaşam tarzıdır. Araştırmalar, bu beslenme şeklinin kalp krizi ve felç riskini ciddi oranda azalttığını göstermektedir.</p><h3>Neler Tüketilmeli?</h3><ul><li>Bol miktarda taze sebze ve meyve</li><li>Sağlıklı yağ kaynağı olarak soğuk sıkım sızma zeytinyağı</li><li>Haftada en az 2 gün omega-3 yönünden zengin balıklar</li><li>Ceviz, fındık gibi çiğ kuruyemişler</li></ul>',
            'resim' => isset($dummyImages[1]) ? $dummyImages[1] : null,
            'meta_baslik' => 'Akdeniz Diyeti ve Kalp Sağlığı - Prof. Dr. Ahmet Yılmaz',
            'meta_aciklama' => 'Akdeniz tipi beslenmenin kalp ve damar sağlığı üzerindeki faydaları ve diyet önerileri.',
            'meta_anahtar_kelimeler' => 'akdeniz diyeti, kalp sagligi, omega 3, zeytinyagi',
            'aktif_mi' => true,
            'okunma_sayisi' => 88,
        ]);

        // 2. Doktor Elif Kaya Blogları
        Blog::create([
            'doktor_id' => $doktor2->id,
            'baslik' => 'Gebelik Döneminde Beslenme ve Egzersiz Rehberi',
            'icerik' => '<h2>Gebelik Sürecinde Sağlıklı Yaşam</h2><p>Gebelik, bir kadının hayatındaki en mucizevi ve hassas dönemlerden biridir. Bu süreçte hem annenin hem de bebeğin sağlığı için doğru beslenme ve hafif egzersizler büyük önem taşır.</p><h3>Gebelikte Beslenme İlkeleri</h3><p>Gebelikte "iki kişilik yemek" yerine "kaliteli ve dengeli beslenme" ilkesi benimsenmelidir. Kalsiyum, demir, folik asit ve protein alımına özen gösterilmelidir.</p><h3>Gebelikte Güvenli Egzersizler</h3><p>Doktorunuz aksini belirtmediği sürece, düzenli yürüyüşler ve gebelik yogası doğumu kolaylaştırır ve sırt ağrılarını azaltır.</p>',
            'resim' => isset($dummyImages[2]) ? $dummyImages[2] : null,
            'meta_baslik' => 'Gebelikte Beslenme ve Egzersiz Rehberi - Op. Dr. Elif Kaya',
            'meta_aciklama' => 'Hamilelik döneminde anne adaylarının uygulaması gereken beslenme önerileri ve güvenli egzersizler.',
            'meta_anahtar_kelimeler' => 'gebelik, hamilelik, beslenme, gebelik yogasi',
            'aktif_mi' => true,
            'okunma_sayisi' => 210,
        ]);

        // 3. Doktor Can Demir Blogları
        Blog::create([
            'doktor_id' => $doktor3->id,
            'baslik' => 'Bebeklerde Ek Gıdaya Geçiş Dönemi',
            'icerik' => '<h2>Ek Gıdaya Ne Zaman ve Nasıl Başlanmalı?</h2><p>Bebeklerde ilk 6 ay sadece anne sütü yeterlidir. 6. aydan itibaren bebeklerin artan enerji ve besin ögesi ihtiyaçlarını karşılamak üzere ek gıdalara başlanması gerekir.</p><h3>İlk Başlangıç Besinleri</h3><p>İlk olarak mevsim sebzelerinin püreleri, ev yapımı yoğurt ve şeftali, elma gibi meyvelerin püreleri tercih edilmelidir. Her yeni besin 3 gün kuralı ile denenmelidir.</p>',
            'resim' => isset($dummyImages[0]) ? $dummyImages[0] : null,
            'meta_baslik' => 'Bebeklerde Ek Gıda Rehberi - Uzm. Dr. Can Demir',
            'meta_aciklama' => '6. aydan sonra bebeklerde ek gıdaya geçiş süreci, ilk verilecek besinler ve dikkat edilmesi gerekenler.',
            'meta_anahtar_kelimeler' => 'bebek sagligi, ek gida, 3 gun kurali, bebek beslenmesi',
            'aktif_mi' => true,
            'okunma_sayisi' => 195,
        ]);

        // 4. Doktor Merve Aslan Blogları
        Blog::create([
            'doktor_id' => $doktor4->id,
            'baslik' => 'Kaygı ve Stresle Baş Etme Yöntemleri',
            'icerik' => '<h2>Zor Zamanlarda Psikolojik Sağlığımızı Korumak</h2><p>Günümüzün yoğun temposunda stres ve kaygı (anksiyete) hayatımızın kaçınılmaz bir parçası haline gelebilmektedir. Ancak bu durumla baş etmeyi öğrenerek yaşam kalitemizi artırabiliriz.</p><h3>Nefes ve Gevşeme Egzersizleri</h3><p>Kaygı anında diyafram nefesi almak vücuttaki stres yanıtını yavaşlatır. 4-7-8 tekniği ile nefesinizi kontrol altına alın.</p><h3>Bilişsel Yeniden Yapılandırma</h3><p>Kaygıyı tetikleyen felaket senaryoları ve olumsuz düşünceleri fark edip, onları daha rasyonel ve yapıcı düşüncelerle değiştirmeye çalışın.</p>',
            'resim' => isset($dummyImages[1]) ? $dummyImages[1] : null,
            'meta_baslik' => 'Kaygı ve Stresle Baş Etme - Klinik Psikolog Merve Aslan',
            'meta_aciklama' => 'Anksiyete ve stres anında kendinizi sakinleştirmek için uygulayabileceğiniz psikolojik yöntemler.',
            'meta_anahtar_kelimeler' => 'kaygi, stres yonetimi, anksiyete, psikoloji, nefes egzersizi',
            'aktif_mi' => true,
            'okunma_sayisi' => 312,
        ]);

        // 5. Doktor Melis Şen Blogları
        Blog::create([
            'doktor_id' => $doktor5->id,
            'baslik' => 'Kalıcı ve Sağlıklı Kilo Vermenin Sırları',
            'icerik' => '<h2>Popüler Diyetler Yerine Sürdürülebilir Beslenme</h2><p>Şok diyetler kısa sürede kilo verdirse de kas kaybına yol açar ve verilen kilolar hızla geri alınır. Sağlıklı ve kalıcı kilo kaybı, yaşam tarzı değişikliği ile mümkündür.</p><h3>Porsiyon Kontrolü</h3><p>Yiyecekleri yasaklamak yerine porsiyonlarınızı küçültün. Tabağınızın yarısını sebzelerle doldurun.</p><h3>Yeterli Su Tüketimi</h3><p>Bazen susuzluk hissi açlıkla karıştırılabilir. Günde en az 2-2.5 litre su içtiğinizden emin olun.</p>',
            'resim' => isset($dummyImages[2]) ? $dummyImages[2] : null,
            'meta_baslik' => 'Kalıcı ve Sağlıklı Kilo Verme - Dyt. Melis Şen',
            'meta_aciklama' => 'Sağlıklı beslenme alışkanlıkları edinerek kalıcı ve ideal kilonuza ulaşmanın temel yolları.',
            'meta_anahtar_kelimeler' => 'kilo verme, diyet, saglikli beslenme, diyetisyen',
            'aktif_mi' => true,
            'okunma_sayisi' => 256,
        ]);

        // 1. Doktor Ahmet Yılmaz Hizmetleri
        Hizmet::create([
            'doktor_id' => $doktor1->id,
            'ad' => 'Kardiyoloji Muayenesi',
            'aciklama' => 'Detaylı kardiyolojik muayene, EKG çekimi ve doktor değerlendirmesi.',
            'resim' => isset($dummyHizmetImages[0]) ? $dummyHizmetImages[0] : null,
            'sure' => 30,
            'fiyat' => 1500.00,
            'aktif_mi' => true,
            'meta_baslik' => 'Kardiyoloji Muayenesi - Prof. Dr. Ahmet Yılmaz',
            'meta_aciklama' => 'Prof. Dr. Ahmet Yılmaz kliniğinde detaylı kardiyoloji muayenesi ve EKG check-up hizmeti.',
            'meta_anahtar_kelimeler' => 'kardiyoloji muayenesi, ekg, kalp kontrolü, ahmet yılmaz',
        ]);

        Hizmet::create([
            'doktor_id' => $doktor1->id,
            'ad' => 'Efor Testi (Treadmill)',
            'aciklama' => 'Kalp ritminin fiziksel aktivite altındaki durumunu izlemek için efor testi.',
            'resim' => isset($dummyHizmetImages[1]) ? $dummyHizmetImages[1] : null,
            'sure' => 45,
            'fiyat' => 2000.00,
            'aktif_mi' => true,
            'meta_baslik' => 'Efor Testi - Prof. Dr. Ahmet Yılmaz',
            'meta_aciklama' => 'Efor testi (Treadmill) uygulaması ile kalp ve damar sağlığının efor altındaki takibi.',
            'meta_anahtar_kelimeler' => 'efor testi, treadmill, kalp ritmi testi, ahmet yılmaz',
        ]);

        // 2. Doktor Elif Kaya Hizmetleri
        Hizmet::create([
            'doktor_id' => $doktor2->id,
            'ad' => 'Jinekolojik Muayene',
            'aciklama' => 'Rutin jinekolojik kontrol, muayene ve smear testi uygulamaları.',
            'resim' => isset($dummyHizmetImages[2]) ? $dummyHizmetImages[2] : null,
            'sure' => 20,
            'fiyat' => 1200.00,
            'aktif_mi' => true,
            'meta_baslik' => 'Jinekolojik Muayene - Op. Dr. Elif Kaya',
            'meta_aciklama' => 'Op. Dr. Elif Kaya Kadın Sağlığı kliniğinde jinekolojik kontrol ve rutin sağlık taramaları.',
            'meta_anahtar_kelimeler' => 'jinekolojik muayene, kadın sağlığı, smear testi, elif kaya',
        ]);

        Hizmet::create([
            'doktor_id' => $doktor2->id,
            'ad' => 'Gebelik Takibi ve Ultrasonografi',
            'aciklama' => 'Gebelik süreci boyunca anne ve bebek sağlığının ultrason ile yakından izlenmesi.',
            'resim' => isset($dummyHizmetImages[0]) ? $dummyHizmetImages[0] : null,
            'sure' => 40,
            'fiyat' => 1800.00,
            'aktif_mi' => true,
            'meta_baslik' => 'Gebelik Takibi ve Ultrason - Op. Dr. Elif Kaya',
            'meta_aciklama' => 'Gebelikte rutin hekim kontrolleri, fetal gelişim takibi ve ultrasonografi hizmeti.',
            'meta_anahtar_kelimeler' => 'gebelik takibi, ultrason, hamilelik kontrolü, elif kaya',
        ]);

        // 3. Doktor Can Demir Hizmetleri
        Hizmet::create([
            'doktor_id' => $doktor3->id,
            'ad' => 'Pediatrik Sağlık Kontrolü',
            'aciklama' => 'Yenidoğan ve çocukluk dönemi rutin sağlık kontrolleri, büyüme ve gelişim takibi.',
            'resim' => isset($dummyHizmetImages[1]) ? $dummyHizmetImages[1] : null,
            'sure' => 30,
            'fiyat' => 1000.00,
            'aktif_mi' => true,
            'meta_baslik' => 'Pediatrik Sağlık Kontrolü - Uzm. Dr. Can Demir',
            'meta_aciklama' => 'Çocuk sağlığı ve hastalıkları kontrolü, aşı takibi ve rutin pediatrik muayene.',
            'meta_anahtar_kelimeler' => 'pediatrik kontrol, çocuk sağlığı, bebek muayenesi, can demir',
        ]);

        // 4. Doktor Merve Aslan Hizmetleri
        Hizmet::create([
            'doktor_id' => $doktor4->id,
            'ad' => 'Bireysel Psikoterapi Seansı',
            'aciklama' => 'Yetişkinler için bireysel psikolojik danışmanlık ve psikoterapi seansları.',
            'resim' => isset($dummyHizmetImages[2]) ? $dummyHizmetImages[2] : null,
            'sure' => 50,
            'fiyat' => 1500.00,
            'aktif_mi' => true,
            'meta_baslik' => 'Bireysel Psikoterapi - Klinik Psikolog Merve Aslan',
            'meta_aciklama' => 'Bilişsel davranışçı terapi ve EMDR teknikleri ile yetişkin bireysel psikoterapi.',
            'meta_anahtar_kelimeler' => 'bireysel psikoterapi, psikolojik danışmanlık, terapi seansı, merve aslan',
        ]);

        // 5. Doktor Melis Şen Hizmetleri
        Hizmet::create([
            'doktor_id' => $doktor5->id,
            'ad' => 'İlk Beslenme Danışmanlığı',
            'aciklama' => 'Detaylı vücut analizi, beslenme alışkanlıkları sorgulaması ve ilk diyet programı hazırlığı.',
            'resim' => isset($dummyHizmetImages[0]) ? $dummyHizmetImages[0] : null,
            'sure' => 60,
            'fiyat' => 800.00,
            'aktif_mi' => true,
            'meta_baslik' => 'İlk Beslenme Danışmanlığı - Dyt. Melis Şen',
            'meta_aciklama' => 'Kişiye özel ilk beslenme programı, vücut analizi ve diyetisyen kontrolü.',
            'meta_anahtar_kelimeler' => 'beslenme danışmanlığı, diyetisyen kontrolü, vücut analizi, melis şen',
        ]);

        // Create public/uploads/galeri directory if it doesn't exist
        $galeriUploadsDir = public_path('uploads/galeri');
        if (! file_exists($galeriUploadsDir)) {
            mkdir($galeriUploadsDir, 0777, true);
        }

        // Generate dummy gallery images
        $dummyGaleriImages = [];
        for ($i = 1; $i <= 3; $i++) {
            $imagePath = 'uploads/galeri/seeder_galeri_'.$i.'.jpg';
            $fullPath = public_path($imagePath);
            if (! file_exists($fullPath)) {
                $created = false;
                if (function_exists('imagecreate')) {
                    $im = @imagecreate(600, 400);
                    if ($im) {
                        $colors = [
                            [31, 41, 55],    // Dark Gray (#1F2937)
                            [201, 106, 43],  // Copper (#C96A2B)
                            [231, 181, 138], // Light Copper (#E7B58A)
                        ];
                        $bg = imagecolorallocate($im, $colors[$i - 1][0], $colors[$i - 1][1], $colors[$i - 1][2]);
                        $textColor = ($i == 3) ? imagecolorallocate($im, 31, 41, 55) : imagecolorallocate($im, 255, 255, 255);
                        imagestring($im, 4, 150, 190, 'Muayenehane Galeri Fotografi '.$i, $textColor);
                        imagejpeg($im, $fullPath);
                        imagedestroy($im);
                        $created = true;
                    }
                }
                if (! $created && file_exists(public_path('assets/images/logo.png'))) {
                    copy(public_path('assets/images/logo.png'), $fullPath);
                }
            }
            $dummyGaleriImages[] = $imagePath;
        }

        // 6. Doktor: Canan Dağdeviren (Klinik Sahibi)
        $doktor6 = Doktor::create([
            'ad_soyad' => 'Canan Dağdeviren',
            'e_posta' => 'canan@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '0 (533) 111 22 33',
            'il_id' => $istanbul?->id,
            'ilce_id' => $sisli?->id,
            'tur' => 'klinik',
            'klinik_rolu' => 'sahip',
            'klinik_adi' => 'Şişli Sağlık Kliniği',
            'paket_id' => null,
            'aktif_mi' => true,
            'unvan' => 'Doç. Dr.',
            'uzmanlik_alani' => 'Dermatoloji (Cildiye)',
            'mezuniyet' => [
                'İstanbul Üniversitesi İstanbul Tıp Fakültesi (2008)',
                'Dermatoloji İhtisası - Cerrahpaşa Tıp Fakültesi (2013)',
            ],
            'biyografi' => 'Dermatoloji alanında uzmanlaşmış olan Doç. Dr. Canan Dağdeviren, Şişli Sağlık Kliniği kurucusudur.',
            'enlem' => 41.0610,
            'boylam' => 28.9890,
            'adres' => 'Halaskargazi Cd. No:140, Şişli/İstanbul',
        ]);
        $bransDermatoloji = Brans::where('ad', 'Dermatoloji (Cildiye)')->first();
        if ($bransDermatoloji) {
            $doktor6->branslar()->attach($bransDermatoloji->id);
        }

        // Klinik Profesyonel Paketini alalım
        $klinikPaket = Paket::where('tur', 'klinik')->where('ad', 'like', '%Profesyonel%')->first();

        // 6.1 Klinik Oluşturma
        $klinik = Klinik::create([
            'ad' => 'Şişli Sağlık Kliniği',
            'sahip_doktor_id' => $doktor6->id,
            'paket_id' => $klinikPaket ? $klinikPaket->id : null,
            'telefon' => '0 (212) 222 33 44',
            'e_posta' => 'sislisaglik@test.com',
            'adres' => 'Halaskargazi Cd. No:140, Şişli/İstanbul',
            'il_id' => $istanbul?->id,
            'ilce_id' => $sisli?->id,
            'enlem' => 41.0610,
            'boylam' => 28.9890,
            'aktif_mi' => true,
            'uyelik_baslangic' => now(),
            'uyelik_bitis' => now()->addYear(),
            'max_doktor_sayisi' => ($klinikPaket && $klinikPaket->max_doktor_sayisi) ? $klinikPaket->max_doktor_sayisi : 10,
            'calisma_saatleri' => [
                'pazartesi' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
                'sali' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
                'carsamba' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
                'persembe' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
                'cuma' => ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false],
                'cumartesi' => ['acilis' => '09:00', 'kapanis' => '14:00', 'kapali' => false],
                'pazar' => ['acilis' => '00:00', 'kapanis' => '00:00', 'kapali' => true],
            ],
        ]);

        $doktor6->update([
            'klinik_id' => $klinik->id,
            'klinik_katilma_tarihi' => now(),
            'klinik_aktif_mi' => true,
        ]);

        // 7. Doktor: Özgür Demir (Klinik Üyesi/Doktoru)
        $doktor7 = Doktor::create([
            'ad_soyad' => 'Özgür Demir',
            'e_posta' => 'ozgur@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '0 (533) 222 33 44',
            'il_id' => $istanbul?->id,
            'ilce_id' => $sisli?->id,
            'tur' => 'klinik',
            'klinik_rolu' => 'doktor',
            'klinik_adi' => 'Şişli Sağlık Kliniği',
            'klinik_id' => $klinik->id,
            'klinik_katilma_tarihi' => now(),
            'klinik_aktif_mi' => true,
            'paket_id' => null,
            'aktif_mi' => true,
            'unvan' => 'Uzm. Dr.',
            'uzmanlik_alani' => 'Çocuk Sağlığı ve Hastalıkları',
            'mezuniyet' => [
                'Ege Üniversitesi Tıp Fakültesi (2010)',
                'Pediatri İhtisası - İstanbul Üniversitesi (2015)',
            ],
            'biyografi' => 'Çocuk sağlığı ve hastalıkları uzmanı olarak Şişli Sağlık Kliniğinde hizmet vermektedir.',
            'enlem' => 41.0610,
            'boylam' => 28.9890,
            'adres' => 'Halaskargazi Cd. No:140, Şişli/İstanbul',
        ]);
        $bransCocuk = Brans::where('ad', 'Çocuk Sağlığı ve Hastalıkları')->first();
        if ($bransCocuk) {
            $doktor7->branslar()->attach($bransCocuk->id);
        }

        // 7.1 Hizmet Oluşturma (Klinik Hekimleri için)
        Hizmet::create([
            'doktor_id' => $doktor6->id,
            'ad' => 'Dermatoloji Muayenesi',
            'aciklama' => 'Cilt, saç ve tırnak hastalıkları teşhis ve tedavisi.',
            'resim' => isset($dummyHizmetImages[0]) ? $dummyHizmetImages[0] : null,
            'sure' => 20,
            'fiyat' => 1200.00,
            'aktif_mi' => true,
            'meta_baslik' => 'Dermatoloji Muayenesi - Doç. Dr. Canan Dağdeviren',
            'meta_aciklama' => 'Şişli Sağlık Kliniği bünyesinde Doç. Dr. Canan Dağdeviren ile cilt hastalıkları teşhis ve tedavisi.',
            'meta_anahtar_kelimeler' => 'dermatoloji muayenesi, cilt hastalıkları, cildiye, canan dağdeviren',
        ]);

        Hizmet::create([
            'doktor_id' => $doktor7->id,
            'ad' => 'Çocuk Sağlığı Muayenesi',
            'aciklama' => 'Pediatrik rutin kontroller, gelişim takibi ve muayene.',
            'resim' => isset($dummyHizmetImages[1]) ? $dummyHizmetImages[1] : null,
            'sure' => 30,
            'fiyat' => 1000.00,
            'aktif_mi' => true,
            'meta_baslik' => 'Çocuk Sağlığı Muayenesi - Uzm. Dr. Özgür Demir',
            'meta_aciklama' => 'Pediatri uzmanı Dr. Özgür Demir eşliğinde rutin bebek ve çocuk sağlığı muayenesi.',
            'meta_anahtar_kelimeler' => 'çocuk sağlığı, pediatri, bebek muayenesi, özgür demir',
        ]);

        // 8. Ahmet Yılmaz için bekleyen bir klinik davetiyesi ekleyelim (Test için)
        KlinikDavetiye::create([
            'klinik_id' => $klinik->id,
            'davet_eden_id' => $doktor6->id,
            'davet_edilen_eposta' => $doktor1->e_posta,
            'davet_edilen_doktor_id' => $doktor1->id,
            'token' => \Illuminate\Support\Str::random(40),
            'durum' => 'beklemede',
            'son_kullanma_tarihi' => now()->addDays(7),
        ]);

        // Seed default appointment settings, working hours and gallery for all doctors
        $doktorlar = [$doktor1, $doktor2, $doktor3, $doktor4, $doktor5, $doktor6, $doktor7];
        foreach ($doktorlar as $index => $doktor) {
            // Dyt. Melis Şen (index 4) has online appointment system closed indefinitely to test "İletişime Geç" card
            $aktifMi = ($index !== 4);

            RandevuAyari::create([
                'doktor_id' => $doktor->id,
                'randevu_onay_tipi' => 'manuel',
                'en_erken_randevu_saati' => 2,
                'en_gec_randevu_gunu' => 30,
                'aktif_mi' => $aktifMi,
            ]);

            for ($gun = 1; $gun <= 7; $gun++) {
                $isWeekday = ($gun <= 5);

                DoktorCalismaSaati::create([
                    'doktor_id' => $doktor->id,
                    'gun' => $gun,
                    'aktif_mi' => $isWeekday,
                    'mesai_baslangic' => '09:00',
                    'mesai_bitis' => '17:00',
                    'ogle_arasi_aktif_mi' => $isWeekday,
                    'ogle_baslangic' => '12:00',
                    'ogle_bitis' => '13:00',
                ]);
            }

            // Seed 3 gallery images for each doctor
            $captions = [
                'Giriş Alanı ve Karşılama Bankosu',
                'Modern Muayenehane Odası',
                'Tıbbi Ekipman ve Teknolojik Altyapımız',
            ];

            foreach ($dummyGaleriImages as $gIndex => $imagePath) {
                DoktorGaleri::create([
                    'doktor_id' => $doktor->id,
                    'resim_yolu' => $imagePath,
                    'baslik' => $captions[$gIndex],
                    'sira' => $gIndex + 1,
                ]);
            }
        }
    }
}
