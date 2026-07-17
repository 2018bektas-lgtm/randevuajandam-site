@extends('frontend.layouts.app')

@section('baslik', 'KVKK Aydınlatma Metni — Randevu Ajandam')
@section('meta_aciklama', 'Randevu Ajandam KVKK aydınlatma metni: kişisel verilerin işlenmesi hakkında bilgilendirme.')

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
        6698 sayılı Kişisel Verilerin Korunması Kanunu (“KVKK”) uyarınca, Randevu Ajandam platformu
        kapsamında işlenen kişisel verilerinize ilişkin aydınlatma metnidir.
    </p>

    <h2>Veri sorumlusu</h2>
    <p>
        Randevu Ajandam platform işletmecisi<br>
        E-posta: <a href="mailto:destek@randevuajandam.com">destek@randevuajandam.com</a>
    </p>

    <h2>İşlenen veri kategorileri</h2>
    <ul>
        <li>Kimlik, iletişim, mesleki (unvan, branş)</li>
        <li>Müşteri işlem (randevu, ödeme kaydı)</li>
        <li>İşlem güvenliği (IP, log, cihaz)</li>
        <li>Sağlık verisi: randevu notları yalnızca hekim/klinik tarafında, hizmet amacıyla işlenir</li>
    </ul>

    <h2>Toplama yöntemi</h2>
    <p>Web formları, mobil uygulama, çağrı/e-posta desteği, çerez ve log kayıtları.</p>

    <h2>Aktarım</h2>
    <p>
        Yurt içi / yurt dışı bulut, e-posta, push ve mağaza altyapı sağlayıcılarına KVKK’ya uygun teknik
        ve idari tedbirlerle aktarılabilir.
    </p>

    <h2>Haklar (KVKK m.11)</h2>
    <p>
        Bilgi talep etme, düzeltme, silme, itiraz ve şikâyet haklarınız için
        <a href="mailto:destek@randevuajandam.com">destek@randevuajandam.com</a> adresine başvurun.
        Gerekirse Kişisel Verileri Koruma Kurulu’na şikâyette bulunabilirsiniz.
    </p>

    <p class="text-sm text-slate-500 mt-10">
        <a href="{{ route('frontend.legal.gizlilik') }}">Gizlilik Politikası</a> ·
        <a href="{{ route('frontend.legal.kullanim') }}">Kullanım Koşulları</a>
    </p>
</section>
@endsection
