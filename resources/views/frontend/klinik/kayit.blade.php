@extends('frontend.layouts.app')

@section('baslik', 'Klinik Kayıt & Ödeme - Randevu Ajandam')

@section('head')
    <!-- jQuery and Select2 CSS & JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        /* Custom Premium Multiselect Widget Styles */
        .multiselect-dropdown-open {
            border-color: #C96A2B !important;
            box-shadow: 0 0 0 4px rgba(201, 106, 43, 0.1) !important;
        }
        .multiselect-badge {
            animation: badge-appear 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes badge-appear {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        /* Dropdown transition animation */
        #multiselect-dropdown {
            opacity: 0;
            transform: translateY(-8px) scale(0.98);
            transition: opacity 0.18s cubic-bezier(0.16, 1, 0.3, 1), transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
        }
        #multiselect-dropdown.show {
            display: flex !important;
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }
        /* Password Requirements Popover transition */
        #password-requirements {
            opacity: 0;
            transform: translateY(-8px) scale(0.98);
            transition: opacity 0.18s cubic-bezier(0.16, 1, 0.3, 1), transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
        }
        #password-requirements.show {
            display: block !important;
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

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
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02) !important;
            z-index: 9999 !important;
            overflow: hidden !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #E5E7EB !important;
            border-radius: 0.5rem !important;
            padding: 6px 10px !important;
            font-size: 0.75rem !important;
            outline: none !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #C96A2B !important;
        }
        .select2-container--default .select2-results__option {
            padding: 8px 12px !important;
            font-size: 0.75rem !important;
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

        /* Step navigation transition effects */
        .wizard-step {
            transition: opacity 0.25s ease, transform 0.25s ease;
        }
        .wizard-step.hidden {
            display: none !important;
            opacity: 0;
            transform: translateY(10px);
        }

        /* Progress Circles */
        .step-circle {
            transition: all 0.3s ease;
        }
        .step-circle.active {
            border-color: #C96A2B !important;
            background-color: #C96A2B !important;
            color: #FFFFFF !important;
            box-shadow: 0 0 0 4px rgba(201, 106, 43, 0.15) !important;
        }
        .step-circle.completed {
            border-color: #10B981 !important;
            background-color: #10B981 !important;
            color: #FFFFFF !important;
        }
    </style>
@endsection

@section('icerik')
<section class="fe-page bg-[#FAFAFA] select-none">
    <div class="fe-container">
        <!-- Top Action Header -->
        <div class="mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
            <div>
                <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                    <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                    Klinik Üyelik Kaydı
                </h2>
                <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Randevu Ajandam Klinik Yönetim paneline katılmak için bilgilerinizi girin.</p>
            </div>
            <div>
                <a href="{{ route('frontend.paketler') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] transition-all shadow-sm">
                    ← Paketlere Dön
                </a>
            </div>
        </div>

        <!-- Progress Tracker Bar -->
        <div class="max-w-3xl mx-auto mb-12 relative flex items-center justify-between select-none">
            <!-- Progress Line Background -->
            <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-0.5 bg-gray-200 z-0"></div>
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-0.5 bg-[#C96A2B] z-0 transition-all duration-300" id="progressBar" style="width: 0%;"></div>

            <!-- Step 1 -->
            <div class="relative z-10 flex flex-col items-center gap-2">
                <div class="step-circle active w-10 h-10 rounded-full border-2 border-gray-300 bg-white text-gray-500 flex items-center justify-center font-bold text-xs font-display" id="circleStep1">1</div>
                <span class="text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display bg-[#FAFAFA] px-2">Klinik Bilgileri</span>
            </div>
            <!-- Step 2 -->
            <div class="relative z-10 flex flex-col items-center gap-2">
                <div class="step-circle w-10 h-10 rounded-full border-2 border-gray-300 bg-white text-gray-500 flex items-center justify-center font-bold text-xs font-display" id="circleStep2">2</div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider font-display bg-[#FAFAFA] px-2">Yönetici Hekim</span>
            </div>
            <!-- Step 3 -->
            <div class="relative z-10 flex flex-col items-center gap-2">
                <div class="step-circle w-10 h-10 rounded-full border-2 border-gray-300 bg-white text-gray-500 flex items-center justify-center font-bold text-xs font-display" id="circleStep3">3</div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider font-display bg-[#FAFAFA] px-2">Güvenli Ödeme</span>
            </div>
        </div>

        <form action="{{ route('frontend.hekim.klinik.kayit.post') }}" method="POST" id="wizardForm" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
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
                            <span class="text-[10px] text-[#C96A2B] uppercase font-bold tracking-wider mt-0.5 block">
                                Klinik Yönetim Planı
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
                            <span class="text-[9px] text-[#6B7280] font-bold block mt-0.5">
                                {{ $periyot === 'aylik' ? '/ aylık abonelik' : '/ yıllık abonelik' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Paket Özellikleri -->
                <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm">
                    <h4 class="text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display mb-4">Dahil Olan Özellikler</h4>
                    <ul class="space-y-3">
                        @if($secilenPaket->max_doktor_sayisi)
                            <li class="flex items-start gap-2.5 text-xs text-[#4B5563]">
                                <div class="w-4 h-4 rounded-full bg-orange-50 border border-orange-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="text-[9px] font-bold text-[#C96A2B]">+</span>
                                </div>
                                <span class="leading-tight font-semibold">{{ $secilenPaket->max_doktor_sayisi }} Hekime Kadar Davet</span>
                            </li>
                        @endif
                        @if($secilenPaket->max_personel_sayisi)
                            <li class="flex items-start gap-2.5 text-xs text-[#4B5563]">
                                <div class="w-4 h-4 rounded-full bg-orange-50 border border-orange-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="text-[9px] font-bold text-[#C96A2B]">+</span>
                                </div>
                                <span class="leading-tight font-semibold">{{ $secilenPaket->max_personel_sayisi }} Sekreter / Personel Ekleme</span>
                            </li>
                        @endif
                        @if(is_array($secilenPaket->ozellikler))
                            @foreach($secilenPaket->ozellikler as $ozellik)
                                <li class="flex items-start gap-2.5 text-xs text-[#4B5563]">
                                    <div class="w-4 h-4 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                        </svg>
                                    </div>
                                    <span class="leading-tight">{{ $ozellik }}</span>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Sağ Sütun: Form Adımları -->
            <div class="lg:col-span-8 bg-white border border-[#E5E7EB] rounded-3xl p-6 sm:p-8 shadow-sm min-h-[580px] flex flex-col justify-between">
                
                <!-- STEP 1: Klinik Bilgileri -->
                <div class="wizard-step flex-grow" id="step1">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold font-display text-[#111827]">Klinik Bilgileri</h3>
                        <p class="text-xs text-[#6B7280] mt-1.5">Kliniğinize ait genel iletişim ve vergi bilgilerini girin.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label for="klinik_adi" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Klinik Adı *</label>
                            <input type="text" name="klinik_adi" id="klinik_adi" value="{{ old('klinik_adi') }}" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="Örn: Sağlık Plus Polikliniği">
                            @error('klinik_adi') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="telefon" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Klinik Telefonu *</label>
                            <input type="text" name="telefon" id="telefon" value="{{ old('telefon') }}" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="Örn: 0 (212) 123 45 67">
                            @error('telefon') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="e_posta" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Klinik E-posta</label>
                            <input type="email" name="e_posta" id="e_posta" value="{{ old('e_posta') }}" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="Örn: info@saglikplus.com">
                            @error('e_posta') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="il_id" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">İl *</label>
                            <select name="il_id" id="il_id" class="w-full select2-select">
                                <option value="">İl Seçin</option>
                                @foreach($iller as $il)
                                    <option value="{{ $il->id }}" {{ old('il_id') == $il->id ? 'selected' : '' }}>{{ $il->ad }}</option>
                                @endforeach
                            </select>
                            @error('il_id') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="ilce_id" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">İlçe *</label>
                            <select name="ilce_id" id="ilce_id" class="w-full select2-select" disabled>
                                <option value="">Önce İl Seçin</option>
                            </select>
                            @error('ilce_id') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="adres" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Açık Adres *</label>
                            <textarea name="adres" id="adres" rows="3" 
                                      class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium resize-none"
                                      placeholder="Kliniğinizin adresi...">{{ old('adres') }}</textarea>
                            @error('adres') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="vergi_no" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Vergi Numarası</label>
                            <input type="text" name="vergi_no" id="vergi_no" value="{{ old('vergi_no') }}" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="10 Haneli Vergi No">
                            @error('vergi_no') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="vergi_dairesi" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Vergi Dairesi</label>
                            <input type="text" name="vergi_dairesi" id="vergi_dairesi" value="{{ old('vergi_dairesi') }}" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="Vergi Dairesi Adı">
                            @error('vergi_dairesi') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Yönetici Hekim (Sahip) Bilgileri -->
                <div class="wizard-step hidden flex-grow" id="step2">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold font-display text-[#111827]">Klinik Yöneticisi Hekim Bilgileri</h3>
                        <p class="text-xs text-[#6B7280] mt-1.5">Kliniği yönetecek ve sistemi ilk kullanacak hekimin bilgilerini girin.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="ad_soyad" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Ad Soyad *</label>
                            <input type="text" name="ad_soyad" id="ad_soyad" value="{{ old('ad_soyad') }}" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="Örn: Dr. Ahmet Yılmaz">
                            @error('ad_soyad') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="doktor_eposta" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Hekim E-posta *</label>
                            <input type="email" name="doktor_eposta" id="doktor_eposta" value="{{ old('doktor_eposta') }}" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="Örn: ahmet@saglikplus.com">
                            @error('doktor_eposta') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="relative">
                            <label for="sifre" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Şifre *</label>
                            <input type="password" name="sifre" id="sifre" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="••••••••" onfocus="showPasswordRequirements()" onblur="hidePasswordRequirements()" onkeyup="checkPasswordStrength(this.value)">
                            @error('sifre') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror

                            <!-- Password Requirements Popover -->
                            <div id="password-requirements" class="absolute left-0 right-0 top-[75px] bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-xl z-50 hidden">
                                <span class="text-[10px] font-extrabold text-[#111827] uppercase tracking-wider font-display block mb-2.5">Şifre Güvenlik Kriterleri</span>
                                <ul class="space-y-2">
                                    <li class="flex items-center gap-2 text-[10px] font-semibold text-gray-400" id="req-length">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 flex-shrink-0" id="bullet-length"></span> En az 8 Karakter
                                    </li>
                                    <li class="flex items-center gap-2 text-[10px] font-semibold text-gray-400" id="req-uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 flex-shrink-0" id="bullet-uppercase"></span> En az bir büyük harf
                                    </li>
                                    <li class="flex items-center gap-2 text-[10px] font-semibold text-gray-400" id="req-lowercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 flex-shrink-0" id="bullet-lowercase"></span> En az bir küçük harf
                                    </li>
                                    <li class="flex items-center gap-2 text-[10px] font-semibold text-gray-400" id="req-number">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 flex-shrink-0" id="bullet-number"></span> En az bir sayı
                                    </li>
                                    <li class="flex items-center gap-2 text-[10px] font-semibold text-gray-400" id="req-special">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 flex-shrink-0" id="bullet-special"></span> En az bir özel karakter (@,!,#,$,% vb.)
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div>
                            <label for="sifre_confirmation" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Şifre Tekrarı *</label>
                            <input type="password" name="sifre_confirmation" id="sifre_confirmation" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="••••••••">
                            @error('sifre_confirmation') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="doktor_telefon" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Hekim Telefonu *</label>
                            <input type="text" name="doktor_telefon" id="doktor_telefon" value="{{ old('doktor_telefon') }}" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="Örn: 0 (555) 123 45 67">
                            @error('doktor_telefon') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="unvan" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Mesleki Ünvan *</label>
                            <select name="unvan" id="unvan" class="w-full select2-select">
                                <option value="">Ünvan Seçin</option>
                                @foreach($unvanlar as $unvan)
                                    <option value="{{ $unvan->ad }}" {{ old('unvan') == $unvan->ad ? 'selected' : '' }}>{{ $unvan->ad }}</option>
                                @endforeach
                            </select>
                            @error('unvan') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2 relative">
                            <label class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Uzmanlık Alanları / Branşlar *</label>
                            
                            <!-- Custom Multiple Select Dropdown Trigger -->
                            <div class="w-full bg-slate-50 border border-gray-200 rounded-xl p-3 flex flex-wrap gap-1.5 items-center justify-between cursor-pointer transition-all hover:bg-white focus-within:border-[#C96A2B] min-h-[42px] select-none"
                                 id="multiselect-trigger" onclick="toggleMultiselectDropdown(event)">
                                <div class="flex flex-wrap gap-1.5" id="multiselect-badges-container">
                                    <span class="text-xs text-gray-400 font-normal">Branş Seçin (En az 1 adet)</span>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 pointer-events-none transition-transform" id="multiselect-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path>
                                </svg>
                            </div>
                            @error('branslar') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror

                            <!-- Custom Multiple Select Dropdown -->
                            <div id="multiselect-dropdown" class="absolute left-0 right-0 top-[75px] bg-white border border-[#E5E7EB] rounded-xl shadow-xl flex-col z-40 hidden max-h-[220px] overflow-y-auto p-3.5 space-y-2.5">
                                <div class="relative mb-1">
                                    <input type="text" id="multiselect-search" class="w-full text-xs bg-slate-50 border border-gray-200 rounded-lg p-2.5 outline-none focus:border-[#C96A2B] focus:bg-white" 
                                           placeholder="Branş ara..." onkeyup="filterMultiselectOptions(this.value)">
                                </div>
                                @foreach($branslar as $brans)
                                    <label class="multiselect-option flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 cursor-pointer text-xs font-semibold text-[#4B5563] transition-colors" data-brans-id="{{ $brans->id }}" data-brans-name="{{ $brans->ad }}">
                                        <span>{{ $brans->ad }}</span>
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" name="branslar[]" value="{{ $brans->id }}" class="sr-only peer brans-checkbox" 
                                                   {{ is_array(old('branslar')) && in_array($brans->id, old('branslar')) ? 'checked' : '' }}
                                                   onchange="handleBransCheckboxChange(this, '{{ $brans->ad }}')">
                                            <div class="w-4 h-4 rounded border border-gray-300 flex items-center justify-center peer-checked:border-[#C96A2B] peer-checked:bg-[#C96A2B] transition-all">
                                                <svg class="w-2.5 h-2.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Güvenli Ödeme -->
                <div class="wizard-step hidden flex-grow" id="step3">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold font-display text-[#111827]">Güvenli Ödeme Bilgileri</h3>
                        <p class="text-xs text-[#6B7280] mt-1.5">iyzico altyapısıyla güvenli kredi kartı ödemenizi tamamlayın.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div class="sm:col-span-3">
                            <label for="kart_sahibi" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Kart Üzerindeki İsim *</label>
                            <input type="text" name="kart_sahibi" id="kart_sahibi" value="{{ old('kart_sahibi') }}" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="Örn: Ahmet Yılmaz">
                            @error('kart_sahibi') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-3 relative">
                            <label for="kart_no" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Kredi Kartı Numarası *</label>
                            <input type="text" name="kart_no" id="kart_no" value="{{ old('kart_no') }}" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="0000 0000 0000 0000" maxlength="19">
                            @error('kart_no') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="kart_skt" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Son Kullanma Tarihi *</label>
                            <input type="text" name="kart_skt" id="kart_skt" value="{{ old('kart_skt') }}" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="AA/YY" maxlength="5">
                            @error('kart_skt') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="kart_cvv" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">CVV / Güvenlik Kodu *</label>
                            <input type="password" name="kart_cvv" id="kart_cvv" 
                                   class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                                   placeholder="***" maxlength="3">
                            @error('kart_cvv') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Alt Butonlar (Wizard Navigasyon) -->
                <div class="mt-8 pt-6 border-t border-[#E5E7EB] flex items-center justify-between">
                    <button type="button" class="px-5 py-3 rounded-xl border border-[#E5E7EB] text-xs font-bold text-[#6B7280] hover:bg-slate-50 transition-all uppercase tracking-wider font-display disabled:opacity-40 disabled:pointer-events-none" 
                            id="btnBack" onclick="prevStep()" disabled>
                        Geri
                    </button>
                    
                    <button type="button" class="px-7 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-xs font-bold text-white shadow-sm transition-all uppercase tracking-wider font-display" 
                            id="btnNext" onclick="nextStep()">
                        Devam Et
                    </button>
                </div>

            </div>
        </form>
    </div>
</section>

<!-- Page-specific scripts -->
<script>
    let currentWizardStep = 1;
    const totalWizardSteps = 3;

    $(document).ready(function() {
        // Initialize Select2 dropdowns
        $('.select2-select').select2({
            placeholder: function() {
                return $(this).data('placeholder');
            },
            allowClear: false,
            width: '100%'
        });

        // AJAX İlceler loading based on selected İl
        $('#il_id').on('change', function() {
            const ilId = $(this).val();
            const ilceSelect = $('#ilce_id');
            
            ilceSelect.html('<option value="">Yükleniyor...</option>').prop('disabled', true);
            
            if (!ilId) {
                ilceSelect.html('<option value="">Önce İl Seçin</option>').prop('disabled', true);
                return;
            }
            
            $.ajax({
                url: `/iller/${ilId}/ilceler`,
                type: 'GET',
                success: function(response) {
                    let options = '<option value="">İlçe Seçin</option>';
                    response.forEach(function(ilce) {
                        options += `<option value="${ilce.ad}">${ilce.ad}</option>`;
                    });
                    ilceSelect.html(options).prop('disabled', false);
                    ilceSelect.select2('destroy').select2({ width: '100%' });
                },
                error: function() {
                    ilceSelect.html('<option value="">Hata Oluştu, Tekrar Deneyin</option>').prop('disabled', false);
                }
            });
        });

        // Initialize multiple select checkbox options if any are pre-checked (old input)
        document.querySelectorAll('.brans-checkbox').forEach(checkbox => {
            if (checkbox.checked) {
                handleBransCheckboxChange(checkbox, checkbox.closest('.multiselect-option').getAttribute('data-brans-name'));
            }
        });

        // Format Credit Card input fields
        $('#kart_no').on('input', function() {
            let val = $(this).val().replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let matches = val.match(/\d{4,16}/g);
            let match = (matches && matches[0]) || '';
            let parts = [];

            for (let i = 0, len = match.length; i < len; i += 4) {
                parts.push(match.substring(i, i + 4));
            }

            if (parts.length > 0) {
                $(this).val(parts.join(' '));
            } else {
                $(this).val(val);
            }
        });

        $('#kart_skt').on('input', function() {
            let val = $(this).val().replace(/\D/g, '');
            if (val.length > 2) {
                $(this).val(val.substring(0, 2) + '/' + val.substring(2, 4));
            } else {
                $(this).val(val);
            }
        });

        // Format phone inputs
        const formatPhone = function(elId) {
            $(elId).on('input', function() {
                let val = $(this).val().replace(/\D/g, '');
                if (val.startsWith('0')) {
                    val = val.substring(1);
                }
                let formatted = '';
                if (val.length > 0) {
                    formatted += '0 (' + val.substring(0, 3);
                }
                if (val.length > 3) {
                    formatted += ') ' + val.substring(3, 6);
                }
                if (val.length > 6) {
                    formatted += ' ' + val.substring(6, 8);
                }
                if (val.length > 8) {
                    formatted += ' ' + val.substring(8, 10);
                }
                $(this).val(formatted);
            });
        };

        formatPhone('#telefon');
        formatPhone('#doktor_telefon');
    });

    // Close multiple select dropdown if clicked outside
    document.addEventListener('click', function(event) {
        const trigger = document.getElementById('multiselect-trigger');
        const dropdown = document.getElementById('multiselect-dropdown');
        if (trigger && dropdown && !trigger.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
            dropdown.classList.remove('show');
            trigger.classList.remove('multiselect-dropdown-open');
            document.getElementById('multiselect-arrow').style.transform = 'rotate(0deg)';
        }
    });

    // Toggle Multiselect Dropdown
    function toggleMultiselectDropdown(event) {
        event.stopPropagation();
        const trigger = document.getElementById('multiselect-trigger');
        const dropdown = document.getElementById('multiselect-dropdown');
        const arrow = document.getElementById('multiselect-arrow');

        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('hidden');
            setTimeout(() => dropdown.classList.add('show'), 5);
            trigger.classList.add('multiselect-dropdown-open');
            arrow.style.transform = 'rotate(180deg)';
        } else {
            dropdown.classList.add('hidden');
            dropdown.classList.remove('show');
            trigger.classList.remove('multiselect-dropdown-open');
            arrow.style.transform = 'rotate(0deg)';
        }
    }

    // Filter Multiselect Dropdown options
    function filterMultiselectOptions(query) {
        query = query.toLowerCase();
        document.querySelectorAll('.multiselect-option').forEach(option => {
            const name = option.getAttribute('data-brans-name').toLowerCase();
            if (name.includes(query)) {
                option.style.display = 'flex';
            } else {
                option.style.display = 'none';
            }
        });
    }

    // Handle check/uncheck changes in multiple select options
    const selectedBranches = new Map();
    function handleBransCheckboxChange(checkbox, name) {
        const bransId = checkbox.value;
        const container = document.getElementById('multiselect-badges-container');
        
        if (checkbox.checked) {
            selectedBranches.set(bransId, name);
            checkbox.closest('.multiselect-option').classList.add('bg-orange-50/50');
        } else {
            selectedBranches.delete(bransId);
            checkbox.closest('.multiselect-option').classList.remove('bg-orange-50/50');
        }

        // Re-render badges
        if (selectedBranches.size === 0) {
            container.innerHTML = '<span class="text-xs text-gray-400 font-normal">Branş Seçin (En az 1 adet)</span>';
        } else {
            let badgesHtml = '';
            selectedBranches.forEach((val, key) => {
                badgesHtml += `
                    <span class="multiselect-badge inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-orange-50 border border-[#E7B58A]/30 text-xs font-bold text-[#C96A2B]">
                        ${val}
                        <button type="button" class="text-orange-400 hover:text-[#C96A2B] focus:outline-none" onclick="removeBransBadge(event, '${key}')">×</button>
                    </span>
                `;
            });
            container.innerHTML = badgesHtml;
        }
    }

    // Remove badge on close button click
    function removeBransBadge(event, key) {
        event.stopPropagation();
        const checkbox = document.querySelector(`.brans-checkbox[value="${key}"]`);
        if (checkbox) {
            checkbox.checked = false;
            handleBransCheckboxChange(checkbox, '');
        }
    }

    // Password requirement popover controls
    function showPasswordRequirements() {
        document.getElementById('password-requirements').classList.remove('hidden');
        setTimeout(() => document.getElementById('password-requirements').classList.add('show'), 5);
    }
    function hidePasswordRequirements() {
        document.getElementById('password-requirements').classList.add('hidden');
        document.getElementById('password-requirements').classList.remove('show');
    }

    // Verify strength on keystroke
    function checkPasswordStrength(password) {
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>_\-#\[\]\\\/]/.test(password)
        };

        const updateStatus = (id, isValid) => {
            const el = document.getElementById(`req-${id}`);
            const bullet = document.getElementById(`bullet-${id}`);
            if (isValid) {
                el.classList.remove('text-gray-400');
                el.classList.add('text-emerald-600');
                bullet.classList.remove('bg-gray-300');
                bullet.classList.add('bg-emerald-500');
            } else {
                el.classList.add('text-gray-400');
                el.classList.remove('text-emerald-600');
                bullet.classList.add('bg-gray-300');
                bullet.classList.remove('bg-emerald-500');
            }
        };

        updateStatus('length', checks.length);
        updateStatus('uppercase', checks.uppercase);
        updateStatus('lowercase', checks.lowercase);
        updateStatus('number', checks.number);
        updateStatus('special', checks.special);
    }

    // Wizard Step Navigation Controls
    function nextStep() {
        if (currentWizardStep === 1) {
            // Validation step 1 (Klinik Bilgileri)
            if (!validateStep1()) return;
        } else if (currentWizardStep === 2) {
            // Validation step 2 (Hekim Bilgileri)
            if (!validateStep2()) return;
        } else if (currentWizardStep === 3) {
            // Submit form
            document.getElementById('wizardForm').submit();
            return;
        }

        // Hide current step, show next
        $(`#step${currentWizardStep}`).addClass('hidden');
        
        // Mark current progress step circle completed
        $(`#circleStep${currentWizardStep}`).removeClass('active').addClass('completed').html('✓');
        
        currentWizardStep++;
        
        // Show next step
        $(`#step${currentWizardStep}`).removeClass('hidden');
        $(`#circleStep${currentWizardStep}`).addClass('active');

        // Update progress bar percentage
        const progressPercentage = ((currentWizardStep - 1) / (totalWizardSteps - 1)) * 100;
        document.getElementById('progressBar').style.width = progressPercentage + '%';

        // Adjust navigation buttons
        document.getElementById('btnBack').disabled = false;
        
        if (currentWizardStep === totalWizardSteps) {
            document.getElementById('btnNext').innerHTML = 'Ödeme Yap & Kaydı Tamamla';
            document.getElementById('btnNext').classList.remove('bg-[#C96A2B]', 'hover:bg-[#B55A20]');
            document.getElementById('btnNext').classList.add('bg-emerald-600', 'hover:bg-emerald-700');
        }
    }

    function prevStep() {
        if (currentWizardStep === 1) return;

        // Hide current step, show previous
        $(`#step${currentWizardStep}`).addClass('hidden');
        $(`#circleStep${currentWizardStep}`).removeClass('active');

        if (currentWizardStep === totalWizardSteps) {
            document.getElementById('btnNext').innerHTML = 'Devam Et';
            document.getElementById('btnNext').classList.add('bg-[#C96A2B]', 'hover:bg-[#B55A20]');
            document.getElementById('btnNext').classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
        }

        currentWizardStep--;

        $(`#step${currentWizardStep}`).removeClass('hidden');
        $(`#circleStep${currentWizardStep}`).addClass('active').removeClass('completed').html(currentWizardStep);

        const progressPercentage = ((currentWizardStep - 1) / (totalWizardSteps - 1)) * 100;
        document.getElementById('progressBar').style.width = progressPercentage + '%';

        if (currentWizardStep === 1) {
            document.getElementById('btnBack').disabled = true;
        }
    }

    // Step 1 Validation Helper
    function validateStep1() {
        let isValid = true;
        const clearErrors = () => $('.wizard-step#step1 .text-red-500').remove();
        const addError = (selector, msg) => {
            $(selector).after(`<span class="text-[10px] text-red-500 font-semibold mt-1 block error-temp">${msg}</span>`);
            isValid = false;
        };

        clearErrors();

        const name = $('#klinik_adi').val().trim();
        const phone = $('#telefon').val().trim();
        const address = $('#adres').val().trim();
        const city = $('#il_id').val();
        const district = $('#ilce_id').val();

        if (!name) addError('#klinik_adi', 'Klinik adı alanı zorunludur.');
        if (!phone || phone.length < 17) addError('#telefon', 'Geçerli bir klinik telefon numarası girin.');
        if (!address) addError('#adres', 'Klinik adresi zorunludur.');
        if (!city) addError('#il_id', 'Hizmet verilen il seçimi zorunludur.');
        if (!district) addError('#ilce_id', 'Hizmet verilen ilçe seçimi zorunludur.');

        return isValid;
    }

    // Step 2 Validation Helper
    function validateStep2() {
        let isValid = true;
        const clearErrors = () => $('.wizard-step#step2 .text-red-500').remove();
        const addError = (selector, msg) => {
            $(selector).after(`<span class="text-[10px] text-red-500 font-semibold mt-1 block error-temp">${msg}</span>`);
            isValid = false;
        };

        clearErrors();

        const name = $('#ad_soyad').val().trim();
        const email = $('#doktor_eposta').val().trim();
        const password = $('#sifre').val();
        const confirmation = $('#sifre_confirmation').val();
        const phone = $('#doktor_telefon').val().trim();
        const title = $('#unvan').val();
        const branchesCount = selectedBranches.size;

        if (!name) addError('#ad_soyad', 'Ad Soyad alanı zorunludur.');
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailRegex.test(email)) addError('#doktor_eposta', 'Geçerli bir e-posta adresi girin.');
        
        // Passwords checks
        if (!password) {
            addError('#sifre', 'Şifre alanı zorunludur.');
        } else {
            const hasLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>_\-#\[\]\\\/]/.test(password);
            
            if (!hasLength || !hasUpper || !hasLower || !hasNumber || !hasSpecial) {
                addError('#sifre', 'Şifreniz güvenlik kriterlerini karşılamıyor.');
            }
        }

        if (password !== confirmation) addError('#sifre_confirmation', 'Şifre tekrarı uyuşmuyor.');
        if (!phone || phone.length < 17) addError('#doktor_telefon', 'Geçerli bir telefon numarası girin.');
        if (!title) addError('#unvan', 'Ünvan seçimi zorunludur.');
        if (branchesCount === 0) addError('#multiselect-trigger', 'En az bir uzmanlık alanı / branş seçmelisiniz.');

        return isValid;
    }
</script>
@endsection
