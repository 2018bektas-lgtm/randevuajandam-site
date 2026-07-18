{{-- Shared SMS OTP modal + helpers. Include once per page. --}}
@php
    $otpRequired = (bool) config('randevu.otp_required', true);
    $otpSendUrl = route('frontend.sms-otp.gonder');
    $otpVerifyUrl = route('frontend.sms-otp.dogrula');
@endphp

<div id="ra-otp-modal" class="ra-otp-modal" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="ra-otp-title">
    <div class="ra-otp-backdrop" data-otp-close></div>
    <div class="ra-otp-panel">
        <button type="button" class="ra-otp-x" data-otp-close aria-label="Kapat">&times;</button>
        <h3 id="ra-otp-title" class="ra-otp-title">SMS Doğrulama</h3>
        <p class="ra-otp-desc">
            <span id="ra-otp-phone-label"></span> numarasına gönderilen 6 haneli kodu girin.
        </p>
        <input type="text"
               id="ra-otp-code"
               class="ra-otp-input"
               inputmode="numeric"
               autocomplete="one-time-code"
               maxlength="6"
               placeholder="••••••"
               aria-label="Doğrulama kodu">
        <p id="ra-otp-error" class="ra-otp-error" hidden></p>
        <p id="ra-otp-info" class="ra-otp-info" hidden></p>
        <div class="ra-otp-actions">
            <button type="button" id="ra-otp-resend" class="ra-otp-btn ra-otp-btn-ghost">Kodu tekrar gönder</button>
            <button type="button" id="ra-otp-confirm" class="ra-otp-btn ra-otp-btn-primary">Doğrula</button>
        </div>
    </div>
</div>

<style>
.ra-otp-modal[hidden] { display: none !important; }
.ra-otp-modal {
    position: fixed; inset: 0; z-index: 200;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
}
.ra-otp-backdrop {
    position: absolute; inset: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(6px);
}
.ra-otp-panel {
    position: relative; z-index: 1;
    width: 100%; max-width: 22rem;
    background: #fff;
    border: 1px solid #E5E7EB;
    border-radius: 1.25rem;
    padding: 1.35rem 1.25rem 1.2rem;
    box-shadow: 0 24px 50px rgba(15, 23, 42, 0.18);
}
.ra-otp-x {
    position: absolute; top: 0.65rem; right: 0.75rem;
    border: none; background: transparent;
    font-size: 1.4rem; line-height: 1; color: #94A3B8;
    cursor: pointer; padding: 0.2rem 0.4rem;
}
.ra-otp-title {
    margin: 0 0 0.35rem;
    font-size: 1.05rem; font-weight: 800; color: #111827;
}
.ra-otp-desc {
    margin: 0 0 1rem;
    font-size: 0.78rem; color: #64748B; line-height: 1.45;
}
.ra-otp-input {
    width: 100%;
    text-align: center;
    letter-spacing: 0.35em;
    font-size: 1.35rem;
    font-weight: 700;
    padding: 0.75rem 0.5rem;
    border-radius: 0.85rem;
    border: 1.5px solid #E5E7EB;
    background: #FAFAFA;
    color: #0F172A;
    outline: none;
}
.ra-otp-input:focus {
    border-color: #C96A2B;
    box-shadow: 0 0 0 3px rgba(201, 106, 43, 0.15);
}
.ra-otp-error {
    margin: 0.55rem 0 0;
    font-size: 0.72rem; font-weight: 600; color: #B91C1C;
}
.ra-otp-info {
    margin: 0.55rem 0 0;
    font-size: 0.72rem; font-weight: 600; color: #047857;
}
.ra-otp-actions {
    display: flex; gap: 0.5rem; margin-top: 1rem;
}
.ra-otp-btn {
    flex: 1;
    border-radius: 0.85rem;
    padding: 0.7rem 0.6rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    cursor: pointer;
    border: 1px solid transparent;
}
.ra-otp-btn:disabled { opacity: 0.55; cursor: not-allowed; }
.ra-otp-btn-primary {
    background: #C96A2B; color: #fff; border-color: #C96A2B;
}
.ra-otp-btn-primary:hover:not(:disabled) { background: #B55A20; }
.ra-otp-btn-ghost {
    background: #fff; color: #64748B; border-color: #E5E7EB;
}
.ra-otp-btn-ghost:hover:not(:disabled) { background: #F8FAFC; color: #C96A2B; border-color: #E7B58A; }
</style>

<script>
window.RA_OTP = window.RA_OTP || (function () {
    var required = @json($otpRequired);
    var sendUrl = @json($otpSendUrl);
    var verifyUrl = @json($otpVerifyUrl);
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

    var modal = document.getElementById('ra-otp-modal');
    var codeInput = document.getElementById('ra-otp-code');
    var errEl = document.getElementById('ra-otp-error');
    var infoEl = document.getElementById('ra-otp-info');
    var phoneLabel = document.getElementById('ra-otp-phone-label');
    var confirmBtn = document.getElementById('ra-otp-confirm');
    var resendBtn = document.getElementById('ra-otp-resend');

    var state = {
        phone: '',
        purpose: 'randevu',
        doktorId: null,
        onVerified: null,
        sending: false,
        verifying: false,
    };

    function showErr(msg) {
        if (!errEl) return;
        errEl.hidden = !msg;
        errEl.textContent = msg || '';
    }
    function showInfo(msg) {
        if (!infoEl) return;
        infoEl.hidden = !msg;
        infoEl.textContent = msg || '';
    }

    function openModal() {
        if (!modal) return;
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        showErr('');
        showInfo('');
        if (codeInput) {
            codeInput.value = '';
            setTimeout(function () { codeInput.focus(); }, 50);
        }
    }

    function closeModal() {
        if (!modal) return;
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function normalizePhone(raw) {
        var d = String(raw || '').replace(/\D/g, '');
        if (d.indexOf('90') === 0 && d.length === 12) d = '0' + d.slice(2);
        if (d.length === 10 && d.charAt(0) === '5') d = '0' + d;
        return d;
    }

    function isValidPhone(raw) {
        return /^05[0-9]{9}$/.test(normalizePhone(raw));
    }

    /** Bind input: only digits, force 05 start, max 11 */
    function bindPhoneInput(el) {
        if (!el || el.dataset.raPhoneBound) return;
        el.dataset.raPhoneBound = '1';
        el.setAttribute('inputmode', 'numeric');
        el.setAttribute('pattern', '05[0-9]{9}');
        el.setAttribute('maxlength', '11');
        el.setAttribute('autocomplete', 'tel-national');
        if (!el.placeholder) el.placeholder = '05xxxxxxxxx';

        el.addEventListener('input', function () {
            var v = el.value.replace(/\D/g, '');
            if (v.length === 0) {
                el.value = '';
                return;
            }
            // Must start with 0; if user types 5 first, prepend 0
            if (v.charAt(0) === '5') v = '0' + v;
            if (v.charAt(0) !== '0') v = '0' + v.replace(/^0+/, '');
            // Second digit must be 5
            if (v.length >= 2 && v.charAt(1) !== '5') {
                v = '05' + v.slice(2).replace(/^5+/, '');
            }
            if (v.length === 1) v = '0';
            el.value = v.slice(0, 11);
        });

        el.addEventListener('keypress', function (e) {
            if (e.ctrlKey || e.metaKey || e.key.length > 1) return;
            if (!/[0-9]/.test(e.key)) e.preventDefault();
        });

        el.addEventListener('paste', function (e) {
            e.preventDefault();
            var t = (e.clipboardData || window.clipboardData).getData('text') || '';
            el.value = t;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }

    function postJson(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(body),
        }).then(function (r) {
            return r.json().then(function (j) {
                return { ok: r.ok, status: r.status, data: j };
            });
        });
    }

    function sendCode() {
        if (state.sending) return Promise.resolve(false);
        state.sending = true;
        if (resendBtn) resendBtn.disabled = true;
        showErr('');
        showInfo('Kod gönderiliyor…');

        var body = {
            telefon: state.phone,
            purpose: state.purpose,
        };
        if (state.purpose === 'randevu' && state.doktorId) {
            body.doktor_id = state.doktorId;
        }

        return postJson(sendUrl, body).then(function (res) {
            state.sending = false;
            if (resendBtn) resendBtn.disabled = false;
            if (!res.ok || !res.data.success) {
                showInfo('');
                showErr((res.data && res.data.message) || 'SMS gönderilemedi.');
                return false;
            }
            showInfo('Kod gönderildi. 5 dakika geçerlidir.');
            return true;
        }).catch(function () {
            state.sending = false;
            if (resendBtn) resendBtn.disabled = false;
            showInfo('');
            showErr('Bağlantı hatası. Tekrar deneyin.');
            return false;
        });
    }

    function verifyCode() {
        if (state.verifying) return;
        var kod = (codeInput && codeInput.value || '').replace(/\D/g, '');
        if (kod.length !== 6) {
            showErr('6 haneli kodu girin.');
            return;
        }
        state.verifying = true;
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Doğrulanıyor…';
        }
        showErr('');

        var body = {
            telefon: state.phone,
            kod: kod,
            purpose: state.purpose,
        };
        if (state.purpose === 'randevu' && state.doktorId) {
            body.doktor_id = state.doktorId;
        }

        postJson(verifyUrl, body).then(function (res) {
            state.verifying = false;
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Doğrula';
            }
            if (!res.ok || !res.data.success) {
                showErr((res.data && res.data.message) || 'Kod hatalı.');
                return;
            }
            closeModal();
            if (typeof state.onVerified === 'function') {
                state.onVerified(state.phone);
            }
        }).catch(function () {
            state.verifying = false;
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Doğrula';
            }
            showErr('Bağlantı hatası. Tekrar deneyin.');
        });
    }

    /**
     * Ensure phone is OTP-verified (if required). Calls onVerified when ready.
     * opts: { phone, purpose, doktorId, onVerified, skipOtp }
     */
    function ensureVerified(opts) {
        opts = opts || {};
        if (opts.skipOtp || !required) {
            if (typeof opts.onVerified === 'function') opts.onVerified(normalizePhone(opts.phone));
            return;
        }

        var phone = normalizePhone(opts.phone);
        if (!isValidPhone(phone)) {
            alert('Telefon 05 ile başlamalı ve 11 haneli olmalıdır (yalnızca rakam).');
            return;
        }

        state.phone = phone;
        state.purpose = opts.purpose || 'randevu';
        state.doktorId = opts.doktorId || null;
        state.onVerified = opts.onVerified || null;

        if (phoneLabel) phoneLabel.textContent = phone;
        openModal();
        sendCode();
    }

    if (confirmBtn) confirmBtn.addEventListener('click', verifyCode);
    if (resendBtn) resendBtn.addEventListener('click', sendCode);
    if (codeInput) {
        codeInput.addEventListener('input', function () {
            codeInput.value = codeInput.value.replace(/\D/g, '').slice(0, 6);
        });
        codeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                verifyCode();
            }
        });
    }
    if (modal) {
        modal.querySelectorAll('[data-otp-close]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });
    }

    return {
        required: required,
        bindPhoneInput: bindPhoneInput,
        normalizePhone: normalizePhone,
        isValidPhone: isValidPhone,
        ensureVerified: ensureVerified,
        closeModal: closeModal,
    };
})();
</script>
