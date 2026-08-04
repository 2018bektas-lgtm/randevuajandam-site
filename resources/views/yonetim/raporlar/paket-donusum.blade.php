@extends('yonetim.layout')

@section('baslik', 'Paket Dönüşüm Raporu')
@section('sayfa_baslik', 'Paket & dönüşüm')

@section('icerik')
<div class="space-y-6 max-w-5xl">
    <div>
        <h2 class="text-xl font-bold font-display text-[#111827]">Paket dönüşüm ve ek satış</h2>
        <p class="text-xs text-slate-500 mt-1">Excel hedefi: ücretsiz → ücretli %8–10 (6 ay). Bu panel anlık + son 90 gün sinyali verir.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <p class="text-[10px] font-bold uppercase text-slate-400">Ücretsiz (Vitrin) aktif</p>
            <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ $ucretsizAktif }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <p class="text-[10px] font-bold uppercase text-slate-400">Ücretli aktif hekim</p>
            <p class="text-3xl font-extrabold text-[#C96A2B] mt-1">{{ $ucretliAktif }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <p class="text-[10px] font-bold uppercase text-slate-400">Ücretli payı</p>
            <p class="text-3xl font-extrabold mt-1
                @if($hedefDurum==='hedef_alti') text-red-600
                @elseif($hedefDurum==='hedefte') text-amber-600
                @else text-emerald-600 @endif">%{{ $donusumOrani }}</p>
            <p class="text-[10px] text-slate-400">Hedef %{{ $hedefMin }}–{{ $hedefMax }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <p class="text-[10px] font-bold uppercase text-slate-400">Aktif klinik</p>
            <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ $klinikSayisi }}</p>
        </div>
    </div>

    <div class="grid sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200">
            <p class="text-xs font-bold text-slate-600">Son 90 günde ücretli ödeme (benzersiz hekim)</p>
            <p class="text-2xl font-bold text-[#111827] mt-2">{{ $yeniUcretli }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200">
            <p class="text-xs font-bold text-slate-600">Son 90 günde paket yükseltme sinyali</p>
            <p class="text-2xl font-bold text-[#111827] mt-2">{{ $yukseltme }}</p>
            <p class="text-[10px] text-slate-400">Aynı hekimin birden fazla farklı paket ödemesi</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200">
            <p class="text-xs font-bold text-slate-600">SMS ek satış (90g)</p>
            <p class="text-2xl font-bold text-[#111827] mt-2">{{ (int)($smsSatis->adet ?? 0) }}</p>
            <p class="text-[10px] text-slate-400">Ciro ₺{{ number_format((float)($smsSatis->ciro ?? 0), 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="p-5 rounded-2xl bg-white border border-slate-200">
        <h3 class="text-sm font-bold font-display mb-3">Paket dağılımı (aktif hekim)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[10px] uppercase text-slate-400 border-b">
                        <th class="py-2">Paket</th>
                        <th>Tür</th>
                        <th>Aylık</th>
                        <th class="text-right">Hekim</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($paketDagilim as $row)
                        <tr>
                            <td class="py-2 font-semibold">{{ $row->paket?->ad ?? ('#'.$row->paket_id) }}</td>
                            <td class="text-slate-500">{{ $row->paket?->tur }}</td>
                            <td>₺{{ number_format((float)($row->paket?->aylik_fiyat ?? 0), 0, ',', '.') }}</td>
                            <td class="text-right font-bold">{{ $row->c }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="p-5 rounded-2xl bg-[#FFF7ED] border border-orange-100 text-xs text-amber-950 leading-relaxed">
        <strong>Personel koltuk satışı (90g):</strong> {{ (int)($personelSatis->adet ?? 0) }} adet ·
        ₺{{ number_format((float)($personelSatis->ciro ?? 0), 0, ',', '.') }}
        <br>
        Not: Dönüşüm oranı tüm aktif hekimler içinde ücretli payıdır; Excel’deki “Vitrin’den gelen %8–10” için 6 aylık kohort takibi ayrıca eklenebilir.
    </div>
</div>
@endsection
