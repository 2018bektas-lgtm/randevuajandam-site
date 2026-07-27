@extends('frontend.layouts.app')

@section('baslik', \App\Support\SeoMeta::homeTitle())
@section('meta_aciklama', \App\Support\SeoMeta::homeDescription())
@section('meta_anahtar_kelimeler', \App\Support\SeoMeta::keywords([
    'online randevu', 'doktor randevu', 'hekim randevu', 'klinik randevu',
    'hasta randevu', 'uzman doktor bul', 'diyetisyen randevu', 'psikolog randevu',
    'diş hekimi randevu', 'randevu ajandam',
]))
@section('canonical', url('/'))

@section('icerik')
@php
    $dg = rtrim(asset('themes/delogis'), '/');
    $stats = $istatistikler ?? [];
    $doktorSayisi = (int) ($stats['doktor_sayisi'] ?? 0);
    $randevuSayisi = (int) ($stats['randevu_sayisi'] ?? 0);
    $yorumSayisi = (int) ($stats['yorum_sayisi'] ?? 0);
    $bransSayisi = (int) ($stats['brans_sayisi'] ?? 0);
    $vitrin = collect($vitrinHekimler ?? $hekimler ?? [])->take(6);
    $branslar = collect($branslar ?? [])->take(6);
@endphp

{{-- Hero --}}
<section class="main-slider-three">
    <div class="main-slider__carousel owl-carousel owl-theme thm-owl__carousel"
         data-owl-options='{"loop": true, "items": 1, "navText": ["<span class=\"icon-left-arrow\"></span>","<span class=\"icon-right-arrow\"></span>"], "margin": 0, "dots": false, "nav": true, "animateOut": "fadeOut", "animateIn": "fadeIn", "active": true, "smartSpeed": 1000, "autoplay": true, "autoplayTimeout": 7000, "autoplayHoverPause": false}'>
        <div class="item main-slider-three__slide-1">
            <div class="main-slider-three__bg" style="background-image: url({{ $dg }}/images/backgrounds/main-slider-three-bg.jpg);"></div>
            <div class="main-slider-three__shape-3 img-bounce">
                <img src="{{ $dg }}/images/shapes/main-slider-three-shape-3.png" alt="">
            </div>
            <div class="main-slider-three__img">
                <img src="{{ $dg }}/images/resources/main-slider-three-img-1.png" alt="Randevu Ajandam">
            </div>
            <div class="main-slider-three__star-one zoominout">
                <img src="{{ $dg }}/images/shapes/main-slider-three-star-1.png" alt="">
            </div>
            <div class="main-slider-three__star-two img-bounce">
                <img src="{{ $dg }}/images/shapes/main-slider-three-star-2.png" alt="">
            </div>
            <div class="container">
                <div class="main-slider-three__content">
                    <div class="main-slider-three__sub-title-box">
                        <div class="main-slider-three__shape-1" style="background-image: url({{ $dg }}/images/shapes/main-slider-three-shape-1.png);"></div>
                        <p class="main-slider-three__sub-title">Online randevu platformu</p>
                    </div>
                    <h2 class="main-slider-three__title">
                        Uzman hekimlere<br>
                        <span>kolay randevu</span>
                    </h2>
                    <div class="main-slider-three__btn-founder-box">
                        <a href="{{ route('frontend.hekimler') }}" class="main-slider-two__btn-one thm-btn">Hekim bul</a>
                        <div class="main-slider-three__founder-box">
                            <h4 class="main-slider-three__founder-name">Hekim misiniz?</h4>
                            <p class="main-slider-three__founder-sub-title"><a href="{{ route('frontend.paketler') }}" style="color:inherit">Paketlerle başlayın →</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="item main-slider-three__slide-2">
            <div class="main-slider-three__bg" style="background-image: url({{ $dg }}/images/backgrounds/main-slider-three-bg.jpg);"></div>
            <div class="main-slider-three__img">
                <img src="{{ $dg }}/images/resources/main-slider-three-img-2.png" alt="">
            </div>
            <div class="container">
                <div class="main-slider-three__content">
                    <div class="main-slider-three__sub-title-box">
                        <div class="main-slider-three__shape-1" style="background-image: url({{ $dg }}/images/shapes/main-slider-three-shape-1.png);"></div>
                        <p class="main-slider-three__sub-title">14 gün deneme</p>
                    </div>
                    <h2 class="main-slider-three__title">
                        Kliniğinizi<br>
                        <span>dijitale taşıyın</span>
                    </h2>
                    <div class="main-slider-three__btn-founder-box">
                        <a href="{{ route('frontend.paketler') }}" class="main-slider-two__btn-one thm-btn">Paketleri incele</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Feature branş / özellik --}}
<section class="feature-two">
    <div class="container">
        <div class="row">
            @foreach ([
                ['t' => 'Online randevu', 'd' => 'Hasta kendi saati seçer; siz onaylarsınız.', 'i' => 'icon-account', 'href' => route('frontend.hekimler')],
                ['t' => 'Hekim paneli', 'd' => 'Takvim, hasta, finans ve web sitesi tek yerde.', 'i' => 'icon-mental-health', 'href' => route('frontend.paketler')],
                ['t' => 'Klinik paketi', 'd' => 'Çok hekimli klinik ve domain seçenekleri.', 'i' => 'icon-psychology', 'href' => route('frontend.paketler')],
            ] as $idx => $f)
                <div class="col-xl-4 col-lg-4 wow fadeInUp" data-wow-delay="{{ ($idx+1)*100 }}ms">
                    <div class="feature-two__single">
                        <div class="feature-two__img-box">
                            <div class="feature-two__img">
                                <img src="{{ $dg }}/images/resources/feature-2-{{ $idx+1 }}.jpg" alt="{{ $f['t'] }}">
                            </div>
                            <div class="feature-two__title-box">
                                <h3><a href="{{ $f['href'] }}">{{ $f['t'] }}</a></h3>
                                <div class="feature-two__icon"><span class="{{ $f['i'] }}"></span></div>
                            </div>
                            <div class="feature-two__hover-title-box">
                                <h3><a href="{{ $f['href'] }}">{{ $f['t'] }}</a></h3>
                                <p class="feature-two__hover-text">{{ $f['d'] }}</p>
                                <div class="feature-two__hover-icon"><span class="{{ $f['i'] }}"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- About / platform --}}
<section class="about-three">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-xl-6">
                <div class="about-three__left">
                    <div class="about-three__img-box">
                        <div class="about-three__img">
                            <img src="{{ $dg }}/images/resources/about-three-img-1.jpg" alt="Randevu Ajandam" style="border-radius:12px;max-width:100%">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="about-three__right">
                    <div class="section-title text-left">
                        <span class="section-title__tagline">Neden Randevu Ajandam?</span>
                        <h2 class="section-title__title">Hekim ve kliniklere özel randevu altyapısı</h2>
                    </div>
                    <p class="about-three__text-1">
                        Kayıt olun, belgelerinizi yükleyin; yönetici onayından sonra deneme veya paket ile devam edin.
                        Hastalar platformdan veya sizin web sitenizden randevu alabilir.
                    </p>
                    <div class="about-three__btn-box">
                        <a href="{{ route('frontend.paketler') }}" class="thm-btn">Hekim / klinik kaydı</a>
                        <a href="{{ route('frontend.hekimler') }}" class="thm-btn thm-btn--two" style="margin-left:10px">Hekimleri gör</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Counters --}}
<section class="counter-two">
    <div class="container">
        <div class="row">
            @foreach ([
                ['n' => $doktorSayisi, 'l' => 'Aktif uzman'],
                ['n' => $randevuSayisi, 'l' => 'Tamamlanan randevu'],
                ['n' => $yorumSayisi, 'l' => 'Hasta yorumu'],
                ['n' => $bransSayisi, 'l' => 'Uzmanlık alanı'],
            ] as $s)
                <div class="col-xl-3 col-lg-3 col-md-6">
                    <div class="counter-two__single">
                        <div class="counter-two__count-box">
                            <h3 class="odometer" data-count="{{ max(0, $s['n']) }}">00</h3>
                            <span class="counter-two__plus">+</span>
                        </div>
                        <p class="counter-two__text">{{ $s['l'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Branş / services style --}}
@if($branslar->isNotEmpty())
<section class="services-three">
    <div class="container">
        <div class="section-title text-center">
            <span class="section-title__tagline">Branşlar</span>
            <h2 class="section-title__title">Aradığınız uzmanlık alanı</h2>
        </div>
        <div class="row">
            @foreach ($branslar as $idx => $b)
                @php
                    $ad = is_object($b) ? ($b->ad ?? '') : ($b['ad'] ?? '');
                    $id = is_object($b) ? ($b->id ?? null) : ($b['id'] ?? null);
                    $href = $id ? route('frontend.hekimler', ['brans' => $id]) : route('frontend.hekimler');
                @endphp
                <div class="col-xl-4 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="{{ ($idx % 3 + 1) * 100 }}ms">
                    <div class="services-three__single">
                        <div class="services-three__icon"><span class="icon-psychology"></span></div>
                        <h3 class="services-three__title"><a href="{{ $href }}">{{ $ad }}</a></h3>
                        <p class="services-three__text">Bu branşta randevu alabileceğiniz hekimleri görün.</p>
                        <div class="services-three__btn-box">
                            <a href="{{ $href }}">Hekimleri gör <span class="icon-right-arrow"></span></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Vitrin hekimler --}}
@if($vitrin->isNotEmpty())
<section class="blog-two">
    <div class="container">
        <div class="section-title text-center">
            <span class="section-title__tagline">Hekimler</span>
            <h2 class="section-title__title">Öne çıkan uzmanlar</h2>
        </div>
        <div class="row">
            @foreach ($vitrin as $h)
                @php
                    $hAd = is_object($h) ? trim(($h->unvan ?? '').' '.($h->ad_soyad ?? '')) : trim(($h['unvan'] ?? '').' '.($h['ad_soyad'] ?? ''));
                    $slug = is_object($h) ? ($h->slug ?? $h->id) : ($h['slug'] ?? $h['id'] ?? '');
                    $img = is_object($h) ? ($h->profil_resmi_url ?? $h->profil_resmi ?? null) : ($h['profil_resmi'] ?? null);
                    $uzm = is_object($h) ? ($h->uzmanlik_alani ?? '') : ($h['uzmanlik_alani'] ?? '');
                    $href = $slug ? route('frontend.hekim.detay', $slug) : route('frontend.hekimler');
                @endphp
                <div class="col-xl-4 col-lg-4 wow fadeInUp">
                    <div class="blog-two__single">
                        <div class="blog-two__img">
                            <img src="{{ $img ?: $dg.'/images/team/team-1-1.jpg' }}" alt="{{ $hAd }}" style="height:240px;object-fit:cover;width:100%">
                            <a href="{{ $href }}"><span class="blog-two__plus"></span></a>
                        </div>
                        <div class="blog-two__content">
                            <h3 class="blog-two__title"><a href="{{ $href }}">{{ $hAd }}</a></h3>
                            <p class="blog-two__text">{{ \Illuminate\Support\Str::limit((string)$uzm, 80) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center" style="margin-top:28px">
            <a href="{{ route('frontend.hekimler') }}" class="thm-btn">Tüm hekimler</a>
        </div>
    </div>
</section>
@endif

{{-- CTA --}}
<section class="cta-one">
    <div class="cta-one__shape-1 float-bob-x">
        <img src="{{ $dg }}/images/shapes/cta-one-shape-1.png" alt="">
    </div>
    <div class="container">
        <div class="cta-one__inner">
            <p class="cta-one__text">Hekim veya klinik olarak 14 gün ücretsiz deneyin</p>
            <div class="cta-one__btn-box">
                <a href="{{ route('frontend.paketler') }}" class="cta-one__btn thm-btn">Hemen başla</a>
            </div>
        </div>
    </div>
</section>

@if(view()->exists('frontend.partials.seo-faq-schema'))
    @include('frontend.partials.seo-faq-schema')
@endif
@endsection
