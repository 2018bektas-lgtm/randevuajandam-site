@extends('frontend.layouts.app')

@section('baslik', 'Üyelik Başarılı - Randevu Ajandam')

@section('icerik')
<section class="fe-page fe-page--auth relative bg-[#FAFAFA] flex items-center justify-center overflow-hidden">
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

        @if(session('basarili'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-900 font-semibold text-left">
                {{ session('basarili') }}
            </div>
        @endif

        <div class="text-left w-full">
            @include('frontend.hekim.partials.havale_bildirim_durumu')
        </div>

        <!-- Success Header -->
        <div class="space-y-3">
            <h1 class="text-3xl font-extrabold font-display text-[#111827] tracking-tight">
                @if($doktor->isOnTrial())
                    Denemeniz başladı!
                @elseif(!empty($sonOnayliHavale) && empty($bekleyenHavale))
                    Havale onaylandı — üyelik aktif!
                @else
                    Üyeliğiniz Başarıyla Oluşturuldu!
                @endif
            </h1>
            @if($doktor->isOnTrial())
                <p class="text-xs text-[#6B7280] leading-relaxed max-w-sm mx-auto">
                    Aramıza hoş geldiniz! <strong class="text-emerald-700">{{ $doktor->uyelik_bitis?->format('d.m.Y') }}</strong> tarihine kadar
                    ücretsiz deneme aktif. Süre bitince girişte paket seçip ödeme yapmanız istenir.
                </p>
            @elseif(!empty($sonOnayliHavale) && empty($bekleyenHavale))
                <p class="text-xs text-[#6B7280] leading-relaxed max-w-sm mx-auto">
                    Banka havaleniz yönetici tarafından onaylandı.
                    <strong class="text-emerald-700">Paneliniz açıldı</strong> — randevu ve hasta yönetimine başlayabilirsiniz.
                </p>
            @elseif($doktor->paket && (float) $doktor->paket->aylik_fiyat == 0)
                <p class="text-xs text-[#6B7280] leading-relaxed max-w-sm mx-auto">
                    Aramıza hoş geldiniz! Ücretsiz planınız aktif hale getirildi.
                </p>
            @else
                <p class="text-xs text-[#6B7280] leading-relaxed max-w-sm mx-auto">
                    Aramıza hoş geldiniz! Ödemeniz alındı ve hekim profiliniz aktif hale getirildi.
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
                        @if($doktor->odeme_periyodu === 'deneme')
                            Ücretsiz deneme
                        @elseif($doktor->paket && (float) $doktor->paket->aylik_fiyat == 0)
                            Ücretsiz Plan
                        @elseif($doktor->odeme_periyodu === 'aylik')
                            Aylık Plan
                        @else
                            Yıllık Plan
                        @endif
                    </span>
                </div>

                @if($doktor->uyelik_bitis)
                <div class="flex items-center justify-between border-t border-dashed border-slate-100 pt-3 text-[11px]">
                    <span class="text-[#6B7280]">{{ $doktor->isOnTrial() ? 'Deneme bitiş' : 'Sonraki dönem' }}</span>
                    <span class="font-bold text-[#111827] font-mono">
                        {{ $doktor->uyelik_bitis->format('d.m.Y H:i') }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        @if(session('onboarding_domain_done'))
        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-5 text-left space-y-2">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-800">Web sitesi domaini</p>
            <p class="text-sm font-bold text-emerald-900 font-mono">{{ session('onboarding_domain_done') }}</p>
            <p class="text-xs text-emerald-800/80">Domain kaydı tamamlandı. DNS yayılımı bir süre alabilir.</p>
        </div>
        @endif

        @if(session('plain_api_secret'))
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-left space-y-2">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-amber-900">API secret (yalnızca bir kez)</p>
            <p class="text-xs font-mono break-all text-amber-950 bg-white/80 rounded-lg px-3 py-2 border border-amber-100">{{ session('plain_api_secret') }}</p>
            <p class="text-[11px] text-amber-800">Hemen kopyalayın — bu sayfayı kapatınca bir daha gösterilmez. Panel → Web Sitesi’nden anahtar yenileyebilirsiniz.</p>
        </div>
        @endif

        @if($doktor->needsWebsiteDomainOnboarding())
        <div class="bg-orange-50 border border-orange-100 rounded-2xl p-5 text-left space-y-3">
            <p class="text-sm font-bold text-orange-900">Web siteniz için domain henüz kurulmadı</p>
            <p class="text-xs text-orange-800/90">Paketinize web sitesi dahil. Domain seçerek sitenizi hemen açabilirsiniz.</p>
            <a href="{{ route('frontend.hekim.onboarding.domain') }}"
               class="inline-flex px-4 py-2.5 rounded-xl bg-[#C96A2B] text-white text-xs font-bold uppercase tracking-wide">
                Domain kurulumuna git
            </a>
        </div>
        @endif

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
