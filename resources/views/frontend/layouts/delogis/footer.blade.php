@php
    $dg = rtrim(asset('themes/delogis'), '/');
@endphp
<footer class="site-footer">
    <div class="site-footer__shape-1 float-bob-y">
        <img src="{{ $dg }}/images/shapes/site-footer-shape-1.png" alt="">
    </div>
    <div class="site-footer__top">
        <div class="container">
            <div class="site-footer__top-inner">
                <div class="site-footer__top-left">
                    <div class="site-footer__top-icon"><span class="icon-business-people"></span></div>
                    <div class="site-footer__top-content">
                        <h3>Online randevu: <span>7/24 açık</span></h3>
                    </div>
                </div>
                <div class="site-footer__top-right">
                    <div class="site-footer__social-title"><p>Takip edin:</p></div>
                    <div class="site-footer__social">
                        <a href="https://instagram.com/randevuajandam" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="site-footer__middle">
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="footer-widget__column footer-widget__about">
                        <div class="footer-widget__logo">
                            <a href="{{ url('/') }}"><strong style="color:#fff;font-size:1.2rem">Randevu Ajandam</strong></a>
                        </div>
                        <p class="footer-widget__about-text">Hekim ve kliniklerin online randevu, hasta ve abonelik yönetimi platformu.</p>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-6 col-md-6">
                    <div class="footer-widget__column footer-widget__link">
                        <div class="footer-widget__title-box"><h3 class="footer-widget__title">Keşfet</h3></div>
                        <ul class="footer-widget__link-list list-unstyled">
                            <li><a href="{{ route('frontend.hekimler') }}">Hekimler</a></li>
                            <li><a href="{{ route('frontend.paketler') }}">Paketler</a></li>
                            <li><a href="{{ route('frontend.egitimler.index') }}">Eğitimler</a></li>
                            <li><a href="{{ route('frontend.blog.index') }}">Blog</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="footer-widget__column footer-widget__Contact">
                        <div class="footer-widget__title-box"><h3 class="footer-widget__title">İletişim</h3></div>
                        <ul class="footer-widget__Contact-list list-unstyled">
                            <li>
                                <div class="icon"><span class="fas fa-envelope"></span></div>
                                <div class="text"><span>E-posta</span><p><a href="mailto:info@randevuajandam.com">info@randevuajandam.com</a></p></div>
                            </li>
                            <li>
                                <div class="icon"><span class="fas fa-phone-square"></span></div>
                                <div class="text"><span>WhatsApp</span><p><a href="https://wa.me/905319912427" target="_blank" rel="noopener">+90 531 991 24 27</a></p></div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="footer-widget__column footer-widget__newsletter">
                        <div class="footer-widget__title-box"><h3 class="footer-widget__title">Hekim misiniz?</h3></div>
                        <p class="footer-widget__newsletter-text">Ücretsiz deneme ile başlayın; belge yükleyin, yönetici onaylasın.</p>
                        <a href="{{ route('frontend.paketler') }}" class="thm-btn">Paketleri incele</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="site-footer__bottom">
        <div class="container">
            <div class="site-footer__bottom-inner">
                <p class="site-footer__bottom-text">
                    © {{ date('Y') }} <a href="{{ url('/') }}">Randevu Ajandam</a>
                    ·
                    <a href="{{ route('frontend.legal.gizlilik') }}">Gizlilik</a>
                    ·
                    <a href="{{ route('frontend.legal.kvkk') }}">KVKK</a>
                    ·
                    <a href="{{ route('frontend.legal.kullanim') }}">Kullanım</a>
                </p>
            </div>
        </div>
    </div>
</footer>
