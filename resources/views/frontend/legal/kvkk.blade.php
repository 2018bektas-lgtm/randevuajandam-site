@extends('frontend.layouts.app')

@section('baslik', 'KVKK Aydınlatma Metni — Randevu Ajandam')
@section('meta_aciklama', '6698 sayılı KVKK uyarınca Randevu Ajandam platformunda kişisel verilerin işlenmesine ilişkin aydınlatma metni.')

@section('icerik')
@php
    $sections = [
        'amac' => '1. Amaç ve kapsam',
        'sorumlu' => '2. Veri sorumlusu',
        'taraflar' => '3. Taraflar ve roller',
        'kategoriler' => '4. İşlenen veri kategorileri',
        'amaclar' => '5. İşleme amaçları',
        'hukuki' => '6. Hukuki sebepler',
        'yontem' => '7. Toplama yöntemi',
        'aktarim' => '8. Aktarım',
        'saklama' => '9. Saklama süreleri',
        'guvenlik' => '10. Güvenlik tedbirleri',
        'haklar' => '11. İlgili kişi hakları (m.11)',
        'basvuru' => '12. Başvuru usulü',
        'cerez' => '13. Çerezler ve izleme',
        'guncelleme' => '14. Güncellemeler',
    ];
@endphp

@component('frontend.legal._layout', [
    'baslik' => $baslik,
    'guncelleme' => $guncelleme,
    'ozet' => '6698 sayılı Kişisel Verilerin Korunması Kanunu’nun 10. maddesi uyarınca; kişisel verilerinizin kim tarafından, hangi amaçla, hangi hukuki sebeple işlendiği ve haklarınız hakkında bilgilendirilmeniz amacıyla hazırlanmıştır.',
    'sections' => $sections,
])
    <p>
        İşbu Aydınlatma Metni, <strong>Randevu Ajandam</strong> markası altında sunulan web sitesi
        (<a href="{{ config('company.web', 'https://randevuajandam.com') }}">{{ config('company.web', 'https://randevuajandam.com') }}</a>),
        hekim/klinik panelleri, personel panelleri, hekim özel web siteleri (domain’li) ve mobil uygulamalar
        (“Platform”) kapsamında kişisel verilerin işlenmesine ilişkindir.
    </p>

    <h2 id="amac">1. Amaç ve kapsam</h2>
    <p>
        6698 sayılı Kişisel Verilerin Korunması Kanunu (“KVKK”) ve ilgili ikincil mevzuat uyarınca,
        veri sorumlusu sıfatıyla kişisel verilerinizi işlerken sizi bilgilendirmekle yükümlüyüz.
        Bu metin; hasta/danışan, hekim, klinik yetkilisi, personel ve ziyaretçi (misafir randevu) sıfatıyla
        Platform’u kullanan gerçek kişileri kapsar.
    </p>

    <h2 id="sorumlu">2. Veri sorumlusu</h2>
    <p>KVKK kapsamında veri sorumlusu:</p>
    @include('frontend.layouts.partials.company-identity')
    <p>
        KVKK başvuruları:
        <a href="mailto:{{ config('company.email', 'info@randevuajandam.com') }}">{{ config('company.email', 'info@randevuajandam.com') }}</a>
        · <a href="{{ route('frontend.legal.iletisim') }}">İletişim</a>
    </p>

    <h2 id="taraflar">3. Taraflar ve roller</h2>
    <p>
        Platform, hekim/klinik ile hasta/danışan arasında randevu ve operasyonel iletişimi kolaylaştıran bir aracıdır.
        <strong>Tıbbi teşhis ve tedavi hizmeti Platform tarafından sunulmaz.</strong>
    </p>
    <ul>
        <li>
            <strong>Platform (Randevu Ajandam):</strong> Hesap, randevu altyapısı, paket/üyelik, fatura bilgisi kaydı,
            domain/web sitesi kurulumu, bildirim altyapısı, güvenlik logları ve platform işletimine ilişkin veriler
            bakımından veri sorumlusu.
        </li>
        <li>
            <strong>Hekim / klinik:</strong> Muayene/hizmet sürecinde oluşturdukları klinik not, teşhis/tedaviye ilişkin
            açıklamalar ve kendi hasta kayıtları bakımından kendi faaliyetleri ölçüsünde ayrıca veri sorumlusu
            olabilir; bu durumda aydınlatma yükümlülüğü hekim/klinik tarafında da doğar.
        </li>
        <li>
            <strong>Sağlık verisi:</strong> Randevu notu veya form alanlarında paylaşılan sağlık bilgileri özel nitelikli
            kişisel veri sayılabilir; yalnızca hizmetin ifası ve kanuni istisnalar/açık rıza çerçevesinde işlenir.
        </li>
    </ul>

    <h2 id="kategoriler">4. İşlenen veri kategorileri</h2>
    <ul>
        <li><strong>Kimlik:</strong> Ad, soyad, unvan, T.C. kimlik no (hekim kaydı / fatura bireysel)</li>
        <li><strong>İletişim:</strong> Telefon, e-posta, adres, il/ilçe, posta kodu</li>
        <li><strong>Müşteri işlem:</strong> Randevu tarih/saat, hizmet, randevu durumu, iptal/erteleme, eğitim başvuruları, bekleme listesi</li>
        <li><strong>Mesleki:</strong> Branş, biyografi, diploma/mezuniyet, e-Devlet barkodlu YÖK mezun belgesi ve doğrulama sonuçları</li>
        <li><strong>Finans / abonelik:</strong> Paket, ödeme periyodu, tutar, ödeme yöntemi (PayTR / iyzico / havale), havale referansı, üyelik başlangıç-bitiş, deneme durumu; <strong>fatura tipi, unvan, TC/VKN, vergi dairesi, fatura adresi</strong> (ödeme adımında)</li>
        <li><strong>Web sitesi:</strong> Domain, site ayarları, tema tercihi, yayınlanan içerik (blog, hizmet, galeri)</li>
        <li><strong>İşlem güvenliği:</strong> IP, oturum, log, cihaz, reCAPTCHA, SMS OTP, belge erişim logları</li>
        <li><strong>Pazarlama (rıza varsa):</strong> Kampanya bildirim tercihleri</li>
        <li><strong>Özel nitelikli (sınırlı):</strong> Kullanıcının girdiği sağlık notları</li>
    </ul>
    <p><strong>Tam kart numarası ve CVV Platform veritabanında saklanmaz</strong>; kartlı ödemeler ödeme kuruluşu altyapısında işlenir.</p>

    <h2 id="amaclar">5. İşleme amaçları</h2>
    <ul>
        <li>Randevu oluşturma, onay, iptal, erteleme ve hatırlatma (SMS/e-posta/push)</li>
        <li>Hesap, kimlik doğrulama (SMS OTP, 2FA), güvenli oturum</li>
        <li>Hekim kaydında kimlik ve mezuniyet teyidi</li>
        <li>Paket/üyelik, deneme, yenileme, iptal ve fatura bilgisi kaydı (manuel faturalama için)</li>
        <li>Özel web sitesi / domain kurulumu ve içerik yayını</li>
        <li>Eğitim/seminer başvurusu ve iletişim</li>
        <li>Destek, güvenlik, dolandırıcılık önleme</li>
        <li>Yasal yükümlülükler (muhasebe, vergi, yetkili merciler)</li>
        <li>Platform performans analizi (mümkün olduğunca anonim/istatistiksel)</li>
    </ul>

    <h2 id="hukuki">6. Hukuki sebepler (KVKK m.5 ve m.6)</h2>
    <ul>
        <li><strong>Sözleşmenin kurulması/ifası:</strong> Randevu, üyelik, paket, domain, eğitim başvurusu</li>
        <li><strong>Kanuni yükümlülük:</strong> Muhasebe, vergi, regülasyon</li>
        <li><strong>Meşru menfaat:</strong> Güvenlik, hizmet iyileştirme, hile önleme</li>
        <li><strong>Açık rıza:</strong> Zorunlu olmayan pazarlama; gerekli hallerde özel nitelikli veri</li>
        <li><strong>Bir hakkın tesisi/korunması:</strong> Uyuşmazlık süreçleri</li>
    </ul>

    <h2 id="yontem">7. Toplama yöntemi</h2>
    <p>
        Veriler; web ve mobil formlar, misafir randevu sihirbazı, hekim/klinik panelleri, ödeme ekranı (fatura bilgileri),
        SMS doğrulama, çerezler, sunucu logları, ödeme kuruluşu bildirimleri ve destek kanalları aracılığıyla toplanır.
    </p>

    <h2 id="aktarim">8. Aktarım</h2>
    <ul>
        <li>Hekim / klinik / yetkili personel (randevu ve hizmet ifası)</li>
        <li>Barındırma (hosting), e-posta, SMS, push bildirim sağlayıcıları</li>
        <li>Ödeme kuruluşları (PayTR, iyzico), banka (havale)</li>
        <li>Domain/hosting iş ortakları (web sitesi paketi; örn. Hostinger entegrasyonu)</li>
        <li>App Store / Google Play (mobil IAP varsa)</li>
        <li>Güvenlik (reCAPTCHA vb.) ve analitik (yapılandırılmışsa)</li>
        <li>Yetkili kamu kurumları (yasal zorunluluk)</li>
    </ul>
    <p><strong>Verileriniz satılmaz, kiralanmaz veya izinsiz pazarlama listelerine eklenmez.</strong></p>
    <p>Yurt dışına aktarım olursa KVKK yurt dışı aktarım hükümleri gözetilir.</p>

    <h2 id="saklama">9. Saklama süreleri</h2>
    <ul>
        <li>Hesap: üyelik + yasal zamanaşımı</li>
        <li>Randevu / işlem: hizmet ilişkisi + yasal süreler</li>
        <li>Ödeme ve fatura kayıtları: vergi/muhasebe saklama süreleri</li>
        <li>Log / güvenlik: makul süre</li>
        <li>Pazarlama rızası: geri alına veya süre dolana kadar</li>
    </ul>

    <h2 id="guvenlik">10. Güvenlik tedbirleri</h2>
    <p>
        HTTPS, erişim yetkilendirme, hash’li parolalar, oturum güvenliği, rate limit,
        (mümkünse) 2FA, yedekleme ve erişim logları uygulanır. Mutlak güvenlik garanti edilemez.
    </p>

    <h2 id="haklar">11. İlgili kişi hakları (KVKK m.11)</h2>
    <ul>
        <li>İşlenip işlenmediğini öğrenme ve bilgi talep etme</li>
        <li>Amacı ve amaca uygun kullanımı öğrenme</li>
        <li>Aktarılan üçüncü kişileri bilme</li>
        <li>Eksik/yanlış işlenmişse düzeltme</li>
        <li>KVKK m.7 kapsamında silme/yok etme</li>
        <li>Düzeltme/silmenin aktarıldığı üçüncü kişilere bildirilmesini isteme</li>
        <li>Münhasıran otomatik analiz nedeniyle aleyhe sonuca itiraz</li>
        <li>Kanuna aykırı işleme nedeniyle zararın giderilmesini talep</li>
    </ul>

    <h2 id="basvuru">12. Başvuru usulü</h2>
    <p>
        Kimliğinizi tevsik eden talebinizi
        <a href="mailto:{{ config('company.email', 'info@randevuajandam.com') }}">{{ config('company.email', 'info@randevuajandam.com') }}</a>
        adresine iletebilirsiniz. Başvurular en geç 30 gün içinde sonuçlandırılır.
        Kurul tarifesi saklıdır. Memnun kalınmazsa Kişisel Verileri Koruma Kurulu’na şikâyet hakkı vardır.
    </p>

    <h2 id="cerez">13. Çerezler ve izleme</h2>
    <p>
        Oturum, güvenlik ve (yapılandırılmışsa) analitik/reklam çerezleri kullanılabilir.
        Ayrıntı: <a href="{{ route('frontend.legal.gizlilik') }}">Gizlilik Politikası</a>.
    </p>

    <h2 id="guncelleme">14. Güncellemeler</h2>
    <p>
        Bu metin güncellenebilir. Güncel sürüm sitede yayınlandığı tarihte yürürlüğe girer.
        Son güncelleme tarihi sayfa başındadır.
    </p>
@endcomponent
@endsection
