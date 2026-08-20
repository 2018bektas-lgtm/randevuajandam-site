@extends('hekim.layout')

@section('baslik', 'Finansal Genel Bakış - Randevu Ajandam')
@section('sayfa_baslik', 'Finansal Yönetim')

@php
    $canFinansRapor = auth('doktor')->user()?->hasPaketFeature('finans_rapor');

    // Delta rozet render helper — küçük ok + yüzde
    $deltaRenk = function (float $delta, bool $tersineCevir = false) {
        $iyi = $tersineCevir ? $delta <= 0 : $delta >= 0;
        return $iyi
            ? 'text-emerald-700 bg-emerald-50 border-emerald-100'
            : 'text-rose-700 bg-rose-50 border-rose-100';
    };
    $deltaOk = fn (float $d) => $d >= 0
        ? '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/>'
        : '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/>';
@endphp

@section('icerik')
    {{-- FINANS NAV --}}
    @component('hekim.finans.partials._nav')
        @slot('aksiyon')
            @if($canFinansRapor)
                <a href="{{ route('hekim.finans.rapor-pdf') }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-[#E5E7EB] hover:border-[#C96A2B] text-[#4B5563] hover:text-[#C96A2B] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Aylık PDF
                </a>
            @else
                <a href="{{ route('frontend.hekim.paket_sec', ['degistir' => 1]) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#FFF7ED] border border-[#FED7AA] text-[#92400e] hover:bg-amber-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    PDF için paket yükselt
                </a>
            @endif
        @endslot
    @endcomponent

    {{-- KRİTİK UYARI ŞERİDİ — sadece vadesi geçen fatura varsa --}}
    @if($vadesiGecenSayi > 0)
        <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-rose-50 to-white border border-rose-200 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-rose-900">
                    {{ $vadesiGecenSayi }} fatura 30 günden uzun süredir tahsil edilmedi
                </p>
                <p class="text-xs text-rose-800/80 mt-0.5">
                    Toplam bekleyen tutar: <strong>{{ number_format($vadesiGecenTutar, 2, ',', '.') }} ₺</strong>
                    — hasta cari hesaplarını gözden geçirin.
                </p>
            </div>
            <a href="{{ route('hekim.finans.hasta-bakiyeleri', ['durum' => 'borclu']) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition-colors">
                Borçlu hastalar
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        </div>
    @endif

    {{-- HERO KPI GRID — 4 kart, delta rozetleriyle --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        {{-- Tahsilat --}}
        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Bu Ay Tahsilat</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold font-display text-emerald-700 tracking-tight">
                {{ number_format($buAyGelir, 2, ',', '.') }} ₺
            </p>
            <div class="mt-2 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold border {{ $deltaRenk($gelirDeltaYuzde) }}">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        {!! $deltaOk($gelirDeltaYuzde) !!}
                    </svg>
                    {{ number_format(abs($gelirDeltaYuzde), 1, ',', '.') }}%
                </span>
                <span class="text-[11px] text-[#6B7280]">geçen ay</span>
            </div>
        </div>

        {{-- Gider --}}
        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Bu Ay Gider</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold font-display text-rose-700 tracking-tight">
                {{ number_format($buAyGider, 2, ',', '.') }} ₺
            </p>
            <div class="mt-2 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold border {{ $deltaRenk($giderDeltaYuzde, true) }}">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        {!! $deltaOk($giderDeltaYuzde) !!}
                    </svg>
                    {{ number_format(abs($giderDeltaYuzde), 1, ',', '.') }}%
                </span>
                <span class="text-[11px] text-[#6B7280]">geçen ay</span>
            </div>
        </div>

        {{-- Net Kâr --}}
        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Net Kâr</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.281m5.94 2.28l-2.28 5.941"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold font-display tracking-tight {{ $buAyNetKar >= 0 ? 'text-[#111827]' : 'text-rose-700' }}">
                {{ number_format($buAyNetKar, 2, ',', '.') }} ₺
            </p>
            <div class="mt-2 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold border {{ $deltaRenk($netKarDeltaYuzde) }}">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        {!! $deltaOk($netKarDeltaYuzde) !!}
                    </svg>
                    {{ number_format(abs($netKarDeltaYuzde), 1, ',', '.') }}%
                </span>
                <span class="text-[11px] text-[#6B7280]">geçen ay</span>
            </div>
        </div>

        {{-- Bekleyen Alacak --}}
        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Bekleyen Alacak</span>
                <span class="w-8 h-8 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold font-display text-[#C96A2B] tracking-tight">
                {{ number_format($toplamBorc, 2, ',', '.') }} ₺
            </p>
            <div class="mt-2 text-[11px] text-[#6B7280]">
                {{ $enCokBorcluHastalar->count() }} borçlu hasta
            </div>
        </div>
    </div>

    {{-- İKİNCİ SIRA: Nakit Akışı Trendi + Tahsilat Performansı --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Nakit Akışı (2/3) --}}
        <div class="lg:col-span-2 p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <h3 class="text-base font-bold font-display text-[#111827]">Nakit Akışı</h3>
                    <p class="text-xs text-[#6B7280] mt-0.5">Son 12 ayın tahsilat ve gider seyri</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-[11px] font-semibold text-[#4B5563]">Tahsilat</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <span class="text-[11px] font-semibold text-[#4B5563]">Gider</span>
                    </div>
                </div>
            </div>
            <div id="trendChart" class="w-full h-[320px]"></div>
        </div>

        {{-- Tahsilat Performansı + En Borçlu Hastalar (1/3) --}}
        <div class="space-y-6">
            {{-- Tahsilat Oranı Gauge --}}
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-1">Tahsilat Oranı</h3>
                <p class="text-xs text-[#6B7280] mb-3">Bu ay faturalanan tutarın ne kadarı tahsil edildi</p>
                <div id="tahsilatGauge" class="w-full h-[180px]"></div>
                <div class="grid grid-cols-2 gap-3 mt-3 pt-3 border-t border-[#F3F4F6] text-center">
                    <div>
                        <p class="text-[10px] font-bold text-[#9CA3AF] uppercase tracking-wide">Faturalanan</p>
                        <p class="text-sm font-bold text-[#111827] mt-0.5">{{ number_format($buAyFaturalanan, 0, ',', '.') }} ₺</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-[#9CA3AF] uppercase tracking-wide">Fatura Sayısı</p>
                        <p class="text-sm font-bold text-[#111827] mt-0.5">{{ $buAyFaturaSayisi }}</p>
                    </div>
                </div>
            </div>

            {{-- En Borçlu 3 Hasta --}}
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold font-display text-[#111827]">En Borçlu Hastalar</h3>
                        <p class="text-xs text-[#6B7280] mt-0.5">Kalan bakiyeye göre ilk 3</p>
                    </div>
                    <a href="{{ route('hekim.finans.hasta-bakiyeleri', ['durum' => 'borclu']) }}"
                       class="text-[11px] font-bold text-[#C96A2B] hover:underline whitespace-nowrap">
                        Tümü →
                    </a>
                </div>
                @forelse($enCokBorcluHastalar as $borclu)
                    <a href="{{ route('hekim.finans.hasta-hesap', $borclu->hasta_id) }}"
                       class="flex items-center gap-3 py-2.5 border-b border-[#F3F4F6] last:border-0 hover:bg-[#FAFAFA]/50 -mx-2 px-2 rounded-lg transition-colors">
                        <div class="w-9 h-9 rounded-full bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center text-[11px] font-bold flex-shrink-0">
                            {{ mb_strtoupper(mb_substr($borclu->hasta->ad_soyad, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-[#111827] truncate">{{ $borclu->hasta->ad_soyad }}</p>
                            <p class="text-[11px] text-[#9CA3AF]">{{ $borclu->hasta->telefon ?? '—' }}</p>
                        </div>
                        <span class="text-sm font-bold text-rose-600 whitespace-nowrap">
                            {{ number_format($borclu->kalan, 0, ',', '.') }} ₺
                        </span>
                    </a>
                @empty
                    <div class="text-center py-6">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </div>
                        <p class="text-xs text-[#6B7280]">Bekleyen borçlu hasta yok</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ÜÇÜNCÜ SIRA: Dağılım Grafikleri --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Hizmet Bazlı Gelir --}}
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-bold font-display text-[#111827]">Hizmet Bazlı Gelir</h3>
                <p class="text-xs text-[#6B7280] mt-0.5">Tahsilatlarınızın hizmetlere göre dağılımı</p>
            </div>
            @if(count($hizmetValues) > 0)
                <div id="hizmetChart" class="w-full h-[260px]"></div>
            @else
                <div class="h-[220px] flex flex-col items-center justify-center text-center">
                    <div class="w-12 h-12 rounded-full bg-[#FAFAFA] text-[#9CA3AF] flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-[#6B7280]">Henüz hizmet bazlı gelir yok</p>
                </div>
            @endif
        </div>

        {{-- Gider Kategori Dağılımı --}}
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-bold font-display text-[#111827]">Gider Kategorileri</h3>
                <p class="text-xs text-[#6B7280] mt-0.5">Giderlerinizin nereye harcandığı</p>
            </div>
            @if(count($giderValues) > 0)
                <div id="giderChart" class="w-full h-[260px]"></div>
            @else
                <div class="h-[220px] flex flex-col items-center justify-center text-center">
                    <div class="w-12 h-12 rounded-full bg-[#FAFAFA] text-[#9CA3AF] flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-[#6B7280]">Henüz gider kaydı yok</p>
                </div>
            @endif
        </div>
    </div>

    {{-- SON İŞLEMLER --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Son Gelirler --}}
        <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#F3F4F6]">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </span>
                    <h3 class="text-base font-bold font-display text-[#111827]">Son Gelirler</h3>
                </div>
                <a href="{{ route('hekim.finans.gelirler') }}" class="text-[11px] font-bold text-[#C96A2B] hover:underline">Tümü →</a>
            </div>
            <div class="divide-y divide-[#F3F4F6]">
                @forelse($sonOdemeler as $odeme)
                    <div class="px-6 py-3.5 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-[#111827] truncate">
                                {{ $odeme->hasta ? $odeme->hasta->ad_soyad : ($odeme->randevu ? $odeme->randevu->ad . ' ' . $odeme->randevu->soyad : 'Serbest Gelir') }}
                            </p>
                            <p class="text-[11px] text-[#6B7280] mt-0.5 truncate">
                                {{ $odeme->hizmet ? $odeme->hizmet->ad : 'Hizmet dışı' }}
                                · {{ ($odeme->odeme_tarihi ?? $odeme->created_at)->format('d.m.Y') }}
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold text-emerald-600">+{{ number_format($odeme->odenen_tutar, 2, ',', '.') }} ₺</p>
                            @php
                                $durumStil = [
                                    'beklemede'   => 'bg-amber-50 text-amber-800',
                                    'kismi_odeme' => 'bg-blue-50 text-blue-800',
                                    'odendi'      => 'bg-emerald-50 text-emerald-800',
                                    'iptal'       => 'bg-red-50 text-red-800',
                                ][$odeme->durum] ?? '';
                                $durumEtiket = [
                                    'beklemede'   => 'Bekliyor',
                                    'kismi_odeme' => 'Kısmi',
                                    'odendi'      => 'Ödendi',
                                    'iptal'       => 'İptal',
                                ][$odeme->durum] ?? '';
                            @endphp
                            @if($durumEtiket)
                                <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[10px] font-bold {{ $durumStil }}">{{ $durumEtiket }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <p class="text-sm text-[#6B7280]">Henüz gelir kaydı yok</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Son Giderler --}}
        <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#F3F4F6]">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6"/>
                        </svg>
                    </span>
                    <h3 class="text-base font-bold font-display text-[#111827]">Son Giderler</h3>
                </div>
                <a href="{{ route('hekim.finans.giderler') }}" class="text-[11px] font-bold text-[#C96A2B] hover:underline">Tümü →</a>
            </div>
            <div class="divide-y divide-[#F3F4F6]">
                @forelse($sonGiderler as $gider)
                    <div class="px-6 py-3.5 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-[#111827] truncate">{{ $gider->baslik }}</p>
                            <p class="text-[11px] text-[#6B7280] mt-0.5 truncate">
                                @if($gider->finansKategori)
                                    {{ $gider->finansKategori->ad }} ·
                                @endif
                                {{ $gider->tarih->format('d.m.Y') }}
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold text-rose-600">-{{ number_format($gider->tutar, 2, ',', '.') }} ₺</p>
                            @if($gider->belge_yolu)
                                <a href="{{ asset($gider->belge_yolu) }}" target="_blank"
                                   class="inline-flex items-center gap-1 mt-0.5 text-[10px] font-bold text-[#C96A2B] hover:underline">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25"/>
                                    </svg>
                                    Belge
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <p class="text-sm text-[#6B7280]">Henüz gider kaydı yok</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ApexCharts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const trFmt = v => (v ?? 0).toLocaleString('tr-TR', {minimumFractionDigits: 0}) + ' ₺';

            // Nakit Akışı Trendi
            new ApexCharts(document.querySelector("#trendChart"), {
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif',
                    zoom: { enabled: false }
                },
                series: [
                    { name: 'Tahsilat', data: {!! json_encode($incomeTrends) !!} },
                    { name: 'Gider',    data: {!! json_encode($expenseTrends) !!} },
                ],
                colors: ['#10B981', '#EF4444'],
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.28, opacityTo: 0.02, stops: [0, 100] }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2.5 },
                legend: { show: false },
                xaxis: {
                    categories: {!! json_encode($months) !!},
                    labels: { style: { colors: '#9CA3AF', fontSize: '11px' } },
                    axisBorder: { show: false },
                    axisTicks:  { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: v => v >= 1000 ? (v/1000).toFixed(0) + 'K ₺' : v + ' ₺',
                        style: { colors: '#9CA3AF', fontSize: '11px' }
                    }
                },
                tooltip: { y: { formatter: trFmt } },
                grid: { borderColor: '#F3F4F6', strokeDashArray: 4, padding: { left: 10, right: 10 } }
            }).render();

            // Tahsilat Oranı Gauge
            new ApexCharts(document.querySelector("#tahsilatGauge"), {
                chart: { type: 'radialBar', height: 180, sparkline: { enabled: true } },
                series: [{{ round($tahsilatOrani, 1) }}],
                colors: ['#C96A2B'],
                plotOptions: {
                    radialBar: {
                        hollow: { size: '62%' },
                        track: { background: '#F3F4F6', strokeWidth: '100%' },
                        dataLabels: {
                            name: { show: false },
                            value: {
                                offsetY: 5,
                                fontSize: '26px',
                                fontWeight: 700,
                                color: '#111827',
                                formatter: v => v + '%'
                            }
                        }
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: { shade: 'light', shadeIntensity: 0.15, gradientToColors: ['#F59E0B'], type: 'diagonal2', opacityFrom: 1, opacityTo: 1, stops: [0, 100] }
                },
                stroke: { lineCap: 'round' }
            }).render();

            @if(count($hizmetValues) > 0)
            new ApexCharts(document.querySelector("#hizmetChart"), {
                chart: { type: 'donut', height: 260, fontFamily: 'Inter, sans-serif' },
                series: {!! json_encode($hizmetValues) !!},
                labels: {!! json_encode($hizmetLabels) !!},
                colors: ['#C96A2B', '#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899'],
                legend: { position: 'bottom', fontSize: '11px', markers: { width: 8, height: 8 } },
                dataLabels: { enabled: false },
                stroke: { width: 2, colors: ['#FFFFFF'] },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Toplam',
                                    fontSize: '11px',
                                    color: '#9CA3AF',
                                    formatter: w => w.globals.seriesTotals.reduce((a,b) => a+b, 0).toLocaleString('tr-TR') + ' ₺'
                                }
                            }
                        }
                    }
                },
                tooltip: { y: { formatter: trFmt } }
            }).render();
            @endif

            @if(count($giderValues) > 0)
            new ApexCharts(document.querySelector("#giderChart"), {
                chart: { type: 'donut', height: 260, fontFamily: 'Inter, sans-serif' },
                series: {!! json_encode($giderValues) !!},
                labels: {!! json_encode($giderLabels) !!},
                colors: ['#EF4444', '#F59E0B', '#3B82F6', '#8B5CF6', '#EC4899', '#10B981', '#6B7280'],
                legend: { position: 'bottom', fontSize: '11px', markers: { width: 8, height: 8 } },
                dataLabels: { enabled: false },
                stroke: { width: 2, colors: ['#FFFFFF'] },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Toplam',
                                    fontSize: '11px',
                                    color: '#9CA3AF',
                                    formatter: w => w.globals.seriesTotals.reduce((a,b) => a+b, 0).toLocaleString('tr-TR') + ' ₺'
                                }
                            }
                        }
                    }
                },
                tooltip: { y: { formatter: trFmt } }
            }).render();
            @endif
        });
    </script>
@endsection
