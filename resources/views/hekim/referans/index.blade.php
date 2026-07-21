@extends('hekim.layout')

@section('baslik', 'Referans programı')
@section('sayfa_baslik', 'Referans programı')

@section('icerik')
<div class="max-w-3xl space-y-6">
    <div class="rounded-2xl border border-[#E5E7EB] bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold font-display text-[#111827]">Arkadaşını getir, süre kazan</h2>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
            Davet ettiğin hekim <strong>ilk ücretli paket ödemesinde %{{ $ozet['indirim'] }} indirim</strong> alır.
            Ödeme onaylanınca sen, onun abonelik süresinin <strong>%{{ $ozet['komisyon'] }}’i kadar ücretsiz gün</strong> kazanırsın
            (aylık ≈ {{ max(1, (int) round(30 * $ozet['komisyon'] / 100)) }} gün, yıllık ≈ {{ max(1, (int) round(365 * $ozet['komisyon'] / 100)) }} gün).
            Nakit ödeme yok — yalnızca üyelik süresi.
        </p>

        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-orange-100 bg-[#FFF7ED]/60 p-4">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Referans kodun</div>
                <div class="mt-1 font-mono text-xl font-extrabold text-[#C96A2B] select-all" id="refKod">{{ $ozet['kod'] }}</div>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Bu ay kalan kota</div>
                <div class="mt-1 text-xl font-extrabold text-slate-800">{{ $ozet['kalan'] }} / {{ $ozet['limit'] }}</div>
            </div>
        </div>

        <div class="mt-4">
            <label class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Davet linki</label>
            <div class="mt-1 flex flex-col gap-2 sm:flex-row">
                <input type="text" readonly id="refLink" value="{{ $ozet['link'] }}"
                       class="w-full rounded-xl border border-[#E5E7EB] bg-white px-3 py-2.5 text-xs font-mono text-slate-700">
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('refLink').value); this.textContent='Kopyalandı'; setTimeout(()=>this.textContent='Kopyala',1500)"
                        class="shrink-0 rounded-xl bg-[#C96A2B] px-5 py-2.5 text-xs font-bold text-white hover:bg-[#B55A20]">
                    Kopyala
                </button>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-4 text-xs text-slate-600">
            <span>Bekleyen: <strong>{{ $ozet['bekleyen'] }}</strong></span>
            <span>Ödüllü: <strong>{{ $ozet['odullu'] }}</strong></span>
            <span>Bu ay ödül: <strong>{{ $ozet['bu_ay'] }}</strong></span>
        </div>
    </div>

    <div class="rounded-2xl border border-[#E5E7EB] bg-white shadow-sm overflow-hidden">
        <div class="border-b border-[#E5E7EB] px-5 py-3 text-xs font-bold uppercase tracking-wider text-slate-500">Davetlerin</div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[10px] uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Hekim</th>
                        <th class="px-5 py-3">Durum</th>
                        <th class="px-5 py-3">Ödül</th>
                        <th class="px-5 py-3">Tarih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($davetler as $d)
                        <tr>
                            <td class="px-5 py-3 font-semibold text-slate-800">
                                {{ $d->davetEdilen?->ad_soyad ?? '—' }}
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $label = match($d->durum) {
                                        'bekliyor' => 'Ödeme bekliyor',
                                        'odullendirildi' => 'Ödüllendirildi',
                                        'iptal' => 'İptal',
                                        'reddedildi' => 'Red',
                                        default => $d->durum,
                                    };
                                @endphp
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 font-bold text-slate-600">{{ $label }}</span>
                                @if($d->red_nedeni)
                                    <div class="mt-0.5 text-[10px] text-red-500">{{ $d->red_nedeni }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-600">
                                @if($d->durum === 'odullendirildi')
                                    +{{ $d->odul_gun_davet_eden }} gün
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500">
                                {{ ($d->odullendirildi_at ?? $d->created_at)?->format('d.m.Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-slate-400">Henüz davet yok. Linkini paylaş.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
