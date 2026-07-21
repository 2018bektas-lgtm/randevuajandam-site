{{-- Ana platform izleme: yönetim SEO ayarlarından --}}
@php
    $tr = $siteAyari ?? \App\Models\SiteAyari::cached();
    $gtm = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($tr->gtm_container_id ?? ''));
    $ga4 = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($tr->ga4_measurement_id ?? ''));
    $pixel = preg_replace('/[^0-9]/', '', (string) ($tr->meta_pixel_id ?? ''));
    $ads = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($tr->google_ads_id ?? ''));
    $metaPixelEvents = \App\Support\MetaPixel::pull();
@endphp
@if($gtm !== '' || $ga4 !== '' || $ads !== '' || $pixel !== '')

@if($gtm !== '')
<script>
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ $gtm }}');
</script>
@endif

@if($ga4 !== '' || $ads !== '')
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4 !== '' ? $ga4 : $ads }}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
window.gtag = gtag;
gtag('js', new Date());
@if($ga4 !== '')
gtag('config', '{{ $ga4 }}', { send_page_view: true, anonymize_ip: true });
@endif
@if($ads !== '')
gtag('config', '{{ $ads }}');
@endif
</script>
@endif

<script>
(function(){
  /**
   * Sağlık & zindelik domain sınıflandırması uyumu:
   * - Standart: PageView, ViewContent, Lead
   * - Custom: FormSubmit, DemoRequest, SelectPlan, CheckoutStart, PlanPurchase...
   * - ASLA: DoctorAppointment, PatientRegister, MedicalBooking, HealthAppointment
   */
  var STANDARD_OK = { PageView: 1, ViewContent: 1, Lead: 1 };
  var MAP = {
    Schedule: { e: 'FormSubmit', c: 1 },
    Lead: { e: 'Lead', c: 0 },
    CompleteRegistration: { e: 'Lead', c: 0 },
    Contact: { e: 'Lead', c: 0 },
    SubmitApplication: { e: 'FormSubmit', c: 1 },
    AddToCart: { e: 'SelectPlan', c: 1 },
    InitiateCheckout: { e: 'CheckoutStart', c: 1 },
    AddPaymentInfo: { e: 'PaymentInfo', c: 1 },
    Purchase: { e: 'PlanPurchase', c: 1 },
    Subscribe: { e: 'PlanSubscribe', c: 1 },
    StartTrial: { e: 'DemoRequest', c: 1 },
    Search: { e: 'SiteSearch', c: 1 },
    FindLocation: { e: 'SiteSearch', c: 1 },
    ViewContent: { e: 'ViewContent', c: 0 },
    PageView: { e: 'PageView', c: 0 },
    // Tıklama data-meta-event
    FormSubmit: { e: 'FormSubmit', c: 1 },
    DemoRequest: { e: 'DemoRequest', c: 1 }
  };
  var FORBIDDEN = /^(Doctor|Patient|Medical|Health|Appointment|RA_)/i;

  function cleanParams(p) {
    p = p && typeof p === 'object' ? p : {};
    var out = { source: 'website' };
    if (typeof p.value === 'number') out.value = p.value;
    if (typeof p.currency === 'string') out.currency = p.currency;
    if (typeof p.plan === 'string') out.plan = String(p.plan).slice(0, 24);
    if (typeof p.content_name === 'string') {
      var cn = p.content_name.toLowerCase();
      if (/starter|plus|pro|premium|clinic|plan|trial|subscription|form|product|page|search|signup|booking|waitlist/.test(cn)) {
        out.content_name = cn.replace(/[^a-z0-9_\-]/g, '').slice(0, 24) || 'product';
      } else {
        out.content_name = 'product';
      }
    }
    if (Array.isArray(p.content_ids)) out.content_ids = p.content_ids.slice(0, 5).map(String);
    if (typeof p.content_type === 'string') out.content_type = 'product';
    if (typeof p.num_items === 'number') out.num_items = p.num_items;
    if (typeof p.method === 'string') out.method = String(p.method).slice(0, 16);
    return out;
  }

  function raFbqSend(eventName, params, forceCustom) {
    if (!eventName || typeof fbq !== 'function') return;
    if (FORBIDDEN.test(eventName)) eventName = 'FormSubmit';
    var m = MAP[eventName];
    var name = m ? m.e : (STANDARD_OK[eventName] ? eventName : 'FormSubmit');
    var custom = m ? !!m.c : (forceCustom || !STANDARD_OK[name]);
    if (STANDARD_OK[name]) custom = false;
    var p = cleanParams(params);
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
  window.raMetaTrack = function (eventName, params) {
    if (!eventName) return;
    try {
      if (typeof fbq === 'function') raFbqSend(eventName, params || {}, false);
      else window.raMetaPixelQueue.push({ event: eventName, params: params || {} });
    } catch (e) {}
  };

  function loadMetaPixel() {
@if($pixel !== '')
    if (window.fbq) return;
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $pixel }}');
    fbq('track', 'PageView');

    var serverEvents = @json($metaPixelEvents);
    if (Array.isArray(serverEvents)) {
      serverEvents.forEach(function (item) {
        if (!item || !item.event) return;
        raFbqSend(item.event, item.params || {}, !!item.custom);
      });
    }
    if (window.raMetaPixelQueue && window.raMetaPixelQueue.length) {
      window.raMetaPixelQueue.forEach(function (item) {
        if (!item || !item.event) return;
        raFbqSend(item.event, item.params || {}, false);
      });
      window.raMetaPixelQueue = [];
    }
@endif
  }

@if($pixel !== '')
  if ('requestIdleCallback' in window) requestIdleCallback(loadMetaPixel, { timeout: 2000 });
  else window.addEventListener('load', function () { setTimeout(loadMetaPixel, 400); });
@endif

  document.addEventListener('click', function (ev) {
    var el = ev.target && ev.target.closest ? ev.target.closest('[data-meta-event]') : null;
    if (!el) return;
    var name = el.getAttribute('data-meta-event') || 'Lead';
    // Eski Contact → Lead
    if (name === 'Contact') name = 'Lead';
    var raw = el.getAttribute('data-meta-params');
    var params = { source: 'website' };
    if (raw) {
      try { params = Object.assign(params, JSON.parse(raw)); } catch (e) {}
    }
    if (typeof window.raMetaTrack === 'function') window.raMetaTrack(name, params);
  }, true);
})();
</script>
@endif
