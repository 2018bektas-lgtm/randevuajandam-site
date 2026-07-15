@extends('klinik.layout')

@section('baslik', 'Klinik Duyuruları - ' . $klinik->ad)
@section('sayfa_baslik', 'Klinik Duyuruları')

@section('icerik')
    <div class="space-y-6">
        <!-- Header banner -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <h3 class="text-lg font-bold font-display text-[#111827]">{{ $klinik->ad }} - Duyuru Panosu</h3>
            <p class="text-xs text-[#6B7280] mt-1">
                Klinik yönetimi tarafından yayınlanan genel duyuruları, önemli gelişmeleri ve acil bildirimleri buradan takip edebilirsiniz.
            </p>
        </div>

        <!-- Duyuru listesi -->
        <div class="space-y-4">
            @forelse($duyurular as $duyuru)
                <div class="p-6 rounded-2xl bg-white border shadow-sm transition-all duration-200 hover:shadow-md
                    @if($duyuru->onem_derecesi === 'acil') border-red-200 bg-red-50/20 @elseif($duyuru->onem_derecesi === 'onemli') border-amber-200 bg-amber-50/10 @else border-[#E5E7EB] @endif">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider font-display border
                                @if($duyuru->onem_derecesi === 'acil') bg-red-50 text-red-700 border-red-200
                                @elseif($duyuru->onem_derecesi === 'onemli') bg-amber-50 text-amber-700 border-amber-200
                                @else bg-blue-50 text-blue-700 border-blue-200 @endif">
                                @if($duyuru->onem_derecesi === 'acil') Acil Duyuru
                                @elseif($duyuru->onem_derecesi === 'onemli') Önemli Duyuru
                                @else Genel Duyuru @endif
                            </span>
                            <h4 class="text-base font-bold text-[#111827]">{{ $duyuru->baslik }}</h4>
                        </div>
                        <span class="text-xs text-[#6B7280]">
                            {{ $duyuru->created_at->format('d.m.Y H:i') }}
                        </span>
                    </div>
                    <div class="text-sm text-[#4B5563] leading-relaxed whitespace-pre-line">
                        {!! nl2br(e($duyuru->icerik)) !!}
                    </div>
                </div>
            @empty
                <div class="p-10 rounded-2xl bg-white border border-[#E5E7EB] text-center">
                    <p class="text-xs text-[#6B7280]">Aktif klinik duyurusu bulunmamaktadır.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
