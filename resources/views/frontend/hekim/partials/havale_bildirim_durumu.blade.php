{{-- Bekleyen / onaylı havale — hekim frontend (çok net durum) --}}
@php
    $doktorCtx = $doktor ?? auth('doktor')->user();
    $aktifUyelik = $doktorCtx && method_exists($doktorCtx, 'hasActiveMembership')
        ? $doktorCtx->hasActiveMembership()
        : false;
@endphp

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
    <div class="mb-6 rounded-2xl border-2 border-amber-300 bg-gradient-to-br from-amber-50 to-orange-50 p-5 sm:p-6 shadow-sm">
        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-amber-200/80 text-amber-950 border border-amber-300">
                    <span class="w-2 h-2 rounded-full bg-amber-600 animate-pulse"></span>
                    Adım 1/2 tamam · Yönetici onayı bekleniyor
                </span>
            </div>
            <div>
                <h3 class="text-lg sm:text-xl font-extrabold text-amber-950 font-display tracking-tight">
                    Havale bildiriminiz alındı
                </h3>
                <p class="mt-2 text-sm text-amber-900/90 leading-relaxed max-w-2xl">
                    Ödeme bildirimi <strong>sisteme kaydedildi</strong>.
                    Banka hareketi kontrol edilip onaylanınca üyeliğiniz <strong>otomatik açılır</strong>.
                    <span class="block mt-1 font-semibold">Şu an panel / randevu özellikleri henüz aktif değildir — onay sonrası açılır.</span>
                </p>
                <p class="mt-2 text-xs text-amber-800/80">
                    Aynı tutar için <strong>tekrar bildirim göndermenize gerek yok</strong>. Onaylanınca bu sarı kutu yeşile döner ve paneli kullanırsınız.
                </p>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                <div class="rounded-xl bg-white border border-amber-100 px-3.5 py-3">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-amber-700/80">Paket</dt>
                    <dd class="mt-1 font-bold text-slate-900">{{ $paketAd }} · {{ $periyot }}</dd>
                </div>
                <div class="rounded-xl bg-white border border-amber-100 px-3.5 py-3">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-amber-700/80">Tutar</dt>
                    <dd class="mt-1 font-bold text-slate-900">₺{{ number_format((float) $h->tutar, 2, ',', '.') }}</dd>
                </div>
                <div class="rounded-xl bg-white border border-amber-100 px-3.5 py-3 sm:col-span-2">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-amber-700/80">Girdiğiniz havale referansı</dt>
                    <dd class="mt-1 font-mono font-semibold text-slate-900 break-all">{{ $h->havale_referans ?: '—' }}</dd>
                </div>
                <div class="rounded-xl bg-white border border-amber-100 px-3.5 py-3 sm:col-span-2">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-amber-700/80">Bildirim zamanı</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $h->created_at?->format('d.m.Y H:i') }}</dd>
                </div>
            </dl>
            <div class="flex flex-wrap gap-2 pt-1">
                <span class="inline-flex items-center px-3 py-2 rounded-xl bg-white border border-amber-200 text-[11px] font-bold text-amber-900">
                    Sıra sizde değil · Onay yönetimde
                </span>
                @if(Route::has('frontend.legal.iletisim'))
                    <a href="{{ route('frontend.legal.iletisim') }}" class="inline-flex items-center px-3 py-2 rounded-xl text-[11px] font-bold text-amber-900 underline">
                        Destek ile iletişim
                    </a>
                @endif
            </div>
        </div>
    </div>
@elseif(!empty($sonOnayliHavale))
    @php
        $o = $sonOnayliHavale;
        $paketAd = $o->paket?->ad ?? ($doktorCtx?->paket?->ad ?? 'Paket');
        $periyot = match ($o->odeme_periyodu ?? $doktorCtx?->odeme_periyodu) {
            'yillik' => 'Yıllık',
            'aylik' => 'Aylık',
            default => $o->odeme_periyodu ?? '—',
        };
        $bitis = $doktorCtx?->uyelik_bitis;
        $yeniOnay = $o->onaylandi_at && $o->onaylandi_at->gt(now()->subDays(14));
    @endphp
    <div class="mb-6 rounded-2xl border-2 border-emerald-300 bg-gradient-to-br from-emerald-50 via-white to-teal-50 p-5 sm:p-7 shadow-md overflow-hidden relative">
        <div class="absolute -top-8 -right-8 w-32 h-32 rounded-full bg-emerald-200/40 blur-2xl pointer-events-none"></div>
        <div class="relative space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-emerald-600 text-white shadow-sm">
                    ✓ Havale onaylandı
                </span>
                @if($aktifUyelik)
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-200">
                        Üyelik aktif
                    </span>
                @endif
            </div>

            <div>
                <h3 class="text-xl sm:text-2xl font-extrabold text-emerald-950 font-display tracking-tight">
                    @if($yeniOnay)
                        Tebrikler — ödemeniz onaylandı!
                    @else
                        Havale ile üyeliğiniz onaylı
                    @endif
                </h3>
                <p class="mt-2 text-sm text-emerald-900/90 leading-relaxed max-w-2xl">
                    @if($aktifUyelik)
                        <strong>Artık hekim panelinizi kullanabilirsiniz.</strong>
                        Randevu, hasta, hizmet ve diğer özellikler paket haklarınıza göre açıktır.
                        Ana sitede profiliniz listelenir (isterseniz Web Sitesi ayarından gizleyebilirsiniz).
                    @else
                        Havale kaydı onaylandı. Üyelik bilgileriniz kısa süre içinde güncellenmiş olmalı; paneli yenileyin veya tekrar giriş yapın.
                    @endif
                </p>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                <div class="rounded-xl bg-white border border-emerald-100 px-3.5 py-3 shadow-sm">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-emerald-700/70">Paket</dt>
                    <dd class="mt-1 font-bold text-slate-900">{{ $paketAd }}</dd>
                    <dd class="text-[11px] text-slate-500 mt-0.5">{{ $periyot }}</dd>
                </div>
                <div class="rounded-xl bg-white border border-emerald-100 px-3.5 py-3 shadow-sm">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-emerald-700/70">Ödenen</dt>
                    <dd class="mt-1 font-bold text-slate-900">₺{{ number_format((float) $o->tutar, 2, ',', '.') }}</dd>
                </div>
                <div class="rounded-xl bg-white border border-emerald-100 px-3.5 py-3 shadow-sm">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-emerald-700/70">Onay / bitiş</dt>
                    <dd class="mt-1 font-bold text-slate-900">
                        {{ $o->onaylandi_at?->format('d.m.Y H:i') ?? '—' }}
                    </dd>
                    @if($bitis)
                        <dd class="text-[11px] text-slate-500 mt-0.5">Üyelik bitiş: {{ $bitis->format('d.m.Y') }}</dd>
                    @endif
                </div>
            </dl>

            <div class="flex flex-col sm:flex-row flex-wrap gap-2 pt-1 items-stretch sm:items-center">
                @if(Route::has('hekim.panel') && ! request()->routeIs('hekim.panel'))
                    <a href="{{ route('hekim.panel') }}"
                       class="inline-flex justify-center items-center px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-sm transition-colors">
                        Panele git — kullanmaya başla
                    </a>
                @endif
                @if(Route::has('hekim.uyelik') && ! request()->routeIs('hekim.uyelik'))
                    <a href="{{ route('hekim.uyelik') }}"
                       class="inline-flex justify-center items-center px-5 py-3 rounded-xl border border-emerald-200 bg-white hover:bg-emerald-50 text-emerald-900 text-sm font-bold transition-colors">
                        Üyelik detayı
                    </a>
                @endif
                @if(!empty($havaleKartKapatilabilir) && $havaleKartKapatilabilir && Route::has('hekim.panel'))
                    <a href="{{ route('hekim.panel', ['havale_onay_kapat' => 1, 'odeme_id' => $o->id]) }}"
                       class="inline-flex justify-center items-center px-4 py-3 rounded-xl text-sm font-semibold text-emerald-800/80 hover:text-emerald-950 underline underline-offset-2">
                        Anladım, bu kartı kapat
                    </a>
                @endif
            </div>
            <p class="text-[10px] text-emerald-800/70 pt-1">
                Bu tebrik kartı kalıcı değildir — en fazla birkaç gün görünür; kapatabilir veya üyelik özetini “Üyelik detayı”ndan izleyebilirsiniz.
            </p>
        </div>
    </div>
@elseif($aktifUyelik && !empty($showAktifUyelikKart) && $showAktifUyelikKart)
    {{-- Genel aktif üyelik (havale dışı da) — ödeme sayfasında “neden hâlâ buradayım?” --}}
    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
        <h3 class="text-base font-extrabold text-emerald-950 font-display">Üyeliğiniz zaten aktif</h3>
        <p class="mt-1 text-xs text-emerald-900/85 leading-relaxed">
            Aktif paket: <strong>{{ $doktorCtx->aktifPaket()?->ad ?? $doktorCtx->paket?->ad ?? '—' }}</strong>
            @if($doktorCtx->uyelik_bitis)
                · bitiş <strong>{{ $doktorCtx->uyelik_bitis->format('d.m.Y') }}</strong>
            @endif
        </p>
        <a href="{{ route('hekim.panel') }}" class="inline-flex mt-3 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold">Panele dön</a>
    </div>
@endif
