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
                                <td class="px-6 py-3 max-w-xs truncate" title="{{ $r->hekim_notu ?: $r->not }}">
                                    {{ $r->hekim_notu ?: $r->not ?: '—' }}
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Dosyalar --}}
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#E5E7EB] flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Hasta dosyaları</h3>
            </div>
            @if(!empty($canNotDosya))
                <form method="POST" action="{{ route('hekim.randevu.hastalar.dosya.store', $hasta->id) }}" enctype="multipart/form-data" class="p-4 border-b border-[#E5E7EB] space-y-2">
                    @csrf
                    <input type="text" name="baslik" placeholder="Başlık (opsiyonel)" class="w-full text-xs p-2.5 rounded-xl border border-[#E5E7EB]">
                    <input type="file" name="dosya" required accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx" class="w-full text-xs">
                    <textarea name="not" rows="2" placeholder="Not (opsiyonel)" class="w-full text-xs p-2.5 rounded-xl border border-[#E5E7EB]"></textarea>
                    <button type="submit" class="px-3 py-2 rounded-lg bg-[#C96A2B] text-white text-[11px] font-bold">Yükle</button>
                </form>
                @if($dosyalar->isEmpty())
                    <p class="p-4 text-xs text-[#6B7280]">Henüz dosya yok.</p>
                @else
                    <ul class="divide-y divide-[#E5E7EB] text-xs">
                        @foreach($dosyalar as $d)
                            <li class="px-4 py-3 flex items-start justify-between gap-2">
                                <div>
                                    <a href="{{ asset($d->dosya_yolu) }}" target="_blank" class="font-bold text-[#C96A2B] hover:underline">
                                        {{ $d->baslik ?: $d->orijinal_ad }}
                                    </a>
                                    <div class="text-[10px] text-[#6B7280]">{{ $d->created_at?->format('d.m.Y H:i') }}</div>
                                    @if($d->not)<div class="text-[11px] text-[#4B5563] mt-0.5">{{ $d->not }}</div>@endif
                                </div>
                                <form method="POST" action="{{ route('hekim.randevu.hastalar.dosya.destroy', $d->id) }}" onsubmit="return confirm('Silinsin mi?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 font-bold text-[10px]">Sil</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @else
                <p class="p-4 text-xs text-amber-800 bg-amber-50">
                    Dosya yükleme paketinizde yok.
                    <a href="{{ route('frontend.hekim.paket_sec', ['degistir' => 1]) }}" class="font-bold underline">Yükseltin</a>.
                </p>
            @endif
        </div>

        {{-- Onam --}}
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#E5E7EB]">
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Onam kayıtları</h3>
            </div>
            @if(!empty($canOnam))
                <form method="POST" action="{{ route('hekim.onam.imza') }}" class="p-4 border-b border-[#E5E7EB] space-y-2">
                    @csrf
                    <input type="hidden" name="hasta_id" value="{{ $hasta->id }}">
                    <select name="onam_form_id" required class="w-full text-xs p-2.5 rounded-xl border border-[#E5E7EB]">
                        <option value="">Form seçin</option>
                        @foreach($onamFormlar as $f)
                            <option value="{{ $f->id }}">{{ $f->baslik }}</option>
                        @endforeach
                    </select>
                    <textarea name="not" rows="2" placeholder="Yüz yüze imza notu (opsiyonel)" class="w-full text-xs p-2.5 rounded-xl border border-[#E5E7EB]"></textarea>
                    <button type="submit" class="px-3 py-2 rounded-lg bg-[#1F2937] text-white text-[11px] font-bold">Onam kaydı oluştur</button>
                    @if($onamFormlar->isEmpty())
                        <p class="text-[10px] text-[#6B7280]">Önce <a href="{{ route('hekim.onam.index') }}" class="text-[#C96A2B] font-bold underline">onam formu</a> oluşturun.</p>
                    @endif
                </form>
                @if($onamImzalar->isEmpty())
                    <p class="p-4 text-xs text-[#6B7280]">Bu hastaya ait onam kaydı yok.</p>
                @else
                    <ul class="divide-y divide-[#E5E7EB] text-xs">
                        @foreach($onamImzalar as $imza)
                            <li class="px-4 py-3">
                                <div class="font-bold text-[#111827]">{{ $imza->form?->baslik }}</div>
                                <div class="text-[10px] text-[#6B7280]">{{ $imza->imzalandi_at?->format('d.m.Y H:i') }}</div>
                                @if($imza->not)<div class="text-[11px] mt-0.5">{{ $imza->not }}</div>@endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            @else
                <p class="p-4 text-xs text-amber-800 bg-amber-50">
                    Onam formu paketinizde yok.
                    <a href="{{ route('frontend.hekim.paket_sec', ['degistir' => 1]) }}" class="font-bold underline">Yükseltin</a>.
                </p>
            @endif
        </div>
    </div>
@endsection
