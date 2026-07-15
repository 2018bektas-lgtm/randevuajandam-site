@extends('hekim.layout')

@section('baslik', 'Hekim Paneli - Randevu Ajandam')
@section('sayfa_baslik', 'Panel Özeti')

@section('icerik')
    <!-- Clinic Invitations -->
    @if(isset($davetiyeler) && $davetiyeler->count() > 0)
        @foreach($davetiyeler as $davetiye)
            <div class="mb-6 p-6 rounded-2xl bg-amber-50 border border-amber-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-100/80 text-[#C96A2B] flex items-center justify-center font-bold text-xl">
                        🏥
                    </div>
                    <div>
                        <h4 class="font-bold text-[#111827] text-sm font-display">{{ $davetiye->klinik->ad }} Daveti</h4>
                        <p class="text-xs text-[#6B7280] mt-0.5">Bu klinik sizi bünyesine katılmaya davet ediyor.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <form action="{{ route('frontend.hekim.klinik.davet.kabul.post', $davetiye->token) }}" method="POST" onsubmit="return confirm('Mevcut bireysel paketiniz iptal edilecek ve kliniğe geçişiniz yapılacaktır. Emin misiniz?');">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all">
                            Kabul Et
                        </button>
                    </form>
                    <form action="{{ route('frontend.hekim.klinik.davet.reddet', $davetiye->token) }}" method="POST" onsubmit="return confirm('Daveti reddetmek istediğinize emin misiniz?');">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition-all">
                            Reddet
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    @endif

    <!-- Welcome Banner -->
    <div class="mb-8 p-8 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden group">
        <div class="relative z-10">
            <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight">
                Tekrar Hoş Geldiniz, Sayın {{ $doktor->unvan ? $doktor->unvan . ' ' : '' }}{{ $doktor->ad_soyad }}!
            </h2>
            <p class="text-sm text-[#6B7280] mt-1.5">
                Randevu Ajandam Hekim Paneli'ndesiniz. Randevularınızı, hastalarınızı, takviminizi ve klinik çalışma planınızı buradan kolayca yönetebilirsiniz.
            </p>
        </div>
        <div class="absolute right-0 bottom-0 top-0 w-1/3 bg-gradient-to-l from-[#FFF7ED]/35 to-transparent pointer-events-none"></div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Stat Card 1: Toplam Randevu -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] hover:-translate-y-0.5 transition-all duration-300">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Toplam Randevularım</span>
            <span class="text-3xl font-bold font-display text-[#111827] mt-2 block">{{ $toplamRandevu }}</span>
            <span class="text-xs text-[#C96A2B] mt-1.5 block font-medium">Sistemde kayıtlı toplam randevu</span>
        </div>
        <!-- Stat Card 2: Kayıtlı Hastalar -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] hover:-translate-y-0.5 transition-all duration-300">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Kayıtlı Hastalarım</span>
            <span class="text-3xl font-bold font-display text-[#111827] mt-2 block">{{ $kayitliHasta }}</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Randevu almış tekil hasta sayısı</span>
        </div>
        <!-- Stat Card 3: Bekleyen Randevular -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] hover:-translate-y-0.5 transition-all duration-300">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Bekleyen Talepler</span>
            <span class="text-3xl font-bold font-display text-[#111827] mt-2 block">{{ $bekleyenTalep }}</span>
            <span class="text-xs text-[#C96A2B] mt-1.5 block font-medium">Onay bekleyen randevu talebi</span>
        </div>
        <!-- Stat Card 4: Randevu Durumu -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] hover:-translate-y-0.5 transition-all duration-300">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Randevu Durumu</span>
            <span class="text-3xl font-bold font-display {{ $klinikDurumu ? 'text-emerald-600' : 'text-red-500' }} mt-2 block">{{ $klinikDurumu ? 'Aktif' : 'Pasif' }}</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">{{ $klinikDurumu ? 'Randevu alımına açık' : 'Randevu alımına kapalı' }}</span>
        </div>
    </div>

    <!-- Info Section & Sidebar layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Quick Actions & Package Overview -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Quick Actions Panel -->
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-4">Hızlı Erişim ve İşlemler</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="{{ route('hekim.randevu.takvim') }}" class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:bg-white hover:border-[#C96A2B] transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-[#111827] group-hover:text-[#C96A2B] transition-colors">Takvimi Yönet</span>
                            <span class="block text-[11px] text-[#6B7280] mt-0.5">Çalışma planını ve randevuları gör</span>
                        </div>
                    </a>
                    
                    <a href="{{ route('hekim.randevu.hastalar') }}" class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:bg-white hover:border-[#C96A2B] transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-[#111827] group-hover:text-[#C96A2B] transition-colors">Hastalarım</span>
                            <span class="block text-[11px] text-[#6B7280] mt-0.5">Sistemdeki kayıtlı hastalarını gör</span>
                        </div>
                    </a>

                    <a href="{{ route('hekim.randevu.calisma-saatleri') }}" class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:bg-white hover:border-[#C96A2B] transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-[#111827] group-hover:text-[#C96A2B] transition-colors">Çalışma Saatleri</span>
                            <span class="block text-[11px] text-[#6B7280] mt-0.5">Haftalık çalışma gün ve saatleri</span>
                        </div>
                    </a>

                    <a href="{{ route('hekim.hizmetler.index') }}" class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:bg-white hover:border-[#C96A2B] transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-[#111827] group-hover:text-[#C96A2B] transition-colors">Hizmetleri Listele</span>
                            <span class="block text-[11px] text-[#6B7280] mt-0.5">Muayene ve tedavi ücretlerini düzenle</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Subscription Details Widget -->
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-5">Üyelik ve Paket Detayları</h3>
                
                @if($doktor->klinikteMi())
                    <div class="p-5 rounded-2xl bg-gradient-to-br from-[#FFF7ED] to-[#FFFBEB] border border-[#E7B58A]/30 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-[#C96A2B] block font-display">Bağlı Klinik</span>
                            <h4 class="text-lg font-extrabold font-display text-[#111827] mt-1.5">{{ $doktor->klinik->ad }}</h4>
                            <p class="text-xs text-[#6B7280] mt-2 leading-relaxed font-sans">Klinik bünyesinde hizmet vermektesiniz. Üyelik ve abonelik ödemeleriniz klinik yönetimi tarafından karşılanmaktadır.</p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-[#E7B58A]/20 text-xs">
                            <span class="text-gray-400">Klinik Rolünüz:</span>
                            <span class="font-bold text-[#111827] capitalize">{{ $doktor->klinik_rolu }}</span>
                        </div>
                    </div>
                @elseif($doktor->paket)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                        <div class="p-5 rounded-2xl bg-gradient-to-br from-[#FFF7ED] to-[#FFFBEB] border border-[#E7B58A]/30 flex flex-col justify-between h-full min-h-[160px] relative overflow-hidden group">
                            <div class="relative z-10">
                                <span class="text-[10px] uppercase font-bold tracking-wider text-[#C96A2B] block">Aktif Paket</span>
                                <h4 class="text-xl font-extrabold font-display text-[#111827] mt-1.5">{{ $doktor->paket->ad }}</h4>
                                <p class="text-xs text-[#6B7280] mt-2 leading-relaxed">{{ $doktor->paket->aciklama }}</p>
                            </div>
                            
                            <div class="mt-4 relative z-10 flex items-center justify-between border-t border-[#E7B58A]/20 pt-4">
                                <div>
                                    <span class="text-[10px] text-[#6B7280] block">Ödeme Periyodu</span>
                                    <span class="text-xs font-bold text-[#111827] capitalize">{{ $doktor->odeme_periyodu === 'yillik' ? 'Yıllık' : 'Aylık' }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-[#6B7280] block">Bitiş Tarihi</span>
                                    <span class="text-xs font-bold text-[#111827]">
                                        {{ $doktor->uyelik_bitis ? $doktor->uyelik_bitis->format('d.m.Y') : '-' }}
                                    </span>
                                </div>
                            </div>
                            <div class="absolute right-[-10px] top-[-10px] w-24 h-24 bg-white/20 rounded-full blur-xl pointer-events-none group-hover:scale-110 transition-transform"></div>
                        </div>

                        <div class="space-y-3">
                            <span class="text-xs font-bold text-[#111827] block font-display">Paketinizin Sağladığı Özellikler:</span>
                            <ul class="space-y-2.5">
                                @if(is_array($doktor->paket->ozellikler))
                                    @foreach($doktor->paket->ozellikler as $ozellik)
                                        <li class="flex items-start gap-2.5 text-xs text-[#4B5563]">
                                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>{{ $ozellik }}</span>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="text-xs text-[#6B7280]">Paket özellikleri bulunamadı.</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="p-6 rounded-xl border border-dashed border-[#E5E7EB] text-center">
                        <svg class="w-12 h-12 text-[#9CA3AF] mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"></path>
                        </svg>
                        <span class="block text-sm font-semibold text-[#111827]">Herhangi bir pakete abone değilsiniz</span>
                        <p class="text-xs text-[#6B7280] mt-1">Hizmet vermeye başlamak için lütfen paket satın alınız.</p>
                        <a href="{{ route('frontend.paketler') }}" class="mt-4 inline-block px-4 py-2 bg-[#C96A2B] text-white font-semibold text-xs rounded-xl hover:bg-[#b05a20] transition-colors font-display">
                            Paketleri İncele
                        </a>
                    </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Doctor Professional Details -->
        <div class="space-y-6">
            
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-4">Mesleki Profil Bilgileri</h3>
                <div class="space-y-4 text-sm">
                    <div class="pb-3 border-b border-[#E5E7EB]">
                        <span class="block text-xs text-[#6B7280] font-bold uppercase tracking-wider font-display">Unvan & Branş</span>
                        <span class="text-[#111827] mt-1.5 block font-semibold font-display">
                            {{ $doktor->unvan ? $doktor->unvan . ' ' : '' }}{{ $doktor->uzmanlik_alani ?? 'Genel Hekim' }}
                        </span>
                    </div>
                    


                    <div class="pb-3 border-b border-[#E5E7EB]">
                        <span class="block text-xs text-[#6B7280] font-bold uppercase tracking-wider font-display">Mezuniyet</span>
                        <span class="text-[#111827] mt-1.5 block font-medium">
                            @if(is_array($doktor->mezuniyet) && count($doktor->mezuniyet) > 0)
                                {{ implode(', ', $doktor->mezuniyet) }}
                            @elseif(is_string($doktor->mezuniyet) && !empty($doktor->mezuniyet))
                                {{ $doktor->mezuniyet }}
                            @else
                                Belirtilmedi
                            @endif
                        </span>
                    </div>

                    <div class="pb-3 border-b border-[#E5E7EB]">
                        <span class="block text-xs text-[#6B7280] font-bold uppercase tracking-wider font-display">İletişim Bilgileri</span>
                        <span class="text-[#111827] mt-1.5 block font-medium">
                            E-posta: {{ $doktor->e_posta }}
                        </span>
                        <span class="text-[#111827] mt-1.5 block font-medium">
                            Telefon: {{ $doktor->telefon ?? 'Belirtilmedi' }}
                        </span>
                    </div>

                    <div>
                        <span class="block text-xs text-[#6B7280] font-bold uppercase tracking-wider font-display">Biyografi</span>
                        <p class="text-[#6B7280] text-xs leading-relaxed mt-2.5">
                            {{ $doktor->biyografi ?? 'Henüz biyografi eklenmedi.' }}
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
