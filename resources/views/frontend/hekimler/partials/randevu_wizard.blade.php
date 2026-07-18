@php
    $aktifHizmetler = $doktor->hizmetler->where('aktif_mi', true)->values();
    $hastaAuth = Auth::guard('hasta')->check();
    $hastaUser = $hastaAuth ? Auth::guard('hasta')->user() : null;
    $onlineGorusmeAcik = $doktor->aktifPaket()?->hasFeature('online_gorusme');
    $slotsUrl = route('frontend.doktorlar.slotlar', $doktor->id);
    $formAction = $hastaAuth
        ? route('frontend.hasta.randevu.kaydet')
        : route('frontend.hasta.randevu.misafir');
    $minDate = date('Y-m-d');
@endphp

@if($doktor->randevuya_acik_mi && $aktifHizmetler->isNotEmpty())
<section id="randevu-wizard" class="mb-10 scroll-mt-24">
    <div class="bg-white border border-[#E5E7EB] rounded-3xl shadow-[0_8px_30px_rgba(31,41,55,0.04)] overflow-hidden">
        {{-- Header --}}
        <div class="px-6 md:px-8 py-5 border-b border-slate-100 bg-gradient-to-r from-[#FFF7ED]/80 to-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#C96A2B] font-display">Online Randevu</p>
                <h2 class="text-lg md:text-xl font-extrabold font-display text-[#111827] tracking-tight">Adım adım randevu al</h2>
            </div>
            <p class="text-[11px] text-[#6B7280] max-w-xs sm:text-right leading-relaxed">Hizmet → Tarih → Saat → Bilgiler. Dolu saatler seçilemez.</p>
        </div>

        {{-- Step indicators (horizontal progress) --}}
        <div class="px-4 md:px-8 pt-6 pb-2">
            <div class="flex items-center gap-1 md:gap-2" id="rw-steps">
                @foreach([
                    1 => 'Hizmet',
                    2 => 'Tarih',
                    3 => 'Saat',
                    4 => 'Bilgiler',
                ] as $n => $label)
                    <div class="flex items-center flex-1 min-w-0 {{ $n < 4 ? '' : '' }}">
                        <div class="rw-step-pill flex items-center gap-2 min-w-0" data-step="{{ $n }}">
                            <span class="rw-step-num w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-xs font-bold font-display border-2 transition-all
                                {{ $n === 1 ? 'bg-[#C96A2B] border-[#C96A2B] text-white' : 'bg-white border-slate-200 text-slate-400' }}">{{ $n }}</span>
                            <span class="rw-step-label hidden sm:block text-[11px] font-bold font-display uppercase tracking-wider truncate
                                {{ $n === 1 ? 'text-[#C96A2B]' : 'text-slate-400' }}">{{ $label }}</span>
                        </div>
                        @if($n < 4)
                            <div class="rw-step-line flex-1 h-0.5 mx-1.5 md:mx-2 rounded bg-slate-100 transition-colors" data-line="{{ $n }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Flash --}}
        @if(session('basarili') || session('hata'))
            <div class="px-6 md:px-8 pt-4">
                @if(session('basarili'))
                    <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl text-[12px] text-emerald-700 font-medium">{{ session('basarili') }}</div>
                @endif
                @if(session('hata'))
                    <div class="p-3 bg-red-50 border border-red-100 rounded-xl text-[12px] text-red-700 font-medium">{{ session('hata') }}</div>
                @endif
            </div>
        @endif

        <form action="{{ $formAction }}" method="POST" id="rw-form" class="p-6 md:p-8 pt-4">
            @csrf
            <input type="hidden" name="doktor_id" value="{{ $doktor->id }}">
            <input type="hidden" name="hizmet_id" id="rw-hizmet-id" value="{{ old('hizmet_id') }}">
            <input type="hidden" name="tarih" id="rw-tarih" value="{{ old('tarih') }}">
            <input type="hidden" name="saat" id="rw-saat" value="{{ old('saat') }}">
            @unless($hastaAuth)
                <input type="hidden" name="recaptcha_token" id="rw-recaptcha-token" value="">
                <div class="hidden" aria-hidden="true">
                    <input type="text" name="{{ config('randevu.honeypot_field', 'website_url') }}" value="" tabindex="-1" autocomplete="off">
                </div>
            @endunless

            {{-- Summary strip --}}
            <div id="rw-summary" class="mb-5 hidden flex-wrap items-center gap-2 text-[11px]">
                <span class="text-slate-400 font-display uppercase tracking-wider font-bold text-[9px]">Seçiminiz:</span>
                <span id="rw-sum-hizmet" class="hidden px-2.5 py-1 rounded-lg bg-[#FFF7ED] text-[#C96A2B] font-semibold border border-[#E7B58A]/30"></span>
                <span id="rw-sum-tarih" class="hidden px-2.5 py-1 rounded-lg bg-slate-50 text-slate-700 font-semibold border border-slate-100"></span>
                <span id="rw-sum-saat" class="hidden px-2.5 py-1 rounded-lg bg-slate-50 text-slate-700 font-semibold border border-slate-100"></span>
            </div>

            {{-- STEP 1: Hizmet --}}
            <div class="rw-panel" data-panel="1">
                <h3 class="text-sm font-bold font-display text-[#111827] mb-1">Hizmet seçin</h3>
                <p class="text-[11px] text-[#6B7280] mb-4">Randevu almak istediğiniz hizmeti seçin, ardından ileriye geçin.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($aktifHizmetler as $hizmet)
                        <button type="button"
                                class="rw-hizmet-card text-left p-4 rounded-2xl border-2 border-slate-100 hover:border-[#E7B58A] bg-slate-50/40 hover:bg-[#FFF7ED]/40 transition-all group"
                                data-id="{{ $hizmet->id }}"
                                data-ad="{{ $hizmet->ad }}"
                                data-sure="{{ $hizmet->sure }}">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-[#C96A2B] shrink-0 group-hover:border-[#E7B58A]/50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-[#111827] font-display truncate">{{ $hizmet->ad }}</p>
                                    <p class="text-[10px] font-semibold text-[#C96A2B] uppercase tracking-wider mt-0.5">{{ $hizmet->sure }} dk</p>
                                    @if($hizmet->aciklama)
                                        <p class="text-[10px] text-[#6B7280] mt-1 line-clamp-2">{{ Str::limit(strip_tags($hizmet->aciklama), 80) }}</p>
                                    @endif
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
                <p id="rw-err-1" class="hidden mt-3 text-[11px] text-red-600 font-medium">Lütfen bir hizmet seçin.</p>
            </div>

            {{-- STEP 2: Tarih --}}
            <div class="rw-panel hidden" data-panel="2">
                <h3 class="text-sm font-bold font-display text-[#111827] mb-1">Tarih seçin</h3>
                <p class="text-[11px] text-[#6B7280] mb-4">Müsait günlerden birini seçin. Geçmiş tarihler seçilemez.</p>
                <div class="max-w-sm">
                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display mb-1.5">Randevu tarihi</label>
                    <input type="date" id="rw-date-input" min="{{ $minDate }}" value="{{ old('tarih', date('Y-m-d', strtotime('+1 day'))) }}"
                           class="w-full px-4 py-3 rounded-2xl border border-[#E5E7EB] text-sm text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/20 transition-all font-display">
                </div>
                <p id="rw-err-2" class="hidden mt-3 text-[11px] text-red-600 font-medium">Lütfen bir tarih seçin.</p>
            </div>

            {{-- STEP 3: Saat --}}
            <div class="rw-panel hidden" data-panel="3">
                <h3 class="text-sm font-bold font-display text-[#111827] mb-1">Saat seçin</h3>
                <p class="text-[11px] text-[#6B7280] mb-4">
                    <span id="rw-saat-tarih-label" class="font-semibold text-[#C96A2B]"></span>
                    için müsait saatler. <span class="line-through text-slate-400">Dolu</span> saatler seçilemez.
                </p>
                <div id="rw-slots-loading" class="hidden py-8 text-center text-xs text-slate-400">Saatler yükleniyor…</div>
                <div id="rw-slots-empty" class="hidden py-8 text-center space-y-2">
                    <p class="text-xs text-slate-500">Bu günde müsait saat yok veya hekim çalışmıyor.</p>
                    <button type="button" class="rw-back-btn text-[11px] font-bold text-[#C96A2B] underline">Başka tarih seç</button>
                </div>
                <div id="rw-slots-grid" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2"></div>
                <div class="mt-4 flex flex-wrap gap-3 text-[10px] text-slate-500">
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded border-2 border-[#C96A2B] bg-[#FFF7ED]"></span> Müsait</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-slate-100 border border-slate-200"></span> Dolu / kapalı</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded border-2 border-[#C96A2B] bg-[#C96A2B]"></span> Seçili</span>
                </div>
                <p id="rw-err-3" class="hidden mt-3 text-[11px] text-red-600 font-medium">Lütfen müsait bir saat seçin.</p>
            </div>

            {{-- STEP 4: Bilgiler --}}
            <div class="rw-panel hidden" data-panel="4">
                <h3 class="text-sm font-bold font-display text-[#111827] mb-1">Bilgileriniz</h3>
                <p class="text-[11px] text-[#6B7280] mb-4">Randevu talebini tamamlamak için bilgilerinizi girin.</p>

                @if($hastaAuth)
                    <div class="mb-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-[9px] font-bold text-[#6B7280] uppercase tracking-wider font-display mb-1">Randevu sahibi</p>
                        <p class="text-sm font-bold text-[#111827]">{{ $hastaUser->ad_soyad }}</p>
                        <p class="text-[11px] text-[#6B7280]">{{ $hastaUser->telefon }} · {{ $hastaUser->e_posta }}</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display mb-1">Ad</label>
                            <input type="text" name="ad" value="{{ old('ad') }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none focus:ring-1 focus:ring-[#C96A2B]">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display mb-1">Soyad</label>
                            <input type="text" name="soyad" value="{{ old('soyad') }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none focus:ring-1 focus:ring-[#C96A2B]">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display mb-1">Telefon</label>
                            <input type="tel" name="telefon" value="{{ old('telefon') }}" required placeholder="05xx xxx xx xx"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none focus:ring-1 focus:ring-[#C96A2B]">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display mb-1">E-posta (opsiyonel)</label>
                            <input type="email" name="e_posta" value="{{ old('e_posta') }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none focus:ring-1 focus:ring-[#C96A2B]">
                        </div>
                    </div>
                @endif

                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display mb-1">Not / şikayet (opsiyonel)</label>
                    <textarea name="not" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none resize-none">{{ old('not') }}</textarea>
                </div>

                @if($onlineGorusmeAcik)
                    <div class="mb-4 p-4 rounded-2xl border border-slate-100 bg-slate-50/80 space-y-2">
                        <span class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Görüşme türü</span>
                        <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                            <input type="radio" name="gorusme_tipi" value="yuz_yuze" @checked(old('gorusme_tipi', 'yuz_yuze') === 'yuz_yuze') class="text-[#C96A2B] focus:ring-[#C96A2B]">
                            Yüz yüze (muayenehane)
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                            <input type="radio" name="gorusme_tipi" value="online" @checked(old('gorusme_tipi') === 'online') class="text-[#C96A2B] focus:ring-[#C96A2B]">
                            Online görüntülü görüşme
                        </label>
                    </div>
                @else
                    <input type="hidden" name="gorusme_tipi" value="yuz_yuze">
                @endif

                @unless($hastaAuth)
                    <label class="flex items-start gap-2 text-[11px] text-slate-600 cursor-pointer mb-2">
                        <input type="checkbox" name="kvkk_onay" value="1" required class="mt-0.5 rounded border-slate-300 text-[#C96A2B] focus:ring-[#C96A2B]">
                        <span>Kişisel verilerimin randevu amacıyla işlenmesini kabul ediyorum.</span>
                    </label>
                    <p class="text-[10px] text-slate-400 mb-2">Hesabınız var mı? <a href="{{ route('frontend.hasta.giris') }}" class="text-[#C96A2B] font-semibold hover:underline">Giriş yapın</a></p>
                @endunless
            </div>

            {{-- Nav buttons --}}
            <div class="mt-8 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-100 pt-5">
                <button type="button" id="rw-btn-back"
                        class="hidden sm:inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 font-display uppercase tracking-wider transition-all">
                    ← Geri
                </button>
                <div class="flex gap-2 w-full sm:w-auto sm:ml-auto">
                    <button type="button" id="rw-btn-back-m"
                            class="sm:hidden flex-1 items-center justify-center gap-2 px-4 py-3 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 font-display uppercase tracking-wider">
                        Geri
                    </button>
                    <button type="button" id="rw-btn-next"
                            class="flex-1 sm:flex-none px-8 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold font-display uppercase tracking-wider shadow-sm hover:shadow-md transition-all">
                        İleri →
                    </button>
                    <button type="submit" id="rw-btn-submit"
                            class="hidden flex-1 sm:flex-none px-8 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold font-display uppercase tracking-wider shadow-sm hover:shadow-md transition-all">
                        Randevu Talebi Oluştur
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<style>
    .rw-hizmet-card.is-selected {
        border-color: #C96A2B !important;
        background: #FFF7ED !important;
        box-shadow: 0 0 0 1px #C96A2B;
    }
    .rw-slot {
        padding: 0.65rem 0.25rem;
        border-radius: 0.75rem;
        text-align: center;
        font-size: 0.75rem;
        font-weight: 700;
        border: 1px solid transparent;
        transition: all 0.15s ease;
    }
    .rw-slot-musait {
        border-color: #E7B58A;
        background: #FFFBEB;
        color: #C96A2B;
        cursor: pointer;
    }
    .rw-slot-musait:hover {
        background: #FFF7ED;
        border-color: #C96A2B;
    }
    .rw-slot-musait.is-selected {
        background: #C96A2B;
        border-color: #C96A2B;
        color: #fff;
    }
    .rw-slot-dolu {
        border-color: #E5E7EB;
        background: #F3F4F6;
        color: #9CA3AF;
        text-decoration: line-through;
        cursor: not-allowed;
        opacity: 0.85;
    }
</style>

<script>
(function () {
    const slotsUrl = @json($slotsUrl);
    const isGuest = @json(! $hastaAuth);
    let step = 1;
    const maxStep = 4;
    let selectedHizmet = { id: '', ad: '', sure: '' };
    let selectedSaat = '';

    const form = document.getElementById('rw-form');
    if (!form) return;

    const panels = () => document.querySelectorAll('.rw-panel');
    const err = (n) => document.getElementById('rw-err-' + n);

    function setStep(n) {
        step = n;
        panels().forEach(p => {
            p.classList.toggle('hidden', Number(p.dataset.panel) !== n);
        });
        document.querySelectorAll('.rw-step-pill').forEach(pill => {
            const s = Number(pill.dataset.step);
            const num = pill.querySelector('.rw-step-num');
            const lab = pill.querySelector('.rw-step-label');
            if (s < n) {
                num.className = 'rw-step-num w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-xs font-bold font-display border-2 transition-all bg-emerald-500 border-emerald-500 text-white';
                if (lab) lab.className = 'rw-step-label hidden sm:block text-[11px] font-bold font-display uppercase tracking-wider truncate text-emerald-600';
            } else if (s === n) {
                num.className = 'rw-step-num w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-xs font-bold font-display border-2 transition-all bg-[#C96A2B] border-[#C96A2B] text-white';
                if (lab) lab.className = 'rw-step-label hidden sm:block text-[11px] font-bold font-display uppercase tracking-wider truncate text-[#C96A2B]';
            } else {
                num.className = 'rw-step-num w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-xs font-bold font-display border-2 transition-all bg-white border-slate-200 text-slate-400';
                if (lab) lab.className = 'rw-step-label hidden sm:block text-[11px] font-bold font-display uppercase tracking-wider truncate text-slate-400';
            }
        });
        document.querySelectorAll('.rw-step-line').forEach(line => {
            const s = Number(line.dataset.line);
            line.classList.toggle('bg-[#C96A2B]', s < n);
            line.classList.toggle('bg-slate-100', s >= n);
        });

        const back = document.getElementById('rw-btn-back');
        const backM = document.getElementById('rw-btn-back-m');
        const next = document.getElementById('rw-btn-next');
        const sub = document.getElementById('rw-btn-submit');
        if (back) back.classList.toggle('hidden', n === 1);
        if (backM) {
            backM.classList.toggle('hidden', n === 1);
            backM.classList.toggle('flex', n > 1);
        }
        if (next) next.classList.toggle('hidden', n === maxStep);
        if (sub) {
            sub.classList.toggle('hidden', n !== maxStep);
            sub.classList.toggle('inline-flex', n === maxStep);
        }
        updateSummary();
        if (n === 3) loadSlots();
    }

    function updateSummary() {
        const box = document.getElementById('rw-summary');
        const h = document.getElementById('rw-sum-hizmet');
        const t = document.getElementById('rw-sum-tarih');
        const s = document.getElementById('rw-sum-saat');
        let any = false;
        if (selectedHizmet.id) {
            h.textContent = selectedHizmet.ad + (selectedHizmet.sure ? ' · ' + selectedHizmet.sure + ' dk' : '');
            h.classList.remove('hidden');
            any = true;
        } else h.classList.add('hidden');
        const tarih = document.getElementById('rw-tarih').value;
        if (tarih) {
            const p = tarih.split('-');
            t.textContent = p.length === 3 ? (p[2] + '.' + p[1] + '.' + p[0]) : tarih;
            t.classList.remove('hidden');
            any = true;
        } else t.classList.add('hidden');
        if (selectedSaat) {
            s.textContent = selectedSaat;
            s.classList.remove('hidden');
            any = true;
        } else s.classList.add('hidden');
        box.classList.toggle('hidden', !any);
        box.classList.toggle('flex', any);
    }

    // Hizmet cards
    document.querySelectorAll('.rw-hizmet-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.rw-hizmet-card').forEach(c => c.classList.remove('is-selected'));
            card.classList.add('is-selected');
            selectedHizmet = {
                id: card.dataset.id,
                ad: card.dataset.ad,
                sure: card.dataset.sure,
            };
            document.getElementById('rw-hizmet-id').value = selectedHizmet.id;
            if (err(1)) err(1).classList.add('hidden');
            updateSummary();
        });
    });

    // Preselect old hizmet
    const oldHizmet = document.getElementById('rw-hizmet-id').value;
    if (oldHizmet) {
        const card = document.querySelector('.rw-hizmet-card[data-id="' + oldHizmet + '"]');
        if (card) card.click();
    }

    function formatTrDate(iso) {
        if (!iso) return '';
        const p = iso.split('-');
        return p.length === 3 ? (p[2] + '.' + p[1] + '.' + p[0]) : iso;
    }

    function loadSlots() {
        const tarih = document.getElementById('rw-date-input').value;
        document.getElementById('rw-tarih').value = tarih || '';
        document.getElementById('rw-saat-tarih-label').textContent = formatTrDate(tarih);
        const grid = document.getElementById('rw-slots-grid');
        const loading = document.getElementById('rw-slots-loading');
        const empty = document.getElementById('rw-slots-empty');
        grid.innerHTML = '';
        selectedSaat = '';
        document.getElementById('rw-saat').value = '';
        if (!tarih) {
            empty.classList.remove('hidden');
            loading.classList.add('hidden');
            return;
        }
        loading.classList.remove('hidden');
        empty.classList.add('hidden');

        fetch(slotsUrl + '?tarih=' + encodeURIComponent(tarih), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(r => r.json())
            .then(json => {
                loading.classList.add('hidden');
                const slots = (json.data && json.data.slots) ? json.data.slots : [];
                if (!slots.length) {
                    empty.classList.remove('hidden');
                    return;
                }
                empty.classList.add('hidden');
                slots.forEach(slot => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    const musait = !!slot.musait;
                    btn.className = 'rw-slot ' + (musait ? 'rw-slot-musait' : 'rw-slot-dolu');
                    btn.textContent = slot.saat;
                    btn.title = slot.etiket || '';
                    btn.disabled = !musait;
                    if (musait) {
                        btn.addEventListener('click', () => {
                            grid.querySelectorAll('.rw-slot-musait').forEach(b => b.classList.remove('is-selected'));
                            btn.classList.add('is-selected');
                            selectedSaat = slot.saat;
                            document.getElementById('rw-saat').value = slot.saat;
                            if (err(3)) err(3).classList.add('hidden');
                            updateSummary();
                        });
                    }
                    grid.appendChild(btn);
                });
            })
            .catch(() => {
                loading.classList.add('hidden');
                empty.classList.remove('hidden');
            });
    }

    function validateStep(n) {
        if (n === 1) {
            if (!document.getElementById('rw-hizmet-id').value) {
                err(1).classList.remove('hidden');
                return false;
            }
        }
        if (n === 2) {
            const d = document.getElementById('rw-date-input').value;
            if (!d) {
                err(2).classList.remove('hidden');
                return false;
            }
            document.getElementById('rw-tarih').value = d;
            // Saat sıfırla tarih değişince
            selectedSaat = '';
            document.getElementById('rw-saat').value = '';
        }
        if (n === 3) {
            if (!document.getElementById('rw-saat').value) {
                err(3).classList.remove('hidden');
                return false;
            }
        }
        return true;
    }

    document.getElementById('rw-btn-next').addEventListener('click', () => {
        if (!validateStep(step)) return;
        if (step < maxStep) setStep(step + 1);
    });

    function goBack() {
        if (step > 1) setStep(step - 1);
    }
    document.getElementById('rw-btn-back').addEventListener('click', goBack);
    document.getElementById('rw-btn-back-m').addEventListener('click', goBack);
    document.querySelectorAll('.rw-back-btn').forEach(b => b.addEventListener('click', () => setStep(2)));

    document.getElementById('rw-date-input').addEventListener('change', () => {
        document.getElementById('rw-tarih').value = document.getElementById('rw-date-input').value;
        if (err(2)) err(2).classList.add('hidden');
        updateSummary();
    });

    // Guest reCAPTCHA on submit
    if (isGuest) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.rcOk === '1') return;
            e.preventDefault();
            if (!validateStep(1) || !document.getElementById('rw-tarih').value || !document.getElementById('rw-saat').value) {
                setStep(1);
                return;
            }
            const btn = document.getElementById('rw-btn-submit');
            if (btn) { btn.disabled = true; btn.textContent = 'Doğrulanıyor…'; }
            const done = function (token) {
                const inp = document.getElementById('rw-recaptcha-token');
                if (inp) inp.value = token || '';
                form.dataset.rcOk = '1';
                form.submit();
            };
            if (typeof window.raGetRecaptchaToken === 'function') {
                window.raGetRecaptchaToken('randevu').then(done).catch(function () { done(''); });
            } else {
                done('');
            }
        });
    }

    setStep(1);
})();
</script>
@elseif($doktor->randevuya_acik_mi)
<section class="mb-10">
    <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 text-center">
        <p class="text-sm text-[#6B7280]">Bu hekim için henüz randevu hizmeti tanımlanmamış. Lütfen iletişime geçin.</p>
    </div>
</section>
@endif
