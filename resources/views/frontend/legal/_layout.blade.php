{{-- Ortak yasal sayfa iskeleti: $baslik, $guncelleme, $ozet, $sections (slug=>baslik) --}}
@php
    $legalNav = [
        ['route' => 'frontend.legal.hakkimizda', 'label' => 'Hakkımızda'],
        ['route' => 'frontend.legal.iletisim', 'label' => 'İletişim'],
        ['route' => 'frontend.legal.kullanim', 'label' => 'Kullanım'],
        ['route' => 'frontend.legal.gizlilik', 'label' => 'Gizlilik'],
        ['route' => 'frontend.legal.kvkk', 'label' => 'KVKK'],
        ['route' => 'frontend.legal.mesafeli', 'label' => 'Mesafeli satış'],
        ['route' => 'frontend.legal.iade', 'label' => 'İade / iptal'],
    ];
@endphp

<section class="relative bg-[#FAFAFA] border-b border-[#E5E7EB] overflow-hidden">
    <div class="absolute top-[-20%] right-[-10%] w-[400px] h-[400px] rounded-full bg-[#E7B58A]/15 blur-[100px] pointer-events-none"></div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-6 pb-8 md:pt-8 md:pb-10 relative z-10">
        <nav class="flex flex-wrap items-center gap-2 text-[11px] font-bold font-display uppercase tracking-wider text-[#6B7280] mb-5">
            <a href="/" class="hover:text-[#C96A2B] transition-colors">Ana sayfa</a>
            <span class="text-slate-300">/</span>
            <span class="text-[#C96A2B]">Yasal</span>
            <span class="text-slate-300">/</span>
            <span class="text-[#111827] normal-case tracking-normal font-semibold">{{ $baslik }}</span>
        </nav>
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#C96A2B] font-display">Yasal belgeler</p>
        <h1 class="mt-2 text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#111827] font-display tracking-tight">{{ $baslik }}</h1>
        @if(!empty($ozet))
            <p class="mt-3 text-sm text-[#6B7280] max-w-2xl leading-relaxed">{{ $ozet }}</p>
        @endif
        <p class="mt-3 text-xs text-[#9CA3AF]">Son güncelleme: <span class="font-semibold text-[#6B7280]">{{ $guncelleme }}</span></p>

        <div class="mt-6 flex flex-wrap gap-2">
            @foreach($legalNav as $item)
                <a href="{{ route($item['route']) }}"
                   class="inline-flex px-3.5 py-2 rounded-xl text-[11px] font-bold font-display uppercase tracking-wider border transition-colors
                   {{ request()->routeIs($item['route'])
                        ? 'bg-[#C96A2B] border-[#C96A2B] text-white shadow-sm'
                        : 'bg-white border-[#E5E7EB] text-[#4B5563] hover:border-[#C96A2B]/40 hover:text-[#C96A2B]' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 md:py-14">
        <div class="grid lg:grid-cols-12 gap-8 lg:gap-10">
            @if(!empty($sections) && is_array($sections))
            <aside class="lg:col-span-3 order-2 lg:order-1">
                <div class="lg:sticky lg:top-24 rounded-2xl border border-[#E5E7EB] bg-[#FAFAFA] p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#9CA3AF] font-display mb-3">İçindekiler</p>
                    <nav class="space-y-1 max-h-[60vh] overflow-y-auto pr-1">
                        @foreach($sections as $slug => $title)
                            <a href="#{{ $slug }}"
                               class="block text-[12px] leading-snug text-[#4B5563] hover:text-[#C96A2B] py-1.5 border-l-2 border-transparent hover:border-[#C96A2B] pl-2.5 transition-colors">
                                {{ $title }}
                            </a>
                        @endforeach
                    </nav>
                </div>
            </aside>
            <div class="lg:col-span-9 order-1 lg:order-2">
            @else
            <div class="lg:col-span-12">
            @endif
                <article class="legal-prose rounded-3xl border border-[#E5E7EB] bg-white p-6 sm:p-8 md:p-10 shadow-[0_8px_30px_rgba(31,41,55,0.03)]
                    prose prose-slate max-w-none
                    prose-headings:font-display prose-headings:text-[#111827] prose-headings:scroll-mt-28
                    prose-h2:text-lg prose-h2:font-extrabold prose-h2:mt-10 prose-h2:mb-3 prose-h2:pb-2 prose-h2:border-b prose-h2:border-slate-100
                    prose-h3:text-sm prose-h3:font-bold prose-h3:text-[#C96A2B] prose-h3:uppercase prose-h3:tracking-wider prose-h3:mt-6
                    prose-p:text-sm prose-p:text-[#4B5563] prose-p:leading-relaxed
                    prose-li:text-sm prose-li:text-[#4B5563]
                    prose-a:text-[#C96A2B] prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-[#111827]
                    prose-table:text-xs">
                    {{ $slot }}
                </article>

                <div class="mt-6 flex flex-wrap gap-3 text-xs">
                    <a href="mailto:{{ config('company.email', 'info@randevuajandam.com') }}" class="font-semibold text-[#C96A2B] hover:underline">{{ config('company.email', 'info@randevuajandam.com') }}</a>
                    <span class="text-slate-300">·</span>
                    <a href="{{ route('frontend.legal.kullanim') }}" class="text-[#6B7280] hover:text-[#C96A2B]">Kullanım Koşulları</a>
                    <span class="text-slate-300">·</span>
                    <a href="{{ route('frontend.legal.gizlilik') }}" class="text-[#6B7280] hover:text-[#C96A2B]">Gizlilik</a>
                    <span class="text-slate-300">·</span>
                    <a href="{{ route('frontend.legal.kvkk') }}" class="text-[#6B7280] hover:text-[#C96A2B]">KVKK</a>
                    <span class="text-slate-300">·</span>
                    <a href="{{ route('frontend.legal.iletisim') }}" class="text-[#6B7280] hover:text-[#C96A2B]">İletişim</a>
                </div>
            </div>
        </div>
    </div>
</section>
