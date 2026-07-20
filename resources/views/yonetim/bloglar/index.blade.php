@extends('yonetim.layout')

@section('baslik', 'Blog Yönetimi - Randevu Ajandam')
@section('sayfa_baslik', 'Blog Yönetimi')

@section('icerik')
    <!-- Top Action Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
        <div>
            <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                Tüm Blog Yazıları
            </h2>
            <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Sistem genelinde hekimlerin yayınladığı tüm blog yazılarının listesi.</p>
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
                    <input type="text" id="tableSearch" placeholder="Yazı başlığı veya hekim ara..." 
                           class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                </div>
            </div>
            <div class="text-xs text-[#6B7280]">
                Toplam <span class="font-bold text-[#111827]">{{ $bloglar->total() }}</span> blog yazısı tanımlı.
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-[#E5E7EB]">
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Yazı Bilgisi</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Yazar / Hekim</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Okunma</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display">Tarih</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-[#6B7280] uppercase tracking-widest font-display text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]" id="blogTableBody">
                    @foreach($bloglar as $b)
                        <tr class="hover:bg-slate-50/30 transition-colors group">
                            <!-- Title & Image -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($b->resim)
                                        <img src="{{ asset($b->resim) }}" alt="{{ $b->baslik }}" class="w-12 h-10 object-cover rounded-lg border border-slate-200 shadow-sm shrink-0">
                                    @else
                                        <div class="w-12 h-10 rounded-lg bg-orange-50 text-[#C96A2B] border border-[#E7B58A]/20 flex items-center justify-center font-bold text-xs font-display shrink-0">
                                            NEWS
                                        </div>
                                    @endif
                                    <div class="min-w-0 max-w-sm">
                                        <span class="block text-sm font-bold text-[#111827] font-display truncate hover:text-[#C96A2B] transition-colors">
                                            <a href="{{ $b->url }}" target="_blank">
                                                {{ $b->baslik }}
                                            </a>
                                        </span>
                                        <span class="block text-[10px] text-[#6B7280] mt-0.5 truncate">{{ Str::limit(strip_tags($b->icerik), 100) }}</span>
                                    </div>
                                </div>
                            </td>
                            <!-- Author -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($b->doktor)
                                    <div class="text-sm font-semibold text-[#111827] font-display">
                                        {{ $b->doktor->unvan }} {{ $b->doktor->ad_soyad }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">{{ $b->doktor->uzmanlik_alani }}</div>
                                @else
                                    <div class="text-sm font-semibold text-red-600 font-display">Hekim silinmiş</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">doktor_id: {{ $b->doktor_id }}</div>
                                @endif
                            </td>
                            <!-- Read Count -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 font-display">
                                    {{ $b->okunma_sayisi }} Okunma
                                </span>
                            </td>
                            <!-- Date -->
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-[#4B5563] font-semibold font-display">
                                {{ $b->created_at->translatedFormat('d M Y') }}
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ $b->url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] hover:border-[#E7B58A]/40 transition-all duration-200 shadow-sm" title="Görüntüle">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Göster
                                    </a>

                                    <form action="{{ route('yonetim.bloglar.sil', $b->id) }}" method="POST" class="inline" onsubmit="onayModalAc(event, this, 'Bu blog yazısını silmek istediğinize emin misiniz?');">
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
        
        @if($bloglar->isEmpty())
            <div class="p-12 text-center text-sm text-[#6B7280] bg-slate-50/30">
                Sistemde kayıtlı blog yazısı bulunamadı.
            </div>
        @else
            <!-- Pagination Links -->
            @if ($bloglar->hasPages())
                <div class="p-5 border-t border-[#E5E7EB] bg-slate-50/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-xs text-[#6B7280]">
                            <span class="font-semibold text-[#111827]">{{ $bloglar->firstItem() ?? 0 }}</span> ile <span class="font-semibold text-[#111827]">{{ $bloglar->lastItem() ?? 0 }}</span> arası gösteriliyor (Toplam <span class="font-bold text-[#111827]">{{ $bloglar->total() }}</span> kayıttan)
                        </p>
                    </div>
                    <div>
                        {{ $bloglar->links('vendor.pagination.tailwind') }}
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Simple JS Client-Side filtering for Search -->
    <script>
        document.getElementById('tableSearch').addEventListener('input', function(e) {
            const searchVal = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#blogTableBody tr');
            
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
