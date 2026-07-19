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
            // ——— Temel / birinci basamak ———
            ['ad' => 'Aile Hekimliği', 'aciklama' => 'Her yaş grubunda koruyucu hekimlik, kronik hastalık takibi ve birinci basamak sağlık hizmeti.', 'aliases' => ['Genel Pratisyen']],
            ['ad' => 'Acil Tıp', 'aciklama' => 'Ani gelişen hastalık ve yaralanmalarda acil değerlendirme, stabilizasyon ve tedavi.'],
            ['ad' => 'İş ve Meslek Hastalıkları', 'aciklama' => 'Mesleki maruziyetlere bağlı hastalıkların tanı, tedavi ve önlenmesi.'],
            ['ad' => 'Halk Sağlığı', 'aciklama' => 'Toplum sağlığı, epidemiyoloji, koruyucu hekimlik ve sağlık yönetimi.'],
            ['ad' => 'Adli Tıp', 'aciklama' => 'Hukuki süreçlere yönelik tıbbi inceleme, raporlama ve adli değerlendirme.'],
            ['ad' => 'Spor Hekimliği', 'aciklama' => 'Spor yaralanmaları, performans ve sporcu sağlığı değerlendirmesi.'],
            ['ad' => 'Sualtı Hekimliği ve Hiperbarik Tıp', 'aciklama' => 'Dalış ve hiperbarik oksijen tedavisi ile ilgili tıbbi değerlendirme.'],
            ['ad' => 'Havacılık Tıbbı', 'aciklama' => 'Pilot ve hava personeli sağlık muayeneleri ve uçuşa elverişlilik değerlendirmesi.'],

            // ——— Dahili bilimler ———
            ['ad' => 'İç Hastalıkları', 'aciklama' => 'Erişkin iç hastalıkları; tanı, tedavi ve kronik hastalık yönetimi.', 'aliases' => ['Dahiliye (İç Hastalıkları)', 'Dahiliye']],
            ['ad' => 'Kardiyoloji', 'aciklama' => 'Kalp ve damar hastalıklarının tanı, tedavi ve takibi.'],
            ['ad' => 'Gastroenteroloji', 'aciklama' => 'Sindirim sistemi hastalıkları; endoskopik tanı ve tedavi yaklaşımları.'],
            ['ad' => 'Nefroloji', 'aciklama' => 'Böbrek hastalıkları, hipertansiyon ve diyaliz hasta takibi.'],
            ['ad' => 'Endokrinoloji ve Metabolizma Hastalıkları', 'aciklama' => 'Diyabet, tiroid, obezite ve hormonal bozuklukların tanı ve tedavisi.'],
            ['ad' => 'Romatoloji', 'aciklama' => 'Eklem, bağ dokusu ve otoimmün romatizmal hastalıkların yönetimi.'],
            ['ad' => 'Hematoloji', 'aciklama' => 'Kan hastalıkları, anemi, pıhtılaşma bozuklukları ve hematolojik maligniteler.'],
            ['ad' => 'Tıbbi Onkoloji', 'aciklama' => 'Kanser tanı sonrası medikal onkolojik tedavi ve hasta takibi.'],
            ['ad' => 'Radyasyon Onkolojisi', 'aciklama' => 'Kanser tedavisinde radyoterapi planlama ve uygulama.'],
            ['ad' => 'İmmünoloji ve Alerji Hastalıkları', 'aciklama' => 'Alerjik hastalıklar ve bağışıklık sistemi bozukluklarının tanı-tedavisi.'],
            ['ad' => 'Enfeksiyon Hastalıkları ve Klinik Mikrobiyoloji', 'aciklama' => 'Bakteriyel, viral ve diğer enfeksiyon hastalıklarının yönetimi.'],
            ['ad' => 'Göğüs Hastalıkları', 'aciklama' => 'Akciğer ve solunum yolu hastalıklarının tanı ve tedavisi.'],
            ['ad' => 'Nöroloji', 'aciklama' => 'Beyin, omurilik ve sinir sistemi hastalıklarının tanı ve tedavisi.'],
            ['ad' => 'Psikiyatri', 'aciklama' => 'Ruhsal hastalıkların tanı, tedavi ve psikososyal desteği.'],
            ['ad' => 'Dermatoloji ve Venereoloji', 'aciklama' => 'Cilt, saç, tırnak hastalıkları ve cinsel yolla bulaşan hastalıklar.', 'aliases' => ['Cildiye (Dermatoloji)', 'Dermatoloji (Cildiye)', 'Dermatoloji', 'Cildiye']],
            ['ad' => 'Fiziksel Tıp ve Rehabilitasyon', 'aciklama' => 'Kas-iskelet sistemi hastalıkları, ağrı ve rehabilitasyon programları.', 'aliases' => ['Fizik Tedavi ve Rehabilitasyon']],
            ['ad' => 'Geriatri', 'aciklama' => 'Yaşlı sağlığı, çoklu hastalık yönetimi ve geriatrik değerlendirme.'],
            ['ad' => 'Algoloji', 'aciklama' => 'Kronik ve akut ağrının tanı, tedavi ve girişimsel yönetimi.', 'aliases' => ['Algoloji (Ağrı)']],
            ['ad' => 'Yoğun Bakım', 'aciklama' => 'Kritik hastaların yoğun bakımda izlem ve tedavisi.'],
            ['ad' => 'Klinik Farmakoloji ve Tedavi', 'aciklama' => 'İlaç tedavisi optimizasyonu, ilaç etkileşimleri ve akılcı ilaç kullanımı.'],
            ['ad' => 'Tıbbi Genetik', 'aciklama' => 'Kalıtsal hastalıklar, genetik danışmanlık ve genetik test yorumu.'],
            ['ad' => 'Tıbbi Ekoloji ve Hidroklimatoloji', 'aciklama' => 'Çevre, iklim ve kaplıca tıbbı uygulamaları.'],

            // ——— Cerrahi bilimler ———
            ['ad' => 'Genel Cerrahi', 'aciklama' => 'Karın, meme, tiroid ve genel cerrahi girişimler.'],
            ['ad' => 'Kalp ve Damar Cerrahisi', 'aciklama' => 'Kalp, damar ve büyük damar cerrahisi.'],
            ['ad' => 'Göğüs Cerrahisi', 'aciklama' => 'Akciğer, göğüs duvarı ve mediasten cerrahisi.'],
            ['ad' => 'Beyin ve Sinir Cerrahisi', 'aciklama' => 'Beyin, omurga ve sinir cerrahisi girişimleri.', 'aliases' => ['Nöroşirürji']],
            ['ad' => 'Ortopedi ve Travmatoloji', 'aciklama' => 'Kemik, eklem, kas ve travma cerrahisi.'],
            ['ad' => 'Üroloji', 'aciklama' => 'İdrar yolları ve erkek üreme sistemi hastalıkları ile cerrahisi.'],
            ['ad' => 'Plastik, Rekonstrüktif ve Estetik Cerrahi', 'aciklama' => 'Rekonstrüktif ve estetik cerrahi uygulamalar.'],
            ['ad' => 'Kulak Burun Boğaz Hastalıkları', 'aciklama' => 'Kulak, burun, boğaz ve baş-boyun hastalıkları.', 'aliases' => ['Kulak Burun Boğaz (KBB)', 'KBB']],
            ['ad' => 'Göz Hastalıkları', 'aciklama' => 'Göz ve görme sistemi hastalıklarının tanı ve tedavisi.'],
            ['ad' => 'Kadın Hastalıkları ve Doğum', 'aciklama' => 'Kadın sağlığı, doğum ve jinekolojik cerrahi.'],
            ['ad' => 'Çocuk Cerrahisi', 'aciklama' => 'Yenidoğan ve çocukluk çağı cerrahi hastalıkları.'],
            ['ad' => 'Çocuk Ürolojisi', 'aciklama' => 'Çocuklarda üriner sistem ve genital cerrahi hastalıklar.'],
            ['ad' => 'El Cerrahisi', 'aciklama' => 'El ve üst ekstremite travma ve rekonstrüktif cerrahisi.'],
            ['ad' => 'Ağız, Yüz ve Çene Cerrahisi', 'aciklama' => 'Çene yüz bölgesi travma, tümör ve rekonstrüktif cerrahi.'],

            // ——— Çocuk sağlığı ———
            ['ad' => 'Çocuk Sağlığı ve Hastalıkları', 'aciklama' => 'Bebek, çocuk ve ergen sağlığı ile pediatrik hastalıklar.'],
            ['ad' => 'Çocuk Kardiyolojisi', 'aciklama' => 'Çocuklarda doğumsal ve edinsel kalp hastalıkları.'],
            ['ad' => 'Çocuk Gastroenterolojisi', 'aciklama' => 'Çocuklarda sindirim sistemi ve karaciğer hastalıkları.'],
            ['ad' => 'Çocuk Nefrolojisi', 'aciklama' => 'Çocuklarda böbrek ve idrar yolu hastalıkları.'],
            ['ad' => 'Çocuk Endokrinolojisi', 'aciklama' => 'Çocuklarda büyüme, diyabet ve hormonal bozukluklar.'],
            ['ad' => 'Çocuk Nörolojisi', 'aciklama' => 'Çocuklarda epilepsi, gelişimsel ve nörolojik hastalıklar.'],
            ['ad' => 'Çocuk Hematolojisi ve Onkolojisi', 'aciklama' => 'Çocuklarda kan hastalıkları ve kanser tedavisi.'],
            ['ad' => 'Çocuk Enfeksiyon Hastalıkları', 'aciklama' => 'Çocukluk çağı enfeksiyon hastalıklarının yönetimi.'],
            ['ad' => 'Çocuk Göğüs Hastalıkları', 'aciklama' => 'Çocuklarda solunum yolu ve akciğer hastalıkları.'],
            ['ad' => 'Çocuk Alerjisi ve İmmünolojisi', 'aciklama' => 'Çocuklarda alerji ve bağışıklık sistemi hastalıkları.'],
            ['ad' => 'Çocuk Romatolojisi', 'aciklama' => 'Çocuklarda romatizmal ve otoimmün hastalıklar.'],
            ['ad' => 'Neonatoloji', 'aciklama' => 'Yenidoğan yoğun bakım ve prematüre bebek takibi.'],
            ['ad' => 'Çocuk ve Ergen Ruh Sağlığı ve Hastalıkları', 'aciklama' => 'Çocuk ve ergenlerde ruhsal hastalıkların tanı ve tedavisi.', 'aliases' => ['Çocuk Psikiyatrisi']],
            ['ad' => 'Gelişimsel Pediatri', 'aciklama' => 'Çocuk gelişimi, gelişimsel gecikme ve izlem.'],
            ['ad' => 'Sosyal Pediatri', 'aciklama' => 'Çocuk sağlığında sosyal risk, koruma ve destek.'],

            // ——— Anestezi / görüntüleme / laboratuvar ———
            ['ad' => 'Anesteziyoloji ve Reanimasyon', 'aciklama' => 'Ameliyat anestezisi, ağrı kontrolü ve reanimasyon.'],
            ['ad' => 'Radyoloji', 'aciklama' => 'Görüntüleme ile tanı; röntgen, USG, BT, MR değerlendirmesi.'],
            ['ad' => 'Girişimsel Radyoloji', 'aciklama' => 'Görüntüleme eşliğinde minimal invaziv tanı ve tedavi işlemleri.'],
            ['ad' => 'Nükleer Tıp', 'aciklama' => 'Radyofarmasötiklerle görüntüleme ve tedavi uygulamaları.'],
            ['ad' => 'Tıbbi Patoloji', 'aciklama' => 'Doku ve hücre örneklerinin patolojik incelemesi.'],
            ['ad' => 'Tıbbi Biyokimya', 'aciklama' => 'Laboratuvar biyokimyası ve metabolik test yorumu.'],
            ['ad' => 'Tıbbi Mikrobiyoloji', 'aciklama' => 'Enfeksiyon etkenlerinin laboratuvar tanısı.'],
            ['ad' => 'Tıbbi Farmakoloji', 'aciklama' => 'İlaç bilimi, farmakokinetik ve klinik ilaç değerlendirmesi.'],

            // ——— Diş hekimliği ———
            ['ad' => 'Diş Hekimliği', 'aciklama' => 'Genel ağız ve diş sağlığı muayene, tedavi ve koruyucu uygulamalar.'],
            ['ad' => 'Ağız, Diş ve Çene Cerrahisi', 'aciklama' => 'Gömülü diş, çene cerrahisi ve oral cerrahi girişimler.'],
            ['ad' => 'Ortodonti', 'aciklama' => 'Diş ve çene düzensizliklerinin ortodontik tedavisi.'],
            ['ad' => 'Pedodonti', 'aciklama' => 'Çocuk diş hekimliği ve koruyucu pedodontik uygulamalar.', 'aliases' => ['Pedodonti (Çocuk Diş Hekimliği)']],
            ['ad' => 'Periodontoloji', 'aciklama' => 'Diş eti hastalıkları ve periodonsiyum tedavisi.'],
            ['ad' => 'Endodonti', 'aciklama' => 'Kök kanal tedavisi ve diş pulpası hastalıkları.'],
            ['ad' => 'Protetik Diş Tedavisi', 'aciklama' => 'Protez, kaplama ve diş eksikliği restorasyonları.'],
            ['ad' => 'Restoratif Diş Tedavisi', 'aciklama' => 'Dolgu, estetik restorasyon ve diş dokusu onarımı.'],
            ['ad' => 'Ağız, Diş ve Çene Radyolojisi', 'aciklama' => 'Dental görüntüleme ve ağız-çene radyolojik değerlendirme.'],
            ['ad' => 'Oral Diagnoz ve Radyoloji', 'aciklama' => 'Ağız hastalıklarının tanı ve görüntüleme değerlendirmesi.'],

            // ——— Sağlık meslekleri / danışmanlık (randevu platformu) ———
            ['ad' => 'Psikoloji', 'aciklama' => 'Psikolojik değerlendirme, danışmanlık ve destek süreçleri.'],
            ['ad' => 'Klinik Psikoloji', 'aciklama' => 'Klinik değerlendirme, psikoterapi ve ruh sağlığı desteği.'],
            ['ad' => 'Psikolojik Danışmanlık ve Rehberlik', 'aciklama' => 'Bireysel, eğitimsel ve kariyer odaklı psikolojik danışmanlık.'],
            ['ad' => 'Aile Danışmanlığı', 'aciklama' => 'Çift, aile ve ilişki sorunlarında danışmanlık desteği.'],
            ['ad' => 'Beslenme ve Diyetetik', 'aciklama' => 'Beslenme planı, diyet danışmanlığı ve metabolik destek.', 'aliases' => ['Diyetisyen (Beslenme ve Diyetetik)', 'Diyetisyen']],
            ['ad' => 'Fizyoterapi ve Rehabilitasyon', 'aciklama' => 'Hareket, rehabilitasyon ve fizyoterapi uygulamaları.', 'aliases' => ['Fizyoterapi']],
            ['ad' => 'Dil ve Konuşma Terapisi', 'aciklama' => 'Konuşma, dil, ses ve yutma bozukluklarının terapisi.'],
            ['ad' => 'Ergoterapi', 'aciklama' => 'Günlük yaşam aktiviteleri ve işlevsel bağımsızlığı destekleyen terapi.'],
            ['ad' => 'Odyoloji', 'aciklama' => 'İşitme ve denge değerlendirmesi ile odyolojik destek.'],
            ['ad' => 'Optisyenlik', 'aciklama' => 'Gözlük ve optik ürün danışmanlığı, optik ölçüm ve uygulama.'],
            ['ad' => 'Podoloji', 'aciklama' => 'Ayak sağlığı, tırnak ve deri sorunlarında podolojik bakım.'],
            ['ad' => 'Ebelik', 'aciklama' => 'Gebelik, doğum ve lohusalık döneminde ebelik desteği.'],
            ['ad' => 'Hemşirelik', 'aciklama' => 'Hemşirelik bakımı, eğitim ve sağlık danışmanlığı.'],
            ['ad' => 'Çocuk Gelişimi', 'aciklama' => 'Çocuk gelişim değerlendirmesi ve gelişimsel destek programları.'],
            ['ad' => 'Odyometri', 'aciklama' => 'İşitme testleri ve odyometrik ölçüm uygulamaları.'],
            ['ad' => 'Perfüzyon', 'aciklama' => 'Kalp-damar cerrahisinde ekstrakorporeal dolaşım desteği.'],
            ['ad' => 'Anestezi Teknisyenliği', 'aciklama' => 'Anestezi uygulamalarında teknik destek (yetki sınırları dahilinde).'],
            ['ad' => 'Acil Tıp Teknisyenliği', 'aciklama' => 'Acil sağlık hizmetlerinde saha ve ambulans desteği.'],
            ['ad' => 'Radyoterapi Teknisyenliği', 'aciklama' => 'Radyoterapi cihazları ve tedavi seanslarında teknik destek.'],
            ['ad' => 'Tıbbi Görüntüleme Teknisyenliği', 'aciklama' => 'Röntgen, BT, MR vb. görüntüleme işlemlerinde teknik uygulama.'],
            ['ad' => 'Laboratuvar Teknisyenliği', 'aciklama' => 'Tıbbi laboratuvar numune analizi ve teknik süreçler.'],
            ['ad' => 'Ortez-Protez', 'aciklama' => 'Ortez ve protez uygulama, uyum ve hasta eğitimi.'],
            ['ad' => 'Geleneksel ve Tamamlayıcı Tıp', 'aciklama' => 'Mevzuata uygun GETAT uygulamaları (akupunktur, ozon vb.).', 'aliases' => ['Akupunktur']],
            ['ad' => 'Estetik ve Kozmetik Uygulamalar', 'aciklama' => 'Medikal estetik ve kozmetik danışmanlık / uygulamalar (yetki dahilinde).'],
            ['ad' => 'Saç Ekimi ve Medikal Estetik', 'aciklama' => 'Saç ekimi, PRP ve medikal estetik işlem danışmanlığı.'],
            ['ad' => 'Check-up ve Koruyucu Hekimlik', 'aciklama' => 'Genel check-up, tarama testleri ve koruyucu sağlık planları.'],
            ['ad' => 'Evde Sağlık Hizmetleri', 'aciklama' => 'Evde hekimlik, hemşirelik ve bakım odaklı randevu hizmetleri.'],
            ['ad' => 'Tele-Tıp / Online Danışmanlık', 'aciklama' => 'Uzaktan görüntülü veya çevrim içi sağlık danışmanlığı.'],
            ['ad' => 'Diğer', 'aciklama' => 'Listede yer almayan veya çok disiplinli uzmanlık alanları.'],
        ];
    }
}
