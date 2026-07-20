@include('frontend.layouts.partials.recaptcha')

<input type="hidden" name="recaptcha_token" id="{{ $formId }}-recaptcha-token">
@error('recaptcha_token')
    <p class="text-xs text-red-600 mt-2" role="alert">{{ $message }}</p>
@enderror

<script>
    (function () {
        const form = document.getElementById(@json($formId));
        const tokenInput = document.getElementById(@json($formId.'-recaptcha-token'));
        const action = @json($recaptchaAction ?? 'submit');

        if (!form || !tokenInput) {
            return;
        }

        form.addEventListener('submit', function (event) {
            if (form.dataset.recaptchaVerified === '1') {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const submitBtns = form.querySelectorAll('[type="submit"]');
            submitBtns.forEach(function (b) { b.disabled = true; });

            const finish = function (token) {
                tokenInput.value = token || '';
                // Anahtar yoksa (dev) boş geçilebilir; production'da secret varsa sunucu kontrol eder
                form.dataset.recaptchaVerified = '1';
                submitBtns.forEach(function (b) { b.disabled = false; });
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            };

            const fetchToken = function (attempt) {
                window.raGetRecaptchaToken(action)
                    .then(function (token) {
                        if ((!token || token.length < 20) && attempt < 2) {
                            // Bir kez daha dene (script geç yüklendiyse)
                            setTimeout(function () { fetchToken(attempt + 1); }, 250);
                            return;
                        }
                        finish(token);
                    })
                    .catch(function () {
                        if (attempt < 2) {
                            setTimeout(function () { fetchToken(attempt + 1); }, 250);
                            return;
                        }
                        finish('');
                    });
            };

            fetchToken(1);
        });

        // Hata ile geri dönüldüyse bayrağı temizle — yeni token alınsın
        form.dataset.recaptchaVerified = '0';
        tokenInput.value = '';
    })();
</script>
