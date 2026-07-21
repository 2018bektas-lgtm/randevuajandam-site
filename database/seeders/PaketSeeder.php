<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Models\PaketOzelligi;
use Illuminate\Database\Seeder;

class PaketSeeder extends Seeder
{
    /**
     * Bireysel paketler + sistem özellikleri.
     * updateOrCreate: varsa günceller, yoksa ekler.
     */
    public function run(): void
    {
        $ozellikler = [
            ['kod' => 'hakkimda', 'ad' => 'Hakkımda / Özgeçmiş Yönetimi', 'aciklama' => 'Detaylı özgeçmiş ve mezuniyet bilgisi ekleme yetkisi.'],
            ['kod' => 'galeri', 'ad' => 'Fotoğraf Galerisi Modülü', 'aciklama' => 'Klinik / muayenehane resimleri yükleme yetkisi.'],
            ['kod' => 'randevu_talepleri', 'ad' => 'Danışan Randevu Talepleri Modülü', 'aciklama' => 'Beklemedeki randevu taleplerini yönetme yetkisi.'],
            ['kod' => 'finans', 'ad' => 'Gelir / Gider Raporlaması', 'aciklama' => 'Finans ve muhasebe takibi yetkisi.'],
            ['kod' => 'blog', 'ad' => 'Blog / Makale Paneli', 'aciklama' => 'Blog ve sağlık makalesi yayınlama yetkisi.'],
            ['kod' => 'yorum', 'ad' => 'Danışan Yorumları Modülü', 'aciklama' => 'Hasta yorumlarını ve geri bildirimlerini yönetme yetkisi.'],
            ['kod' => 'faq', 'ad' => 'Sıkça Sorulan Sorular Modülü', 'aciklama' => 'Profilde S.S.S. yayınlama yetkisi.'],
            ['kod' => 'web_sitesi', 'ad' => 'Özel Web Sitesi Entegrasyonu', 'aciklama' => 'Kişisel hekim web sitesi + 1 yıl domain (com/net) pakete dahil; Hostinger üzerinden sistem kaydı.'],
            ['kod' => 'klinik_web_sitesi', 'ad' => 'Klinik Web Sitesi', 'aciklama' => 'Klinik markalı özel web sitesi, çok hekim vitrini ve online randevu.'],
            ['kod' => 'egitimler', 'ad' => 'Eğitimler & Başvuru Formu', 'aciklama' => 'Kurs/webinar vitrini, dinamik başvuru formu ve finansa yansıyan eğitim geliri takibi.'],
            ['kod' => 'online_gorusme', 'ad' => 'Online Görüşme', 'aciklama' => 'Randevuda online seans; platform üzerinden görüntülü oda (Zoom linki yok).'],
        ];

        $dbOzellikler = [];
        foreach ($ozellikler as $oVeri) {
            $dbOzellikler[$oVeri['kod']] = PaketOzelligi::updateOrCreate(
                ['kod' => $oVeri['kod']],
                $oVeri
            );
        }

        // Kampanyalı = indirimli; Normal = liste fiyatı
        $bireyselPaketler = [
            [
                'ad' => 'Ücretsiz Deneme (Demo)',
                'tur' => 'bireysel',
                'aciklama' => 'Ücretsiz deneme: en fazla 10 hasta ve 20 randevu. Sistemi risk almadan test edin.',
                'aylik_fiyat' => 0.00,
                'aylik_indirimli_fiyat' => null,
                'yillik_fiyat' => 0.00,
                'yillik_indirimli_fiyat' => null,
                'ozellikler' => [
                    'Online Randevu Takvimi',
                    'Maksimum 10 Hasta Kaydı (limit)',
                    'Maksimum 20 Randevu (limit)',
                    'Ücretli paketlere tek tıkla yükseltme',
                ],
                'aktif_mi' => true,
                'sira' => 1,
                'one_cikan_mi' => false,
                'etiket' => 'Ücretsiz',
                'etiket_stil' => 'free',
                'sistem_ozellikleri' => [],
                'max_hasta_sayisi' => 10,
                'max_randevu_sayisi' => 20,
            ],
            [
                'ad' => 'Başlangıç (Starter) Paketi',
                'tur' => 'bireysel',
                'aciklama' => 'Mesleğe yeni başlayan veya temel dijitalleşme ihtiyacı olan hekimler için.',
                'aylik_fiyat' => 1900.00,
                'aylik_indirimli_fiyat' => 1500.00,
                'yillik_fiyat' => 18990.00,
                'yillik_indirimli_fiyat' => 14990.00,
                'ozellikler' => [
                    '14 gün ücretsiz deneme (kart gerekmez)',
                    'Online Randevu Takvimi ve Yönetimi',
                    'Hasta / Danışan Kartı Kayıt Yönetimi (CRM)',
                    'Hizmet ve Tedavi Tanımlama Modülü',
                    'Randevu Ayarları (Çalışma Saatleri & Öğle Araları)',
                    'Hekim Profili (Unvan, Branş & Biyografi)',
                    'Arama Sonuçlarında Standart Listeleme',
                ],
                'aktif_mi' => true,
                'deneme_gun' => 14,
                'sira' => 2,
                'one_cikan_mi' => false,
                'etiket' => null,
                'etiket_stil' => null,
                'sistem_ozellikleri' => [],
                'max_hasta_sayisi' => null,
                'max_randevu_sayisi' => null,
            ],
            [
                'ad' => 'Profesyonel (Plus) Paket',
                'tur' => 'bireysel',
                'aciklama' => 'Görünürlüğünü ve hasta portföyünü artırmak isteyen aktif hekimler için.',
                'aylik_fiyat' => 3000.00,
                'aylik_indirimli_fiyat' => 2500.00,
                'yillik_fiyat' => 29990.00,
                'yillik_indirimli_fiyat' => 24990.00,
                'ozellikler' => [
                    'Başlangıç Paketi Özelliklerinin Tümü',
                    'Detaylı Özgeçmiş / Hakkımda & Mezuniyet Alanları',
                    'Fotoğraf Galerisi (Klinik / Muayenehane Resimleri)',
                    'Danışan Randevu Talepleri Yönetim Sistemi',
                    'Arama Sonuçlarında Öncelikli Listeleme',
                    'Hızlı Randevu Slotu Kapatma / Bloklama',
                ],
                'aktif_mi' => true,
                'sira' => 3,
                'one_cikan_mi' => true,
                'etiket' => 'Popüler',
                'etiket_stil' => 'popular',
                'sistem_ozellikleri' => ['hakkimda', 'galeri', 'randevu_talepleri'],
                'max_hasta_sayisi' => null,
                'max_randevu_sayisi' => null,
            ],
            [
                'ad' => 'VIP (Elite) Paket',
                'tur' => 'bireysel',
                'aciklama' => 'Maksimum dijital görünürlük ve tam otomasyon arayan lider hekimler için.',
                'aylik_fiyat' => 4200.00,
                'aylik_indirimli_fiyat' => 3500.00,
                'yillik_fiyat' => 41990.00,
                'yillik_indirimli_fiyat' => 34990.00,
                'ozellikler' => [
                    'Profesyonel Paket Özelliklerinin Tümü',
                    'Finansal Raporlar & Gelir / Gider Muhasebe Takibi',
                    'Sıkça Sorulan Sorular (S.S.S.) Yönetim Modülü',
                    'Blog & Sağlık Makalesi Yayınlama Paneli',
                    'Danışan Yorumları & Geri Bildirim Yönetimi',
                    'Eğitimler & dinamik başvuru formu',
                    'Online görüntülü görüşme (platform odası)',
                    'Arama Sonuçlarında En Üst Sırada VIP Listeleme',
                ],
                'aktif_mi' => true,
                'sira' => 4,
                'one_cikan_mi' => false,
                'etiket' => null,
                'etiket_stil' => null,
                'sistem_ozellikleri' => ['hakkimda', 'galeri', 'randevu_talepleri', 'finans', 'blog', 'yorum', 'faq', 'egitimler', 'online_gorusme'],
                'max_hasta_sayisi' => null,
                'max_randevu_sayisi' => null,
            ],
            [
                'ad' => 'Özel Web Sitesi Entegrasyon Paketi',
                'tur' => 'bireysel',
                'aciklama' => 'VIP paneli + kişisel hekim web sitesi: 1 yıl domain (.com/.net) pakete dahil, CMS, online randevu ve SEO tek fiyatta.',
                'aylik_fiyat' => 6000.00,
                'aylik_indirimli_fiyat' => 5000.00,
                'yillik_fiyat' => 59990.00,
                'yillik_indirimli_fiyat' => 49990.00,
                'ozellikler' => [
                    'Tüm VIP Paket Özelliklerinin Tamamı',
                    'Finans / muhasebe modülü (hekim paneli)',
                    '1 yıl domain dahil (.com / .net) — ek ücret yok',
                    'Mobil Uyumlu Modern Hekim Web Sitesi (doktorsitesi)',
                    'Premium site temaları (Sıcak, Ocean) + 3 ücretsiz tema',
                    'Eğitimler & dinamik başvuru formu',
                    'Online görüntülü görüşme (platform odası)',
                    'Web Sitesi Üzerinden Anlık Online Randevu',
                    'Blog / Makale ve İçerik Yönetim Modülü',
                    'Google Haritalar, SEO ve Analytics Entegrasyonu',
                    'Hosting, SSL ve Teknik Bakım Dahil',
                ],
                'aktif_mi' => true,
                'domain_dahil_mi' => true,
                'domain_dahil_yil' => 1,
                'domain_dahil_tlds' => ['com', 'net'],
                'sira' => 5,
                'one_cikan_mi' => true,
                'etiket' => 'Web sitesi',
                'etiket_stil' => 'web',
                'sistem_ozellikleri' => ['hakkimda', 'galeri', 'randevu_talepleri', 'finans', 'blog', 'yorum', 'faq', 'web_sitesi', 'egitimler', 'online_gorusme'],
                'max_hasta_sayisi' => null,
                'max_randevu_sayisi' => null,
            ],
        ];

        foreach ($bireyselPaketler as $paketVeri) {
            $sistemOzellikKodlari = $paketVeri['sistem_ozellikleri'] ?? [];
            unset($paketVeri['sistem_ozellikleri']);

            $paketVeri['domain_dahil_mi'] = (bool) ($paketVeri['domain_dahil_mi'] ?? false);
            $paketVeri['domain_dahil_yil'] = (int) ($paketVeri['domain_dahil_yil'] ?? 1);
            $paketVeri['domain_dahil_tlds'] = $paketVeri['domain_dahil_tlds'] ?? null;
            $paketVeri['one_cikan_mi'] = (bool) ($paketVeri['one_cikan_mi'] ?? false);

            $paket = Paket::updateOrCreate(
                ['ad' => $paketVeri['ad'], 'tur' => $paketVeri['tur']],
                $paketVeri
            );

            $featureIds = [];
            foreach ($sistemOzellikKodlari as $kod) {
                if (isset($dbOzellikler[$kod])) {
                    $featureIds[] = $dbOzellikler[$kod]->id;
                }
            }
            $paket->sistemOzellikleri()->sync($featureIds);
        }
    }
}
