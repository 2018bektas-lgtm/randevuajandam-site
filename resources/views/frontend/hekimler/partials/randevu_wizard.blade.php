@php
    $aktifHizmetler = $doktor->hizmetler->where('aktif_mi', true)->values();
    $hastaAuth = Auth::guard('hasta')->check();
    $hastaUser = $hastaAuth ? Auth::guard('hasta')->user() : null;
    $onlineGorusmeAcik = (bool) $doktor->aktifPaket()?->hasFeature('online_gorusme');
    $slotsUrl = route('frontend.doktorlar.slotlar', $doktor->id);
    $formAction = $hastaAuth
        ? route('frontend.hasta.randevu.kaydet')
        : route('frontend.hasta.randevu.misafir');
    $calismaGunleri = $doktor->calismaSaatleri
        ->where('aktif_mi', true)
        ->pluck('gun')
        ->map(fn ($g) => (int) $g)
        ->values()
        ->all();
@endphp

@if($doktor->randevuya_acik_mi && $aktifHizmetler->isNotEmpty())
<section id="randevu-wizard" class="rw">
    <div class="rw-shell">
        <header class="rw-head">
            <div>
                <h2 class="rw-title">Randevu al</h2>
                <p class="rw-caption" id="rw-step-caption">Hizmet seçin</p>
            </div>
            <div class="rw-dots" id="rw-dots" aria-hidden="true">
                @for($i = 1; $i <= 4; $i++)
                    <span class="rw-dot{{ $i === 1 ? ' is-active' : '' }}" data-dot="{{ $i }}"></span>
                @endfor
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
            <input type="hidden" name="hizmet_id" id="rw-hizmet-id" value="{{ old('hizmet_id') }}">
            <input type="hidden" name="tarih" id="rw-tarih" value="{{ old('tarih') }}">
            <input type="hidden" name="saat" id="rw-saat" value="{{ old('saat') }}">
            @unless($hastaAuth)
                <input type="hidden" name="recaptcha_token" id="rw-recaptcha-token" value="">
                <div class="rw-hp" aria-hidden="true">
                    <input type="text" name="{{ config('randevu.honeypot_field', 'website_url') }}" value="" tabindex="-1" autocomplete="off">
                </div>
            @endunless

            <div id="rw-summary" class="rw-summary" hidden></div>

            {{-- 1 Hizmet — minimal grid (mobil 2 / tablet 3 / geniş 4) --}}
            <div class="rw-panel" data-panel="1">
                <div class="rw-svc-grid">
                    @foreach($aktifHizmetler as $hizmet)
                        @php
                            $hasImg = !empty($hizmet->resim);
                            $sure = (int) ($hizmet->sure ?? 0);
                        @endphp
                        <button type="button"
                                class="rw-svc rw-hizmet-card"
                                data-id="{{ $hizmet->id }}"
                                data-ad="{{ $hizmet->ad }}"
                                data-sure="{{ $sure }}">
                            <span class="rw-svc-radio" aria-hidden="true"></span>
                            <span class="rw-svc-media" aria-hidden="true">
                                @if($hasImg)
                                    <img src="{{ asset($hizmet->resim) }}" alt="" class="rw-svc-img">
                                @else
                                    <span class="rw-svc-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                @endif
                            </span>
                            <span class="rw-svc-name">{{ $hizmet->ad }}</span>
                            @if($hizmet->aciklama)
                                <span class="rw-svc-desc">{{ Str::limit(strip_tags($hizmet->aciklama), 48) }}</span>
                            @endif
                            @if($sure > 0)
                                <span class="rw-svc-tags">
                                    <span class="rw-tag">{{ $sure }} dk</span>
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>
                <p id="rw-err-1" class="rw-err" hidden>Bir hizmet seçin</p>
            </div>

            {{-- 2 Takvim --}}
            <div class="rw-panel" data-panel="2" hidden>
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
                        <span><i class="rw-lg-on"></i> Açık</span>
                        <span><i class="rw-lg-off"></i> Kapalı</span>
                        <span><i class="rw-lg-sel"></i> Seçili</span>
                    </div>
                </div>
                <p id="rw-err-2" class="rw-err" hidden>Takvimden bir gün seçin</p>
            </div>

            {{-- 3 Saat --}}
            <div class="rw-panel" data-panel="3" hidden>
                <p class="rw-sub">
                    <strong id="rw-saat-tarih-label"></strong> için saatler
                </p>
                <div id="rw-slots-loading" class="rw-empty" hidden>Yükleniyor…</div>
                <div id="rw-slots-empty" class="rw-empty" hidden>
                    <p>Bu günde müsait saat yok.</p>
                    <button type="button" class="rw-link rw-goto-date">Başka gün seç</button>
                </div>
                <div id="rw-slots-grid" class="rw-slots"></div>
                <div class="rw-legend">
                    <span><i class="rw-lg-on"></i> Müsait</span>
                    <span><i class="rw-lg-busy"></i> Dolu</span>
                </div>
                <p id="rw-err-3" class="rw-err" hidden>Müsait bir saat seçin</p>
            </div>

            {{-- 4 Bilgi --}}
            <div class="rw-panel" data-panel="4" hidden>
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
                            <input id="rw-tel" type="tel" name="telefon" value="{{ old('telefon') }}" required placeholder="05xx xxx xx xx" autocomplete="tel">
                        </div>
                        <div class="rw-field">
                            <label for="rw-mail">E-posta <em>(opsiyonel)</em></label>
                            <input id="rw-mail" type="email" name="e_posta" value="{{ old('e_posta') }}" autocomplete="email">
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
    margin-bottom: 2.5rem;
    scroll-margin-top: 6rem;
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
    gap: 1rem;
    padding: 1.15rem 1.35rem;
    border-bottom: 1px solid #F1F5F9;
    background: linear-gradient(90deg, #FFF7ED 0%, #fff 55%);
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

/* ── Minimal service tiles: 2 / 3 / 4 columns ── */
.rw-svc-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.6rem;
}
@media (min-width: 640px) {
    .rw-svc-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.7rem; }
}
@media (min-width: 960px) {
    .rw-svc-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.75rem; }
}

.rw-svc {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 0.55rem;
    width: 100%;
    min-height: 8.5rem;
    padding: 1rem 0.7rem 0.85rem;
    border-radius: 1.1rem;
    border: 1.5px solid #ECEFF3;
    background: #fff;
    color: inherit;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    transition: border-color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease, transform 0.15s ease;
}
.rw-svc:hover {
    border-color: #E7B58A;
    box-shadow: 0 10px 24px rgba(201, 106, 43, 0.09);
    transform: translateY(-2px);
}
.rw-svc.is-selected {
    border-color: #C96A2B;
    background: linear-gradient(180deg, #FFF7ED 0%, #fff 70%);
    box-shadow: 0 0 0 1px #C96A2B, 0 12px 28px rgba(201, 106, 43, 0.12);
}

.rw-svc-radio {
    position: absolute;
    top: 0.55rem;
    right: 0.55rem;
    width: 1rem;
    height: 1rem;
    border-radius: 999px;
    border: 2px solid #D1D5DB;
    background: #fff;
    transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
}
.rw-svc.is-selected .rw-svc-radio {
    border-color: #C96A2B;
    background: #C96A2B;
    box-shadow: 0 0 0 3px rgba(201, 106, 43, 0.14);
}
.rw-svc.is-selected .rw-svc-radio::after {
    content: '';
    position: absolute;
    left: 0.22rem;
    top: 0.06rem;
    width: 0.24rem;
    height: 0.42rem;
    border: solid #fff;
    border-width: 0 1.8px 1.8px 0;
    transform: rotate(45deg);
}

.rw-svc-media {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 0.85rem;
    overflow: hidden;
    background: linear-gradient(145deg, #FFF7ED, #FFE8D2);
    border: 1px solid rgba(231, 181, 138, 0.35);
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
}
.rw-svc-icon {
    color: #C96A2B;
    display: flex;
    align-items: center;
    justify-content: center;
}
.rw-svc.is-selected .rw-svc-media {
    border-color: rgba(201, 106, 43, 0.5);
    box-shadow: 0 4px 10px rgba(201, 106, 43, 0.14);
}

.rw-svc-name {
    font-size: 0.8rem;
    font-weight: 700;
    color: #0F172A;
    line-height: 1.3;
    letter-spacing: -0.01em;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    max-width: 100%;
    padding: 0 0.15rem;
}
.rw-svc-desc {
    font-size: 0.68rem;
    font-weight: 500;
    color: #94A3B8;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    max-width: 100%;
    padding: 0 0.2rem;
}

.rw-svc-tags {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.3rem;
    margin-top: auto;
}
.rw-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.18rem 0.48rem;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 700;
    color: #C96A2B;
    background: #FFF7ED;
    border: 1px solid rgba(231, 181, 138, 0.4);
}
.rw-tag-soft {
    color: #64748B;
    background: #F8FAFC;
    border-color: #E2E8F0;
}

@media (max-width: 380px) {
    .rw-svc { min-height: 7.75rem; padding: 0.85rem 0.55rem 0.7rem; }
    .rw-svc-name { font-size: 0.75rem; }
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
    transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
}
.rw-card:hover {
    border-color: #E7B58A;
    background: #FFFBF5;
}
.rw-card.is-selected {
    border-color: #C96A2B;
    background: #FFF7ED;
    box-shadow: 0 0 0 1px #C96A2B;
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

<script>
(function () {
    const slotsUrl = @json($slotsUrl);
    const isGuest = @json(! $hastaAuth);
    const workDays = @json($calismaGunleri);
    const captions = ['', 'Hizmet seçin', 'Gün seçin', 'Saat seçin', 'Bilgilerinizi tamamlayın'];
    const monthNames = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];

    let step = 1;
    const maxStep = 4;
    let selectedHizmet = { id: '', ad: '', sure: '' };
    let selectedSaat = '';
    let calYear, calMonth;

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
        if (n === 2) renderCalendar();
        if (n === 3) loadSlots();
    }

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

    function renderCalendar() {
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
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = String(day);

            if (past || !work) {
                btn.className = 'rw-day is-off';
                btn.disabled = true;
            } else {
                btn.className = 'rw-day is-on' + (dIso === todayIso ? ' is-today' : '');
                if (dIso === selectedIso) btn.classList.add('is-selected');
                btn.addEventListener('click', (function (isoVal) {
                    return function () {
                        document.getElementById('rw-tarih').value = isoVal;
                        selectedSaat = '';
                        document.getElementById('rw-saat').value = '';
                        hideErr(2);
                        updateSummary();
                        renderCalendar();
                    };
                })(dIso));
            }
            grid.appendChild(btn);
        }
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

    function loadSlots() {
        var tarih = document.getElementById('rw-tarih').value;
        document.getElementById('rw-saat-tarih-label').textContent = formatTr(tarih);
        var grid = document.getElementById('rw-slots-grid');
        var loading = document.getElementById('rw-slots-loading');
        var empty = document.getElementById('rw-slots-empty');
        grid.innerHTML = '';
        selectedSaat = '';
        document.getElementById('rw-saat').value = '';
        updateSummary();

        if (!tarih) {
            empty.hidden = false;
            loading.hidden = true;
            return;
        }
        loading.hidden = false;
        empty.hidden = true;

        fetch(slotsUrl + '?tarih=' + encodeURIComponent(tarih), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                loading.hidden = true;
                var slots = (json.data && json.data.slots) ? json.data.slots : [];
                if (!slots.length) {
                    empty.hidden = false;
                    return;
                }
                empty.hidden = true;
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
                            hideErr(3);
                            updateSummary();
                        });
                    }
                    grid.appendChild(btn);
                });
            })
            .catch(function () {
                loading.hidden = true;
                empty.hidden = false;
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
    function validateStep(n) {
        if (n === 1 && !document.getElementById('rw-hizmet-id').value) { showErr(1); return false; }
        if (n === 2 && !document.getElementById('rw-tarih').value) { showErr(2); return false; }
        if (n === 3 && !document.getElementById('rw-saat').value) { showErr(3); return false; }
        return true;
    }

    document.getElementById('rw-btn-next').addEventListener('click', function () {
        if (!validateStep(step)) return;
        if (step < maxStep) setStep(step + 1);
    });
    document.getElementById('rw-btn-back').addEventListener('click', function () {
        if (step > 1) setStep(step - 1);
    });
    document.querySelectorAll('.rw-goto-date').forEach(function (b) {
        b.addEventListener('click', function () { setStep(2); });
    });

    if (isGuest) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.rcOk === '1') return;
            e.preventDefault();
            if (!document.getElementById('rw-hizmet-id').value ||
                !document.getElementById('rw-tarih').value ||
                !document.getElementById('rw-saat').value) {
                setStep(1);
                return;
            }
            var btn = document.getElementById('rw-btn-submit');
            if (btn) { btn.disabled = true; btn.textContent = 'Gönderiliyor…'; }
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
        });
    }

    var oldT = document.getElementById('rw-tarih').value;
    if (oldT) {
        var od = parseIso(oldT);
        if (od) { calYear = od.getFullYear(); calMonth = od.getMonth(); }
    }

    setStep(1);
})();
</script>
@elseif($doktor->randevuya_acik_mi)
<section class="mb-10">
    <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 text-center">
        <p class="text-sm text-[#6B7280]">Bu hekim için henüz randevu hizmeti tanımlanmamış.</p>
    </div>
</section>
@endif
