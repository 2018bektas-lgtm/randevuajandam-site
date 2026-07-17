@extends('frontend.layouts.app')

@section('baslik', 'Kullanım Koşulları — Randevu Ajandam')
@section('meta_aciklama', 'Randevu Ajandam web ve mobil platform kullanım koşulları, paket ve abonelik kuralları.')

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
        Randevu Ajandam platformunu (web ve mobil) kullanarak aşağıdaki koşulları kabul etmiş sayılırsınız.
        Hizmet, hekimler, klinikler, personel ve danışanlar arasındadır; tıbbi teşhis veya tedavi taahhüdü değildir.
    </p>

    <h2>1. Hesaplar</h2>
    <ul>
        <li>Doğru ve güncel bilgi vermek kullanıcının sorumluluğundadır.</li>
        <li>Hesap güvenliği (şifre, 2FA) kullanıcıya aittir.</li>
        <li>Personel hesapları klinik yöneticisi tarafından tanımlanır ve yetkilendirilir.</li>
    </ul>

    <h2>2. Paketler ve ödeme</h2>
    <ul>
        <li>Bireysel hekim paketleri demo, starter, plus, VIP ve web entegrasyonu kademelerini içerir.</li>
        <li>Klinik paketleri ekip / klinik yapısı içindir; mobil abonelikle açılamayabilir.</li>
        <li>Mobil ücretli abonelikler App Store / Google Play abonelik kurallarına tabidir (IAP).</li>
        <li>Havale/EFT talepleri onay sonrası aktifleşir.</li>
        <li>İptal ve iade: ilgili mağaza politikası ve/veya yazılı destek talebi ile değerlendirilir.</li>
    </ul>

    <h2>3. Kabul edilemez kullanım</h2>
    <p>
        Yetkisiz erişim, spam, sahte randevu, hasta verilerinin izinsiz paylaşımı, sistemin bozulması
        veya yasalara aykırı içerik yasaktır. İhlalde hesap askıya alınabilir.
    </p>

    <h2>4. Fikri mülkiyet</h2>
    <p>
        Platform yazılımı, marka ve arayüz Randevu Ajandam’a aittir. Hekim/klinik içerikleri (biyografi, blog vb.)
        ilgili kullanıcıya aittir; yayın için gerekli haklara sahip olduğunu beyan eder.
    </p>

    <h2>5. Sorumluluk sınırı</h2>
    <p>
        Randevu organizasyonu ve yazılım hizmeti sunulur; hekim–danışan ilişkisinden doğan tıbbi sonuçlardan
        platform sorumlu tutulamaz. Kesintisiz erişim garanti edilmez; makul çaba gösterilir.
    </p>

    <h2>6. Değişiklikler</h2>
    <p>
        Koşullar güncellenebilir. Önemli değişiklikler uygulama veya site üzerinden duyurulur.
        Güncelleme sonrası kullanıma devam, kabul anlamına gelir.
    </p>

    <h2>7. İletişim</h2>
    <p>
        <a href="mailto:destek@randevuajandam.com">destek@randevuajandam.com</a><br>
        <a href="{{ route('frontend.legal.gizlilik') }}">Gizlilik Politikası</a> ·
        <a href="{{ route('frontend.legal.kvkk') }}">KVKK</a>
    </p>
</section>
@endsection
