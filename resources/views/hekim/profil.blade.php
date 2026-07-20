@extends('hekim.layout')

@section('baslik', 'Profil Düzenle - Randevu Ajandam')
@section('sayfa_baslik', 'Profil Düzenle')

@section('icerik')
    <!-- jQuery and Select2 CSS & JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Leaflet Map CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

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

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('hekim.profil.post') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- Card 1: Avatar and main details -->
            <div class="p-8 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-6 pb-3 border-b border-slate-100">Profil Fotoğrafı & Kimlik Bilgileri</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Left Column: Avatar & Guide -->
                    <div class="md:col-span-1 flex flex-col items-center gap-5 w-full">
                        <!-- Avatar Upload Area with Preview (Dikey 3:4) -->
                        <div class="flex flex-col items-center gap-3 w-full">
                            <div class="relative group">
                                <div class="w-36 h-48 rounded-2xl overflow-hidden bg-gradient-to-br from-[#FFF7ED] to-[#FFFBEB] border border-[#E7B58A]/40 text-[#C96A2B] flex items-center justify-center font-extrabold font-display text-4xl shadow-md select-none shrink-0 relative z-10">
                                    @if($doktor->profil_resmi)
                                        <img id="previewImage" src="{{ asset($doktor->profil_resmi) }}" alt="{{ $doktor->ad_soyad }}" class="w-full h-full object-cover">
                                    @else
                                        <span id="avatarInitials">
                                            @php
                                                $words = explode(' ', $doktor->ad_soyad);
                                                $kisaAd = mb_strtoupper(mb_substr($words[0], 0, 1));
                                                if (count($words) > 1) {
                                                    $kisaAd .= mb_strtoupper(mb_substr(end($words), 0, 1));
                                                }
                                                echo $kisaAd;
                                            @endphp
                                        </span>
                                        <img id="previewImage" src="" alt="Önizleme" class="w-full h-full object-cover hidden">
                                    @endif
                                </div>
                                
                                <!-- Upload Overlay -->
                                <label for="profil_resmi" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-black/60 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 cursor-pointer text-white text-center p-2">
                                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"></path>
                                    </svg>
                                    <span class="text-[9px] font-bold uppercase tracking-wider font-display">Resim Seç</span>
                                </label>
                                <input type="file" name="profil_resmi" id="profil_resmi" accept="image/*" class="hidden" onchange="previewAvatar(this)">
                            </div>
                            <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Görsel Önizleme</span>
                        </div>

                        <!-- Photo Upload Instructions Card -->
                        <div class="w-full p-4.5 rounded-2xl bg-amber-50/40 border border-amber-100 text-xs text-[#8A5A36] space-y-2">
                            <div class="flex items-center gap-2 font-bold font-display text-[11px] uppercase tracking-wider">
                                <svg class="w-4 h-4 text-[#C96A2B] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.063.852l-.708 2.836a.75.75 0 001.063.852l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"></path>
                                </svg>
                                Yükleme Kılavuzu
                            </div>
                            <p class="leading-relaxed text-[11px]">
                                Listelemede en iyi görünüm için <strong>dikey (3:4)</strong> fotoğraf yükleyin.
                            </p>
                            <div class="grid grid-cols-2 gap-2 pt-1.5 border-t border-amber-200/40 text-[10px] font-medium font-display">
                                <div>
                                    <span class="block text-gray-400 font-bold uppercase text-[9px]">Format</span>
                                    <span class="font-bold text-[#111827]">JPG, PNG, GIF</span>
                                </div>
                                <div>
                                    <span class="block text-gray-400 font-bold uppercase text-[9px]">Maks. Boyut</span>
                                    <span class="font-bold text-[#111827]">10 MB</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Main fields -->
                    <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-5 self-start">
                        <!-- Unvan -->
                        <div class="space-y-1.5">
                            <label for="unvan" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Akademik Unvan</label>
                            <select name="unvan" id="unvan" required
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all cursor-pointer font-semibold">
                                @foreach($unvanlar as $u)
                                    <option value="{{ $u->ad }}" {{ $doktor->unvan === $u->ad ? 'selected' : '' }}>{{ $u->ad }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Ad Soyad -->
                        <div class="space-y-1.5">
                            <label for="ad_soyad" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Ad Soyad</label>
                            <input type="text" name="ad_soyad" id="ad_soyad" value="{{ old('ad_soyad', $doktor->ad_soyad) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                        </div>

                        <!-- E-Posta (Read Only) -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider font-display">E-Posta (Değiştirilemez)</label>
                            <input type="email" value="{{ $doktor->e_posta }}" readonly
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-gray-50 border border-[#E5E7EB] text-gray-400 focus:outline-none text-xs transition-all cursor-not-allowed select-none">
                        </div>

                        <!-- Telefon -->
                        <div class="space-y-1.5">
                            <label for="telefon" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Telefon</label>
                            <input type="text" name="telefon" id="telefon" value="{{ old('telefon', $doktor->telefon) }}" required placeholder="0 (5XX) XXX XX XX"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label for="tc_kimlik_no" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">T.C. Kimlik No</label>
                            <input type="text" name="tc_kimlik_no" id="tc_kimlik_no" value="{{ old('tc_kimlik_no', $doktor->tc_kimlik_no) }}" maxlength="11" inputmode="numeric" placeholder="11 hane (kartlı abonelik için)"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                            <p class="text-[10px] text-slate-400">iyzico abonelik ödemesi için gereklidir. Gizli tutulur.</p>
                            @error('tc_kimlik_no')
                                <p class="text-[10px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- İl -->
                        <div class="space-y-1.5">
                            <label for="il" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Hizmet Verilen İl</label>
                            <select name="il" id="il" required class="w-full">
                                <option value="" disabled>İl Seçin...</option>
                            </select>
                        </div>

                        <!-- İlçe -->
                        <div class="space-y-1.5">
                            <label for="ilce" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Hizmet Verilen İlçe</label>
                            <select name="ilce" id="ilce" required class="w-full">
                                <option value="" disabled>Önce İl Seçin...</option>
                            </select>
                        </div>

                        <!-- Klinik Adresi -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <label for="adres" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Klinik / Muayenehane Adresi</label>
                            <textarea name="adres" id="adres" rows="3" placeholder="Hizmet verdiğiniz açık adresi buraya girin..."
                                      class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">{{ old('adres', $doktor->adres) }}</textarea>
                        </div>

                        <!-- Klinik Harita Konumu -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Harita Üzerinde Konum Seçin</label>
                            <p class="text-[10px] text-[#6B7280] mb-2">Hastalarınızın sizi kolayca bulabilmesi için harita üzerinde kliniğinizin yerini tıklayarak işaretleyin.</p>
                            <div id="map" class="w-full h-64 rounded-2xl border border-[#E5E7EB] shadow-inner relative z-10"></div>
                            <input type="hidden" name="enlem" id="enlem" value="{{ old('enlem', $doktor->enlem) }}">
                            <input type="hidden" name="boylam" id="boylam" value="{{ old('boylam', $doktor->boylam) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Social Media Accounts -->
            <div class="p-8 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-6 pb-3 border-b border-slate-100">Sosyal Medya Hesapları</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Instagram -->
                    <div class="space-y-1.5">
                        <label for="instagram" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Instagram</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-xs">@</span>
                            <input type="text" name="instagram" id="instagram" value="{{ old('instagram', $doktor->instagram) }}" placeholder="kullanıcı_adı"
                                   class="w-full pl-7 pr-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                        </div>
                    </div>

                    <!-- Facebook -->
                    <div class="space-y-1.5">
                        <label for="facebook" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Facebook Linki</label>
                        <input type="text" name="facebook" id="facebook" value="{{ old('facebook', $doktor->facebook) }}" placeholder="kullanıcı_adı veya link"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>

                    <!-- Twitter (X) -->
                    <div class="space-y-1.5">
                        <label for="twitter" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Twitter (X)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-xs">@</span>
                            <input type="text" name="twitter" id="twitter" value="{{ old('twitter', $doktor->twitter) }}" placeholder="kullanıcı_adı"
                                   class="w-full pl-7 pr-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                        </div>
                    </div>

                    <!-- Linkedin -->
                    <div class="space-y-1.5">
                        <label for="linkedin" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">LinkedIn Linki</label>
                        <input type="text" name="linkedin" id="linkedin" value="{{ old('linkedin', $doktor->linkedin) }}" placeholder="kullanıcı_adı veya link"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>

                    <!-- Youtube -->
                    <div class="space-y-1.5">
                        <label for="youtube" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">YouTube Kanalı</label>
                        <input type="text" name="youtube" id="youtube" value="{{ old('youtube', $doktor->youtube) }}" placeholder="kullanıcı_adı veya link"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>

                </div>
            </div>

            <!-- Form Submission Action -->
            <div class="flex justify-end gap-3.5">
                <a href="{{ route('hekim.panel') }}" 
                   class="px-6 py-3 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#6B7280] font-bold text-xs uppercase tracking-wider transition-all font-display text-center select-none shadow-sm cursor-pointer">
                    Geri Dön
                </a>
                <button type="submit" 
                        class="px-8 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                    Bilgilerimi Güncelle
                </button>
            </div>
        </form>
    </div>

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
        });

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

        document.addEventListener('DOMContentLoaded', function() {
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

        // JavaScript live avatar image preview
        function previewAvatar(input) {
            const preview = document.getElementById('previewImage');
            const initials = document.getElementById('avatarInitials');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (preview && initials) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                        initials.classList.add('hidden');
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Leaflet Map Initialization & Location Picker
        document.addEventListener('DOMContentLoaded', function() {
            var defaultLat = {{ old('enlem', $doktor->enlem) ?? 39.0 }};
            var defaultLng = {{ old('boylam', $doktor->boylam) ?? 35.0 }};
            var defaultZoom = {{ ($doktor->enlem && $doktor->boylam) ? 15 : 6 }};

            var map = L.map('map').setView([defaultLat, defaultLng], defaultZoom);

            L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                attribution: '&copy; <a href="https://maps.google.com">Google Maps</a>'
            }).addTo(map);

            var marker;

            if ({{ ($doktor->enlem && $doktor->boylam) ? 'true' : 'false' }}) {
                marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);
                bindMarkerEvents(marker);
            }

            map.on('click', function(e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;

                document.getElementById('enlem').value = lat.toFixed(8);
                document.getElementById('boylam').value = lng.toFixed(8);

                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng, {draggable: true}).addTo(map);
                    bindMarkerEvents(marker);
                }
            });

            function bindMarkerEvents(m) {
                m.on('dragend', function(e) {
                    var position = marker.getLatLng();
                    document.getElementById('enlem').value = position.lat.toFixed(8);
                    document.getElementById('boylam').value = position.lng.toFixed(8);
                });
            }

            // Fly to city when changed
            $('#il').on('change', function() {
                var cityName = this.value;
                if (cityName) {
                    // Quick geocoding via OpenStreetMap Nominatim or approximate coordinates
                    fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(cityName + ', Turkey'))
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                var lat = parseFloat(data[0].lat);
                                var lon = parseFloat(data[0].lon);
                                map.setView([lat, lon], 11);
                            }
                        })
                        .catch(err => console.error(err));
                }
            });

            // Fly to district when changed
            $('#ilce').on('change', function() {
                var cityName = $('#il').val();
                var districtName = this.value;
                if (cityName && districtName) {
                    fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(districtName + ', ' + cityName + ', Turkey'))
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                var lat = parseFloat(data[0].lat);
                                var lon = parseFloat(data[0].lon);
                                map.setView([lat, lon], 14);
                            }
                        })
                        .catch(err => console.error(err));
                }
            });
        });
    </script>
@endsection
