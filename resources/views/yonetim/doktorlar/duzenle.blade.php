@extends('yonetim.layout')

@section('baslik', 'Doktoru Düzenle - Randevu Ajandam')
@section('sayfa_baslik', 'Doktor Yönetimi')

@section('icerik')
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
            height: 48px !important; /* matches py-3 in admin panels */
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
            font-size: 0.875rem !important; /* text-sm */
            font-weight: 500 !important;
            padding: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9CA3AF !important;
            font-weight: 400 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
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
            font-size: 0.875rem !important;
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
            font-size: 0.875rem !important;
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

    <div class="max-w-3xl mx-auto">
        <!-- Top Action Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
            <div>
                <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                    <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                    Doktor Bilgilerini Düzenle
                </h2>
                <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Doktor üyelik bilgileri, paket ataması ve durumunu güncelleyin.</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('yonetim.doktorlar.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] hover:border-[#E7B58A]/40 transition-all duration-200 shadow-sm select-none group">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                    </svg>
                    Listeye Dön
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8">
            <form action="{{ route('yonetim.doktorlar.update', $doktor->id) }}" method="POST" class="space-y-6">
                @csrf

                <!-- Doctor Profile Header -->
                <div class="flex items-center gap-4 pb-6 border-b border-[#E5E7EB] mb-6">
                    @if($doktor->profil_resmi)
                        <img src="{{ asset($doktor->profil_resmi) }}" alt="{{ $doktor->ad_soyad }}" class="w-16 h-16 rounded-2xl object-cover border border-[#E5E7EB] shadow-sm">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center font-bold text-xl font-display shadow-sm">
                            {{ mb_strtoupper(mb_substr(preg_replace('/^(Prof\.|Doç\.|Dr\.|Uzm\.)\s+/i', '', $doktor->ad_soyad), 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <h3 class="text-base font-bold text-[#111827] font-display">
                            @if($doktor->unvan)
                                <span class="text-sm font-semibold text-[#C96A2B] mr-0.5">{{ $doktor->unvan }}</span>
                            @endif
                            {{ $doktor->ad_soyad }}
                        </h3>
                        <p class="text-xs text-[#6B7280] mt-1">{{ $doktor->e_posta }}</p>
                        @if($doktor->uzmanlik_alani)
                            <span class="inline-flex items-center mt-1.5 px-2 py-0.5 rounded bg-slate-50 border border-slate-200 text-[10px] font-semibold text-[#4B5563]">
                                {{ $doktor->uzmanlik_alani }}
                            </span>
                        @endif
                    </div>
                </div>

                <input type="hidden" name="tur" value="bireysel">
                <input type="hidden" name="klinik_adi" value="">

                <!-- Unvan & Ad Soyad -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label for="unvan" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Ünvan</label>
                        <select name="unvan" id="unvan"
                            class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200 cursor-pointer">
                            <option value="">Ünvan Seçin (Opsiyonel)</option>
                            <option value="Prof. Dr." {{ old('unvan', $doktor->unvan) === 'Prof. Dr.' ? 'selected' : '' }}>Prof. Dr.</option>
                            <option value="Doç. Dr." {{ old('unvan', $doktor->unvan) === 'Doç. Dr.' ? 'selected' : '' }}>Doç. Dr.</option>
                            <option value="Dr. Öğr. Üyesi" {{ old('unvan', $doktor->unvan) === 'Dr. Öğr. Üyesi' ? 'selected' : '' }}>Dr. Öğr. Üyesi</option>
                            <option value="Uzm. Dr." {{ old('unvan', $doktor->unvan) === 'Uzm. Dr.' ? 'selected' : '' }}>Uzm. Dr.</option>
                            <option value="Dr." {{ old('unvan', $doktor->unvan) === 'Dr.' ? 'selected' : '' }}>Dr.</option>
                            <option value="Dt." {{ old('unvan', $doktor->unvan) === 'Dt.' ? 'selected' : '' }}>Dt.</option>
                            <option value="Uzm. Dt." {{ old('unvan', $doktor->unvan) === 'Uzm. Dt.' ? 'selected' : '' }}>Uzm. Dt.</option>
                            <option value="Fzt." {{ old('unvan', $doktor->unvan) === 'Fzt.' ? 'selected' : '' }}>Fzt.</option>
                            <option value="Psk." {{ old('unvan', $doktor->unvan) === 'Psk.' ? 'selected' : '' }}>Psk.</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="ad_soyad" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Ad Soyad</label>
                        <input type="text" name="ad_soyad" id="ad_soyad" value="{{ old('ad_soyad', $doktor->ad_soyad) }}" required placeholder="Örn: Ahmet Yılmaz"
                            class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                    </div>
                </div>

                <!-- İletişim Bilgileri -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="e_posta" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">E-Posta Adresi</label>
                        <input type="email" name="e_posta" id="e_posta" value="{{ old('e_posta', $doktor->e_posta) }}" required placeholder="e-posta@adresiniz.com"
                            class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                    </div>
                    <div>
                        <label for="telefon" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Telefon Numarası</label>
                        <input type="text" name="telefon" id="telefon" value="{{ old('telefon', $doktor->telefon) }}" placeholder="0 (5XX) XXX XX XX"
                            class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                    </div>
                </div>

                <!-- Hizmet Verilen Konum (İl/İlçe) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="il" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Hizmet Verilen İl</label>
                        <select name="il" id="il" required class="w-full">
                            <option value="" disabled>İl Seçin...</option>
                        </select>
                    </div>
                    <div>
                        <label for="ilce" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2.5 font-display">Hizmet Verilen İlçe</label>
                        <select name="ilce" id="ilce" required class="w-full">
                            <option value="" disabled>Önce İl Seçin...</option>
                        </select>
                    </div>
                </div>

                <!-- Paket & Ödeme Planı -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-4.5 bg-slate-50/30 border border-[#E5E7EB] rounded-xl">
                    <div>
                        <label for="paket_id" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Abonelik Paketi</label>
                        <select name="paket_id" id="paket_id"
                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200 cursor-pointer">
                            <option value="">Paket Atanmamış</option>
                            @foreach($paketler as $p)
                                <option value="{{ $p->id }}" {{ old('paket_id', $doktor->paket_id) == $p->id ? 'selected' : '' }}>
                                    {{ $p->ad }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="odeme_periyodu" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Ödeme Periyodu</label>
                        <select name="odeme_periyodu" id="odeme_periyodu"
                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200 cursor-pointer">
                            <option value="">Seçilmemiş</option>
                            <option value="aylik" {{ old('odeme_periyodu', $doktor->odeme_periyodu) === 'aylik' ? 'selected' : '' }}>Aylık</option>
                            <option value="yillik" {{ old('odeme_periyodu', $doktor->odeme_periyodu) === 'yillik' ? 'selected' : '' }}>Yıllık</option>
                        </select>
                    </div>
                </div>

                <!-- Üyelik Tarihleri -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="uyelik_baslangic" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Üyelik Başlangıç Tarihi</label>
                        <input type="date" name="uyelik_baslangic" id="uyelik_baslangic" 
                            value="{{ old('uyelik_baslangic', $doktor->uyelik_baslangic ? $doktor->uyelik_baslangic->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                    </div>
                    <div>
                        <label for="uyelik_bitis" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Üyelik Bitiş Tarihi</label>
                        <input type="date" name="uyelik_bitis" id="uyelik_bitis" 
                            value="{{ old('uyelik_bitis', $doktor->uyelik_bitis ? $doktor->uyelik_bitis->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                    </div>
                </div>

                <!-- Şifre Değiştirme -->
                <div class="p-4.5 bg-amber-50/30 border border-amber-100/50 rounded-xl space-y-4">
                    <div>
                        <h4 class="text-xs font-bold text-[#C96A2B] uppercase tracking-wider font-display flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"></path>
                            </svg>
                            Şifre Güncelleme
                        </h4>
                        <p class="text-[11px] text-[#6B7280] mt-1">Hekim şifresini değiştirmek isterseniz yeni şifreyi yazın. Mevcut şifreyi korumak için boş bırakın.</p>
                    </div>
                    <div>
                        <label for="sifre" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Yeni Şifre</label>
                        <input type="password" name="sifre" id="sifre" placeholder="En az 6 karakter girin"
                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                    </div>
                </div>

                <!-- Perfect iOS Toggle Switch for Durum -->
                <div class="flex items-center justify-between p-4.5 bg-slate-50/50 border border-[#E5E7EB] rounded-xl">
                    <div>
                        <span class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display">Hesap Durumu</span>
                        <span class="block text-[11px] text-[#6B7280] mt-0.5">Doktor hesabı aktif ve sisteme giriş yapabilsin mi?</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="aktif_mi" value="1" {{ old('aktif_mi', $doktor->aktif_mi) ? 'checked' : '' }} class="sr-only peer">
                        <!-- iOS-style Switch -->
                        <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all after:duration-300 peer-checked:after:translate-x-5 shadow-inner"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4.5 bg-slate-50/50 border border-[#E5E7EB] rounded-xl">
                    <div>
                        <span class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display">Ana platform vitrini</span>
                        <span class="block text-[11px] text-[#6B7280] mt-0.5">Randevu Ajandam arama/listesinde görünsün mü? (Kendi web sitesi ayrıdır.)</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="platformda_gorunur" value="1" {{ old('platformda_gorunur', $doktor->platformda_gorunur ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all after:duration-300 peer-checked:after:translate-x-5 shadow-inner"></div>
                    </label>
                </div>

                <!-- Submit buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#E5E7EB]">
                    <a href="{{ route('yonetim.doktorlar.index') }}" 
                       class="px-5 py-2.5 rounded-xl border border-[#E5E7EB] hover:bg-slate-50 text-[#6B7280] font-bold text-sm transition-all duration-200 select-none">
                        İptal Et
                    </a>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-sm transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer select-none">
                        Değişiklikleri Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script for Dynamic Inputs & Phone Formatting -->
    <script>
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

        document.addEventListener('DOMContentLoaded', function() {
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
                placeholder: "İlçe Seçin...",
                allowClear: false
            });

            // Current user values
            const currentIl = @json(old('il', $doktor->il?->ad));
            const currentIlce = @json(old('ilce', $doktor->ilce?->ad));

            fetch('/assets/data/cities.json')
                .then(response => response.json())
                .then(data => {
                    turkeyCitiesData = data;
                    
                    // Capitalize and sort cities
                    turkeyCitiesData.forEach(city => {
                        city.displayName = capitalizeTurkish(city.name);
                    });
                    turkeyCitiesData.sort((a, b) => a.displayName.localeCompare(b.displayName, 'tr'));
                    
                    ilSelect.empty().append('<option value="" disabled>İl Seçin...</option>');
                    
                    turkeyCitiesData.forEach(city => {
                        const isSelected = (currentIl === city.displayName);
                        const opt = new Option(city.displayName, city.displayName, isSelected, isSelected);
                        ilSelect.append(opt);
                    });
                    
                    ilSelect.trigger('change.select2');
                    
                    if (currentIl) {
                        populateDistricts(currentIl, currentIlce);
                    }
                })
                .catch(err => console.error('Şehir verisi yüklenemedi:', err));

            function populateDistricts(ilName, selectedDistrict = null) {
                ilceSelect.empty().append('<option value="" disabled>İlçe Seçin...</option>');
                
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
            });
        });
    </script>
@endsection
