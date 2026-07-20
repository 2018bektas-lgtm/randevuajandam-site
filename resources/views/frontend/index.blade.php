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

    @include('frontend.partials.home-vitrin')
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

        (function initRaVitrin() {
            var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            function itemStep(track) {
                var item = track.querySelector('.ra-vitrin-item');
                if (!item) return 280;
                var styles = window.getComputedStyle(track);
                var gap = parseFloat(styles.columnGap || styles.gap || '16') || 16;
                return item.getBoundingClientRect().width + gap;
            }

            document.querySelectorAll('[data-vitrin-track]').forEach(function (track) {
                var name = track.getAttribute('data-vitrin-track');
                var baseItems = Array.prototype.slice.call(track.children);
                if (!baseItems.length) return;

                var baseCount = baseItems.length;
                var canSlide = baseCount > 1;
                var speed = parseFloat(track.getAttribute('data-speed') || '0.6') || 0.6;
                if (reduceMotion) speed = 0;

                function setWidth() {
                    // gap + item widths for one original set
                    var styles = window.getComputedStyle(track);
                    var gap = parseFloat(styles.columnGap || styles.gap || '16') || 16;
                    var total = 0;
                    for (var i = 0; i < baseCount; i++) {
                        var el = track.children[i];
                        if (!el) continue;
                        total += el.getBoundingClientRect().width;
                        if (i < baseCount - 1) total += gap;
                    }
                    // set width includes full gap after last item for seamless loop
                    if (baseCount > 0) total += gap;
                    return total;
                }

                if (canSlide) {
                    var viewportW = track.parentElement ? track.parentElement.clientWidth : 900;
                    var copies = 0;
                    var maxCopies = 10;
                    // En az 1 kopya; viewport 2.5x dolana kadar çoğalt
                    do {
                        baseItems.forEach(function (node) {
                            var clone = node.cloneNode(true);
                            clone.setAttribute('aria-hidden', 'true');
                            clone.querySelectorAll('a').forEach(function (a) { a.setAttribute('tabindex', '-1'); });
                            track.appendChild(clone);
                        });
                        copies++;
                    } while (setWidth() * (copies + 1) < viewportW * 2.5 && copies < maxCopies);
                }

                var x = 0;
                var loopW = 0;
                var paused = false;
                var dragging = false;
                var dragStartX = 0;
                var dragStartOffset = 0;
                var suppressClick = false;
                var raf = null;

                function measure() {
                    loopW = setWidth();
                    if (loopW <= 0) loopW = track.scrollWidth / 2 || 1;
                }

                function apply() {
                    track.style.transform = 'translate3d(' + (-x) + 'px,0,0)';
                }

                function normalize() {
                    if (loopW <= 0) return;
                    while (x >= loopW) x -= loopW;
                    while (x < 0) x += loopW;
                }

                function tick() {
                    if (canSlide && speed > 0 && !paused && !dragging) {
                        x += speed;
                        normalize();
                        apply();
                    }
                    raf = requestAnimationFrame(tick);
                }

                measure();
                apply();
                window.addEventListener('resize', function () {
                    measure();
                    normalize();
                    apply();
                }, { passive: true });

                var root = track.closest('[data-vitrin]') || track.parentElement;

                function pause() { paused = true; }
                function resume() { if (!dragging) paused = false; }

                if (root) {
                    root.addEventListener('mouseenter', pause);
                    root.addEventListener('mouseleave', resume);
                }
                track.addEventListener('touchstart', pause, { passive: true });
                track.addEventListener('touchend', function () { setTimeout(resume, 1400); }, { passive: true });

                track.addEventListener('pointerdown', function (e) {
                    if (e.button !== undefined && e.button !== 0) return;
                    dragging = true;
                    suppressClick = false;
                    dragStartX = e.clientX;
                    dragStartOffset = x;
                    track.classList.add('is-dragging');
                    try { track.setPointerCapture(e.pointerId); } catch (err) {}
                    pause();
                });
                track.addEventListener('pointermove', function (e) {
                    if (!dragging) return;
                    var dx = e.clientX - dragStartX;
                    if (Math.abs(dx) > 4) suppressClick = true;
                    x = dragStartOffset - dx;
                    normalize();
                    apply();
                });
                function endDrag(e) {
                    if (!dragging) return;
                    dragging = false;
                    track.classList.remove('is-dragging');
                    try { track.releasePointerCapture(e.pointerId); } catch (err) {}
                    setTimeout(resume, 900);
                }
                track.addEventListener('pointerup', endDrag);
                track.addEventListener('pointercancel', endDrag);

                track.addEventListener('click', function (e) {
                    if (suppressClick) {
                        e.preventDefault();
                        e.stopPropagation();
                        suppressClick = false;
                    }
                }, true);

                var prev = document.querySelector('[data-vitrin-prev="' + name + '"]');
                var next = document.querySelector('[data-vitrin-next="' + name + '"]');
                function nudge(dir) {
                    if (!canSlide) return;
                    pause();
                    x += dir * itemStep(track);
                    normalize();
                    apply();
                    setTimeout(resume, 1600);
                }
                if (prev) prev.addEventListener('click', function () { nudge(-1); });
                if (next) next.addEventListener('click', function () { nudge(1); });

                if (canSlide) raf = requestAnimationFrame(tick);
            });
        })();
    </script>
@endsection
