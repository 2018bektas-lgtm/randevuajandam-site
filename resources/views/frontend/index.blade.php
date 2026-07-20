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
        /* E-ticaret tarzı otomatik kayan ürün rayı */
        .ra-rail-section { position: relative; }
        .ra-rail-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .ra-rail-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.625rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #C96A2B;
            margin-bottom: 0.4rem;
        }
        .ra-rail-kicker::before {
            content: '';
            width: 1.25rem;
            height: 2px;
            border-radius: 99px;
            background: linear-gradient(90deg, #C96A2B, #E7B58A);
        }
        .ra-rail {
            position: relative;
            margin-left: -0.25rem;
            margin-right: -0.25rem;
        }
        .ra-rail::before,
        .ra-rail::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 1rem;
            width: 2.5rem;
            z-index: 5;
            pointer-events: none;
        }
        .ra-rail::before {
            left: 0;
            background: linear-gradient(90deg, var(--ra-fade, #FAFAFA) 0%, transparent 100%);
        }
        .ra-rail::after {
            right: 0;
            background: linear-gradient(270deg, var(--ra-fade, #FAFAFA) 0%, transparent 100%);
        }
        .ra-rail--white { --ra-fade: #ffffff; }
        .ra-rail--stone { --ra-fade: #FAFAFA; }

        .ra-rail-track {
            display: flex;
            gap: 1.1rem;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: auto;
            -webkit-overflow-scrolling: touch;
            padding: 0.5rem 1.75rem 1.25rem;
            scrollbar-width: none;
            cursor: grab;
        }
        .ra-rail-track.is-dragging { cursor: grabbing; scroll-snap-type: none; }
        .ra-rail-track::-webkit-scrollbar { display: none; }

        .ra-rail-item {
            flex: 0 0 min(78vw, 17.5rem);
            scroll-snap-align: start;
            min-width: 0;
        }
        @media (min-width: 640px) {
            .ra-rail-item { flex-basis: 16.5rem; }
        }
        @media (min-width: 1024px) {
            .ra-rail-item { flex-basis: 17.25rem; }
        }
        @media (min-width: 1280px) {
            .ra-rail-item { flex-basis: 18rem; }
        }

        .ra-rail-nav {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 9999px;
            border: 1px solid rgba(229, 231, 235, 0.95);
            background: rgba(255,255,255,0.95);
            color: #6B7280;
            box-shadow: 0 8px 22px -10px rgba(31,41,55,0.25);
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .ra-rail-nav:hover {
            color: #C96A2B;
            border-color: rgba(231, 181, 138, 0.65);
            background: #FFF7ED;
            transform: scale(1.04);
        }
        .ra-rail-nav:disabled {
            opacity: 0.35;
            pointer-events: none;
            transform: none;
        }

        /* Ürün kartı (şeffaf e-ticaret hissi) */
        .ra-product {
            height: 100%;
            display: flex;
            flex-direction: column;
            background: #fff;
            border: 1px solid rgba(229, 231, 235, 0.95);
            border-radius: 1.35rem;
            overflow: hidden;
            box-shadow: 0 10px 30px -18px rgba(31, 41, 55, 0.28);
            transition: transform 0.28s cubic-bezier(0.22,1,0.36,1), box-shadow 0.28s ease, border-color 0.28s ease;
            text-decoration: none;
            color: inherit;
        }
        .ra-product:hover {
            transform: translateY(-6px);
            border-color: rgba(231, 181, 138, 0.55);
            box-shadow: 0 22px 40px -18px rgba(201, 106, 43, 0.28);
        }
        .ra-product-media {
            position: relative;
            aspect-ratio: 4 / 3;
            background: linear-gradient(145deg, #FFF7ED 0%, #F8FAFC 100%);
            overflow: hidden;
        }
        .ra-product-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.55s cubic-bezier(0.22,1,0.36,1);
        }
        .ra-product:hover .ra-product-media img { transform: scale(1.07); }
        .ra-product-badge {
            position: absolute;
            top: 0.75rem;
            left: 0.75rem;
            z-index: 2;
            padding: 0.28rem 0.55rem;
            border-radius: 9999px;
            font-size: 0.5625rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #C96A2B;
            background: rgba(255,255,255,0.94);
            border: 1px solid rgba(231, 181, 138, 0.35);
            backdrop-filter: blur(8px);
        }
        .ra-product-body {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 1rem 1.05rem 1.1rem;
            gap: 0.35rem;
        }
        .ra-product-title {
            font-size: 0.9rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #111827;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            transition: color 0.2s ease;
        }
        .ra-product:hover .ra-product-title { color: #C96A2B; }
        .ra-product-meta {
            font-size: 0.7rem;
            color: #6B7280;
            line-height: 1.35;
        }
        .ra-product-foot {
            margin-top: auto;
            padding-top: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            border-top: 1px solid #F1F5F9;
        }
        .ra-product-price {
            font-size: 0.95rem;
            font-weight: 800;
            color: #C96A2B;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.02em;
        }
        .ra-product-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.1rem;
            padding: 0 0.85rem;
            border-radius: 0.75rem;
            background: #C96A2B;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .ra-product:hover .ra-product-cta {
            background: #B55A20;
            transform: translateX(1px);
        }

        /* Uzman kartı (profil odaklı ürün) */
        .ra-expert-top {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 1.35rem 1.1rem 0.85rem;
            background: linear-gradient(180deg, #FFF7ED 0%, #FFFFFF 72%);
            border-bottom: 1px solid #F8FAFC;
        }
        .ra-expert-avatar {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 1.15rem;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.95);
            box-shadow: 0 10px 24px -12px rgba(201, 106, 43, 0.55);
            background: #FFF7ED;
            color: #C96A2B;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1rem;
        }
        .ra-stars { color: #C96A2B; letter-spacing: 0.05em; font-size: 0.7rem; }

        .ra-quote-card {
            height: 100%;
            padding: 1.15rem;
            border-radius: 1.35rem;
            background: linear-gradient(160deg, #FFFFFF 0%, #FFFBF7 100%);
            border: 1px solid #E5E7EB;
            box-shadow: 0 10px 30px -18px rgba(31, 41, 55, 0.25);
        }

        @media (prefers-reduced-motion: reduce) {
            .ra-product, .ra-product-media img { transition: none; }
        }
    </style>

    {{-- Öne çıkan uzmanlar --}}
    <section id="doktorlar" class="ra-rail-section max-w-7xl mx-auto px-4 sm:px-6 pt-14 pb-8 md:pt-4 md:pb-10 select-none">
        <div class="ra-rail-head">
            <div>
                <div class="ra-rail-kicker">Seçkiler</div>
                <h2 class="text-2xl md:text-3xl font-bold font-display text-[#111827] tracking-tight">Öne Çıkan Uzmanlar</h2>
                <p class="text-sm text-[#6B7280] mt-1.5">Danışan memnuniyeti yüksek, randevuya açık uzmanlar.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="ra-rail-nav" data-rail-prev="uzmanlar" aria-label="Önceki">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" class="ra-rail-nav" data-rail-next="uzmanlar" aria-label="Sonraki">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <a href="{{ route('frontend.hekimler') }}" class="ml-1 text-xs font-bold uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] font-display no-underline">Tümü →</a>
            </div>
        </div>

        <div class="ra-rail ra-rail--stone" data-rail="uzmanlar">
            <div class="ra-rail-track" data-rail-track="uzmanlar" data-autoplay="1" data-speed="0.55">
                @forelse($oneCikanDoktorlar as $doktor)
                    @php
                        $brans = $doktor->branslar->first();
                        $bransAd = $brans ? $brans->ad : 'Uzman';
                        $initials = collect(explode(' ', $doktor->ad_soyad))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
                        $ortalamaPuan = $doktor->ortalama_puan_cache ?? 0;
                        $yorumSayisi = $doktor->yorum_sayisi_cache ?? 0;
                    @endphp
                    <div class="ra-rail-item">
                        <a href="{{ $doktor->profil_url }}" class="ra-product group">
                            <div class="ra-expert-top">
                                <span class="ra-product-badge" style="position:static;margin-bottom:0.75rem">{{ $bransAd }}</span>
                                @if($doktor->profil_resmi)
                                    <img src="{{ asset($doktor->profil_resmi) }}" alt="{{ $doktor->ad_soyad }}" class="ra-expert-avatar" loading="lazy">
                                @else
                                    <div class="ra-expert-avatar">{{ $initials }}</div>
                                @endif
                                <h3 class="ra-product-title mt-3 px-1">{{ $doktor->unvan ? $doktor->unvan.' ' : '' }}{{ $doktor->ad_soyad }}</h3>
                                <p class="ra-product-meta mt-1 line-clamp-1">{{ $doktor->uzmanlik_alani ?? $bransAd }}@if($doktor->il) · {{ $doktor->il->ad }}@endif</p>
                                @if($ortalamaPuan)
                                    <div class="ra-stars mt-2">★ {{ $ortalamaPuan }} <span class="text-[#9CA3AF] font-semibold">({{ $yorumSayisi }})</span></div>
                                @endif
                            </div>
                            <div class="ra-product-body">
                                <div class="ra-product-foot !border-0 !pt-0">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#6B7280]">{{ $doktor->il?->ad ?? 'Türkiye' }}</span>
                                    <span class="ra-product-cta">Randevu Al</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="ra-rail-item" style="flex-basis:100%"><p class="text-center text-sm text-[#6B7280] py-10">Henüz öne çıkan uzman bulunmuyor.</p></div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Öne çıkan klinikler --}}
    <section id="klinikler" class="ra-rail-section bg-white border-y border-[#E5E7EB] py-4 md:py-4 select-none">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="ra-rail-head">
                <div>
                    <div class="ra-rail-kicker">Klinikler</div>
                    <h2 class="text-2xl md:text-3xl font-bold font-display text-[#111827] tracking-tight">Öne Çıkan Klinikler</h2>
                    <p class="text-sm text-[#6B7280] mt-1.5">Aktif klinik ve poliklinikler, tek bakışta.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="ra-rail-nav" data-rail-prev="klinikler" aria-label="Önceki">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" class="ra-rail-nav" data-rail-next="klinikler" aria-label="Sonraki">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <a href="{{ route('frontend.hekimler', ['sadece_klinik' => 1]) }}" class="ml-1 text-xs font-bold uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] font-display no-underline">Tümü →</a>
                </div>
            </div>

            <div class="ra-rail ra-rail--white" data-rail="klinikler">
                <div class="ra-rail-track" data-rail-track="klinikler" data-autoplay="1" data-speed="0.5">
                    @forelse(($oneCikanKlinikler ?? collect()) as $klinik)
                        @php
                            $klinikUrl = route('frontend.klinik.profil', [
                                'il_slug' => $klinik->il->slug ?? 'il',
                                'ilce_slug' => $klinik->ilce->slug ?? 'ilce',
                                'klinik_slug' => $klinik->slug,
                            ]);
                            $initials = mb_strtoupper(mb_substr($klinik->ad, 0, 2));
                        @endphp
                        <div class="ra-rail-item">
                            <a href="{{ $klinikUrl }}" class="ra-product">
                                <div class="ra-product-media">
                                    <span class="ra-product-badge">Klinik</span>
                                    @if($klinik->logo)
                                        <img src="{{ asset($klinik->logo) }}" alt="{{ $klinik->ad }}" loading="lazy">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <div class="w-16 h-16 rounded-2xl bg-white border border-[#E7B58A]/35 text-[#C96A2B] flex items-center justify-center font-extrabold text-lg shadow-sm">{{ $initials }}</div>
                                        </div>
                                    @endif
                                </div>
                                <div class="ra-product-body">
                                    <h3 class="ra-product-title">{{ $klinik->ad }}</h3>
                                    <p class="ra-product-meta">{{ $klinik->il?->ad }}{{ $klinik->ilce?->ad ? ', '.$klinik->ilce->ad : '' }}</p>
                                    <div class="ra-product-foot">
                                        <span class="ra-product-price" style="font-size:0.8rem">{{ (int) ($klinik->doktorlar_count ?? 0) }} uzman</span>
                                        <span class="ra-product-cta">İncele</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="ra-rail-item" style="flex-basis:100%"><p class="text-center text-sm text-[#6B7280] py-10">Henüz öne çıkan klinik bulunmuyor.</p></div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- Hizmetler --}}
    <section id="hizmetler" class="ra-rail-section max-w-7xl mx-auto px-4 sm:px-6 py-4 md:py-4 select-none">
        <div class="ra-rail-head">
            <div>
                <div class="ra-rail-kicker">Hizmetler</div>
                <h2 class="text-2xl md:text-3xl font-bold font-display text-[#111827] tracking-tight">Popüler Hizmetler</h2>
                <p class="text-sm text-[#6B7280] mt-1.5">Randevu alınabilir hizmetler, ürün vitrini gibi kayar.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="ra-rail-nav" data-rail-prev="hizmetler" aria-label="Önceki">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" class="ra-rail-nav" data-rail-next="hizmetler" aria-label="Sonraki">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <a href="{{ route('frontend.hekimler') }}" class="ml-1 text-xs font-bold uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] font-display no-underline">Keşfet →</a>
            </div>
        </div>

        <div class="ra-rail ra-rail--stone" data-rail="hizmetler">
            <div class="ra-rail-track" data-rail-track="hizmetler" data-autoplay="1" data-speed="0.6">
                @forelse(($oneCikanHizmetler ?? collect()) as $hizmet)
                    <div class="ra-rail-item">
                        <a href="{{ $hizmet->url }}" class="ra-product">
                            <div class="ra-product-media">
                                @if($hizmet->sure)
                                    <span class="ra-product-badge">{{ $hizmet->sure }} dk</span>
                                @endif
                                @if($hizmet->resim_url ?? $hizmet->resim)
                                    <img src="{{ $hizmet->resim_url ?? asset($hizmet->resim) }}" alt="{{ $hizmet->ad }}" loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[#C96A2B]/40">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="ra-product-body">
                                <h3 class="ra-product-title">{{ $hizmet->ad }}</h3>
                                <p class="ra-product-meta line-clamp-2">
                                    {{ $hizmet->doktor ? (($hizmet->doktor->unvan ? $hizmet->doktor->unvan.' ' : '').$hizmet->doktor->ad_soyad) : 'Uzman hizmeti' }}
                                </p>
                                <div class="ra-product-foot">
                                    <span class="ra-product-price" style="font-size:0.8rem">Randevu al</span>
                                    <span class="ra-product-cta">Seç</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="ra-rail-item" style="flex-basis:100%"><p class="text-center text-sm text-[#6B7280] py-10">Henüz listelenecek hizmet bulunmuyor.</p></div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Uzman blogları --}}
    @if(isset($sonBloglar) && $sonBloglar->count() > 0)
    <section id="bloglar" class="ra-rail-section bg-white border-t border-[#E5E7EB] py-4 md:py-4 select-none">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="ra-rail-head">
                <div>
                    <div class="ra-rail-kicker">İçerik</div>
                    <h2 class="text-2xl md:text-3xl font-bold font-display text-[#111827] tracking-tight">Uzman Blogları</h2>
                    <p class="text-sm text-[#6B7280] mt-1.5">Uzmanlardan güncel yazılar, otomatik vitrin.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="ra-rail-nav" data-rail-prev="bloglar" aria-label="Önceki">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" class="ra-rail-nav" data-rail-next="bloglar" aria-label="Sonraki">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <a href="{{ route('frontend.blog.index') }}" class="ml-1 text-xs font-bold uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] font-display no-underline">Tüm Bloglar →</a>
                </div>
            </div>

            <div class="ra-rail ra-rail--white" data-rail="bloglar">
                <div class="ra-rail-track" data-rail-track="bloglar" data-autoplay="1" data-speed="0.48">
                    @foreach($sonBloglar as $blog)
                        <div class="ra-rail-item">
                            <a href="{{ $blog->url }}" class="ra-product">
                                <div class="ra-product-media">
                                    <span class="ra-product-badge">Blog</span>
                                    @if($blog->resim)
                                        <img src="{{ asset($blog->resim) }}" alt="{{ $blog->baslik }}" loading="lazy">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-[#C96A2B]/30">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ra-product-body">
                                    <h3 class="ra-product-title">{{ $blog->baslik }}</h3>
                                    <p class="ra-product-meta line-clamp-1">
                                        {{ $blog->doktor ? (($blog->doktor->unvan ? $blog->doktor->unvan.' ' : '').$blog->doktor->ad_soyad) : 'Uzman' }}
                                    </p>
                                    <div class="ra-product-foot">
                                        <span class="text-[10px] font-bold text-[#9CA3AF]">{{ $blog->created_at->format('d.m.Y') }}</span>
                                        <span class="ra-product-cta">Oku</span>
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

    {{-- Yorumlar --}}
    @if(isset($sonYorumlar) && $sonYorumlar->count() > 0)
    <section class="ra-rail-section border-t border-[#E5E7EB] py-4 md:py-4 select-none">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="ra-rail-head">
                <div>
                    <div class="ra-rail-kicker">Güven</div>
                    <h2 class="text-2xl md:text-3xl font-bold font-display text-[#111827] tracking-tight">Hastalarımız Ne Diyor?</h2>
                    <p class="text-sm text-[#6B7280] mt-1.5">Gerçek hasta değerlendirmeleri.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="ra-rail-nav" data-rail-prev="yorumlar" aria-label="Önceki">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" class="ra-rail-nav" data-rail-next="yorumlar" aria-label="Sonraki">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <div class="ra-rail ra-rail--stone" data-rail="yorumlar">
                <div class="ra-rail-track" data-rail-track="yorumlar" data-autoplay="1" data-speed="0.42">
                    @foreach($sonYorumlar as $yorum)
                        <div class="ra-rail-item">
                            <div class="ra-quote-card flex flex-col min-h-[200px]">
                                <div class="flex items-center gap-0.5 mb-3 text-[#C96A2B] text-sm">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= $yorum->puan ? '' : 'text-[#E5E7EB]' }}">★</span>
                                    @endfor
                                </div>
                                <p class="text-sm text-[#374151] leading-relaxed italic flex-grow">"{{ Str::limit($yorum->yorum, 130) }}"</p>
                                <div class="mt-4 pt-3 border-t border-[#F1F5F9] flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-[#111827] truncate">{{ $yorum->hasta ? $yorum->hasta->maskeli_ad : 'Anonim Hasta' }}</p>
                                        <p class="text-[10px] text-[#6B7280] truncate">{{ $yorum->doktor ? (($yorum->doktor->unvan ? $yorum->doktor->unvan.' ' : '').$yorum->doktor->ad_soyad) : '' }}</p>
                                    </div>
                                    <span class="text-[10px] text-[#9CA3AF] shrink-0">{{ $yorum->created_at->diffForHumans() }}</span>
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

        (function initRaRails() {
            var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            function step(track) {
                var item = track.querySelector('.ra-rail-item');
                if (!item) return 300;
                var styles = window.getComputedStyle(track);
                var gap = parseFloat(styles.columnGap || styles.gap || '18') || 18;
                return item.getBoundingClientRect().width + gap;
            }

            function maxScroll(track) {
                return Math.max(0, track.scrollWidth - track.clientWidth);
            }

            document.querySelectorAll('[data-rail-track]').forEach(function (track) {
                var name = track.getAttribute('data-rail-track');
                var autoplay = track.getAttribute('data-autoplay') === '1' && !reduceMotion;
                var speed = parseFloat(track.getAttribute('data-speed') || '0.5') || 0.5;
                var paused = false;
                var dragging = false;
                var dragStartX = 0;
                var dragScrollLeft = 0;
                var raf = null;

                function updateNav() {
                    var prev = document.querySelector('[data-rail-prev="' + name + '"]');
                    var next = document.querySelector('[data-rail-next="' + name + '"]');
                    var max = maxScroll(track);
                    if (prev) prev.disabled = track.scrollLeft <= 2;
                    if (next) next.disabled = track.scrollLeft >= max - 2;
                }

                // Sonsuz vitrin: içerik yetersizse kopyala
                if (autoplay && track.children.length > 1) {
                    var kids = Array.prototype.slice.call(track.children);
                    kids.forEach(function (node) {
                        var clone = node.cloneNode(true);
                        clone.setAttribute('aria-hidden', 'true');
                        track.appendChild(clone);
                    });
                }

                function tick() {
                    if (!autoplay || paused || dragging) {
                        raf = requestAnimationFrame(tick);
                        return;
                    }
                    var max = maxScroll(track);
                    if (max <= 4) {
                        raf = requestAnimationFrame(tick);
                        return;
                    }
                    track.scrollLeft += speed;
                    // Ortaya gelince başa sar (kopyalanmış set sayesinde akıcı)
                    if (track.scrollLeft >= max / 2) {
                        track.scrollLeft = track.scrollLeft - max / 2;
                    }
                    updateNav();
                    raf = requestAnimationFrame(tick);
                }

                track.addEventListener('mouseenter', function () { paused = true; });
                track.addEventListener('mouseleave', function () { paused = false; });
                track.addEventListener('touchstart', function () { paused = true; }, { passive: true });
                track.addEventListener('touchend', function () { setTimeout(function () { paused = false; }, 1200); }, { passive: true });

                // Sürükle-kaydır
                track.addEventListener('mousedown', function (e) {
                    dragging = true;
                    track.classList.add('is-dragging');
                    dragStartX = e.pageX;
                    dragScrollLeft = track.scrollLeft;
                    paused = true;
                });
                window.addEventListener('mouseup', function () {
                    if (!dragging) return;
                    dragging = false;
                    track.classList.remove('is-dragging');
                    setTimeout(function () { paused = false; }, 800);
                });
                window.addEventListener('mousemove', function (e) {
                    if (!dragging) return;
                    e.preventDefault();
                    var dx = e.pageX - dragStartX;
                    track.scrollLeft = dragScrollLeft - dx;
                });

                track.addEventListener('scroll', updateNav, { passive: true });
                updateNav();

                if (autoplay) raf = requestAnimationFrame(tick);
            });

            document.querySelectorAll('[data-rail-prev], [data-rail-next]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var name = btn.getAttribute('data-rail-prev') || btn.getAttribute('data-rail-next');
                    var track = document.querySelector('[data-rail-track="' + name + '"]');
                    if (!track) return;
                    var dir = btn.hasAttribute('data-rail-prev') ? -1 : 1;
                    track.scrollBy({ left: dir * step(track), behavior: 'smooth' });
                });
            });
        })();
    </script>
@endsection
