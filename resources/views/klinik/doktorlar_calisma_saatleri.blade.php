@extends('klinik.layout')

@section('baslik', 'Hekim Çalışma Saatleri Tablosu - ' . $klinik->ad)
@section('sayfa_baslik', 'Çalışma Saatleri Tablosu')

@section('icerik')
    <div class="space-y-6">
        <div>
            <a href="{{ route('hekim.klinik.doktorlar') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#6B7280] hover:text-[#111827] transition-colors">
                ← Hekim Listesine Dön
            </a>
        </div>

        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <h3 class="text-lg font-bold font-display text-[#111827] mb-6">Tüm Hekimlerin Haftalık Çalışma Saatleri</h3>

            @if($doktorlar->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                                <th class="pb-3 font-display">Gün</th>
                                @foreach($doktorlar as $doc)
                                    <th class="pb-3 font-display">
                                        {{ $doc->unvan ? $doc->unvan . ' ' : '' }}{{ $doc->ad_soyad }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB]">
                            @php
                                $gunler = [
                                    1 => 'Pazartesi',
                                    2 => 'Salı',
                                    3 => 'Çarşamba',
                                    4 => 'Perşembe',
                                    5 => 'Cuma',
                                    6 => 'Cumartesi',
                                    7 => 'Pazar'
                                ];
                            @endphp

                            @foreach($gunler as $gunSayi => $gunAd)
                                <tr class="text-xs text-[#4B5563]">
                                    <td class="py-4 font-bold text-[#111827]">{{ $gunAd }}</td>
                                    @foreach($doktorlar as $doc)
                                        @php
                                            $saat = $doc->calismaSaatleri->firstWhere('gun', $gunSayi);
                                        @endphp
                                        <td class="py-4">
                                            @if($saat && $saat->aktif_mi)
                                                <div class="font-medium text-[#111827]">
                                                    {{ substr($saat->mesai_baslangic, 0, 5) }} - {{ substr($saat->mesai_bitis, 0, 5) }}
                                                </div>
                                                @if($saat->ogle_arasi_aktif_mi)
                                                    <div class="text-[9px] text-[#C96A2B] mt-0.5">
                                                        Öğle: {{ substr($saat->ogle_baslangic, 0, 5) }} - {{ substr($saat->ogle_bitis, 0, 5) }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-red-500 font-semibold uppercase text-[10px] tracking-wider">Kapalı</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-xs text-[#6B7280] py-4 text-center">Kliniğe ait aktif hekim bulunmamaktadır.</p>
            @endif
        </div>
    </div>
@endsection
