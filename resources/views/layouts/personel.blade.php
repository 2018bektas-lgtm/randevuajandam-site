<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('baslik', 'Personel Paneli - Randevu Ajandam')</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- JQuery & Select2 CSS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F5F4;
        }
        .font-display {
            font-family: 'Outfit', sans-serif;
        }

        /* Custom Premium Select2 Overrides */
        .select2-container--default .select2-selection--single {
            background-color: #FAFAFA !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 12px !important;
            height: 44px !important;
            display: flex !important;
            align-items: center !important;
            padding-left: 0.75rem !important;
            padding-right: 2rem !important;
            transition: all 0.15s ease-in-out !important;
            outline: none !important;
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
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            flex: 1 !important;
            min-width: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9CA3AF !important;
            font-weight: 400 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
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
        .select2 {
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
        }
    </style>
</head>
<body class="min-h-screen flex bg-[#F5F5F4]">
    @php
        $personelUser = auth('personel')->user();
        $kisaAd = '';
        if ($personelUser && $personelUser->ad_soyad) {
            $words = explode(' ', $personelUser->ad_soyad);
            $kisaAd = mb_strtoupper(mb_substr($words[0], 0, 1));
            if (count($words) > 1) {
                $kisaAd .= mb_strtoupper(mb_substr(end($words), 0, 1));
            }
        } else {
            $kisaAd = 'PE';
        }
    @endphp

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-[#E5E7EB] flex flex-col shrink-0 h-screen sticky top-0">
        <!-- Logo -->
        <div class="h-16 flex items-center px-6 border-b border-[#E5E7EB] shrink-0">
            <span class="text-[#C96A2B] font-display font-bold text-lg">Randevu Ajandam</span>
            <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded bg-gray-100 text-[8px] font-extrabold text-[#6B7280] uppercase tracking-wider font-display border">Personel</span>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
            <!-- Dashboard link -->
            <a href="{{ route('personel.panel') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('personel.panel') ? 'bg-[#FFF7ED] text-[#C96A2B] font-semibold border-l-4 border-[#C96A2B]' : 'text-[#6B7280] hover:text-[#111827] hover:bg-[#FAFAFA] font-medium text-sm' }}">
                <svg class="w-5 h-5 transition-colors {{ request()->routeIs('personel.panel') ? 'text-[#C96A2B]' : 'text-[#6B7280] group-hover:text-[#C96A2B]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"></path>
                </svg>
                <span class="font-display">Panel Özeti</span>
            </a>

            <!-- Condition-based Links -->
            @if($personelUser && $personelUser->yetkisiVarMi('randevu'))
                <!-- Randevu Takvimi -->
                <a href="{{ route('personel.randevular') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('personel.randevular') ? 'bg-[#FFF7ED] text-[#C96A2B] font-semibold border-l-4 border-[#C96A2B]' : 'text-[#6B7280] hover:text-[#111827] hover:bg-[#FAFAFA] font-medium text-sm' }}">
                    <svg class="w-5 h-5 transition-colors {{ request()->routeIs('personel.randevular') ? 'text-[#C96A2B]' : 'text-[#6B7280] group-hover:text-[#C96A2B]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path>
                    </svg>
                    <span class="font-display">Randevu Takvimi</span>
                </a>

                <!-- Randevu Talepleri -->
                <a href="{{ route('personel.randevular.talepler') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('personel.randevular.talepler') ? 'bg-[#FFF7ED] text-[#C96A2B] font-semibold border-l-4 border-[#C96A2B]' : 'text-[#6B7280] hover:text-[#111827] hover:bg-[#FAFAFA] font-medium text-sm' }}">
                    <svg class="w-5 h-5 transition-colors {{ request()->routeIs('personel.randevular.talepler') ? 'text-[#C96A2B]' : 'text-[#6B7280] group-hover:text-[#C96A2B]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span class="font-display">Randevu Talepleri</span>
                </a>
            @endif

            @if($personelUser && $personelUser->yetkisiVarMi('hasta'))
                <a href="{{ route('personel.hastalar.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('personel.hastalar.*') ? 'bg-[#FFF7ED] text-[#C96A2B] font-semibold border-l-4 border-[#C96A2B]' : 'text-[#6B7280] hover:text-[#111827] hover:bg-[#FAFAFA] font-medium text-sm' }}">
                    <svg class="w-5 h-5 transition-colors {{ request()->routeIs('personel.hastalar.*') ? 'text-[#C96A2B]' : 'text-[#6B7280] group-hover:text-[#C96A2B]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A2.25 2.25 0 0112.75 21.5h-1.5a2.25 2.25 0 01-2.25-2.263V19.13m-2.621-3.072a9.3 9.3 0 00-2.638-.37c-1.618 0-3.113.411-4.417 1.136a1.125 1.125 0 00-.518.985v2.247c0 .622.506 1.124 1.128 1.124H6v-2.247a8.97 8.97 0 012.378-5.877zM7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"></path>
                    </svg>
                    <span class="font-display">Hasta Yönetimi</span>
                </a>
            @endif

            @if($personelUser && $personelUser->yetkisiVarMi('odeme'))
                <a href="{{ route('personel.odemeler.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('personel.odemeler.*') ? 'bg-[#FFF7ED] text-[#C96A2B] font-semibold border-l-4 border-[#C96A2B]' : 'text-[#6B7280] hover:text-[#111827] hover:bg-[#FAFAFA] font-medium text-sm' }}">
                    <svg class="w-5 h-5 transition-colors {{ request()->routeIs('personel.odemeler.*') ? 'text-[#C96A2B]' : 'text-[#6B7280] group-hover:text-[#C96A2B]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.268-.118a5.5 5.5 0 007.478-4.992c0-3.037-2.463-5.5-5.5-5.5L9 3m3 3L9 6"></path>
                    </svg>
                    <span class="font-display">Ödeme İşlemleri</span>
                </a>
            @endif
        </nav>

        <!-- Sidebar Profile Footer -->
        <div class="p-4 border-t border-[#E5E7EB] flex items-center justify-between bg-white shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-full bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-xs font-bold font-display">
                    {{ $kisaAd }}
                </div>
                <div class="min-w-0">
                    <span class="block text-xs font-bold text-[#111827] truncate">{{ $personelUser->ad_soyad }}</span>
                    <span class="block text-[9px] text-[#6B7280] capitalize font-medium">{{ $personelUser->rol }}</span>
                </div>
            </div>
            
            <form action="{{ route('personel.cikis') }}" method="POST" class="inline-flex shrink-0">
                @csrf
                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-gray-50 transition-colors" title="Çıkış Yap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"></path>
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Header -->
        <header class="h-16 bg-white border-b border-[#E5E7EB] flex items-center justify-between px-8 z-10 shrink-0">
            <div>
                <h1 class="text-lg font-bold font-display text-[#111827]">@yield('sayfa_baslik', 'Genel Bakış')</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold text-[#6B7280]">{{ $personelUser->klinik->ad }}</span>
            </div>
        </header>

        <!-- Content Container -->
        <main class="flex-1 overflow-y-auto p-8">
            @yield('icerik')
        </main>
    </div>
    <!-- Global Select2 Initialization Script -->
    <script>
        $(document).ready(function() {
            $('select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible') || $(this).hasClass('no-select2') || $(this).data('no-select2')) {
                    return;
                }
                $(this).select2({
                    width: '100%',
                    placeholder: $(this).attr('placeholder') || 'Seçiniz...'
                });
            });
        });
    </script>
</body>
</html>
