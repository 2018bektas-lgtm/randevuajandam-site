@extends('frontend.layouts.app')

@section('baslik', 'Mesafeli Satış ve Abonelik Sözleşmesi — Randevu Ajandam')
@section('meta_aciklama', 'Randevu Ajandam hekim ve klinik abonelik paketleri için mesafeli satış ve abonelik sözleşmesi.')

@section('icerik')
@php
    $sections = [
        'taraf' => '1. Taraflar',
        'konu' => '2. Konu',
        'hizmet' => '3. Hizmet ve paketler',
        'deneme' => '4. Ücretsiz deneme',
        'bedel' => '5. Bedel ve ödeme',
        'fatura' => '6. Fatura',
        'sure' => '7. Süre, yenileme ve paket değişimi',
        'domain' => '8. Domain ve web sitesi',
        'cayma' => '9. Cayma ve iptal',
        'yukumluluk' => '10. Yükümlülükler',
        'uyusmazlik' => '11. Uyuşmazlık',
    ];
@endphp

@component('frontend.legal._layout', [
    'baslik' => $baslik,
    'guncelleme' => $guncelleme,
    'ozet' => 'Hekim ve klinik abonelik paketlerinin (SaaS) mesafeli satışı. Fiyatlara KDV dahildir. Deneme bitince tam ücret; paket değişiminde süre sıfırdan başlar.',
    'sections' => $sections,
])
    <p>
        İşbu Mesafeli Satış ve Abonelik Sözleşmesi (“Sözleşme”),
        <strong>Randevu Ajandam</strong> platformu üzerinden sunulan dijital abonelik hizmetlerinin
        6502 sayılı Tüketicinin Korunması Hakkında Kanun, Mesafeli Sözleşmeler Yönetmeliği ve ilgili mevzuat
        çerçevesinde satışı ve kullanımına ilişkindir. Fiziksel mal teslimi yoktur.
    </p>

    <h2 id="taraf">1. Taraflar</h2>
    <p><strong>Satıcı / Hizmet sağlayıcı</strong></p>
    @include('frontend.layouts.partials.company-identity')
    <p>
        Platform:
        <a href="{{ config('company.web', 'https://randevuajandam.com') }}">{{ config('company.web', 'https://randevuajandam.com') }}</a>
        · <a href="{{ route('frontend.legal.iletisim') }}">İletişim</a>
    </p>
    <p>
        <strong>Alıcı / Abone:</strong> Platforma hekim veya klinik olarak kayıt olup
        ücretli paket seçen veya ücretsiz deneme başlatan gerçek veya tüzel kişi.
    </p>

    <h2 id="konu">2. Konu</h2>
    <p>
        Sözleşme konusu; randevu yönetimi, hasta/hekim paneli, isteğe bağlı özel web sitesi ve domain,
        paket kapsamında belirtilen dijital özelliklerin abonelik modeliyle sunulmasıdır.
    </p>

    <h2 id="hizmet">3. Hizmet ve paketler</h2>
    <p>
        Paket adları, kapsamı, deneme günü (varsa) ve fiyatları
        <a href="{{ route('frontend.paketler') }}">Paketler</a> sayfasında ve ödeme adımında
        güncel olarak gösterilir. Alıcı, ödeme veya deneme öncesi paket içeriğini incelemiş sayılır.
        Platform tıbbi teşhis/tedavi hizmeti değildir; mesleki sorumluluk hekim/kliniğe aittir.
    </p>

    <h2 id="deneme">4. Ücretsiz deneme</h2>
    <ul>
        <li>Belirli bireysel paketlerde, paket kartında açıkça yazılan süre kadar (ör. 14 gün) ücretsiz deneme sunulabilir.</li>
        <li>Deneme süresi boyunca ilgili paket özellikleri için ücret alınmaz; deneme <strong>otomatik ücretli aboneliğe dönüşmez</strong>.</li>
        <li>Deneme bitiminde devam için Alıcı’nın paket seçip <strong>tam ücret</strong> ödemesi gerekir.</li>
        <li>Deneme hakkı kural olarak bir kez kullanılabilir (sistemde “deneme kullanıldı” bayrağı).</li>
        <li>Deneme bitiş tarihi ve kalan gün, hekim paneli ve üyelik sayfasında gösterilir.</li>
    </ul>

    <h2 id="bedel">5. Bedel ve ödeme</h2>
    <ul>
        <li>Bedel, seçilen paket ve periyoda (aylık/yıllık) göre TL cinsinden tahsil edilir.</li>
        <li><strong>Tüm paket fiyatlarına KDV dahildir.</strong> Gösterilen tutar ödenecek nihai bedeldir.</li>
        <li>Kartlı ödemeler <strong>PayTR</strong> ve/veya <strong>iyzico</strong> altyapısı ile alınabilir; kart verileri sitemizde saklanmaz.</li>
        <li>Havale/EFT seçeneği açık ise, bildirim sonrası üyelik yönetici onayıyla açılabilir.</li>
        <li>Mobil mağaza abonelikleri (IAP) varsa App Store / Google Play kuralları da geçerlidir.</li>
    </ul>

    <h2 id="fatura">6. Fatura</h2>
    <ul>
        <li>Ücretli ödeme adımında Alıcı, fatura tipi (bireysel / kurumsal) ve fatura bilgilerini girer.</li>
        <li>Bilgiler hem hekim kaydında saklanır hem ilgili ödeme kaydına snapshot olarak yazılır.</li>
        <li>Satıcı, yürürlükteki vergi mevzuatına uygun faturalandırmayı (ör. e-Arşiv / e-Fatura, GİB süreçleri)
            <strong>manuel veya kendi muhasebe süreçleriyle</strong> gerçekleştirir; Platform otomatik GİB entegrasyonu taahhüt etmez.</li>
        <li>Alıcı, girdiği fatura bilgilerinin doğruluğundan sorumludur.</li>
    </ul>

    <h2 id="sure">7. Süre, yenileme ve paket değişimi</h2>
    <ul>
        <li>Ücretli abonelik, ödeme onayından itibaren seçilen periyot için geçerlidir
            (aylık ≈ bir ay; yıllık ≈ bir yıl — sistem takvim hesabı).</li>
        <li><strong>Paket değiştirme / yükseltme / yenileme ödemesinde süre sıfırdan başlar.</strong>
            Önceki dönemden kalan günler yeni bitiş tarihine eklenmez; kısmi dönem indirimi uygulanmaz.
            (Örn. uzun dönem alıp hemen üst pakete geçildiğinde “çift dönem bedava” oluşmaz.)</li>
        <li>Otomatik yenileme açıksa dönem sonunda aynı koşullarla yenilenebilir (ödeme kanalına bağlı).</li>
        <li>Alıcı panelden aboneliği iptal ederek yenilemeyi kapatabilir; ödenen dönem sonuna kadar erişim devam eder,
            sonrasında yeni çekim yapılmaz.</li>
    </ul>

    <h2 id="domain">8. Domain ve web sitesi</h2>
    <ul>
        <li>Web sitesi özellikli paketlerde ödeme öncesi domain seçimi istenebilir (yeni domain veya mevcut domain).</li>
        <li>Sistemde kayıtlı domain varsa Alıcı’ya “bu domain ile devam mı?” sorusu yöneltilir.</li>
        <li>Domain / DNS / Hostinger tarafı teknik kurulum ödeme veya onay sonrası tamamlanabilir;
            BYOD’da DNS sorumluluğu Alıcı’ya aittir.</li>
        <li>Hekim sitesinde yayınlanan içerik Alıcı’nın sorumluluğundadır.</li>
    </ul>

    <h2 id="cayma">9. Cayma ve iptal</h2>
    <p>
        Dijital abonelik / anında ifa edilen hizmetlerde mevzuatın öngördüğü istisnalar saklıdır.
        Detay:
        <a href="{{ route('frontend.legal.iade') }}">İade ve İptal Politikası</a>.
        Panelden “Aboneliği iptal et” ile yenileme kapatılabilir.
    </p>

    <h2 id="yukumluluk">10. Yükümlülükler</h2>
    <p>
        Alıcı, hesabını hukuka ve
        <a href="{{ route('frontend.legal.kullanim') }}">Kullanım Koşulları</a>’na uygun kullanır;
        mesleki mevzuata uyum sorumluluğu kendisine aittir.
        Hizmet sağlayıcı makul sürelerde erişilebilirlik ve güvenlik için çaba gösterir.
    </p>

    <h2 id="uyusmazlik">11. Uyuşmazlık</h2>
    <p>
        Uyuşmazlıklarda Türkiye Cumhuriyeti hukuku uygulanır.
        Tüketici işlemlerinde Tüketici Hakem Heyetleri ve Tüketici Mahkemeleri yetkilidir.
        Öncelikle
        <a href="mailto:{{ config('company.email', 'info@randevuajandam.com') }}">{{ config('company.email', 'info@randevuajandam.com') }}</a>
        üzerinden iletişime geçilmesi önerilir.
        Ticari uyuşmazlıklarda satıcının bulunduğu yer (Ankara) mahkemeleri yetkili kılınabilir.
    </p>

    <p class="text-xs text-slate-500 mt-8">
        Ödeme: PayTR / iyzico / havale · Kart: Visa, Mastercard, Troy (desteklenenlerde) · 3D Secure.
    </p>
@endcomponent
@endsection
