{{--
  iyzico mağaza şartı: sitede “iyzico ile Öde”, Visa, Mastercard yer almalı.
  https://docs.iyzico.com/ek-bilgiler/iyzico-logo-paketi
  Resmî paket: iyzico docs’tan indirilip public/assets/images/payment/ altına konabilir.
--}}
@php
    $compact = $compact ?? false;
    $payBase = asset('assets/images/payment');
@endphp
<div class="{{ $compact ? '' : 'w-full' }}" role="group" aria-label="Güvenli ödeme yöntemleri">
    @unless($compact)
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#6B7280] font-display mb-2.5">
            Güvenli ödeme
        </p>
    @endunless
    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
        <img src="{{ $payBase }}/iyzico-ile-ode.svg"
             alt="iyzico ile Öde"
             class="h-8 w-auto rounded-md shadow-sm ring-1 ring-slate-200/80"
             loading="lazy" width="140" height="32">

        <span class="inline-flex items-center justify-center h-8 px-2 rounded-md bg-white ring-1 ring-slate-200/80 shadow-sm">
            <img src="{{ $payBase }}/visa.svg" alt="Visa" class="h-4 w-auto" loading="lazy" width="48" height="16">
        </span>

        <span class="inline-flex items-center justify-center h-8 px-1.5 rounded-md bg-white ring-1 ring-slate-200/80 shadow-sm">
            <img src="{{ $payBase }}/mastercard.svg" alt="Mastercard" class="h-6 w-auto" loading="lazy" width="38" height="24">
        </span>

        <img src="{{ $payBase }}/troy.svg"
             alt="Troy"
             class="h-8 w-auto rounded-md shadow-sm"
             loading="lazy" width="64" height="40">

        <span class="inline-flex items-center gap-1 h-8 px-2.5 rounded-md border border-emerald-100 bg-emerald-50/90 text-emerald-800" title="3D Secure">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
            <span class="text-[10px] font-bold">3D Secure</span>
        </span>
    </div>
    @unless($compact)
        <p class="mt-2 text-[10px] text-[#9CA3AF] leading-relaxed max-w-lg">
            Kartlı ödemeler <strong class="text-[#6B7280]">iyzico</strong> altyapısı ile alınır. Kart bilgileriniz sitemizde saklanmaz.
        </p>
    @endunless
</div>
