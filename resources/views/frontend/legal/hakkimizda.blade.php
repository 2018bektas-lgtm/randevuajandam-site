@extends('frontend.layouts.app')

@section('baslik', 'Hakkımızda — Randevu Ajandam')
@section('meta_aciklama', 'Randevu Ajandam: hastaları uzman hekimlerle buluşturan dijital randevu ve ajanda platformu.')

@section('icerik')
@php
    $sections = [
        'biz' => '1. Biz kimiz',
        'ne' => '2. Ne sunuyoruz',
        'guven' => '3. Güven ve ödeme',
        'iletisim' => '4. İletişim',
    ];
@endphp

@component('frontend.legal._layout', [
    'baslik' => $baslik,
    'guncelleme' => $guncelleme,
    'ozet' => 'Randevu Ajandam; hasta, hekim ve klinikler için randevu, ajanda ve isteğe bağlı web sitesi çözümleri sunar.',
    'sections' => $sections,
])
    <h2 id="biz">1. Biz kimiz</h2>
    <p>
        <strong>Randevu Ajandam</strong>, danışanları uzman sağlık ve danışmanlık profesyonelleriyle
        buluşturan; hekim ve kliniklere randevu, hasta ve ajanda yönetimi sağlayan dijital bir platformdur.
        Web adresi: <a href="https://randevuajandam.com">randevuajandam.com</a>
    </p>

    <h2 id="ne">2. Ne sunuyoruz</h2>
    <ul>
        <li>Hastalar için online randevu ve uzman arama</li>
        <li>Hekim paneli: takvim, hastalar, içerik ve finans modülleri (pakete göre)</li>
        <li>Klinik yönetimi: ortak hasta havuzu, personel, raporlama (pakete göre)</li>
        <li>İsteğe bağlı özel web sitesi ve domain (üst paketler)</li>
    </ul>

    <h2 id="guven">3. Güven ve ödeme</h2>
    <p>
        Kartlı abonelik ödemeleri <strong>iyzico</strong> güvenli ödeme altyapısı ile alınır.
        Sitemizde Visa, Mastercard ve iyzico ile Öde logoları yer alır. SSL ile bağlantı şifrelenir.
    </p>
    @include('frontend.layouts.partials.payment-methods', ['compact' => true])

    <h2 id="iletisim">4. İletişim</h2>
    <p>
        E-posta: <a href="mailto:info@randevuajandam.com">info@randevuajandam.com</a><br>
        WhatsApp: <a href="https://wa.me/905319912427" target="_blank" rel="noopener">+90 531 991 24 27</a><br>
        Detay: <a href="{{ route('frontend.legal.iletisim') }}">İletişim sayfası</a>
    </p>
@endcomponent
@endsection
