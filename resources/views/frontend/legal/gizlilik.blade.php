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
        'haklar' => '12. Haklarınız',
        'degisiklik' => '13. Değişiklikler',
        'iletisim' => '14. İletişim',
    ];
@endphp

@component('frontend.legal._layout', [
    'baslik' => $baslik,
    'guncelleme' => $guncelleme,
    'ozet' => 'Web, panel ve mobil uygulamalarımızda kişisel verilerinizi nasıl topladığımızı, neden kullandığımızı ve nasıl koruduğumuzu açıklar.',
    'sections' => $sections,
])
    <p>
        Bu Gizlilik Politikası, <strong>Randevu Ajandam</strong>’ın
        <a href="https://randevuajandam.com">randevuajandam.com</a> sitesi, hekim/klinik/personel panelleri
        ve hasta–hekim mobil uygulamaları (“Hizmetler”) için geçerlidir.
        Hizmetleri kullanarak bu politikada açıklanan uygulamaları kabul etmiş sayılırsınız.
    </p>

    <h2 id="kapsam">1. Kapsam</h2>
    <p>
        Politika; ziyaretçi, hasta/danışan, hekim, klinik yetkilisi ve personel kullanıcılarını kapsar.
        Hekim–danışan arasındaki tıbbi sürecin içeriği öncelikle ilgili hekim/kliniğin sorumluluğundadır;
        Platform altyapı ve operasyonel verileri yönetir. Detay için
        <a href="{{ route('frontend.legal.kvkk') }}">KVKK Aydınlatma Metni</a>.
    </p>

    <h2 id="sorumlu">2. Veri sorumlusu</h2>
    <p>
        Randevu Ajandam platform işletmecisi<br>
        E-posta: <a href="mailto:info@randevuajandam.com">info@randevuajandam.com</a>
    </p>

    <h2 id="toplanan">3. Toplanan veriler</h2>
    <h3>3.1 Doğrudan verdiğiniz veriler</h3>
    <ul>
        <li>Ad, soyad, e-posta, telefon (05… formatında cep)</li>
        <li>Hekim profili: unvan, branş, biyografi, adres, çalışma saatleri, galeri, blog, eğitim ilanları</li>
        <li>Randevu ve form alanları (not, eğitim başvuru cevapları)</li>
        <li>Destek yazışmaları</li>
    </ul>
    <h3>3.2 Otomatik toplanan veriler</h3>
    <ul>
        <li>IP adresi, tarayıcı/cihaz bilgisi, oturum ve güvenlik logları</li>
        <li>SMS doğrulama ve reCAPTCHA sonuçları (yapılandırılmışsa)</li>
        <li>Push bildirim cihaz token’ı (mobil)</li>
        <li>Yaklaşık konum (yalnızca “yakınımdaki hekimler” özelliğini siz açarsanız)</li>
    </ul>
    <h3>3.3 Ödeme verileri</h3>
    <ul>
        <li>Havale/EFT referans ve durum bilgisi</li>
        <li>Iyzico / App Store / Google Play işlem kimlikleri</li>
        <li><strong>Tam kart numarası Platform veritabanında saklanmaz.</strong></li>
    </ul>

    <h2 id="amac">4. İşleme amaçları</h2>
    <ul>
        <li>Randevu, bekleme listesi, eğitim başvurusu ve bildirimler</li>
        <li>Hesap, paket, abonelik ve faturalama</li>
        <li>Kimlik doğrulama ve güvenlik (OTP, 2FA, rate limit)</li>
        <li>Müşteri desteği</li>
        <li>Yasal yükümlülükler ve uyuşmazlıkların çözümü</li>
        <li>Ürün iyileştirme (toplu/istatistiksel kullanım)</li>
    </ul>

    <h2 id="hukuki">5. Hukuki dayanak</h2>
    <p>
        KVKK m.5/6: sözleşme ifası, kanuni yükümlülük, meşru menfaat, bir hakkın korunması ve
        gerektiğinde açık rıza. Özel nitelikli (sağlık) veriler yalnızca kanuni istisnalar veya açık rıza ile işlenir.
    </p>

    <h2 id="paylasim">6. Paylaşım ve aktarım</h2>
    <ul>
        <li><strong>Hekim/klinik:</strong> Randevu ve sizin paylaştığınız notlar ilgili hekim/klinik ile paylaşılır.</li>
        <li><strong>Hizmet sağlayıcılar:</strong> Hosting, e-posta, SMS, push, ödeme, güvenlik, (varsa) analitik.</li>
        <li><strong>Yasal merciler:</strong> Mahkeme, savcılık, idari kurum talepleri.</li>
    </ul>
    <p>Veriler pazarlama amacıyla üçüncü kişilere satılmaz.</p>

    <h2 id="saklama">7. Saklama ve silme</h2>
    <p>
        Veriler, hizmet ilişkisinin devamı ve yasal saklama süreleri boyunca tutulur.
        Hesap kapatma veya silme taleplerinde; yasal zorunluluklar saklı kalmak kaydıyla silme/anonimleştirme yapılır.
        Yedeklerdeki kopyalar teknik döngü sonunda temizlenir.
    </p>

    <h2 id="guvenlik">8. Güvenlik</h2>
    <p>
        HTTPS, erişim kontrolü, hash’li parolalar, oturum koruması, (mümkünse) 2FA ve loglama kullanılır.
        İnternet üzerinden iletimde risk sıfırlanamaz; şüpheli erişimde derhal şifrenizi değiştirin ve bize bildirin.
    </p>

    <h2 id="cerez">9. Çerezler</h2>
    <ul>
        <li><strong>Zorunlu:</strong> Oturum, CSRF, güvenlik, dil/tercih</li>
        <li><strong>İşlevsel:</strong> Form ve arayüz tercihleri</li>
        <li><strong>Analitik/reklam (yapılandırılmışsa):</strong> GTM, GA4, Meta Pixel vb. — mümkün olduğunca geciktirilmiş yükleme</li>
    </ul>
    <p>Tarayıcı ayarlarından çerezleri silebilir veya engelleyebilirsiniz; zorunlu çerezler olmadan bazı özellikler çalışmayabilir.</p>

    <h2 id="cocuk">10. Çocuklar</h2>
    <p>
        Platform genel olarak 18 yaş altı için tasarlanmamıştır.
        Velayet/veli onayı ile çocuk adına randevu alınması halinde veri, veli/hekim ilişkisi çerçevesinde işlenir.
    </p>

    <h2 id="mobil">11. Mobil uygulamalar</h2>
    <ul>
        <li>Oturum token’ı cihazda güvenli depolamada tutulabilir.</li>
        <li>Kamera/mikrofon: profil fotoğrafı veya online görüşme için istenir.</li>
        <li>Bildirimler: cihaz token’ı ile randevu/hatırlatma gönderilir; cihaz ayarlarından kapatılabilir.</li>
        <li>Konum: yalnızca “yakındaki hekim” özelliği için isteğe bağlıdır.</li>
    </ul>

    <h2 id="haklar">12. Haklarınız</h2>
    <p>
        KVKK m.11 haklarınız için
        <a href="mailto:info@randevuajandam.com">info@randevuajandam.com</a> adresine başvurun.
        Ayrıntılı liste <a href="{{ route('frontend.legal.kvkk') }}">KVKK Aydınlatma Metni</a>’ndedir.
    </p>

    <h2 id="degisiklik">13. Değişiklikler</h2>
    <p>
        Bu politika güncellenebilir. Yayın tarihi (“Son güncelleme”) ile yürürlüğe girer.
        Önemli değişikliklerde sitede duyuru yapılabilir.
    </p>

    <h2 id="iletisim">14. İletişim</h2>
    <p>
        <a href="mailto:info@randevuajandam.com">info@randevuajandam.com</a><br>
        WhatsApp: +90 531 991 24 27
    </p>
@endcomponent
@endsection
