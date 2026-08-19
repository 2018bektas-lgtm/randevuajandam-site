<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Support\PaketOzellikKatalogu;
use Illuminate\Database\Seeder;

/**
 * Bireysel paketler — Excel: randevu-ajandam-paket-fiyat-onerisi.xlsx
 * Fiyatlar KDV dahil; yıllık ≈ aylık × 12 × 0.80
 */
class PaketSeeder extends Seeder
{
    public function run(): void
    {
        $map = PaketOzellikKatalogu::sync();

        // Eski isim → Vitrin
        Paket::query()
            ->where('tur', 'bireysel')
            ->whereIn('ad', ['Ücretsiz Deneme (Demo)', 'Ücretsiz Deneme', 'Demo'])
            ->update(['ad' => 'Vitrin']);

        $ids = function (array $kodlar) use ($map): array {
            $out = [];
            foreach ($kodlar as $k) {
                if (isset($map[$k])) {
                    $out[] = $map[$k]->id;
                }
            }

            return $out;
        };

        $yillik = fn (float $aylik): float => round($aylik * 12 * 0.80, 2);

        // Ortak setler
        $vitrinOz = [
            'randevu_talebi_goruntule',
            'profil_sayfasi', 'dogrulanmis_rozet', 'yorum_gorunur',
        ];

        $baslangicOz = array_merge($vitrinOz, [
            'randevu_talepleri', 'online_takvim', 'bekleme_listesi', 'hizli_slot',
            'email_bildirim', 'sms_hatirlatma',
            'hasta_kartlari', 'iletisim_profilde', 'yorum_yanit', 'finans',
            'destek_email',
        ]);

        $profesyonelOz = array_merge($baslangicOz, [
            'seri_randevu', 'ical_export', 'sms_baslik', 'no_show_mesaj',
            'tedavi_gecmisi', 'hasta_export',
            'hakkimda', 'galeri', 'dis_baglanti', 'oncelikli_liste',
            'yorum_davet', 'hasta_bakiyeleri',
            'ai_asistan',
        ]);

        $vipOz = array_merge($profesyonelOz, [
            'finans_rapor', 'blog', 'faq', 'egitimler', 'online_gorusme',
            'destek_oncelikli', 'veri_tasima',
        ]);

        $webOz = array_merge($vipOz, ['web_sitesi']);

        $bireysel = [
            [
                'ad' => 'Vitrin',
                'aciklama' => 'Platformda görünmek için ücretsiz vitrin. Talepleri görürsünüz; onay ve takvim ücretli pakette. En fazla 10 randevu kaydı.',
                'aylik' => 0.0,
                'deneme_gun' => null,
                'sira' => 1,
                'etiket' => 'Ücretsiz',
                'etiket_stil' => 'free',
                'one_cikan_mi' => false,
                'listeleme_oncelik' => 0,
                'max_randevu_sayisi' => 10,
                'max_hasta_sayisi' => null,
                'max_hizmet_sayisi' => 3,
                'max_biyografi_karakter' => 300,
                'max_profil_foto' => 1,
                'sms_aylik_kontor' => null,
                'max_personel_sayisi' => null,
                'domain_dahil_mi' => false,
                'ozellik_kodlari' => $vitrinOz,
            ],
            [
                'ad' => 'Başlangıç',
                'aciklama' => 'Tek hekim: takvim, hasta, talep onayı, SMS hatırlatma, temel finans. 14 gün deneme.',
                'aylik' => 1000.0,
                'deneme_gun' => 14,
                'sira' => 2,
                'etiket' => null,
                'etiket_stil' => null,
                'one_cikan_mi' => false,
                'listeleme_oncelik' => 1,
                'max_randevu_sayisi' => null,
                'max_hasta_sayisi' => null,
                'max_hizmet_sayisi' => null,
                'max_biyografi_karakter' => null,
                'max_profil_foto' => 3,
                'sms_aylik_kontor' => 250,
                'max_personel_sayisi' => null,
                'domain_dahil_mi' => false,
                'ozellik_kodlari' => $baslangicOz,
            ],
            [
                'ad' => 'Profesyonel',
                'aciklama' => 'CRM, galeri, hakkımda, hasta bakiyesi, 750 SMS, 1 personel koltuğu. En çok tercih edilen.',
                'aylik' => 1750.0,
                'deneme_gun' => 14,
                'sira' => 3,
                'etiket' => 'Popüler',
                'etiket_stil' => 'popular',
                'one_cikan_mi' => true,
                'listeleme_oncelik' => 2,
                'max_randevu_sayisi' => null,
                'max_hasta_sayisi' => null,
                'max_hizmet_sayisi' => null,
                'max_biyografi_karakter' => null,
                'max_profil_foto' => 10,
                'sms_aylik_kontor' => 750,
                'max_personel_sayisi' => 1,
                'domain_dahil_mi' => false,
                'ozellik_kodlari' => $profesyonelOz,
            ],
            [
                'ad' => 'VIP',
                'aciklama' => 'Blog, eğitim, online görüşme, 2000 SMS, öncelikli destek. Finans raporları dahil.',
                'aylik' => 2500.0,
                'deneme_gun' => 14,
                'sira' => 4,
                'etiket' => null,
                'etiket_stil' => null,
                'one_cikan_mi' => false,
                'listeleme_oncelik' => 2,
                'max_randevu_sayisi' => null,
                'max_hasta_sayisi' => null,
                'max_hizmet_sayisi' => null,
                'max_biyografi_karakter' => null,
                'max_profil_foto' => 30,
                'sms_aylik_kontor' => 2000,
                'max_personel_sayisi' => 1,
                'domain_dahil_mi' => false,
                'ozellik_kodlari' => $vipOz,
            ],
            [
                'ad' => 'Özel Web',
                'aciklama' => 'VIP + kişisel web sitesi; 1 yıl domain (.com/.net), hosting, SSL. 3000 SMS, 2 personel.',
                'aylik' => 3750.0,
                'deneme_gun' => 14,
                'sira' => 5,
                'etiket' => 'Web sitesi',
                'etiket_stil' => 'web',
                'one_cikan_mi' => true,
                'listeleme_oncelik' => 3,
                'max_randevu_sayisi' => null,
                'max_hasta_sayisi' => null,
                'max_hizmet_sayisi' => null,
                'max_biyografi_karakter' => null,
                'max_profil_foto' => null,
                'sms_aylik_kontor' => 3000,
                'max_personel_sayisi' => 2,
                'domain_dahil_mi' => true,
                'ozellik_kodlari' => $webOz,
            ],
        ];

        // Eski uzun isimleri sadeleştir
        $rename = [
            'Başlangıç (Starter) Paketi' => 'Başlangıç',
            'Profesyonel (Plus) Paket' => 'Profesyonel',
            'VIP (Elite) Paket' => 'VIP',
            'Özel Web Sitesi Entegrasyon Paketi' => 'Özel Web',
        ];
        foreach ($rename as $from => $to) {
            Paket::query()->where('tur', 'bireysel')->where('ad', $from)->update(['ad' => $to]);
        }

        foreach ($bireysel as $row) {
            $kodlar = $row['ozellik_kodlari'];
            $aylik = $row['aylik'];
            $y = $yillik($aylik);

            $payload = [
                'tur' => 'bireysel',
                'ad' => $row['ad'],
                'aciklama' => $row['aciklama'],
                'aylik_fiyat' => $aylik,
                'aylik_indirimli_fiyat' => $aylik > 0 ? $aylik : null,
                'yillik_fiyat' => $y,
                'yillik_indirimli_fiyat' => $aylik > 0 ? $y : null,
                'aktif_mi' => true,
                'sira' => $row['sira'],
                'one_cikan_mi' => $row['one_cikan_mi'],
                'etiket' => $row['etiket'],
                'etiket_stil' => $row['etiket_stil'],
                'deneme_gun' => $row['deneme_gun'],
                'max_randevu_sayisi' => $row['max_randevu_sayisi'],
                'max_hasta_sayisi' => $row['max_hasta_sayisi'],
                'max_hizmet_sayisi' => $row['max_hizmet_sayisi'] ?? null,
                'max_biyografi_karakter' => $row['max_biyografi_karakter'] ?? null,
                'max_profil_foto' => $row['max_profil_foto'] ?? null,
                'sms_aylik_kontor' => $row['sms_aylik_kontor'],
                'max_personel_sayisi' => $row['max_personel_sayisi'],
                'max_doktor_sayisi' => null,
                'listeleme_oncelik' => $row['listeleme_oncelik'],
                'domain_dahil_mi' => $row['domain_dahil_mi'],
                'domain_dahil_yil' => 1,
                'domain_dahil_tlds' => $row['domain_dahil_mi'] ? ['com', 'net'] : null,
                'ek_doktor_aylik_fiyat' => null,
                'ek_doktor_yillik_fiyat' => null,
                'ek_personel_aylik_fiyat' => 300,
                'ek_personel_yillik_fiyat' => 2880,
                'ozellikler' => PaketOzellikKatalogu::vitrinMetinleri(
                    $kodlar,
                    $row['sms_aylik_kontor'],
                    $row['max_randevu_sayisi'],
                    $row['max_hasta_sayisi']
                ),
            ];

            $paket = Paket::updateOrCreate(
                ['ad' => $row['ad'], 'tur' => 'bireysel'],
                $payload
            );
            $paket->sistemOzellikleri()->sync($ids($kodlar));
        }
    }
}
