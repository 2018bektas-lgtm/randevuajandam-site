@extends('frontend.layouts.app')

@section('baslik', ($basarili ?? false) ? 'Ödeme sonucu - Randevu Ajandam' : 'Ödeme tamamlanamadı - Randevu Ajandam')

@section('icerik')
<section class="fe-page fe-page--auth relative bg-[#FAFAFA] flex items-center justify-center overflow-hidden">
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="max-w-md w-full px-6 relative z-10 text-center space-y-6">
        @if($basarili ?? false)
            <div class="w-16 h-16 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            </div>
            <h1 class="text-2xl font-extrabold font-display text-[#111827] tracking-tight">Ödeme alındı</h1>
            <p class="text-xs text-[#6B7280] leading-relaxed">
                {{ $mesaj ?? 'Ödemeniz alındı. Üyeliğiniz birkaç saniye içinde aktifleşir. Sayfayı yenileyebilir veya panele gidebilirsiniz.' }}
            </p>
            @if(!empty($paketAd))
                <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 text-left text-xs space-y-2 shadow-sm">
                    <div class="flex justify-between gap-3">
                        <span class="text-slate-500">Paket</span>
                        <span class="font-bold text-[#111827]">{{ $paketAd }}</span>
                    </div>
                    @if(!empty($periyotLabel))
                        <div class="flex justify-between gap-3">
                            <span class="text-slate-500">Periyot</span>
                            <span class="font-semibold">{{ $periyotLabel }}</span>
                        </div>
                    @endif
                    @if(!empty($bitis))
                        <div class="flex justify-between gap-3">
                            <span class="text-slate-500">Bitiş</span>
                            <span class="font-semibold">{{ $bitis }}</span>
                        </div>
                    @endif
                    <p class="text-[10px] text-slate-400 pt-2 border-t border-slate-100">Fiyatlara KDV dahildir. Fatura talepleriniz için destek ile iletişime geçebilirsiniz.</p>
                </div>
            @endif
            <div class="flex flex-col sm:flex-row gap-2 justify-center">
                <a href="{{ route('hekim.panel') }}" class="inline-flex justify-center py-3 px-5 rounded-xl bg-[#C96A2B] text-white text-xs font-bold uppercase tracking-wider">Panele git</a>
                <a href="{{ route('frontend.hekim.paket_sec') }}" class="inline-flex justify-center py-3 px-5 rounded-xl border border-[#E5E7EB] bg-white text-xs font-bold text-slate-600">Paketler</a>
            </div>
        @else
            <div class="w-16 h-16 rounded-full bg-red-50 border border-red-100 text-red-600 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h1 class="text-2xl font-extrabold font-display text-[#111827] tracking-tight">Ödeme tamamlanamadı</h1>
            <p class="text-xs text-[#6B7280] leading-relaxed">
                {{ $mesaj ?? 'Ödeme iptal edildi veya banka tarafından reddedildi. Tekrar deneyebilir veya destek ile iletişime geçebilirsiniz.' }}
            </p>
            <div class="flex flex-col sm:flex-row gap-2 justify-center">
                <a href="{{ route('frontend.hekim.paket_sec') }}" class="inline-flex justify-center py-3 px-5 rounded-xl bg-[#C96A2B] text-white text-xs font-bold uppercase tracking-wider">Tekrar dene</a>
                <a href="{{ route('frontend.legal.iletisim') }}" class="inline-flex justify-center py-3 px-5 rounded-xl border border-[#E5E7EB] bg-white text-xs font-bold text-slate-600">Destek</a>
            </div>
        @endif
    </div>
</section>
@endsection
