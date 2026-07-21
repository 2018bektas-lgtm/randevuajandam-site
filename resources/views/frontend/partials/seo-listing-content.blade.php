{{-- Liste altı SEO metni + iç linkler — thin content'i önler --}}
@php
    $seoIlAd = $seoIlAd ?? null;
    $seoIlceAd = $seoIlceAd ?? null;
    $seoBransAd = $seoBransAd ?? null;
    $seoH1 = $seoH1 ?? 'Doktor Randevu';
@endphp
<section class="mt-12 md:mt-16 max-w-3xl mx-auto px-1">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 md:p-8 shadow-sm space-y-4 text-left">
        <h2 class="text-base font-extrabold font-display text-[#111827]">
            {{ $seoH1 }} hakkında
        </h2>
        <p class="text-xs md:text-sm text-slate-600 leading-relaxed">
            @if($seoBransAd && $seoIlAd)
                {{ $seoIlAd }}{{ $seoIlceAd ? ' / '.$seoIlceAd : '' }} bölgesinde
                <strong>{{ $seoBransAd }}</strong> uzmanlarından online randevu alabilirsiniz.
                Randevu Ajandam üzerinden müsait saatleri görün, hasta randevunuzu oluşturun.
            @elseif($seoIlAd)
                <strong>{{ $seoIlAd }}</strong>{{ $seoIlceAd ? ' '.$seoIlceAd : '' }} doktor ve klinik listesi.
                Branş filtreleyerek online randevu alın; hekim profillerinde hizmet ve müsaitlik bilgisi yer alır.
            @elseif($seoBransAd)
                Türkiye genelinde <strong>{{ $seoBransAd }}</strong> doktor randevusu.
                Şehir seçerek size en yakın uzmanı bulun.
            @else
                Randevu Ajandam ile online doktor randevusu alın. Branş, il ve ilçe filtreleriyle
                hekim bulun; diyetisyen, psikolog, diş hekimi ve diğer uzmanlıklardan randevu oluşturun.
            @endif
        </p>
        <div class="flex flex-wrap gap-2 pt-1">
            <a href="{{ route('frontend.seo.hub') }}" class="text-[11px] font-bold text-[#C96A2B] underline">Randevu rehberi</a>
            <a href="{{ route('frontend.hekimler') }}" class="text-[11px] font-bold text-[#C96A2B] underline">Tüm doktorlar</a>
            <a href="{{ route('frontend.blog.index') }}" class="text-[11px] font-bold text-[#C96A2B] underline">Sağlık blogu</a>
            @if($seoBransAd)
                @php $bransModel = \App\Models\Brans::where('ad', $seoBransAd)->first(); @endphp
                @if($bransModel?->slug)
                    <a href="{{ route('frontend.seo.brans', $bransModel->slug) }}" class="text-[11px] font-bold text-[#C96A2B] underline">{{ $seoBransAd }} rehberi</a>
                @endif
            @endif
        </div>
    </div>
</section>
