@extends('frontend.layouts.app')

@section('baslik', ($doktor->unvan ? $doktor->unvan . ' ' : '') . $doktor->ad_soyad . ' - Randevu Ajandam')

@section('meta_aciklama', Str::limit(strip_tags($doktor->biyografi ?? 'Uzman hekimimiz hakkında detaylı bilgi, randevu saatleri ve hizmetleri.'), 150))
@section('og_image', $doktor->profil_resmi ? asset($doktor->profil_resmi) : asset('assets/images/logo.png'))
@section('og_type', 'profile')

@section('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Physician",
  "name": "{{ ($doktor->unvan ? $doktor->unvan . ' ' : '') . $doktor->ad_soyad }}",
  "image": "{{ $doktor->profil_resmi ? asset($doktor->profil_resmi) : asset('assets/images/logo.png') }}",
  "medicalSpecialty": "{{ $doktor->uzmanlik_alani ?? 'Hekim' }}",
  "telephone": "{{ $doktor->telefon }}",
  "email": "{{ $doktor->e_posta }}",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "{{ $doktor->adres ?? 'Hekim Muayenehanesi' }}",
    "addressLocality": "{{ $doktor->ilce?->ad ?? '' }}",
    "addressRegion": "{{ $doktor->il?->ad ?? '' }}",
    "addressCountry": "TR"
  },
  "url": "{{ $doktor->profil_url }}"
  @if($doktor->ortalama_puan)
  ,"aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{{ $doktor->ortalama_puan }}",
    "reviewCount": "{{ $doktor->yorum_sayisi }}",
    "bestRating": "5",
    "worstRating": "1"
  }
  @endif
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Ana Sayfa",
      "item": "{{ url('/') }}"
    }
    @if($doktor->il)
    ,{
      "@type": "ListItem",
      "position": 2,
      "name": "{{ $doktor->il->ad }}",
      "item": "{{ route('frontend.il.liste', ['il_slug' => $doktor->il->slug]) }}"
    }
    @endif
    @if($doktor->il && $doktor->ilce)
    ,{
      "@type": "ListItem",
      "position": 3,
      "name": "{{ $doktor->ilce->ad }}",
      "item": "{{ route('frontend.ilce.liste', ['il_slug' => $doktor->il->slug, 'ilce_slug' => $doktor->ilce->slug]) }}"
    }
    @endif
    ,{
      "@type": "ListItem",
      "position": {{ ($doktor->il && $doktor->ilce) ? 4 : (($doktor->il) ? 3 : 2) }},
      "name": "{{ ($doktor->unvan ? $doktor->unvan . ' ' : '') . $doktor->ad_soyad }}",
      "item": "{{ $doktor->profil_url }}"
    }
  ]
}
</script>

@php
    $aktifFaqsForSchema = $doktor->faqs()->aktif()->orderBy('sira')->get();
@endphp
@if($aktifFaqsForSchema->isNotEmpty())
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    @foreach($aktifFaqsForSchema as $index => $faq)
    {
      "@type": "Question",
      "name": {!! json_encode($faq->soru) !!},
      "acceptedAnswer": {
        "@type": "Answer",
        "text": {!! json_encode(strip_tags($faq->cevap)) !!}
      }
    }{{ $index < $aktifFaqsForSchema->count() - 1 ? ',' : '' }}
    @endforeach
  ]
}
</script>
@endif

<!-- Leaflet Map CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<style>
    .scrollbar-none::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-none {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection

@section('icerik')
<section class="relative bg-[#FAFAFA] py-16 md:py-24 overflow-hidden min-h-[85vh]">
    <!-- Background Ambient Lights -->
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-[#C96A2B]/3 blur-[120px] pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-6 relative z-10">

        <!-- Back Navigation Link -->
        <div class="mb-8">
            <a href="{{ route('frontend.hekimler') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#6B7280] hover:text-[#C96A2B] transition-colors font-display uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                </svg>
                Hekim Listesine Dön
            </a>
        </div>

        <!-- Doctor Hero Profile Card -->
        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-8 md:p-10 shadow-[0_8px_30px_rgba(31,41,55,0.02)] mb-10 flex flex-col md:flex-row items-center md:items-start gap-8 relative overflow-hidden group">
            @php
                $kisaAd = '';
                if ($doktor->ad_soyad) {
                    $words = explode(' ', $doktor->ad_soyad);
                    $kisaAd = mb_strtoupper(mb_substr($words[0], 0, 1));
                    if (count($words) > 1) {
                        $kisaAd .= mb_strtoupper(mb_substr(end($words), 0, 1));
                    }
                } else {
                    $kisaAd = 'DR';
                }
            @endphp
            <!-- Big Initials Avatar / Profile Picture -->
            <div class="w-24 h-24 md:w-32 md:h-32 rounded-3xl overflow-hidden bg-gradient-to-br from-[#FFF7ED] to-[#FFFBEB] border border-[#E7B58A]/40 text-[#C96A2B] flex items-center justify-center font-extrabold font-display text-2xl md:text-4xl shadow-sm shrink-0 select-none">
                @if($doktor->profil_resmi)
                    <img src="{{ asset($doktor->profil_resmi) }}" alt="{{ $doktor->ad_soyad }}" class="w-full h-full object-cover">
                @else
                    {{ $kisaAd }}
                @endif
            </div>

            <!-- Header Info Details -->
            <div class="text-center md:text-left space-y-3 flex-1">
                <div class="flex flex-col md:flex-row md:items-center gap-2 justify-center md:justify-start">
                    <span class="inline-block self-center md:self-auto px-2.5 py-1 text-[9px] uppercase font-bold tracking-wider rounded-full bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30">
                        Bireysel Uzman
                    </span>
                    <span class="inline-block self-center md:self-auto px-2.5 py-1 text-[9px] uppercase font-bold tracking-wider rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Online Randevuya Açık
                    </span>
                </div>

                <div>
                    <h1 class="text-2xl md:text-4xl font-extrabold font-display text-[#111827] tracking-tight">
                        {{ $doktor->unvan ? $doktor->unvan . ' ' : '' }}{{ $doktor->ad_soyad }}
                    </h1>
                    <p class="text-sm font-semibold text-[#C96A2B] font-display uppercase tracking-wider mt-1">
                        {{ $doktor->uzmanlik_alani ?? 'Genel Branş Hekimi' }}
                    </p>
                    @if($doktor->ortalama_puan)
                        <div class="flex items-center gap-2 mt-2">
                            <div class="flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= round($doktor->ortalama_puan) ? 'text-[#C96A2B]' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-sm font-bold text-[#C96A2B] font-display">{{ $doktor->ortalama_puan }}</span>
                            <span class="text-xs text-[#6B7280]">({{ $doktor->yorum_sayisi }} yorum)</span>
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-center md:justify-start gap-y-2 gap-x-4 text-xs text-[#6B7280]">
                    @if($doktor->il_id)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#C96A2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25A7.5 7.5 0 0119.5 10.5z"></path>
                            </svg>
                            <span>{{ $doktor->il?->ad }}{{ $doktor->ilce?->ad ? ' / ' . $doktor->ilce->ad : '' }}</span>
                        </span>
                    @endif
                    @if($doktor->telefon)
                        <span class="text-slate-300 hidden md:inline">|</span>
                        <span class="flex items-center gap-1.5">
                            <strong>Tel:</strong> {{ $doktor->telefon }}
                        </span>
                    @endif
                    <span class="text-slate-300 hidden md:inline">|</span>
                    <span class="flex items-center gap-1.5">
                        <strong>E-Posta:</strong> {{ $doktor->e_posta }}
                    </span>
                </div>

                @if($doktor->instagram || $doktor->facebook || $doktor->twitter || $doktor->linkedin || $doktor->youtube)
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 pt-2">
                        @if($doktor->instagram)
                            <a href="https://instagram.com/{{ ltrim($doktor->instagram, '@') }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-slate-50 border border-slate-200 hover:border-[#C96A2B] hover:text-[#C96A2B] flex items-center justify-center transition-all duration-300 text-slate-500 shadow-sm" title="Instagram">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                            </a>
                        @endif
                        @if($doktor->facebook)
                            <a href="{{ str_starts_with($doktor->facebook, 'http') ? $doktor->facebook : 'https://facebook.com/' . ltrim($doktor->facebook, '@') }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-slate-50 border border-slate-200 hover:border-[#C96A2B] hover:text-[#C96A2B] flex items-center justify-center transition-all duration-300 text-slate-500 shadow-sm" title="Facebook">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                        @endif
                        @if($doktor->twitter)
                            <a href="https://x.com/{{ ltrim($doktor->twitter, '@') }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-slate-50 border border-slate-200 hover:border-[#C96A2B] hover:text-[#C96A2B] flex items-center justify-center transition-all duration-300 text-slate-500 shadow-sm" title="Twitter / X">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </a>
                        @endif
                        @if($doktor->linkedin)
                            <a href="{{ str_starts_with($doktor->linkedin, 'http') ? $doktor->linkedin : 'https://linkedin.com/in/' . $doktor->linkedin }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-slate-50 border border-slate-200 hover:border-[#C96A2B] hover:text-[#C96A2B] flex items-center justify-center transition-all duration-300 text-slate-500 shadow-sm" title="LinkedIn">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            </a>
                        @endif
                        @if($doktor->youtube)
                            <a href="{{ str_starts_with($doktor->youtube, 'http') ? $doktor->youtube : 'https://youtube.com/' . $doktor->youtube }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-slate-50 border border-slate-200 hover:border-[#C96A2B] hover:text-[#C96A2B] flex items-center justify-center transition-all duration-300 text-slate-500 shadow-sm" title="YouTube">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.163a3.003 3.003 0 00-2.11-2.11C19.518 3.545 12 3.545 12 3.545s-7.518 0-9.388.508a3.003 3.003 0 00-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 002.11 2.11c1.87.508 9.388.508 9.388.508s7.518 0 9.388-.508a3.002 3.002 0 002.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                        @endif

                    </div>
                @endif
            </div>

            <div class="absolute right-0 bottom-0 top-0 w-1/4 bg-gradient-to-l from-[#FFF7ED]/20 to-transparent pointer-events-none"></div>
        </div>

        {{-- Kimlik kartı ile sekmeler arasında: adım adım randevu sihirbazı --}}
        @include('frontend.hekimler.partials.randevu_wizard', ['doktor' => $doktor])

        <!-- Details: Hakkımda / Hizmetler / Blog vb. (tam genişlik) -->
        <div class="space-y-6">

                @php
                    $aktifFaqs = $doktor->faqs()->aktif()->orderBy('sira')->get();
                    $faqCount = $aktifFaqs->count();
                    $onayliYorumlar = $doktor->yorumlar()->onaylandi()->with('hasta')->latest()->take(10)->get();
                    $yorumCount = $onayliYorumlar->count();
                    $yayinEgitimler = $doktor->egitimler()->yayinda()->orderBy('sira')->orderByDesc('baslangic_at')->take(6)->get();
                    $egitimListeUrl = route('frontend.hekim.egitimler', [
                        'il_slug' => $doktor->il?->slug ?? 'il',
                        'ilce_slug' => $doktor->ilce?->slug ?? 'ilce',
                        'brans_slug' => $doktor->branslar->first()?->slug ?? 'hekim',
                        'doctor_slug' => $doktor->slug,
                    ]);
                @endphp

                <!-- Premium Tabs Navigation -->
                <div class="border-b border-[#E5E7EB] mb-6 flex overflow-x-auto scrollbar-none gap-2 pb-px shrink-0">
                    <button onclick="switchProfileTab('hakkimda')" id="tab-btn-hakkimda" class="profile-tab-btn whitespace-nowrap px-4 py-3.5 border-b-2 font-display text-xs font-bold transition-all duration-200 border-[#C96A2B] text-[#C96A2B]">
                        Hekim Hakkında
                    </button>
                    @if($doktor->hizmetler->isNotEmpty())
                        <button onclick="switchProfileTab('hizmetler')" id="tab-btn-hizmetler" class="profile-tab-btn whitespace-nowrap px-4 py-3.5 border-b-2 font-display text-xs font-bold transition-all duration-200 border-transparent text-[#4B5563] hover:text-[#111827] hover:border-slate-300">
                            Hizmet & Tedaviler
                        </button>
                    @endif
                    @if($yayinEgitimler->isNotEmpty())
                        <button onclick="switchProfileTab('egitimler')" id="tab-btn-egitimler" class="profile-tab-btn whitespace-nowrap px-4 py-3.5 border-b-2 font-display text-xs font-bold transition-all duration-200 border-transparent text-[#4B5563] hover:text-[#111827] hover:border-slate-300">
                            Eğitimler
                        </button>
                    @endif
                    @if($doktor->galeriler->isNotEmpty())
                        <button onclick="switchProfileTab('galeri')" id="tab-btn-galeri" class="profile-tab-btn whitespace-nowrap px-4 py-3.5 border-b-2 font-display text-xs font-bold transition-all duration-200 border-transparent text-[#4B5563] hover:text-[#111827] hover:border-slate-300">
                            Klinik Galeri
                        </button>
                    @endif
                    @if($doktor->bloglar->isNotEmpty())
                        <button onclick="switchProfileTab('bloglar')" id="tab-btn-bloglar" class="profile-tab-btn whitespace-nowrap px-4 py-3.5 border-b-2 font-display text-xs font-bold transition-all duration-200 border-transparent text-[#4B5563] hover:text-[#111827] hover:border-slate-300">
                            Blog Yazıları
                        </button>
                    @endif
                    @if($faqCount > 0 || $yorumCount > 0)
                        <button onclick="switchProfileTab('yorumlar')" id="tab-btn-yorumlar" class="profile-tab-btn whitespace-nowrap px-4 py-3.5 border-b-2 font-display text-xs font-bold transition-all duration-200 border-transparent text-[#4B5563] hover:text-[#111827] hover:border-slate-300">
                            Yorumlar & SSS
                        </button>
                    @endif
                </div>

                <!-- Tab 1: Hakkında -->
                <div id="tab-content-hakkimda" class="profile-tab-content space-y-8">
                    <!-- Biography -->
                    <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
                        <h3 class="text-lg font-bold font-display text-[#111827] border-b border-slate-100 pb-3">Hekim Hakkında</h3>
                        <div class="text-xs text-[#4B5563] leading-relaxed space-y-3">
                            @if($doktor->biyografi)
                                {!! $doktor->biyografi !!}
                            @else
                                <p>Uzman hekim hakkında detaylı biyografi bilgisi henüz eklenmemiştir.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Education -->
                    @if($doktor->mezuniyet && is_array($doktor->mezuniyet) && count($doktor->mezuniyet) > 0)
                        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
                            <h3 class="text-lg font-bold font-display text-[#111827] border-b border-slate-100 pb-3">Eğitim ve Mezuniyet</h3>
                            <div class="space-y-4">
                                @foreach($doktor->mezuniyet as $mezuniyet)
                                    <div class="flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#C96A2B] flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.263 15.918a9 9 0 0012.484 0m-12.484 0a9 9 0 0112.484 0M3.75 6.115V18a2.25 2.25 0 002.25 2.25h12A2.25 2.25 0 0020.25 18V6.115M3.75 6.115l8.529 3.529a1.5 1.5 0 001.092 0l8.529-3.529M3.75 6.115L12 3m0 0l8.25 3.115M12 3v13.5"></path>
                                            </svg>
                                        </div>
                                        <div class="space-y-1">
                                            <h4 class="text-xs font-bold text-[#111827] font-display">Tıp Fakültesi / Mezuniyet Bilgisi</h4>
                                            <p class="text-xs text-[#6B7280] font-medium leading-relaxed">{{ $mezuniyet }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Services details / Map -->
                    <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
                        <h3 class="text-lg font-bold font-display text-[#111827] border-b border-slate-100 pb-3">Çalışma Konumu</h3>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#C96A2B] flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25A7.5 7.5 0 0119.5 10.5z"></path>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-xs font-bold text-[#111827] font-display">Muayenehane Adresi</h4>
                                <p class="text-xs text-[#6B7280] leading-relaxed">
                                    <span class="font-semibold text-[#111827]">Adres:</span> {{ $doktor->adres ?? 'Belirtilmedi' }}
                                </p>
                            </div>
                        </div>
                        @if($doktor->enlem && $doktor->boylam)
                            <div class="flex items-center justify-between mt-4">
                                <span class="text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Görünüm Seçenekleri</span>
                                <div class="flex gap-1 bg-slate-100 p-0.5 rounded-lg border border-slate-200">
                                    <button type="button" id="btnShowMap" onclick="toggleMapMode('map')" class="px-2.5 py-1 text-[10px] font-bold rounded-md transition-all duration-200 bg-white text-[#C96A2B] shadow-sm font-display uppercase tracking-wider">
                                        Harita
                                    </button>
                                    <button type="button" id="btnShowStreet" onclick="toggleMapMode('street')" class="px-2.5 py-1 text-[10px] font-bold rounded-md transition-all duration-200 text-[#4B5563] hover:text-[#111827] font-display uppercase tracking-wider">
                                        Sokak Görünümü
                                    </button>
                                </div>
                            </div>
                            <div class="relative w-full h-64 rounded-2xl overflow-hidden border border-[#E5E7EB] shadow-sm mt-2 z-10">
                                <!-- Map (default) -->
                                <div id="detailMap" class="w-full h-full"></div>
                                <!-- Street View -->
                                <div id="detailStreetView" class="w-full h-full bg-slate-100 hidden">
                                    <iframe
                                        id="streetViewIframe"
                                        width="100%"
                                        height="100%"
                                        style="border:0;"
                                        allow="accelerometer *; gyroscope *; magnetometer *; xr-spatial-tracking *"
                                        allowfullscreen=""
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                        src="https://maps.google.com/maps?q=&layer=c&cbll={{ $doktor->enlem }},{{ $doktor->boylam }}&cbp=11,0,0,0,0&source=embed&output=svembed">
                                    </iframe>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tab 2: Hizmetler -->
                @if($doktor->hizmetler->isNotEmpty())
                    <div id="tab-content-hizmetler" class="profile-tab-content space-y-8 hidden">
                        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
                            <h3 class="text-lg font-bold font-display text-[#111827] border-b border-slate-100 pb-3">Hizmet ve Tedaviler</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($doktor->hizmetler as $hizmet)
                                    @if($hizmet->aktif_mi)
                                        <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/30 flex flex-col justify-between hover:border-[#E7B58A]/60 hover:shadow-sm transition-all duration-300 relative group/hizmet">
                                            <div class="flex items-start gap-4">
                                                @if($hizmet->resim_url)
                                                    <img src="{{ $hizmet->resim_url }}" alt="{{ $hizmet->ad }}" class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-2xl border border-slate-200/60 shadow-sm shrink-0">
                                                @else
                                                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center shrink-0 border border-[#E7B58A]/20">
                                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div class="space-y-0.5 flex-1 min-w-0">
                                                    <h4 class="text-xs font-bold text-[#111827] font-display truncate group-hover/hizmet:text-[#C96A2B] transition-colors">
                                                        <a href="{{ $hizmet->url }}">
                                                            {{ $hizmet->ad }}
                                                        </a>
                                                    </h4>
                                                    <p class="text-[10px] font-semibold text-[#C96A2B] font-display uppercase tracking-wider">{{ $hizmet->sure }} Dakika Süre</p>
                                                    @if($hizmet->aciklama)
                                                        <div class="text-[11px] text-[#6B7280] leading-relaxed mt-1.5 line-clamp-2">
                                                            {{ strip_tags($hizmet->aciklama) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="mt-4 pt-3 border-t border-slate-100/60 flex items-center justify-end">
                                                <a href="{{ $hizmet->url }}" class="inline-flex items-center gap-1.5 text-[10px] font-bold text-[#C96A2B] hover:text-[#B55A20] font-display uppercase tracking-wider">
                                                    Detayları İncele
                                                    <svg class="w-3.5 h-3.5 transition-transform group-hover/hizmet:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Tab: Eğitimler -->
                @if($yayinEgitimler->isNotEmpty())
                    <div id="tab-content-egitimler" class="profile-tab-content space-y-8 hidden">
                        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h3 class="text-lg font-bold font-display text-[#111827]">Eğitimler</h3>
                                <span class="text-xs text-[#C96A2B] font-bold font-display uppercase tracking-wider bg-[#FFF7ED] px-2.5 py-1 rounded-full">
                                    {{ $yayinEgitimler->count() }} Eğitim
                                </span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($yayinEgitimler as $egitim)
                                    <a href="{{ $egitim->url }}" class="p-4 rounded-2xl border border-slate-100 bg-slate-50/30 flex flex-col justify-between hover:border-[#E7B58A]/60 hover:shadow-sm transition-all duration-300 relative group/egitim block">
                                        <div class="flex items-start gap-4">
                                            @if($egitim->kapak)
                                                <img src="{{ asset('storage/'.$egitim->kapak) }}" alt="{{ $egitim->baslik }}" class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-2xl border border-slate-200/60 shadow-sm shrink-0">
                                            @else
                                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center shrink-0 border border-[#E7B58A]/20">
                                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                            <div class="space-y-0.5 flex-1 min-w-0">
                                                @if($egitim->tip)
                                                    <p class="text-[10px] font-semibold text-[#C96A2B] font-display uppercase tracking-wider">{{ $egitim->tip }}</p>
                                                @endif
                                                <h4 class="text-xs font-bold text-[#111827] font-display line-clamp-2 group-hover/egitim:text-[#C96A2B] transition-colors">
                                                    {{ $egitim->baslik }}
                                                </h4>
                                                @if($egitim->ozet)
                                                    <div class="text-[11px] text-[#6B7280] leading-relaxed mt-1.5 line-clamp-2">
                                                        {{ strip_tags($egitim->ozet) }}
                                                    </div>
                                                @endif
                                                <p class="text-[10px] text-[#6B7280] font-medium mt-1.5">
                                                    {{ $egitim->baslangic_at?->format('d.m.Y') ?? 'Tarih yakında' }}
                                                    @if($egitim->fiyat !== null && (float)$egitim->fiyat > 0)
                                                        · {{ number_format((float)$egitim->fiyat, 0, ',', '.') }} ₺
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-4 pt-3 border-t border-slate-100/60 flex items-center justify-end">
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-[#C96A2B] group-hover/egitim:text-[#B55A20] font-display uppercase tracking-wider">
                                                Detayları İncele
                                                <svg class="w-3.5 h-3.5 transition-transform group-hover/egitim:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path>
                                                </svg>
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            @if($egitimListeUrl)
                                <div class="pt-2 text-center">
                                    <a href="{{ $egitimListeUrl }}" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-[#6B7280] hover:text-[#C96A2B] font-display transition-colors">
                                        Tüm eğitimleri gör
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path>
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Tab 3: Galeri -->
                @if($doktor->galeriler->isNotEmpty())
                    <div id="tab-content-galeri" class="profile-tab-content space-y-8 hidden">
                        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h3 class="text-lg font-bold font-display text-[#111827]">Klinik / Muayenehane Fotoğrafları</h3>
                                <span class="text-xs text-[#C96A2B] font-bold font-display uppercase tracking-wider bg-[#FFF7ED] px-2.5 py-1 rounded-full">
                                    {{ $doktor->galeriler->count() }} Fotoğraf
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                @foreach($doktor->galeriler as $index => $galeri)
                                    <div class="group relative aspect-[4/3] rounded-2xl overflow-hidden border border-slate-100 shadow-sm cursor-pointer bg-slate-50"
                                         onclick="openLightbox({{ $index }})">
                                        <img src="{{ asset($galeri->resim_yolu) }}" alt="{{ $galeri->baslik ?? 'Hekim Fotoğrafı' }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                                        @if($galeri->baslik)
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3.5">
                                                <span class="text-[10px] text-white font-medium truncate font-sans w-full">{{ $galeri->baslik }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Custom Premium Lightbox Modal -->
                        <div id="galleryLightbox"
                             class="gallery-lightbox fixed inset-0 z-[150] flex-col justify-between p-4 sm:p-6 select-none"
                             role="dialog"
                             aria-modal="true"
                             aria-label="Galeri görüntüleyici"
                             style="display: none;">
                            <div class="gallery-lightbox-backdrop absolute inset-0 bg-slate-950/95 backdrop-blur-md" onclick="closeLightbox()"></div>

                            <!-- Header -->
                            <div class="relative z-10 flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-400 font-display uppercase tracking-widest bg-white/5 border border-white/10 px-3 py-1.5 rounded-full">
                                    <span id="lightboxIndex">1</span> / {{ $doktor->galeriler->count() }}
                                </span>
                                <button type="button"
                                        onclick="closeLightbox()"
                                        class="inline-flex items-center gap-2 text-white bg-white/10 hover:bg-white/20 border border-white/15 px-3.5 py-2 rounded-2xl transition-all duration-200 cursor-pointer outline-none hover:scale-105 active:scale-95"
                                        title="Kapat (Esc)"
                                        aria-label="Kapat">
                                    <span class="text-[11px] font-bold font-display uppercase tracking-wider hidden sm:inline">Kapat</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Image Display Container -->
                            <div class="relative z-10 flex-1 flex items-center justify-center my-3 sm:my-4 min-h-0">
                                <!-- Prev Button -->
                                <button type="button" onclick="prevLightboxImage()" class="absolute left-0 md:left-4 p-3 rounded-2xl bg-white/10 border border-white/15 text-white hover:bg-white/20 transition-all duration-200 cursor-pointer outline-none select-none z-10 hover:scale-105 active:scale-95" title="Önceki">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                    </svg>
                                </button>

                                <!-- Image -->
                                <div class="gallery-lightbox-frame max-w-[92%] max-h-[75vh] flex items-center justify-center overflow-hidden rounded-2xl border border-white/10 shadow-2xl">
                                    <img id="lightboxImage" src="" alt="" class="gallery-lightbox-img max-w-full max-h-[75vh] object-contain select-none pointer-events-none">
                                </div>

                                <!-- Next Button -->
                                <button type="button" onclick="nextLightboxImage()" class="absolute right-0 md:right-4 p-3 rounded-2xl bg-white/10 border border-white/15 text-white hover:bg-white/20 transition-all duration-200 cursor-pointer outline-none select-none z-10 hover:scale-105 active:scale-95" title="Sonraki">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Footer Caption -->
                            <div class="relative z-10 text-center pb-1">
                                <p id="lightboxCaption" class="text-sm font-medium text-white/90 max-w-xl mx-auto truncate font-sans px-4"></p>
                            </div>
                        </div>

                        <style>
                            .gallery-lightbox {
                                opacity: 0;
                                pointer-events: none;
                                transition: opacity 0.35s cubic-bezier(0.22, 1, 0.36, 1);
                            }
                            .gallery-lightbox.is-open {
                                opacity: 1;
                                pointer-events: auto;
                            }
                            .gallery-lightbox-frame {
                                transform: scale(0.92) translateY(12px);
                                opacity: 0;
                                transition: transform 0.4s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.35s ease;
                            }
                            .gallery-lightbox.is-open .gallery-lightbox-frame {
                                transform: scale(1) translateY(0);
                                opacity: 1;
                            }
                            .gallery-lightbox-img {
                                transition: opacity 0.28s ease, transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
                            }
                            .gallery-lightbox-img.is-switching {
                                opacity: 0;
                                transform: scale(0.97);
                            }
                        </style>

                        <!-- Lightbox Script -->
                        <script>
                            const galleryImages = [
                                @foreach($doktor->galeriler as $galeri)
                                    {
                                        src: @json(asset($galeri->resim_yolu)),
                                        caption: @json($galeri->baslik)
                                    },
                                @endforeach
                            ];

                            let currentImgIndex = 0;
                            let lightboxClosing = false;
                            const lightboxEl = document.getElementById('galleryLightbox');
                            const lightboxImg = document.getElementById('lightboxImage');
                            const lightboxCap = document.getElementById('lightboxCaption');
                            const lightboxIdx = document.getElementById('lightboxIndex');

                            function openLightbox(index) {
                                currentImgIndex = index;
                                lightboxClosing = false;
                                updateLightboxImage(false);
                                lightboxEl.style.display = 'flex';
                                // force reflow for enter animation
                                void lightboxEl.offsetWidth;
                                lightboxEl.classList.add('is-open');
                                document.body.style.overflow = 'hidden';
                            }

                            function closeLightbox() {
                                if (lightboxClosing || !lightboxEl.classList.contains('is-open')) return;
                                lightboxClosing = true;
                                lightboxEl.classList.remove('is-open');
                                setTimeout(function() {
                                    lightboxEl.style.display = 'none';
                                    document.body.style.overflow = '';
                                    lightboxClosing = false;
                                }, 350);
                            }

                            function updateLightboxImage(animate) {
                                const imgData = galleryImages[currentImgIndex];
                                if (!imgData) return;

                                const apply = function() {
                                    lightboxImg.src = imgData.src;
                                    lightboxCap.innerText = imgData.caption || 'Klinik Fotoğrafı';
                                    lightboxIdx.innerText = currentImgIndex + 1;
                                    lightboxImg.classList.remove('is-switching');
                                };

                                if (animate) {
                                    lightboxImg.classList.add('is-switching');
                                    setTimeout(apply, 160);
                                } else {
                                    apply();
                                }
                            }

                            function nextLightboxImage() {
                                currentImgIndex = (currentImgIndex + 1) % galleryImages.length;
                                updateLightboxImage(true);
                            }

                            function prevLightboxImage() {
                                currentImgIndex = (currentImgIndex - 1 + galleryImages.length) % galleryImages.length;
                                updateLightboxImage(true);
                            }

                            document.addEventListener('keydown', function(e) {
                                if (!lightboxEl.classList.contains('is-open')) return;
                                if (e.key === 'Escape') closeLightbox();
                                if (e.key === 'ArrowRight') nextLightboxImage();
                                if (e.key === 'ArrowLeft') prevLightboxImage();
                            });

                            // Expose for inline onclick
                            window.openLightbox = openLightbox;
                            window.closeLightbox = closeLightbox;
                            window.nextLightboxImage = nextLightboxImage;
                            window.prevLightboxImage = prevLightboxImage;
                        </script>
                    </div>
                @endif

                <!-- Tab 4: Bloglar -->
                @if($doktor->bloglar->isNotEmpty())
                    <div id="tab-content-bloglar" class="profile-tab-content space-y-8 hidden">
                        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h3 class="text-lg font-bold font-display text-[#111827]">Yayınladığı Blog Yazıları</h3>
                                <span class="text-xs text-[#C96A2B] font-bold font-display uppercase tracking-wider bg-[#FFF7ED] px-2.5 py-1 rounded-full">
                                    {{ $doktor->bloglar->count() }} Yazı
                                </span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                @foreach($doktor->bloglar as $blog)
                                    <div class="bg-white border border-[#E5E7EB] rounded-2xl overflow-hidden hover:shadow-[0_4px_24px_rgba(31,41,55,0.06)] hover:-translate-y-0.5 transition-all duration-300 flex flex-col group">
                                        @if($blog->resim)
                                            <div class="w-full h-36 overflow-hidden relative shrink-0">
                                                <img src="{{ asset($blog->resim) }}" alt="{{ $blog->baslik }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                            </div>
                                        @else
                                            <div class="w-full h-36 bg-slate-50 flex items-center justify-center border-b border-[#E5E7EB] shrink-0 text-slate-400">
                                                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12"></path>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="p-5 flex-1 flex flex-col justify-between">
                                            <div class="space-y-2">
                                                <span class="block text-[10px] font-bold text-[#C96A2B] uppercase tracking-wider font-display">
                                                    {{ $blog->created_at->translatedFormat('d M Y') }}
                                                </span>
                                                <h4 class="text-sm font-bold font-display text-[#111827] leading-snug hover:text-[#C96A2B] transition-colors line-clamp-2">
                                                    <a href="{{ $blog->url }}">
                                                        {{ $blog->baslik }}
                                                    </a>
                                                </h4>
                                                <p class="text-xs text-[#6B7280] leading-relaxed line-clamp-3">
                                                    {{ Str::limit(strip_tags($blog->icerik), 120) }}
                                                </p>
                                            </div>
                                            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-[11px] text-[#6B7280]">
                                                <span class="flex items-center gap-1 font-semibold font-display">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                    {{ $blog->okunma_sayisi }} Okunma
                                                </span>
                                                <a href="{{ $blog->url }}" class="font-bold text-[#C96A2B] hover:text-[#B55A20] font-display flex items-center gap-0.5">
                                                    Oku
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Tab 5: Yorumlar & SSS -->
                @if($faqCount > 0 || $yorumCount > 0)
                    <div id="tab-content-yorumlar" class="profile-tab-content space-y-8 hidden">
                        <!-- Sıkça Sorulan Sorular (SSS) Section -->
                        @if($aktifFaqs->isNotEmpty())
                            <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <h3 class="text-lg font-bold font-display text-[#111827]">Sıkça Sorulan Sorular</h3>
                                    <span class="text-xs text-[#C96A2B] font-bold font-display uppercase tracking-wider bg-[#FFF7ED] px-2.5 py-1 rounded-full">
                                        {{ $aktifFaqs->count() }} Soru
                                    </span>
                                </div>
                                <div class="space-y-4">
                                    @foreach($aktifFaqs as $faq)
                                        <details class="group border border-slate-100 rounded-2xl p-4 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden hover:border-[#E7B58A]/30">
                                            <summary class="flex items-center justify-between cursor-pointer focus:outline-none list-none">
                                                <h4 class="text-xs font-bold text-[#111827] font-display pr-4 select-none">
                                                    {{ $faq->soru }}
                                                </h4>
                                                <span class="shrink-0 transition duration-300 group-open:-rotate-180 text-[#C96A2B]">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </span>
                                            </summary>
                                            <div class="mt-3 text-xs text-[#6B7280] leading-relaxed select-none border-t border-slate-50 pt-3">
                                                {!! nl2br(e($faq->cevap)) !!}
                                            </div>
                                        </details>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Patient Reviews Section -->
                        @if($onayliYorumlar->isNotEmpty())
                            <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <h3 class="text-lg font-bold font-display text-[#111827]">Hasta Yorumları</h3>
                                    <div class="flex items-center gap-2">
                                        @if($doktor->ortalama_puan)
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4 text-[#C96A2B]" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path>
                                                </svg>
                                                <span class="text-sm font-bold text-[#C96A2B] font-display">{{ $doktor->ortalama_puan }}</span>
                                            </div>
                                        @endif
                                        <span class="text-xs text-[#6B7280] font-bold font-display bg-slate-50 px-2.5 py-1 rounded-full">
                                            {{ $doktor->yorum_sayisi }} Yorum
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    @foreach($onayliYorumlar as $yorum)
                                        <div class="border border-slate-100 rounded-2xl p-4 space-y-3 hover:border-[#E7B58A]/40 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-8 h-8 rounded-full bg-[#FFF7ED] border border-[#E7B58A]/30 text-[#C96A2B] flex items-center justify-center text-[10px] font-bold font-display">
                                                        {{ mb_strtoupper(mb_substr($yorum->hasta->ad, 0, 1)) }}{{ mb_strtoupper(mb_substr($yorum->hasta->soyad, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-bold text-[#111827] font-display">{{ $yorum->hasta->ad }} {{ mb_substr($yorum->hasta->soyad, 0, 1) }}.</p>
                                                        <p class="text-[10px] text-[#6B7280]">{{ $yorum->created_at->translatedFormat('d M Y') }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-0.5">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="w-3.5 h-3.5 {{ $i <= $yorum->puan ? 'text-[#C96A2B]' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path>
                                                        </svg>
                                                    @endfor
                                                </div>
                                            </div>
                                            <p class="text-xs text-[#4B5563] leading-relaxed">{{ $yorum->yorum }}</p>
                                            @if($yorum->doktor_yaniti)
                                                <div class="bg-[#FFF7ED] border border-[#E7B58A]/20 rounded-xl p-3 ml-6">
                                                    <p class="text-[10px] font-bold text-[#C96A2B] uppercase tracking-wider font-display mb-1">Hekim Yanıtı</p>
                                                    <p class="text-xs text-[#4B5563] leading-relaxed">{{ $yorum->doktor_yaniti }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

        </div>

    </div>
</section>

<!-- Simulated Booking Success Modal -->
<div id="bookingDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
    <div id="bookingDetailModalContainer" class="bg-white rounded-2xl border border-[#E5E7EB] shadow-2xl max-w-sm w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]">
        <!-- Modal Body -->
        <div class="p-6 text-center space-y-4 overflow-y-auto flex-1">
            <!-- Success icon -->
            <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center mx-auto animate-bounce shrink-0">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                </svg>
            </div>

            <h3 class="text-lg font-bold font-display text-[#111827]">Talebiniz Alındı!</h3>
            <div class="text-xs text-[#6B7280] leading-relaxed space-y-2">
                <p>Sayın <strong class="text-[#111827]">{{ $doktor->unvan }} {{ $doktor->ad_soyad }}</strong> ile planlanan randevu talebiniz başarıyla simüle edildi.</p>
                <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl text-left space-y-1 mt-3">
                    <p class="font-bold text-[10px] text-gray-500 uppercase tracking-wider font-display border-b border-slate-200 pb-1 mb-2">Talep Özet Bilgileri</p>
                    <p><strong>Hasta Adı:</strong> <span id="summaryName" class="text-[#111827]"></span></p>
                    <p><strong>Tarih:</strong> <span id="summaryDate" class="text-[#111827]"></span></p>
                    <p><strong>Hekim Branşı:</strong> <span class="text-[#111827]">{{ $doktor->uzmanlik_alani }}</span></p>
                </div>
                <p class="text-[10px] text-[#C96A2B] font-semibold mt-2.5">Talep hekimin yönetim paneline iletilmiştir.</p>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 bg-slate-50 border-t border-[#E5E7EB] text-center shrink-0">
            <button onclick="kapatBookingModal()" class="w-full py-2.5 rounded-xl bg-[#1F2937] hover:bg-[#111827] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display">
                Kapat
            </button>
        </div>
    </div>
</div>

<script>
    function gonderRandevuTalebi(event) {
        event.preventDefault();

        const nameVal = document.getElementById('p_ad').value;
        const dateVal = document.getElementById('p_tarih').value;

        // Formatting date
        let formattedDate = dateVal;
        if (dateVal) {
            const parts = dateVal.split('-');
            if(parts.length === 3) {
                formattedDate = `${parts[2]}.${parts[1]}.${parts[0]}`;
            }
        }

        const modal = document.getElementById('bookingDetailModal');
        const container = document.getElementById('bookingDetailModalContainer');
        const summaryName = document.getElementById('summaryName');
        const summaryDate = document.getElementById('summaryDate');

        if(modal && container && summaryName && summaryDate) {
            summaryName.innerText = nameVal;
            summaryDate.innerText = formattedDate;

            modal.classList.remove('hidden');
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 50);
        }
    }

    function kapatBookingModal() {
        const modal = document.getElementById('bookingDetailModal');
        const container = document.getElementById('bookingDetailModalContainer');

        if(modal && container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                const f = document.getElementById('randevuForm');
                if (f) f.reset();
            }, 300);
        }
    }

    // Detail page map initialization
    var detailMap = null;
    function initDetailMap() {
        if (detailMap) return;
        if (document.getElementById('detailMap')) {
            var lat = {{ $doktor->enlem ?? 0 }};
            var lng = {{ $doktor->boylam ?? 0 }};
            detailMap = L.map('detailMap', {
                scrollWheelZoom: false
            }).setView([lat, lng], 15);

            L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                attribution: '&copy; <a href="https://maps.google.com">Google Maps</a>'
            }).addTo(detailMap);

            var title = @json(($doktor->unvan ? $doktor->unvan . ' ' : '') . $doktor->ad_soyad);
            L.marker([lat, lng]).addTo(detailMap)
                .bindPopup('<div class="text-xs font-bold font-display">' + title + '</div><div class="text-[10px] text-gray-500 mt-1">Muayenehane</div>')
                .openPopup();
        }
    }

    window.toggleMapMode = function(mode) {
        const mapEl = document.getElementById('detailMap');
        const streetEl = document.getElementById('detailStreetView');
        const btnMap = document.getElementById('btnShowMap');
        const btnStreet = document.getElementById('btnShowStreet');
        if (!mapEl || !streetEl) return;

        if (mode === 'map') {
            streetEl.classList.add('hidden');
            mapEl.classList.remove('hidden');

            if (btnMap) btnMap.className = "px-2.5 py-1 text-[10px] font-bold rounded-md transition-all duration-200 bg-white text-[#C96A2B] shadow-sm font-display uppercase tracking-wider";
            if (btnStreet) btnStreet.className = "px-2.5 py-1 text-[10px] font-bold rounded-md transition-all duration-200 text-[#4B5563] hover:text-[#111827] font-display uppercase tracking-wider";

            initDetailMap();
            if (detailMap) {
                setTimeout(function() {
                    detailMap.invalidateSize();
                }, 100);
            }
        } else {
            mapEl.classList.add('hidden');
            streetEl.classList.remove('hidden');

            if (btnMap) btnMap.className = "px-2.5 py-1 text-[10px] font-bold rounded-md transition-all duration-200 text-[#4B5563] hover:text-[#111827] font-display uppercase tracking-wider";
            if (btnStreet) btnStreet.className = "px-2.5 py-1 text-[10px] font-bold rounded-md transition-all duration-200 bg-white text-[#C96A2B] shadow-sm font-display uppercase tracking-wider";
        }
    };

    // Close on overlay click + default map view
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('bookingDetailModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    kapatBookingModal();
                }
            });
        }
        // Varsayılan: harita (sokak görünümü değil)
        if (document.getElementById('detailMap')) {
            toggleMapMode('map');
        }
    });

    window.switchProfileTab = function(tabId) {
        // Hide all tab contents
        document.querySelectorAll('.profile-tab-content').forEach(el => {
            el.classList.add('hidden');
        });
        
        // Show target tab content
        const targetContent = document.getElementById('tab-content-' + tabId);
        if (targetContent) {
            targetContent.classList.remove('hidden');
        }
        
        // Reset all buttons styling
        document.querySelectorAll('.profile-tab-btn').forEach(btn => {
            btn.className = "profile-tab-btn whitespace-nowrap px-4 py-3.5 border-b-2 font-display text-xs font-bold transition-all duration-200 border-transparent text-[#4B5563] hover:text-[#111827] hover:border-slate-300";
        });
        
        // Apply active styling to clicked button
        const activeBtn = document.getElementById('tab-btn-' + tabId);
        if (activeBtn) {
            activeBtn.className = "profile-tab-btn whitespace-nowrap px-4 py-3.5 border-b-2 font-display text-xs font-bold transition-all duration-200 border-[#C96A2B] text-[#C96A2B]";
        }

        // If the map is in the active tab (Hakkımda) and we switched back to Hakkımda, we should invalidate size
        if (tabId === 'hakkimda' && detailMap) {
            setTimeout(function() {
                detailMap.invalidateSize();
            }, 100);
        }
    };

</script>
@endsection
