@extends('hekim.layout')

@section('baslik', 'Finansal Genel Bakış - Randevu Ajandam')
@section('sayfa_baslik', 'Finansal Yönetim')

@section('icerik')
    <!-- Finance Navigation Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0">
            <a href="{{ route('hekim.finans.index') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('hekim.finans.index') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">
                📊 Genel Bakış
            </a>
            <a href="{{ route('hekim.finans.gelirler') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('hekim.finans.gelirler') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">
                💵 Gelir Kayıtları
            </a>
            <a href="{{ route('hekim.finans.giderler') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('hekim.finans.giderler') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">
                💸 Gider Kayıtları
            </a>
            <a href="{{ route('hekim.finans.hasta-bakiyeleri') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('hekim.finans.hasta-bakiyeleri') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">
                👥 Hasta Bakiyeleri
            </a>
        </div>
        <div>
            <a href="{{ route('hekim.finans.rapor-pdf') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-white border border-[#E5E7EB] hover:border-[#C96A2B] text-[#4B5563] hover:text-[#C96A2B] transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path>
                </svg>
                Bu Ayki Raporu İndir (PDF)
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Stat Card 1: Bu Ay Gelir -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Bu Ayki Tahsilat</span>
            <span class="text-3xl font-bold font-display text-emerald-600 mt-2 block">{{ number_format($buAyGelir, 2, ',', '.') }} ₺</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Bu ay yapılan toplam tahsilat</span>
            <div class="absolute right-0 bottom-0 top-0 w-1/3 bg-gradient-to-l from-emerald-50/20 to-transparent pointer-events-none"></div>
        </div>

        <!-- Stat Card 2: Bu Ay Gider -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Bu Ayki Gider</span>
            <span class="text-3xl font-bold font-display text-rose-600 mt-2 block">{{ number_format($buAyGider, 2, ',', '.') }} ₺</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Bu ay kaydedilen toplam gider</span>
            <div class="absolute right-0 bottom-0 top-0 w-1/3 bg-gradient-to-l from-rose-50/20 to-transparent pointer-events-none"></div>
        </div>

        <!-- Stat Card 3: Bu Ay Net Kar -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Bu Ayki Net Kâr</span>
            <span class="text-3xl font-bold font-display {{ $buAyNetKar >= 0 ? 'text-emerald-700' : 'text-rose-700' }} mt-2 block">
                {{ number_format($buAyNetKar, 2, ',', '.') }} ₺
            </span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Gelir - Gider farkı</span>
            <div class="absolute right-0 bottom-0 top-0 w-1/3 bg-gradient-to-l from-amber-50/20 to-transparent pointer-events-none"></div>
        </div>

        <!-- Stat Card 4: Toplam Bekleyen Alacak -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Bekleyen Toplam Alacak</span>
            <span class="text-3xl font-bold font-display text-amber-600 mt-2 block">{{ number_format($toplamBorc, 2, ',', '.') }} ₺</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Ödemesi bekleyen / eksik tutarlar</span>
            <div class="absolute right-0 bottom-0 top-0 w-1/3 bg-gradient-to-l from-amber-50/20 to-transparent pointer-events-none"></div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        <!-- Trend Chart (Left 2/3) -->
        <div class="lg:col-span-2 p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <h3 class="text-lg font-bold font-display text-[#111827] mb-4">Aylık Finansal Trend (Son 12 Ay)</h3>
            <div id="trendChart" class="w-full h-[320px]"></div>
        </div>

        <!-- Distribution Charts (Right 1/3) -->
        <div class="space-y-8">
            <!-- Services Distribution -->
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-4">Hizmet Bazlı Gelir Dağılımı</h3>
                @if(count($hizmetValues) > 0)
                    <div id="hizmetChart" class="w-full h-[220px]"></div>
                @else
                    <div class="h-[220px] flex items-center justify-center text-xs text-[#6B7280]">
                        Henüz hizmet bazlı gelir verisi bulunmuyor.
                    </div>
                @endif
            </div>

            <!-- Expenses Distribution -->
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-4">Gider Kategori Dağılımı</h3>
                @if(count($giderValues) > 0)
                    <div id="giderChart" class="w-full h-[220px]"></div>
                @else
                    <div class="h-[220px] flex items-center justify-center text-xs text-[#6B7280]">
                        Henüz gider verisi bulunmuyor.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Transactions Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Latest Payments -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold font-display text-[#111827]">Son Gelir Kayıtları</h3>
                <a href="{{ route('hekim.finans.gelirler') }}" class="text-xs font-semibold text-[#C96A2B] hover:underline">Tümünü Gör</a>
            </div>
            <div class="divide-y divide-[#F3F4F6]">
                @forelse($sonOdemeler as $odeme)
                    <div class="py-3.5 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-semibold text-[#111827]">
                                {{ $odeme->hasta ? $odeme->hasta->ad_soyad : ($odeme->randevu ? $odeme->randevu->ad . ' ' . $odeme->randevu->soyad : 'Serbest Gelir') }}
                            </span>
                            <span class="block text-xs text-[#6B7280] mt-0.5">
                                {{ $odeme->hizmet ? $odeme->hizmet->ad : 'Hizmet Dışı Serbest Kayıt' }}
                                • {{ $odeme->odeme_tarihi ? $odeme->odeme_tarihi->format('d.m.Y') : $odeme->created_at->format('d.m.Y') }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="block text-sm font-bold text-emerald-600">
                                +{{ number_format($odeme->odenen_tutar, 2, ',', '.') }} ₺
                            </span>
                            @if($odeme->durum === 'beklemede')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-800 border border-amber-200">Beklemede</span>
                            @elseif($odeme->durum === 'kismi_odeme')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-800 border border-blue-200">Kısmi Ödeme</span>
                            @elseif($odeme->durum === 'odendi')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">Ödendi</span>
                            @elseif($odeme->durum === 'iptal')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-red-50 text-red-800 border border-red-200">İptal</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-[#6B7280]">
                        Kayıtlı gelir hareketi bulunmuyor.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Latest Expenses -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold font-display text-[#111827]">Son Gider Kayıtları</h3>
                <a href="{{ route('hekim.finans.giderler') }}" class="text-xs font-semibold text-[#C96A2B] hover:underline">Tümünü Gör</a>
            </div>
            <div class="divide-y divide-[#F3F4F6]">
                @forelse($sonGiderler as $gider)
                    <div class="py-3.5 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-semibold text-[#111827]">{{ $gider->baslik }}</span>
                            <span class="block text-xs text-[#6B7280] mt-0.5">
                                @php
                                    $kategoriIsimleri = [
                                        'kira' => 'Kira',
                                        'personel' => 'Personel',
                                        'malzeme' => 'Malzeme',
                                        'ekipman' => 'Ekipman',
                                        'vergi' => 'Vergi',
                                        'sigorta' => 'Sigorta',
                                        'diger' => 'Diğer'
                                    ];
                                @endphp
                                {{ $kategoriIsimleri[$gider->kategori] ?? 'Diğer' }} • {{ $gider->tarih->format('d.m.Y') }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="block text-sm font-bold text-rose-600">
                                -{{ number_format($gider->tutar, 2, ',', '.') }} ₺
                            </span>
                            @if($gider->belge_yolu)
                                <a href="{{ asset($gider->belge_yolu) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-semibold text-[#C96A2B] hover:underline mt-0.5">
                                    📄 Belgeyi Gör
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-[#6B7280]">
                        Kayıtlı gider hareketi bulunmuyor.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ApexCharts JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // 1. Trend Chart
            var trendOptions = {
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif'
                },
                series: [{
                    name: 'Tahsil Edilen Gelir',
                    data: {!! json_encode($incomeTrends) !!}
                }, {
                    name: 'Gider',
                    data: {!! json_encode($expenseTrends) !!}
                }],
                colors: ['#10B981', '#EF4444'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2.5 },
                xaxis: {
                    categories: {!! json_encode($months) !!},
                    labels: { style: { colors: '#6B7280', fontSize: '11px' } }
                },
                yaxis: {
                    labels: {
                        formatter: function (val) { return val.toLocaleString('tr-TR') + ' ₺'; },
                        style: { colors: '#6B7280', fontSize: '11px' }
                    }
                },
                tooltip: {
                    y: { formatter: function (val) { return val.toLocaleString('tr-TR') + ' ₺'; } }
                },
                grid: { borderColor: '#F3F4F6' }
            };
            var trendChart = new ApexCharts(document.querySelector("#trendChart"), trendOptions);
            trendChart.render();

            // 2. Service Distribution Chart
            @if(count($hizmetValues) > 0)
            var hizmetOptions = {
                chart: {
                    type: 'donut',
                    height: 220,
                    fontFamily: 'Inter, sans-serif'
                },
                series: {!! json_encode($hizmetValues) !!},
                labels: {!! json_encode($hizmetLabels) !!},
                colors: ['#C96A2B', '#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899'],
                legend: { position: 'bottom', fontSize: '11px' },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Toplam',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('tr-TR') + ' ₺';
                                    }
                                }
                            }
                        }
                    }
                }
            };
            var hizmetChart = new ApexCharts(document.querySelector("#hizmetChart"), hizmetOptions);
            hizmetChart.render();
            @endif

            // 3. Expense Distribution Chart
            @if(count($giderValues) > 0)
            var giderOptions = {
                chart: {
                    type: 'donut',
                    height: 220,
                    fontFamily: 'Inter, sans-serif'
                },
                series: {!! json_encode($giderValues) !!},
                labels: {!! json_encode($giderLabels) !!},
                colors: ['#EF4444', '#F59E0B', '#3B82F6', '#8B5CF6', '#EC4899', '#10B981', '#6B7280'],
                legend: { position: 'bottom', fontSize: '11px' },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Toplam',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('tr-TR') + ' ₺';
                                    }
                                }
                            }
                        }
                    }
                }
            };
            var giderChart = new ApexCharts(document.querySelector("#giderChart"), giderOptions);
            giderChart.render();
            @endif
        });
    </script>
@endsection
