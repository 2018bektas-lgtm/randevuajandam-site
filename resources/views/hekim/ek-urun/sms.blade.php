@extends('hekim.layout')

@section('baslik', 'SMS Kontör Satın Al')
@section('sayfa_baslik', 'SMS Kontör')

@section('icerik')
<div class="max-w-3xl mx-auto space-y-6">
    @if(session('hata'))
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold">{{ session('hata') }}</div>
    @endif

    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm">
        <h2 class="text-lg font-bold font-display text-[#111827] mb-1">SMS kontör durumunuz</h2>
        <p class="text-xs text-slate-500 mb-4">Paket kotası + satın alınan ek kontör. WhatsApp yok; yalnızca SMS.</p>
        <div class="grid grid-cols-3 gap-3">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <p class="text-2xl font-bold text-[#111827]">{{ $kullanilan }}</p>
                <p class="text-[10px] text-slate-500 uppercase font-bold">Bu ay kullanılan</p>
            </div>
            <div class="p-4 rounded-xl bg-orange-50 border border-orange-100 text-center">
                <p class="text-2xl font-bold text-[#C96A2B]">{{ $kalan === null ? '∞' : $kalan }}</p>
                <p class="text-[10px] text-slate-500 uppercase font-bold">Kalan</p>
            </div>
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-center">
                <p class="text-2xl font-bold text-emerald-700">{{ $ek }}</p>
                <p class="text-[10px] text-slate-500 uppercase font-bold">Ek kontör bakiyesi</p>
            </div>
        </div>
        <p class="text-[11px] text-slate-500 mt-3">Paket: <strong>{{ $paket->ad }}</strong> · Aylık paket kotası: {{ $paket->sms_aylik_kontor ?? 'limitsiz' }}</p>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        @foreach($paketler as $kod => $p)
            <form action="{{ route('hekim.ek-urun.sms.odeme') }}" method="POST" class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm flex flex-col">
                @csrf
                <input type="hidden" name="paket_kod" value="{{ $kod }}">
                <h3 class="text-base font-bold font-display text-[#111827]">{{ $p['etiket'] }}</h3>
                <p class="text-3xl font-extrabold text-[#C96A2B] mt-2">{{ number_format($p['fiyat'], 0, ',', '.') }} ₺</p>
                <p class="text-xs text-slate-500 mt-1">Tek seferlik · KDV dahil · {{ number_format($p['adet'], 0, ',', '.') }} SMS</p>
                @if(!empty($p['not']))
                    <p class="text-[10px] text-slate-400 mt-1">{{ $p['not'] }}</p>
                @endif
                <div class="mt-4 space-y-2 border-t border-slate-100 pt-4">
                    <p class="text-[10px] font-bold uppercase text-slate-500">3D Secure kart bilgisi</p>
                    <input type="text" name="kart_sahibi" required placeholder="Kart sahibi" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs">
                    <input type="text" name="kart_no" required placeholder="Kart no" maxlength="19" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs font-mono">
                    <div class="grid grid-cols-3 gap-2">
                        <input type="text" name="kart_ay" required placeholder="AA" maxlength="2" class="rounded-lg border border-slate-200 px-2 py-2 text-xs text-center">
                        <input type="text" name="kart_yil" required placeholder="YY" maxlength="4" class="rounded-lg border border-slate-200 px-2 py-2 text-xs text-center">
                        <input type="text" name="kart_cvv" required placeholder="CVV" maxlength="4" class="rounded-lg border border-slate-200 px-2 py-2 text-xs text-center">
                    </div>
                </div>
                <label class="mt-3 flex items-start gap-2 text-[11px] text-slate-600">
                    <input type="checkbox" name="okudum_anladim" value="1" required class="mt-0.5 rounded text-[#C96A2B]">
                    PayTR 3D ile ödemeyi onaylıyorum (kart saklanmaz).
                </label>
                <button type="submit" class="mt-4 w-full py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold uppercase tracking-wider">
                    3D ile satın al
                </button>
            </form>
        @endforeach
    </div>
</div>
@endsection
