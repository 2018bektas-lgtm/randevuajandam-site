@extends('frontend.layouts.app')

@section('baslik', 'Hekim Kaydı - Randevu Ajandam')

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
    /* Step indicator dots */
    .step-circle {
        transition: all 0.3s ease;
    }
    .step-circle.active {
        background-color: #C96A2B;
        color: #FFFFFF;
        border-color: #C96A2B;
        box-shadow: 0 0 0 4px rgba(201, 106, 43, 0.15);
    }
    .step-circle.completed {
        background-color: #10B981;
        color: #FFFFFF;
        border-color: #10B981;
    }
    
    /* Wizard step transition */
    .wizard-step {
        transition: opacity 0.25s ease, transform 0.25s ease;
    }
    .wizard-step.hidden-step {
        display: none;
        opacity: 0;
        transform: translateX(10px);
    }
</style>

<section class="fe-page relative bg-[#FAFAFA] overflow-hidden">
    <!-- Ambient Lights -->
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-[#C96A2B]/3 blur-[120px] pointer-events-none"></div>

    <div class="max-w-4xl mx-auto px-6 relative z-10">
        
        <!-- Top Action Header -->
        <div class="mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
            <div>
                <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                    <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                    Hekim Kaydı
                </h2>
                <p class="text-xs text-[#6B7280] mt-1.5 ml-4">3 adım · yaklaşık 5–10 dk · paket seçili · mezuniyet doğrulanırsa otomatik onay mümkün.</p>
            </div>
            <div>
                <a href="{{ route('frontend.hasta.giris') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] transition-all shadow-sm">
                    Giriş Yap
                </a>
            </div>
        </div>

        @if(!empty($secilenPaket))
            <div class="mb-6 rounded-2xl border border-[#E7B58A]/50 bg-[#FFF7ED] px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-[#C96A2B]">Seçilen paket</p>
                    <p class="text-sm font-bold text-[#111827] font-display mt-0.5">{{ $secilenPaket->ad }}</p>
                    <p class="text-[11px] text-slate-600 mt-1">
                        {{ ($periyot ?? 'aylik') === 'yillik' ? 'Yıllık' : 'Aylık' }}
                        · Fiyatlara KDV dahildir
                    </p>
                </div>
                <a href="{{ route('frontend.paketler') }}" class="text-xs font-bold text-[#C96A2B] underline whitespace-nowrap">Paketi değiştir</a>
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

        <!-- Progress Tracker Bar -->
        <div class="max-w-md mx-auto mb-12 relative flex items-center justify-between select-none">
            <!-- Progress Line Background -->
            <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-0.5 bg-gray-200 z-0"></div>
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-0.5 bg-[#C96A2B] z-0 transition-all duration-300" id="progressBar" style="width: 0%;"></div>

            <!-- Step 1 -->
            <div class="relative z-10 flex flex-col items-center gap-2">
                <div class="step-circle active w-10 h-10 rounded-full border-2 border-gray-300 bg-white text-gray-500 flex items-center justify-center font-bold text-xs font-display" id="circleStep1">1</div>
                <span class="text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display bg-[#FAFAFA] px-2">Hesap</span>
            </div>
            <!-- Step 2 -->
            <div class="relative z-10 flex flex-col items-center gap-2">
                <div class="step-circle w-10 h-10 rounded-full border-2 border-gray-300 bg-white text-gray-500 flex items-center justify-center font-bold text-xs font-display" id="circleStep2">2</div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider font-display bg-[#FAFAFA] px-2">Mezuniyet</span>
            </div>
            <!-- Step 3 -->
            <div class="relative z-10 flex flex-col items-center gap-2">
                <div class="step-circle w-10 h-10 rounded-full border-2 border-gray-300 bg-white text-gray-500 flex items-center justify-center font-bold text-xs font-display" id="circleStep3">3</div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider font-display bg-[#FAFAFA] px-2">Meslek</span>
            </div>
        </div>

        <form action="{{ route('frontend.hekim.kayit.post') }}" method="POST" id="wizardForm" enctype="multipart/form-data" class="bg-white border border-[#E5E7EB] rounded-3xl p-6 sm:p-8 shadow-sm">
            @csrf
            <input type="hidden" name="paket_id" value="{{ old('paket_id', $secilenPaket->id ?? '') }}">
            <input type="hidden" name="odeme_periyodu" value="{{ old('odeme_periyodu', $periyot ?? 'aylik') }}">
            <input type="hidden" name="referans_kodu" value="{{ old('referans_kodu', request('ref', session('ra_ref', request()->cookie(config('referans.cookie_name', 'ra_ref'))))) }}">
            @include('frontend.layouts.partials.recaptcha-form', ['formId' => 'wizardForm', 'recaptchaAction' => 'hekim_kayit'])
            
            <!-- ADIM 1: HESAP BİLGİLERİ -->
            <div id="step1" class="wizard-step space-y-6">
                <h3 class="text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display pb-2 border-b border-[#E5E7EB]">
                    1. Kişisel ve Hesap Bilgileri
                </h3>

                <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-[11px] text-emerald-900 leading-relaxed">
                    <strong>Akış:</strong> Hesap → e-Devlet barkodlu mezuniyet belgesi doğrulama → unvan/branş (otomatik) → kayıt.
                    Sağlık/yaşam bilimleri programlarında doğrulama başarılıysa meslek onayı otomatik verilir.
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Ad Soyad -->
                    <div>
                        <label for="ad_soyad" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Ad Soyad</label>
                        <input type="text" name="ad_soyad" id="ad_soyad" value="{{ old('ad_soyad') }}" placeholder="Ahmet Yılmaz" required
                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>

                    <!-- Telefon -->
                    <div>
                        <label for="telefon" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Telefon Numarası</label>
                        <input type="text" name="telefon" id="telefon" value="{{ old('telefon') }}" placeholder="0 (5XX) XXX XX XX" required
                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>
                </div>

                <div>
                    <label for="tc_kimlik_no" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">T.C. Kimlik No</label>
                    <input type="text" name="tc_kimlik_no" id="tc_kimlik_no" value="{{ old('tc_kimlik_no') }}" maxlength="11" inputmode="numeric" pattern="[0-9]{11}" placeholder="11 haneli TC" required
                        class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all font-mono">
                    @error('tc_kimlik_no')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- E-Posta -->
                <div>
                    <label for="e_posta" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">E-Posta Adresi</label>
                    <input type="email" name="e_posta" id="e_posta" value="{{ old('e_posta') }}" placeholder="doktor@eposta.com" required
                        class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                </div>

                <!-- Hizmet Verilen Konum (İl/İlçe) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- İl -->
                    <div>
                        <label for="il" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Hizmet Verilen İl</label>
                        <select name="il" id="il" required class="w-full">
                            <option value="" disabled selected>İl Seçin...</option>
                        </select>
                    </div>

                    <!-- İlçe -->
                    <div>
                        <label for="ilce" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Hizmet Verilen İlçe</label>
                        <select name="ilce" id="ilce" required class="w-full">
                            <option value="" disabled selected>Önce İl Seçin...</option>
                        </select>
                    </div>
                </div>

                <!-- Şifre Grubu -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="sifre" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Şifre</label>
                        <div class="relative">
                            <input type="password" name="sifre" id="sifre" placeholder="••••••••" required
                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                            
                            <!-- Şifre Gereksinimleri Listesi -->
                            <div class="absolute left-0 right-0 top-full mt-2.5 p-4 bg-white border border-[#E5E7EB] rounded-2xl shadow-xl space-y-2 z-45 hidden" id="password-requirements">
                                <!-- Popover Arrow (Caret) -->
                                <div class="absolute -top-1.5 left-6 w-3 h-3 bg-white border-t border-l border-[#E5E7EB] rotate-45 z-0"></div>
                                
                                <p class="text-[10px] font-bold text-[#1F2937] uppercase tracking-wider mb-1.5 font-display relative z-10">Şifre Kuralları</p>
                                
                                <div class="flex items-center gap-2 text-[10px] text-red-500 transition-colors duration-150 relative z-10" id="req-length">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path class="cross-path" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                        <path class="check-path hidden" stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                    </svg>
                                    <span>En az 8 karakter</span>
                                </div>
                                <div class="flex items-center gap-2 text-[10px] text-red-500 transition-colors duration-150 relative z-10" id="req-uppercase">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path class="cross-path" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                        <path class="check-path hidden" stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                    </svg>
                                    <span>En az bir büyük harf (A-Z)</span>
                                </div>
                                <div class="flex items-center gap-2 text-[10px] text-red-500 transition-colors duration-150 relative z-10" id="req-lowercase">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path class="cross-path" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                        <path class="check-path hidden" stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                    </svg>
                                    <span>En az bir küçük harf (a-z)</span>
                                </div>
                                <div class="flex items-center gap-2 text-[10px] text-red-500 transition-colors duration-150 relative z-10" id="req-number">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path class="cross-path" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                        <path class="check-path hidden" stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                    </svg>
                                    <span>En az bir sayı (0-9)</span>
                                </div>
                                <div class="flex items-center gap-2 text-[10px] text-red-500 transition-colors duration-150 relative z-10" id="req-symbol">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path class="cross-path" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                        <path class="check-path hidden" stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                    </svg>
                                    <span>En az bir özel karakter (!, @, #, $, %, vb.)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="sifre_confirmation" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Şifre Tekrarı</label>
                        <input type="password" name="sifre_confirmation" id="sifre_confirmation" placeholder="••••••••" required
                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>
                </div>

                <!-- Şifre Hata Mesajı -->
                <div id="password-error-message" class="hidden mt-2 p-3.5 bg-red-50 border border-red-100 rounded-xl text-xs text-red-600 font-semibold flex items-center gap-2 transition-all duration-200">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                    </svg>
                    <span id="password-error-text">Şifreler uyuşmuyor.</span>
                </div>

                <!-- Navigasyon Butonları -->
                <div class="flex items-center justify-end pt-4 border-t border-[#E5E7EB]">
                    <button type="button" onclick="nextStep(2)"
                            class="px-6 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-150 font-display cursor-pointer select-none">
                        Devam Et (Mezuniyet) →
                    </button>
                </div>
            </div>

            <!-- ADIM 2: MEZUNİYET DOĞRULAMA -->
            <div id="step2" class="wizard-step hidden-step space-y-6">
                <h3 class="text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display pb-2 border-b border-[#E5E7EB]">
                    2. Mezuniyet belgeleriniz
                </h3>

                <div class="rounded-2xl border border-[#E7B58A]/40 bg-[#FFF7ED] px-4 py-3.5 text-[11px] text-[#78350F] leading-relaxed space-y-2">
                    <p class="font-bold text-[#9A3412]">Birden fazla belge yükleyin</p>
                    <ul class="list-disc pl-4 space-y-1.5">
                        <li><strong>Tüm mezuniyet belgelerinizi</strong> yükleyin: lisans, yüksek lisans, uzmanlık, YÖK mezun belgesi vb. (en fazla 8 dosya).</li>
                        <li><strong>Her belge ayrı ayrı</strong> aynı işlemlerden geçer: e-Devlet doğrulama → TC eşleşmesi → ad soyad eşleşmesi → branş/meslek listesi kontrolü.</li>
                        <li>İsterseniz her belge için barkodları da yazın (satır satır veya virgülle). PDF içinde barkod varsa otomatik okunur.</li>
                        <li><strong>En az bir belge</strong> TC + ad + program olarak tam uyarsa otomatik onay verilir; diğer belgeler de kayda eklenir.</li>
                        <li>Hiçbiri tam uymasa bile devam edebilirsiniz → durum <em>«Talebiniz inceleniyor»</em>.</li>
                    </ul>
                    <p class="text-[10px] text-[#92400E] pt-1">
                        <a href="https://www.turkiye.gov.tr/yuksekogretim-mezun-belgesi-sorgulama" target="_blank" rel="noopener" class="font-bold underline text-[#C96A2B]">YÖK mezun belgesi al</a>
                        ·
                        <a href="https://www.turkiye.gov.tr/belge-dogrulama" target="_blank" rel="noopener" class="font-bold underline text-[#C96A2B]">Barkod doğrulama</a>
                    </p>
                </div>

                <div>
                    <label for="edevlet_barkod" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">e-Devlet barkod(lar)</label>
                    <textarea name="edevlet_barkod" id="edevlet_barkod" rows="2" placeholder="Her satıra bir barkod&#10;YOKME…&#10;YOKME…"
                        class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all font-mono uppercase">{{ old('edevlet_barkod') }}</textarea>
                    <p class="mt-1 text-[10px] text-[#9CA3AF]">Birden fazla barkod: satır satır veya virgülle. Sıra, yüklediğiniz dosya sırasıyla eşleştirilir; fazlası ayrı doğrulanır.</p>
                    @error('edevlet_barkod')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mezuniyet_belgeleri" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Mezuniyet belgeleri (çoklu seçim)</label>
                    <input type="file" name="mezuniyet_belgeleri[]" id="mezuniyet_belgeleri" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/*" multiple
                        class="w-full text-xs text-[#4B5563] file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-[#FFF7ED] file:text-[#C96A2B] file:font-bold file:text-[11px]">
                    <p class="mt-1.5 text-[10px] text-[#9CA3AF]">Ctrl/Cmd ile birden fazla dosya seçin · PDF/JPG/PNG · dosya başı en fazla 5 MB · en fazla 8 belge. Her dosya tek tek doğrulanır.</p>
                    <p id="mezuniyetDosyaListesi" class="mt-2 text-[11px] text-slate-600 space-y-0.5"></p>
                    @error('mezuniyet_belgeleri')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                    @error('mezuniyet_belgeleri.*')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                </div>

                <input type="hidden" name="diploma_no" id="diploma_no" value="{{ old('diploma_no') }}">
                {{-- geriye uyum gizli alan --}}
                <input type="hidden" name="meslek_belgesi" id="meslek_belgesi_legacy" value="">

                <button type="button" id="btnMezuniyetDogrula"
                        class="w-full py-3 rounded-xl border-2 border-[#C96A2B] text-[#C96A2B] hover:bg-[#FFF7ED] font-bold text-xs uppercase tracking-wider">
                    Tüm belgeleri doğrula (her biri ayrı)
                </button>
                <p id="mezuniyetDogrulaStatus" class="text-[11px] text-slate-500"></p>

                <div id="mezuniyetSonucListesi" class="space-y-3"></div>
                <div id="mezuniyetSonucOzet" class="hidden rounded-2xl border p-4 text-xs"></div>

                <div class="flex items-center justify-between pt-4 border-t border-[#E5E7EB]">
                    <button type="button" onclick="prevStep(1)"
                            class="px-5 py-3 rounded-xl border border-[#E5E7EB] hover:bg-slate-50 text-[#6B7280] font-bold text-xs uppercase tracking-wider">← Geri</button>
                    <button type="button" id="btnToMeslekStep" onclick="goToMeslekStep()"
                            class="px-6 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider">
                        Devam Et (Meslek) →
                    </button>
                </div>
            </div>

            <!-- ADIM 3: MESLEKİ BİLGİLER -->
            <div id="step3" class="wizard-step hidden-step space-y-6">
                <h3 class="text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display pb-2 border-b border-[#E5E7EB]">
                    3. Mesleki bilgiler (otomatik doldurulabilir)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Unvan -->
                    <div>
                        <label for="unvan" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Mesleki Unvan</label>
                        <select name="unvan" id="unvan" required
                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all cursor-pointer font-semibold">
                            <option value="" disabled selected>Unvan Seçin...</option>
                            @foreach($unvanlar as $u)
                                <option value="{{ $u->ad }}">{{ $u->ad }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Branşlar / Uzmanlık Alanları (Özel Çoklu Seçim Widget) -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Branş / Uzmanlık Alanları</label>
                        
                        <div class="relative w-full" id="custom-multiselect-container">
                            <!-- Input Box (Trigger) -->
                            <div id="multiselect-trigger" class="w-full min-h-[46px] pl-4 pr-10 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus-within:border-[#C96A2B] focus-within:ring-1 focus-within:ring-[#C96A2B] text-xs transition-all flex flex-wrap items-center gap-2 cursor-pointer select-none relative">
                                <span class="text-gray-400 py-1" id="multiselect-placeholder">Branş seçin veya aratın...</span>
                                
                                <!-- Chevron Icon -->
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-[#9CA3AF] transition-transform duration-200" id="multiselect-chevron">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Dropdown Menu -->
                            <div id="multiselect-dropdown" class="absolute left-0 right-0 mt-2 bg-white border border-[#E5E7EB] rounded-2xl shadow-xl z-50 hidden max-h-64 overflow-y-auto flex flex-col p-2">
                                <!-- Search Input Container -->
                                <div class="p-1 border-b border-slate-100 relative">
                                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
                                    </svg>
                                    <input type="text" id="multiselect-search" placeholder="Branş adı ile ara..." 
                                           class="w-full pl-9 pr-3 py-2.5 rounded-xl bg-slate-50 border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] text-xs transition-all">
                                </div>
                                <!-- Options -->
                                <div class="flex-grow overflow-y-auto p-1 space-y-0.5 mt-1" id="multiselect-options">
                                    @foreach($branslar as $brans)
                                        <div class="multiselect-option flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs text-slate-700 hover:bg-slate-50 cursor-pointer select-none transition-colors" 
                                             data-id="{{ $brans->id }}" data-name="{{ $brans->ad }}">
                                            <span>{{ $brans->ad }}</span>
                                            <!-- Check icon -->
                                            <svg class="w-4 h-4 text-[#C96A2B] hidden" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                            </svg>
                                        </div>
                                    @endforeach
                                    <!-- No Results -->
                                    <div id="multiselect-no-results" class="p-4 text-center text-xs text-gray-400 hidden">
                                        Eşleşen branş bulunamadı.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden Fields -->
                            <div id="multiselect-hidden-inputs"></div>
                        </div>
                        <!-- Branş Hata Mesajı -->
                        <div id="brans-error-message" class="hidden mt-2.5 p-3.5 bg-red-50 border border-red-100 rounded-xl text-xs text-red-600 font-semibold flex items-center gap-2 transition-all duration-200">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                            </svg>
                            <span>Lütfen en az bir uzmanlık alanı / branş seçin.</span>
                        </div>
                    </div>
                </div>

                <!-- Mezuniyet / Eğitim Bilgileri (Etiket Sistemi) -->
                <div class="space-y-2">
                    <label class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Mezuniyet / Eğitim Bilgileri (Etiket Sistemi)</label>
                    <div class="flex gap-2">
                        <input type="text" id="mezuniyet_input" placeholder="Örn: Hacettepe Üniversitesi Tıp Fakültesi (2005) (Yazıp Ekle'ye basın)"
                               class="flex-grow px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                        <button type="button" onclick="addMezuniyetTag()"
                                class="px-4 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all select-none">
                            Ekle
                        </button>
                    </div>
                    <div id="mezuniyet_tags_container" class="flex flex-wrap gap-2 p-3 border border-[#E5E7EB] rounded-xl bg-slate-50/50 min-h-[50px]">
                        <!-- Dinamik etiketler buraya gelecektir -->
                    </div>
                    <div id="mezuniyet_hidden_fields"></div>
                </div>

                <!-- Hakkında / Biyografi -->
                <div>
                    <label for="biyografi" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Hakkında / Kısa Özgeçmiş</label>
                    <textarea name="biyografi" id="biyografi" rows="4" placeholder="Hastalarınıza kendinizi kısaca tanıtın (Eğitimler, sertifikalar, ilgi alanları vb.)..."
                        class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">{{ old('biyografi') }}</textarea>
                </div>

                <!-- KVKK / sözleşme -->
                <div class="space-y-3 pt-2 border-t border-[#E5E7EB]">
                    <label class="flex items-start gap-2.5 text-xs text-[#4B5563] cursor-pointer">
                        <input type="checkbox" name="kvkk_onay" value="1" required @checked(old('kvkk_onay'))
                               class="mt-0.5 rounded border-slate-300 text-[#C96A2B] focus:ring-[#C96A2B]">
                        <span>
                            <a href="{{ route('frontend.legal.kvkk') }}" target="_blank" class="text-[#C96A2B] font-bold underline">KVKK Aydınlatma Metni</a>’ni okudum; kimlik ve mezuniyet doğrulaması (e-Devlet barkodlu belge) dahil kişisel verilerimin işlenmesini kabul ediyorum.
                        </span>
                    </label>
                    @error('kvkk_onay')<p class="text-[11px] text-red-600">{{ $message }}</p>@enderror
                    <label class="flex items-start gap-2.5 text-xs text-[#4B5563] cursor-pointer">
                        <input type="checkbox" name="sozlesme_onay" value="1" required @checked(old('sozlesme_onay'))
                               class="mt-0.5 rounded border-slate-300 text-[#C96A2B] focus:ring-[#C96A2B]">
                        <span>
                            <a href="{{ route('frontend.legal.kullanim') }}" target="_blank" class="text-[#C96A2B] font-bold underline">Kullanım Koşulları</a>’nı okudum ve kabul ediyorum.
                        </span>
                    </label>
                    @error('sozlesme_onay')<p class="text-[11px] text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Navigasyon Butonları -->
                <div class="flex items-center justify-between pt-4 border-t border-[#E5E7EB]">
                    <button type="button" onclick="prevStep(2)"
                            class="px-5 py-3 rounded-xl border border-[#E5E7EB] hover:bg-slate-50 text-[#6B7280] font-bold text-xs uppercase tracking-wider transition-all select-none">
                        ← Geri Dön
                    </button>
                    <button type="submit" id="submitBtn"
                            class="px-6 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-150 font-display cursor-pointer select-none">
                        Kaydı Tamamla ✓
                    </button>
                </div>
            </div>

        </form>
    </div>
</section>

<!-- Wizard dynamic steps JS -->
<script>
    let currentActiveStep = 1;

    function nextStep(step) {
        // Simple HTML5 validation for the current step fields
        const currentContainer = document.getElementById('step' + (step - 1));
        const inputs = currentContainer.querySelectorAll('input[required], select[required], textarea[required]');
        
        let isValid = true;
        inputs.forEach(input => {
            // Custom validation for Select2 fields
            if (input.tagName === 'SELECT' && ($(input).attr('id') === 'il' || $(input).attr('id') === 'ilce')) {
                if (!input.value) {
                    $(input).next('.select2-container').find('.select2-selection').css('border-color', '#EF4444');
                    isValid = false;
                } else {
                    $(input).next('.select2-container').find('.select2-selection').css('border-color', '');
                }
            } else {
                if (!input.checkValidity()) {
                    input.reportValidity();
                    isValid = false;
                }
            }
        });

        if (!isValid) return;

        // Custom validation for step 1 -> step 2: password mismatch and complexity
        if (step === 2) {
            const password = document.getElementById('sifre').value;
            const passwordConfirm = document.getElementById('sifre_confirmation').value;
            const errorDiv = document.getElementById('password-error-message');
            const errorText = document.getElementById('password-error-text');

            if (errorDiv) {
                errorDiv.classList.add('hidden');
            }

            // 1. Mismatch check
            if (password !== passwordConfirm) {
                if (errorDiv && errorText) {
                    errorText.innerText = 'Girdiğiniz şifreler birbiriyle uyuşmuyor. Lütfen kontrol edin.';
                    errorDiv.classList.remove('hidden');
                    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    alert('Girdiğiniz şifreler birbiriyle uyuşmuyor. Lütfen kontrol edin.');
                }
                return;
            }

            // 2. Password complexity regex
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>_\-#\[\]\\\/]).{8,}$/;
            if (!passwordRegex.test(password)) {
                if (errorDiv && errorText) {
                    errorText.innerText = 'Şifreniz kurallara uygun olmalıdır. Lütfen yukarıdaki gereksinimleri karşıladığınızdan emin olun.';
                    errorDiv.classList.remove('hidden');
                    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    alert('Güvenliğiniz için şifreniz en az 8 karakter olmalı; en az bir büyük harf, bir küçük harf, bir sayı ve bir özel karakter içermelidir.');
                }
                return;
            }
        }

        // Animate transition
        const currentEl = document.getElementById('step' + currentActiveStep);
        const targetEl = document.getElementById('step' + step);

        currentEl.classList.add('hidden-step');
        setTimeout(() => {
            currentEl.style.display = 'none';
            targetEl.style.display = 'block';
            
            setTimeout(() => {
                targetEl.classList.remove('hidden-step');
            }, 30);
        }, 200);

        // Update progress tracker UI
        currentActiveStep = step;
        updateProgressTracker();
    }

    function prevStep(step) {
        const currentEl = document.getElementById('step' + currentActiveStep);
        const targetEl = document.getElementById('step' + step);

        currentEl.classList.add('hidden-step');
        setTimeout(() => {
            currentEl.style.display = 'none';
            targetEl.style.display = 'block';
            
            setTimeout(() => {
                targetEl.classList.remove('hidden-step');
            }, 30);
        }, 200);

        currentActiveStep = step;
        updateProgressTracker();
    }

    function updateProgressTracker() {
        const circles = [
            document.getElementById('circleStep1'),
            document.getElementById('circleStep2'),
            document.getElementById('circleStep3')
        ].filter(Boolean);
        const progressBar = document.getElementById('progressBar');

        if (currentActiveStep === 1) progressBar.style.width = '0%';
        if (currentActiveStep === 2) progressBar.style.width = '50%';
        if (currentActiveStep === 3) progressBar.style.width = '100%';

        circles.forEach((circle, idx) => {
            const stepNum = idx + 1;
            circle.className = "step-circle w-10 h-10 rounded-full border-2 flex items-center justify-center font-bold text-xs font-display transition-all duration-300";
            
            if (stepNum === currentActiveStep) {
                circle.classList.add('active');
                circle.innerText = stepNum;
            } else if (stepNum < currentActiveStep) {
                circle.classList.add('completed');
                circle.innerHTML = `<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                </svg>`;
            } else {
                circle.classList.add('bg-white', 'text-gray-500', 'border-gray-300');
                circle.innerText = stepNum;
            }
            
            const label = circle.nextElementSibling;
            if (stepNum <= currentActiveStep) {
                label.className = "text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display bg-[#FAFAFA] px-2 transition-colors duration-300";
            } else {
                label.className = "text-[10px] font-bold text-gray-400 uppercase tracking-wider font-display bg-[#FAFAFA] px-2 transition-colors duration-300";
            }
        });
    }

    let mezuniyetVerified = false;

    document.getElementById('mezuniyet_belgeleri')?.addEventListener('change', function () {
        const el = document.getElementById('mezuniyetDosyaListesi');
        if (!el) return;
        const files = this.files || [];
        if (!files.length) {
            el.innerHTML = '';
            return;
        }
        el.innerHTML = Array.from(files).map((f, i) =>
            `<span class="block">• ${i + 1}. ${f.name} <span class="text-slate-400">(${Math.round(f.size/1024)} KB)</span></span>`
        ).join('');
    });

    function goToMeslekStep() {
        if (!mezuniyetVerified) {
            const barkod = (document.getElementById('edevlet_barkod')?.value || '').trim();
            const files = document.getElementById('mezuniyet_belgeleri')?.files;
            if (!barkod && !(files && files.length)) {
                alert('Devam etmeden önce en az bir mezuniyet belgesi yükleyin veya barkod girip “Tüm belgeleri doğrula”ya basın.');
                return;
            }
            if (!confirm('Doğrulama yapılmadan devam ederseniz kaydınız «Talebiniz inceleniyor» durumunda kalır. Yine de devam?')) {
                return;
            }
        }
        nextStep(3);
    }

    function renderOneBelgeCard(p, index) {
        const auto = !!p.auto_onay_uygun;
        const badge = auto
            ? '<span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase">Otomatik onay uygun</span>'
            : '<span class="inline-flex px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[10px] font-extrabold uppercase">İncelenecek</span>';
        const ad = p.ad_soyad_belge || 'Ad okunamadı';
        const program = p.program || '—';
        const detay = [
            p.diploma_no ? 'Dip: ' + p.diploma_no : null,
            p.mezuniyet_tarihi || null,
            p.barkod ? 'Barkod: ' + p.barkod : null
        ].filter(Boolean).join(' · ');
        const dosya = p.dosya_adi ? `<p class="text-[10px] text-slate-500 font-mono">${p.dosya_adi}</p>` : '';
        let kontrollerHtml = '';
        (p.kontroller || []).forEach(k => {
            const icon = k.ok ? '✓' : '!';
            const color = k.ok ? 'text-emerald-600' : 'text-amber-600';
            kontrollerHtml += `<div class="flex gap-1.5 text-[11px]"><span class="${color} font-bold">${icon}</span><span><strong>${k.label || ''}</strong> — ${k.detay || ''}</span></div>`;
        });
        let nedenHtml = '';
        if ((p.nedenler || []).length) {
            nedenHtml = '<ul class="text-[11px] text-slate-600 space-y-0.5 pt-1 border-t border-black/5">' +
                p.nedenler.map(n => `<li>• ${n}</li>`).join('') + '</ul>';
        }
        return `
            <div class="rounded-2xl border p-4 space-y-2 text-xs ${auto ? 'border-emerald-200 bg-emerald-50/70' : 'border-amber-200 bg-amber-50/70'}">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-600">Belge ${index + 1}</p>
                    ${badge}
                </div>
                ${dosya}
                <p class="font-bold text-[#111827]">${ad}</p>
                <p class="text-slate-700">${program}</p>
                <p class="text-slate-500 text-[11px]">${detay}</p>
                <div class="space-y-1">${kontrollerHtml}</div>
                ${nedenHtml}
            </div>`;
    }

    function applyAutoFillFromItems(items) {
        const best = items.find(i => i.auto_onay_uygun) || items[0];
        if (!best) return;
        if (best.diploma_no) {
            const dip = document.getElementById('diploma_no');
            if (dip) dip.value = best.diploma_no;
        }
        if (best.onerilen_unvan) {
            const sel = document.getElementById('unvan');
            if (sel) {
                let found = false;
                for (const opt of sel.options) {
                    if (opt.value === best.onerilen_unvan || (opt.text && opt.text.indexOf(best.onerilen_unvan.replace('.', '')) !== -1)) {
                        sel.value = opt.value;
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    const o = document.createElement('option');
                    o.value = best.onerilen_unvan;
                    o.textContent = best.onerilen_unvan;
                    o.selected = true;
                    sel.appendChild(o);
                }
            }
        }
        items.forEach(p => {
            if (p.onerilen_brans) {
                document.querySelectorAll('.multiselect-option').forEach(el => {
                    const name = (el.dataset.name || '').toLocaleLowerCase('tr-TR');
                    const target = (p.onerilen_brans || '').toLocaleLowerCase('tr-TR');
                    if (target && name.includes(target.slice(0, Math.min(10, target.length)))) {
                        if (!el.classList.contains('selected')) el.click();
                    }
                });
            }
            if (p.mezuniyet_satiri) {
                const input = document.getElementById('mezuniyet_input');
                if (input) {
                    input.value = p.mezuniyet_satiri;
                    if (typeof addMezuniyetTag === 'function') addMezuniyetTag();
                }
            }
        });
    }

    document.getElementById('btnMezuniyetDogrula')?.addEventListener('click', async function () {
        const btn = this;
        const statusEl = document.getElementById('mezuniyetDogrulaStatus');
        const ad = document.getElementById('ad_soyad')?.value || '';
        const tc = document.getElementById('tc_kimlik_no')?.value || '';
        const barkod = document.getElementById('edevlet_barkod')?.value || '';
        const fileInput = document.getElementById('mezuniyet_belgeleri');

        if (!ad || !tc || tc.length !== 11) {
            alert('Önce 1. adımda ad soyad ve 11 haneli T.C. kimlik numaranızı girin.');
            return;
        }
        if (!barkod.trim() && !(fileInput?.files?.length)) {
            alert('En az bir mezuniyet belgesi seçin veya barkod girin.');
            return;
        }

        const fd = new FormData();
        fd.append('ad_soyad', ad);
        fd.append('tc_kimlik_no', tc);
        if (barkod.trim()) {
            fd.append('edevlet_barkod', barkod.trim());
            fd.append('edevlet_barkodlar', barkod.trim());
        }
        if (fileInput?.files?.length) {
            Array.from(fileInput.files).forEach(f => fd.append('mezuniyet_belgeleri[]', f));
        }
        fd.append('_token', document.querySelector('input[name="_token"]')?.value || '');

        btn.disabled = true;
        const n = fileInput?.files?.length || 0;
        if (statusEl) {
            statusEl.textContent = n > 1
                ? `${n} belge tek tek doğrulanıyor… Her belge için e-Devlet sorgusu yapılabilir (1–2 dk sürebilir).`
                : 'Belge doğrulanıyor… 10–30 saniye sürebilir.';
            statusEl.className = 'text-[11px] text-slate-500';
        }

        try {
            const res = await fetch(@json(route('frontend.hekim.kayit.mezuniyet_dogrula')), {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            const data = await res.json();
            if (!res.ok || !data.ok) {
                throw new Error(data.error || data.message || 'Doğrulama başarısız');
            }
            mezuniyetVerified = true;
            const items = data.items || (data.payload ? [data.payload] : []);
            const listEl = document.getElementById('mezuniyetSonucListesi');
            if (listEl) {
                listEl.innerHTML = items.map((p, i) => renderOneBelgeCard(p, i)).join('');
            }
            const ozetEl = document.getElementById('mezuniyetSonucOzet');
            if (ozetEl) {
                ozetEl.classList.remove('hidden');
                const auto = !!data.auto_onay_uygun;
                ozetEl.className = 'rounded-2xl border p-4 text-xs space-y-1 ' + (auto
                    ? 'border-emerald-300 bg-emerald-50'
                    : 'border-amber-300 bg-amber-50');
                ozetEl.innerHTML = `
                    <p class="font-bold text-sm ${auto ? 'text-emerald-900' : 'text-amber-900'}">
                        ${auto ? 'Özet: Otomatik onay uygun' : 'Özet: Talebiniz incelenecek'}
                    </p>
                    <p class="text-slate-700">${data.ozet || ''}</p>
                    <p class="text-[11px] text-slate-600">${data.adet || items.length} belge işlendi · ${data.basarili_adet || 0} belge otomatik onaya uygun</p>
                    ${data.uyari ? `<p class="text-amber-800 text-[11px]">${data.uyari}</p>` : ''}
                    <p class="text-[10px] text-slate-500 pt-1">${auto
                        ? 'Kaydı tamamlayınca ödemeye geçebilirsiniz. Tüm belgeler profilinize işlenir.'
                        : '«Devam Et» ile kaydı tamamlayın. Her belge ayrı incelenir; ekibimiz bilgilendirir.'}</p>
                `;
            }
            applyAutoFillFromItems(items);
            if (statusEl) {
                statusEl.textContent = data.ozet || 'Doğrulama tamam.';
                statusEl.className = 'text-[11px] font-semibold ' + (data.auto_onay_uygun ? 'text-emerald-700' : 'text-amber-800');
            }
        } catch (e) {
            mezuniyetVerified = false;
            if (statusEl) {
                statusEl.textContent = e.message || 'Hata';
                statusEl.className = 'text-[11px] text-red-600 font-semibold';
            }
            alert((e.message || 'Doğrulama başarısız') + '\n\nBelgeleri yükleyip kayda devam edebilirsiniz; talebiniz incelenir.');
        } finally {
            btn.disabled = false;
        }
    });

    // Turkish phone number formatting helper
    function formatTurkishPhoneNumber(value) {
        let numbers = value.replace(/\D/g, '');
        if (numbers.length === 0) return '';
        if (numbers[0] !== '0') numbers = '0' + numbers;
        if (numbers.length > 1 && numbers[1] !== '5') numbers = '0';
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
        return formatted;
    }

    // Input handlers
    document.addEventListener('DOMContentLoaded', function() {
        // Form submit handler to validate step 2 fields
        document.getElementById('wizardForm').addEventListener('submit', function(e) {
            const hiddenInputs = document.querySelectorAll('input[name="branslar[]"]');
            const bransErrorDiv = document.getElementById('brans-error-message');
            if (hiddenInputs.length === 0) {
                e.preventDefault();
                if (bransErrorDiv) {
                    bransErrorDiv.classList.remove('hidden');
                    bransErrorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    alert('Lütfen en az bir uzmanlık alanı / branş seçin.');
                }
            }
        });

        const telefonInput = document.getElementById('telefon');
        if (telefonInput) {
            telefonInput.value = formatTurkishPhoneNumber(telefonInput.value);
            telefonInput.addEventListener('input', function() {
                const start = this.selectionStart;
                const prevLen = this.value.length;
                this.value = formatTurkishPhoneNumber(this.value);
                const diff = this.value.length - prevLen;
                this.setSelectionRange(start + diff, start + diff);
            });
            telefonInput.addEventListener('keydown', function(e) {
                const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
                if (allowedKeys.includes(e.key) || (e.ctrlKey && ['c', 'v', 'a', 'x'].includes(e.key.toLowerCase()))) {
                    return;
                }
                if (!/^[0-9]$/.test(e.key)) {
                    e.preventDefault();
                }
            });
        }

        // Password requirements real-time indicator
        const passwordInput = document.getElementById('sifre');
        const passwordRequirements = document.getElementById('password-requirements');
        const reqLength = document.getElementById('req-length');
        const reqUppercase = document.getElementById('req-uppercase');
        const reqLowercase = document.getElementById('req-lowercase');
        const reqNumber = document.getElementById('req-number');
        const reqSymbol = document.getElementById('req-symbol');

        if (passwordInput && passwordRequirements) {
            passwordInput.addEventListener('focus', function() {
                passwordRequirements.classList.remove('hidden');
                setTimeout(() => {
                    passwordRequirements.classList.add('show');
                }, 10);
            });

            passwordInput.addEventListener('blur', function() {
                passwordRequirements.classList.remove('show');
                setTimeout(() => {
                    if (!passwordRequirements.classList.contains('show')) {
                        passwordRequirements.classList.add('hidden');
                    }
                }, 180);
            });

            passwordInput.addEventListener('input', function() {
                const val = this.value;
                updateRuleIndicator(reqLength, val.length >= 8);
                updateRuleIndicator(reqUppercase, /[A-Z]/.test(val));
                updateRuleIndicator(reqLowercase, /[a-z]/.test(val));
                updateRuleIndicator(reqNumber, /\d/.test(val));
                updateRuleIndicator(reqSymbol, /[!@#$%^&*(),.?":{}|<>_\-#\[\]\\\/]/.test(val));

                const errorDiv = document.getElementById('password-error-message');
                if (errorDiv) {
                    errorDiv.classList.add('hidden');
                }
            });

            const passwordConfirmInput = document.getElementById('sifre_confirmation');
            if (passwordConfirmInput) {
                passwordConfirmInput.addEventListener('input', function() {
                    const errorDiv = document.getElementById('password-error-message');
                    if (errorDiv) {
                        errorDiv.classList.add('hidden');
                    }
                });
            }
        }

        function updateRuleIndicator(element, isMet) {
            if (!element) return;
            const crossPath = element.querySelector('.cross-path');
            const checkPath = element.querySelector('.check-path');

            if (isMet) {
                element.classList.remove('text-red-500');
                element.classList.add('text-emerald-600');
                if (crossPath) crossPath.classList.add('hidden');
                if (checkPath) checkPath.classList.remove('hidden');
            } else {
                element.classList.remove('text-emerald-600');
                element.classList.add('text-red-500');
                if (crossPath) crossPath.classList.remove('hidden');
                if (checkPath) checkPath.classList.add('hidden');
            }
        }

        // Turkish capitalization helper function
        function capitalizeTurkish(str) {
            if (!str) return '';
            return str.split(' ').map(word => {
                if (word.length === 0) return '';
                let firstChar = word.charAt(0);
                let rest = word.slice(1);
                
                if (firstChar === 'i') firstChar = 'İ';
                else if (firstChar === 'ı') firstChar = 'I';
                else firstChar = firstChar.toUpperCase();
                
                rest = rest.replace(/İ/g, 'i')
                           .replace(/I/g, 'ı')
                           .replace(/Ş/g, 'ş')
                           .replace(/Ğ/g, 'ğ')
                           .replace(/Ü/g, 'ü')
                           .replace(/Ö/g, 'ö')
                           .replace(/Ç/g, 'ç')
                           .toLowerCase();
                           
                return firstChar + rest;
            }).join(' ');
        }

        // Initialize Select2 with dynamic remote dataset loading
        let turkeyCitiesData = [];
        const ilSelect = $('#il');
        const ilceSelect = $('#ilce');

        ilSelect.select2({
            placeholder: "İl Seçin...",
            allowClear: false
        });

        ilceSelect.select2({
            placeholder: "Önce İl Seçin...",
            allowClear: false
        });

        const oldIl = @json(old('il'));
        const oldIlce = @json(old('ilce'));

        fetch('/assets/data/cities.json')
            .then(response => response.json())
            .then(data => {
                turkeyCitiesData = data;
                
                turkeyCitiesData.forEach(city => {
                    city.displayName = capitalizeTurkish(city.name);
                });
                turkeyCitiesData.sort((a, b) => a.displayName.localeCompare(b.displayName, 'tr'));
                
                ilSelect.empty().append('<option value="" disabled selected>İl Seçin...</option>');
                
                turkeyCitiesData.forEach(city => {
                    const isSelected = (oldIl === city.displayName);
                    const opt = new Option(city.displayName, city.displayName, isSelected, isSelected);
                    ilSelect.append(opt);
                });
                
                ilSelect.trigger('change.select2');
                
                if (oldIl) {
                    populateDistricts(oldIl, oldIlce);
                }
            })
            .catch(err => console.error('Şehir verisi yüklenemedi:', err));

        function populateDistricts(ilName, selectedDistrict = null) {
            ilceSelect.empty().append('<option value="" disabled selected>İlçe Seçin...</option>');
            
            const cityObj = turkeyCitiesData.find(c => c.displayName === ilName);
            if (cityObj && cityObj.counties) {
                const formattedCounties = cityObj.counties.map(county => ({
                    original: county,
                    display: capitalizeTurkish(county)
                }));
                formattedCounties.sort((a, b) => a.display.localeCompare(b.display, 'tr'));
                
                formattedCounties.forEach(county => {
                    const isSelected = (selectedDistrict === county.display);
                    const opt = new Option(county.display, county.display, isSelected, isSelected);
                    ilceSelect.append(opt);
                });
            }
            
            ilceSelect.trigger('change.select2');
        }

        ilSelect.on('change', function() {
            populateDistricts(this.value);
            $(this).next('.select2-container').find('.select2-selection').css('border-color', '');
        });

        ilceSelect.on('change', function() {
            $(this).next('.select2-container').find('.select2-selection').css('border-color', '');
        });
    });

    // Tag based graduation info system
    function addMezuniyetTag() {
        const input = document.getElementById('mezuniyet_input');
        const value = input.value.trim();
        if (!value) return;

        createMezuniyetTag(value);
        input.value = '';
    }

    function createMezuniyetTag(text) {
        const container = document.getElementById('mezuniyet_tags_container');
        const hiddenContainer = document.getElementById('mezuniyet_hidden_fields');

        const tag = document.createElement('span');
        tag.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-50 border border-orange-100 text-xs font-semibold text-[#C96A2B] transition-all';
        tag.innerHTML = `
            <span>${text}</span>
            <button type="button" class="text-orange-400 hover:text-orange-600 focus:outline-none font-bold ml-1" onclick="removeMezuniyetTag(this, '${text.replace(/'/g, "\\'")}')">×</button>
        `;

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'mezuniyet[]';
        hiddenInput.value = text;

        container.appendChild(tag);
        hiddenContainer.appendChild(hiddenInput);
    }

    function removeMezuniyetTag(btn, text) {
        const tag = btn.parentElement;
        tag.remove();

        const hiddenContainer = document.getElementById('mezuniyet_hidden_fields');
        const inputs = hiddenContainer.querySelectorAll('input');
        inputs.forEach(input => {
            if (input.value === text) {
                input.remove();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('mezuniyet_input')?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addMezuniyetTag();
            }
        });
    });
</script>

<!-- Custom Searchable Multiselect JS logic -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('custom-multiselect-container');
        if (!container) return;

        const trigger = document.getElementById('multiselect-trigger');
        const dropdown = document.getElementById('multiselect-dropdown');
        const searchInput = document.getElementById('multiselect-search');
        const optionsContainer = document.getElementById('multiselect-options');
        const placeholder = document.getElementById('multiselect-placeholder');
        const hiddenInputsContainer = document.getElementById('multiselect-hidden-inputs');
        const chevron = document.getElementById('multiselect-chevron');
        const noResults = document.getElementById('multiselect-no-results');
        
        let selectedItems = []; // Array of { id, name }

        function trNormalize(str) {
            return str
                .replace(/İ/g, 'i')
                .replace(/I/g, 'ı')
                .replace(/Ş/g, 'ş')
                .replace(/Ğ/g, 'ğ')
                .replace(/Ü/g, 'ü')
                .replace(/Ö/g, 'ö')
                .replace(/Ç/g, 'ç')
                .toLowerCase();
        }

        trigger.addEventListener('click', function(e) {
            if (e.target.closest('.badge-remove')) return;
            
            const isOpen = dropdown.classList.contains('show');
            if (isOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        function openDropdown() {
            dropdown.classList.remove('hidden');
            setTimeout(() => {
                dropdown.classList.add('show');
                trigger.classList.add('multiselect-dropdown-open');
                if (chevron) chevron.style.transform = 'translateY(-50%) rotate(180deg)';
                searchInput.focus();
            }, 10);
        }

        function closeDropdown() {
            dropdown.classList.remove('show');
            trigger.classList.remove('multiselect-dropdown-open');
            if (chevron) chevron.style.transform = 'translateY(-50%) rotate(0deg)';
            setTimeout(() => {
                if (!dropdown.classList.contains('show')) {
                    dropdown.classList.add('hidden');
                }
            }, 180);
        }

        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                closeDropdown();
            }
        });

        searchInput.addEventListener('input', function(e) {
            const query = trNormalize(e.target.value.trim());
            const options = optionsContainer.querySelectorAll('.multiselect-option');
            let hasVisible = false;

            options.forEach(opt => {
                const name = trNormalize(opt.getAttribute('data-name'));
                if (name.includes(query)) {
                    opt.style.display = 'flex';
                    hasVisible = true;
                } else {
                    opt.style.display = 'none';
                }
            });

            if (hasVisible) {
                if (noResults) noResults.classList.add('hidden');
            } else {
                if (noResults) noResults.classList.remove('hidden');
            }
        });

        optionsContainer.querySelectorAll('.multiselect-option').forEach(opt => {
            opt.addEventListener('click', function() {
                const id = opt.getAttribute('data-id');
                const name = opt.getAttribute('data-name');
                toggleSelection(id, name, opt);
            });
        });

        function toggleSelection(id, name, element) {
            const index = selectedItems.findIndex(item => item.id === id);
            if (index === -1) {
                selectedItems.push({ id, name });
                element.querySelector('svg').classList.remove('hidden');
                element.classList.add('bg-orange-50/70', 'text-[#C96A2B]', 'font-semibold');
            } else {
                selectedItems.splice(index, 1);
                element.querySelector('svg').classList.add('hidden');
                element.classList.remove('bg-orange-50/70', 'text-[#C96A2B]', 'font-semibold');
            }

            const bransErrorDiv = document.getElementById('brans-error-message');
            if (bransErrorDiv && selectedItems.length > 0) {
                bransErrorDiv.classList.add('hidden');
            }

            updateUI();
        }

        function updateUI() {
            const badges = trigger.querySelectorAll('.multiselect-badge');
            badges.forEach(b => b.remove());

            hiddenInputsContainer.innerHTML = '';

            if (selectedItems.length === 0) {
                placeholder.classList.remove('hidden');
            } else {
                placeholder.classList.add('hidden');
                selectedItems.forEach(item => {
                    const badge = document.createElement('span');
                    badge.className = 'multiselect-badge inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-orange-50 border border-orange-100 text-[11px] font-semibold text-[#C96A2B] select-none';
                    badge.innerHTML = `
                        <span>${item.name}</span>
                        <span class="badge-remove text-orange-400 hover:text-orange-600 font-bold cursor-pointer text-xs ml-0.5" data-id="${item.id}">×</span>
                    `;
                    trigger.insertBefore(badge, placeholder);

                    badge.querySelector('.badge-remove').addEventListener('click', function(e) {
                        e.stopPropagation();
                        const opt = optionsContainer.querySelector(`.multiselect-option[data-id="${item.id}"]`);
                        toggleSelection(item.id, item.name, opt);
                    });

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'branslar[]';
                    input.value = item.id;
                    hiddenInputsContainer.appendChild(input);
                });
            }
        }

        const oldBranslar = @json(old('branslar', []));
        if (oldBranslar && oldBranslar.length > 0) {
            oldBranslar.forEach(id => {
                const opt = optionsContainer.querySelector(`.multiselect-option[data-id="${id}"]`);
                if (opt) {
                    const name = opt.getAttribute('data-name');
                    toggleSelection(id.toString(), name, opt);
                }
            });
        }
    });
</script>
@endsection
