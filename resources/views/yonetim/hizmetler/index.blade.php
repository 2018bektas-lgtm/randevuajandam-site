@extends('yonetim.layout')

@section('baslik', 'Hizmet Yönetimi - Randevu Ajandam')
@section('sayfa_baslik', 'Hizmet Yönetimi')

@section('icerik')
    <!-- Top Action Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
        <div>
            <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                Tüm Hizmetler & Tedaviler
            </h2>
            <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Sistem genelinde hekimlerin sunduğu tüm hizmet ve tedavilerin listesi.</p>
        </div>
    </div>

    <!-- Table Card Container -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Table Search & Info Bar -->
        <div class="p-5 border-b border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:max-w-xl">
                <!-- Search -->
                <div class="relative max-w-xs w-full">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" id="tableSearch" placeholder="Hizmet veya hekim ara..." 
                           class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                </div>
            </div>
            <div class="text-xs text-[#6B7280]">
                Toplam <span class="font-bold text-[#111827]">{{ $hizmetler->total() }}</span> hizmet tanımlı.
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-[#E5E7EB]">
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Hizmet Adı</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Hekim</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Süre</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Fiyat</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Durum</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]" id="hizmetTableBody">
                    @foreach($hizmetler as $h)
                        <tr class="hover:bg-slate-50/30 transition-colors group">
                            <!-- Service Name -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    @if($h->resim)
                                        <img src="{{ asset($h->resim) }}" alt="{{ $h->ad }}" class="w-9 h-9 object-cover rounded-xl border border-slate-200 shadow-sm shrink-0">
                                    @else
                                        <div class="w-9 h-9 rounded-xl bg-orange-50 text-[#C96A2B] border border-[#E7B58A]/20 flex items-center justify-center font-bold text-xs font-display">
                                            {{ mb_strtoupper(mb_substr($h->ad, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <span class="block text-sm font-bold text-[#111827] font-display">{{ $h->ad }}</span>
                                        <span class="block text-[10px] text-[#6B7280] mt-0.5 max-w-xs truncate">{{ strip_tags($h->aciklama) }}</span>
                                    </div>
                                </div>
                            </td>
                            <!-- Doctor -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-[#111827] font-display">
                                    {{ $h->doktor->unvan }} {{ $h->doktor->ad_soyad }}
                                </div>
                                <div class="text-[10px] text-gray-500 mt-0.5">{{ $h->doktor->uzmanlik_alani }}</div>
                            </td>
                            <!-- Duration -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#4B5563] font-medium font-display">
                                {{ $h->sure }} Dk
                            </td>
                            <!-- Price -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#111827] font-bold font-display">
                                {{ number_format($h->fiyat, 2, ',', '.') }} TL
                            </td>
                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <form action="{{ route('yonetim.hizmetler.durum', $h->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-[10px] font-bold tracking-wide transition-all duration-200 cursor-pointer {{ $h->aktif_mi ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-red-50 text-red-600 border border-red-100' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $h->aktif_mi ? 'bg-emerald-500 animate-pulse' : 'bg-red-400' }}"></span>
                                        {{ $h->aktif_mi ? 'AKTİF' : 'PASİF' }}
                                    </button>
                                </form>
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                <form action="{{ route('yonetim.hizmetler.sil', $h->id) }}" method="POST" class="inline" onsubmit="onayModalAc(event, this, 'Bu hizmeti silmek istediğinize emin misiniz?');">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-100 bg-red-50/30 hover:bg-red-50 text-xs font-semibold text-red-600 hover:text-red-700 transition-all duration-200 cursor-pointer shadow-sm" 
                                            title="Sil">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Sil
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($hizmetler->isEmpty())
            <div class="p-12 text-center text-sm text-[#6B7280] bg-slate-50/30">
                Sistemde kayıtlı hizmet bulunamadı.
            </div>
        @else
            <!-- Pagination Links -->
            @if ($hizmetler->hasPages())
                <div class="p-5 border-t border-[#E5E7EB] bg-slate-50/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-xs text-[#6B7280]">
                            <span class="font-semibold text-[#111827]">{{ $hizmetler->firstItem() ?? 0 }}</span> ile <span class="font-semibold text-[#111827]">{{ $hizmetler->lastItem() ?? 0 }}</span> arası gösteriliyor (Toplam <span class="font-bold text-[#111827]">{{ $hizmetler->total() }}</span> kayıttan)
                        </p>
                    </div>
                    <div>
                        {{ $hizmetler->links('vendor.pagination.tailwind') }}
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Simple JS Client-Side filtering for Search -->
    <script>
        document.getElementById('tableSearch').addEventListener('input', function(e) {
            const searchVal = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#hizmetTableBody tr');
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if(text.includes(searchVal)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
@endsection
