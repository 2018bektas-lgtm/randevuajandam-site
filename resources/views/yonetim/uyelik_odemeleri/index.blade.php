@extends('yonetim.layout')

@section('baslik', 'Üyelik Ödemeleri - Randevu Ajandam')
@section('sayfa_baslik', 'Üyelik Ödemeleri')

@section('icerik')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500">Havale ile gönderilen üyelik başvurularını banka hareketinizle karşılaştırıp onaylayın.</p>
            <div class="flex gap-2 text-xs font-semibold">
                <a href="{{ route('yonetim.uyelik-odemeleri.index', ['durum' => 'beklemede']) }}" class="px-3 py-2 rounded-lg {{ request('durum', 'beklemede') === 'beklemede' ? 'bg-[#C96A2B] text-white' : 'bg-white border border-slate-200 text-slate-600' }}">Bekleyenler</a>
                <a href="{{ route('yonetim.uyelik-odemeleri.index', ['durum' => 'onaylandi']) }}" class="px-3 py-2 rounded-lg {{ request('durum') === 'onaylandi' ? 'bg-[#C96A2B] text-white' : 'bg-white border border-slate-200 text-slate-600' }}">Onaylananlar</a>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider">
                    <tr><th class="px-5 py-4">Hekim</th><th class="px-5 py-4">Paket</th><th class="px-5 py-4">Tutar</th><th class="px-5 py-4">Havale referansı</th><th class="px-5 py-4">Tarih</th><th class="px-5 py-4 text-right">İşlem</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($odemeler as $odeme)
                        <tr>
                            <td class="px-5 py-4"><p class="font-bold text-slate-800">{{ $odeme->doktor->ad_soyad }}</p><p class="text-slate-500">{{ $odeme->doktor->e_posta }}</p></td>
                            <td class="px-5 py-4"><p class="font-semibold">{{ $odeme->paket->ad }}</p><p class="text-slate-500">{{ ucfirst($odeme->odeme_periyodu) }}</p></td>
                            <td class="px-5 py-4 font-bold">₺{{ number_format((float) $odeme->tutar, 2, ',', '.') }}</td>
                            <td class="px-5 py-4 font-mono text-slate-600">{{ $odeme->havale_referans }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $odeme->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-5 py-4 text-right">
                                @if($odeme->durum === 'beklemede')
                                    <form action="{{ route('yonetim.uyelik-odemeleri.onayla', $odeme->id) }}" method="POST" onsubmit="return confirm('Banka hareketini doğruladınız mı? Üyelik aktifleştirilecek.');">@csrf<button class="rounded-lg bg-emerald-600 px-3 py-2 font-bold text-white hover:bg-emerald-700">Havaleyi Onayla</button></form>
                                @else
                                    <span class="rounded-full bg-emerald-50 px-3 py-1.5 font-bold text-emerald-700">Onaylandı</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Gösterilecek üyelik ödemesi bulunmuyor.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $odemeler->links() }}
    </div>
@endsection
