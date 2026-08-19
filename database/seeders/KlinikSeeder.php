<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Support\PaketOzellikKatalogu;
use Illuminate\Database\Seeder;

/**
 * Klinik paketleri — Excel: randevu-ajandam-paket-fiyat-onerisi.xlsx
 * Hekim paneli özellikleri basamaklı (Başlangıç / Profesyonel / VIP eşdeğeri).
 */
class KlinikSeeder extends Seeder
{
    public function run(): void
    {
        $map = PaketOzellikKatalogu::sync();
        $yillik = fn (float $aylik): float => round($aylik * 12 * 0.80, 2);
        $ids = function (array $kodlar) use ($map): array {
            $out = [];
            foreach ($kodlar as $k) {
                if (isset($map[$k])) {
                    $out[] = $map[$k]->id;
                }
            }

            return $out;
        };

        $hekimBaslangic = [
            'randevu_talebi_goruntule', 'randevu_talepleri', 'online_takvim', 'bekleme_listesi', 'hizli_slot',
            'email_bildirim', 'sms_hatirlatma',
            'hasta_kartlari', 'iletisim_profilde', 'yorum_yanit', 'yorum_gorunur',
            'finans', 'profil_sayfasi', 'dogrulanmis_rozet', 'destek_email',
        ];

        $hekimProfesyonel = array_merge($hekimBaslangic, [
            'seri_randevu', 'ical_export', 'sms_baslik', 'no_show_mesaj',
            'tedavi_gecmisi', 'hasta_export',
            'hakkimda', 'galeri', 'dis_baglanti', 'oncelikli_liste',
            'yorum_davet', 'hasta_bakiyeleri',
            'ai_asistan',
        ]);

        $hekimVip = array_merge($hekimProfesyonel, [
            'finans_rapor', 'blog', 'faq', 'egitimler', 'online_gorusme',
            'destek_oncelikli', 'veri_tasima',
        ]);

        $paketler = [
            [
                'ad' => 'Klinik Başlangıç',
                'aciklama' => '3 hekim / 1 personel. Ortak hasta havuzu + klinik takvim. Hekim paneli: Başlangıç seviyesi.',
                'aylik' => 2400.0,
                'max_doktor' => 3,
                'max_personel' => 1,
                'max_ek_doktor' => 3,
                'sms' => 750,
                'sira' => 1,
                'etiket' => null,
                'etiket_stil' => null,
                'one_cikan' => false,
                'merkezi_finans' => false,
                'toplu_randevu' => false,
                'raporlama' => false,
                'hasta_havuzu' => true,
                'domain' => false,
                'klinik_web' => false,
                'hekim_oz' => $hekimBaslangic,
            ],
            [
                'ad' => 'Klinik Plus',
                'aciklama' => '6 hekim / 2 personel. Merkezi finans, muhasebeci, toplu randevu. Hekim paneli: Profesyonel.',
                'aylik' => 4200.0,
                'max_doktor' => 6,
                'max_personel' => 2,
                'max_ek_doktor' => 4,
                'sms' => 2000,
                'sira' => 2,
                'etiket' => null,
                'etiket_stil' => null,
                'one_cikan' => false,
                'merkezi_finans' => true,
                'toplu_randevu' => true,
                'raporlama' => false,
                'hasta_havuzu' => true,
                'domain' => false,
                'klinik_web' => false,
                'hekim_oz' => $hekimProfesyonel,
            ],
            [
                'ad' => 'Klinik Profesyonel',
                'aciklama' => '10 hekim / 5 personel. Performans raporları + hakediş. Hekim paneli: Profesyonel.',
                'aylik' => 5500.0,
                'max_doktor' => 10,
                'max_personel' => 5,
                'max_ek_doktor' => 4,
                'sms' => 5000,
                'sira' => 3,
                'etiket' => 'Önerilen',
                'etiket_stil' => 'popular',
                'one_cikan' => true,
                'merkezi_finans' => true,
                'toplu_randevu' => true,
                'raporlama' => true,
                'hasta_havuzu' => true,
                'domain' => false,
                'klinik_web' => false,
                'hekim_oz' => $hekimProfesyonel,
            ],
            [
                'ad' => 'Klinik Özel Web',
                'aciklama' => '20 hekim / 10 personel + klinik web sitesi. Hekim paneli: VIP seviyesi.',
                'aylik' => 8500.0,
                'max_doktor' => 20,
                'max_personel' => 10,
                'max_ek_doktor' => 10,
                'sms' => 10000,
                'sira' => 4,
                'etiket' => 'Web sitesi dahil',
                'etiket_stil' => 'web',
                'one_cikan' => true,
                'merkezi_finans' => true,
                'toplu_randevu' => true,
                'raporlama' => true,
                'hasta_havuzu' => true,
                'domain' => true,
                'klinik_web' => true,
                'hekim_oz' => $hekimVip,
            ],
        ];

        // Eski ad
        Paket::query()->where('tur', 'klinik')->where('ad', 'Klinik Özel Web Sitesi')->update(['ad' => 'Klinik Özel Web']);

        foreach ($paketler as $row) {
            $kodlar = $row['hekim_oz'];
            if ($row['klinik_web']) {
                $kodlar[] = 'klinik_web_sitesi';
            }
            $aylik = $row['aylik'];
            $y = $yillik($aylik);

            $payload = [
                'tur' => 'klinik',
                'ad' => $row['ad'],
                'aciklama' => $row['aciklama'],
                'aylik_fiyat' => $aylik,
                'aylik_indirimli_fiyat' => $aylik,
                'yillik_fiyat' => $y,
                'yillik_indirimli_fiyat' => $y,
                'ek_doktor_aylik_fiyat' => 650,
                'ek_doktor_yillik_fiyat' => 6240,
                'ek_personel_aylik_fiyat' => 300,
                'ek_personel_yillik_fiyat' => 2880,
                'max_doktor_sayisi' => $row['max_doktor'],
                'max_ek_doktor' => $row['max_ek_doktor'],
                'max_personel_sayisi' => $row['max_personel'],
                'max_hasta_sayisi' => null,
                'max_randevu_sayisi' => null,
                'sms_aylik_kontor' => $row['sms'],
                'merkezi_finans_mi' => $row['merkezi_finans'],
                'toplu_randevu_mi' => $row['toplu_randevu'],
                'raporlama_mi' => $row['raporlama'],
                'hasta_havuzu_mi' => $row['hasta_havuzu'],
                'domain_dahil_mi' => $row['domain'],
                'domain_dahil_yil' => 1,
                'domain_dahil_tlds' => $row['domain'] ? ['com', 'net'] : null,
                'sira' => $row['sira'],
                'listeleme_oncelik' => 1,
                'one_cikan_mi' => $row['one_cikan'],
                'etiket' => $row['etiket'],
                'etiket_stil' => $row['etiket_stil'],
                'aktif_mi' => true,
                'deneme_gun' => null,
                'ozellikler' => PaketOzellikKatalogu::vitrinMetinleri($kodlar, $row['sms']),
            ];

            $paket = $this->upsertKlinik($row['ad'], $payload);
            $paket->sistemOzellikleri()->sync($ids($kodlar));
        }
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function upsertKlinik(string $preferredName, array $attrs): Paket
    {
        $paket = Paket::query()->where('tur', 'klinik')->where('ad', $preferredName)->first();

        if (! $paket) {
            $like = match ($preferredName) {
                'Klinik Başlangıç' => '%Başlangıç%',
                'Klinik Plus' => '%Plus%',
                'Klinik Profesyonel' => '%Profesyonel%',
                'Klinik Özel Web' => '%Web%',
                default => $preferredName,
            };
            $q = Paket::query()->where('tur', 'klinik')->where('ad', 'like', $like);
            if ($preferredName === 'Klinik Plus') {
                $q->where('ad', 'not like', '%Profesyonel%')
                    ->where('ad', 'not like', '%Başlangıç%')
                    ->where('ad', 'not like', '%Web%');
            }
            if ($preferredName === 'Klinik Profesyonel') {
                $q->where('ad', 'not like', '%Plus%');
            }
            $paket = $q->orderBy('id')->first();
        }

        if ($paket) {
            $paket->fill($attrs);
            $paket->save();
        } else {
            $paket = Paket::create($attrs);
        }

        return $paket->fresh();
    }
}
