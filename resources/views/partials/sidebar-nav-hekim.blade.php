@php
    $doktorUser = auth('doktor')->user();
    $aktifPaket = $doktorUser ? $doktorUser->aktifPaket() : null;
    $hasHakkimda = $doktorUser && $aktifPaket && $aktifPaket->hasFeature('hakkimda');
    $hasBlog = $doktorUser && $aktifPaket && $aktifPaket->hasFeature('blog');
    $hasTalepler = $doktorUser && $aktifPaket && $aktifPaket->hasFeature('randevu_talepleri');
    $hasFaq = $doktorUser && $aktifPaket && $aktifPaket->hasFeature('faq');
    $hasFinans = $doktorUser && $aktifPaket && $aktifPaket->hasFeature('finans');
    $hasGaleri = $doktorUser && $aktifPaket && $aktifPaket->hasFeature('galeri');
    $hasWebSitesi = $doktorUser && $aktifPaket && $aktifPaket->hasFeature('web_sitesi');
    $hasEgitimler = $doktorUser && $aktifPaket && $aktifPaket->hasFeature('egitimler');
    $paketYukseltUrl = route('frontend.hekim.paket_sec');

    $ysbDash = [
        'href' => route('hekim.panel'),
        'match' => 'hekim.panel',
        'title' => 'Panel ozeti',
        'sub' => 'Genel bakis',
    ];

    $ysbGroups = [
        [
            'id' => 'randevu',
            'label' => 'Randevu & Hastalar',
            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'items' => [
                ['href' => route('hekim.randevu.takvim'), 'match' => 'hekim.randevu.takvim', 'label' => 'Takvimim'],
                ['href' => route('hekim.randevu.talepler'), 'match' => 'hekim.randevu.talepler', 'label' => 'Randevu Talepleri', 'locked' => ! $hasTalepler],
                ['href' => route('hekim.randevu.bekleme-listesi'), 'match' => 'hekim.randevu.bekleme-listesi*', 'label' => 'Bekleme Listesi'],
                ['href' => route('hekim.randevu.hastalar'), 'match' => 'hekim.randevu.hastalar', 'label' => 'Hasta Kayitlari'],
                ['href' => route('hekim.randevu.ayarlar'), 'match' => 'hekim.randevu.ayarlar', 'label' => 'Randevu Ayarlari'],
            ],
        ],
        [
            'id' => 'icerik',
            'label' => 'Icerik',
            'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
            'items' => [
                ['href' => route('hekim.hizmetler.index'), 'match' => 'hekim.hizmetler.*', 'label' => 'Hizmet ve Tedaviler'],
                ['href' => $hasHakkimda ? route('hekim.hakkimda') : $paketYukseltUrl, 'match' => 'hekim.hakkimda', 'label' => 'Hakkimda Sayfam', 'locked' => ! $hasHakkimda],
                ['href' => $hasBlog ? route('hekim.bloglar.index') : $paketYukseltUrl, 'match' => 'hekim.bloglar.*', 'label' => 'Blog Yazilarim', 'locked' => ! $hasBlog],
                [
                    'href' => $hasEgitimler ? route('hekim.egitimler.index') : $paketYukseltUrl,
                    'match' => ['hekim.egitimler.index', 'hekim.egitimler.create', 'hekim.egitimler.edit', 'hekim.egitimler.store', 'hekim.egitimler.update'],
                    'label' => 'Egitimler',
                    'locked' => ! $hasEgitimler,
                ],
                [
                    'href' => $hasEgitimler ? route('hekim.egitimler.basvurular.tumu') : $paketYukseltUrl,
                    'match' => ['hekim.egitimler.basvurular', 'hekim.egitimler.basvurular.tumu'],
                    'label' => 'Egitim basvurulari',
                    'locked' => ! $hasEgitimler,
                ],
                // Yorum listesi/yanıt tüm aktif panellerde (paket gate yok; onay yalnızca yönetimde)
                ['href' => route('hekim.yorumlar.index'), 'match' => 'hekim.yorumlar.*', 'label' => 'Hasta Yorumlari'],
                ['href' => $hasFaq ? route('hekim.faqs.index') : $paketYukseltUrl, 'match' => 'hekim.faqs.*', 'label' => 'SSS', 'locked' => ! $hasFaq],
                ['href' => $hasGaleri ? route('hekim.galeriler.index') : $paketYukseltUrl, 'match' => 'hekim.galeriler.*', 'label' => 'Fotograf Galerisi', 'locked' => ! $hasGaleri],
            ],
        ],
        [
            'id' => 'finans',
            'label' => 'Finans',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'items' => [
                ['href' => $hasFinans ? route('hekim.finans.index') : $paketYukseltUrl, 'match' => 'hekim.finans.index', 'label' => 'Genel Bakis', 'locked' => ! $hasFinans],
                ['href' => $hasFinans ? route('hekim.finans.gelirler') : $paketYukseltUrl, 'match' => 'hekim.finans.gelirler', 'label' => 'Gelirler', 'locked' => ! $hasFinans],
                ['href' => $hasFinans ? route('hekim.finans.giderler') : $paketYukseltUrl, 'match' => 'hekim.finans.giderler', 'label' => 'Giderler', 'locked' => ! $hasFinans],
                ['href' => $hasFinans ? route('hekim.finans.hasta-bakiyeleri') : $paketYukseltUrl, 'match' => 'hekim.finans.hasta-bakiyeleri', 'label' => 'Hasta Bakiyeleri', 'locked' => ! $hasFinans],
                ['href' => $hasFinans ? route('hekim.finans.kategoriler') : $paketYukseltUrl, 'match' => 'hekim.finans.kategoriler', 'label' => 'Kategoriler', 'locked' => ! $hasFinans],
            ],
        ],
        [
            'id' => 'site',
            'label' => 'Web & Hesap',
            'icon' => 'M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3',
            'items' => array_values(array_filter([
                ['href' => $hasWebSitesi ? route('hekim.web-sitesi.kurulum') : $paketYukseltUrl, 'match' => 'hekim.web-sitesi.*', 'label' => 'Kisisel Web Sitem', 'locked' => ! $hasWebSitesi],
                // Klinik geçişi yalnızca yönetici tarafından yapılabilir — hekim sidebar'dan kaldırıldı
                ['href' => route('hekim.uyelik'), 'match' => 'hekim.uyelik*', 'label' => 'Uyelik / Abonelik'],
                ['href' => route('hekim.referans'), 'match' => 'hekim.referans*', 'label' => 'Referans programi'],
                ['href' => route('hekim.profil'), 'match' => 'hekim.profil', 'label' => 'Profil'],
                ['href' => route('hekim.sifre'), 'match' => 'hekim.sifre', 'label' => 'Sifre Degistir'],
                ['href' => route('hekim.two-factor'), 'match' => 'hekim.two-factor', 'label' => '2FA Guvenlik'],
            ])),
        ],
    ];

    if ($doktorUser && $doktorUser->klinikteMi()) {
        $ysbGroups[] = [
            'id' => 'bagli-klinik',
            'label' => 'Bagli Klinik',
            'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            'items' => [
                ['href' => route('hekim.klinik.uye.duyurular'), 'match' => 'hekim.klinik.uye.duyurular', 'label' => 'Klinik Duyurulari'],
                ['href' => route('hekim.klinik.uye.hastalar'), 'match' => 'hekim.klinik.uye.hastalar', 'label' => 'Hasta Havuzu'],
                ['href' => route('hekim.klinik.uye.bilgiler'), 'match' => 'hekim.klinik.uye.bilgiler', 'label' => 'Klinik Bilgileri'],
            ],
        ];
    }

    $ysbExtraHtml = '';
    if ($doktorUser && $doktorUser->hasClinicPermission('yonetim_paneli')) {
        $ysbExtraHtml .= '<a href="'.e(route('hekim.klinik.yonetim')).'" class="ysb-dash" style="margin-top:.35rem;background:linear-gradient(135deg,#1e3a5f,#2a4f7f);border-color:transparent">'
            .'<span class="ysb-dash-text"><span class="ysb-dash-title" style="color:#fff">Klinik Paneline Git</span>'
            .'<span class="ysb-dash-sub" style="color:rgba(255,255,255,.7)">Yonetim</span></span></a>';
    }
    if ($doktorUser && $doktorUser->paket && (float) $doktorUser->paket->aylik_fiyat == 0) {
        $ysbExtraHtml .= '<div style="margin-top:.65rem;padding:.85rem;border-radius:.9rem;background:#fff7ed;border:1px solid #fed7aa">'
            .'<div style="font-size:.75rem;font-weight:700;color:#92400e">Demo Hesabi</div>'
            .'<a href="'.e(route('frontend.paketler')).'" style="font-size:.7rem;font-weight:800;color:#c96a2b">Paketleri Incele</a></div>';
    }

    $ysbSectionLabel = 'Menu';
@endphp
@include('partials.sidebar-ysb-nav')
