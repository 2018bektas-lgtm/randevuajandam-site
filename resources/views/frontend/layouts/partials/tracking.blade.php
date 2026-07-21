{{-- Ana platform izleme: yönetim SEO ayarlarından (sadece platform sahibi) --}}
@php
    $tr = $siteAyari ?? \App\Models\SiteAyari::cached();
    $gtm = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($tr->gtm_container_id ?? ''));
    $ga4 = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($tr->ga4_measurement_id ?? ''));
    $pixel = preg_replace('/[^0-9]/', '', (string) ($tr->meta_pixel_id ?? ''));
    $ads = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($tr->google_ads_id ?? ''));
    $metaPixelEvents = \App\Support\MetaPixel::pull();
@endphp
{{-- Tracking: idle sonrası yükle — ilk boyamayı engellemesin --}}
@if($gtm !== '' || $ga4 !== '' || $ads !== '' || $pixel !== '')
<script>
(function(){
  // Meta Pixel kuyruk: pixel yüklenmeden önce de event birikebilir
  window.raMetaPixelQueue = window.raMetaPixelQueue || [];
  window.raMetaTrack = function(eventName, params){
    if (!eventName) return;
    try {
      if (typeof fbq === 'function') {
        if (params && typeof params === 'object' && Object.keys(params).length) {
          fbq('track', eventName, params);
        } else {
          fbq('track', eventName);
        }
      } else {
        window.raMetaPixelQueue.push({ event: eventName, params: params || {} });
      }
    } catch (e) {}
  };

  function loadTracking(){
@if($gtm !== '')
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $gtm }}');
@elseif($ga4 !== '')
    var s=document.createElement('script'); s.async=true; s.src='https://www.googletagmanager.com/gtag/js?id={{ $ga4 }}';
    document.head.appendChild(s);
    window.dataLayer=window.dataLayer||[]; function gtag(){dataLayer.push(arguments);} window.gtag=gtag;
    gtag('js', new Date()); gtag('config','{{ $ga4 }}');
    @if($ads !== '') gtag('config','{{ $ads }}'); @endif
@elseif($ads !== '')
    var s=document.createElement('script'); s.async=true; s.src='https://www.googletagmanager.com/gtag/js?id={{ $ads }}';
    document.head.appendChild(s);
    window.dataLayer=window.dataLayer||[]; function gtag(){dataLayer.push(arguments);} window.gtag=gtag;
    gtag('js', new Date()); gtag('config','{{ $ads }}');
@endif
@if($pixel !== '')
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init','{{ $pixel }}');
    fbq('track','PageView');

    // Sunucudan gelen standart olaylar
    var serverEvents = @json($metaPixelEvents);
    if (Array.isArray(serverEvents)) {
      serverEvents.forEach(function(item){
        if (!item || !item.event) return;
        var p = item.params || {};
        if (p && Object.keys(p).length) fbq('track', item.event, p);
        else fbq('track', item.event);
      });
    }

    // JS kuyruğu (pixel yüklenmeden tıklanan Contact vb.)
    if (window.raMetaPixelQueue && window.raMetaPixelQueue.length) {
      window.raMetaPixelQueue.forEach(function(item){
        if (!item || !item.event) return;
        var p = item.params || {};
        if (p && Object.keys(p).length) fbq('track', item.event, p);
        else fbq('track', item.event);
      });
      window.raMetaPixelQueue = [];
    }
@endif
  }
  if ('requestIdleCallback' in window) requestIdleCallback(loadTracking, {timeout: 3500});
  else window.addEventListener('load', function(){ setTimeout(loadTracking, 1200); });

  // data-meta-event="Contact" tıklamaları
  document.addEventListener('click', function(ev){
    var el = ev.target && ev.target.closest ? ev.target.closest('[data-meta-event]') : null;
    if (!el) return;
    var name = el.getAttribute('data-meta-event');
    if (!name) return;
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
