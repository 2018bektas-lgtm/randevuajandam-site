<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik Daveti - Randevu Ajandam</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/logo.png') }}" type="image/png">
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 CSS/JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F9FAFB;
        }
        .font-display {
            font-family: 'Outfit', sans-serif;
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

        /* Custom Premium Select2 Overrides */
        .select2-container--default .select2-selection--single {
            background-color: #FAFAFA !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 12px !important;
            height: 46px !important;
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
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9CA3AF !important;
            font-weight: 400 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px !important;
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
        .select2-container {
            width: 100% !important;
        }
    </style>
</head>
<body class="text-slate-700 antialiased min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white border border-[#E5E7EB] rounded-3xl p-8 shadow-sm">
        
        <!-- Header -->
        <div class="text-center space-y-3">
            <div class="w-16 h-16 bg-[#FFF7ED] text-[#C96A2B] rounded-2xl flex items-center justify-center mx-auto border border-[#E7B58A]/30">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold font-display text-[#111827]">Klinik Daveti</h2>
            <p class="text-xs text-[#6B7280]">
                Sayın Meslektaşımız, <span class="font-bold text-[#C96A2B]">{{ $davetEden->unvan }} {{ $davetEden->ad_soyad }}</span> sizi <span class="font-bold text-[#111827]">{{ $klinik->ad }}</span> kadrosuna hekim olarak katılmaya davet ediyor.
            </p>
        </div>

        @if($existingDoctor)
            <!-- Existing Doctor Section -->
            <div class="space-y-6 pt-4 border-t border-slate-100">
                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-xs text-blue-800 space-y-2">
                    <p class="font-bold">Hekim Hesabınız Tespit Edildi</p>
                    <p>Sistemde **{{ $davetiye->davet_edilen_eposta }}** adresiyle kayıtlı bir hesabınız bulunmaktadır.</p>
                </div>

                @if(auth('doktor')->check() && auth('doktor')->id() === $existingDoctor->id)
                    <form action="{{ route('frontend.hekim.klinik.davet.kabul.post', ['token' => $davetiye->token]) }}" method="POST" class="space-y-3">
                        @csrf
                        <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-xs font-bold text-white shadow-sm transition-all uppercase tracking-wider font-display">
                            Daveti Kabul Et ve Katıl
                        </button>
                    </form>
                @else
                    <div class="space-y-4">
                        <a href="{{ route('frontend.hekim.giris', ['redirect' => url()->current()]) }}" class="block text-center w-full py-3.5 px-4 rounded-xl bg-[#1F2937] hover:bg-[#111827] text-xs font-bold text-white shadow-sm transition-all uppercase tracking-wider font-display">
                            Giriş Yaparak Daveti Kabul Et
                        </a>
                    </div>
                @endif

                <form action="{{ route('frontend.hekim.klinik.davet.reddet', ['token' => $davetiye->token]) }}" method="POST" class="pt-2">
                    @csrf
                    <button type="submit" class="w-full text-center text-xs font-bold text-red-600 hover:text-red-700 transition-colors uppercase tracking-wider font-display py-2">
                        Daveti Reddet
                    </button>
                </form>
            </div>
        @else
            <!-- New Doctor Sign Up Wizard -->
            <form action="{{ route('frontend.hekim.klinik.davet.kabul.post', ['token' => $davetiye->token]) }}" method="POST" class="space-y-5 pt-4 border-t border-slate-100">
                @csrf

                <div>
                    <label for="ad_soyad" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider mb-1.5">Ad Soyad *</label>
                    <input type="text" name="ad_soyad" id="ad_soyad" value="{{ old('ad_soyad') }}" required
                           class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                           placeholder="Örn: Ahmet Yılmaz">
                    @error('ad_soyad') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider mb-1.5">E-posta Adresiniz</label>
                    <input type="email" value="{{ $davetiye->davet_edilen_eposta }}" disabled
                           class="w-full text-xs bg-slate-100 border border-gray-200 text-slate-500 rounded-xl p-3 outline-none cursor-not-allowed font-medium">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="sifre" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider mb-1.5">Şifre *</label>
                        <input type="password" name="sifre" id="sifre" required
                               class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                               placeholder="********">
                        @error('sifre') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="sifre_confirmation" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider mb-1.5">Şifre Tekrarı *</label>
                        <input type="password" name="sifre_confirmation" id="sifre_confirmation" required
                               class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                               placeholder="********">
                    </div>
                </div>

                <div>
                    <label for="telefon" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider mb-1.5">Telefon Numarası *</label>
                    <input type="text" name="telefon" id="telefon" value="{{ old('telefon') }}" required
                           class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                           placeholder="0 (5xx) xxx xx xx">
                    @error('telefon') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="unvan" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider mb-1.5">Ünvan *</label>
                        <select name="unvan" id="unvan" class="w-full select2-select" required>
                            <option value="">Seçin</option>
                            @foreach($unvanlar as $unvan)
                                <option value="{{ $unvan->ad }}" {{ old('unvan') == $unvan->ad ? 'selected' : '' }}>{{ $unvan->ad }}</option>
                            @endforeach
                        </select>
                        @error('unvan') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="il_id" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider mb-1.5">İl *</label>
                        <select name="il_id" id="il_id" class="w-full select2-select" required>
                            <option value="">Seçin</option>
                            @foreach($iller as $il)
                                <option value="{{ $il->id }}" {{ old('il_id') == $il->id ? 'selected' : '' }}>{{ $il->ad }}</option>
                            @endforeach
                        </select>
                        @error('il_id') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="ilce_id" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider mb-1.5">İlçe *</label>
                        <select name="ilce_id" id="ilce_id" class="w-full select2-select" disabled required>
                            <option value="">Önce İl Seçin</option>
                        </select>
                        @error('ilce_id') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Branşlar / Uzmanlık Alanları (Özel Çoklu Seçim Widget) -->
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider mb-1.5 font-display">Branş / Uzmanlık Alanları *</label>
                        
                        <div class="relative w-full" id="custom-multiselect-container">
                            <!-- Input Box (Trigger) -->
                            <div id="multiselect-trigger" class="w-full min-h-[46px] pl-4 pr-10 py-2.5 rounded-xl bg-slate-50 border border-gray-200 focus-within:border-[#C96A2B] focus-within:ring-1 focus-within:ring-[#C96A2B] text-xs transition-all flex flex-wrap items-center gap-2 cursor-pointer select-none relative">
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
                        @error('branslar') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="pt-4 space-y-3">
                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-xs font-bold text-white shadow-sm transition-all uppercase tracking-wider font-display">
                        Kayıt Ol & Kliniğe Katıl
                    </button>

                    <button type="submit" name="action" value="reject" formnovalidate
                            class="w-full text-center text-xs font-bold text-red-600 hover:text-red-700 transition-colors uppercase tracking-wider font-display py-2 block">
                        Daveti Reddet
                    </button>
                </div>
            </form>
        @endif
    </div>

    <script>
        $(document).ready(function() {
            $('.select2-select').select2({
                width: '100%'
            });

            // AJAX İlceler
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

            // Phone mask
            $('#telefon').on('input', function() {
                let val = $(this).val().replace(/\D/g, '');
                if (val.startsWith('0')) val = val.substring(1);
                let formatted = '';
                if (val.length > 0) formatted += '0 (' + val.substring(0, 3);
                if (val.length > 3) formatted += ') ' + val.substring(3, 6);
                if (val.length > 6) formatted += ' ' + val.substring(6, 8);
                if (val.length > 8) formatted += ' ' + val.substring(8, 10);
                $(this).val(formatted);
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
</body>
</html>
