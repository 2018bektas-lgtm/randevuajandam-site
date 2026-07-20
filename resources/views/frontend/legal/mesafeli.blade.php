@extends('frontend.layouts.app')

@section('baslik', 'Mesafeli Satış ve Abonelik Sözleşmesi — Randevu Ajandam')
@section('meta_aciklama', 'Randevu Ajandam hekim ve klinik abonelik paketleri için mesafeli satış ve abonelik sözleşmesi.')

@section('icerik')
@php
    $sections = [
        'taraf' => '1. Taraflar',
        'konu' => '2. Konu',
        'hizmet' => '3. Hizmet ve paketler',
        'bedel' => '4. Bedel ve ödeme',
        'sure' => '5. Süre ve yenileme',
        'cayma' => '6. Cayma ve iptal',
        'yukumluluk' => '7. Yükümlülükler',
        'uyusmazlik' => '8. Uyuşmazlık',
    ];
@endphp

@component('frontend.legal._layout', [
    'baslik' => $baslik,
    'guncelleme' => $guncelleme,
    'ozet' => 'Hekim ve klinik abonelik paketlerinin (SaaS) mesafeli satışı ve abonelik koşulları. Fiyatlara KDV dahildir. Kartlı ödemeler PayTR altyapısı ile alınır.',
    'sections' => $sections,
])
    <p>
        İşbu Mesafeli Satış ve Abonelik Sözleşmesi (“Sözleşme”),
        <strong>Randevu Ajandam</strong> platformu üzerinden sunulan dijital abonelik hizmetlerinin
        6502 sayılı Tüketicinin Korunması Hakkında Kanun ve ilgili mevzuat çerçevesinde
        satışı ve kullanımına ilişkindir.
    </p>

    <h2 id="taraf">1. Taraflar</h2>
    <p><strong>Satıcı / Hizmet sağlayıcı</strong></p>
    @include('frontend.layouts.partials.company-identity')
    <p>
        Platform: <a href="{{ config('company.web', 'https://randevuajandam.com') }}">{{ config('company.web', 'https://randevuajandam.com') }}</a>
        · <a href="{{ route('frontend.legal.iletisim') }}">İletişim</a>
    </p>
    <p>
        <strong>Alıcı / Abone:</strong> Platforma hekim veya klinik olarak kayıt olup
        ücretli paket seçen gerçek veya tüzel kişi.
    </p>

    <h2 id="konu">2. Konu</h2>
    <p>
        Sözleşme konusu; randevu yönetimi, hasta/hekim paneli, isteğe bağlı web sitesi ve
        paket kapsamında belirtilen dijital özelliklerin abonelik modeliyle sunulmasıdır.
        Fiziksel mal teslimi yoktur.
    </p>

    <h2 id="hizmet">3. Hizmet ve paketler</h2>
    <p>
        Paket adları, kapsamı ve fiyatları
        <a href="{{ route('frontend.paketler') }}">Paketler</a> sayfasında ve ödeme adımında
        güncel olarak gösterilir. Alıcı, ödeme öncesi paket içeriğini incelemiş sayılır.
    </p>

    <h2 id="bedel">4. Bedel ve ödeme</h2>
    <ul>
        <li>Bedel, seçilen paket ve periyoda (aylık/yıllık) göre TL cinsinden tahsil edilir.</li>
        <li>Kartlı ödemeler <strong>PayTR</strong> ödeme kuruluşu altyapısı ile alınır; kart verileri sitemizde saklanmaz.</li>
        <li>Havale/EFT seçeneği sunulduğunda, onay sonrası üyelik yönetici onayıyla açılabilir.</li>
        <li><strong>Tüm paket fiyatlarına KDV dahildir.</strong> Gösterilen tutar ödenecek nihai bedeldir.</li>
    </ul>

    <h2 id="sure">5. Süre ve yenileme</h2>
    <p>
        Abonelik, seçilen periyot için geçerlidir. Otomatik yenileme açıksa dönem sonunda
        aynı koşullarla yenilenebilir. Alıcı, panel üzerinden
        <strong>aboneliği iptal</strong> ederek yenilemeyi kapatabilir; bu durumda
        ödenen dönem sonuna kadar erişim devam eder, sonrasında yeni çekim yapılmaz.
    </p>

    <h2 id="cayma">6. Cayma ve iptal</h2>
    <p>
        Dijital abonelik / anında ifa edilen hizmetlerde mevzuatın öngördüğü istisnalar saklıdır.
        Detaylar için
        <a href="{{ route('frontend.legal.iade') }}">İade ve İptal Politikası</a> sayfasına bakınız.
        Panelden “Aboneliği iptal et” ile yenileme kapatılabilir (dönem sonuna kadar kullanım hakkı korunur).
    </p>

    <h2 id="yukumluluk">7. Yükümlülükler</h2>
    <p>
        Alıcı, hesabını hukuka ve kullanım koşullarına uygun kullanır; mesleki mevzuata (hekimlik/klinik)
        uyum sorumluluğu kendisine aittir. Hizmet sağlayıcı, makul sürelerde erişilebilirlik ve güvenlik için çaba gösterir.
    </p>

    <h2 id="uyusmazlik">8. Uyuşmazlık</h2>
    <p>
        Uyuşmazlıklarda Türkiye Cumhuriyeti hukuku uygulanır. Tüketici işlemlerinde
        Tüketici Hakem Heyetleri ve Tüketici Mahkemeleri yetkilidir.
        Öncelikle <a href="mailto:info@randevuajandam.com">info@randevuajandam.com</a> üzerinden iletişime geçilmesi önerilir.
    </p>

    <p class="text-xs text-slate-500 mt-8">
        Ödeme altyapısı: iyzico · Kart logoları: Visa, Mastercard, Troy · Güvenlik: 3D Secure (desteklenen işlemlerde).
    </p>
@endcomponent
@endsection
