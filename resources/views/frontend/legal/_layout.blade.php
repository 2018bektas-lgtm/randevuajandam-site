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
    $hasToc = ! empty($sections) && is_array($sections);
@endphp

<style>
    .legal-page { --legal-accent: #C96A2B; }
    .legal-prose > h2:first-child { margin-top: 0 !important; }
    .legal-prose h2 {
        scroll-margin-top: 6rem;
        font-family: inherit;
        font-size: 1.125rem;
        font-weight: 800;
        color: #111827;
        margin: 2rem 0 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #F1F5F9;
        letter-spacing: -0.01em;
    }
    .legal-prose h3 {
        font-size: 0.8125rem;
        font-weight: 700;
        color: var(--legal-accent);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin: 1.5rem 0 0.5rem;
    }
    .legal-prose p {
        font-size: 0.875rem;
        line-height: 1.7;
        color: #4B5563;
        margin: 0 0 0.85rem;
    }
    .legal-prose ul, .legal-prose ol {
        margin: 0 0 1rem;
        padding-left: 1.25rem;
        font-size: 0.875rem;
        color: #4B5563;
        line-height: 1.65;
    }
    .legal-prose li { margin-bottom: 0.35rem; }
    .legal-prose a {
        color: var(--legal-accent);
        font-weight: 600;
        text-decoration: none;
    }
    .legal-prose a:hover { text-decoration: underline; }
    .legal-prose strong { color: #111827; font-weight: 700; }
    .legal-prose code {
        font-size: 0.7rem;
        background: #F1F5F9;
        padding: 0.1rem 0.35rem;
        border-radius: 0.25rem;
        color: #334155;
    }
    .legal-toc-link.active {
        color: var(--legal-accent) !important;
        border-left-color: var(--legal-accent) !important;
        font-weight: 700;
    }
    @media (max-width: 1023px) {
        .legal-toc-mobile {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.25rem;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }
        .legal-toc-mobile a {
            flex-shrink: 0;
            white-space: nowrap;
        }
    }
</style>

<section class="legal-page relative bg-[#FAFAFA] border-b border-[#E5E7EB] overflow-hidden">
    <div class="absolute top-[-20%] right-[-10%] w-[400px] h-[400px] rounded-full bg-[#E7B58A]/15 blur-[100px] pointer-events-none" aria-hidden="true"></div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-6 pb-8 md:pt-8 md:pb-10 relative z-10">
        <nav class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-semibold text-[#6B7280] mb-5" aria-label="Sayfa yolu">
            <a href="{{ url('/') }}" class="hover:text-[#C96A2B] transition-colors">Ana sayfa</a>
            <span class="text-slate-300" aria-hidden="true">/</span>
            <span class="text-[#C96A2B] font-bold uppercase tracking-wider text-[10px]">Yasal</span>
            <span class="text-slate-300" aria-hidden="true">/</span>
            <span class="text-[#111827] font-semibold truncate max-w-[min(100%,16rem)]">{{ $baslik }}</span>
        </nav>

        <p class="text-[10px] font-bold uppercase tracking-wider text-[#C96A2B] font-display">Yasal belgeler</p>
        <h1 class="mt-2 text-2xl sm:text-3xl md:text-[2rem] font-extrabold text-[#111827] font-display tracking-tight leading-tight">
            {{ $baslik }}
        </h1>
        @if(!empty($ozet))
            <p class="mt-3 text-sm text-[#6B7280] max-w-2xl leading-relaxed">{{ $ozet }}</p>
        @endif
        <p class="mt-3 text-xs text-[#9CA3AF]">
            Son güncelleme:
            <time class="font-semibold text-[#6B7280]" datetime="{{ $guncelleme ?? '' }}">{{ $guncelleme }}</time>
        </p>

        <div class="mt-6 flex flex-wrap gap-2" role="navigation" aria-label="Yasal sayfalar">
            @foreach($legalNav as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   @if($active) aria-current="page" @endif
                   class="inline-flex items-center px-3.5 py-2 rounded-xl text-[11px] font-bold font-display uppercase tracking-wider border transition-colors
                   {{ $active
                        ? 'bg-[#C96A2B] border-[#C96A2B] text-white shadow-sm'
                        : 'bg-white border-[#E5E7EB] text-[#4B5563] hover:border-[#C96A2B]/50 hover:text-[#C96A2B]' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 md:py-12">
        @if($hasToc)
            {{-- Mobil içindekiler (yatay kaydırma) --}}
            <div class="lg:hidden mb-6 -mx-1 px-1">
                <p class="text-[10px] font-bold uppercase tracking-wider text-[#9CA3AF] mb-2">İçindekiler</p>
                <nav class="legal-toc-mobile" aria-label="İçindekiler">
                    @foreach($sections as $slug => $title)
                        <a href="#{{ $slug }}"
                           class="inline-flex px-3 py-1.5 rounded-lg text-[11px] font-semibold border border-[#E5E7EB] bg-[#FAFAFA] text-[#4B5563] hover:border-[#C96A2B]/40 hover:text-[#C96A2B]">
                            {{ $title }}
                        </a>
                    @endforeach
                </nav>
            </div>
        @endif

        <div class="grid grid-cols-1 {{ $hasToc ? 'lg:grid-cols-12' : '' }} gap-8 lg:gap-10 items-start">
            @if($hasToc)
                <aside class="hidden lg:block lg:col-span-3">
                    <div class="sticky top-24 rounded-2xl border border-[#E5E7EB] bg-[#FAFAFA] p-4 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#9CA3AF] font-display mb-3">İçindekiler</p>
                        <nav class="space-y-0.5 max-h-[min(60vh,28rem)] overflow-y-auto overscroll-contain" aria-label="İçindekiler">
                            @foreach($sections as $slug => $title)
                                <a href="#{{ $slug }}"
                                   class="legal-toc-link block text-[12px] leading-snug text-[#4B5563] hover:text-[#C96A2B] py-1.5 border-l-2 border-transparent hover:border-[#C96A2B] pl-2.5 transition-colors">
                                    {{ $title }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </aside>
            @endif

            <div class="{{ $hasToc ? 'lg:col-span-9' : 'w-full max-w-3xl' }} min-w-0">
                <article class="legal-prose rounded-2xl sm:rounded-3xl border border-[#E5E7EB] bg-white p-5 sm:p-8 md:p-10 shadow-[0_8px_30px_rgba(31,41,55,0.04)] overflow-hidden">
                    {{ $slot }}
                </article>

                <div class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-2 text-xs text-[#6B7280]">
                    <a href="mailto:{{ config('company.email', 'info@randevuajandam.com') }}"
                       class="font-semibold text-[#C96A2B] hover:underline break-all">
                        {{ config('company.email', 'info@randevuajandam.com') }}
                    </a>
                    <span class="text-slate-300 hidden sm:inline" aria-hidden="true">·</span>
                    <a href="{{ route('frontend.legal.kullanim') }}" class="hover:text-[#C96A2B]">Kullanım</a>
                    <span class="text-slate-300" aria-hidden="true">·</span>
                    <a href="{{ route('frontend.legal.gizlilik') }}" class="hover:text-[#C96A2B]">Gizlilik</a>
                    <span class="text-slate-300" aria-hidden="true">·</span>
                    <a href="{{ route('frontend.legal.kvkk') }}" class="hover:text-[#C96A2B]">KVKK</a>
                    <span class="text-slate-300" aria-hidden="true">·</span>
                    <a href="{{ route('frontend.legal.iletisim') }}" class="hover:text-[#C96A2B]">İletişim</a>
                </div>
            </div>
        </div>
    </div>
</section>

@if($hasToc)
<script>
document.addEventListener('DOMContentLoaded', function () {
    var links = document.querySelectorAll('.legal-toc-link');
    if (!links.length || !('IntersectionObserver' in window)) return;
    var map = {};
    links.forEach(function (a) {
        var id = (a.getAttribute('href') || '').replace('#', '');
        if (id) map[id] = a;
    });
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var id = entry.target.id;
            links.forEach(function (l) { l.classList.remove('active'); });
            if (map[id]) map[id].classList.add('active');
        });
    }, { rootMargin: '-20% 0px -60% 0px', threshold: 0 });
    Object.keys(map).forEach(function (id) {
        var el = document.getElementById(id);
        if (el) observer.observe(el);
    });
});
</script>
@endif
