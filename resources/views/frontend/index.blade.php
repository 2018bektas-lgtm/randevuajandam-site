@extends('frontend.layouts.app')

@section('baslik', 'Randevu Ajandam - Uzman Doktor ve Randevu Platformu')

@section('icerik')
    @php
        $heroStats = isset($istatistikler) ? [
            [
                'value' => number_format($istatistikler['doktor_sayisi']).'+',
                'label' => 'Aktif Uzman',
                'delay' => '0s',
                'draw' => '0.1s',
                'side' => 'tl',
            ],
            [
                'value' => number_format($istatistikler['randevu_sayisi']).'+',
                'label' => 'Randevu',
                'delay' => '0.6s',
                'draw' => '0.35s',
                'side' => 'tr',
            ],
            [
                'value' => number_format($istatistikler['yorum_sayisi']).'+',
                'label' => 'Hasta Yorumu',
                'delay' => '1.2s',
                'draw' => '0.6s',
                'side' => 'bl',
            ],
            [
                'value' => (string) $istatistikler['brans_sayisi'],
                'label' => 'Uzmanlık',
                'delay' => '1.8s',
                'draw' => '0.85s',
                'side' => 'br',
            ],
        ] : [];
        // SVG daire çevresi: r=42 → 2πr ≈ 263.9
        $heroCircleLen = 264;
    @endphp

    <style>
        /* Animasyonlu daire çizimi (stroke-dash) */
        @keyframes hero-circle-draw {
            0% { stroke-dashoffset: {{ $heroCircleLen }}; opacity: 0.35; }
            12% { opacity: 1; }
            55% { stroke-dashoffset: 0; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes hero-circle-spin {
            0% { transform: rotate(-90deg); }
            100% { transform: rotate(270deg); }
        }
        @keyframes hero-bob {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        @keyframes hero-fade-up {
            from { opacity: 0; transform: translateY(10px) scale(0.92); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes hero-soft-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(201, 106, 43, 0.18); }
            50% { box-shadow: 0 0 0 10px rgba(201, 106, 43, 0); }
        }

        .hero-stage {
            position: relative;
            max-width: 56rem;
            margin-left: auto;
            margin-right: auto;
        }

        /* 4 kenar: yazının etrafında */
        .hero-ring-stat {
            position: absolute;
            z-index: 20;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
            width: 6.25rem;
            text-align: center;
            pointer-events: none;
            animation: hero-fade-up 0.6s ease-out both;
        }
        .hero-ring-stat--tl { top: -0.5rem; left: 0; animation-delay: 0.05s; }
        .hero-ring-stat--tr { top: -0.5rem; right: 0; animation-delay: 0.15s; }
        .hero-ring-stat--bl { bottom: -0.5rem; left: 0; animation-delay: 0.25s; }
        .hero-ring-stat--br { bottom: -0.5rem; right: 0; animation-delay: 0.35s; }

        @media (min-width: 768px) {
            .hero-ring-stat { width: 7rem; }
            .hero-ring-stat--tl { top: -0.75rem; left: -0.75rem; }
            .hero-ring-stat--tr { top: -0.75rem; right: -0.75rem; }
            .hero-ring-stat--bl { bottom: -0.75rem; left: -0.75rem; }
            .hero-ring-stat--br { bottom: -0.75rem; right: -0.75rem; }
        }
        @media (min-width: 1024px) {
            .hero-ring-stat--tl { top: -0.5rem; left: -2rem; }
            .hero-ring-stat--tr { top: -0.5rem; right: -2rem; }
            .hero-ring-stat--bl { bottom: -0.5rem; left: -2rem; }
            .hero-ring-stat--br { bottom: -0.5rem; right: -2rem; }
        }
        @media (min-width: 1280px) {
            .hero-ring-stat--tl { top: -0.25rem; left: -4rem; }
            .hero-ring-stat--tr { top: -0.25rem; right: -4rem; }
            .hero-ring-stat--bl { bottom: -0.25rem; left: -4rem; }
            .hero-ring-stat--br { bottom: -0.25rem; right: -4rem; }
        }

        .hero-ring-bob {
            animation: hero-bob 5s ease-in-out infinite;
        }
        .hero-ring-stat--tr .hero-ring-bob { animation-delay: 0.7s; }
        .hero-ring-stat--bl .hero-ring-bob { animation-delay: 1.4s; }
        .hero-ring-stat--br .hero-ring-bob { animation-delay: 2.1s; }

        .hero-ring {
            position: relative;
            width: 5.25rem;
            height: 5.25rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(231, 181, 138, 0.35);
            box-shadow: 0 10px 28px -10px rgba(31, 41, 55, 0.14);
            animation: hero-soft-pulse 3.2s ease-in-out infinite;
        }
        @media (min-width: 768px) {
            .hero-ring { width: 5.75rem; height: 5.75rem; }
        }

        .hero-ring svg {
            position: absolute;
            inset: -4px;
            width: calc(100% + 8px);
            height: calc(100% + 8px);
            overflow: visible;
            transform-origin: 50% 50%;
            /* Üstten çizime başla + yavaş dönüş */
            animation: hero-circle-spin 12s linear infinite;
        }
        .hero-ring-track {
            fill: none;
            stroke: rgba(231, 181, 138, 0.28);
            stroke-width: 2.75;
        }
        .hero-ring-progress {
            fill: none;
            stroke: #C96A2B;
            stroke-width: 3.5;
            stroke-linecap: round;
            stroke-dasharray: {{ $heroCircleLen }};
            stroke-dashoffset: {{ $heroCircleLen }};
            animation: hero-circle-draw 2.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            /* spin ile birlikte dönüş için transform SVG'de */
            transform-origin: 50% 50%;
        }

        .hero-ring-core {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.35rem;
            z-index: 1;
        }
        .hero-ring-value {
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #C96A2B;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }
        @media (min-width: 768px) {
            .hero-ring-value { font-size: 1.05rem; }
        }
        .hero-ring-label {
            font-size: 0.5625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6B7280;
            line-height: 1.15;
            max-width: 6.5rem;
        }

        .hero-text-frame {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 1.25rem 5.25rem 1.5rem;
        }
        @media (min-width: 768px) {
            .hero-text-frame { padding: 1.5rem 6.25rem 1.75rem; }
        }
        @media (min-width: 1024px) {
            .hero-text-frame { padding: 1.75rem 7rem 2rem; }
        }
        .hero-search-block {
            position: relative;
            z-index: 10;
            text-align: center;
            margin-top: 1.75rem;
        }
        @media (max-width: 639px) {
            /* Mobil: daireler yazının üstünde 2x2 grid */
            .hero-stage { max-width: 100%; }
            .hero-ring-stat {
                position: static;
                width: auto;
                animation: hero-fade-up 0.5s ease-out both;
            }
            .hero-ring-stats-mobile {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.85rem 0.5rem;
                justify-items: center;
                margin-bottom: 1.25rem;
            }
            .hero-text-frame { padding: 0 0.25rem 0.5rem; }
            .hero-ring-stats-desktop { display: none !important; }
            .hero-ring { width: 5rem; height: 5rem; }
            .hero-ring-bob { animation: none; }
        }
        @media (min-width: 640px) {
            .hero-ring-stats-mobile { display: none !important; }
            .hero-ring-stats-desktop { display: contents; }
        }

        @media (prefers-reduced-motion: reduce) {
            .hero-ring-progress,
            .hero-ring svg,
            .hero-ring-bob,
            .hero-ring-stat,
            .hero-ring {
                animation: none !important;
            }
            .hero-ring svg {
                transform: rotate(-90deg);
            }
            .hero-ring-progress {
                stroke-dashoffset: 0 !important;
            }
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white border-b border-[#E5E7EB] pt-12 pb-14 md:pt-20 md:pb-24 lg:pt-24 lg:pb-28 select-none">
        <!-- Background Ambient Lights (ayrı katmanda clip — daireler kesilmesin) -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
            <div class="absolute top-[-30%] right-[-10%] w-[550px] h-[550px] rounded-full bg-[#E7B58A]/10 blur-[130px]"></div>
            <div class="absolute bottom-[-20%] left-[-10%] w-[550px] h-[550px] rounded-full bg-[#C96A2B]/4 blur-[130px]"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="hero-stage">
                @if(!empty($heroStats))
                    {{-- Mobil: yazının üstünde 2x2 daireler --}}
                    <div class="hero-ring-stats-mobile" role="list" aria-label="Platform istatistikleri">
                        @foreach($heroStats as $stat)
                            <div class="hero-ring-stat" role="listitem" style="animation-delay: {{ $stat['draw'] }}">
                                <div class="hero-ring-bob">
                                    <div class="hero-ring">
                                        <svg viewBox="0 0 100 100" style="animation-delay: {{ $stat['delay'] }}">
                                            <circle class="hero-ring-track" cx="50" cy="50" r="42"></circle>
                                            <circle class="hero-ring-progress" cx="50" cy="50" r="42"
                                                    style="animation-delay: {{ $stat['draw'] }}"></circle>
                                        </svg>
                                        <div class="hero-ring-core">
                                            <span class="hero-ring-value">{{ $stat['value'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="hero-ring-label">{{ $stat['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Başlık metni + 4 kenar daire --}}
                <div class="hero-text-frame">
                    @if(!empty($heroStats))
                        <div class="hero-ring-stats-desktop" role="list" aria-label="Platform istatistikleri">
                            @foreach($heroStats as $stat)
                                <div class="hero-ring-stat hero-ring-stat--{{ $stat['side'] }}" role="listitem">
                                    <div class="hero-ring-bob">
                                        <div class="hero-ring">
                                            <svg viewBox="0 0 100 100" style="animation-delay: {{ $stat['delay'] }}">
                                                <circle class="hero-ring-track" cx="50" cy="50" r="42"></circle>
                                                <circle class="hero-ring-progress" cx="50" cy="50" r="42"
                                                        style="animation-delay: {{ $stat['draw'] }}"></circle>
                                            </svg>
                                            <div class="hero-ring-core">
                                                <span class="hero-ring-value">{{ $stat['value'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="hero-ring-label">{{ $stat['label'] }}</span>
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

                {{-- Arama (dairelerin dışında) --}}
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

                    <div class="mt-5 flex items-center justify-center flex-wrap gap-2 text-xs">
                        <span class="text-[#6B7280] font-medium mr-1.5">Popüler:</span>
                        @foreach($populerAramalar as $arama)
                            <button type="button" onclick="setSearch(@js($arama))" class="px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-slate-50 hover:bg-[#FFF7ED] hover:text-[#C96A2B] hover:border-[#E7B58A]/30 transition-all font-semibold cursor-pointer">{{ $arama }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section (Dinamik) -->
    <section id="hizmetler" class="max-w-7xl mx-auto px-6 py-20 select-none">
        <div class="text-center max-w-xl mx-auto mb-16">
            <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Kategorilere Göre Keşfedin</h2>
            <p class="text-sm text-[#6B7280] mt-2.5">Aradığınız desteği ve uzmanlığı kategorilerimiz üzerinden hızlıca listeleyebilirsiniz.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($branslar->take(8) as $brans)
                <a href="{{ route('frontend.hekimler', ['brans' => $brans->slug]) }}"
                   class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.03)] hover:-translate-y-0.5 transition-all duration-300 group block no-underline">
                    <div class="w-12 h-12 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center mb-5 transition-transform group-hover:scale-105">
                        @include('frontend.partials.brans_ikon', ['slug' => $brans->slug])
                    </div>
                    <h3 class="text-base font-bold font-display text-[#111827] mb-1.5">{{ $brans->ad }}</h3>
                    <p class="text-xs text-[#6B7280] leading-relaxed mb-4">
                        @if($brans->aciklama)
                            {{ Str::limit($brans->aciklama, 80) }}
                        @else
                            {{ $brans->doktorlar_count }} aktif uzman bu alanda hizmet veriyor.
                        @endif
                    </p>
                    <span class="text-xs font-semibold text-[#C96A2B] flex items-center gap-1 group-hover:underline cursor-pointer font-display">
                        {{ $brans->doktorlar_count }} Uzmanı Gör →
                    </span>
                </a>
            @empty
                <div class="col-span-4 text-center text-[#6B7280] py-10">
                    Henüz branş eklenmemiş.
                </div>
            @endforelse
        </div>
    </section>

    <!-- How it works -->
    <section id="nasil-calisir" class="bg-white border-t border-b border-[#E5E7EB] py-20 select-none">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-xl mx-auto mb-16">
                <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Nasıl Çalışır?</h2>
                <p class="text-sm text-[#6B7280] mt-2.5">Sadece 3 adımda dilediğiniz uzmanla görüşmenizi planlayın.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Step 1 -->
                <div class="text-center space-y-4">
                    <div class="w-12 h-12 rounded-full bg-[#FFF7ED] text-[#C96A2B] font-bold font-display text-lg flex items-center justify-center mx-auto shadow-inner">1</div>
                    <h3 class="text-base font-bold font-display text-[#111827]">Uzmanını Seç</h3>
                    <p class="text-xs text-[#6B7280] leading-relaxed max-w-xs mx-auto">
                        @if(isset($istatistikler))
                            {{ number_format($istatistikler['doktor_sayisi']) }} uzman ve {{ $istatistikler['brans_sayisi'] }} farklı branş arasından filtreleri kullanarak aradığınız uzmanı bulun.
                        @else
                            Binlerce hekim ve danışan arasından filtreleri kullanarak aradığınız uzmanı bulun.
                        @endif
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center space-y-4">
                    <div class="w-12 h-12 rounded-full bg-[#FFF7ED] text-[#C96A2B] font-bold font-display text-lg flex items-center justify-center mx-auto shadow-inner">2</div>
                    <h3 class="text-base font-bold font-display text-[#111827]">Günü ve Saati Belirle</h3>
                    <p class="text-xs text-[#6B7280] leading-relaxed max-w-xs mx-auto">
                        Uzmanın güncel ajandasına doğrudan erişerek size en uygun seansı seçin.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center space-y-4">
                    <div class="w-12 h-12 rounded-full bg-[#FFF7ED] text-[#C96A2B] font-bold font-display text-lg flex items-center justify-center mx-auto shadow-inner">3</div>
                    <h3 class="text-base font-bold font-display text-[#111827]">Randevunu Tamamla</h3>
                    <p class="text-xs text-[#6B7280] leading-relaxed max-w-xs mx-auto">
                        Kaydınızı tamamlayın. Onay SMS ve e-postanız anında cebinize gelsin.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctors / Experts Grid (Dinamik) -->
    <section id="doktorlar" class="max-w-7xl mx-auto px-6 py-20 select-none">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-16">
            <div>
                <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Öne Çıkan Uzmanlarımız</h2>
                <p class="text-sm text-[#6B7280] mt-2.5">Danışan memnuniyeti en yüksek olan bazı aktif uzman kadromuz.</p>
            </div>
            <div>
                <a href="{{ route('frontend.hekimler') }}" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] transition-colors font-display no-underline">
                    Tümünü Gör
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="expertsGrid">
            @forelse($oneCikanDoktorlar as $doktor)
                @php
                    $brans = $doktor->branslar->first();
                    $bransAd = $brans ? $brans->ad : 'Uzman';
                    $initials = collect(explode(' ', $doktor->ad_soyad))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
                    $ortalamaPuan = $doktor->ortalama_puan_cache ?? $doktor->ortalama_puan ?? 0;
                    $yorumSayisi = $doktor->yorum_sayisi_cache ?? 0;
                @endphp
                <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-[0_4px_24px_rgba(31,41,55,0.03)] hover:-translate-y-0.5 transition-all duration-300 p-6 flex flex-col justify-between">
                    <div>
                        <!-- Doctor Header Info -->
                        <div class="flex gap-4 mb-6">
                            <!-- Avatar -->
                            @if($doktor->profil_resmi)
                                <img src="{{ asset('storage/' . $doktor->profil_resmi) }}"
                                     alt="{{ $doktor->ad_soyad }}"
                                     class="w-14 h-14 rounded-full object-cover border border-[#E7B58A]/30 flex-shrink-0">
                            @else
                                <div class="w-14 h-14 rounded-full bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center font-extrabold text-sm font-display flex-shrink-0">
                                    {{ $initials }}
                                </div>
                            @endif
                            <div>
                                <span class="inline-block px-2.5 py-0.5 bg-[#FFF7ED] text-[#C96A2B] text-[10px] font-bold rounded-full font-display uppercase tracking-wider">{{ $bransAd }}</span>
                                <h3 class="text-base font-bold font-display text-[#111827] mt-1.5">
                                    {{ $doktor->unvan ? $doktor->unvan . ' ' : '' }}{{ $doktor->ad_soyad }}
                                </h3>
                                <p class="text-[11px] text-[#6B7280] mt-0.5">
                                    {{ $doktor->uzmanlik_alani ?? $bransAd }}
                                    @if($doktor->il)
                                        · {{ $doktor->il->ad }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Statistics & Reviews -->
                        <div class="grid grid-cols-2 gap-4 py-3 border-t border-b border-[#E5E7EB] mb-6 text-xs font-semibold">
                            <div>
                                <span class="text-[10px] text-[#6B7280] block font-bold uppercase font-display">Memnuniyet</span>
                                <span class="text-[#111827] mt-1 block flex items-center gap-1">
                                    <span class="text-[#C96A2B]">★</span> {{ $ortalamaPuan }} <span class="text-[#6B7280] font-normal">({{ $yorumSayisi }} Değerlendirme)</span>
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] text-[#6B7280] block font-bold uppercase font-display">Konum</span>
                                <span class="text-[#111827] mt-1 block">
                                    {{ $doktor->il ? $doktor->il->ad : '—' }}{{ $doktor->ilce ? ', ' . $doktor->ilce->ad : '' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ $doktor->profil_url }}" class="w-full text-center py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm block font-display no-underline">
                        Online Randevu Al
                    </a>
                </div>
            @empty
                <div class="col-span-3 text-center text-[#6B7280] py-10">
                    Henüz uzman kaydı bulunmamaktadır.
                </div>
            @endforelse
        </div>
    </section>

    <!-- Son Yorumlar / Testimonials (Dinamik) -->
    @if(isset($sonYorumlar) && $sonYorumlar->count() > 0)
    <section class="bg-white border-t border-b border-[#E5E7EB] py-20 select-none">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-xl mx-auto mb-16">
                <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Hastalarımız Ne Diyor?</h2>
                <p class="text-sm text-[#6B7280] mt-2.5">Platformumuzdan hizmet alan hastaların değerlendirmeleri.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($sonYorumlar->take(3) as $yorum)
                    <div class="p-6 rounded-2xl bg-[#FAFAFA] border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.03)]">
                        <!-- Yıldızlar -->
                        <div class="flex items-center gap-0.5 mb-4">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="{{ $i <= $yorum->puan ? 'text-[#C96A2B]' : 'text-[#D1D5DB]' }} text-sm">★</span>
                            @endfor
                        </div>

                        <!-- Yorum -->
                        <p class="text-sm text-[#374151] leading-relaxed mb-5 italic">
                            "{{ Str::limit($yorum->yorum, 150) }}"
                        </p>

                        <!-- Hasta ve Doktor bilgisi -->
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-[#111827]">
                                    {{ $yorum->hasta ? $yorum->hasta->maskeli_ad : 'Anonim Hasta' }}
                                </p>
                                <p class="text-[10px] text-[#6B7280] mt-0.5">
                                    {{ $yorum->doktor ? ($yorum->doktor->unvan ? $yorum->doktor->unvan . ' ' : '') . $yorum->doktor->ad_soyad : '' }}
                                </p>
                            </div>
                            <span class="text-[10px] text-[#9CA3AF]">{{ $yorum->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Son Blog Yazıları (Dinamik) -->
    @if(isset($sonBloglar) && $sonBloglar->count() > 0)
    <section class="max-w-7xl mx-auto px-6 py-20 select-none">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-16">
            <div>
                <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Uzman Yazıları</h2>
                <p class="text-sm text-[#6B7280] mt-2.5">Uzmanlarımızın kaleme aldığı güncel sağlık ve bilgilendirme yazıları.</p>
            </div>
            <div>
                <a href="{{ route('frontend.blog.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] transition-colors font-display no-underline">
                    Tüm Yazılar
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($sonBloglar as $blog)
                <a href="{{ $blog->url }}" class="rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.03)] hover:-translate-y-0.5 transition-all duration-300 overflow-hidden group block no-underline">
                    @if($blog->resim)
                        <div class="aspect-video overflow-hidden">
                            <img src="{{ asset($blog->resim) }}"
                                 alt="{{ $blog->baslik }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                    @else
                        <div class="aspect-video bg-gradient-to-br from-[#FFF7ED] to-[#FEE2C5] flex items-center justify-center">
                            <svg class="w-12 h-12 text-[#C96A2B]/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                            </svg>
                        </div>
                    @endif
                    <div class="p-5">
                        <h3 class="text-sm font-bold font-display text-[#111827] mb-2 group-hover:text-[#C96A2B] transition-colors line-clamp-2">{{ $blog->baslik }}</h3>
                        <div class="flex items-center justify-between text-[10px] text-[#6B7280]">
                            <span>{{ $blog->doktor ? ($blog->doktor->unvan ? $blog->doktor->unvan . ' ' : '') . $blog->doktor->ad_soyad : '' }}</span>
                            <span>{{ $blog->created_at->format('d.m.Y') }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif
@endsection

@section('script')
    <script>
        function setSearch(val) {
            const bar = document.getElementById('searchBar');
            if(bar) {
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
    </script>
@endsection
