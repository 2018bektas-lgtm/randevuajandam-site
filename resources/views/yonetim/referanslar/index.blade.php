@extends('yonetim.layout')

@section('baslik', 'Referanslar')
@section('sayfa_baslik', 'Referans programı')

@section('icerik')
<div class="mb-6 rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-sm">
    <h2 class="text-sm font-bold text-[#111827]">Ayarlar (env / config)</h2>
    <p class="mt-2 text-xs text-slate-600">
        Aktif: <strong>{{ $ayar['aktif'] ? 'evet' : 'hayır' }}</strong> ·
        Gelen indirim: <strong>%{{ $ayar['indirim'] }}</strong> ·
        Davet eden süre payı: <strong>%{{ $ayar['komisyon'] }}</strong> ·
        Aylık limit: <strong>{{ $ayar['limit'] }}</strong>
    </p>
    <p class="mt-1 text-[11px] text-slate-400">Değiştirmek için .env: REFERANS_YUZDE_DAVET_EDILEN, REFERANS_YUZDE_DAVET_EDEN, REFERANS_AYLIK_LIMIT</p>
</div>

<form method="get" class="mb-4 flex gap-2">
    <select name="durum" class="rounded-xl border border-[#E5E7EB] px-3 py-2 text-xs" onchange="this.form.submit()">
        <option value="">Tüm durumlar</option>
        @foreach(['bekliyor','odullendirildi','reddedildi','iptal'] as $d)
            <option value="{{ $d }}" @selected(request('durum')===$d)>{{ $d }}</option>
        @endforeach
    </select>
</form>

<div class="overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-sm">
    <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-[10px] uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Davet eden</th>
                <th class="px-4 py-3">Davet edilen</th>
                <th class="px-4 py-3">Durum</th>
                <th class="px-4 py-3">Ödül</th>
                <th class="px-4 py-3">Tutar</th>
                <th class="px-4 py-3 text-right">İşlem</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($davetler as $d)
                <tr>
                    <td class="px-4 py-3">
                        <div class="font-semibold">{{ $d->davetEden?->ad_soyad }}</div>
                        <div class="text-[10px] text-slate-400">{{ $d->kod }}</div>
                    </td>
                    <td class="px-4 py-3">{{ $d->davetEdilen?->ad_soyad }}</td>
                    <td class="px-4 py-3">
                        {{ $d->durum }}
                        @if($d->red_nedeni)<div class="text-[10px] text-red-500">{{ $d->red_nedeni }}</div>@endif
                    </td>
                    <td class="px-4 py-3">{{ $d->odul_gun_davet_eden ? '+'.$d->odul_gun_davet_eden.' gün' : '—' }}</td>
                    <td class="px-4 py-3">{{ $d->odeme_tutari_net ? number_format($d->odeme_tutari_net, 0, ',', '.').' ₺' : '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        @if(in_array($d->durum, ['bekliyor','reddedildi'], true))
                            <form method="post" action="{{ route('yonetim.referanslar.iptal', $d->id) }}" onsubmit="return confirm('İptal?')">
                                @csrf
                                <button class="text-[11px] font-bold text-red-600">İptal</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Kayıt yok</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $davetler->links() }}</div>
@endsection
