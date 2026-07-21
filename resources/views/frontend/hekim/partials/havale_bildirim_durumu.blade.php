{{-- Bekleyen / son havale bildirimi — hekim frontend --}}
@if(!empty($bekleyenHavale))
    @php
        $h = $bekleyenHavale;
        $paketAd = $h->paket?->ad ?? 'Paket';
        $periyot = match ($h->odeme_periyodu) {
            'yillik' => 'Yıllık',
            'aylik' => 'Aylık',
            'deneme' => 'Deneme',
            default => $h->odeme_periyodu ?? '—',
        };
    @endphp
    <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div class="min-w-0 space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-200">
                        Bildirim alındı — onay bekleniyor
                    </span>
                </div>
                <h3 class="text-sm font-bold text-amber-950 font-display">
                    Havale ödeme bildiriminiz sisteme kaydedildi
                </h3>
                <p class="text-xs text-amber-900/85 leading-relaxed max-w-xl">
                    Yönetici banka hareketinizi kontrol edip üyeliğinizi açacak.
                    <strong>Tekrar bildirim göndermenize gerek yok</strong> — aynı tutar için ikinci dekont yazmayın.
                    Onaylanınca e-posta / panel üzerinden üyeliğiniz aktif görünür.
                </p>
                <dl class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                    <div class="rounded-xl bg-white/70 border border-amber-100 px-3 py-2">
                        <dt class="text-amber-800/70 font-semibold uppercase tracking-wider text-[9px]">Paket</dt>
                        <dd class="font-bold text-amber-950 mt-0.5">{{ $paketAd }} · {{ $periyot }}</dd>
                    </div>
                    <div class="rounded-xl bg-white/70 border border-amber-100 px-3 py-2">
                        <dt class="text-amber-800/70 font-semibold uppercase tracking-wider text-[9px]">Tutar</dt>
                        <dd class="font-bold text-amber-950 mt-0.5">₺{{ number_format((float) $h->tutar, 2, ',', '.') }}</dd>
                    </div>
                    <div class="rounded-xl bg-white/70 border border-amber-100 px-3 py-2 sm:col-span-2">
                        <dt class="text-amber-800/70 font-semibold uppercase tracking-wider text-[9px]">Sizin girdiğiniz referans</dt>
                        <dd class="font-mono font-semibold text-amber-950 mt-0.5 break-all">{{ $h->havale_referans ?: '—' }}</dd>
                    </div>
                    <div class="rounded-xl bg-white/70 border border-amber-100 px-3 py-2 sm:col-span-2">
                        <dt class="text-amber-800/70 font-semibold uppercase tracking-wider text-[9px]">Bildirim zamanı</dt>
                        <dd class="font-semibold text-amber-950 mt-0.5">{{ $h->created_at?->format('d.m.Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endif

@if(!empty($sonOnayliHavale) && empty($bekleyenHavale))
    @php $o = $sonOnayliHavale; @endphp
    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 sm:p-5">
        <div class="flex flex-wrap items-center gap-2 mb-1">
            <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                Havale onaylandı
            </span>
        </div>
        <p class="text-xs text-emerald-900 leading-relaxed">
            Son havale bildiriminiz onaylandı
            @if($o->onaylandi_at)
                ({{ $o->onaylandi_at->format('d.m.Y H:i') }})
            @endif.
            Üyeliğiniz aktif olmalı; paneli kullanmaya devam edebilirsiniz.
        </p>
    </div>
@endif
