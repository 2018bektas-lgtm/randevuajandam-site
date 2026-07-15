@extends('hekim.layout')

@section('baslik', 'Bekleme Listesi - Hekim Paneli')
@section('sayfa_baslik', 'Bekleme Listesi')

@section('icerik')
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-[0_4px_24px_rgba(31,41,55,0.04)] overflow-hidden">
        <div class="p-6 border-b border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Bekleme Listesi</h3>
                <p class="text-[11px] text-[#6B7280] mt-1">Uygun randevu bekleyen hastalar. İptal sonrası uygun adaylara otomatik e-posta gider.</p>
            </div>
            <span class="text-xs text-[#C96A2B] font-bold font-display bg-[#FFF7ED] px-3 py-1 rounded-full border border-[#E7B58A]/30 self-start">
                {{ $bekleyenSayisi }} Bekleyen
            </span>
        </div>

        @if(session('basarili'))
            <div class="mx-6 mt-4 p-3 bg-emerald-50 border border-emerald-100 rounded-xl text-xs text-emerald-800 font-medium">
                {{ session('basarili') }}
            </div>
        @endif

        <div class="px-6 pt-4 flex flex-wrap gap-2">
            @foreach(['aktif' => 'Aktif', 'beklemede' => 'Beklemede', 'bildirildi' => 'Bildirildi', 'randevu_alindi' => 'Randevu aldı', 'iptal' => 'İptal', 'hepsi' => 'Tümü'] as $k => $label)
                <a href="{{ route('hekim.randevu.bekleme-listesi', ['durum' => $k]) }}"
                   class="px-3 py-1.5 rounded-lg text-[11px] font-bold font-display border transition-colors
                          {{ $durum === $k ? 'bg-[#C96A2B] text-white border-[#C96A2B]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#C96A2B]/40' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if($kayitlar->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-50 text-slate-400 border border-slate-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-[#111827] font-display">Bekleme listesinde kayıt yok</h4>
                <p class="text-xs text-[#6B7280] mt-1">Hastalar profilinizden bekleme listesine kaydolabilir.</p>
            </div>
        @else
            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-y border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                            <th class="px-6 py-4">Hasta</th>
                            <th class="px-6 py-4">Tercih</th>
                            <th class="px-6 py-4">Hizmet</th>
                            <th class="px-6 py-4">Durum</th>
                            <th class="px-6 py-4">Not</th>
                            <th class="px-6 py-4 text-right">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] text-xs text-[#4B5563]">
                        @foreach($kayitlar as $k)
                            <tr class="hover:bg-[#FAFAFA]/75">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[#111827] font-display">{{ $k->ad_soyad }}</div>
                                    <div class="text-[10px] text-[#6B7280] mt-0.5">{{ $k->telefon }}</div>
                                    @if($k->e_posta)
                                        <div class="text-[10px] text-[#6B7280]">{{ $k->e_posta }}</div>
                                    @endif
                                    <div class="text-[9px] text-slate-400 mt-1">{{ $k->created_at?->format('d.m.Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($k->tercih_tarih)
                                        <div class="font-bold text-[#111827] font-display">{{ $k->tercih_tarih->translatedFormat('d F Y') }}</div>
                                        @if($k->tercih_saat)
                                            <div class="text-[#C96A2B] font-bold font-display">{{ substr($k->tercih_saat, 0, 5) }}</div>
                                        @endif
                                    @else
                                        <span class="text-slate-400">Esnek</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $k->hizmet?->ad ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $badge = match($k->durum) {
                                            'beklemede' => 'bg-amber-50 text-amber-800 border-amber-200',
                                            'bildirildi' => 'bg-sky-50 text-sky-800 border-sky-200',
                                            'randevu_alindi' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                            'iptal' => 'bg-red-50 text-red-700 border-red-200',
                                            default => 'bg-slate-50 text-slate-600 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badge }}">{{ $k->durum }}</span>
                                    @if($k->bildirildi_at)
                                        <div class="text-[9px] text-slate-400 mt-1">Bildirim: {{ $k->bildirildi_at->format('d.m H:i') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 max-w-[160px]">
                                    @if($k->not)
                                        <p class="text-[#6B7280] italic line-clamp-2" title="{{ $k->not }}">{{ $k->not }}</p>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        @if(in_array($k->durum, ['beklemede', 'bildirildi'], true))
                                            <form method="POST" action="{{ route('hekim.randevu.bekleme-listesi.bildir', $k->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-[10px] font-display">
                                                    Bilgilendir
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('hekim.randevu.bekleme-listesi.durum', $k->id) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="durum" value="randevu_alindi">
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] font-display">
                                                    Randevu aldı
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('hekim.randevu.bekleme-listesi.durum', $k->id) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="durum" value="iptal">
                                                <button type="submit" class="px-2.5 py-1 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 font-bold text-[10px] font-display">
                                                    İptal
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('hekim.randevu.bekleme-listesi.sil', $k->id) }}" class="inline" onsubmit="return confirm('Kaydı silmek istediğinize emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 font-bold text-[10px] font-display">
                                                Sil
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-[#E5E7EB]">
                {{ $kayitlar->links() }}
            </div>
        @endif
    </div>
@endsection
