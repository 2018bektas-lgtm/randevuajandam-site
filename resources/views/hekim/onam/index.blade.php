@extends('hekim.layout')

@section('baslik', 'Onam Formları - Hekim Paneli')
@section('sayfa_baslik', 'Onam Formları')

@section('icerik')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Yeni onam formu</h3>
            <form method="POST" action="{{ route('hekim.onam.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase mb-1">Başlık *</label>
                    <input type="text" name="baslik" required value="{{ old('baslik') }}"
                           class="w-full text-xs p-3 rounded-xl border border-[#E5E7EB] focus:border-[#C96A2B] outline-none"
                           placeholder="Örn: Tedavi bilgilendirme ve onam">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase mb-1">Metin *</label>
                    <textarea name="icerik" required rows="8"
                              class="w-full text-xs p-3 rounded-xl border border-[#E5E7EB] focus:border-[#C96A2B] outline-none"
                              placeholder="Hasta bilgilendirme metni...">{{ old('icerik') }}</textarea>
                </div>
                <label class="inline-flex items-center gap-2 text-xs text-[#4B5563]">
                    <input type="checkbox" name="aktif_mi" value="1" checked class="rounded border-slate-300 text-[#C96A2B]">
                    Aktif
                </label>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#C96A2B] text-white text-xs font-bold">Kaydet</button>
            </form>
        </div>

        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#E5E7EB]">
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Son imza kayıtları</h3>
            </div>
            @if($sonImzalar->isEmpty())
                <p class="p-6 text-xs text-[#6B7280]">Henüz imza kaydı yok. Hasta tedavi geçmişinden onam kaydı oluşturabilirsiniz.</p>
            @else
                <ul class="divide-y divide-[#E5E7EB] text-xs">
                    @foreach($sonImzalar as $imza)
                        <li class="px-4 py-3 flex justify-between gap-2">
                            <div>
                                <div class="font-bold text-[#111827]">{{ $imza->hasta_ad_soyad }}</div>
                                <div class="text-[#6B7280]">{{ $imza->form?->baslik }}</div>
                            </div>
                            <div class="text-[10px] text-[#6B7280] whitespace-nowrap">
                                {{ $imza->imzalandi_at?->format('d.m.Y H:i') }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="mt-6 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-[#E5E7EB]">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Form şablonlarım</h3>
        </div>
        @if($formlar->isEmpty())
            <p class="p-6 text-xs text-[#6B7280]">Henüz form yok. Soldan ekleyin.</p>
        @else
            <div class="divide-y divide-[#E5E7EB]">
                @foreach($formlar as $form)
                    <div class="p-4 space-y-3">
                        <form method="POST" action="{{ route('hekim.onam.update', $form->id) }}" class="space-y-2">
                            @csrf
                            <div class="flex flex-wrap gap-2 items-center justify-between">
                                <input type="text" name="baslik" value="{{ $form->baslik }}" required
                                       class="flex-1 min-w-[200px] text-xs p-2.5 rounded-xl border border-[#E5E7EB]">
                                <label class="inline-flex items-center gap-1.5 text-[11px]">
                                    <input type="checkbox" name="aktif_mi" value="1" @checked($form->aktif_mi) class="rounded text-[#C96A2B]">
                                    Aktif
                                </label>
                            </div>
                            <textarea name="icerik" rows="4" required class="w-full text-xs p-2.5 rounded-xl border border-[#E5E7EB]">{{ $form->icerik }}</textarea>
                            <div class="flex gap-2">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#1F2937] text-white text-[11px] font-bold">Güncelle</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('hekim.onam.destroy', $form->id) }}" onsubmit="return confirm('Form silinsin mi?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[11px] font-bold text-red-600">Sil</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
