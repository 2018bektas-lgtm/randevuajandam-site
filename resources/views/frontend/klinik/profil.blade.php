@extends('frontend.layouts.app')

@section('title', $klinik->meta_baslik ?: $klinik->ad . ' - Klinik Profili')
@section('meta_description', $klinik->meta_aciklama ?: $klinik->ad . ' klinik detayları, doktor kadrosu, hizmetler ve iletişim bilgileri.')

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
                            @if($ortalamaPuan > 0)
                                <span class="flex items-center gap-1 text-amber-600 font-bold">⭐ {{ $ortalamaPuan }} / 5.0 Puan</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Navigation Tabs -->
            <div class="flex flex-wrap items-center gap-2 mt-6 pt-2">
                <a href="{{ route('frontend.klinik.profil', [$klinik->il?->slug ?? 'il', $klinik->ilce?->slug ?? 'ilce', $klinik->slug]) }}" class="px-5 py-2.5 rounded-xl text-xs font-bold font-display transition-all duration-150 bg-[#C96A2B] text-white border border-[#C96A2B]">
                    Klinik Hakkında
                </a>
                <a href="{{ route('frontend.klinik.doktorlar', [$klinik->il?->slug ?? 'il', $klinik->ilce?->slug ?? 'ilce', $klinik->slug]) }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold font-display transition-all duration-150 bg-gray-50 border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-100">
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left 2 Columns: Klinik Hakkında & Yorumlar -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Hakkında -->
                <div class="p-6 sm:p-8 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm space-y-4">
                    <h3 class="text-lg font-bold font-display text-[#111827] pb-3 border-b border-[#F5F5F4]">Klinik Hakkında</h3>
                    <p class="text-sm text-[#4B5563] leading-relaxed whitespace-pre-line">
                        {{ $klinik->aciklama ?: 'Klinik hakkında detaylı açıklama belirtilmemiştir.' }}
                    </p>
                </div>

                <!-- Son Yorumlar -->
                <div class="p-6 sm:p-8 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm space-y-6">
                    <h3 class="text-lg font-bold font-display text-[#111827] pb-3 border-b border-[#F5F5F4]">Hasta Yorumları</h3>
                    
                    @forelse($yorumlar as $yorum)
                        <div class="pb-6 border-b border-[#F5F5F4] last:border-b-0 last:pb-0 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-[#111827]">{{ $yorum->hasta->maskeli_ad ?? 'Hasta' }}</span>
                                    <span class="text-xs text-amber-500 font-bold">★ {{ $yorum->puan }}</span>
                                </div>
                                <span class="text-[10px] text-[#6B7280]">{{ $yorum->created_at->format('d.m.Y') }}</span>
                            </div>
                            <p class="text-xs text-[#4B5563] italic leading-relaxed">
                                "{{ $yorum->yorum }}"
                            </p>
                            <div class="text-[10px] text-[#C96A2B] font-semibold">
                                Hekim: {{ $yorum->doktor->unvan ? $yorum->doktor->unvan . ' ' : '' }}{{ $yorum->doktor->ad_soyad }}
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-[#6B7280] text-center py-4">Kliniğe ait onaylanmış hasta yorumu henüz bulunmamaktadır.</p>
                    @endforelse
                </div>
            </div>

            <!-- Right Column: Çalışma Saatleri & İletişim Özeti -->
            <div class="space-y-6">
                <!-- Çalışma Saatleri -->
                <div class="p-6 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm">
                    <h3 class="text-base font-bold font-display text-[#111827] mb-4 pb-3 border-b border-[#F5F5F4]">⏱️ Çalışma Saatleri</h3>
                    
                    @if($klinik->calisma_saatleri)
                        <div class="space-y-2 text-xs font-medium">
                            @foreach(['pazartesi', 'sali', 'carsamba', 'persembe', 'cuma', 'cumartesi', 'pazar'] as $day)
                                @php
                                    $dayData = $klinik->calisma_saatleri[$day] ?? null;
                                    $label = ucfirst($day === 'sali' ? 'salı' : ($day === 'carsamba' ? 'çarşamba' : ($day === 'persembe' ? 'perşembe' : $day)));
                                @endphp
                                <div class="flex justify-between py-1.5 border-b border-[#F5F5F4] last:border-0">
                                    <span class="text-[#6B7280]">{{ $label }}</span>
                                    <span class="text-[#111827] font-semibold">
                                        @if($dayData && isset($dayData['kapali']) && $dayData['kapali'])
                                            <span class="text-red-500">Kapalı</span>
                                        @else
                                            {{ $dayData['acilis'] ?? '09:00' }} - {{ $dayData['kapanis'] ?? '18:00' }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-[#6B7280] text-center">Çalışma saatleri belirtilmemiştir.</p>
                    @endif
                </div>

                <!-- Hızlı İletişim -->
                <div class="p-6 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm space-y-4">
                    <h3 class="text-base font-bold font-display text-[#111827] pb-3 border-b border-[#F5F5F4]">📞 İletişim Bilgileri</h3>
                    <div class="space-y-3 text-xs font-semibold">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase">Telefon</span>
                            <p class="text-[#111827] mt-1">{{ $klinik->telefon }}</p>
                        </div>
                        @if($klinik->e_posta)
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase">E-posta</span>
                                <p class="text-[#111827] mt-1">{{ $klinik->e_posta }}</p>
                            </div>
                        @endif
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase">Adres</span>
                            <p class="text-[#111827] mt-1 leading-relaxed">{{ $klinik->adres }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- LocalBusiness Schema markup -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "MedicalClinic",
  "name": "{{ $klinik->ad }}",
  "image": "{{ $klinik->logo ? asset($klinik->logo) : '' }}",
  "@id": "{{ url()->current() }}",
  "url": "{{ url()->current() }}",
  "telephone": "{{ $klinik->telefon }}",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "{{ $klinik->adres }}",
    "addressLocality": "{{ $klinik->ilce->ad ?? '' }}",
    "addressRegion": "{{ $klinik->il->ad ?? '' }}",
    "addressCountry": "TR"
  }
}
</script>
@endsection
