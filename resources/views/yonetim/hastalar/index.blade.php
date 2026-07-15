@extends('yonetim.layout')

@section('baslik', 'Hastalar - Yönetim')
@section('sayfa_baslik', 'Platform Hastaları')

@section('icerik')
    <div class="mb-6">
        <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
            <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
            Hasta kayıtları
        </h2>
        <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Tüm platform hasta hesapları (yönetici görünümü).</p>
    </div>

    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <form method="GET" class="p-5 border-b border-[#E5E7EB] flex flex-wrap gap-3">
            <input type="text" name="arama" value="{{ request('arama') }}" placeholder="Ad, e-posta, telefon..."
                   class="px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs max-w-xs w-full focus:border-[#C96A2B] focus:outline-none">
            <select name="aktif" class="px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs font-semibold focus:border-[#C96A2B] focus:outline-none">
                <option value="">Tüm durumlar</option>
                <option value="1" @selected(request('aktif') === '1')>Aktif</option>
                <option value="0" @selected(request('aktif') === '0')>Pasif</option>
            </select>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#C96A2B] text-white text-xs font-bold font-display">Ara</button>
            <a href="{{ route('yonetim.hastalar') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Sıfırla</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                        <th class="px-5 py-3">Hasta</th>
                        <th class="px-5 py-3">İletişim</th>
                        <th class="px-5 py-3">Randevu</th>
                        <th class="px-5 py-3">Durum</th>
                        <th class="px-5 py-3">Kayıt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($hastalar as $h)
                        <tr class="hover:bg-slate-50/40">
                            <td class="px-5 py-3 font-bold text-slate-900 font-display">{{ $h->ad }} {{ $h->soyad }}</td>
                            <td class="px-5 py-3 text-slate-600">
                                <div>{{ $h->e_posta }}</div>
                                <div class="text-[10px] text-slate-400">{{ $h->telefon }}</div>
                            </td>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $h->randevular_count }}</td>
                            <td class="px-5 py-3">
                                @if($h->aktif_mi)
                                    <span class="text-emerald-700 font-bold text-[10px] uppercase">Aktif</span>
                                @else
                                    <span class="text-slate-400 font-bold text-[10px] uppercase">Pasif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $h->created_at?->format('d.m.Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-500">Hasta bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($hastalar->hasPages())
            <div class="p-4 border-t border-[#E5E7EB]">{{ $hastalar->links() }}</div>
        @endif
    </div>
@endsection
