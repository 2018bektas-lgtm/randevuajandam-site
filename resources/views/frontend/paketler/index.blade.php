@extends('frontend.layouts.app')

@section('baslik', 'Fiyatlar & Paketler - Randevu Ajandam')

@section('icerik')
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

    <div class="max-w-7xl mx-auto px-5 sm:px-6 relative z-10">
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

        <!-- Bireysel -->
        <div id="bireyselPlans" class="plan-container grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 lg:gap-6 items-stretch">
            @forelse($bireyselPaketler as $p)
                @php
                    $isFree = (float) $p->aylik_fiyat == 0;
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
                    @if($vitrin)
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
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-400 font-display mb-3">Dahil olanlar</p>
                            <ul class="space-y-2.5">
                                @if(is_array($p->ozellikler))
                                    @foreach($p->ozellikler as $ozellik)
                                        <li class="feature-row">
                                            <span class="feature-check {{ $isFeatured || $isWebsite ? 'brand' : '' }}">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                            <span>{{ $ozellik }}</span>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>

                        <div class="mt-auto">
                            <a href="{{ route('frontend.hekim.kayit') }}?paket={{ $p->id }}&periyot=aylik"
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
        <div id="klinikPlans" class="plan-container is-hidden grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 lg:gap-6 items-stretch max-w-7xl mx-auto">
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
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-400 font-display mb-3">Dahil olanlar</p>
                            <ul class="space-y-2.5">
                                @if($p->max_doktor_sayisi)
                                    <li class="feature-row">
                                        <span class="feature-check brand">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <span class="font-semibold text-slate-700">
                                            {{ $p->max_doktor_sayisi >= 999 ? 'Sınırsız hekim' : $p->max_doktor_sayisi.' hekime kadar' }}
                                        </span>
                                    </li>
                                @endif
                                @if($p->max_personel_sayisi)
                                    <li class="feature-row">
                                        <span class="feature-check brand">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <span class="font-semibold text-slate-700">
                                            {{ $p->max_personel_sayisi >= 999 ? 'Sınırsız personel' : $p->max_personel_sayisi.' sekreter / personel' }}
                                        </span>
                                    </li>
                                @endif
                                @if($p->merkezi_finans_mi)
                                    <li class="feature-row">
                                        <span class="feature-check brand">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <span class="font-semibold text-slate-700">Muhasebeci girişi + merkezi finans</span>
                                    </li>
                                @endif
                                @if(is_array($p->ozellikler))
                                    @foreach($p->ozellikler as $ozellik)
                                        @php
                                            $fLower = mb_strtolower((string) $ozellik);
                                            $skipDup = str_contains($fLower, 'muhasebeci')
                                                || str_contains($fLower, 'merkezi finans')
                                                || str_contains($fLower, 'maksimum')
                                                || str_contains($fLower, 'sınırsız hekim')
                                                || str_contains($fLower, 'sınırsız sekreter');
                                        @endphp
                                        @if($skipDup) @continue @endif
                                        <li class="feature-row">
                                            <span class="feature-check {{ $isFeatured || $isWebsite ? 'brand' : '' }}">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                            <span>{{ $ozellik }}</span>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>

                        <div class="mt-auto">
                            <a href="{{ route('frontend.hekim.kayit') }}?paket={{ $p->id }}&periyot=aylik"
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

        <!-- Trust strip -->
        <div class="mt-14 md:mt-16 trust-strip max-w-5xl mx-auto">
            <div class="trust-item">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 grid place-items-center border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800 font-display">Güvenli ödeme</p>
                    <p class="text-[11px] text-slate-500">iyzico altyapısı</p>
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

        document.querySelectorAll('.btn-checkout-link').forEach(link => {
            const pkgId = link.getAttribute('data-package-id');
            link.href = `{{ route('frontend.hekim.kayit') }}?paket=${pkgId}&periyot=${cycle}`;
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

    function togglePackageType(type) {
        if (currentType === type) return;
        currentType = type;

        const btnBireysel = document.getElementById('btnBireysel');
        const btnKlinik = document.getElementById('btnKlinik');
        const activeBtn = type === 'bireysel' ? btnBireysel : btnKlinik;
        const inactiveBtn = type === 'bireysel' ? btnKlinik : btnBireysel;

        activeBtn.classList.add('active');
        inactiveBtn.classList.remove('active');
        adjustSliderPosition('typeToggle', activeBtn.id, 'typeSlider');

        const bireyselPlans = document.getElementById('bireyselPlans');
        const klinikPlans = document.getElementById('klinikPlans');
        const fadeOutClass = 'fade-out';

        if (type === 'bireysel') {
            klinikPlans.classList.add(fadeOutClass);
            setTimeout(() => {
                klinikPlans.classList.add('is-hidden');
                bireyselPlans.classList.remove('is-hidden');
                setTimeout(() => bireyselPlans.classList.remove(fadeOutClass), 30);
            }, 240);
        } else {
            bireyselPlans.classList.add(fadeOutClass);
            setTimeout(() => {
                bireyselPlans.classList.add('is-hidden');
                klinikPlans.classList.remove('is-hidden');
                setTimeout(() => klinikPlans.classList.remove(fadeOutClass), 30);
            }, 240);
        }
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
</script>
@endsection
