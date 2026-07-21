<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Il;
use App\Support\SeoMeta;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Genel arama motoru hub sayfaları — ince (thin) içerik değil, benzersiz metin + iç link.
 */
class PublicSeoController extends Controller
{
    public function hub(): View
    {
        $branslar = Cache::remember('seo:hub:branslar', now()->addHours(6), function () {
            return Brans::query()
                ->withCount(['doktorlar' => fn ($q) => $q->platformdaListelenen()])
                ->orderBy('ad')
                ->get();
        });

        $iller = Cache::remember('seo:hub:iller', now()->addHours(6), function () {
            return Il::query()->orderBy('ad')->get(['id', 'ad', 'slug']);
        });

        $populerIller = $iller->whereIn('slug', [
            'istanbul', 'ankara', 'izmir', 'bursa', 'antalya', 'adana', 'konya',
            'gaziantep', 'kocaeli', 'mersin', 'diyarbakir', 'kayseri', 'eskisehir',
            'samsun', 'denizli', 'sanliurfa', 'malatya', 'trabzon',
        ])->values();
        if ($populerIller->isEmpty()) {
            $populerIller = $iller->take(18);
        }

        $title = SeoMeta::title('Online Randevu Rehberi', 'Doktor Klinik Hasta');
        $desc = SeoMeta::description(
            'Online doktor randevusu, hekim ve klinik bulma rehberi. Branş ve şehre göre randevu alın; '
            .'hasta olarak uzman seçin. Randevu Ajandam — Türkiye geneli randevu platformu.'
        );

        $faqs = $this->platformFaqs();

        return view('frontend.seo.hub', compact(
            'branslar',
            'iller',
            'populerIller',
            'title',
            'desc',
            'faqs'
        ));
    }

    public function brans(string $brans_slug): View
    {
        $brans = Brans::query()->where('slug', $brans_slug)->firstOrFail();

        $doktorlar = Doktor::platformdaListelenen()
            ->whereHas('branslar', fn ($q) => $q->where('branslar.id', $brans->id))
            ->with(['il', 'ilce', 'branslar'])
            ->orderBy('ad_soyad')
            ->limit(24)
            ->get();

        $iller = Il::query()
            ->whereIn('id', $doktorlar->pluck('il_id')->filter()->unique())
            ->orderBy('ad')
            ->get();

        $tumIller = Cache::remember('seo:hub:iller', now()->addHours(6), function () {
            return Il::query()->orderBy('ad')->get(['id', 'ad', 'slug']);
        });

        $title = SeoMeta::title($brans->ad.' Online Randevu', 'Doktor Bul');
        $desc = SeoMeta::description(
            "{$brans->ad} uzmanlarından online randevu alın. Türkiye genelinde {$brans->ad} doktorları listelenir; "
            .'müsait saat seçin, hasta randevunuzu oluşturun. Randevu Ajandam.'
        );
        $keywords = SeoMeta::keywords([
            $brans->ad,
            $brans->ad.' randevu',
            $brans->ad.' doktor',
            $brans->ad.' online randevu',
            'hekim randevu',
            'hasta randevu',
            'randevu ajandam',
        ]);

        $faqs = [
            [
                'q' => "{$brans->ad} online randevu nasıl alınır?",
                'a' => "Randevu Ajandam'da {$brans->ad} listesinden uzman seçin, müsait saati tıklayın ve randevunuzu onaylayın. Üye olmadan da misafir randevu talebi oluşturabilirsiniz.",
            ],
            [
                'q' => "{$brans->ad} doktorları hangi şehirlerde var?",
                'a' => 'Platformda kayıtlı ve vitrinde listelenen hekimler şehir bazında görünür. Aşağıdaki şehir linklerinden veya doktor arama sayfasından filtreleyebilirsiniz.',
            ],
            [
                'q' => 'Randevu ücretli mi?',
                'a' => 'Platform üzerinden randevu oluşturmak hastaya ücretsizdir. Muayene/hizmet ücreti hekimin belirlediği şekilde muayenehanede veya klinikte geçerlidir.',
            ],
        ];

        return view('frontend.seo.brans', compact(
            'brans',
            'doktorlar',
            'iller',
            'tumIller',
            'title',
            'desc',
            'keywords',
            'faqs'
        ));
    }

    public function sehir(string $il_slug): View
    {
        $il = Il::query()->where('slug', $il_slug)->firstOrFail();

        // Mevcut SEO URL ile aynı veri; zengin içerikli hub
        return redirect()->route('frontend.il.liste', ['il_slug' => $il->slug], 301);
    }

    /**
     * @return list<array{q: string, a: string}>
     */
    public function platformFaqs(): array
    {
        return [
            [
                'q' => 'Online doktor randevusu nasıl alınır?',
                'a' => 'Doktorlar sayfasından branş veya şehir seçin, hekim profiline girin, müsait saati seçip randevu formunu doldurun. Onay sonrası bilgilendirilirsiniz.',
            ],
            [
                'q' => 'Randevu Ajandam nedir?',
                'a' => 'Randevu Ajandam; hastaları uzman hekim ve kliniklerle buluşturan, hekimlere randevu ve danışan yönetimi sunan dijital randevu platformudur.',
            ],
            [
                'q' => 'Hangi branşlardan randevu alabilirim?',
                'a' => 'Diyetisyen, psikolog, diş hekimliği, aile hekimliği, dermatoloji ve platformda listelenen diğer uzmanlıklardan randevu oluşturabilirsiniz.',
            ],
            [
                'q' => 'Üye olmadan randevu alabilir miyim?',
                'a' => 'Evet. Birçok hekim profilinde misafir randevu formu vardır. Telefon doğrulaması istenebilir.',
            ],
            [
                'q' => 'Hekim veya klinik olarak nasıl kayıt olurum?',
                'a' => 'Paketler sayfasından plan seçip hekim kaydı oluşturun. Meslek belgesi onayı ve ödeme sonrası paneliniz açılır.',
            ],
            [
                'q' => 'Randevumu iptal edebilir miyim?',
                'a' => 'Evet. Hasta paneli veya randevu yönetim linkiniz üzerinden hekimin iptal kurallarına uygun şekilde iptal edebilirsiniz.',
            ],
        ];
    }
}
