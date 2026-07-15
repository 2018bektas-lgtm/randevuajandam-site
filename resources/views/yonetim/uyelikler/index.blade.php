@extends('yonetim.layout')

@section('baslik', 'Üyelikler - Yönetim')
@section('sayfa_baslik', 'Üyelik & Abonelik')

@section('icerik')
    <div class="mb-6">
        <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
            <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
            Üyelik / abonelik özeti
        </h2>
        <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Hekim paket ve üyelik bitiş takibi. Düzenleme için doktor kartına gidin.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="p-4 rounded-2xl bg-white border border-[#E5E7EB] text-center">
            <div class="text-[10px] font-bold uppercase text-slate-400 font-display">Aktif üyelik</div>
            <div class="text-2xl font-bold text-slate-900 font-display mt-1">{{ $ozet['aktif_uyelik'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-[#E5E7EB] text-center">
            <div class="text-[10px] font-bold uppercase text-slate-400 font-display">Süresi dolmuş</div>
            <div class="text-2xl font-bold text-red-700 font-display mt-1">{{ $ozet['suresi_dolmus'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-[#E5E7EB] text-center">
            <div class="text-[10px] font-bold uppercase text-slate-400 font-display">14 gün içinde</div>
            <div class="text-2xl font-bold text-amber-700 font-display mt-1">{{ $ozet['yakinda'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-[#E5E7EB] text-center">
            <div class="text-[10px] font-bold uppercase text-slate-400 font-display">Vitrin gizli</div>
            <div class="text-2xl font-bold text-slate-700 font-display mt-1">{{ $ozet['gizli_vitrin'] }}</div>
        </div>
    </div>

    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <form method="GET" class="p-5 border-b border-[#E5E7EB] flex flex-wrap gap-2 items-center">
            <input type="text" name="arama" value="{{ request('arama') }}" placeholder="Hekim ara..."
                   class="px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs max-w-xs w-full focus:border-[#C96A2B] focus:outline-none">
            @foreach([
                '' => 'Tümü',
                'aktif' => 'Aktif üyelik',
                'yakinda' => 'Yakında biten',
                'biten' => 'Süresi dolmuş',
                'gizli' => 'Vitrin gizli',
            ] as $k => $label)
                <a href="{{ route('yonetim.uyelikler', array_filter(['filtre' => $k ?: null, 'arama' => request('arama')])) }}"
                   class="px-3 py-2 rounded-xl text-[11px] font-bold font-display border transition-colors
                          {{ request('filtre', '') === $k ? 'bg-[#C96A2B] text-white border-[#C96A2B]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#C96A2B]/40' }}">
                    {{ $label }}
                </a>
            @endforeach
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold">Ara</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                        <th class="px-5 py-3">Hekim</th>
                        <th class="px-5 py-3">Paket</th>
                        <th class="px-5 py-3">Periyot</th>
                        <th class="px-5 py-3">Bitiş</th>
                        <th class="px-5 py-3">iyzico</th>
                        <th class="px-5 py-3">Vitrin</th>
                        <th class="px-5 py-3">Durum</th>
                        <th class="px-5 py-3 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($doktorlar as $d)
                        @php
                            $bitis = $d->uyelik_bitis;
                            $dolmus = $bitis && $bitis->isPast();
                            $yakinda = $bitis && ! $dolmus && $bitis->lte(now()->addDays(14));
                        @endphp
                        <tr class="hover:bg-slate-50/40">
                            <td class="px-5 py-3">
                                <div class="font-bold text-slate-900 font-display">{{ $d->unvan }} {{ $d->ad_soyad }}</div>
                                <div class="text-[10px] text-slate-500">{{ $d->e_posta }}</div>
                            </td>
                            <td class="px-5 py-3">{{ $d->paket?->ad ?? '—' }}</td>
                            <td class="px-5 py-3 capitalize">{{ $d->odeme_periyodu ?? '—' }}</td>
                            <td class="px-5 py-3 whitespace-nowrap font-semibold
                                @if($dolmus) text-red-600
                                @elseif($yakinda) text-amber-600
                                @else text-slate-800 @endif">
                                {{ $bitis ? $bitis->format('d.m.Y') : '—' }}
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $d->iyzico_subscription_status ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @if($d->platformda_gorunur ?? true)
                                    <span class="text-emerald-700 font-bold text-[10px]">Açık</span>
                                @else
                                    <span class="text-amber-700 font-bold text-[10px]">Gizli</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if($d->aktif_mi)
                                    <span class="text-emerald-700 font-bold text-[10px] uppercase">Aktif</span>
                                @else
                                    <span class="text-slate-400 font-bold text-[10px] uppercase">Pasif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('yonetim.doktorlar.duzenle', $d->id) }}" class="text-[#C96A2B] font-bold hover:underline">Düzenle</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-500">Kayıt yok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($doktorlar->hasPages())
            <div class="p-4 border-t border-[#E5E7EB]">{{ $doktorlar->links() }}</div>
        @endif
    </div>
@endsection
