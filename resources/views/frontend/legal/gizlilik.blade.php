@extends('frontend.layouts.app')

@section('baslik', 'Gizlilik Politikası — Randevu Ajandam')
@section('meta_aciklama', 'Randevu Ajandam gizlilik politikası: verilerin toplanması, işlenmesi, saklanması ve haklarınız.')

@section('icerik')
@php
    $sections = [
        'kapsam' => '1. Kapsam',
        'sorumlu' => '2. Veri sorumlusu',
        'toplanan' => '3. Toplanan veriler',
        'amac' => '4. İşleme amaçları',
        'hukuki' => '5. Hukuki dayanak',
        'paylasim' => '6. Paylaşım ve aktarım',
        'saklama' => '7. Saklama ve silme',
        'guvenlik' => '8. Güvenlik',
        'cerez' => '9. Çerezler',
        'cocuk' => '10. Çocuklar',
        'mobil' => '11. Mobil uygulamalar',
        'web' => '12. Hekim web siteleri',
        'haklar' => '13. Haklarınız',
        'degisiklik' => '14. Değişiklikler',
        'iletisim' => '15. İletişim',
    ];
@endphp

@component('frontend.legal._layout', [
    'baslik' => $baslik,
    'guncelleme' => $guncelleme,
    'ozet' => 'Web, panel, hekim özel siteleri ve mobil uygulamalarımızda kişisel verilerinizi nasıl topladığımızı, neden kullandığımızı ve nasıl koruduğumuzu açıklar. Detaylı aydınlatma için KVKK metnine bakınız.',
    'sections' => $sections,
])
    <p>
        Bu Gizlilik Politikası, <strong>Randevu Ajandam</strong>’ın
        <a href="{{ config('company.web', 'https://randevuajandam.com') }}">{{ config('company.web', 'https://randevuajandam.com') }}</a>
        sitesi, hekim/klinik/personel panelleri, hekim domain’li web siteleri ve hasta–hekim mobil uygulamaları
        (“Hizmetler”) için geçerlidir. Hizmetleri kullanarak bu politikada açıklanan uygulamaları kabul etmiş sayılırsınız.
    </p>
    <p>
        Politika yapısı; sektördeki benzer platformlar gibi (gizlilik, kullanım şartları, KVKK ayrımı)
        şeffaflık ve erişilebilirlik ilkelerine dayanır; içerik tamamen Randevu Ajandam hizmet modeline özeldir.
    </p>

    <h2 id="kapsam">1. Kapsam</h2>
    <p>
        Politika; ziyaretçi, hasta/danışan, hekim, klinik yetkilisi ve personel kullanıcılarını kapsar.
        Hekim–danışan arasındaki tıbbi sürecin içeriği öncelikle ilgili hekim/kliniğin sorumluluğundadır;
        Platform altyapı ve operasyonel verileri yönetir. Detay:
        <a href="{{ route('frontend.legal.kvkk') }}">KVKK Aydınlatma Metni</a>.
    </p>

    <h2 id="sorumlu">2. Veri sorumlusu</h2>
    @include('frontend.layouts.partials.company-identity')

    <h2 id="toplanan">3. Toplanan veriler</h2>
    <h3>3.1 Doğrudan verdiğiniz veriler</h3>
    <ul>
        <li>Ad, soyad, e-posta, telefon</li>
        <li>Hekim: unvan, branş, biyografi, adres, çalışma saatleri, galeri, blog, eğitim, T.C. / meslek belgesi</li>
        <li>Randevu ve form alanları (not, eğitim başvurusu)</li>
        <li>Ödeme adımında fatura bilgileri (bireysel/kurumsal unvan, TC veya vergi no, vergi dairesi, fatura adresi)</li>
        <li>Domain / web sitesi tercihleri</li>
        <li>Destek yazışmaları</li>
    </ul>
    <h3>3.2 Otomatik toplanan veriler</h3>
    <ul>
        <li>IP, tarayıcı/cihaz, oturum ve güvenlik logları</li>
        <li>SMS doğrulama ve reCAPTCHA (yapılandırılmışsa)</li>
        <li>Push bildirim cihaz token’ı (mobil)</li>
        <li>Yaklaşık konum (yalnızca “yakınımdaki hekimler” özelliğini siz açarsanız)</li>
    </ul>
    <h3>3.3 Ödeme verileri</h3>
    <ul>
        <li>Havale/EFT referans ve durum</li>
        <li>PayTR / iyzico işlem kimlikleri; App Store / Google Play (IAP varsa)</li>
        <li><strong>Tam kart numarası ve CVV Platform veritabanında saklanmaz.</strong></li>
    </ul>

    <h2 id="amac">4. İşleme amaçları</h2>
    <ul>
        <li>Randevu, bekleme listesi, eğitim başvurusu ve bildirimler</li>
        <li>Hesap, paket, abonelik, deneme, fatura bilgisi kaydı (manuel faturalama dâhil)</li>
        <li>Kimlik doğrulama ve güvenlik</li>
        <li>Hekim web sitesi ve domain kurulumu</li>
        <li>Müşteri desteği ve yasal yükümlülükler</li>
        <li>Ürün iyileştirme (toplu/istatistiksel)</li>
    </ul>

    <h2 id="hukuki">5. Hukuki dayanak</h2>
    <p>
        KVKK m.5/6: sözleşme ifası, kanuni yükümlülük, meşru menfaat, bir hakkın korunması ve
        gerektiğinde açık rıza. Özel nitelikli (sağlık) veriler yalnızca kanuni istisnalar veya açık rıza ile işlenir.
    </p>

    <h2 id="paylasim">6. Paylaşım ve aktarım</h2>
    <ul>
        <li><strong>Hekim/klinik:</strong> Randevu ve sizin paylaştığınız notlar ilgili hekim/klinik ile paylaşılır.</li>
        <li><strong>Hizmet sağlayıcılar:</strong> Hosting, e-posta, SMS, push, ödeme (PayTR, iyzico), domain/hosting, güvenlik, (varsa) analitik.</li>
        <li><strong>Yasal merciler:</strong> Mahkeme, savcılık, idari kurum talepleri.</li>
    </ul>
    <p>Veriler pazarlama amacıyla üçüncü kişilere satılmaz.</p>

    <h2 id="saklama">7. Saklama ve silme</h2>
    <p>
        Veriler, hizmet ilişkisi ve yasal saklama süreleri boyunca tutulur.
        Hesap kapatma veya silme taleplerinde yasal zorunluluklar saklı kalmak kaydıyla silme/anonimleştirme yapılır.
        Ödeme ve fatura kayıtları vergi/muhasebe süreleri kadar saklanabilir.
    </p>

    <h2 id="guvenlik">8. Güvenlik</h2>
    <p>
        HTTPS, erişim kontrolü, hash’li parolalar, oturum koruması, (mümkünse) 2FA ve loglama kullanılır.
        İnternet üzerinden risk sıfırlanamaz; şüpheli erişimde şifrenizi değiştirin ve bize bildirin.
    </p>

    <h2 id="cerez">9. Çerezler</h2>
    <ul>
        <li><strong>Zorunlu:</strong> Oturum, CSRF, güvenlik, dil/tercih</li>
        <li><strong>İşlevsel:</strong> Form ve arayüz tercihleri</li>
        <li><strong>Analitik/reklam (yapılandırılmışsa):</strong> GTM, GA4, Meta Pixel vb.</li>
    </ul>
    <p>Tarayıcı ayarlarından çerezleri silebilirsiniz; zorunlu çerezler olmadan bazı özellikler çalışmayabilir.</p>

    <h2 id="cocuk">10. Çocuklar</h2>
    <p>
        Platform genel olarak 18 yaş altı için tasarlanmamıştır.
        Velayet/veli onayı ile çocuk adına randevu alınması halinde veri, veli/hekim ilişkisi çerçevesinde işlenir.
    </p>

    <h2 id="mobil">11. Mobil uygulamalar</h2>
    <ul>
        <li>Oturum token’ı cihazda güvenli depolamada tutulabilir.</li>
        <li>Kamera/mikrofon: profil fotoğrafı veya online görüşme için istenir.</li>
        <li>Bildirimler: cihaz token’ı ile randevu/hatırlatma; cihaz ayarlarından kapatılabilir.</li>
        <li>Konum: yalnızca “yakındaki hekim” için isteğe bağlıdır.</li>
    </ul>

    <h2 id="web">12. Hekim web siteleri</h2>
    <p>
        Web sitesi paketinde hekimin seçtiği domain üzerinde yayınlanan içerik (hizmet, blog, galeri, iletişim)
        hekim tarafından yönetilir. Platform barındırma ve teknik altyapıyı sağlar.
        Hekim sitesini ziyaret eden üçüncü kişilerin verileri, o sitede sunulan hizmet ve hekimin
        kendi aydınlatma yükümlülükleri çerçevesinde de değerlendirilmelidir.
    </p>

    <h2 id="haklar">13. Haklarınız</h2>
    <p>
        KVKK m.11 haklarınız için
        <a href="mailto:{{ config('company.email', 'info@randevuajandam.com') }}">{{ config('company.email', 'info@randevuajandam.com') }}</a>
        adresine başvurun. Ayrıntı:
        <a href="{{ route('frontend.legal.kvkk') }}">KVKK Aydınlatma Metni</a>.
    </p>

    <h2 id="degisiklik">14. Değişiklikler</h2>
    <p>
        Bu politika güncellenebilir. Yayın tarihi (“Son güncelleme”) ile yürürlüğe girer.
    </p>

    <h2 id="iletisim">15. İletişim</h2>
    @include('frontend.layouts.partials.company-identity')
@endcomponent
@endsection
