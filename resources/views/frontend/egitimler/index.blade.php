@extends('frontend.layouts.app')

@section('baslik', 'Eğitimler - Randevu Ajandam')
@section('meta_aciklama', 'Platformdaki uzman hekim ve sağlık profesyonellerinin sunduğu eğitim, seminer ve kursları keşfedin.')

@section('icerik')
<section class="relative bg-[#FAFAFA] py-12 md:py-16 overflow-hidden min-h-[70vh]">
    <div class="absolute top-[-12%] right-[-8%] w-[420px] h-[420px] rounded-full bg-[#E7B58A]/12 blur-[110px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-8%] w-[360px] h-[360px] rounded-full bg-[#C96A2B]/6 blur-[110px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10">
        <header class="mb-8 md:mb-10">
            <p class="text-[10px] font-bold uppercase tracking-wider text-[#C96A2B] font-display">Uzman eğitimleri</p>
            <h1 class="mt-1.5 text-2xl sm:text-3xl md:text-4xl font-extrabold font-display text-[#111827] tracking-tight">
                Eğitimler
            </h1>
            <p class="mt-2 text-sm text-[#6B7280] max-w-2xl leading-relaxed">
                Uzmanların yayınladığı seminer, kurs ve eğitim programlarını tek listede görün; detaya gidip başvuru yapın.
            </p>
        </header>

        {{-- Filtre --}}
        <form method="GET" action="{{ route('frontend.egitimler.index') }}" class="mb-8 p-4 sm:p-5 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <div class="sm:col-span-6">
                    <label for="arama" class="block text-[10px] font-bold uppercase tracking-wider text-[#6B7280] mb-1.5 font-display">Ara</label>
                    <input type="text" name="arama" id="arama" value="{{ $arama }}"
                           placeholder="Eğitim adı, uzman veya anahtar kelime..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-sm text-[#111827] placeholder:text-[#9CA3AF] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
                </div>
                <div class="sm:col-span-3">
                    <label for="tip" class="block text-[10px] font-bold uppercase tracking-wider text-[#6B7280] mb-1.5 font-display">Tür</label>
                    <select name="tip" id="tip"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-sm text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] bg-white">
                        <option value="">Tümü</option>
                        @foreach($tipler as $t)
                            <option value="{{ $t }}" @selected($tip === $t)>{{ str_replace('_', ' ', $t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-3 flex gap-2">
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold uppercase tracking-wider font-display transition-colors">
                        Filtrele
                    </button>
                    @if($arama !== '' || $tip !== '')
                        <a href="{{ route('frontend.egitimler.index') }}"
                           class="px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs font-bold text-[#6B7280] hover:text-[#C96A2B] hover:border-[#E7B58A]/50 transition-colors inline-flex items-center">
                            Sıfırla
                        </a>
                    @endif
                </div>
            </div>
            <p class="mt-3 text-[11px] text-[#9CA3AF]">
                {{ $egitimler->total() }} eğitim listeleniyor
                @if($arama !== '' || $tip !== '')
                    <span class="text-[#6B7280]">(filtreli)</span>
                @endif
            </p>
        </form>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($egitimler as $e)
                @php
                    $doktor = $e->doktor;
                    $bransAd = $doktor?->branslar?->first()?->ad;
                @endphp
                <article class="group flex flex-col bg-white border border-[#E5E7EB] rounded-3xl overflow-hidden shadow-sm hover:shadow-[0_16px_40px_rgba(201,106,43,0.12)] hover:border-[#E7B58A]/50 hover:-translate-y-1 transition-all duration-300">
                    <a href="{{ $e->url }}" class="relative aspect-[16/10] bg-gradient-to-br from-[#FFF7ED] to-[#FFE8D2] overflow-hidden block">
                        @if($e->kapak_url)
                            <img src="{{ $e->kapak_url }}" alt="{{ $e->baslik }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
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
                    </a>

                    <div class="flex flex-col flex-1 p-5">
                        <a href="{{ $e->url }}" class="no-underline">
                            <h2 class="text-base font-bold font-display text-[#111827] group-hover:text-[#C96A2B] transition-colors line-clamp-2 leading-snug">
                                {{ $e->baslik }}
                            </h2>
                        </a>
                        @if($e->ozet)
                            <p class="mt-2 text-xs text-[#6B7280] leading-relaxed line-clamp-2">{{ strip_tags($e->ozet) }}</p>
                        @endif

                        @if($doktor)
                            <a href="{{ $doktor->profil_url }}" class="mt-3 flex items-center gap-2.5 no-underline group/doc">
                                @if($doktor->profil_resmi)
                                    <img src="{{ asset($doktor->profil_resmi) }}" alt=""
                                         class="w-9 h-9 rounded-xl object-cover border border-[#E7B58A]/30" loading="lazy">
                                @else
                                    <div class="w-9 h-9 rounded-xl bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center text-[10px] font-bold font-display">
                                        {{ mb_strtoupper(mb_substr($doktor->ad_soyad, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-[#111827] font-display truncate group-hover/doc:text-[#C96A2B] transition-colors">
                                        {{ $doktor->unvan ? $doktor->unvan.' ' : '' }}{{ $doktor->ad_soyad }}
                                    </p>
                                    <p class="text-[10px] text-[#6B7280] truncate">
                                        {{ $bransAd ?? $doktor->uzmanlik_alani ?? 'Uzman' }}
                                        @if($doktor->il)
                                            · {{ $doktor->il->ad }}
                                        @endif
                                    </p>
                                </div>
                            </a>
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

                        <a href="{{ $e->url }}"
                           class="mt-3 inline-flex items-center justify-center gap-1.5 w-full py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-[10px] font-bold uppercase tracking-wider font-display transition-colors no-underline">
                            Detayı incele
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full py-16 text-center bg-white border border-[#E5E7EB] rounded-3xl">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold font-display text-[#111827]">Eğitim bulunamadı</p>
                    <p class="text-xs text-[#6B7280] mt-1 max-w-sm mx-auto">
                        @if($arama !== '' || $tip !== '')
                            Filtrelerinize uygun yayında eğitim yok. Arama veya türü değiştirip tekrar deneyin.
                        @else
                            Henüz platformda yayınlanmış eğitim bulunmuyor.
                        @endif
                    </p>
                    @if($arama !== '' || $tip !== '')
                        <a href="{{ route('frontend.egitimler.index') }}" class="inline-flex mt-4 text-xs font-bold text-[#C96A2B] hover:underline">Filtreleri temizle</a>
                    @endif
                </div>
            @endforelse
        </div>

        @if($egitimler->hasPages())
            <div class="mt-10 flex justify-center">
                {{ $egitimler->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
