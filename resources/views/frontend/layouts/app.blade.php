<!DOCTYPE html>
<html lang="tr">
<head>
    @include('frontend.layouts.partials.head')
</head>
<body class="text-[#4B5563] antialiased min-h-screen flex flex-col pb-16 lg:pb-0 selection:bg-[#C96A2B] selection:text-white">
    @include('frontend.layouts.partials.tracking-body')

    <!-- Header -->
    @include('frontend.layouts.partials.header')

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('icerik')
    </main>

    <!-- Mobile Sticky Bottom Navigation -->
    <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-[#E5E7EB] shadow-[0_-8px_30px_rgba(0,0,0,0.04)] block lg:hidden pb-safe-bottom">
        <div class="grid grid-cols-4 h-16">
            <!-- Home -->
            <a href="/" class="flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-[#C96A2B] transition-colors {{ request()->is('/') ? 'text-[#C96A2B]' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"></path>
                </svg>
                <span class="text-[9px] font-bold font-display tracking-wide">Ana Sayfa</span>
            </a>
            
            <!-- Find Doctors -->
            <a href="{{ route('frontend.hekimler') }}" class="flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-[#C96A2B] transition-colors {{ request()->routeIs('frontend.hekimler') ? 'text-[#C96A2B]' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                </svg>
                <span class="text-[9px] font-bold font-display tracking-wide">Hekimler</span>
            </a>

            <!-- Appointments -->
            <a href="{{ route('frontend.hasta.profil') }}" class="flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-[#C96A2B] transition-colors {{ request()->routeIs('frontend.hasta.profil') ? 'text-[#C96A2B]' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"></path>
                </svg>
                <span class="text-[9px] font-bold font-display tracking-wide">Randevular</span>
            </a>

            <!-- Auth -->
            @if(Auth::guard('hasta')->check())
                <a href="{{ route('frontend.hasta.profil') }}" class="flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-[#C96A2B] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-[9px] font-bold font-display tracking-wide">Profilim</span>
                </a>
            @elseif(Auth::guard('doktor')->check())
                <a href="{{ route('hekim.panel') }}" class="flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-[#C96A2B] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.248-8.25-3.285z"></path>
                    </svg>
                    <span class="text-[9px] font-bold font-display tracking-wide">Hekim Paneli</span>
                </a>
            @else
                <a href="{{ route('frontend.hasta.giris') }}" class="flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-[#C96A2B] transition-colors {{ request()->routeIs('frontend.hasta.giris') ? 'text-[#C96A2B]' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"></path>
                    </svg>
                    <span class="text-[9px] font-bold font-display tracking-wide">Giriş Yap</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Footer -->
    @include('frontend.layouts.partials.footer')

    <!-- Scripts -->
    @include('frontend.layouts.partials.script')

</body>
</html>
