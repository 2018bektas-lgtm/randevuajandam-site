@extends('yonetim.layout')

@section('baslik', 'Unvan Yönetimi - Randevu Ajandam')
@section('sayfa_baslik', 'Unvan Yönetimi')

@section('icerik')
    <!-- Top Action Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
        <div>
            <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                Mesleki Unvanlar
            </h2>
            <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Hekimlerin profil bilgilerinde kullanabileceği resmi mesleki unvanlar.</p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('yonetim.unvanlar.ekle') }}" 
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-sm transition-all duration-200 shadow-sm hover:shadow-md select-none group">
                <svg class="w-4 h-4 transform group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                </svg>
                Unvan Ekle
            </a>
        </div>
    </div>

    <!-- Table Card Container -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Table Search Bar -->
        <div class="p-5 border-b border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:max-w-xl">
                <!-- Search -->
                <div class="relative max-w-xs w-full">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" id="tableSearch" placeholder="Unvan ara..." 
                           class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                </div>
                
                <!-- PerPage Selector -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-[#6B7280] whitespace-nowrap">Sayfada:</span>
                    <select id="perPageSelect" class="px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#4B5563] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] font-semibold transition-all cursor-pointer">
                        <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
            </div>
            <div class="text-xs text-[#6B7280]">
                Toplam <span class="font-bold text-[#111827]">{{ $unvanlar->total() }}</span> unvan tanımlı.
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-[#E5E7EB]">
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Unvan Adı</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Bu Unvana Sahip Hekimler</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]" id="unvanTableBody">
                    @foreach($unvanlar as $u)
                        @php
                            $doktorSayisi = \App\Models\Doktor::where('unvan', $u->ad)->count();
                        @endphp
                        <tr class="hover:bg-slate-50/30 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center font-bold text-xs font-display">
                                        {{ mb_strtoupper(mb_substr($u->ad, 0, 2)) }}
                                    </div>
                                    <span class="block text-sm font-bold text-[#111827] font-display">{{ $u->ad }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#4B5563] font-medium">
                                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">
                                    {{ $doktorSayisi }} Hekim
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                <div class="inline-flex items-center gap-2">
                                    <!-- Edit Button -->
                                    <a href="{{ route('yonetim.unvanlar.duzenle', $u->id) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] hover:border-[#E7B58A]/40 transition-all duration-200 shadow-sm" 
                                       title="Düzenle">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        Düzenle
                                    </a>

                                    <!-- Delete Button -->
                                    <form action="{{ route('yonetim.unvanlar.sil', $u->id) }}" method="POST" class="inline" onsubmit="onayModalAc(event, this, 'Bu unvanı silmek istediğinize emin misiniz?');">
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
        
        @if($unvanlar->isEmpty())
            <div class="p-12 text-center text-sm text-[#6B7280] bg-slate-50/30">
                Kayıtlı unvan bulunamadı.
            </div>
        @else
            <!-- Pagination Links -->
            @if ($unvanlar->hasPages())
                <div class="p-5 border-t border-[#E5E7EB] bg-slate-50/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-xs text-[#6B7280]">
                            <span class="font-semibold text-[#111827]">{{ $unvanlar->firstItem() ?? 0 }}</span> ile <span class="font-semibold text-[#111827]">{{ $unvanlar->lastItem() ?? 0 }}</span> arası gösteriliyor (Toplam <span class="font-bold text-[#111827]">{{ $unvanlar->total() }}</span> kayıttan)
                        </p>
                    </div>
                    <div>
                        <nav class="inline-flex rounded-xl -space-x-px shadow-sm" aria-label="Pagination">
                            <!-- Önceki Sayfa Linki -->
                            @if ($unvanlar->onFirstPage())
                                <span class="inline-flex items-center px-3.5 py-2.5 rounded-l-xl border border-[#E5E7EB] bg-white text-xs font-semibold text-gray-300 cursor-default select-none">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </span>
                            @else
                                <a href="{{ $unvanlar->previousPageUrl() }}" class="inline-flex items-center px-3.5 py-2.5 rounded-l-xl border border-[#E5E7EB] bg-white text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] hover:bg-slate-50 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </a>
                            @endif

                            <!-- Sayfa Numaraları -->
                            @foreach ($unvanlar->getUrlRange(1, $unvanlar->lastPage()) as $page => $url)
                                @if ($page == $unvanlar->currentPage())
                                    <span class="inline-flex items-center px-4 py-2.5 border border-[#C96A2B] bg-[#C96A2B] text-xs font-bold text-white select-none">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center px-4 py-2.5 border border-[#E5E7EB] bg-white text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] hover:bg-slate-50 transition-colors">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            <!-- Sonraki Sayfa Linki -->
                            @if ($unvanlar->hasMorePages())
                                <a href="{{ $unvanlar->nextPageUrl() }}" class="inline-flex items-center px-3.5 py-2.5 rounded-r-xl border border-[#E5E7EB] bg-white text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] hover:bg-slate-50 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            @else
                                <span class="inline-flex items-center px-3.5 py-2.5 rounded-r-xl border border-[#E5E7EB] bg-white text-xs font-semibold text-gray-300 cursor-default select-none">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            @endif
                        </nav>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Search & PerPage JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableSearch = document.getElementById('tableSearch');
            const rows = document.querySelectorAll('#unvanTableBody tr');
            const perPageSelect = document.getElementById('perPageSelect');

            if (tableSearch) {
                tableSearch.addEventListener('input', function() {
                    const query = this.value.toLowerCase().trim();
                    rows.forEach(row => {
                        const cellsText = row.innerText.toLowerCase();
                        if (cellsText.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('per_page', this.value);
                    url.searchParams.set('page', 1); // Reset page to 1
                    window.location.href = url.toString();
                });
            }
        });
    </script>
@endsection
