@extends('frontend.layouts.app')

@section('baslik', 'Eğitimler - Randevu Ajandam')
@section('meta_aciklama', 'Platformdaki uzman hekim ve sağlık profesyonellerinin sunduğu eğitim, seminer ve kursları keşfedin.')

@section('icerik')
<style>
    .eg-search-shell {
        position: relative;
        max-width: 46rem;
        margin: 0 auto;
    }
    .eg-search-bar {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.45rem 0.45rem 1.1rem;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(231, 181, 138, 0.45);
        border-radius: 9999px;
        box-shadow:
            0 18px 40px -20px rgba(201, 106, 43, 0.35),
            0 8px 24px -16px rgba(15, 23, 42, 0.18),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .eg-search-bar:focus-within {
        border-color: #C96A2B;
        box-shadow:
            0 0 0 4px rgba(201, 106, 43, 0.12),
            0 20px 44px -18px rgba(201, 106, 43, 0.4);
        transform: translateY(-1px);
    }
    .eg-search-icon { color: #C96A2B; flex-shrink: 0; opacity: 0.9; }
    .eg-search-input {
        flex: 1 1 auto;
        min-width: 0;
        border: 0;
        outline: none;
        background: transparent;
        font-size: 0.95rem;
        font-weight: 500;
        color: #111827;
        padding: 0.65rem 0.35rem;
    }
    .eg-search-input::placeholder { color: #9CA3AF; font-weight: 400; }
    .eg-search-btn {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        height: 2.75rem;
        padding: 0 1.25rem;
        border: 0;
        border-radius: 9999px;
        background: linear-gradient(135deg, #C96A2B 0%, #B55A20 100%);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        cursor: pointer;
        box-shadow: 0 8px 18px -8px rgba(201, 106, 43, 0.7);
        transition: transform 0.15s ease, filter 0.15s ease;
    }
    .eg-search-btn:hover { filter: brightness(1.05); transform: scale(1.02); }
    @media (max-width: 480px) {
        .eg-search-btn span { display: none; }
        .eg-search-btn { width: 2.75rem; padding: 0; }
    }

    .eg-chip-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.45rem;
        margin-top: 1.15rem;
    }
    .eg-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.45rem 0.85rem;
        border-radius: 9999px;
        border: 1px solid #E5E7EB;
        background: #fff;
        color: #6B7280;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-decoration: none;
        transition: all 0.18s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .eg-chip:hover {
        border-color: rgba(231, 181, 138, 0.65);
        color: #C96A2B;
        background: #FFF7ED;
    }
    .eg-chip.is-active {
        border-color: transparent;
        background: linear-gradient(135deg, #C96A2B, #B55A20);
        color: #fff;
        box-shadow: 0 8px 18px -10px rgba(201, 106, 43, 0.75);
    }

    .eg-stats {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 0.5rem 1rem;
        margin-top: 1.35rem;
        font-size: 0.75rem;
        color: #6B7280;
    }
    .eg-stats strong { color: #111827; font-weight: 800; }
    .eg-stats a {
        color: #C96A2B;
        font-weight: 700;
        text-decoration: none;
    }
    .eg-stats a:hover { text-decoration: underline; }

    .eg-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #fff;
        border: 1px solid #E8ECF1;
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: 0 10px 28px -18px rgba(15, 23, 42, 0.28);
        transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.28s ease, border-color 0.28s ease;
    }
    .eg-card:hover {
        transform: translateY(-5px);
        border-color: rgba(231, 181, 138, 0.55);
        box-shadow: 0 22px 40px -18px rgba(201, 106, 43, 0.28);
    }
    .eg-card-media {
        position: relative;
        aspect-ratio: 16 / 10;
        background: linear-gradient(145deg, #FFF7ED, #FFE8D2);
        overflow: hidden;
    }
    .eg-card-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .eg-card:hover .eg-card-media img { transform: scale(1.06); }
    .eg-card-badge {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        padding: 0.3rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.6rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #C96A2B;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(231, 181, 138, 0.35);
        backdrop-filter: blur(8px);
    }
    .eg-card-price {
        position: absolute;
        bottom: 0.75rem;
        right: 0.75rem;
        padding: 0.35rem 0.7rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 800;
        color: #fff;
        background: rgba(17, 24, 39, 0.82);
        backdrop-filter: blur(6px);
    }
    .eg-card-body { display: flex; flex-direction: column; flex: 1; padding: 1.15rem 1.2rem 1.2rem; }
    .eg-card-title {
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #111827;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .eg-card:hover .eg-card-title { color: #C96A2B; }
    .eg-card-desc {
        margin-top: 0.45rem;
        font-size: 0.78rem;
        color: #6B7280;
        line-height: 1.45;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .eg-doc {
        margin-top: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        text-decoration: none;
        padding: 0.55rem 0.65rem;
        border-radius: 0.9rem;
        background: #FAFAFA;
        border: 1px solid #F1F5F9;
        transition: border-color 0.2s ease, background 0.2s ease;
    }
    .eg-doc:hover { border-color: rgba(231, 181, 138, 0.5); background: #FFF7ED; }
    .eg-doc-av {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.7rem;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid rgba(231, 181, 138, 0.3);
        background: #FFF7ED;
        color: #C96A2B;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        font-weight: 800;
    }
    .eg-foot {
        margin-top: auto;
        padding-top: 0.95rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        border-top: 1px solid #F1F5F9;
    }
    .eg-cta {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.5rem 0.85rem;
        border-radius: 0.75rem;
        background: #C96A2B;
        color: #fff;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        text-decoration: none;
        transition: background 0.15s ease;
    }
    .eg-cta:hover { background: #B55A20; color: #fff; }
</style>

<section class="relative bg-[#FAFAFA] pb-10 md:pb-14 overflow-hidden">
    {{-- Hero --}}
    <div class="relative fe-page--tight overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-[-30%] left-1/2 -translate-x-1/2 w-[720px] h-[420px] rounded-full bg-[#E7B58A]/15 blur-[100px]"></div>
            <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-b from-transparent to-[#FAFAFA]"></div>
        </div>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 relative z-10 text-center">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/90 border border-[#E7B58A]/35 text-[10px] font-bold uppercase tracking-widest text-[#C96A2B] font-display shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-[#C96A2B] animate-pulse"></span>
                Uzman eğitim &amp; seminer
            </span>
            <h1 class="mt-4 text-3xl sm:text-4xl md:text-[2.75rem] font-black font-display text-[#111827] tracking-tight leading-[1.1]">
                Eğitimleri keşfedin
            </h1>
            <p class="mt-3 text-sm md:text-base text-[#6B7280] leading-relaxed max-w-xl mx-auto">
                Hekim ve sağlık uzmanlarının yayınladığı kurs, seminer ve eğitimleri tek yerden bulun.
            </p>

            {{-- Premium arama --}}
            <form method="GET" action="{{ route('frontend.egitimler.index') }}" class="eg-search-shell mt-8" id="egitim-search-form">
                @if($tip !== '')
                    <input type="hidden" name="tip" value="{{ $tip }}">
                @endif
                <div class="eg-search-bar">
                    <svg class="eg-search-icon w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input type="search" name="arama" id="arama" value="{{ $arama }}"
                           class="eg-search-input"
                           placeholder="Eğitim, uzman veya anahtar kelime ara…"
                           autocomplete="off"
                           aria-label="Eğitim ara">
                    <button type="submit" class="eg-search-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        <span>Ara</span>
                    </button>
                </div>

                {{-- Tür chip'leri --}}
                @if($tipler->isNotEmpty() || $tip !== '')
                    <div class="eg-chip-row" role="list" aria-label="Eğitim türleri">
                        <a href="{{ route('frontend.egitimler.index', array_filter(['arama' => $arama ?: null])) }}"
                           class="eg-chip {{ $tip === '' ? 'is-active' : '' }}" role="listitem">
                            Tümü
                        </a>
                        @foreach($tipler as $t)
                            <a href="{{ route('frontend.egitimler.index', array_filter(['arama' => $arama ?: null, 'tip' => $t])) }}"
                               class="eg-chip {{ $tip === $t ? 'is-active' : '' }}" role="listitem">
                                {{ str_replace('_', ' ', $t) }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="eg-stats">
                    <span>
                        <strong>{{ number_format($egitimler->total()) }}</strong> eğitim
                        @if($arama !== '' || $tip !== '')
                            · filtreli sonuç
                        @endif
                    </span>
                    @if($arama !== '' || $tip !== '')
                        <span class="text-slate-300">|</span>
                        <a href="{{ route('frontend.egitimler.index') }}">Filtreleri temizle</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Liste --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
            @forelse($egitimler as $e)
                @php
                    $doktor = $e->doktor;
                    $bransAd = $doktor?->branslar?->first()?->ad;
                    $fiyatLabel = ($e->fiyat === null || (float) $e->fiyat <= 0)
                        ? 'Ücretsiz'
                        : number_format((float) $e->fiyat, 0, ',', '.').' ₺';
                @endphp
                <article class="eg-card group">
                    <a href="{{ $e->url }}" class="eg-card-media block no-underline">
                        @if($e->kapak_url)
                            <img src="{{ $e->kapak_url }}" alt="{{ $e->baslik }}" loading="lazy">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-[#C96A2B]/30">
                                <svg class="w-14 h-14" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84"/>
                                </svg>
                            </div>
                        @endif
                        @if($e->tip)
                            <span class="eg-card-badge">{{ str_replace('_', ' ', $e->tip) }}</span>
                        @endif
                        <span class="eg-card-price">{{ $fiyatLabel }}</span>
                    </a>

                    <div class="eg-card-body">
                        <a href="{{ $e->url }}" class="eg-card-title no-underline">{{ $e->baslik }}</a>
                        @if($e->ozet)
                            <p class="eg-card-desc">{{ strip_tags($e->ozet) }}</p>
                        @endif

                        @if($doktor)
                            <a href="{{ $doktor->profil_url }}" class="eg-doc">
                                @if($doktor->profil_resmi)
                                    <img src="{{ asset($doktor->profil_resmi) }}" alt="" class="eg-doc-av" loading="lazy">
                                @else
                                    <div class="eg-doc-av">{{ mb_strtoupper(mb_substr($doktor->ad_soyad, 0, 2)) }}</div>
                                @endif
                                <div class="min-w-0 text-left">
                                    <p class="text-xs font-bold text-[#111827] font-display truncate">
                                        {{ $doktor->unvan ? $doktor->unvan.' ' : '' }}{{ $doktor->ad_soyad }}
                                    </p>
                                    <p class="text-[10px] text-[#6B7280] truncate">
                                        {{ $bransAd ?? $doktor->uzmanlik_alani ?? 'Uzman' }}
                                        @if($doktor->il) · {{ $doktor->il->ad }}@endif
                                    </p>
                                </div>
                            </a>
                        @endif

                        <div class="eg-foot">
                            <span class="text-[11px] font-semibold text-[#6B7280]">
                                {{ $e->baslangic_at?->translatedFormat('d M Y') ?? 'Tarih yakında' }}
                            </span>
                            <a href="{{ $e->url }}" class="eg-cta">
                                İncele
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full py-16 sm:py-20 text-center bg-white border border-[#E5E7EB] rounded-[1.75rem] shadow-sm">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center mb-4 border border-[#E7B58A]/25">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </div>
                    <p class="text-base font-bold font-display text-[#111827]">Sonuç bulunamadı</p>
                    <p class="text-sm text-[#6B7280] mt-1.5 max-w-md mx-auto leading-relaxed">
                        @if($arama !== '' || $tip !== '')
                            Aramanıza veya seçtiğiniz türe uygun eğitim yok. Farklı bir kelime deneyin.
                        @else
                            Henüz platformda yayınlanmış eğitim bulunmuyor.
                        @endif
                    </p>
                    @if($arama !== '' || $tip !== '')
                        <a href="{{ route('frontend.egitimler.index') }}"
                           class="inline-flex mt-5 px-5 py-2.5 rounded-xl bg-[#C96A2B] text-white text-xs font-bold uppercase tracking-wider font-display hover:bg-[#B55A20] transition-colors no-underline">
                            Tüm eğitimleri göster
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        @if($egitimler->hasPages())
            <div class="mt-12 flex justify-center">
                {{ $egitimler->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
