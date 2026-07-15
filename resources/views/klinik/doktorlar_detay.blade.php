@extends('klinik.layout')

@section('baslik', $doc->ad_soyad . ' - Hekim Detayı & Performans')
@section('sayfa_baslik', 'Hekim Detay & Raporları')

@section('icerik')
    <div class="space-y-6">
        <!-- Back Link -->
        <div>
            <a href="{{ route('hekim.klinik.doktorlar') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#6B7280] hover:text-[#111827] transition-colors">
                ← Hekim Listesine Dön
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Doctor Profile Card -->
            <div class="lg:col-span-1 space-y-6">
                <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                    <div class="flex flex-col items-center text-center pb-6 border-b border-[#F5F5F4] mb-6">
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
                        @if($doc->profil_resmi)
                            <img src="{{ asset($doc->profil_resmi) }}" alt="{{ $doc->ad_soyad }}" class="w-20 h-20 rounded-2xl object-cover border border-[#E5E7EB] shadow-sm mb-4">
                        @else
                            <div class="w-20 h-20 rounded-2xl bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-2xl font-bold font-display mb-4">
                                {{ $kisaAd }}
                            </div>
                        @endif

                        <h3 class="text-base font-bold font-display text-[#111827]">{{ $doc->unvan ? $doc->unvan . ' ' : '' }}{{ $doc->ad_soyad }}</h3>
                        <span class="text-xs text-[#6B7280] mt-1">{{ $doc->uzmanlik_alani }}</span>

                        <div class="flex gap-2 mt-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/20">
                                {{ $doc->klinik_rolu === 'sahip' ? 'Klinik Sahibi' : 'Klinik Hekimi' }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $doc->klinik_aktif_mi ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                {{ $doc->klinik_aktif_mi ? 'Aktif' : 'Pasif' }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-4 text-xs font-semibold">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase">E-posta</span>
                            <span class="text-[#111827] mt-1 block">{{ $doc->e_posta }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase">Telefon</span>
                            <span class="text-[#111827] mt-1 block">{{ $doc->telefon }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase">Hakediş Komisyon Oranı</span>
                            <span class="text-[#111827] mt-1 block">%{{ number_format($doc->komisyon_orani, 2, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase">Klinike Katılma Tarihi</span>
                            <span class="text-[#6B7280] mt-1 block">{{ $doc->klinik_katilma_tarihi ? $doc->klinik_katilma_tarihi->format('d.m.Y H:i') : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 2 Columns: Stats, Chart and Appointments -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Stats Overview -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                        <span class="text-[10px] font-bold text-[#6B7280] uppercase tracking-wider block">Bu Ay Randevu</span>
                        <span class="text-2xl font-extrabold text-[#111827] mt-1 block font-display">{{ $buAyRandevuSayisi }}</span>
                    </div>

                    <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                        <span class="text-[10px] font-bold text-[#6B7280] uppercase tracking-wider block">Bu Ay Gelir</span>
                        <span class="text-2xl font-extrabold text-[#111827] mt-1 block font-display">₺{{ number_format($buAyGelir, 2, ',', '.') }}</span>
                    </div>

                    <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                        <span class="text-[10px] font-bold text-[#6B7280] uppercase tracking-wider block">Ortalama Puan</span>
                        <span class="text-2xl font-extrabold text-amber-500 mt-1 block font-display">
                            {{ $doc->ortalama_puan ? $doc->ortalama_puan . ' ★' : 'Yok' }}
                        </span>
                    </div>
                </div>

                <!-- Monthly Revenue Chart -->
                <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                    <h4 class="text-sm font-bold font-display text-[#111827] mb-4">Son 6 Aylık Klinik Ciro Grafiği</h4>
                    <div class="h-64">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <!-- Son 10 Randevu -->
                <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                    <h4 class="text-sm font-bold font-display text-[#111827] mb-4">Son 10 Randevusu</h4>
                    
                    @if($sonRandevular->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                                        <th class="pb-2.5 font-display">Hasta</th>
                                        <th class="pb-2.5 font-display">Hizmet</th>
                                        <th class="pb-2.5 font-display">Tarih</th>
                                        <th class="pb-2.5 font-display text-right">Ücret</th>
                                        <th class="pb-2.5 font-display text-center">Durum</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5E7EB]">
                                    @foreach($sonRandevular as $randevu)
                                        <tr class="text-xs text-[#4B5563]">
                                            <td class="py-3 font-semibold text-[#111827]">{{ $randevu->hasta->ad_soyad ?? 'Bilinmeyen Hasta' }}</td>
                                            <td class="py-3">{{ $randevu->hizmet->ad ?? 'Genel Randevu' }}</td>
                                            <td class="py-3 font-medium">
                                                {{ \Carbon\Carbon::parse($randevu->tarih)->format('d.m.Y') }} {{ substr($randevu->saat, 0, 5) }}
                                            </td>
                                            <td class="py-3 font-bold text-[#111827] text-right">
                                                ₺{{ number_format($randevu->ucret, 2, ',', '.') }}
                                            </td>
                                            <td class="py-3 text-center">
                                                <span class="inline-block px-2 py-0.5 rounded text-[9px] font-extrabold uppercase border
                                                    @if($randevu->durum === 'onaylandi') bg-emerald-50 text-emerald-700 border-emerald-200
                                                    @elseif($randevu->durum === 'tamamlandi') bg-blue-50 text-blue-700 border-blue-200
                                                    @elseif($randevu->durum === 'iptal') bg-red-50 text-red-700 border-red-200
                                                    @else bg-amber-50 text-amber-700 border-amber-200 @endif">
                                                    @if($randevu->durum === 'onaylandi') Onaylı
                                                    @elseif($randevu->durum === 'tamamlandi') Tamamlandı
                                                    @elseif($randevu->durum === 'iptal') İptal
                                                    @else Bekliyor @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-[#6B7280] py-4 text-center">Bu hekime ait geçmiş randevu kaydı bulunmamaktadır.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($aylar) !!},
                    datasets: [{
                        label: 'Klinik Gelir (₺)',
                        data: {!! json_encode($gelirler) !!},
                        borderColor: '#C96A2B',
                        backgroundColor: 'rgba(201, 106, 43, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#F3F4F6' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
@endsection
