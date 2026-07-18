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
        'hasta' => '5. Hasta / danışan kuralları',
        'hekim' => '6. Hekim ve klinik kuralları',
        'randevu' => '7. Randevu, iptal ve erteleme',
        'egitim' => '8. Eğitim ve başvurular',
        'odeme' => '9. Paketler ve ödeme',
        'icerik' => '10. İçerik ve fikri mülkiyet',
        'yasak' => '11. Yasak kullanımlar',
        'sorumluluk' => '12. Sorumluluk sınırı',
        'fesih' => '13. Askıya alma ve fesih',
        'uyusmazlik' => '14. Uygulanacak hukuk',
        'degisiklik' => '15. Değişiklikler',
        'iletisim' => '16. İletişim',
    ];
@endphp

@component('frontend.legal._layout', [
    'baslik' => $baslik,
    'guncelleme' => $guncelleme,
    'ozet' => 'Platformu kullanarak hasta, hekim ve klinik olarak hak ve yükümlülüklerinizi; randevu, ödeme ve kabul edilemez kullanımları düzenler.',
    'sections' => $sections,
])
    <p>
        Bu Kullanım Koşulları (“Koşullar”), <strong>Randevu Ajandam</strong> markası altındaki web sitesi,
        paneller ve mobil uygulamalara (“Platform”) erişiminizi ve kullanımınızı düzenler.
        Platform’u ziyaret ederek, hesap oluşturarak veya randevu/eğitim başvurusu yaparak Koşullar’ı kabul etmiş sayılırsınız.
    </p>

    <h2 id="tanim">1. Tanımlar</h2>
    <ul>
        <li><strong>Platform / Hizmet:</strong> randevuajandam.com ve bağlı paneller ile mobil uygulamalar</li>
        <li><strong>Kullanıcı:</strong> Hasta/danışan, hekim, klinik yetkilisi veya personel</li>
        <li><strong>Hasta/Danışan:</strong> Randevu veya eğitim başvurusu yapan gerçek kişi</li>
        <li><strong>Hekim / Klinik:</strong> Hizmet ve randevu sunan profesyonel veya kurum</li>
        <li><strong>İçerik:</strong> Profil, blog, galeri, eğitim ilanı, yorum vb. her türlü metin/görsel</li>
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
        <li>Online görüşme (açık ise) teknik altyapı sağlar; görüşmenin tıbbi uygunluğu hekime aittir.</li>
    </ul>

    <h2 id="hesap">4. Hesaplar ve güvenlik</h2>
    <ul>
        <li>Doğru, güncel ve size ait bilgiler vermelisiniz.</li>
        <li>Şifre ve oturum güvenliği size aittir; paylaşım yasaktır.</li>
        <li>SMS OTP ve (açık ise) 2FA güvenlik amaçlıdır; kodları üçüncü kişilerle paylaşmayın.</li>
        <li>Personel hesapları klinik/hekim yetkilisi tarafından tanımlanır; yetki ihlalinden hesap sahibi sorumludur.</li>
        <li>Şüpheli erişimde şifreyi değiştirin ve bize bildirin.</li>
    </ul>

    <h2 id="hasta">5. Hasta / danışan kuralları</h2>
    <ul>
        <li>Randevu talebi, hekimin onay politikasına göre “beklemede” veya otomatik onaylı olabilir.</li>
        <li>Misafir randevuda telefon doğrulaması ve zorunlu alanlar (e-posta vb.) uygulanabilir.</li>
        <li>Yorumlar tamamlanan randevulara ilişkindir; küfür/hakaret yasaktır; yayın platform moderasyonuna tabidir.</li>
        <li>Sağlık notlarında yalnızca gerekli bilgileri paylaşın; acil durumda 112’yi arayın.</li>
    </ul>

    <h2 id="hekim">6. Hekim ve klinik kuralları</h2>
    <ul>
        <li>Mesleki yetki ve yasal gerekliliklere uygun hizmet sunmakla yükümlüsünüz.</li>
        <li>Profil, fiyat/bilgi, çalışma saatleri ve içerik doğruluğundan sorumlusunuz.</li>
        <li>Hasta verilerini KVKK ve meslek etiğine uygun işlemek sizin sorumluluğunuzdadır.</li>
        <li>Paket özellik limitleri (randevu, blog, eğitim vb.) aboneliğinize göre uygulanır.</li>
        <li>Yorum moderasyonu platform yönetimine aittir; hekim paneli üzerinden seçici onay yapılamaz.</li>
    </ul>

    <h2 id="randevu">7. Randevu, iptal ve erteleme</h2>
    <ul>
        <li>Slot müsaitliği hekim takvimi, izin ve doluluk durumuna göre değişir.</li>
        <li>İptal/erteleme süreleri hekim ayarlarına ve Platform kurallarına tabidir.</li>
        <li>Hekim onayı gerektiren taleplerde randevu, onaylanana kadar kesinleşmiş sayılmaz.</li>
        <li>Gelmeme / geç iptal politikası hekim tarafından ayrıca duyurulabilir.</li>
    </ul>

    <h2 id="egitim">8. Eğitim ve başvurular</h2>
    <p>
        Hekimlerin yayınladığı eğitim/seminer ilanları bilgilendirme amaçlıdır.
        Başvuru, kontenjan ve ücret hekim tarafından belirlenir; siteden otomatik tahsilat yapılmayabilir.
        Ödeme notları ve başvuru formları ilgili eğitim sayfasında yer alır.
    </p>

    <h2 id="odeme">9. Paketler ve ödeme</h2>
    <ul>
        <li>Bireysel ve klinik paketleri, özellik seti ve fiyatları sitede veya panelde ilan edilir.</li>
        <li>Havale/EFT: ödeme kanıtı sonrası manuel onay ile aktivasyon yapılabilir.</li>
        <li>Online ödeme (Iyzico vb.): ilgili ödeme kuruluşunun koşulları geçerlidir.</li>
        <li>Mobil abonelik (IAP): App Store / Google Play abonelik ve iptal kuralları geçerlidir.</li>
        <li>İade talepleri yasal haklar, mağaza politikası ve yazılı destek kaydı ile değerlendirilir.</li>
        <li>Vergi fatura bilgisi talep edildiğinde doğru bilgi vermek kullanıcıya aittir.</li>
    </ul>

    <h2 id="icerik">10. İçerik ve fikri mülkiyet</h2>
    <ul>
        <li>Platform yazılımı, marka, tasarım ve kod Randevu Ajandam’a aittir.</li>
        <li>Hekim/klinik içeriği (biyografi, blog, görseller) ilgili kullanıcıya aittir; yayın için yasal haklara sahip olduğunu beyan eder.</li>
        <li>Platform, hizmetin sunumu için içeriği barındırma ve gösterme lisansı alır; sahipliği devralmaz.</li>
        <li>Kullanıcılar, hukuka aykırı, yanıltıcı veya üçüncü kişi haklarını ihlal eden içerik yükleyemez.</li>
    </ul>

    <h2 id="yasak">11. Yasak kullanımlar</h2>
    <ul>
        <li>Yetkisiz erişim, tersine mühendislik, güvenlik açıklarının istismarı</li>
        <li>Spam, sahte randevu, bot ile sistem yükleme</li>
        <li>Hasta verilerinin izinsiz paylaşımı veya satışı</li>
        <li>Hakaret, nefret, müstehcen veya yasa dışı içerik</li>
        <li>Başkasının kimliğine bürünme</li>
        <li>Platform’u rakip istihbarat veya toplu veri çekme için kötüye kullanma</li>
    </ul>
    <p>İhlalde hesap askıya alınabilir, yasal mercilere başvurulabilir ve zararlar talep edilebilir.</p>

    <h2 id="sorumluluk">12. Sorumluluk sınırı</h2>
    <ul>
        <li>Platform “olduğu gibi” sunulur; kesintisiz ve hatasız çalışma garanti edilmez.</li>
        <li>Hekim–danışan ilişkisinden doğan tıbbi, hukuki veya mali sonuçlardan Platform sorumlu değildir.</li>
        <li>Üçüncü taraf hizmetler (SMS, ödeme, mağaza, harita) kendi koşullarına tabidir.</li>
        <li>Kanunen izin verilen azami ölçüde dolaylı, arızi veya kâr kaybı zararlarından sorumluluk sınırlıdır.</li>
        <li>Zorunlu tüketici hakları saklıdır.</li>
    </ul>

    <h2 id="fesih">13. Askıya alma ve fesih</h2>
    <p>
        Koşullar’ın ihlali, yasal risk veya güvenlik gerekçesiyle hesabınızı askıya alabilir veya sonlandırabiliriz.
        Kullanıcı, hesap kapatma talebini destek kanalından iletebilir; yasal saklama yükümlülükleri saklıdır.
    </p>

    <h2 id="uyusmazlik">14. Uygulanacak hukuk ve yetki</h2>
    <p>
        Koşullar Türkiye Cumhuriyeti hukukuna tabidir.
        Uyuşmazlıklarda (tüketici hakem heyetleri ve zorunlu merciler saklı kalmak kaydıyla)
        İstanbul mahkemeleri ve icra daireleri yetkilidir — ticari tercihinize göre güncelleyebilirsiniz.
    </p>

    <h2 id="degisiklik">15. Değişiklikler</h2>
    <p>
        Koşullar güncellenebilir. Güncel metin sitede yayınlandığı anda yürürlüğe girer.
        Önemli değişikliklerde makul duyuru yapılmaya çalışılır. Kullanıma devam, güncel Koşullar’ın kabulü sayılır.
    </p>

    <h2 id="iletisim">16. İletişim</h2>
    <p>
        <a href="mailto:info@randevuajandam.com">info@randevuajandam.com</a><br>
        WhatsApp destek: +90 531 991 24 27<br>
        Ayrıca:
        <a href="{{ route('frontend.legal.gizlilik') }}">Gizlilik Politikası</a> ·
        <a href="{{ route('frontend.legal.kvkk') }}">KVKK Aydınlatma Metni</a>
    </p>
@endcomponent
@endsection
