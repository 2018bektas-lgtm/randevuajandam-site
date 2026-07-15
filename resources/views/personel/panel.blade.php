@extends('layouts.personel')

@section('baslik', 'Personel Paneli - ' . $klinik->ad)
@section('sayfa_baslik', 'Genel Bakış')

@section('icerik')
    @if(session('basari'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
            {{ session('basari') }}
        </div>
    @endif

    <!-- Welcome Banner -->
    <div class="mb-8 p-8 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden group">
        <div class="relative z-10">
            <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight">
                Hoş Geldiniz, Sayın {{ $personel->ad_soyad }}!
            </h2>
            <p class="text-sm text-[#6B7280] mt-1.5">
                {{ $klinik->ad }} bünyesinde <strong>{{ ucfirst($personel->rol) }}</strong> rolüyle oturum açtınız. Yetkili olduğunuz modüllere sol menüden hızlıca erişebilirsiniz.
            </p>
        </div>
        <div class="absolute right-0 bottom-0 top-0 w-1/3 bg-gradient-to-l from-[#FFF7ED]/35 to-transparent pointer-events-none"></div>
    </div>

    <!-- Quick Stats Grid (Depending on permission) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Stat Item 1: Rolü -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Rolünüz</span>
                <span class="text-2xl font-bold font-display text-[#111827] mt-1.5 block capitalize">{{ $personel->rol }}</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                </svg>
            </div>
        </div>

        <!-- Stat Item 2: Klinik Hekimleri -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Klinik Hekim Sayısı</span>
                <span class="text-2xl font-bold font-display text-[#111827] mt-1.5 block">{{ $klinik->doktorlar()->count() }} Hekim</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6 6 0 015.08-5.92 7.5 7.5 0 006.18 0 6 6 0 015.08 5.92v.112H3z"></path>
                </svg>
            </div>
        </div>

        <!-- Stat Item 3: Ortak Hasta Havuzu -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Ortak Hasta Sayısı</span>
                <span class="text-2xl font-bold font-display text-[#111827] mt-1.5 block">{{ $klinik->hastalar()->count() }} Hasta</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Active Clinic Announcements -->
    <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <h3 class="text-lg font-bold font-display text-[#111827] mb-5">Aktif Klinik Duyuruları</h3>
        
        @php
            $duyurular = $klinik->duyurular()->where('aktif_mi', true)->orderBy('created_at', 'desc')->take(3)->get();
        @endphp

        <div class="space-y-4">
            @forelse($duyurular as $duyuru)
                <div class="p-5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB]
                    @if($duyuru->onem_derecesi === 'acil') border-l-4 border-l-red-500 @elseif($duyuru->onem_derecesi === 'onemli') border-l-4 border-l-amber-500 @endif">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-[#111827]">{{ $duyuru->baslik }}</span>
                        <span class="text-[10px] text-[#6B7280]">{{ $duyuru->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    <p class="text-xs text-[#4B5563] leading-relaxed">{{ Str::limit($duyuru->icerik, 200) }}</p>
                </div>
            @empty
                <p class="text-xs text-[#6B7280] py-4 text-center">Aktif klinik duyurusu bulunmamaktadır.</p>
            @endforelse
        </div>
    </div>
@endsection
