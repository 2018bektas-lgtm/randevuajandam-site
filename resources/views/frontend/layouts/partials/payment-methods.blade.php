{{--
  iyzico mağaza şartları: sitede “iyzico ile Öde”, Visa, Mastercard logoları yer almalı.
  Kaynak: https://docs.iyzico.com/ek-bilgiler/iyzico-logo-paketi
  Resmî logo paketi: iyzico dokümantasyonundan indirilebilir; burada erişilebilir SVG rozetler kullanılır.
--}}
@php
    $compact = $compact ?? false;
@endphp
<div class="{{ $compact ? '' : 'w-full' }}" role="group" aria-label="Güvenli ödeme yöntemleri">
    @unless($compact)
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#6B7280] font-display mb-2.5">
            Güvenli ödeme
        </p>
    @endunless
    <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
        {{-- iyzico ile Öde --}}
        <span class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg border border-[#E5E7EB] bg-white shadow-sm" title="iyzico ile Öde">
            <svg class="h-4 w-auto" viewBox="0 0 80 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <text x="0" y="15" font-family="system-ui,sans-serif" font-size="12" font-weight="800" fill="#1E64FF">iyzico</text>
            </svg>
            <span class="text-[10px] font-bold text-slate-700 tracking-tight">ile Öde</span>
        </span>

        {{-- Visa --}}
        <span class="inline-flex items-center justify-center h-8 px-2.5 rounded-lg border border-[#E5E7EB] bg-white shadow-sm" title="Visa">
            <svg class="h-3.5 w-10" viewBox="0 0 48 16" xmlns="http://www.w3.org/2000/svg" aria-label="Visa">
                <text x="0" y="13" font-family="system-ui,sans-serif" font-size="14" font-weight="800" font-style="italic" fill="#1A1F71">VISA</text>
            </svg>
        </span>

        {{-- Mastercard --}}
        <span class="inline-flex items-center justify-center h-8 px-2 rounded-lg border border-[#E5E7EB] bg-white shadow-sm" title="Mastercard">
            <svg class="h-5 w-8" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" aria-label="Mastercard">
                <circle cx="15" cy="12" r="8" fill="#EB001B"/>
                <circle cx="23" cy="12" r="8" fill="#F79E1B"/>
                <path d="M19 6.2a8 8 0 010 11.6 8 8 0 000-11.6z" fill="#FF5F00"/>
            </svg>
        </span>

        {{-- Troy --}}
        <span class="inline-flex items-center justify-center h-8 px-2.5 rounded-lg border border-[#E5E7EB] bg-white shadow-sm" title="Troy">
            <span class="text-[11px] font-extrabold tracking-wide text-[#00A651]">TROY</span>
        </span>

        {{-- 3D Secure --}}
        <span class="inline-flex items-center gap-1 h-8 px-2.5 rounded-lg border border-emerald-100 bg-emerald-50/80 text-emerald-800" title="3D Secure">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
            <span class="text-[10px] font-bold">3D Secure</span>
        </span>
    </div>
    @unless($compact)
        <p class="mt-2 text-[10px] text-[#9CA3AF] leading-relaxed max-w-md">
            Kartlı ödemeler <strong class="text-[#6B7280]">iyzico</strong> altyapısı ile alınır. Kart bilgileriniz sitemizde saklanmaz.
        </p>
    @endunless
</div>
