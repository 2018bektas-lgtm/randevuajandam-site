@extends('frontend.layouts.app')

@section('baslik', 'Üyelik Başarılı - Randevu Ajandam')

@section('icerik')
<section class="relative bg-[#FAFAFA] py-20 min-h-[70vh] flex items-center justify-center overflow-hidden">
    <!-- Ambient Lights -->
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-[#C96A2B]/3 blur-[120px] pointer-events-none"></div>

    <div class="max-w-md w-full px-6 relative z-10 text-center space-y-8">
        
        <!-- Animated Success Tick -->
        <div class="w-20 h-20 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-500 flex items-center justify-center mx-auto animate-bounce shadow-sm">
            <svg class="w-10 h-10 animate-pulse" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
            </svg>
        </div>

        <!-- Success Header -->
        <div class="space-y-3">
            <h1 class="text-3xl font-extrabold font-display text-[#111827] tracking-tight">Üyeliğiniz Başarıyla Oluşturuldu!</h1>
            @if($doktor->paket->aylik_fiyat == 0)
                <p class="text-xs text-[#6B7280] leading-relaxed max-w-sm mx-auto">
                    Aramıza hoş geldiniz! Ücretsiz deneme profiliniz başarıyla oluşturuldu ve aktif hale getirildi.
                </p>
            @else
                <p class="text-xs text-[#6B7280] leading-relaxed max-w-sm mx-auto">
                    Aramıza hoş geldiniz! Ödemeniz başarıyla simüle edildi ve hekim profiliniz aktif hale getirildi.
                </p>
            @endif
        </div>

        <!-- Subscription Details Card -->
        <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 text-left shadow-sm space-y-4">
            <h3 class="text-[10px] font-extrabold text-[#1F2937] uppercase tracking-wider font-display border-b border-slate-100 pb-2.5">
                Hesap & Abonelik Özeti
            </h3>
            
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[#6B7280]">Hekim / Kurum</span>
                    <span class="font-bold text-[#111827] font-display">{{ $doktor->ad_soyad }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-[#6B7280]">E-Posta Adresi</span>
                    <span class="font-semibold text-[#111827]">{{ $doktor->e_posta }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-[#6B7280]">Seçilen Paket</span>
                    <span class="font-bold text-[#C96A2B] font-display">{{ $doktor->paket->ad }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-[#6B7280]">Ödeme Periyodu</span>
                    <span class="font-semibold text-[#111827] capitalize">
                        {{ $doktor->paket->aylik_fiyat == 0 ? 'Ücretsiz Plan' : ($doktor->odeme_periyodu === 'aylik' ? 'Aylık Plan' : 'Yıllık Plan') }}
                    </span>
                </div>

                @if($doktor->paket->aylik_fiyat > 0)
                <div class="flex items-center justify-between border-t border-dashed border-slate-100 pt-3 text-[11px]">
                    <span class="text-[#6B7280]">Sonraki Ödeme Tarihi</span>
                    <span class="font-bold text-[#111827] font-mono">
                        {{ $doktor->uyelik_bitis ? $doktor->uyelik_bitis->format('d.m.Y H:i') : '-' }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 items-center w-full">
            <a href="/" 
               class="w-full sm:flex-1 py-3 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#6B7280] hover:text-[#111827] font-bold text-xs uppercase tracking-wider transition-all font-display text-center select-none shadow-sm">
                Ana Sayfaya Dön
            </a>
            <a href="{{ route('hekim.panel') }}" 
               class="w-full sm:flex-1 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all font-display text-center select-none shadow-sm hover:shadow-md">
                Hekim Paneline Git
            </a>
        </div>

    </div>
</section>
@endsection
