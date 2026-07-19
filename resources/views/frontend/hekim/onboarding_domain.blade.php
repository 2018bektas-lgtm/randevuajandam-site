@extends('frontend.layouts.app')

@section('baslik', 'Domain seçimi - Kayıt - Randevu Ajandam')

@section('icerik')
@php
    $de = $eligibility ?? [];
    $tlds = $de['tlds'] ?? ['com', 'net'];
    $domainEligible = (bool) ($de['eligible'] ?? false);
    $hostingerReady = (bool) ($de['hostinger_ready'] ?? false);
    $mode = old('mode', request('mode', ''));
@endphp

<section class="relative bg-[#FAFAFA] py-12 md:py-16 min-h-[70vh] overflow-hidden">
    <div class="absolute top-[-10%] right-[-10%] w-[420px] h-[420px] rounded-full bg-[#E7B58A]/15 blur-[100px] pointer-events-none"></div>
    <div class="max-w-3xl mx-auto px-4 relative z-10 space-y-8">

        {{-- Wizard steps --}}
        <nav aria-label="Kayıt adımları" class="flex flex-wrap items-center justify-center gap-2 md:gap-0">
            @foreach($steps as $i => $step)
                @php
                    $done = $step['status'] === 'done';
                    $cur = $step['status'] === 'current';
                @endphp
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold
                        {{ $done ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : '' }}
                        {{ $cur ? 'bg-[#C96A2B] text-white shadow-sm' : '' }}
                        {{ $step['status'] === 'todo' ? 'bg-white text-slate-400 border border-slate-200' : '' }}">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px]
                            {{ $done ? 'bg-emerald-500 text-white' : ($cur ? 'bg-white/20' : 'bg-slate-100') }}">
                            @if($done) ✓ @else {{ $i + 1 }} @endif
                        </span>
                        {{ $step['label'] }}
                    </div>
                    @if($i < count($steps) - 1)
                        <span class="hidden md:inline text-slate-300 mx-1">→</span>
                    @endif
                </div>
            @endforeach
        </nav>

        <div class="text-center space-y-2">
            <h1 class="text-2xl md:text-3xl font-extrabold font-display text-[#111827] tracking-tight">
                Web sitenizin alan adı
            </h1>
            <p class="text-sm text-[#6B7280] max-w-lg mx-auto">
                @if($target === 'clinic')
                    Klinik kurumsal paketinize web sitesi dahil. Domaininizi seçin veya mevcut domaininizi bağlayın.
                @else
                    Özel web sitesi paketinize domain adımı dahil. Yeni isim alın (pakete dahil) veya mevcut domaininizi kullanın.
                @endif
            </p>
        </div>

        @if(session('hata'))
            <div class="rounded-xl border border-red-100 bg-red-50 text-red-800 text-sm px-4 py-3">{{ session('hata') }}</div>
        @endif
        @if(session('basarili'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-800 text-sm px-4 py-3">{{ session('basarili') }}</div>
        @endif

        {{-- Choice cards --}}
        <div class="grid md:grid-cols-2 gap-4" id="choice-cards">
            <button type="button" data-mode="byod"
                class="mode-card text-left p-5 rounded-2xl border-2 border-slate-200 bg-white hover:border-[#C96A2B] transition-all shadow-sm space-y-2">
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-[#C96A2B]">Zaten var</div>
                <h2 class="text-lg font-bold text-slate-900 font-display">Domainim var</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Örn. <strong>dr-ahmet.com</strong> — Hostinger’dan yeni satın alma yok. DNS’i (A/CNAME) sizin yönlendirmeniz gerekir.
                </p>
            </button>

            <button type="button" data-mode="included"
                class="mode-card text-left p-5 rounded-2xl border-2 border-slate-200 bg-white hover:border-emerald-500 transition-all shadow-sm space-y-2 {{ $domainEligible ? '' : 'opacity-70' }}"
                @if(! $domainEligible) title="{{ $de['reason'] ?? 'Domain hakkı yok' }}" @endif>
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600">Pakete dahil</div>
                <h2 class="text-lg font-bold text-slate-900 font-display">Yeni domain al</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    .{{ implode(' / .', $tlds) }} — <strong>1 yıl ek ücret yok</strong>.
                    @if(! $hostingerReady)
                        <span class="text-amber-700">(Hostinger API hazır değilse simülasyon)</span>
                    @endif
                </p>
                @if(! $domainEligible && !empty($de['reason']))
                    <p class="text-[11px] text-amber-800 bg-amber-50 rounded-lg px-2 py-1">{{ $de['reason'] }}</p>
                @endif
            </button>
        </div>

        {{-- BYOD panel --}}
        <div id="panel-byod" class="hidden bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-5">
            <h3 class="text-base font-bold text-slate-900 font-display">Mevcut domaininizi yazın</h3>
            <form method="POST" action="{{ route('frontend.hekim.onboarding.domain.byod') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="byod-domain" class="block text-sm font-medium text-slate-700 mb-1.5">Alan adı</label>
                    <input type="text" name="domain" id="byod-domain" value="{{ old('domain') }}" required
                        placeholder="ornek: dr-ahmet-yilmaz.com"
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-[#C96A2B] focus:border-[#C96A2B]">
                    @error('domain')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-400 mt-2">www olmadan yazın. DNS yönlendirmesi kurulumu panelde hatırlatılır.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="px-5 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-sm font-bold">
                        Domaini bağla ve devam et
                    </button>
                    <button type="button" class="back-choice px-4 py-3 rounded-xl border border-slate-200 text-sm text-slate-600">Geri</button>
                </div>
            </form>
        </div>

        {{-- Included domain panel --}}
        <div id="panel-included" class="hidden bg-white rounded-2xl border border-emerald-100 shadow-sm p-6 md:p-8 space-y-5">
            <h3 class="text-base font-bold text-slate-900 font-display">Yeni site adını sorgula</h3>
            @if(! $domainEligible)
                <p class="text-sm text-amber-800 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2">
                    {{ $de['reason'] ?? 'Pakete dahil domain hakkı şu an kullanılamıyor.' }}
                    Kendi domaininiz varsa “Domainim var” seçeneğini kullanın veya daha sonra panelden kurun.
                </p>
            @else
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" id="domain-sld" placeholder="ornek: dr-ahmet-yilmaz"
                        class="flex-1 px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    <button type="button" id="domain-check-btn"
                        class="px-5 py-3 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
                        Sorgula
                    </button>
                </div>
                <div id="domain-check-results" class="space-y-2"></div>
                <p id="domain-check-msg" class="text-xs text-slate-400 hidden"></p>

                <form id="domain-claim-form" method="POST" action="{{ route('frontend.hekim.onboarding.domain.claim') }}" class="hidden space-y-3">
                    @csrf
                    <input type="hidden" name="domain" id="claim-domain-val">
                    <p class="text-sm text-slate-700">Seçilen: <strong id="claim-domain-label"></strong>
                        — <span class="text-emerald-700 font-semibold">pakete dahil, ek ücret yok</span></p>
                    <button type="submit" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">
                        Bu domaini al ve siteyi aç
                    </button>
                </form>
            @endif
            <button type="button" class="back-choice text-sm text-slate-500 underline">← Seçimlere dön</button>
        </div>

        <div class="text-center pt-2">
            <form method="POST" action="{{ route('frontend.hekim.onboarding.domain.skip') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-slate-600 underline">
                    Şimdilik atla — daha sonra panelden kuracağım
                </button>
            </form>
        </div>
    </div>
</section>

<script>
(function () {
    const cards = document.querySelectorAll('.mode-card');
    const panelByod = document.getElementById('panel-byod');
    const panelIncluded = document.getElementById('panel-included');
    const choiceCards = document.getElementById('choice-cards');

    function showMode(mode) {
        choiceCards.classList.add('hidden');
        panelByod.classList.toggle('hidden', mode !== 'byod');
        panelIncluded.classList.toggle('hidden', mode !== 'included');
        cards.forEach(c => {
            c.classList.toggle('ring-2', c.dataset.mode === mode);
            c.classList.toggle('ring-[#C96A2B]', c.dataset.mode === mode && mode === 'byod');
            c.classList.toggle('ring-emerald-500', c.dataset.mode === mode && mode === 'included');
        });
    }

    cards.forEach(c => c.addEventListener('click', () => showMode(c.dataset.mode)));
    document.querySelectorAll('.back-choice').forEach(btn => {
        btn.addEventListener('click', () => {
            choiceCards.classList.remove('hidden');
            panelByod.classList.add('hidden');
            panelIncluded.classList.add('hidden');
        });
    });

    @if(old('domain') && !$domainEligible)
        showMode('byod');
    @elseif(old('domain'))
        // leave choice open
    @endif

    const checkBtn = document.getElementById('domain-check-btn');
    const sldEl = document.getElementById('domain-sld');
    const resultsEl = document.getElementById('domain-check-results');
    const msgEl = document.getElementById('domain-check-msg');
    const claimForm = document.getElementById('domain-claim-form');
    if (!checkBtn || !sldEl) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value;
    const checkUrl = @json($checkUrl);

    checkBtn.addEventListener('click', async () => {
        const sld = (sldEl.value || '').trim();
        if (sld.length < 2) {
            msgEl.textContent = 'En az 2 karakter girin.';
            msgEl.classList.remove('hidden');
            return;
        }
        checkBtn.disabled = true;
        checkBtn.textContent = 'Sorgulanıyor…';
        msgEl.classList.add('hidden');
        resultsEl.innerHTML = '';
        if (claimForm) claimForm.classList.add('hidden');
        try {
            const res = await fetch(checkUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ sld }),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || !json.success) {
                throw new Error(json.message || 'Sorgu başarısız');
            }
            const rows = json.data?.results || [];
            if (!rows.length) {
                resultsEl.innerHTML = '<p class="text-sm text-slate-500">Sonuç yok.</p>';
            } else {
                resultsEl.innerHTML = rows.map(r => {
                    const d = r.domain || '';
                    const ok = !!r.is_available;
                    const mock = r.mock ? ' <span class="text-[10px] text-amber-600">(simülasyon)</span>' : '';
                    return `<div class="flex items-center justify-between gap-3 p-3 rounded-xl border ${ok ? 'border-emerald-100 bg-emerald-50/50' : 'border-slate-100 bg-slate-50'}">
                        <div>
                            <span class="font-semibold text-sm text-slate-900">${d}</span>${mock}
                            <span class="block text-xs ${ok ? 'text-emerald-700' : 'text-slate-500'}">${ok ? 'Müsait — pakete dahil' : 'Dolu'}</span>
                        </div>
                        ${ok ? `<button type="button" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold" data-pick="${d}">Seç</button>` : ''}
                    </div>`;
                }).join('');
                resultsEl.querySelectorAll('[data-pick]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        document.getElementById('claim-domain-val').value = btn.dataset.pick;
                        document.getElementById('claim-domain-label').textContent = btn.dataset.pick;
                        claimForm.classList.remove('hidden');
                        claimForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    });
                });
            }
        } catch (e) {
            msgEl.textContent = e.message || 'Hata';
            msgEl.classList.remove('hidden');
        } finally {
            checkBtn.disabled = false;
            checkBtn.textContent = 'Sorgula';
        }
    });
})();
</script>
@endsection
