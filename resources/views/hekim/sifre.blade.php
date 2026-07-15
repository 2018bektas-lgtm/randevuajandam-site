@extends('hekim.layout')

@section('baslik', 'Şifre Değiştir - Randevu Ajandam')
@section('sayfa_baslik', 'Şifre Değiştir')

@section('icerik')
    <div class="max-w-xl mx-auto">
        <form action="{{ route('hekim.sifre.post') }}" method="POST" class="space-y-8">
            @csrf

            <!-- Password Card -->
            <div class="p-8 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-6 pb-3 border-b border-slate-100">Şifre Bilgileri</h3>
                
                <div class="space-y-5">
                    <!-- Mevcut Şifre -->
                    <div class="space-y-1.5">
                        <label for="mevcut_sifre" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Mevcut Şifre</label>
                        <input type="password" name="mevcut_sifre" id="mevcut_sifre" required
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>

                    <!-- Yeni Şifre -->
                    <div class="space-y-1.5">
                        <label for="sifre" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Yeni Şifre</label>
                        <input type="password" name="sifre" id="sifre" required
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>

                    <!-- Yeni Şifre Tekrar -->
                    <div class="space-y-1.5">
                        <label for="sifre_confirmation" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Yeni Şifre Tekrar</label>
                        <input type="password" name="sifre_confirmation" id="sifre_confirmation" required
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>
                </div>
            </div>

            <!-- Form Submission Action -->
            <div class="flex justify-end gap-3.5">
                <a href="{{ route('hekim.panel') }}" 
                   class="px-6 py-3 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#6B7280] font-bold text-xs uppercase tracking-wider transition-all font-display text-center select-none shadow-sm cursor-pointer">
                    Geri Dön
                </a>
                <button type="submit" 
                        class="px-8 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                    Şifremi Güncelle
                </button>
            </div>
        </form>
    </div>
@endsection
