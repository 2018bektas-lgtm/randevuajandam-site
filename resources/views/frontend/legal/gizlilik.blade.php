@extends('frontend.layouts.app')

@section('baslik', 'Gizlilik Politikası — Randevu Ajandam')
@section('meta_aciklama', 'Randevu Ajandam gizlilik politikası: kişisel verilerin toplanması, işlenmesi ve korunması.')

@section('icerik')

<section class="bg-slate-50 border-b border-slate-200">
    <div class="max-w-3xl mx-auto px-6 py-12 md:py-16">
        <p class="text-xs font-bold uppercase tracking-widest text-[#C96A2B] mb-3">Yasal</p>
        <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 font-display">{{ $baslik }}</h1>
        <p class="mt-3 text-sm text-slate-500">Son güncelleme: {{ $guncelleme }}</p>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10 md:py-14 prose prose-slate prose-headings:font-display">
    <p>
        Bu Gizlilik Politikası, <strong>Randevu Ajandam</strong> web sitesi ve mobil uygulamaları
        (hekim, personel ve hasta uygulamaları dâhil) üzerinden toplanan kişisel verilerin işlenmesine ilişkindir.
        Veri sorumlusu: platform işletmecisi (iletişim: <a href="mailto:destek@randevuajandam.com">destek@randevuajandam.com</a>).
    </p>

    <h2>1. Toplanan veriler</h2>
    <ul>
        <li>Kimlik ve iletişim: ad soyad, e-posta, telefon, unvan, branş</li>
        <li>Hesap ve güvenlik: şifre (hash), 2FA, cihaz / oturum token’ları</li>
        <li>Hizmet verisi: randevu, hasta kaydı, ödeme kaydı, paket aboneliği</li>
        <li>Teknik: IP, cihaz modeli, uygulama sürümü, push bildirim token’ı</li>
        <li>Ödeme: havale referansı; mağaza içi satın almada App Store / Google Play işlem kimlikleri (kart bilgisi saklanmaz)</li>
    </ul>

    <h2>2. İşleme amaçları</h2>
    <ul>
        <li>Randevu ve klinik operasyonunun yürütülmesi</li>
        <li>Üyelik, paket ve faturalama süreçleri</li>
        <li>Güvenlik, dolandırıcılık önleme ve yasal yükümlülükler</li>
        <li>Bildirim (randevu hatırlatma, talep, sistem duyurusu)</li>
        <li>Ürün iyileştirme ve destek</li>
    </ul>

    <h2>3. Hukuki sebepler</h2>
    <p>
        6698 sayılı KVKK kapsamında sözleşme ifası, meşru menfaat, açık rıza (gerekli hallerde) ve
        yasal yükümlülükler temel alınır. Detay için
        <a href="{{ route('frontend.legal.kvkk') }}">KVKK Aydınlatma Metni</a>.
    </p>

    <h2>4. Paylaşım</h2>
    <p>
        Veriler; barındırma, e-posta, push (ör. Expo / FCM / APNs), ödeme ve mağaza altyapı sağlayıcıları ile
        yalnızca hizmetin ifası için paylaşılabilir. Yasal zorunluluk halinde yetkili mercilere aktarılabilir.
        Verileriniz izinsiz üçüncü taraflara satılmaz.
    </p>

    <h2>5. Saklama</h2>
    <p>
        Veriler, hizmet ilişkisi ve yasal zamanaşımı süreleri boyunca saklanır; süre bitiminde silinir veya anonimleştirilir.
    </p>

    <h2>6. Haklarınız</h2>
    <p>
        KVKK m.11 kapsamındaki haklarınız için
        <a href="mailto:destek@randevuajandam.com">destek@randevuajandam.com</a> adresine başvurabilirsiniz.
    </p>

    <h2>7. Mobil uygulama</h2>
    <p>
        Hekim mobil uygulaması oturum token’ını cihazda güvenli depolamada tutar. Kamera/mikrofon yalnızca
        online görüşme ve profil fotoğrafı için istenir. Konum zorunlu değildir.
    </p>

    <p class="text-sm text-slate-500 mt-10">
        Ayrıca bakınız:
        <a href="{{ route('frontend.legal.kullanim') }}">Kullanım Koşulları</a> ·
        <a href="{{ route('frontend.legal.kvkk') }}">KVKK</a>
    </p>
</section>
@endsection
