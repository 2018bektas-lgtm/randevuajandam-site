@extends('klinik.layout')

@section('baslik', 'Hasta Havuzu - ' . $klinik->ad)
@section('sayfa_baslik', 'Ortak Hasta Havuzu')

@section('icerik')
    <div class="space-y-6">
        <!-- Header Banner -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <h3 class="text-lg font-bold font-display text-[#111827]">Klinik Hasta Havuzu</h3>
            <p class="text-xs text-[#6B7280] mt-1">
                Kliniğin ortak hasta havuzunda kayıtlı olan hastaları aşağıda listeleyebilirsiniz.
            </p>
        </div>

        <!-- Patients List -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            @if($hastalar->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                                <th class="pb-3 font-display">Ad Soyad</th>
                                <th class="pb-3 font-display">E-posta</th>
                                <th class="pb-3 font-display">Telefon</th>
                                <th class="pb-3 font-display">Kayıt Tarihi (Klinik)</th>
                                <th class="pb-3 font-display">Notlar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB]">
                            @foreach($hastalar as $hasta)
                                <tr class="text-sm text-[#4B5563]">
                                    <td class="py-3.5 font-semibold text-[#111827]">{{ $hasta->ad_soyad }}</td>
                                    <td class="py-3.5 text-xs">{{ $hasta->e_posta }}</td>
                                    <td class="py-3.5 text-xs">{{ $hasta->telefon ?: '-' }}</td>
                                    <td class="py-3.5 text-xs text-[#6B7280]">
                                        @if($hasta->pivot && $hasta->pivot->kayit_tarihi)
                                            {{ \Carbon\Carbon::parse($hasta->pivot->kayit_tarihi)->format('d.m.Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-3.5 text-xs text-[#6B7280] max-w-[200px] truncate" title="{{ $hasta->pivot->notlar ?? '' }}">
                                        {{ $hasta->pivot->notlar ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $hastalar->links() }}
                </div>
            @else
                <p class="text-xs text-[#6B7280] py-6 text-center">Klinik ortak hasta havuzunda henüz kayıtlı hasta bulunmamaktadır.</p>
            @endif
        </div>
    </div>
@endsection
