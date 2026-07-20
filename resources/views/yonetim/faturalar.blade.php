@extends('yonetim.layout')

@section('baslik', 'Faturalar - Randevu Ajandam')
@section('sayfa_baslik', 'Üyelik faturaları (manuel)')

@section('icerik')
<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('yonetim.faturalar', ['fatura' => 'bekliyor']) }}" class="px-3 py-2 rounded-xl text-xs font-bold border {{ ($durum ?? '') === 'bekliyor' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-white border-slate-200' }}">Fatura bekliyor</a>
    <a href="{{ route('yonetim.faturalar', ['fatura' => 'kesildi']) }}" class="px-3 py-2 rounded-xl text-xs font-bold border {{ ($durum ?? '') === 'kesildi' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200' }}">Kesildi</a>
    <a href="{{ route('yonetim.faturalar', ['fatura' => 'onayli_odeme']) }}" class="px-3 py-2 rounded-xl text-xs font-bold border {{ ($durum ?? '') === 'onayli_odeme' ? 'bg-slate-100 border-slate-300' : 'bg-white border-slate-200' }}">Tüm onaylı ödemeler</a>
</div>

@if(session('basarili'))
    <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-xs text-emerald-800 font-semibold">{{ session('basarili') }}</div>
@endif

<div class="bg-white border border-[#E5E7EB] rounded-2xl overflow-hidden shadow-sm">
    <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 border-b">
            <tr>
                <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-500">Tarih</th>
                <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-500">Hekim</th>
                <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-500">Paket</th>
                <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-500">Tutar</th>
                <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-500">Ödeme</th>
                <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-500">Fatura</th>
                <th class="px-4 py-3 text-[10px] font-bold uppercase text-slate-500"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($odemeler as $o)
                <tr>
                    <td class="px-4 py-2.5">{{ $o->onaylandi_at?->format('d.m.Y') ?: $o->created_at?->format('d.m.Y') }}</td>
                    <td class="px-4 py-2.5">{{ $o->doktor?->ad_soyad }}<br><span class="text-slate-400">{{ $o->doktor?->e_posta }}</span></td>
                    <td class="px-4 py-2.5">{{ $o->paket?->ad }} · {{ $o->odeme_periyodu }}</td>
                    <td class="px-4 py-2.5 font-bold">₺{{ number_format((float)$o->tutar, 2, ',', '.') }}</td>
                    <td class="px-4 py-2.5">{{ $o->durum }} / {{ $o->provider ?: $o->odeme_yontemi }}</td>
                    <td class="px-4 py-2.5">{{ $o->fatura_durumu ?? 'bekliyor' }}</td>
                    <td class="px-4 py-2.5">
                        @if(($o->fatura_durumu ?? 'bekliyor') !== 'kesildi')
                            <form method="POST" action="{{ route('yonetim.faturalar.guncelle', $o->id) }}">
                                @csrf
                                <input type="hidden" name="fatura_durumu" value="kesildi">
                                <button class="text-[11px] font-bold text-emerald-700 underline">Kesildi işaretle</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('yonetim.faturalar.guncelle', $o->id) }}">
                                @csrf
                                <input type="hidden" name="fatura_durumu" value="bekliyor">
                                <button class="text-[11px] font-bold text-slate-500 underline">Geri al</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">Kayıt yok</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<p class="mt-3 text-[10px] text-slate-400">Fiyatlara KDV dahildir. İlk dönem manuel fatura süreci için bayrak kullanılır.</p>
@endsection
