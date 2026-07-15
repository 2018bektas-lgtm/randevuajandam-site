@extends('klinik.layout')
@section('baslik', 'Klinik Raporlama ve İstatistikler')
@section('sayfa_baslik', 'Klinik İstatistik & Raporları')

@section('icerik')
<div class="space-y-6">
    <!-- Filter Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="GET" action="{{ route('hekim.klinik.raporlar') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end flex-grow">
            <div class="space-y-1">
                <label for="baslangic" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Başlangıç Tarihi</label>
                <input type="date" name="baslangic" id="baslangic" value="{{ $baslangic }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-2.5 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
            </div>
            <div class="space-y-1">
                <label for="bitis" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Bitiş Tarihi</label>
                <input type="date" name="bitis" id="bitis" value="{{ $bitis }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-2.5 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-grow bg-[#1E3A5F] hover:bg-[#152a47] text-white font-bold text-xs uppercase tracking-wider py-3 px-6 rounded-xl transition-all">Filtrele</button>
                <a href="{{ route('hekim.klinik.raporlar') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs uppercase tracking-wider py-3 px-4 rounded-xl transition-all flex items-center justify-center">Bu Ay</a>
            </div>
        </form>

        <div class="flex-shrink-0">
            <a href="{{ route('hekim.klinik.raporlar.pdf', ['baslangic' => $baslangic, 'bitis' => $bitis]) }}" 
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-sm transition-all duration-200 shadow-sm hover:shadow-md select-none group">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path>
                </svg>
                PDF İndir
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Toplam Randevu</span>
            <span class="text-3xl font-bold font-display text-slate-800 mt-2 block">{{ $toplamRandevu }}</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Seçilen dönemdeki toplam randevu</span>
        </div>

        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Onaylı / Tamamlanan</span>
            <span class="text-3xl font-bold font-display text-emerald-600 mt-2 block">
                {{ ($durumDagilimi['onaylandi'] ?? 0) + ($durumDagilimi['tamamlandi'] ?? 0) }}
            </span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Gerçekleşen veya onaylı randevular</span>
        </div>

        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Bekleyen Talepler</span>
            <span class="text-3xl font-bold font-display text-amber-500 mt-2 block">
                {{ $durumDagilimi['beklemede'] ?? 0 }}
            </span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium font-display">Onay bekleyen online talepler</span>
        </div>

        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">İptal / Gelmedi</span>
            <span class="text-3xl font-bold font-display text-rose-500 mt-2 block">
                {{ ($durumDagilimi['iptal'] ?? 0) + ($durumDagilimi['gelmedi'] ?? 0) }}
            </span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">İptal edilen veya gelinmeyen randevular</span>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Status Distribution Donut -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <h3 class="text-sm font-bold font-display text-[#111827] uppercase tracking-wider mb-4">Randevu Durum Dağılımı</h3>
            @if($toplamRandevu > 0)
                <div id="statusChart" class="w-full h-[300px]"></div>
            @else
                <div class="h-[300px] flex items-center justify-center text-xs text-[#6B7280]">
                    Bu dönemde randevu kaydı bulunmuyor.
                </div>
            @endif
        </div>

        <!-- Doctor Workload Bar Chart -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <h3 class="text-sm font-bold font-display text-[#111827] uppercase tracking-wider mb-4">Hekim Bazlı Randevu Dağılımı</h3>
            @if($toplamRandevu > 0)
                <div id="doctorWorkloadChart" class="w-full h-[300px]"></div>
            @else
                <div class="h-[300px] flex items-center justify-center text-xs text-[#6B7280]">
                    Bu dönemde randevu kaydı bulunmuyor.
                </div>
            @endif
        </div>

        <!-- Patient Growth Chart -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <h3 class="text-sm font-bold font-display text-[#111827] uppercase tracking-wider mb-4">Yeni Hasta Kayıt Trendi (Son 12 Ay)</h3>
            <div id="patientGrowthChart" class="w-full h-[300px]"></div>
        </div>

        <!-- Revenue vs Expense Comparison -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <h3 class="text-sm font-bold font-display text-[#111827] uppercase tracking-wider mb-4">Gelir / Gider Karşılaştırması (Son 6 Ay)</h3>
            <div id="revenueExpenseChart" class="w-full h-[300px]"></div>
        </div>
    </div>

    <!-- Popular Services Table -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-[#E5E7EB]">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">En Çok Tercih Edilen Hizmetler</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                        <th class="px-6 py-4">Hizmet Adı</th>
                        <th class="px-6 py-4 text-center">Talep Sayısı</th>
                        <th class="px-6 py-4 text-right">Tahmini Ciro (Hizmet Fiyatı × Adet)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-xs text-[#4B5563]">
                    @forelse($populerHizmetler as $hizmet)
                        <tr class="hover:bg-[#FAFAFA]/75 transition-colors">
                            <td class="px-6 py-4 font-bold text-[#111827] font-display">{{ $hizmet->hizmet_ad }}</td>
                            <td class="px-6 py-4 text-center font-semibold">{{ $hizmet->adet }} Kez</td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-600">₺{{ number_format($hizmet->tahmini_gelir, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-[#6B7280]">Bu dönemde randevu alınmış bir hizmet bulunmuyor.</td>
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
        @if($toplamRandevu > 0)
        // 1. Status Chart
        const statusData = @json($durumDagilimi);
        const statusOptions = {
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'Inter, sans-serif'
            },
            labels: ['Beklemede', 'Onaylandı', 'Reddedildi', 'İptal', 'Gelmedi', 'Tamamlandı'],
            series: [
                statusData.beklemede || 0,
                statusData.onaylandi || 0,
                statusData.reddedildi || 0,
                statusData.iptal || 0,
                statusData.gelmedi || 0,
                statusData.tamamlandi || 0
            ],
            colors: ['#F59E0B', '#10B981', '#EF4444', '#6B7280', '#EC4899', '#3B82F6'],
            legend: {
                position: 'bottom'
            },
            dataLabels: {
                enabled: true,
                formatter: function (val, opts) {
                    return opts.w.globals.series[opts.seriesIndex]
                }
            }
        };
        new ApexCharts(document.querySelector("#statusChart"), statusOptions).render();

        // 2. Doctor Workload Chart
        const docData = @json($doktorRandevuSayilari);
        const docOptions = {
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    horizontal: true,
                    distributed: true
                }
            },
            series: [{
                name: 'Randevu Sayısı',
                data: docData.map(item => item.adet)
            }],
            xaxis: {
                categories: docData.map(item => item.ad_soyad)
            },
            colors: ['#1E3A5F', '#C96A2B', '#10B981', '#3B82F6', '#8B5CF6', '#F59E0B'],
            legend: { show: false },
            dataLabels: {
                enabled: true
            }
        };
        new ApexCharts(document.querySelector("#doctorWorkloadChart"), docOptions).render();
        @endif

        // 3. Patient Growth Chart
        const patientData = @json($hastaBuyume);
        const patientOptions = {
            chart: {
                type: 'line',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            stroke: {
                curve: 'smooth',
                width: 3.5
            },
            series: [{
                name: 'Yeni Kayıtlı Hasta',
                data: patientData.map(item => item.adet)
            }],
            xaxis: {
                categories: patientData.map(item => item.ay)
            },
            colors: ['#C96A2B'],
            markers: {
                size: 5,
                colors: ['#C96A2B']
            },
            grid: {
                borderColor: '#F1F5F9'
            }
        };
        new ApexCharts(document.querySelector("#patientGrowthChart"), patientOptions).render();

        // 4. Revenue vs Expense Chart
        const compareData = @json($finansKarsilastirma);
        const compareOptions = {
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            series: [{
                name: 'Tahsil Edilen Gelir',
                data: compareData.map(item => item.gelir)
            }, {
                name: 'Klinik Gideri',
                data: compareData.map(item => item.gider)
            }],
            xaxis: {
                categories: compareData.map(item => item.ay)
            },
            colors: ['#10B981', '#EF4444'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '55%'
                }
            },
            grid: {
                borderColor: '#F1F5F9'
            }
        };
        new ApexCharts(document.querySelector("#revenueExpenseChart"), compareOptions).render();
    });
</script>
@endsection
