@extends('frontend.layouts.app')

@section('baslik', ($hizmet->meta_baslik ?? $hizmet->ad) . ' - ' . ($doktor->unvan ? $doktor->unvan . ' ' : '') . $doktor->ad_soyad . ' - Randevu Ajandam')
@section('meta_aciklama', $hizmet->meta_aciklama ?? Str::limit(strip_tags($hizmet->aciklama), 150))
@section('meta_anahtar_kelimeler', $hizmet->meta_anahtar_kelimeler ?? '')
@section('og_image', $hizmet->resim_url ?: asset('assets/images/logo.png'))
@section('og_type', 'website')

@section('icerik')
<section class="relative bg-[#FAFAFA] py-12 md:py-20 overflow-hidden min-h-[85vh]">
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-[#C96A2B]/3 blur-[120px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10">
        <div class="mb-6">
            <a href="{{ $doktor->profil_url }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#6B7280] hover:text-[#C96A2B] transition-colors font-display uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                </svg>
                Hekim Profiline Dön
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Sol: hizmet + hekim bilgisi --}}
            <div class="lg:col-span-7 space-y-6">
                <article class="bg-white border border-[#E5E7EB] rounded-3xl overflow-hidden shadow-[0_8px_30px_rgba(31,41,55,0.02)]">
                    @if($hizmet->resim_url)
                        <div class="w-full h-[240px] md:h-[320px] overflow-hidden relative">
                            <img src="{{ $hizmet->resim_url }}" alt="{{ $hizmet->ad }}" class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="p-6 md:p-9 space-y-5">
                        <div class="flex flex-wrap items-center gap-3 text-[11px] font-bold font-display uppercase tracking-wider text-[#C96A2B] border-b border-slate-100 pb-4">
                            @if($hizmet->sure)
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $hizmet->sure }} Dakika
                                </span>
                                <span class="text-slate-300">•</span>
                            @endif
                            <span>Hizmet ve Tedavi</span>
                        </div>

                        <h1 class="text-2xl md:text-3xl font-extrabold font-display text-[#111827] tracking-tight leading-tight">
                            {{ $hizmet->ad }}
                        </h1>

                        @if($hizmet->aciklama)
                            <div class="text-sm text-[#4B5563] leading-relaxed space-y-4 font-normal prose prose-sm max-w-none">
                                {!! $hizmet->aciklama !!}
                            </div>
                        @else
                            <p class="text-sm text-gray-400">Bu hizmet hakkında detaylı açıklama bulunmamaktadır.</p>
                        @endif
                    </div>
                </article>

                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-5 md:p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-5">
                    @php
                        $words = preg_split('/\s+/', trim((string) $doktor->ad_soyad)) ?: [];
                        $kisaAd = collect($words)->filter()->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('') ?: 'DR';
                    @endphp
                    <div class="flex items-center gap-4 text-center sm:text-left flex-col sm:flex-row min-w-0">
                        <div class="w-14 h-14 rounded-2xl overflow-hidden bg-[#FFF7ED] border border-[#E7B58A]/30 text-[#C96A2B] flex items-center justify-center font-extrabold font-display text-base shadow-sm shrink-0">
                            @if($doktor->profil_resmi)
                                <img src="{{ asset($doktor->profil_resmi) }}" alt="{{ $doktor->ad_soyad }}" class="w-full h-full object-cover">
                            @else
                                {{ $kisaAd }}
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold font-display text-[#111827]">
                                {{ $doktor->unvan ? $doktor->unvan.' ' : '' }}{{ $doktor->ad_soyad }}
                            </h2>
                            <p class="text-xs font-semibold text-[#C96A2B] font-display uppercase tracking-wider mt-0.5">
                                {{ $doktor->uzmanlik_alani ?? 'Uzman Hekim' }}
                            </p>
                            <p class="text-[11px] text-[#6B7280] mt-1">
                                {{ $doktor->il?->ad }}{{ $doktor->ilce?->ad ? ' / '.$doktor->ilce->ad : '' }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ $doktor->profil_url }}" class="shrink-0 inline-flex px-5 py-2.5 bg-[#1F2937] hover:bg-[#111827] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm font-display items-center gap-2">
                        Profili Görüntüle
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Sağ: misafir + üye randevu wizard --}}
            <aside class="lg:col-span-5 lg:sticky lg:top-24 space-y-4">
                @if($doktor->randevuya_acik_mi)
                    @include('frontend.hekimler.partials.randevu_wizard', [
                        'doktor' => $doktor,
                        'preselectedHizmetId' => $hizmet->id,
                    ])

                    @guest('hasta')
                        <p class="text-[11px] text-center text-[#6B7280] leading-relaxed px-2">
                            Hesap oluşturmadan randevu alabilirsiniz.
                            İsterseniz
                            <a href="{{ route('frontend.hasta.giris') }}" class="font-semibold text-[#C96A2B] hover:underline">giriş yapın</a>
                            veya
                            <a href="{{ route('frontend.hasta.kayit') }}" class="font-semibold text-[#C96A2B] hover:underline">üye olun</a>.
                        </p>
                    @endguest
                @else
                    <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-md text-center space-y-4">
                        <div class="w-12 h-12 bg-amber-50 text-[#C96A2B] rounded-2xl flex items-center justify-center mx-auto border border-[#E7B58A]/30">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z"></path>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Randevu Al</h3>
                            <p class="text-xs text-[#6B7280] leading-relaxed">
                                Hekimimiz online randevu alımına geçici olarak kapalıdır. Randevu bilgisi için lütfen iletişime geçiniz.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 space-y-2">
                            @if($doktor->telefon)
                                <a href="tel:{{ $doktor->telefon }}" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all font-display">
                                    {{ $doktor->telefon }}
                                </a>
                            @endif
                            @if($doktor->e_posta)
                                <a href="mailto:{{ $doktor->e_posta }}" class="flex items-center justify-center gap-2 px-4 py-2.5 border border-[#E5E7EB] hover:bg-slate-50 text-[#1F2937] font-bold text-xs uppercase tracking-wider rounded-xl transition-all font-display">
                                    E-Posta ile İletişim
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</section>
@endsection
