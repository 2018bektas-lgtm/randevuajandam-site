@php
    $klinikUser = auth('doktor')->user();
    $klinik = $klinikUser?->klinik;
    $pkgHastaHavuzu = $klinik && $klinik->hasPaketFlag('hasta_havuzu');
    $pkgFinans = $klinik && $klinik->hasPaketFlag('merkezi_finans');
    $pkgRapor = $klinik && $klinik->hasPaketFlag('raporlama');
    $pkgWeb = $klinik && $klinik->hasPaketFlag('klinik_web_sitesi');
    $pkgToplu = $klinik && $klinik->hasPaketFlag('toplu_randevu');

    $ysbDash = [
        'href' => route('hekim.klinik.yonetim'),
        'match' => 'hekim.klinik.yonetim',
        'title' => 'Panel ozeti',
        'sub' => 'Klinik genel bakis',
    ];

    $randevuItems = [
        ['href' => route('hekim.klinik.randevular.takvim'), 'match' => 'hekim.klinik.randevular.takvim', 'label' => 'Klinik Takvimi'],
        [
            'href' => route('hekim.klinik.randevular.talepler'),
            'match' => 'hekim.klinik.randevular.talepler',
            'label' => 'Randevu Talepleri',
            'badge' => ! $pkgToplu ? 'Toplu' : null,
        ],
    ];

    $ekipItems = [];
    if ($klinikUser->hasClinicPermission('hekim_yonetimi')) {
        $ekipItems[] = ['href' => route('hekim.klinik.doktorlar'), 'match' => 'hekim.klinik.doktorlar*', 'label' => 'Hekim Yonetimi'];
    }
    if ($klinikUser->hasClinicPermission('personel_yonetimi')) {
        $ekipItems[] = ['href' => route('hekim.klinik.personeller'), 'match' => 'hekim.klinik.personeller*', 'label' => 'Personel Yonetimi'];
    }
    if ($pkgHastaHavuzu && $klinikUser->hasClinicPermission('ortak_hasta_havuzu')) {
        $ekipItems[] = ['href' => route('hekim.klinik.hastalar.index'), 'match' => 'hekim.klinik.hastalar*', 'label' => 'Ortak Hasta Havuzu'];
    }

    $finansItems = [];
    if ($pkgFinans && $klinikUser->hasClinicPermission('finans_yonetimi')) {
        $finansItems[] = ['href' => route('hekim.klinik.finans'), 'match' => 'hekim.klinik.finans*', 'label' => 'Finansal Analiz'];
        $finansItems[] = ['href' => route('hekim.klinik.giderler'), 'match' => 'hekim.klinik.giderler', 'label' => 'Klinik Giderleri'];
    }
    if ($pkgFinans && $klinikUser->hasClinicPermission('hakedis_yonetimi')) {
        $finansItems[] = ['href' => route('hekim.klinik.hakedisler'), 'match' => 'hekim.klinik.hakedisler', 'label' => 'Hakedis Yonetimi'];
    }
    if ($pkgRapor && $klinikUser->hasClinicPermission('finans_yonetimi')) {
        $finansItems[] = ['href' => route('hekim.klinik.raporlar'), 'match' => 'hekim.klinik.raporlar*', 'label' => 'Klinik Raporlari'];
    }

    $ayarItems = [];
    if ($klinikUser->hasClinicPermission('klinik_ayarlari')) {
        $ayarItems[] = ['href' => route('hekim.klinik.ayarlar'), 'match' => 'hekim.klinik.ayarlar', 'label' => 'Klinik Ayarlari'];
    }
    if ($pkgWeb && $klinikUser->hasClinicPermission('klinik_ayarlari')) {
        $ayarItems[] = ['href' => route('hekim.klinik.web-sitesi.kurulum'), 'match' => 'hekim.klinik.web-sitesi*', 'label' => 'Klinik Web Sitesi'];
    }
    if ($klinikUser->hasClinicPermission('duyuru_yonetimi')) {
        $ayarItems[] = ['href' => route('hekim.klinik.duyurular.index'), 'match' => 'hekim.klinik.duyurular*', 'label' => 'Duyuru Yonetimi'];
    }

    $ysbGroups = array_values(array_filter([
        [
            'id' => 'randevu',
            'label' => 'Randevu',
            'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
            'items' => $randevuItems,
        ],
        $ekipItems ? [
            'id' => 'ekip',
            'label' => 'Ekip & Hastalar',
            'icon' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0z',
            'items' => $ekipItems,
        ] : null,
        $finansItems ? [
            'id' => 'finans',
            'label' => 'Finans & Rapor',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'items' => $finansItems,
        ] : null,
        $ayarItems ? [
            'id' => 'ayarlar',
            'label' => 'Ayarlar & Web',
            'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            'items' => $ayarItems,
        ] : null,
    ]));

    $ysbExtraHtml = '<a href="'.e(route('hekim.panel')).'" class="ysb-dash" style="margin-top:.25rem">'
        .'<span class="ysb-dash-text"><span class="ysb-dash-title">Hekim Paneline Don</span>'
        .'<span class="ysb-dash-sub">Kisisel panel</span></span></a>';

    $ysbSectionLabel = 'Menu';
@endphp
@include('partials.sidebar-ysb-nav')
