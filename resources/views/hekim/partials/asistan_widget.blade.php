{{-- Hekim AI Asistan Widget --}}
<style>
    #ra-asistan-fab {
        position: fixed;
        bottom: 28px;
        right: 28px;
        z-index: 1000;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #C96A2B, #a8511e);
        color: #fff;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(201,106,43,0.40);
        transition: transform .18s, box-shadow .18s;
    }
    #ra-asistan-fab:hover { transform: scale(1.08); box-shadow: 0 6px 28px rgba(201,106,43,0.55); }

    #ra-asistan-panel {
        position: fixed;
        bottom: 96px;
        right: 28px;
        z-index: 1000;
        width: 360px;
        max-width: calc(100vw - 32px);
        max-height: 560px;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 16px 48px rgba(31,41,55,0.18);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: opacity .2s, transform .2s;
    }
    #ra-asistan-panel.ra-kapali { opacity: 0; transform: translateY(16px) scale(.97); pointer-events: none; }

    .ra-header {
        background: linear-gradient(135deg, #C96A2B, #a8511e);
        color: #fff;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .ra-header-icon { width: 32px; height: 32px; background: rgba(255,255,255,.18); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .ra-header h4 { font-size: 14px; font-weight: 700; margin: 0; font-family: 'Outfit', sans-serif; }
    .ra-header p  { font-size: 11px; opacity: .80; margin: 0; font-family: 'Inter', sans-serif; }
    .ra-header-close { margin-left: auto; background: none; border: none; color: rgba(255,255,255,.75); cursor: pointer; padding: 4px; border-radius: 6px; line-height:1; }
    .ra-header-close:hover { color: #fff; background: rgba(255,255,255,.15); }

    .ra-mesajlar {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        scroll-behavior: smooth;
    }
    .ra-mesaj { max-width: 85%; display: flex; flex-direction: column; gap: 2px; }
    .ra-mesaj.ra-kullanici { align-self: flex-end; align-items: flex-end; }
    .ra-mesaj.ra-asistan  { align-self: flex-start; align-items: flex-start; }

    .ra-balon {
        padding: 9px 13px;
        border-radius: 14px;
        font-size: 13px;
        line-height: 1.5;
        font-family: 'Inter', sans-serif;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .ra-kullanici .ra-balon { background: #C96A2B; color: #fff; border-bottom-right-radius: 4px; }
    .ra-asistan  .ra-balon { background: #F3F4F6; color: #111827; border-bottom-left-radius: 4px; }

    .ra-onay-kutu {
        background: #FFF7ED;
        border: 1px solid #FDE68A;
        border-radius: 12px;
        padding: 10px 12px;
        font-size: 12px;
        color: #92400E;
        margin-top: 4px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .ra-onay-butonlar { display: flex; gap: 8px; }
    .ra-onay-evet, .ra-onay-hayir {
        flex: 1;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        transition: opacity .15s;
    }
    .ra-onay-evet  { background: #C96A2B; color: #fff; }
    .ra-onay-hayir { background: #F3F4F6; color: #374151; }
    .ra-onay-evet:hover  { opacity: .88; }
    .ra-onay-hayir:hover { opacity: .80; }

    .ra-secim-kutu {
        background: #FFF7ED;
        border: 1px solid #FED7AA;
        border-radius: 12px;
        padding: 10px 12px;
        font-size: 12px;
        color: #92400E;
        margin-top: 4px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .ra-secim-btn {
        width: 100%;
        padding: 8px 12px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid transparent;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        text-align: left;
        transition: background .15s, border-color .15s;
    }
    .ra-secim-baslik { font-weight: 700; font-size: 12px; color: #92400E; margin-bottom: 2px; }
    .ra-secim-btn { background: #fff; color: #111827; border-color: #E5E7EB; }
    .ra-secim-btn:last-child { background: #F3F4F6; color: #6B7280; }
    .ra-secim-btn[data-deger="kapat_ve_iptal"],
    .ra-secim-btn[data-deger="kapat_iptal_sms"],
    .ra-secim-btn[data-deger="kapat_bekleme"] { color: #991B1B; border-color: #FECACA; background: #FEF2F2; }
    .ra-secim-btn[data-deger="sadece_kapat"] { color: #1E3A5F; border-color: #BFDBFE; background: #EFF6FF; }
    .ra-secim-btn:hover { filter: brightness(.94); }
    .ra-secim-bilgi { font-size: 10.5px; color: #78716C; margin-top: 2px; line-height: 1.4; padding: 0 2px; }

    .ra-hizli-kutu {
        background: #FAFAFA;
        border: 1px solid #F0F0F0;
        border-radius: 12px;
        padding: 10px 12px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        align-self: flex-start;
        max-width: 92%;
    }
    .ra-hizli-baslik { font-size: 11px; color: #9CA3AF; font-family: 'Inter', sans-serif; margin-bottom: 2px; }
    .ra-hizli-btn {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 20px;
        padding: 7px 14px;
        font-size: 12.5px;
        font-family: 'Inter', sans-serif;
        color: #374151;
        cursor: pointer;
        text-align: left;
        transition: background .15s, border-color .15s, color .15s;
        white-space: nowrap;
    }
    .ra-hizli-btn:hover { background: #FFF7ED; border-color: #C96A2B; color: #C96A2B; }

    .ra-giris-alani {
        border-top: 1px solid #F3F4F6;
        padding: 12px 14px;
        display: flex;
        gap: 8px;
        align-items: flex-end;
    }
    #ra-input {
        flex: 1;
        resize: none;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 9px 12px;
        font-size: 13px;
        font-family: 'Inter', sans-serif;
        color: #111827;
        outline: none;
        max-height: 100px;
        line-height: 1.45;
        transition: border-color .15s;
    }
    #ra-input:focus { border-color: #C96A2B; box-shadow: 0 0 0 3px rgba(201,106,43,.10); }
    #ra-gonder-btn {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #C96A2B;
        color: #fff;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: opacity .15s;
    }
    #ra-gonder-btn:disabled { opacity: .45; cursor: not-allowed; }

    .ra-yukleniyor {
        display: flex;
        gap: 5px;
        align-items: center;
        padding: 10px 14px;
        background: #F3F4F6;
        border-radius: 14px;
        border-bottom-left-radius: 4px;
        width: fit-content;
    }
    .ra-dot { width: 6px; height: 6px; border-radius: 50%; background: #9CA3AF; animation: raPuls .9s ease-in-out infinite; }
    .ra-dot:nth-child(2) { animation-delay: .15s; }
    .ra-dot:nth-child(3) { animation-delay: .30s; }
    @keyframes raPuls { 0%,80%,100%{transform:scale(.7);opacity:.5} 40%{transform:scale(1);opacity:1} }
</style>

{{-- FAB Button --}}
<button id="ra-asistan-fab" title="AI Asistan" aria-label="AI Asistanı Aç">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        <circle cx="12" cy="10" r="1" fill="currentColor" stroke="none"/>
        <circle cx="8"  cy="10" r="1" fill="currentColor" stroke="none"/>
        <circle cx="16" cy="10" r="1" fill="currentColor" stroke="none"/>
    </svg>
</button>

{{-- Chat Panel --}}
<div id="ra-asistan-panel" class="ra-kapali" role="dialog" aria-label="AI Asistan">
    <div class="ra-header">
        <div class="ra-header-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2a5 5 0 1 0 0 10 5 5 0 0 0 0-10z"/><path d="M12 12c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4z"/>
            </svg>
        </div>
        <div>
            <h4>Dijital Asistan</h4>
            <p>Randevu sorularınızı sorun</p>
        </div>
        <button class="ra-header-close" id="ra-kapat-btn" title="Kapat">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <div class="ra-mesajlar" id="ra-mesajlar"></div>

    <div class="ra-giris-alani">
        <textarea id="ra-input" rows="1" placeholder="Mesajınızı yazın…" maxlength="500"></textarea>
        <button id="ra-gonder-btn" title="Gönder">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
    </div>
</div>

<script>
(function () {
    const fab      = document.getElementById('ra-asistan-fab');
    const panel    = document.getElementById('ra-asistan-panel');
    const kapatBtn = document.getElementById('ra-kapat-btn');
    const mesajlar = document.getElementById('ra-mesajlar');
    const input    = document.getElementById('ra-input');
    const gonderBtn= document.getElementById('ra-gonder-btn');
    const csrfToken= document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const endpoint = '{{ route("hekim.asistan.mesaj") }}';

    let acik = false;
    let bekleyenOnay = null;
    let gecmis = [];
    let karsilamaGosterildi = false;

    fab.addEventListener('click', () => { acik ? kapat() : ac(); });
    kapatBtn.addEventListener('click', kapat);

    function ac() {
        acik = true;
        panel.classList.remove('ra-kapali');
        if (!karsilamaGosterildi) {
            karsilamaGosterildi = true;
            karsilamaGoster();
        }
        input.focus();
        scrolla();
    }
    function kapat() {
        acik = false;
        panel.classList.add('ra-kapali');
    }

    function karsilamaGoster() {
        const wrap = document.createElement('div');
        wrap.className = 'ra-mesaj ra-asistan';
        const balon = document.createElement('div');
        balon.className = 'ra-balon';
        balon.textContent = 'Merhaba! Size nasıl yardımcı olabilirim?';
        wrap.appendChild(balon);
        mesajlar.appendChild(wrap);

        const hizliIslemler = [
            { etiket: 'Bugünkü randevularım',          mesaj: 'bugünkü randevularımı listele' },
            { etiket: 'Bu haftanın özeti',              mesaj: 'bu haftanın randevu özeti' },
            { etiket: 'Yarın müsait saatler',           mesaj: 'yarın boş saatlerimi göster' },
            { etiket: 'Takvimi kapat / izin al',        mesaj: 'takvimi kapatmak istiyorum' },
            { etiket: 'Randevu onayla veya iptal et',   mesaj: 'bugünkü randevularımı listele, durumunu güncellemek isteyeceğim' },
            { etiket: 'Bekleme listesi',                mesaj: 'bekleme listemde kimler var' },
            { etiket: 'Hizmetlerimi listele',           mesaj: 'hizmetlerimi listele' },
            { etiket: 'Profilime SEO önerisi',          mesaj: 'profilimi SEO açısından incele ve önerilerde bulun' },
            { etiket: 'Blog yazılarımı SEO incele',     mesaj: 'blog yazılarımı SEO açısından analiz et ve önerilerde bulun' },
        ];

        const kutu = document.createElement('div');
        kutu.className = 'ra-hizli-kutu';
        const baslik = document.createElement('div');
        baslik.className = 'ra-hizli-baslik';
        baslik.textContent = 'Hızlı işlemler';
        kutu.appendChild(baslik);

        hizliIslemler.forEach(item => {
            const btn = document.createElement('button');
            btn.className = 'ra-hizli-btn';
            btn.textContent = item.etiket;
            btn.addEventListener('click', () => {
                kutu.remove();
                input.value = item.mesaj;
                gonder();
            });
            kutu.appendChild(btn);
        });

        mesajlar.appendChild(kutu);
        scrolla();
    }

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            gonder();
        }
    });
    input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });
    gonderBtn.addEventListener('click', gonder);

    function gonder() {
        const metin = input.value.trim();
        if (!metin || gonderBtn.disabled) return;
        mesajEkle('kullanici', metin);
        input.value = '';
        input.style.height = 'auto';
        gonderBtn.disabled = true;
        bekleyenOnay = null;
        yukleniyorGoster();
        // Pass completed exchanges only (not current message) so PHP adds it last
        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ mesaj: metin, gecmis: gecmis.slice(-10) }),
        })
        .then(r => r.json())
        .then(d => {
            yukleniyorGizle();
            // Push to history only after successful response
            gecmis.push({ rol: 'kullanici', mesaj: metin });
            isleSonuc(d);
        })
        .catch(() => { yukleniyorGizle(); mesajEkle('asistan', 'Bağlantı hatası. Lütfen tekrar deneyin.'); })
        .finally(() => { gonderBtn.disabled = false; });
    }

    function isleSonuc(d) {
        const paket = d && d.data ? d.data : d;
        const yanit = (paket && (paket.yanit || paket.message)) || (d && d.message) || 'Yanıt alınamadı.';
        gecmis.push({ rol: 'asistan', mesaj: yanit });
        mesajEkle('asistan', yanit);
        d = paket || {};
        if (d.secim_gerekli) {
            secimKartusuEkle(d.secim_gerekli);
        } else if (d.onay_gerekli) {
            bekleyenOnay = d.onay_gerekli;
            onayKutusuEkle(d.onay_gerekli);
        }
    }

    function secimKartusuEkle(secim) {
        const kutu = document.createElement('div');
        kutu.className = 'ra-secim-kutu';
        if (secim.baslik) {
            const baslik = document.createElement('div');
            baslik.className = 'ra-secim-baslik';
            baslik.textContent = secim.baslik;
            kutu.appendChild(baslik);
        }
        secim.secenekler.forEach(s => {
            const wrap = document.createElement('div');
            const btn = document.createElement('button');
            btn.className = 'ra-secim-btn';
            btn.dataset.deger = s.deger;
            btn.textContent = s.etiket;
            wrap.appendChild(btn);
            if (s.bilgi) {
                const bilgi = document.createElement('div');
                bilgi.className = 'ra-secim-bilgi';
                bilgi.textContent = s.bilgi;
                wrap.appendChild(bilgi);
            }
            btn.addEventListener('click', () => {
                kutu.remove();
                if (s.deger === 'vazgec') {
                    mesajEkle('asistan', 'İptal edildi.');
                    return;
                }
                gonderBtn.disabled = true;
                yukleniyorGoster();
                fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ secim: s.deger, secim_parametreler: secim.parametreler }),
                })
                .then(r => r.json())
                .then(d => { yukleniyorGizle(); isleSonuc(d); })
                .catch(() => { yukleniyorGizle(); mesajEkle('asistan', 'İşlem sırasında hata oluştu.'); })
                .finally(() => { gonderBtn.disabled = false; });
            });
            kutu.appendChild(wrap);
        });
        mesajlar.appendChild(kutu);
        scrolla();
    }

    function onayKutusuEkle(onay) {
        const kutu = document.createElement('div');
        kutu.className = 'ra-onay-kutu';
        kutu.innerHTML = '<div>Bu işlemi onaylıyor musunuz?</div><div class="ra-onay-butonlar"><button class="ra-onay-evet">Evet, onayla</button><button class="ra-onay-hayir">İptal</button></div>';
        const evet  = kutu.querySelector('.ra-onay-evet');
        const hayir = kutu.querySelector('.ra-onay-hayir');
        evet.addEventListener('click', () => { kutu.remove(); onayliIsleCalistir(onay); });
        hayir.addEventListener('click', () => { kutu.remove(); bekleyenOnay = null; mesajEkle('asistan', 'İşlem iptal edildi.'); });
        mesajlar.appendChild(kutu);
        scrolla();
    }

    function onayliIsleCalistir(onay) {
        gonderBtn.disabled = true;
        yukleniyorGoster();
        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ onay: onay }),
        })
        .then(r => r.json())
        .then(d => { yukleniyorGizle(); isleSonuc(d); })
        .catch(() => { yukleniyorGizle(); mesajEkle('asistan', 'İşlem sırasında hata oluştu.'); })
        .finally(() => { gonderBtn.disabled = false; });
    }

    let yukleniyorEl = null;
    function yukleniyorGoster() {
        yukleniyorEl = document.createElement('div');
        yukleniyorEl.className = 'ra-mesaj ra-asistan';
        yukleniyorEl.innerHTML = '<div class="ra-yukleniyor"><span class="ra-dot"></span><span class="ra-dot"></span><span class="ra-dot"></span></div>';
        mesajlar.appendChild(yukleniyorEl);
        scrolla();
    }
    function yukleniyorGizle() {
        if (yukleniyorEl) { yukleniyorEl.remove(); yukleniyorEl = null; }
    }

    function mesajEkle(rol, metin) {
        const wrap = document.createElement('div');
        wrap.className = 'ra-mesaj ra-' + rol;
        const balon = document.createElement('div');
        balon.className = 'ra-balon';
        balon.textContent = metin;
        wrap.appendChild(balon);
        mesajlar.appendChild(wrap);
        scrolla();
    }

    function scrolla() {
        requestAnimationFrame(() => { mesajlar.scrollTop = mesajlar.scrollHeight; });
    }
})();
</script>
