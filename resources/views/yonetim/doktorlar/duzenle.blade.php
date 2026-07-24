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

    <div class="max-w-4xl mx-auto">
        <!-- Top Action Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
            <div>
                <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                    <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                    Doktor Bilgilerini Düzenle
                </h2>
                <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Üyelik, paket, klinik bağlantısı, rol ve yetkileri buradan yönetin.</p>
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

        {{-- Meslek belgesi: AYRI form (iç içe form HTML'de geçersizdir; kaydet butonunu bozar) --}}
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8 mb-6">
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

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 font-display">Meslek belgesi — manuel onay</h3>
                        <p class="text-[11px] text-slate-500 mt-1">
                            1) Belgeyi açın · 2) TC + ad-soyad + diploma no eşleştirin ·
                            3) Barkod varsa e-Devlet’te doğrulayın · 4) Onayla / Reddet
                        </p>
                    </div>
                    @php $md = $doktor->meslek_dogrulama_durumu ?? 'beklemede'; @endphp
                    @if($md === 'onaylandi')
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Onaylı — ödeme açık</span>
                    @elseif($md === 'reddedildi')
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800">Reddedildi</span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900">Onay bekliyor — ödeme kilitli</span>
                    @endif
                </div>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <dt class="text-[10px] font-bold uppercase text-slate-500">T.C. Kimlik (kopyala)</dt>
                        <dd class="mt-0.5 font-mono font-semibold select-all">{{ $doktor->tc_kimlik_no ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase text-slate-500">Diploma / tescil no</dt>
                        <dd class="mt-0.5 font-semibold">{{ $doktor->diploma_no ?: '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-[10px] font-bold uppercase text-slate-500">e-Devlet barkod (kopyala)</dt>
                        <dd class="mt-0.5 font-mono font-semibold select-all break-all">{{ $doktor->edevlet_barkod ?: 'Girilmemiş' }}</dd>
                    </div>
                    <div class="sm:col-span-2 flex flex-wrap gap-2">
                        @if($doktor->meslek_belge_yolu)
                            <a href="{{ route('yonetim.doktorlar.meslek-belge', $doktor->id) }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-[#C96A2B] hover:bg-[#FFF7ED]">
                                Yüklenen belgeyi aç
                            </a>
                        @endif
                        <a href="https://www.turkiye.gov.tr/belge-dogrulama" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#1C3761] text-white text-xs font-bold hover:bg-[#152a4a]">
                            e-Devlet belge doğrulama (yeni sekme)
                        </a>
                    </div>
                    @if($doktor->meslek_dogrulama_notu)
                        <div class="sm:col-span-2 text-slate-600">Son not: {{ $doktor->meslek_dogrulama_notu }}</div>
                    @endif
                </dl>
                <p class="text-[10px] text-slate-500 leading-relaxed border-t border-slate-200 pt-3">
                    e-Devlet: önce barkodu, sonra TC’yi girin. Sonuç uyumluysa <strong>Onayla</strong>.
                    Otomatik bot yok — doğrulama sizin manuel onayınızla kesinleşir.
                </p>
                <form action="{{ route('yonetim.doktorlar.meslek-dogrula', $doktor->id) }}" method="POST" class="flex flex-col sm:flex-row gap-2 sm:items-end border-t border-slate-200 pt-4" id="meslekDogrulaForm">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold uppercase text-slate-600 mb-1">Not (red için zorunlu)</label>
                        <input type="text" name="not" id="meslekNot" placeholder="Örn. Belge okunmuyor / TC uyuşmuyor" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs" value="{{ old('not') }}">
                        @error('not')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" name="karar" value="onaylandi" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">Onayla → ödeme açılsın</button>
                    <button type="submit" name="karar" value="reddedildi" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold" onclick="if(!document.getElementById('meslekNot').value.trim()){event.preventDefault();alert('Reddetmeden önce gerekçe notu girin.');}">Reddet</button>
                </form>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8">
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800">
                    <p class="font-bold mb-1">Kayıt yapılamadı — lütfen düzeltin:</p>
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('yonetim.doktorlar.update', $doktor->id) }}" method="POST" class="space-y-6" id="doktorDuzenleForm">
                @csrf

                <!-- Hesap Türü -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-4 bg-slate-50/40 border border-[#E5E7EB] rounded-xl">
                    <div>
                        <label for="tur" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Hesap Türü (etiket)</label>
                        <select name="tur" id="tur"
                            class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200 cursor-pointer">
                            <option value="bireysel" {{ old('tur', $doktor->tur) === 'bireysel' ? 'selected' : '' }}>Tek Hekim (Bireysel)</option>
                            <option value="klinik" {{ old('tur', $doktor->tur) === 'klinik' ? 'selected' : '' }}>Klinik</option>
                        </select>
                        <p class="text-[10px] text-[#6B7280] mt-1.5">Bu alan etiket amaçlıdır. Gerçek klinik yetkisi aşağıdaki klinik bağlantısından gelir.</p>
                    </div>
                    <div id="klinikAdiWrap" style="{{ old('tur', $doktor->tur) === 'klinik' ? '' : 'display:none' }}">
                        <label for="klinik_adi" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Klinik Adı (serbest metin)</label>
                        <input type="text" name="klinik_adi" id="klinik_adi" value="{{ old('klinik_adi', $doktor->klinik_adi) }}" placeholder="Klinik adını girin"
                            class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                    </div>
                </div>

                @php
                    $selectedKlinikId = old('klinik_id', $doktor->klinik_id);
                    $selectedRolu = old('klinik_rolu', $doktor->klinik_rolu ?: 'doktor');
                    $oldYetkiler = old('klinik_yetkileri');
                    $yetkiEtiketleri = [
                        'yonetim_paneli' => ['Klinik Yönetim Paneli', 'Yönetim özet paneline giriş'],
                        'klinik_ayarlari' => ['Klinik Ayarları', 'Künye, logo ve çalışma saatleri'],
                        'hekim_yonetimi' => ['Hekim Yönetimi', 'Davet / çıkarma yetkisi'],
                        'personel_yonetimi' => ['Personel Yönetimi', 'Personel ekleme ve düzenleme'],
                        'finans_yonetimi' => ['Gider & Finans', 'Gelir, gider ve raporlar'],
                        'hakedis_yonetimi' => ['Hakediş Yönetimi', 'Hekim hakediş hesaplama'],
                        'ortak_hasta_havuzu' => ['Ortak Hasta Havuzu', 'Tüm klinik hastalarını görme'],
                        'duyuru_yonetimi' => ['Duyuru Yönetimi', 'İç duyuru oluşturma'],
                    ];
                    $isOwnerLike = in_array($selectedRolu, ['sahip', 'ortak'], true);
                @endphp

                <!-- Klinik Üyelik & Yetkiler -->
                <div class="p-5 border border-indigo-100 bg-indigo-50/30 rounded-2xl space-y-5" id="klinikUyelikBolumu">
                    <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b border-indigo-100/80">
                        <div>
                            <h4 class="text-sm font-bold text-[#1F2937] font-display flex items-center gap-2">
                                <span class="inline-flex w-7 h-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 text-xs">K</span>
                                Klinik Bağlantısı ve Yetkiler
                            </h4>
                            <p class="text-[11px] text-[#6B7280] mt-1 ml-9">
                                Kliniğe bağlı hekim paneli erişimi buradan yönetilir.
                                <strong>Bağlantıyı kaldır</strong> seçerseniz tüm klinik yetkileri kesilir.
                            </p>
                        </div>
                        @if($doktor->klinik_id)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">
                                Şu an bağlı: {{ $doktor->klinik?->ad ?? ('#'.$doktor->klinik_id) }}
                                @if($doktor->klinik_rolu)
                                    · {{ $doktor->klinik_rolu }}
                                @endif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                Kliniğe bağlı değil
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="klinik_id" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Bağlı Klinik</label>
                            <select name="klinik_id" id="klinik_id"
                                class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200 cursor-pointer">
                                <option value="">— Bağlantıyı kaldır (bireysel) —</option>
                                @foreach($klinikler as $k)
                                    <option value="{{ $k->id }}" {{ (string) $selectedKlinikId === (string) $k->id ? 'selected' : '' }}>
                                        {{ $k->ad }}{{ $k->aktif_mi ? '' : ' (pasif)' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('klinik_id')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div id="klinikRoluWrap">
                            <label for="klinik_rolu" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Klinik Rolü</label>
                            <select name="klinik_rolu" id="klinik_rolu"
                                class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200 cursor-pointer">
                                <option value="doktor" {{ $selectedRolu === 'doktor' ? 'selected' : '' }}>Hekim (standart üye — yetkileri seçin)</option>
                                <option value="ortak" {{ $selectedRolu === 'ortak' ? 'selected' : '' }}>Ortak / eş-sahip (tüm yetkiler)</option>
                                <option value="sahip" {{ $selectedRolu === 'sahip' ? 'selected' : '' }}>Klinik sahibi (tüm yetkiler)</option>
                            </select>
                            @error('klinik_rolu')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
                            <p class="text-[10px] text-[#6B7280] mt-1.5">Sahip seçilirse klinik kaydındaki <code class="text-[10px]">sahip_doktor_id</code> bu hekime güncellenir.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" id="klinikEkAlanlar">
                        <div>
                            <label for="komisyon_orani" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Komisyon Oranı (%)</label>
                            <input type="number" name="komisyon_orani" id="komisyon_orani" step="0.01" min="0" max="100"
                                value="{{ old('komisyon_orani', $doktor->komisyon_orani ?? 0) }}"
                                class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                            @error('komisyon_orani')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex items-center justify-between p-4 bg-white border border-[#E5E7EB] rounded-xl">
                            <div>
                                <span class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display">Klinik içi aktif</span>
                                <span class="block text-[11px] text-[#6B7280] mt-0.5">Kliniğe bağlı ama pasif tutulabilir</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" name="klinik_aktif_mi" value="1"
                                    {{ old('klinik_aktif_mi', $doktor->klinik_aktif_mi ?? true) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all after:duration-300 peer-checked:after:translate-x-5 shadow-inner"></div>
                            </label>
                        </div>
                    </div>

                    <div id="klinikYetkilerAlani" class="p-4 rounded-xl border border-[#E5E7EB] bg-white space-y-3 {{ $isOwnerLike ? 'opacity-50 pointer-events-none' : '' }}">
                        <div>
                            <span class="block text-xs font-bold text-[#111827] uppercase tracking-wider font-display">Klinik Yetkileri</span>
                            <p class="text-[10px] text-[#6B7280] mt-0.5">Sadece “Hekim” rolünde geçerlidir. Sahip ve ortak tüm yetkilere sahiptir.</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($yetkiEtiketleri as $kod => [$baslik, $aciklama])
                                @php
                                    if (is_array($oldYetkiler)) {
                                        $checked = ! empty($oldYetkiler[$kod]);
                                    } elseif (in_array($selectedRolu, ['sahip', 'ortak'], true)) {
                                        $checked = true;
                                    } else {
                                        $checked = is_array($doktor->klinik_yetkileri)
                                            && ! empty($doktor->klinik_yetkileri[$kod]);
                                    }
                                @endphp
                                <label class="flex items-start gap-3 cursor-pointer select-none p-2.5 rounded-lg hover:bg-slate-50">
                                    <input type="checkbox" name="klinik_yetkileri[{{ $kod }}]" value="1" {{ $checked ? 'checked' : '' }}
                                        class="mt-0.5 rounded text-[#C96A2B] focus:ring-[#C96A2B]/20">
                                    <div>
                                        <span class="block text-xs font-semibold text-[#111827]">{{ $baslik }}</span>
                                        <span class="block text-[10px] text-gray-400">{{ $aciklama }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

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
                        <select name="il" id="il" class="w-full">
                            <option value="">İl Seçin...</option>
                        </select>
                    </div>
                    <div>
                        <label for="ilce" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2.5 font-display">Hizmet Verilen İlçe</label>
                        <select name="ilce" id="ilce" class="w-full">
                            <option value="">Önce İl Seçin...</option>
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
                                    @if(($p->tur ?? '') === 'klinik')
                                        · Klinik
                                    @else
                                        · Bireysel
                                    @endif
                                    @if(! $p->aktif_mi)
                                        (pasif)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-[#6B7280] mt-1.5">Kliniğe bağlı hekimde panel özellikleri çoğunlukla <strong>klinik paketinden</strong> okunur; bu alan hekimin kendi aboneliğidir.</p>
                    </div>
                    <div>
                        <label for="odeme_periyodu" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Ödeme Periyodu</label>
                        <select name="odeme_periyodu" id="odeme_periyodu"
                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200 cursor-pointer">
                            <option value="">Seçilmemiş</option>
                            <option value="aylik" {{ old('odeme_periyodu', $doktor->odeme_periyodu) === 'aylik' ? 'selected' : '' }}>Aylık</option>
                            <option value="yillik" {{ old('odeme_periyodu', $doktor->odeme_periyodu) === 'yillik' ? 'selected' : '' }}>Yıllık</option>
                            <option value="deneme" {{ old('odeme_periyodu', $doktor->odeme_periyodu) === 'deneme' ? 'selected' : '' }}>Ücretsiz deneme</option>
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
            const turSelect = document.getElementById('tur');
            const klinikAdiWrap = document.getElementById('klinikAdiWrap');
            if (turSelect && klinikAdiWrap) {
                turSelect.addEventListener('change', function() {
                    klinikAdiWrap.style.display = this.value === 'klinik' ? '' : 'none';
                });
            }

            // Klinik üyelik alanları göster/gizle + sahip/ortak yetki kilidi
            const klinikIdSelect = document.getElementById('klinik_id');
            const klinikRoluSelect = document.getElementById('klinik_rolu');
            const klinikRoluWrap = document.getElementById('klinikRoluWrap');
            const klinikEkAlanlar = document.getElementById('klinikEkAlanlar');
            const klinikYetkilerAlani = document.getElementById('klinikYetkilerAlani');
            const turSelectForKlinik = document.getElementById('tur');

            function setKlinikFieldsEnabled(enabled) {
                [klinikRoluWrap, klinikEkAlanlar, klinikYetkilerAlani].forEach(function(el) {
                    if (!el) return;
                    el.style.opacity = enabled ? '' : '0.45';
                    el.style.pointerEvents = enabled ? '' : 'none';
                });
            }

            function syncYetkiLock() {
                if (!klinikYetkilerAlani || !klinikRoluSelect) return;
                const rol = klinikRoluSelect.value;
                const ownerLike = (rol === 'sahip' || rol === 'ortak');
                if (ownerLike) {
                    klinikYetkilerAlani.classList.add('opacity-50', 'pointer-events-none');
                    klinikYetkilerAlani.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                        cb.checked = true;
                    });
                } else {
                    klinikYetkilerAlani.classList.remove('opacity-50', 'pointer-events-none');
                }
            }

            function syncKlinikSection() {
                const hasKlinik = klinikIdSelect && klinikIdSelect.value !== '';
                setKlinikFieldsEnabled(hasKlinik);
                if (hasKlinik) {
                    syncYetkiLock();
                    // Kliniğe bağlarken hesap türünü klinik etiketine çek (manuel değiştirilebilir)
                    if (turSelectForKlinik && turSelectForKlinik.value === 'bireysel') {
                        // Zorlamıyoruz; sadece kullanıcıya görsel ipucu
                    }
                }
            }

            if (klinikIdSelect) {
                klinikIdSelect.addEventListener('change', function() {
                    syncKlinikSection();
                    // Bağlantı kaldırılırsa tur'u bireysel öner
                    if (!this.value && turSelectForKlinik) {
                        turSelectForKlinik.value = 'bireysel';
                        if (klinikAdiWrap) klinikAdiWrap.style.display = 'none';
                    }
                    if (this.value && turSelectForKlinik) {
                        turSelectForKlinik.value = 'klinik';
                        if (klinikAdiWrap) klinikAdiWrap.style.display = '';
                    }
                });
            }
            if (klinikRoluSelect) {
                klinikRoluSelect.addEventListener('change', syncYetkiLock);
            }
            syncKlinikSection();

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
                    
                    ilSelect.empty().append('<option value="">İl Seçin...</option>');
                    
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
                ilceSelect.empty().append('<option value="">İlçe Seçin...</option>');
                
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
