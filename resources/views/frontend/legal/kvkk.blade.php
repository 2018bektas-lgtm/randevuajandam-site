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
        (<a href="https://randevuajandam.com">randevuajandam.com</a>), hekim/klinik panelleri, personel panelleri
        ve mobil uygulamalar (“Platform”) kapsamında kişisel verilerin işlenmesine ilişkindir.
    </p>

    <h2 id="amac">1. Amaç ve kapsam</h2>
    <p>
        6698 sayılı Kişisel Verilerin Korunması Kanunu (“KVKK”) ve ilgili ikincil mevzuat uyarınca,
        veri sorumlusu sıfatıyla kişisel verilerinizi işlerken sizi bilgilendirmekle yükümlüyüz.
        Bu metin; hasta/danışan, hekim, klinik yetkilisi, personel ve ziyaretçi (misafir randevu) sıfatıyla
        Platform’u kullanan gerçek kişileri kapsar.
    </p>

    <h2 id="sorumlu">2. Veri sorumlusu</h2>
    <p>
        <strong>Unvan:</strong> Randevu Ajandam platform işletmecisi<br>
        <strong>E-posta:</strong> <a href="mailto:info@randevuajandam.com">info@randevuajandam.com</a><br>
        <strong>Web:</strong> <a href="https://randevuajandam.com">https://randevuajandam.com</a><br>
        <strong>Destek (WhatsApp):</strong> +90 531 991 24 27
    </p>
    <p class="text-xs text-slate-500">
        Resmî unvan, adres, vergi/MERSİS ve VERBİS kayıt bilgileri ticari belgelerinizle uyumlu olacak şekilde
        bu bölüme eklenmelidir.
    </p>

    <h2 id="taraflar">3. Taraflar ve roller</h2>
    <p>
        Platform, hekim/klinik ile hasta/danışan arasında randevu ve operasyonel iletişimi kolaylaştıran bir aracıdır.
    </p>
    <ul>
        <li>
            <strong>Platform (Randevu Ajandam):</strong> Hesap, randevu altyapısı, paket/üyelik, bildirim altyapısı,
            güvenlik logları ve platform işletimine ilişkin veriler bakımından veri sorumlusu.
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
        <li><strong>Kimlik:</strong> Ad, soyad, unvan</li>
        <li><strong>İletişim:</strong> Telefon, e-posta, adres (varsa), il/ilçe</li>
        <li><strong>Müşteri işlem:</strong> Randevu tarih/saat, hizmet, randevu durumu, iptal/erteleme kayıtları, eğitim başvuruları, bekleme listesi</li>
        <li><strong>Mesleki:</strong> Branş, biyografi, diploma/mezuniyet bilgisi, e-Devlet barkodlu YÖK mezun belgesi ve belge doğrulama sonuçları (hekim/sağlık profesyoneli kaydı)</li>
        <li><strong>Finans:</strong> Paket/üyelik, ödeme durumu, havale referansı, fatura takip bayrağı; kart verisi Platform’da saklanmaz (PayTR / ödeme kuruluşu)</li>
        <li><strong>İşlem güvenliği:</strong> IP, oturum, log, cihaz bilgisi, reCAPTCHA/güvenlik skorları, SMS doğrulama kayıtları, belge erişim logları</li>
        <li><strong>Pazarlama (rıza varsa):</strong> Kampanya bildirim tercihleri</li>
        <li><strong>Özel nitelikli (sınırlı):</strong> Randevu/eğitim formunda kullanıcı tarafından girilen sağlık notları</li>
    </ul>

    <h2 id="amaclar">5. İşleme amaçları</h2>
    <ul>
        <li>Randevu oluşturma, onay, iptal, erteleme ve hatırlatma</li>
        <li>Hesap oluşturma, kimlik doğrulama (SMS OTP, e-posta, 2FA), güvenli oturum</li>
        <li>Hekim/sağlık profesyoneli kaydında kimlik ve mezuniyet teyidi: e-Devlet barkodlu belge doğrulama, TC/ad soyad eşleştirmesi, meslek belgesi incelemesi</li>
        <li>Hekim/klinik paket ve abonelik yönetimi</li>
        <li>Eğitim/seminer başvurusu ve iletişim</li>
        <li>Destek taleplerinin yanıtlanması</li>
        <li>Dolandırıcılık, kötüye kullanım ve güvenlik olaylarının önlenmesi</li>
        <li>Yasal yükümlülüklerin yerine getirilmesi (saklama, yetkili mercilere bilgi)</li>
        <li>Platform performans ve kullanım analizi (mümkün olduğunca anonim/istatistiksel)</li>
    </ul>

    <h2 id="hukuki">6. Hukuki sebepler (KVKK m.5 ve m.6)</h2>
    <ul>
        <li><strong>Sözleşmenin kurulması/ifası:</strong> Randevu, üyelik, paket, eğitim başvurusu</li>
        <li><strong>Kanuni yükümlülük:</strong> Muhasebe, vergi, regülasyon</li>
        <li><strong>Meşru menfaat:</strong> Güvenlik, hizmet iyileştirme, hile önleme (ilgili kişinin temel haklarını zedelememek kaydıyla)</li>
        <li><strong>Açık rıza:</strong> Zorunlu olmayan pazarlama iletişimi; gerekli hallerde özel nitelikli veri</li>
        <li><strong>Bir hakkın tesisi, kullanılması veya korunması:</strong> Uyuşmazlık süreçleri</li>
    </ul>

    <h2 id="yontem">7. Toplama yöntemi</h2>
    <p>
        Veriler; web ve mobil formlar, misafir randevu sihirbazı, hekim/klinik panelleri, SMS doğrulama,
        çerezler, sunucu logları, ödeme/mağaza bildirimleri ve destek kanalları aracılığıyla otomatik veya kısmen otomatik yollarla toplanır.
    </p>

    <h2 id="aktarim">8. Aktarım</h2>
    <p>Kişisel verileriniz, amaçla sınırlı ve gerekli ölçüde aşağıdaki alıcı gruplarına aktarılabilir:</p>
    <ul>
        <li>Hekim / klinik / yetkili personel (randevu ve hizmet ifası için)</li>
        <li>Barındırma (hosting), e-posta, SMS, push bildirim (FCM/APNs/Expo vb.) sağlayıcıları</li>
        <li>Ödeme kuruluşları, banka (havale), App Store / Google Play (IAP)</li>
        <li>Güvenlik (reCAPTCHA vb.) ve analitik araçları (yapılandırılmışsa)</li>
        <li>Yetkili kamu kurum ve kuruluşları (yasal zorunluluk)</li>
    </ul>
    <p>
        Yurt dışına aktarım söz konusu olursa KVKK’nın yurt dışı aktarım hükümleri ve taahhütname/standart sözleşme
        veya açık rıza gibi uygun hukuki yollar gözetilir.
    </p>
    <p><strong>Verileriniz satılmaz, kiralanmaz veya izinsiz pazarlama listelerine eklenmez.</strong></p>

    <h2 id="saklama">9. Saklama süreleri</h2>
    <ul>
        <li>Hesap verileri: üyelik süresince + yasal zamanaşımı</li>
        <li>Randevu ve işlem kayıtları: hizmet ilişkisi ve yasal saklama süreleri</li>
        <li>Log / güvenlik kayıtları: makul süre (genellikle aylar mertebesinde, risk ve mevzuata göre)</li>
        <li>Pazarlama rızası: rıza geri alınana veya süre dolana kadar</li>
    </ul>
    <p>Süre sonunda veriler silinir, yok edilir veya anonim hâle getirilir.</p>

    <h2 id="guvenlik">10. Güvenlik tedbirleri</h2>
    <p>
        Erişim yetkilendirme, şifrelerin hash ile saklanması, HTTPS, oturum güvenliği, rate limit,
        (mümkün olduğunda) iki adımlı doğrulama, yedekleme ve erişim logları gibi teknik ve idari tedbirler uygulanır.
        Mutlak güvenlik garanti edilemez; olay tespitinde makul bildirim süreçleri işletilir.
    </p>

    <h2 id="haklar">11. İlgili kişi hakları (KVKK m.11)</h2>
    <p>Kanun’un 11. maddesi uyarınca:</p>
    <ul>
        <li>Kişisel verilerinizin işlenip işlenmediğini öğrenme</li>
        <li>İşlenmişse buna ilişkin bilgi talep etme</li>
        <li>İşlenme amacını ve amaca uygun kullanılıp kullanılmadığını öğrenme</li>
        <li>Yurt içinde/yurt dışında aktarıldığı üçüncü kişileri bilme</li>
        <li>Eksik veya yanlış işlenmişse düzeltilmesini isteme</li>
        <li>KVKK m.7 şartları çerçevesinde silinmesini/yok edilmesini isteme</li>
        <li>Düzeltme/silme işlemlerinin aktarıldığı üçüncü kişilere bildirilmesini isteme</li>
        <li>Münhasıran otomatik sistemlerle analiz edilmesi nedeniyle aleyhe bir sonucun ortaya çıkmasına itiraz etme</li>
        <li>Kanuna aykırı işleme nedeniyle zararın giderilmesini talep etme</li>
    </ul>

    <h2 id="basvuru">12. Başvuru usulü</h2>
    <p>
        Haklarınızı kullanmak için kimliğinizi tevsik eden bir talep ile
        <a href="mailto:info@randevuajandam.com">info@randevuajandam.com</a> adresine yazabilirsiniz.
        Başvurular KVKK ve ilgili tebliğler uyarınca en geç 30 gün içinde sonuçlandırılır;
        işlemin ayrıca bir maliyet gerektirmesi hâlinde Kurulca belirlenen tarife uygulanabilir.
        Yanıttan memnun kalınmaması hâlinde Kişisel Verileri Koruma Kurulu’na şikâyet hakkı saklıdır.
    </p>

    <h2 id="cerez">13. Çerezler ve izleme</h2>
    <p>
        Platform; oturum, güvenlik ve (yapılandırılmışsa) analitik/reklam çerezleri kullanabilir.
        Zorunlu çerezler hizmetin çalışması için gereklidir. Tercih edilebilir çerezler için tarayıcı ayarlarınızı kullanabilirsiniz.
        Ayrıntılar <a href="{{ route('frontend.legal.gizlilik') }}">Gizlilik Politikası</a> içindedir.
    </p>

    <h2 id="guncelleme">14. Güncellemeler</h2>
    <p>
        Bu metin mevzuat veya hizmet değişikliklerine göre güncellenebilir.
        Güncel sürüm Platform’da yayınlandığı tarihte yürürlüğe girer. Son güncelleme tarihi sayfa başında yer alır.
    </p>
@endcomponent
@endsection
