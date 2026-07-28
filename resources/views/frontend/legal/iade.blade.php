@extends('frontend.layouts.app')

@section('baslik', 'İade ve İptal Politikası — Randevu Ajandam')
@section('meta_aciklama', 'Randevu Ajandam abonelik iptali, cayma, deneme ve iade koşulları.')

@section('icerik')
@php
    $sections = [
        'kapsam' => '1. Kapsam',
        'dijital' => '2. Dijital abonelik',
        'deneme' => '3. Ücretsiz deneme',
        'iptal' => '4. Abonelik iptali',
        'paket' => '5. Paket değişimi',
        'iade' => '6. Ücret iadesi',
        'havale' => '7. Havale bildirimleri',
        'iletisim' => '8. İletişim',
    ];
@endphp

@component('frontend.legal._layout', [
    'baslik' => $baslik,
    'guncelleme' => $guncelleme,
    'ozet' => 'Dijital abonelikte iptal, deneme, paket değişimi ve iade kuralları. Fiziksel ürün gönderimi yoktur. Fatura satıcı tarafından manuel düzenlenebilir.',
    'sections' => $sections,
])
    <h2 id="kapsam">1. Kapsam</h2>
    <p>
        Bu politika, Randevu Ajandam üzerinden satın alınan
        <strong>dijital abonelik paketleri</strong> (bireysel hekim, klinik, web sitesi paketleri) için geçerlidir.
        Platform randevu yazılımı / SaaS hizmetidir; kargo ile mal teslimi yapılmaz.
    </p>

    <h2 id="dijital">2. Dijital abonelik</h2>
    <p>
        Ödeme onayından sonra panel erişimi ve paket özellikleri anında veya kısa süre içinde açılır
        (havale’de yönetici onayı gerekebilir). Anında ifa edilen dijital hizmetlerde mevzuatın öngördüğü
        cayma hakkı istisnaları uygulanabilir.
    </p>

    <h2 id="deneme">3. Ücretsiz deneme</h2>
    <ul>
        <li>Paket kartında yazılı gün sayısı kadar ücretsiz deneme sunulabilir.</li>
        <li>Deneme süresinde ücret alınmaz; deneme bitince <strong>otomatik ücretli abonelik başlamaz</strong>.</li>
        <li>Devam için paket seçip <strong>tam ücret</strong> ödemeniz gerekir.</li>
        <li>Deneme hakkı kural olarak bir kez kullanılır.</li>
        <li>Deneme bitiş tarihi hekim paneli ve üyelik sayfasında gösterilir.</li>
    </ul>

    <h2 id="iptal">4. Abonelik iptali</h2>
    <ul>
        <li>Hekim paneli → <strong>Üyelik / Abonelik</strong> → “Aboneliği iptal et”.</li>
        <li>İptal, <strong>otomatik yenilemeyi kapatır</strong>.</li>
        <li>Ödenen dönem bitiş tarihine kadar erişim <strong>devam eder</strong>.</li>
        <li>Dönem sonunda yeni ücret çekilmez; erişim sona erer.</li>
    </ul>

    <h2 id="paket">5. Paket değişimi</h2>
    <ul>
        <li>Yeni paket seçip ödeme yaptığınızda süre <strong>ödeme anından itibaren sıfırdan</strong> başlar
            (aylık / yıllık dönem).</li>
        <li><strong>Kalan günler devretmez</strong>; kısmi dönem iadesi veya mahsup genel kural değildir.</li>
        <li>Bu kural, uzun dönem alıp hemen üst pakete geçildiğinde “çift dönem bedava” oluşmasını önlemek içindir.</li>
        <li>Ödeme ve paket seçim ekranlarında bu husus ayrıca belirtilir.</li>
    </ul>

    <h2 id="iade">6. Ücret iadesi</h2>
    <p>
        Genel kural: Kullanıma açılmış dönem için kısmi iade yapılmaz.
        Hizmetin teknik olarak sunulamadığı, mükerrer tahsilat veya yasal zorunluluk hallerinde
        iade talebi değerlendirilir. Onaylanan iadeler, ödeme kuruluşu (PayTR / iyzico) ve banka sürelerine bağlıdır.
        Fatura düzenlenmişse iade/fatura iptali vergi mevzuatına uygun yapılır.
    </p>

    <h2 id="havale">7. Havale bildirimleri</h2>
    <ul>
        <li>Havale/EFT bildirimi “beklemede” iken üyeliğiniz henüz açılmamış olabilir.</li>
        <li>Onay sonrası erişim başlar; reddedilirse gerekçe panel veya destek üzerinden iletilir.</li>
        <li>Bekleyen bildirim varken yeni paket seçiminde dikkatli olun; destek ile netleştirin.</li>
    </ul>

    <h2 id="iletisim">8. İletişim</h2>
    <p>İptal / iade talepleri:</p>
    @include('frontend.layouts.partials.company-identity')
    <p>
        <a href="{{ route('frontend.legal.iletisim') }}">İletişim sayfası</a> ·
        WhatsApp:
        <a href="https://wa.me/{{ config('company.whatsapp', '905319912427') }}" target="_blank" rel="noopener">{{ config('company.telefon', '+90 531 991 24 27') }}</a>
    </p>
@endcomponent
@endsection
