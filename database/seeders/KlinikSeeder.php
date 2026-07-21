<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Models\PaketOzelligi;
use Illuminate\Database\Seeder;

/**
 * Klinik paketleri.
 *
 * ÖNEMLİ: Kliniğe bağlı hekim girişinde aktifPaket() = klinik paketi.
 * Bu yüzden klinik paketlerine hekim paneli sistem özellikleri de bağlanır;
 * aksi halde hekim menüsü (hakkımda, galeri, talep, finans…) kilitlenir.
 *
 * Klinik bayrakları (hasPaketFlag):
 *   hasta_havuzu, toplu_randevu, merkezi_finans, raporlama, klinik_web_sitesi
 *
 * Limitler: max_doktor_sayisi, max_personel_sayisi
 * Çoklu şube YOK.
 */
class KlinikSeeder extends Seeder
{
    public function run(): void
    {
        $ozellikMap = [];
        foreach ([
            ['kod' => 'hakkimda', 'ad' => 'Hakkımda / Özgeçmiş Yönetimi', 'aciklama' => 'Detaylı özgeçmiş ve mezuniyet bilgisi ekleme yetkisi.'],
            ['kod' => 'galeri', 'ad' => 'Fotoğraf Galerisi Modülü', 'aciklama' => 'Klinik / muayenehane resimleri yükleme yetkisi.'],
            ['kod' => 'randevu_talepleri', 'ad' => 'Danışan Randevu Talepleri Modülü', 'aciklama' => 'Beklemedeki randevu taleplerini yönetme yetkisi.'],
            ['kod' => 'finans', 'ad' => 'Gelir / Gider Raporlaması', 'aciklama' => 'Hekim paneli finans takibi.'],
            ['kod' => 'blog', 'ad' => 'Blog / Makale Paneli', 'aciklama' => 'Blog ve sağlık makalesi yayınlama yetkisi.'],
            ['kod' => 'faq', 'ad' => 'Sıkça Sorulan Sorular Modülü', 'aciklama' => 'Profilde S.S.S. yayınlama yetkisi.'],
            ['kod' => 'egitimler', 'ad' => 'Eğitimler & Başvuru Formu', 'aciklama' => 'Kurs/webinar vitrini ve başvuru formu.'],
            ['kod' => 'online_gorusme', 'ad' => 'Online Görüşme', 'aciklama' => 'Platform görüntülü görüşme odası.'],
            ['kod' => 'klinik_web_sitesi', 'ad' => 'Klinik Web Sitesi', 'aciklama' => 'Klinik markalı özel web sitesi, çok hekim vitrini ve online randevu.'],
            ['kod' => 'web_sitesi', 'ad' => 'Özel Web Sitesi Entegrasyonu', 'aciklama' => 'Kişisel hekim web sitesi (bireysel paket).'],
        ] as $row) {
            $ozellikMap[$row['kod']] = PaketOzelligi::updateOrCreate(['kod' => $row['kod']], $row);
        }

        // Kliniğe bağlı her hekimin paneli (takvim/hasta dışında gated modüller)
        $hekimPanel = [
            'hakkimda',
            'galeri',
            'randevu_talepleri',
            'finans',
            'blog',
            'faq',
            'egitimler',
            'online_gorusme',
        ];

        $ids = fn (array $kodlar) => array_values(array_filter(array_map(
            fn ($k) => $ozellikMap[$k]->id ?? null,
            $kodlar
        )));

        $hekimPanelMetin = [
            'Her hekim paneli: randevu takvimi, hasta, hizmet, çalışma saatleri',
            'Her hekim: hakkımda, galeri, randevu talepleri',
            'Her hekim: kişisel finans (gelir/gider), blog, S.S.S., eğitimler',
            'Her hekim: online görüntülü görüşme',
            'Hızlı slot kapatma, bekleme listesi, yorum yanıtlama',
        ];

        // 1) Klinik Başlangıç
        $this->upsertKlinik(
            preferredName: 'Klinik Başlangıç',
            aliases: ['%Başlangıç%'],
            attrs: [
                'aciklama' => '3 hekim / 1 personel. Ortak hasta havuzu. Merkezi klinik finans yok; her hekim kendi paneline erişir.',
                'aylik_fiyat' => 3600.00,
                'aylik_indirimli_fiyat' => 3000.00,
                'yillik_fiyat' => 35990.00,
                'yillik_indirimli_fiyat' => 29990.00,
                'ozellikler' => array_merge([
                    'En fazla 3 hekim + 1 sekreter/personel',
                    'Klinik paneli (sahip): ayarlar, hekim daveti, duyuru',
                    'Ortak hasta havuzu',
                    'Klinik takvimi',
                    'Randevu talepleri (klinik görünümü)',
                ], $hekimPanelMetin, [
                    'Merkezi klinik finans / hakediş: yok (Plus ve üzeri)',
                    'Gelişmiş klinik raporlar: yok',
                    'Klinik web sitesi: yok',
                ]),
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
            featureIds: $ids($hekimPanel)
        );

        // 2) Klinik Plus
        $this->upsertKlinik(
            preferredName: 'Klinik Plus',
            aliases: ['%Klinik Plus%', '%Plus%'],
            attrs: [
                'aciklama' => '6 hekim / 2 personel. Merkezi finans + muhasebeci + toplu randevu. Hekim panelleri açık.',
                'aylik_fiyat' => 5400.00,
                'aylik_indirimli_fiyat' => 4500.00,
                'yillik_fiyat' => 53990.00,
                'yillik_indirimli_fiyat' => 44990.00,
                'ozellikler' => array_merge([
                    'En fazla 6 hekim + 2 sekreter/personel',
                    'Klinik paneli: hekim/personel yönetimi, duyuru',
                    'Ortak hasta havuzu',
                    'Merkezi klinik finans (gelir-gider, klinik giderleri)',
                    'Muhasebeci personel girişi',
                    'Hakediş / komisyon yönetimi',
                    'Toplu randevu işlemleri',
                ], $hekimPanelMetin, [
                    'Gelişmiş klinik raporlar: yok (Profesyonel ve üzeri)',
                    'Klinik web sitesi: yok',
                ]),
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
            featureIds: $ids($hekimPanel)
        );

        // 3) Klinik Profesyonel
        $this->upsertKlinik(
            preferredName: 'Klinik Profesyonel',
            aliases: ['%Profesyonel%'],
            attrs: [
                'aciklama' => '10 hekim / 5 personel. Finans + hakediş + performans raporları. Hekim panelleri açık.',
                'aylik_fiyat' => 7200.00,
                'aylik_indirimli_fiyat' => 6000.00,
                'yillik_fiyat' => 71990.00,
                'yillik_indirimli_fiyat' => 59990.00,
                'ozellikler' => array_merge([
                    'En fazla 10 hekim + 5 sekreter/personel',
                    'Klinik Plus klinik paneli yetkilerinin tümü',
                    'Klinik performans raporları (PDF)',
                    'Merkezi finans, muhasebeci, hakediş, toplu randevu',
                ], $hekimPanelMetin, [
                    'Klinik web sitesi: yok (Özel Web paketi)',
                ]),
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
            featureIds: $ids($hekimPanel)
        );

        // 4) Klinik Özel Web Sitesi
        $this->upsertKlinik(
            preferredName: 'Klinik Özel Web Sitesi',
            aliases: ['%Özel Web%', '%Kurumsal%', '%Web Sitesi%'],
            attrs: [
                'aciklama' => 'Sınırsız hekim/personel + klinik web sitesi. Tek klinik (çoklu şube yok). Hekim panelleri açık.',
                'aylik_fiyat' => 12000.00,
                'aylik_indirimli_fiyat' => 10000.00,
                'yillik_fiyat' => 119990.00,
                'yillik_indirimli_fiyat' => 99990.00,
                'ozellikler' => array_merge([
                    'Sınırsız hekim ve personel',
                    'Klinik Profesyonel klinik paneli yetkilerinin tümü',
                    'Klinik web sitesi (CMS, tema, çok hekimli vitrin)',
                    '1 yıl domain (.com/.net), hosting, SSL',
                    'Siteden hekim seçimli online randevu',
                    'Ortak hasta havuzu (tek klinik geneli)',
                ], $hekimPanelMetin),
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
            featureIds: $ids(array_merge($hekimPanel, ['klinik_web_sitesi']))
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
