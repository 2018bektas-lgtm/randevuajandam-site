@extends('frontend.layouts.app')
@section('baslik', ($doktor->unvan ? $doktor->unvan.' ' : '').$doktor->ad_soyad.' - Eğitimler')

@section('icerik')
<section class="py-10 sm:py-14">
    <div class="max-w-5xl mx-auto px-4">
        <a href="{{ $doktor->profil_url }}" class="text-xs font-bold text-slate-500 hover:text-[#C96A2B]">← Hekim profili</a>
        <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold font-display text-slate-900">Eğitimler</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $doktor->unvan }} {{ $doktor->ad_soyad }}</p>

        <div class="mt-8 grid sm:grid-cols-2 gap-5">
            @forelse($egitimler as $e)
                <a href="{{ $e->url }}" class="block p-5 rounded-3xl bg-white border border-slate-200 shadow-sm hover:border-[#C96A2B]/40 transition-colors">
                    @if($e->kapak)
                        <img src="{{ asset('storage/'.$e->kapak) }}" alt="" class="w-full h-36 object-cover rounded-2xl mb-3">
                    @endif
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#C96A2B] font-display">{{ $e->tip }}</span>
                    <h2 class="mt-1 text-lg font-bold font-display text-slate-900">{{ $e->baslik }}</h2>
                    <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $e->ozet }}</p>
                    <div class="mt-3 flex justify-between text-[11px] font-semibold text-slate-600">
                        <span>{{ $e->baslangic_at?->format('d.m.Y') ?? 'Tarih yakında' }}</span>
                        <span>
                            @if($e->fiyat === null || (float)$e->fiyat <= 0) Ücretsiz / bilgi
                            @else {{ number_format((float)$e->fiyat, 0, ',', '.') }} ₺
                            @endif
                        </span>
                    </div>
                </a>
            @empty
                <p class="col-span-2 text-center text-slate-500 py-12">Yayında eğitim bulunmuyor.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
