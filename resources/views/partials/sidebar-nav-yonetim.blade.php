@php
    $bekleyenYorum = \App\Models\Yorum::where('onay_durumu', 'beklemede')->count();
    $bekleyenMeslek = \App\Models\Doktor::where('meslek_dogrulama_durumu', 'beklemede')->count();
    $faturaBekleyen = \Illuminate\Support\Facades\Schema::hasColumn('uyelik_odemeleri', 'fatura_durumu')
        ? \App\Models\UyelikOdeme::where('durum', 'onaylandi')->where('fatura_durumu', 'bekliyor')->count()
        : 0;

    $ysbDash = [
        'href' => route('yonetim.panel'),
        'match' => 'yonetim.panel',
        'title' => 'Panel ozeti',
        'sub' => 'Genel bakis',
    ];

    $ysbGroups = [
        [
            'id' => 'operasyon',
            'label' => 'Operasyon',
            'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
            'items' => [
                ['href' => route('yonetim.randevular'), 'match' => 'yonetim.randevular', 'label' => 'Randevular'],
                ['href' => route('yonetim.hastalar'), 'match' => 'yonetim.hastalar', 'label' => 'Hastalar'],
                ['href' => route('yonetim.uyelikler'), 'match' => 'yonetim.uyelikler', 'label' => 'Uyelikler'],
                [
                    'href' => route('yonetim.doktorlar.meslek-kuyruk'),
                    'match' => 'yonetim.doktorlar.meslek-kuyruk',
                    'label' => 'Meslek kuyrugu',
                    'badge' => $bekleyenMeslek > 0 ? (string) $bekleyenMeslek : null,
                ],
                [
                    'href' => route('yonetim.faturalar'),
                    'match' => 'yonetim.faturalar*',
                    'label' => 'Faturalar',
                    'badge' => $faturaBekleyen > 0 ? (string) $faturaBekleyen : null,
                ],
                ['href' => route('yonetim.edevlet-loglari'), 'match' => 'yonetim.edevlet-loglari', 'label' => 'e-Devlet log'],
            ],
        ],
        [
            'id' => 'hesaplar',
            'label' => 'Hesaplar',
            'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
            'items' => [
                ['href' => route('yonetim.doktorlar.index'), 'match' => 'yonetim.doktorlar.*', 'label' => 'Doktorlar'],
                ['href' => route('yonetim.klinikler.index'), 'match' => 'yonetim.klinikler.*', 'label' => 'Klinikler'],
                ['href' => route('yonetim.yoneticiler.index'), 'match' => 'yonetim.yoneticiler.*', 'label' => 'Yoneticiler'],
            ],
        ],
        [
            'id' => 'tanimlar',
            'label' => 'Tanimlar',
            'icon' => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z M6 6h.008v.008H6V6z',
            'items' => [
                ['href' => route('yonetim.branslar.index'), 'match' => 'yonetim.branslar.*', 'label' => 'Branslar'],
                ['href' => route('yonetim.unvanlar.index'), 'match' => 'yonetim.unvanlar.*', 'label' => 'Unvanlar'],
                ['href' => route('yonetim.paketler.index'), 'match' => 'yonetim.paketler.*', 'label' => 'Paketler'],
            ],
        ],
        [
            'id' => 'icerik',
            'label' => 'Icerik',
            'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
            'items' => [
                ['href' => route('yonetim.hizmetler.index'), 'match' => 'yonetim.hizmetler.*', 'label' => 'Hizmetler'],
                ['href' => route('yonetim.bloglar.index'), 'match' => 'yonetim.bloglar.*', 'label' => 'Bloglar'],
                ['href' => route('yonetim.faqs.index'), 'match' => 'yonetim.faqs.*', 'label' => 'S.S.S.'],
                ['href' => route('yonetim.galeriler.index'), 'match' => 'yonetim.galeriler.*', 'label' => 'Galeriler'],
                [
                    'href' => route('yonetim.yorumlar.index'),
                    'match' => 'yonetim.yorumlar.*',
                    'label' => 'Yorumlar',
                    'badge' => $bekleyenYorum > 0 ? (string) $bekleyenYorum : null,
                ],
            ],
        ],
        [
            'id' => 'sistem',
            'label' => 'Sistem',
            'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            'items' => [
                ['href' => route('yonetim.seo'), 'match' => 'yonetim.seo', 'label' => 'SEO Ayarlari'],
                ['href' => route('yonetim.odeme-ayarlari'), 'match' => 'yonetim.odeme-ayarlari*', 'label' => 'Odeme Ayarlari'],
                ['href' => route('yonetim.uyelik-odemeleri.index'), 'match' => 'yonetim.uyelik-odemeleri.*', 'label' => 'Uyelik Odemeleri'],
                ['href' => route('yonetim.two-factor'), 'match' => 'yonetim.two-factor', 'label' => '2FA Guvenlik'],
            ],
        ],
    ];

    $ysbSectionLabel = 'Menu';
    $ysbExtraHtml = '';
@endphp
@include('partials.sidebar-ysb-nav')
