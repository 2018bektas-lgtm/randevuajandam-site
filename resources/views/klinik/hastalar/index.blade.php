@extends('klinik.layout')

@section('baslik', 'Ortak Hasta Havuzu - ' . $klinik->ad)
@section('sayfa_baslik', 'Ortak Hasta Havuzu')

@section('icerik')
    @if(session('basari'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
            {{ session('basari') }}
        </div>
    @endif

    <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <!-- Search Filter -->
        <form method="GET" action="{{ route('hekim.klinik.hastalar.index') }}" class="mb-6 flex gap-3">
            <input type="text" name="q" value="{{ $q }}" placeholder="Hasta adı, e-posta veya telefon ile ara..." class="flex-1 bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-2.5 text-xs outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
            <button type="submit" class="bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl transition-colors">
                Filtrele
            </button>
            @if(!empty($q))
                <a href="{{ route('hekim.klinik.hastalar.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl transition-colors flex items-center justify-center">
                    Temizle
                </a>
            @endif
        </form>

        @if($hastalar->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                            <th class="pb-3 font-display">Ad Soyad</th>
                            <th class="pb-3 font-display">İletişim</th>
                            <th class="pb-3 font-display">Son Randevu</th>
                            <th class="pb-3 font-display">Hekim</th>
                            <th class="pb-3 font-display text-center">Toplam Randevu</th>
                            <th class="pb-3 text-right font-display">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @foreach($hastalar as $hasta)
                            <tr class="text-sm text-[#4B5563]">
                                <td class="py-3.5 font-semibold text-[#111827]">{{ $hasta->ad_soyad }}</td>
                                <td class="py-3.5 text-xs">
                                    <div>{{ $hasta->telefon }}</div>
                                    <div class="text-[#9CA3AF] mt-0.5">{{ $hasta->e_posta }}</div>
                                </td>
                                <td class="py-3.5 text-xs">
                                    @if($hasta->son_randevu)
                                        {{ \Carbon\Carbon::parse($hasta->son_randevu->tarih)->format('d.m.Y') }}
                                        <span class="text-[#C96A2B] font-semibold">{{ substr($hasta->son_randevu->saat, 0, 5) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3.5 text-xs font-semibold text-[#111827]">
                                    @if($hasta->son_randevu && $hasta->son_randevu->doktor)
                                        {{ $hasta->son_randevu->doktor->unvan ? $hasta->son_randevu->doktor->unvan . ' ' : '' }}{{ $hasta->son_randevu->doktor->ad_soyad }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3.5 text-center font-bold text-xs text-[#111827]">{{ $hasta->toplam_randevu }}</td>
                                <td class="py-3.5 text-right">
                                    <a href="{{ route('hekim.klinik.hastalar.show', $hasta->id) }}" class="px-3 py-1.5 bg-[#FFF7ED] hover:bg-[#FFEADB] text-[#C96A2B] text-xs font-bold rounded-lg transition-colors">
                                        Detay ve Geçmiş Gör
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $hastalar->appends(request()->query())->links() }}
            </div>
        @else
            <p class="text-xs text-[#6B7280] py-6 text-center">Klinik ortak hasta havuzunda aranan kriterlere uygun kayıtlı hasta bulunamadı.</p>
        @endif
    </div>
@endsection
