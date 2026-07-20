@php
    $aktifHizmetler = $doktor->hizmetler->where('aktif_mi', true)->values();
    $hastaAuth = Auth::guard('hasta')->check();
    $hastaUser = $hastaAuth ? Auth::guard('hasta')->user() : null;
    $onlineGorusmeAcik = (bool) $doktor->aktifPaket()?->hasFeature('online_gorusme');
    $slotsUrl = route('frontend.doktorlar.slotlar', $doktor->id);
    $daysUrl = route('frontend.doktorlar.musait-gunler', $doktor->id);
    $formAction = $hastaAuth
        ? route('frontend.hasta.randevu.kaydet')
        : route('frontend.hasta.randevu.misafir');
    $calismaGunleri = $doktor->calismaSaatleri
        ->where('aktif_mi', true)
        ->pluck('gun')
        ->map(fn ($g) => (int) $g)
        ->values()
        ->all();
    // Hizmet detay vb. sayfalardan gelen ön seçim
    $preselectedHizmetId = $preselectedHizmetId ?? null;
    $initialHizmetId = old('hizmet_id', $preselectedHizmetId);
@endphp

@if($doktor->randevuya_acik_mi && $aktifHizmetler->isNotEmpty())
<section id="randevu-wizard" class="rw{{ !empty($wizardCompact) ? ' rw--compact' : '' }}">
    <div class="rw-shell">
        <header class="rw-head">
            <div class="rw-head-main">
                <h2 class="rw-title">Randevu al</h2>
                <p class="rw-caption" id="rw-step-caption">Hizmet seçin</p>
            </div>
            <div class="rw-head-tools">
                <div class="rw-svc-search" id="rw-svc-search-wrap">
                    <svg class="rw-svc-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m0 0A7.5 7.5 0 105.2 5.2a7.5 7.5 0 0010.6 10.6z"/>
                    </svg>
                    <input type="search"
                           id="rw-svc-search"
                           class="rw-svc-search-input"
                           placeholder="Hizmet ara…"
                           autocomplete="off"
                           enterkeyhint="search"
                           aria-label="Hizmet ara">
                    <button type="button" id="rw-svc-search-clear" class="rw-svc-search-clear" hidden aria-label="Aramayı temizle">×</button>
                </div>
                <div class="rw-dots" id="rw-dots" aria-hidden="true">
                    @for($i = 1; $i <= 3; $i++)
                        <span class="rw-dot{{ $i === 1 ? ' is-active' : '' }}" data-dot="{{ $i }}"></span>
                    @endfor
                </div>
            </div>
        </header>

        @if(session('basarili') || session('hata'))
            <div class="rw-flash">
                @if(session('basarili'))
                    <div class="rw-flash-ok">{{ session('basarili') }}</div>
                @endif
                @if(session('hata'))
                    <div class="rw-flash-err">{{ session('hata') }}</div>
                @endif
            </div>
        @endif

        <form action="{{ $formAction }}" method="POST" id="rw-form" class="rw-body">
            @csrf
            <input type="hidden" name="doktor_id" value="{{ $doktor->id }}">
            <input type="hidden" name="hizmet_id" id="rw-hizmet-id" value="{{ $initialHizmetId }}">
            <input type="hidden" name="tarih" id="rw-tarih" value="{{ old('tarih') }}">
            <input type="hidden" name="saat" id="rw-saat" value="{{ old('saat') }}">
            @unless($hastaAuth)
                <input type="hidden" name="recaptcha_token" id="rw-recaptcha-token" value="">
                <div class="rw-hp" aria-hidden="true">
                    <input type="text" name="{{ config('randevu.honeypot_field', 'website_url') }}" value="" tabindex="-1" autocomplete="off">
                </div>
            @endunless

            <div id="rw-summary" class="rw-summary" hidden></div>

            {{-- 1 Hizmet — max 8 görünür, kaydırmalı + arama --}}
            <div class="rw-panel" data-panel="1">
                <div class="rw-svc-meta">
                    <span id="rw-svc-count" class="rw-svc-count">{{ $aktifHizmetler->count() }} hizmet</span>
                    @if($aktifHizmetler->count() > 8)
                        <span class="rw-svc-hint">Kaydırarak tümünü görün · en fazla 8 kart aynı anda</span>
                    @endif
                </div>
                <div class="rw-svc-scroll" id="rw-svc-scroll">
                    <div class="rw-svc-grid" id="rw-svc-grid">
                        @foreach($aktifHizmetler as $hizmet)
                            @php
                                $sure = (int) ($hizmet->sure ?? 0);
                                $resimUrl = $hizmet->resim_url;
                                $searchBlob = mb_strtolower(trim(
                                    ($hizmet->ad ?? '').' '.strip_tags((string) ($hizmet->aciklama ?? ''))
                                ));
                            @endphp
                            <button type="button"
                                    class="rw-svc rw-hizmet-card"
                                    data-id="{{ $hizmet->id }}"
                                    data-ad="{{ $hizmet->ad }}"
                                    data-sure="{{ $sure }}"
                                    data-search="{{ e($searchBlob) }}">
                                <span class="rw-svc-radio" aria-hidden="true"></span>
                                <span class="rw-svc-media" aria-hidden="true">
                                    @if($resimUrl)
                                        <img src="{{ $resimUrl }}" alt="" class="rw-svc-img" loading="lazy">
                                    @else
                                        <span class="rw-svc-icon">
                                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                    @endif
                                    <span class="rw-svc-shine" aria-hidden="true"></span>
                                    <span class="rw-svc-media-overlay" aria-hidden="true"></span>
                                </span>
                                <span class="rw-svc-body">
                                    <span class="rw-svc-name">{{ $hizmet->ad }}</span>
                                    @if($hizmet->aciklama)
                                        <span class="rw-svc-desc">{{ Str::limit(strip_tags($hizmet->aciklama), 48) }}</span>
                                    @endif
                                    @if($sure > 0)
                                        <span class="rw-svc-tags">
                                            <span class="rw-tag">{{ $sure }} dk</span>
                                        </span>
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
                <p id="rw-svc-empty" class="rw-empty" hidden>Aramanıza uygun hizmet bulunamadı.</p>
                <p id="rw-err-1" class="rw-err" hidden>Bir hizmet seçin</p>
            </div>

            {{-- 2 Tarih + Saat (yan yana) --}}
            <div class="rw-panel" data-panel="2" hidden>
                <div class="rw-datetime">
                    <div class="rw-datetime-cal">
                        <p class="rw-label">Tarih seçin</p>
                        <div class="rw-cal">
                            <div class="rw-cal-nav">
                                <button type="button" id="rw-cal-prev" class="rw-icon-btn" aria-label="Önceki ay">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                                </button>
                                <p id="rw-cal-title" class="rw-cal-title"></p>
                                <button type="button" id="rw-cal-next" class="rw-icon-btn" aria-label="Sonraki ay">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </button>
                            </div>
                            <div class="rw-cal-weekdays">
                                @foreach(['Pt','Sa','Ça','Pe','Cu','Ct','Pz'] as $d)
                                    <span>{{ $d }}</span>
                                @endforeach
                            </div>
                            <div id="rw-cal-grid" class="rw-cal-grid"></div>
                            <div class="rw-legend">
                                <span><i class="rw-lg-on"></i> Müsait</span>
                                <span><i class="rw-lg-off"></i> Kapalı</span>
                                <span><i class="rw-lg-sel"></i> Seçili</span>
                            </div>
                            <p id="rw-cal-loading" class="rw-empty" hidden style="padding:0.5rem 0 0">Günler kontrol ediliyor…</p>
                        </div>
                    </div>
                    <div class="rw-datetime-slots">
                        <p class="rw-label">
                            Saat seçin
                            <span id="rw-saat-tarih-label" class="rw-saat-date"></span>
                        </p>
                        <div id="rw-slots-placeholder" class="rw-empty rw-slots-ph">
                            Soldan bir tarih seçin; müsait saatler burada listelenir.
                        </div>
                        <div id="rw-slots-loading" class="rw-empty" hidden>Saatler yükleniyor…</div>
                        <div id="rw-slots-empty" class="rw-empty" hidden>
                            <p>Bu günde müsait saat yok.</p>
                            <p class="rw-empty-hint">Başka bir gün seçin.</p>
                        </div>
                        <div id="rw-slots-grid" class="rw-slots"></div>
                        <div class="rw-legend rw-slots-legend" id="rw-slots-legend" hidden>
                            <span><i class="rw-lg-on"></i> Müsait</span>
                            <span><i class="rw-lg-busy"></i> Dolu</span>
                        </div>
                    </div>
                </div>
                <p id="rw-err-2" class="rw-err" hidden>Müsait bir gün ve saat seçin</p>
            </div>

            {{-- 3 Bilgi --}}
            <div class="rw-panel" data-panel="3" hidden>
                @if($onlineGorusmeAcik)
                    <p class="rw-label">Görüşme türü</p>
                    <div class="rw-cards rw-cards-2 mb">
                        <button type="button" class="rw-card rw-gorusme-card is-selected" data-value="yuz_yuze">
                            <span class="rw-check" aria-hidden="true"></span>
                            <span class="rw-card-body">
                                <span class="rw-card-title">Yüz yüze</span>
                                <span class="rw-card-meta">Muayenehanede</span>
                            </span>
                        </button>
                        <button type="button" class="rw-card rw-gorusme-card" data-value="online">
                            <span class="rw-check" aria-hidden="true"></span>
                            <span class="rw-card-body">
                                <span class="rw-card-title">Online</span>
                                <span class="rw-card-meta">Görüntülü görüşme</span>
                            </span>
                        </button>
                    </div>
                    <input type="hidden" name="gorusme_tipi" id="rw-gorusme" value="{{ old('gorusme_tipi', 'yuz_yuze') }}">
                @else
                    <input type="hidden" name="gorusme_tipi" value="yuz_yuze">
                @endif

                @if($hastaAuth)
                    <div class="rw-patient">
                        <p class="rw-label">Randevu sahibi</p>
                        <p class="rw-patient-name">{{ $hastaUser->ad_soyad }}</p>
                        <p class="rw-patient-meta">{{ $hastaUser->telefon }} · {{ $hastaUser->e_posta }}</p>
                    </div>
                @else
                    <div class="rw-fields">
                        <div class="rw-field">
                            <label for="rw-ad">Ad</label>
                            <input id="rw-ad" type="text" name="ad" value="{{ old('ad') }}" required autocomplete="given-name">
                        </div>
                        <div class="rw-field">
                            <label for="rw-soyad">Soyad</label>
                            <input id="rw-soyad" type="text" name="soyad" value="{{ old('soyad') }}" required autocomplete="family-name">
                        </div>
                        <div class="rw-field">
                            <label for="rw-tel">Telefon</label>
                            <input id="rw-tel" type="tel" name="telefon" value="{{ old('telefon') }}" required
                                   inputmode="numeric" pattern="05[0-9]{9}" maxlength="11"
                                   placeholder="05xxxxxxxxx" autocomplete="tel-national"
                                   title="05 ile başlayan 11 haneli numara">
                            <span class="rw-field-hint">Yalnızca rakam · 05 ile başlamalı · 11 hane</span>
                        </div>
                        <div class="rw-field">
                            <label for="rw-mail">E-posta</label>
                            <input id="rw-mail" type="email" name="e_posta" value="{{ old('e_posta') }}" required autocomplete="email" placeholder="ornek@email.com">
                        </div>
                    </div>
                @endif

                <div class="rw-field">
                    <label for="rw-not">Not <em>(opsiyonel)</em></label>
                    <textarea id="rw-not" name="not" rows="2" placeholder="Şikayet veya notunuz…">{{ old('not') }}</textarea>
                </div>

                @unless($hastaAuth)
                    <label class="rw-checkline">
                        <input type="checkbox" name="kvkk_onay" value="1" required>
                        <span>Kişisel verilerimin randevu amacıyla işlenmesini kabul ediyorum.</span>
                    </label>
                    <p class="rw-login-hint">Hesabınız var mı? <a href="{{ route('frontend.hasta.giris') }}">Giriş yapın</a></p>
                    <p class="rw-login-hint" style="margin-top:0.35rem">Randevularınızı takip etmek için <a href="{{ route('frontend.hasta.kayit') }}">ücretsiz üye olun</a> — randevu kodunuz e-posta ile de gelir.</p>
                @endunless
            </div>

            <div class="rw-foot">
                <button type="button" id="rw-btn-back" class="rw-btn rw-btn-ghost" hidden>Geri</button>
                <span class="rw-foot-spacer"></span>
                <button type="button" id="rw-btn-next" class="rw-btn rw-btn-primary">İleri</button>
                <button type="submit" id="rw-btn-submit" class="rw-btn rw-btn-primary" hidden>Randevu oluştur</button>
            </div>
        </form>
    </div>
</section>

<style>
/* ── Randevu Wizard (self-contained, no invalid Tailwind deps) ── */
#randevu-wizard.rw {
    margin-bottom: 0;
    scroll-margin-top: 6rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 100%;
    min-width: 0;
}
#randevu-wizard .rw-shell {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    min-height: 32rem;
    height: 100%;
    width: 100%;
    max-width: 100%;
    min-width: 0;
}
/* Hizmet detay sidebar: tam yükseklik zorlamasın, taşma olmasın */
#randevu-wizard.rw--compact {
    height: auto;
}
#randevu-wizard.rw--compact .rw-shell {
    height: auto;
    min-height: 0;
}
#randevu-wizard.rw--compact .rw-body {
    flex: 0 1 auto;
}
#randevu-wizard.rw--compact .rw-panel:not([hidden]) {
    flex: 0 1 auto;
}
#randevu-wizard .rw-body {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    min-height: 0;
}
#randevu-wizard .rw-panel:not([hidden]) {
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    flex-direction: column;
}
#randevu-wizard .rw-foot {
    margin-top: auto;
}
#randevu-wizard *,
#randevu-wizard *::before,
#randevu-wizard *::after {
    box-sizing: border-box;
}
#randevu-wizard button {
    font-family: inherit;
    appearance: none;
    -webkit-appearance: none;
    margin: 0;
    cursor: pointer;
}
#randevu-wizard button:disabled {
    cursor: not-allowed;
}
#randevu-wizard input,
#randevu-wizard textarea {
    font-family: inherit;
    margin: 0;
}

.rw-shell {
    background: #fff;
    border: 1px solid #E5E7EB;
    border-radius: 1.5rem;
    box-shadow: 0 8px 30px rgba(31, 41, 55, 0.04);
    overflow: hidden;
}

.rw-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.85rem;
    flex-wrap: wrap;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #F1F5F9;
    background: linear-gradient(90deg, #FFF7ED 0%, #fff 55%);
}
.rw-head-main { min-width: 0; flex: 1 1 auto; }
.rw-head-tools {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
    flex-shrink: 0;
}
.rw-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.02em;
    line-height: 1.25;
}
.rw-caption {
    margin: 0.2rem 0 0;
    font-size: 0.75rem;
    color: #94A3B8;
}
/* Hizmet arama — başlık yanında */
.rw-svc-search {
    position: relative;
    display: flex;
    align-items: center;
    min-width: 11.5rem;
    max-width: 16rem;
    width: min(100%, 16rem);
    height: 2.4rem;
    padding: 0 0.55rem 0 2rem;
    border-radius: 9999px;
    border: 1px solid #E8ECF1;
    background: #fff;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.rw-svc-search:focus-within {
    border-color: #C96A2B;
    box-shadow: 0 0 0 3px rgba(201, 106, 43, 0.12);
}
.rw-svc-search[hidden] { display: none !important; }
.rw-svc-search-icon {
    position: absolute;
    left: 0.65rem;
    color: #C96A2B;
    pointer-events: none;
    opacity: 0.9;
}
.rw-svc-search-input {
    width: 100%;
    border: 0 !important;
    outline: none !important;
    background: transparent !important;
    box-shadow: none !important;
    font-size: 0.8rem !important;
    font-weight: 500;
    color: #111827;
    padding: 0.35rem 1.4rem 0.35rem 0 !important;
    height: auto !important;
}
.rw-svc-search-input::placeholder { color: #9CA3AF; font-weight: 400; }
.rw-svc-search-clear {
    position: absolute;
    right: 0.35rem;
    width: 1.35rem;
    height: 1.35rem;
    border: 0;
    border-radius: 999px;
    background: #F1F5F9;
    color: #64748B;
    font-size: 1rem;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}
.rw-svc-search-clear:hover { background: #E2E8F0; color: #111827; }
.rw-svc-search-clear[hidden] { display: none !important; }
.rw-svc-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.35rem 0.75rem;
    margin-bottom: 0.65rem;
}
.rw-svc-count {
    font-size: 0.7rem;
    font-weight: 700;
    color: #64748B;
    letter-spacing: 0.02em;
}
.rw-svc-hint {
    font-size: 0.65rem;
    font-weight: 600;
    color: #94A3B8;
}
/* Max ~8 kart (2 satır × 4 kolon) görünür, kaydırılabilir */
.rw-svc-scroll {
    max-height: 26.5rem;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 0.25rem;
    margin-right: -0.15rem;
    scrollbar-width: thin;
    scrollbar-color: #E7B58A transparent;
}
.rw-svc-scroll::-webkit-scrollbar { width: 6px; }
.rw-svc-scroll::-webkit-scrollbar-thumb {
    background: #E7B58A;
    border-radius: 99px;
}
.rw-svc-scroll::-webkit-scrollbar-track { background: transparent; }
@media (max-width: 639px) {
    /* mobil 2 kolon × 4 satır ≈ 8 kart */
    .rw-svc-scroll { max-height: 34rem; }
}
.rw-dots {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-shrink: 0;
}
.rw-dot {
    width: 0.4rem;
    height: 0.4rem;
    border-radius: 999px;
    background: #E2E8F0;
    transition: width 0.25s ease, background 0.25s ease;
}
.rw-dot.is-active {
    width: 1.35rem;
    background: #C96A2B;
}
.rw-dot.is-done {
    width: 0.4rem;
    background: #34D399;
}

.rw-flash { padding: 0.85rem 1.35rem 0; }
.rw-flash-ok,
.rw-flash-err {
    padding: 0.7rem 0.85rem;
    border-radius: 0.75rem;
    font-size: 0.8rem;
    font-weight: 500;
}
.rw-flash-ok { background: #ECFDF5; color: #047857; }
.rw-flash-err { background: #FEF2F2; color: #B91C1C; }

.rw-body { padding: 1.25rem 1.35rem 1.35rem; }
.rw-hp { position: absolute; left: -9999px; height: 0; overflow: hidden; }

.rw-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-bottom: 1rem;
}
.rw-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.7rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #C96A2B;
    background: #FFF7ED;
    border: 1px solid #F3D5B5;
}

.rw-panel[hidden] { display: none !important; }

/* Tarih + saat yan yana */
.rw-datetime {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.15rem;
    flex: 1 1 auto;
    min-height: 0;
}
@media (min-width: 720px) {
    .rw-datetime {
        grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
        gap: 1.25rem;
        align-items: start;
    }
}
.rw-datetime-cal,
.rw-datetime-slots {
    min-width: 0;
    min-height: 16rem;
    padding: 0.85rem 0.9rem 1rem;
    border: 1px solid #F1F5F9;
    border-radius: 1.1rem;
    background: #FAFAFA;
}
.rw-datetime-slots {
    display: flex;
    flex-direction: column;
    max-height: 22rem;
}
.rw-saat-date {
    display: block;
    margin-top: 0.2rem;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0;
    text-transform: none;
    color: #C96A2B;
}
.rw-slots-ph { color: #94A3B8; font-size: 0.8rem; line-height: 1.45; }
.rw-empty-hint { margin: 0.35rem 0 0; font-size: 0.75rem; color: #94A3B8; }
.rw-datetime-slots .rw-slots {
    flex: 1 1 auto;
    overflow-y: auto;
    max-height: 14rem;
    padding-right: 0.15rem;
}
.rw-slots-legend { margin-top: 0.65rem; }

/* ── Premium service cards: 2 / 3 / 4 columns ── */
.rw-svc-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}
@media (min-width: 640px) {
    .rw-svc-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.85rem; }
}
@media (min-width: 960px) {
    .rw-svc-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.9rem; }
}

.rw-svc {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    text-align: left;
    gap: 0;
    width: 100%;
    padding: 0;
    overflow: hidden;
    border-radius: 1.15rem;
    border: 1.5px solid #E8ECF1;
    background: #fff;
    color: inherit;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    transition:
        border-color 0.35s cubic-bezier(0.22, 1, 0.36, 1),
        box-shadow 0.4s cubic-bezier(0.22, 1, 0.36, 1),
        transform 0.4s cubic-bezier(0.22, 1, 0.36, 1),
        background 0.3s ease;
}
.rw-svc:hover {
    border-color: #E7B58A;
    box-shadow:
        0 16px 36px rgba(201, 106, 43, 0.14),
        0 4px 12px rgba(15, 23, 42, 0.06);
    transform: translateY(-5px);
}
.rw-svc:active {
    transform: translateY(-2px) scale(0.99);
}
.rw-svc.is-selected {
    border-color: #C96A2B;
    background: #fff;
    box-shadow:
        0 0 0 1.5px #C96A2B,
        0 18px 40px rgba(201, 106, 43, 0.18);
    transform: translateY(-3px);
}

.rw-svc-radio {
    position: absolute;
    top: 0.55rem;
    right: 0.55rem;
    z-index: 4;
    width: 1.15rem;
    height: 1.15rem;
    border-radius: 999px;
    border: 2px solid rgba(255, 255, 255, 0.85);
    background: rgba(15, 23, 42, 0.25);
    backdrop-filter: blur(4px);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
    transition: border-color 0.25s ease, background 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
}
.rw-svc:hover .rw-svc-radio {
    background: rgba(15, 23, 42, 0.35);
    transform: scale(1.06);
}
.rw-svc.is-selected .rw-svc-radio {
    border-color: #fff;
    background: #C96A2B;
    box-shadow: 0 0 0 3px rgba(201, 106, 43, 0.35), 0 2px 8px rgba(201, 106, 43, 0.4);
    transform: scale(1.08);
}
.rw-svc.is-selected .rw-svc-radio::after {
    content: '';
    position: absolute;
    left: 0.28rem;
    top: 0.08rem;
    width: 0.28rem;
    height: 0.48rem;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.rw-svc-media {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 11;
    overflow: hidden;
    background: linear-gradient(145deg, #FFF7ED 0%, #FFE8D2 50%, #FDE6D0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.rw-svc-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transform: scale(1);
    transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
}
.rw-svc:hover .rw-svc-img {
    transform: scale(1.1);
}
.rw-svc.is-selected .rw-svc-img {
    transform: scale(1.05);
}

.rw-svc-media-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 40%, rgba(15, 23, 42, 0.18) 100%);
    opacity: 0.55;
    transition: opacity 0.35s ease;
    pointer-events: none;
}
.rw-svc:hover .rw-svc-media-overlay {
    opacity: 0.75;
}
.rw-svc.is-selected .rw-svc-media-overlay {
    background: linear-gradient(180deg, rgba(201, 106, 43, 0.12) 0%, rgba(201, 106, 43, 0.28) 100%);
    opacity: 1;
}

/* Shine sweep on hover */
.rw-svc-shine {
    position: absolute;
    inset: 0;
    z-index: 2;
    background: linear-gradient(
        115deg,
        transparent 30%,
        rgba(255, 255, 255, 0.35) 48%,
        rgba(255, 255, 255, 0.55) 50%,
        rgba(255, 255, 255, 0.35) 52%,
        transparent 70%
    );
    transform: translateX(-120%) skewX(-12deg);
    transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
    pointer-events: none;
}
.rw-svc:hover .rw-svc-shine {
    transform: translateX(120%) skewX(-12deg);
}

.rw-svc-icon {
    color: #C96A2B;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
    transition: transform 0.4s cubic-bezier(0.22, 1, 0.36, 1), color 0.3s ease;
}
.rw-svc:hover .rw-svc-icon {
    transform: scale(1.12);
    color: #B55A20;
}

.rw-svc-body {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.35rem;
    padding: 0.8rem 0.85rem 0.95rem;
    background: #fff;
    transition: background 0.3s ease;
}
.rw-svc:hover .rw-svc-body {
    background: linear-gradient(180deg, #fff 0%, #FFFBF7 100%);
}
.rw-svc.is-selected .rw-svc-body {
    background: linear-gradient(180deg, #FFF7ED 0%, #fff 100%);
}

.rw-svc-name {
    font-size: 0.8rem;
    font-weight: 700;
    color: #0F172A;
    line-height: 1.3;
    letter-spacing: -0.015em;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    max-width: 100%;
    transition: color 0.25s ease;
}
.rw-svc:hover .rw-svc-name,
.rw-svc.is-selected .rw-svc-name {
    color: #C96A2B;
}
.rw-svc-desc {
    font-size: 0.68rem;
    font-weight: 500;
    color: #94A3B8;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    max-width: 100%;
}

.rw-svc-tags {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
    gap: 0.3rem;
    margin-top: 0.15rem;
}
.rw-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 700;
    color: #C96A2B;
    background: #FFF7ED;
    border: 1px solid rgba(231, 181, 138, 0.45);
    transition: background 0.25s ease, border-color 0.25s ease, transform 0.25s ease;
}
.rw-svc:hover .rw-tag {
    background: #FFEDD5;
    border-color: rgba(201, 106, 43, 0.35);
    transform: translateY(-1px);
}
.rw-svc.is-selected .rw-tag {
    background: #C96A2B;
    color: #fff;
    border-color: #C96A2B;
}
.rw-tag-soft {
    color: #64748B;
    background: #F8FAFC;
    border-color: #E2E8F0;
}

@media (max-width: 380px) {
    .rw-svc-body { padding: 0.7rem 0.7rem 0.8rem; }
    .rw-svc-name { font-size: 0.75rem; }
    .rw-svc-media { aspect-ratio: 16 / 12; }
}

@media (prefers-reduced-motion: reduce) {
    .rw-svc,
    .rw-svc-img,
    .rw-svc-shine,
    .rw-svc-radio,
    .rw-svc-icon,
    .rw-tag {
        transition: none !important;
    }
    .rw-svc:hover { transform: none; }
    .rw-svc:hover .rw-svc-img { transform: none; }
    .rw-svc:hover .rw-svc-shine { transform: none; }
}

/* Görüşme kartları (daha sade) */
.rw-cards {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.6rem;
}
@media (min-width: 640px) {
    .rw-cards { grid-template-columns: 1fr 1fr; }
    .rw-cards-2 { grid-template-columns: 1fr 1fr; }
}
.rw-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    text-align: left;
    padding: 0.95rem 1rem;
    border-radius: 1rem;
    border: 1.5px solid #E5E7EB;
    background: #FAFAFA;
    color: inherit;
    transition:
        border-color 0.3s cubic-bezier(0.22, 1, 0.36, 1),
        background 0.3s ease,
        box-shadow 0.35s cubic-bezier(0.22, 1, 0.36, 1),
        transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}
.rw-card:hover {
    border-color: #E7B58A;
    background: #FFFBF5;
    box-shadow: 0 10px 24px rgba(201, 106, 43, 0.1);
    transform: translateY(-2px);
}
.rw-card.is-selected {
    border-color: #C96A2B;
    background: #FFF7ED;
    box-shadow: 0 0 0 1.5px #C96A2B, 0 10px 24px rgba(201, 106, 43, 0.12);
    transform: translateY(-1px);
}
.rw-check {
    width: 1.15rem;
    height: 1.15rem;
    border-radius: 999px;
    border: 2px solid #D1D5DB;
    flex-shrink: 0;
    position: relative;
    transition: border-color 0.15s ease, background 0.15s ease;
}
.rw-card.is-selected .rw-check {
    border-color: #C96A2B;
    background: #C96A2B;
}
.rw-card.is-selected .rw-check::after {
    content: '';
    position: absolute;
    left: 0.28rem;
    top: 0.1rem;
    width: 0.28rem;
    height: 0.5rem;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}
.rw-card-body {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 0;
}
.rw-card-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #111827;
    line-height: 1.3;
}
.rw-card-meta {
    font-size: 0.72rem;
    color: #94A3B8;
    font-weight: 500;
}

/* Calendar */
.rw-cal { max-width: 22rem; margin: 0 auto; }
.rw-cal-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.85rem;
}
.rw-cal-title {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 700;
    color: #111827;
}
.rw-icon-btn {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.75rem;
    border: 1px solid #E5E7EB;
    background: #fff;
    color: #64748B;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.rw-icon-btn:hover {
    background: #FFF7ED;
    color: #C96A2B;
    border-color: #E7B58A;
}
.rw-cal-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.25rem;
    margin-bottom: 0.35rem;
}
.rw-cal-weekdays span {
    text-align: center;
    font-size: 0.65rem;
    font-weight: 700;
    color: #CBD5E1;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.25rem 0;
}
.rw-cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.3rem;
}
.rw-day {
    aspect-ratio: 1 / 1;
    min-height: 0;
    width: 100%;
    border-radius: 0.7rem;
    border: 1px solid transparent;
    background: transparent;
    font-size: 0.8rem;
    font-weight: 700;
    color: #1F2937;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
}
.rw-day.is-empty {
    visibility: hidden;
    pointer-events: none;
}
.rw-day.is-off {
    color: #CBD5E1;
    background: #F8FAFC;
    font-weight: 500;
}
.rw-day.is-full {
    color: #CBD5E1;
    background: #F1F5F9;
    font-weight: 500;
    text-decoration: line-through;
    opacity: 0.85;
}
.rw-day.is-on {
    background: #FAFAFA;
    border-color: #F1F5F9;
}
.rw-day.is-on:hover {
    border-color: #E7B58A;
    background: #FFFBF5;
    color: #C96A2B;
}
.rw-day.is-today:not(.is-selected) {
    box-shadow: inset 0 0 0 1.5px #E7B58A;
}
.rw-day.is-selected {
    background: #C96A2B !important;
    border-color: #C96A2B !important;
    color: #fff !important;
    box-shadow: 0 4px 12px rgba(201, 106, 43, 0.28);
}

/* Slots */
.rw-sub {
    margin: 0 0 0.75rem;
    font-size: 0.8rem;
    color: #64748B;
}
.rw-sub strong { color: #C96A2B; font-weight: 700; }
.rw-slots {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.45rem;
}
@media (min-width: 640px) {
    .rw-slots { grid-template-columns: repeat(4, 1fr); }
}
@media (min-width: 768px) {
    .rw-slots { grid-template-columns: repeat(6, 1fr); }
}
.rw-slot {
    padding: 0.7rem 0.2rem;
    border-radius: 0.75rem;
    border: 1px solid #E5E7EB;
    background: #FAFAFA;
    font-size: 0.8rem;
    font-weight: 700;
    color: #1F2937;
    text-align: center;
    transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
}
.rw-slot.is-free:hover {
    border-color: #E7B58A;
    background: #FFFBF5;
    color: #C96A2B;
}
.rw-slot.is-free.is-selected {
    background: #C96A2B;
    border-color: #C96A2B;
    color: #fff;
    box-shadow: 0 4px 12px rgba(201, 106, 43, 0.25);
}
.rw-slot.is-busy {
    background: #F8FAFC;
    border-color: #F1F5F9;
    color: #CBD5E1;
    text-decoration: line-through;
    font-weight: 500;
}

.rw-empty {
    text-align: center;
    padding: 2rem 0.5rem;
    font-size: 0.8rem;
    color: #94A3B8;
}
.rw-empty p { margin: 0 0 0.5rem; }
.rw-link {
    border: 0;
    background: none;
    color: #C96A2B;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: underline;
    padding: 0;
}

.rw-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem 1rem;
    margin-top: 0.85rem;
    font-size: 0.68rem;
    color: #94A3B8;
}
.rw-legend span {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.rw-legend i {
    display: inline-block;
    width: 0.65rem;
    height: 0.65rem;
    border-radius: 0.25rem;
    border: 1px solid transparent;
}
.rw-lg-on { background: #FFF7ED; border-color: #E7B58A !important; }
.rw-lg-off { background: #F8FAFC; border-color: #E5E7EB !important; }
.rw-lg-sel { background: #C96A2B; }
.rw-lg-busy { background: #F1F5F9; border-color: #E5E7EB !important; }

/* Form fields */
.rw-label {
    margin: 0 0 0.5rem;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #94A3B8;
}
.rw-fields {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.65rem;
    margin-bottom: 0.65rem;
}
@media (min-width: 640px) {
    .rw-fields { grid-template-columns: 1fr 1fr; }
}
.rw-field { margin-bottom: 0.65rem; }
.rw-field label {
    display: block;
    margin-bottom: 0.3rem;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #94A3B8;
}
.rw-field label em {
    font-style: normal;
    font-weight: 500;
    text-transform: none;
    letter-spacing: 0;
    color: #CBD5E1;
}
.rw-field input,
.rw-field textarea {
    width: 100%;
    padding: 0.7rem 0.85rem;
    border: 1px solid #E5E7EB;
    border-radius: 0.75rem;
    font-size: 0.85rem;
    color: #111827;
    background: #fff;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.rw-field input:focus,
.rw-field textarea:focus {
    border-color: #C96A2B;
    box-shadow: 0 0 0 3px rgba(201, 106, 43, 0.12);
}
.rw-field textarea { resize: vertical; min-height: 4rem; }
.rw-field-hint {
    display: block;
    margin-top: 0.3rem;
    font-size: 0.65rem;
    font-weight: 500;
    color: #94A3B8;
}

.rw-patient {
    padding: 0.9rem 1rem;
    border-radius: 1rem;
    background: #FAFAFA;
    border: 1px solid #F1F5F9;
    margin-bottom: 0.85rem;
}
.rw-patient-name {
    margin: 0.15rem 0 0;
    font-size: 0.9rem;
    font-weight: 700;
    color: #111827;
}
.rw-patient-meta {
    margin: 0.15rem 0 0;
    font-size: 0.75rem;
    color: #94A3B8;
}

.rw-checkline {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #64748B;
    cursor: pointer;
    margin: 0.25rem 0 0.5rem;
}
.rw-checkline input {
    margin-top: 0.15rem;
    accent-color: #C96A2B;
}
.rw-login-hint {
    margin: 0;
    font-size: 0.75rem;
    color: #94A3B8;
}
.rw-login-hint a {
    color: #C96A2B;
    font-weight: 600;
    text-decoration: none;
}
.rw-login-hint a:hover { text-decoration: underline; }

.rw-err {
    margin: 0.65rem 0 0;
    font-size: 0.75rem;
    color: #DC2626;
    font-weight: 500;
}
.rw-err[hidden] { display: none !important; }
.mb { margin-bottom: 1rem; }

/* Footer */
.rw-foot {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1.25rem;
    padding-top: 1rem;
    border-top: 1px solid #F1F5F9;
}
.rw-foot-spacer { flex: 1; }
.rw-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 2.75rem;
    padding: 0 1.25rem;
    border-radius: 0.8rem;
    font-size: 0.8rem;
    font-weight: 700;
    border: 0;
    transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
}
.rw-btn[hidden] { display: none !important; }
.rw-btn-primary {
    background: #C96A2B;
    color: #fff;
    box-shadow: 0 2px 8px rgba(201, 106, 43, 0.2);
}
.rw-btn-primary:hover { background: #B55A20; }
.rw-btn-primary:disabled {
    opacity: 0.65;
}
.rw-btn-ghost {
    background: transparent;
    color: #64748B;
    border: 1px solid #E5E7EB;
}
.rw-btn-ghost:hover {
    background: #F8FAFC;
    color: #111827;
}
</style>

@include('frontend.partials.phone_otp_modal')

<script>
(function () {
    const slotsUrl = @json($slotsUrl);
    const daysUrl = @json($daysUrl);
    const isGuest = @json(! $hastaAuth);
    const workDays = @json($calismaGunleri);
    const captions = ['', 'Hizmet seçin', 'Tarih ve saat seçin', 'Bilgilerinizi tamamlayın'];
    const monthNames = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];

    let step = 1;
    const maxStep = 3;
    var slotsRequestId = 0;
    let selectedHizmet = { id: '', ad: '', sure: '' };
    let selectedSaat = '';
    let calYear, calMonth;
    /** @type {Object.<string, Object.<string, boolean>>} ayKey -> { 'Y-m-d': true } */
    var availableDaysCache = {};
    var calRequestId = 0;

    const form = document.getElementById('rw-form');
    if (!form) return;

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    calYear = today.getFullYear();
    calMonth = today.getMonth();

    function pad(n) { return String(n).padStart(2, '0'); }
    function iso(d) {
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function parseIso(s) {
        if (!s) return null;
        const p = s.split('-').map(Number);
        return new Date(p[0], p[1] - 1, p[2]);
    }
    function formatTr(isoStr) {
        const d = parseIso(isoStr);
        if (!d) return '';
        return pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear();
    }
    function dbDay(date) {
        const g = date.getDay();
        return g === 0 ? 7 : g;
    }
    function isWorkDay(date) {
        if (!workDays || !workDays.length) return true;
        return workDays.indexOf(dbDay(date)) !== -1;
    }

    function setStep(n) {
        step = n;
        document.querySelectorAll('.rw-panel').forEach(function (p) {
            const show = Number(p.dataset.panel) === n;
            if (show) p.removeAttribute('hidden');
            else p.setAttribute('hidden', '');
        });
        document.querySelectorAll('.rw-dot').forEach(function (dot) {
            const s = Number(dot.dataset.dot);
            dot.classList.remove('is-active', 'is-done');
            if (s === n) dot.classList.add('is-active');
            else if (s < n) dot.classList.add('is-done');
        });
        var cap = document.getElementById('rw-step-caption');
        if (cap) cap.textContent = captions[n] || '';

        var back = document.getElementById('rw-btn-back');
        var next = document.getElementById('rw-btn-next');
        var sub = document.getElementById('rw-btn-submit');
        if (back) {
            if (n === 1) back.setAttribute('hidden', '');
            else back.removeAttribute('hidden');
        }
        if (next) {
            if (n === maxStep) next.setAttribute('hidden', '');
            else next.removeAttribute('hidden');
        }
        if (sub) {
            if (n === maxStep) sub.removeAttribute('hidden');
            else sub.setAttribute('hidden', '');
        }

        updateSummary();

        // Hizmet arama yalnızca 1. adımda
        var searchWrap = document.getElementById('rw-svc-search-wrap');
        if (searchWrap) {
            if (n === 1) searchWrap.removeAttribute('hidden');
            else searchWrap.setAttribute('hidden', '');
        }

        if (n === 2) {
            renderCalendar();
            // Tarih zaten seçiliyse saatleri yenile
            if (document.getElementById('rw-tarih').value) {
                loadSlots(false);
            } else {
                resetSlotsUi(true);
            }
        }
    }

    // Hizmet arama + görünür liste filtreleme
    (function initHizmetSearch() {
        var input = document.getElementById('rw-svc-search');
        var clearBtn = document.getElementById('rw-svc-search-clear');
        var empty = document.getElementById('rw-svc-empty');
        var countEl = document.getElementById('rw-svc-count');
        var total = document.querySelectorAll('.rw-hizmet-card').length;
        if (!input) return;

        function applyFilter() {
            var q = (input.value || '').trim().toLocaleLowerCase('tr-TR');
            if (clearBtn) clearBtn.hidden = q.length === 0;
            var shown = 0;
            document.querySelectorAll('.rw-hizmet-card').forEach(function (card) {
                var hay = (card.getAttribute('data-search') || card.getAttribute('data-ad') || '').toLocaleLowerCase('tr-TR');
                var ok = !q || hay.indexOf(q) !== -1;
                card.hidden = !ok;
                card.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });
            if (empty) empty.hidden = shown > 0;
            if (countEl) {
                countEl.textContent = q
                    ? (shown + ' / ' + total + ' hizmet')
                    : (total + ' hizmet');
            }
        }

        input.addEventListener('input', applyFilter);
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                applyFilter();
                input.focus();
            });
        }
    })();

    function updateSummary() {
        var box = document.getElementById('rw-summary');
        var chips = [];
        if (selectedHizmet.id) chips.push(selectedHizmet.ad + (selectedHizmet.sure ? ' · ' + selectedHizmet.sure + ' dk' : ''));
        var t = document.getElementById('rw-tarih').value;
        if (t) chips.push(formatTr(t));
        if (selectedSaat) chips.push(selectedSaat);
        if (!chips.length) {
            box.hidden = true;
            box.innerHTML = '';
            return;
        }
        box.hidden = false;
        box.innerHTML = chips.map(function (c) {
            return '<span class="rw-chip">' + c + '</span>';
        }).join('');
    }

    document.querySelectorAll('.rw-hizmet-card').forEach(function (card) {
        card.addEventListener('click', function () {
            document.querySelectorAll('.rw-hizmet-card').forEach(function (c) { c.classList.remove('is-selected'); });
            card.classList.add('is-selected');
            selectedHizmet = { id: card.dataset.id, ad: card.dataset.ad, sure: card.dataset.sure };
            document.getElementById('rw-hizmet-id').value = selectedHizmet.id;
            hideErr(1);
            updateSummary();
        });
    });
    var oldH = document.getElementById('rw-hizmet-id').value;
    var preselectedService = @json((bool) $preselectedHizmetId);
    if (oldH) {
        var c = document.querySelector('.rw-hizmet-card[data-id="' + oldH + '"]');
        if (c) c.click();
    }

    document.querySelectorAll('.rw-gorusme-card').forEach(function (card) {
        card.addEventListener('click', function () {
            document.querySelectorAll('.rw-gorusme-card').forEach(function (x) { x.classList.remove('is-selected'); });
            card.classList.add('is-selected');
            var inp = document.getElementById('rw-gorusme');
            if (inp) inp.value = card.dataset.value;
        });
    });

    function monthKey() {
        return calYear + '-' + pad(calMonth + 1);
    }

    function paintCalendar(availMap) {
        var title = document.getElementById('rw-cal-title');
        var grid = document.getElementById('rw-cal-grid');
        title.textContent = monthNames[calMonth] + ' ' + calYear;
        grid.innerHTML = '';

        var first = new Date(calYear, calMonth, 1);
        var startPad = first.getDay() - 1;
        if (startPad < 0) startPad = 6;
        var daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
        var selectedIso = document.getElementById('rw-tarih').value;
        var todayIso = iso(today);
        availMap = availMap || {};

        // Seçili gün artık müsait değilse temizle
        if (selectedIso && !availMap[selectedIso]) {
            var selDate = parseIso(selectedIso);
            if (selDate && selDate.getFullYear() === calYear && selDate.getMonth() === calMonth) {
                document.getElementById('rw-tarih').value = '';
                selectedIso = '';
                selectedSaat = '';
                document.getElementById('rw-saat').value = '';
                updateSummary();
            }
        }

        for (var i = 0; i < startPad; i++) {
            var empty = document.createElement('button');
            empty.type = 'button';
            empty.className = 'rw-day is-empty';
            empty.tabIndex = -1;
            empty.setAttribute('aria-hidden', 'true');
            grid.appendChild(empty);
        }

        for (var day = 1; day <= daysInMonth; day++) {
            var d = new Date(calYear, calMonth, day);
            d.setHours(0, 0, 0, 0);
            var dIso = iso(d);
            var past = d < today;
            var work = isWorkDay(d);
            var hasSlot = !!availMap[dIso];
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = String(day);

            if (past || !work) {
                btn.className = 'rw-day is-off';
                btn.disabled = true;
                btn.title = past ? 'Geçmiş' : 'Kapalı';
            } else if (!hasSlot) {
                btn.className = 'rw-day is-full';
                btn.disabled = true;
                btn.title = 'Bu günde müsait saat yok';
            } else {
                btn.className = 'rw-day is-on' + (dIso === todayIso ? ' is-today' : '');
                if (dIso === selectedIso) btn.classList.add('is-selected');
                btn.title = 'Müsait';
                btn.addEventListener('click', (function (isoVal) {
                    return function () {
                        document.getElementById('rw-tarih').value = isoVal;
                        selectedSaat = '';
                        document.getElementById('rw-saat').value = '';
                        hideErr(2);
                        updateSummary();
                        paintCalendar(availMap);
                        // Tarih değişince müsait saatler anında yüklensin
                        loadSlots(true);
                    };
                })(dIso));
            }
            grid.appendChild(btn);
        }
    }

    function renderCalendar() {
        var key = monthKey();
        var loading = document.getElementById('rw-cal-loading');
        var title = document.getElementById('rw-cal-title');
        title.textContent = monthNames[calMonth] + ' ' + calYear;

        if (availableDaysCache[key]) {
            if (loading) loading.hidden = true;
            paintCalendar(availableDaysCache[key]);
            return;
        }

        // Önce kapalı/çalışma günü iskeleti
        paintCalendar({});
        if (loading) loading.hidden = false;
        var req = ++calRequestId;

        fetch(daysUrl + '?ay=' + encodeURIComponent(key), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                if (req !== calRequestId) return;
                if (loading) loading.hidden = true;
                var map = {};
                var list = (json.data && json.data.gunler) ? json.data.gunler : [];
                list.forEach(function (d) { map[d] = true; });
                availableDaysCache[key] = map;
                paintCalendar(map);
            })
            .catch(function () {
                if (req !== calRequestId) return;
                if (loading) loading.hidden = true;
                // Hata: çalışma günlerini geçici seçilebilir bırakma — güvenli taraf: hepsini kapalı
                paintCalendar({});
            });
    }

    document.getElementById('rw-cal-prev').addEventListener('click', function () {
        calMonth--;
        if (calMonth < 0) { calMonth = 11; calYear--; }
        if (calYear < today.getFullYear() || (calYear === today.getFullYear() && calMonth < today.getMonth())) {
            calYear = today.getFullYear();
            calMonth = today.getMonth();
        }
        renderCalendar();
    });
    document.getElementById('rw-cal-next').addEventListener('click', function () {
        calMonth++;
        if (calMonth > 11) { calMonth = 0; calYear++; }
        renderCalendar();
    });

    function resetSlotsUi(showPlaceholder) {
        var grid = document.getElementById('rw-slots-grid');
        var loading = document.getElementById('rw-slots-loading');
        var empty = document.getElementById('rw-slots-empty');
        var ph = document.getElementById('rw-slots-placeholder');
        var legend = document.getElementById('rw-slots-legend');
        var label = document.getElementById('rw-saat-tarih-label');
        if (grid) grid.innerHTML = '';
        if (loading) loading.hidden = true;
        if (empty) empty.hidden = true;
        if (ph) ph.hidden = !showPlaceholder;
        if (legend) legend.hidden = true;
        if (label) label.textContent = '';
    }

    function loadSlots(clearSelection) {
        var tarih = document.getElementById('rw-tarih').value;
        var label = document.getElementById('rw-saat-tarih-label');
        var grid = document.getElementById('rw-slots-grid');
        var loading = document.getElementById('rw-slots-loading');
        var empty = document.getElementById('rw-slots-empty');
        var ph = document.getElementById('rw-slots-placeholder');
        var legend = document.getElementById('rw-slots-legend');
        if (!grid) return;

        if (clearSelection !== false) {
            selectedSaat = '';
            document.getElementById('rw-saat').value = '';
            updateSummary();
        }

        grid.innerHTML = '';
        if (!tarih) {
            resetSlotsUi(true);
            return;
        }

        if (label) label.textContent = formatTr(tarih) + ' için müsait saatler';
        if (ph) ph.hidden = true;
        if (empty) empty.hidden = true;
        if (legend) legend.hidden = true;
        if (loading) loading.hidden = false;

        var req = ++slotsRequestId;
        fetch(slotsUrl + '?tarih=' + encodeURIComponent(tarih), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                if (req !== slotsRequestId) return;
                if (loading) loading.hidden = true;
                var slots = (json.data && json.data.slots) ? json.data.slots : [];
                var free = slots.filter(function (s) { return !!s.musait; });
                if (!free.length) {
                    if (empty) empty.hidden = false;
                    if (legend) legend.hidden = true;
                    var mk = tarih.slice(0, 7);
                    if (availableDaysCache[mk]) {
                        delete availableDaysCache[mk][tarih];
                    }
                    // Tarihi temizleme — kullanıcı başka gün seçsin
                    document.getElementById('rw-tarih').value = '';
                    selectedSaat = '';
                    document.getElementById('rw-saat').value = '';
                    updateSummary();
                    paintCalendar(availableDaysCache[monthKey()] || {});
                    return;
                }
                if (empty) empty.hidden = true;
                if (legend) legend.hidden = false;
                slots.forEach(function (slot) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    var ok = !!slot.musait;
                    btn.className = 'rw-slot ' + (ok ? 'is-free' : 'is-busy');
                    btn.textContent = slot.saat;
                    btn.disabled = !ok;
                    if (ok) {
                        btn.addEventListener('click', function () {
                            grid.querySelectorAll('.rw-slot.is-free').forEach(function (b) { b.classList.remove('is-selected'); });
                            btn.classList.add('is-selected');
                            selectedSaat = slot.saat;
                            document.getElementById('rw-saat').value = slot.saat;
                            hideErr(2);
                            updateSummary();
                        });
                    }
                    grid.appendChild(btn);
                });
                // Eski seçim hâlâ müsaitse koru
                var keep = document.getElementById('rw-saat').value;
                if (keep) {
                    var keepBtn = Array.prototype.find.call(grid.querySelectorAll('.rw-slot.is-free'), function (b) {
                        return b.textContent === keep;
                    });
                    if (keepBtn) {
                        keepBtn.classList.add('is-selected');
                        selectedSaat = keep;
                    } else {
                        document.getElementById('rw-saat').value = '';
                        selectedSaat = '';
                        updateSummary();
                    }
                }
            })
            .catch(function () {
                if (req !== slotsRequestId) return;
                if (loading) loading.hidden = true;
                if (empty) empty.hidden = false;
            });
    }

    function hideErr(n) {
        var e = document.getElementById('rw-err-' + n);
        if (e) e.hidden = true;
    }
    function showErr(n) {
        var e = document.getElementById('rw-err-' + n);
        if (e) e.hidden = false;
    }
    function isDateAvailable(isoVal) {
        if (!isoVal) return false;
        var mk = isoVal.slice(0, 7);
        // Cache yoksa (henüz yüklenmedi) tarih alanına güven
        if (!availableDaysCache[mk]) return !!isoVal;
        return !!availableDaysCache[mk][isoVal];
    }
    function validateStep(n) {
        if (n === 1 && !document.getElementById('rw-hizmet-id').value) { showErr(1); return false; }
        if (n === 2) {
            var t = document.getElementById('rw-tarih').value;
            var s = document.getElementById('rw-saat').value;
            if (!t || !isDateAvailable(t) || !s) {
                showErr(2);
                return false;
            }
        }
        return true;
    }

    document.getElementById('rw-btn-next').addEventListener('click', function () {
        if (!validateStep(step)) return;
        if (step < maxStep) setStep(step + 1);
    });
    document.getElementById('rw-btn-back').addEventListener('click', function () {
        if (step > 1) setStep(step - 1);
    });

    if (isGuest) {
        var telEl = document.getElementById('rw-tel');
        if (window.RA_OTP && telEl) {
            window.RA_OTP.bindPhoneInput(telEl);
        }

        form.addEventListener('submit', function (e) {
            if (form.dataset.rcOk === '1') return;
            e.preventDefault();
            if (!document.getElementById('rw-hizmet-id').value ||
                !document.getElementById('rw-tarih').value ||
                !document.getElementById('rw-saat').value) {
                setStep(1);
                return;
            }

            var ad = (document.getElementById('rw-ad') || {}).value || '';
            var soyad = (document.getElementById('rw-soyad') || {}).value || '';
            var tel = (telEl && telEl.value) || '';
            var mail = (document.getElementById('rw-mail') || {}).value || '';
            if (!ad.trim() || !soyad.trim() || !mail.trim()) {
                setStep(3);
                alert('Lütfen ad, soyad ve e-posta alanlarını doldurun.');
                return;
            }
            if (!window.RA_OTP || !window.RA_OTP.isValidPhone(tel)) {
                setStep(3);
                alert('Telefon 05 ile başlamalı ve 11 haneli olmalıdır (yalnızca rakam).');
                if (telEl) telEl.focus();
                return;
            }
            if (telEl) telEl.value = window.RA_OTP.normalizePhone(tel);

            var btn = document.getElementById('rw-btn-submit');
            var resetBtn = function () {
                if (btn) { btn.disabled = false; btn.textContent = 'Randevu oluştur'; }
            };
            if (btn) { btn.disabled = true; btn.textContent = 'Doğrulanıyor…'; }

            var submitWithCaptcha = function () {
                if (btn) btn.textContent = 'Gönderiliyor…';
                var done = function (token) {
                    var inp = document.getElementById('rw-recaptcha-token');
                    if (inp) inp.value = token || '';
                    form.dataset.rcOk = '1';
                    form.submit();
                };
                if (typeof window.raGetRecaptchaToken === 'function') {
                    window.raGetRecaptchaToken('randevu').then(done).catch(function () { done(''); });
                } else {
                    done('');
                }
            };

            window.RA_OTP.ensureVerified({
                phone: telEl.value,
                purpose: 'randevu',
                doktorId: {{ (int) $doktor->id }},
                onVerified: function (verifiedPhone) {
                    if (telEl) telEl.value = verifiedPhone;
                    submitWithCaptcha();
                },
            });

            // If OTP modal closed without verify, re-enable button after a beat
            setTimeout(function () {
                if (form.dataset.rcOk !== '1' && btn && btn.disabled) {
                    // keep disabled while modal open; re-enable if modal closed
                    var modal = document.getElementById('ra-otp-modal');
                    if (modal && modal.hidden) resetBtn();
                }
            }, 400);

            // Watch modal close without verify
            var modal = document.getElementById('ra-otp-modal');
            if (modal) {
                var obs = new MutationObserver(function () {
                    if (modal.hidden && form.dataset.rcOk !== '1') {
                        resetBtn();
                        obs.disconnect();
                    }
                });
                obs.observe(modal, { attributes: true, attributeFilter: ['hidden'] });
            }
        });
    }

    var oldT = document.getElementById('rw-tarih').value;
    if (oldT) {
        var od = parseIso(oldT);
        if (od) { calYear = od.getFullYear(); calMonth = od.getMonth(); }
    }

    // Hizmet detaydan gelindiyse hizmet ön seçili; tarih adımına geç
    if (preselectedService && document.getElementById('rw-hizmet-id').value) {
        setStep(2);
    } else {
        setStep(1);
    }
})();
</script>
@elseif($doktor->randevuya_acik_mi)
<section class="mb-10">
    <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 text-center">
        <p class="text-sm text-[#6B7280]">Bu hekim için henüz randevu hizmeti tanımlanmamış.</p>
    </div>
</section>
@endif
