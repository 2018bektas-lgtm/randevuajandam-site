@extends('frontend.layouts.app')

@section('baslik', 'İletişim — Randevu Ajandam')
@section('meta_aciklama', 'Randevu Ajandam iletişim bilgileri: e-posta, WhatsApp ve destek.')

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
    'ozet' => 'iyzico mağaza şartları kapsamında site üzerinden doğrudan ulaşılabilir iletişim bilgileri.',
    'sections' => $sections,
])
    <h2 id="bilgi">1. İletişim ve ticari bilgiler</h2>
    @include('frontend.layouts.partials.company-identity')
    <p class="text-xs text-slate-500">
        Şirket bilgileri .env üzerinden girilir:
        <code class="text-[10px] bg-slate-100 px-1 rounded">COMPANY_UNVAN</code>,
        <code class="text-[10px] bg-slate-100 px-1 rounded">COMPANY_ADRES</code>,
        <code class="text-[10px] bg-slate-100 px-1 rounded">COMPANY_VERGI_NO</code>,
        <code class="text-[10px] bg-slate-100 px-1 rounded">COMPANY_MERSIS</code>,
        <code class="text-[10px] bg-slate-100 px-1 rounded">COMPANY_VERBIS</code> vb.
        Dolu olmayan alanlar “—” olarak görünür.
    </p>

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

    <div class="mt-8 not-prose">
        @include('frontend.layouts.partials.payment-methods')
    </div>
@endcomponent
@endsection
