@extends('klinik.layout')
@section('baslik', 'Duyuru Düzenle')
@section('sayfa_baslik', 'Duyuruyu Düzenle')

@section('icerik')
<div class="space-y-6 max-w-2xl mx-auto">
    <!-- Back Link -->
    <a href="{{ route('hekim.klinik.duyurular.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#6B7280] hover:text-[#111827] transition-colors">
        ⬅️ Duyuru Yönetimine Dön
    </a>

    <!-- Edit Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-8">
        <form action="{{ route('hekim.klinik.duyurular.update', $duyuru->id) }}" method="POST" class="space-y-5">
            @csrf
            <div class="space-y-1">
                <label for="baslik" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Duyuru Başlığı</label>
                <input type="text" name="baslik" id="baslik" required value="{{ old('baslik', $duyuru->baslik) }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
            </div>

            <div class="space-y-1">
                <label for="onem_derecesi" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Önem Derecesi</label>
                <select name="onem_derecesi" id="onem_derecesi" required class="w-full">
                    <option value="genel" {{ old('onem_derecesi', $duyuru->onem_derecesi) === 'genel' ? 'selected' : '' }}>Genel (Mavi)</option>
                    <option value="onemli" {{ old('onem_derecesi', $duyuru->onem_derecesi) === 'onemli' ? 'selected' : '' }}>Önemli (Turuncu)</option>
                    <option value="acil" {{ old('onem_derecesi', $duyuru->onem_derecesi) === 'acil' ? 'selected' : '' }}>Acil (Kırmızı)</option>
                </select>
            </div>

            <div class="space-y-1">
                <label for="icerik" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Duyuru İçeriği</label>
                <textarea name="icerik" id="icerik" rows="6" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none resize-none">{{ old('icerik', $duyuru->icerik) }}</textarea>
            </div>

            <div class="pt-4 flex items-center gap-3 border-t border-slate-100">
                <a href="{{ route('hekim.klinik.duyurular.index') }}" class="flex-1 py-3 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#6B7280] text-center font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display">
                    İptal Et
                </a>
                <button type="submit" class="flex-1 py-3 rounded-xl bg-[#1E3A5F] hover:bg-[#152a47] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display">
                    Değişiklikleri Kaydet
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
