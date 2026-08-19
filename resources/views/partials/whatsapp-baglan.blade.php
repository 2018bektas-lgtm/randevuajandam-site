@php
    /**
     * WhatsApp Business — Model B bağlama kartı (bireysel hekim + klinik ortak).
     *
     * Beklenen değişkenler:
     *   $whatsappOwner   : 'hekim' | 'klinik'
     *   $whatsappConfig  : array|null   (whatsapp_config sütunu — bağlıysa doldu)
     *   $whatsappBaglandiAt : datetime|null
     *   $whatsappBaglanUrl  : POST endpoint (route('hekim.whatsapp.baglan') | route('hekim.klinik.whatsapp.baglan'))
     *   $whatsappAyirUrl    : POST endpoint (route('hekim.whatsapp.ayir')   | route('hekim.klinik.whatsapp.ayir'))
     *
     * Facebook SDK sadece bu partial mount edildiğinde yüklenir.
     * WHATSAPP_APP_ID + WHATSAPP_EMBEDDED_SIGNUP_CONFIG_ID env değerleri yoksa
     * "yakında" uyarısı gösterilir; buton pasiftir.
     */
    $appId = (string) config('whatsapp.app_id');
    $configId = (string) config('whatsapp.embedded_signup_config_id');
    $signupReady = $appId !== '' && $configId !== '';
    $bagli = ! empty($whatsappConfig['phone_number_id'] ?? null);
    $displayName = $whatsappConfig['display_name'] ?? null;
    $sonBaglanti = $whatsappBaglandiAt
        ? (\Carbon\Carbon::parse($whatsappBaglandiAt)->format('d.m.Y H:i'))
        : null;
@endphp

<div class="bg-white rounded-2xl border border-[#E5E7EB] p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h3 class="text-base font-bold font-display text-[#111827] flex items-center gap-2">
                <span class="inline-flex w-6 h-6 rounded-full bg-emerald-100 items-center justify-center text-emerald-600 text-xs">💬</span>
                WhatsApp Business
            </h3>
            <p class="text-xs text-[#6B7280] mt-1">
                Hastalarınıza kendi WhatsApp numaranızdan hatırlatma / onay bildirimi gönderin.
            </p>
        </div>

        @if($bagli)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase tracking-wider border border-emerald-200">
                Bağlı
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-50 text-slate-600 text-[10px] font-bold uppercase tracking-wider border border-slate-200">
                Bağlı değil
            </span>
        @endif
    </div>

    @if($bagli)
        <div class="mt-4 space-y-2.5 text-xs">
            <div class="flex justify-between py-1.5 border-b border-[#F5F5F4]">
                <span class="text-[#6B7280]">Görünen ad:</span>
                <span class="font-semibold text-[#111827]">{{ $displayName ?: '—' }}</span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-[#F5F5F4]">
                <span class="text-[#6B7280]">Phone Number ID:</span>
                <code class="font-mono text-[10px] text-[#4B5563]">{{ $whatsappConfig['phone_number_id'] }}</code>
            </div>
            @if($sonBaglanti)
                <div class="flex justify-between py-1.5">
                    <span class="text-[#6B7280]">Bağlantı tarihi:</span>
                    <span class="font-semibold text-[#111827]">{{ $sonBaglanti }}</span>
                </div>
            @endif
        </div>

        <div class="mt-4 flex items-center gap-2">
            <button type="button" id="waAyirBtn"
                    class="px-4 py-2 rounded-xl bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 text-xs font-bold transition">
                Bağlantıyı Kaldır
            </button>
            <span class="text-[10px] text-[#6B7280]">Ana platform sistem numarasına döner.</span>
        </div>
    @else
        <div class="mt-4 p-3 rounded-xl bg-amber-50/60 border border-amber-100 text-[11px] text-amber-800 leading-relaxed">
            <p><strong>Önemli:</strong> Bir numara WhatsApp API'ye kaydedildiğinde <strong>normal WhatsApp uygulamasında artık kullanılamaz.</strong> Bu hattı hastalarla yazışmak için kullanıyorsanız, işletme için ikinci bir hat almanız gerekir.</p>
        </div>

        <div class="mt-4">
            @if($signupReady)
                <button type="button" id="waBaglanBtn"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm">
                    <span>💬</span> WhatsApp Numaranızı Bağlayın
                </button>
                <p class="text-[10px] text-[#6B7280] mt-2">Facebook / Meta popup'ı açılır. Numara doğrulaması SMS ile Meta tarafından yapılır.</p>
            @else
                <button type="button" disabled
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-200 text-slate-500 text-xs font-bold cursor-not-allowed">
                    <span>⏳</span> WhatsApp Entegrasyonu Yakında
                </button>
                <p class="text-[10px] text-[#6B7280] mt-2">Meta Tech Provider onay süreci devam ediyor. Bu özellik kısa süre içinde aktif olacaktır.</p>
            @endif
        </div>
    @endif
</div>

@if($signupReady && ! $bagli)
    <script>
    (function () {
        var baglanBtn = document.getElementById('waBaglanBtn');
        if (!baglanBtn || window.__waFbLoaded) {
            initButton();
            return;
        }
        window.__waFbLoaded = true;

        // Facebook SDK yükle
        var script = document.createElement('script');
        script.async = true;
        script.crossOrigin = 'anonymous';
        script.src = 'https://connect.facebook.net/en_US/sdk.js';
        document.head.appendChild(script);

        window.fbAsyncInit = function () {
            FB.init({
                appId: @json($appId),
                version: 'v21.0',
                cookie: true,
            });
            initButton();
        };
    })();

    function initButton() {
        var baglanBtn = document.getElementById('waBaglanBtn');
        if (!baglanBtn || baglanBtn.__waInit) return;
        baglanBtn.__waInit = true;

        baglanBtn.addEventListener('click', function () {
            if (typeof FB === 'undefined') {
                alert('Facebook SDK yüklenemedi. Lütfen sayfayı yenileyip tekrar deneyin.');
                return;
            }

            FB.login(function (response) {
                if (!response.authResponse || !response.authResponse.code) {
                    return;
                }

                fetch(@json($whatsappBaglanUrl), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ code: response.authResponse.code }),
                })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
                .then(function (out) {
                    if (out.ok && out.body.success) {
                        location.reload();
                    } else {
                        alert(out.body.message || 'Bağlantı kurulamadı.');
                    }
                })
                .catch(function () { alert('Sunucuya ulaşılamadı.'); });
            }, {
                config_id: @json($configId),
                response_type: 'code',
                override_default_response_type: true,
            });
        });
    }
    </script>
@endif

@if($bagli)
    <script>
    (function () {
        var ayirBtn = document.getElementById('waAyirBtn');
        if (!ayirBtn) return;

        ayirBtn.addEventListener('click', function () {
            if (! confirm('WhatsApp bağlantısını kaldırmak istediğinize emin misiniz? Bildirimler sistem numarasından gitmeye başlar.')) {
                return;
            }

            fetch(@json($whatsappAyirUrl), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (j.success) location.reload();
                else alert(j.message || 'Kaldırılamadı.');
            })
            .catch(function () { alert('Sunucuya ulaşılamadı.'); });
        });
    })();
    </script>
@endif
