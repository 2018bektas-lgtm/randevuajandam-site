@extends('frontend.layouts.app-tw')

@section('baslik', 'Randevuyu Yeniden Planla - Randevu Ajandam')

@section('icerik')
<section class="fe-page relative bg-[#FAFAFA] overflow-hidden">
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-[#C96A2B]/3 blur-[120px] pointer-events-none"></div>

    <div class="max-w-3xl mx-auto px-6 relative z-10">

        <!-- Back link -->
        <div class="mb-6">
            <a href="{{ route('frontend.hasta.randevular') }}" class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#6B7280] hover:text-[#C96A2B] transition-colors font-display">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                </svg>
                Randevularıma Dön
            </a>
        </div>

        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-6">

            <!-- Header -->
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold font-display text-[#111827]">Randevuyu Yeniden Planla</h2>
                <p class="text-xs text-[#6B7280]">Mevcut randevunuzu iptal ederek yeni bir tarih ve saat seçebilirsiniz.</p>
            </div>

            @if($errors->any())
                <div class="p-4 bg-red-50 border border-red-100 rounded-2xl text-xs text-red-600 space-y-1">
                    @foreach($errors->all() as $error)
                        <p class="flex items-center gap-1.5 font-medium">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                            </svg>
                            {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif

            @if(session('hata'))
                <div class="p-4 bg-red-50 border border-red-100 rounded-2xl text-xs text-red-600 font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                    </svg>
                    {{ session('hata') }}
                </div>
            @endif

            <!-- Current Appointment Info -->
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path>
                </svg>
                <div class="space-y-0.5 text-xs">
                    <p class="font-bold text-amber-900 font-display">Mevcut Randevunuz İptal Edilecek</p>
                    <p class="text-amber-800">
                        <span class="font-semibold">{{ $randevu->doktor->unvan ? $randevu->doktor->unvan . ' ' : '' }}{{ $randevu->doktor->ad_soyad }}</span>
                        — {{ $randevu->hizmet->ad }}
                    </p>
                    <p class="text-amber-700">
                        {{ $randevu->tarih->translatedFormat('d M Y, l') }} saat {{ substr($randevu->saat, 0, 5) }}
                    </p>
                </div>
            </div>

            <!-- Reschedule Form -->
            <form action="{{ route('frontend.hasta.randevu.yeniden-planla.post', $randevu->id) }}" method="POST" id="rescheduleForm" class="space-y-6">
                @csrf

                <!-- Date Picker -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Yeni Tarih Seçin</label>
                    <input type="date" id="tarihInput" name="tarih" value="{{ old('tarih') }}"
                           min="{{ now()->toDateString() }}"
                           class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-sm text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                </div>

                <!-- Slot Picker -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Uygun Saat Seçin</label>
                    <input type="hidden" name="saat" id="saatInput" value="{{ old('saat') }}">

                    <div id="slotContainer" class="min-h-[48px] flex items-center">
                        <p class="text-xs text-[#6B7280]" id="slotPlaceholder">Önce tarih seçin.</p>
                        <div id="slotLoading" class="hidden items-center gap-2 text-xs text-[#6B7280]">
                            <svg class="w-4 h-4 animate-spin text-[#C96A2B]" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Müsait saatler yükleniyor…
                        </div>
                        <div id="slotGrid" class="hidden flex-wrap gap-2"></div>
                        <p id="slotEmpty" class="hidden text-xs text-[#6B7280]">Bu tarihte müsait saat bulunamadı. Başka bir gün seçin.</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                    <a href="{{ route('frontend.hasta.randevular') }}"
                       class="px-5 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#4B5563] font-bold text-xs uppercase tracking-wider transition-all font-display">
                        Vazgeç
                    </a>
                    <button type="submit" id="submitBtn" disabled
                            class="px-6 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs uppercase tracking-wider transition-all shadow-sm font-display">
                        Yeniden Planla
                    </button>
                </div>
            </form>

        </div>
    </div>
</section>

<script>
(function() {
    const doktorId = {{ $randevu->doktor_id }};
    const hizmetId = {{ $randevu->hizmet_id }};
    const slotsBaseUrl = '{{ route('frontend.doktorlar.slotlar', $randevu->doktor_id) }}';

    const tarihInput = document.getElementById('tarihInput');
    const saatInput = document.getElementById('saatInput');
    const slotContainer = document.getElementById('slotContainer');
    const slotPlaceholder = document.getElementById('slotPlaceholder');
    const slotLoading = document.getElementById('slotLoading');
    const slotGrid = document.getElementById('slotGrid');
    const slotEmpty = document.getElementById('slotEmpty');
    const submitBtn = document.getElementById('submitBtn');

    let currentXhr = null;

    function showOnly(el) {
        [slotPlaceholder, slotLoading, slotGrid, slotEmpty].forEach(e => {
            if (e === slotGrid) {
                e.classList.add('hidden');
                e.classList.remove('flex');
            } else {
                e.classList.add('hidden');
            }
        });
        if (el === slotGrid) {
            el.classList.remove('hidden');
            el.classList.add('flex');
        } else {
            el.classList.remove('hidden');
        }
    }

    function selectSlot(saat) {
        saatInput.value = saat;
        document.querySelectorAll('.slot-btn').forEach(btn => {
            if (btn.dataset.saat === saat) {
                btn.classList.add('bg-[#C96A2B]', 'text-white', 'border-[#C96A2B]');
                btn.classList.remove('bg-white', 'text-[#111827]');
            } else {
                btn.classList.remove('bg-[#C96A2B]', 'text-white', 'border-[#C96A2B]');
                btn.classList.add('bg-white', 'text-[#111827]');
            }
        });
        submitBtn.disabled = false;
    }

    function loadSlots(tarih) {
        saatInput.value = '';
        submitBtn.disabled = true;
        showOnly(slotLoading);
        slotGrid.innerHTML = '';

        if (currentXhr) { currentXhr.abort(); }

        const url = slotsBaseUrl + '?tarih=' + encodeURIComponent(tarih) + '&hizmet_id=' + hizmetId;
        currentXhr = new XMLHttpRequest();
        currentXhr.open('GET', url);
        currentXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        currentXhr.onload = function() {
            if (this.status !== 200) { showOnly(slotEmpty); return; }
            let data;
            try { data = JSON.parse(this.responseText); } catch(e) { showOnly(slotEmpty); return; }
            const slotlar = data.slotlar ?? data.slots ?? data ?? [];
            if (!Array.isArray(slotlar) || slotlar.length === 0) { showOnly(slotEmpty); return; }

            slotlar.forEach(function(s) {
                const saat = typeof s === 'string' ? s : (s.saat ?? s.time ?? '');
                if (!saat) return;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.dataset.saat = saat;
                btn.className = 'slot-btn px-3.5 py-2 rounded-xl border border-[#E5E7EB] bg-white text-[#111827] text-xs font-bold font-display hover:border-[#C96A2B] hover:text-[#C96A2B] transition-all cursor-pointer';
                btn.textContent = saat.substring(0, 5);
                btn.addEventListener('click', function() { selectSlot(saat); });
                slotGrid.appendChild(btn);
            });
            showOnly(slotGrid);
        };
        currentXhr.onerror = function() { showOnly(slotEmpty); };
        currentXhr.send();
    }

    tarihInput.addEventListener('change', function() {
        if (this.value) { loadSlots(this.value); }
    });

    // Auto-load if old tarih value exists
    if (tarihInput.value) { loadSlots(tarihInput.value); }
})();
</script>
@endsection
