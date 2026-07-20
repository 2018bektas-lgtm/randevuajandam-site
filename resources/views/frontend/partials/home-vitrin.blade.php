{{-- E-ticaret ürün vitrini: uzman / klinik / hizmet / blog / yorum --}}
<style>
    .ra-vitrin-section { position: relative; }
    .ra-vitrin-head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 0.85rem 1rem;
        margin-bottom: 1.15rem;
    }
    .ra-vitrin-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.625rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #C96A2B;
        margin-bottom: 0.35rem;
    }
    .ra-vitrin-kicker::before {
        content: '';
        width: 1.15rem;
        height: 2px;
        border-radius: 99px;
        background: linear-gradient(90deg, #C96A2B, #E7B58A);
    }
    .ra-vitrin-nav {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }
    .ra-vitrin-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.45rem;
        height: 2.45rem;
        border-radius: 9999px;
        border: 1px solid #E5E7EB;
        background: #fff;
        color: #6B7280;
        box-shadow: 0 6px 16px -10px rgba(31,41,55,0.35);
        cursor: pointer;
        transition: color .2s, border-color .2s, background .2s, transform .2s;
        flex-shrink: 0;
    }
    .ra-vitrin-btn:hover {
        color: #C96A2B;
        border-color: #E7B58A;
        background: #FFF7ED;
        transform: scale(1.05);
    }
    .ra-vitrin-btn:disabled {
        opacity: .35;
        pointer-events: none;
        transform: none;
    }
    .ra-vitrin-link {
        margin-left: 0.25rem;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #C96A2B;
        text-decoration: none;
        white-space: nowrap;
    }
    .ra-vitrin-link:hover { color: #B55A20; }

    /* Marketplace kayan ray */
    .ra-vitrin {
        position: relative;
        margin-inline: -0.35rem;
    }
    .ra-vitrin-viewport {
        overflow: hidden;
        position: relative;
        padding: 0.35rem 0 0.85rem;
        mask-image: linear-gradient(90deg, transparent 0, #000 2.5%, #000 97.5%, transparent 100%);
        -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 2.5%, #000 97.5%, transparent 100%);
    }
    .ra-vitrin-track {
        display: flex;
        gap: 1rem;
        width: max-content;
        will-change: transform;
        cursor: grab;
        touch-action: pan-y;
    }
    .ra-vitrin-track.is-dragging {
        cursor: grabbing;
        user-select: none;
        -webkit-user-select: none;
    }
    .ra-vitrin-track a.ra-card {
        cursor: pointer;
        -webkit-user-drag: none;
        pointer-events: auto;
    }
    .ra-vitrin-track.is-dragging a.ra-card {
        pointer-events: none;
    }
    .ra-vitrin-item {
        flex: 0 0 min(78vw, 16.75rem);
        width: min(78vw, 16.75rem);
        min-width: 0;
    }
    @media (min-width: 640px) {
        .ra-vitrin-item { flex-basis: 15.75rem; width: 15.75rem; }
    }
    @media (min-width: 1024px) {
        .ra-vitrin-item { flex-basis: 16.5rem; width: 16.5rem; }
    }
    @media (min-width: 1280px) {
        .ra-vitrin-item { flex-basis: 17.25rem; width: 17.25rem; }
    }

    /* Ürün kartı */
    .ra-card {
        height: 100%;
        display: flex;
        flex-direction: column;
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 1.15rem;
        overflow: hidden;
        box-shadow: 0 10px 28px -18px rgba(31,41,55,.32);
        text-decoration: none;
        color: inherit;
        transition: transform .25s cubic-bezier(.22,1,.36,1), box-shadow .25s ease, border-color .25s ease;
    }
    .ra-card:hover {
        transform: translateY(-5px);
        border-color: rgba(231,181,138,.65);
        box-shadow: 0 20px 36px -16px rgba(201,106,43,.28);
    }
    .ra-card-media {
        position: relative;
        aspect-ratio: 1 / 1;
        background: linear-gradient(160deg, #FFF7ED 0%, #F8FAFC 100%);
        overflow: hidden;
    }
    .ra-card-media--wide { aspect-ratio: 4 / 3; }
    .ra-card-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .5s cubic-bezier(.22,1,.36,1);
        pointer-events: none;
    }
    .ra-card:hover .ra-card-media img { transform: scale(1.06); }
    .ra-card-badge {
        position: absolute;
        top: 0.65rem;
        left: 0.65rem;
        z-index: 2;
        max-width: calc(100% - 1.3rem);
        padding: 0.28rem 0.55rem;
        border-radius: 9999px;
        font-size: 0.5625rem;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #C96A2B;
        background: rgba(255,255,255,.95);
        border: 1px solid rgba(231,181,138,.4);
        backdrop-filter: blur(6px);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ra-card-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 0.9rem 0.95rem 1rem;
        gap: 0.3rem;
        min-height: 6.75rem;
    }
    .ra-card-title {
        font-size: 0.88rem;
        font-weight: 800;
        letter-spacing: -.02em;
        color: #111827;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color .2s;
    }
    .ra-card:hover .ra-card-title { color: #C96A2B; }
    .ra-card-meta {
        font-size: 0.7rem;
        color: #6B7280;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .ra-card-foot {
        margin-top: auto;
        padding-top: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        border-top: 1px solid #F1F5F9;
    }
    .ra-card-price {
        font-size: 0.82rem;
        font-weight: 800;
        color: #C96A2B;
        letter-spacing: -.02em;
        font-variant-numeric: tabular-nums;
    }
    .ra-card-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2rem;
        padding: 0 0.75rem;
        border-radius: 0.7rem;
        background: #C96A2B;
        color: #fff;
        font-size: 0.62rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
        transition: background .2s, transform .2s;
    }
    .ra-card:hover .ra-card-cta {
        background: #B55A20;
        transform: translateX(1px);
    }
    .ra-card-stars {
        color: #C96A2B;
        font-size: 0.68rem;
        letter-spacing: .04em;
        font-weight: 700;
    }
    .ra-card-avatar-fallback {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(160deg, #FFF7ED, #F8FAFC);
        color: #C96A2B;
        font-weight: 800;
        font-size: 1.75rem;
        letter-spacing: -.03em;
    }
    .ra-quote {
        height: 100%;
        min-height: 11.5rem;
        display: flex;
        flex-direction: column;
        padding: 1.1rem;
        border-radius: 1.15rem;
        background: linear-gradient(160deg, #fff 0%, #FFFBF7 100%);
        border: 1px solid #E5E7EB;
        box-shadow: 0 10px 28px -18px rgba(31,41,55,.28);
    }
    @media (prefers-reduced-motion: reduce) {
        .ra-card, .ra-card-media img { transition: none; }
    }
</style>

{{-- Öne çıkan uzmanlar --}}
<section id="doktorlar" class="ra-vitrin-section max-w-7xl mx-auto px-4 sm:px-6 pt-8 pb-8 md:pt-10 md:pb-10">
    <div class="ra-vitrin-head">
        <div>
            <div class="ra-vitrin-kicker">Seçkiler</div>
            <h2 class="text-2xl md:text-3xl font-bold font-display text-[#111827] tracking-tight">Öne Çıkan Uzmanlar</h2>
            <p class="text-sm text-[#6B7280] mt-1">Danışan memnuniyeti yüksek, randevuya açık uzmanlar.</p>
        </div>
        <div class="ra-vitrin-nav">
            <button type="button" class="ra-vitrin-btn" data-vitrin-prev="uzmanlar" aria-label="Önceki">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" class="ra-vitrin-btn" data-vitrin-next="uzmanlar" aria-label="Sonraki">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            <a href="{{ route('frontend.hekimler') }}" class="ra-vitrin-link">Tümü →</a>
        </div>
    </div>

    <div class="ra-vitrin" data-vitrin="uzmanlar">
        <div class="ra-vitrin-viewport">
            <div class="ra-vitrin-track" data-vitrin-track="uzmanlar" data-speed="0.65">
                @forelse($oneCikanDoktorlar as $doktor)
                    @php
                        $brans = $doktor->branslar->first();
                        $bransAd = $brans ? $brans->ad : 'Uzman';
                        $initials = collect(explode(' ', $doktor->ad_soyad))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
                        $ortalamaPuan = $doktor->ortalama_puan_cache ?? 0;
                        $yorumSayisi = $doktor->yorum_sayisi_cache ?? 0;
                    @endphp
                    <div class="ra-vitrin-item">
                        <a href="{{ $doktor->profil_url }}" class="ra-card">
                            <div class="ra-card-media">
                                <span class="ra-card-badge">{{ $bransAd }}</span>
                                @if($doktor->profil_resmi)
                                    <img src="{{ asset($doktor->profil_resmi) }}" alt="{{ $doktor->ad_soyad }}" loading="lazy" draggable="false">
                                @else
                                    <div class="ra-card-avatar-fallback">{{ $initials }}</div>
                                @endif
                            </div>
                            <div class="ra-card-body">
                                <h3 class="ra-card-title">{{ $doktor->unvan ? $doktor->unvan.' ' : '' }}{{ $doktor->ad_soyad }}</h3>
                                <p class="ra-card-meta">{{ $doktor->uzmanlik_alani ?? $bransAd }}@if($doktor->il) · {{ $doktor->il->ad }}@endif</p>
                                @if($ortalamaPuan)
                                    <div class="ra-card-stars">★ {{ $ortalamaPuan }} <span class="text-[#9CA3AF] font-semibold">({{ $yorumSayisi }})</span></div>
                                @endif
                                <div class="ra-card-foot">
                                    <span class="ra-card-price" style="font-size:0.72rem;color:#6B7280">{{ $doktor->il?->ad ?? 'Türkiye' }}</span>
                                    <span class="ra-card-cta">Randevu Al</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="ra-vitrin-item" style="width:100%;flex-basis:100%">
                        <p class="text-center text-sm text-[#6B7280] py-10">Henüz öne çıkan uzman bulunmuyor.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

{{-- Öne çıkan klinikler --}}
<section id="klinikler" class="ra-vitrin-section bg-white border-y border-[#E5E7EB] py-8 md:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="ra-vitrin-head">
            <div>
                <div class="ra-vitrin-kicker">Klinikler</div>
                <h2 class="text-2xl md:text-3xl font-bold font-display text-[#111827] tracking-tight">Öne Çıkan Klinikler</h2>
                <p class="text-sm text-[#6B7280] mt-1">Aktif klinik ve poliklinikler, tek bakışta.</p>
            </div>
            <div class="ra-vitrin-nav">
                <button type="button" class="ra-vitrin-btn" data-vitrin-prev="klinikler" aria-label="Önceki">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" class="ra-vitrin-btn" data-vitrin-next="klinikler" aria-label="Sonraki">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <a href="{{ route('frontend.hekimler', ['sadece_klinik' => 1]) }}" class="ra-vitrin-link">Tümü →</a>
            </div>
        </div>

        <div class="ra-vitrin" data-vitrin="klinikler">
            <div class="ra-vitrin-viewport">
                <div class="ra-vitrin-track" data-vitrin-track="klinikler" data-speed="0.55">
                    @forelse(($oneCikanKlinikler ?? collect()) as $klinik)
                        @php
                            $klinikUrl = route('frontend.klinik.profil', [
                                'il_slug' => $klinik->il->slug ?? 'il',
                                'ilce_slug' => $klinik->ilce->slug ?? 'ilce',
                                'klinik_slug' => $klinik->slug,
                            ]);
                            $initials = mb_strtoupper(mb_substr($klinik->ad, 0, 2));
                        @endphp
                        <div class="ra-vitrin-item">
                            <a href="{{ $klinikUrl }}" class="ra-card">
                                <div class="ra-card-media ra-card-media--wide">
                                    <span class="ra-card-badge">Klinik</span>
                                    @if($klinik->logo)
                                        <img src="{{ asset($klinik->logo) }}" alt="{{ $klinik->ad }}" loading="lazy" draggable="false">
                                    @else
                                        <div class="ra-card-avatar-fallback">{{ $initials }}</div>
                                    @endif
                                </div>
                                <div class="ra-card-body">
                                    <h3 class="ra-card-title">{{ $klinik->ad }}</h3>
                                    <p class="ra-card-meta">{{ $klinik->il?->ad }}{{ $klinik->ilce?->ad ? ', '.$klinik->ilce->ad : '' }}</p>
                                    <div class="ra-card-foot">
                                        <span class="ra-card-price">{{ (int) ($klinik->doktorlar_count ?? 0) }} uzman</span>
                                        <span class="ra-card-cta">İncele</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="ra-vitrin-item" style="width:100%;flex-basis:100%">
                            <p class="text-center text-sm text-[#6B7280] py-10">Henüz öne çıkan klinik bulunmuyor.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Popüler hizmetler --}}
<section id="hizmetler" class="ra-vitrin-section max-w-7xl mx-auto px-4 sm:px-6 py-8 md:py-10">
    <div class="ra-vitrin-head">
        <div>
            <div class="ra-vitrin-kicker">Hizmetler</div>
            <h2 class="text-2xl md:text-3xl font-bold font-display text-[#111827] tracking-tight">Popüler Hizmetler</h2>
            <p class="text-sm text-[#6B7280] mt-1">Randevu alınabilir hizmetler, ürün vitrini gibi kayar.</p>
        </div>
        <div class="ra-vitrin-nav">
            <button type="button" class="ra-vitrin-btn" data-vitrin-prev="hizmetler" aria-label="Önceki">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" class="ra-vitrin-btn" data-vitrin-next="hizmetler" aria-label="Sonraki">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            <a href="{{ route('frontend.hekimler') }}" class="ra-vitrin-link">Keşfet →</a>
        </div>
    </div>

    <div class="ra-vitrin" data-vitrin="hizmetler">
        <div class="ra-vitrin-viewport">
            <div class="ra-vitrin-track" data-vitrin-track="hizmetler" data-speed="0.7">
                @forelse(($oneCikanHizmetler ?? collect()) as $hizmet)
                    <div class="ra-vitrin-item">
                        <a href="{{ $hizmet->url }}" class="ra-card" draggable="false">
                            <div class="ra-card-media ra-card-media--wide">
                                @if($hizmet->sure)
                                    <span class="ra-card-badge">{{ $hizmet->sure }} dk</span>
                                @endif
                                @if($hizmet->resim_url ?? $hizmet->resim)
                                    <img src="{{ $hizmet->resim_url ?? asset($hizmet->resim) }}" alt="{{ $hizmet->ad }}" loading="lazy" draggable="false">
                                @else
                                    <div class="ra-card-avatar-fallback" style="font-size:0">
                                        <svg class="w-12 h-12 text-[#C96A2B]/45" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="ra-card-body">
                                <h3 class="ra-card-title">{{ $hizmet->ad }}</h3>
                                <p class="ra-card-meta">
                                    {{ $hizmet->doktor ? (($hizmet->doktor->unvan ? $hizmet->doktor->unvan.' ' : '').$hizmet->doktor->ad_soyad) : 'Uzman hizmeti' }}
                                </p>
                                <div class="ra-card-foot">
                                    <span class="ra-card-price" style="font-size:0.75rem">Randevu al</span>
                                    <span class="ra-card-cta" role="presentation">Seç</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="ra-vitrin-item" style="width:100%;flex-basis:100%">
                        <p class="text-center text-sm text-[#6B7280] py-10">Henüz listelenecek hizmet bulunmuyor.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

{{-- Uzman blogları --}}
@if(isset($sonBloglar) && $sonBloglar->count() > 0)
<section id="bloglar" class="ra-vitrin-section bg-white border-t border-[#E5E7EB] py-8 md:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="ra-vitrin-head">
            <div>
                <div class="ra-vitrin-kicker">İçerik</div>
                <h2 class="text-2xl md:text-3xl font-bold font-display text-[#111827] tracking-tight">Uzman Blogları</h2>
                <p class="text-sm text-[#6B7280] mt-1">Uzmanlardan güncel yazılar, otomatik vitrin.</p>
            </div>
            <div class="ra-vitrin-nav">
                <button type="button" class="ra-vitrin-btn" data-vitrin-prev="bloglar" aria-label="Önceki">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" class="ra-vitrin-btn" data-vitrin-next="bloglar" aria-label="Sonraki">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <a href="{{ route('frontend.blog.index') }}" class="ra-vitrin-link">Tüm Bloglar →</a>
            </div>
        </div>

        <div class="ra-vitrin" data-vitrin="bloglar">
            <div class="ra-vitrin-viewport">
                <div class="ra-vitrin-track" data-vitrin-track="bloglar" data-speed="0.5">
                    @foreach($sonBloglar as $blog)
                        <div class="ra-vitrin-item">
                            <a href="{{ $blog->url }}" class="ra-card">
                                <div class="ra-card-media ra-card-media--wide">
                                    <span class="ra-card-badge">Blog</span>
                                    @if($blog->resim)
                                        <img src="{{ asset($blog->resim) }}" alt="{{ $blog->baslik }}" loading="lazy" draggable="false">
                                    @else
                                        <div class="ra-card-avatar-fallback" style="font-size:0">
                                            <svg class="w-12 h-12 text-[#C96A2B]/35" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ra-card-body">
                                    <h3 class="ra-card-title">{{ $blog->baslik }}</h3>
                                    <p class="ra-card-meta">
                                        {{ $blog->doktor ? (($blog->doktor->unvan ? $blog->doktor->unvan.' ' : '').$blog->doktor->ad_soyad) : 'Uzman' }}
                                    </p>
                                    <div class="ra-card-foot">
                                        <span class="text-[10px] font-bold text-[#9CA3AF]">{{ $blog->created_at->format('d.m.Y') }}</span>
                                        <span class="ra-card-cta">Oku</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- Yorumlar --}}
@if(isset($sonYorumlar) && $sonYorumlar->count() > 0)
<section class="ra-vitrin-section border-t border-[#E5E7EB] py-8 md:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="ra-vitrin-head">
            <div>
                <div class="ra-vitrin-kicker">Güven</div>
                <h2 class="text-2xl md:text-3xl font-bold font-display text-[#111827] tracking-tight">Hastalarımız Ne Diyor?</h2>
                <p class="text-sm text-[#6B7280] mt-1">Gerçek hasta değerlendirmeleri.</p>
            </div>
            <div class="ra-vitrin-nav">
                <button type="button" class="ra-vitrin-btn" data-vitrin-prev="yorumlar" aria-label="Önceki">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" class="ra-vitrin-btn" data-vitrin-next="yorumlar" aria-label="Sonraki">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <div class="ra-vitrin" data-vitrin="yorumlar">
            <div class="ra-vitrin-viewport">
                <div class="ra-vitrin-track" data-vitrin-track="yorumlar" data-speed="0.45">
                    @foreach($sonYorumlar as $yorum)
                        <div class="ra-vitrin-item">
                            <div class="ra-quote">
                                <div class="flex items-center gap-0.5 mb-3 text-[#C96A2B] text-sm">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= $yorum->puan ? '' : 'text-[#E5E7EB]' }}">★</span>
                                    @endfor
                                </div>
                                <p class="text-sm text-[#374151] leading-relaxed italic flex-grow">"{{ Str::limit($yorum->yorum, 130) }}"</p>
                                <div class="mt-4 pt-3 border-t border-[#F1F5F9] flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-[#111827] truncate">{{ $yorum->hasta ? $yorum->hasta->maskeli_ad : 'Anonim Hasta' }}</p>
                                        <p class="text-[10px] text-[#6B7280] truncate">{{ $yorum->doktor ? (($yorum->doktor->unvan ? $yorum->doktor->unvan.' ' : '').$yorum->doktor->ad_soyad) : '' }}</p>
                                    </div>
                                    <span class="text-[10px] text-[#9CA3AF] shrink-0">{{ $yorum->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif
