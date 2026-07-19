<?php

namespace Database\Seeders;

use App\Models\Paket;
use Illuminate\Database\Seeder;

class PaketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create system features (Paket Yetkileri)
        $ozellikler = [
            ['kod' => 'hakkimda', 'ad' => 'Hakkımda / Özgeçmiş Yönetimi', 'aciklama' => 'Detaylı özgeçmiş ve mezuniyet bilgisi ekleme yetkisi.'],
            ['kod' => 'galeri', 'ad' => 'Fotoğraf Galerisi Modülü', 'aciklama' => 'Klinik / muayenehane resimleri yükleme yetkisi.'],
            ['kod' => 'randevu_talepleri', 'ad' => 'Danışan Randevu Talepleri Modülü', 'aciklama' => 'Beklemedeki randevu taleplerini yönetme yetkisi.'],
            ['kod' => 'finans', 'ad' => 'Gelir / Gider Raporlaması', 'aciklama' => 'Finans ve muhasebe takibi yetkisi.'],
            ['kod' => 'blog', 'ad' => 'Blog / Makale Paneli', 'aciklama' => 'Blog ve sağlık makalesi yayınlama yetkisi.'],
            ['kod' => 'yorum', 'ad' => 'Danışan Yorumları Modülü', 'aciklama' => 'Hasta yorumlarını ve geri bildirimlerini yönetme yetkisi.'],
            ['kod' => 'faq', 'ad' => 'Sıkça Sorulan Sorular Modülü', 'aciklama' => 'Profilde S.S.S. yayınlama yetkisi.'],
            ['kod' => 'web_sitesi', 'ad' => 'Özel Web Sitesi Entegrasyonu', 'aciklama' => 'Kişisel hekim web sitesi + 1 yıl domain (com/net) pakete dahil; Hostinger üzerinden sistem kaydı.'],
            ['kod' => 'klinik_web_sitesi', 'ad' => 'Klinik Web Sitesi', 'aciklama' => 'Klinik markalı özel web sitesi, çok hekim vitrini ve online randevu (yalnızca Klinik Kurumsal).'],
            ['kod' => 'egitimler', 'ad' => 'Eğitimler & Başvuru Formu', 'aciklama' => 'Kurs/webinar vitrini, dinamik başvuru formu ve finansa yansıyan eğitim geliri takibi.'],
            ['kod' => 'online_gorusme', 'ad' => 'Online Görüşme', 'aciklama' => 'Randevuda online seans; platform üzerinden görüntülü oda (Zoom linki yok).'],
        ];

        $dbOzellikler = [];
        foreach ($ozellikler as $oVeri) {
            $dbOzellikler[$oVeri['kod']] = \App\Models\PaketOzelligi::updateOrCreate(
                ['kod' => $oVeri['kod']],
                $oVeri
            );
        }

        // Bireysel Hekim Paketleri (DoktorTakvimi / DoktorSitesi kıyaslamalı)
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
                'sistem_ozellikleri' => [],
                'max_hasta_sayisi' => 10,
                'max_randevu_sayisi' => 20,
            ],
            [
                'ad' => 'Başlangıç (Starter) Paketi',
                'tur' => 'bireysel',
                'aciklama' => 'Mesleğe yeni başlayan veya temel dijitalleşme ihtiyacı olan hekimler için.',
                'aylik_fiyat' => 1299.00,
                'aylik_indirimli_fiyat' => 999.00,
                'yillik_fiyat' => 12990.00,
                'yillik_indirimli_fiyat' => 9999.00,
                'ozellikler' => [
                    'Online Randevu Takvimi ve Yönetimi',
                    'Hasta / Danışan Kartı Kayıt Yönetimi (CRM)',
                    'Hizmet ve Tedavi Tanımlama Modülü',
                    'Randevu Ayarları (Çalışma Saatleri & Öğle Araları)',
                    'Hekim Profili (Unvan, Branş & Biyografi)',
                    'Arama Sonuçlarında Standart Listeleme',
                ],
                'aktif_mi' => true,
                'sistem_ozellikleri' => [],
                'max_hasta_sayisi' => null,
                'max_randevu_sayisi' => null,
            ],
            [
                'ad' => 'Profesyonel (Plus) Paket',
                'tur' => 'bireysel',
                'aciklama' => 'Görünürlüğünü ve hasta portföyünü artırmak isteyen aktif hekimler için.',
                'aylik_fiyat' => 1699.00,
                'aylik_indirimli_fiyat' => 1299.00,
                'yillik_fiyat' => 16990.00,
                'yillik_indirimli_fiyat' => 12999.00,
                'ozellikler' => [
                    'Başlangıç Paketi Özelliklerinin Tümü',
                    'Detaylı Özgeçmiş / Hakkımda & Mezuniyet Alanları',
                    'Fotoğraf Galerisi (Klinik / Muayenehane Resimleri)',
                    'Danışan Randevu Talepleri Yönetim Sistemi',
                    'Arama Sonuçlarında Öncelikli Listeleme',
                    'Hızlı Randevu Slotu Kapatma / Bloklama',
                ],
                'aktif_mi' => true,
                'sistem_ozellikleri' => ['hakkimda', 'galeri', 'randevu_talepleri'],
                'max_hasta_sayisi' => null,
                'max_randevu_sayisi' => null,
            ],
            [
                'ad' => 'VIP (Elite) Paket',
                'tur' => 'bireysel',
                'aciklama' => 'Maksimum dijital görünürlük ve tam otomasyon arayan lider hekimler için.',
                'aylik_fiyat' => 2099.00,
                'aylik_indirimli_fiyat' => 1599.00,
                'yillik_fiyat' => 20990.00,
                'yillik_indirimli_fiyat' => 15999.00,
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
                'sistem_ozellikleri' => ['hakkimda', 'galeri', 'randevu_talepleri', 'finans', 'blog', 'yorum', 'faq', 'egitimler', 'online_gorusme'],
                'max_hasta_sayisi' => null,
                'max_randevu_sayisi' => null,
            ],
            [
                'ad' => 'Özel Web Sitesi Entegrasyon Paketi',
                'tur' => 'bireysel',
                'aciklama' => 'VIP paneli + kişisel hekim web sitesi: 1 yıl domain (.com/.net) pakete dahil, CMS, online randevu ve SEO tek fiyatta.',
                'aylik_fiyat' => 2499.00,
                'aylik_indirimli_fiyat' => 1999.00,
                'yillik_fiyat' => 24999.00,
                'yillik_indirimli_fiyat' => 19999.00,
                'ozellikler' => [
                    'Tüm VIP Paket Özelliklerinin Tamamı',
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
                'sistem_ozellikleri' => ['hakkimda', 'galeri', 'randevu_talepleri', 'finans', 'blog', 'yorum', 'faq', 'web_sitesi', 'egitimler', 'online_gorusme'],
                'max_hasta_sayisi' => null,
                'max_randevu_sayisi' => null,
            ],
        ];

        foreach ($bireyselPaketler as $paketVeri) {
            $sistemOzellikKodlari = $paketVeri['sistem_ozellikleri'] ?? [];
            unset($paketVeri['sistem_ozellikleri']);
            // domain alanları yoksa false
            $paketVeri['domain_dahil_mi'] = (bool) ($paketVeri['domain_dahil_mi'] ?? false);
            $paketVeri['domain_dahil_yil'] = (int) ($paketVeri['domain_dahil_yil'] ?? 1);
            $paketVeri['domain_dahil_tlds'] = $paketVeri['domain_dahil_tlds'] ?? null;

            $paket = Paket::updateOrCreate(
                ['ad' => $paketVeri['ad'], 'tur' => $paketVeri['tur']],
                $paketVeri
            );

            // Sync features in the pivot table
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
