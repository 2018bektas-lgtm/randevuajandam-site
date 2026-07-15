@once
{{-- reCAPTCHA v3 — ana platform --}}
@php
    $rcService = app(\App\Services\RecaptchaService::class);
    $rcSiteKey = $rcService->siteKey();
    $rcOn = (bool) config('recaptcha.enabled', true)
        && $rcSiteKey !== ''
        && ((\App\Models\SiteAyari::query()->value('recaptcha_enabled') ?? true) !== false);
@endphp
@if($rcOn)
<script src="https://www.google.com/recaptcha/api.js?render={{ $rcSiteKey }}"></script>
<script>
window.raRecaptchaSiteKey = @json($rcSiteKey);
window.raGetRecaptchaToken = function (action) {
    action = action || 'submit';
    return new Promise(function (resolve) {
        if (typeof grecaptcha === 'undefined' || !window.raRecaptchaSiteKey) {
            resolve('');
            return;
        }
        grecaptcha.ready(function () {
            grecaptcha.execute(window.raRecaptchaSiteKey, { action: action })
                .then(function (token) { resolve(token || ''); })
                .catch(function () { resolve(''); });
        });
    });
};
</script>
@else
<script>
window.raRecaptchaSiteKey = '';
window.raGetRecaptchaToken = function () { return Promise.resolve(''); };
</script>
@endif
@endonce
