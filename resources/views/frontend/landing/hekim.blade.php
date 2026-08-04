@extends('frontend.layouts.app')

@section('baslik', \App\Support\SeoMeta::hekimLandingTitle())
@section('meta_aciklama', \App\Support\SeoMeta::hekimLandingDescription())
@section('meta_anahtar_kelimeler', \App\Support\SeoMeta::keywords([
    'hekim randevu yazılımı',
    'doktor randevu sistemi',
    'online randevu programı hekim',
    'muayenehane randevu yazılımı',
    'hasta yönetim sistemi',
    'randevu ajandam',
]))

@section('icerik')
@php
    $qs = $kayitQuery ?? '';
    $kayitUrl = route('frontend.hekim.kayit').($qs !== '' ? '?'.$qs : '');
    $paketlerUrl = route('frontend.paketler').($qs !== '' ? '?'.$qs : '');
    $fiyat = null;
    if (! empty($ornekPaket)) {
        $fiyat = (float) ($ornekPaket->aylik_indirimli_fiyat ?? $ornekPaket->aylik_fiyat ?? 0);
    }
    $deneme = (int) ($denemeGun ?? 14);
@endphp

<style>
    .lp-hekim {
        --brand: #C96A2B;
        --brand-dark: #B55A20;
        --ink: #0F172A;
        --muted: #64748B;
        --line: #E2E8F0;
        --soft: #FFF7ED;
    }
    .lp-hekim .lp-wrap {
        max-width: 72rem;
        margin-left: auto;
        margin-right: auto;
        padding-left: 1.25rem;
        padding-right: 1.25rem;
    }
    @@media (min-width: 640px) {
        .lp-hekim .lp-wrap { padding-left: 1.5rem; padding-right: 1.5rem; }
    }
    @@media (min-width: 1280px) {
        .lp-hekim .lp-wrap { max-width: 78rem; }
    }

    .lp-hekim .btn-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0.95rem 1.5rem;
        border-radius: 16px;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        font-family: Outfit, Inter, system-ui, sans-serif;
        transition: transform .15s ease, box-shadow .2s ease, filter .15s ease;
        white-space: nowrap;
    }
    @@media (min-width: 1024px) {
        .lp-hekim .btn-cta {
            padding: 1.05rem 1.75rem;
            font-size: 13.5px;
            border-radius: 18px;
        }
    }
    .lp-hekim .btn-cta-primary {
        background: linear-gradient(135deg, #C96A2B, #D87A3C);
        color: #fff;
        box-shadow: 0 12px 28px rgba(201, 106, 43, 0.32);
    }
    .lp-hekim .btn-cta-primary:hover {
        filter: brightness(1.05);
        transform: translateY(-1px);
        color: #fff;
    }
    .lp-hekim .btn-cta-ghost {
        background: #fff;
        color: var(--ink);
        border: 1px solid var(--line);
    }
    .lp-hekim .btn-cta-ghost:hover {
        border-color: rgba(201,106,43,.4);
        color: var(--brand);
        background: var(--soft);
    }

    .lp-hekim .feat-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 22px;
        padding: 1.25rem 1.2rem;
        height: 100%;
        transition: border-color .2s, box-shadow .2s, transform .2s;
    }
    @@media (min-width: 1024px) {
        .lp-hekim .feat-card {
            padding: 1.6rem 1.5rem;
            border-radius: 24px;
        }
    }
    .lp-hekim .feat-card:hover {
        border-color: rgba(201,106,43,.35);
        box-shadow: 0 18px 44px rgba(15,23,42,.07);
        transform: translateY(-3px);
    }
    .lp-hekim .feat-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: var(--soft);
        color: var(--brand);
        border: 1px solid rgba(231,181,138,.45);
        margin-bottom: 0.85rem;
    }
    @@media (min-width: 1024px) {
        .lp-hekim .feat-icon { width: 52px; height: 52px; border-radius: 16px; margin-bottom: 1.1rem; }
        .lp-hekim .feat-icon svg { width: 22px; height: 22px; }
    }
    .lp-hekim .step-num {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        font-size: 13px;
        font-weight: 800;
        background: var(--soft);
        color: var(--brand);
        border: 1px solid rgba(231,181,138,.5);
        flex-shrink: 0;
    }
    @@media (min-width: 1024px) {
        .lp-hekim .step-num { width: 44px; height: 44px; font-size: 15px; }
    }

    /* —— Hero grid: mobil / tablet / masaüstü —— */
    .lp-hero-grid {
        display: grid;
        gap: 2rem;
        align-items: center;
    }
    @@media (min-width: 768px) and (max-width: 1023px) {
        /* Tablet: metin üstte, mock altta ortalı, dengeli */
        .lp-hero-grid {
            gap: 2.25rem;
            max-width: 40rem;
            margin-left: auto;
            margin-right: auto;
        }
        .lp-hero-copy { text-align: center; }
        .lp-hero-copy .lp-badge { margin-left: auto; margin-right: auto; }
        .lp-hero-copy p.lp-lead { margin-left: auto; margin-right: auto; }
        .lp-hero-actions { justify-content: center; }
        .lp-hero-checks { justify-content: center; }
        .lp-hero-visual { max-width: 32rem; margin-left: auto; margin-right: auto; width: 100%; }
    }
    @@media (min-width: 1024px) {
        .lp-hero-grid {
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
            gap: 3rem;
            align-items: center;
        }
        .lp-hero-copy { text-align: left; }
        .lp-hero-visual { max-width: none; width: 100%; }
    }
    @@media (min-width: 1280px) {
        .lp-hero-grid { gap: 3.75rem; }
    }

    /* Panel mock — düz, gölgeli; tablet/desktop’ta perspective yok (bozuyordu) */
    .lp-mock {
        position: relative;
        border-radius: 20px;
        background: #fff;
        border: 1px solid #E2E8F0;
        box-shadow:
            0 1px 2px rgba(15,23,42,.04),
            0 24px 48px -20px rgba(15,23,42,.22);
        overflow: hidden;
        width: 100%;
    }
    @@media (min-width: 768px) {
        .lp-mock {
            border-radius: 24px;
            box-shadow:
                0 1px 2px rgba(15,23,42,.04),
                0 32px 64px -24px rgba(15,23,42,.2),
                0 0 0 1px rgba(15,23,42,.03);
        }
    }
    .lp-mock-bar {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 11px 14px;
        background: #0f172a;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 600;
    }
    @@media (min-width: 768px) {
        .lp-mock-bar { padding: 12px 16px; font-size: 12px; }
    }
    .lp-mock-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .lp-mock-url {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin-left: 6px;
        color: #64748b;
        font-size: 10.5px;
    }
    @@media (min-width: 768px) {
        .lp-mock-url { font-size: 11.5px; }
    }
    .lp-mock-badge {
        flex-shrink: 0;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #E7B58A;
        background: rgba(201,106,43,.15);
        border: 1px solid rgba(201,106,43,.3);
        padding: 3px 8px;
        border-radius: 999px;
    }
    .lp-mock-body {
        display: grid;
        grid-template-columns: 56px 1fr;
        min-height: 0;
    }
    @@media (min-width: 768px) {
        .lp-mock-body { grid-template-columns: 72px 1fr; }
    }
    @@media (min-width: 1024px) {
        .lp-mock-body { grid-template-columns: 80px 1fr; }
    }
    .lp-mock-side {
        background: #0f172a;
        padding: 10px 6px;
        display: flex;
        flex-direction: column;
        gap: 7px;
        align-items: stretch;
    }
    @@media (min-width: 768px) {
        .lp-mock-side { padding: 12px 8px; gap: 8px; }
    }
    .lp-mock-side span {
        display: block;
        height: 28px;
        border-radius: 9px;
        background: rgba(255,255,255,.06);
    }
    @@media (min-width: 768px) {
        .lp-mock-side span { height: 32px; border-radius: 10px; }
    }
    .lp-mock-side span.on {
        background: rgba(201,106,43,.4);
        box-shadow: inset 0 0 0 1px rgba(231,181,138,.45);
    }
    .lp-mock-main {
        padding: 12px 12px 14px;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    }
    @@media (min-width: 768px) {
        .lp-mock-main { padding: 16px 16px 18px; }
    }
    @@media (min-width: 1024px) {
        .lp-mock-main { padding: 18px 18px 20px; }
    }
    .lp-mock-stat {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 10px 10px;
        text-align: left;
    }
    @@media (min-width: 768px) {
        .lp-mock-stat { border-radius: 14px; padding: 12px; }
    }
    .lp-mock-cal {
        margin-top: 10px;
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 10px 12px;
    }
    @@media (min-width: 768px) {
        .lp-mock-cal { margin-top: 12px; border-radius: 14px; padding: 12px 14px; }
    }
    .lp-mock-slot {
        min-height: 30px;
        border-radius: 8px;
        background: #FFF7ED;
        border: 1px solid rgba(231,181,138,.5);
        font-size: 11px;
        font-weight: 700;
        color: #C96A2B;
        display: flex;
        align-items: center;
        padding: 6px 10px;
        line-height: 1.25;
    }
    @@media (min-width: 768px) {
        .lp-mock-slot { font-size: 12px; min-height: 32px; padding: 7px 12px; }
    }
    .lp-mock-slot.busy {
        background: #f1f5f9;
        border-color: #e2e8f0;
        color: #64748b;
        font-weight: 600;
    }
    .lp-mock-caption {
        text-align: center;
        font-size: 11px;
        color: #94a3b8;
        font-weight: 600;
        margin-top: 12px;
    }
    @@media (min-width: 768px) {
        .lp-mock-caption { font-size: 12px; margin-top: 14px; }
    }

    /* Stats: mobil 2x2 · tablet 4 yan yana · desktop 4 + gölge */
    .lp-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 2rem;
    }
    @@media (min-width: 768px) {
        .lp-stats {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 2.25rem;
        }
    }
    @@media (min-width: 1024px) {
        .lp-stats {
            gap: 16px;
            margin-top: 2.75rem;
        }
    }
    .lp-stats .stat-card {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 0.95rem 1rem;
    }
    @@media (min-width: 768px) {
        .lp-stats .stat-card {
            border-radius: 18px;
            padding: 1.1rem 1.15rem;
            text-align: center;
        }
    }
    @@media (min-width: 1024px) {
        .lp-stats .stat-card {
            border-radius: 20px;
            padding: 1.25rem 1.2rem;
            box-shadow: 0 12px 32px -18px rgba(15,23,42,.12);
        }
    }

    .lp-steps-line { display: none; }
    @@media (min-width: 768px) {
        .lp-steps-wrap { position: relative; }
        .lp-steps-line {
            display: block;
            position: absolute;
            top: 28px;
            left: 12%;
            right: 12%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #E7B58A, transparent);
            z-index: 0;
        }
    }

    .lp-final {
        border-radius: 28px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #334155 100%);
        color: #fff;
        padding: 2rem 1.5rem;
        position: relative;
        overflow: hidden;
    }
    @@media (min-width: 1024px) {
        .lp-final {
            padding: 3rem 3.5rem;
            border-radius: 32px;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 2.5rem;
            align-items: center;
            text-align: left;
        }
        .lp-final .lp-final-actions { justify-content: flex-start; }
        .lp-final .lp-final-sub { text-align: left; margin-left: 0; }
    }
    .lp-final::before {
        content: '';
        position: absolute;
        width: 360px;
        height: 360px;
        right: -80px;
        top: -100px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(201,106,43,.35), transparent 65%);
        pointer-events: none;
    }

    .lp-sticky-cta {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 45;
        padding: 10px 14px calc(10px + env(safe-area-inset-bottom, 0px));
        background: rgba(255,255,255,.96);
        backdrop-filter: blur(12px);
        border-top: 1px solid #E5E7EB;
        box-shadow: 0 -10px 30px rgba(15,23,42,.06);
    }
    @@media (min-width: 1024px) {
        .lp-sticky-cta { display: none !important; }
    }
    @@media (max-width: 1023px) {
        body.lp-hekim-page { padding-bottom: 5.5rem !important; }
        body.lp-hekim-page > .fixed.bottom-0.z-40 { display: none !important; }
    }
</style>

<div class="lp-hekim bg-[#F8FAFC]">
    {{-- HERO --}}
    <section class="relative overflow-hidden">
        <div class="absolute top-[-18%] right-[-12%] w-[640px] h-[640px] rounded-full bg-[#E7B58A]/18 blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-[-22%] left-[-8%] w-[480px] h-[480px] rounded-full bg-[#C96A2B]/10 blur-[110px] pointer-events-none"></div>
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-[#E7B58A]/50 to-transparent"></div>

        <div class="lp-wrap pt-12 sm:pt-14 md:pt-16 lg:pt-20 xl:pt-22 pb-12 sm:pb-14 md:pb-16 lg:pb-20 relative z-10">
            <div class="lp-hero-grid">
                <div class="lp-hero-copy">
                    <span class="lp-badge inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#FFF7ED] border border-[#E7B58A]/40 text-[11px] font-bold uppercase tracking-[0.14em] text-[#C96A2B] font-display mb-4 md:mb-5 lg:mb-6">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#C96A2B] animate-pulse"></span>
                        Hekimler için randevu yazılımı
                    </span>

                    <h1 class="text-[1.85rem] sm:text-4xl md:text-[2.35rem] lg:text-[2.75rem] xl:text-[3.15rem] font-extrabold font-display text-[#0F172A] leading-[1.1] tracking-tight">
                        Randevu, hasta ve takvim
                        <span class="block mt-1 md:mt-1.5 bg-gradient-to-r from-[#C96A2B] via-[#D4894A] to-[#B55A20] bg-clip-text text-transparent">
                            tek panelde.
                        </span>
                    </h1>

                    <p class="lp-lead mt-4 md:mt-5 lg:mt-6 text-[15px] sm:text-base md:text-[16px] lg:text-[17px] text-slate-600 leading-relaxed max-w-xl">
                        Online randevu talepleri, ajanda, SMS hatırlatma, hasta kartları ve isteğe bağlı kişisel web siteniz.
                        <strong class="text-slate-800">Randevu Ajandam</strong> ile muayenehanenizi dijitalleştirin —
                        {{ $deneme }} gün deneme ile başlayın.
                    </p>

                    <div class="lp-hero-actions mt-6 md:mt-7 lg:mt-8 flex flex-col sm:flex-row flex-wrap gap-3">
                        <a href="{{ $kayitUrl }}" class="btn-cta btn-cta-primary" data-lp-cta="hero_kayit">
                            Ücretsiz profil oluştur
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                        <a href="{{ $paketlerUrl }}" class="btn-cta btn-cta-ghost" data-lp-cta="hero_paketler">
                            Paketleri karşılaştır
                        </a>
                    </div>

                    <ul class="lp-hero-checks mt-5 md:mt-6 lg:mt-7 flex flex-wrap gap-x-4 gap-y-2 text-[12.5px] md:text-[13px] font-semibold text-slate-500">
                        <li class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Kart zorunlu değil
                        </li>
                        <li class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            {{ $deneme }} gün deneme
                        </li>
                        <li class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            PayTR güvenli ödeme
                        </li>
                        @if($fiyat && $fiyat > 0)
                        <li class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            {{ number_format($fiyat, 0, ',', '.') }} ₺/ay’dan
                        </li>
                        @endif
                    </ul>
                </div>

                <div class="lp-hero-visual">
                    <div class="lp-mock" aria-hidden="true">
                        <div class="lp-mock-bar">
                            <span class="lp-mock-dot" style="background:#f87171"></span>
                            <span class="lp-mock-dot" style="background:#fbbf24"></span>
                            <span class="lp-mock-dot" style="background:#34d399"></span>
                            <span class="lp-mock-url">panel.randevuajandam.com</span>
                            <span class="lp-mock-badge">Hekim paneli</span>
                        </div>
                        <div class="lp-mock-body">
                            <div class="lp-mock-side" aria-hidden="true">
                                <span class="on"></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <div class="lp-mock-main">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <div class="min-w-0">
                                        <p class="text-[10px] md:text-[11px] font-bold text-slate-400 uppercase tracking-wider">Bugün</p>
                                        <p class="text-sm md:text-[15px] font-extrabold font-display text-slate-800 truncate">Takvim özeti</p>
                                    </div>
                                    <span class="shrink-0 px-2.5 py-1 rounded-full bg-[#FFF7ED] text-[10px] md:text-[11px] font-bold text-[#C96A2B] border border-[#E7B58A]/40">8 randevu</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 md:gap-2.5">
                                    <div class="lp-mock-stat">
                                        <p class="text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-wide">Bekleyen</p>
                                        <p class="text-lg md:text-xl font-extrabold font-display text-slate-800 mt-0.5 tabular-nums">3</p>
                                    </div>
                                    <div class="lp-mock-stat">
                                        <p class="text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-wide">Onaylı</p>
                                        <p class="text-lg md:text-xl font-extrabold font-display text-emerald-600 mt-0.5 tabular-nums">5</p>
                                    </div>
                                    <div class="lp-mock-stat">
                                        <p class="text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-wide">SMS</p>
                                        <p class="text-lg md:text-xl font-extrabold font-display text-[#C96A2B] mt-0.5 tabular-nums">12</p>
                                    </div>
                                </div>
                                <div class="lp-mock-cal">
                                    <p class="text-[10px] md:text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Öğleden sonra</p>
                                    <div class="space-y-1.5 md:space-y-2">
                                        <div class="lp-mock-slot">14:00 · Kontrol · Ayşe Y.</div>
                                        <div class="lp-mock-slot busy">14:30 · Dolu</div>
                                        <div class="lp-mock-slot">15:00 · İlk muayene · M. Kaya</div>
                                        <div class="lp-mock-slot">16:00 · Online görüşme</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="lp-mock-caption">Takvim · talepler · hasta · SMS — tek panel</p>
                </div>
            </div>

            <div class="lp-stats">
                @foreach([
                    ['7/24', 'Online randevu talebi'],
                    ['SMS', 'Otomatik hatırlatma'],
                    ['1 panel', 'Takvim + hasta + finans'],
                    [$deneme.' gün', 'Deneme ile başlayın'],
                ] as $s)
                    <div class="stat-card">
                        <p class="text-base sm:text-lg md:text-xl font-extrabold font-display text-[#0F172A]">{{ $s[0] }}</p>
                        <p class="text-[11px] sm:text-[12px] md:text-[13px] text-slate-500 mt-0.5 font-medium leading-snug">{{ $s[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAYDALAR --}}
    <section class="border-t border-slate-200/80 bg-white">
        <div class="lp-wrap py-14 sm:py-16 lg:py-20">
            <div class="text-center max-w-2xl lg:max-w-3xl mx-auto mb-10 lg:mb-14">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#C96A2B] font-display mb-3">Neden Randevu Ajandam?</p>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold font-display text-[#0F172A] tracking-tight">
                    WhatsApp ve ajanda yetmiyorsa
                </h2>
                <p class="mt-3 lg:mt-4 text-sm lg:text-base text-slate-500 leading-relaxed">
                    Talepler dağınık, no-show artıyor, hasta kaydı kayboluyor. Randevu Ajandam bunları tek yerde toplar.
                </p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 lg:gap-6">
                @foreach([
                    ['Online randevu', 'Hastalar uygun saati seçer; siz onaylarsınız. 7/24 talep alın.'],
                    ['Akıllı takvim', 'Slotlar, izinler, seri randevu ve bekleme listesi (pakete göre).'],
                    ['SMS hatırlatma', 'Randevu öncesi otomatik hatırlatma ile no-show azaltın.'],
                    ['Hasta yönetimi', 'Kart, not, dosya ve seans takibi — tek ekrandan.'],
                    ['Finans paneli', 'Gelir, gider, hasta bakiyesi (pakete göre).'],
                    ['Kişisel web sitesi', 'Markanızı yansıtan site + domain seçenekleri (üst paket).'],
                ] as $i => $f)
                    <div class="feat-card">
                        <div class="feat-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                @if($i === 0)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                @elseif($i === 1)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @elseif($i === 2)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                @elseif($i === 3)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                @elseif($i === 4)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                @endif
                            </svg>
                        </div>
                        <h3 class="text-[15px] lg:text-base font-bold font-display text-slate-900">{{ $f[0] }}</h3>
                        <p class="mt-1.5 lg:mt-2 text-[13px] lg:text-[14px] text-slate-500 leading-relaxed">{{ $f[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 3 ADIM --}}
    <section class="border-t border-slate-200/80 bg-[#F8FAFC]">
        <div class="lp-wrap py-14 sm:py-16 lg:py-20">
            <div class="text-center mb-10 lg:mb-14">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#C96A2B] font-display mb-3">Kurulum</p>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold font-display text-[#0F172A] tracking-tight">
                    3 adımda başlayın
                </h2>
            </div>
            <div class="lp-steps-wrap max-w-5xl mx-auto">
                <div class="lp-steps-line" aria-hidden="true"></div>
                <div class="grid md:grid-cols-3 gap-5 lg:gap-8 relative z-[1]">
                    @foreach([
                        ['1', 'Kayıt olun', 'E-posta ve temel bilgilerle profil oluşturun. Ücretsiz vitrin ile başlayabilirsiniz.'],
                        ['2', 'Belge onayı', 'Meslek belgesi / e-Devlet doğrulaması (gerekli paketlerde) güvenli süreçle ilerler.'],
                        ['3', 'Paket & ödeme', 'Deneme veya abonelik seçin. PayTR ile güvenli ödeme; dilerseniz havale.'],
                    ] as $step)
                        <div class="rounded-2xl lg:rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 lg:p-7 shadow-sm">
                            <div class="flex items-center gap-3 mb-3 lg:mb-4">
                                <span class="step-num">{{ $step[0] }}</span>
                                <h3 class="text-base lg:text-lg font-bold font-display text-slate-900">{{ $step[1] }}</h3>
                            </div>
                            <p class="text-[13px] lg:text-[14px] text-slate-500 leading-relaxed">{{ $step[2] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-10 lg:mt-12 text-center">
                <a href="{{ $kayitUrl }}" class="btn-cta btn-cta-primary" data-lp-cta="steps_kayit">
                    Ücretsiz kayda git
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- GÜVEN --}}
    <section class="border-t border-slate-200/80 bg-white">
        <div class="lp-wrap py-12 sm:py-14 lg:py-16">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5">
                @foreach([
                    ['Güvenli ödeme', 'PayTR altyapısı, 3D Secure'],
                    ['KVKK & SSL', 'Veri koruma ve şifreli bağlantı'],
                    ['Mobil uyum', 'Panel ve hasta deneyimi mobil'],
                    ['Esnek paket', 'İstediğiniz zaman yükseltin'],
                ] as $t)
                    <div class="rounded-2xl lg:rounded-3xl border border-slate-100 bg-slate-50/80 px-4 py-4 lg:px-5 lg:py-5 flex gap-3 items-start">
                        <span class="w-9 h-9 lg:w-10 lg:h-10 rounded-xl bg-emerald-50 text-emerald-600 grid place-items-center border border-emerald-100 flex-shrink-0">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </span>
                        <div>
                            <p class="text-sm lg:text-[15px] font-bold text-slate-800 font-display">{{ $t[0] }}</p>
                            <p class="text-[12px] lg:text-[13px] text-slate-500 mt-0.5">{{ $t[1] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-8 lg:mt-10 text-center text-[12px] lg:text-[13px] text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Randevu Ajandam bir randevu ve işletme yazılımıdır; tıbbi teşhis/tedavi hizmeti sunmaz.
                Muayene süreci hekim ile hasta arasındadır.
            </p>
        </div>
    </section>

    {{-- SON CTA --}}
    <section class="border-t border-slate-200/80 bg-[#F8FAFC]">
        <div class="lp-wrap py-12 sm:py-16 lg:py-20">
            <div class="lp-final relative z-[1]">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#E7B58A] font-display mb-3">Hemen başlayın</p>
                    <h2 class="text-2xl sm:text-3xl lg:text-[2.35rem] font-extrabold font-display text-white tracking-tight leading-tight">
                        Bugün profilinizi oluşturun
                    </h2>
                    <p class="mt-3 lg:mt-4 text-sm lg:text-base text-slate-300 leading-relaxed max-w-md">
                        Dakikalar içinde kaydolun, paneli keşfedin.
                        @if($fiyat && $fiyat > 0)
                            Ücretli paketler <strong class="text-white">{{ number_format($fiyat, 0, ',', '.') }} ₺/ay</strong>’dan başlar.
                        @endif
                    </p>
                </div>
                <div>
                    <div class="lp-final-actions flex flex-col sm:flex-row gap-3 mt-6 lg:mt-0">
                        <a href="{{ $kayitUrl }}" class="btn-cta btn-cta-primary" data-lp-cta="footer_kayit">
                            Ücretsiz profil oluştur
                        </a>
                        <a href="{{ $paketlerUrl }}" class="btn-cta btn-cta-ghost !bg-white/10 !text-white !border-white/20 hover:!bg-white/15" data-lp-cta="footer_paketler">
                            Tüm paketler
                        </a>
                    </div>
                    <p class="lp-final-sub mt-4 text-[12px] lg:text-[13px] text-slate-400 text-center">
                        Zaten hesabınız var mı?
                        <a href="{{ route('frontend.hekim.giris') }}" class="font-bold text-[#E7B58A] hover:underline">Hekim girişi</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Mobil sticky CTA --}}
<div class="lp-sticky-cta lg:hidden">
    <a href="{{ $kayitUrl }}" class="btn-cta btn-cta-primary w-full" data-lp-cta="sticky_kayit">
        Ücretsiz başla
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
    </a>
</div>
<script>
    document.body.classList.add('lp-hekim-page');
</script>
@endsection
