@extends('yonetim.layout')

@section('baslik', 'Paket Yönetimi - Randevu Ajandam')
@section('sayfa_baslik', 'Paket Yönetimi')

@section('icerik')
    <!-- Top Action Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
        <div>
            <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                Paket Yönetimi
            </h2>
            <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Doktor ve klinikler için sunulan aylık ve yıllık üyelik paketleri.</p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('yonetim.paketler.ekle') }}" 
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-sm transition-all duration-200 shadow-sm hover:shadow-md select-none group">
                <svg class="w-4 h-4 transform group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                </svg>
                Paket Ekle
            </a>
        </div>
    </div>



    <!-- Table Card Container -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Table Search & Filter Bar -->
        <div class="p-5 border-b border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Search -->
            <div class="relative max-w-xs w-full">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" id="tableSearch" placeholder="Paket ara..." 
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
            </div>
            
            <!-- Filter -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-[#6B7280]">Tür:</span>
                    <select id="typeFilter" class="px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#4B5563] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] font-semibold transition-all cursor-pointer">
                        <option value="hepsi">Tümü</option>
                        <option value="bireysel">Bireysel</option>
                        <option value="klinik">Klinik</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-[#6B7280]">Durum:</span>
                    <select id="statusFilter" class="px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#4B5563] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] font-semibold transition-all cursor-pointer">
                        <option value="hepsi">Tümü</option>
                        <option value="aktif">Aktif</option>
                        <option value="pasif">Pasif</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-[#E5E7EB]">
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Paket Adı</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Tür</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Aylık Plan</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Yıllık Plan</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Özellikler</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Durum</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]" id="paketTableBody">
                    @foreach($paketler as $p)
                        <tr class="hover:bg-slate-50/30 transition-colors group" data-status="{{ $p->aktif_mi ? 'aktif' : 'pasif' }}" data-type="{{ $p->tur }}" data-name="{{ Str::slug($p->ad) }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center font-bold text-xs font-display">
                                        {{ mb_strtoupper(mb_substr($p->ad, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold text-[#111827] font-display">{{ $p->ad }}</span>
                                        <span class="block text-[11px] text-[#6B7280] mt-0.5 max-w-[180px] truncate" title="{{ $p->aciklama }}">{{ $p->aciklama ?? 'Açıklama belirtilmedi' }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                @if($p->tur === 'klinik')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider font-display border bg-[#FFF7ED] text-[#C96A2B] border-[#E7B58A]/30">
                                        Klinik
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider font-display border bg-gray-50 text-gray-600 border-gray-200">
                                        Bireysel
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#111827] font-bold font-display">
                                @if($p->aylik_indirimli_fiyat)
                                    <span class="text-[#C96A2B]">{{ number_format($p->aylik_indirimli_fiyat, 2, ',', '.') }} TL</span>
                                    <span class="block text-[10px] text-gray-400 line-through font-normal mt-0.5">{{ number_format($p->aylik_fiyat, 2, ',', '.') }} TL</span>
                                @else
                                    <span>{{ number_format($p->aylik_fiyat, 2, ',', '.') }} TL</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#111827] font-bold font-display">
                                @if($p->yillik_indirimli_fiyat)
                                    <span class="text-[#C96A2B]">{{ number_format($p->yillik_indirimli_fiyat, 2, ',', '.') }} TL</span>
                                    <span class="block text-[10px] text-gray-400 line-through font-normal mt-0.5">{{ number_format($p->yillik_fiyat, 2, ',', '.') }} TL</span>
                                @else
                                    <span>{{ number_format($p->yillik_fiyat, 2, ',', '.') }} TL</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-[#4B5563]">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-[#E5E7EB] font-semibold">
                                    {{ is_array($p->ozellikler) ? count($p->ozellikler) : 0 }} Özellik
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form action="{{ route('yonetim.paketler.durum', $p->id) }}" method="POST" class="inline">
                                    @csrf
                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" name="aktif_mi" value="1" 
                                               {{ $p->aktif_mi ? 'checked' : '' }} 
                                               onchange="this.form.submit()"
                                               class="sr-only peer">
                                        <!-- iOS-style Switch -->
                                        <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[2.5px] after:left-[2.5px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all after:duration-300 peer-checked:after:translate-x-4 shadow-inner"></div>
                                        <span class="ml-2 text-xs font-semibold {{ $p->aktif_mi ? 'text-[#C96A2B]' : 'text-slate-400' }}">
                                            {{ $p->aktif_mi ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </label>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                <div class="inline-flex items-center gap-2">
                                    
                                    <!-- Edit Button -->
                                    <a href="{{ route('yonetim.paketler.duzenle', $p->id) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] hover:border-[#E7B58A]/40 transition-all duration-200 shadow-sm" 
                                       title="Düzenle">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        Düzenle
                                    </a>

                                    <!-- Delete Button -->
                                    <form action="{{ route('yonetim.paketler.sil', $p->id) }}" method="POST" class="inline" onsubmit="onayModalAc(event, this, 'Bu paketi silmek istediğinize emin misiniz?');">
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

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($paketler->isEmpty())
            <div class="p-12 text-center text-sm text-[#6B7280] bg-slate-50/30">
                Kayıtlı abonelik paketi bulunamadı.
            </div>
        @endif
    </div>

    <!-- Search / Filter JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableSearch = document.getElementById('tableSearch');
            const statusFilter = document.getElementById('statusFilter');
            const typeFilter = document.getElementById('typeFilter');
            const rows = document.querySelectorAll('#paketTableBody tr');

            function filterTable() {
                const query = tableSearch.value.toLowerCase().trim();
                const filterVal = statusFilter.value;
                const typeVal = typeFilter.value;

                rows.forEach(row => {
                    const status = row.getAttribute('data-status') || '';
                    const type = row.getAttribute('data-type') || '';
                    const cellsText = row.innerText.toLowerCase();

                    const matchesSearch = cellsText.includes(query);
                    const matchesStatus = (filterVal === 'hepsi') || (status === filterVal);
                    const matchesType = (typeVal === 'hepsi') || (type === typeVal);

                    if (matchesSearch && matchesStatus && matchesType) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (tableSearch && statusFilter && typeFilter) {
                tableSearch.addEventListener('input', filterTable);
                statusFilter.addEventListener('change', filterTable);
                typeFilter.addEventListener('change', filterTable);
            }
        });
    </script>
@endsection
