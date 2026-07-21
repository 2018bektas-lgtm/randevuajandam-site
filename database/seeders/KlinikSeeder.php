<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Models\PaketOzelligi;
use Illuminate\Database\Seeder;

/**
 * Klinik paketleri — içerikler projedeki gerçek bayraklara göre:
 *
 * Bayraklar (klinik.paket middleware / hasPaketFlag):
 *   hasta_havuzu, toplu_randevu, merkezi_finans, raporlama, klinik_web_sitesi
 *
 * Limitler: max_doktor_sayisi, max_personel_sayisi
 * Muhasebeci personel girişi: merkezi_finans_mi açık paketlerde anlamlıdır.
 * Çoklu şube YOK — tek klinik / tek lokasyon.
 */
class KlinikSeeder extends Seeder
{
    public function run(): void
    {
        $webOzellik = PaketOzelligi::updateOrCreate(
            ['kod' => 'klinik_web_sitesi'],
            [
                'ad' => 'Klinik Web Sitesi',
                'aciklama' => 'Klinik markalı özel web sitesi, çok hekim vitrini ve online randevu.',
            ]
        );

        // 1) Klinik Başlangıç
        $this->upsertKlinik(
            preferredName: 'Klinik Başlangıç',
            aliases: ['%Başlangıç%'],
            attrs: [
                'aciklama' => 'Küçük klinik / muayenehane: ortak hasta havuzu, 3 hekim, 1 personel. Merkezi finans yok.',
                'aylik_fiyat' => 3600.00,
                'aylik_indirimli_fiyat' => 3000.00,
                'yillik_fiyat' => 35990.00,
                'yillik_indirimli_fiyat' => 29990.00,
                'ozellikler' => [
                    'Klinik paneli ve ayarlar',
                    'Ortak hasta havuzu (CRM)',
                    'Klinik takvimi (hekim randevuları)',
                    'Randevu talepleri listesi',
                    'En fazla 3 hekim',
                    'En fazla 1 sekreter / personel hesabı',
                    'Klinik duyuruları',
                    'Hekim davetiye ile ekleme',
                ],
                'max_doktor_sayisi' => 3,
                'max_personel_sayisi' => 1,
                'merkezi_finans_mi' => false,
                'toplu_randevu_mi' => false,
                'raporlama_mi' => false,
                'hasta_havuzu_mi' => true,
                'domain_dahil_mi' => false,
                'sira' => 1,
                'one_cikan_mi' => false,
                'etiket' => null,
                'etiket_stil' => null,
                'aktif_mi' => true,
            ],
            featureIds: []
        );

        // 2) Klinik Plus — 6 hekim, 2 personel + finans + toplu randevu
        $this->upsertKlinik(
            preferredName: 'Klinik Plus',
            aliases: ['%Klinik Plus%', '%Plus%'],
            attrs: [
                'aciklama' => 'Büyüyen klinikler: 6 hekim, 2 personel, merkezi finans ve muhasebeci girişi.',
                'aylik_fiyat' => 5400.00,
                'aylik_indirimli_fiyat' => 4500.00,
                'yillik_fiyat' => 53990.00,
                'yillik_indirimli_fiyat' => 44990.00,
                'ozellikler' => [
                    'Klinik Başlangıç özelliklerinin tümü',
                    'En fazla 6 hekim',
                    'En fazla 2 sekreter / personel',
                    'Merkezi finans (gelir-gider, klinik giderleri)',
                    'Muhasebeci personel girişi (finans yetkisi)',
                    'Hakediş / komisyon yönetimi',
                    'Toplu randevu işlemleri',
                ],
                'max_doktor_sayisi' => 6,
                'max_personel_sayisi' => 2,
                'merkezi_finans_mi' => true,
                'toplu_randevu_mi' => true,
                'raporlama_mi' => false,
                'hasta_havuzu_mi' => true,
                'domain_dahil_mi' => false,
                'sira' => 2,
                'one_cikan_mi' => false,
                'etiket' => null,
                'etiket_stil' => null,
                'aktif_mi' => true,
            ],
            featureIds: []
        );

        // 3) Klinik Profesyonel — + gelişmiş raporlar
        $this->upsertKlinik(
            preferredName: 'Klinik Profesyonel',
            aliases: ['%Profesyonel%'],
            attrs: [
                'aciklama' => 'Orta ölçek: 10 hekim, 5 personel, finans + hakediş + performans raporları.',
                'aylik_fiyat' => 7200.00,
                'aylik_indirimli_fiyat' => 6000.00,
                'yillik_fiyat' => 71990.00,
                'yillik_indirimli_fiyat' => 59990.00,
                'ozellikler' => [
                    'Klinik Plus özelliklerinin tümü',
                    'En fazla 10 hekim',
                    'En fazla 5 sekreter / personel',
                    'Merkezi finans ve muhasebeci girişi',
                    'Hakediş / komisyon yönetimi',
                    'Toplu randevu işlemleri',
                    'Klinik performans raporları (PDF dahil)',
                ],
                'max_doktor_sayisi' => 10,
                'max_personel_sayisi' => 5,
                'merkezi_finans_mi' => true,
                'toplu_randevu_mi' => true,
                'raporlama_mi' => true,
                'hasta_havuzu_mi' => true,
                'domain_dahil_mi' => false,
                'sira' => 3,
                'one_cikan_mi' => true,
                'etiket' => 'Önerilen',
                'etiket_stil' => 'popular',
                'aktif_mi' => true,
            ],
            featureIds: []
        );

        // 4) Klinik Özel Web Sitesi — sınırsız + web (çoklu şube YOK)
        $this->upsertKlinik(
            preferredName: 'Klinik Özel Web Sitesi',
            aliases: [
                '%Özel Web%',
                '%Kurumsal%',
                '%Web Sitesi%',
            ],
            attrs: [
                'aciklama' => 'Sınırsız hekim/personel + klinik web sitesi; 1 yıl domain dahil. Tek klinik (çoklu şube yok).',
                'aylik_fiyat' => 12000.00,
                'aylik_indirimli_fiyat' => 10000.00,
                'yillik_fiyat' => 119990.00,
                'yillik_indirimli_fiyat' => 99990.00,
                'ozellikler' => [
                    'Klinik Profesyonel özelliklerinin tümü',
                    'Sınırsız hekim',
                    'Sınırsız sekreter / personel',
                    'Merkezi finans, muhasebeci girişi, hakediş',
                    'Klinik performans raporları',
                    'Ortak hasta havuzu (tek klinik geneli)',
                    'Klinik web sitesi (CMS + tema seçimi)',
                    '1 yıl domain dahil (.com / .net)',
                    'Hosting ve SSL dahil',
                    'Çok hekimli vitrin ve hekim seçimli online randevu',
                    'Domain kurulum ve DNS yönlendirme adımları',
                ],
                'max_doktor_sayisi' => 999,
                'max_personel_sayisi' => 999,
                'merkezi_finans_mi' => true,
                'toplu_randevu_mi' => true,
                'raporlama_mi' => true,
                'hasta_havuzu_mi' => true,
                'domain_dahil_mi' => true,
                'domain_dahil_yil' => 1,
                'domain_dahil_tlds' => ['com', 'net'],
                'sira' => 4,
                'one_cikan_mi' => true,
                'etiket' => 'Web sitesi dahil',
                'etiket_stil' => 'web',
                'aktif_mi' => true,
            ],
            featureIds: [$webOzellik->id]
        );
    }

    /**
     * @param  list<string>  $aliases
     * @param  array<string, mixed>  $attrs
     * @param  list<int>  $featureIds
     */
    private function upsertKlinik(string $preferredName, array $aliases, array $attrs, array $featureIds): Paket
    {
        $paket = Paket::query()
            ->where('tur', 'klinik')
            ->where('ad', $preferredName)
            ->first();

        if (! $paket) {
            foreach ($aliases as $like) {
                $q = Paket::query()->where('tur', 'klinik')->where('ad', 'like', $like);
                if ($preferredName === 'Klinik Plus') {
                    $q->where('ad', 'not like', '%Profesyonel%')
                        ->where('ad', 'not like', '%Başlangıç%')
                        ->where('ad', 'not like', '%Web%')
                        ->where('ad', 'not like', '%Kurumsal%');
                }
                if (str_contains($preferredName, 'Web') || str_contains($preferredName, 'Kurumsal')) {
                    $q->where(function ($w) {
                        $w->where('ad', 'like', '%Web%')
                            ->orWhere('ad', 'like', '%Kurumsal%');
                    });
                }
                if ($preferredName === 'Klinik Profesyonel') {
                    $q->where('ad', 'not like', '%Plus%');
                }
                $paket = $q->orderBy('id')->first();
                if ($paket) {
                    break;
                }
            }
        }

        $payload = array_merge($attrs, [
            'ad' => $preferredName,
            'tur' => 'klinik',
            'max_hasta_sayisi' => $attrs['max_hasta_sayisi'] ?? null,
            'max_randevu_sayisi' => $attrs['max_randevu_sayisi'] ?? null,
        ]);

        if ($paket) {
            $paket->fill($payload);
            $paket->save();
        } else {
            $paket = Paket::create($payload);
        }

        $paket->sistemOzellikleri()->sync($featureIds);

        return $paket->fresh();
    }
}
