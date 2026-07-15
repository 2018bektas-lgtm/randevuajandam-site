<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('baslik', 'Hekim Paneli - Randevu Ajandam')</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/logo.png') }}" type="image/png">
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Flatpickr Datepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* Select2 Premium Theme Override */
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 12px !important;
            background-color: #FAFAFA !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 10px !important;
            font-size: 0.875rem !important;
            font-family: 'Inter', sans-serif !important;
            color: #111827 !important;
            transition: border-color 0.15s, box-shadow 0.15s !important;
        }
        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #C96A2B !important;
            box-shadow: 0 0 0 3px rgba(201, 106, 43, 0.1) !important;
            outline: none !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #111827 !important;
            line-height: 42px !important;
            padding-left: 0 !important;
            padding-right: 24px !important;
            font-size: 0.875rem !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            flex: 1 !important;
            min-width: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9CA3AF !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
            right: 10px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #6B7280 transparent transparent transparent !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #C96A2B transparent !important;
        }
        .select2-dropdown {
            border: 1px solid #E5E7EB !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 30px rgba(31, 41, 55, 0.08) !important;
            font-family: 'Inter', sans-serif !important;
            overflow: hidden !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #E5E7EB !important;
            border-radius: 8px !important;
            padding: 6px 10px !important;
            font-size: 0.8125rem !important;
            font-family: 'Inter', sans-serif !important;
            outline: none !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #C96A2B !important;
            box-shadow: 0 0 0 2px rgba(201, 106, 43, 0.1) !important;
        }
        .select2-results__option {
            font-size: 0.875rem !important;
            padding: 8px 12px !important;
            color: #4B5563 !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #FFF7ED !important;
            color: #C96A2B !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #FFF7ED !important;
            color: #C96A2B !important;
            font-weight: 600 !important;
        }
        .select2-search--dropdown {
            padding: 8px !important;
        }
        .select2 {
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
        }
    </style>

    <style>
        /* Flatpickr Custom Premium Theme Styling */
        .flatpickr-calendar {
            border-radius: 16px !important;
            border: 1px solid #E5E7EB !important;
            box-shadow: 0 10px 30px rgba(31, 41, 55, 0.08) !important;
            font-family: 'Inter', sans-serif !important;
            padding: 6px !important;
            background: #ffffff !important;
        }
        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange,
        .flatpickr-day.selected.inRange,
        .flatpickr-day.startRange.inRange,
        .flatpickr-day.endRange.inRange,
        .flatpickr-day.selected:focus,
        .flatpickr-day.startRange:focus,
        .flatpickr-day.endRange:focus,
        .flatpickr-day.selected:hover,
        .flatpickr-day.startRange:hover,
        .flatpickr-day.endRange:hover,
        .flatpickr-day.prevMonthDay.selected,
        .flatpickr-day.nextMonthDay.selected {
            background: #C96A2B !important;
            border-color: #C96A2B !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            border-radius: 12px !important;
        }
        .flatpickr-day:hover {
            background: #FFF7ED !important;
            border-color: #FFF7ED !important;
            color: #C96A2B !important;
            border-radius: 12px !important;
        }
        .flatpickr-day.today {
            border-color: #E7B58A !important;
            border-radius: 12px !important;
            color: #C96A2B !important;
            font-weight: 600 !important;
        }
        .flatpickr-day.today:hover {
            background: #FFF7ED !important;
            border-color: #C96A2B !important;
            color: #C96A2B !important;
        }
        .flatpickr-day.today.selected {
            color: #ffffff !important;
            background: #C96A2B !important;
        }
        .flatpickr-months .flatpickr-month {
            color: #111827 !important;
            fill: #111827 !important;
            font-family: 'Outfit', sans-serif !important;
            font-weight: 700 !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months {
            font-weight: 700 !important;
            font-family: 'Outfit', sans-serif !important;
        }
        .flatpickr-weekdays {
            font-family: 'Outfit', sans-serif !important;
        }
        span.flatpickr-weekday {
            color: #6B7280 !important;
            font-weight: 600 !important;
        }
        .flatpickr-calendar.arrowTop:after, .flatpickr-calendar.arrowTop:before {
            border-bottom-color: #ffffff !important;
        }
        .flatpickr-time input:focus {
            background: #FFF7ED !important;
        }
        .flatpickr-time .numInputWrapper span.arrowUp:after {
            border-bottom-color: #C96A2B !important;
        }
        .flatpickr-time .numInputWrapper span.arrowDown:after {
            border-top-color: #C96A2B !important;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F5F4;
        }
        .font-display {
            font-family: 'Outfit', sans-serif;
        }
        /* Sidebar dropdown groups */
        .nav-group-btn { color: #374151; }
        .nav-group-btn:hover { background: #FAFAFA; color: #111827; }
        .nav-group-btn.is-open .nav-chevron { transform: rotate(180deg); }
        .nav-group-btn.has-active { color: #C96A2B; }
        .nav-group-panel { display: none; }
        .nav-group-panel.is-open { display: block; }
        .nav-child-active { background: #FFF7ED; color: #C96A2B; font-weight: 600; }
        .nav-child-idle { color: #6B7280; }
        .nav-child-idle:hover { color: #111827; background: #F9FAFB; }

        /* Floating Pixel Animations */
        @keyframes float-pixel-1 {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-3px) translateX(-1px); }
        }
        @keyframes float-pixel-2 {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-1.5px) translateX(2px); }
        }
        .animate-pixel-1 {
            animation: float-pixel-1 3.5s ease-in-out infinite;
        }
        .animate-pixel-2 {
            animation: float-pixel-2 4.5s ease-in-out infinite;
        }

        /* Metallic Shimmer Sweep for Panel Images */
        .shimmer-overlay-small {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                115deg,
                transparent 35%,
                rgba(255, 255, 255, 0.75) 48%,
                rgba(255, 255, 255, 0.9) 50%,
                rgba(255, 255, 255, 0.75) 52%,
                transparent 65%
            );
            background-size: 200% 100%;
            background-position: -200% 0;
            mix-blend-mode: overlay;
            pointer-events: none;
            border-radius: 50%;
            animation: shimmer-sweep-img 5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
        @keyframes shimmer-sweep-img {
            0% { background-position: -200% 0; }
            25% { background-position: 180% 0; }
            100% { background-position: 180% 0; }
        }

        /* Logo Breathing Animation for Small Icons */
        @keyframes logo-breathing-small {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.06); }
        }
        .logo-breathing-small-animate {
            animation: logo-breathing-small 4s ease-in-out infinite;
        }

        /* Pulsating Ambient Glow Behind Logo */
        @keyframes pulse-glow {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.15); opacity: 0.5; }
        }
        .logo-ambient-glow {
            position: absolute;
            width: 70px;
            height: 70px;
            background: radial-gradient(circle, rgba(201, 106, 43, 0.4) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
            animation: pulse-glow 3s ease-in-out infinite;
            filter: blur(8px);
            left: 10px;
            top: 10px;
        }

        /* Shimmering Text Gradient Animation */
        @keyframes text-shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .brand-text-shimmer {
            background: linear-gradient(
                120deg,
                #111827 20%,
                #C96A2B 45%,
                #E7B58A 50%,
                #C96A2B 55%,
                #111827 80%
            );
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: text-shimmer 5s linear infinite;
        }

        /* Logo Mark Pulse (Entire R Logo breathing) */
        @keyframes logo-pulse {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 2px 6px rgba(31, 41, 55, 0.04)); }
            50% { transform: scale(1.03); filter: drop-shadow(0 4px 12px rgba(201, 106, 43, 0.1)); }
        }
        .logo-pulse-animate {
            animation: logo-pulse 4s ease-in-out infinite;
        }

        /* Preloader Specific Styles */
        .preloader-ambient-glow {
            position: absolute;
            width: 140px;
            height: 140px;
            background: radial-gradient(circle, rgba(201, 106, 43, 0.45) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
            animation: pulse-glow 3s ease-in-out infinite;
            filter: blur(14px);
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }
        .preloader-logo-container {
            perspective: 600px;
            position: relative;
            z-index: 10;
        }
        .preloader-logo-wrapper {
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            animation: preloader-logo-breathing 4s ease-in-out infinite;
        }
        @keyframes preloader-logo-breathing {
            0%, 100% { transform: scale(1) translateY(0); filter: drop-shadow(0 4px 10px rgba(31, 41, 55, 0.05)); }
            50% { transform: scale(1.03) translateY(-3px); filter: drop-shadow(0 12px 24px rgba(201, 106, 43, 0.18)); }
        }
        .preloader-shimmer {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                115deg,
                transparent 35%,
                rgba(255, 255, 255, 0.75) 48%,
                rgba(255, 255, 255, 0.9) 50%,
                rgba(255, 255, 255, 0.75) 52%,
                transparent 65%
            );
            background-size: 200% 100%;
            background-position: -200% 0;
            mix-blend-mode: overlay;
            pointer-events: none;
            border-radius: 50%;
            animation: shimmer-sweep-img 5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        /* Floating Particles for Preloader */
        @keyframes float-particle-up-1 {
            0% { transform: translateY(20px) translateX(0) scale(0); opacity: 0; }
            30% { opacity: 0.8; }
            80% { opacity: 0.4; }
            100% { transform: translateY(-30px) translateX(-15px) scale(1.2) rotate(45deg); opacity: 0; }
        }
        @keyframes float-particle-up-2 {
            0% { transform: translateY(30px) translateX(5px) scale(0); opacity: 0; }
            20% { opacity: 0.9; }
            70% { opacity: 0.3; }
            100% { transform: translateY(-40px) translateX(10px) scale(1) rotate(-35deg); opacity: 0; }
        }
        @keyframes float-particle-up-3 {
            0% { transform: translateY(40px) translateX(-5px) scale(0); opacity: 0; }
            40% { opacity: 0.7; }
            90% { opacity: 0.2; }
            100% { transform: translateY(-20px) translateX(-20px) scale(1.4) rotate(90deg); opacity: 0; }
        }

        .float-particle-1 {
            animation: float-particle-up-1 4.5s ease-in-out infinite;
            top: 25%;
            left: 20%;
        }
        .float-particle-2 {
            animation: float-particle-up-2 3.5s ease-in-out infinite;
            animation-delay: 1.2s;
            top: 45%;
            left: 15%;
        }
        .float-particle-3 {
            animation: float-particle-up-3 5.2s ease-in-out infinite;
            animation-delay: 2.3s;
            top: 35%;
            left: 30%;
        }

        /* Preloader Progress Bar Animation */
        @keyframes load-progress {
            0% { left: -40%; width: 40%; }
            50% { left: 20%; width: 60%; }
            100% { left: 100%; width: 40%; }
        }
        .loader-progress-bar {
            animation: load-progress 1.8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
    </style>
@include('partials.sidebar-ysb-theme')
</head>
<body class="text-[#4B5563] antialiased h-screen overflow-hidden flex flex-col md:flex-row relative">

    <!-- Premium Preloader -->
    <div id="preloader" class="fixed inset-0 bg-[#F5F5F4] z-50 flex flex-col items-center justify-center transition-opacity duration-500 ease-out select-none">
        <div class="relative w-28 h-28 flex items-center justify-center preloader-logo-container">
            <!-- Ambient Pulsating Light Glow -->
            <div class="preloader-ambient-glow"></div>

            <!-- Floating Particle Pixels -->
            <div class="absolute inset-0 pointer-events-none z-20">
                <span class="absolute w-2 h-2 bg-[#C96A2B] rounded-full opacity-0 float-particle-1"></span>
                <span class="absolute w-1.5 h-1.5 bg-[#E7B58A] rounded-sm opacity-0 float-particle-2"></span>
                <span class="absolute w-2.5 h-2.5 bg-[#C96A2B] rounded-sm opacity-0 float-particle-3"></span>
            </div>

            <!-- Logo Image Wrapper -->
            <div class="preloader-logo-wrapper w-full h-full flex items-center justify-center">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Randevu Ajandam"
                     style="mix-blend-mode: multiply;"
                     class="w-full h-full object-contain">
                <div class="preloader-shimmer"></div>
            </div>
        </div>

        <!-- Loading Bar -->
        <div class="mt-6 flex flex-col items-center gap-2">
            <span class="text-xs font-bold font-display tracking-widest text-[#1F2937] uppercase opacity-75">Yükleniyor</span>
            <div class="w-24 h-1 bg-[#E5E7EB] rounded-full overflow-hidden relative">
                <div class="absolute inset-y-0 left-0 bg-[#C96A2B] rounded-full loader-progress-bar"></div>
            </div>
        </div>
    </div>

    <!-- Mobile Header -->
    <header class="md:hidden w-full h-16 bg-white border-b border-[#E5E7EB] flex items-center justify-between px-6 z-30 relative overflow-hidden">
        <div class="flex items-center gap-2.5 relative">
            <!-- Animated R Logo for Mobile Header -->
            <div class="relative w-9 h-9 select-none flex-shrink-0 logo-breathing-small-animate">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Randevu Ajandam"
                     style="mix-blend-mode: multiply;"
                     class="w-full h-full object-contain">
                <div class="shimmer-overlay-small"></div>
            </div>
            <span class="font-bold font-display tracking-tight text-base brand-text-shimmer select-none ml-0.5">Randevu Ajandam</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="hizliKapatModalAc()" class="p-2 rounded-lg bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 cursor-pointer" title="Hızlı Saat Kapat">
                ⚡
            </button>
            <button id="menuToggle" class="p-2 rounded-lg hover:bg-slate-50 border border-slate-100 text-[#111827] cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </header>

    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="ysb fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:static w-[18rem] h-full md:h-screen md:shrink-0 flex flex-col z-40 overflow-hidden">
        <div class="ysb-brand shrink-0">
            <div class="ysb-brand-row">
                <div class="ysb-brand-mark">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Randevu Ajandam">
                </div>
                <div class="min-w-0">
                    <div class="ysb-brand-title">Randevu Ajandam</div>
                    <div class="ysb-brand-sub">Hekim paneli</div>
                </div>
            </div>
        </div>
        @include('partials.sidebar-nav-hekim')

        <!-- Sidebar Profile Footer -->
        @php
            $doktorUser = auth('doktor')->user();
            $kisaAd = '';
            if ($doktorUser && $doktorUser->ad_soyad) {
                $words = explode(' ', $doktorUser->ad_soyad);
                $kisaAd = mb_strtoupper(mb_substr($words[0], 0, 1));
                if (count($words) > 1) {
                    $kisaAd .= mb_strtoupper(mb_substr(end($words), 0, 1));
                }
            } else {
                $kisaAd = 'HE';
            }
        @endphp
        <div class="ysb-footer shrink-0">
            <div class="ysb-footer-row">
                <div class="ysb-avatar">{{ $kisaAd }}</div>
                <div class="min-w-0">
                    <div class="ysb-user-name" title="{{ $doktorUser->ad_soyad }}">{{ $doktorUser->ad_soyad }}</div>
                    <div class="ysb-user-role">{{ $doktorUser->uzmanlik_alani ?? 'Hekim' }}</div>
                </div>
                <div class="ysb-footer-actions">
                    <form action="{{ route('hekim.cikis') }}" method="POST" class="inline" onsubmit="onayModalAc(event, this, 'Cikis yapmak istediginize emin misiniz?');">
                        @csrf
                        <button type="submit" class="ysb-icon-btn" title="Cikis">
                            <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Overlay on mobile sidebar opened -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-35 hidden transition-opacity duration-300"></div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden md:h-screen">

        <!-- Top Header for Page Title & Profile Dropdown -->
        <header class="hidden md:flex items-center justify-between px-10 h-18 bg-white border-b border-[#E5E7EB] shrink-0">
            <div>
                <h1 class="text-lg font-bold font-display text-[#111827]">@yield('sayfa_baslik', 'Panel Özeti')</h1>
            </div>

            <div class="flex items-center gap-6">
                <!-- Hızlı Saat Kapat Butonu -->
                <button onclick="hizliKapatModalAc()" class="flex items-center gap-2 text-xs bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 hover:border-red-300 px-4 py-2 rounded-full font-bold font-display transition-all cursor-pointer">
                    <span>⚡ Hızlı Saat Kapat</span>
                </button>

                @php
                    if ($doktorUser->klinikteMi()) {
                        $paketAdi = $doktorUser->klinik->ad;
                        $kalanGunText = ' (Klinik Üyesi)';
                    } else {
                        $paketAdi = $doktorUser->paket ? $doktorUser->paket->ad : 'Paket Yok';
                        $kalanGunText = '';
                        if ($doktorUser->uyelik_bitis) {
                            $diff = (int) ceil(now()->diffInDays($doktorUser->uyelik_bitis, false));
                            if ($diff > 0) {
                                $kalanGunText = " ({$diff} Gün Kaldı)";
                            } else {
                                $kalanGunText = " (Süresi Doldu)";
                            }
                        }
                    }
                @endphp
                <!-- Badge in Light Copper & Copper Text -->
                <div class="flex items-center gap-2 text-xs bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 px-3.5 py-1.5 rounded-full font-semibold font-display">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#C96A2B] animate-pulse"></span>
                    <span>{{ $paketAdi }}{{ $kalanGunText }}</span>
                </div>

                <!-- Vertical Divider -->
                <span class="h-6 w-px bg-slate-200"></span>

                <!-- Profile Dropdown -->
                <div class="relative" id="profileDropdownContainer">
                    <button type="button" id="profileDropdownBtn" class="flex items-center gap-2.5 focus:outline-none cursor-pointer group">
                        @if($doktorUser->profil_resmi)
                            <img src="{{ asset($doktorUser->profil_resmi) }}" alt="{{ $doktorUser->ad_soyad }}" class="w-9 h-9 rounded-full object-cover border border-[#E5E7EB] group-hover:border-[#E7B58A]/40 transition-colors">
                        @else
                            <div class="w-9 h-9 rounded-full bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-xs font-bold font-display group-hover:bg-[#FFF2E2] transition-colors">
                                {{ $kisaAd }}
                            </div>
                        @endif
                        <span class="text-xs font-bold text-[#111827] font-display group-hover:text-[#C96A2B] transition-colors max-w-[120px] truncate">
                            {{ $doktorUser->ad_soyad }}
                        </span>
                        <svg class="w-4 h-4 text-[#6B7280] group-hover:text-[#C96A2B] transition-all" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu Box -->
                    <div id="profileDropdownMenu" class="absolute right-0 mt-2.5 w-48 bg-white border border-[#E5E7EB] rounded-2xl shadow-xl py-2 z-50 hidden transition-all transform scale-95 opacity-0 duration-150">
                        <div class="px-4 py-2 border-b border-[#E5E7EB] text-left">
                            <span class="block text-xs font-bold text-[#111827] truncate font-display">{{ $doktorUser->unvan ? $doktorUser->unvan . ' ' : '' }}{{ $doktorUser->ad_soyad }}</span>
                            <span class="block text-[10px] text-[#C96A2B] uppercase tracking-wide font-semibold font-display mt-0.5 truncate">{{ $doktorUser->uzmanlik_alani ?? 'Hekim' }}</span>
                        </div>

                        <a href="{{ route('hekim.profil') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-[#4B5563] hover:text-[#C96A2B] hover:bg-[#FFF7ED]/35 font-display transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                            </svg>
                            Profilimi Düzenle
                        </a>

                        <a href="{{ route('hekim.two-factor') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-[#4B5563] hover:text-[#C96A2B] hover:bg-[#FFF7ED]/35 font-display transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            İki Adımlı Doğrulama
                        </a>
                        <a href="{{ route('hekim.sifre') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-[#4B5563] hover:text-[#C96A2B] hover:bg-[#FFF7ED]/35 font-display transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path>
                            </svg>
                            Şifre Değiştir
                        </a>

                        <div class="border-t border-[#E5E7EB] my-1"></div>

                        <!-- Logout -->
                        <form action="{{ route('hekim.cikis') }}" method="POST" class="w-full" onsubmit="onayModalAc(event, this, 'Çıkış yapmak istediğinize emin misiniz?');">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 font-display transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"></path>
                                </svg>
                                Çıkış Yap
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Dashboard Page Content -->
        <main class="flex-1 p-6 md:p-10 overflow-y-auto">
            @yield('icerik')
        </main>
    </div>

    <!-- Toggle Script for Mobile Sidebar -->
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if(menuToggle && sidebar && overlay) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }
    </script>
    <!-- Profile Dropdown Toggle Script -->
    <script>
        const profileDropdownBtn = document.getElementById('profileDropdownBtn');
        const profileDropdownMenu = document.getElementById('profileDropdownMenu');

        if (profileDropdownBtn && profileDropdownMenu) {
            profileDropdownBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (profileDropdownMenu.classList.contains('hidden')) {
                    profileDropdownMenu.classList.remove('hidden');
                    setTimeout(() => {
                        profileDropdownMenu.classList.remove('scale-95', 'opacity-0');
                        profileDropdownMenu.classList.add('scale-100', 'opacity-100');
                    }, 50);
                } else {
                    closeProfileDropdown();
                }
            });

            function closeProfileDropdown() {
                profileDropdownMenu.classList.remove('scale-100', 'opacity-100');
                profileDropdownMenu.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    profileDropdownMenu.classList.add('hidden');
                }, 150);
            }

            document.addEventListener('click', (e) => {
                if (!profileDropdownBtn.contains(e.target) && !profileDropdownMenu.contains(e.target)) {
                    closeProfileDropdown();
                }
            });
        }
    </script>
    <!-- Preloader Script -->
    <script>
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.classList.add('opacity-0');
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 500);
            }
        });
    </script>

    <!-- Premium Alert Modal -->
    <div id="alertModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
        <div id="alertModalContainer" class="bg-white rounded-2xl sm:rounded-3xl border border-[#E5E7EB] shadow-[0_25px_60px_-15px_rgba(31,41,55,0.2)] w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]">
            <!-- Modal Body -->
            <div class="p-5 sm:p-8 text-center space-y-4 sm:space-y-5 overflow-y-auto flex-1">
                <!-- Success Icon Wrapper -->
                <div id="alertSuccessIcon" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-[#ECFDF5] text-emerald-500 border border-emerald-100 flex items-center justify-center mx-auto shadow-[0_10px_25px_-5px_rgba(16,185,129,0.15)] transition-all duration-300 transform scale-75 opacity-0 hidden">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                    </svg>
                </div>

                <!-- Error Icon Wrapper -->
                <div id="alertErrorIcon" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-[#FEF2F2] text-red-500 border border-red-100 flex items-center justify-center mx-auto shadow-[0_10px_25px_-5px_rgba(239,68,68,0.15)] transition-all duration-300 transform scale-75 opacity-0 hidden">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                    </svg>
                </div>

                <!-- Warning Icon Wrapper -->
                <div id="alertWarningIcon" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-[#FFF7ED] text-[#C96A2B] border border-[#FFEDD5]/50 flex items-center justify-center mx-auto shadow-[0_10px_25px_-5px_rgba(201,106,43,0.15)] transition-all duration-300 transform scale-75 opacity-0 hidden">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path>
                    </svg>
                </div>

                <div class="space-y-2">
                    <h3 id="alertModalTitle" class="text-lg font-bold font-display text-[#111827] tracking-tight">Mesaj</h3>
                    <div id="alertModalContent" class="text-sm text-[#4B5563] leading-relaxed font-sans px-2 break-words"></div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 sm:p-6 bg-slate-50 border-t border-[#E5E7EB] text-center shrink-0">
                <button id="closeAlertBtn" class="w-full py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display shadow-md shadow-orange-500/10 outline-none">
                    Tamam
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Modal & Toast System Script -->
    <script>
        function mesajModalAc(mesaj, tip = 'basarili') {
            const modal = document.getElementById('alertModal');
            const container = document.getElementById('alertModalContainer');
            const successIcon = document.getElementById('alertSuccessIcon');
            const errorIcon = document.getElementById('alertErrorIcon');
            const warningIcon = document.getElementById('alertWarningIcon');
            const title = document.getElementById('alertModalTitle');
            const content = document.getElementById('alertModalContent');
            const closeBtn = document.getElementById('closeAlertBtn');

            if (modal && container && title && content) {
                // Clear icons and styles
                successIcon.classList.add('hidden', 'scale-75', 'opacity-0');
                errorIcon.classList.add('hidden', 'scale-75', 'opacity-0');
                if (warningIcon) warningIcon.classList.add('hidden', 'scale-75', 'opacity-0');

                if (tip === 'basarili') {
                    successIcon.classList.remove('hidden');
                    title.innerText = 'İşlem Başarılı';
                    title.className = 'text-lg font-bold font-display text-emerald-700';
                    if (closeBtn) {
                        closeBtn.className = 'w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display shadow-md shadow-emerald-500/15 outline-none';
                    }
                    setTimeout(() => {
                        successIcon.classList.remove('scale-75', 'opacity-0');
                        successIcon.classList.add('scale-100', 'opacity-100');
                    }, 50);
                } else if (tip === 'uyari' || tip === 'warning') {
                    if (warningIcon) {
                        warningIcon.classList.remove('hidden');
                        title.innerText = 'Uyarı';
                        title.className = 'text-lg font-bold font-display text-amber-700';
                        if (closeBtn) {
                            closeBtn.className = 'w-full py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display shadow-md shadow-orange-500/15 outline-none';
                        }
                        setTimeout(() => {
                            warningIcon.classList.remove('scale-75', 'opacity-0');
                            warningIcon.classList.add('scale-100', 'opacity-100');
                        }, 50);
                    }
                } else {
                    errorIcon.classList.remove('hidden');
                    title.innerText = 'Bir Hata Oluştu';
                    title.className = 'text-lg font-bold font-display text-red-700';
                    if (closeBtn) {
                        closeBtn.className = 'w-full py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display shadow-md shadow-red-500/15 outline-none';
                    }
                    setTimeout(() => {
                        errorIcon.classList.remove('scale-75', 'opacity-0');
                        errorIcon.classList.add('scale-100', 'opacity-100');
                    }, 50);
                }

                // Handle message content (can be string or array for multiple errors)
                if (Array.isArray(mesaj)) {
                    content.innerHTML = mesaj.map(m => `<p>${m}</p>`).join('');
                } else {
                    content.innerHTML = `<p>${mesaj}</p>`;
                }

                modal.classList.remove('hidden');
                setTimeout(() => {
                    container.classList.remove('scale-95', 'opacity-0');
                    container.classList.add('scale-100', 'opacity-100');
                }, 50);
            }
        }

        // Premium Dynamic Toast Notification Helper
        function toastAc(mesaj, tip = 'basarili') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'fixed top-4 right-4 left-4 sm:left-auto sm:top-5 sm:right-5 z-[9999] flex flex-col gap-3 max-w-none sm:max-w-sm w-auto sm:w-full';
                document.body.appendChild(container);
            }
            
            const toast = document.createElement('div');
            toast.className = 'bg-white rounded-2xl border border-[#E5E7EB] shadow-2xl overflow-hidden transform translate-x-full opacity-0 transition-all duration-300 flex flex-col relative';
            
            let accentColor = '#C96A2B';
            let iconHTML = '';
            let titleText = 'Bilgi';
            
            if (tip === 'basarili') {
                accentColor = '#10B981';
                titleText = 'Başarılı';
                iconHTML = `<div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                    </svg>
                </div>`;
            } else if (tip === 'hata' || tip === 'error') {
                accentColor = '#EF4444';
                titleText = 'Hata';
                iconHTML = `<div class="w-8 h-8 rounded-full bg-red-50 text-red-500 border border-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                    </svg>
                </div>`;
            } else if (tip === 'uyari' || tip === 'warning') {
                accentColor = '#F59E0B';
                titleText = 'Uyarı';
                iconHTML = `<div class="w-8 h-8 rounded-full bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path>
                    </svg>
                </div>`;
            }
            
            toast.innerHTML = `
                <div class="h-1 w-full" style="background-color: ${accentColor}"></div>
                <div class="p-4 flex items-start gap-3">
                    ${iconHTML}
                    <div class="flex-1 space-y-1">
                        <h4 class="text-sm font-extrabold font-display text-[#111827] leading-none">${titleText}</h4>
                        <p class="text-xs text-[#6B7280] leading-relaxed font-sans">${mesaj}</p>
                    </div>
                    <button class="text-[#9CA3AF] hover:text-[#4B5563] focus:outline-none cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="absolute bottom-0 left-0 h-0.5 bg-[#E5E7EB] w-full">
                    <div class="h-full transition-all duration-3000 ease-linear" style="width: 100%; background-color: ${accentColor}"></div>
                </div>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');
            }, 50);
            
            const closeBtn = toast.querySelector('button');
            let dismissed = false;
            
            const dismiss = () => {
                if (dismissed) return;
                dismissed = true;
                toast.classList.remove('translate-x-0', 'opacity-100');
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            };
            
            closeBtn.addEventListener('click', dismiss);
            
            const progress = toast.querySelector('.absolute.bottom-0 div');
            if (progress) {
                setTimeout(() => {
                    progress.style.transition = 'width 3000ms linear';
                    progress.style.width = '0%';
                }, 10);
            }
            
            setTimeout(dismiss, 3000);
        }
 
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('alertModal');
            const container = document.getElementById('alertModalContainer');
            const closeBtn = document.getElementById('closeAlertBtn');
 
            if (modal && container) {
                function closeModal() {
                    container.classList.remove('scale-100', 'opacity-100');
                    container.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                }
 
                if (closeBtn) {
                    closeBtn.addEventListener('click', closeModal);
                }
 
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            }
 
            // PHP Side Session triggers
            @if(session('basarili'))
                mesajModalAc("{{ session('basarili') }}", 'basarili');
            @endif
 
            @if($errors->any())
                const errors = [];
                @foreach($errors->all() as $error)
                    errors.push("{{ $error }}");
                @endforeach
                mesajModalAc(errors, 'hata');
            @endif
        });
    </script>
 
    <!-- Premium Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
        <div id="confirmModalContainer" class="bg-white rounded-2xl sm:rounded-3xl border border-[#E5E7EB] shadow-[0_25px_60px_-15px_rgba(31,41,55,0.2)] w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]">
            <!-- Modal Body -->
            <div class="p-5 sm:p-8 text-center space-y-4 sm:space-y-5 overflow-y-auto flex-1">
                <!-- Warning Icon -->
                <div id="confirmWarningIcon" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-[#FFF7ED] text-[#C96A2B] border border-[#FFEDD5]/50 flex items-center justify-center mx-auto shadow-[0_10px_25px_-5px_rgba(201,106,43,0.15)] transition-all duration-300 transform scale-75 opacity-0">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path>
                    </svg>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-bold font-display text-[#111827] tracking-tight">Emin misiniz?</h3>
                    <p id="confirmModalMessage" class="text-sm text-[#4B5563] leading-relaxed font-sans px-2">Bu işlemi gerçekleştirmek istediğinize emin misiniz?</p>
                </div>
            </div>
 
            <!-- Modal Footer -->
            <div class="p-4 sm:p-6 bg-slate-50 border-t border-[#E5E7EB] flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 shrink-0">
                <button id="confirmCancelBtn" class="w-full sm:flex-1 py-3 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-100 text-[#4B5563] font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display outline-none shadow-sm">
                    İptal Et
                </button>
                <button id="confirmSubmitBtn" class="w-full sm:flex-1 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display shadow-md shadow-orange-500/10 outline-none">
                    Evet, Onayla
                </button>
            </div>
        </div>
    </div>
 
    <!-- Confirm Modal script -->
    <script>
        let activeFormToSubmit = null;
        let activeConfirmCallback = null;
 
        function onayModalAc(event, form, message, callback = null) {
            if (event) event.preventDefault();
            activeFormToSubmit = form;
            activeConfirmCallback = callback;
 
            const modal = document.getElementById('confirmModal');
            const container = document.getElementById('confirmModalContainer');
            const msgElement = document.getElementById('confirmModalMessage');
            const warningIcon = document.getElementById('confirmWarningIcon');
            const submitBtn = document.getElementById('confirmSubmitBtn');
 
            if (modal && container && msgElement) {
                msgElement.innerText = message;
                
                // Customize confirmation button dynamically
                const lowerMsg = message.toLowerCase();
                const isDestructive = lowerMsg.includes('sil') || lowerMsg.includes('iptal') || lowerMsg.includes('geri alınamaz') || lowerMsg.includes('silmek');
                
                if (isDestructive) {
                    if (submitBtn) {
                        submitBtn.className = 'flex-1 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display shadow-md shadow-red-500/15 outline-none';
                        submitBtn.innerText = 'Evet, Sil';
                    }
                } else {
                    if (submitBtn) {
                        submitBtn.className = 'flex-1 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display shadow-md shadow-orange-500/15 outline-none';
                        submitBtn.innerText = 'Evet, Onayla';
                    }
                }
                
                modal.classList.remove('hidden');
 
                if (warningIcon) {
                    warningIcon.classList.add('scale-75', 'opacity-0');
                }
 
                setTimeout(() => {
                    container.classList.remove('scale-95', 'opacity-0');
                    container.classList.add('scale-100', 'opacity-100');
                    if (warningIcon) {
                        warningIcon.classList.remove('scale-75', 'opacity-0');
                        warningIcon.classList.add('scale-100', 'opacity-100');
                    }
                }, 50);
            }
        }
 
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('confirmModal');
            const container = document.getElementById('confirmModalContainer');
            const cancelBtn = document.getElementById('confirmCancelBtn');
            const submitBtn = document.getElementById('confirmSubmitBtn');
 
            if (modal && container) {
                function closeConfirmModal() {
                    container.classList.remove('scale-100', 'opacity-100');
                    container.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                    activeFormToSubmit = null;
                    activeConfirmCallback = null;
                }
 
                if (cancelBtn) {
                    cancelBtn.addEventListener('click', closeConfirmModal);
                }
 
                if (submitBtn) {
                    submitBtn.addEventListener('click', function() {
                        if (activeFormToSubmit) {
                            activeFormToSubmit.submit();
                        } else if (activeConfirmCallback) {
                            activeConfirmCallback();
                        }
                        closeConfirmModal();
                    });
                }
 
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeConfirmModal();
                    }
                });
            }
        });
    </script>

    <!-- Hızlı Saat Kapatma Modalı -->
    <div id="hizliKapatModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
        <div id="hizliKapatModalContainer" class="bg-white rounded-2xl border border-[#E5E7EB] shadow-2xl max-w-lg w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
            <!-- Modal Header -->
            <div class="p-6 border-b border-[#E5E7EB] flex items-center justify-between bg-white">
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display flex items-center gap-2">
                    <span>⚡</span> Hızlı Saat Dilimi Kapat / Aç
                </h3>
                <button onclick="hizliKapatModalKapat()" class="text-[#6B7280] hover:text-[#1F2937] cursor-pointer outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Tarih Seçin</label>
                        <span id="secili_tarih_formatli" class="text-xs font-bold text-[#C96A2B] font-display">Bugün</span>
                    </div>

                    <!-- Yatay Tarih Şeridi -->
                    <div class="flex items-center gap-2">
                        <div id="tarih_seridi" class="flex-1 flex gap-2 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-transparent snap-x">
                            <!-- JS ile dinamik olarak 14 gün doldurulacak -->
                        </div>

                        <!-- Manuel Takvim Seçici Butonu -->
                        <div class="relative flex-shrink-0">
                            <input type="date" id="hizli_kapat_tarih" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}"
                                   onchange="hizliKapatTarihDegisti(this.value)"
                                   class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                            <button type="button" class="p-3 rounded-2xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#1F2937] hover:text-[#C96A2B] hover:border-[#C96A2B] transition-all flex items-center justify-center shadow-sm cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Kapatılacak / Açılacak Saat Dilimleri</label>
                    <div id="hizli_kapat_slotlar_container" class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2">
                        <!-- Ajax ile doldurulacak -->
                        <div class="col-span-full py-6 text-center text-[#6B7280] text-xs">
                            Saat dilimleri yükleniyor...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-slate-50 border-t border-[#E5E7EB] flex items-center justify-between gap-3">
                <span class="text-[10px] text-[#6B7280] leading-relaxed max-w-[70%]">
                    * Sürgülü butonu aktif ettiğiniz saat dilimleri anında randevuya kapatılır. Pasif duruma getirdiğinizde ise tekrar kullanıma açılır.
                </span>
                <div class="flex items-center gap-3">
                    <button onclick="hizliKapatModalKapat()" class="px-5 py-2.5 rounded-xl bg-[#1F2937] hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display shadow-sm">
                        Kapat
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hızlı Saat Kapat Scriptleri -->
    <script>
        let hizliKapatDegisiklikYapildi = false;

        function tarihSeridiniOlustur(seciliTarihStr) {
            const serit = document.getElementById('tarih_seridi');
            if (!serit) return;

            const bugun = new Date();
            let html = '';

            for (let i = 0; i < 14; i++) {
                const tarih = new Date();
                tarih.setDate(bugun.getDate() + i);

                const yyyy = tarih.getFullYear();
                const mm = String(tarih.getMonth() + 1).padStart(2, '0');
                const dd = String(tarih.getDate()).padStart(2, '0');
                const tarihStr = `${yyyy}-${mm}-${dd}`;

                const gunAdi = tarih.toLocaleDateString('tr-TR', { weekday: 'short' }); // Örn: Pzt, Sal
                const gunNo = tarih.getDate();
                const ayAdi = tarih.toLocaleDateString('tr-TR', { month: 'short' }); // Örn: Haz, Tem

                const isSelected = (tarihStr === seciliTarihStr);
                const activeClasses = isSelected
                    ? 'bg-[#C96A2B] text-white border-[#C96A2B] shadow-md shadow-orange-500/10'
                    : 'bg-[#FAFAFA] text-[#4B5563] border-[#E5E7EB] hover:border-[#C96A2B] hover:text-[#C96A2B]';

                html += `
                    <button type="button" onclick="hizliKapatSerittenTarihSec('${tarihStr}')"
                            class="flex-shrink-0 snap-start flex flex-col items-center justify-center w-14 py-2.5 rounded-2xl border text-center transition-all duration-200 cursor-pointer ${activeClasses}">
                        <span class="text-[9px] font-bold uppercase tracking-wider ${isSelected ? 'text-orange-100' : 'text-[#6B7280]' }">${gunAdi}</span>
                        <span class="text-base font-extrabold font-display leading-tight mt-0.5">${gunNo}</span>
                        <span class="text-[8px] font-semibold uppercase tracking-wider ${isSelected ? 'text-orange-100' : 'text-[#6B7280]' } mt-0.5">${ayAdi}</span>
                    </button>
                `;
            }

            serit.innerHTML = html;

            // Seçili tarih formatlı başlığını güncelle
            const formatliBaslik = document.getElementById('secili_tarih_formatli');
            if (formatliBaslik) {
                const parts = seciliTarihStr.split('-');
                const secilenDate = new Date(parts[0], parts[1] - 1, parts[2]);
                const bugunStr = bugun.toISOString().split('T')[0];
                const yarin = new Date();
                yarin.setDate(bugun.getDate() + 1);
                const yarinStr = yarin.toISOString().split('T')[0];

                if (seciliTarihStr === bugunStr) {
                    formatliBaslik.innerText = 'Bugün';
                } else if (seciliTarihStr === yarinStr) {
                    formatliBaslik.innerText = 'Yarın';
                } else {
                    formatliBaslik.innerText = secilenDate.toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', weekday: 'long' });
                }
            }
        }

        function hizliKapatSerittenTarihSec(tarihStr) {
            const input = document.getElementById('hizli_kapat_tarih');
            if (input) {
                input.value = tarihStr;
            }
            tarihSeridiniOlustur(tarihStr);
            hizliKapatSlotlariYukle(tarihStr);
        }

        function hizliKapatTarihDegisti(tarihStr) {
            tarihSeridiniOlustur(tarihStr);
            hizliKapatSlotlariYukle(tarihStr);
        }

        function hizliKapatModalAc() {
            hizliKapatDegisiklikYapildi = false;
            const modal = document.getElementById('hizliKapatModal');
            const container = document.getElementById('hizliKapatModalContainer');
            if (modal && container) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    container.classList.remove('scale-95', 'opacity-0');
                    container.classList.add('scale-100', 'opacity-100');
                }, 50);

                // Bugünün slotlarını yükle
                const bugun = document.getElementById('hizli_kapat_tarih').value;
                tarihSeridiniOlustur(bugun); // Şeridi oluştur
                hizliKapatSlotlariYukle(bugun);
            }
        }

        function hizliKapatModalKapat() {
            const modal = document.getElementById('hizliKapatModal');
            const container = document.getElementById('hizliKapatModalContainer');
            if (modal && container) {
                container.classList.remove('scale-100', 'opacity-100');
                container.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    if (hizliKapatDegisiklikYapildi) {
                        window.location.reload();
                    }
                }, 300);
            }
        }

        function hizliKapatSlotlariYukle(tarih) {
            const container = document.getElementById('hizli_kapat_slotlar_container');
            container.innerHTML = `
                <div class="col-span-full py-6 text-center text-[#6B7280] text-xs animate-pulse">
                    Saat dilimleri yükleniyor...
                </div>
            `;

            fetch(`{{ route('hekim.randevu.hizli-kapat-slotlar') }}?tarih=${tarih}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.aktif_mi) {
                        container.innerHTML = `
                            <div class="col-span-full py-6 text-center text-red-500 text-xs font-semibold bg-red-50 rounded-xl border border-red-100">
                                ${data.mesaj}
                            </div>
                        `;
                        return;
                    }

                    if (data.slots.length === 0) {
                        container.innerHTML = `
                            <div class="col-span-full py-6 text-center text-[#6B7280] text-xs">
                                Bu tarihe ait çalışma saati aralığı bulunamadı.
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    data.slots.forEach((slot, index) => {
                        let statusColor = 'bg-[#FAFAFA] border-[#E5E7EB]';
                        let badgeHtml = '';
                        let disabled = '';

                        if (slot.dolu_mu) {
                            statusColor = 'bg-emerald-50 border-emerald-100 opacity-60 pointer-events-none';
                            badgeHtml = '<span class="absolute right-2 top-2 text-[8px] bg-emerald-500 text-white font-bold px-1.5 py-0.5 rounded font-display">DOLU</span>';
                            disabled = 'disabled';
                        } else if (slot.ogle_mi) {
                            statusColor = 'bg-amber-50 border-amber-100 opacity-60 pointer-events-none';
                            badgeHtml = '<span class="absolute right-2 top-2 text-[8px] bg-amber-500 text-white font-bold px-1.5 py-0.5 rounded font-display">ÖĞLE</span>';
                            disabled = 'disabled';
                        } else if (slot.kapali_mi) {
                            statusColor = 'bg-red-50/50 border-red-200';
                            badgeHtml = '<span class="absolute right-2 top-2 text-[8px] bg-red-500 text-white font-bold px-1.5 py-0.5 rounded font-display">KAPALI</span>';
                        }

                        html += `
                            <label id="slot-card-${index}" class="relative p-4 rounded-2xl border ${statusColor} flex flex-col justify-between gap-3 select-none cursor-pointer hover:border-[#C96A2B] transition-all duration-200 shadow-sm">
                                ${badgeHtml}
                                <span class="text-xs font-bold text-[#111827] font-display pt-1">${slot.saat_baslangic} - ${slot.saat_bitis}</span>

                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-[10px] text-[#6B7280] font-semibold">Kapalı</span>
                                    <div class="relative inline-flex items-center">
                                        <input type="checkbox" name="hizli_kapat_saatler[]" value="${slot.saat_baslangic}"
                                               ${slot.kapali_mi ? 'checked' : ''} ${disabled}
                                               onchange="hizliKapatToggle(this, 'slot-card-${index}')"
                                               class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#C96A2B] transition-colors duration-200"></div>
                                    </div>
                                </div>
                            </label>
                        `;
                    });
                    container.innerHTML = html;
                })
                .catch(err => {
                    container.innerHTML = `
                        <div class="col-span-full py-6 text-center text-red-500 text-xs bg-red-50 rounded-xl border border-red-100">
                            Slotlar yüklenirken bir hata oluştu.
                        </div>
                    `;
                });
        }

        function hizliKapatToggle(checkbox, cardId) {
            const card = document.getElementById(cardId);
            if (!card) return;

            // Kartı işlem sırasında soluklaştır ve koru
            card.classList.add('opacity-50', 'pointer-events-none');

            const tarih = document.getElementById('hizli_kapat_tarih').value;
            const checkboxes = document.querySelectorAll('input[name="hizli_kapat_saatler[]"]:checked');
            const saatler = Array.from(checkboxes).map(cb => cb.value);

            // Fetch Token
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}";

            fetch(`{{ route('hekim.randevu.hizli-kapat.post') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _token: token,
                    tarih: tarih,
                    saatler: saatler
                })
            })
            .then(res => res.json())
            .then(data => {
                // Kartı tekrar aktif et
                card.classList.remove('opacity-50', 'pointer-events-none');

                if (data.basarili) {
                    hizliKapatDegisiklikYapildi = true;

                    // Mevcut görsel durum sınıflarını ve badge'leri temizle
                    card.classList.remove('bg-red-50/50', 'border-red-200', 'bg-[#FAFAFA]', 'border-[#E5E7EB]');
                    const oldBadge = card.querySelector('span.absolute');
                    if (oldBadge) {
                        oldBadge.remove();
                    }

                    if (checkbox.checked) {
                        card.classList.add('bg-red-50/50', 'border-red-200');
                        card.insertAdjacentHTML('afterbegin', '<span class="absolute right-2 top-2 text-[8px] bg-red-500 text-white font-bold px-1.5 py-0.5 rounded font-display">KAPALI</span>');
                    } else {
                        card.classList.add('bg-[#FAFAFA]', 'border-[#E5E7EB]');
                    }
                } else {
                    // İşlemi geri al
                    checkbox.checked = !checkbox.checked;
                    alert(data.mesaj || data.message || 'Kayıt sırasında bir hata oluştu.');
                }
            })
            .catch(err => {
                card.classList.remove('opacity-50', 'pointer-events-none');
                checkbox.checked = !checkbox.checked;
                console.error(err);
                alert('Sunucuyla iletişim kurulurken bir hata oluştu: ' + err.message);
            });
        }
    </script>

    <!-- jQuery (Select2 dependency) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Flatpickr Datepicker & Timepicker JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/tr.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize flatpickr on date inputs
            flatpickr('input[type="date"]', {
                locale: 'tr',
                dateFormat: 'Y-m-d',
                minDate: 'today',
                onChange: function(selectedDates, dateStr, instance) {
                    // Trigger native change event
                    const event = new Event('change', { bubbles: true });
                    instance.element.dispatchEvent(event);
                }
            });

            // Initialize flatpickr on time inputs
            flatpickr('input[type="time"]', {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true,
                onChange: function(selectedDates, dateStr, instance) {
                    // Trigger native change event
                    const event = new Event('change', { bubbles: true });
                    instance.element.dispatchEvent(event);
                }
            });
        });
    </script>

    <!-- Global Select2 Initialization -->
    <script>
        $(document).ready(function() {
            $('select').each(function() {
                // Skip if it's already initialized, marked as no-select2, or needs custom initialization (like modals or specific IDs)
                if ($(this).data('select2') || 
                    $(this).hasClass('select2-hidden-accessible') || 
                    $(this).hasClass('no-select2') || 
                    $(this).data('no-select2') ||
                    $(this).hasClass('select2-modal') ||
                    $(this).hasClass('select2-filter') ||
                    $(this).hasClass('select2-hasta-filter') ||
                    ['il', 'ilce', 'formDanisanSelect', 'formHizmetSelect'].indexOf($(this).attr('id')) !== -1 ||
                    $(this).closest('.modal, [id*="Modal"], [class*="modal"]').length > 0) {
                    return;
                }

                $(this).select2({
                    width: '100%',
                    language: {
                        noResults: function() { return 'Sonuç bulunamadı'; },
                        searching: function() { return 'Aranıyor...'; }
                    },
                    placeholder: $(this).attr('placeholder') || $(this).find('option:first').text() || 'Seçiniz...',
                    allowClear: $(this).prop('required') ? false : true
                });
            });
        });
    </script>

</body>
</html>
