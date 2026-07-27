@php
    $dg = rtrim(asset('themes/delogis'), '/');
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&family=Castoro:ital@0;1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ $dg }}/vendors/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/animate/animate.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/animate/custom-animate.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/fontawesome/css/all.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/jarallax/jarallax.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/jquery-magnific-popup/jquery.magnific-popup.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/nouislider/nouislider.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/nouislider/nouislider.pips.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/odometer/odometer.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/swiper/swiper.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/delogis-icons/style.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/tiny-slider/tiny-slider.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/reey-font/stylesheet.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/alagambe-font/stylesheet.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/owl-carousel/owl.carousel.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/owl-carousel/owl.theme.default.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/bxslider/jquery.bxslider.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/bootstrap-select/css/bootstrap-select.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/vegas/vegas.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/jquery-ui/jquery-ui.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/timepicker/timePicker.css">
<link rel="stylesheet" href="{{ $dg }}/css/delogis.css">
<link rel="stylesheet" href="{{ $dg }}/css/delogis-color-1.css">
<style>
:root {
  --delogis-base: #C96A2B;
  --delogis-base-rgb: 201, 106, 43;
}
/* Tailwind form/page içerik alanı */
body.delogis-site .ra-tw-content,
body.delogis-site main .fe-page,
body.delogis-site main .max-w-7xl {
  /* mevcut blade içerikleri korunur */
}
body.delogis-site main > .fe-page,
body.delogis-site main > section:not([class*="main-slider"]):not([class*="feature"]):not([class*="about"]):not([class*="services"]):not([class*="counter"]):not([class*="blog"]):not([class*="cta"]):not([class*="testimonial"]) {
  /* iç sayfalar için hafif üst boşluk */
}
body.delogis-site main > div,
body.delogis-site main > section.fe-page {
  padding-top: 0;
}
/* Vite/Tailwind yüklü form sayfalarında container */
body.delogis-site .fe-container { max-width: 1200px; margin: 0 auto; padding: 24px 16px 64px; }
</style>
