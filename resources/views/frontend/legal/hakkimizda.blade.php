@extends('frontend.layouts.app')

@section('baslik', \App\Support\SeoMeta::aboutTitle())
@section('meta_aciklama', \App\Support\SeoMeta::description('Randevu Ajandam; doktor, klinik ve hastaları buluşturan online randevu platformudur. Hekim yazılımı ve hasta randevu deneyimi bir arada.'))

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
        Kartlı abonelik ödemeleri <strong>PayTR</strong> güvenli ödeme altyapısı ile alınır (3D Secure).
        Sitemizde Visa, Mastercard, Troy ve PayTR logoları yer alır. SSL ile bağlantı şifrelenir.
        Kart bilgileri Randevu Ajandam sunucularında saklanmaz.
    </p>
    @include('frontend.layouts.partials.payment-methods', ['compact' => true])

    <h2 id="iletisim">4. İletişim</h2>
    @include('frontend.layouts.partials.company-identity')
    <p>
        E-posta: <a href="mailto:{{ config('company.email', 'info@randevuajandam.com') }}">{{ config('company.email', 'info@randevuajandam.com') }}</a><br>
        Telefon / WhatsApp: <a href="https://wa.me/{{ config('company.whatsapp', '905319912427') }}" target="_blank" rel="noopener">{{ config('company.telefon', '+90 531 991 24 27') }}</a><br>
        Detay: <a href="{{ route('frontend.legal.iletisim') }}">İletişim sayfası</a>
    </p>
@endcomponent
@endsection
