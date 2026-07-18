@php
    $aktifHizmetler = $doktor->hizmetler->where('aktif_mi', true)->values();
    $hastaAuth = Auth::guard('hasta')->check();
    $hastaUser = $hastaAuth ? Auth::guard('hasta')->user() : null;
    $onlineGorusmeAcik = (bool) $doktor->aktifPaket()?->hasFeature('online_gorusme');
    $slotsUrl = route('frontend.doktorlar.slotlar', $doktor->id);
    $formAction = $hastaAuth
        ? route('frontend.hasta.randevu.kaydet')
        : route('frontend.hasta.randevu.misafir');
    $calismaGunleri = $doktor->calismaSaatleri
        ->where('aktif_mi', true)
        ->pluck('gun')
        ->map(fn ($g) => (int) $g)
        ->values()
        ->all();
@endphp

@if($doktor->randevuya_acik_mi && $aktifHizmetler->isNotEmpty())
<section id="randevu-wizard" class="mb-10 scroll-mt-24">
    <div class="bg-white border border-[#E5E7EB]/80 rounded-[1.75rem] shadow-[0_4px_24px_rgba(15,23,42,0.04)] overflow-hidden">

        {{-- Minimal top bar --}}
        <div class="px-5 md:px-7 pt-5 pb-4 flex items-center justify-between gap-4">
            <div class="min-w-0">
                <h2 class="text-base md:text-lg font-extrabold font-display text-[#111827] tracking-tight">Randevu al</h2>
                <p class="text-[11px] text-slate-400 mt-0.5" id="rw-step-caption">Hizmet seçin</p>
            </div>
            <div class="flex items-center gap-1.5 shrink-0" id="rw-dots" aria-hidden="true">
                @for($i = 1; $i <= 4; $i++)
                    <span class="rw-dot h-1.5 rounded-full transition-all duration-300 {{ $i === 1 ? 'w-6 bg-[#C96A2B]' : 'w-1.5 bg-slate-200' }}" data-dot="{{ $i }}"></span>
                @endfor
            </div>
        </div>

        <div class="h-px bg-slate-100"></div>

        @if(session('basarili') || session('hata'))
            <div class="px-5 md:px-7 pt-4">
                @if(session('basarili'))
                    <div class="px-3 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 text-[12px] font-medium">{{ session('basarili') }}</div>
                @endif
                @if(session('hata'))
                    <div class="px-3 py-2.5 rounded-xl bg-red-50 text-red-700 text-[12px] font-medium">{{ session('hata') }}</div>
                @endif
            </div>
        @endif

        <form action="{{ $formAction }}" method="POST" id="rw-form" class="p-5 md:p-7">
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

            {{-- Chip summary --}}
            <div id="rw-summary" class="mb-5 hidden flex-wrap gap-1.5"></div>

            {{-- ═══ 1 HİZMET ═══ --}}
            <div class="rw-panel" data-panel="1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach($aktifHizmetler as $hizmet)
                        <button type="button"
                                class="rw-card rw-hizmet-card text-left p-4 rounded-2xl border border-slate-150 bg-[#FAFAFA] hover:border-[#E7B58A] hover:bg-[#FFFBF5] transition-all"
                                data-id="{{ $hizmet->id }}"
                                data-ad="{{ $hizmet->ad }}"
                                data-sure="{{ $hizmet->sure }}">
                            <div class="flex items-center gap-3">
                                <span class="rw-card-check w-5 h-5 rounded-full border-2 border-slate-200 shrink-0 flex items-center justify-center transition-all">
                                    <svg class="w-3 h-3 text-white opacity-0 transition-opacity" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[13px] font-bold text-[#111827] font-display leading-snug">{{ $hizmet->ad }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $hizmet->sure }} dakika</p>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
                <p id="rw-err-1" class="hidden mt-3 text-[11px] text-red-500">Bir hizmet seçin</p>
            </div>

            {{-- ═══ 2 TAKVİM ═══ --}}
            <div class="rw-panel hidden" data-panel="2">
                <div class="max-w-md mx-auto">
                    <div class="flex items-center justify-between mb-4">
                        <button type="button" id="rw-cal-prev" class="w-9 h-9 rounded-xl border border-slate-150 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-[#C96A2B] transition-all" aria-label="Önceki ay">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                        </button>
                        <p id="rw-cal-title" class="text-sm font-bold font-display text-[#111827] tracking-tight"></p>
                        <button type="button" id="rw-cal-next" class="w-9 h-9 rounded-xl border border-slate-150 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-[#C96A2B] transition-all" aria-label="Sonraki ay">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-7 gap-1 mb-1.5">
                        @foreach(['Pt','Sa','Ça','Pe','Cu','Ct','Pz'] as $d)
                            <div class="text-center text-[10px] font-bold text-slate-300 uppercase tracking-wider py-1">{{ $d }}</div>
                        @endforeach
                    </div>
                    <div id="rw-cal-grid" class="grid grid-cols-7 gap-1.5"></div>

                    <div class="mt-4 flex flex-wrap gap-3 text-[10px] text-slate-400">
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-md bg-[#FFF7ED] border border-[#E7B58A]"></span> Seçilebilir</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-md bg-slate-50 border border-slate-100"></span> Kapalı / geçmiş</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-md bg-[#C96A2B]"></span> Seçili</span>
                    </div>
                </div>
                <p id="rw-err-2" class="hidden mt-3 text-[11px] text-red-500 text-center">Takvimden bir gün seçin</p>
            </div>

            {{-- ═══ 3 SAAT ═══ --}}
            <div class="rw-panel hidden" data-panel="3">
                <p class="text-[12px] text-slate-500 mb-3">
                    <span id="rw-saat-tarih-label" class="font-semibold text-[#C96A2B]"></span>
                    · müsait saatler
                </p>
                <div id="rw-slots-loading" class="hidden py-10 text-center text-[12px] text-slate-400">Yükleniyor…</div>
                <div id="rw-slots-empty" class="hidden py-10 text-center space-y-2">
                    <p class="text-[12px] text-slate-500">Bu günde müsait saat yok.</p>
                    <button type="button" class="rw-goto-date text-[12px] font-bold text-[#C96A2B]">Başka gün seç</button>
                </div>
                <div id="rw-slots-grid" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2"></div>
                <div class="mt-3 flex flex-wrap gap-3 text-[10px] text-slate-400">
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-md bg-[#FFFBEB] border border-[#E7B58A]"></span> Müsait</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-md bg-slate-100 line-through"></span> Dolu</span>
                </div>
                <p id="rw-err-3" class="hidden mt-3 text-[11px] text-red-500">Müsait bir saat seçin</p>
            </div>

            {{-- ═══ 4 BİLGİ + GÖRÜŞME ═══ --}}
            <div class="rw-panel hidden" data-panel="4">
                @if($onlineGorusmeAcik)
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5 font-display">Görüşme türü</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-5">
                        <button type="button" class="rw-card rw-gorusme-card text-left p-4 rounded-2xl border border-slate-150 bg-[#FAFAFA] hover:border-[#E7B58A] hover:bg-[#FFFBF5] transition-all is-selected" data-value="yuz_yuze">
                            <div class="flex items-center gap-3">
                                <span class="rw-card-check w-5 h-5 rounded-full border-2 border-[#C96A2B] bg-[#C96A2B] shrink-0 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white opacity-100" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                </span>
                                <div>
                                    <p class="text-[13px] font-bold text-[#111827] font-display">Yüz yüze</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Muayenehanede</p>
                                </div>
                            </div>
                        </button>
                        <button type="button" class="rw-card rw-gorusme-card text-left p-4 rounded-2xl border border-slate-150 bg-[#FAFAFA] hover:border-[#E7B58A] hover:bg-[#FFFBF5] transition-all" data-value="online">
                            <div class="flex items-center gap-3">
                                <span class="rw-card-check w-5 h-5 rounded-full border-2 border-slate-200 shrink-0 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white opacity-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                </span>
                                <div>
                                    <p class="text-[13px] font-bold text-[#111827] font-display">Online</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Görüntülü görüşme</p>
                                </div>
                            </div>
                        </button>
                    </div>
                    <input type="hidden" name="gorusme_tipi" id="rw-gorusme" value="{{ old('gorusme_tipi', 'yuz_yuze') }}">
                @else
                    <input type="hidden" name="gorusme_tipi" value="yuz_yuze">
                @endif

                @if($hastaAuth)
                    <div class="mb-4 px-4 py-3.5 rounded-2xl bg-[#FAFAFA] border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Randevu sahibi</p>
                        <p class="text-[13px] font-bold text-[#111827]">{{ $hastaUser->ad_soyad }}</p>
                        <p class="text-[11px] text-slate-400">{{ $hastaUser->telefon }} · {{ $hastaUser->e_posta }}</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-2.5">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Ad</label>
                            <input type="text" name="ad" value="{{ old('ad') }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-[13px] focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]/20 outline-none bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Soyad</label>
                            <input type="text" name="soyad" value="{{ old('soyad') }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-[13px] focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]/20 outline-none bg-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-2.5">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Telefon</label>
                            <input type="tel" name="telefon" value="{{ old('telefon') }}" required placeholder="05xx xxx xx xx"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-[13px] focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]/20 outline-none bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">E-posta <span class="font-normal normal-case">(opsiyonel)</span></label>
                            <input type="email" name="e_posta" value="{{ old('e_posta') }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-[13px] focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]/20 outline-none bg-white">
                        </div>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Not <span class="font-normal normal-case">(opsiyonel)</span></label>
                    <textarea name="not" rows="2" placeholder="Şikayet veya notunuz…"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-[13px] focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]/20 outline-none resize-none bg-white">{{ old('not') }}</textarea>
                </div>

                @unless($hastaAuth)
                    <label class="flex items-start gap-2 text-[11px] text-slate-500 cursor-pointer mb-1">
                        <input type="checkbox" name="kvkk_onay" value="1" required class="mt-0.5 rounded border-slate-300 text-[#C96A2B] focus:ring-[#C96A2B]">
                        <span>Kişisel verilerimin randevu amacıyla işlenmesini kabul ediyorum.</span>
                    </label>
                    <p class="text-[11px] text-slate-400">Hesabınız var mı? <a href="{{ route('frontend.hasta.giris') }}" class="text-[#C96A2B] font-semibold hover:underline">Giriş yapın</a></p>
                @endunless
            </div>

            {{-- Nav --}}
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-2">
                <button type="button" id="rw-btn-back" class="hidden h-11 px-4 rounded-xl text-[12px] font-bold text-slate-500 hover:bg-slate-50 font-display transition-all">
                    Geri
                </button>
                <div class="flex-1"></div>
                <button type="button" id="rw-btn-next"
                        class="h-11 px-6 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-[12px] font-bold font-display tracking-wide shadow-sm transition-all">
                    İleri
                </button>
                <button type="submit" id="rw-btn-submit"
                        class="hidden h-11 px-6 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-[12px] font-bold font-display tracking-wide shadow-sm transition-all">
                    Randevu oluştur
                </button>
            </div>
        </form>
    </div>
</section>

<style>
    .rw-card.is-selected {
        border-color: #C96A2B !important;
        background: #FFF7ED !important;
        box-shadow: 0 0 0 1px #C96A2B;
    }
    .rw-card.is-selected .rw-card-check {
        border-color: #C96A2B !important;
        background: #C96A2B !important;
    }
    .rw-card.is-selected .rw-card-check svg {
        opacity: 1 !important;
    }
    .rw-day {
        aspect-ratio: 1;
        border-radius: 0.85rem;
        border: 1px solid transparent;
        font-size: 0.8rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        background: transparent;
        color: #111827;
    }
    .rw-day-muted {
        color: #D1D5DB;
        cursor: default;
        font-weight: 500;
    }
    .rw-day-off {
        color: #CBD5E1;
        background: #F8FAFC;
        cursor: not-allowed;
        font-weight: 500;
    }
    .rw-day-on {
        background: #FAFAFA;
        border-color: #F1F5F9;
        cursor: pointer;
        color: #1F2937;
    }
    .rw-day-on:hover {
        border-color: #E7B58A;
        background: #FFFBF5;
        color: #C96A2B;
    }
    .rw-day-on.is-selected {
        background: #C96A2B;
        border-color: #C96A2B;
        color: #fff;
        box-shadow: 0 4px 12px rgba(201, 106, 43, 0.25);
    }
    .rw-day-today:not(.is-selected) {
        box-shadow: inset 0 0 0 1.5px #E7B58A;
    }
    .rw-slot {
        padding: 0.7rem 0.25rem;
        border-radius: 0.85rem;
        text-align: center;
        font-size: 0.8rem;
        font-weight: 700;
        border: 1px solid transparent;
        transition: all 0.15s ease;
    }
    .rw-slot-musait {
        border-color: #F1F5F9;
        background: #FAFAFA;
        color: #1F2937;
        cursor: pointer;
    }
    .rw-slot-musait:hover {
        border-color: #E7B58A;
        background: #FFFBF5;
        color: #C96A2B;
    }
    .rw-slot-musait.is-selected {
        background: #C96A2B;
        border-color: #C96A2B;
        color: #fff;
        box-shadow: 0 4px 12px rgba(201, 106, 43, 0.22);
    }
    .rw-slot-dolu {
        border-color: #F1F5F9;
        background: #F8FAFC;
        color: #CBD5E1;
        text-decoration: line-through;
        cursor: not-allowed;
    }
    .rw-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        background: #FFF7ED;
        color: #C96A2B;
        border: 1px solid rgba(231, 181, 138, 0.35);
    }
</style>

<script>
(function () {
    const slotsUrl = @json($slotsUrl);
    const isGuest = @json(! $hastaAuth);
    const workDays = @json($calismaGunleri); // 1=Mon … 7=Sun
    const captions = ['', 'Hizmet seçin', 'Gün seçin', 'Saat seçin', 'Bilgilerinizi tamamlayın'];
    const monthNames = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];

    let step = 1;
    const maxStep = 4;
    let selectedHizmet = { id: '', ad: '', sure: '' };
    let selectedSaat = '';
    let calYear, calMonth; // month 0-11

    const form = document.getElementById('rw-form');
    if (!form) return;

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    calYear = today.getFullYear();
    calMonth = today.getMonth();

    function pad(n) { return String(n).padStart(2, '0'); }
    function iso(d) {
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function parseIso(s) {
        if (!s) return null;
        const p = s.split('-').map(Number);
        return new Date(p[0], p[1] - 1, p[2]);
    }
    function formatTr(isoStr) {
        const d = parseIso(isoStr);
        if (!d) return '';
        return pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear();
    }
    /** JS getDay: 0=Sun → DB: 1=Mon … 7=Sun */
    function dbDay(date) {
        const g = date.getDay();
        return g === 0 ? 7 : g;
    }
    function isWorkDay(date) {
        if (!workDays || !workDays.length) return true;
        return workDays.indexOf(dbDay(date)) !== -1;
    }

    function setStep(n) {
        step = n;
        document.querySelectorAll('.rw-panel').forEach(p => {
            p.classList.toggle('hidden', Number(p.dataset.panel) !== n);
        });
        document.querySelectorAll('.rw-dot').forEach(dot => {
            const s = Number(dot.dataset.dot);
            if (s === n) {
                dot.style.width = '1.5rem';
                dot.style.background = '#C96A2B';
            } else if (s < n) {
                dot.style.width = '0.375rem';
                dot.style.background = '#34D399';
            } else {
                dot.style.width = '0.375rem';
                dot.style.background = '#E2E8F0';
            }
        });
        const cap = document.getElementById('rw-step-caption');
        if (cap) cap.textContent = captions[n] || '';

        const back = document.getElementById('rw-btn-back');
        const next = document.getElementById('rw-btn-next');
        const sub = document.getElementById('rw-btn-submit');
        if (back) back.classList.toggle('hidden', n === 1);
        if (next) next.classList.toggle('hidden', n === maxStep);
        if (sub) sub.classList.toggle('hidden', n !== maxStep);

        updateSummary();
        if (n === 2) renderCalendar();
        if (n === 3) loadSlots();
    }

    function updateSummary() {
        const box = document.getElementById('rw-summary');
        const chips = [];
        if (selectedHizmet.id) chips.push(selectedHizmet.ad + (selectedHizmet.sure ? ' · ' + selectedHizmet.sure + ' dk' : ''));
        const t = document.getElementById('rw-tarih').value;
        if (t) chips.push(formatTr(t));
        if (selectedSaat) chips.push(selectedSaat);
        if (!chips.length) {
            box.classList.add('hidden');
            box.innerHTML = '';
            return;
        }
        box.classList.remove('hidden');
        box.classList.add('flex');
        box.innerHTML = chips.map(c => '<span class="rw-chip">' + c + '</span>').join('');
    }

    // ── Hizmet cards ──
    document.querySelectorAll('.rw-hizmet-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.rw-hizmet-card').forEach(c => c.classList.remove('is-selected'));
            card.classList.add('is-selected');
            selectedHizmet = { id: card.dataset.id, ad: card.dataset.ad, sure: card.dataset.sure };
            document.getElementById('rw-hizmet-id').value = selectedHizmet.id;
            hideErr(1);
            updateSummary();
        });
    });
    const oldH = document.getElementById('rw-hizmet-id').value;
    if (oldH) {
        const c = document.querySelector('.rw-hizmet-card[data-id="' + oldH + '"]');
        if (c) c.click();
    }

    // ── Görüşme cards ──
    document.querySelectorAll('.rw-gorusme-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.rw-gorusme-card').forEach(c => c.classList.remove('is-selected'));
            card.classList.add('is-selected');
            const inp = document.getElementById('rw-gorusme');
            if (inp) inp.value = card.dataset.value;
        });
    });
    const gOld = document.getElementById('rw-gorusme');
    if (gOld && gOld.value) {
        const gc = document.querySelector('.rw-gorusme-card[data-value="' + gOld.value + '"]');
        if (gc) {
            document.querySelectorAll('.rw-gorusme-card').forEach(c => c.classList.remove('is-selected'));
            gc.classList.add('is-selected');
        }
    }

    // ── Calendar ──
    function renderCalendar() {
        const title = document.getElementById('rw-cal-title');
        const grid = document.getElementById('rw-cal-grid');
        title.textContent = monthNames[calMonth] + ' ' + calYear;
        grid.innerHTML = '';

        const first = new Date(calYear, calMonth, 1);
        // Monday-first offset: Mon=0 … Sun=6
        let startPad = first.getDay() - 1;
        if (startPad < 0) startPad = 6;

        const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
        const selectedIso = document.getElementById('rw-tarih').value;
        const todayIso = iso(today);

        for (let i = 0; i < startPad; i++) {
            const cell = document.createElement('div');
            cell.className = 'rw-day rw-day-muted';
            cell.textContent = '';
            grid.appendChild(cell);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const d = new Date(calYear, calMonth, day);
            d.setHours(0, 0, 0, 0);
            const dIso = iso(d);
            const past = d < today;
            const work = isWorkDay(d);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = String(day);

            if (past || !work) {
                btn.className = 'rw-day rw-day-off';
                btn.disabled = true;
                btn.title = past ? 'Geçmiş' : 'Kapalı';
            } else {
                btn.className = 'rw-day rw-day-on' + (dIso === todayIso ? ' rw-day-today' : '');
                if (dIso === selectedIso) btn.classList.add('is-selected');
                btn.addEventListener('click', () => {
                    document.getElementById('rw-tarih').value = dIso;
                    selectedSaat = '';
                    document.getElementById('rw-saat').value = '';
                    hideErr(2);
                    updateSummary();
                    renderCalendar();
                });
            }
            grid.appendChild(btn);
        }
    }

    document.getElementById('rw-cal-prev').addEventListener('click', () => {
        calMonth--;
        if (calMonth < 0) { calMonth = 11; calYear--; }
        // Don't go before current month
        if (calYear < today.getFullYear() || (calYear === today.getFullYear() && calMonth < today.getMonth())) {
            calYear = today.getFullYear();
            calMonth = today.getMonth();
        }
        renderCalendar();
    });
    document.getElementById('rw-cal-next').addEventListener('click', () => {
        calMonth++;
        if (calMonth > 11) { calMonth = 0; calYear++; }
        renderCalendar();
    });

    // ── Slots ──
    function loadSlots() {
        const tarih = document.getElementById('rw-tarih').value;
        document.getElementById('rw-saat-tarih-label').textContent = formatTr(tarih);
        const grid = document.getElementById('rw-slots-grid');
        const loading = document.getElementById('rw-slots-loading');
        const empty = document.getElementById('rw-slots-empty');
        grid.innerHTML = '';
        selectedSaat = '';
        document.getElementById('rw-saat').value = '';
        updateSummary();

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
                    const ok = !!slot.musait;
                    btn.className = 'rw-slot ' + (ok ? 'rw-slot-musait' : 'rw-slot-dolu');
                    btn.textContent = slot.saat;
                    btn.disabled = !ok;
                    if (ok) {
                        btn.addEventListener('click', () => {
                            grid.querySelectorAll('.rw-slot-musait').forEach(b => b.classList.remove('is-selected'));
                            btn.classList.add('is-selected');
                            selectedSaat = slot.saat;
                            document.getElementById('rw-saat').value = slot.saat;
                            hideErr(3);
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

    function hideErr(n) {
        const e = document.getElementById('rw-err-' + n);
        if (e) e.classList.add('hidden');
    }
    function showErr(n) {
        const e = document.getElementById('rw-err-' + n);
        if (e) e.classList.remove('hidden');
    }

    function validateStep(n) {
        if (n === 1 && !document.getElementById('rw-hizmet-id').value) {
            showErr(1); return false;
        }
        if (n === 2 && !document.getElementById('rw-tarih').value) {
            showErr(2); return false;
        }
        if (n === 3 && !document.getElementById('rw-saat').value) {
            showErr(3); return false;
        }
        return true;
    }

    document.getElementById('rw-btn-next').addEventListener('click', () => {
        if (!validateStep(step)) return;
        if (step < maxStep) setStep(step + 1);
    });
    document.getElementById('rw-btn-back').addEventListener('click', () => {
        if (step > 1) setStep(step - 1);
    });
    document.querySelectorAll('.rw-goto-date').forEach(b => {
        b.addEventListener('click', () => setStep(2));
    });

    if (isGuest) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.rcOk === '1') return;
            e.preventDefault();
            if (!document.getElementById('rw-hizmet-id').value ||
                !document.getElementById('rw-tarih').value ||
                !document.getElementById('rw-saat').value) {
                setStep(1);
                return;
            }
            const btn = document.getElementById('rw-btn-submit');
            if (btn) { btn.disabled = true; btn.textContent = 'Gönderiliyor…'; }
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

    // Restore date month if old value
    const oldT = document.getElementById('rw-tarih').value;
    if (oldT) {
        const d = parseIso(oldT);
        if (d) { calYear = d.getFullYear(); calMonth = d.getMonth(); }
    }

    setStep(1);
})();
</script>
@elseif($doktor->randevuya_acik_mi)
<section class="mb-10">
    <div class="bg-white border border-slate-100 rounded-3xl p-6 text-center">
        <p class="text-sm text-slate-500">Bu hekim için henüz randevu hizmeti tanımlanmamış.</p>
    </div>
</section>
@endif
