@extends('yonetim.layout')

@section('baslik', 'Doktor Yönetimi - Randevu Ajandam')
@section('sayfa_baslik', 'Doktor Yönetimi')

@section('icerik')
    <!-- Top Action Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
        <div>
            <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                Doktor Yönetimi
            </h2>
            <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Sistemde kayıtlı olan hekimlerin listesi, üyelik detayları ve durum yönetimi.</p>
        </div>
        @if(($bekleyenMeslek ?? 0) > 0)
            <a href="{{ route('yonetim.doktorlar.index', ['meslek' => 'beklemede']) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold">
                {{ $bekleyenMeslek }} meslek belgesi onay bekliyor →
            </a>
        @endif
    </div>

    <!-- Table Card Container -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Table Search & Filter Bar -->
        <div class="p-5 border-b border-[#E5E7EB] flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Search -->
            <div class="relative max-w-xs w-full">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" id="tableSearch" placeholder="Doktor ara..." 
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-4">


                <!-- Status Filter -->
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
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Doktor Adı / İletişim</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Branş / Uzmanlık</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Paket / Periyot</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Üyelik Süresi</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Durum</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Meslek</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]" id="doktorTableBody">
                    @foreach($doktorlar as $d)
                        <tr class="hover:bg-slate-50/30 transition-colors group" 
                            data-status="{{ $d->aktif_mi ? 'aktif' : 'pasif' }}" 
                            data-type="{{ $d->tur }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    @if($d->profil_resmi)
                                        <img src="{{ asset($d->profil_resmi) }}" alt="{{ $d->ad_soyad }}" class="w-10 h-10 rounded-xl object-cover border border-[#E5E7EB]">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center font-bold text-xs font-display">
                                            {{ mb_strtoupper(mb_substr(preg_replace('/^(Prof\.|Doç\.|Dr\.|Uzm\.)\s+/i', '', $d->ad_soyad), 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <span class="block text-sm font-bold text-[#111827] font-display">
                                            @if($d->unvan)
                                                <span class="text-xs font-semibold text-[#C96A2B] mr-0.5">{{ $d->unvan }}</span>
                                            @endif
                                            {{ $d->ad_soyad }}
                                        </span>
                                        <span class="block text-[11px] text-[#6B7280] mt-0.5">{{ $d->e_posta }}</span>
                                        @if($d->telefon)
                                            <span class="block text-[10px] text-gray-400 mt-0.5">{{ $d->telefon }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-xs text-[#4B5563] font-medium">
                                {{ $d->uzmanlik_alani ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($d->paket)
                                    <span class="block text-xs font-bold text-[#111827] font-display">{{ $d->paket->ad }}</span>
                                    <span class="block text-[10px] text-[#C96A2B] font-semibold uppercase tracking-wider mt-0.5">
                                        {{ $d->odeme_periyodu === 'yillik' ? 'Yıllık' : 'Aylık' }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 font-medium">Paket Tanımsız</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-[#4B5563]">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-1">
                                        <span class="text-[10px] text-gray-400 font-semibold w-10 uppercase">Başl:</span>
                                        <span class="font-medium text-[#111827]">{{ $d->uyelik_baslangic ? $d->uyelik_baslangic->format('d.m.Y') : '-' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-[10px] text-gray-400 font-semibold w-10 uppercase">Bitiş:</span>
                                        <span class="font-medium text-[#111827]">{{ $d->uyelik_bitis ? $d->uyelik_bitis->format('d.m.Y') : '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form action="{{ route('yonetim.doktorlar.durum', $d->id) }}" method="POST" class="inline">
                                    @csrf
                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" name="aktif_mi" value="1" 
                                               {{ $d->aktif_mi ? 'checked' : '' }} 
                                               onchange="this.form.submit()"
                                               class="sr-only peer">
                                        <!-- iOS-style Switch -->
                                        <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[2.5px] after:left-[2.5px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all after:duration-300 peer-checked:after:translate-x-4 shadow-inner"></div>
                                        <span class="ml-2 text-xs font-semibold {{ $d->aktif_mi ? 'text-[#C96A2B]' : 'text-slate-400' }}">
                                            {{ $d->aktif_mi ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </label>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $md = $d->meslek_dogrulama_durumu ?? 'beklemede'; @endphp
                                @if($md === 'onaylandi')
                                    <span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Onaylı</span>
                                @elseif($md === 'reddedildi')
                                    <span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-100">Red</span>
                                @else
                                    <span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-100">Bekliyor</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                <div class="inline-flex items-center gap-2">
                                    
                                    <!-- Edit Button -->
                                    <a href="{{ route('yonetim.doktorlar.duzenle', $d->id) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] hover:border-[#E7B58A]/40 transition-all duration-200 shadow-sm" 
                                       title="Düzenle">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        Düzenle
                                    </a>

                                    <!-- Delete Button -->
                                    <form action="{{ route('yonetim.doktorlar.sil', $d->id) }}" method="POST" class="inline" onsubmit="onayModalAc(event, this, 'Bu doktor hesabını ve tüm verilerini silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">
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
        
        @if($doktorlar->isEmpty())
            <div class="p-12 text-center text-sm text-[#6B7280] bg-slate-50/30">
                Sistemde kayıtlı doktor bulunamadı.
            </div>
        @endif
    </div>

    <!-- Search / Filter JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableSearch = document.getElementById('tableSearch');
            const statusFilter = document.getElementById('statusFilter');
            const rows = document.querySelectorAll('#doktorTableBody tr');

            function filterTable() {
                const query = tableSearch.value.toLowerCase().trim();
                const statusVal = statusFilter.value;

                rows.forEach(row => {
                    const status = row.getAttribute('data-status') || '';
                    const cellsText = row.innerText.toLowerCase();

                    const matchesSearch = cellsText.includes(query);
                    const matchesStatus = (statusVal === 'hepsi') || (status === statusVal);

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
