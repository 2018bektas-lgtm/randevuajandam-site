@extends('frontend.layouts.app')

@section('baslik', $title)
@section('meta_aciklama', $desc)
@section('meta_anahtar_kelimeler', $keywords)
@section('canonical', route('frontend.seo.brans', $brans->slug))

@section('json_ld')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'MedicalWebPage',
            'name' => $title,
            'description' => $desc,
            'url' => route('frontend.seo.brans', $brans->slug),
            'about' => [
                '@type' => 'MedicalSpecialty',
                'name' => $brans->ad,
            ],
            'inLanguage' => 'tr-TR',
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
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Randevu Rehberi', 'item' => route('frontend.seo.hub')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $brans->ad, 'item' => route('frontend.seo.brans', $brans->slug)],
            ],
        ],
        [
            '@type' => 'ItemList',
            'name' => $brans->ad.' doktorları',
            'numberOfItems' => $doktorlar->count(),
            'itemListElement' => $doktorlar->take(12)->values()->map(fn ($d, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => trim(($d->unvan ? $d->unvan.' ' : '').$d->ad_soyad),
                'url' => $d->profil_url,
            ])->all(),
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
            <a href="{{ route('frontend.seo.hub') }}" class="hover:text-[#C96A2B]">Randevu Rehberi</a>
            <span class="mx-1.5">/</span>
            <span class="text-slate-800 font-semibold">{{ $brans->ad }}</span>
        </nav>

        <header class="mb-8 space-y-3">
            <p class="text-[11px] font-bold uppercase tracking-widest text-[#C96A2B]">{{ $brans->ad }} · Online Randevu</p>
            <h1 class="text-3xl md:text-4xl font-extrabold font-display text-[#111827] tracking-tight">
                {{ $brans->ad }} Doktorları — Online Randevu Al
            </h1>
            <p class="text-sm text-slate-600 leading-relaxed max-w-3xl">
                {{ $brans->ad }} alanında uzman hekimlerden <strong>online randevu</strong> alın.
                @if($brans->aciklama)
                    {{ Str::limit(strip_tags($brans->aciklama), 220) }}
                @else
                    Randevu Ajandam üzerinden müsait saatleri görün, hasta randevunuzu oluşturun.
                @endif
            </p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('frontend.hekimler', ['brans' => $brans->slug]) }}"
                   class="inline-flex px-4 py-2.5 rounded-xl bg-[#C96A2B] text-white text-xs font-bold">
                    Tüm {{ $brans->ad }} listesi
                </a>
                <a href="{{ route('frontend.seo.hub') }}" class="inline-flex px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700">
                    Tüm branşlar
                </a>
            </div>
        </header>

        @if($doktorlar->isNotEmpty())
            <section class="mb-10">
                <h2 class="text-lg font-extrabold font-display text-[#111827] mb-4">Listelenen {{ $brans->ad }} uzmanları</h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    @foreach($doktorlar as $d)
                        <a href="{{ $d->profil_url }}" class="rounded-2xl border border-slate-200 bg-white p-4 hover:border-[#C96A2B]/50 shadow-sm transition-colors">
                            <p class="text-sm font-bold text-[#111827]">
                                {{ $d->unvan ? $d->unvan.' ' : '' }}{{ $d->ad_soyad }}
                            </p>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{ $d->il?->ad }}{{ $d->ilce?->ad ? ' / '.$d->ilce->ad : '' }}
                                · Online randevu
                            </p>
                        </a>
                    @endforeach
                </div>
            </section>
        @else
            <div class="mb-10 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-xs text-amber-900">
                Şu an vitrinde listelenen {{ $brans->ad }} hekimi yok. Yakında eklenecek veya
                <a href="{{ route('frontend.hekimler') }}" class="font-bold underline">tüm doktorlar</a> sayfasını ziyaret edin.
            </div>
        @endif

        <section class="mb-10">
            <h2 class="text-lg font-extrabold font-display text-[#111827] mb-3">Şehre göre {{ $brans->ad }} randevu</h2>
            <div class="flex flex-wrap gap-2">
                @forelse($iller as $il)
                    <a href="{{ route('frontend.hekimler', ['brans' => $brans->slug, 'il' => $il->id]) }}"
                       class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-700 hover:border-[#C96A2B] hover:text-[#C96A2B]">
                        {{ $il->ad }} {{ $brans->ad }}
                    </a>
                @empty
                    @foreach($tumIller->take(20) as $il)
                        @if($il->slug)
                            <a href="{{ route('frontend.il.liste', $il->slug) }}"
                               class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-700 hover:border-[#C96A2B]">
                                {{ $il->ad }} doktor
                            </a>
                        @endif
                    @endforeach
                @endforelse
            </div>
        </section>

        <section class="mb-10 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold font-display text-[#111827] mb-4">Sık sorulan sorular — {{ $brans->ad }}</h2>
            <div class="space-y-4">
                @foreach($faqs as $f)
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">{{ $f['q'] }}</h3>
                        <p class="mt-1 text-xs text-slate-600 leading-relaxed">{{ $f['a'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <article class="prose prose-sm max-w-none text-slate-600">
            <h2 class="!text-base !font-extrabold !text-[#111827]">{{ $brans->ad }} online randevu hakkında</h2>
            <p>
                {{ $brans->ad }} randevusu arayan hastalar, Randevu Ajandam üzerinde listelenen uzmanların
                profillerini inceleyebilir, çalışma saatlerini görebilir ve randevu talebi oluşturabilir.
                Hekimler ise kendi panellerinden müsaitlik, hasta ve randevu yönetimini yürütür.
            </p>
            <p>
                Aradığınız uzmanı bulamazsanız
                <a href="{{ route('frontend.hekimler', ['brans' => $brans->slug]) }}">{{ $brans->ad }} filtreli doktor listesini</a>
                veya <a href="{{ route('frontend.seo.hub') }}">tüm randevu rehberini</a> kullanın.
            </p>
        </article>
    </div>
</section>
@endsection
