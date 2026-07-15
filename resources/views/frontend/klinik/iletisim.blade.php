@extends('frontend.layouts.app')

@section('title', $klinik->ad . ' - İletişim & Konum')
@section('meta_description', $klinik->ad . ' klinik iletişim bilgileri, telefon, e-posta, adres ve harita konumu.')

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
                            <span class="flex items-center gap-1">📞 {{ $klinik->telefon }}</span>
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
                <a href="{{ route('frontend.klinik.hizmetler', [$klinik->il?->slug ?? 'il', $klinik->ilce?->slug ?? 'ilce', $klinik->slug]) }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold font-display transition-all duration-150 bg-gray-50 border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-100">
                    Sunulan Hizmetler
                </a>
                <a href="{{ route('frontend.klinik.iletisim', [$klinik->il?->slug ?? 'il', $klinik->ilce?->slug ?? 'ilce', $klinik->slug]) }}" class="px-5 py-2.5 rounded-xl text-xs font-bold font-display transition-all duration-150 bg-[#C96A2B] text-white border border-[#C96A2B]">
                    İletişim & Konum
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Detailed Contact Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="p-6 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm space-y-5">
                    <h3 class="text-base font-bold font-display text-[#111827] pb-3 border-b border-[#F5F5F4]">📞 İletişim Bilgileri</h3>
                    
                    <div class="space-y-4 text-xs font-semibold">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase">Telefon Numarası</span>
                            <p class="text-[#111827] mt-1.5 text-sm">{{ $klinik->telefon }}</p>
                        </div>

                        @if($klinik->e_posta)
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase">E-posta Adresi</span>
                                <p class="text-[#111827] mt-1.5 text-sm">{{ $klinik->e_posta }}</p>
                            </div>
                        @endif

                        @if($klinik->web_sitesi)
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase">Web Sitesi</span>
                                <p class="text-[#C96A2B] mt-1.5 text-sm">
                                    <a href="{{ Str::start($klinik->web_sitesi, 'http') }}" target="_blank" class="hover:underline">
                                        {{ $klinik->web_sitesi }}
                                    </a>
                                </p>
                            </div>
                        @endif

                        <div>
                            <span class="text-[10px] text-gray-400 uppercase">Açık Adres</span>
                            <p class="text-[#111827] mt-1.5 leading-relaxed">
                                {{ $klinik->adres }}<br>
                                {{ $klinik->ilce->ad ?? '' }} / {{ $klinik->il->ad ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 2 Columns: Map Embed -->
            <div class="lg:col-span-2 space-y-6">
                <div class="p-6 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm space-y-4">
                    <h3 class="text-base font-bold font-display text-[#111827] pb-3 border-b border-[#F5F5F4]">🗺️ Harita Konumu</h3>
                    
                    @if($klinik->enlem && $klinik->boylam)
                        @php
                            $lat = $klinik->enlem;
                            $lon = $klinik->boylam;
                            $bbox = ($lon - 0.015) . ',' . ($lat - 0.01) . ',' . ($lon + 0.015) . ',' . ($lat + 0.01);
                        @endphp
                        <div class="w-full h-[400px] rounded-2xl overflow-hidden border border-[#E5E7EB]">
                            <iframe 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                scrolling="no" 
                                marginheight="0" 
                                marginwidth="0" 
                                src="https://www.openstreetmap.org/export/embed.html?bbox={{ urlencode($bbox) }}&amp;layer=mapnik&amp;marker={{ $lat }}%2C{{ $lon }}" 
                                style="border: 0;">
                            </iframe>
                        </div>
                        <div class="text-[10px] text-right text-gray-400">
                            <a href="https://www.openstreetmap.org/?mlat={{ $lat }}&amp;mlon={{ $lon }}#map=15/{{ $lat }}/{{ $lon }}" target="_blank" class="hover:underline">Daha Büyük Haritada Görüntüle</a>
                        </div>
                    @else
                        <div class="w-full h-[400px] rounded-2xl bg-gray-50 flex flex-col items-center justify-center border border-dashed border-gray-200 p-6 text-center">
                            <span class="text-4xl">📍</span>
                            <span class="block text-sm font-semibold text-[#111827] mt-3">Harita Konumu Belirtilmemiştir</span>
                            <p class="text-xs text-[#6B7280] mt-1 max-w-sm">Kliniğin koordinatları ayarlar sayfasından girildiğinde harita otomatik olarak burada görünecektir.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
