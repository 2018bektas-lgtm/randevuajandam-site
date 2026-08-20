@extends('hekim.layout')

@section('baslik', 'Üyelik & Abonelik - Randevu Ajandam')
@section('sayfa_baslik', 'Üyelik & Abonelik')

@section('icerik')
@php
    $paket = $paket ?? $doktor->aktifPaket();
    $aktif = $doktor->hasActiveMembership();
    $iptalBekliyor = $doktor->isSubscriptionCancelPending();
    $deneme = $doktor->isOnTrial();
@endphp

<div class="max-w-2xl space-y-6">
    @if(session('basarili'))
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-900 text-sm px-4 py-3">{{ session('basarili') }}</div>
    @endif
    @if(session('hata'))
        <div class="rounded-xl border border-red-100 bg-red-50 text-red-800 text-sm px-4 py-3">{{ session('hata') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 text-red-800 text-sm px-4 py-3 space-y-1">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif

    @include('frontend.hekim.partials.havale_bildirim_durumu')
    @include('hekim.partials.uyelik_yenileme_uyari', ['doktor' => $doktor])

    @if($deneme && $doktor->uyelik_bitis)
        @php
            $uTrialKalan = $doktor->membershipDaysLeft();
            $uTrialPaket = $paket;
            $uTrialAylik = $uTrialPaket ? (float) ($uTrialPaket->aylik_indirimli_fiyat ?? $uTrialPaket->aylik_fiyat ?? 0) : 0;
            if ($uTrialAylik <= 0 && $uTrialPaket) {
                $uTrialAylik = (float) $uTrialPaket->aylik_fiyat;
            }
        @endphp
        <div class="rounded-2xl border-2 border-emerald-300 bg-emerald-50 p-5 space-y-3">
            <p class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-800">Ücretsiz deneme</p>
            <p class="text-base font-bold text-emerald-950 font-display leading-snug">
                Denemeniz <strong>{{ $doktor->uyelik_bitis->format('d.m.Y') }}</strong> tarihinde biter
                @if($uTrialKalan !== null)
                    ({{ max(0, $uTrialKalan) }} gün kaldı)
                @endif.
            </p>
            <p class="text-sm text-emerald-900 leading-relaxed">
                Süre dolunca erişim için <strong>tam ücretli paket ödemesi</strong> gerekir.
                Deneme bitiminde otomatik ücret çekilmez; siz paket seçip ödersiniz.
                @if($uTrialAylik > 0)
                    <span class="block mt-1 font-semibold text-emerald-950">
                        Tam ücret örneği: ₺{{ number_format($uTrialAylik, 0, ',', '.') }}/ay (KDV dahil) ve üzeri paketler.
                    </span>
                @endif
            </p>
            <a href="{{ route('frontend.hekim.paket_sec', ['degistir' => 1]) }}"
               class="inline-flex px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-extrabold uppercase tracking-wide">
                Paket seç / öde
            </a>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <h2 class="text-lg font-bold font-display text-slate-900">Paket özeti</h2>

        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Paket</dt>
                <dd class="font-semibold text-slate-900 text-right">{{ $paket?->ad ?? 'Paket yok' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Durum</dt>
                <dd class="font-semibold text-right">
                    @if($iptalBekliyor)
                        <span class="text-amber-700">İptal edildi — dönem sonuna kadar aktif</span>
                    @elseif($deneme)
                        <span class="text-emerald-700">Ücretsiz deneme</span>
                    @elseif($aktif)
                        <span class="text-emerald-700">Aktif</span>
                    @else
                        <span class="text-red-600">Süresi dolmuş / yok</span>
                    @endif
                </dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Periyot</dt>
                <dd class="font-semibold text-slate-900">
                    @if($doktor->odeme_periyodu === 'deneme') Deneme
                    @elseif($doktor->odeme_periyodu === 'aylik') Aylık
                    @elseif($doktor->odeme_periyodu === 'yillik') Yıllık
                    @else {{ $doktor->odeme_periyodu ?? '—' }}
                    @endif
                </dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ $iptalBekliyor || $deneme ? 'Erişim bitiş' : 'Sonraki dönem' }}</dt>
                <dd class="font-mono font-semibold text-slate-900">
                    {{ $doktor->uyelik_bitis?->format('d.m.Y H:i') ?? '—' }}
                </dd>
            </div>
            @if($doktor->membershipDaysLeft() !== null)
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Kalan</dt>
                <dd class="font-semibold text-slate-900">{{ $doktor->membershipDaysLeft() }} gün</dd>
            </div>
            @endif
            @if(! $deneme && ! $iptalBekliyor && $aktif)
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Otomatik yenileme</dt>
                <dd class="font-semibold text-right">
                    @if($doktor->willAutoRenew())
                        <span class="text-emerald-700">Açık — süre dolunca 3D’siz kart çekimi</span>
                    @elseif($doktor->hasPaytrSavedCard() || (isset($klinik) && $klinik && $klinik->hasPaytrSavedCard()))
                        <span class="text-amber-700">Kart kayıtlı; yenileme kapalı veya yapılandırma eksik</span>
                    @else
                        <span class="text-slate-600">Kapalı / kayıtlı kart yok (manuel ödeme)</span>
                    @endif
                </dd>
            </div>
            @if($doktor->willAutoRenew() && ($t = $doktor->estimatedRenewalAmount()))
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Tahmini çekim</dt>
                <dd class="font-semibold text-slate-900">₺{{ number_format($t, 2, ',', '.') }}</dd>
            </div>
            @endif
            @endif
            @if($doktor->abonelik_iptal_at)
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">İptal tarihi</dt>
                <dd class="font-mono text-slate-700">{{ $doktor->abonelik_iptal_at->format('d.m.Y H:i') }}</dd>
            </div>
            @endif
        </dl>

        <div class="pt-2 space-y-3">
            <p class="text-[11px] text-amber-900/90 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2.5 leading-relaxed">
                <strong>Paket değiştir / yenile:</strong> Ödeme sonrası süre sıfırdan başlar (kalan günler devretmez; tam dönem ücreti alınır).
            </p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('frontend.hekim.paket_sec', ['degistir' => 1]) }}"
                   class="inline-flex px-4 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold">
                    Paket değiştir / yenile
                </a>
            </div>
        </div>
    </div>

    @if(isset($sonOdemeler) && $sonOdemeler->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <h2 class="text-lg font-bold font-display text-slate-900">Ödeme bildirimleri</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Havale veya kartlı ödeme kayıtlarınız. Havale bildirimleri yönetici onayına kadar "Beklemede" kalır.</p>
                </div>
                <a href="{{ route('hekim.faturalarim') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold bg-[#FFF7ED] border border-[#FED7AA] text-[#C96A2B] hover:bg-[#C96A2B] hover:text-white hover:border-[#C96A2B] transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                    </svg>
                    Faturalarım
                </a>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-100">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-3 py-2.5">Tarih</th>
                            <th class="px-3 py-2.5">Yöntem</th>
                            <th class="px-3 py-2.5">Paket</th>
                            <th class="px-3 py-2.5">Tutar</th>
                            <th class="px-3 py-2.5">Durum</th>
                            <th class="px-3 py-2.5">Fatura</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($sonOdemeler as $od)
                            <tr>
                                <td class="px-3 py-2.5 text-slate-600 whitespace-nowrap">{{ $od->created_at?->format('d.m.Y H:i') }}</td>
                                <td class="px-3 py-2.5 font-semibold">
                                    @if($od->odeme_yontemi === 'havale' || $od->provider === 'banka')
                                        Havale
                                    @elseif($od->odeme_yontemi === 'paytr' || $od->provider === 'paytr')
                                        PayTR
                                    @else
                                        {{ $od->odeme_yontemi ?? '—' }}
                                    @endif
                                </td>
                                <td class="px-3 py-2.5">{{ $od->paket?->ad ?? '—' }}</td>
                                <td class="px-3 py-2.5 font-bold">₺{{ number_format((float) $od->tutar, 2, ',', '.') }}</td>
                                <td class="px-3 py-2.5">
                                    @if($od->durum === 'beklemede')
                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-100 font-bold">Beklemede — onay bekleniyor</span>
                                    @elseif($od->durum === 'onaylandi')
                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-100 font-bold">Onaylandı</span>
                                    @elseif($od->durum === 'reddedildi')
                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-100 font-bold">Reddedildi</span>
                                    @else
                                        <span class="font-semibold text-slate-600">{{ $od->durum }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5">
                                    @if($od->fatura_url)
                                        <a href="{{ str_starts_with($od->fatura_url, 'http') ? $od->fatura_url : asset($od->fatura_url) }}"
                                           target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#FFF7ED] border border-[#FED7AA] text-[#C96A2B] hover:bg-[#C96A2B] hover:text-white hover:border-[#C96A2B] font-bold transition-all">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                            </svg>
                                            İndir
                                        </a>
                                    @elseif($od->durum === 'onaylandi')
                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-100 font-bold">Hazırlanıyor</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($iptalBekliyor)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-950 space-y-2">
            <p class="font-bold">Yenileme kapalı</p>
            <p class="text-xs leading-relaxed">
                Aboneliğinizi iptal ettiniz. Kartınızdan <strong>yeni çekim yapılmayacak</strong>.
                Mevcut haklarınız <strong>{{ $doktor->uyelik_bitis?->format('d.m.Y') }}</strong> tarihine kadar devam eder;
                bu tarihten sonra panele girişte paket seçip yeniden abone olmanız gerekir.
            </p>
        </div>
    @elseif($doktor->canCancelSubscription() || ($doktor->klinikSahibiMi() && $klinik && $klinik->uyelik_bitis && $klinik->uyelik_bitis->isFuture() && !($klinik->abonelik_yenileme_kapali ?? false)))
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h2 class="text-lg font-bold font-display text-slate-900">Aboneliği iptal et</h2>
            <p class="text-xs text-slate-500 leading-relaxed">
                Grok / Netflix tarzı iptal: aboneliği şimdi sonlandırırsınız ama
                <strong>ödediğiniz dönem bitene kadar</strong> sistemi kullanmaya devam edersiniz.
                Otomatik yenileme kapanır; dönem sonunda erişim biter ve yeni ücret kesilmez.
            </p>

            <form method="POST" action="{{ route('hekim.uyelik.iptal') }}" class="space-y-4"
                  onsubmit="return confirm('Aboneliği iptal etmek istediğinize emin misiniz? Dönem sonuna kadar erişiminiz devam eder; yenileme yapılmaz.');">
                @csrf
                @if($doktor->klinikSahibiMi() && $klinik)
                    <input type="hidden" name="hedef" value="klinik">
                    <input type="hidden" name="klinik" value="1">
                    <p class="text-xs text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                        Klinik sahibi olarak <strong>klinik aboneliği</strong> iptal edilecek.
                    </p>
                @else
                    <input type="hidden" name="hedef" value="bireysel">
                @endif
                <div>
                    <label for="neden" class="block text-xs font-semibold text-slate-600 mb-1">İptal nedeni (opsiyonel)</label>
                    <input type="text" name="neden" id="neden" maxlength="255"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm"
                           placeholder="Örn: Şimdilik ihtiyacım yok">
                </div>
                <label class="flex items-start gap-2 text-xs text-slate-700 cursor-pointer">
                    <input type="checkbox" name="onay" value="1" class="mt-0.5 rounded border-slate-300 text-[#C96A2B]" required>
                    <span>
                        İptali onaylıyorum. <strong>{{ ($klinik && $doktor->klinikSahibiMi() ? $klinik->uyelik_bitis : $doktor->uyelik_bitis)?->format('d.m.Y') ?? 'Dönem sonu' }}</strong>
                        tarihine kadar erişim sürecek; sonrasında otomatik yenileme olmayacak.
                    </span>
                </label>
                <button type="submit"
                        class="px-4 py-2.5 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-red-800 text-xs font-bold">
                    Aboneliği iptal et
                </button>
            </form>
        </div>
    @else
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-xs text-slate-600">
            İptal edilebilir aktif abonelik yok.
            <a href="{{ route('frontend.hekim.paket_sec') }}" class="text-[#C96A2B] font-bold underline ml-1">Paket seç</a>
        </div>
    @endif
</div>
@endsection
