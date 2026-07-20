@extends('frontend.layouts.app')

@section('baslik', 'Güvenli Ödeme - PayTR - Randevu Ajandam')

@section('icerik')
<section class="fe-page relative bg-[#FAFAFA] overflow-hidden">
    <div class="fe-container max-w-3xl">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-[#C96A2B] font-display">PayTR güvenli ödeme</p>
                <h1 class="text-xl font-extrabold text-[#111827] font-display tracking-tight">Kart ile ödeme</h1>
                <p class="text-xs text-[#6B7280] mt-1">
                    Sipariş no: <span class="font-mono font-semibold text-[#111827]">{{ $merchantOid }}</span>
                    · Tutar: <span class="font-bold text-[#C96A2B]">{{ number_format((float) $odeme->tutar, 2, ',', '.') }} ₺</span>
                </p>
            </div>
            <a href="{{ route('frontend.hekim.paket_sec') }}" class="text-xs font-bold text-[#6B7280] hover:text-[#C96A2B]">← Geri</a>
        </div>

        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-3 sm:p-5 shadow-sm">
            <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
            <iframe
                src="https://www.paytr.com/odeme/guvenli/{{ $token }}"
                id="paytriframe"
                frameborder="0"
                scrolling="no"
                style="width: 100%; min-height: 420px;"
                title="PayTR güvenli ödeme"
            ></iframe>
            <script>
                if (window.iFrameResize) {
                    iFrameResize({ checkOrigin: false }, '#paytriframe');
                }
            </script>
        </div>

        <p class="mt-4 text-[11px] text-center text-[#9CA3AF] leading-relaxed">
            Ödeme PayTR altyapısı ile 3D Secure üzerinden alınır. Kart verileri Randevu Ajandam sunucularında saklanmaz.
        </p>
    </div>
</section>
@endsection
