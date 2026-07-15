@extends('klinik.layout')

@section('baslik', 'Klinik Yönetim Paneli - ' . $klinik->ad)
@section('sayfa_baslik', 'Klinik Genel Bakış')

@section('icerik')
    <!-- Welcome/Clinic Info Banner -->
    <div class="mb-8 p-8 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden group">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight">
                    {{ $klinik->ad }}
                </h2>
                <p class="text-sm text-[#6B7280] mt-1.5">
                    Klinik Yönetim Paneli'ndesiniz. Kliniğinize bağlı doktorları, sekreterleri, hastaları ve klinik finansını buradan yönetebilirsiniz.
                </p>
            </div>
            <div class="shrink-0 flex items-center gap-3">
                @if($klinik->logo)
                    <img src="{{ asset($klinik->logo) }}" alt="{{ $klinik->ad }}" class="w-16 h-16 rounded-xl object-cover border border-[#E5E7EB]">
                @else
                    <div class="w-16 h-16 rounded-xl bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-xl font-bold font-display">
                        {{ mb_strtoupper(mb_substr($klinik->ad, 0, 2)) }}
                    </div>
                @endif
            </div>
        </div>
        <div class="absolute right-0 bottom-0 top-0 w-1/3 bg-gradient-to-l from-[#FFF7ED]/35 to-transparent pointer-events-none"></div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Stat Card 1: Aktif Hekim -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">Aktif Hekimler</span>
                <div class="w-8 h-8 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A2.25 2.25 0 0112.75 21.5h-1.5a2.25 2.25 0 01-2.25-2.263V19.13m-2.621-3.072a9.3 9.3 0 00-2.638-.37c-1.618 0-3.113.411-4.417 1.136a1.125 1.125 0 00-.518.985v2.247c0 .622.506 1.124 1.128 1.124H6v-2.247a8.97 8.97 0 012.378-5.877zM7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"></path>
                    </svg>
                </div>
            </div>
            <span class="text-3xl font-bold font-display text-[#111827]">{{ $doktorSayisi }}</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Paket Limiti: {{ $klinik->max_doktor_sayisi }} hekim</span>
        </div>

        <!-- Stat Card 2: Personel Sayısı -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">Klinik Personeli</span>
                <div class="w-8 h-8 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
            <span class="text-3xl font-bold font-display text-[#111827]">{{ $personelSayisi }}</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Aktif sekreter ve personeller</span>
        </div>

        <!-- Stat Card 3: Toplam Hasta -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">Ortak Hasta Havuzu</span>
                <div class="w-8 h-8 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                    </svg>
                </div>
            </div>
            <span class="text-3xl font-bold font-display text-[#111827]">{{ $toplamHasta }}</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Toplam kayıtlı tekil hasta</span>
        </div>

        <!-- Stat Card 4: Gelir Bu Ay -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">Bu Ayki Gelir</span>
                <div class="w-8 h-8 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.268-.118a5.5 5.5 0 007.478-4.992c0-3.037-2.463-5.5-5.5-5.5L9 3m3 3L9 6"></path>
                    </svg>
                </div>
            </div>
            <span class="text-2xl font-bold font-display text-emerald-600">₺{{ number_format($gelirBuAy, 2, ',', '.') }}</span>
            <span class="text-xs text-[#6B7280] mt-2 block font-medium">Klinik bünyesindeki tüm gelirler</span>
        </div>
    </div>

    <!-- Main Layout Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left 2 Columns -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Quick Actions Panel -->
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-4">Klinik Yönetim İşlemleri</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="{{ route('hekim.klinik.doktorlar') }}" class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:bg-white hover:border-[#C96A2B] transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6 6 0 015.08-5.92 7.5 7.5 0 006.18 0 6 6 0 015.08 5.92v.112H3z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-[#111827] group-hover:text-[#C96A2B] transition-colors">Hekimleri Yönet</span>
                            <span class="block text-[11px] text-[#6B7280] mt-0.5">Davet gönder, hekimleri çıkar veya listele</span>
                        </div>
                    </a>

                    <a href="{{ route('hekim.klinik.personeller') }}" class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:bg-white hover:border-[#C96A2B] transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-[#111827] group-hover:text-[#C96A2B] transition-colors">Personel Yönetimi</span>
                            <span class="block text-[11px] text-[#6B7280] mt-0.5">Sekreter, muhasebeci ekle ve yetkilendir</span>
                        </div>
                    </a>

                    <a href="{{ route('hekim.klinik.giderler') }}" class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:bg-white hover:border-[#C96A2B] transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5a1.5 1.5 0 011.5 1.5v12a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V6a1.5 1.5 0 011.5-1.5zm10.5 11.25h.008v.008h-.008V15.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-[#111827] group-hover:text-[#C96A2B] transition-colors">Klinik Giderleri</span>
                            <span class="block text-[11px] text-[#6B7280] mt-0.5">Kira, maaş ve fatura giderlerini takip et</span>
                        </div>
                    </a>

                    <a href="{{ route('hekim.klinik.hakedisler') }}" class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:bg-white hover:border-[#C96A2B] transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.75h-15a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 004.5 21h15a2.25 2.25 0 002.25-2.25V5.25A2.25 2.25 0 0019.5 1.5z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-[#111827] group-hover:text-[#C96A2B] transition-colors">Hakediş Yönetimi</span>
                            <span class="block text-[11px] text-[#6B7280] mt-0.5">Hekim komisyon ve net hakediş hesapları</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Appointments Table -->
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-4">Son Randevular (Klinik Geneli)</h3>
                
                @if($sonRandevular->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                                    <th class="pb-3 font-display">Hasta</th>
                                    <th class="pb-3 font-display">Hekim</th>
                                    <th class="pb-3 font-display">Tarih & Saat</th>
                                    <th class="pb-3 font-display">Durum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB]">
                                @foreach($sonRandevular as $randevu)
                                    <tr class="text-sm text-[#4B5563]">
                                        <td class="py-3.5 font-semibold text-[#111827]">
                                            {{ $randevu->hasta ? $randevu->hasta->ad_soyad : ($randevu->ad_soyad ?? 'Kayıtsız Hasta') }}
                                        </td>
                                        <td class="py-3.5 text-xs">
                                            {{ $randevu->doktor ? $randevu->doktor->unvan . ' ' . $randevu->doktor->ad_soyad : '-' }}
                                        </td>
                                        <td class="py-3.5 text-xs">
                                            {{ $randevu->tarih instanceof \DateTime ? $randevu->tarih->format('d.m.Y') : \Carbon\Carbon::parse($randevu->tarih)->format('d.m.Y') }}
                                            <span class="text-[#6B7280] ml-1">{{ mb_substr($randevu->saat, 0, 5) }}</span>
                                        </td>
                                        <td class="py-3.5">
                                            @if($randevu->durum === 'onaylandi')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Onaylandı</span>
                                            @elseif($randevu->durum === 'beklemede')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">Beklemede</span>
                                            @elseif($randevu->durum === 'iptal')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700">İptal</span>
                                            @elseif($randevu->durum === 'tamamlandi')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">Tamamlandı</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-50 text-gray-700">{{ $randevu->durum }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-xs text-[#6B7280] py-4 text-center">Kliniğe ait güncel randevu kaydı bulunamadı.</p>
                @endif
            </div>
        </div>

        <!-- Right Sidebar (Financial Overview and Subscription Info) -->
        <div class="space-y-6">
            <!-- Financial Mini Card -->
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-4">Bu Ay Finansal Özet</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-emerald-50/50 border border-emerald-100">
                        <div>
                            <span class="text-[10px] text-[#6B7280] block font-semibold uppercase">Toplam Gelir</span>
                            <span class="text-base font-bold text-emerald-700 mt-0.5 block">₺{{ number_format($gelirBuAy, 2, ',', '.') }}</span>
                        </div>
                        <div class="text-emerald-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-red-50/50 border border-red-100">
                        <div>
                            <span class="text-[10px] text-[#6B7280] block font-semibold uppercase">Toplam Gider</span>
                            <span class="text-base font-bold text-red-700 mt-0.5 block">₺{{ number_format($giderBuAy, 2, ',', '.') }}</span>
                        </div>
                        <div class="text-red-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"></path>
                            </svg>
                        </div>
                    </div>

                    @php
                        $netKar = $gelirBuAy - $giderBuAy;
                    @endphp
                    <div class="border-t border-[#E5E7EB] pt-4 flex items-center justify-between">
                        <span class="text-sm font-semibold text-[#111827]">Net Kâr / Zarar:</span>
                        <span class="text-base font-bold font-display {{ $netKar >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ $netKar >= 0 ? '+' : '' }}₺{{ number_format($netKar, 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Subscription Details Card -->
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-4">Abonelik ve Paket Bilgileri</h3>
                
                @if($klinik->paket)
                    <div class="space-y-4">
                        <div class="p-4 rounded-xl bg-amber-50/50 border border-amber-100/60">
                            <span class="text-[10px] uppercase font-bold text-[#C96A2B] block">Aktif Abonelik</span>
                            <span class="text-lg font-bold text-[#111827] mt-1 block">{{ $klinik->paket->ad }}</span>
                        </div>
                        
                        <div class="text-xs space-y-2">
                            <div class="flex justify-between py-1.5 border-b border-[#F5F5F4]">
                                <span class="text-[#6B7280]">Ödeme Periyodu:</span>
                                <span class="font-semibold text-[#111827] capitalize">{{ $klinik->odeme_periyodu === 'yillik' ? 'Yıllık' : 'Aylık' }}</span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-[#F5F5F4]">
                                <span class="text-[#6B7280]">Hekim Limiti:</span>
                                <span class="font-semibold text-[#111827]">{{ $doktorSayisi }} / {{ $klinik->max_doktor_sayisi }}</span>
                            </div>
                            <div class="flex justify-between py-1.5">
                                <span class="text-[#6B7280]">Son Kullanma:</span>
                                <span class="font-semibold text-[#111827]">
                                    {{ $klinik->uyelik_bitis ? ($klinik->uyelik_bitis instanceof \DateTime ? $klinik->uyelik_bitis->format('d.m.Y') : \Carbon\Carbon::parse($klinik->uyelik_bitis)->format('d.m.Y')) : '-' }}
                                </span>
                            </div>
                        </div>
                        
                        <a href="{{ route('hekim.klinik.ayarlar') }}" class="w-full text-center block bg-[#FAFAFA] border border-[#E5E7EB] hover:bg-white hover:border-[#C96A2B] text-[#1F2937] hover:text-[#C96A2B] font-bold text-[10px] uppercase tracking-wider py-3 rounded-xl transition-all duration-200">
                            Detaylı Bilgi
                        </a>
                    </div>
                @else
                    <p class="text-xs text-[#6B7280] py-2">Kayıtlı paket bulunamadı.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
