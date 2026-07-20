@once
{{-- reCAPTCHA v3 — ana platform --}}
@php
    $rcService = app(\App\Services\RecaptchaService::class);
    $rcSiteKey = $rcService->siteKey();
    $rcOn = (bool) config('recaptcha.enabled', true)
        && $rcSiteKey !== ''
        && (((\App\Models\SiteAyari::cached())?->recaptcha_enabled ?? true) !== false);
@endphp
@if($rcOn)
<script src="https://www.google.com/recaptcha/api.js?render={{ $rcSiteKey }}" async defer></script>
<script>
window.raRecaptchaSiteKey = @json($rcSiteKey);
window.raRecaptchaReady = false;
window.raSanitizeRecaptchaAction = function (action) {
    action = String(action || 'submit').toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/_+/g, '_');
    if (!action) action = 'submit';
    return action.substring(0, 64);
};
window.raGetRecaptchaToken = function (action) {
    action = window.raSanitizeRecaptchaAction(action);
    return new Promise(function (resolve) {
        if (!window.raRecaptchaSiteKey) {
            resolve('');
            return;
        }
        var tries = 0;
        var run = function () {
            if (typeof grecaptcha === 'undefined' || !grecaptcha.execute) {
                tries += 1;
                if (tries > 40) {
                    resolve('');
                    return;
                }
                setTimeout(run, 100);
                return;
            }
            grecaptcha.ready(function () {
                grecaptcha.execute(window.raRecaptchaSiteKey, { action: action })
                    .then(function (token) { resolve(token || ''); })
                    .catch(function () { resolve(''); });
            });
        };
        run();
    });
};
// Sayfa yüklenince ısındır — v3 skoru genelde daha iyi olur
document.addEventListener('DOMContentLoaded', function () {
    if (!window.raRecaptchaSiteKey) return;
    window.raGetRecaptchaToken('page_view').then(function () {
        window.raRecaptchaReady = true;
    });
});
</script>
@else
<script>
window.raRecaptchaSiteKey = '';
window.raGetRecaptchaToken = function () { return Promise.resolve(''); };
window.raSanitizeRecaptchaAction = function (a) { return a || 'submit'; };
</script>
@endif
@endonce
