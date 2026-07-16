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

        <!-- Details & Booking Layout Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Column: Bio and Education -->
            <div class="lg:col-span-2 space-y-6">

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
                        <a href="{{ $egitimListeUrl }}" class="whitespace-nowrap px-4 py-3.5 border-b-2 font-display text-xs font-bold border-transparent text-[#4B5563] hover:text-[#C96A2B] hover:border-slate-300">
                            Eğitimler
                        </a>
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
                                    <button id="btnShowStreet" onclick="toggleMapMode('street')" class="px-2.5 py-1 text-[10px] font-bold rounded-md transition-all duration-200 bg-white text-[#C96A2B] shadow-sm font-display uppercase tracking-wider">
                                        Sokak Görünümü
                                    </button>
                                    <button id="btnShowMap" onclick="toggleMapMode('map')" class="px-2.5 py-1 text-[10px] font-bold rounded-md transition-all duration-200 text-[#4B5563] hover:text-[#111827] font-display uppercase tracking-wider">
                                        Harita
                                    </button>
                                </div>
                            </div>
                            <div class="relative w-full h-64 rounded-2xl overflow-hidden border border-[#E5E7EB] shadow-sm mt-2 z-10">
                                <!-- Street View Container -->
                                <div id="detailStreetView" class="w-full h-full bg-slate-100">
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
                                <!-- Map Container -->
                                <div id="detailMap" class="w-full h-full hidden"></div>
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
                                                @if($hizmet->resim)
                                                    <img src="{{ asset($hizmet->resim) }}" alt="{{ $hizmet->ad }}" class="w-14 h-14 object-cover rounded-xl border border-slate-200/60 shadow-sm shrink-0">
                                                @else
                                                    <div class="w-14 h-14 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center shrink-0 border border-[#E7B58A]/20">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                        <div id="galleryLightbox" class="fixed inset-0 z-[150] bg-slate-950/95 backdrop-blur-md hidden flex-col justify-between p-6 select-none transition-opacity duration-300">
                            <!-- Header -->
                            <div class="flex justify-between items-center z-10">
                                <span class="text-xs font-bold text-slate-400 font-display uppercase tracking-widest"><span id="lightboxIndex">1</span> / {{ $doktor->galeriler->count() }}</span>
                                <button onclick="closeLightbox()" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/10 transition-colors cursor-pointer outline-none">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Image Display Container -->
                            <div class="flex-1 flex items-center justify-center relative my-4">
                                <!-- Prev Button -->
                                <button onclick="prevLightboxImage()" class="absolute left-0 md:left-4 p-3 rounded-2xl bg-white/5 border border-white/10 text-white hover:bg-white/15 transition-all cursor-pointer outline-none select-none z-10" title="Önceki">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                    </svg>
                                </button>

                                <!-- Image -->
                                <div class="max-w-[90%] max-h-[75vh] flex items-center justify-center overflow-hidden rounded-2xl border border-white/5 shadow-2xl">
                                    <img id="lightboxImage" src="" alt="" class="max-w-full max-h-[75vh] object-contain select-none pointer-events-none">
                                </div>

                                <!-- Next Button -->
                                <button onclick="nextLightboxImage()" class="absolute right-0 md:right-4 p-3 rounded-2xl bg-white/5 border border-white/10 text-white hover:bg-white/15 transition-all cursor-pointer outline-none select-none z-10" title="Sonraki">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Footer Caption -->
                            <div class="text-center z-10">
                                <p id="lightboxCaption" class="text-sm font-medium text-white max-w-xl mx-auto truncate font-sans px-4"></p>
                            </div>
                        </div>

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
                            const lightboxEl = document.getElementById('galleryLightbox');
                            const lightboxImg = document.getElementById('lightboxImage');
                            const lightboxCap = document.getElementById('lightboxCaption');
                            const lightboxIdx = document.getElementById('lightboxIndex');

                            function openLightbox(index) {
                                currentImgIndex = index;
                                updateLightboxImage();
                                lightboxEl.classList.remove('hidden');
                                lightboxEl.classList.add('flex');
                                document.body.style.overflow = 'hidden'; // Lock body scroll
                            }

                            function closeLightbox() {
                                lightboxEl.classList.add('hidden');
                                lightboxEl.classList.remove('flex');
                                document.body.style.overflow = ''; // Unlock body scroll
                            }

                            function updateLightboxImage() {
                                const imgData = galleryImages[currentImgIndex];
                                if (imgData) {
                                    lightboxImg.src = imgData.src;
                                    lightboxCap.innerText = imgData.caption || 'Klinik Fotoğrafı';
                                    lightboxIdx.innerText = currentImgIndex + 1;
                                }
                            }

                            function nextLightboxImage() {
                                currentImgIndex = (currentImgIndex + 1) % galleryImages.length;
                                updateLightboxImage();
                            }

                            function prevLightboxImage() {
                                currentImgIndex = (currentImgIndex - 1 + galleryImages.length) % galleryImages.length;
                                updateLightboxImage();
                            }

                            // Close lightbox on Escape key
                            document.addEventListener('keydown', function(e) {
                                if (!lightboxEl.classList.contains('hidden')) {
                                    if (e.key === 'Escape') closeLightbox();
                                    if (e.key === 'ArrowRight') nextLightboxImage();
                                    if (e.key === 'ArrowLeft') prevLightboxImage();
                                }
                            });
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

            <!-- Right Column: Booking Form and Timetable -->
            <div class="space-y-8">

                @if($doktor->randevuya_acik_mi)
                    @if(Auth::guard('hasta')->check())
                        <!-- Online Booking Form (Real) -->
                        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-md relative overflow-hidden">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display border-b border-slate-100 pb-3.5 mb-4">
                                Online Randevu Planla
                            </h3>

                            @if(session('basarili'))
                                <div class="p-3 mb-4 bg-emerald-50 border border-emerald-100 rounded-xl text-[11px] text-emerald-700 font-medium">
                                    {{ session('basarili') }}
                                </div>
                            @endif

                            @if(session('hata'))
                                <div class="p-3 mb-4 bg-red-50 border border-red-100 rounded-xl text-[11px] text-red-700 font-medium">
                                    {{ session('hata') }}
                                </div>
                            @endif

                            <form action="{{ route('frontend.hasta.randevu.kaydet') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="doktor_id" value="{{ $doktor->id }}">

                                <!-- Select Service -->
                                @if($doktor->hizmetler->isNotEmpty())
                                    <div class="space-y-1">
                                        <label for="p_hizmet" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Almak İstediğiniz Hizmet</label>
                                        <select id="p_hizmet" name="hizmet_id" required
                                                class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                                            <option value="" disabled selected>Hizmet Seçin...</option>
                                            @foreach($doktor->hizmetler as $hizmet)
                                                @if($hizmet->aktif_mi)
                                                    <option value="{{ $hizmet->id }}" {{ old('hizmet_id') == $hizmet->id ? 'selected' : '' }}>{{ $hizmet->ad }} ({{ $hizmet->sure }} dk)</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <!-- Confirm Patient Data -->
                                <div class="p-3 bg-slate-50 border border-slate-100 rounded-2xl space-y-1 text-xs">
                                    <p class="text-[9px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Randevu Sahibi</p>
                                    <p class="font-bold text-[#111827]">{{ Auth::guard('hasta')->user()->ad_soyad }}</p>
                                    <p class="text-[10px] text-[#6B7280]">{{ Auth::guard('hasta')->user()->telefon }} • {{ Auth::guard('hasta')->user()->e_posta }}</p>
                                </div>

                                <!-- Date -->
                                <div class="space-y-1">
                                    <label for="p_tarih" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Tercih Edilen Tarih</label>
                                    <input type="date" name="tarih" id="p_tarih" required value="{{ old('tarih', date('Y-m-d', strtotime('+1 day'))) }}" min="{{ date('Y-m-d') }}"
                                           class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                                </div>

                                <!-- Time Slot -->
                                <div class="space-y-1">
                                    <label for="p_saat" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Tercih Edilen Saat</label>
                                    <select name="saat" id="p_saat" required
                                            class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                                        <option value="" disabled selected>Önce Tarih Seçin...</option>
                                    </select>
                                </div>

                                <!-- Notes -->
                                <div class="space-y-1">
                                    <label for="p_not" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Şikayet / Not (Opsiyonel)</label>
                                    <textarea id="p_not" name="not" rows="2" placeholder="Şikayetiniz veya belirtmek istediğiniz notlar..."
                                              class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all resize-none">{{ old('not') }}</textarea>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit"
                                        class="w-full py-3.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                                    Randevu Talebi Oluştur
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Guest booking (no account required) -->
                        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-md relative overflow-hidden">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display border-b border-slate-100 pb-3.5 mb-4">
                                Misafir Randevu (Kayıt Yok)
                            </h3>
                            <p class="text-[11px] text-[#6B7280] mb-4 leading-relaxed">
                                Hesap oluşturmadan randevu talebi bırakabilirsiniz. İsterseniz sonra hesap açıp randevularınızı yönetebilirsiniz.
                            </p>

                            @if(session('basarili'))
                                <div class="p-3 mb-4 bg-emerald-50 border border-emerald-100 rounded-xl text-[11px] text-emerald-700 font-medium">
                                    {{ session('basarili') }}
                                </div>
                            @endif
                            @if(session('hata'))
                                <div class="p-3 mb-4 bg-red-50 border border-red-100 rounded-xl text-[11px] text-red-700 font-medium">
                                    {{ session('hata') }}
                                </div>
                            @endif

                            <form action="{{ route('frontend.hasta.randevu.misafir') }}" method="POST" class="space-y-4" id="misafir-randevu-form">
                                @csrf
                                <input type="hidden" name="recaptcha_token" id="recaptcha_token" value="">
                                <input type="hidden" name="doktor_id" value="{{ $doktor->id }}">
                                {{-- Honeypot --}}
                                <div class="hidden" aria-hidden="true">
                                    <input type="text" name="{{ config('randevu.honeypot_field', 'website_url') }}" value="" tabindex="-1" autocomplete="off">
                                </div>

                                @if($doktor->hizmetler->isNotEmpty())
                                    <div class="space-y-1">
                                        <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Hizmet</label>
                                        <select name="hizmet_id" required
                                                class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] text-xs">
                                            <option value="" disabled selected>Hizmet Seçin...</option>
                                            @foreach($doktor->hizmetler as $hizmet)
                                                @if($hizmet->aktif_mi)
                                                    <option value="{{ $hizmet->id }}" @selected(old('hizmet_id') == $hizmet->id)>{{ $hizmet->ad }} ({{ $hizmet->sure }} dk)</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Ad</label>
                                        <input type="text" name="ad" required value="{{ old('ad') }}"
                                               class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Soyad</label>
                                        <input type="text" name="soyad" required value="{{ old('soyad') }}"
                                               class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Telefon</label>
                                    <input type="tel" name="telefon" required value="{{ old('telefon') }}" placeholder="05xx xxx xx xx"
                                           class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">E-posta (opsiyonel)</label>
                                    <input type="email" name="e_posta" value="{{ old('e_posta') }}"
                                           class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
                                </div>

                                <div class="space-y-1">
                                    <label for="g_tarih" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Tarih</label>
                                    <input type="date" name="tarih" id="g_tarih" required value="{{ old('tarih', date('Y-m-d', strtotime('+1 day'))) }}" min="{{ date('Y-m-d') }}"
                                           class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
                                </div>

                                <div class="space-y-1">
                                    <label for="g_saat" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Saat</label>
                                    <select name="saat" id="g_saat" required
                                            class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
                                        <option value="" disabled selected>Önce tarih seçin...</option>
                                    </select>
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Not (opsiyonel)</label>
                                    <textarea name="not" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none resize-none">{{ old('not') }}</textarea>
                                </div>

                                @php $onlineGorusmeAcik = $doktor->aktifPaket()?->hasFeature('online_gorusme'); @endphp
                                @if($onlineGorusmeAcik)
                                    <div class="space-y-2 rounded-xl border border-slate-100 bg-slate-50/80 p-3">
                                        <span class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Görüşme türü</span>
                                        <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                                            <input type="radio" name="gorusme_tipi" value="yuz_yuze" @checked(old('gorusme_tipi', 'yuz_yuze') === 'yuz_yuze') class="text-[#C96A2B]">
                                            Yüz yüze
                                        </label>
                                        <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                                            <input type="radio" name="gorusme_tipi" value="online" @checked(old('gorusme_tipi') === 'online') class="text-[#C96A2B]">
                                            Online — sitemiz üzerinden görüntülü (Zoom yok)
                                        </label>
                                        <p class="text-[10px] text-slate-500 leading-relaxed">Online seçerseniz onay sonrası görüşme odası otomatik açılır; katılım linki SMS/e-posta ve randevu yönetim sayfanızda yer alır.</p>
                                    </div>
                                @else
                                    <input type="hidden" name="gorusme_tipi" value="yuz_yuze">
                                @endif

                                <label class="flex items-start gap-2 text-[11px] text-slate-600 cursor-pointer">
                                    <input type="checkbox" name="kvkk_onay" value="1" required class="mt-0.5">
                                    <span>Kişisel verilerimin randevu amacıyla işlenmesini kabul ediyorum.</span>
                                </label>

                                <button type="submit"
                                        class="w-full py-3.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all font-display">
                                    Randevu Talebi Oluştur
                                </button>
                            </form>

                            <div class="pt-4 mt-4 border-t border-slate-100 flex flex-col gap-2">
                                <p class="text-[10px] text-center text-slate-400">Zaten hesabınız var mı?</p>
                                <a href="{{ route('frontend.hasta.giris') }}" class="w-full py-2.5 border border-[#E5E7EB] hover:bg-slate-50 text-[#1F2937] font-bold text-xs uppercase tracking-wider rounded-xl text-center font-display">
                                    Giriş Yap
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Bekleme listesi (uygun slot yoksa / iptal sonrası) -->
                    @include('frontend.hekimler.partials.bekleme_listesi_form', ['doktor' => $doktor])
                @else
                    <!-- Online Booking Closed / Contact Info Widget -->
                    <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-md relative overflow-hidden text-center space-y-4">
                        <div class="w-12 h-12 bg-amber-50 text-[#C96A2B] rounded-2xl flex items-center justify-center mx-auto border border-[#E7B58A]/30">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z"></path>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">
                                Randevu Al
                            </h3>
                            <p class="text-xs text-[#6B7280] leading-relaxed">
                                Hekimimiz online randevu alımına geçici olarak kapalıdır. Randevu bilgisi ve detaylar için lütfen iletişime geçiniz.
                            </p>
                        </div>
                        <div class="pt-4 border-t border-slate-100 space-y-2">
                            <p class="text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">İLETİŞİME GEÇ</p>
                            @if($doktor->telefon)
                                <a href="tel:{{ $doktor->telefon }}" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all font-display">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.622l1.5-1.5a2.25 2.25 0 013.182 0l1.287 1.287a2.25 2.25 0 010 3.182l-1.07 1.07a11.94 11.94 0 005.176 5.176l1.07-1.07a2.25 2.25 0 013.182 0l1.287 1.287a2.25 2.25 0 010 3.182l-2.872 2.872a2.25 2.25 0 01-3.182 0C5.172 18.828 2.25 11.895 2.25 6.622z"></path>
                                    </svg>
                                    {{ $doktor->telefon }}
                                </a>
                            @endif
                            <a href="mailto:{{ $doktor->e_posta }}" class="flex items-center justify-center gap-2 px-4 py-2.5 border border-[#E5E7EB] hover:bg-slate-50 text-[#1F2937] font-bold text-xs uppercase tracking-wider rounded-xl transition-all font-display">
                                E-Posta ile İletişim
                            </a>
                        </div>
                    </div>

                    @include('frontend.hekimler.partials.bekleme_listesi_form', ['doktor' => $doktor])
                @endif

                <!-- Timetable card -->
                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#1F2937] font-display border-b border-slate-100 pb-3 mb-4">
                        Haftalık Çalışma Saatleri
                    </h3>
                    <div class="space-y-2 text-xs">
                        @foreach($doktor->calismaSaatleri->sortBy('gun') as $cs)
                            <div class="flex justify-between py-1 {{ !$loop->last ? 'border-b border-slate-50' : '' }}">
                                <span class="text-[#6B7280]">{{ $cs->gun_adi }}</span>
                                @if($cs->aktif_mi)
                                    <span class="font-semibold text-[#111827]">
                                        {{ substr($cs->mesai_baslangic, 0, 5) }} - {{ substr($cs->mesai_bitis, 0, 5) }}
                                    </span>
                                @else
                                    <span class="font-bold text-red-500 uppercase">Kapalı</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

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
                document.getElementById('randevuForm').reset();
            }, 300);
        }
    }

    const calismaSaatleri = @json($doktor->calismaSaatleri);
    const periyot = {{ $doktor->randevuAyari ? $doktor->randevuAyari->randevu_periyodu : 30 }};

    function generateTimeSlotsFor(dateInputId, saatSelectId) {
        const dateInput = document.getElementById(dateInputId);
        const saatSelect = document.getElementById(saatSelectId);
        if (!dateInput || !saatSelect) return;

        const selectedDateVal = dateInput.value;
        if (!selectedDateVal) {
            saatSelect.innerHTML = '<option value="" disabled selected>Önce Tarih Seçin...</option>';
            return;
        }

        const selectedDate = new Date(selectedDateVal);
        let dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
        let dbDay = dayOfWeek === 0 ? 7 : dayOfWeek; // 1 = Pazartesi, 7 = Pazar

        const cs = calismaSaatleri.find(item => Number(item.gun) === Number(dbDay));

        if (!cs || !cs.aktif_mi) {
            saatSelect.innerHTML = '<option value="" disabled selected>Hekim Bu Gün Çalışmamaktadır</option>';
            return;
        }

        function timeToMins(timeStr) {
            const parts = timeStr.split(':');
            return parseInt(parts[0]) * 60 + parseInt(parts[1]);
        }

        function minsToTime(mins) {
            const h = Math.floor(mins / 60).toString().padStart(2, '0');
            const m = (mins % 60).toString().padStart(2, '0');
            return `${h}:${m}`;
        }

        const startMins = timeToMins(cs.mesai_baslangic);
        const endMins = timeToMins(cs.mesai_bitis);
        const lunchStart = cs.ogle_arasi_aktif_mi && cs.ogle_baslangic ? timeToMins(cs.ogle_baslangic) : null;
        const lunchEnd = cs.ogle_arasi_aktif_mi && cs.ogle_bitis ? timeToMins(cs.ogle_bitis) : null;

        let current = startMins;
        saatSelect.innerHTML = '<option value="" disabled selected>Saat Seçin...</option>';

        while (current < endMins) {
            let slotEnd = current + periyot;
            if (slotEnd > endMins) {
                break;
            }

            let isLunch = false;
            if (lunchStart !== null && lunchEnd !== null) {
                if (current >= lunchStart && current < lunchEnd) {
                    isLunch = true;
                }
            }

            if (!isLunch) {
                const timeStr = minsToTime(current);
                const opt = document.createElement('option');
                opt.value = timeStr;
                opt.textContent = timeStr;
                saatSelect.appendChild(opt);
            }

            current += periyot;
        }

        if (saatSelect.options.length <= 1) {
            saatSelect.innerHTML = '<option value="" disabled selected>Bu Güne Müsait Saat Bulunmuyor</option>';
        }
    }

    function generateTimeSlots() {
        generateTimeSlotsFor('p_tarih', 'p_saat');
        generateTimeSlotsFor('g_tarih', 'g_saat');
    }

    // Close on overlay click
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('bookingDetailModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    kapatBookingModal();
                }
            });
        }

        const dateInput = document.getElementById('p_tarih');
        if (dateInput) {
            dateInput.addEventListener('change', function () { generateTimeSlotsFor('p_tarih', 'p_saat'); });
            generateTimeSlotsFor('p_tarih', 'p_saat');
        }
        const guestDate = document.getElementById('g_tarih');
        if (guestDate) {
            guestDate.addEventListener('change', function () { generateTimeSlotsFor('g_tarih', 'g_saat'); });
            generateTimeSlotsFor('g_tarih', 'g_saat');
        }

    });

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

        if (mode === 'map') {
            streetEl.classList.add('hidden');
            mapEl.classList.remove('hidden');
            
            btnMap.className = "px-2.5 py-1 text-[10px] font-bold rounded-md transition-all duration-200 bg-white text-[#C96A2B] shadow-sm font-display uppercase tracking-wider";
            btnStreet.className = "px-2.5 py-1 text-[10px] font-bold rounded-md transition-all duration-200 text-[#4B5563] hover:text-[#111827] font-display uppercase tracking-wider";
            
            initDetailMap();
            if (detailMap) {
                setTimeout(function() {
                    detailMap.invalidateSize();
                }, 100);
            }
        } else {
            mapEl.classList.add('hidden');
            streetEl.classList.remove('hidden');

            btnMap.className = "px-2.5 py-1 text-[10px] font-bold rounded-md transition-all duration-200 text-[#4B5563] hover:text-[#111827] font-display uppercase tracking-wider";
            btnStreet.className = "px-2.5 py-1 text-[10px] font-bold rounded-md transition-all duration-200 bg-white text-[#C96A2B] shadow-sm font-display uppercase tracking-wider";
        }
    };

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

    // reCAPTCHA v3 — misafir randevu formu
    (function () {
        var form = document.getElementById('misafir-randevu-form');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            if (form.dataset.rcOk === '1') return;
            e.preventDefault();
            var tokenInput = document.getElementById('recaptcha_token');
            var btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.dataset.old = btn.textContent; btn.textContent = 'Doğrulanıyor…'; }
            var done = function (token) {
                if (tokenInput) tokenInput.value = token || '';
                form.dataset.rcOk = '1';
                form.submit();
            };
            if (typeof window.raGetRecaptchaToken === 'function') {
                window.raGetRecaptchaToken('randevu').then(done).catch(function () { done(''); });
            } else {
                done('');
            }
        });
    })();
</script>
@endsection
