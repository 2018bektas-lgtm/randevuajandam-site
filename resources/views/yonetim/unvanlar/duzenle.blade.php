@extends('yonetim.layout')

@section('baslik', 'Unvan Düzenle - Randevu Ajandam')
@section('sayfa_baslik', 'Unvan Düzenle')

@section('icerik')
    <!-- Top Action Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
        <div>
            <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                Unvan Düzenle
            </h2>
            <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Seçilen unvanın adını güncelleyin.</p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('yonetim.unvanlar.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] transition-all shadow-sm">
                ← Listeye Dön
            </a>
        </div>
    </div>

    <!-- Form Container -->
    <div class="max-w-xl bg-white border border-[#E5E7EB] rounded-2xl p-6 sm:p-8 shadow-sm">
        <form action="{{ route('yonetim.unvanlar.update', $unvan->id) }}" method="POST" class="space-y-6">
            @csrf

            <!-- Unvan Adı -->
            <div>
                <label for="ad" class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Unvan Adı</label>
                <input type="text" name="ad" id="ad" value="{{ old('ad', $unvan->ad) }}" placeholder="Örn: Prof. Dr." required
                       class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E5E7EB]">
                <a href="{{ route('yonetim.unvanlar.index') }}" 
                   class="px-5 py-3 rounded-xl border border-[#E5E7EB] hover:bg-slate-50 text-[#6B7280] font-bold text-xs uppercase tracking-wider transition-all select-none">
                    İptal
                </a>
                <button type="submit" 
                        class="px-6 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-150 font-display cursor-pointer select-none shadow-sm hover:shadow-md">
                    Unvanı Güncelle
                </button>
            </div>
        </form>
    </div>
@endsection
