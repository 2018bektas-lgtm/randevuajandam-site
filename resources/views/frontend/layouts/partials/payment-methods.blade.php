{{-- Güvenli ödeme şeridi: PayTR + Visa / Mastercard / Troy + 3D Secure --}}
@php
    $compact = $compact ?? false;
    $payBase = asset('assets/images/payment');
@endphp

<style>
    .pay-strip { width: 100%; }
    .pay-strip-label {
        font-size: 0.625rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #6B7280;
        margin-bottom: 0.65rem;
    }
    .pay-strip-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }
    .pay-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 2.25rem;
        min-width: 3.25rem;
        padding: 0 0.65rem;
        border-radius: 0.55rem;
        background: #fff;
        border: 1px solid #E5E7EB;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .pay-chip img {
        display: block;
        height: 1.15rem;
        width: auto;
        max-width: 3.5rem;
        object-fit: contain;
    }
    .pay-chip--paytr {
        background: #0B1F3A;
        border-color: #0B1F3A;
        padding: 0 0.75rem;
    }
    .pay-chip--paytr img {
        height: 1.05rem;
        filter: none;
    }
    .pay-chip--secure {
        gap: 0.35rem;
        padding: 0 0.7rem;
        background: #ECFDF5;
        border-color: #A7F3D0;
        color: #047857;
        font-size: 0.625rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .pay-chip--secure svg {
        width: 0.9rem;
        height: 0.9rem;
        flex-shrink: 0;
    }
    .pay-strip-note {
        margin-top: 0.65rem;
        font-size: 0.625rem;
        line-height: 1.5;
        color: #9CA3AF;
        max-width: 36rem;
    }
    .pay-strip-note strong { color: #6B7280; font-weight: 700; }
    @media (max-width: 480px) {
        .pay-chip { height: 2.1rem; min-width: 2.85rem; padding: 0 0.5rem; }
        .pay-chip img { height: 1rem; max-width: 3rem; }
    }
</style>

<div class="pay-strip {{ $compact ? 'pay-strip--compact' : '' }}" role="group" aria-label="Güvenli ödeme yöntemleri">
    @unless($compact)
        <p class="pay-strip-label">Güvenli ödeme</p>
    @endunless

    <div class="pay-strip-row">
        <span class="pay-chip pay-chip--paytr" title="PayTR">
            <img src="{{ $payBase }}/paytr.svg" alt="PayTR" width="72" height="22" loading="lazy">
        </span>

        <span class="pay-chip" title="Visa">
            <img src="{{ $payBase }}/visa.svg" alt="Visa" width="48" height="16" loading="lazy">
        </span>

        <span class="pay-chip" title="Mastercard">
            <img src="{{ $payBase }}/mastercard.svg" alt="Mastercard" width="38" height="24" loading="lazy">
        </span>

        <span class="pay-chip" title="Troy">
            <img src="{{ $payBase }}/troy.svg" alt="Troy" width="48" height="24" loading="lazy">
        </span>

        <span class="pay-chip pay-chip--secure" title="3D Secure">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
            3D Secure
        </span>
    </div>

    @unless($compact)
        <p class="pay-strip-note">
            Kartlı ödemeler <strong>PayTR</strong> altyapısı ile 3D Secure üzerinden alınır. Kart bilgileriniz sitemizde saklanmaz.
        </p>
    @endunless
</div>
