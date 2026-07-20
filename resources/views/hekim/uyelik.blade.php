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
            @if($doktor->abonelik_iptal_at)
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">İptal tarihi</dt>
                <dd class="font-mono text-slate-700">{{ $doktor->abonelik_iptal_at->format('d.m.Y H:i') }}</dd>
            </div>
            @endif
        </dl>

        <div class="pt-2 flex flex-wrap gap-2">
            <a href="{{ route('frontend.hekim.paket_sec') }}"
               class="inline-flex px-4 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold">
                Paket değiştir / yenile
            </a>
        </div>
    </div>

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
