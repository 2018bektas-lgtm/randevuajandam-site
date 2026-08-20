@extends('hekim.layout')

@section('baslik', 'Bildirimler - Randevu Ajandam')
@section('sayfa_baslik', 'Bildirimler')

@php
    $renkStilleri = [
        'rose'    => ['bg' => 'bg-rose-50',    'text' => 'text-rose-700',    'border' => 'border-rose-200',    'icon_bg' => 'bg-rose-100 text-rose-600'],
        'amber'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'border' => 'border-amber-200',   'icon_bg' => 'bg-amber-100 text-amber-600'],
        'blue'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'border' => 'border-blue-200',    'icon_bg' => 'bg-blue-100 text-blue-600'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon_bg' => 'bg-emerald-100 text-emerald-600'],
        'indigo'  => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-700',  'border' => 'border-indigo-200',  'icon_bg' => 'bg-indigo-100 text-indigo-600'],
        'orange'  => ['bg' => 'bg-orange-50',  'text' => 'text-orange-700',  'border' => 'border-orange-200',  'icon_bg' => 'bg-orange-100 text-orange-600'],
    ];

    $onemEtiket = [
        'kritik' => ['label' => 'Kritik',  'color' => 'bg-rose-100 text-rose-700 border-rose-200'],
        'uyari'  => ['label' => 'Uyarı',   'color' => 'bg-amber-100 text-amber-700 border-amber-200'],
        'bilgi'  => ['label' => 'Bilgi',   'color' => 'bg-blue-100 text-blue-700 border-blue-200'],
    ];

    $bildirimKart = function ($b) use ($renkStilleri, $onemEtiket) {
        $r = $renkStilleri[$b['renk']] ?? $renkStilleri['blue'];
        $o = $onemEtiket[$b['onem']] ?? $onemEtiket['bilgi'];
        return compact('r', 'o');
    };
@endphp

@section('icerik')
    {{-- Header --}}
    <div class="mb-6 p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold font-display text-[#111827]">Bildirimler</h2>
                <p class="text-sm text-[#6B7280] mt-0.5">
                    @if($bildirimler->isEmpty())
                        Yapılması gereken bir şey yok.
                    @else
                        {{ $bildirimler->count() }} aktif bildirim — kritikten önemsize sıralanmış.
                    @endif
                </p>
            </div>
        </div>

        @if($bildirimler->isNotEmpty())
            <div class="flex items-center gap-2 text-xs">
                @foreach(['kritik', 'uyari', 'bilgi'] as $onem)
                    @php $sayi = $bildirimler->where('onem', $onem)->count(); @endphp
                    @if($sayi > 0)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-bold border {{ $onemEtiket[$onem]['color'] }}">
                            {{ $sayi }} {{ $onemEtiket[$onem]['label'] }}
                        </span>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    @if($bildirimler->isEmpty())
        {{-- Empty state --}}
        <div class="p-14 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold font-display text-[#111827]">Her şey yolunda</h3>
            <p class="text-sm text-[#6B7280] mt-2 max-w-md mx-auto">
                Şu an ilgilenmeniz gereken bir bildirim yok. Yeni randevu talepleri, vadesi geçen faturalar veya iptal edilen randevular burada görünecek.
            </p>
        </div>
    @else
        @foreach(['bugun' => 'Bugün', 'bu_hafta' => 'Bu Hafta', 'daha_once' => 'Daha Önce'] as $anahtar => $baslik)
            @if($gruplar[$anahtar]->isNotEmpty())
                <div class="mb-6">
                    <div class="mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-[#C96A2B] rounded-full"></span>
                        <h3 class="text-[11px] font-bold uppercase tracking-widest text-[#6B7280]">{{ $baslik }}</h3>
                        <span class="text-[11px] font-semibold text-[#9CA3AF]">({{ $gruplar[$anahtar]->count() }})</span>
                        <span class="flex-1 h-px bg-gradient-to-r from-[#E5E7EB] to-transparent"></span>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($gruplar[$anahtar] as $b)
                            @php $s = $bildirimKart($b); @endphp
                            <a href="{{ $b['url'] }}"
                               class="group flex items-start gap-4 p-5 rounded-2xl bg-white border border-[#E5E7EB] hover:border-[#C96A2B]/40 hover:shadow-md transition-all">
                                {{-- İkon --}}
                                <span class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 {{ $s['r']['icon_bg'] }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        {!! $b['ikon'] !!}
                                    </svg>
                                </span>

                                {{-- İçerik --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <h4 class="text-sm font-bold text-[#111827] font-display">{{ $b['baslik'] }}</h4>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $s['o']['color'] }}">
                                            {{ $s['o']['label'] }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-[#4B5563] leading-relaxed">{{ $b['mesaj'] }}</p>
                                    <p class="text-[11px] text-[#9CA3AF] mt-1.5">
                                        {{ $b['tarih']->diffForHumans() }} · {{ $b['tarih']->format('d.m.Y H:i') }}
                                    </p>
                                </div>

                                {{-- Ok --}}
                                <span class="flex-shrink-0 self-center text-[#D1D5DB] group-hover:text-[#C96A2B] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                                    </svg>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @endif
@endsection
