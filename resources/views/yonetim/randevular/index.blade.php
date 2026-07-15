@extends('yonetim.layout')

@section('baslik', 'Randevular - Yönetim')
@section('sayfa_baslik', 'Platform Randevuları')

@section('icerik')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                Randevu yönetimi
            </h2>
            <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Tüm hekimlerin randevuları (salt okunur operasyon görünümü).</p>
        </div>
        <div class="flex flex-wrap gap-2 text-[11px] font-bold font-display">
            <span class="px-3 py-1.5 rounded-full bg-amber-50 text-amber-800 border border-amber-100">Beklemede: {{ $ozet['beklemede'] }}</span>
            <span class="px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-100">Onaylı: {{ $ozet['onaylandi'] }}</span>
            <span class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">Bugün: {{ $ozet['bugun'] }}</span>
        </div>
    </div>

    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <form method="GET" class="p-5 border-b border-[#E5E7EB] flex flex-col lg:flex-row flex-wrap gap-3">
            <input type="text" name="arama" value="{{ request('arama') }}" placeholder="Hasta, telefon, hekim..."
                   class="px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs max-w-xs w-full focus:border-[#C96A2B] focus:outline-none">
            <input type="date" name="tarih" value="{{ request('tarih') }}"
                   class="px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
            <select name="durum" class="px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs font-semibold focus:border-[#C96A2B] focus:outline-none">
                <option value="">Tüm durumlar</option>
                @foreach(['beklemede','onaylandi','tamamlandi','iptal'] as $d)
                    <option value="{{ $d }}" @selected(request('durum') === $d)>{{ $d }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#C96A2B] text-white text-xs font-bold font-display">Filtrele</button>
            <a href="{{ route('yonetim.randevular') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Sıfırla</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                        <th class="px-5 py-3">Hasta</th>
                        <th class="px-5 py-3">Hekim</th>
                        <th class="px-5 py-3">Hizmet</th>
                        <th class="px-5 py-3">Tarih / Saat</th>
                        <th class="px-5 py-3">Durum</th>
                        <th class="px-5 py-3">Oluşturma</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($randevular as $r)
                        <tr class="hover:bg-slate-50/40">
                            <td class="px-5 py-3">
                                <div class="font-bold text-slate-900">{{ $r->ad }} {{ $r->soyad }}</div>
                                <div class="text-[10px] text-slate-500">{{ $r->telefon }}</div>
                            </td>
                            <td class="px-5 py-3 text-slate-700">{{ $r->doktor?->ad_soyad ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $r->hizmet?->ad ?? '—' }}</td>
                            <td class="px-5 py-3 whitespace-nowrap font-semibold text-slate-800">
                                {{ $r->tarih?->format('d.m.Y') }} · {{ \Illuminate\Support\Str::substr($r->saat, 0, 5) }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold
                                    @if($r->durum === 'beklemede') bg-amber-50 text-amber-800
                                    @elseif($r->durum === 'onaylandi') bg-emerald-50 text-emerald-800
                                    @elseif($r->durum === 'iptal') bg-red-50 text-red-700
                                    @else bg-slate-100 text-slate-600 @endif">{{ $r->durum }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-500 whitespace-nowrap">{{ $r->created_at?->format('d.m.Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">Kayıt bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($randevular->hasPages())
            <div class="p-4 border-t border-[#E5E7EB]">{{ $randevular->links() }}</div>
        @endif
    </div>
@endsection
