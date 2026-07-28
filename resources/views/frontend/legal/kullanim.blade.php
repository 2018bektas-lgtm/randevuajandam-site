@extends('frontend.layouts.app')

@section('baslik', 'Kullanım Koşulları — Randevu Ajandam')
@section('meta_aciklama', 'Randevu Ajandam web ve mobil platform kullanım koşulları, tarafların hak ve yükümlülükleri.')

@section('icerik')
@php
    $sections = [
        'tanim' => '1. Tanımlar',
        'kabul' => '2. Kabul',
        'hizmet' => '3. Hizmetin niteliği',
        'hesap' => '4. Hesaplar ve güvenlik',
        'hasta' => '5. Hasta / danışan',
        'hekim' => '6. Hekim ve klinik',
        'randevu' => '7. Randevu',
        'egitim' => '8. Eğitim',
        'odeme' => '9. Paketler, deneme ve ödeme',
        'web' => '10. Web sitesi ve domain',
        'icerik' => '11. İçerik ve fikri mülkiyet',
        'yasak' => '12. Yasak kullanımlar',
        'sorumluluk' => '13. Sorumluluk sınırı',
        'fesih' => '14. Askıya alma ve fesih',
        'uyusmazlik' => '15. Uygulanacak hukuk',
        'degisiklik' => '16. Değişiklikler',
        'iletisim' => '17. İletişim',
    ];
@endphp

@component('frontend.legal._layout', [
    'baslik' => $baslik,
    'guncelleme' => $guncelleme,
    'ozet' => 'Hasta, hekim ve klinik olarak hak ve yükümlülükleriniz; randevu, paket, deneme, web sitesi ve kabul edilemez kullanımlar.',
    'sections' => $sections,
])
    <p>
        Bu Kullanım Koşulları (“Koşullar”), <strong>Randevu Ajandam</strong> markası altındaki web sitesi,
        paneller, hekim özel web siteleri ve mobil uygulamalara (“Platform”) erişiminizi ve kullanımınızı düzenler.
        Platform’u ziyaret ederek, hesap oluşturarak veya randevu/eğitim başvurusu yaparak Koşullar’ı kabul etmiş sayılırsınız.
    </p>
    <p>
        Benzer sektör platformlarında olduğu gibi (kullanım şartnamesi / gizlilik ayrımı),
        randevu aracı rolü ve mesleki sorumluluğun hekimde kaldığı açıkça belirtilir.
    </p>

    <h2 id="tanim">1. Tanımlar</h2>
    <ul>
        <li><strong>Platform / Hizmet:</strong> randevuajandam.com, bağlı paneller, hekim domain’li siteler ve mobil uygulamalar</li>
        <li><strong>Kullanıcı:</strong> Hasta/danışan, hekim, klinik yetkilisi veya personel</li>
        <li><strong>Hasta/Danışan:</strong> Randevu veya eğitim başvurusu yapan gerçek kişi</li>
        <li><strong>Hekim / Klinik:</strong> Hizmet ve randevu sunan profesyonel veya kurum</li>
        <li><strong>İçerik:</strong> Profil, blog, galeri, eğitim ilanı, yorum vb.</li>
        <li><strong>Paket:</strong> Bireysel veya klinik abonelik planı (isteğe bağlı web sitesi / domain)</li>
    </ul>

    <h2 id="kabul">2. Kabul</h2>
    <p>
        18 yaşından küçükler veli/vasi onayı olmadan hesap açamaz.
        Kurumsal hesaplarda işlemi yapan kişi, ilgili hekim/kliniği temsile yetkili olduğunu beyan eder.
        Koşullar’ı kabul etmiyorsanız Platform’u kullanmayınız.
    </p>

    <h2 id="hizmet">3. Hizmetin niteliği</h2>
    <ul>
        <li>Platform; randevu planlama, ajanda, bildirim, paket ve içerik yönetimi sunar.</li>
        <li><strong>Tıbbi teşhis, tedavi veya acil sağlık hizmeti değildir.</strong></li>
        <li>Hekim–danışan ilişkisinden doğan mesleki ve hukuki sorumluluk ilgili hekim/kliniğe aittir.</li>
        <li>Online görüşme (açık ise) teknik altyapı sağlar; tıbbi uygunluk hekime aittir.</li>
        <li>Acil durumda 112’yi arayın.</li>
    </ul>

    <h2 id="hesap">4. Hesaplar ve güvenlik</h2>
    <ul>
        <li>Doğru, güncel ve size ait bilgiler vermelisiniz.</li>
        <li>Şifre ve oturum güvenliği size aittir.</li>
        <li>SMS OTP ve (açık ise) 2FA güvenlik amaçlıdır.</li>
        <li>Hekim kaydında meslek/mezuniyet belgesi ve T.C. bilgisi talep edilebilir; onay süreci uygulanır.</li>
        <li>Personel hesapları klinik/hekim yetkilisi tarafından tanımlanır.</li>
    </ul>

    <h2 id="hasta">5. Hasta / danışan kuralları</h2>
    <ul>
        <li>Randevu talebi hekimin onay politikasına göre “beklemede” veya otomatik onaylı olabilir.</li>
        <li>Misafir randevuda telefon doğrulaması uygulanabilir.</li>
        <li>Yorumlar tamamlanan randevulara ilişkindir; küfür/hakaret yasaktır; yayın moderasyona tabidir.</li>
        <li>Sağlık notlarında yalnızca gerekli bilgileri paylaşın.</li>
    </ul>

    <h2 id="hekim">6. Hekim ve klinik kuralları</h2>
    <ul>
        <li>Mesleki yetki ve yasal gerekliliklere uygun hizmet sunmakla yükümlüsünüz.</li>
        <li>Profil, fiyat/bilgi, çalışma saatleri ve içerik doğruluğundan sorumlusunuz.</li>
        <li>Hasta verilerini KVKK ve meslek etiğine uygun işlemek sizin sorumluluğunuzdadır.</li>
        <li>Paket özellik limitleri aboneliğinize göre uygulanır.</li>
        <li>Yorum moderasyonu platform yönetimine aittir.</li>
    </ul>

    <h2 id="randevu">7. Randevu, iptal ve erteleme</h2>
    <ul>
        <li>Slot müsaitliği hekim takvimi, izin ve doluluk durumuna göre değişir.</li>
        <li>İptal/erteleme süreleri hekim ayarlarına ve Platform kurallarına tabidir.</li>
        <li>Hekim onayı gerektiren taleplerde randevu, onaylanana kadar kesinleşmiş sayılmaz.</li>
    </ul>

    <h2 id="egitim">8. Eğitim ve başvurular</h2>
    <p>
        Hekimlerin yayınladığı eğitim/seminer ilanları bilgilendirme amaçlıdır.
        Başvuru, kontenjan ve ücret hekim tarafından belirlenir; siteden otomatik tahsilat yapılmayabilir.
    </p>

    <h2 id="odeme">9. Paketler, deneme ve ödeme</h2>
    <ul>
        <li>Bireysel ve klinik paketleri, deneme günü (varsa) ve fiyatları sitede/panelde ilan edilir.</li>
        <li><strong>Ücretsiz deneme:</strong> Kart zorunlu olmayabilir; bitince tam ücretli paket gerekir; otomatik çekim yok.</li>
        <li>Ödeme: PayTR, iyzico ve/veya havale (yönetici ayarına göre).</li>
        <li>Ücretli ödemede fatura bilgileri ödeme adımında alınır; fatura satıcı tarafından manuel (ör. GİB) kesilebilir.</li>
        <li><strong>Paket değişiminde süre sıfırdan başlar; kalan gün devretmez.</strong></li>
        <li>Fiyatlara KDV dahildir.</li>
        <li>Mobil IAP: mağaza kuralları geçerlidir.</li>
    </ul>
    <p>
        Ayrıntı:
        <a href="{{ route('frontend.legal.mesafeli') }}">Mesafeli satış</a> ·
        <a href="{{ route('frontend.legal.iade') }}">İade / iptal</a>.
    </p>

    <h2 id="web">10. Web sitesi ve domain</h2>
    <ul>
        <li>Web sitesi paketinde domain seçimi (yeni veya mevcut) istenebilir.</li>
        <li>Kayıtlı domain varsa “bu domain ile devam?” onayı alınabilir.</li>
        <li>Hekim sitesi içeriği hekime aittir; yasal ve mesleki uygunluktan hekim sorumludur.</li>
        <li>Platform, hekim sitesinde “Randevu Ajandam” markasını zorunlu göstermeyebilir; teknik altyapı sağlar.</li>
    </ul>

    <h2 id="icerik">11. İçerik ve fikri mülkiyet</h2>
    <ul>
        <li>Platform yazılımı, marka, tasarım ve kod Randevu Ajandam’a aittir.</li>
        <li>Hekim/klinik içeriği ilgili kullanıcıya aittir; yayın için yasal haklara sahip olduğunu beyan eder.</li>
        <li>Platform, hizmet sunumu için içeriği barındırma ve gösterme lisansı alır.</li>
        <li>Hukuka aykırı, yanıltıcı veya üçüncü kişi haklarını ihlal eden içerik yüklenemez.</li>
    </ul>

    <h2 id="yasak">12. Yasak kullanımlar</h2>
    <ul>
        <li>Yetkisiz erişim, tersine mühendislik, güvenlik açıklarının istismarı</li>
        <li>Spam, sahte randevu, bot ile sistem yükleme</li>
        <li>Hasta verilerinin izinsiz paylaşımı veya satışı</li>
        <li>Hakaret, nefret, müstehcen veya yasa dışı içerik</li>
        <li>Başkasının kimliğine bürünme</li>
        <li>Platform’u toplu veri çekme / rakip istihbarat için kötüye kullanma</li>
    </ul>
    <p>İhlalde hesap askıya alınabilir, yasal mercilere başvurulabilir.</p>

    <h2 id="sorumluluk">13. Sorumluluk sınırı</h2>
    <ul>
        <li>Platform “olduğu gibi” sunulur; kesintisiz ve hatasız çalışma garanti edilmez.</li>
        <li>Hekim–danışan ilişkisinden doğan tıbbi, hukuki veya mali sonuçlardan Platform sorumlu değildir.</li>
        <li>Üçüncü taraf hizmetler (SMS, ödeme, domain, mağaza) kendi koşullarına tabidir.</li>
        <li>Kanunen izin verilen azami ölçüde dolaylı zarar sorumluluğu sınırlıdır.</li>
        <li>Zorunlu tüketici hakları saklıdır.</li>
    </ul>

    <h2 id="fesih">14. Askıya alma ve fesih</h2>
    <p>
        Koşullar’ın ihlali, yasal risk veya güvenlik gerekçesiyle hesabınızı askıya alabilir veya sonlandırabiliriz.
        Kullanıcı hesap kapatma talebini destek kanalından iletebilir; yasal saklama yükümlülükleri saklıdır.
    </p>

    <h2 id="uyusmazlik">15. Uygulanacak hukuk ve yetki</h2>
    <p>
        Koşullar Türkiye Cumhuriyeti hukukuna tabidir.
        Tüketici hakem heyetleri ve zorunlu merciler saklıdır.
        Ticari uyuşmazlıklarda satıcının bulunduğu yer (Ankara) mahkemeleri ve icra daireleri yetkili kılınabilir.
    </p>

    <h2 id="degisiklik">16. Değişiklikler</h2>
    <p>
        Koşullar güncellenebilir. Güncel metin sitede yayınlandığı anda yürürlüğe girer.
        Kullanıma devam, güncel Koşullar’ın kabulü sayılır.
    </p>

    <h2 id="iletisim">17. İletişim</h2>
    @include('frontend.layouts.partials.company-identity')
    <p>
        <a href="{{ route('frontend.legal.gizlilik') }}">Gizlilik</a> ·
        <a href="{{ route('frontend.legal.kvkk') }}">KVKK</a> ·
        <a href="{{ route('frontend.legal.mesafeli') }}">Mesafeli satış</a>
    </p>
@endcomponent
@endsection
