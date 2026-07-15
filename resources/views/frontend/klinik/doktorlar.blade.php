@extends('frontend.layouts.app')

@section('title', $klinik->ad . ' - Hekim Kadromuz')
@section('meta_description', $klinik->ad . ' klinik hekim kadrosu, uzmanlık alanları ve randevu alma bilgileri.')

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
                            <span class="flex items-center gap-1">👥 {{ $doktorlar->count() }} Aktif Hekim</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Navigation Tabs -->
            <div class="flex flex-wrap items-center gap-2 mt-6 pt-2">
                <a href="{{ route('frontend.klinik.profil', [$klinik->il?->slug ?? 'il', $klinik->ilce?->slug ?? 'ilce', $klinik->slug]) }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold font-display transition-all duration-150 bg-gray-50 border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-100">
                    Klinik Hakkında
                </a>
                <a href="{{ route('frontend.klinik.doktorlar', [$klinik->il?->slug ?? 'il', $klinik->ilce?->slug ?? 'ilce', $klinik->slug]) }}" class="px-5 py-2.5 rounded-xl text-xs font-bold font-display transition-all duration-150 bg-[#C96A2B] text-white border border-[#C96A2B]">
                    Hekim Kadromuz
                </a>
                <a href="{{ route('frontend.klinik.hizmetler', [$klinik->il?->slug ?? 'il', $klinik->ilce?->slug ?? 'ilce', $klinik->slug]) }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold font-display transition-all duration-150 bg-gray-50 border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-100">
                    Sunulan Hizmetler
                </a>
                <a href="{{ route('frontend.klinik.iletisim', [$klinik->il?->slug ?? 'il', $klinik->ilce?->slug ?? 'ilce', $klinik->slug]) }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold font-display transition-all duration-150 bg-gray-50 border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-100">
                    İletişim & Konum
                </a>
            </div>
        </div>

        <!-- Doctors Kadro Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($doktorlar as $doc)
                @php
                    $kisaAd = '';
                    if ($doc->ad_soyad) {
                        $words = explode(' ', $doc->ad_soyad);
                        $kisaAd = mb_strtoupper(mb_substr($words[0], 0, 1));
                        if (count($words) > 1) {
                            $kisaAd .= mb_strtoupper(mb_substr(end($words), 0, 1));
                        }
                    } else {
                        $kisaAd = 'HE';
                    }
                @endphp
                <div class="p-6 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow duration-200">
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            @if($doc->profil_resmi)
                                <img src="{{ asset($doc->profil_resmi) }}" alt="{{ $doc->ad_soyad }}" class="w-14 h-14 rounded-2xl object-cover border border-[#E5E7EB] shadow-sm shrink-0">
                            @else
                                <div class="w-14 h-14 rounded-2xl bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-lg font-bold font-display shrink-0">
                                    {{ $kisaAd }}
                                </div>
                            @endif
                            <div>
                                <h3 class="text-sm font-bold text-[#111827] font-display">
                                    {{ $doc->unvan ? $doc->unvan . ' ' : '' }}{{ $doc->ad_soyad }}
                                </h3>
                                <p class="text-[10px] text-[#6B7280] mt-0.5 font-medium">{{ $doc->uzmanlik_alani }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-[#6B7280] py-2 border-y border-[#F5F5F4]">
                            <span>⭐ {{ $doc->ortalama_puan ? $doc->ortalama_puan . ' / 5.0' : 'Puan yok' }}</span>
                            <span>💬 {{ $doc->yorum_sayisi }} Yorum</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ $doc->profil_url }}" class="w-full flex items-center justify-center bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider py-3 rounded-xl transition-colors">
                            Hekim Profilini Gör & Randevu Al
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-12 text-center bg-white rounded-3xl border border-[#E5E7EB]">
                    <p class="text-xs text-[#6B7280]">Klinikte aktif hizmet veren hekim bulunmamaktadır.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
