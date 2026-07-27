<!DOCTYPE html>
<html lang="tr">
<head>
    @include('frontend.layouts.partials.head')
    @include('frontend.layouts.delogis.assets-css')
    <style>
      :root {
        --delogis-base: #C96A2B;
        --delogis-base-rgb: 201, 106, 43;
      }
      body.delogis-site { background: #fff; }
      body.delogis-site main > .fe-page,
      body.delogis-site main > section.pricing-page,
      body.delogis-site main > div:not(.page-wrapper) {
        /* Tailwind içerik */
      }
      body.delogis-site .fe-page { padding-top: 1.5rem; padding-bottom: 3rem; }
      body.delogis-site .fe-container { max-width: 1200px; margin-left: auto; margin-right: auto; padding-left: 1rem; padding-right: 1rem; }
      /* Page header altı Tailwind blokları */
      body.delogis-site main .page-header + * { margin-top: 0; }
    </style>
    @stack('delogis_head')
</head>
@php $dg = rtrim(asset('themes/delogis'), '/'); @endphp
<body class="custom-cursor delogis-site">
    @include('frontend.layouts.partials.tracking-body')

    <div class="custom-cursor__cursor"></div>
    <div class="custom-cursor__cursor-two"></div>
    {{-- Preloader: yavaş bağlantıda kapalı (isteğe açmak için class ekleyin) --}}
    <div class="preloader" style="display:none">
        <div class="preloader__image" style="background-image:url({{ $dg }}/images/loader.png)"></div>
    </div>

    <div class="page-wrapper">
        @include('frontend.layouts.delogis.header')

        <main>
            @yield('icerik')
        </main>

        @include('frontend.layouts.delogis.footer')
    </div>

    <div class="mobile-nav__wrapper">
        <div class="mobile-nav__overlay mobile-nav__toggler"></div>
        <div class="mobile-nav__content">
            <span class="mobile-nav__close mobile-nav__toggler"><i class="fa fa-times"></i></span>
            <div class="logo-box">
                <a href="{{ url('/') }}"><strong style="color:#fff">Randevu Ajandam</strong></a>
            </div>
            <div class="mobile-nav__container"></div>
            <ul class="mobile-nav__contact list-unstyled">
                <li><i class="fa fa-envelope"></i> <a href="mailto:info@randevuajandam.com">info@randevuajandam.com</a></li>
                <li><i class="fa fa-phone-alt"></i> <a href="https://wa.me/905319912427">WhatsApp Destek</a></li>
            </ul>
        </div>
    </div>

    <a href="#" data-target="html" class="scroll-to-target scroll-to-top"><i class="icon-up-arrow"></i></a>

    @include('frontend.layouts.partials.script')
    @include('frontend.layouts.delogis.assets-js')
    @stack('scripts')
</body>
</html>
