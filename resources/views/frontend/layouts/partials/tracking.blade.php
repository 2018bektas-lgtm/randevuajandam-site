{{-- Ana platform izleme: yönetim SEO ayarlarından (sadece platform sahibi) --}}
@php
    $tr = $siteAyari ?? \App\Models\SiteAyari::cached();
    $gtm = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($tr->gtm_container_id ?? ''));
    $ga4 = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($tr->ga4_measurement_id ?? ''));
    $pixel = preg_replace('/[^0-9]/', '', (string) ($tr->meta_pixel_id ?? ''));
    $ads = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($tr->google_ads_id ?? ''));
@endphp
{{-- Tracking: idle sonrası yükle — ilk boyamayı engellemesin --}}
@if($gtm !== '' || $ga4 !== '' || $ads !== '' || $pixel !== '')
<script>
(function(){
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
    fbq('init','{{ $pixel }}'); fbq('track','PageView');
@endif
  }
  if ('requestIdleCallback' in window) requestIdleCallback(loadTracking, {timeout: 3500});
  else window.addEventListener('load', function(){ setTimeout(loadTracking, 1200); });
})();
</script>
@endif
