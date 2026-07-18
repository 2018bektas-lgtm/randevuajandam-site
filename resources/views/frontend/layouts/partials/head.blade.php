@php
    $siteAyari = \App\Models\SiteAyari::cached();
    $defaultTitle = $siteAyari?->meta_baslik ?? 'Randevu Ajandam - Premium Randevu ve Danışan Yönetim Platformu';
    $defaultDesc = $siteAyari?->meta_aciklama ?? 'Uzman hekim ve kliniklerden online randevu alın. Randevu Ajandam ile hasta ve randevu yönetimini kolaylaştırın.';
    $pageTitle = trim($__env->yieldContent('baslik', $defaultTitle));
    $pageDesc = trim($__env->yieldContent('meta_aciklama', $defaultDesc));
    $ogImage = trim($__env->yieldContent('og_image', asset('assets/images/logo.png')));
    $canonical = url()->current();
    // reCAPTCHA: yalnızca form içeren sayfalarda (her sayfada Google script yok)
    $needsRecaptcha = request()->routeIs([
        'frontend.hasta.kayit',
        'frontend.hasta.kayit.post',
        'frontend.hasta.giris',
        'frontend.hasta.giris.post',
        'frontend.hekim.kayit',
        'frontend.hekim.kayit.post',
        'frontend.hekim.giris',
        'frontend.hekim.giris.post',
        'frontend.hekim.detay',
        'frontend.hekim.hizmet.detay',
        'frontend.hekim.egitim.detay',
        'frontend.paketler',
        'frontend.hekim.paket_ode',
        'frontend.hekim.paket_sec',
        'frontend.klinik.*',
        'frontend.randevu.yonet.hesap',
        'frontend.randevu.yonet.hesap.post',
    ]);
    $needsSelect2 = request()->routeIs([
        'frontend.hekimler',
        'frontend.hasta.profil',
        'frontend.hasta.randevular',
        'frontend.hekim.kayit',
        'frontend.paketler',
        'frontend.hekim.paket_ode',
        'frontend.hekim.paket_sec',
        'frontend.hekim.klinik.*',
        'frontend.klinik.*',
    ]);
@endphp
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@include('frontend.layouts.partials.tracking')
@if($needsRecaptcha)
    @include('frontend.layouts.partials.recaptcha')
@else
<script>
window.raRecaptchaSiteKey = '';
window.raGetRecaptchaToken = function () { return Promise.resolve(''); };
</script>
@endif
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDesc }}">
<meta name="keywords" content="@yield('meta_anahtar_kelimeler', $siteAyari?->meta_anahtar_kelimeler ?? 'randevu, hekim, klinik, online randevu')">
<meta name="author" content="{{ $siteAyari?->meta_yazar ?? 'Randevu Ajandam' }}">
<link rel="canonical" href="@yield('canonical', $canonical)">

<!-- Open Graph / Facebook -->
<meta property="og:locale" content="tr_TR">
<meta property="og:site_name" content="Randevu Ajandam">
<meta property="og:type" content="@yield('og_type', 'website')">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDesc }}">
<meta property="og:image" content="{{ $ogImage }}">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $canonical }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDesc }}">
<meta name="twitter:image" content="{{ $ogImage }}">

@hasSection('json_ld')
    @yield('json_ld')
@else
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            'name' => 'Randevu Ajandam',
            'url' => url('/'),
            'logo' => asset('assets/images/logo.png'),
            'description' => $defaultDesc,
        ],
        [
            '@type' => 'WebSite',
            'name' => 'Randevu Ajandam',
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('frontend.hekimler').'?arama={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
@endif

<link rel="shortcut icon" href="{{ asset('assets/images/logo.png') }}" type="image/png">
<!-- Google Fonts (display=swap, fewer weights) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet"></noscript>

@vite(['resources/css/app.css', 'resources/js/app.js'])
{{-- Kritik CSS/JS'i mümkün olduğunca erken; fontlar async --}}
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
<meta http-equiv="x-dns-prefetch-control" content="on">

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #F5F5F4;
    }
    .font-display {
        font-family: 'Outfit', sans-serif;
    }

    /* Logo Breathing Animation for Small Icons */
    @keyframes logo-breathing-small {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.06); }
    }
    .logo-breathing-small-animate {
        animation: logo-breathing-small 4s ease-in-out infinite;
    }

    /* Pulsating Ambient Glow Behind Logo */
    @keyframes pulse-glow {
        0%, 100% { transform: scale(1); opacity: 0.3; }
        50% { transform: scale(1.15); opacity: 0.5; }
    }
    .logo-ambient-glow {
        position: absolute;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        z-index: 0;
        animation: pulse-glow 3s ease-in-out infinite;
        filter: blur(8px);
        left: 0;
        top: 0;
        pointer-events: none;
    }

    /* Metallic Shimmer Sweep for Panel Images */
    .shimmer-overlay-small {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            115deg,
            transparent 35%,
            rgba(255, 255, 255, 0.75) 48%,
            rgba(255, 255, 255, 0.9) 50%,
            rgba(255, 255, 255, 0.75) 52%,
            transparent 65%
        );
        background-size: 200% 100%;
        background-position: -200% 0;
        mix-blend-mode: overlay;
        pointer-events: none;
        border-radius: 50%;
        animation: shimmer-sweep-img 5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    }
    @keyframes shimmer-sweep-img {
        0% { background-position: -200% 0; }
        25% { background-position: 180% 0; }
        100% { background-position: 180% 0; }
    }

    /* Shimmering Text Gradient Animation */
    @keyframes text-shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    .brand-text-shimmer {
        background: linear-gradient(
            120deg,
            #111827 20%,
            #C96A2B 45%,
            #E7B58A 50%,
            #C96A2B 55%,
            #111827 80%
        );
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: text-shimmer 5s linear infinite;
    }
    .brand-text-shimmer-light {
        background: linear-gradient(
            120deg,
            #FFFFFF 20%,
            #E7B58A 45%,
            #C96A2B 50%,
            #E7B58A 55%,
            #FFFFFF 80%
        );
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: text-shimmer 5s linear infinite;
    }
    </style>

@if($needsSelect2)
<!-- Select2 CSS (yalnızca filtre/form sayfaları) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    /* Select2 Premium Theme Override */
    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1px solid #E5E7EB !important;
        border-radius: 12px !important;
        background-color: #FAFAFA !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 10px !important;
        font-size: 0.875rem !important;
        font-family: 'Inter', sans-serif !important;
        color: #111827 !important;
        transition: border-color 0.15s, box-shadow 0.15s !important;
    }
    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #C96A2B !important;
        box-shadow: 0 0 0 3px rgba(201, 106, 43, 0.1) !important;
        outline: none !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111827 !important;
        line-height: 42px !important;
        padding-left: 0 !important;
        padding-right: 24px !important;
        font-size: 0.875rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9CA3AF !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
        right: 10px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #6B7280 transparent transparent transparent !important;
    }
    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent #C96A2B transparent !important;
    }
    .select2-dropdown {
        border: 1px solid #E5E7EB !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 30px rgba(31, 41, 55, 0.08) !important;
        font-family: 'Inter', sans-serif !important;
        overflow: hidden !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #E5E7EB !important;
        border-radius: 8px !important;
        padding: 6px 10px !important;
        font-size: 0.8125rem !important;
        font-family: 'Inter', sans-serif !important;
        outline: none !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #C96A2B !important;
        box-shadow: 0 0 0 2px rgba(201, 106, 43, 0.1) !important;
    }
    .select2-results__option {
        font-size: 0.875rem !important;
        padding: 8px 12px !important;
        color: #4B5563 !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #FFF7ED !important;
        color: #C96A2B !important;
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #FFF7ED !important;
        color: #C96A2B !important;
        font-weight: 600 !important;
    }
    .select2-search--dropdown {
        padding: 8px !important;
    }
    .select2-container {
        width: 100% !important;
    }
</style>
@endif

@yield('head')
