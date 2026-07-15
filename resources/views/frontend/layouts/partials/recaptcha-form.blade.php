@include('frontend.layouts.partials.recaptcha')

<input type="hidden" name="recaptcha_token" id="{{ $formId }}-recaptcha-token">

<script>
    (function () {
        const form = document.getElementById(@json($formId));
        const tokenInput = document.getElementById(@json($formId.'-recaptcha-token'));

        if (!form || !tokenInput) {
            return;
        }

        form.addEventListener('submit', function (event) {
            if (form.dataset.recaptchaVerified === '1') {
                return;
            }

            event.preventDefault();

            window.raGetRecaptchaToken(@json($recaptchaAction))
                .then(function (token) {
                    tokenInput.value = token || '';
                    form.dataset.recaptchaVerified = '1';
                    form.requestSubmit();
                })
                .catch(function () {
                    tokenInput.value = '';
                    form.dataset.recaptchaVerified = '1';
                    form.requestSubmit();
                });
        });
    })();
</script>
