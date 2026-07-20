@extends('frontend.layouts.app')

@section('baslik', ($blog->meta_baslik ?? $blog->baslik) . ' - ' . ($doktor->unvan ? $doktor->unvan . ' ' : '') . $doktor->ad_soyad . ' - Randevu Ajandam')
@section('meta_aciklama', $blog->meta_aciklama ?? Str::limit(strip_tags($blog->icerik), 150))
@section('meta_anahtar_kelimeler', $blog->meta_anahtar_kelimeler ?? '')
@section('og_image', $blog->resim ? asset($blog->resim) : asset('assets/images/logo.png'))
@section('og_type', 'article')

@section('icerik')
<section class="fe-page relative bg-[#FAFAFA] overflow-hidden">
    <!-- Background Ambient Lights -->
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-[#C96A2B]/3 blur-[120px] pointer-events-none"></div>

    <div class="max-w-4xl mx-auto px-6 relative z-10">
        
        <!-- Back Navigation Link -->
        <div class="mb-8 flex justify-between items-center">
            <a href="{{ $doktor->profil_url }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#6B7280] hover:text-[#C96A2B] transition-colors font-display uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                </svg>
                Hekim Profiline Dön
            </a>
            
            <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-[#C96A2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                {{ $blog->okunma_sayisi }} Görüntülenme
            </span>
        </div>

        <!-- Article Card -->
        <article class="bg-white border border-[#E5E7EB] rounded-3xl overflow-hidden shadow-[0_8px_30px_rgba(31,41,55,0.02)] mb-10">
            @if($blog->resim)
                <div class="w-full h-[320px] md:h-[420px] overflow-hidden relative">
                    <img src="{{ asset($blog->resim) }}" alt="{{ $blog->baslik }}" class="w-full h-full object-cover">
                </div>
            @endif

            <div class="p-8 md:p-12 space-y-6">
                <!-- Meta Info -->
                <div class="flex items-center gap-3.5 text-[11px] font-bold font-display uppercase tracking-wider text-[#C96A2B] border-b border-slate-100 pb-5">
                    <span>{{ $blog->created_at->translatedFormat('d F Y') }}</span>
                    <span class="text-slate-300">•</span>
                    <span>Tıbbi Makale</span>
                </div>

                <!-- Title -->
                <h1 class="text-2xl md:text-4xl font-extrabold font-display text-[#111827] tracking-tight leading-tight">
                    {{ $blog->baslik }}
                </h1>

                <!-- Content -->
                <div class="text-sm text-[#4B5563] leading-relaxed space-y-4 font-normal">
                    {!! $blog->icerik !!}
                </div>
            </div>
        </article>

        <!-- Doctor Card for conversions -->
        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6 relative overflow-hidden">
            <div class="flex items-center gap-4 text-center sm:text-left flex-col sm:flex-row">
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
                <div class="w-16 h-16 rounded-2xl overflow-hidden bg-[#FFF7ED] border border-[#E7B58A]/30 text-[#C96A2B] flex items-center justify-center font-extrabold font-display text-lg shadow-sm shrink-0 select-none">
                    @if($doktor->profil_resmi)
                        <img src="{{ asset($doktor->profil_resmi) }}" alt="{{ $doktor->ad_soyad }}" class="w-full h-full object-cover">
                    @else
                        {{ $kisaAd }}
                    @endif
                </div>
                <div>
                    <h4 class="text-base font-bold font-display text-[#111827]">
                        {{ $doktor->unvan ? $doktor->unvan . ' ' : '' }}{{ $doktor->ad_soyad }}
                    </h4>
                    <p class="text-xs font-semibold text-[#C96A2B] font-display uppercase tracking-wider mt-0.5">
                        {{ $doktor->uzmanlik_alani ?? 'Genel Branş Hekimi' }}
                    </p>
                    <p class="text-[11px] text-[#6B7280] mt-1">
                        {{ $doktor->il?->ad }}{{ $doktor->ilce?->ad ? ' / ' . $doktor->ilce->ad : '' }}
                    </p>
                </div>
            </div>
            <div class="shrink-0">
                <a href="{{ $doktor->profil_url }}" class="inline-flex px-6 py-3 bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-200 shadow-sm font-display items-center gap-2">
                    Online Randevu Al
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                    </svg>
                </a>
            </div>
            <div class="absolute right-0 bottom-0 top-0 w-1/5 bg-gradient-to-l from-[#FFF7ED]/15 to-transparent pointer-events-none"></div>
        </div>

    </div>
</section>
@endsection
