@extends('frontend.layouts.app')

@section('baslik', ($phase ?? 'pre_payment') === 'pre_payment' ? 'Domain seçimi - Ödeme öncesi' : 'Domain seçimi - Randevu Ajandam')

@section('icerik')
@php
    $phase = $phase ?? 'pre_payment';
    $isPre = $phase === 'pre_payment';
    $de = $eligibility ?? [];
    $tlds = $de['tlds'] ?? ['com', 'net'];
    $domainEligible = (bool) ($de['eligible'] ?? false);
    $hostingerReady = (bool) ($de['hostinger_ready'] ?? false);
    $paket = $secilenPaket ?? null;
    $periyot = $periyot ?? 'aylik';
@endphp

<section class="relative bg-[#FAFAFA] py-12 md:py-16 min-h-[70vh] overflow-hidden">
    <div class="absolute top-[-10%] right-[-10%] w-[420px] h-[420px] rounded-full bg-[#E7B58A]/15 blur-[100px] pointer-events-none"></div>
    <div class="max-w-3xl mx-auto px-4 relative z-10 space-y-8">

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
                @if($isPre)
                    <strong>Ödemeden önce</strong> domaininizi belirleyin.
                    @if($paket)
                        Seçilen paket: <span class="text-[#C96A2B] font-semibold">{{ $paket->ad }}</span>
                        ({{ $periyot === 'yillik' ? 'yıllık' : 'aylık' }}).
                    @endif
                    Hostinger kaydı / site kurulumu ödeme başarılı olduktan sonra otomatik yapılır.
                @else
                    Üyeliğiniz aktif. Domain seçerek web sitenizi tamamlayın.
                @endif
            </p>
        </div>

        @if(session('hata'))
            <div class="rounded-xl border border-red-100 bg-red-50 text-red-800 text-sm px-4 py-3">{{ session('hata') }}</div>
        @endif
        @if(session('basarili'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-800 text-sm px-4 py-3">{{ session('basarili') }}</div>
        @endif

        <div class="grid md:grid-cols-2 gap-4" id="choice-cards">
            <button type="button" data-mode="byod"
                class="mode-card text-left p-5 rounded-2xl border-2 border-slate-200 bg-white hover:border-[#C96A2B] transition-all shadow-sm space-y-2">
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-[#C96A2B]">Zaten var</div>
                <h2 class="text-lg font-bold text-slate-900 font-display">Domainim var</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Örn. <strong>dr-ahmet.com</strong> — yeni satın alma yok. DNS yönlendirmesi sizin sorumluluğunuzda.
                </p>
            </button>

            <button type="button" data-mode="included"
                class="mode-card text-left p-5 rounded-2xl border-2 border-slate-200 bg-white hover:border-emerald-500 transition-all shadow-sm space-y-2 {{ $domainEligible ? '' : 'opacity-70' }}">
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600">Pakete dahil</div>
                <h2 class="text-lg font-bold text-slate-900 font-display">Yeni domain al</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    .{{ implode(' / .', $tlds) }} — <strong>1 yıl ek ücret yok</strong>.
                    Müsaitliği şimdi soruyoruz; kayıt ödeme sonrası.
                </p>
                @if(! $domainEligible && !empty($de['reason']))
                    <p class="text-[11px] text-amber-800 bg-amber-50 rounded-lg px-2 py-1">{{ $de['reason'] }}</p>
                @endif
            </button>
        </div>

        {{-- BYOD --}}
        <div id="panel-byod" class="hidden bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 space-y-5">
            <h3 class="text-base font-bold text-slate-900 font-display">Mevcut domaininizi yazın</h3>
            <form method="POST"
                  action="{{ $isPre ? route('frontend.hekim.onboarding.domain.save') : route('frontend.hekim.onboarding.domain.byod') }}"
                  class="space-y-4">
                @csrf
                @if($isPre)
                    <input type="hidden" name="paket_id" value="{{ $paket?->id }}">
                    <input type="hidden" name="periyot" value="{{ $periyot }}">
                    <input type="hidden" name="mode" value="byod">
                @endif
                <div>
                    <label for="byod-domain" class="block text-sm font-medium text-slate-700 mb-1.5">Alan adı</label>
                    <input type="text" name="domain" id="byod-domain" value="{{ old('domain', $pending['domain'] ?? '') }}" required
                        placeholder="ornek: dr-ahmet-yilmaz.com"
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-[#C96A2B] focus:border-[#C96A2B]">
                    @error('domain')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="px-5 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-sm font-bold">
                        {{ $isPre ? 'Kaydet ve ödemeye geç' : 'Domaini bağla ve devam et' }}
                    </button>
                    <button type="button" class="back-choice px-4 py-3 rounded-xl border border-slate-200 text-sm text-slate-600">Geri</button>
                </div>
            </form>
        </div>

        {{-- Included --}}
        <div id="panel-included" class="hidden bg-white rounded-2xl border border-emerald-100 shadow-sm p-6 md:p-8 space-y-5">
            <h3 class="text-base font-bold text-slate-900 font-display">Yeni site adını sorgula</h3>
            @if(! $domainEligible)
                <p class="text-sm text-amber-800 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2">
                    {{ $de['reason'] ?? 'Pakete dahil domain bu planda yok.' }}
                    “Domainim var” seçeneğini kullanın veya atlayın.
                </p>
            @else
                <div class="space-y-3 max-w-2xl">
                    <div>
                        <label for="domain-sld" class="block text-xs font-bold text-slate-600 mb-1.5">Site adı (uzantısız)</label>
                        <div class="flex flex-col sm:flex-row gap-2 items-stretch">
                            <input type="text" id="domain-sld" placeholder="ornek: dr-ahmet-yilmaz" autocomplete="off"
                                class="flex-1 px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <select id="domain-tld" class="sm:w-36 px-3 py-3 rounded-xl border border-slate-300 text-sm font-semibold bg-white focus:ring-emerald-500 focus:border-emerald-500">
                                @foreach($tlds as $tld)
                                    <option value="{{ $tld }}" @selected($loop->first)>.{{ $tld }}</option>
                                @endforeach
                            </select>
                            <button type="button" id="domain-check-btn"
                                class="px-5 py-3 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 shrink-0">
                                Sorgula
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">
                            Önizleme: <strong id="domain-preview" class="font-mono text-slate-800">—</strong>
                            <span class="text-slate-400"> · Sadece isim yazın; uzantıyı listeden seçin.</span>
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2" id="tld-chips" role="group" aria-label="Uzantı seçimi">
                        @foreach($tlds as $tld)
                            <button type="button" data-tld="{{ $tld }}"
                                class="tld-chip px-3 py-1.5 rounded-full text-xs font-bold border transition-all {{ $loop->first ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300' }}">
                                .{{ $tld }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div id="domain-check-results" class="space-y-2"></div>
                <p id="domain-check-msg" class="text-xs text-slate-400 hidden"></p>

                <form id="domain-claim-form" method="POST"
                      action="{{ $isPre ? route('frontend.hekim.onboarding.domain.save') : route('frontend.hekim.onboarding.domain.claim') }}"
                      class="hidden space-y-3">
                    @csrf
                    @if($isPre)
                        <input type="hidden" name="paket_id" value="{{ $paket?->id }}">
                        <input type="hidden" name="periyot" value="{{ $periyot }}">
                        <input type="hidden" name="mode" value="included">
                    @endif
                    <input type="hidden" name="domain" id="claim-domain-val">
                    <p class="text-sm text-slate-700">Seçilen: <strong id="claim-domain-label"></strong>
                        — <span class="text-emerald-700 font-semibold">pakete dahil, ek ücret yok</span>
                        @if($isPre)
                            <span class="block text-xs text-slate-500 mt-1">Ödeme sonrası Hostinger’a kaydedilir.</span>
                        @endif
                    </p>
                    <button type="submit" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">
                        {{ $isPre ? 'Bu domaini seç ve ödemeye geç' : 'Bu domaini al ve siteyi aç' }}
                    </button>
                </form>
            @endif
            <button type="button" class="back-choice text-sm text-slate-500 underline">← Seçimlere dön</button>
        </div>

        <div class="text-center pt-2 space-y-2">
            @if($isPre)
                <form method="POST" action="{{ route('frontend.hekim.onboarding.domain.skip_pre') }}" class="inline">
                    @csrf
                    <input type="hidden" name="paket_id" value="{{ $paket?->id }}">
                    <input type="hidden" name="periyot" value="{{ $periyot }}">
                    <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-slate-600 underline">
                        Domaini atla — doğrudan ödemeye git
                    </button>
                </form>
                <p class="text-[11px] text-slate-400">
                    <a href="{{ route('frontend.hekim.paket_sec') }}" class="underline">← Paket seçimine dön</a>
                </p>
            @else
                <form method="POST" action="{{ route('frontend.hekim.onboarding.domain.skip') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-slate-600 underline">
                        Şimdilik atla — panelden kuracağım
                    </button>
                </form>
            @endif
        </div>
    </div>
</section>

<script>
(function () {
    const cards = document.querySelectorAll('.mode-card');
    const panelByod = document.getElementById('panel-byod');
    const panelIncluded = document.getElementById('panel-included');
    const choiceCards = document.getElementById('choice-cards');
    const isPre = @json($isPre);
    const paketId = @json($paket?->id);
    const checkUrl = @json($checkUrl);
    const allowedTlds = @json(array_values($tlds));

    function showMode(mode) {
        choiceCards.classList.add('hidden');
        panelByod.classList.toggle('hidden', mode !== 'byod');
        panelIncluded.classList.toggle('hidden', mode !== 'included');
    }

    cards.forEach(c => c.addEventListener('click', () => showMode(c.dataset.mode)));
    document.querySelectorAll('.back-choice').forEach(btn => {
        btn.addEventListener('click', () => {
            choiceCards.classList.remove('hidden');
            panelByod.classList.add('hidden');
            panelIncluded.classList.add('hidden');
        });
    });

    const checkBtn = document.getElementById('domain-check-btn');
    const sldEl = document.getElementById('domain-sld');
    const tldEl = document.getElementById('domain-tld');
    const previewEl = document.getElementById('domain-preview');
    const resultsEl = document.getElementById('domain-check-results');
    const msgEl = document.getElementById('domain-check-msg');
    const claimForm = document.getElementById('domain-claim-form');
    if (!checkBtn || !sldEl || !tldEl) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value;

    /** Kullanıcı yanlışlıkla .com yazdıysa ayır */
    function parseSldInput(raw) {
        let v = (raw || '').trim().toLowerCase();
        v = v.replace(/^https?:\/\//, '').replace(/^www\./, '').replace(/\/.*$/, '');
        for (const t of allowedTlds) {
            const suf = '.' + t;
            if (v.endsWith(suf)) {
                return { sld: v.slice(0, -suf.length).replace(/[^a-z0-9\-]/g, ''), tld: t };
            }
        }
        // genel .uzanti
        const m = v.match(/^([a-z0-9\-]+)\.([a-z0-9.]+)$/);
        if (m) {
            return { sld: m[1], tld: allowedTlds.includes(m[2]) ? m[2] : null };
        }
        return { sld: v.replace(/[^a-z0-9\-]/g, ''), tld: null };
    }

    function selectedTld() {
        return (tldEl.value || allowedTlds[0] || 'com').replace(/^\./, '');
    }

    function updatePreview() {
        const p = parseSldInput(sldEl.value);
        const tld = p.tld || selectedTld();
        if (p.tld && p.tld !== selectedTld()) {
            tldEl.value = p.tld;
            syncChips(p.tld);
        }
        if (p.sld !== sldEl.value.replace(/[^a-z0-9\-.]/gi, '') && p.sld) {
            // sadece sld kısmını temiz tut (tam domain yazıldıysa input'u sadeleştirme opsiyonel)
        }
        previewEl.textContent = p.sld.length >= 2 ? (p.sld + '.' + tld) : '—';
    }

    function syncChips(tld) {
        document.querySelectorAll('.tld-chip').forEach(btn => {
            const on = btn.dataset.tld === tld;
            btn.classList.toggle('bg-emerald-600', on);
            btn.classList.toggle('text-white', on);
            btn.classList.toggle('border-emerald-600', on);
            btn.classList.toggle('bg-white', !on);
            btn.classList.toggle('text-slate-600', !on);
            btn.classList.toggle('border-slate-200', !on);
        });
    }

    document.querySelectorAll('.tld-chip').forEach(btn => {
        btn.addEventListener('click', () => {
            tldEl.value = btn.dataset.tld;
            syncChips(btn.dataset.tld);
            updatePreview();
        });
    });
    tldEl.addEventListener('change', () => { syncChips(selectedTld()); updatePreview(); });
    sldEl.addEventListener('input', updatePreview);
    updatePreview();

    checkBtn.addEventListener('click', async () => {
        const parsed = parseSldInput(sldEl.value);
        const sld = parsed.sld;
        const tld = parsed.tld || selectedTld();
        if (sld.length < 2) {
            msgEl.textContent = 'En az 2 karakterlik site adı girin (uzantısız).';
            msgEl.classList.remove('hidden');
            return;
        }
        if (!allowedTlds.includes(tld)) {
            msgEl.textContent = 'Bu uzantı pakete dahil değil. Seçenekler: .' + allowedTlds.join(', .');
            msgEl.classList.remove('hidden');
            return;
        }
        // Input'u sade isme çevir
        sldEl.value = sld;
        tldEl.value = tld;
        syncChips(tld);
        updatePreview();

        checkBtn.disabled = true;
        checkBtn.textContent = 'Sorgulanıyor…';
        msgEl.classList.add('hidden');
        resultsEl.innerHTML = '';
        if (claimForm) claimForm.classList.add('hidden');
        try {
            const body = { sld, tlds: [tld] };
            if (isPre && paketId) body.paket_id = paketId;
            const res = await fetch(checkUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || !json.success) {
                throw new Error(json.message || 'Sorgu başarısız');
            }
            const rows = json.data?.results || [];
            if (!rows.length) {
                resultsEl.innerHTML = '<p class="text-sm text-slate-500">Sonuç yok.</p>';
            } else {
                const primary = rows.filter(r => !r.is_alternative);
                const alts = rows.filter(r => !!r.is_alternative && !!r.is_available);
                const renderRow = (r) => {
                    const d = r.domain || '';
                    const ok = !!r.is_available;
                    const alt = !!r.is_alternative;
                    const mock = r.mock ? ' <span class="text-[10px] text-amber-600">(simülasyon)</span>' : '';
                    let badge = ok
                        ? (alt ? 'Benzer öneri — uygun' : 'Uygun — pakete dahil')
                        : 'Uygun değil (dolu)';
                    const border = ok
                        ? (alt ? 'border-sky-100 bg-sky-50/50' : 'border-emerald-100 bg-emerald-50/50')
                        : 'border-slate-100 bg-slate-50';
                    const text = ok ? (alt ? 'text-sky-800' : 'text-emerald-700') : 'text-slate-500';
                    return `<div class="flex items-center justify-between gap-3 p-3 rounded-xl border ${border}">
                        <div>
                            <span class="font-semibold text-sm text-slate-900">${d}</span>${mock}
                            <span class="block text-xs ${text}">${badge}</span>
                        </div>
                        ${ok ? `<button type="button" class="px-3 py-1.5 rounded-lg ${alt ? 'bg-sky-600' : 'bg-emerald-600'} text-white text-xs font-bold" data-pick="${d}">Seç</button>` : ''}
                    </div>`;
                };
                let html = primary.map(renderRow).join('');
                if (alts.length) {
                    html += `<p class="text-xs font-bold text-sky-800 pt-2">Benzer / alternatif uygun domainler</p>`;
                    html += alts.map(renderRow).join('');
                } else if (primary.length && primary.every(r => !r.is_available)) {
                    html += `<p class="text-xs text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2 mt-1">Bu isim + uzantı dolu. Başka uzantı seçin, farklı isim deneyin veya “Domainim var” kullanın.</p>`;
                }
                resultsEl.innerHTML = html;
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
