@extends('hekim.layout')

@section('baslik', 'Tedavi Geçmişi - Hekim Paneli')
@section('sayfa_baslik', 'Tedavi / Seans Geçmişi')

@section('icerik')
    <div class="mb-4">
        <a href="{{ route('hekim.randevu.hastalar') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#6B7280] hover:text-[#C96A2B] uppercase tracking-wider font-display">
            ← Hasta listesine dön
        </a>
    </div>

    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="p-6 border-b border-[#E5E7EB]">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">
                {{ $hasta->ad }} {{ $hasta->soyad }}
            </h3>
            <p class="text-xs text-[#6B7280] mt-1">
                {{ $hasta->telefon }} · {{ $hasta->e_posta }}
            </p>
        </div>

        @if($randevular->isEmpty())
            <div class="p-12 text-center text-xs text-[#6B7280]">Bu hastaya ait seans kaydı bulunamadı.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                            <th class="px-6 py-4">Tarih</th>
                            <th class="px-6 py-4">Saat</th>
                            <th class="px-6 py-4">Hizmet / Tedavi</th>
                            <th class="px-6 py-4">Durum</th>
                            <th class="px-6 py-4">Not</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] text-xs text-[#4B5563]">
                        @foreach($randevular as $r)
                            <tr class="hover:bg-[#FAFAFA]">
                                <td class="px-6 py-3 font-medium text-[#111827]">
                                    {{ \Carbon\Carbon::parse($r->tarih)->format('d.m.Y') }}
                                </td>
                                <td class="px-6 py-3">{{ substr((string) $r->saat, 0, 5) }}</td>
                                <td class="px-6 py-3">{{ $r->hizmet?->ad ?? '—' }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-50 border border-slate-200">
                                        {{ $r->durum }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 max-w-xs truncate" title="{{ $r->not }}">
                                    {{ $r->not ?: '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($randevular->hasPages())
                <div class="p-6 border-t border-[#E5E7EB]">{{ $randevular->links() }}</div>
            @endif
        @endif
    </div>

@endsection
