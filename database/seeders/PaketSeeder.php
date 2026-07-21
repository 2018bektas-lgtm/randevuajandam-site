<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Models\PaketOzelligi;
use Illuminate\Database\Seeder;

/**
 * Bireysel paketler — içerikler projedeki gerçek yetkilere göre:
 *
 * Sistem kodları (paket.yetki / hasFeature):
 *   hakkimda, galeri, randevu_talepleri, finans, blog, faq, egitimler,
 *   online_gorusme (randevu), web_sitesi
 *
 * Her pakette (limit dışında) açık: takvim, hasta, hizmet, randevu ayarları,
 * bekleme listesi, hızlı slot kapatma, profil, yorum yanıtları.
 */
class PaketSeeder extends Seeder
{
    public function run(): void
    {
        $ozellikler = [
            ['kod' => 'hakkimda', 'ad' => 'Hakkımda / Özgeçmiş Yönetimi', 'aciklama' => 'Detaylı özgeçmiş ve mezuniyet bilgisi ekleme yetkisi.'],
            ['kod' => 'galeri', 'ad' => 'Fotoğraf Galerisi Modülü', 'aciklama' => 'Klinik / muayenehane resimleri yükleme yetkisi.'],
            ['kod' => 'randevu_talepleri', 'ad' => 'Danışan Randevu Talepleri Modülü', 'aciklama' => 'Beklemedeki randevu taleplerini yönetme yetkisi.'],
            ['kod' => 'finans', 'ad' => 'Gelir / Gider Raporlaması', 'aciklama' => 'Hekim paneli finans ve muhasebe takibi yetkisi.'],
            ['kod' => 'blog', 'ad' => 'Blog / Makale Paneli', 'aciklama' => 'Blog ve sağlık makalesi yayınlama yetkisi.'],
            ['kod' => 'yorum', 'ad' => 'Danışan Yorumları Modülü', 'aciklama' => 'Hasta yorumlarını yönetme (profil yorumları).'],
            ['kod' => 'faq', 'ad' => 'Sıkça Sorulan Sorular Modülü', 'aciklama' => 'Profilde S.S.S. yayınlama yetkisi.'],
            ['kod' => 'web_sitesi', 'ad' => 'Özel Web Sitesi Entegrasyonu', 'aciklama' => 'Kişisel hekim web sitesi + 1 yıl domain (com/net) pakete dahil.'],
            ['kod' => 'klinik_web_sitesi', 'ad' => 'Klinik Web Sitesi', 'aciklama' => 'Klinik markalı özel web sitesi, çok hekim vitrini ve online randevu.'],
            ['kod' => 'egitimler', 'ad' => 'Eğitimler & Başvuru Formu', 'aciklama' => 'Kurs/webinar vitrini, dinamik başvuru formu ve eğitim geliri takibi.'],
            ['kod' => 'online_gorusme', 'ad' => 'Online Görüşme', 'aciklama' => 'Randevuda online seans; platform görüntülü oda.'],
        ];

        $dbOzellikler = [];
        foreach ($ozellikler as $oVeri) {
            $dbOzellikler[$oVeri['kod']] = PaketOzelligi::updateOrCreate(
                ['kod' => $oVeri['kod']],
                $oVeri
            );
        }

        $bireyselPaketler = [
            [
                'ad' => 'Ücretsiz Deneme (Demo)',
                'tur' => 'bireysel',
                'aciklama' => 'Sistemi denemek için: en fazla 10 hasta ve 20 randevu. Ücretli pakete istediğiniz zaman geçin.',
                'aylik_fiyat' => 0.00,
                'aylik_indirimli_fiyat' => null,
                'yillik_fiyat' => 0.00,
                'yillik_indirimli_fiyat' => null,
                'ozellikler' => [
                    'Online randevu takvimi',
                    'Hasta kayıtları (en fazla 10)',
                    'Randevu oluşturma (en fazla 20)',
                    'Hizmet / tedavi tanımlama',
                    'Çalışma saatleri ve randevu ayarları',
                    'Platformda hekim profili',
                    'Ücretli pakete tek tıkla yükseltme',
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
                'aciklama' => 'Temel randevu ve hasta yönetimi — limitsiz hasta/randevu, 14 gün deneme.',
                'aylik_fiyat' => 1900.00,
                'aylik_indirimli_fiyat' => 1500.00,
                'yillik_fiyat' => 18990.00,
                'yillik_indirimli_fiyat' => 14990.00,
                'ozellikler' => [
                    '14 gün ücretsiz deneme (kart gerekmez)',
                    'Limitsiz hasta ve randevu',
                    'Online randevu takvimi',
                    'Hasta / danışan kartları (CRM)',
                    'Hizmet ve tedavi tanımlama',
                    'Çalışma saatleri, molalar, randevu ayarları',
                    'Bekleme listesi',
                    'Hızlı slot kapatma / bloklama',
                    'Hekim profili (unvan, branş, biyografi)',
                    'Danışan yorumlarını yanıtlama',
                    'Platformda listeleme',
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
                'aciklama' => 'Profil ve talep yönetimi güçlendirilmiş paket: hakkımda, galeri, randevu talepleri.',
                'aylik_fiyat' => 3000.00,
                'aylik_indirimli_fiyat' => 2500.00,
                'yillik_fiyat' => 29990.00,
                'yillik_indirimli_fiyat' => 24990.00,
                'ozellikler' => [
                    'Başlangıç paketinin tümü',
                    'Hakkımda / özgeçmiş & mezuniyet alanları',
                    'Fotoğraf galerisi (muayenehane / klinik görselleri)',
                    'Gelen randevu taleplerini onaylama / reddetme',
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
                'aciklama' => 'Finans, içerik ve online görüşme dahil tam hekim paneli (web sitesi hariç).',
                'aylik_fiyat' => 4200.00,
                'aylik_indirimli_fiyat' => 3500.00,
                'yillik_fiyat' => 41990.00,
                'yillik_indirimli_fiyat' => 34990.00,
                'ozellikler' => [
                    'Profesyonel paketinin tümü',
                    'Finans: gelir / gider / hasta bakiyeleri',
                    'Blog ve makale yayınlama',
                    'S.S.S. (sık sorulan sorular) yönetimi',
                    'Eğitim / kurs vitrini ve başvuru formu',
                    'Online görüntülü görüşme (platform odası)',
                ],
                'aktif_mi' => true,
                'sira' => 4,
                'one_cikan_mi' => false,
                'etiket' => null,
                'etiket_stil' => null,
                // yorum route’u pakete bağlı değil; kod katalogda kalır, zorunlu gate yok
                'sistem_ozellikleri' => [
                    'hakkimda', 'galeri', 'randevu_talepleri',
                    'finans', 'blog', 'faq', 'egitimler', 'online_gorusme',
                ],
                'max_hasta_sayisi' => null,
                'max_randevu_sayisi' => null,
            ],
            [
                'ad' => 'Özel Web Sitesi Entegrasyon Paketi',
                'tur' => 'bireysel',
                'aciklama' => 'VIP panel + kişisel hekim web sitesi; 1 yıl .com/.net domain pakete dahil.',
                'aylik_fiyat' => 6000.00,
                'aylik_indirimli_fiyat' => 5000.00,
                'yillik_fiyat' => 59990.00,
                'yillik_indirimli_fiyat' => 49990.00,
                'ozellikler' => [
                    'VIP paketinin tümü',
                    'Kişisel hekim web sitesi (CMS)',
                    'Tema seçimi (Sıcak, Ocean, Modern, Minimal, Klasik)',
                    '1 yıl domain dahil (.com / .net)',
                    'Hosting ve SSL dahil',
                    'Siteden online randevu alma',
                    'Domain kurulum ve DNS yönlendirme adımları',
                ],
                'aktif_mi' => true,
                'domain_dahil_mi' => true,
                'domain_dahil_yil' => 1,
                'domain_dahil_tlds' => ['com', 'net'],
                'sira' => 5,
                'one_cikan_mi' => true,
                'etiket' => 'Web sitesi',
                'etiket_stil' => 'web',
                'sistem_ozellikleri' => [
                    'hakkimda', 'galeri', 'randevu_talepleri',
                    'finans', 'blog', 'faq', 'egitimler', 'online_gorusme',
                    'web_sitesi',
                ],
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
