@extends('frontend.layouts.app')

@section('baslik', 'İade ve İptal Politikası — Randevu Ajandam')
@section('meta_aciklama', 'Randevu Ajandam abonelik iptali, cayma ve iade koşulları.')

@section('icerik')
@php
    $sections = [
        'kapsam' => '1. Kapsam',
        'dijital' => '2. Dijital abonelik',
        'iptal' => '3. Abonelik iptali',
        'iade' => '4. Ücret iadesi',
        'deneme' => '5. Ücretsiz deneme',
        'iletisim' => '6. İletişim',
    ];
@endphp

@component('frontend.legal._layout', [
    'baslik' => $baslik,
    'guncelleme' => $guncelleme,
    'ozet' => 'Hekim/klinik abonelik paketlerinde iptal, cayma ve iade kuralları. Fiziksel ürün gönderimi yoktur.',
    'sections' => $sections,
])
    <h2 id="kapsam">1. Kapsam</h2>
    <p>
        Bu politika, Randevu Ajandam üzerinden satın alınan
        <strong>dijital abonelik paketleri</strong> için geçerlidir.
        Platform randevu yazılımı / SaaS hizmetidir; kargo ile mal teslimi yapılmaz.
    </p>

    <h2 id="dijital">2. Dijital abonelik</h2>
    <p>
        Ödeme onayından sonra panel erişimi ve paket özellikleri anında veya kısa süre içinde açılır.
        Anında ifa edilen dijital hizmetlerde mevzuatın öngördüğü cayma hakkı istisnaları uygulanabilir.
    </p>

    <h2 id="iptal">3. Abonelik iptali</h2>
    <ul>
        <li>Hekim paneli → <strong>Üyelik / Abonelik</strong> → “Aboneliği iptal et”.</li>
        <li>İptal, <strong>otomatik yenilemeyi kapatır</strong>.</li>
        <li>Ödenen dönem bitiş tarihine kadar erişim <strong>devam eder</strong>.</li>
        <li>Dönem sonunda yeni ücret çekilmez; erişim sona erer.</li>
        <li>Kartlı aboneliklerde yenileme iyzico tarafında da durdurulur (yapılandırılmışsa).</li>
    </ul>

    <h2 id="iade">4. Ücret iadesi</h2>
    <p>
        Genel kural: Kullanıma açılmış dönem için kısmi iade yapılmaz.
        Hizmetin teknik olarak sunulamadığı, mükerrer tahsilat veya yasal zorunluluk hallerinde
        iade talebi değerlendirilir. Onaylanan iadeler, ödeme kuruluşu (iyzico) ve banka sürelerine bağlıdır.
    </p>

    <h2 id="deneme">5. Ücretsiz deneme</h2>
    <p>
        Başlangıç paketindeki ücretsiz deneme süresinde ücret alınmaz.
        Deneme bitiminde paket seçilmezse erişim kısıtlanır; deneme hakkı bir kez kullanılabilir.
    </p>

    <h2 id="iletisim">6. İletişim</h2>
    <p>
        İptal / iade talepleri:
        <a href="mailto:info@randevuajandam.com">info@randevuajandam.com</a> ·
        WhatsApp: <a href="https://wa.me/905319912427" target="_blank" rel="noopener">+90 531 991 24 27</a> ·
        <a href="{{ route('frontend.legal.iletisim') }}">İletişim formu sayfası</a>
    </p>
@endcomponent
@endsection
