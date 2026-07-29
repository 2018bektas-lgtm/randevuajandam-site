@extends('yonetim.layout')

@section('baslik', 'Üyelik Ödemeleri - Randevu Ajandam')
@section('sayfa_baslik', 'Üyelik Ödemeleri')

@section('icerik')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500">Üyelik ödemeleri (havale, PayTR, iyzico). Havale için banka hareketini doğrulayıp onaylayın.</p>
            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                <a href="{{ route('yonetim.uyelik-odemeleri.index') }}" class="px-3 py-2 rounded-lg {{ ! request('durum') ? 'bg-[#C96A2B] text-white' : 'bg-white border border-slate-200 text-slate-600' }}">Tümü</a>
                <a href="{{ route('yonetim.uyelik-odemeleri.index', ['durum' => 'beklemede']) }}" class="px-3 py-2 rounded-lg {{ request('durum') === 'beklemede' ? 'bg-[#C96A2B] text-white' : 'bg-white border border-slate-200 text-slate-600' }}">Bekleyenler</a>
                <a href="{{ route('yonetim.uyelik-odemeleri.index', ['durum' => 'onaylandi']) }}" class="px-3 py-2 rounded-lg {{ request('durum') === 'onaylandi' ? 'bg-[#C96A2B] text-white' : 'bg-white border border-slate-200 text-slate-600' }}">Onaylananlar</a>
                <a href="{{ route('yonetim.faturalar') }}" class="px-3 py-2 rounded-lg bg-white border border-slate-200 text-slate-600 hover:border-[#C96A2B]">Fatura kuyruğu →</a>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-4">Hekim</th>
                        <th class="px-5 py-4">Paket</th>
                        <th class="px-5 py-4">Tutar</th>
                        <th class="px-5 py-4">Yöntem</th>
                        <th class="px-5 py-4">Referans</th>
                        <th class="px-5 py-4">Tarih</th>
                        <th class="px-5 py-4 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($odemeler as $odeme)
                        @php
                            $fb = is_array($odeme->fatura_bilgisi) ? $odeme->fatura_bilgisi : [];
                            $fbOzet = collect([
                                $fb['unvan'] ?? $fb['fatura_unvan'] ?? null,
                                $fb['tc_vkn'] ?? $fb['fatura_tc_vkn'] ?? null,
                            ])->filter()->implode(' · ');
                        @endphp
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-800">{{ $odeme->doktor?->ad_soyad }}</p>
                                <p class="text-slate-500">{{ $odeme->doktor?->e_posta }}</p>
                                @if($fbOzet)
                                    <p class="text-[10px] text-slate-400 mt-0.5">Fatura: {{ $fbOzet }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4"><p class="font-semibold">{{ $odeme->paket?->ad }}</p><p class="text-slate-500">{{ ucfirst((string) $odeme->odeme_periyodu) }}</p></td>
                            <td class="px-5 py-4 font-bold">₺{{ number_format((float) $odeme->tutar, 2, ',', '.') }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $odeme->provider ?: $odeme->odeme_yontemi }}<br><span class="text-[10px] text-slate-400">{{ $odeme->durum }}</span></td>
                            <td class="px-5 py-4 font-mono text-slate-600 text-[11px]">{{ $odeme->havale_referans ?: ($odeme->merchant_oid ?: '—') }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $odeme->created_at?->format('d.m.Y H:i') }}</td>
                            <td class="px-5 py-4 text-right space-y-1">
                                @if($odeme->durum === 'beklemede' && (in_array($odeme->odeme_yontemi, ['havale'], true) || in_array($odeme->provider, ['banka', 'havale'], true)))
                                    <form action="{{ route('yonetim.uyelik-odemeleri.onayla', $odeme->id) }}" method="POST" onsubmit="return confirm('Banka hareketini doğruladınız mı? Üyelik aktifleştirilecek.');">@csrf<button class="rounded-lg bg-emerald-600 px-3 py-2 font-bold text-white hover:bg-emerald-700">Havaleyi onayla</button></form>
                                @elseif($odeme->durum === 'onaylandi')
                                    <span class="rounded-full bg-emerald-50 px-3 py-1.5 font-bold text-emerald-700">Onaylandı</span>
                                    <a href="{{ route('yonetim.faturalar', ['fatura' => 'onayli_odeme']) }}" class="block text-[10px] font-bold text-[#C96A2B] mt-1">Fatura →</a>
                                @else
                                    <span class="rounded-full bg-slate-100 px-3 py-1.5 font-bold text-slate-600">{{ $odeme->durum }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-500">Gösterilecek üyelik ödemesi bulunmuyor.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $odemeler->links() }}
    </div>
@endsection
