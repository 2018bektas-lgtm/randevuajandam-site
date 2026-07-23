@extends('hekim.layout')

@section('baslik', 'Hasta Kayıtları - Hekim Paneli')
@section('sayfa_baslik', 'Kayıtlı Hastalarım')

@section('icerik')
    <!-- Patients Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-[0_4px_24px_rgba(31,41,55,0.04)] overflow-hidden">
        <div class="p-6 border-b border-[#E5E7EB] flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Sistemde Kayıtlı Hasta Listeniz</h3>
            <span class="text-xs text-[#6B7280] font-medium">Toplam {{ $hastalar->total() }} hasta</span>
        </div>

        @if($hastalar->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-16 h-16 text-[#9CA3AF] mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A11.386 11.386 0 0110.089 21c-2.243 0-4.32-.647-6.07-1.758v-.19a6 6 0 0111.411-2.911M15 12a3 3 0 11-6 0 3 3 0 016 0zm6.375-1.5a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-8.25-3a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                </svg>
                <h4 class="text-sm font-bold text-[#111827] font-display">Henüz kayıtlı hastanız bulunmuyor</h4>
                <p class="text-xs text-[#6B7280] mt-1">Hastalarınız hekim profilinizden online randevu aldığında burada listelenecektir.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                            <th class="px-6 py-4">Hasta Adı Soyadı</th>
                            <th class="px-6 py-4">Telefon Numarası</th>
                            <th class="px-6 py-4">E-Posta Adresi</th>
                            <th class="px-6 py-4">Sizden Aldığı Randevu Sayısı</th>
                            <th class="px-6 py-4">Kayıt Durumu</th>
                            <th class="px-6 py-4">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] text-xs text-[#4B5563]">
                        @foreach($hastalar as $hasta)
                            <tr class="hover:bg-[#FAFAFA]/75 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[#111827] font-display">
                                        {{ $hasta->ad }} {{ $hasta->soyad }}
                                    </div>
                                    <div class="text-[10px] text-[#6B7280] mt-0.5">Üye No: #{{ $hasta->id }}</div>
                                </td>
                                <td class="px-6 py-4 font-medium">
                                    {{ $hasta->telefon }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $hasta->e_posta }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center justify-center px-3 py-1 text-xs font-bold text-[#C96A2B] bg-[#FFF7ED] rounded-full border border-[#E7B58A]/30 font-display">
                                        {{ $hasta->randevular_count }} Randevu
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($hasta->aktif_mi)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif Üye
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-50 text-slate-500 border border-slate-200">
                                            Pasif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('hekim.finans.hasta-hesap', $hasta->id) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-white bg-[#C96A2B] hover:bg-[#b05a22] rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
                                        </svg>
                                        Cari Hesap
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($hastalar->hasPages())
                <div class="p-6 border-t border-[#E5E7EB]">
                    {{ $hastalar->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
