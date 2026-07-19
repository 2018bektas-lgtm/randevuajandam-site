@extends('frontend.layouts.app')

@section('baslik', 'Ödeme & Kurulum - Randevu Ajandam')

@section('head')
    <!-- jQuery and Select2 CSS & JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        /* Custom Premium Select2 Overrides */
        .select2-container--default .select2-selection--single {
            background-color: #FFF !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 0.75rem !important; /* rounded-xl */
            height: 42px !important;
            display: flex !important;
            align-items: center !important;
            padding-left: 0.5rem !important;
            padding-right: 2rem !important;
            transition: all 0.15s ease-in-out !important;
            outline: none !important;
        }
        .select2-container--default .select2-selection--single:focus-within,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #C96A2B !important;
            box-shadow: 0 0 0 4px rgba(201, 106, 43, 0.08) !important;
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
    </style>
@endsection

@section('icerik')
<style>
    /* Premium visual credit card styling */
    .credit-card {
        background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
        box-shadow: 0 15px 30px rgba(15, 23, 42, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.08);
        transition: transform 0.6s;
        transform-style: preserve-3d;
        position: relative;
    }
    .card-chip {
        background: linear-gradient(135deg, #E2E8F0 0%, #94A3B8 100%);
        border-radius: 4px;
        box-shadow: inset 0 1px 1px rgba(255,255,255,0.5);
    }
</style>

<section class="relative bg-[#FAFAFA] py-16 overflow-hidden">
    <!-- Ambient Lights -->
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-[#C96A2B]/3 blur-[120px] pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-6 relative z-10">
        
        <!-- Top Action Header -->
        <div class="mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
            <div>
                <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                    <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                    Ödeme & Kurulum
                </h2>
                <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Seçtiğiniz paketin kurulumunu ve ödemesini tamamlayın.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('frontend.hekim.onboarding.domain', ['paket' => $secilenPaket->id, 'periyot' => $periyot]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-xs font-semibold text-emerald-800 transition-all shadow-sm">
                    Domaini değiştir
                </a>
                <a href="{{ route('frontend.hekim.paket_sec') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] transition-all shadow-sm">
                    ← Paket Seçimine Dön
                </a>
            </div>
        </div>

        @if(!empty($pendingDomain['domain']))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-800">Seçilen domain (ödeme sonrası kurulur)</p>
                    <p class="text-sm font-bold text-emerald-950 font-mono mt-0.5">{{ $pendingDomain['domain'] }}</p>
                    <p class="text-[11px] text-emerald-800/80 mt-1">
                        {{ ($pendingDomain['mode'] ?? '') === 'byod' ? 'Kendi domaininiz (BYOD) — DNS sizin' : 'Pakete dahil yeni domain — ek ücret yok' }}
                    </p>
                </div>
                <a href="{{ route('frontend.hekim.onboarding.domain', ['paket' => $secilenPaket->id, 'periyot' => $periyot]) }}"
                   class="text-xs font-bold text-emerald-800 underline shrink-0">Değiştir</a>
            </div>
        @elseif(session('onboarding_domain_skipped'))
            <div class="mb-6 p-4 bg-amber-50 border border-amber-100 rounded-2xl text-xs text-amber-900">
                Domain adımını atladınız. Ödeme sonrası panelden kurabilirsiniz.
                <a href="{{ route('frontend.hekim.onboarding.domain', ['paket' => $secilenPaket->id, 'periyot' => $periyot]) }}" class="font-bold underline ml-1">Şimdi seç</a>
            </div>
        @endif

        @if(session('basarili'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-xs text-emerald-800 font-semibold">
                {{ session('basarili') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-xs text-red-600 space-y-1 font-semibold">
                @foreach($errors->all() as $error)
                    <div class="flex items-center gap-2">
                        <span class="w-1 h-1 rounded-full bg-red-500"></span>
                        <span>{{ $error }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('frontend.hekim.paket_ode.post') }}" method="POST" id="checkoutForm" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            @csrf
            
            <input type="hidden" name="paket_id" id="paketId" value="{{ $secilenPaket->id }}">
            <input type="hidden" name="odeme_periyodu" id="odemePeriyodu" value="{{ $periyot }}">

            <!-- Sol Sütun: Paket Özeti -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Paket Bilgi Kutusu -->
                <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm">
                    <h3 class="text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display mb-4">Seçilen Paket</h3>
                    
                    <div class="flex items-center justify-between pb-4 border-b border-[#E5E7EB]">
                        <div>
                            <span class="text-sm font-bold text-[#111827] font-display block">{{ $secilenPaket->ad }}</span>
                            <span class="text-[10px] text-[#6B7280] uppercase font-bold tracking-wider mt-0.5 block">
                                {{ $secilenPaket->klinikPaketiMi() ? 'Klinik Yönetim Planı' : 'Bireysel Hekim Planı' }}
                            </span>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-orange-50 border border-orange-100 text-[10px] font-bold text-[#C96A2B] uppercase tracking-wider font-display">
                            {{ $periyot === 'aylik' ? 'Aylık' : 'Yıllık' }}
                        </span>
                    </div>

                    <!-- Fiyat Detayı -->
                    <div class="py-4 border-b border-[#E5E7EB] space-y-2.5">
                        <div class="flex items-center justify-between text-xs text-[#6B7280]">
                            <span>Standart Fiyat</span>
                            <span class="font-semibold text-[#111827] font-display">
                                @if($periyot === 'aylik')
                                    ₺{{ number_format($secilenPaket->aylik_fiyat, 2, ',', '.') }}
                                @else
                                    ₺{{ number_format($secilenPaket->yillik_fiyat, 2, ',', '.') }}
                                @endif
                            </span>
                        </div>
                        
                        @php
                            $hasDiscount = ($periyot === 'aylik' && $secilenPaket->aylik_indirimli_fiyat) || ($periyot === 'yillik' && $secilenPaket->yillik_indirimli_fiyat);
                            $fiyat = $periyot === 'aylik' ? $secilenPaket->aylik_fiyat : $secilenPaket->yillik_fiyat;
                            $indirimli = $periyot === 'aylik' ? $secilenPaket->aylik_indirimli_fiyat : $secilenPaket->yillik_indirimli_fiyat;
                            $toplam = $hasDiscount ? $indirimli : $fiyat;
                        @endphp

                        @if($hasDiscount)
                            <div class="flex items-center justify-between text-xs text-emerald-600">
                                <span>İndirim Avantajı</span>
                                <span class="font-bold font-display">
                                    -₺{{ number_format($fiyat - $indirimli, 2, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Toplam Ödeme -->
                    <div class="pt-4 flex items-center justify-between">
                        <span class="text-xs font-bold text-[#1F2937]">Toplam Ödeme</span>
                        <div class="text-right">
                            <span class="text-xl font-extrabold font-display text-[#111827]">
                                ₺{{ number_format($toplam, 2, ',', '.') }}
                            </span>
                            <span class="text-[9px] text-[#6B7280] block mt-0.5">
                                {{ $periyot === 'aylik' ? 'Aylık Faturalandırılır' : 'Yıllık Faturalandırılır' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Paket Özellikleri Listesi -->
                <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm">
                    <h4 class="text-[10px] font-extrabold text-[#111827] uppercase tracking-wider font-display mb-4">Paket Kapsamı</h4>
                    <ul class="space-y-3.5">
                        @if(is_array($secilenPaket->ozellikler))
                            @foreach($secilenPaket->ozellikler as $ozellik)
                                <li class="flex items-start gap-2.5 text-xs text-[#4B5563]">
                                    <div class="w-4.5 h-4.5 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                        </svg>
                                    </div>
                                    <span class="leading-snug">{{ $ozellik }}</span>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Sağ Sütun: Ödeme ve Klinik Kurulum -->
            <div class="lg:col-span-8 bg-white border border-[#E5E7EB] rounded-3xl p-6 sm:p-8 shadow-sm space-y-8">
                
                @if($secilenPaket->klinikPaketiMi())
                    <!-- KLİNİK BİLGİLERİ (Sadece Klinik Paketleri İçin) -->
                    <div class="space-y-5">
                        <h3 class="text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display pb-2 border-b border-[#E5E7EB]">
                            🏥 Klinik / Muayenehane Kurulum Bilgileri
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Klinik Adı -->
                            <div>
                                <label for="klinik_adi" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Klinik / Muayenehane Adı</label>
                                <input type="text" name="klinik_adi" id="klinik_adi" value="{{ old('klinik_adi') }}" placeholder="Sağlık Plus Polikliniği" required
                                    class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                            </div>

                            <!-- Telefon -->
                            <div>
                                <label for="telefon" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Klinik Telefon Numarası</label>
                                <input type="text" name="telefon" id="telefon" value="{{ old('telefon') }}" placeholder="0 (2XX) XXX XX XX" required
                                    class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <!-- E-posta -->
                            <div class="md:col-span-1">
                                <label for="e_posta" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Klinik E-Posta Adresi</label>
                                <input type="email" name="e_posta" id="e_posta" value="{{ old('e_posta') }}" placeholder="klinik@iletisim.com"
                                    class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                            </div>

                            <!-- İl -->
                            <div>
                                <label for="il" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">İl</label>
                                <select name="il_id" id="il" required class="w-full">
                                    <option value="" disabled selected>İl Seçin...</option>
                                    @foreach($iller as $il)
                                        <option value="{{ $il->id }}">{{ $il->ad }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- İlçe -->
                            <div>
                                <label for="ilce" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">İlçe</label>
                                <select name="ilce_id" id="ilce" required class="w-full">
                                    <option value="" disabled selected>Önce İl Seçin...</option>
                                </select>
                            </div>
                        </div>

                        <!-- Adres -->
                        <div>
                            <label for="adres" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Klinik Açık Adresi</label>
                            <textarea name="adres" id="adres" rows="3" placeholder="Örn: Barbaros Mah. Dr. Medikal Cad. No:12 Daire:4 Ataşehir" required
                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">{{ old('adres') }}</textarea>
                        </div>
                    </div>
                @endif

                <!-- ÖDEME BİLGİLERİ -->
                <div class="space-y-6">
                    @if($tutar <= 0)
                        <!-- Ücretsiz Paket Durumu -->
                        <div class="p-5 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-[#1F2937] font-display uppercase tracking-wider">Ücretsiz Plan Kurulumu</h4>
                                <p class="text-xs text-gray-500 mt-1">Bu paket tamamen ücretsizdir. Kurulumu tamamlamak için kredi kartı girmenize gerek yoktur. Aşağıdaki butonla hemen kurulumu tamamlayabilirsiniz.</p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-[#E5E7EB]">
                            <button type="submit"
                                    class="w-full py-4 rounded-2xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                                Ücretsiz Kurulumu Tamamla
                            </button>
                        </div>
                    @else
                        @if($iyzicoAvailable && $bankAvailable)
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <label class="cursor-pointer rounded-2xl border-2 border-[#C96A2B] bg-[#FFF7ED] p-4">
                                    <input type="radio" name="odeme_yontemi" value="iyzico" checked class="sr-only">
                                    <span class="block text-xs font-bold text-[#111827]">Kredi kartı ile öde</span>
                                    <span class="mt-1 block text-[11px] text-slate-500">Güvenli iyzico ödeme altyapısı</span>
                                </label>
                                <label class="cursor-pointer rounded-2xl border border-[#E5E7EB] bg-white p-4">
                                    <input type="radio" name="odeme_yontemi" value="havale" class="sr-only">
                                    <span class="block text-xs font-bold text-[#111827]">Banka havalesi</span>
                                    <span class="mt-1 block text-[11px] text-slate-500">Yönetici onayı sonrası aktifleşir</span>
                                </label>
                            </div>
                        @elseif($iyzicoAvailable)
                            <input type="hidden" name="odeme_yontemi" value="iyzico">
                        @else
                            <input type="hidden" name="odeme_yontemi" value="havale">
                        @endif

                        @if(! $bankAvailable && ! $iyzicoAvailable)
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">
                                Havale ödeme bilgileri henüz yapılandırılmamış. Lütfen daha sonra tekrar deneyin.
                            </div>
                        @endif

                        <!-- Ücretli Paket Ödeme Formu -->
                        <div id="card-payment-fields" class="grid grid-cols-1 md:grid-cols-12 gap-8 {{ $iyzicoAvailable ? '' : 'hidden' }}">
                            <!-- Form Inputs (Sol) -->
                            <div class="md:col-span-6 space-y-4">
                                <h3 class="text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display pb-2 border-b border-[#E5E7EB]">
                                    💳 Kredi Kartı Ödeme Bilgileri
                                </h3>

                                <div class="space-y-3">
                                    <!-- Kart Sahibi -->
                                    <div>
                                        <label for="kart_sahibi" class="block text-[10px] font-bold text-[#4B5563] uppercase mb-1 font-display">Kart Sahibi Adı</label>
                                        <input type="text" name="kart_sahibi" id="kart_sahibi" placeholder="Kart Üzerindeki İsim" required
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                                    </div>

                                    <!-- Kart Numarası -->
                                    <div>
                                        <label for="kart_no" class="block text-[10px] font-bold text-[#4B5563] uppercase mb-1 font-display">Kart Numarası</label>
                                        <input type="text" name="kart_no" id="kart_no" maxlength="19" placeholder="Kart Numarası" required
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <!-- SKT -->
                                        <div>
                                            <label for="kart_skt" class="block text-[10px] font-bold text-[#4B5563] uppercase mb-1 font-display">Son Kullanma</label>
                                            <input type="text" name="kart_skt" id="kart_skt" maxlength="5" placeholder="AA/YY" required
                                                class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                                        </div>
                                        <!-- CVV -->
                                        <div>
                                            <label for="kart_cvv" class="block text-[10px] font-bold text-[#4B5563] uppercase mb-1 font-display">CVC / CVV</label>
                                            <input type="text" name="kart_cvv" id="kart_cvv" maxlength="4" placeholder="CVV" required
                                                class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sanal Kart Görseli (Sağ) -->
                            <div class="md:col-span-6 flex flex-col justify-between space-y-6">
                                <div class="space-y-4">
                                    <!-- İnteraktif Kredi Kartı Görseli -->
                                    <div class="credit-card w-full aspect-[1.58/1] rounded-2xl p-5 text-white flex flex-col justify-between relative overflow-hidden select-none">
                                        <div class="absolute w-32 h-32 bg-white/5 rounded-full -top-10 -right-10 pointer-events-none"></div>
                                        <div class="flex items-center justify-between relative z-10">
                                            <div class="card-chip w-10 h-7"></div>
                                            <span class="text-xs font-black tracking-widest font-display text-white/50">MOCK PAY</span>
                                        </div>
                                        <div class="text-lg font-mono tracking-widest text-center py-2 relative z-10" id="visualCardNo">
                                            •••• •••• •••• ••••
                                        </div>
                                        <div class="flex items-center justify-between relative z-10">
                                            <div class="text-left">
                                                <span class="block text-[8px] text-white/45 tracking-wider font-semibold font-display uppercase">Kart Sahibi</span>
                                                <span class="block text-xs font-bold font-display uppercase truncate max-w-[150px]" id="visualCardOwner">İSİM SOYAD</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="block text-[8px] text-white/45 tracking-wider font-semibold font-display uppercase">Son Kul. Tar.</span>
                                                <span class="block text-xs font-bold font-display font-mono" id="visualCardExpiry">AA/YY</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Navigasyon Butonları -->
                                <div class="space-y-3">
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center py-3.5 rounded-2xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                                        Ödemeyi Tamamla ve Sistemi Kur
                                    </button>
                                    
                                    <div class="flex items-center justify-center gap-1 text-[9px] text-gray-400">
                                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path>
                                        </svg>
                                        <span>256-Bit SSL Güvenli Ödeme Şifrelemesi</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="bank-transfer-fields" class="{{ $iyzicoAvailable ? 'hidden' : '' }} rounded-2xl border border-[#E5E7EB] bg-[#F8FAFC] p-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[#1F2937]">Banka havalesi ile ödeme</h3>
                            @if($bankAvailable)
                                <dl class="mt-4 grid grid-cols-1 gap-3 text-xs sm:grid-cols-2">
                                    <div><dt class="text-slate-500">Banka</dt><dd class="mt-1 font-bold text-slate-800">{{ $paymentSettings->banka_adi }}</dd></div>
                                    <div><dt class="text-slate-500">Hesap sahibi</dt><dd class="mt-1 font-bold text-slate-800">{{ $paymentSettings->banka_hesap_sahibi }}</dd></div>
                                    <div class="sm:col-span-2"><dt class="text-slate-500">IBAN</dt><dd class="mt-1 font-mono font-bold tracking-wide text-slate-800">{{ $paymentSettings->banka_iban }}</dd></div>
                                    @if($paymentSettings->banka_aciklama)<div class="sm:col-span-2"><dt class="text-slate-500">Not</dt><dd class="mt-1 text-slate-700">{{ $paymentSettings->banka_aciklama }}</dd></div>@endif
                                </dl>
                                <div class="mt-5"><label for="havale_referans" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-600">Havale referansı / açıklaması</label><input type="text" name="havale_referans" id="havale_referans" value="{{ old('havale_referans') }}" placeholder="Örn. banka dekont numarası" class="w-full rounded-xl border border-[#E5E7EB] px-3.5 py-2.5 text-xs"></div>
                                <button type="submit" class="mt-5 w-full rounded-2xl bg-[#C96A2B] py-3.5 text-xs font-bold uppercase tracking-wider text-white">Havale bildirimini gönder</button>
                                <p class="mt-3 text-center text-[11px] text-slate-500">Üyeliğiniz, banka hareketi yönetici tarafından onaylandığında aktifleştirilir.</p>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </form>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Turkish phone number formatting
        const telefonInput = document.getElementById('telefon');
        if (telefonInput) {
            telefonInput.addEventListener('input', function() {
                let numbers = this.value.replace(/\D/g, '');
                if (numbers.length === 0) {
                    this.value = '';
                    return;
                }
                if (numbers[0] !== '0') numbers = '0' + numbers;
                numbers = numbers.substring(0, 11);
                
                let formatted = '0';
                if (numbers.length > 1) {
                    formatted += ' (';
                    formatted += numbers.substring(1, Math.min(numbers.length, 4));
                    if (numbers.length >= 4) formatted += ')';
                }
                if (numbers.length > 4) {
                    formatted += ' ';
                    formatted += numbers.substring(4, Math.min(numbers.length, 7));
                }
                if (numbers.length > 7) {
                    formatted += ' ';
                    formatted += numbers.substring(7, Math.min(numbers.length, 9));
                }
                if (numbers.length > 9) {
                    formatted += ' ';
                    formatted += numbers.substring(9, numbers.length);
                }
                
                const start = this.selectionStart;
                const prevLen = this.value.length;
                this.value = formatted;
                const diff = formatted.length - prevLen;
                this.setSelectionRange(start + diff, start + diff);
            });
        }

        // Select2 il/ilçe loading (For clinics)
        const ilSelect = $('#il');
        const ilceSelect = $('#ilce');

        if (ilSelect.length > 0) {
            ilSelect.select2({
                placeholder: "İl Seçin...",
                allowClear: false
            });

            ilceSelect.select2({
                placeholder: "Önce İl Seçin...",
                allowClear: false
            });

            ilSelect.on('change', function() {
                const ilId = this.value;
                ilceSelect.empty().append('<option value="" disabled selected>Yükleniyor...</option>').trigger('change.select2');

                if (ilId) {
                    fetch(`/iller/${ilId}/ilceler`)
                        .then(res => res.json())
                        .then(data => {
                            ilceSelect.empty().append('<option value="" disabled selected>İlçe Seçin...</option>');
                            data.forEach(item => {
                                ilceSelect.append(new Option(item.ad, item.ad));
                            });
                            ilceSelect.trigger('change.select2');
                        })
                        .catch(err => {
                            console.error('İlçeler yüklenemedi:', err);
                            ilceSelect.empty().append('<option value="" disabled selected>Hata oluştu</option>').trigger('change.select2');
                        });
                }
            });
        }

        // Kredi Kartı Görsel Güncelleme
        const kartSahibiInput = document.getElementById('kart_sahibi');
        const kartNoInput = document.getElementById('kart_no');
        const kartSktInput = document.getElementById('kart_skt');
        
        const visualCardOwner = document.getElementById('visualCardOwner');
        const visualCardNo = document.getElementById('visualCardNo');
        const visualCardExpiry = document.getElementById('visualCardExpiry');

        if (kartSahibiInput) {
            kartSahibiInput.addEventListener('input', function(e) {
                let val = e.target.value;
                if (visualCardOwner) {
                    visualCardOwner.innerText = val.trim() ? val.toUpperCase() : 'İSİM SOYAD';
                }
            });
        }

        if (kartNoInput) {
            kartNoInput.addEventListener('input', function(e) {
                let val = e.target.value.replace(/\D/g, '');
                let formatted = '';
                
                for(let i = 0; i < val.length; i++) {
                    if(i > 0 && i % 4 === 0) {
                        formatted += ' ';
                    }
                    formatted += val[i];
                }
                
                e.target.value = formatted.substring(0, 19);
                
                let displayVal = formatted;
                let placeholders = '•••• •••• •••• ••••';
                if (visualCardNo) {
                    visualCardNo.innerText = displayVal + placeholders.substring(displayVal.length);
                }
            });
        }

        if (kartSktInput) {
            kartSktInput.addEventListener('input', function(e) {
                let val = e.target.value.replace(/\D/g, '');
                if(val.length > 2) {
                    val = val.substring(0, 2) + '/' + val.substring(2, 4);
                }
                e.target.value = val;
                
                if (visualCardExpiry) {
                    visualCardExpiry.innerText = val.trim() ? val : 'AA/YY';
                }

                const paymentMethods = document.querySelectorAll('input[name="odeme_yontemi"]');
                const cardFields = document.getElementById('card-payment-fields');
                const bankFields = document.getElementById('bank-transfer-fields');
                const cardInputs = cardFields ? cardFields.querySelectorAll('input[name^="kart_"]') : [];
                const bankReference = document.getElementById('havale_referans');
                function updatePaymentMethod() {
                    const method = document.querySelector('input[name="odeme_yontemi"]:checked')?.value
                        || document.querySelector('input[name="odeme_yontemi"]')?.value;
                    const isCard = method === 'iyzico';
                    cardFields?.classList.toggle('hidden', !isCard);
                    bankFields?.classList.toggle('hidden', isCard);
                    cardInputs.forEach((input) => { input.required = isCard; });
                    if (bankReference) bankReference.required = !isCard;
                }
                paymentMethods.forEach((input) => input.addEventListener('change', updatePaymentMethod));
                updatePaymentMethod();
            });
        }
    });
</script>
@endsection
