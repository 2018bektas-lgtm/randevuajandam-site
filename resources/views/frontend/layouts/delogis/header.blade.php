@php
    $dg = rtrim(asset('themes/delogis'), '/');
    $logo = asset('assets/images/logo.png');
@endphp
<header class="main-header-three">
    <nav class="main-menu main-menu-three">
        <div class="main-menu-three__wrapper">
            <div class="main-menu-three__wrapper-inner">
                <div class="main-menu-three__left">
                    <div class="main-menu-three__logo">
                        <a href="{{ url('/') }}">
                            <img src="{{ $logo }}" alt="Randevu Ajandam" style="max-height:52px;width:auto;mix-blend-mode:multiply">
                        </a>
                    </div>
                    <div class="main-menu-three__main-menu-box">
                        <a href="#" class="mobile-nav__toggler"><i class="fa fa-bars"></i></a>
                        <ul class="main-menu__list">
                            <li class="{{ request()->is('/') ? 'current' : '' }}"><a href="{{ url('/') }}">Ana Sayfa</a></li>
                            <li class="{{ request()->routeIs('frontend.hekimler') ? 'current' : '' }}"><a href="{{ route('frontend.hekimler') }}">Hekimler</a></li>
                            <li class="{{ request()->routeIs('frontend.egitimler.*') ? 'current' : '' }}"><a href="{{ route('frontend.egitimler.index') }}">Eğitimler</a></li>
                            <li class="{{ request()->routeIs('frontend.blog.*') ? 'current' : '' }}"><a href="{{ route('frontend.blog.index') }}">Blog</a></li>
                            <li class="{{ request()->routeIs('frontend.paketler') ? 'current' : '' }}"><a href="{{ route('frontend.paketler') }}">Paketler</a></li>
                            <li class="{{ request()->routeIs('frontend.legal.iletisim') ? 'current' : '' }}"><a href="{{ route('frontend.legal.iletisim') }}">İletişim</a></li>
                        </ul>
                    </div>
                </div>
                <div class="main-menu-three__right">
                    <div class="main-menu-three__call">
                        <div class="main-menu-three__call-icon">
                            <span class="icon-phone-call"></span>
                        </div>
                        <div class="main-menu-three__call-content">
                            <p class="main-menu-three__call-sub-title">Destek</p>
                            <h5 class="main-menu-three__call-number">
                                <a href="https://wa.me/905319912427" target="_blank" rel="noopener">WhatsApp</a>
                            </h5>
                        </div>
                    </div>
                    <div class="main-menu-three__search-cart-box" style="gap:10px;align-items:center">
                        @if(Auth::guard('hasta')->check())
                            <a href="{{ route('frontend.hasta.profil') }}" class="thm-btn" style="padding:10px 16px;font-size:13px">Profilim</a>
                        @elseif(Auth::guard('doktor')->check())
                            <a href="{{ route('hekim.panel') }}" class="thm-btn" style="padding:10px 16px;font-size:13px">Hekim Panel</a>
                        @else
                            <a href="{{ route('frontend.hasta.giris') }}" class="thm-btn thm-btn--two" style="padding:10px 16px;font-size:13px">Giriş</a>
                            <a href="{{ route('frontend.hekim.kayit') }}" class="thm-btn" style="padding:10px 16px;font-size:13px">Hekim Ol</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
<div class="stricky-header stricked-menu main-menu main-menu-three">
    <div class="sticky-header__content"></div>
</div>
