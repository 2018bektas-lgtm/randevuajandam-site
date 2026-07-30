{{-- Üyelik bitiş / otomatik yenileme paneli uyarısı --}}
@php
    /** @var \App\Models\Doktor $doktor */
    $doktor = $doktor ?? auth('doktor')->user();
    if (! $doktor) {
        return;
    }
    $klinikCtx = ($doktor->klinikSahibiMi() && $doktor->klinik) ? $doktor->klinik : null;
    $bitis = $klinikCtx?->uyelik_bitis ?? $doktor->uyelik_bitis;
    $kalan = $bitis ? (int) now()->startOfDay()->diffInDays($bitis->copy()->startOfDay(), false) : null;
    $auto = $klinikCtx ? $klinikCtx->willAutoRenew() : $doktor->willAutoRenew();
    $tutar = $klinikCtx ? $klinikCtx->estimatedRenewalAmount() : $doktor->estimatedRenewalAmount();
    $periyot = $klinikCtx?->odeme_periyodu ?? $doktor->odeme_periyodu;
    $periyotLabel = $periyot === 'yillik' ? 'yıllık' : 'aylık';
    $show = $bitis
        && $kalan !== null
        && $kalan >= 0
        && $kalan <= 14
        && ! $doktor->isOnTrial()
        && ($doktor->odeme_periyodu ?? '') !== 'deneme'
        && ! $doktor->isSubscriptionCancelPending();
@endphp

@if($show)
    @if($auto)
        <div class="mb-6 p-5 md:p-6 rounded-2xl bg-sky-50 border border-sky-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-widest text-sky-800 mb-1">Otomatik yenileme</p>
                <h4 class="font-bold text-sky-950 text-sm font-display">
                    @if($kalan === 0)
                        Üyeliğiniz bugün doluyor — kartınızdan otomatik çekim yapılacak
                    @else
                        {{ $kalan }} gün sonra süreniz doluyor; aboneliğiniz otomatik yenilenecek
                    @endif
                </h4>
                <p class="text-xs text-sky-900/85 mt-1.5 max-w-xl leading-relaxed">
                    Bitiş: <strong>{{ $bitis->format('d.m.Y') }}</strong>.
                    Kayıtlı kartınızdan <strong>3D Secure olmadan</strong>
                    @if($tutar)
                        yaklaşık <strong>₺{{ number_format($tutar, 2, ',', '.') }}</strong>
                    @endif
                    ({{ $periyotLabel }} paket ücreti) tahsil edilecektir.
                    İstemiyorsanız dönem bitmeden aboneliği iptal ederek yenilemeyi kapatabilirsiniz.
                </p>
            </div>
            <a href="{{ route('hekim.uyelik') }}"
               class="shrink-0 px-4 py-2.5 rounded-xl bg-sky-700 hover:bg-sky-800 text-white text-xs font-bold transition-all">
                Üyelik / iptal
            </a>
        </div>
    @else
        <div class="mb-6 p-5 md:p-6 rounded-2xl bg-amber-50 border border-amber-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-widest text-amber-800 mb-1">Üyelik hatırlatması</p>
                <h4 class="font-bold text-amber-950 text-sm font-display">
                    @if($kalan === 0)
                        Üyeliğiniz bugün sona eriyor
                    @else
                        {{ $kalan }} gün sonra üyeliğiniz sona erecek
                    @endif
                </h4>
                <p class="text-xs text-amber-900/85 mt-1.5 max-w-xl leading-relaxed">
                    Bitiş: <strong>{{ $bitis->format('d.m.Y') }}</strong>.
                    @if($doktor->hasPaytrSavedCard() || ($klinikCtx && $klinikCtx->hasPaytrSavedCard()))
                        Kayıtlı kartınız var; otomatik yenileme kapalıysa veya çekim başarısız olursa
                        paket seçip yeniden ödeme yapmanız gerekir.
                    @else
                        Bu üyelikte kayıtlı kart / otomatik yenileme yok — süre bitince
                        <strong>manuel paket ödemesi</strong> gerekir.
                    @endif
                </p>
            </div>
            <a href="{{ route('frontend.hekim.paket_sec', ['degistir' => 1]) }}"
               class="shrink-0 px-4 py-2.5 rounded-xl bg-amber-700 hover:bg-amber-800 text-white text-xs font-bold transition-all">
                Paket yenile
            </a>
        </div>
    @endif
@endif
