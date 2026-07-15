@extends('frontend.layouts.app')

@section('title', $klinik->ad . ' - Hizmetlerimiz')
@section('meta_description', $klinik->ad . ' klinik bünyesinde sunulan tüm sağlık hizmetleri, tedavi süreleri ve ücretleri.')

@section('icerik')
<div class="bg-[#F5F5F4] py-10 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Clinic Header Card -->
        <div class="bg-white rounded-3xl border border-[#E5E7EB] shadow-sm overflow-hidden p-6 sm:p-8">
            <div class="flex flex-col md:flex-row items-center md:items-start justify-between gap-6 pb-6 border-b border-[#F5F5F4]">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
                    @if($klinik->logo)
                        <img src="{{ asset($klinik->logo) }}" alt="Logo" class="w-24 h-24 rounded-2xl object-cover border border-[#E5E7EB] shadow-sm">
                    @else
                        <div class="w-24 h-24 rounded-2xl bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-3xl font-bold font-display">
                            {{ mb_strtoupper(mb_substr($klinik->ad, 0, 2)) }}
                        </div>
                    @endif
                    <div class="space-y-1.5">
                        <h1 class="text-2xl sm:text-3xl font-extrabold font-display text-[#111827]">{{ $klinik->ad }}</h1>
                        <div class="flex flex-wrap justify-center sm:justify-start items-center gap-x-4 gap-y-2 text-xs text-[#6B7280]">
                            <span class="flex items-center gap-1">📍 {{ $klinik->ilce->ad ?? '' }} / {{ $klinik->il->ad ?? '' }}</span>
                            <span class="flex items-center gap-1">👥 {{ $hizmetler->count() }} Farklı Tedavi & Hizmet</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Navigation Tabs -->
            <div class="flex flex-wrap items-center gap-2 mt-6 pt-2">
                <a href="{{ route('frontend.klinik.profil', [$klinik->il?->slug ?? 'il', $klinik->ilce?->slug ?? 'ilce', $klinik->slug]) }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold font-display transition-all duration-150 bg-gray-50 border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-100">
                    Klinik Hakkında
                </a>
                <a href="{{ route('frontend.klinik.doktorlar', [$klinik->il?->slug ?? 'il', $klinik->ilce?->slug ?? 'ilce', $klinik->slug]) }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold font-display transition-all duration-150 bg-gray-50 border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-100">
                    Hekim Kadromuz
                </a>
                <a href="{{ route('frontend.klinik.hizmetler', [$klinik->il?->slug ?? 'il', $klinik->ilce?->slug ?? 'ilce', $klinik->slug]) }}" class="px-5 py-2.5 rounded-xl text-xs font-bold font-display transition-all duration-150 bg-[#C96A2B] text-white border border-[#C96A2B]">
                    Sunulan Hizmetler
                </a>
                <a href="{{ route('frontend.klinik.iletisim', [$klinik->il?->slug ?? 'il', $klinik->ilce?->slug ?? 'ilce', $klinik->slug]) }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold font-display transition-all duration-150 bg-gray-50 border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-100">
                    İletişim & Konum
                </a>
            </div>
        </div>

        <!-- Services List -->
        <div class="bg-white rounded-3xl border border-[#E5E7EB] shadow-sm overflow-hidden p-6 sm:p-8">
            @if($hizmetler->isNotEmpty())
                <div class="divide-y divide-[#F5F5F4]">
                    @foreach($hizmetler as $hizmet)
                        <div class="py-5 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <h3 class="text-base font-bold text-[#111827] font-display">{{ $hizmet->ad }}</h3>
                                <div class="flex items-center gap-2 text-xs text-[#6B7280]">
                                    <span>⏱️ {{ $hizmet->sure }} Dakika</span>
                                    <span>•</span>
                                    <span class="text-[#C96A2B] font-semibold">Hekim: {{ $hizmet->doktor->unvan ? $hizmet->doktor->unvan . ' ' : '' }}{{ $hizmet->doktor->ad_soyad }}</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between sm:justify-end gap-6 shrink-0">
                                <span class="text-base font-bold text-[#111827]">₺{{ number_format($hizmet->fiyat, 2, ',', '.') }}</span>
                                <a href="{{ $hizmet->doktor->profil_url }}" class="px-4 py-2 bg-gray-50 hover:bg-gray-100 border border-[#E5E7EB] text-[#4B5563] text-xs font-bold rounded-xl transition-colors">
                                    Randevu Al
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-[#6B7280] text-center py-6">Klinik bünyesinde tanımlanmış aktif hizmet bulunmamaktadır.</p>
            @endif
        </div>
    </div>
</div>
@endsection
