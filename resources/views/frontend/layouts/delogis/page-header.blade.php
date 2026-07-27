@php
    $dg = rtrim(asset('themes/delogis'), '/');
    $title = $title ?? 'Sayfa';
    $crumb = $crumb ?? $title;
@endphp
<section class="page-header">
    <div class="page-header-bg" style="background-image: url({{ $dg }}/images/backgrounds/page-header-bg.jpg)"></div>
    <div class="container">
        <div class="page-header__inner">
            <h2>{{ $title }}</h2>
            <ul class="thm-breadcrumb list-unstyled">
                <li><a href="{{ url('/') }}">Ana Sayfa</a></li>
                <li><span>/</span></li>
                <li>{{ $crumb }}</li>
            </ul>
        </div>
    </div>
</section>
