@extends('layouts.personel')

@section('baslik', 'Randevu Takvimi - Personel Paneli')
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
    .fc { font-family: 'Inter', sans-serif; }
    .fc-toolbar-title { font-family: 'Outfit', sans-serif !important; font-weight: 700 !important; color: #111827 !important; font-size: 1.25rem !important; }
    .fc-button-primary { background-color: #ffffff !important; border-color: #E5E7EB !important; color: #4B5563 !important; border-radius: 12px !important; font-weight: 600 !important; font-family: 'Outfit', sans-serif !important; font-size: 0.75rem !important; padding: 8px 16px !important; transition: all 0.2s ease-in-out !important; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important; }
    .fc-button-primary:hover { background-color: #F9FAFB !important; color: #111827 !important; border-color: #D1D5DB !important; }
    .fc-button-active { background-color: #C96A2B !important; border-color: #C96A2B !important; color: #ffffff !important; }
    .fc-button-active:hover { background-color: #B55A20 !important; border-color: #B55A20 !important; color: #ffffff !important; }
    .fc-today-button { background-color: #1F2937 !important; border-color: #1F2937 !important; color: #ffffff !important; }
    .fc-today-button:hover { background-color: #111827 !important; border-color: #111827 !important; color: #ffffff !important; }
    .fc-theme-standard td, .fc-theme-standard th { border-color: #F3F4F6 !important; }
    .fc-col-header-cell { background-color: #FAFAFA !important; padding: 12px 0 !important; border-bottom: 2px solid #E5E7EB !important; }
    .fc-col-header-cell-cushion { font-family: 'Outfit', sans-serif !important; font-weight: 600 !important; color: #1F2937 !important; font-size: 13px !important; text-decoration: none !important; }
    .fc-daygrid-day, .fc-timegrid-col { background-color: #ffffff; }
    .fc-non-business { background-color: #FAFAFA !important; background-image: repeating-linear-gradient(45deg, transparent, transparent 8px, rgba(243, 244, 246, 0.6) 8px, rgba(243, 244, 246, 0.6) 16px) !important; opacity: 0.95 !important; cursor: not-allowed !important; }
    .fc-day-today { background-color: rgba(201, 106, 43, 0.02) !important; }
    .fc-day-today .fc-col-header-cell-cushion { color: #C96A2B !important; font-weight: 700 !important; }
    .fc-timegrid-slot { height: 55px !important; border-bottom: 1px solid #F9FAFB !important; }
    .fc-timegrid-slot-minor { border-top-style: dashed !important; border-top-color: #F3F4F6 !important; }
    .fc-timegrid-slot-label-cushion { font-size: 11px !important; font-weight: 600 !important; color: #4B5563 !important; }
    .fc-event { border-radius: 10px !important; cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease; }
    .fc-event:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(31, 41, 55, 0.08) !important; }
    .fc-v-event .fc-event-main { padding: 6px 8px !important; display: flex; flex-direction: column; justify-content: flex-start; height: 100%; gap: 2px; }
    .fc-event-time { font-size: 9px !important; font-weight: 700 !important; margin-bottom: 1px; }
    .fc-event-title { font-size: 11px !important; font-weight: 600 !important; line-height: 1.3; }

    /* Select2 Override */
    .select2-container--default .select2-selection--single { height: 44px !important; border-radius: 14px !important; border: 1px solid #E5E7EB !important; display: flex !important; align-items: center !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: #1F2937 !important; font-size: 13px !important; padding-left: 12px !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px !important; }
    .select2-dropdown { border-radius: 14px !important; border-color: #E5E7EB !important; box-shadow: 0 10px 30px rgba(31, 41, 55, 0.08) !important; z-index: 99999 !important; }
</style>

<div class="space-y-6">
    <!-- Doctor Selection Filter & Guide -->
    <div class="bg-white p-5 rounded-2xl border border-[#E5E7EB] shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <label for="doktorSelect" class="text-xs font-bold text-[#111827] font-display shrink-0">Hekim Seçimi:</label>
            <select id="doktorSelect" onchange="window.location.href='?doktor_id=' + this.value" class="bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-2.5 text-xs font-semibold focus:border-[#C96A2B] outline-none min-w-[200px] cursor-pointer">
                @foreach($doktorlar as $doc)
                    <option value="{{ $doc->id }}" {{ $secilenDoktorId == $doc->id ? 'selected' : '' }}>
                        {{ $doc->unvan ? $doc->unvan . ' ' : '' }}{{ $doc->ad_soyad }} ({{ $doc->uzmanlik_alani }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-xs text-[#4B5563]">
            <span class="flex items-center gap-2">
                <span class="w-4.5 h-4.5 rounded bg-white border border-[#E5E7EB] block"></span>
                Mesai Saatleri
            </span>
            <span class="flex items-center gap-2">
                <span class="w-4.5 h-4.5 rounded block bg-[#FEF9C3] border border-[#FDE047]"></span>
                Öğle Arası
            </span>
            <span class="flex items-center gap-2">
                <span class="w-4.5 h-4.5 rounded block bg-[#F3F4F6] border border-[#E5E7EB]"></span>
                İzinli Süreç
            </span>
        </div>
    </div>

    @if($doktor)
        <!-- Calendar Card -->
        <div class="bg-white rounded-2xl p-6 border border-[#E5E7EB] shadow-sm">
            <div id="calendar" class="w-full"></div>
        </div>
    @else
        <div class="p-12 text-center bg-white rounded-2xl border border-[#E5E7EB]">
            <p class="text-sm text-[#6B7280]">Lütfen takvimi görüntülemek için bir hekim seçin.</p>
        </div>
    @endif
</div>

<!-- Randevu Detay Modalı -->
<div id="appointmentDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
    <div id="appointmentDetailContainer" class="bg-white rounded-3xl border border-[#E5E7EB] shadow-xl max-w-lg w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-[#E5E7EB] flex items-center justify-between bg-white">
            <h3 class="text-base font-bold font-display text-[#111827]">Randevu Detayları</h3>
            <button onclick="closeDetailModal()" class="text-[#6B7280] hover:text-[#1F2937] cursor-pointer outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <!-- Modal Body -->
        <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/20 text-[#C96A2B] shrink-0 font-bold font-display text-lg">
                    👤
                </div>
                <div class="space-y-1">
                    <h4 id="detailDanisanAd" class="text-base font-bold text-[#111827] font-display">-</h4>
                    <p id="detailDanisanTel" class="text-xs text-[#6B7280] font-medium">-</p>
                    <p id="detailDanisanEmail" class="text-xs text-[#6B7280] font-medium">-</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 border-t border-[#F5F5F4] pt-4">
                <div>
                    <span class="text-[10px] font-bold text-[#9CA3AF] uppercase block">Tarih</span>
                    <span id="detailTarih" class="text-sm font-semibold text-[#111827]">-</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-[#9CA3AF] uppercase block">Saat</span>
                    <span id="detailSaat" class="text-sm font-semibold text-[#111827]">-</span>
                </div>
                <div class="col-span-2">
                    <span class="text-[10px] font-bold text-[#9CA3AF] uppercase block">Hizmet</span>
                    <span id="detailHizmetAd" class="text-sm font-semibold text-[#111827]">-</span>
                </div>
            </div>

            <div class="border-t border-[#F5F5F4] pt-4 space-y-3">
                <div>
                    <span class="text-[10px] font-bold text-[#9CA3AF] uppercase block">Randevu Notu</span>
                    <p id="detailDanisanNot" class="text-xs text-[#4B5563] bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl p-3 mt-1 min-h-[50px] font-medium whitespace-pre-line">-</p>
                </div>
                
                <div>
                    <label for="detailStatusSelect" class="text-[10px] font-bold text-[#9CA3AF] uppercase block mb-1">Durum</label>
                    <select id="detailStatusSelect" class="bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-2.5 text-xs font-semibold focus:border-[#C96A2B] outline-none w-full">
                        <option value="beklemede">Onay Bekliyor</option>
                        <option value="onaylandi">Onaylandı</option>
                        <option value="tamamlandi">Tamamlandı</option>
                        <option value="iptal">İptal Edildi</option>
                    </select>
                </div>
            </div>
        </div>
        <!-- Modal Footer -->
        <div class="px-6 py-4 bg-slate-50 border-t border-[#E5E7EB] flex items-center justify-between">
            <button onclick="deleteAppointmentFromDetail()" class="text-xs text-red-600 hover:text-red-800 font-bold uppercase tracking-wider transition-colors">
                Sil
            </button>
            <div class="flex items-center gap-2">
                <button onclick="closeDetailModal()" class="px-4 py-2.5 rounded-xl border border-[#E5E7EB] bg-white text-xs font-bold text-[#4B5563] hover:bg-gray-50 transition-colors">
                    Vazgeç
                </button>
                <button onclick="updateAppointmentFromDetail()" class="px-5 py-2.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-colors">
                    Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Randevu Ekleme Form Modalı -->
<div id="appointmentFormModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
    <div id="appointmentFormContainer" class="bg-white rounded-3xl border border-[#E5E7EB] shadow-xl max-w-lg w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-[#E5E7EB] flex items-center justify-between bg-white">
            <h3 class="text-base font-bold font-display text-[#111827]">Yeni Randevu Ekle</h3>
            <button onclick="closeFormModal()" class="text-[#6B7280] hover:text-[#1F2937] cursor-pointer outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <!-- Modal Body -->
        <form id="appointmentForm" onsubmit="submitAppointmentForm(e)">
            <input type="hidden" id="formTarihInput" name="tarih">
            <input type="hidden" name="doktor_id" value="{{ $secilenDoktorId }}">

            <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                <div class="p-4 rounded-xl bg-orange-50/50 border border-orange-200/50 text-xs font-semibold text-[#C96A2B] flex items-center gap-2">
                    📅 Seçilen Zaman: <span id="formSelectedDateTime" class="font-extrabold">-</span>
                </div>

                <!-- Patient Selection with select2 -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="formDanisanSelect" class="text-xs font-semibold text-[#4B5563]">Hasta / Danışan</label>
                        <button type="button" onclick="openNewClientModal()" class="text-[10px] text-[#C96A2B] hover:text-[#B55A20] font-bold uppercase tracking-wider">
                            + Yeni Hasta Ekle
                        </button>
                    </div>
                    <select id="formDanisanSelect" name="hasta_id" class="w-full" required></select>
                </div>

                <!-- Service Selection -->
                <div class="space-y-1.5">
                    <label for="formHizmetSelect" class="text-xs font-semibold text-[#4B5563] block">Verilecek Hizmet</label>
                    <select id="formHizmetSelect" name="hizmet_id" class="w-full" required></select>
                </div>

                <!-- Time select -->
                <div class="space-y-1.5">
                    <label for="formSlotSelect" class="text-xs font-semibold text-[#4B5563] block">Saat</label>
                    <select id="formSlotSelect" name="saat" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none" required></select>
                </div>

                <!-- Description -->
                <div class="space-y-1.5">
                    <label for="formAciklamaInput" class="text-xs font-semibold text-[#4B5563]">Açıklama / Notlar</label>
                    <textarea id="formAciklamaInput" name="not" rows="3" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none" placeholder="Randevu ile ilgili notlar..."></textarea>
                </div>
            </div>
            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-[#E5E7EB] flex items-center justify-end gap-2">
                <button type="button" onclick="closeFormModal()" class="px-4 py-2.5 rounded-xl border border-[#E5E7EB] bg-white text-xs font-bold text-[#4B5563] hover:bg-gray-50 transition-colors">
                    Kapat
                </button>
                <button type="submit" class="px-5 py-2.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-colors">
                    Randevu Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Yeni Hasta Ekleme Modalı -->
<div id="newClientModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
    <div id="newClientContainer" class="bg-white rounded-3xl border border-[#E5E7EB] shadow-xl max-w-md w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-[#E5E7EB] flex items-center justify-between bg-white">
            <h3 class="text-base font-bold font-display text-[#111827]">Yeni Hasta Kaydet</h3>
            <button type="button" onclick="closeNewClientModal()" class="text-[#6B7280] hover:text-[#1F2937] cursor-pointer outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <!-- Modal Body -->
        <form id="newClientForm" onsubmit="submitNewClientForm(event)">
            <div class="p-6 space-y-4">
                <div class="space-y-1.5">
                    <label for="new_ad_soyad" class="text-xs font-semibold text-[#4B5563]">Ad Soyad</label>
                    <input type="text" id="new_ad_soyad" name="ad_soyad" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] outline-none">
                </div>

                <div class="space-y-1.5">
                    <label for="new_e_posta" class="text-xs font-semibold text-[#4B5563]">E-posta</label>
                    <input type="email" id="new_e_posta" name="e_posta" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] outline-none">
                </div>

                <div class="space-y-1.5">
                    <label for="new_telefon" class="text-xs font-semibold text-[#4B5563]">Telefon</label>
                    <input type="text" id="new_telefon" name="telefon" required placeholder="05551234567" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] outline-none">
                </div>
            </div>
            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-[#E5E7EB] flex items-center justify-end gap-2">
                <button type="button" onclick="closeNewClientModal()" class="px-4 py-2.5 rounded-xl border border-[#E5E7EB] bg-white text-xs font-bold text-[#4B5563] hover:bg-gray-50 transition-colors">
                    Kapat
                </button>
                <button type="submit" class="px-5 py-2.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-colors">
                    Hasta Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/tr.global.min.js"></script>

<script>
    var calendar;
    var currentEvent;

    document.addEventListener('DOMContentLoaded', function() {
        @if(!$doktor)
            return;
        @endif

        var calendarEl = document.getElementById('calendar');

        // Business / working bounds
        var minTime = '{{ $minHour }}';
        var maxTime = '{{ $maxHour }}';
        var slotDur = '{{ $slotDurationString }}';

        calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'tr',
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridWeek,timeGridDay'
            },
            slotMinTime: minTime,
            slotMaxTime: maxTime,
            slotDuration: slotDur,
            allDaySlot: false,
            editable: true,
            selectable: true,
            selectMirror: true,
            eventDurationEditable: false,
            businessHours: {!! json_encode($businessHours) !!},
            eventSources: [
                {
                    url: '{{ route("personel.randevular.events") }}',
                    extraParams: {
                        doktor_id: '{{ $secilenDoktorId }}'
                    }
                }
            ],
            eventDidMount: function(info) {
                if (info.event.display === 'background') {
                    if (info.event.extendedProps.type === 'ogle') {
                        info.el.style.backgroundColor = '#FFFBEB';
                        info.el.style.opacity = '1';
                        info.el.style.display = 'flex';
                        info.el.style.alignItems = 'center';
                        info.el.style.justifyContent = 'center';
                        info.el.innerHTML = '<div style="font-size:9px;font-weight:700;color:#D97706;text-align:center;">🍽️ ÖĞLE ARASI</div>';
                    } else if (info.event.extendedProps.type === 'izin') {
                        info.el.style.backgroundColor = '#F3F4F6';
                        info.el.style.opacity = '0.9';
                        info.el.style.display = 'flex';
                        info.el.style.alignItems = 'center';
                        info.el.style.justifyContent = 'center';
                        info.el.innerHTML = '<div style="font-size:9px;font-weight:700;color:#6B7280;text-align:center;">🔒 İZİNLİ</div>';
                    }
                } else if (info.event.extendedProps.type === 'randevu') {
                    var durum = info.event.extendedProps.durum;
                    let bgColor = 'rgba(201, 106, 43, 0.09)';
                    let borderColor = '#C96A2B';
                    let textColor = '#92400E';

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
                    }

                    info.el.style.backgroundColor = bgColor;
                    info.el.style.borderLeft = '4px solid ' + borderColor;
                    info.el.style.color = textColor;
                    info.el.style.borderTop = 'none';
                    info.el.style.borderRight = 'none';
                    info.el.style.borderBottom = 'none';
                }
            },
            eventClick: function(info) {
                if (info.event.extendedProps.type === 'randevu') {
                    showAppointmentDetail(info.event);
                }
            },
            select: function(info) {
                if (new Date(info.start) < new Date()) {
                    alert('Geçmiş bir zamana randevu eklenemez.');
                    calendar.unselect();
                    return;
                }
                createNewAppointment(info.startStr, info.endStr);
            },
            eventDrop: function(info) {
                rescheduleAppointment(info);
            }
        });

        calendar.render();

        // Select2 Patient search
        $('#formDanisanSelect').select2({
            ajax: {
                url: '{{ route("personel.randevular.hastalar-ara") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data.results };
                },
                cache: true
            },
            placeholder: 'Hasta seçin...',
            minimumInputLength: 2,
            dropdownParent: $('#appointmentFormModal')
        });

        // Initialize form validation & submit
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitAppointmentForm();
        });
    });

    // Drag and Drop rescheduling
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

        fetch(`/personel/randevular/${appointmentId}/reschedule`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                tarih: formattedDate,
                saat: formattedTime
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
            } else {
                alert(data.message);
                info.revert();
            }
        })
        .catch(err => {
            alert('Bir hata oluştu.');
            info.revert();
        });
    }

    // Detail Modal Actions
    function showAppointmentDetail(event) {
        currentEvent = event;
        var props = event.extendedProps;
        var randevu = props.randevu;

        document.getElementById('detailDanisanAd').innerText = props.hasta_ad;
        document.getElementById('detailDanisanTel').innerText = randevu.telefon || 'Telefon yok';
        document.getElementById('detailDanisanEmail').innerText = randevu.e_posta || 'E-posta yok';
        document.getElementById('detailHizmetAd').innerText = props.hizmet_ad;

        var startDate = new Date(event.start);
        var dateStr = startDate.toLocaleDateString('tr-TR');
        var timeStr = String(startDate.getHours()).padStart(2, '0') + ':' + String(startDate.getMinutes()).padStart(2, '0');

        document.getElementById('detailTarih').innerText = dateStr;
        document.getElementById('detailSaat').innerText = timeStr;
        document.getElementById('detailDanisanNot').innerText = randevu.not || 'Not yok.';
        document.getElementById('detailStatusSelect').value = props.durum;

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
        }, 300);
    }

    function updateAppointmentFromDetail() {
        if (!currentEvent) return;
        var appointmentId = currentEvent.id.replace('randevu_', '');
        var newStatus = document.getElementById('detailStatusSelect').value;

        fetch(`/personel/randevular/${appointmentId}/guncelle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                durum: newStatus,
                not: document.getElementById('detailDanisanNot').innerText
            })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            closeDetailModal();
            calendar.refetchEvents();
        });
    }

    function deleteAppointmentFromDetail() {
        if (!currentEvent) return;
        var appointmentId = currentEvent.id.replace('randevu_', '');

        if (confirm('Bu randevuyu iptal etmek istediğinize emin misiniz?')) {
            fetch(`/personel/randevular/${appointmentId}/iptal`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                closeDetailModal();
                calendar.refetchEvents();
            });
        }
    }

    // Form Modal Actions
    function createNewAppointment(startStr, endStr) {
        var datePart = startStr.substring(0, 10);
        document.getElementById('formTarihInput').value = datePart;

        document.getElementById('formSelectedDateTime').innerText = datePart;

        // Fetch services and slots for the doctor
        var docId = $('#doktorSelect').val();
        fetch(`/personel/randevular/doktor-veri?doktor_id=${docId}&tarih=${datePart}`)
        .then(res => res.json())
        .then(data => {
            var servicesSelect = $('#formHizmetSelect');
            servicesSelect.empty();
            servicesSelect.append('<option value="">Hizmet Seçin</option>');
            data.hizmetler.forEach(srv => {
                servicesSelect.append(`<option value="${srv.id}">${srv.ad} (${srv.sure} dk - ₺${srv.fiyat})</option>`);
            });

            var slotSelect = $('#formSlotSelect');
            slotSelect.empty();
            data.slots.forEach(slot => {
                slotSelect.append(`<option value="${slot}">${slot}</option>`);
            });

            var modal = document.getElementById('appointmentFormModal');
            var container = document.getElementById('appointmentFormContainer');
            modal.classList.remove('hidden');
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 50);
        });
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

    function submitAppointmentForm() {
        var form = document.getElementById('appointmentForm');
        var formData = new FormData(form);
        var jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        fetch('{{ route("personel.randevular.kaydet") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(jsonData)
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            closeFormModal();
            calendar.refetchEvents();
        });
    }

    // New Patient Actions
    function openNewClientModal() {
        var modal = document.getElementById('newClientModal');
        var container = document.getElementById('newClientContainer');
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

    function submitNewClientForm(e) {
        e.preventDefault();
        var form = document.getElementById('newClientForm');
        var formData = new FormData(form);
        var jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        fetch('{{ route("personel.randevular.hasta-ekle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(jsonData)
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                var newOption = new Option(data.danisan.name + ' (' + data.danisan.phone + ')', data.danisan.id, true, true);
                $('#formDanisanSelect').append(newOption).trigger('change');
                closeNewClientModal();
            }
        });
    }
</script>

@endsection
