@extends('yonetim.layout')

@section('baslik', 'Yönetici Yönetimi - Randevu Ajandam')
@section('sayfa_baslik', 'Yönetici Yönetimi')

@section('icerik')
    <!-- Top Action Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
        <div>
            <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                Yöneticiler
            </h2>
            <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Sistem ayarlarına ve kullanıcı verilerine erişim yetkisi olan yöneticiler.</p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('yonetim.yoneticiler.ekle') }}" 
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-sm transition-all duration-200 shadow-sm hover:shadow-md select-none group">
                <svg class="w-4 h-4 transform group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                </svg>
                Yönetici Ekle
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
                <input type="text" id="tableSearch" placeholder="Yönetici ara..." 
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
            </div>
            
            <!-- Filter -->
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-[#6B7280]">Durum:</span>
                <select id="statusFilter" class="px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#4B5563] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] font-semibold transition-all cursor-pointer">
                    <option value="hepsi">Tümü</option>
                    <option value="aktif">Aktif</option>
                    <option value="pasif">Askıda</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-[#E5E7EB]">
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">İsim Soyisim</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">E-Posta Adresi</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Telefon</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Hesap Durumu</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]" id="yoneticiTableBody">
                    @foreach($yoneticiler as $y)
                        <tr class="hover:bg-slate-50/30 transition-colors group" data-status="{{ $y->aktif_mi ? 'aktif' : 'pasif' }}" data-name="{{ Str::slug($y->ad_soyad) }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <!-- Dynamic Initials Circle -->
                                    <div class="w-9 h-9 rounded-xl bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center font-bold text-xs font-display">
                                        {{ mb_strtoupper(mb_substr($y->ad_soyad, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="block text-sm font-bold text-[#111827] font-display">{{ $y->ad_soyad }}</span>
                                            @if($y->id === $yonetici->id)
                                                <span class="inline-block text-[9px] font-extrabold bg-[#C96A2B]/10 text-[#C96A2B] px-1.5 py-0.5 rounded uppercase">Ben</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#4B5563] font-medium">
                                {{ $y->e_posta }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#4B5563] font-medium">
                                {{ $y->telefon ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($y->id === $yonetici->id)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 select-none">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif (Ben)
                                    </span>
                                @else
                                    <form action="{{ route('yonetim.yoneticiler.durum', $y->id) }}" method="POST" class="inline">
                                        @csrf
                                        <label class="relative inline-flex items-center cursor-pointer select-none">
                                            <input type="checkbox" name="aktif_mi" value="1" 
                                                   {{ $y->aktif_mi ? 'checked' : '' }} 
                                                   onchange="this.form.submit()"
                                                   class="sr-only peer">
                                            <!-- iOS-style Switch -->
                                            <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[2.5px] after:left-[2.5px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all after:duration-300 peer-checked:after:translate-x-4 shadow-inner animate-all"></div>
                                            <span class="ml-2 text-xs font-semibold {{ $y->aktif_mi ? 'text-[#C96A2B]' : 'text-slate-400' }}">
                                                {{ $y->aktif_mi ? 'Aktif' : 'Askıda' }}
                                            </span>
                                        </label>
                                    </form>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                <div class="inline-flex items-center gap-2">

                                    <!-- Edit Button -->
                                    <a href="{{ route('yonetim.yoneticiler.duzenle', $y->id) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] hover:border-[#E7B58A]/40 transition-all duration-200 shadow-sm" 
                                       title="Düzenle">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        Düzenle
                                    </a>

                                    <!-- Delete Button -->
                                    @if($y->id !== $yonetici->id)
                                        <form action="{{ route('yonetim.yoneticiler.sil', $y->id) }}" method="POST" class="inline" onsubmit="onayModalAc(event, this, 'Bu yöneticiyi silmek istediğinize emin misiniz?');">
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
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($yoneticiler->isEmpty())
            <div class="p-12 text-center text-sm text-[#6B7280] bg-slate-50/30">
                Kayıtlı yönetici bulunamadı.
            </div>
        @endif
    </div>

    <!-- Search / Filter JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableSearch = document.getElementById('tableSearch');
            const statusFilter = document.getElementById('statusFilter');
            const rows = document.querySelectorAll('#yoneticiTableBody tr');

            function filterTable() {
                const query = tableSearch.value.toLowerCase().trim();
                const filterVal = statusFilter.value;

                rows.forEach(row => {
                    const status = row.getAttribute('data-status') || '';
                    const cellsText = row.innerText.toLowerCase();

                    const matchesSearch = cellsText.includes(query);
                    const matchesStatus = (filterVal === 'hepsi') || (status === filterVal);

                    if (matchesSearch && matchesStatus) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (tableSearch && statusFilter) {
                tableSearch.addEventListener('input', filterTable);
                statusFilter.addEventListener('change', filterTable);
            }
        });
    </script>
@endsection
