@extends('frontend.layouts.app')

@section('baslik', $title)
@section('meta_aciklama', $desc)
@section('meta_anahtar_kelimeler', \App\Support\SeoMeta::keywords([
    'online randevu', 'doktor randevu rehberi', 'hekim bul', 'klinik randevu',
    'hasta randevu', 'branş randevu', 'şehir doktor', 'randevu ajandam',
]))
@section('canonical', route('frontend.seo.hub'))

@section('json_ld')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'CollectionPage',
            'name' => $title,
            'description' => $desc,
            'url' => route('frontend.seo.hub'),
            'isPartOf' => ['@type' => 'WebSite', 'name' => 'Randevu Ajandam', 'url' => url('/')],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ])->values()->all(),
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Online Randevu Rehberi', 'item' => route('frontend.seo.hub')],
            ],
        ],
        [
            '@type' => 'HowTo',
            'name' => 'Online doktor randevusu nasıl alınır?',
            'description' => 'Randevu Ajandam üzerinden 3 adımda hekim randevusu oluşturun.',
            'step' => [
                ['@type' => 'HowToStep', 'position' => 1, 'name' => 'Doktor veya branş seçin', 'text' => 'Doktorlar sayfasından branş, şehir veya isim ile arayın.'],
                ['@type' => 'HowToStep', 'position' => 2, 'name' => 'Müsait saat seçin', 'text' => 'Hekim profilinde takvimden boş saati tıklayın.'],
                ['@type' => 'HowToStep', 'position' => 3, 'name' => 'Randevuyu onaylayın', 'text' => 'Bilgilerinizi girin; onay sonrası bilgilendirilirsiniz.'],
            ],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection

@section('icerik')
<section class="fe-page bg-[#FAFAFA]">
    <div class="fe-container max-w-5xl">
        <nav class="text-[11px] text-slate-500 mb-4" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-[#C96A2B]">Ana Sayfa</a>
            <span class="mx-1.5">/</span>
            <span class="text-slate-800 font-semibold">Online Randevu Rehberi</span>
        </nav>

        <header class="mb-10 space-y-3">
            <p class="text-[11px] font-bold uppercase tracking-widest text-[#C96A2B]">SEO Rehber · Hasta & Hekim</p>
            <h1 class="text-3xl md:text-4xl font-extrabold font-display text-[#111827] tracking-tight">
                Online Doktor Randevusu ve Hekim Bulma Rehberi
            </h1>
            <p class="text-sm md:text-base text-slate-600 leading-relaxed max-w-3xl">
                Randevu Ajandam ile <strong>online randevu</strong> alın: branş ve şehre göre
                <strong>doktor</strong>, <strong>klinik</strong> ve <strong>hasta randevu</strong> süreçlerini tek platformda yönetin.
                Aşağıdan uzmanlık veya şehir seçerek aramaya başlayın.
            </p>
            <div class="flex flex-wrap gap-2 pt-1">
                <a href="{{ route('frontend.hekimler') }}" class="inline-flex px-4 py-2.5 rounded-xl bg-[#C96A2B] text-white text-xs font-bold">Tüm doktorları gör</a>
                <a href="{{ route('frontend.paketler') }}" class="inline-flex px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700">Hekim paketi</a>
            </div>
        </header>

        <div class="grid md:grid-cols-3 gap-4 mb-12">
            @foreach([
                ['t' => 'Hasta için', 'd' => 'Branş ve şehir filtresiyle hekim bulun, müsait saatte randevu oluşturun.'],
                ['t' => 'Hekim için', 'd' => 'Online randevu paneli, hasta takibi, web sitesi ve abonelik paketleri.'],
                ['t' => 'Klinik için', 'd' => 'Çok hekimli klinik vitrini, personel ve kurumsal randevu yönetimi.'],
            ] as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-extrabold text-[#111827] font-display">{{ $card['t'] }}</h2>
                    <p class="mt-2 text-xs text-slate-600 leading-relaxed">{{ $card['d'] }}</p>
                </div>
            @endforeach
        </div>

        <section class="mb-12">
            <h2 class="text-xl font-extrabold font-display text-[#111827] mb-4">Branşa göre online randevu</h2>
            <p class="text-xs text-slate-500 mb-4">Arama motorlarında sık aranan uzmanlıklar. Her sayfa {{ $branslar->count() }}+ branş için ayrı rehber içerir.</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                @foreach($branslar as $b)
                    @if($b->slug)
                        <a href="{{ route('frontend.seo.brans', $b->slug) }}"
                           class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[11px] font-semibold text-slate-800 hover:border-[#C96A2B] hover:text-[#C96A2B] transition-colors">
                            {{ $b->ad }}
                            @if(($b->doktorlar_count ?? 0) > 0)
                                <span class="text-slate-400 font-normal">({{ $b->doktorlar_count }})</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
        </section>

        <section class="mb-12">
            <h2 class="text-xl font-extrabold font-display text-[#111827] mb-4">Şehre göre doktor randevusu</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                @foreach($populerIller as $il)
                    @if($il->slug)
                        <a href="{{ route('frontend.il.liste', $il->slug) }}"
                           class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[11px] font-semibold text-slate-800 hover:border-[#C96A2B] hover:text-[#C96A2B] transition-colors">
                            {{ $il->ad }} doktor randevu
                        </a>
                    @endif
                @endforeach
            </div>
            <p class="mt-3 text-[11px] text-slate-500">
                Tüm iller:
                @foreach($iller->take(40) as $il)
                    @if($il->slug)
                        <a href="{{ route('frontend.il.liste', $il->slug) }}" class="text-[#C96A2B] hover:underline">{{ $il->ad }}</a>@if(!$loop->last), @endif
                    @endif
                @endforeach
            </p>
        </section>

        <section class="mb-12 rounded-2xl border border-slate-200 bg-white p-6 md:p-8 shadow-sm">
            <h2 class="text-xl font-extrabold font-display text-[#111827] mb-4">Sık sorulan sorular</h2>
            <div class="space-y-4">
                @foreach($faqs as $f)
                    <div class="border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                        <h3 class="text-sm font-bold text-slate-900">{{ $f['q'] }}</h3>
                        <p class="mt-1.5 text-xs text-slate-600 leading-relaxed">{{ $f['a'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="prose prose-sm max-w-none text-slate-600">
            <h2 class="text-lg font-extrabold text-[#111827] font-display !mb-3">Neden Randevu Ajandam?</h2>
            <p>
                Klasik telefon randevusunun yerine <strong>dijital randevu</strong> deneyimi sunuyoruz.
                Hastalar müsaitlik takvimini görür; hekimler ajanda, hasta kaydı ve hatırlatmaları tek panelden yönetir.
                <strong>Online randevu sistemi</strong> arayan klinikler ve bireysel hekimler paketler sayfasından abone olabilir.
            </p>
        </section>
    </div>
</section>
@endsection
