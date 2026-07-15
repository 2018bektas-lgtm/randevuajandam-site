@extends('hekim.layout')
@section('baslik', 'Randevu Takvimi - Hekim Paneli')
@section('sayfa_baslik', 'Haftalık Randevu Takvimi')
@section('icerik')

<!-- FullCalendar & Select2 CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

@php
    $minHour = '08:00:00';
    $maxHour = '20:00:00';
    if ($calismaSaatleri && $calismaSaatleri->count() > 0) {
        $baslangiclar = [];
        $bitisler = [];
        foreach ($calismaSaatleri as $cs) {
            if ($cs->aktif_mi) {
                if ($cs->mesai_baslangic) {
                    $baslangiclar[] = $cs->mesai_baslangic;
                }
                if ($cs->mesai_bitis) {
                    $bitisler[] = $cs->mesai_bitis;
                }
            }
        }
        if (count($baslangiclar) > 0) {
            sort($baslangiclar);
            $minHour = $baslangiclar[0];
            if (strlen($minHour) === 5) {
                $minHour .= ':00';
            }
        }
        if (count($bitisler) > 0) {
            rsort($bitisler);
            $maxHour = $bitisler[0];
            if (strlen($maxHour) === 5) {
                $maxHour .= ':00';
            }
        }
    }

    $slotDurationString = '00:30:00';
    if (isset($periyot) && $periyot > 0) {
        $slotDurationString = '00:' . str_pad($periyot, 2, '0', STR_PAD_LEFT) . ':00';
    }

    // Business hours mapping for FullCalendar (1=Monday...7=Sunday to 1=Monday...0=Sunday)
    $businessHours = [];
    if ($calismaSaatleri && $calismaSaatleri->count() > 0) {
        foreach ($calismaSaatleri as $cs) {
            if ($cs->aktif_mi) {
                $fcDay = $cs->gun === 7 ? 0 : $cs->gun;
                $businessHours[] = [
                    'daysOfWeek' => [$fcDay],
                    'startTime' => substr($cs->mesai_baslangic, 0, 5),
                    'endTime' => substr($cs->mesai_bitis, 0, 5)
                ];
            }
        }
    } else {
        $businessHours = [
            [
                'daysOfWeek' => [1, 2, 3, 4, 5],
                'startTime' => '09:00',
                'endTime' => '17:00'
            ]
        ];
    }
@endphp

<style>
    /* FullCalendar Premium Custom Styling */
    .fc {
        font-family: 'Inter', sans-serif;
    }
    .fc-toolbar-title {
        font-family: 'Outfit', sans-serif !important;
        font-weight: 700 !important;
        color: #111827 !important;
        font-size: 1.25rem !important;
    }
    .fc-button-primary {
        background-color: #ffffff !important;
        border-color: #E5E7EB !important;
        color: #4B5563 !important;
        border-radius: 12px !important;
        font-weight: 600 !important;
        font-family: 'Outfit', sans-serif !important;
        font-size: 0.75rem !important;
        padding: 8px 16px !important;
        transition: all 0.2s ease-in-out !important;
        text-transform: capitalize !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
    }
    .fc-button-primary:hover {
        background-color: #F9FAFB !important;
        color: #111827 !important;
        border-color: #D1D5DB !important;
    }
    .fc-button-active {
        background-color: #C96A2B !important;
        border-color: #C96A2B !important;
        color: #ffffff !important;
    }
    .fc-button-active:hover {
        background-color: #B55A20 !important;
        border-color: #B55A20 !important;
        color: #ffffff !important;
    }
    .fc-today-button {
        background-color: #1F2937 !important;
        border-color: #1F2937 !important;
        color: #ffffff !important;
    }
    .fc-today-button:hover {
        background-color: #111827 !important;
        border-color: #111827 !important;
        color: #ffffff !important;
    }
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: #F3F4F6 !important;
    }
    .fc-col-header-cell {
        background-color: #FAFAFA !important;
        padding: 12px 0 !important;
        border-bottom: 2px solid #E5E7EB !important;
    }
    .fc-col-header-cell-cushion {
        font-family: 'Outfit', sans-serif !important;
        font-weight: 600 !important;
        color: #1F2937 !important;
        font-size: 13px !important;
        text-decoration: none !important;
    }

    /* Clickable working hours */
    .fc-daygrid-day, .fc-timegrid-col {
        background-color: #ffffff;
    }

    /* Distinguish Non-business hours */
    .fc-non-business {
        background-color: #FAFAFA !important;
        background-image: repeating-linear-gradient(45deg, transparent, transparent 8px, rgba(243, 244, 246, 0.6) 8px, rgba(243, 244, 246, 0.6) 16px) !important;
        opacity: 0.95 !important;
        cursor: not-allowed !important;
    }

    .fc-day-today {
        background-color: rgba(201, 106, 43, 0.02) !important;
    }
    .fc-day-today .fc-col-header-cell-cushion {
        color: #C96A2B !important;
        font-weight: 700 !important;
    }

    /* Layout period slots height & separation */
    .fc-timegrid-slot {
        height: 55px !important; /* Premium luxury spacing */
        border-bottom: 1px solid #F9FAFB !important;
    }
    .fc-timegrid-slot-minor {
        border-top-style: dashed !important;
        border-top-color: #F3F4F6 !important;
    }

    .fc-timegrid-slot-label-cushion {
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #4B5563 !important;
    }

    /* Events visual styling */
    .fc-event {
        border-radius: 10px !important;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .fc-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(31, 41, 55, 0.08) !important;
    }
    .fc-v-event .fc-event-main {
        padding: 6px 8px !important;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        height: 100%;
        gap: 2px;
    }
    .fc-event-time {
        font-size: 9px !important;
        font-weight: 700 !important;
        margin-bottom: 1px;
    }
    .fc-event-title {
        font-size: 11px !important;
        font-weight: 600 !important;
        line-height: 1.3;
    }
    .fc-list-event {
        cursor: pointer !important;
    }

    /* Select2 Premium Override */
    .select2-container--default .select2-selection--single {
        height: 44px !important;
        border-radius: 14px !important;
        border: 1px solid #E5E7EB !important;
        display: flex !important;
        align-items: center !important;
        transition: border-color 0.2s;
    }
    .select2-container--default .select2-selection--single:focus-within {
        border-color: #C96A2B !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1F2937 !important;
        font-size: 13px !important;
        font-family: 'Inter', sans-serif !important;
        padding-left: 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
    }
    .select2-dropdown {
        border-radius: 14px !important;
        border-color: #E5E7EB !important;
        box-shadow: 0 10px 30px rgba(31, 41, 55, 0.08) !important;
        overflow: hidden !important;
        z-index: 99999 !important;
    }
    .select2-results__option {
        font-size: 13px !important;
        padding: 10px 12px !important;
        font-family: 'Inter', sans-serif !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #C96A2B !important;
        color: #ffffff !important;
    }
</style>

<div class="space-y-6">
    <!-- Visual Legend (Takvim Rehberi) & Slot Selector -->
    <div class="bg-white p-5 rounded-2xl border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.02)] flex flex-col lg:flex-row lg:items-center justify-between gap-4 text-xs text-[#4B5563]">
        <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
            <span class="font-bold text-[#111827] font-display">Takvim Rehberi:</span>
            <span class="flex items-center gap-2">
                <span class="w-4 h-4 rounded bg-white border border-[#E5E7EB] block"></span>
                Eklenebilir (Mesai Saatleri)
            </span>
            <span class="flex items-center gap-2">
                <span class="w-4 h-4 rounded block" style="background-color: #F8F8F7; background-image: repeating-linear-gradient(45deg, transparent, transparent 5px, rgba(229, 231, 235, 0.4) 5px, rgba(229, 231, 235, 0.4) 10px); border: 1px solid #E5E7EB;"></span>
                Eklenemez (Mesai Dışı)
            </span>
            <span class="flex items-center gap-2">
                <span class="w-4 h-4 rounded block bg-[#FEF3C7] border border-[#FCD34D]"></span>
                Öğle Arası
            </span>
            <span class="flex items-center gap-2">
                <span class="w-4 h-4 rounded block bg-[#EF4444]"></span>
                İzin / Tatil
            </span>
            <span class="flex items-center gap-2">
                <span class="w-4 h-4 rounded block bg-[#F5F5F4]" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 4px, rgba(229, 231, 235, 0.3) 4px, rgba(229, 231, 235, 0.3) 8px); border: 1px solid #E5E7EB;"></span>
                Geçmiş Zaman (Eklenemez)
            </span>
        </div>
        <div class="flex items-center gap-2 border-t lg:border-t-0 pt-3 lg:pt-0 border-slate-100 shrink-0 flex-wrap">
            <span class="font-bold text-[#111827] font-display flex items-center gap-1">⏱️ Zaman Dilimi (Periyot):</span>
            <select id="calendarSlotDurationSelect" onchange="changeCalendarSlotDuration(this.value)" class="px-3 py-1.5 rounded-xl border border-[#E5E7EB] bg-white font-semibold text-[#4B5563] focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] outline-none cursor-pointer text-xs">
                <option value="00:15:00" {{ (isset($periyot) && $periyot == 15) ? 'selected' : '' }}>15 Dakika</option>
                <option value="00:20:00" {{ (isset($periyot) && $periyot == 20) ? 'selected' : '' }}>20 Dakika</option>
                <option value="00:30:00" {{ (!isset($periyot) || $periyot == 30) ? 'selected' : '' }}>30 Dakika</option>
                <option value="00:45:00" {{ (isset($periyot) && $periyot == 45) ? 'selected' : '' }}>45 Dakika</option>
                <option value="01:00:00" {{ (isset($periyot) && $periyot == 60) ? 'selected' : '' }}>60 Dakika</option>
            </select>
            <a href="{{ route('hekim.randevu.ical') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[#E5E7EB] bg-white text-xs font-bold text-[#4B5563] hover:border-[#C96A2B] hover:text-[#C96A2B] transition"
               title="Google / Outlook takvimine aktar (.ics)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                iCal indir
            </a>
        </div>
    </div>

    <!-- Calendar Container matching panel style -->
    <div class="bg-white rounded-2xl p-6 border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)]">
        <!-- FullCalendar Hook -->
        <div id="calendar" class="w-full"></div>
    </div>
</div>

<!-- Randevu Detay Modalı -->
<div id="appointmentDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
    <div id="appointmentDetailContainer" class="bg-white rounded-3xl border border-[#E5E7EB] shadow-[0_25px_60px_-15px_rgba(31,41,55,0.2)] max-w-lg w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-[#E5E7EB] flex items-center justify-between bg-white">
            <h3 class="text-base font-bold font-display text-[#111827]">Randevu Detayları</h3>
            <button onclick="closeDetailModal()" class="text-[#6B7280] hover:text-[#1F2937] cursor-pointer outline-none transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <!-- Modal Body -->
        <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
            <!-- Patient Info Card -->
            <div class="bg-[#F5F5F4]/60 p-5 rounded-2xl border border-[#E5E7EB] relative overflow-hidden">
                <div class="absolute right-4 top-4 text-slate-200">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                    </svg>
                </div>
                <span class="block text-[10px] font-bold text-[#C96A2B] uppercase tracking-widest font-display mb-1">Danışan Bilgileri</span>
                <span id="detailDanisanAd" class="block text-lg font-bold text-[#111827]">-</span>
                <div class="mt-3 flex flex-wrap gap-x-6 gap-y-1.5 text-xs text-[#4B5563]">
                    <span id="detailDanisanTel" class="flex items-center gap-1.5">
                        <span class="text-[#6B7280]">📞</span> -
                    </span>
                    <span id="detailDanisanEmail" class="flex items-center gap-1.5">
                        <span class="text-[#6B7280]">✉️</span> -
                    </span>
                </div>
            </div>

            <!-- Randevu Info Grid -->
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <span class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display mb-1">Hizmet / Tedavi</span>
                    <div class="px-4 py-3 rounded-xl bg-slate-50 border border-[#E5E7EB]">
                        <span id="detailHizmetAd" class="block text-xs font-semibold text-[#1F2937]">-</span>
                    </div>
                </div>

                <div>
                    <span class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display mb-1">Tarih</span>
                    <div class="px-4 py-3 rounded-xl bg-slate-50 border border-[#E5E7EB] flex items-center gap-2">
                        <span class="text-xs">📅</span>
                        <span id="detailTarih" class="text-xs font-semibold text-[#1F2937]">-</span>
                    </div>
                </div>

                <div>
                    <span class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display mb-1">Saat</span>
                    <div class="px-4 py-3 rounded-xl bg-slate-50 border border-[#E5E7EB] flex items-center gap-2">
                        <span class="text-xs">⏱️</span>
                        <span id="detailSaat" class="text-xs font-semibold text-[#1F2937]">-</span>
                    </div>
                </div>

                <div class="col-span-2">
                    <span class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display mb-1">Danışan Notu</span>
                    <p id="detailDanisanNot" class="text-xs text-[#4B5563] bg-[#FAFAFA] p-3 rounded-xl border border-[#E5E7EB] italic leading-relaxed">-</p>
                </div>

                <!-- Durum / Status Badge -->
                <div>
                    <span class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display mb-1">Randevu Durumu</span>
                    <span id="detailDurumBadge" class="inline-block mt-0.5 px-3 py-1 rounded-full text-xs font-bold">-</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display mb-1">Görüşme</span>
                    <span id="detailGorusmeTipi" class="inline-block mt-0.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-50 text-slate-600 border border-slate-200">Yüz yüze</span>
                </div>
            </div>

            <!-- Online join -->
            <div id="detailOnlineJoinBox" class="hidden rounded-2xl border border-sky-100 bg-sky-50/70 p-4 space-y-2">
                <p class="text-xs font-bold text-sky-900 font-display">📹 Online görüşme (platform)</p>
                <p class="text-[11px] text-sky-800 leading-relaxed">Zoom linki yok — randevu saatinde sitemiz üzerinden katılın. Oda randevu saatine yakın aktif olur.</p>
                <a id="detailJoinLink" href="#" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold font-display transition">
                    Görüşmeye Katıl →
                </a>
            </div>

            <!-- Hekim Notu Section -->
            <div class="border-t border-[#E5E7EB] pt-4 space-y-2">
                <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Hekim Notu / Takip Notları</label>
                <textarea id="detailHekimNotuInput" class="w-full text-xs p-3.5 border border-[#E5E7EB] rounded-xl focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] outline-none transition-colors" rows="3" placeholder="Randevuya dair takip notlarınızı buraya yazabilirsiniz..."></textarea>
            </div>
        </div>
        <!-- Modal Footer -->
        <div class="px-6 py-4 bg-slate-50 border-t border-[#E5E7EB] flex flex-wrap items-center justify-between gap-3">
            <button onclick="deleteAppointmentFromDetail()" class="px-4 py-2.5 rounded-xl bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 text-xs font-bold font-display cursor-pointer transition-all flex items-center gap-1.5">
                🗑️ Randevuyu Sil
            </button>
            <div class="flex items-center gap-2">
                <!-- Status Actions -->
                <div class="relative inline-block text-left" id="statusDropdownContainer">
                    <button type="button" onclick="toggleStatusDropdown()" class="px-4 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-bold text-[#4B5563] cursor-pointer transition-all flex items-center gap-1.5 shadow-sm">
                        ⚙️ Durumu Güncelle
                    </button>
                    <!-- Dropdown Options -->
                    <div id="statusDropdownOptions" class="absolute right-0 bottom-full mb-2 w-44 bg-white border border-[#E5E7EB] rounded-xl shadow-xl py-1 hidden z-50">
                        <button onclick="updateStatus('onaylandi')" class="w-full text-left px-4 py-2.5 text-xs text-[#065f46] hover:bg-emerald-50 font-semibold transition-colors flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Onayla
                        </button>
                        <button onclick="updateStatus('tamamlandi')" class="w-full text-left px-4 py-2.5 text-xs text-[#1e40af] hover:bg-blue-50 font-semibold transition-colors flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> Tamamlandı
                        </button>
                        <button onclick="updateStatus('beklemede')" class="w-full text-left px-4 py-2.5 text-xs text-[#92400e] hover:bg-amber-50 font-semibold transition-colors flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Beklemeye Al
                        </button>
                        <button onclick="updateStatus('iptal')" class="w-full text-left px-4 py-2.5 text-xs text-[#991b1b] hover:bg-red-50 font-semibold transition-colors flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span> İptal Et
                        </button>
                    </div>
                </div>
                <button onclick="saveHekimNotuOnly()" class="px-4 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold font-display cursor-pointer transition-all shadow-md shadow-orange-500/10">
                    💾 Notu Kaydet
                </button>
                <button onclick="closeDetailModal()" class="px-4 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#6B7280] text-xs font-bold font-display cursor-pointer transition-all">
                    Kapat
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Randevu Ekleme Modalı -->
<div id="appointmentFormModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
    <div id="appointmentFormContainer" class="bg-white rounded-3xl border border-[#E5E7EB] shadow-[0_25px_60px_-15px_rgba(31,41,55,0.2)] max-w-lg w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-[#E5E7EB] flex items-center justify-between bg-white">
            <h3 class="text-base font-bold font-display text-[#111827]">Yeni Randevu Oluştur</h3>
            <button onclick="closeFormModal()" class="text-[#6B7280] hover:text-[#1F2937] cursor-pointer outline-none transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <!-- Modal Body -->
        <form id="appointmentForm" onsubmit="submitAppointmentForm(event)">
            @csrf
            <div class="p-6 space-y-4">
                <!-- Date/Time Display -->
                <div class="bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 p-4 rounded-2xl flex flex-col gap-1.5 text-xs font-semibold">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-1.5 text-[#C96A2B]">
                            📅 Seçilen Tarih & Saat:
                        </span>
                        <span id="formSelectedDateTime" class="font-bold text-[#1F2937]">-</span>
                    </div>
                    <div id="formHizmetSuresiContainer" class="flex items-center justify-between border-t border-[#E7B58A]/20 pt-2 hidden">
                        <span class="text-[#C96A2B]">⏱️ Tahmini Bitiş Saati (Süre):</span>
                        <span id="formSelectedEndTime" class="font-bold text-[#1F2937]">-</span>
                    </div>
                </div>

                <input type="hidden" name="tarih" id="formTarihInput">
                <input type="hidden" name="saat" id="formSaatInput">

                <!-- Hizmet Seçimi -->
                <div>
                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display mb-1.5">Hizmet / Tedavi *</label>
                    <select name="hizmet_id" id="formHizmetSelect" required class="w-full text-xs" style="width: 100%;">
                        <option value="">Hizmet Seçin</option>
                        @foreach($doktor->hizmetler as $hizmet)
                            <option value="{{ $hizmet->id }}" data-sure="{{ $hizmet->sure }}">{{ $hizmet->ad }} ({{ $hizmet->sure }} dk)</option>
                        @endforeach
                    </select>
                </div>

                <!-- Danışan Seçimi (Select2) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Danışan Seçin *</label>
                        <button type="button" onclick="openNewClientModal()" class="text-xs text-[#C96A2B] hover:text-[#B55A20] font-bold font-display cursor-pointer flex items-center gap-1">
                            ➕ Yeni Danışan Ekle
                        </button>
                    </div>
                    <select name="danisan_id" id="formDanisanSelect" required class="w-full text-xs" style="width: 100%;">
                        <option value="">Danışan ara (ad, e-posta veya telefon)...</option>
                    </select>
                </div>

                @php $onlineGorusmeAcik = $doktor->aktifPaket()?->hasFeature('online_gorusme'); @endphp
                @if($onlineGorusmeAcik)
                <div>
                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display mb-1.5">Görüşme tipi</label>
                    <div class="flex gap-3 text-xs">
                        <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-[#E5E7EB] cursor-pointer hover:border-[#C96A2B]/50">
                            <input type="radio" name="gorusme_tipi" value="yuz_yuze" checked class="text-[#C96A2B]">
                            <span class="font-semibold text-[#1F2937]">Yüz yüze</span>
                        </label>
                        <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-[#E5E7EB] cursor-pointer hover:border-sky-300">
                            <input type="radio" name="gorusme_tipi" value="online" class="text-sky-600">
                            <span class="font-semibold text-[#1F2937]">Online (platform)</span>
                        </label>
                    </div>
                    <p class="mt-1 text-[10px] text-[#6B7280]">Online randevu da aynı saati kilitler; Zoom linki yok, sitemiz üzerinden görüşme.</p>
                </div>
                @else
                <input type="hidden" name="gorusme_tipi" value="yuz_yuze">
                @endif

                <!-- Açıklama / Not -->
                <div>
                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display mb-1.5">Randevu Notu / Açıklama</label>
                    <textarea name="aciklama" id="formAciklamaInput" class="w-full text-xs p-3.5 border border-[#E5E7EB] rounded-xl focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] outline-none" rows="3" placeholder="Randevu ile ilgili doktor notları..."></textarea>
                </div>
            </div>
            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-[#E5E7EB] flex items-center justify-end gap-3">
                <button type="button" onclick="closeFormModal()" class="px-5 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#6B7280] text-xs font-bold font-display cursor-pointer transition-all">
                    İptal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold font-display cursor-pointer transition-all shadow-md shadow-orange-500/10">
                    Randevuyu Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Yeni Danışan Modalı -->
<div id="newClientModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
    <div id="newClientContainer" class="bg-white rounded-3xl border border-[#E5E7EB] shadow-[0_25px_60px_-15px_rgba(31,41,55,0.2)] max-w-sm w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-[#E5E7EB] flex items-center justify-between bg-white">
            <h3 class="text-base font-bold font-display text-[#111827]">Yeni Danışan Kaydı</h3>
            <button onclick="closeNewClientModal()" class="text-[#6B7280] hover:text-[#1F2937] cursor-pointer outline-none transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <!-- Modal Body -->
        <form id="newClientForm" onsubmit="submitNewClientForm(event)">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display mb-1.5">Ad Soyad *</label>
                    <input type="text" name="name" required class="w-full text-xs p-3.5 border border-[#E5E7EB] rounded-xl focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] outline-none" placeholder="Örn: Mehmet Can">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display mb-1.5">E-posta Adresi *</label>
                    <input type="email" name="email" required class="w-full text-xs p-3.5 border border-[#E5E7EB] rounded-xl focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] outline-none" placeholder="mehmetcan@email.com">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display mb-1.5">Telefon Numarası *</label>
                    <input type="tel" name="telefon" required class="w-full text-xs p-3.5 border border-[#E5E7EB] rounded-xl focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] outline-none" placeholder="0 (5xx) xxx xx xx">
                </div>
            </div>
            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-[#E5E7EB] flex items-center justify-end gap-3">
                <button type="button" onclick="closeNewClientModal()" class="px-5 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#6B7280] text-xs font-bold font-display cursor-pointer transition-all">
                    İptal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold font-display cursor-pointer transition-all shadow-md shadow-orange-500/10">
                    Oluştur
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/locales/tr.global.min.js"></script>

<script>
    var calendar;
    var currentEvent = null;

    // Time Slot Duration change handler
    function changeCalendarSlotDuration(duration) {
        if (calendar) {
            calendar.setOption('slotDuration', duration);
            calendar.setOption('snapDuration', duration);
            calendar.setOption('slotLabelInterval', duration);

            var minutes = parseInt(duration.split(':')[1]) || 30;

            fetch('{{ route("hekim.randevu.update-period") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    periyot: minutes
                })
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Zaman dilimi sunucuda güncellenemedi.');
                }
                return res.json();
            })
            .then(data => {
                toastAc(data.message, 'basarili');
            })
            .catch(err => {
                toastAc('Takvim yerelde güncellendi ancak veritabanına kaydedilemedi.', 'uyari');
            });
        }
    }

    // Dynamic end time indicator based on service selected
    function hizmetSecildi() {
        var select = document.getElementById('formHizmetSelect');
        var selectedOption = select.options[select.selectedIndex];
        var sureContainer = document.getElementById('formHizmetSuresiContainer');

        if (!selectedOption || !selectedOption.value) {
            sureContainer.classList.add('hidden');
            return;
        }

        var sure = parseInt(selectedOption.getAttribute('data-sure')) || 30;
        var startVal = document.getElementById('formSaatInput').value; // e.g. "10:00"

        if (startVal) {
            var timeParts = startVal.split(':');
            var hours = parseInt(timeParts[0]);
            var minutes = parseInt(timeParts[1]);

            var date = new Date();
            date.setHours(hours);
            date.setMinutes(minutes + sure);

            var endHours = String(date.getHours()).padStart(2, '0');
            var endMinutes = String(date.getMinutes()).padStart(2, '0');

            document.getElementById('formSelectedEndTime').innerText = `${endHours}:${endMinutes} (${sure} dk)`;
            sureContainer.classList.remove('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        // Dynamic config variables from PHP
        var minTime = '{{ $minHour }}';
        var maxTime = '{{ $maxHour }}';
        var slotDurationString = '{{ $slotDurationString }}';

        var businessHoursData = {!! json_encode($businessHours) !!};

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            locale: 'tr',
            firstDay: 1, // Pazartesi
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },

            // Turkish button overrides for premium feel
            buttonText: {
                today: 'Bugün',
                month: 'Aylık',
                week: 'Haftalık',
                day: 'Günlük',
                list: 'Ajanda'
            },

            // Active & Blocked constraints configuration
            businessHours: businessHoursData,
            selectConstraint: 'businessHours',
            eventConstraint: 'businessHours',
            selectOverlap: function(event) {
                return event.display !== 'background';
            },
            eventOverlap: function(stillEvent, movingEvent) {
                return stillEvent.display !== 'background';
            },

            slotDuration: slotDurationString,
            snapDuration: slotDurationString,
            slotLabelInterval: slotDurationString,
            slotLabelFormat: {
                hour: '2-digit',
                minute: '2-digit',
                omitZeroMinute: false,
                meridiem: false
            },
            slotMinTime: minTime,
            slotMaxTime: maxTime,
            allDaySlot: false,
            editable: true,
            selectable: true,
            selectMirror: true,
            eventDurationEditable: false,
            eventSources: [
                {
                    url: '{{ route("hekim.randevu.takvim.events") }}'
                },
                {
                    events: function(info, successCallback, failureCallback) {
                        var now = new Date();
                        var start = info.start;
                        var end = info.end;

                        if (now > start) {
                            var pastEnd = now;
                            if (now > end) {
                                pastEnd = end;
                            }
                            successCallback([
                                {
                                    id: 'past-time-shading',
                                    start: start.toISOString(),
                                    end: pastEnd.toISOString(),
                                    display: 'background',
                                    extendedProps: {
                                        type: 'gecmis'
                                    }
                                }
                            ]);
                        } else {
                            successCallback([]);
                        }
                    }
                }
            ],

            // Custom background event rendering for lunch breaks and holidays
            eventDidMount: function(info) {
                if (info.event.display === 'background') {
                    if (info.event.extendedProps.type === 'ogle') {
                        info.el.style.backgroundImage = 'repeating-linear-gradient(45deg, transparent, transparent 6px, rgba(201, 106, 43, 0.08) 6px, rgba(201, 106, 43, 0.08) 12px)';
                        info.el.style.backgroundColor = '#FFF7ED';
                        info.el.style.borderLeft = '4px solid #E7B58A';
                        info.el.style.opacity = '1';
                        info.el.style.display = 'flex';
                        info.el.style.alignItems = 'center';
                        info.el.style.justifyContent = 'center';
                        info.el.innerHTML = '<div style="font-size:10px;font-weight:700;color:#C96A2B;font-family:\'Outfit\',sans-serif;letter-spacing:0.05em;text-align:center;">🍽️ ÖĞLE ARASI</div>';
                    } else if (info.event.extendedProps.type === 'izin') {
                        info.el.style.backgroundImage = 'repeating-linear-gradient(45deg, transparent, transparent 6px, rgba(229, 231, 235, 0.3) 6px, rgba(229, 231, 235, 0.3) 12px)';
                        info.el.style.backgroundColor = '#F5F5F4';
                        info.el.style.borderLeft = '4px solid #9CA3AF';
                        info.el.style.opacity = '1';
                        info.el.style.display = 'flex';
                        info.el.style.alignItems = 'center';
                        info.el.style.justifyContent = 'center';
                        info.el.innerHTML = '<div style="font-size:10px;font-weight:700;color:#6B7280;font-family:\'Outfit\',sans-serif;letter-spacing:0.05em;text-align:center;">🔒 KAPALI SÜREÇ</div>';
                    } else if (info.event.extendedProps.type === 'gecmis') {
                        info.el.style.backgroundImage = 'repeating-linear-gradient(45deg, transparent, transparent 8px, rgba(229, 231, 235, 0.15) 8px, rgba(229, 231, 235, 0.15) 16px)';
                        info.el.style.backgroundColor = '#F5F5F4';
                        info.el.style.opacity = '0.9';
                        info.el.style.cursor = 'not-allowed';
                    }
                } else if (info.event.extendedProps.type === 'randevu') {
                    var durum = info.event.extendedProps.durum;
                    info.el.style.borderRadius = '10px';
                    info.el.style.border = 'none';
                    info.el.style.boxShadow = '0 2px 8px rgba(31, 41, 55, 0.04)';

                    let bgColor, borderColor, textColor;
                    if (durum === 'onaylandi') {
                        bgColor = 'rgba(16, 185, 129, 0.09)';
                        borderColor = '#10B981';
                        textColor = '#065F46';
                    } else if (durum === 'tamamlandi') {
                        bgColor = 'rgba(59, 130, 246, 0.09)';
                        borderColor = '#3B82F6';
                        textColor = '#1E40AF';
                    } else if (durum === 'iptal') {
                        bgColor = 'rgba(239, 68, 68, 0.09)';
                        borderColor = '#EF4444';
                        textColor = '#991B1B';
                    } else { // beklemede
                        bgColor = 'rgba(201, 106, 43, 0.09)';
                        borderColor = '#C96A2B';
                        textColor = '#92400E';
                    }

                    info.el.style.backgroundColor = bgColor;
                    info.el.style.borderLeft = '4px solid ' + borderColor;
                    info.el.style.color = textColor;

                    // Style nested elements if they exist
                    var titleEl = info.el.querySelector('.fc-event-title');
                    var timeEl = info.el.querySelector('.fc-event-time');
                    if (titleEl) {
                        titleEl.style.color = textColor;
                        titleEl.style.fontWeight = '600';
                    }
                    if (timeEl) {
                        timeEl.style.color = textColor;
                        timeEl.style.fontWeight = '700';
                        timeEl.style.opacity = '0.85';
                    }
                }
            },

            // Event Click Handler
            eventClick: function(info) {
                if (info.event.extendedProps.type === 'randevu') {
                    showAppointmentDetail(info.event);
                }
            },

            // Date Select Handler (Create New Appointment)
            select: function(info) {
                if (new Date(info.start) < new Date()) {
                    mesajModalAc('Geçmiş bir tarihe veya saate randevu eklenemez.', 'uyari');
                    calendar.unselect();
                    return;
                }
                createNewAppointment(info.startStr, info.endStr);
            },

            // Event Drop (Drag and Drop Reschedule Handler)
            eventDrop: function(info) {
                rescheduleAppointment(info);
            }
        });

        calendar.render();

        // Initialize Select2 Patient search
        $('#formDanisanSelect').select2({
            ajax: {
                url: '{{ route("hekim.randevu.hastalar-ara") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            placeholder: 'Danışan ara (ad, e-posta veya telefon)...',
            minimumInputLength: 2,
            dropdownParent: $('#appointmentFormModal')
        });

        // Initialize Select2 Service selection
        $('#formHizmetSelect').select2({
            placeholder: 'Hizmet Seçin',
            dropdownParent: $('#appointmentFormModal')
        }).on('change', function() {
            hizmetSecildi();
        });

        // Setup Close Modal clicking outside
        document.getElementById('appointmentDetailModal').addEventListener('click', function(e) {
            if (e.target === this) closeDetailModal();
        });
        document.getElementById('appointmentFormModal').addEventListener('click', function(e) {
            if (e.target === this) closeFormModal();
        });
        document.getElementById('newClientModal').addEventListener('click', function(e) {
            if (e.target === this) closeNewClientModal();
        });
    });

    // --- Drag and Drop Rescheduling ---
    function rescheduleAppointment(info) {
        var event = info.event;
        var appointmentId = event.id.replace('randevu_', '');
        var newStart = event.start;

        var localYear = newStart.getFullYear();
        var localMonth = String(newStart.getMonth() + 1).padStart(2, '0');
        var localDay = String(newStart.getDate()).padStart(2, '0');
        var localHours = String(newStart.getHours()).padStart(2, '0');
        var localMinutes = String(newStart.getMinutes()).padStart(2, '0');

        var formattedDate = `${localYear}-${localMonth}-${localDay}`;
        var formattedTime = `${localHours}:${localMinutes}`;

        fetch(`/hekim/randevular/${appointmentId}/reschedule`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                tarih: formattedDate,
                saat: formattedTime
            })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(data => {
                    throw new Error(data.message || 'Randevu taşınamadı.');
                });
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                toastAc(data.message, 'basarili');
            } else {
                mesajModalAc(data.message, 'hata');
                info.revert();
            }
        })
        .catch(err => {
            mesajModalAc(err.message || 'Bir hata oluştu.', 'hata');
            info.revert();
        });
    }

    // --- Show Details Modal ---
    function showAppointmentDetail(event) {
        currentEvent = event;
        var props = event.extendedProps;
        var randevu = props.randevu;

        document.getElementById('detailDanisanAd').innerText = props.hasta_ad || (randevu.ad + ' ' + randevu.soyad);
        document.getElementById('detailDanisanTel').innerHTML = '<span class="text-[#6B7280]">📞</span> ' + (randevu.telefon || 'Telefon yok');
        document.getElementById('detailDanisanEmail').innerHTML = '<span class="text-[#6B7280]">✉️</span> ' + (randevu.e_posta || 'E-posta yok');
        document.getElementById('detailHizmetAd').innerText = props.hizmet_ad || 'Genel Hizmet';

        var startDate = new Date(event.start);
        var dateStr = startDate.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
        var timeStr = String(startDate.getHours()).padStart(2, '0') + ':' + String(startDate.getMinutes()).padStart(2, '0');

        document.getElementById('detailTarih').innerText = dateStr;
        document.getElementById('detailSaat').innerText = timeStr;
        document.getElementById('detailDanisanNot').innerText = randevu.not || 'Not bırakılmamış.';
        document.getElementById('detailHekimNotuInput').value = randevu.hekim_notu || '';

        var durum = props.durum;
        var badge = document.getElementById('detailDurumBadge');
        badge.innerText = durum.toUpperCase();
        badge.className = 'inline-block mt-0.5 px-3 py-1 rounded-full text-xs font-bold ';

        if (durum === 'onaylandi') {
            badge.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
            badge.innerText = 'ONAYLANDI';
        } else if (durum === 'tamamlandi') {
            badge.classList.add('bg-blue-50', 'text-blue-700', 'border', 'border-blue-200');
            badge.innerText = 'TAMAMLANDI';
        } else if (durum === 'iptal') {
            badge.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-200');
            badge.innerText = 'İPTAL EDİLDİ';
        } else {
            badge.classList.add('bg-amber-50', 'text-amber-700', 'border', 'border-amber-200');
            badge.innerText = 'ONAY BEKLİYOR';
        }

        var gorusmeTipi = props.gorusme_tipi || (randevu && randevu.gorusme_tipi) || 'yuz_yuze';
        var gorusmeBadge = document.getElementById('detailGorusmeTipi');
        if (gorusmeBadge) {
            if (gorusmeTipi === 'online') {
                gorusmeBadge.innerText = 'ONLINE';
                gorusmeBadge.className = 'inline-block mt-0.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200';
            } else {
                gorusmeBadge.innerText = 'YÜZ YÜZE';
                gorusmeBadge.className = 'inline-block mt-0.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-50 text-slate-600 border border-slate-200';
            }
        }
        var joinBox = document.getElementById('detailOnlineJoinBox');
        var joinLink = document.getElementById('detailJoinLink');
        var randevuId = event.id ? String(event.id).replace('randevu_', '') : null;
        var hekimJoinUrl = randevuId ? ('{{ url('/hekim/gorusme') }}/' + randevuId) : null;
        var joinUrl = hekimJoinUrl || props.platform_join_url || null;
        if (joinBox && joinLink) {
            joinLink.classList.remove('opacity-50', 'pointer-events-none');
            joinLink.textContent = 'Görüşmeye Katıl →';
            if (gorusmeTipi === 'online' && durum === 'onaylandi' && joinUrl) {
                joinBox.classList.remove('hidden');
                joinLink.href = joinUrl;
            } else if (gorusmeTipi === 'online' && durum === 'beklemede') {
                joinBox.classList.remove('hidden');
                joinLink.href = '#';
                joinLink.classList.add('opacity-50', 'pointer-events-none');
                joinLink.textContent = 'Onay sonrası katılım linki oluşur';
            } else {
                joinBox.classList.add('hidden');
            }
        }

        var modal = document.getElementById('appointmentDetailModal');
        var container = document.getElementById('appointmentDetailContainer');
        modal.classList.remove('hidden');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 50);
    }

    function closeDetailModal() {
        var modal = document.getElementById('appointmentDetailModal');
        var container = document.getElementById('appointmentDetailContainer');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.getElementById('statusDropdownOptions').classList.add('hidden');
        }, 300);
    }

    // --- Status Actions ---
    function toggleStatusDropdown() {
        document.getElementById('statusDropdownOptions').classList.toggle('hidden');
    }

    function updateStatus(newStatus) {
        if (!currentEvent) return;
        var appointmentId = currentEvent.id.replace('randevu_', '');
        var hekimNotu = document.getElementById('detailHekimNotuInput').value;

        fetch(`/hekim/randevular/${appointmentId}/durum`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                durum: newStatus,
                hekim_notu: hekimNotu
            })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(data => {
                    throw new Error(data.message || 'Durum güncellenemedi.');
                });
            }
            return res.json();
        })
        .then(data => {
            toastAc(data.message, 'basarili');
            closeDetailModal();
            calendar.refetchEvents();
        })
        .catch(err => {
            mesajModalAc(err.message || 'Bir hata oluştu.', 'hata');
        });
    }

    function saveHekimNotuOnly() {
        if (!currentEvent) return;
        var appointmentId = currentEvent.id.replace('randevu_', '');
        var hekimNotu = document.getElementById('detailHekimNotuInput').value;
        var props = currentEvent.extendedProps;

        fetch(`/hekim/randevular/${appointmentId}/durum`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                durum: props.durum,
                hekim_notu: hekimNotu
            })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(data => {
                    throw new Error(data.message || 'Not kaydedilemedi.');
                });
            }
            return res.json();
        })
        .then(data => {
            toastAc('Hekim notu başarıyla güncellendi.', 'basarili');
            closeDetailModal();
            calendar.refetchEvents();
        })
        .catch(err => {
            mesajModalAc(err.message || 'Bir hata oluştu.', 'hata');
        });
    }

    // --- Delete Appointment ---
    function deleteAppointmentFromDetail() {
        if (!currentEvent) return;
        var appointmentId = currentEvent.id.replace('randevu_', '');

        onayModalAc(null, null, 'Bu randevuyu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.', function() {
            fetch(`/hekim/randevular/${appointmentId}/sil`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(data => {
                        throw new Error(data.message || 'Randevu silinemedi.');
                    });
                }
                return res.json();
            })
            .then(data => {
                toastAc(data.message, 'basarili');
                closeDetailModal();
                calendar.refetchEvents();
            })
            .catch(err => {
                mesajModalAc(err.message || 'Bir hata oluştu.', 'hata');
            });
        });
    }

    // --- Create Appointment Form ---
    function createNewAppointment(startStr, endStr) {
        var datePart = startStr.substring(0, 10);
        var timePart = startStr.indexOf('T') !== -1 ? startStr.substring(11, 16) : '09:00';

        document.getElementById('formTarihInput').value = datePart;
        document.getElementById('formSaatInput').value = timePart;

        var parts = datePart.split('-');
        var formattedDate = `${parts[2]}.${parts[1]}.${parts[0]}`;
        document.getElementById('formSelectedDateTime').innerText = `${formattedDate} - ${timePart}`;

        $('#formHizmetSelect').val('').trigger('change');
        document.getElementById('formHizmetSuresiContainer').classList.add('hidden');
        $('#formDanisanSelect').val(null).trigger('change');
        document.getElementById('formAciklamaInput').value = '';
        var gorusmeYuz = document.querySelector('input[name="gorusme_tipi"][value="yuz_yuze"]');
        if (gorusmeYuz) gorusmeYuz.checked = true;

        var modal = document.getElementById('appointmentFormModal');
        var container = document.getElementById('appointmentFormContainer');
        modal.classList.remove('hidden');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 50);
    }

    function closeFormModal() {
        var modal = document.getElementById('appointmentFormModal');
        var container = document.getElementById('appointmentFormContainer');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function submitAppointmentForm(e) {
        e.preventDefault();

        var form = document.getElementById('appointmentForm');
        var formData = new FormData(form);
        var jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        fetch('{{ route("hekim.randevu.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(jsonData)
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(data => {
                    var errorMsg = 'Randevu oluşturulamadı.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    } else if (data.message) {
                        errorMsg = data.message;
                    }
                    throw new Error(errorMsg);
                });
            }
            return res.json();
        })
        .then(data => {
            toastAc(data.message, 'basarili');
            closeFormModal();
            calendar.refetchEvents();
        })
        .catch(err => {
            mesajModalAc(err.message || 'Bir hata oluştu.', 'hata');
        });
    }

    // --- New Patient Registration ---
    function openNewClientModal() {
        var modal = document.getElementById('newClientModal');
        var container = document.getElementById('newClientContainer');

        document.getElementById('newClientForm').reset();

        modal.classList.remove('hidden');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 50);
    }

    function closeNewClientModal() {
        var modal = document.getElementById('newClientModal');
        var container = document.getElementById('newClientContainer');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // --- Patient form submit --
    function submitNewClientForm(e) {
        e.preventDefault();

        var form = document.getElementById('newClientForm');
        var formData = new FormData(form);
        var jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        fetch('{{ route("hekim.randevu.hasta-ekle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(jsonData)
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(data => {
                    var errorMsg = 'Danışan eklenemedi.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    } else if (data.message) {
                        errorMsg = data.message;
                    }
                    throw new Error(errorMsg);
                });
            }
            return res.json();
        })
        .then(data => {
            toastAc(data.message, 'basarili');

            // Append newly created patient directly to Select2
            var newOption = new Option(data.danisan.name + ' (' + data.danisan.email + ')', data.danisan.id, true, true);
            $('#formDanisanSelect').append(newOption).trigger('change');

            closeNewClientModal();
        })
        .catch(err => {
            mesajModalAc(err.message || 'Bir hata oluştu.', 'hata');
        });
    }
</script>

@endsection
