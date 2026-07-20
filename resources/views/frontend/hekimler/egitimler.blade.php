@extends('frontend.layouts.app')
@section('baslik', ($doktor->unvan ? $doktor->unvan.' ' : '').$doktor->ad_soyad.' - Eğitimler')
@section('meta_aciklama', ($doktor->unvan ? $doktor->unvan.' ' : '').$doktor->ad_soyad.' tarafından sunulan eğitimler ve seminerler.')

@section('icerik')
<section class="fe-page relative bg-[#FAFAFA] overflow-hidden">
    <div class="absolute top-[-10%] right-[-10%] w-[400px] h-[400px] rounded-full bg-[#E7B58A]/15 blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[360px] h-[360px] rounded-full bg-[#C96A2B]/8 blur-[100px] pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">
        <a href="{{ $doktor->profil_url }}"
           class="inline-flex items-center gap-2 text-[11px] font-bold text-[#6B7280] hover:text-[#C96A2B] transition-colors font-display uppercase tracking-wider">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Hekim profili
        </a>

        <header class="mt-5 mb-8 sm:mb-10">
            <p class="text-[10px] font-bold uppercase tracking-wider text-[#C96A2B] font-display">Eğitim &amp; Seminer</p>
            <h1 class="mt-1.5 text-2xl sm:text-3xl font-extrabold font-display text-[#111827] tracking-tight">Eğitimler</h1>
            <p class="mt-2 text-sm text-[#6B7280]">
                {{ $doktor->unvan ? $doktor->unvan.' ' : '' }}{{ $doktor->ad_soyad }}
                @if($doktor->uzmanlik_alani)
                    <span class="text-slate-300 mx-1">·</span>{{ $doktor->uzmanlik_alani }}
                @endif
            </p>
        </header>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($egitimler as $e)
                <a href="{{ $e->url }}"
                   class="group flex flex-col bg-white border border-[#E5E7EB] rounded-3xl overflow-hidden shadow-sm hover:shadow-[0_16px_40px_rgba(201,106,43,0.12)] hover:border-[#E7B58A]/50 hover:-translate-y-1 transition-all duration-300">
                    <div class="relative aspect-[16/10] bg-gradient-to-br from-[#FFF7ED] to-[#FFE8D2] overflow-hidden">
                        @if($e->kapak)
                            <img src="{{ $e->kapak_url }}" alt=""
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-[#C96A2B]/35">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84"/>
                                </svg>
                            </div>
                        @endif
                        @if($e->tip)
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider font-display bg-white/95 text-[#C96A2B] shadow-sm border border-white/50">
                                {{ str_replace('_', ' ', $e->tip) }}
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-col flex-1 p-5">
                        <h2 class="text-base font-bold font-display text-[#111827] group-hover:text-[#C96A2B] transition-colors line-clamp-2 leading-snug">
                            {{ $e->baslik }}
                        </h2>
                        @if($e->ozet)
                            <p class="mt-2 text-xs text-[#6B7280] leading-relaxed line-clamp-2">{{ $e->ozet }}</p>
                        @endif
                        <div class="mt-auto pt-4 flex items-center justify-between gap-2 border-t border-slate-100 text-[11px] font-semibold">
                            <span class="text-[#4B5563]">{{ $e->baslangic_at?->format('d.m.Y') ?? 'Tarih yakında' }}</span>
                            <span class="text-[#C96A2B]">
                                @if($e->fiyat === null || (float) $e->fiyat <= 0)
                                    Ücretsiz / bilgi
                                @else
                                    {{ number_format((float) $e->fiyat, 0, ',', '.') }} ₺
                                @endif
                            </span>
                        </div>
                        <span class="mt-3 inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider font-display text-[#C96A2B]">
                            Detayı incele
                            <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </span>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-16 text-center bg-white border border-[#E5E7EB] rounded-3xl">
                    <p class="text-sm font-bold font-display text-[#111827]">Yayında eğitim yok</p>
                    <p class="text-xs text-[#6B7280] mt-1">Bu hekim için henüz yayınlanmış eğitim bulunmuyor.</p>
                    <a href="{{ $doktor->profil_url }}" class="inline-flex mt-4 text-xs font-bold text-[#C96A2B] hover:underline">Hekim profiline dön</a>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
