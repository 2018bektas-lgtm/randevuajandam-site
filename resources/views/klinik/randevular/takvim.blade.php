@extends('klinik.layout')
@section('baslik', 'Klinik Randevu Takvimi')
@section('sayfa_baslik', 'Klinik Randevu Takvimi')

@section('extra_css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet" />
<style>
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
        background-color: #1E3A5F !important;
        color: #ffffff !important;
        border-color: #1E3A5F !important;
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.15) !important;
    }
    .fc-col-header-cell {
        background-color: #FAFAFA !important;
        padding: 12px 0 !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        color: #4B5563 !important;
        border-bottom: 2px solid #E5E7EB !important;
    }
    .fc-daygrid-day {
        transition: background-color 0.15s ease !important;
    }
    .fc-daygrid-day:hover {
        background-color: #F9FAFB !important;
    }
    .fc-event {
        cursor: pointer;
        padding: 4px 8px !important;
        border-radius: 8px !important;
        margin: 2px 4px !important;
        border: 1px solid transparent !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease !important;
    }
    .fc-event:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05) !important;
    }
    .fc-timegrid-slot {
        height: 48px !important;
    }
</style>
@endsection

@section('icerik')
<div class="space-y-6">
    <!-- Filters Header -->
    <div class="bg-white rounded-2xl border border-[#E5E7EB] p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Hekim Filtresi</h3>
                <p class="text-xs text-[#6B7280] mt-1">Takvimde görmek istediğiniz hekimleri seçin.</p>
            </div>
            <div class="flex flex-wrap gap-2" id="doctor-filters">
                <button type="button" onclick="selectAllDoctors()" class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 transition-colors">Tümü</button>
                <button type="button" onclick="deselectAllDoctors()" class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 transition-colors">Temizle</button>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 mt-4">
            @foreach($doktorlar as $doc)
                @php
                    $hue = ($doc->id * 67) % 360;
                    $borderCol = "hsl({$hue}, 65%, 45%)";
                    $bgLight = "hsl({$hue}, 65%, 95%)";
                    $textCol = "hsl({$hue}, 65%, 35%)";
                @endphp
                <label class="relative p-3 rounded-xl border flex items-center gap-3 cursor-pointer select-none transition-all hover:shadow-sm" style="border-color: {{ $borderCol }}; background-color: {{ $bgLight }};">
                    <input type="checkbox" name="doktor_ids[]" value="{{ $doc->id }}" checked onchange="doctorFilterChanged()" class="w-4 h-4 rounded text-[#1E3A5F] focus:ring-[#1E3A5F] border-gray-300">
                    <div class="flex flex-col truncate">
                        <span class="text-xs font-bold truncate" style="color: {{ $textCol }}">{{ $doc->unvan ? $doc->unvan . ' ' : '' }}{{ $doc->ad_soyad }}</span>
                        <span class="text-[9px] font-semibold text-gray-500 truncate">{{ $doc->uzmanlik_alani ?? 'Hekim' }}</span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="bg-white rounded-2xl border border-[#E5E7EB] p-6 shadow-sm">
        <div id="calendar" class="min-h-[600px]"></div>
    </div>
</div>

<!-- Details Modal -->
<div id="appointmentDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
    <div id="appointmentDetailContainer" class="bg-white rounded-3xl border border-[#E5E7EB] shadow-2xl max-w-md w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
        <div class="p-6 border-b border-[#E5E7EB] flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display flex items-center gap-2">
                <span>📅</span> Randevu Detayı
            </h3>
            <button onclick="closeDetailModal()" class="text-[#6B7280] hover:text-[#1F2937] cursor-pointer outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block font-display">Hekim</span>
                <span id="detailHekim" class="text-xs font-bold text-[#111827]"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block font-display">Danışan / Hasta</span>
                <span id="detailHasta" class="text-xs font-bold text-[#1E3A5F]"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block font-display">Telefon</span>
                <span id="detailTelefon" class="text-xs font-semibold text-gray-600"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block font-display">Hizmet / Tedavi</span>
                <span id="detailHizmet" class="text-xs font-semibold text-gray-700"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block font-display">Tarih & Saat</span>
                <span id="detailTarihSaat" class="text-xs font-bold text-[#C96A2B]"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block font-display">Ücret</span>
                <span id="detailUcret" class="text-xs font-bold text-gray-900"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block font-display">Durum</span>
                <span id="detailDurum" class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider"></span>
            </div>
        </div>
        <div class="p-6 bg-slate-50 border-t border-[#E5E7EB] text-center">
            <button onclick="closeDetailModal()" class="w-full py-3 rounded-xl bg-[#1E3A5F] hover:bg-[#152a47] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display outline-none">
                Kapat
            </button>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/tr.global.min.js"></script>
<script>
    let calendar;

    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'tr',
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            slotMinTime: '08:00:00',
            slotMaxTime: '20:00:00',
            allDaySlot: false,
            slotDuration: '00:15:00',
            events: function(info, successCallback, failureCallback) {
                const doktorIds = Array.from(document.querySelectorAll('input[name="doktor_ids[]"]:checked')).map(cb => cb.value);
                const url = new URL('{{ route("hekim.klinik.randevular.takvim.events") }}');
                url.searchParams.append('start', info.startStr);
                url.searchParams.append('end', info.endStr);
                doktorIds.forEach(id => url.searchParams.append('doktor_ids[]', id));

                fetch(url)
                    .then(res => res.json())
                    .then(data => successCallback(data))
                    .catch(err => failureCallback(err));
            },
            eventClick: function(info) {
                showAppointmentDetail(info.event);
            }
        });

        calendar.render();
    });

    function doctorFilterChanged() {
        if (calendar) {
            calendar.refetchEvents();
        }
    }

    function selectAllDoctors() {
        document.querySelectorAll('input[name="doktor_ids[]"]').forEach(cb => cb.checked = true);
        doctorFilterChanged();
    }

    function deselectAllDoctors() {
        document.querySelectorAll('input[name="doktor_ids[]"]').forEach(cb => cb.checked = false);
        doctorFilterChanged();
    }

    function showAppointmentDetail(event) {
        const props = event.extendedProps;
        document.getElementById('detailHekim').innerText = props.doktor;
        document.getElementById('detailHasta').innerText = props.hasta;
        document.getElementById('detailTelefon').innerText = props.telefon || 'Telefon belirtilmemiş';
        document.getElementById('detailHizmet').innerText = props.hizmet;
        
        const start = new Date(event.start);
        const formatOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        document.getElementById('detailTarihSaat').innerText = start.toLocaleDateString('tr-TR', formatOptions);
        document.getElementById('detailUcret').innerText = props.ucret;

        const durum = props.durum;
        const durumEl = document.getElementById('detailDurum');
        durumEl.innerText = durum;
        durumEl.className = 'px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider ';
        
        if (durum === 'Onaylandı') {
            durumEl.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
        } else if (durum === 'Tamamlandı') {
            durumEl.classList.add('bg-blue-50', 'text-blue-700', 'border', 'border-blue-200');
        } else {
            durumEl.classList.add('bg-amber-50', 'text-amber-700', 'border', 'border-amber-200');
        }

        // Online badge / join (optional nodes if present in modal)
        const gorusmeEl = document.getElementById('detailGorusmeTipi');
        if (gorusmeEl) {
            gorusmeEl.innerText = (props.gorusme_tipi === 'online') ? 'Online' : 'Yüz yüze';
        }
        const joinBox = document.getElementById('detailOnlineJoin');
        const joinLink = document.getElementById('detailJoinLink');
        if (joinBox && joinLink) {
            if (props.gorusme_tipi === 'online' && props.platform_join_url) {
                joinBox.classList.remove('hidden');
                joinLink.href = props.platform_join_url;
            } else {
                joinBox.classList.add('hidden');
            }
        }

        const modal = document.getElementById('appointmentDetailModal');
        const container = document.getElementById('appointmentDetailContainer');
        modal.classList.remove('hidden');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 50);
    }

    function closeDetailModal() {
        const modal = document.getElementById('appointmentDetailModal');
        const container = document.getElementById('appointmentDetailContainer');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Setup Close Modal clicking outside
    document.getElementById('appointmentDetailModal').addEventListener('click', function(e) {
        if (e.target === this) closeDetailModal();
    });
</script>
@endsection
