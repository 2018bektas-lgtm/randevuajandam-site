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
    .lp-hekim .btn-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0.95rem 1.4rem;
        border-radius: 16px;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        font-family: Outfit, Inter, system-ui, sans-serif;
        transition: transform .15s ease, box-shadow .2s ease, filter .15s ease;
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
    .lp-hekim .feat-card:hover {
        border-color: rgba(201,106,43,.35);
        box-shadow: 0 16px 40px rgba(15,23,42,.06);
        transform: translateY(-2px);
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
    .lp-sticky-cta {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 45;
        padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
        background: rgba(255,255,255,.96);
        backdrop-filter: blur(12px);
        border-top: 1px solid #E5E7EB;
        box-shadow: 0 -10px 30px rgba(15,23,42,.06);
    }
    @media (min-width: 1024px) {
        .lp-sticky-cta { display: none; }
    }
    /* mobil alt menü + sticky CTA çakışmasın */
    @media (max-width: 1023px) {
        body:has(.lp-sticky-cta) { padding-bottom: 5.5rem; }
        body:has(.lp-sticky-cta) .fixed.bottom-0.z-40 { display: none !important; }
    }
</style>

<div class="lp-hekim bg-[#F8FAFC]">
    {{-- HERO --}}
    <section class="relative overflow-hidden">
        <div class="absolute top-[-20%] right-[-15%] w-[520px] h-[520px] rounded-full bg-[#E7B58A]/20 blur-[110px] pointer-events-none"></div>
        <div class="absolute bottom-[-25%] left-[-10%] w-[420px] h-[420px] rounded-full bg-[#C96A2B]/10 blur-[100px] pointer-events-none"></div>

        <div class="max-w-6xl mx-auto px-5 sm:px-6 pt-12 sm:pt-16 pb-14 sm:pb-20 relative z-10">
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                <div class="lg:col-span-7">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#FFF7ED] border border-[#E7B58A]/40 text-[11px] font-bold uppercase tracking-[0.12em] text-[#C96A2B] font-display mb-5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#C96A2B] animate-pulse"></span>
                        Hekimler için randevu yazılımı
                    </span>

                    <h1 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-extrabold font-display text-[#0F172A] leading-[1.12] tracking-tight">
                        Randevu, hasta ve takvim
                        <span class="block mt-1 bg-gradient-to-r from-[#C96A2B] via-[#D4894A] to-[#B55A20] bg-clip-text text-transparent">
                            tek panelde.
                        </span>
                    </h1>

                    <p class="mt-5 text-[15px] sm:text-base text-slate-600 leading-relaxed max-w-xl">
                        Online randevu talepleri, ajanda, SMS hatırlatma, hasta kartları ve isteğe bağlı kişisel web siteniz.
                        <strong class="text-slate-800">Randevu Ajandam</strong> ile muayenehanenizi dijitalleştirin —
                        {{ $deneme }} gün deneme ile başlayın.
                    </p>

                    <div class="mt-7 flex flex-col sm:flex-row gap-3">
                        <a href="{{ $kayitUrl }}" class="btn-cta btn-cta-primary" data-lp-cta="hero_kayit">
                            Ücretsiz profil oluştur
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                        <a href="{{ $paketlerUrl }}" class="btn-cta btn-cta-ghost" data-lp-cta="hero_paketler">
                            Paketleri karşılaştır
                        </a>
                    </div>

                    <ul class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-[12.5px] font-semibold text-slate-500">
                        <li class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Kart zorunlu değil
                        </li>
                        <li class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            {{ $deneme }} gün deneme
                        </li>
                        <li class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            PayTR güvenli ödeme
                        </li>
                        @if($fiyat && $fiyat > 0)
                        <li class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Ücretli paketler {{ number_format($fiyat, 0, ',', '.') }} ₺/ay’dan
                        </li>
                        @endif
                    </ul>
                </div>

                <div class="lg:col-span-5">
                    <div class="relative rounded-[28px] border border-slate-200 bg-white p-5 sm:p-6 shadow-[0_24px_60px_-28px_rgba(15,23,42,0.25)]">
                        <div class="absolute -top-3 right-6 px-3 py-1 rounded-full bg-[#C96A2B] text-white text-[10px] font-extrabold uppercase tracking-wider shadow-lg">
                            Hekim paneli
                        </div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400 mb-4">Tek bakışta</p>
                        <div class="space-y-3">
                            @foreach([
                                ['Takvim & slotlar', 'Online randevu, müsaitlik, hızlı kapatma'],
                                ['Randevu talepleri', 'Onayla, ertele, reddet — tek ekrandan'],
                                ['Hasta kartları', 'Not, dosya, seans geçmişi (pakete göre)'],
                                ['SMS & e-posta', 'Hatırlatma ve bilgilendirme'],
                                ['Web sitesi', 'Kişisel vitrin ve domain (üst paket)'],
                            ] as $row)
                                <div class="flex gap-3 items-start rounded-2xl bg-slate-50 border border-slate-100 px-3.5 py-3">
                                    <span class="mt-0.5 w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 grid place-items-center flex-shrink-0">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800 font-display">{{ $row[0] }}</p>
                                        <p class="text-[12px] text-slate-500 leading-snug mt-0.5">{{ $row[1] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ $kayitUrl }}" class="btn-cta btn-cta-primary w-full mt-5" data-lp-cta="card_kayit">
                            Hemen başla
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SORUN / ÇÖZÜM --}}
    <section class="border-t border-slate-200/80 bg-white">
        <div class="max-w-6xl mx-auto px-5 sm:px-6 py-14 sm:py-16">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-2xl sm:text-3xl font-extrabold font-display text-[#0F172A] tracking-tight">
                    WhatsApp ve ajanda yetmiyorsa
                </h2>
                <p class="mt-3 text-sm text-slate-500 leading-relaxed">
                    Talepler dağınık, no-show artıyor, hasta kaydı kayboluyor. Randevu Ajandam bunları tek yerde toplar.
                </p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
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
                        <h3 class="text-[15px] font-bold font-display text-slate-900">{{ $f[0] }}</h3>
                        <p class="mt-1.5 text-[13px] text-slate-500 leading-relaxed">{{ $f[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- NASIL BAŞLAR --}}
    <section class="border-t border-slate-200/80 bg-[#F8FAFC]">
        <div class="max-w-6xl mx-auto px-5 sm:px-6 py-14 sm:py-16">
            <h2 class="text-2xl sm:text-3xl font-extrabold font-display text-[#0F172A] text-center tracking-tight">
                3 adımda başlayın
            </h2>
            <div class="mt-10 grid md:grid-cols-3 gap-5 max-w-4xl mx-auto">
                @foreach([
                    ['1', 'Kayıt olun', 'E-posta ve temel bilgilerle profil oluşturun. Ücretsiz vitrin ile başlayabilirsiniz.'],
                    ['2', 'Belge onayı', 'Meslek belgesi / e-Devlet doğrulaması (gerekli paketlerde) güvenli süreçle ilerler.'],
                    ['3', 'Paket & ödeme', 'Deneme veya abonelik seçin. PayTR ile güvenli ödeme; dilerseniz havale.'],
                ] as $step)
                    <div class="rounded-2xl bg-white border border-slate-200 p-5 sm:p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="step-num">{{ $step[0] }}</span>
                            <h3 class="text-base font-bold font-display text-slate-900">{{ $step[1] }}</h3>
                        </div>
                        <p class="text-[13px] text-slate-500 leading-relaxed">{{ $step[2] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-10 text-center">
                <a href="{{ $kayitUrl }}" class="btn-cta btn-cta-primary" data-lp-cta="steps_kayit">
                    Ücretsiz kayda git
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- GÜVEN --}}
    <section class="border-t border-slate-200/80 bg-white">
        <div class="max-w-6xl mx-auto px-5 sm:px-6 py-12 sm:py-14">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach([
                    ['Güvenli ödeme', 'PayTR altyapısı, 3D Secure'],
                    ['KVKK & SSL', 'Veri koruma ve şifreli bağlantı'],
                    ['Mobil uyum', 'Panel ve hasta deneyimi mobil'],
                    ['Esnek paket', 'İstediğiniz zaman yükseltin'],
                ] as $t)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-4 flex gap-3 items-start">
                        <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 grid place-items-center border border-emerald-100 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-800 font-display">{{ $t[0] }}</p>
                            <p class="text-[12px] text-slate-500 mt-0.5">{{ $t[1] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-8 text-center text-[12px] text-slate-400 max-w-xl mx-auto leading-relaxed">
                Randevu Ajandam bir randevu ve işletme yazılımıdır; tıbbi teşhis/tedavi hizmeti sunmaz.
                Muayene süreci hekim ile hasta arasındadır.
            </p>
        </div>
    </section>

    {{-- SON CTA --}}
    <section class="border-t border-slate-200/80 bg-gradient-to-b from-[#FFF7ED] to-white">
        <div class="max-w-3xl mx-auto px-5 sm:px-6 py-14 sm:py-16 text-center">
            <h2 class="text-2xl sm:text-3xl font-extrabold font-display text-[#0F172A] tracking-tight">
                Bugün profilinizi oluşturun
            </h2>
            <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                Dakikalar içinde kaydolun, paneli keşfedin.
                @if($fiyat && $fiyat > 0)
                    Ücretli paketler <strong>{{ number_format($fiyat, 0, ',', '.') }} ₺/ay</strong>’dan başlar (KDV dahil fiyatlar paket sayfasında).
                @endif
            </p>
            <div class="mt-7 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ $kayitUrl }}" class="btn-cta btn-cta-primary" data-lp-cta="footer_kayit">
                    Ücretsiz profil oluştur
                </a>
                <a href="{{ $paketlerUrl }}" class="btn-cta btn-cta-ghost" data-lp-cta="footer_paketler">
                    Tüm paketler
                </a>
            </div>
            <p class="mt-5 text-[12px] text-slate-400">
                Zaten hesabınız var mı?
                <a href="{{ route('frontend.hekim.giris') }}" class="font-bold text-[#C96A2B] hover:underline">Hekim girişi</a>
            </p>
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
@endsection
