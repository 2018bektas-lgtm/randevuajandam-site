@extends('frontend.layouts.app')

@section('baslik', 'Hekim Paket Seçimi - Randevu Ajandam')

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
        border-color: rgba(201, 106, 43, 0.28);
        background: linear-gradient(180deg, #FFFBF7 0%, #FFFFFF 48%);
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

    .pricing-page .plan-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        background: #FFF7ED;
        color: #C96A2B;
        border: 1px solid rgba(231, 181, 138, 0.45);
        margin-bottom: 1.1rem;
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
        background: #FFF7ED;
        color: #C96A2B;
        border-color: rgba(231, 181, 138, 0.45);
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
</style>

<section class="pricing-page relative bg-[#F8FAFC] pt-16 pb-24 md:pt-20 md:pb-28 overflow-hidden select-none">
    <div class="absolute top-[-18%] right-[-12%] w-[640px] h-[640px] rounded-full bg-[#E7B58A]/15 blur-[130px] pointer-events-none"></div>
    <div class="absolute bottom-[-12%] left-[-10%] w-[560px] h-[560px] rounded-full bg-[#C96A2B]/10 blur-[120px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-5 sm:px-6 relative z-10">
        <div class="text-center max-w-3xl mx-auto mb-12 md:mb-14">
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/35 rounded-full text-[11px] font-bold font-display uppercase tracking-[0.14em] mb-5">
                <span class="w-1.5 h-1.5 rounded-full bg-[#C96A2B] animate-pulse"></span>
                Hoş geldiniz, Dr. {{ mb_strtoupper($doktor->ad_soyad) }}
            </span>
            <h1 class="text-4xl md:text-5xl font-extrabold font-display text-[#0F172A] tracking-tight leading-[1.08]">
                Devam etmek için
                <span class="block mt-1 bg-gradient-to-r from-[#C96A2B] via-[#D4894A] to-[#B55A20] bg-clip-text text-transparent">
                    bir paket seçin
                </span>
            </h1>
            <p class="text-sm md:text-[15px] text-slate-500 max-w-2xl mx-auto mt-5 leading-relaxed">
                Hesabınız hazır. Bireysel veya klinik planınızı seçip kurulumu tamamlayabilirsiniz.
            </p>

            @if(session('hata'))
                <div class="mt-6 max-w-md mx-auto p-4 bg-red-50 border border-red-200 rounded-2xl text-xs text-red-600 font-semibold">
                    {{ session('hata') }}
                </div>
            @endif

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <div class="toggle-container" id="billingToggle">
                    <div class="toggle-slider" id="billingSlider"></div>
                    <button type="button" class="toggle-btn active" id="btnMonthly" onclick="toggleBilling('aylik')">Aylık</button>
                    <button type="button" class="toggle-btn" id="btnYearly" onclick="toggleBilling('yillik')">
                        Yıllık
                        @if(!empty($maxYillikTasarrufYuzde) && $maxYillikTasarrufYuzde > 0)
                        <span class="ml-1.5 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-100 text-[9px] font-extrabold text-emerald-700 uppercase tracking-wider">%{{ $maxYillikTasarrufYuzde }}’e varan</span>
                        @else
                        <span class="ml-1.5 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-100 text-[9px] font-extrabold text-emerald-700 uppercase tracking-wider">Tasarruf</span>
                        @endif
                    </button>
                </div>
                <div class="toggle-container" id="typeToggle">
                    <div class="toggle-slider" id="typeSlider"></div>
                    <button type="button" class="toggle-btn active" id="btnBireysel" onclick="togglePackageType('bireysel')">Bireysel</button>
                    <button type="button" class="toggle-btn" id="btnKlinik" onclick="togglePackageType('klinik')">Klinik</button>
                </div>
            </div>
        </div>

        <div id="bireyselPlans" class="plan-container grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 lg:gap-6 items-stretch">
            @forelse($bireyselPaketler as $p)
                @php
                    $isFree = (float) $p->aylik_fiyat == 0;
                    $isWebsite = \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($p->ad), 'web sitesi');
                    $isFeatured = false;
                    $paidIndex = 0;
                    foreach ($bireyselPaketler as $bp) {
                        $bpWeb = \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($bp->ad), 'web sitesi');
                        if ((float) $bp->aylik_fiyat > 0 && ! $bpWeb) {
                            $paidIndex++;
                            if ($bp->id === $p->id && $paidIndex === 2) {
                                $isFeatured = true;
                            }
                        }
                    }
                    $cardClass = 'price-card';
                    if ($isFeatured) $cardClass .= ' featured';
                    if ($isWebsite) $cardClass .= ' website';
                @endphp
                <article class="{{ $cardClass }}">
                    @if($isFeatured)
                        <span class="ribbon ribbon-popular">Popüler</span>
                    @elseif($isWebsite)
                        <span class="ribbon ribbon-web">Web sitesi</span>
                    @elseif($isFree)
                        <span class="ribbon ribbon-free">Ücretsiz</span>
                    @endif

                    <div class="relative z-[1] flex flex-col h-full">
                        <div class="plan-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div class="mb-5 pr-16">
                            <h3 class="text-[17px] font-bold font-display text-[#0F172A]">{{ $p->ad }}</h3>
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
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-[10px] font-bold text-emerald-700 border border-emerald-100">₺{{ number_format($p->aylik_fiyat - $p->aylik_indirimli_fiyat, 0, ',', '.') }} tasarruf</span>
                                    </div>
                                @else
                                    <div class="flex items-end gap-1">
                                        @if($isFree)
                                            <span class="text-[2.2rem] leading-none font-extrabold font-display text-[#C96A2B]">Ücretsiz</span>
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
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-[10px] font-bold text-emerald-700 border border-emerald-100">₺{{ number_format($p->yillik_fiyat - $p->yillik_indirimli_fiyat, 0, ',', '.') }} tasarruf</span>
                                    </div>
                                @else
                                    <div class="flex items-end gap-1">
                                        @if($isFree)
                                            <span class="text-[2.2rem] leading-none font-extrabold font-display text-[#C96A2B]">Ücretsiz</span>
                                        @else
                                            <span class="text-sm font-bold text-slate-400 mb-1.5">₺</span>
                                            <span class="text-[2.55rem] leading-none font-extrabold font-display text-[#0F172A] tracking-tight">{{ number_format($p->yillik_fiyat, 0, ',', '.') }}</span>
                                            <span class="text-xs font-semibold text-slate-400 mb-1.5">/ yıl</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <ul class="space-y-2.5 flex-1 mb-7">
                            @if(is_array($p->ozellikler))
                                @foreach($p->ozellikler as $feature)
                                    <li class="feature-row">
                                        <span class="feature-check {{ $isFeatured || $isWebsite ? 'brand' : '' }}">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>

                        <a href="{{ route('frontend.hekim.paket_ode', ['paket' => $p->id, 'periyot' => 'aylik']) }}"
                           class="btn-select-package btn-plan {{ $isFeatured || $isWebsite ? 'btn-plan-primary' : 'btn-plan-ghost' }} mt-auto"
                           data-base-url="{{ route('frontend.hekim.paket_ode') }}"
                           data-paket-id="{{ $p->id }}">
                            {{ $isFree ? 'Hemen başla' : 'Seç ve öde' }}
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-16 text-sm text-slate-500">Aktif bireysel paket bulunmamaktadır.</div>
            @endforelse
        </div>

        <div id="klinikPlans" class="plan-container is-hidden grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-6 items-stretch max-w-6xl mx-auto">
            @forelse($klinikPaketler as $p)
                @php
                    $isFeatured = $loop->iteration === 2;
                    $isWebsite = \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($p->ad), 'kurumsal');
                    $cardClass = 'price-card';
                    if ($isFeatured) $cardClass .= ' featured';
                    if ($isWebsite && ! $isFeatured) $cardClass .= ' website';
                @endphp
                <article class="{{ $cardClass }}">
                    @if($isFeatured)
                        <span class="ribbon ribbon-popular">Önerilen</span>
                    @elseif($isWebsite)
                        <span class="ribbon ribbon-web">Web sitesi dahil</span>
                    @endif

                    <div class="relative z-[1] flex flex-col h-full">
                        <div class="plan-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div class="mb-5">
                            <h3 class="text-[17px] font-bold font-display text-[#0F172A]">{{ $p->ad }}</h3>
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
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-[10px] font-bold text-emerald-700 border border-emerald-100">₺{{ number_format($p->aylik_fiyat - $p->aylik_indirimli_fiyat, 0, ',', '.') }} tasarruf</span>
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
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-[10px] font-bold text-emerald-700 border border-emerald-100">₺{{ number_format($p->yillik_fiyat - $p->yillik_indirimli_fiyat, 0, ',', '.') }} tasarruf</span>
                                    </div>
                                @else
                                    <div class="flex items-end gap-1">
                                        <span class="text-sm font-bold text-slate-400 mb-1.5">₺</span>
                                        <span class="text-[2.55rem] leading-none font-extrabold font-display text-[#0F172A] tracking-tight">{{ number_format($p->yillik_fiyat, 0, ',', '.') }}</span>
                                        <span class="text-xs font-semibold text-slate-400 mb-1.5">/ yıl</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <ul class="space-y-2.5 flex-1 mb-7">
                            <li class="feature-row">
                                <span class="feature-check brand"><svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                                <span class="font-semibold text-slate-700">Max hekim: {{ $p->max_doktor_sayisi ?? 'Sınırsız' }}</span>
                            </li>
                            <li class="feature-row">
                                <span class="feature-check brand"><svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                                <span class="font-semibold text-slate-700">Max personel: {{ $p->max_personel_sayisi ?? 'Sınırsız' }}</span>
                            </li>
                            @if(is_array($p->ozellikler))
                                @foreach($p->ozellikler as $feature)
                                    <li class="feature-row">
                                        <span class="feature-check {{ $isFeatured || $isWebsite ? 'brand' : '' }}">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>

                        <a href="{{ route('frontend.hekim.paket_ode', ['paket' => $p->id, 'periyot' => 'aylik']) }}"
                           class="btn-select-package btn-plan {{ $isFeatured || $isWebsite ? 'btn-plan-primary' : 'btn-plan-ghost' }} mt-auto"
                           data-base-url="{{ route('frontend.hekim.paket_ode') }}"
                           data-paket-id="{{ $p->id }}">
                            Seç ve öde
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-16 text-sm text-slate-500">Aktif klinik paketi bulunmamaktadır.</div>
            @endforelse
        </div>
    </div>
</section>

<script>
    let currentBillingPeriod = 'aylik';
    let currentPackageType = 'bireysel';

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

    function toggleBilling(period) {
        if (currentBillingPeriod === period) return;
        currentBillingPeriod = period;

        const btnMonthly = document.getElementById('btnMonthly');
        const btnYearly = document.getElementById('btnYearly');
        const activeBtn = period === 'aylik' ? btnMonthly : btnYearly;
        const inactiveBtn = period === 'aylik' ? btnYearly : btnMonthly;
        activeBtn.classList.add('active');
        inactiveBtn.classList.remove('active');
        adjustSliderPosition('billingToggle', activeBtn.id, 'billingSlider');

        if (period === 'aylik') {
            document.querySelectorAll('.billing-price-monthly').forEach(el => el.classList.remove('is-hidden'));
            document.querySelectorAll('.billing-price-yearly').forEach(el => el.classList.add('is-hidden'));
        } else {
            document.querySelectorAll('.billing-price-monthly').forEach(el => el.classList.add('is-hidden'));
            document.querySelectorAll('.billing-price-yearly').forEach(el => el.classList.remove('is-hidden'));
        }
        updateActionUrls();
    }

    function togglePackageType(type) {
        if (currentPackageType === type) return;
        currentPackageType = type;

        const btnBireysel = document.getElementById('btnBireysel');
        const btnKlinik = document.getElementById('btnKlinik');
        const activeBtn = type === 'bireysel' ? btnBireysel : btnKlinik;
        const inactiveBtn = type === 'bireysel' ? btnKlinik : btnBireysel;
        activeBtn.classList.add('active');
        inactiveBtn.classList.remove('active');
        adjustSliderPosition('typeToggle', activeBtn.id, 'typeSlider');

        const bireyselContainer = document.getElementById('bireyselPlans');
        const klinikContainer = document.getElementById('klinikPlans');

        if (type === 'bireysel') {
            klinikContainer.classList.add('fade-out');
            setTimeout(() => {
                klinikContainer.classList.add('is-hidden');
                bireyselContainer.classList.remove('is-hidden');
                setTimeout(() => bireyselContainer.classList.remove('fade-out'), 30);
            }, 240);
        } else {
            bireyselContainer.classList.add('fade-out');
            setTimeout(() => {
                bireyselContainer.classList.add('is-hidden');
                klinikContainer.classList.remove('is-hidden');
                setTimeout(() => klinikContainer.classList.remove('fade-out'), 30);
            }, 240);
        }
    }

    function updateActionUrls() {
        document.querySelectorAll('.btn-select-package').forEach(btn => {
            const baseUrl = btn.getAttribute('data-base-url');
            const paketId = btn.getAttribute('data-paket-id');
            btn.setAttribute('href', `${baseUrl}?paket=${paketId}&periyot=${currentBillingPeriod}`);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        adjustSliderPosition('billingToggle', 'btnMonthly', 'billingSlider');
        adjustSliderPosition('typeToggle', 'btnBireysel', 'typeSlider');
    });

    window.addEventListener('resize', () => {
        adjustSliderPosition('billingToggle', currentBillingPeriod === 'aylik' ? 'btnMonthly' : 'btnYearly', 'billingSlider');
        adjustSliderPosition('typeToggle', currentPackageType === 'bireysel' ? 'btnBireysel' : 'btnKlinik', 'typeSlider');
    });
</script>
@endsection
