@extends('klinik.layout')
@section('baslik', 'Finansal Genel Bakış')
@section('sayfa_baslik', 'Klinik Finansal Analiz')

@section('icerik')
<div class="space-y-6">
    <!-- Filter Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm">
        <form method="GET" action="{{ route('hekim.klinik.finans') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="space-y-1">
                <label for="baslangic" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Başlangıç Tarihi</label>
                <input type="date" name="baslangic" id="baslangic" value="{{ $baslangic }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-2.5 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
            </div>
            <div class="space-y-1">
                <label for="bitis" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Bitiş Tarihi</label>
                <input type="date" name="bitis" id="bitis" value="{{ $bitis }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-2.5 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-[#1E3A5F] hover:bg-[#152a47] text-white font-bold text-xs uppercase tracking-wider py-3 px-6 rounded-xl transition-all">Filtrele</button>
                <a href="{{ route('hekim.klinik.finans') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs uppercase tracking-wider py-3 px-6 rounded-xl transition-all flex items-center justify-center">Bu Ay</a>
            </div>
        </form>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Stat Card 1: Toplam Gelir -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Toplam Tahsilat</span>
            <span class="text-3xl font-bold font-display text-emerald-600 mt-2 block">₺{{ number_format($toplamGelir, 2, ',', '.') }}</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Seçilen dönemdeki tahsil edilen tutar</span>
        </div>

        <!-- Stat Card 2: Toplam Gider -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Klinik Gideri</span>
            <span class="text-3xl font-bold font-display text-rose-600 mt-2 block">₺{{ number_format($toplamGider, 2, ',', '.') }}</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Seçilen dönemdeki toplam gider</span>
        </div>

        <!-- Stat Card 3: Net Kar -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Net Kâr</span>
            <span class="text-3xl font-bold font-display {{ $netKar >= 0 ? 'text-emerald-700' : 'text-rose-700' }} mt-2 block">₺{{ number_format($netKar, 2, ',', '.') }}</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Gelir - Gider farkı</span>
        </div>

        <!-- Stat Card 4: Bekleyen Alacak -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Bekleyen Alacak</span>
            <span class="text-3xl font-bold font-display text-amber-600 mt-2 block">₺{{ number_format($bekleyenOdeme, 2, ',', '.') }}</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Kısmi veya onay bekleyen alacaklar</span>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Trend Line Chart (2/3 width) -->
        <div class="lg:col-span-2 p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <h3 class="text-sm font-bold font-display text-[#111827] uppercase tracking-wider mb-4">Aylık Trend Analizi (Son 6 Ay)</h3>
            <div id="trendChart" class="w-full h-[320px]"></div>
        </div>

        <!-- Doctor Revenue Distribution Pie Chart (1/3 width) -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <h3 class="text-sm font-bold font-display text-[#111827] uppercase tracking-wider mb-4">Hekim Bazlı Gelir Dağılımı</h3>
            @if(count($dagilim) > 0)
                <div id="distributionChart" class="w-full h-[320px]"></div>
            @else
                <div class="h-[320px] flex items-center justify-center text-xs text-[#6B7280]">
                    Bu dönemde gelir dağılımı bulunmuyor.
                </div>
            @endif
        </div>
    </div>

    <!-- Doctor Revenue Details Table -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-[#E5E7EB]">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Hekim Gelir Listesi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                        <th class="px-6 py-4">Hekim</th>
                        <th class="px-6 py-4 text-right">Toplam Gelir</th>
                        <th class="px-6 py-4 text-right">Gelir Payı (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-xs text-[#4B5563]">
                    @forelse($dagilim as $item)
                        <tr class="hover:bg-[#FAFAFA]/75 transition-colors">
                            <td class="px-6 py-4 font-bold text-[#111827] font-display">{{ $item['ad_soyad'] }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-600">₺{{ number_format($item['tutar'], 2, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="font-bold text-gray-700">%{{ $item['oran'] }}</span>
                                    <div class="w-20 bg-gray-150 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-[#1E3A5F] h-full" style="width: {{ $item['oran'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-[#6B7280]">Bu dönemde gelir kaydeden hekim bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // 1. Trend Chart (Gelir vs Gider - Son 6 Ay)
        const trendData = @json($aylikTrend);
        const categories = trendData.map(item => item.ay);
        const gelirData = trendData.map(item => item.gelir);
        const giderData = trendData.map(item => item.gider);

        var trendOptions = {
            chart: {
                type: 'area',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            series: [{
                name: 'Tahsil Edilen Gelir',
                data: gelirData
            }, {
                name: 'Klinik Gideri',
                data: giderData
            }],
            colors: ['#10B981', '#EF4444'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2.5 },
            xaxis: {
                categories: categories,
                labels: { style: { colors: '#6B7280', fontSize: '11px' } }
            },
            yaxis: {
                labels: {
                    formatter: function (val) { return '₺' + val.toLocaleString('tr-TR'); },
                    style: { colors: '#6B7280', fontSize: '11px' }
                }
            },
            tooltip: {
                y: { formatter: function (val) { return '₺' + val.toLocaleString('tr-TR'); } }
            },
            grid: { borderColor: '#F3F4F6' }
        };
        var trendChart = new ApexCharts(document.querySelector("#trendChart"), trendOptions);
        trendChart.render();

        // 2. Doctor Revenue Distribution Donut Chart
        @if(count($dagilim) > 0)
        const distributionData = @json($dagilim);
        const docLabels = distributionData.map(item => item.ad_soyad);
        const docValues = distributionData.map(item => item.tutar);

        var distOptions = {
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'Inter, sans-serif'
            },
            series: docValues,
            labels: docLabels,
            colors: ['#1E3A5F', '#C96A2B', '#10B981', '#3B82F6', '#F59E0B', '#8B5CF6', '#EC4899'],
            legend: { position: 'bottom', fontSize: '11px' },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Toplam',
                                formatter: function (w) {
                                    return '₺' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('tr-TR');
                                }
                            }
                        }
                    }
                }
            }
        };
        var distChart = new ApexCharts(document.querySelector("#distributionChart"), distOptions);
        distChart.render();
        @endif
    });
</script>
@endsection
