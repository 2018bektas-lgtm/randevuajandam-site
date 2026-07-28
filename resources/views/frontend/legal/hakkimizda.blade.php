@extends('frontend.layouts.app')

@section('baslik', \App\Support\SeoMeta::aboutTitle())
@section('meta_aciklama', \App\Support\SeoMeta::description('Randevu Ajandam; doktor, klinik ve hastaları buluşturan online randevu platformudur. Hekim yazılımı ve hasta randevu deneyimi bir arada.'))

@section('icerik')
@php
    $sections = [
        'biz' => '1. Biz kimiz',
        'ne' => '2. Ne sunuyoruz',
        'guven' => '3. Güven ve ödeme',
        'yasal' => '4. Yasal belgeler',
        'iletisim' => '5. İletişim',
    ];
@endphp

@component('frontend.legal._layout', [
    'baslik' => $baslik,
    'guncelleme' => $guncelleme,
    'ozet' => 'Randevu Ajandam; hasta, hekim ve klinikler için randevu, ajanda, abonelik ve isteğe bağlı web sitesi çözümleri sunar.',
    'sections' => $sections,
])
    <h2 id="biz">1. Biz kimiz</h2>
    <p>
        <strong>Randevu Ajandam</strong>, danışanları uzman sağlık ve danışmanlık profesyonelleriyle
        buluşturan; hekim ve kliniklere randevu, hasta ve ajanda yönetimi sağlayan dijital bir platformdur.
        Web: <a href="{{ config('company.web', 'https://randevuajandam.com') }}">{{ config('company.web', 'https://randevuajandam.com') }}</a>
    </p>
    <p>
        Platform bir <strong>randevu ve işletme yazılımı (SaaS)</strong>dır; tıbbi teşhis veya tedavi hizmeti sunmaz.
        Muayene ve danışmanlık süreci ilgili hekim/klinik ile hasta arasındadır.
    </p>

    <h2 id="ne">2. Ne sunuyoruz</h2>
    <ul>
        <li>Hastalar için online randevu ve uzman arama</li>
        <li>Hekim paneli: takvim, hastalar, içerik ve finans modülleri (pakete göre)</li>
        <li>Klinik yönetimi: ortak hasta havuzu, personel, raporlama (pakete göre)</li>
        <li>Ücretsiz deneme (belirli paketlerde) ve ücretli abonelik</li>
        <li>İsteğe bağlı özel web sitesi, tema (ör. Hipno, Delogis) ve domain</li>
        <li>Mobil uygulama desteği (hasta / hekim — yayın durumuna göre)</li>
    </ul>

    <h2 id="guven">3. Güven ve ödeme</h2>
    <p>
        Kartlı abonelik ödemeleri <strong>PayTR</strong> ve/veya <strong>iyzico</strong> altyapısı ile alınabilir (3D Secure).
        Havale seçeneği yönetici ayarına göre sunulur. SSL ile bağlantı şifrelenir.
        Kart bilgileri Randevu Ajandam sunucularında saklanmaz.
        Fatura bilgisi ödeme adımında alınır; faturalandırma satıcı tarafından mevzuata uygun yürütülür.
    </p>
    <div class="not-prose my-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        @include('frontend.layouts.partials.payment-methods', ['compact' => true])
    </div>

    <h2 id="yasal">4. Yasal belgeler</h2>
    <ul>
        <li><a href="{{ route('frontend.legal.kullanim') }}">Kullanım koşulları</a></li>
        <li><a href="{{ route('frontend.legal.gizlilik') }}">Gizlilik politikası</a></li>
        <li><a href="{{ route('frontend.legal.kvkk') }}">KVKK aydınlatma</a></li>
        <li><a href="{{ route('frontend.legal.mesafeli') }}">Mesafeli satış / abonelik</a></li>
        <li><a href="{{ route('frontend.legal.iade') }}">İade ve iptal</a></li>
    </ul>

    <h2 id="iletisim">5. İletişim</h2>
    @include('frontend.layouts.partials.company-identity')
    <p>
        <a href="{{ route('frontend.legal.iletisim') }}">İletişim sayfası</a>
        · WhatsApp:
        <a href="https://wa.me/{{ config('company.whatsapp', '905319912427') }}" target="_blank" rel="noopener">{{ config('company.telefon', '+90 531 991 24 27') }}</a>
    </p>
@endcomponent
@endsection
