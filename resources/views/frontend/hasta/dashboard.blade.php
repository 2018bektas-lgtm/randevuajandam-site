@extends('frontend.layouts.app')

@section('baslik', 'Panelim - Randevu Ajandam')

@section('icerik')
<section class="fe-page relative bg-[#FAFAFA] overflow-hidden">
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-[#C96A2B]/3 blur-[120px] pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-6 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-sm text-center lg:text-left space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#FFF7ED] to-[#FFFBEB] border border-[#E7B58A]/40 text-[#C96A2B] flex items-center justify-center font-extrabold font-display text-xl mx-auto lg:mx-0 select-none shadow-sm">
                        {{ mb_strtoupper(mb_substr($hasta->ad, 0, 1)) }}{{ mb_strtoupper(mb_substr($hasta->soyad, 0, 1)) }}
                    </div>
                    <div class="space-y-0.5">
                        <h3 class="text-sm font-bold font-display text-[#111827]">{{ $hasta->ad_soyad }}</h3>
                        <p class="text-[11px] text-[#6B7280]">{{ $hasta->e_posta }}</p>
                    </div>
                </div>

                <div class="bg-white border border-[#E5E7EB] rounded-3xl overflow-hidden shadow-sm">
                    <nav class="flex flex-col">
                        <a href="{{ route('frontend.hasta.dashboard') }}"
                           class="flex items-center gap-3 px-5 py-4 text-xs font-bold font-display uppercase tracking-wider border-b border-slate-100 transition-colors {{ request()->routeIs('frontend.hasta.dashboard') ? 'bg-slate-50 text-[#C96A2B]' : 'text-[#4B5563] hover:text-[#C96A2B] hover:bg-slate-50/50' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"></path>
                            </svg>
                            Panelim
                        </a>
                        <a href="{{ route('frontend.hasta.profil') }}"
                           class="flex items-center gap-3 px-5 py-4 text-xs font-bold font-display uppercase tracking-wider border-b border-slate-100 transition-colors {{ request()->routeIs('frontend.hasta.profil') ? 'bg-slate-50 text-[#C96A2B]' : 'text-[#4B5563] hover:text-[#C96A2B] hover:bg-slate-50/50' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                            </svg>
                            Profil Bilgilerim
                        </a>
                        <a href="{{ route('frontend.hasta.randevular') }}"
                           class="flex items-center gap-3 px-5 py-4 text-xs font-bold font-display uppercase tracking-wider border-b border-slate-100 transition-colors {{ request()->routeIs('frontend.hasta.randevular') ? 'bg-slate-50 text-[#C96A2B]' : 'text-[#4B5563] hover:text-[#C96A2B] hover:bg-slate-50/50' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"></path>
                            </svg>
                            Randevularım
                        </a>
                        <form action="{{ route('frontend.hasta.cikis') }}" method="POST" class="w-full" onsubmit="return confirm('Çıkış yapmak istediğinize emin misiniz?');">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-3 px-5 py-4 text-xs font-bold font-display uppercase tracking-wider text-red-500 hover:text-red-700 hover:bg-slate-50/50 transition-colors border-none text-left cursor-pointer bg-transparent">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"></path>
                                </svg>
                                Güvenli Çıkış
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            <!-- Content -->
            <div class="lg:col-span-3 space-y-6">

                @if(session('basarili'))
                    <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-xs text-emerald-700 font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ session('basarili') }}
                    </div>
                @endif

                @if($bekleyenYorumSayisi > 0)
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-800 font-medium flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path>
                        </svg>
                        <span>
                            <span class="font-bold">{{ $bekleyenYorumSayisi }}</span> tamamlanmış randevunuz için henüz yorum yazmadınız.
                            <a href="{{ route('frontend.hasta.randevular') }}" class="underline hover:no-underline ml-1">Yorum yaz</a>
                        </span>
                    </div>
                @endif

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm text-center space-y-1">
                        <p class="text-2xl font-extrabold font-display text-[#C96A2B]">{{ $toplamRandevuSayisi }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#6B7280] font-display">Toplam Randevu</p>
                    </div>
                    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm text-center space-y-1">
                        <p class="text-2xl font-extrabold font-display text-amber-600">{{ $yaklasanSayi }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#6B7280] font-display">Yaklaşan</p>
                    </div>
                    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm text-center space-y-1">
                        <p class="text-2xl font-extrabold font-display text-blue-600">{{ $tamamlananSayisi }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#6B7280] font-display">Tamamlanan</p>
                    </div>
                    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm text-center space-y-1">
                        <p class="text-2xl font-extrabold font-display text-{{ $bekleyenYorumSayisi > 0 ? 'amber-500' : 'slate-300' }}">{{ $bekleyenYorumSayisi }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#6B7280] font-display">Bekleyen Yorum</p>
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-lg font-bold font-display text-[#111827]">Yaklaşan Randevularım</h2>
                            <p class="text-xs text-[#6B7280]">En yakın 3 aktif randevunuz</p>
                        </div>
                        <a href="{{ route('frontend.hasta.randevular') }}"
                           class="text-[10px] font-bold font-display uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] transition-colors">
                            Tümünü Gör →
                        </a>
                    </div>

                    @if($yaklasanRandevular->isEmpty())
                        <div class="text-center py-8 space-y-2">
                            <div class="w-12 h-12 bg-[#FFF7ED] text-[#C96A2B] rounded-2xl flex items-center justify-center mx-auto border border-[#E7B58A]/20">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path>
                                </svg>
                            </div>
                            <p class="text-xs text-[#6B7280]">Yaklaşan aktif randevunuz bulunmuyor.</p>
                            <a href="{{ route('frontend.hekimler') }}"
                               class="inline-flex px-4 py-2 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-sm font-display">
                                Randevu Al
                            </a>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($yaklasanRandevular as $randevu)
                                <div class="flex items-center gap-4 p-4 bg-slate-50/70 border border-slate-100 rounded-2xl hover:border-[#E7B58A]/40 transition-all">
                                    <!-- Date Badge -->
                                    <div class="shrink-0 w-12 text-center bg-white border border-[#E5E7EB] rounded-xl py-2 shadow-sm">
                                        <p class="text-base font-extrabold font-display text-[#C96A2B] leading-none">{{ $randevu->tarih->format('d') }}</p>
                                        <p class="text-[9px] font-bold uppercase tracking-wider text-[#6B7280] mt-0.5 font-display">{{ $randevu->tarih->translatedFormat('M') }}</p>
                                    </div>
                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-[#111827] truncate">
                                            {{ $randevu->doktor->unvan ? $randevu->doktor->unvan . ' ' : '' }}{{ $randevu->doktor->ad_soyad }}
                                        </p>
                                        <p class="text-[10px] text-[#6B7280] truncate mt-0.5">{{ $randevu->hizmet->ad }}</p>
                                    </div>
                                    <!-- Time + Status -->
                                    <div class="shrink-0 text-right space-y-1">
                                        <p class="text-xs font-bold font-display text-[#111827]">{{ substr($randevu->saat, 0, 5) }}</p>
                                        @if($randevu->durum === 'beklemede')
                                            <span class="inline-block px-2 py-0.5 text-[8px] uppercase font-bold tracking-wider rounded-full bg-amber-50 text-amber-700 border border-amber-200">Beklemede</span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 text-[8px] uppercase font-bold tracking-wider rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Onaylandı</span>
                                        @endif
                                    </div>
                                    <!-- iCal Link -->
                                    <a href="{{ route('frontend.hasta.randevu.ical', $randevu->id) }}"
                                       title="Takvime Ekle (.ics)"
                                       class="shrink-0 w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-[#E5E7EB] text-[#6B7280] hover:text-[#C96A2B] hover:border-[#E7B58A]/60 transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path>
                                        </svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
