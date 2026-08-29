{{--
    Randevu kapalı paneli — hekim online randevuya kapalıyken gösterilir.

    Hem hekim profil sayfasında (randevu_wizard @else dalı) hem hizmet detay
    sayfasında kullanılır. Amaç: hastayı boş bir alanla baş başa bırakmamak.

    Sıralama:
      1) Hekimin hasta iletişim kanalları (telefon / WhatsApp) varsa onlar
      2) Paketinde bekleme listesi varsa kayıt formu
      3) Hiçbiri yoksa en azından açıklayıcı bir yönlendirme

    Not: publicTelefon() yalnızca `hasta_telefon` alanını döner; hekimin
    kayıt telefonu ("telefon") public tarafta asla gösterilmez.
--}}
@php
    $rkTel = $doktor->publicTelefon();
    $rkTelHref = $rkTel ? preg_replace('/\D+/', '', $rkTel) : null;
    $rkWa = $doktor->publicWhatsappDigits();
    $rkIletisimVar = filled($rkTel) || filled($rkWa);
    $rkBeklemeVar = \App\Support\PaketYetki::has($doktor, 'bekleme_listesi');
@endphp

<div class="rk-kapali">
    <div class="rk-kapali-icon" aria-hidden="true">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z"/>
        </svg>
    </div>
    <h3>Randevu Al</h3>

    @if($rkIletisimVar)
        <p>Hekimimiz online randevu alımına geçici olarak kapalıdır. Randevu bilgisi için lütfen iletişime geçiniz.</p>
        <div class="rk-kapali-actions">
            @if($rkTel)
                <a href="tel:{{ $rkTelHref }}"
                   data-meta-event="Contact"
                   data-meta-params='{"content_name":"Hekim telefon"}'
                   class="primary">{{ $rkTel }}</a>
            @endif
            @if($rkWa)
                <a href="https://wa.me/{{ $rkWa }}" target="_blank" rel="noopener"
                   data-meta-event="Contact"
                   data-meta-params='{"content_name":"Hekim WhatsApp"}'
                   class="ghost">WhatsApp</a>
            @endif
        </div>
    @elseif($rkBeklemeVar)
        <p>Hekimimiz online randevu alımına geçici olarak kapalıdır. Bekleme listesine kaydolun, randevular açıldığında size haber verelim.</p>
    @else
        <p>Hekimimiz online randevu alımına geçici olarak kapalıdır. Randevular yeniden açıldığında bu sayfadan randevu oluşturabilirsiniz.</p>
    @endif

    @if($rkBeklemeVar)
        <div class="rk-kapali-bekleme">
            @include('frontend.hekimler.partials.bekleme_listesi_form', ['doktor' => $doktor])
        </div>
    @endif
</div>

@once
<style>
.rk-kapali {
    background: #fff;
    border: 1px solid #E5E7EB;
    border-radius: 1.5rem;
    padding: 2rem 1.5rem;
    text-align: center;
    box-shadow: 0 8px 30px rgba(31, 41, 55, 0.04);
}
.rk-kapali-icon {
    width: 3rem;
    height: 3rem;
    margin: 0 auto 0.85rem;
    border-radius: 1rem;
    background: #FFF7ED;
    border: 1px solid rgba(231, 181, 138, 0.45);
    color: #C96A2B;
    display: flex;
    align-items: center;
    justify-content: center;
}
.rk-kapali h3 {
    margin: 0 0 0.5rem;
    font-size: 1.05rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.02em;
}
.rk-kapali p {
    margin: 0 auto;
    max-width: 34rem;
    font-size: 0.85rem;
    line-height: 1.6;
    color: #6B7280;
}
.rk-kapali-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.6rem;
    margin-top: 1.15rem;
}
.rk-kapali-actions a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 2.75rem;
    padding: 0 1.35rem;
    border-radius: 0.8rem;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.rk-kapali-actions .primary {
    background: #C96A2B;
    color: #fff;
    border: 1px solid #C96A2B;
}
.rk-kapali-actions .primary:hover { background: #B55A20; color: #fff; }
.rk-kapali-actions .ghost {
    background: transparent;
    color: #64748B;
    border: 1px solid #E5E7EB;
}
.rk-kapali-actions .ghost:hover { background: #F8FAFC; color: #111827; }
.rk-kapali-bekleme { margin-top: 1.35rem; text-align: left; }
</style>
@endonce
