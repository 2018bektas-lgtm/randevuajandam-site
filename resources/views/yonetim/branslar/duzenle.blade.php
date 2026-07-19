@extends('yonetim.layout')

@section('baslik', 'Branş Düzenle - Randevu Ajandam')
@section('sayfa_baslik', 'Branş Düzenle')

@section('icerik')
    <!-- Top Action Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
        <div>
            <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                Branş Düzenle
            </h2>
            <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Seçilen branşın adını ve açıklamasını güncelleyin.</p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('yonetim.branslar.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] transition-all shadow-sm">
                ← Listeye Dön
            </a>
        </div>
    </div>

    <!-- Form Container -->
    <div class="max-w-2xl bg-white border border-[#E5E7EB] rounded-2xl p-6 sm:p-8 shadow-sm">
        <form action="{{ route('yonetim.branslar.update', $brans->id) }}" method="POST" class="space-y-6">
            @csrf

            <!-- Branş Adı -->
            <div>
                <label for="ad" class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Branş Adı</label>
                <input type="text" name="ad" id="ad" value="{{ old('ad', $brans->ad) }}" placeholder="Örn: Göz Hastalıkları" required
                       class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all @error('ad') border-red-400 @enderror">
                @error('ad')
                    <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Açıklama -->
            <div>
                <label for="aciklama" class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Açıklama <span class="font-normal normal-case text-[#9CA3AF]">(opsiyonel)</span></label>
                <textarea name="aciklama" id="aciklama" rows="5" maxlength="5000" placeholder="Bu branşın neyi kapsadığını kısaca yazın. Örn: Kalp ve damar hastalıklarının tanı ve tedavisi."
                          class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all resize-y min-h-[120px] @error('aciklama') border-red-400 @enderror">{{ old('aciklama', $brans->aciklama) }}</textarea>
                <p class="mt-1.5 text-[11px] text-[#9CA3AF]">Hasta ve hekim arayüzlerinde branşı tanıtmak için kullanılabilir. En fazla 5000 karakter.</p>
                @error('aciklama')
                    <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E5E7EB]">
                <a href="{{ route('yonetim.branslar.index') }}" 
                   class="px-5 py-3 rounded-xl border border-[#E5E7EB] hover:bg-slate-50 text-[#6B7280] font-bold text-xs uppercase tracking-wider transition-all select-none">
                    İptal
                </a>
                <button type="submit" 
                        class="px-6 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-150 font-display cursor-pointer select-none shadow-sm hover:shadow-md">
                    Branşı Güncelle
                </button>
            </div>
        </form>
    </div>
@endsection
