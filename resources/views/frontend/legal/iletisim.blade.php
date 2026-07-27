@extends('frontend.layouts.app')

@section('baslik', \App\Support\SeoMeta::contactTitle())
@section('meta_aciklama', \App\Support\SeoMeta::description('Randevu Ajandam iletişim: hekim abonelik, hasta randevu ve teknik destek. E-posta ve WhatsApp ile bize ulaşın.'))

@section('icerik')
@php
    $sections = [
        'bilgi' => '1. İletişim bilgileri',
        'destek' => '2. Destek konuları',
        'yasal' => '3. Yasal belgeler',
    ];
@endphp

@component('frontend.legal._layout', [
    'baslik' => $baslik,
    'guncelleme' => $guncelleme,
    'ozet' => 'PayTR sanal POS / abonelik şartları kapsamında site üzerinden doğrudan ulaşılabilir iletişim ve adres bilgileri.',
    'sections' => $sections,
])
    <h2 id="bilgi">1. İletişim ve ticari bilgiler</h2>
    <p>
        Aşağıdaki unvan, yurt içi adres, telefon ve e-posta bilgileri sitemiz üzerinden
        doğrudan erişilebilir durumdadır (PayTR sanal POS / abonelik mağaza şartları).
    </p>
    @include('frontend.layouts.partials.company-identity')

    <h2 id="destek">2. Destek konuları</h2>
    <ul>
        <li>Hekim / klinik abonelik ve ödeme</li>
        <li>Randevu ve panel kullanımı</li>
        <li>Web sitesi / domain kurulumu</li>
        <li>KVKK ve veri talepleri</li>
    </ul>

    <h2 id="yasal">3. Yasal belgeler</h2>
    <ul>
        <li><a href="{{ route('frontend.legal.gizlilik') }}">Gizlilik politikası</a></li>
        <li><a href="{{ route('frontend.legal.kvkk') }}">KVKK aydınlatma</a></li>
        <li><a href="{{ route('frontend.legal.mesafeli') }}">Mesafeli satış / abonelik</a></li>
        <li><a href="{{ route('frontend.legal.iade') }}">İade ve iptal</a></li>
        <li><a href="{{ route('frontend.legal.kullanim') }}">Kullanım koşulları</a></li>
    </ul>

    <div class="not-prose mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        @include('frontend.layouts.partials.payment-methods', ['compact' => true])
    </div>
@endcomponent
@endsection
