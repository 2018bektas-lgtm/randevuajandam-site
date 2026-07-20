@extends('frontend.layouts.app')

@section('baslik', ($hizmet->meta_baslik ?? $hizmet->ad) . ' - ' . ($doktor->unvan ? $doktor->unvan . ' ' : '') . $doktor->ad_soyad . ' - Randevu Ajandam')
@section('meta_aciklama', $hizmet->meta_aciklama ?? Str::limit(strip_tags($hizmet->aciklama), 150))
@section('meta_anahtar_kelimeler', $hizmet->meta_anahtar_kelimeler ?? '')
@section('og_image', $hizmet->resim_url ?: asset('assets/images/logo.png'))
@section('og_type', 'website')

@section('icerik')
<style>
    .svc-page {
        position: relative;
        background: #FAFAFA;
        padding: 2.5rem 0 3.5rem;
        min-height: 70vh;
    }
    @media (min-width: 768px) {
        .svc-page { padding: 3rem 0 4rem; }
    }
    .svc-page-bg {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: none;
        z-index: 0;
    }
    .svc-page-bg span {
        position: absolute;
        border-radius: 9999px;
        filter: blur(120px);
    }
    .svc-page-bg .a {
        top: -12%;
        right: -8%;
        width: 28rem;
        height: 28rem;
        background: rgba(231, 181, 138, 0.12);
    }
    .svc-page-bg .b {
        bottom: -14%;
        left: -10%;
        width: 26rem;
        height: 26rem;
        background: rgba(201, 106, 43, 0.06);
    }
    .svc-page-inner {
        position: relative;
        z-index: 1;
        max-width: 80rem;
        margin: 0 auto;
        padding: 0 1rem;
    }
    @media (min-width: 640px) {
        .svc-page-inner { padding: 0 1.5rem; }
    }
    .svc-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6B7280;
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .svc-back:hover { color: #C96A2B; }
    .svc-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        align-items: start;
    }
    @media (min-width: 1024px) {
        .svc-layout {
            grid-template-columns: minmax(0, 1.35fr) minmax(18rem, 0.95fr);
            gap: 1.75rem;
        }
    }
    .svc-main,
    .svc-aside {
        min-width: 0;
        width: 100%;
    }
    @media (min-width: 1024px) {
        .svc-aside {
            position: sticky;
            top: 5.5rem;
        }
    }
    .svc-card {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(31, 41, 55, 0.03);
    }
    .svc-card + .svc-card { margin-top: 1.25rem; }
    .svc-media {
        width: 100%;
        aspect-ratio: 16 / 9;
        max-height: 22rem;
        background: linear-gradient(145deg, #FFF7ED, #F8FAFC);
        overflow: hidden;
    }
    .svc-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .svc-body {
        padding: 1.35rem 1.35rem 1.5rem;
    }
    @media (min-width: 768px) {
        .svc-body { padding: 1.75rem 2rem 2rem; }
    }
    .svc-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.55rem 0.75rem;
        padding-bottom: 0.95rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid #F1F5F9;
        font-size: 0.6875rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #C96A2B;
    }
    .svc-meta .dot { color: #D1D5DB; }
    .svc-title {
        margin: 0 0 0.85rem;
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.2;
        color: #111827;
    }
    @media (min-width: 768px) {
        .svc-title { font-size: 1.85rem; }
    }
    .svc-content {
        font-size: 0.9rem;
        line-height: 1.7;
        color: #4B5563;
        word-wrap: break-word;
        overflow-wrap: anywhere;
    }
    .svc-content :where(p, ul, ol) { margin: 0 0 0.85rem; }
    .svc-content :where(img, video, iframe) {
        max-width: 100%;
        height: auto;
        border-radius: 0.75rem;
    }
    .svc-content :where(h1, h2, h3, h4) {
        color: #111827;
        font-weight: 800;
        margin: 1.1rem 0 0.5rem;
        line-height: 1.3;
    }
    .svc-doc {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 1.15rem 1.25rem;
        align-items: stretch;
    }
    @media (min-width: 640px) {
        .svc-doc {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            padding: 1.25rem 1.5rem;
        }
    }
    .svc-doc-info {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        min-width: 0;
    }
    .svc-doc-avatar {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 1rem;
        overflow: hidden;
        flex-shrink: 0;
        background: #FFF7ED;
        border: 1px solid rgba(231, 181, 138, 0.35);
        color: #C96A2B;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.95rem;
    }
    .svc-doc-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .svc-doc-name {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 800;
        color: #111827;
        line-height: 1.25;
    }
    .svc-doc-brans {
        margin: 0.2rem 0 0;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #C96A2B;
    }
    .svc-doc-loc {
        margin: 0.25rem 0 0;
        font-size: 0.7rem;
        color: #6B7280;
    }
    .svc-doc-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        flex-shrink: 0;
        padding: 0.7rem 1.1rem;
        border-radius: 0.85rem;
        background: #1F2937;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        text-decoration: none;
        transition: background 0.15s ease;
        white-space: nowrap;
    }
    .svc-doc-btn:hover { background: #111827; color: #fff; }
    .svc-guest-note {
        margin: 0.85rem 0 0;
        text-align: center;
        font-size: 0.7rem;
        line-height: 1.5;
        color: #6B7280;
        padding: 0 0.5rem;
    }
    .svc-guest-note a {
        font-weight: 700;
        color: #C96A2B;
        text-decoration: none;
    }
    .svc-guest-note a:hover { text-decoration: underline; }
    .svc-closed {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 1.5rem;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 8px 30px rgba(31, 41, 55, 0.03);
    }
    .svc-closed-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 0.85rem;
        border-radius: 1rem;
        background: #FFF7ED;
        border: 1px solid rgba(231, 181, 138, 0.35);
        color: #C96A2B;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .svc-closed h3 {
        margin: 0 0 0.4rem;
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #111827;
    }
    .svc-closed p {
        margin: 0;
        font-size: 0.8rem;
        color: #6B7280;
        line-height: 1.55;
    }
    .svc-closed-actions {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #F1F5F9;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .svc-closed-actions a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.7rem 1rem;
        border-radius: 0.85rem;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        text-decoration: none;
    }
    .svc-closed-actions .primary {
        background: #C96A2B;
        color: #fff;
    }
    .svc-closed-actions .primary:hover { background: #B55A20; color: #fff; }
    .svc-closed-actions .ghost {
        border: 1px solid #E5E7EB;
        color: #1F2937;
    }
    .svc-closed-actions .ghost:hover { background: #F8FAFC; }
</style>

<section class="svc-page">
    <div class="svc-page-bg" aria-hidden="true">
        <span class="a"></span>
        <span class="b"></span>
    </div>

    <div class="svc-page-inner">
        <a href="{{ $doktor->profil_url }}" class="svc-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
            </svg>
            Hekim Profiline Dön
        </a>

        <div class="svc-layout">
            <div class="svc-main">
                <article class="svc-card">
                    @if($hizmet->resim_url)
                        <div class="svc-media">
                            <img src="{{ $hizmet->resim_url }}" alt="{{ $hizmet->ad }}">
                        </div>
                    @endif
                    <div class="svc-body">
                        <div class="svc-meta">
                            @if($hizmet->sure)
                                <span>{{ $hizmet->sure }} dk süre</span>
                                <span class="dot">•</span>
                            @endif
                            <span>Hizmet ve tedavi</span>
                        </div>
                        <h1 class="svc-title">{{ $hizmet->ad }}</h1>
                        @if($hizmet->aciklama)
                            <div class="svc-content">
                                {!! $hizmet->aciklama !!}
                            </div>
                        @else
                            <p class="svc-content" style="color:#9CA3AF">Bu hizmet hakkında detaylı açıklama bulunmamaktadır.</p>
                        @endif
                    </div>
                </article>

                @php
                    $words = preg_split('/\s+/', trim((string) $doktor->ad_soyad)) ?: [];
                    $kisaAd = collect($words)->filter()->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('') ?: 'DR';
                @endphp
                <div class="svc-card svc-doc">
                    <div class="svc-doc-info">
                        <div class="svc-doc-avatar">
                            @if($doktor->profil_resmi)
                                <img src="{{ asset($doktor->profil_resmi) }}" alt="{{ $doktor->ad_soyad }}">
                            @else
                                {{ $kisaAd }}
                            @endif
                        </div>
                        <div style="min-width:0">
                            <p class="svc-doc-name">{{ $doktor->unvan ? $doktor->unvan.' ' : '' }}{{ $doktor->ad_soyad }}</p>
                            <p class="svc-doc-brans">{{ $doktor->uzmanlik_alani ?? 'Uzman Hekim' }}</p>
                            <p class="svc-doc-loc">{{ $doktor->il?->ad }}{{ $doktor->ilce?->ad ? ' / '.$doktor->ilce->ad : '' }}</p>
                        </div>
                    </div>
                    <a href="{{ $doktor->profil_url }}" class="svc-doc-btn">
                        Profili Görüntüle
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <aside class="svc-aside">
                @if($doktor->randevuya_acik_mi)
                    @include('frontend.hekimler.partials.randevu_wizard', [
                        'doktor' => $doktor,
                        'preselectedHizmetId' => $hizmet->id,
                        'wizardCompact' => true,
                    ])

                    @guest('hasta')
                        <p class="svc-guest-note">
                            Hesap oluşturmadan randevu alabilirsiniz.
                            İsterseniz
                            <a href="{{ route('frontend.hasta.giris') }}">giriş yapın</a>
                            veya
                            <a href="{{ route('frontend.hasta.kayit') }}">üye olun</a>.
                        </p>
                    @endguest
                @else
                    <div class="svc-closed">
                        <div class="svc-closed-icon" aria-hidden="true">
                            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z"/>
                            </svg>
                        </div>
                        <h3>Randevu Al</h3>
                        <p>Hekimimiz online randevu alımına geçici olarak kapalıdır. Randevu bilgisi için lütfen iletişime geçiniz.</p>
                        <div class="svc-closed-actions">
                            @if($doktor->telefon)
                                <a href="tel:{{ $doktor->telefon }}" class="primary">{{ $doktor->telefon }}</a>
                            @endif
                            @if($doktor->e_posta)
                                <a href="mailto:{{ $doktor->e_posta }}" class="ghost">E-Posta ile İletişim</a>
                            @endif
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</section>
@endsection
