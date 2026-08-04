@extends('frontend.layouts.app')

@section('baslik', \App\Support\SeoMeta::packagesTitle())
@section('meta_aciklama', \App\Support\SeoMeta::packagesDescription())
@section('meta_anahtar_kelimeler', \App\Support\SeoMeta::keywords([
    'hekim randevu yazılımı', 'klinik yönetim sistemi', 'online randevu paketi',
    'doktor randevu sistemi fiyat', 'randevu ajandam paket',
]))

@section('icerik')
@php
    $referansRefRaw = request('ref') ?: session('ra_ref') ?: request()->cookie('ra_ref');
    $referansRef = (is_string($referansRefRaw) && preg_match('/^[A-Za-z0-9]{4,16}$/', $referansRefRaw))
        ? strtoupper($referansRefRaw)
        : null;
@endphp
<style>

    .pricing-page {
        --brand: #C96A2B;
        --brand-dark: #B55A20;
        --brand-soft: #FFF7ED;
        --brand-line: rgba(231, 181, 138, 0.45);
        --ink: #0F172A;
        --muted: #64748B;
        --line: #E2E8F0;
        --card: #FFFFFF;
        --bg: #F8FAFC;
    }

    .pricing-page .toggle-container {
        position: relative;
        background: rgba(255, 255, 255, 0.72);
        backdrop-filter: blur(10px);
        border: 1px solid var(--line);
        border-radius: 9999px;
        padding: 5px;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }
    .pricing-page .toggle-btn {
        position: relative;
        z-index: 10;
        padding: 11px 22px;
        font-size: 13px;
        font-weight: 700;
        color: var(--muted);
        transition: color 0.25s ease;
        border-radius: 9999px;
        border: none;
        background: none;
        outline: none;
        cursor: pointer;
        font-family: inherit;
        white-space: nowrap;
    }
    .pricing-page .toggle-btn.active { color: var(--ink); }
    .pricing-page .toggle-slider {
        position: absolute;
        top: 5px;
        bottom: 5px;
        left: 5px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
        border-radius: 9999px;
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), width 0.35s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .pricing-page .plan-container {
        transition: opacity 0.28s ease, transform 0.28s ease;
    }
    .pricing-page .plan-container.fade-out {
        opacity: 0;
        transform: translateY(8px) scale(0.985);
    }
    .pricing-page .plan-container.is-hidden { display: none !important; }
    .pricing-page .matrix-panel.is-hidden { display: none !important; }

    .pricing-page .billing-price-monthly,
    .pricing-page .billing-price-yearly {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        transition: opacity 0.15s ease;
    }
    .pricing-page .billing-price-monthly.is-hidden,
    .pricing-page .billing-price-yearly.is-hidden {
        display: none !important;
    }

    /* N paket → tek satır (lg+); mobil/tablet sarmalanır */
    .pricing-page .plans-equal-row {
        --plan-count: 4;
        display: grid;
        gap: 1rem;
        align-items: stretch;
        width: 100%;
        grid-template-columns: 1fr;
    }
    @media (min-width: 640px) {
        .pricing-page .plans-equal-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.15rem;
        }
    }
    @media (min-width: 1024px) {
        .pricing-page .plans-equal-row {
            /* Kaç paket olursa olsun hepsi yan yana */
            grid-template-columns: repeat(var(--plan-count), minmax(0, 1fr));
            gap: 0.85rem;
        }
    }
    @media (min-width: 1400px) {
        .pricing-page .plans-equal-row {
            gap: 1.15rem;
        }
    }
    .pricing-page .plans-equal-row .price-card {
        min-width: 0; /* grid taşmasını engelle */
    }
    /* 5+ pakette kart içi daha sıkı */
    .pricing-page .plans-equal-row[data-count="5"] .price-card,
    .pricing-page .plans-equal-row[data-count="6"] .price-card,
    .pricing-page .plans-equal-row[data-count="7"] .price-card,
    .pricing-page .plans-equal-row[data-count="8"] .price-card {
        padding: 1.25rem 1rem;
        border-radius: 22px;
    }
    .pricing-page .plans-equal-row[data-count="5"] .price-card .text-\[2\.55rem\],
    .pricing-page .plans-equal-row[data-count="6"] .price-card .text-\[2\.55rem\] {
        font-size: 2.05rem !important;
    }

    .pricing-page .price-card {
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 28px;
        padding: 1.75rem;
        overflow: hidden;
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.35s ease, border-color 0.35s ease;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
    }
    .pricing-page .price-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 22px 50px rgba(15, 23, 42, 0.08);
        border-color: #CBD5E1;
    }
    .pricing-page .price-card::after {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: radial-gradient(circle at top right, rgba(201, 106, 43, 0.07), transparent 42%);
        opacity: 0;
        transition: opacity 0.35s ease;
    }
    .pricing-page .price-card:hover::after { opacity: 1; }

    .pricing-page .price-card.featured {
        border-color: transparent;
        background:
            linear-gradient(#fff, #fff) padding-box,
            linear-gradient(145deg, #C96A2B, #E7B58A 45%, #F59E0B) border-box;
        border: 1.5px solid transparent;
        box-shadow: 0 18px 40px rgba(201, 106, 43, 0.14);
        transform: translateY(-4px);
    }
    .pricing-page .price-card.featured:hover {
        transform: translateY(-10px);
        box-shadow: 0 28px 60px rgba(201, 106, 43, 0.18);
    }
    /* Çok paket yan yana iken featured kartı hizayı bozmasın */
    @media (min-width: 1024px) {
        .pricing-page .plans-equal-row[data-count="5"] .price-card.featured,
        .pricing-page .plans-equal-row[data-count="6"] .price-card.featured,
        .pricing-page .plans-equal-row[data-count="7"] .price-card.featured,
        .pricing-page .plans-equal-row[data-count="8"] .price-card.featured {
            transform: none;
        }
        .pricing-page .plans-equal-row[data-count="5"] .price-card.featured:hover,
        .pricing-page .plans-equal-row[data-count="6"] .price-card.featured:hover {
            transform: translateY(-4px);
        }
    }
    .pricing-page .price-card.website {
        border-style: solid;
        border-color: rgba(201, 106, 43, 0.28);
        background:
            linear-gradient(180deg, #FFFBF7 0%, #FFFFFF 48%);
    }

    .pricing-page .price-card .ribbon {
        position: absolute;
        top: 16px;
        right: 16px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        font-family: Outfit, Inter, sans-serif;
    }
    .pricing-page .ribbon-popular {
        background: linear-gradient(135deg, #C96A2B, #E08A4A);
        color: #fff;
        box-shadow: 0 8px 18px rgba(201, 106, 43, 0.28);
    }
    .pricing-page .ribbon-web {
        background: #FFF7ED;
        color: #C96A2B;
        border: 1px solid rgba(231, 181, 138, 0.5);
    }
    .pricing-page .ribbon-free {
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
    }
    .pricing-page .ribbon-custom {
        background: #F1F5F9;
        color: #334155;
        border: 1px solid #E2E8F0;
    }

    .pricing-page .plan-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        background: var(--brand-soft);
        color: var(--brand);
        border: 1px solid var(--brand-line);
        margin-bottom: 1.1rem;
        flex-shrink: 0;
    }
    .pricing-page .price-card.featured .plan-icon {
        background: linear-gradient(145deg, #C96A2B, #E08A4A);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 10px 20px rgba(201, 106, 43, 0.25);
    }

    .pricing-page .feature-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 12.5px;
        line-height: 1.45;
        color: #475569;
    }
    .pricing-page .feature-more {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 0.65rem;
        font-size: 11.5px;
        font-weight: 700;
        color: #C96A2B;
        text-decoration: none;
    }
    .pricing-page .feature-more:hover { text-decoration: underline; }
    .pricing-page .feature-count {
        display: inline-flex;
        align-items: center;
        margin-top: 0.5rem;
        padding: 4px 9px;
        border-radius: 999px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        font-size: 10.5px;
        font-weight: 700;
        color: #64748B;
    }
    .pricing-page .matrix-wrap {
        border: 1px solid #E2E8F0;
        border-radius: 1.25rem;
        background: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .pricing-page .matrix-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 12px 14px;
        border-bottom: 1px solid #E2E8F0;
        background: #F8FAFC;
    }
    .pricing-page .matrix-tab {
        border: 1px solid #E2E8F0;
        background: #fff;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        padding: 7px 12px;
        border-radius: 999px;
        cursor: pointer;
        transition: all .15s ease;
    }
    .pricing-page .matrix-tab.active {
        background: #C96A2B;
        border-color: #C96A2B;
        color: #fff;
    }
    .pricing-page .matrix-group { display: none; }
    .pricing-page .matrix-group.active { display: table-row-group; }
    .pricing-page .matrix-table th.sticky-col,
    .pricing-page .matrix-table td.sticky-col {
        position: sticky;
        left: 0;
        z-index: 1;
        background: #fff;
        box-shadow: 4px 0 8px -6px rgba(15, 23, 42, 0.18);
    }
    .pricing-page .matrix-table thead th.sticky-col { background: #F8FAFC; z-index: 2; }
    .pricing-page .matrix-search {
        width: 100%;
        max-width: 280px;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 9px 12px;
        font-size: 12.5px;
        outline: none;
    }
    .pricing-page .matrix-search:focus {
        border-color: #E7B58A;
        box-shadow: 0 0 0 3px rgba(201, 106, 43, 0.12);
    }
    .pricing-page .matrix-row-hidden { display: none !important; }
    .pricing-page .feature-check {
        width: 18px;
        height: 18px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        flex-shrink: 0;
        margin-top: 1px;
        background: #ECFDF5;
        color: #059669;
        border: 1px solid #A7F3D0;
    }
    .pricing-page .feature-check.brand {
        background: var(--brand-soft);
        color: var(--brand);
        border-color: var(--brand-line);
    }

    .pricing-page .btn-plan {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 0.95rem 1rem;
        border-radius: 16px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        transition: all 0.22s ease;
        font-family: Outfit, Inter, sans-serif;
    }
    .pricing-page .btn-plan-primary {
        background: linear-gradient(135deg, #C96A2B, #D87A3C);
        color: #fff;
        box-shadow: 0 10px 22px rgba(201, 106, 43, 0.28);
    }
    .pricing-page .btn-plan-primary:hover {
        filter: brightness(1.05);
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(201, 106, 43, 0.34);
    }
    .pricing-page .btn-plan-ghost {
        background: #F8FAFC;
        color: #0F172A;
        border: 1px solid #E2E8F0;
    }
    .pricing-page .btn-plan-ghost:hover {
        background: #FFF7ED;
        border-color: rgba(201, 106, 43, 0.35);
        color: #C96A2B;
    }

    .pricing-page .trust-strip {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    @media (min-width: 768px) {
        .pricing-page .trust-strip { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
    .pricing-page .trust-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 18px;
        background: rgba(255,255,255,0.8);
        border: 1px solid var(--line);
        backdrop-filter: blur(8px);
    }
</style>

<section class="pricing-page fe-page relative bg-[#F8FAFC] overflow-hidden select-none">
    <!-- Ambient -->
    <div class="absolute top-[-18%] right-[-12%] w-[640px] h-[640px] rounded-full bg-[#E7B58A]/15 blur-[130px] pointer-events-none"></div>
    <div class="absolute bottom-[-12%] left-[-10%] w-[560px] h-[560px] rounded-full bg-[#C96A2B]/10 blur-[120px] pointer-events-none"></div>
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-[#E7B58A]/50 to-transparent"></div>

    <div class="max-w-[1600px] mx-auto px-4 sm:px-5 lg:px-6 relative z-10">
        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto mb-12 md:mb-14">
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/35 rounded-full text-[11px] font-bold font-display uppercase tracking-[0.14em] mb-5">
                <span class="w-1.5 h-1.5 rounded-full bg-[#C96A2B] animate-pulse"></span>
                Fiyatlandırma
            </span>
            <h1 class="text-4xl md:text-5xl lg:text-[3.4rem] font-extrabold font-display text-[#0F172A] tracking-tight leading-[1.08]">
                İhtiyacınıza uygun
                <span class="block mt-1 bg-gradient-to-r from-[#C96A2B] via-[#D4894A] to-[#B55A20] bg-clip-text text-transparent">
                    net ve esnek planlar
                </span>
            </h1>
            <p class="text-sm md:text-[15px] text-slate-500 max-w-2xl mx-auto mt-5 leading-relaxed">
                Önce planınızı seçin, ardından kayıt ve belge onayı gelir; onay sonrası aynı paketle ödemeye geçersiniz.
                Yıllık ödemede daha avantajlı fiyat.
            </p>
            <p class="mt-3 text-xs font-bold text-[#C96A2B]">Fiyatlara KDV dahildir.</p>

            @if(session('hata'))
                <div class="mt-6 max-w-md mx-auto p-4 bg-red-50 border border-red-200 rounded-2xl text-xs text-red-600 font-semibold">
                    {{ session('hata') }}
                </div>
            @endif
            @if(session('basarili'))
                <div class="mt-6 max-w-md mx-auto p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800 font-semibold">
                    {{ session('basarili') }}
                </div>
            @endif

            <!-- Toggles -->
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <div class="toggle-container" id="billingToggle">
                    <div class="toggle-slider" id="billingSlider"></div>
                    <button type="button" class="toggle-btn active" id="btnMonthly" onclick="toggleBilling('aylik')">
                        Aylık
                    </button>
                    <button type="button" class="toggle-btn" id="btnYearly" onclick="toggleBilling('yillik')">
                        Yıllık
                        @if(!empty($maxYillikTasarrufYuzde) && $maxYillikTasarrufYuzde > 0)
                        <span class="ml-1.5 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-100 text-[9px] font-extrabold text-emerald-700 uppercase tracking-wider">
                            %{{ $maxYillikTasarrufYuzde }}’e varan
                        </span>
                        @else
                        <span class="ml-1.5 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-100 text-[9px] font-extrabold text-emerald-700 uppercase tracking-wider">
                            Tasarruf
                        </span>
                        @endif
                    </button>
                </div>

                <div class="toggle-container" id="typeToggle">
                    <div class="toggle-slider" id="typeSlider"></div>
                    <button type="button" class="toggle-btn active" id="btnBireysel" onclick="togglePackageType('bireysel')">
                        Bireysel
                    </button>
                    <button type="button" class="toggle-btn" id="btnKlinik" onclick="togglePackageType('klinik')">
                        Klinik
                    </button>
                </div>
            </div>
        </div>

        <!-- Bireysel: paket sayısı kadar kolon (hepsi yan yana) -->
        <div id="bireyselPlans"
             class="plan-container plans-equal-row"
             data-count="{{ max(1, $bireyselPaketler->count()) }}"
             style="--plan-count: {{ max(1, $bireyselPaketler->count()) }}">
            @forelse($bireyselPaketler as $p)
                @php
                    $isFree = (float) $p->aylik_fiyat == 0;
                    $trialDaysPublic = (int) ($p->deneme_gun ?? 0);
                    $isWebsite = \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($p->ad), 'web sitesi')
                        || (method_exists($p, 'hasFeature') && $p->hasFeature('web_sitesi'))
                        || (bool) ($p->domain_dahil_mi ?? false);
                    $vitrin = method_exists($p, 'vitrinEtiketi') ? $p->vitrinEtiketi() : null;
                    $isFeatured = (bool) ($p->one_cikan_mi ?? false)
                        || in_array($vitrin['stil'] ?? '', ['popular'], true);
                    $ribbonClass = match ($vitrin['stil'] ?? '') {
                        'popular' => 'ribbon-popular',
                        'web' => 'ribbon-web',
                        'free', 'trial' => 'ribbon-free',
                        default => 'ribbon-custom',
                    };
                    $cardClass = 'price-card';
                    if ($isFeatured) {
                        $cardClass .= ' featured';
                    }
                    if ($isWebsite) {
                        $cardClass .= ' website';
                    }
                @endphp
                <article class="{{ $cardClass }}">
                    @if($trialDaysPublic > 0)
                        <span class="ribbon ribbon-free">{{ $trialDaysPublic }} gün deneme</span>
                    @elseif($vitrin)
                        <span class="ribbon {{ $ribbonClass }}">
                            @if(($vitrin['stil'] ?? '') === 'popular')
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endif
                            {{ $vitrin['label'] }}
                        </span>
                    @endif

                    <div class="relative z-[1] flex flex-col h-full">
                        <div class="plan-icon">
                            @if($isWebsite)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h16.8M3.6 15h16.8M12 3c2.5 2.8 3.8 5.9 3.8 9s-1.3 6.2-3.8 9c-2.5-2.8-3.8-5.9-3.8-9s1.3-6.2 3.8-9z"/></svg>
                            @elseif($isFree)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            @endif
                        </div>

                        <div class="mb-5 pr-16">
                            <h3 class="text-[17px] font-bold font-display text-[#0F172A] leading-snug">{{ $p->ad }}</h3>
                            <p class="text-[12.5px] text-slate-500 mt-2 leading-relaxed min-h-[40px]">{{ $p->aciklama }}</p>
                            @if($trialDaysPublic > 0)
                                <div class="mt-3 rounded-xl border-2 border-emerald-400 bg-emerald-50 px-3 py-2.5">
                                    <p class="text-[15px] font-extrabold text-emerald-900 font-display leading-none">{{ $trialDaysPublic }} GÜN ÜCRETSİZ DENEME</p>
                                    <p class="text-[11px] font-semibold text-emerald-800 mt-1.5 leading-snug">
                                        Deneme bitince tam ücret ödersiniz. Otomatik çekim yok.
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="mb-6 pb-6 border-b border-slate-100 min-h-[88px]">
                            <div class="billing-price-monthly">
                                @if($p->aylik_indirimli_fiyat)
                                    <div class="flex items-end gap-1">
                                        <span class="text-sm font-bold text-slate-400 mb-1.5">₺</span>
                                        <span class="text-[2.55rem] leading-none font-extrabold font-display text-[#0F172A] tracking-tight">{{ number_format($p->aylik_indirimli_fiyat, 0, ',', '.') }}</span>
                                        <span class="text-xs font-semibold text-slate-400 mb-1.5">/ ay</span>
                                    </div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs text-slate-400 line-through">₺{{ number_format($p->aylik_fiyat, 0, ',', '.') }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-[10px] font-bold text-emerald-700 border border-emerald-100">
                                            ₺{{ number_format($p->aylik_fiyat - $p->aylik_indirimli_fiyat, 0, ',', '.') }} tasarruf
                                        </span>
                                    </div>
                                @else
                                    <div class="flex items-end gap-1">
                                        @if($isFree)
                                            <span class="text-[2.2rem] leading-none font-extrabold font-display text-[#C96A2B] tracking-tight">Ücretsiz</span>
                                        @else
                                            <span class="text-sm font-bold text-slate-400 mb-1.5">₺</span>
                                            <span class="text-[2.55rem] leading-none font-extrabold font-display text-[#0F172A] tracking-tight">{{ number_format($p->aylik_fiyat, 0, ',', '.') }}</span>
                                            <span class="text-xs font-semibold text-slate-400 mb-1.5">/ ay</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="billing-price-yearly is-hidden">
                                @if($p->yillik_indirimli_fiyat)
                                    <div class="flex items-end gap-1">
                                        <span class="text-sm font-bold text-slate-400 mb-1.5">₺</span>
                                        <span class="text-[2.55rem] leading-none font-extrabold font-display text-[#0F172A] tracking-tight">{{ number_format($p->yillik_indirimli_fiyat, 0, ',', '.') }}</span>
                                        <span class="text-xs font-semibold text-slate-400 mb-1.5">/ yıl</span>
                                    </div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs text-slate-400 line-through">₺{{ number_format($p->yillik_fiyat, 0, ',', '.') }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-[10px] font-bold text-emerald-700 border border-emerald-100">
                                            ₺{{ number_format($p->yillik_fiyat - $p->yillik_indirimli_fiyat, 0, ',', '.') }} tasarruf
                                        </span>
                                    </div>
                                @else
                                    <div class="flex items-end gap-1">
                                        @if($isFree)
                                            <span class="text-[2.2rem] leading-none font-extrabold font-display text-[#C96A2B] tracking-tight">Ücretsiz</span>
                                        @else
                                            <span class="text-sm font-bold text-slate-400 mb-1.5">₺</span>
                                            <span class="text-[2.55rem] leading-none font-extrabold font-display text-[#0F172A] tracking-tight">{{ number_format($p->yillik_fiyat, 0, ',', '.') }}</span>
                                            <span class="text-xs font-semibold text-slate-400 mb-1.5">/ yıl</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            @if(! $isFree)
                                @include('frontend.partials.kdv-dahil')
                            @endif
                        </div>

                        <div class="flex-1 mb-7">
                            @php $ozet = $p->kartVitrinOzeti(7); @endphp
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-400 font-display mb-3">
                                Öne çıkanlar
                                @if($ozet['toplam'] > 0)
                                    <span class="normal-case tracking-normal font-semibold text-slate-400">· {{ $ozet['toplam'] }} özellik</span>
                                @endif
                            </p>
                            <ul class="space-y-2.5">
                                @foreach($ozet['items'] as $ozellik)
                                    <li class="feature-row">
                                        <span class="feature-check {{ $isFeatured || $isWebsite ? 'brand' : '' }}">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <span>{{ $ozellik }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            @if($ozet['daha_fazla'] > 0)
                                <span class="feature-count">+{{ $ozet['daha_fazla'] }} özellik daha</span>
                                <a href="#ozellik-matrisi" class="feature-more js-scroll-matrix" data-matrix-type="bireysel">
                                    Tümünü karşılaştır
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </a>
                            @elseif($ozet['toplam'] > 0)
                                <a href="#ozellik-matrisi" class="feature-more js-scroll-matrix" data-matrix-type="bireysel">Karşılaştırma tablosu</a>
                            @endif
                        </div>

                        <div class="mt-auto">
                            @php $kayitQs = 'paket='.$p->id.'&periyot=aylik'.($referansRef ? '&ref='.$referansRef : ''); @endphp
                            <a href="{{ route('frontend.hekim.kayit') }}?{{ $kayitQs }}"
                               data-package-id="{{ $p->id }}"
                               class="btn-checkout-link btn-plan {{ $isFeatured || $isWebsite ? 'btn-plan-primary' : 'btn-plan-ghost' }}">
                                {{ $isFree ? 'Ücretsiz dene' : 'Paketi seç' }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-16 text-sm text-slate-500">
                    Kayıtlı bireysel üyelik paketi bulunamadı.
                </div>
            @endforelse
        </div>

        <!-- Klinik -->
        <div id="klinikPlans"
             class="plan-container plans-equal-row is-hidden"
             data-count="{{ max(1, $klinikPaketler->count()) }}"
             style="--plan-count: {{ max(1, $klinikPaketler->count()) }}">
            @forelse($klinikPaketler as $p)
                @php
                    $isWebsite = \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($p->ad), 'kurumsal')
                        || (method_exists($p, 'hasFeature') && $p->hasFeature('klinik_web_sitesi'))
                        || (bool) ($p->domain_dahil_mi ?? false);
                    $vitrin = method_exists($p, 'vitrinEtiketi') ? $p->vitrinEtiketi() : null;
                    $isFeatured = (bool) ($p->one_cikan_mi ?? false)
                        || in_array($vitrin['stil'] ?? '', ['popular'], true);
                    $ribbonClass = match ($vitrin['stil'] ?? '') {
                        'popular' => 'ribbon-popular',
                        'web' => 'ribbon-web',
                        'free', 'trial' => 'ribbon-free',
                        default => 'ribbon-custom',
                    };
                    $cardClass = 'price-card';
                    if ($isFeatured) $cardClass .= ' featured';
                    if ($isWebsite && ! $isFeatured) $cardClass .= ' website';
                @endphp
                <article class="{{ $cardClass }}">
                    @if($vitrin)
                        <span class="ribbon {{ $ribbonClass }}">
                            @if(($vitrin['stil'] ?? '') === 'popular')
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endif
                            {{ $vitrin['label'] }}
                        </span>
                    @endif

                    <div class="relative z-[1] flex flex-col h-full">
                        <div class="flex items-start justify-between gap-3 mb-1">
                            <div class="plan-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-orange-50 text-[10px] font-extrabold text-[#C96A2B] uppercase tracking-wider font-display border border-[#E7B58A]/35">Klinik</span>
                        </div>

                        <div class="mb-5">
                            <h3 class="text-[17px] font-bold font-display text-[#0F172A] leading-snug">{{ $p->ad }}</h3>
                            <p class="text-[12.5px] text-slate-500 mt-2 leading-relaxed min-h-[40px]">{{ $p->aciklama }}</p>
                        </div>

                        <div class="mb-6 pb-6 border-b border-slate-100 min-h-[88px]">
                            <div class="billing-price-monthly">
                                @if($p->aylik_indirimli_fiyat)
                                    <div class="flex items-end gap-1">
                                        <span class="text-sm font-bold text-slate-400 mb-1.5">₺</span>
                                        <span class="text-[2.55rem] leading-none font-extrabold font-display text-[#0F172A] tracking-tight">{{ number_format($p->aylik_indirimli_fiyat, 0, ',', '.') }}</span>
                                        <span class="text-xs font-semibold text-slate-400 mb-1.5">/ ay</span>
                                    </div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs text-slate-400 line-through">₺{{ number_format($p->aylik_fiyat, 0, ',', '.') }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-[10px] font-bold text-emerald-700 border border-emerald-100">
                                            ₺{{ number_format($p->aylik_fiyat - $p->aylik_indirimli_fiyat, 0, ',', '.') }} tasarruf
                                        </span>
                                    </div>
                                @else
                                    <div class="flex items-end gap-1">
                                        <span class="text-sm font-bold text-slate-400 mb-1.5">₺</span>
                                        <span class="text-[2.55rem] leading-none font-extrabold font-display text-[#0F172A] tracking-tight">{{ number_format($p->aylik_fiyat, 0, ',', '.') }}</span>
                                        <span class="text-xs font-semibold text-slate-400 mb-1.5">/ ay</span>
                                    </div>
                                @endif
                            </div>

                            <div class="billing-price-yearly is-hidden">
                                @if($p->yillik_indirimli_fiyat)
                                    <div class="flex items-end gap-1">
                                        <span class="text-sm font-bold text-slate-400 mb-1.5">₺</span>
                                        <span class="text-[2.55rem] leading-none font-extrabold font-display text-[#0F172A] tracking-tight">{{ number_format($p->yillik_indirimli_fiyat, 0, ',', '.') }}</span>
                                        <span class="text-xs font-semibold text-slate-400 mb-1.5">/ yıl</span>
                                    </div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs text-slate-400 line-through">₺{{ number_format($p->yillik_fiyat, 0, ',', '.') }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-[10px] font-bold text-emerald-700 border border-emerald-100">
                                            ₺{{ number_format($p->yillik_fiyat - $p->yillik_indirimli_fiyat, 0, ',', '.') }} tasarruf
                                        </span>
                                    </div>
                                @else
                                    <div class="flex items-end gap-1">
                                        <span class="text-sm font-bold text-slate-400 mb-1.5">₺</span>
                                        <span class="text-[2.55rem] leading-none font-extrabold font-display text-[#0F172A] tracking-tight">{{ number_format($p->yillik_fiyat, 0, ',', '.') }}</span>
                                        <span class="text-xs font-semibold text-slate-400 mb-1.5">/ yıl</span>
                                    </div>
                                @endif
                            </div>
                            @include('frontend.partials.kdv-dahil')
                        </div>

                        <div class="flex-1 mb-7">
                            @php $ozet = $p->kartVitrinOzeti(7); @endphp
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-400 font-display mb-3">
                                Öne çıkanlar
                                @if($ozet['toplam'] > 0)
                                    <span class="normal-case tracking-normal font-semibold text-slate-400">· {{ $ozet['toplam'] }} özellik</span>
                                @endif
                            </p>
                            <ul class="space-y-2.5">
                                @foreach($ozet['items'] as $ozellik)
                                    <li class="feature-row">
                                        <span class="feature-check {{ $isFeatured || $isWebsite ? 'brand' : '' }}">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <span>{{ $ozellik }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            @if($ozet['daha_fazla'] > 0)
                                <span class="feature-count">+{{ $ozet['daha_fazla'] }} özellik daha</span>
                                <a href="#ozellik-matrisi" class="feature-more js-scroll-matrix" data-matrix-type="klinik">
                                    Tümünü karşılaştır
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </a>
                            @elseif($ozet['toplam'] > 0)
                                <a href="#ozellik-matrisi" class="feature-more js-scroll-matrix" data-matrix-type="klinik">Karşılaştırma tablosu</a>
                            @endif
                        </div>

                        <div class="mt-auto">
                            @php $kayitQs = 'paket='.$p->id.'&periyot=aylik'.($referansRef ? '&ref='.$referansRef : ''); @endphp
                            <a href="{{ route('frontend.hekim.kayit') }}?{{ $kayitQs }}"
                               data-package-id="{{ $p->id }}"
                               class="btn-checkout-link btn-plan {{ $isFeatured || $isWebsite ? 'btn-plan-primary' : 'btn-plan-ghost' }}">
                                Paketi seç
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-16 text-sm text-slate-500">
                    Kayıtlı klinik üyelik paketi bulunamadı.
                </div>
            @endforelse
        </div>

        {{-- Özellik karşılaştırma: bireysel / klinik ayrı satır seti --}}
        @php
            $matrixPanels = [
                'bireysel' => [
                    'paketler' => $bireyselPaketler,
                    'matris' => $matrisBireysel ?? collect(),
                    'label' => 'Bireysel paketler',
                ],
                'klinik' => [
                    'paketler' => $klinikPaketler,
                    'matris' => $matrisKlinik ?? collect(),
                    'label' => 'Klinik paketler',
                ],
            ];
            $hasAnyMatrix = $bireyselPaketler->isNotEmpty() || $klinikPaketler->isNotEmpty();
        @endphp
        @if($hasAnyMatrix)
        <div class="mt-16 max-w-6xl mx-auto" id="ozellik-matrisi" style="scroll-margin-top: 7.5rem;">
            <h2 class="text-xl font-extrabold font-display text-slate-900 text-center mb-2">Özellik karşılaştırması</h2>
            <p class="text-xs text-slate-500 text-center mb-1 max-w-xl mx-auto">
                Bireysel ve klinik paketlerin özellikleri ayrıdır. Üstteki sekmeye göre tablo değişir.
            </p>
            <p class="text-xs font-bold text-[#C96A2B] text-center mb-4" id="matrixTypeLabel">Bireysel paketler</p>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <input type="search" id="matrixSearch" class="matrix-search" placeholder="Özellik ara… (ör. SMS, havuz, blog)" autocomplete="off">
                <p class="text-[11px] text-slate-400">Yalnızca bu paket türünde olan özellikler listelenir</p>
            </div>

            @foreach($matrixPanels as $tur => $panel)
                @php
                    $paketler = $panel['paketler'];
                    $matris = $panel['matris'];
                @endphp
                @if($paketler->isEmpty())
                    @continue
                @endif
                <div class="matrix-wrap matrix-panel {{ $tur === 'bireysel' ? '' : 'is-hidden' }}"
                     id="matrix-{{ $tur }}"
                     data-matrix-type="{{ $tur }}"
                     data-label="{{ $panel['label'] }}">
                    <div class="matrix-tabs" role="tablist">
                        <button type="button" class="matrix-tab active" data-group="__all" role="tab" aria-selected="true">Tümü</button>
                        @foreach($matris as $grup => $ozList)
                            <button type="button" class="matrix-tab" data-group="{{ \Illuminate\Support\Str::slug($grup) }}" role="tab" aria-selected="false">
                                {{ $grup }}
                                <span class="opacity-70 font-semibold">({{ $ozList->count() }})</span>
                            </button>
                        @endforeach
                        <button type="button" class="matrix-tab" data-group="__limits" role="tab" aria-selected="false">Limitler</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="matrix-table w-full text-left text-xs min-w-[720px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="p-3 font-bold text-slate-600 sticky-col min-w-[200px]">Özellik</th>
                                    @foreach($paketler as $bp)
                                        <th class="p-3 font-bold text-slate-800 text-center whitespace-nowrap">{{ $bp->ad }}</th>
                                    @endforeach
                                </tr>
                            </thead>

                            @forelse($matris as $grup => $ozList)
                                @php $gSlug = \Illuminate\Support\Str::slug($grup); @endphp
                                <tbody class="matrix-group active" data-group="{{ $gSlug }}">
                                    <tr class="bg-orange-50/60 matrix-group-head">
                                        <td colspan="{{ 1 + $paketler->count() }}" class="px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-[#C96A2B]">
                                            {{ $grup }}
                                        </td>
                                    </tr>
                                    @foreach($ozList as $oz)
                                        <tr class="border-t border-slate-100 matrix-feature-row" data-label="{{ mb_strtolower(($oz->ad ?? '').' '.$grup) }}">
                                            <td class="p-3 text-slate-600 sticky-col">
                                                <span class="font-medium text-slate-700">{{ $oz->ad }}</span>
                                                @if(!empty($oz->aciklama))
                                                    <span class="block text-[10px] text-slate-400 mt-0.5 leading-snug">{{ \Illuminate\Support\Str::limit($oz->aciklama, 72) }}</span>
                                                @endif
                                            </td>
                                            @foreach($paketler as $bp)
                                                @php $var = \App\Support\PaketOzellikKatalogu::paketMatrisVar($bp, $oz); @endphp
                                                <td class="p-3 text-center {{ $var ? 'text-emerald-600 font-bold' : 'text-slate-300' }}">{{ $var ? '✓' : '—' }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            @empty
                                <tbody class="matrix-group active" data-group="__empty">
                                    <tr>
                                        <td colspan="{{ 1 + $paketler->count() }}" class="p-6 text-center text-slate-400">
                                            Bu paket türü için özellik satırı bulunamadı.
                                        </td>
                                    </tr>
                                </tbody>
                            @endforelse

                            <tbody class="matrix-group active" data-group="__limits">
                                <tr class="bg-orange-50/60 matrix-group-head">
                                    <td colspan="{{ 1 + $paketler->count() }}" class="px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-[#C96A2B]">Limitler</td>
                                </tr>
                                <tr class="border-t border-slate-100 matrix-feature-row" data-label="sms aylık kontör limitler">
                                    <td class="p-3 font-bold sticky-col">SMS aylık kontör</td>
                                    @foreach($paketler as $bp)
                                        <td class="p-3 text-center font-semibold">{{ $bp->sms_aylik_kontor ? number_format($bp->sms_aylik_kontor,0,',','.') : '—' }}</td>
                                    @endforeach
                                </tr>
                                @if($tur === 'bireysel')
                                    <tr class="border-t border-slate-100 matrix-feature-row" data-label="max randevu limitler">
                                        <td class="p-3 font-bold sticky-col">Max randevu</td>
                                        @foreach($paketler as $bp)
                                            <td class="p-3 text-center">{{ $bp->max_randevu_sayisi ?? '∞' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr class="border-t border-slate-100 matrix-feature-row" data-label="max personel limitler bireysel">
                                        <td class="p-3 font-bold sticky-col">Personel koltuğu</td>
                                        @foreach($paketler as $bp)
                                            <td class="p-3 text-center">{{ $bp->max_personel_sayisi ? $bp->max_personel_sayisi : '—' }}</td>
                                        @endforeach
                                    </tr>
                                @else
                                    <tr class="border-t border-slate-100 matrix-feature-row" data-label="max hekim limitler">
                                        <td class="p-3 font-bold sticky-col">Max hekim</td>
                                        @foreach($paketler as $bp)
                                            <td class="p-3 text-center">{{ $bp->max_doktor_sayisi ?? '∞' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr class="border-t border-slate-100 matrix-feature-row" data-label="max personel limitler">
                                        <td class="p-3 font-bold sticky-col">Max personel</td>
                                        @foreach($paketler as $bp)
                                            <td class="p-3 text-center">{{ $bp->max_personel_sayisi ?? '∞' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr class="border-t border-slate-100 matrix-feature-row" data-label="max ek hekim limitler">
                                        <td class="p-3 font-bold sticky-col">Ek hekim koltuğu (üst sınır)</td>
                                        @foreach($paketler as $bp)
                                            <td class="p-3 text-center">{{ $bp->max_ek_doktor ?? '—' }}</td>
                                        @endforeach
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <p class="text-[11px] text-slate-400 text-center mt-3">Ek SMS: 1.000 adet ₺450 · 5.000 adet ₺1.950 (panelden satın alınır)</p>
        </div>
        @endif

        <!-- Trust strip -->
        <div class="mt-14 md:mt-16 trust-strip max-w-5xl mx-auto">
            <div class="trust-item">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 grid place-items-center border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800 font-display">Güvenli ödeme</p>
                    <p class="text-[11px] text-slate-500">PayTR altyapısı</p>
                </div>
            </div>
            <div class="trust-item">
                <div class="w-9 h-9 rounded-xl bg-orange-50 text-[#C96A2B] grid place-items-center border border-orange-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800 font-display">İstediğin zaman yükselt</p>
                    <p class="text-[11px] text-slate-500">Paket geçişi esnek</p>
                </div>
            </div>
            <div class="trust-item">
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 grid place-items-center border border-sky-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800 font-display">Canlı destek</p>
                    <p class="text-[11px] text-slate-500">Kurulum yardımı</p>
                </div>
            </div>
            <div class="trust-item">
                <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 grid place-items-center border border-violet-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800 font-display">Web + panel</p>
                    <p class="text-[11px] text-slate-500">Tek ekosistem</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    let currentBilling = 'aylik';
    let currentType = 'bireysel';

    document.addEventListener('DOMContentLoaded', function () {
        adjustSliderPosition('billingToggle', 'btnMonthly', 'billingSlider');
        adjustSliderPosition('typeToggle', 'btnBireysel', 'typeSlider');
    });

    function toggleBilling(cycle) {
        if (currentBilling === cycle) return;
        currentBilling = cycle;

        const btnMonthly = document.getElementById('btnMonthly');
        const btnYearly = document.getElementById('btnYearly');
        const activeBtn = cycle === 'aylik' ? btnMonthly : btnYearly;
        const inactiveBtn = cycle === 'aylik' ? btnYearly : btnMonthly;

        activeBtn.classList.add('active');
        inactiveBtn.classList.remove('active');
        adjustSliderPosition('billingToggle', activeBtn.id, 'billingSlider');

        const refQs = @json($referansRef ? '&ref='.$referansRef : '');
        document.querySelectorAll('.btn-checkout-link').forEach(link => {
            const pkgId = link.getAttribute('data-package-id');
            link.href = `{{ route('frontend.hekim.kayit') }}?paket=${pkgId}&periyot=${cycle}${refQs}`;
        });

        const monthlyPrices = document.querySelectorAll('.billing-price-monthly');
        const yearlyPrices = document.querySelectorAll('.billing-price-yearly');

        monthlyPrices.forEach(el => el.style.opacity = '0');
        yearlyPrices.forEach(el => el.style.opacity = '0');

        setTimeout(() => {
            if (cycle === 'aylik') {
                yearlyPrices.forEach(el => { el.classList.add('is-hidden'); el.style.opacity = ''; });
                monthlyPrices.forEach(el => {
                    el.classList.remove('is-hidden');
                    requestAnimationFrame(() => { el.style.opacity = '1'; });
                });
            } else {
                monthlyPrices.forEach(el => { el.classList.add('is-hidden'); el.style.opacity = ''; });
                yearlyPrices.forEach(el => {
                    el.classList.remove('is-hidden');
                    requestAnimationFrame(() => { el.style.opacity = '1'; });
                });
            }
        }, 100);
    }

    function syncMatrixPanel(type) {
        const label = document.getElementById('matrixTypeLabel');
        document.querySelectorAll('.matrix-panel').forEach((panel) => {
            const match = panel.getAttribute('data-matrix-type') === type;
            panel.classList.toggle('is-hidden', !match);
            if (match && label) {
                label.textContent = panel.getAttribute('data-label') || (type === 'klinik' ? 'Klinik paketler' : 'Bireysel paketler');
            }
        });
        // Aktif panelde arama/filtreyi yenile
        const active = document.querySelector('.matrix-panel:not(.is-hidden)');
        if (active && typeof active.__applyMatrixFilters === 'function') {
            active.__applyMatrixFilters();
        }
    }

    function togglePackageType(type, force) {
        if (!force && currentType === type) return;
        currentType = type;

        const btnBireysel = document.getElementById('btnBireysel');
        const btnKlinik = document.getElementById('btnKlinik');
        const activeBtn = type === 'bireysel' ? btnBireysel : btnKlinik;
        const inactiveBtn = type === 'bireysel' ? btnKlinik : btnBireysel;

        if (activeBtn && inactiveBtn) {
            activeBtn.classList.add('active');
            inactiveBtn.classList.remove('active');
            adjustSliderPosition('typeToggle', activeBtn.id, 'typeSlider');
        }

        const bireyselPlans = document.getElementById('bireyselPlans');
        const klinikPlans = document.getElementById('klinikPlans');
        const fadeOutClass = 'fade-out';

        // Karşılaştırma matrisi: açık sekmeye göre
        syncMatrixPanel(type);

        if (!bireyselPlans || !klinikPlans) return;

        if (type === 'bireysel') {
            klinikPlans.classList.add(fadeOutClass);
            setTimeout(() => {
                klinikPlans.classList.add('is-hidden');
                bireyselPlans.classList.remove('is-hidden');
                setTimeout(() => bireyselPlans.classList.remove(fadeOutClass), 30);
            }, force ? 0 : 240);
        } else {
            bireyselPlans.classList.add(fadeOutClass);
            setTimeout(() => {
                bireyselPlans.classList.add('is-hidden');
                klinikPlans.classList.remove('is-hidden');
                setTimeout(() => klinikPlans.classList.remove(fadeOutClass), 30);
            }, force ? 0 : 240);
        }
    }

    function scrollToMatrix(preferredType) {
        if (preferredType === 'bireysel' || preferredType === 'klinik') {
            togglePackageType(preferredType, true);
        } else {
            syncMatrixPanel(currentType);
        }

        const el = document.getElementById('ozellik-matrisi');
        if (!el) return;

        // Sticky header yüksekliği (~120px) + pay
        const header = document.querySelector('header.sticky') || document.querySelector('header');
        const offset = (header ? header.offsetHeight : 96) + 16;
        const top = el.getBoundingClientRect().top + window.pageYOffset - offset;

        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });

        try {
            history.replaceState(null, '', '#ozellik-matrisi');
        } catch (e) {}
    }

    function adjustSliderPosition(containerId, buttonId, sliderId) {
        const container = document.getElementById(containerId);
        const button = document.getElementById(buttonId);
        const slider = document.getElementById(sliderId);
        if (!container || !button || !slider) return;

        const containerRect = container.getBoundingClientRect();
        const buttonRect = button.getBoundingClientRect();
        const leftPos = Math.max(0, buttonRect.left - containerRect.left - 5);
        slider.style.width = buttonRect.width + 'px';
        slider.style.transform = `translateX(${leftPos}px)`;
    }

    window.addEventListener('resize', function () {
        adjustSliderPosition('billingToggle', currentBilling === 'aylik' ? 'btnMonthly' : 'btnYearly', 'billingSlider');
        adjustSliderPosition('typeToggle', currentType === 'bireysel' ? 'btnBireysel' : 'btnKlinik', 'typeSlider');
    });

    // Özellik matrisi: her panel (bireysel/klinik) kendi grup sekmelerine sahip
    (function initFeatureMatrix() {
        const search = document.getElementById('matrixSearch');
        const panels = document.querySelectorAll('.matrix-panel');
        if (!panels.length) return;

        panels.forEach((panel) => {
            let activeGroup = '__all';
            const tabs = panel.querySelector('.matrix-tabs');

            function applyFilters() {
                const q = (search?.value || '').trim().toLowerCase();
                panel.querySelectorAll('.matrix-group').forEach((group) => {
                    const g = group.getAttribute('data-group');
                    const groupMatch = activeGroup === '__all' || activeGroup === g;
                    let visibleRows = 0;

                    group.querySelectorAll('.matrix-feature-row').forEach((row) => {
                        const label = (row.getAttribute('data-label') || '').toLowerCase();
                        const textMatch = !q || label.includes(q);
                        const show = groupMatch && textMatch;
                        row.classList.toggle('matrix-row-hidden', !show);
                        if (show) visibleRows++;
                    });

                    const showGroup = groupMatch && (q === '' || visibleRows > 0);
                    group.classList.toggle('active', showGroup);
                });
            }

            panel.__applyMatrixFilters = applyFilters;

            if (tabs) {
                tabs.addEventListener('click', function (e) {
                    const btn = e.target.closest('.matrix-tab');
                    if (!btn || !tabs.contains(btn)) return;
                    activeGroup = btn.getAttribute('data-group') || '__all';
                    tabs.querySelectorAll('.matrix-tab').forEach((t) => {
                        const on = t === btn;
                        t.classList.toggle('active', on);
                        t.setAttribute('aria-selected', on ? 'true' : 'false');
                    });
                    applyFilters();
                });
            }

            applyFilters();
        });

        if (search) {
            search.addEventListener('input', function () {
                const active = document.querySelector('.matrix-panel:not(.is-hidden)');
                if (active && typeof active.__applyMatrixFilters === 'function') {
                    active.__applyMatrixFilters();
                }
            });
        }

        // "Tümünü karşılaştır" → sticky header payı ile kaydır
        document.querySelectorAll('.js-scroll-matrix').forEach((link) => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const t = this.getAttribute('data-matrix-type') || currentType;
                scrollToMatrix(t);
            });
        });

        // Sayfa #ozellik-matrisi ile açıldıysa
        if (window.location.hash === '#ozellik-matrisi') {
            setTimeout(() => scrollToMatrix(currentType), 80);
        }

        syncMatrixPanel(currentType);
    })();
</script>
@endsection
