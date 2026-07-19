@extends('frontend.layouts.app')

@section('baslik', 'Randevu Ajandam - Uzman Doktor ve Randevu Platformu')

@section('icerik')
    @php
        $heroStats = isset($istatistikler) ? [
            [
                'value' => number_format($istatistikler['doktor_sayisi']).'+',
                'label' => 'Aktif Uzman',
                'icon' => 'users',
                'side' => 'a',
            ],
            [
                'value' => number_format($istatistikler['randevu_sayisi']).'+',
                'label' => 'Tamamlanan Randevu',
                'icon' => 'check',
                'side' => 'b',
            ],
            [
                'value' => number_format($istatistikler['yorum_sayisi']).'+',
                'label' => 'Hasta Yorumu',
                'icon' => 'chat',
                'side' => 'c',
            ],
            [
                'value' => (string) $istatistikler['brans_sayisi'],
                'label' => 'Uzmanlık Alanı',
                'icon' => 'flask',
                'side' => 'd',
            ],
        ] : [];
    @endphp

    <style>
        @keyframes hero-float-1 {
            0%, 100% { transform: translate3d(0, 0, 0); }
            50% { transform: translate3d(0, -10px, 0); }
        }
        @keyframes hero-float-2 {
            0%, 100% { transform: translate3d(0, 0, 0); }
            50% { transform: translate3d(0, -12px, 0); }
        }
        @keyframes hero-float-3 {
            0%, 100% { transform: translate3d(0, 0, 0); }
            50% { transform: translate3d(0, -8px, 0); }
        }
        @keyframes hero-float-4 {
            0%, 100% { transform: translate3d(0, 0, 0); }
            50% { transform: translate3d(0, -11px, 0); }
        }
        @keyframes hero-card-in {
            from { opacity: 0; transform: translate3d(0, 18px, 0) scale(0.96); }
            to { opacity: 1; transform: translate3d(0, 0, 0) scale(1); }
        }

        .hero-stage {
            position: relative;
            max-width: 58rem;
            margin-left: auto;
            margin-right: auto;
        }

        /* Düz dikdörtgen kutular — farklı konumlarda (eğim yok) */
        .hero-scatter {
            position: absolute;
            z-index: 20;
            pointer-events: none;
            animation: hero-card-in 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .hero-scatter--a {
            top: -0.35rem;
            left: -0.25rem;
            animation-delay: 0.08s;
        }
        .hero-scatter--b {
            top: 12%;
            right: -0.5rem;
            animation-delay: 0.22s;
        }
        .hero-scatter--c {
            bottom: 8%;
            left: -0.75rem;
            animation-delay: 0.36s;
        }
        .hero-scatter--d {
            bottom: -0.5rem;
            right: 0.25rem;
            animation-delay: 0.5s;
        }

        @media (min-width: 768px) {
            .hero-scatter--a { top: 0; left: -1.25rem; }
            .hero-scatter--b { top: 4%; right: -1.5rem; }
            .hero-scatter--c { bottom: 6%; left: -1.75rem; }
            .hero-scatter--d { bottom: -0.75rem; right: -0.75rem; }
        }
        @media (min-width: 1024px) {
            .hero-scatter--a { top: -0.5rem; left: -3rem; }
            .hero-scatter--b { top: 8%; right: -3.25rem; }
            .hero-scatter--c { bottom: 10%; left: -3.5rem; }
            .hero-scatter--d { bottom: -1rem; right: -2.5rem; }
        }
        @media (min-width: 1280px) {
            .hero-scatter--a { top: -0.75rem; left: -5.5rem; }
            .hero-scatter--b { top: 6%; right: -5.75rem; }
            .hero-scatter--c { bottom: 12%; left: -6rem; }
            .hero-scatter--d { bottom: -1.25rem; right: -4.5rem; }
        }

        .hero-scatter-float {
            will-change: transform;
        }
        .hero-scatter--a .hero-scatter-float { animation: hero-float-1 5.2s ease-in-out infinite; animation-delay: 0.2s; }
        .hero-scatter--b .hero-scatter-float { animation: hero-float-2 6.1s ease-in-out infinite; animation-delay: 0.9s; }
        .hero-scatter--c .hero-scatter-float { animation: hero-float-3 5.6s ease-in-out infinite; animation-delay: 1.4s; }
        .hero-scatter--d .hero-scatter-float { animation: hero-float-4 6.4s ease-in-out infinite; animation-delay: 0.5s; }

        .hero-box {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            min-width: 9.5rem;
            max-width: 11.5rem;
            padding: 0.7rem 0.85rem;
            border-radius: 1rem;
            background: linear-gradient(145deg, rgba(255,255,255,0.97) 0%, rgba(255,247,237,0.92) 100%);
            border: 1px solid rgba(231, 181, 138, 0.4);
            box-shadow:
                0 14px 36px -14px rgba(31, 41, 55, 0.18),
                0 0 0 1px rgba(255, 255, 255, 0.65) inset;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .hero-box:hover {
            border-color: rgba(201, 106, 43, 0.45);
            box-shadow:
                0 18px 40px -12px rgba(201, 106, 43, 0.22),
                0 0 0 1px rgba(255, 255, 255, 0.7) inset;
        }
        .hero-box-icon {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 0.7rem;
            background: #FFF7ED;
            color: #C96A2B;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid rgba(231, 181, 138, 0.35);
        }
        .hero-box-icon svg { width: 1.15rem; height: 1.15rem; }
        .hero-box-text { text-align: left; min-width: 0; }
        .hero-box-value {
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #C96A2B;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }
        .hero-box-label {
            margin-top: 0.3rem;
            font-size: 0.5625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6B7280;
            line-height: 1.2;
        }

        /* Kart varyasyonları — dağınık hissi güçlendirir */
        .hero-scatter--b .hero-box {
            min-width: 10.25rem;
            padding: 0.8rem 0.95rem;
            border-radius: 1.15rem;
        }
        .hero-scatter--c .hero-box {
            background: linear-gradient(155deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.95) 100%);
            border-color: rgba(229, 231, 235, 0.95);
        }
        .hero-scatter--d .hero-box {
            min-width: 9rem;
            box-shadow:
                0 16px 40px -12px rgba(201, 106, 43, 0.16),
                0 0 0 1px rgba(255, 255, 255, 0.7) inset;
        }

        .hero-text-frame {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 1.5rem 5.75rem 1.75rem;
        }
        @media (min-width: 768px) {
            .hero-text-frame { padding: 1.75rem 7rem 2rem; }
        }
        @media (min-width: 1024px) {
            .hero-text-frame { padding: 2rem 8rem 2.25rem; }
        }
        .hero-search-block {
            position: relative;
            z-index: 10;
            text-align: center;
            margin-top: 1.75rem;
        }

        /* Mobil: hafif dağınık 2x2, yine dikdörtgen */
        @media (max-width: 639px) {
            .hero-stage { max-width: 100%; }
            .hero-scatter-desktop { display: none !important; }
            .hero-scatter-mobile {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.65rem 0.55rem;
                margin-bottom: 1.15rem;
                align-items: start;
            }
            .hero-scatter-mobile .hero-box {
                min-width: 0;
                max-width: none;
                width: 100%;
                pointer-events: auto;
            }
            .hero-scatter-mobile .hero-scatter {
                position: static;
                animation: hero-card-in 0.55s ease-out both;
            }
            .hero-scatter-mobile .hero-scatter--a { margin-top: 0; }
            .hero-scatter-mobile .hero-scatter--b { margin-top: 0; }
            .hero-scatter-mobile .hero-scatter--c { margin-top: 0; }
            .hero-scatter-mobile .hero-scatter--d { margin-top: 0; }
            .hero-scatter-mobile .hero-scatter-float { animation: none; transform: none; }
            .hero-text-frame { padding: 0 0.15rem 0.5rem; }
        }
        @media (min-width: 640px) {
            .hero-scatter-mobile { display: none !important; }
            .hero-scatter-desktop { display: contents; }
        }

        @media (prefers-reduced-motion: reduce) {
            .hero-scatter,
            .hero-scatter-float {
                animation: none !important;
            }
            .hero-scatter-float {
                transform: none;
            }
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white border-b border-[#E5E7EB] pt-8 pb-8! md:pt-8 md:pb-8 lg:pt-8 lg:pb-8 select-none">
        <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
            <div class="absolute top-[-30%] right-[-10%] w-[550px] h-[550px] rounded-full bg-[#E7B58A]/10 blur-[130px]"></div>
            <div class="absolute bottom-[-20%] left-[-10%] w-[550px] h-[550px] rounded-full bg-[#C96A2B]/4 blur-[130px]"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="hero-stage">
                @if(!empty($heroStats))
                    {{-- Mobil: dağınık 2x2 dikdörtgen --}}
                    <div class="hero-scatter-mobile" role="list" aria-label="Platform istatistikleri">
                        @foreach($heroStats as $stat)
                            <div class="hero-scatter hero-scatter--{{ $stat['side'] }}" role="listitem">
                                <div class="hero-scatter-float">
                                    <div class="hero-box">
                                        <div class="hero-box-icon" aria-hidden="true">
                                            @if($stat['icon'] === 'users')
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            @elseif($stat['icon'] === 'check')
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                            @elseif($stat['icon'] === 'chat')
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            @else
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                            @endif
                                        </div>
                                        <div class="hero-box-text">
                                            <div class="hero-box-value">{{ $stat['value'] }}</div>
                                            <div class="hero-box-label">{{ $stat['label'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="hero-text-frame">
                    @if(!empty($heroStats))
                        <div class="hero-scatter-desktop" role="list" aria-label="Platform istatistikleri">
                            @foreach($heroStats as $stat)
                                <div class="hero-scatter hero-scatter--{{ $stat['side'] }}" role="listitem">
                                    <div class="hero-scatter-float">
                                        <div class="hero-box">
                                            <div class="hero-box-icon" aria-hidden="true">
                                                @if($stat['icon'] === 'users')
                                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                @elseif($stat['icon'] === 'check')
                                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                                @elseif($stat['icon'] === 'chat')
                                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                @else
                                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                                @endif
                                            </div>
                                            <div class="hero-box-text">
                                                <div class="hero-box-value">{{ $stat['value'] }}</div>
                                                <div class="hero-box-label">{{ $stat['label'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 rounded-full text-xs font-bold font-display uppercase tracking-wider mb-6">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#C96A2B] animate-pulse"></span>
                        Türkiye'nin Seçkin Uzman Ağı
                    </span>

                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold font-display text-[#111827] tracking-tight leading-tight md:leading-[1.08]">
                        Aradığınız Uzmanı Bulun, <br class="hidden md:inline">
                        <span class="text-[#C96A2B]">Kolayca Randevu</span> Alın.
                    </h1>

                    <p class="text-base text-[#6B7280] max-w-xl mx-auto mt-5 leading-relaxed">
                        @if(isset($branslar) && $branslar->count() > 0)
                            {{ $branslar->take(4)->pluck('ad')->implode(', ') }} ve daha birçok alanda yüzlerce profesyonel arasından size en uygun olanını seçin.
                        @else
                            Psikologlardan diyetisyenlere, çocuk gelişimcilerinden fizyoterapistlere kadar yüzlerce profesyonel arasından size en uygun olanını seçin.
                        @endif
                    </p>
                </div>

                <div class="hero-search-block">
                    <form action="{{ route('frontend.hekimler') }}" method="GET" class="max-w-2xl mx-auto p-2 bg-white rounded-2xl border border-[#E5E7EB] shadow-lg shadow-slate-200/50 flex flex-col sm:flex-row gap-2">
                        <div class="flex-grow relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#6B7280]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                            <input type="text" name="arama" id="searchBar" placeholder="Uzman adı, branş veya şikayet yazın..."
                                   class="w-full pl-11 pr-4 py-4 rounded-xl bg-transparent text-[#111827] placeholder-[#9CA3AF] focus:outline-none text-sm font-medium">
                        </div>

                        <button type="submit" class="sm:px-8 py-4 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-sm tracking-wide transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                            Uzman Ara
                        </button>
                    </form>

                    <div class="mt-5 flex items-center justify-center flex-wrap gap-2 text-xs pb-2">
                        <span class="text-[#6B7280] font-medium mr-1.5">Popüler:</span>
                        @foreach($populerAramalar as $arama)
                            <button type="button" onclick="setSearch(@js($arama))" class="px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-slate-50 hover:bg-[#FFF7ED] hover:text-[#C96A2B] hover:border-[#E7B58A]/30 transition-all font-semibold cursor-pointer">{{ $arama }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .ra-slider { position: relative; }
        .ra-slider-track {
            display: flex;
            gap: 1.25rem;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            padding: 0.35rem 0.15rem 1rem;
            scrollbar-width: none;
        }
        .ra-slider-track::-webkit-scrollbar { display: none; }
        .ra-slider-item {
            flex: 0 0 min(86vw, 20.5rem);
            scroll-snap-align: start;
            min-width: 0;
        }
        @media (min-width: 768px) {
            .ra-slider-item { flex-basis: calc((100% - 2.5rem) / 2); }
        }
        @media (min-width: 1024px) {
            .ra-slider-item { flex-basis: calc((100% - 3.75rem) / 3); }
        }
        .ra-slider-nav {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            border: 1px solid #E5E7EB;
            background: #fff;
            color: #6B7280;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .ra-slider-nav:hover {
            border-color: rgba(231, 181, 138, 0.55);
            color: #C96A2B;
            background: #FFF7ED;
        }
        .ra-slider-nav:disabled {
            opacity: 0.35;
            cursor: default;
            pointer-events: none;
        }
        .ra-card {
            height: 100%;
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(31, 41, 55, 0.03);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .ra-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(31, 41, 55, 0.07);
        }
    </style>

    {{-- Öne çıkan uzmanlar --}}
    <section id="doktorlar" class="max-w-7xl mx-auto px-6 pt-16 pb-10 md:pt-20 md:pb-12 select-none">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Öne Çıkan Uzmanlar</h2>
                <p class="text-sm text-[#6B7280] mt-2">Danışan memnuniyeti yüksek aktif uzmanlarımız.</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="ra-slider-controls flex items-center gap-2" data-slider-controls="uzmanlar">
                    <button type="button" class="ra-slider-nav" data-slider-prev="uzmanlar" aria-label="Önceki">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" class="ra-slider-nav" data-slider-next="uzmanlar" aria-label="Sonraki">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                <a href="{{ route('frontend.hekimler') }}" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] transition-colors font-display no-underline ml-1">
                    Tümü
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <div class="ra-slider" data-slider="uzmanlar">
            <div class="ra-slider-track" data-slider-track="uzmanlar">
                @forelse($oneCikanDoktorlar as $doktor)
                    @php
                        $brans = $doktor->branslar->first();
                        $bransAd = $brans ? $brans->ad : 'Uzman';
                        $initials = collect(explode(' ', $doktor->ad_soyad))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
                        $ortalamaPuan = $doktor->ortalama_puan_cache ?? 0;
                        $yorumSayisi = $doktor->yorum_sayisi_cache ?? 0;
                    @endphp
                    <div class="ra-slider-item">
                        <div class="ra-card p-5 flex flex-col justify-between min-h-[240px]">
                            <div>
                                <div class="flex gap-3.5 mb-5">
                                    @if($doktor->profil_resmi)
                                        <img src="{{ asset($doktor->profil_resmi) }}" alt="{{ $doktor->ad_soyad }}"
                                             class="w-14 h-14 rounded-full object-cover border border-[#E7B58A]/30 flex-shrink-0" loading="lazy">
                                    @else
                                        <div class="w-14 h-14 rounded-full bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center font-extrabold text-sm font-display flex-shrink-0">{{ $initials }}</div>
                                    @endif
                                    <div class="min-w-0">
                                        <span class="inline-block px-2.5 py-0.5 bg-[#FFF7ED] text-[#C96A2B] text-[10px] font-bold rounded-full font-display uppercase tracking-wider">{{ $bransAd }}</span>
                                        <h3 class="text-sm font-bold font-display text-[#111827] mt-1.5 truncate">
                                            {{ $doktor->unvan ? $doktor->unvan.' ' : '' }}{{ $doktor->ad_soyad }}
                                        </h3>
                                        <p class="text-[11px] text-[#6B7280] mt-0.5 truncate">
                                            {{ $doktor->uzmanlik_alani ?? $bransAd }}@if($doktor->il) · {{ $doktor->il->ad }}@endif
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3 py-3 border-t border-b border-[#E5E7EB] mb-5 text-xs font-semibold">
                                    <div>
                                        <span class="text-[10px] text-[#6B7280] block font-bold uppercase font-display">Memnuniyet</span>
                                        <span class="text-[#111827] mt-1 flex items-center gap-1">
                                            <span class="text-[#C96A2B]">★</span> {{ $ortalamaPuan ?: '—' }}
                                            <span class="text-[#6B7280] font-normal">({{ $yorumSayisi }})</span>
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-[#6B7280] block font-bold uppercase font-display">Konum</span>
                                        <span class="text-[#111827] mt-1 block truncate">
                                            {{ $doktor->il?->ad ?? '—' }}{{ $doktor->ilce ? ', '.$doktor->ilce->ad : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ $doktor->profil_url }}" class="w-full text-center py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all shadow-sm block font-display no-underline">
                                Online Randevu Al
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="ra-slider-item w-full">
                        <div class="text-center text-[#6B7280] py-10 text-sm">Henüz öne çıkan uzman bulunmuyor.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Öne çıkan klinikler --}}
    <section id="klinikler" class="bg-white border-t border-b border-[#E5E7EB] py-12 md:py-16 select-none">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Öne Çıkan Klinikler</h2>
                    <p class="text-sm text-[#6B7280] mt-2">Platformdaki aktif klinik ve poliklinikler.</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-2">
                        <button type="button" class="ra-slider-nav" data-slider-prev="klinikler" aria-label="Önceki">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" class="ra-slider-nav" data-slider-next="klinikler" aria-label="Sonraki">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    <a href="{{ route('frontend.hekimler', ['sadece_klinik' => 1]) }}" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] transition-colors font-display no-underline ml-1">
                        Tümü
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <div class="ra-slider" data-slider="klinikler">
                <div class="ra-slider-track" data-slider-track="klinikler">
                    @forelse(($oneCikanKlinikler ?? collect()) as $klinik)
                        @php
                            $klinikUrl = route('frontend.klinik.profil', [
                                'il_slug' => $klinik->il->slug ?? 'il',
                                'ilce_slug' => $klinik->ilce->slug ?? 'ilce',
                                'klinik_slug' => $klinik->slug,
                            ]);
                            $initials = mb_strtoupper(mb_substr($klinik->ad, 0, 2));
                        @endphp
                        <div class="ra-slider-item">
                            <a href="{{ $klinikUrl }}" class="ra-card p-5 flex flex-col justify-between min-h-[200px] block no-underline group">
                                <div class="flex items-start gap-4">
                                    @if($klinik->logo)
                                        <img src="{{ asset($klinik->logo) }}" alt="{{ $klinik->ad }}" class="w-14 h-14 rounded-2xl object-cover border border-[#E5E7EB] flex-shrink-0" loading="lazy">
                                    @else
                                        <div class="w-14 h-14 rounded-2xl bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center font-extrabold text-sm font-display flex-shrink-0">{{ $initials }}</div>
                                    @endif
                                    <div class="min-w-0">
                                        <span class="inline-block px-2 py-0.5 text-[9px] uppercase font-bold tracking-wider rounded bg-[#C96A2B]/10 text-[#C96A2B] border border-[#C96A2B]/20">Klinik</span>
                                        <h3 class="text-sm font-bold font-display text-[#111827] mt-1.5 group-hover:text-[#C96A2B] transition-colors line-clamp-2">{{ $klinik->ad }}</h3>
                                        <p class="text-[11px] text-[#6B7280] mt-1 truncate">
                                            {{ $klinik->il?->ad }}{{ $klinik->ilce?->ad ? ', '.$klinik->ilce->ad : '' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-[10px] font-bold text-[#C96A2B] bg-[#FFF7ED] px-2.5 py-1 rounded-full">
                                        {{ (int) ($klinik->doktorlar_count ?? 0) }} Uzman
                                    </span>
                                    <span class="text-xs font-bold text-[#C96A2B]">İncele →</span>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="ra-slider-item w-full">
                            <div class="text-center text-[#6B7280] py-10 text-sm">Henüz öne çıkan klinik bulunmuyor.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- Hizmetler --}}
    <section id="hizmetler" class="max-w-7xl mx-auto px-6 py-12 md:py-16 select-none">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Hizmetler</h2>
                <p class="text-sm text-[#6B7280] mt-2">Uzmanların sunduğu randevu alınabilir hizmetler.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="ra-slider-nav" data-slider-prev="hizmetler" aria-label="Önceki">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" class="ra-slider-nav" data-slider-next="hizmetler" aria-label="Sonraki">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <a href="{{ route('frontend.hekimler') }}" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] transition-colors font-display no-underline ml-1">
                    Keşfet
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <div class="ra-slider" data-slider="hizmetler">
            <div class="ra-slider-track" data-slider-track="hizmetler">
                @forelse(($oneCikanHizmetler ?? collect()) as $hizmet)
                    <div class="ra-slider-item">
                        <a href="{{ $hizmet->url }}" class="ra-card overflow-hidden flex flex-col min-h-[260px] block no-underline group">
                            @if($hizmet->resim)
                                <div class="aspect-[16/10] overflow-hidden bg-slate-50">
                                    <img src="{{ asset($hizmet->resim) }}"
                                         alt="{{ $hizmet->ad }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                </div>
                            @else
                                <div class="aspect-[16/10] bg-gradient-to-br from-[#FFF7ED] to-[#FEE2C5] flex items-center justify-center">
                                    <svg class="w-10 h-10 text-[#C96A2B]/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            @endif
                            <div class="p-4 flex flex-col flex-grow">
                                <h3 class="text-sm font-bold font-display text-[#111827] group-hover:text-[#C96A2B] transition-colors line-clamp-2">{{ $hizmet->ad }}</h3>
                                <p class="text-[11px] text-[#6B7280] mt-1.5 line-clamp-2 flex-grow">
                                    @if($hizmet->aciklama)
                                        {{ Str::limit(strip_tags($hizmet->aciklama), 90) }}
                                    @else
                                        {{ $hizmet->doktor ? (($hizmet->doktor->unvan ? $hizmet->doktor->unvan.' ' : '').$hizmet->doktor->ad_soyad) : 'Uzman hizmeti' }}
                                    @endif
                                </p>
                                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                    <span class="font-semibold text-[#6B7280] truncate max-w-[55%]">
                                        {{ $hizmet->doktor ? (($hizmet->doktor->unvan ? $hizmet->doktor->unvan.' ' : '').$hizmet->doktor->ad_soyad) : '' }}
                                    </span>
                                    @if($hizmet->fiyat)
                                        <span class="font-bold text-[#C96A2B] tabular-nums">{{ number_format((float) $hizmet->fiyat, 0, ',', '.') }} ₺</span>
                                    @elseif($hizmet->sure)
                                        <span class="font-bold text-[#C96A2B]">{{ $hizmet->sure }} dk</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="ra-slider-item w-full">
                        <div class="text-center text-[#6B7280] py-10 text-sm">Henüz listelenecek hizmet bulunmuyor.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Blog yazıları --}}
    @if(isset($sonBloglar) && $sonBloglar->count() > 0)
    <section id="bloglar" class="bg-white border-t border-[#E5E7EB] py-12 md:py-16 select-none">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Uzman Yazıları</h2>
                    <p class="text-sm text-[#6B7280] mt-2">Uzmanlarımızın güncel bilgilendirme yazıları.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="ra-slider-nav" data-slider-prev="bloglar" aria-label="Önceki">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" class="ra-slider-nav" data-slider-next="bloglar" aria-label="Sonraki">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <a href="{{ route('frontend.blog.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] transition-colors font-display no-underline ml-1">
                        Tüm Yazılar
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <div class="ra-slider" data-slider="bloglar">
                <div class="ra-slider-track" data-slider-track="bloglar">
                    @foreach($sonBloglar as $blog)
                        <div class="ra-slider-item">
                            <a href="{{ $blog->url }}" class="ra-card overflow-hidden flex flex-col min-h-[260px] block no-underline group">
                                @if($blog->resim)
                                    <div class="aspect-video overflow-hidden">
                                        <img src="{{ asset($blog->resim) }}" alt="{{ $blog->baslik }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                    </div>
                                @else
                                    <div class="aspect-video bg-gradient-to-br from-[#FFF7ED] to-[#FEE2C5] flex items-center justify-center">
                                        <svg class="w-12 h-12 text-[#C96A2B]/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="p-4 flex flex-col flex-grow">
                                    <h3 class="text-sm font-bold font-display text-[#111827] mb-2 group-hover:text-[#C96A2B] transition-colors line-clamp-2">{{ $blog->baslik }}</h3>
                                    <div class="mt-auto flex items-center justify-between text-[10px] text-[#6B7280] pt-2">
                                        <span class="truncate max-w-[65%]">{{ $blog->doktor ? (($blog->doktor->unvan ? $blog->doktor->unvan.' ' : '').$blog->doktor->ad_soyad) : '' }}</span>
                                        <span>{{ $blog->created_at->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Yorumlar (kısa slider) --}}
    @if(isset($sonYorumlar) && $sonYorumlar->count() > 0)
    <section class="border-t border-[#E5E7EB] py-12 md:py-16 select-none">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Hastalarımız Ne Diyor?</h2>
                    <p class="text-sm text-[#6B7280] mt-2">Platformumuzdan hizmet alan hastaların değerlendirmeleri.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="ra-slider-nav" data-slider-prev="yorumlar" aria-label="Önceki">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" class="ra-slider-nav" data-slider-next="yorumlar" aria-label="Sonraki">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <div class="ra-slider" data-slider="yorumlar">
                <div class="ra-slider-track" data-slider-track="yorumlar">
                    @foreach($sonYorumlar as $yorum)
                        <div class="ra-slider-item">
                            <div class="ra-card p-5 min-h-[200px] flex flex-col bg-[#FAFAFA]">
                                <div class="flex items-center gap-0.5 mb-3">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= $yorum->puan ? 'text-[#C96A2B]' : 'text-[#D1D5DB]' }} text-sm">★</span>
                                    @endfor
                                </div>
                                <p class="text-sm text-[#374151] leading-relaxed mb-4 italic flex-grow">"{{ Str::limit($yorum->yorum, 140) }}"</p>
                                <div class="flex items-center justify-between pt-2 border-t border-[#E5E7EB]">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-[#111827] truncate">{{ $yorum->hasta ? $yorum->hasta->maskeli_ad : 'Anonim Hasta' }}</p>
                                        <p class="text-[10px] text-[#6B7280] mt-0.5 truncate">
                                            {{ $yorum->doktor ? (($yorum->doktor->unvan ? $yorum->doktor->unvan.' ' : '').$yorum->doktor->ad_soyad) : '' }}
                                        </p>
                                    </div>
                                    <span class="text-[10px] text-[#9CA3AF] flex-shrink-0 ml-2">{{ $yorum->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif
@endsection

@section('script')
    <script>
        function setSearch(val) {
            const bar = document.getElementById('searchBar');
            if (bar) {
                bar.value = val;
                performSearch();
            }
        }

        function performSearch() {
            const query = document.getElementById('searchBar').value.trim();
            if (query !== '') {
                window.location.href = "{{ route('frontend.hekimler') }}?arama=" + encodeURIComponent(query);
            } else {
                window.location.href = "{{ route('frontend.hekimler') }}";
            }
        }

        (function initRaSliders() {
            function step(track) {
                const item = track.querySelector('.ra-slider-item');
                if (!item) return 280;
                const styles = window.getComputedStyle(track);
                const gap = parseFloat(styles.columnGap || styles.gap || '20') || 20;
                return item.getBoundingClientRect().width + gap;
            }

            function updateNav(name) {
                const track = document.querySelector('[data-slider-track="' + name + '"]');
                const prev = document.querySelector('[data-slider-prev="' + name + '"]');
                const next = document.querySelector('[data-slider-next="' + name + '"]');
                if (!track) return;
                const max = track.scrollWidth - track.clientWidth - 2;
                if (prev) prev.disabled = track.scrollLeft <= 2;
                if (next) next.disabled = track.scrollLeft >= max;
            }

            document.querySelectorAll('[data-slider-track]').forEach(function (track) {
                const name = track.getAttribute('data-slider-track');
                track.addEventListener('scroll', function () { updateNav(name); }, { passive: true });
                updateNav(name);
            });

            document.querySelectorAll('[data-slider-prev], [data-slider-next]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const name = btn.getAttribute('data-slider-prev') || btn.getAttribute('data-slider-next');
                    const track = document.querySelector('[data-slider-track="' + name + '"]');
                    if (!track) return;
                    const dir = btn.hasAttribute('data-slider-prev') ? -1 : 1;
                    track.scrollBy({ left: dir * step(track), behavior: 'smooth' });
                    setTimeout(function () { updateNav(name); }, 350);
                });
            });

            window.addEventListener('resize', function () {
                document.querySelectorAll('[data-slider-track]').forEach(function (track) {
                    updateNav(track.getAttribute('data-slider-track'));
                });
            });
        })();
    </script>
@endsection
