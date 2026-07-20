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
    <h2 id="bilgi">1. İletişim bilgileri</h2>
    <div class="not-prose grid sm:grid-cols-2 gap-4 my-4">
        <div class="p-4 rounded-xl border border-slate-200 bg-white">
            <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">E-posta</p>
            <a href="mailto:info@randevuajandam.com" class="text-sm font-bold text-[#C96A2B]">info@randevuajandam.com</a>
        </div>
        <div class="p-4 rounded-xl border border-slate-200 bg-white">
            <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">WhatsApp / telefon</p>
            <a href="https://wa.me/905319912427" target="_blank" rel="noopener" class="text-sm font-bold text-emerald-700">+90 531 991 24 27</a>
        </div>
        <div class="p-4 rounded-xl border border-slate-200 bg-white sm:col-span-2">
            <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Web</p>
            <a href="https://randevuajandam.com" class="text-sm font-bold text-slate-800">https://randevuajandam.com</a>
        </div>
    </div>
    <p class="text-xs text-slate-500">
        Ticari unvan, vergi no ve açık adres bilgilerinizi şirket kuruluşu tamamlandıkça bu sayfaya ekleyin
        (iyzico üye işyeri incelemesi için önerilir).
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
