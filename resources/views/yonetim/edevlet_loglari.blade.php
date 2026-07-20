@extends('yonetim.layout')

@section('baslik', 'e-Devlet logları - Randevu Ajandam')
@section('sayfa_baslik', 'e-Devlet doğrulama logları')

@section('icerik')
<div class="mb-6 flex flex-wrap gap-3">
    <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-xs">
        <span class="font-bold text-emerald-800">24s başarı:</span> {{ $ozet['basarili'] }}
    </div>
    <div class="rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-xs">
        <span class="font-bold text-red-800">24s fail:</span> {{ $ozet['basarisiz'] }}
    </div>
</div>

<div class="bg-white border border-[#E5E7EB] rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 font-bold text-slate-500 uppercase text-[10px]">Zaman</th>
                    <th class="px-4 py-3 font-bold text-slate-500 uppercase text-[10px]">Barkod</th>
                    <th class="px-4 py-3 font-bold text-slate-500 uppercase text-[10px]">TC</th>
                    <th class="px-4 py-3 font-bold text-slate-500 uppercase text-[10px]">Durum</th>
                    <th class="px-4 py-3 font-bold text-slate-500 uppercase text-[10px]">ms</th>
                    <th class="px-4 py-3 font-bold text-slate-500 uppercase text-[10px]">Hata / meta</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 py-2.5 whitespace-nowrap">{{ $log->created_at?->format('d.m.Y H:i:s') }}</td>
                        <td class="px-4 py-2.5 font-mono">{{ $log->barkod }}</td>
                        <td class="px-4 py-2.5 font-mono">{{ $log->tc_maskeli }}</td>
                        <td class="px-4 py-2.5">
                            @if($log->durum === 'basarili')
                                <span class="text-emerald-700 font-bold">başarılı</span>
                            @else
                                <span class="text-red-600 font-bold">başarısız</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">{{ $log->sure_ms }}</td>
                        <td class="px-4 py-2.5 max-w-md truncate" title="{{ $log->hata }} {{ json_encode($log->meta) }}">
                            {{ $log->hata ?: json_encode($log->meta, JSON_UNESCAPED_UNICODE) }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Log yok</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
