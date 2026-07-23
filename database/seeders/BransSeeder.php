<?php

namespace Database\Seeders;

use App\Models\Brans;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Türkiye tıbbi branş / uzmanlık listesi (TUK + diş + sağlık meslekleri).
 *
 * Çalıştırma:
 *   php artisan db:seed --class=BransSeeder
 *
 * Aynı ada sahip kayıt varsa atlanır/güncellenir (updateOrCreate).
 * Alternatif adlar (eski seeder isimleri) varsa mevcut kayda bağlanır.
 */
class BransSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->bransListesi();

        $created = 0;
        $updated = 0;

        foreach ($items as $item) {
            $ad = trim((string) ($item['ad'] ?? ''));
            if ($ad === '') {
                continue;
            }

            $aciklama = isset($item['aciklama']) ? trim((string) $item['aciklama']) : null;
            if ($aciklama === '') {
                $aciklama = null;
            }

            $aliases = $item['aliases'] ?? [];
            $existing = Brans::query()->where('ad', $ad)->first();

            if (! $existing && ! empty($aliases)) {
                $existing = Brans::query()->whereIn('ad', $aliases)->first();
            }

            if ($existing) {
                $payload = [];
                // Alternatif isimden kanonik ada taşı
                if ($existing->ad !== $ad) {
                    $payload['ad'] = $ad;
                    $payload['slug'] = $this->uniqueSlug($ad, $existing->id);
                }
                // Açıklama boşsa doldur; doluysa seeder ile güncelle (bilinçli senkron)
                if ($aciklama !== null) {
                    $payload['aciklama'] = $aciklama;
                }
                if ($payload !== []) {
                    $existing->update($payload);
                    $updated++;
                }
                continue;
            }

            Brans::create([
                'ad' => $ad,
                'slug' => $this->uniqueSlug($ad),
                'aciklama' => $aciklama,
            ]);
            $created++;
        }

        $this->command?->info("Branş seeder: {$created} eklendi, {$updated} güncellendi. Toplam tanımlı: ".Brans::count());
    }

    protected function uniqueSlug(string $ad, ?int $excludeId = null): string
    {
        $base = Str::slug($ad) ?: 'brans';
        $slug = $base;
        $i = 1;

        while (
            Brans::query()
                ->where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /**
     * @return list<array{ad: string, aciklama?: string, aliases?: list<string>}>
     */
    protected function bransListesi(): array
    {
        return [


            // ——— Dahili bilimler ———
            ['ad' => 'İç Hastalıkları', 'aciklama' => 'Erişkin iç hastalıkları; tanı, tedavi ve kronik hastalık yönetimi.', 'aliases' => ['Dahiliye (İç Hastalıkları)', 'Dahiliye']],
            ['ad' => 'Kardiyoloji', 'aciklama' => 'Kalp ve damar hastalıklarının tanı, tedavi ve takibi.'],
            ['ad' => 'Gastroenteroloji', 'aciklama' => 'Sindirim sistemi hastalıkları; endoskopik tanı ve tedavi yaklaşımları.'],
            ['ad' => 'Gastroenteroloji Cerrahisi', 'aciklama' => 'Sindirim sistemi tümör ve hastalıklarının cerrahi tedavisi.'],
            ['ad' => 'Nefroloji', 'aciklama' => 'Böbrek hastalıkları, hipertansiyon ve diyaliz hasta takibi.'],
            ['ad' => 'Endokrinoloji ve Metabolizma Hastalıkları', 'aciklama' => 'Diyabet, tiroid, obezite ve hormonal bozuklukların tanı ve tedavisi.'],
            ['ad' => 'Üreme Endokrinolojisi ve İnfertilite', 'aciklama' => 'Kısırlık, hormonal sorunlar ve üreme sağlığının tedavisi.'],
            ['ad' => 'Romatoloji', 'aciklama' => 'Eklem, bağ dokusu ve otoimmün romatizmal hastalıkların yönetimi.'],
            ['ad' => 'Hematoloji', 'aciklama' => 'Kan hastalıkları, anemi, pıhtılaşma bozuklukları ve hematolojik maligniteler.'],
            ['ad' => 'Tıbbi Onkoloji', 'aciklama' => 'Kanser tanı sonrası medikal onkolojik tedavi ve hasta takibi.'],
            ['ad' => 'Radyasyon Onkolojisi', 'aciklama' => 'Kanser tedavisinde radyoterapi planlama ve uygulama.'],
            ['ad' => 'Cerrahi Onkoloji', 'aciklama' => 'Kanser tümörlerinin cerrahi yolla çıkarılması ve onkolojik cerrahi.'],
            ['ad' => 'Jinekolojik Onkoloji Cerrahisi', 'aciklama' => 'Kadın üreme sistemi kanserlerinin cerrahi tedavisi.'],
            ['ad' => 'Alerji Hastalıkları', 'aciklama' => 'Alerjik hastalıklar, astım ve immün aracılıklı bozuklukların tanı-tedavisi.', 'aliases' => ['İmmünoloji ve Alerji Hastalıkları']],
            ['ad' => 'İmmünoloji', 'aciklama' => 'Bağışıklık sistemi hastalıkları ve immünodefisiyensi değerlendirmesi.'],
            ['ad' => 'Enfeksiyon Hastalıkları', 'aciklama' => 'Bakteriyel, viral ve diğer enfeksiyon hastalıklarının yönetimi.', 'aliases' => ['Enfeksiyon Hastalıkları ve Klinik Mikrobiyoloji']],
            ['ad' => 'Göğüs Hastalıkları', 'aciklama' => 'Akciğer ve solunum yolu hastalıklarının tanı ve tedavisi.'],
            ['ad' => 'Nöroloji', 'aciklama' => 'Beyin, omurilik ve sinir sistemi hastalıklarının tanı ve tedavisi.'],
            ['ad' => 'Psikiyatri', 'aciklama' => 'Ruhsal hastalıkların tanı, tedavi ve psikososyal desteği.'],
            ['ad' => 'Dermatoloji', 'aciklama' => 'Cilt, saç, tırnak hastalıkları ve cinsel yolla bulaşan hastalıklar.', 'aliases' => ['Dermatoloji ve Venereoloji', 'Cildiye (Dermatoloji)', 'Dermatoloji (Cildiye)', 'Cildiye']],
            ['ad' => 'Fiziksel Tıp ve Rehabilitasyon', 'aciklama' => 'Kas-iskelet sistemi hastalıkları, ağrı ve rehabilitasyon programları.', 'aliases' => ['Fizik Tedavi ve Rehabilitasyon']],
            ['ad' => 'Geriatri', 'aciklama' => 'Yaşlı sağlığı, çoklu hastalık yönetimi ve geriatrik değerlendirme.'],
            ['ad' => 'Algoloji', 'aciklama' => 'Kronik ve akut ağrının tanı, tedavi ve girişimsel yönetimi.', 'aliases' => ['Algoloji (Ağrı)']],
            ['ad' => 'Tıbbi Genetik', 'aciklama' => 'Kalıtsal hastalıklar, genetik danışmanlık ve genetik test yorumu.'],
            ['ad' => 'Perinatoloji', 'aciklama' => 'Yüksek riskli gebelik, fetal tıp ve anne-bebek sağlığı takibi.'],
            ['ad' => 'Androloji', 'aciklama' => 'Erkek üreme sistemi hastalıkları, cinsel işlev bozuklukları ve erkek kısırlığı.'],

            // ——— Cerrahi bilimler ———
            ['ad' => 'Genel Cerrahi', 'aciklama' => 'Karın, meme, tiroid ve genel cerrahi girişimler.'],
            ['ad' => 'Kalp ve Damar Cerrahisi', 'aciklama' => 'Kalp, damar ve büyük damar cerrahisi.'],
            ['ad' => 'Göğüs Cerrahisi', 'aciklama' => 'Akciğer, göğüs duvarı ve mediasten cerrahisi.'],
            ['ad' => 'Beyin ve Sinir Cerrahisi', 'aciklama' => 'Beyin, omurga ve sinir cerrahisi girişimleri.', 'aliases' => ['Nöroşirürji']],
            ['ad' => 'Ortopedi ve Travmatoloji', 'aciklama' => 'Kemik, eklem, kas ve travma cerrahisi.'],
            ['ad' => 'Üroloji', 'aciklama' => 'İdrar yolları ve erkek üreme sistemi hastalıkları ile cerrahisi.'],
            ['ad' => 'Plastik Rekonstrüktif ve Estetik Cerrahi', 'aciklama' => 'Rekonstrüktif ve estetik cerrahi uygulamalar.', 'aliases' => ['Plastik, Rekonstrüktif ve Estetik Cerrahi']],
            ['ad' => 'Kulak Burun Boğaz', 'aciklama' => 'Kulak, burun, boğaz ve baş-boyun hastalıkları.', 'aliases' => ['Kulak Burun Boğaz Hastalıkları', 'Kulak Burun Boğaz (KBB)', 'KBB']],
            ['ad' => 'Göz Hastalıkları', 'aciklama' => 'Göz ve görme sistemi hastalıklarının tanı ve tedavisi.'],
            ['ad' => 'Kadın Hastalıkları ve Doğum', 'aciklama' => 'Kadın sağlığı, doğum ve jinekolojik cerrahi.'],
            ['ad' => 'Çocuk Cerrahisi', 'aciklama' => 'Yenidoğan ve çocukluk çağı cerrahi hastalıkları.'],
            ['ad' => 'Çocuk Ürolojisi', 'aciklama' => 'Çocuklarda üriner sistem ve genital cerrahi hastalıklar.'],
            ['ad' => 'Çocuk Kalp ve Damar Cerrahisi', 'aciklama' => 'Çocuklarda doğumsal ve edinsel kalp damar cerrahisi.'],
            ['ad' => 'El Cerrahisi', 'aciklama' => 'El ve üst ekstremite travma ve rekonstrüktif cerrahisi.'],
            ['ad' => 'Ağız Diş ve Çene Cerrahisi', 'aciklama' => 'Gömülü diş, çene cerrahisi ve oral cerrahi girişimler.', 'aliases' => ['Ağız, Diş ve Çene Cerrahisi', 'Ağız, Yüz ve Çene Cerrahisi']],

            // ——— Çocuk sağlığı ———
            ['ad' => 'Çocuk Sağlığı ve Hastalıkları', 'aciklama' => 'Bebek, çocuk ve ergen sağlığı ile pediatrik hastalıklar.'],
            ['ad' => 'Çocuk Kardiyolojisi', 'aciklama' => 'Çocuklarda doğumsal ve edinsel kalp hastalıkları.'],
            ['ad' => 'Çocuk Gastroenterolojisi', 'aciklama' => 'Çocuklarda sindirim sistemi ve karaciğer hastalıkları.'],
            ['ad' => 'Çocuk Nefrolojisi', 'aciklama' => 'Çocuklarda böbrek ve idrar yolu hastalıkları.'],
            ['ad' => 'Çocuk Endokrinolojisi', 'aciklama' => 'Çocuklarda büyüme, diyabet ve hormonal bozukluklar.'],
            ['ad' => 'Çocuk Nörolojisi', 'aciklama' => 'Çocuklarda epilepsi, gelişimsel ve nörolojik hastalıklar.'],
            ['ad' => 'Çocuk Hematolojisi', 'aciklama' => 'Çocuklarda kan hastalıkları, anemi ve pıhtılaşma bozuklukları.', 'aliases' => ['Çocuk Hematolojisi ve Onkolojisi']],
            ['ad' => 'Çocuk Onkolojisi', 'aciklama' => 'Çocukluk çağı kanserlerinin tanı ve tedavisi.'],
            ['ad' => 'Çocuk Enfeksiyon Hastalıkları', 'aciklama' => 'Çocukluk çağı enfeksiyon hastalıklarının yönetimi.'],
            ['ad' => 'Çocuk Göğüs Hastalıkları', 'aciklama' => 'Çocuklarda solunum yolu ve akciğer hastalıkları.'],
            ['ad' => 'Çocuk Alerjisi', 'aciklama' => 'Çocuklarda alerjik hastalıklar, astım ve besin alerjileri.', 'aliases' => ['Çocuk Alerjisi ve İmmünolojisi']],
            ['ad' => 'Çocuk İmmünolojisi', 'aciklama' => 'Çocuklarda bağışıklık sistemi hastalıkları ve immünodefisiyensi.'],
            ['ad' => 'Çocuk Romatolojisi', 'aciklama' => 'Çocuklarda romatizmal ve otoimmün hastalıklar.'],
            ['ad' => 'Çocuk Metabolizma Hastalıkları', 'aciklama' => 'Çocuklarda kalıtsal metabolizma bozuklukları ve enzim eksiklikleri.'],
            ['ad' => 'Neonatoloji', 'aciklama' => 'Yenidoğan yoğun bakım ve prematüre bebek takibi.'],
            ['ad' => 'Çocuk ve Ergen Psikiyatrisi', 'aciklama' => 'Çocuk ve ergenlerde ruhsal hastalıkların tanı ve tedavisi.', 'aliases' => ['Çocuk ve Ergen Ruh Sağlığı ve Hastalıkları', 'Çocuk Psikiyatrisi']],
            ['ad' => 'Çocuk Gelişimi', 'aciklama' => 'Çocuk gelişimi, gelişimsel gecikme değerlendirmesi ve izlem.', 'aliases' => ['Gelişimsel Pediatri']],

            // ——— Anestezi / görüntüleme / laboratuvar ———
            ['ad' => 'Anesteziyoloji ve Reanimasyon', 'aciklama' => 'Ameliyat anestezisi, ağrı kontrolü ve reanimasyon.'],
            ['ad' => 'Radyoloji', 'aciklama' => 'Görüntüleme ile tanı; röntgen, USG, BT, MR değerlendirmesi.'],
            ['ad' => 'Girişimsel Radyoloji', 'aciklama' => 'Görüntüleme eşliğinde minimal invaziv tanı ve tedavi işlemleri.'],
            ['ad' => 'Nükleer Tıp', 'aciklama' => 'Radyofarmasötiklerle görüntüleme ve tedavi uygulamaları.'],
            ['ad' => 'Tıbbi Patoloji', 'aciklama' => 'Doku ve hücre örneklerinin patolojik incelemesi.'],
            ['ad' => 'Tıbbi Biyokimya', 'aciklama' => 'Laboratuvar biyokimyası ve metabolik test yorumu.'],
            ['ad' => 'Tıbbi Mikrobiyoloji', 'aciklama' => 'Enfeksiyon etkenlerinin laboratuvar tanısı.'],
            ['ad' => 'Tıbbi Farmakoloji', 'aciklama' => 'İlaç bilimi, farmakokinetik ve klinik ilaç değerlendirmesi.'],
            ['ad' => 'Fizyoloji', 'aciklama' => 'İnsan vücut işlevlerinin değerlendirmesi ve klinik fizyoloji uygulamaları.'],

            // ——— Diş hekimliği ———
            ['ad' => 'Diş Hekimi', 'aciklama' => 'Genel ağız ve diş sağlığı muayene, tedavi ve koruyucu uygulamalar.', 'aliases' => ['Diş Hekimliği']],
            ['ad' => 'Ağız Diş ve Çene Radyolojisi', 'aciklama' => 'Dental görüntüleme ve ağız-çene radyolojik değerlendirme.', 'aliases' => ['Ağız, Diş ve Çene Radyolojisi', 'Oral Diagnoz ve Radyoloji']],
            ['ad' => 'Ortodonti', 'aciklama' => 'Diş ve çene düzensizliklerinin ortodontik tedavisi.'],
            ['ad' => 'Çocuk Diş Hekimliği (Pedodonti)', 'aciklama' => 'Çocuk diş hekimliği ve koruyucu pedodontik uygulamalar.', 'aliases' => ['Pedodonti', 'Pedodonti (Çocuk Diş Hekimliği)']],
            ['ad' => 'Periodontoloji', 'aciklama' => 'Diş eti hastalıkları ve periodonsiyum tedavisi.'],
            ['ad' => 'Endodonti', 'aciklama' => 'Kök kanal tedavisi ve diş pulpası hastalıkları.'],
            ['ad' => 'Protetik Diş Tedavisi', 'aciklama' => 'Protez, kaplama ve diş eksikliği restorasyonları.'],
            ['ad' => 'Restoratif Diş Tedavisi', 'aciklama' => 'Dolgu, estetik restorasyon ve diş dokusu onarımı.'],
            ['ad' => 'Diş Hastalıkları ve Tedavisi', 'aciklama' => 'Diş çürükleri, hassasiyet ve diş dokusu hastalıklarının tedavisi.'],

            // ——— Sağlık meslekleri / danışmanlık ———
            ['ad' => 'Psikoloji', 'aciklama' => 'Psikolojik değerlendirme, danışmanlık ve destek süreçleri.'],
            ['ad' => 'Psikolojik Danışma ve Rehberlik', 'aciklama' => 'Bireysel, eğitimsel ve kariyer odaklı psikolojik danışmanlık.', 'aliases' => ['Psikolojik Danışmanlık ve Rehberlik']],
            ['ad' => 'Aile Danışmanlığı', 'aciklama' => 'Çift, aile ve ilişki sorunlarında danışmanlık desteği.'],
            ['ad' => 'Pedagoji', 'aciklama' => 'Eğitim, öğrenme güçlükleri ve çocuk gelişimi danışmanlığı.'],
            ['ad' => 'Diyetisyen', 'aciklama' => 'Beslenme danışmanlığı, diyet programı ve metabolik sağlık değerlendirmesi.'],
            ['ad' => 'Fizyoterapi ve Rehabilitasyon', 'aciklama' => 'Hareket, rehabilitasyon ve fizyoterapi uygulamaları.', 'aliases' => ['Fizyoterapi']],
            ['ad' => 'Ergoterapi', 'aciklama' => 'Günlük yaşam aktivitelerine yönelik fonksiyonel rehabilitasyon ve destek.'],
            ['ad' => 'Dil ve Konuşma Terapisi', 'aciklama' => 'Konuşma bozuklukları, dil gelişim geriliği ve yutma sorularının tedavisi.'],
            ['ad' => 'Odyoloji (Dil, Konuşma ve Ses Bozuklukları)', 'aciklama' => 'İşitme kaybı, tinnitus ve ses bozukluklarının tanı ve rehabilitasyonu.'],

            // ——— Tamamlayıcı tıp ———
            ['ad' => 'Akupunktur', 'aciklama' => 'Geleneksel Çin tıbbına dayalı akupunktur tedavisi; ağrı ve kronik hastalıklarda uygulanır.'],
            ['ad' => 'Ozon Terapi', 'aciklama' => 'Tıbbi ozon gazının uygulanmasıyla yapılan tedavi yöntemi.'],
            ['ad' => 'Mezoterapi', 'aciklama' => 'Deri altı mikroenjeksiyonla uygulanan ağrı, selülit ve saç dökülmesi tedavileri.'],
            ['ad' => 'Fitoterapi', 'aciklama' => 'Bitkisel kaynaklı ilaç ve ürünlerin terapötik kullanımı.'],
            ['ad' => 'Proloterapi', 'aciklama' => 'Kronik kas-iskelet ağrısında bağ doku iyileşmesini uyaran enjeksiyon tedavisi.'],

            // ——— Medikal estetik ———
            ['ad' => 'Sertifikalı Medikal Estetik', 'aciklama' => 'Medikal estetik sertifikasına sahip hekimler tarafından uygulanan estetik işlemler.', 'aliases' => ['Estetik ve Kozmetik Uygulamalar', 'Saç Ekimi ve Medikal Estetik']],
        ];
    }
}
