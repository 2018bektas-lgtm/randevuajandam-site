@extends('klinik.layout')

@section('baslik', 'Ek Personel Koltuğu')
@section('sayfa_baslik', 'Ek Personel Koltuğu')

@section('icerik')
<div class="max-w-2xl mx-auto">
    @if(session('hata'))
        <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('hata') }}</div>
    @endif

    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm">
        <h2 class="text-lg font-bold font-display text-[#111827] mb-2">Ek personel koltuğu</h2>
        <p class="text-xs text-slate-500 mb-4">Excel: ₺300/ay (kıst uygulanır). Paket dahil: {{ $dahil }} · Ek: {{ $ek }} · Mevcut personel: {{ $mevcut }}</p>

        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 mb-4 text-sm space-y-1">
            <div class="flex justify-between"><span>Birim ({{ $periyot === 'yillik' ? 'yıllık' : 'aylık' }})</span><strong>{{ number_format($birim, 0, ',', '.') }} ₺</strong></div>
            <div class="flex justify-between"><span>Kıst oranı</span><strong>%{{ round($kist['oran'] * 100) }}</strong></div>
            <div class="flex justify-between text-[#C96A2B]"><span>1 koltuk için ödenecek</span><strong>{{ number_format($kist['tutar'], 0, ',', '.') }} ₺</strong></div>
        </div>

        <form action="{{ route('hekim.klinik.ek-personel.odeme') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-2">Adet</label>
                <div class="flex flex-wrap gap-2">
                    @for($i = 1; $i <= 5; $i++)
                        <label class="cursor-pointer">
                            <input type="radio" name="adet" value="{{ $i }}" {{ $i===1?'checked':'' }} class="peer sr-only">
                            <span class="px-4 py-2 rounded-xl border-2 border-slate-200 peer-checked:border-[#C96A2B] peer-checked:bg-[#FFF7ED] text-sm font-bold">{{ $i }}</span>
                        </label>
                    @endfor
                </div>
            </div>
            <div class="space-y-2 border-t border-slate-100 pt-4">
                <p class="text-[10px] font-bold uppercase text-slate-500">3D Secure kart</p>
                <input type="text" name="kart_sahibi" required placeholder="Kart sahibi" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs">
                <input type="text" name="kart_no" required placeholder="Kart no" maxlength="19" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs font-mono">
                <div class="grid grid-cols-3 gap-2">
                    <input type="text" name="kart_ay" required placeholder="AA" maxlength="2" class="rounded-lg border border-slate-200 px-2 py-2 text-xs text-center">
                    <input type="text" name="kart_yil" required placeholder="YY" maxlength="4" class="rounded-lg border border-slate-200 px-2 py-2 text-xs text-center">
                    <input type="text" name="kart_cvv" required placeholder="CVV" maxlength="4" class="rounded-lg border border-slate-200 px-2 py-2 text-xs text-center">
                </div>
            </div>
            <label class="flex items-start gap-2 text-[11px] text-slate-600">
                <input type="checkbox" name="okudum_anladim" value="1" required class="mt-0.5 rounded text-[#C96A2B]">
                Kıst bilgisini okudum; PayTR 3D ile ödeme istiyorum (kart saklanmaz).
            </label>
            <button type="submit" class="w-full py-3 rounded-xl bg-[#C96A2B] text-white text-xs font-bold uppercase tracking-wider">3D ile öde</button>
        </form>
    </div>
</div>
@endsection
