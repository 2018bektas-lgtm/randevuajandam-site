{{-- Ana platform izleme: yönetim SEO ayarlarından --}}
@php
    $tr = $siteAyari ?? \App\Models\SiteAyari::cached();
    $gtm = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($tr->gtm_container_id ?? ''));
    // GA4: G-XXXXXXXX — tire ve alfanümerik
    $ga4 = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($tr->ga4_measurement_id ?? ''));
    $pixel = preg_replace('/[^0-9]/', '', (string) ($tr->meta_pixel_id ?? ''));
    $ads = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($tr->google_ads_id ?? ''));
    $metaPixelEvents = \App\Support\MetaPixel::pull();
@endphp
@if($gtm !== '' || $ga4 !== '' || $ads !== '' || $pixel !== '')

@if($gtm !== '')
{{-- GTM: mümkün olduğunca erken (GA doğrulaması için idle yok) --}}
<script>
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ $gtm }}');
</script>
@endif

@if($ga4 !== '' || $ads !== '')
{{-- gtag: GA4 ve/veya Google Ads — hemen yükle (idle ile GA “veri toplama yok” uyarısı veriyor) --}}
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4 !== '' ? $ga4 : $ads }}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
window.gtag = gtag;
gtag('js', new Date());
@if($ga4 !== '')
gtag('config', '{{ $ga4 }}', {
  send_page_view: true,
  anonymize_ip: true
});
@endif
@if($ads !== '')
gtag('config', '{{ $ads }}');
@endif
</script>
@endif

<script>
(function(){
  // Sağlık kategorisinde bastırılan standart olaylar → custom (RA_*)
  var RA_RESTRICTED = {
    Schedule: 'RA_Booking', Lead: 'RA_Lead', CompleteRegistration: 'RA_Register',
    Purchase: 'RA_Purchase', Subscribe: 'RA_Subscribe', StartTrial: 'RA_Trial',
    InitiateCheckout: 'RA_Checkout', AddToCart: 'RA_AddToCart', AddPaymentInfo: 'RA_PaymentInfo',
    Contact: 'RA_Contact', SubmitApplication: 'RA_Application', FindLocation: 'RA_FindLocation',
    Search: 'RA_Search'
  };

  function raFbqSend(eventName, params, forceCustom){
    if (!eventName || typeof fbq !== 'function') return;
    var name = eventName;
    var custom = !!forceCustom;
    if (RA_RESTRICTED[name]) {
      name = RA_RESTRICTED[name];
      custom = true;
    }
    if (name.indexOf('RA_') === 0) custom = true;
    var p = params && typeof params === 'object' ? params : {};
    // Kısıt riski: kategori/status gönderme
    if (p.content_category) delete p.content_category;
    if (p.status !== undefined) delete p.status;
    try {
      if (custom) {
        if (Object.keys(p).length) fbq('trackCustom', name, p);
        else fbq('trackCustom', name);
      } else {
        if (Object.keys(p).length) fbq('track', name, p);
        else fbq('track', name);
      }
    } catch (e) {}
  }

  window.raMetaPixelQueue = window.raMetaPixelQueue || [];
  window.raMetaTrack = function(eventName, params){
    if (!eventName) return;
    try {
      if (typeof fbq === 'function') {
        raFbqSend(eventName, params || {}, false);
      } else {
        window.raMetaPixelQueue.push({ event: eventName, params: params || {}, custom: false });
      }
    } catch (e) {}
  };

  function loadMetaPixel(){
@if($pixel !== '')
    if (window.fbq) return;
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init','{{ $pixel }}');
    // PageView üst huni — genelde kısıtlanmaz
    fbq('track','PageView');

    var serverEvents = @json($metaPixelEvents);
    if (Array.isArray(serverEvents)) {
      serverEvents.forEach(function(item){
        if (!item || !item.event) return;
        raFbqSend(item.event, item.params || {}, !!item.custom);
      });
    }
    if (window.raMetaPixelQueue && window.raMetaPixelQueue.length) {
      window.raMetaPixelQueue.forEach(function(item){
        if (!item || !item.event) return;
        raFbqSend(item.event, item.params || {}, !!item.custom);
      });
      window.raMetaPixelQueue = [];
    }
@endif
  }

@if($pixel !== '')
  if ('requestIdleCallback' in window) requestIdleCallback(loadMetaPixel, {timeout: 2000});
  else window.addEventListener('load', function(){ setTimeout(loadMetaPixel, 400); });
@endif

  document.addEventListener('click', function(ev){
    var el = ev.target && ev.target.closest ? ev.target.closest('[data-meta-event]') : null;
    if (!el) return;
    var name = el.getAttribute('data-meta-event');
    if (!name) return;
    // data-meta-event="Contact" → RA_Contact
    var raw = el.getAttribute('data-meta-params');
    var params = {};
    if (raw) {
      try { params = JSON.parse(raw); } catch (e) {}
    }
    if (typeof window.raMetaTrack === 'function') window.raMetaTrack(name, params);
  }, true);
})();
</script>
@endif
