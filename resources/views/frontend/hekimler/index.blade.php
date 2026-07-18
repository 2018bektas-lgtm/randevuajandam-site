@extends('frontend.layouts.app')

@section('baslik', 'Uzman Hekimlerimiz - Randevu Ajandam')

@section('head')

    <style>
        /* Custom Premium Select2 Overrides */
        .select2-container--default .select2-selection--single {
            background-color: #FAFAFA !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 0.75rem !important; /* rounded-xl */
            height: 42px !important;
            display: flex !important;
            align-items: center !important;
            padding-left: 0.5rem !important;
            padding-right: 2rem !important;
            transition: all 0.15s ease-in-out !important;
            outline: none !important;
            width: 100% !important;
        }
        .select2-container--default .select2-selection--single:focus-within,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #C96A2B !important;
            box-shadow: 0 0 0 4px rgba(201, 106, 43, 0.08) !important;
            background-color: #FFF !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #111827 !important;
            font-size: 0.75rem !important; /* text-xs */
            font-weight: 500 !important;
            padding: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9CA3AF !important;
            font-weight: 400 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            width: 35px !important;
            top: 0 !important;
            right: 5px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #9CA3AF transparent transparent transparent !important;
            border-width: 5px 4px 0 4px !important;
            margin-left: -2px !important;
            margin-top: -2px !important;
            transition: transform 0.2s ease !important;
        }
        .select2-container--default.select2-container--open .select2-selection__arrow b {
            border-color: transparent transparent #C96A2B transparent !important;
            border-width: 0 4px 5px 4px !important;
        }
        
        /* Dropdown container */
        .select2-dropdown {
            background-color: #FFF !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 1rem !important; /* rounded-2xl */
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.12) !important;
            overflow: hidden !important;
            padding: 6px !important;
            margin-top: 4px !important;
            z-index: 9999 !important;
        }
        .select2-container--default .select2-search--dropdown {
            padding: 4px !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #F8FAFC !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 0.75rem !important;
            font-size: 0.75rem !important;
            padding: 8px 12px !important;
            outline: none !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #C96A2B !important;
        }
        
        /* Options list */
        .select2-container--default .select2-results__options {
            max-height: 200px !important;
            padding: 2px !important;
        }
        .select2-container--default .select2-results__option {
            border-radius: 0.5rem !important; /* rounded-lg */
            font-size: 0.75rem !important;
            padding: 8px 12px !important;
            color: #334155 !important;
            transition: all 0.15s ease !important;
            user-select: none !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #FDF2E9 !important; /* light warm orange */
            color: #C96A2B !important;
            font-weight: 600 !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #FDF2E9 !important;
            color: #C96A2B !important;
            font-weight: 600 !important;
        }
        .select2-container {
            width: 100% !important;
        }
    </style>
    <!-- Leaflet Map CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endsection

@section('icerik')
<style>
    /* Premium Shimmer Sweep */
    .shimmer-sweep {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            115deg,
            transparent 35%,
            rgba(255, 255, 255, 0.4) 48%,
            rgba(255, 255, 255, 0.6) 50%,
            rgba(255, 255, 255, 0.4) 52%,
            transparent 65%
        );
        background-size: 200% 100%;
        background-position: -200% 0;
        mix-blend-mode: overlay;
        pointer-events: none;
        transition: all 0.5s ease;
    }
    .group:hover .shimmer-sweep {
        animation: sweep 1.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    @keyframes sweep {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    /* Ambient Pulsating Glow Behind Card */
    .doctor-glow {
        position: absolute;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(201, 106, 43, 0.12) 0%, transparent 70%);
        border-radius: 50%;
        filter: blur(25px);
        opacity: 0;
        transition: opacity 0.5s ease, transform 0.5s ease;
        transform: scale(0.8);
        pointer-events: none;
        z-index: 0;
        top: -10%;
        right: -10%;
    }
    .group:hover .doctor-glow {
        opacity: 1;
        transform: scale(1.1);
    }

    /* Grid/List Card Layout Toggles */
    .doctor-list-container {
        transition: all 0.3s ease;
    }
    
    /* List View Styles */
    .doctor-list-container.layout-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    @media (min-width: 640px) {
        .doctor-list-container.layout-list .doctor-card {
            flex-direction: row !important;
            text-align: left !important;
            align-items: stretch;
        }
        .doctor-list-container.layout-list .doctor-portrait {
            width: 8rem !important; /* w-32 */
            height: 11rem !important; /* h-44 */
        }
        .doctor-list-container.layout-list .doctor-details {
            text-align: left !important;
            align-items: flex-start !important;
        }
        .doctor-list-container.layout-list .doctor-details .info-row {
            justify-content: flex-start !important;
        }
    }
    
    /* Grid View Styles */
    .doctor-list-container.layout-grid {
        display: grid;
        gap: 2rem;
    }
    .doctor-list-container.layout-grid .doctor-card {
        flex-direction: column !important;
        text-align: center !important;
        align-items: center;
    }
    .doctor-list-container.layout-grid .doctor-portrait {
        width: 100% !important;
        height: 14rem !important; /* w-full h-56 */
        margin-left: auto !important;
        margin-right: auto !important;
    }
    .doctor-list-container.layout-grid .doctor-details {
        text-align: center !important;
        align-items: center !important;
        width: 100%;
    }
    .doctor-list-container.layout-grid .doctor-details .info-row {
        justify-content: center !important;
    }
    
    /* Map Filter Overlay Style */
    #mapFilterOverlay {
        transition: max-height 0.3s ease, opacity 0.3s ease;
    }
    @media (max-width: 767px) {
        #mapFilterOverlay {
            top: auto !important;
            bottom: 1rem !important;
            left: 1rem !important;
            right: 1rem !important;
            width: auto !important;
            max-height: 60% !important;
        }
    }
</style>

<section class="relative bg-[#FAFAFA] py-16 md:py-24 overflow-hidden min-h-[80vh]">
    <!-- Ambient Background Light Glows -->
    <div class="absolute top-[-10%] right-[-10%] w-[600px] h-[600px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[600px] h-[600px] rounded-full bg-[#C96A2B]/3 blur-[120px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        
        <!-- Header Text -->
        <div class="max-w-3xl mx-auto text-center space-y-4 mb-12">
            <span class="text-xs font-bold text-[#C96A2B] uppercase tracking-widest font-display block">Sistem Kayıtlı Uzman Hekimler</span>
            <h1 class="text-3xl md:text-5xl font-black font-display text-[#111827] tracking-tight leading-none">
                Uzman Hekimlerimiz
            </h1>
            <p class="text-sm md:text-base text-[#6B7280] leading-relaxed">
                Alanında uzman doktorlarımızın özgeçmişlerini inceleyin, online çalışma saatlerini görüntüleyin ve anında online randevu talebinizi iletin.
            </p>
        </div>

        <!-- Mobile Filter Toggle Button -->
        <button id="mobileFilterToggle" class="lg:hidden w-full mb-6 py-3 px-4 rounded-xl border border-[#E5E7EB] bg-white text-[#111827] font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-sm cursor-pointer">
            <svg class="w-4 h-4 text-[#C96A2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"></path>
            </svg>
            Filtreleri Göster / Gizle
        </button>

        <!-- Main Content Area with Sidebar Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Sidebar Filter Panel (Left Column) -->
            <aside id="filterSidebar" class="hidden lg:block lg:col-span-3 bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-[0_8px_30px_rgba(31,41,55,0.02)] sticky top-6">
                <h3 class="text-sm font-bold text-[#111827] mb-5 uppercase tracking-wider font-display flex items-center gap-2 border-b border-slate-100 pb-3">
                    <svg class="w-4 h-4 text-[#C96A2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"></path>
                    </svg>
                    Filtrele & Ara
                </h3>
                
                <form id="filterForm" action="{{ route('frontend.hekimler') }}" method="GET" class="space-y-5">
                    <!-- Search Input -->
                    <div class="space-y-1.5">
                        <label for="arama" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider font-display">Arama</label>
                        <input type="text" name="arama" id="arama" value="{{ request('arama') }}" placeholder="Hekim adı veya branş..." 
                               class="w-full px-3 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>

                    <!-- Specialty Dropdown -->
                    <div class="space-y-1.5">
                        <label for="uzmanlik" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider font-display">Uzmanlık Alanı</label>
                        <select name="uzmanlik" id="uzmanlik" data-no-select2="true"
                                class="w-full px-3 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                            <option value="">Tüm Branşlar</option>
                            @foreach($uzmanliklar as $uzmanlik)
                                <option value="{{ $uzmanlik }}" {{ request('uzmanlik') == $uzmanlik ? 'selected' : '' }}>{{ $uzmanlik }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Title Dropdown -->
                    <div class="space-y-1.5">
                        <label for="unvan" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider font-display">Unvan</label>
                        <select name="unvan" id="unvan" data-no-select2="true"
                                class="w-full px-3 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                            <option value="">Tüm Unvanlar</option>
                            @foreach($unvanlar as $unvan)
                                <option value="{{ $unvan }}" {{ request('unvan') == $unvan ? 'selected' : '' }}>{{ $unvan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- City Dropdown -->
                    <div class="space-y-1.5">
                        <label for="il" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider font-display">Hizmet İli</label>
                        <select name="il" id="il" data-no-select2="true"
                                class="w-full px-3 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                            <option value="">Tüm İller</option>
                            @foreach($iller as $ilItem)
                                <option value="{{ $ilItem->id }}" {{ request('il') == $ilItem->id ? 'selected' : '' }}>{{ $ilItem->ad }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- District Dropdown -->
                    <div class="space-y-1.5">
                        <label for="ilce" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider font-display">Hizmet İlçesi</label>
                        <select name="ilce" id="ilce" data-no-select2="true"
                                class="w-full px-3 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                            <option value="">Tüm İlçeler</option>
                        </select>
                    </div>

                    <!-- Geolocation / Nearby Search -->
                    <div class="space-y-2 border-t border-slate-100 pt-3" id="nearbySearchContainer">
                        <div class="flex items-center justify-between">
                            <label for="yakindaki" class="text-[11px] font-bold text-[#4B5563] uppercase tracking-wider font-display cursor-pointer select-none">Yakınımdaki Hekimler</label>
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" name="yakindaki" id="yakindaki" value="1" {{ request('yakindaki') ? 'checked' : '' }} class="sr-only peer" onchange="toggleNearby(this)">
                                <div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                            </label>
                        </div>
                        <div id="distanceRangeContainer" class="{{ request('yakindaki') ? '' : 'hidden' }} space-y-1.5 pt-1.5">
                            <div class="flex justify-between text-[10px] text-[#6B7280] font-bold font-display uppercase tracking-wider">
                                <span>Mesafe Çapı:</span>
                                <span id="distanceVal" class="text-[#C96A2B] font-extrabold">{{ request('cap', 15) }} km</span>
                            </div>
                            <input type="range" name="cap" id="cap" min="1" max="100" value="{{ request('cap', 15) }}" class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-[#C96A2B]" oninput="document.getElementById('distanceVal').innerText = this.value + ' km'">
                        </div>
                        <input type="hidden" name="user_lat" id="user_lat" value="{{ request('user_lat') }}">
                        <input type="hidden" name="user_lng" id="user_lng" value="{{ request('user_lng') }}">
                    </div>

                    <!-- Only Clinics Hidden Input -->
                    <input type="hidden" name="sadece_klinik" id="sadece_klinik" value="{{ request('sadece_klinik') ? '1' : '' }}">

                    <!-- Actions -->
                    @if(request()->anyFilled(['arama', 'uzmanlik', 'unvan', 'il', 'ilce', 'yakindaki', 'sadece_klinik']))
                        <div class="flex flex-col gap-2 pt-2 border-t border-slate-100">
                            <a href="{{ route('frontend.hekimler') }}" 
                               class="w-full py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#C96A2B] hover:border-[#C96A2B] font-bold text-xs uppercase tracking-wider transition-all font-display text-center flex items-center justify-center cursor-pointer shadow-sm clear-filters-btn">
                                Filtreleri Temizle
                            </a>
                        </div>
                    @endif

                </form>
            </aside>

            <!-- Listing Results Area (Right Column) -->
            <main class="col-span-1 lg:col-span-9 space-y-6">
                
                <!-- Toolbar Panel -->
                <div class="bg-white border border-[#E5E7EB] rounded-2xl px-5 py-3 shadow-[0_4px_20px_rgba(0,0,0,0.01)] flex flex-col sm:flex-row justify-between items-center gap-4">
                    <!-- Left: Tab Switcher -->
                    <div class="flex items-center bg-slate-50 border border-slate-200/60 p-1 rounded-xl gap-0.5 select-none font-display">
                        <button type="button" onclick="setTab('doktor')" id="tabDoctors" class="px-4 py-2 text-xs font-bold rounded-lg transition-all duration-150 cursor-pointer focus:outline-none flex items-center gap-2 {{ !request('sadece_klinik') ? 'text-[#C96A2B] bg-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                            <span>🩺</span> Uzman Hekimler
                            <span id="badgeDoctorsCount" class="inline-block px-1.5 py-0.5 rounded-md text-[10px] {{ !request('sadece_klinik') ? 'bg-[#FFF7ED] text-[#C96A2B]' : 'bg-slate-200 text-slate-600' }}">{{ $toplamDoktorSayisi }}</span>
                        </button>
                        <button type="button" onclick="setTab('klinik')" id="tabClinics" class="px-4 py-2 text-xs font-bold rounded-lg transition-all duration-150 cursor-pointer focus:outline-none flex items-center gap-2 {{ request('sadece_klinik') ? 'text-[#C96A2B] bg-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                            <span>🏥</span> Klinikler
                            <span id="badgeClinicsCount" class="inline-block px-1.5 py-0.5 rounded-md text-[10px] {{ request('sadece_klinik') ? 'bg-[#FFF7ED] text-[#C96A2B]' : 'bg-slate-200 text-slate-600' }}">{{ $toplamKlinikSayisi }}</span>
                        </button>
                    </div>

                    <!-- Right: views and columns switcher -->
                    <div class="flex items-center gap-4 shrink-0">
                        <!-- Grid/List/Map layout toggle -->
                        <div class="flex items-center bg-slate-50 border border-slate-200/60 p-1 rounded-xl gap-0.5">
                            <button id="btnViewGrid" title="Izgara Görünümü" class="p-2 rounded-lg text-[#C96A2B] bg-white shadow-sm transition-all duration-150 cursor-pointer focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"></path>
                                </svg>
                            </button>
                            <button id="btnViewList" title="Liste Görünümü" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 transition-all duration-150 cursor-pointer focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5"></path>
                                </svg>
                            </button>
                            <button id="btnToggleMap" title="Harita Görünümü" class="py-1.5 px-3 rounded-lg text-slate-400 hover:text-[#C96A2B] transition-all duration-150 cursor-pointer focus:outline-none flex items-center gap-1.5 text-xs font-bold font-display">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6.75V15m-12-3c0-4.5 9-9 9-9s9 4.5 9 9-9 9-9 9-9-4.5-9-9z"></path>
                                </svg>
                                <span>Harita Görünümü</span>
                            </button>
                        </div>

                        <!-- Column Count Selector -->
                        <div id="colCountPanel" class="hidden md:flex items-center gap-1.5 border-l border-slate-200 pl-4">
                            <span class="text-[10px] uppercase font-bold text-[#6B7280] font-display tracking-wider">Kolon:</span>
                            <div class="flex items-center bg-slate-50 border border-slate-200/60 p-1 rounded-xl gap-0.5">
                                <button data-cols="2" class="btn-col-select px-2.5 py-1 text-xs font-bold rounded-lg text-slate-500 hover:text-slate-700 transition-all cursor-pointer focus:outline-none">2</button>
                                <button data-cols="3" class="btn-col-select px-2.5 py-1 text-xs font-bold rounded-lg text-[#C96A2B] bg-white shadow-sm transition-all cursor-pointer focus:outline-none">3</button>
                                <button data-cols="4" class="btn-col-select px-2.5 py-1 text-xs font-bold rounded-lg text-slate-500 hover:text-slate-700 transition-all cursor-pointer focus:outline-none">4</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="resultsSplitWrapper" class="flex flex-col gap-6 w-full">
                    <!-- Collapsible Map View -->
                    <div id="searchMapWrapper" class="bg-white border border-[#E5E7EB] rounded-3xl p-4 shadow-[0_8px_30px_rgba(31,41,55,0.02)] transition-all duration-300 relative z-10 hidden">
                        <!-- Map Canvas -->
                        <div id="searchMap" class="w-full h-full rounded-2xl border border-[#E5E7EB] shadow-inner relative"></div>
                    </div>

                    <div id="resultsListWrapper" class="space-y-6 flex-1 w-full">
                        <div id="resultsListContainer">
                            @include('frontend.hekimler.partials.doctor_cards')
                        </div>
                    </div>
                </div>
                </div>
            </main>
        </div>

    </div>
</section>

<!-- Simulated Booking Request Success Modal -->
<div id="bookingModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
    <div id="bookingModalContainer" class="bg-white rounded-2xl border border-[#E5E7EB] shadow-2xl max-w-sm w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]">
        <!-- Modal Body -->
        <div class="p-6 text-center space-y-4 overflow-y-auto flex-1">
            <!-- success tick -->
            <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center mx-auto animate-bounce shrink-0">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                </svg>
            </div>
            
            <h3 class="text-lg font-bold font-display text-[#111827]">Randevu Talebi Gönderildi!</h3>
            <div class="text-xs text-[#6B7280] leading-relaxed space-y-2">
                <p>Sayın <strong id="modalDocName" class="text-[#111827]"></strong> (<span id="modalDocBranch"></span>) ile randevu talebi simülasyonu başarıyla oluşturuldu.</p>
                <p class="text-[11px] text-[#C96A2B] font-semibold">Talebiniz hekimin çalışma paneline gönderilmiştir.</p>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 bg-slate-50 border-t border-[#E5E7EB] text-center shrink-0">
            <button onclick="kapatRandevuModal()" class="w-full py-2.5 rounded-xl bg-[#1F2937] hover:bg-[#111827] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display">
                Kapat
            </button>
        </div>
    </div>
</div>

@php
    if (request('sadece_klinik')) {
        $mapDoctors = $klinikler->filter(function($k) {
            return $k->enlem && $k->boylam;
        })->map(function($k) {
            return [
                'ad_soyad' => $k->ad,
                'klinik_adi' => 'Klinik',
                'uzmanlik_alani' => ((int) ($k->doktorlar_count ?? 0)).' Hekim',
                'url' => route('frontend.klinik.profil', ['il_slug' => $k->il->slug ?? 'il', 'ilce_slug' => $k->ilce->slug ?? 'ilce', 'klinik_slug' => $k->slug]),
                'enlem' => (float)$k->enlem,
                'boylam' => (float)$k->boylam,
                'profil_resmi' => $k->logo ? asset($k->logo) : null,
                'kisa_ad' => mb_strtoupper(mb_substr($k->ad, 0, 2))
            ];
        })->values();
    } else {
        $mapDoctors = $doktorlar->filter(function($d) {
            return $d->enlem && $d->boylam;
        })->map(function($d) {
            return [
                'ad_soyad' => ($d->unvan ? $d->unvan . ' ' : '') . $d->ad_soyad,
                'klinik_adi' => null,
                'uzmanlik_alani' => $d->uzmanlik_alani ?? 'Uzman Hekim',
                'url' => $d->profil_url,
                'enlem' => (float)$d->enlem,
                'boylam' => (float)$d->boylam,
                'profil_resmi' => $d->profil_resmi ? asset($d->profil_resmi) : null,
                'kisa_ad' => (function() use ($d) {
                    $words = explode(' ', $d->ad_soyad);
                    $kisaAd = mb_strtoupper(mb_substr($words[0], 0, 1));
                    if (count($words) > 1) {
                        $kisaAd .= mb_strtoupper(mb_substr(end($words), 0, 1));
                    }
                    return $kisaAd;
                })()
            ];
        })->values();
    }
@endphp

<script>
    var mapDoctors = @json($mapDoctors);

    function setTab(type) {
        var input = document.getElementById('sadece_klinik');
        if (input) {
            input.value = (type === 'klinik') ? '1' : '';
            updateTabUI();
            if (window.fetchDoctorsData) {
                window.fetchDoctorsData();
            }
        }
    }

    function updateTabUI() {
        var input = document.getElementById('sadece_klinik');
        if (!input) return;
        var isClinic = input.value === '1';
        
        var tabDoctors = document.getElementById('tabDoctors');
        var tabClinics = document.getElementById('tabClinics');
        var badgeDoctors = document.getElementById('badgeDoctorsCount');
        var badgeClinics = document.getElementById('badgeClinicsCount');

        if (isClinic) {
            if (tabClinics) {
                tabClinics.className = "px-4 py-2 text-xs font-bold rounded-lg transition-all duration-150 cursor-pointer focus:outline-none flex items-center gap-2 text-[#C96A2B] bg-white shadow-sm";
            }
            if (badgeClinics) {
                badgeClinics.className = "inline-block px-1.5 py-0.5 rounded-md text-[10px] bg-[#FFF7ED] text-[#C96A2B]";
            }
            if (tabDoctors) {
                tabDoctors.className = "px-4 py-2 text-xs font-bold rounded-lg transition-all duration-150 cursor-pointer focus:outline-none flex items-center gap-2 text-slate-500 hover:text-slate-700";
            }
            if (badgeDoctors) {
                badgeDoctors.className = "inline-block px-1.5 py-0.5 rounded-md text-[10px] bg-slate-200 text-slate-600";
            }
        } else {
            if (tabDoctors) {
                tabDoctors.className = "px-4 py-2 text-xs font-bold rounded-lg transition-all duration-150 cursor-pointer focus:outline-none flex items-center gap-2 text-[#C96A2B] bg-white shadow-sm";
            }
            if (badgeDoctors) {
                badgeDoctors.className = "inline-block px-1.5 py-0.5 rounded-md text-[10px] bg-[#FFF7ED] text-[#C96A2B]";
            }
            if (tabClinics) {
                tabClinics.className = "px-4 py-2 text-xs font-bold rounded-lg transition-all duration-150 cursor-pointer focus:outline-none flex items-center gap-2 text-slate-500 hover:text-slate-700";
            }
            if (badgeClinics) {
                badgeClinics.className = "inline-block px-1.5 py-0.5 rounded-md text-[10px] bg-slate-200 text-slate-600";
            }
        }
    }

    function toggleNearby(checkbox) {
        var rangeContainer = document.getElementById('distanceRangeContainer');
        var latInput = document.getElementById('user_lat');
        var lngInput = document.getElementById('user_lng');

        if (checkbox.checked) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    latInput.value = position.coords.latitude;
                    lngInput.value = position.coords.longitude;
                    rangeContainer.classList.remove('hidden');
                    $('#filterForm').submit();
                }, function(error) {
                    checkbox.checked = false;
                    alert('Lütfen yakınınızdaki hekimleri görebilmek için tarayıcınızdan konum izni veriniz.');
                });
            } else {
                checkbox.checked = false;
                alert('Tarayıcınız konum servislerini desteklemiyor.');
            }
        } else {
            rangeContainer.classList.add('hidden');
            latInput.value = '';
            lngInput.value = '';
            $('#filterForm').submit();
        }
    }

    function acRandevuModal(docName, branchName) {
        const modal = document.getElementById('bookingModal');
        const container = document.getElementById('bookingModalContainer');
        const nameEl = document.getElementById('modalDocName');
        const branchEl = document.getElementById('modalDocBranch');

        if(modal && container && nameEl && branchEl) {
            nameEl.innerText = docName;
            branchEl.innerText = branchName;
            modal.classList.remove('hidden');
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 50);
        }
    }

    function kapatRandevuModal() {
        const modal = document.getElementById('bookingModal');
        const container = document.getElementById('bookingModalContainer');

        if(modal && container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    }

    // Close on overlay click & DOM elements binding
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('bookingModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    kapatRandevuModal();
                }
            });
        }

        // Initialize Select2 dropdowns
        $('#uzmanlik').select2({
            placeholder: "Branş Seçin...",
            allowClear: true,
            minimumResultsForSearch: 10
        });
        $('#unvan').select2({
            placeholder: "Unvan Seçin...",
            allowClear: true,
            minimumResultsForSearch: 10
        });
        $('#il').select2({
            placeholder: "İl Seçin...",
            allowClear: true
        });
        $('#ilce').select2({
            placeholder: "İlçe Seçin...",
            allowClear: true
        });


        // Mobile Sidebar Filter Toggle
        const mobileFilterToggle = document.getElementById('mobileFilterToggle');
        const filterSidebar = document.getElementById('filterSidebar');
        
        if (mobileFilterToggle && filterSidebar) {
            mobileFilterToggle.addEventListener('click', function() {
                filterSidebar.classList.toggle('hidden');
                
                // Smooth scroll to sidebar if opening
                if (!filterSidebar.classList.contains('hidden')) {
                    filterSidebar.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }

        // Layout and Column Switcher Logic
        const listContainer = document.getElementById('doctorListContainer');
        const btnViewGrid = document.getElementById('btnViewGrid');
        const btnViewList = document.getElementById('btnViewList');
        const btnToggleMap = document.getElementById('btnToggleMap');
        const colCountPanel = document.getElementById('colCountPanel');
        const colButtons = document.querySelectorAll('.btn-col-select');

        let currentView = localStorage.getItem('doctor_list_view') || 'grid'; // grid, list, or map
        let currentCols = localStorage.getItem('doctor_list_cols') || '3'; // 2, 3 or 4

        // Search map variables
        var searchMap;
        var searchMapInitialized = false;
        var resultsSplitWrapper = document.getElementById('resultsSplitWrapper');
        var resultsListWrapper = document.getElementById('resultsListWrapper');
        var searchMapEl = document.getElementById('searchMap');
        var searchMapWrapper = document.getElementById('searchMapWrapper');

        function updateLayoutUI() {
            const listContainer = document.getElementById('doctorListContainer');
            const filterSidebar = document.getElementById('filterSidebar');
            const mainContent = document.querySelector('main');

            // Reset active styles on layout buttons
            btnViewGrid.classList.remove('bg-white', 'text-[#C96A2B]', 'shadow-sm');
            btnViewGrid.classList.add('text-slate-400');
            btnViewList.classList.remove('bg-white', 'text-[#C96A2B]', 'shadow-sm');
            btnViewList.classList.add('text-slate-400');
            btnToggleMap.classList.remove('bg-white', 'text-[#C96A2B]', 'shadow-sm');
            btnToggleMap.classList.add('text-slate-400');

            // Let CSS handle responsive visibility of sidebar and main content grid span

            if (currentView === 'map') {
                // Activate map button style
                btnToggleMap.classList.add('bg-white', 'shadow-sm', 'text-[#C96A2B]');
                btnToggleMap.classList.remove('text-slate-400');

                // Hide doctor list wrapper (pagination, list container)
                if (resultsListWrapper) {
                    resultsListWrapper.classList.add('hidden');
                }

                // Show map wrapper
                if (searchMapWrapper) {
                    searchMapWrapper.classList.remove('hidden');
                    searchMapWrapper.className = "bg-white border border-[#E5E7EB] rounded-3xl p-4 shadow-[0_8px_30px_rgba(31,41,55,0.02)] transition-all duration-300 relative z-10 w-full h-[600px] lg:h-[700px] flex flex-col";
                }
                if (searchMapEl) {
                    searchMapEl.style.height = '100%';
                    searchMapEl.classList.remove('h-80');
                }

                // Hide column selector
                if (colCountPanel) {
                    colCountPanel.classList.add('opacity-45', 'pointer-events-none');
                }

                // Initialize map if not done
                if (!searchMapInitialized) {
                    searchMapInitialized = true;
                    setTimeout(function() {
                        initSearchMap();
                    }, 350); // Delayed to allow CSS transition to complete
                } else {
                    setTimeout(function() {
                        if (searchMap) {
                            searchMap.invalidateSize();
                            refreshMapMarkers(); // Fit bounds on current visible markers
                        }
                    }, 350); // Delayed to allow CSS transition to complete
                }
            } else {
                if (resultsListWrapper) {
                    resultsListWrapper.classList.remove('hidden');
                }
                if (searchMapWrapper) {
                    searchMapWrapper.classList.add('hidden');
                }

                if (currentView === 'list') {
                    btnViewList.classList.add('bg-white', 'text-[#C96A2B]', 'shadow-sm');
                    btnViewList.classList.remove('text-slate-400');
                    if (listContainer) {
                        listContainer.classList.remove('layout-grid', 'layout-list', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-2', 'lg:grid-cols-3', 'xl:grid-cols-4');
                        listContainer.classList.add('layout-list', 'grid-cols-1');
                    }
                    if (colCountPanel) {
                        colCountPanel.classList.add('opacity-45', 'pointer-events-none');
                    }
                } else { // grid
                    btnViewGrid.classList.add('bg-white', 'text-[#C96A2B]', 'shadow-sm');
                    btnViewGrid.classList.remove('text-slate-400');
                    if (listContainer) {
                        listContainer.classList.remove('layout-grid', 'layout-list', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-2', 'lg:grid-cols-3', 'xl:grid-cols-4');
                        listContainer.classList.add('layout-grid');
                        if (currentCols === '2') {
                            listContainer.classList.add('grid-cols-1', 'lg:grid-cols-2');
                        } else if (currentCols === '4') {
                            listContainer.classList.add('grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3', 'xl:grid-cols-4');
                        } else {
                            listContainer.classList.add('grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3');
                        }
                    }
                    if (colCountPanel) {
                        colCountPanel.classList.remove('opacity-45', 'pointer-events-none');
                    }

                    // Update column buttons selection
                    colButtons.forEach(btn => {
                        if (btn.getAttribute('data-cols') === currentCols) {
                            btn.classList.add('bg-white', 'text-[#C96A2B]', 'shadow-sm');
                            btn.classList.remove('text-slate-500');
                        } else {
                            btn.classList.remove('bg-white', 'text-[#C96A2B]', 'shadow-sm');
                            btn.classList.add('text-slate-500');
                        }
                    });
                }
            }
        }

        if (btnViewGrid && btnViewList && btnToggleMap) {
            btnViewGrid.addEventListener('click', function() {
                currentView = 'grid';
                localStorage.setItem('doctor_list_view', 'grid');
                updateLayoutUI();
            });

            btnViewList.addEventListener('click', function() {
                currentView = 'list';
                localStorage.setItem('doctor_list_view', 'list');
                updateLayoutUI();
            });

            btnToggleMap.addEventListener('click', function() {
                currentView = 'map';
                localStorage.setItem('doctor_list_view', 'map');
                updateLayoutUI();
            });

            colButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    if (currentView === 'list') return;
                    currentCols = this.getAttribute('data-cols');
                    localStorage.setItem('doctor_list_cols', currentCols);
                    updateLayoutUI();
                });
            });

            // Initialize layout state
            updateLayoutUI();
        }

        // Dynamic City-District filtering for sidebar form
        const selectedIlce = "{{ request('ilce') }}";

        function populateIlce(ilId, activeIlceId = null) {
            const $ilce = $('#ilce');
            if (!ilId) {
                $ilce.empty().append('<option value="">Tüm İlçeler</option>').trigger('change.select2');
                return;
            }
            
            $ilce.empty().append('<option value="">Yükleniyor...</option>').trigger('change.select2');
            
            fetch(`/iller/${ilId}/ilceler`)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">Tüm İlçeler</option>';
                    data.forEach(item => {
                        const selected = activeIlceId == item.id ? 'selected' : '';
                        options += `<option value="${item.id}" ${selected}>${item.ad}</option>`;
                    });
                    $ilce.empty().append(options).trigger('change.select2');
                })
                .catch(err => {
                    console.error(err);
                    $ilce.empty().append('<option value="">Hata oluştu</option>').trigger('change.select2');
                });
        }

        $('#il').on('change', function() {
            populateIlce(this.value, null);
        });

        const initialIl = $('#il').val();
        if (initialIl) {
            populateIlce(initialIl, selectedIlce);
        }

        // Active AJAX request abort controller to prevent race conditions
        let activeAbortController = null;

        // Auto-submit form on filter change with initial load guard
        let formInitialized = false;
        setTimeout(function() {
            formInitialized = true;
        }, 300);

        function triggerFormSubmit() {
            if (formInitialized) {
                fetchDoctorsData();
            }
        }

        // Form Submit Blocker (prevents Enter key or programmatic submits from reloading the page)
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            fetchDoctorsData();
        });

        // 1. Text Search Input (debounced)
        let searchTimeout;
        $('#arama').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                triggerFormSubmit();
            }, 600); // 600ms debounce
        });

        // 2. Dropdowns and range inputs
        $('#uzmanlik, #unvan, #il, #ilce, #cap').on('change', function() {
            triggerFormSubmit();
        });

        // 3. Dynamic AJAX search logic
        function fetchDoctorsData(url = null) {
            if (activeAbortController) {
                activeAbortController.abort();
            }
            activeAbortController = new AbortController();
            const { signal } = activeAbortController;

            if (!url) {
                const params = $('#filterForm').serialize();
                url = `${window.location.pathname}?${params}`;
            }

            // Update browser history URL
            history.pushState(null, '', url);

            // Shimmer / loading state for listing area
            $('#resultsListWrapper').addClass('opacity-50 pointer-events-none');

            fetch(url, {
                signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update doctor cards listing container
                $('#resultsListContainer').html(data.html);

                // Update dynamic tab badge counts
                $('#badgeDoctorsCount').text(data.toplamDoktorSayisi);
                $('#badgeClinicsCount').text(data.toplamKlinikSayisi);

                // Sync tab UI active styles
                updateTabUI();

                // Update dynamic map data
                mapDoctors = data.mapDoctors;

                // Re-draw map markers and bounds
                refreshMapMarkers();

                // Restore loading states
                $('#resultsListWrapper').removeClass('opacity-50 pointer-events-none');
                updateLayoutUI();

                // Dynamic visibility of Clear Filters button
                const filterFormValues = $('#filterForm').serializeArray().filter(item => {
                    return item.value !== '' && item.name !== '_token';
                });
                
                // If any filters active, show/hide the Clear Filters wrapper
                if (filterFormValues.length > 0) {
                    if ($('#filterForm a.clear-filters-btn').length === 0) {
                        $('#filterForm').append(`
                            <div class="flex flex-col gap-2 pt-2 border-t border-slate-100">
                                <a href="${window.location.pathname}" 
                                   class="w-full py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#C96A2B] hover:border-[#C96A2B] font-bold text-xs uppercase tracking-wider transition-all font-display text-center flex items-center justify-center cursor-pointer shadow-sm clear-filters-btn">
                                    Filtreleri Temizle
                                </a>
                            </div>
                        `);
                    }
                } else {
                    $('#filterForm .clear-filters-btn').parent().remove();
                }
            })
            .catch(err => {
                if (err.name === 'AbortError') {
                    // Fetch was aborted, ignore it
                    return;
                }
                console.error('AJAX filtering failed:', err);
                $('#resultsListWrapper').removeClass('opacity-50 pointer-events-none');
            });
        }

        // Expose fetchDoctorsData globally
        window.fetchDoctorsData = fetchDoctorsData;

        // Dynamic Clear Filters click handler
        $(document).on('click', '.clear-filters-btn', function(e) {
            e.preventDefault();
            
            // Clear text search
            $('#arama').val('');
            
            // Reset Select2 dropdowns
            $('#uzmanlik, #unvan, #il, #ilce').val('').trigger('change.select2');
            
            // Clear geolocation parameters
            const yakindakiCheckbox = document.getElementById('yakindaki');
            if (yakindakiCheckbox) {
                yakindakiCheckbox.checked = false;
            }
            const rangeContainer = document.getElementById('distanceRangeContainer');
            if (rangeContainer) {
                rangeContainer.classList.add('hidden');
            }
            document.getElementById('user_lat').value = '';
            document.getElementById('user_lng').value = '';
            
            // Trigger AJAX update
            fetchDoctorsData();
        });

        // Intercept AJAX pagination link clicks
        $(document).on('click', '#resultsListContainer .pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            fetchDoctorsData(url);
        });

        function refreshMapMarkers() {
            if (!searchMap) return;

            // Clear previous layer groups
            if (window.markersGroup) {
                searchMap.removeLayer(window.markersGroup);
            }

            window.markersGroup = L.featureGroup();

            // Render doctor markers
            mapDoctors.forEach(function(doc) {
                var popupHtml = '<div class="flex gap-3 items-center p-1 font-display" style="min-width: 200px; max-width: 250px;">' +
                    (doc.profil_resmi ? 
                        '<img src="' + doc.profil_resmi + '" class="w-10 h-13 rounded-lg object-cover shrink-0 border border-gray-100">' :
                        '<div class="w-10 h-13 bg-[#FFF7ED] text-[#C96A2B] text-xs font-bold rounded-lg flex items-center justify-center shrink-0 border border-[#E7B58A]/30">' + doc.kisa_ad + '</div>') +
                    '<div class="flex-1 min-w-0">' +
                        '<a href="' + doc.url + '" class="block text-xs font-bold text-[#111827] hover:text-[#C96A2B] transition-colors truncate">' + doc.ad_soyad + '</a>' +
                        '<div class="text-[10px] text-gray-400 font-semibold truncate">' + doc.uzmanlik_alani + '</div>' +
                    '</div>' +
                '</div>';

                L.marker([doc.enlem, doc.boylam]).addTo(window.markersGroup)
                    .bindPopup(popupHtml);
            });

            window.markersGroup.addTo(searchMap);

            // Render user geolocation and distance range circle if active
            var userLat = parseFloat(document.getElementById('user_lat').value);
            var userLng = parseFloat(document.getElementById('user_lng').value);
            if (!isNaN(userLat) && !isNaN(userLng)) {
                // Pulse effect icon for user
                var userIcon = L.divIcon({
                    className: 'relative flex h-5 w-5 items-center justify-center',
                    html: '<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500 border-2 border-white"></span>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });
                var userMarker = L.marker([userLat, userLng], { icon: userIcon }).addTo(window.markersGroup)
                    .bindPopup('<div class="font-display font-semibold text-xs text-[#111827] p-1">Konumunuz</div>');

                // Draw distance circle
                var radiusKm = parseFloat(document.getElementById('cap').value || 15);
                L.circle([userLat, userLng], {
                    color: '#3B82F6',
                    fillColor: '#3B82F6',
                    fillOpacity: 0.1,
                    radius: radiusKm * 1000 // Convert km to meters
                }).addTo(window.markersGroup);
            }

            if (mapDoctors.length > 0 || (!isNaN(userLat) && !isNaN(userLng))) {
                searchMap.fitBounds(window.markersGroup.getBounds().pad(0.15), {
                    maxZoom: 13 // Prevent extreme zooming on a single doctor
                });
            }
        }

        function initSearchMap() {
            // Precise coordinates for Turkey center boundaries
            var defaultLat = 38.9637;
            var defaultLng = 35.2433;
            var defaultZoom = 6;

            searchMap = L.map('searchMap').setView([defaultLat, defaultLng], defaultZoom);

            L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                attribution: '&copy; <a href="https://maps.google.com">Google Maps</a>'
            }).addTo(searchMap);

            refreshMapMarkers();
        }
    });
</script>
@endsection
