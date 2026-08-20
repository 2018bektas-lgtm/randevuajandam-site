@extends('hekim.layout')

@section('baslik', 'Hasta Cari Hesapları - Randevu Ajandam')
@section('sayfa_baslik', 'Finansal Yönetim')

@section('icerik')
    {{-- FINANS NAV --}}
    @include('hekim.finans.partials._nav')

    {{-- ÖZET KARTLARI --}}
    @if($hastalar->count())
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-[#F3F4F6] text-[#4B5563] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-[11px] font-bold uppercase text-[#9CA3AF] tracking-wide">Hasta Sayısı</p>
                        <p class="text-xl font-bold text-[#111827]">{{ $hastalar->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-[11px] font-bold uppercase text-[#9CA3AF] tracking-wide">Toplam Alacak</p>
                        <p class="text-xl font-bold text-[#111827]">{{ number_format($hastalar->sum('toplam_borc'), 2, ',', '.') }} ₺</p>
                    </div>
                </div>
            </div>
            <div class="p-5 rounded-2xl bg-rose-50/40 border border-rose-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-[11px] font-bold uppercase text-rose-500 tracking-wide">Toplam Kalan</p>
                        <p class="text-xl font-bold text-rose-600">{{ number_format($hastalar->sum('kalan_bakiye'), 2, ',', '.') }} ₺</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- FİLTRELER --}}
    @php
        $aktifFiltreSayisi = collect(['arama', 'durum', 'min_bakiye', 'max_bakiye'])
            ->filter(fn ($k) => filled(request($k)))->count();
    @endphp
    <form method="GET" action="{{ route('hekim.finans.hasta-bakiyeleri') }}" class="mb-5">
        <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-[#F3F4F6] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#9CA3AF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/>
                    </svg>
                    <span class="text-xs font-bold text-[#4B5563] uppercase tracking-wider">Filtreler</span>
                    @if($aktifFiltreSayisi > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#FFF7ED] text-[#C96A2B] text-[10px] font-bold border border-[#FED7AA]">
                            {{ $aktifFiltreSayisi }} aktif
                        </span>
                    @endif
                </div>
                @if($aktifFiltreSayisi > 0)
                    <a href="{{ route('hekim.finans.hasta-bakiyeleri') }}"
                       class="text-[11px] font-bold text-[#6B7280] hover:text-rose-600 transition-colors inline-flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Temizle
                    </a>
                @endif
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Ad / Soyad / Telefon</label>
                    <div class="relative">
                        <input type="text" name="arama" value="{{ request('arama') }}" placeholder="Ara..."
                               class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 pl-9 pr-4 py-2.5 bg-[#FAFAFA]">
                        <svg class="w-4 h-4 text-[#9CA3AF] absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Bakiye Durumu</label>
                    <select name="durum" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 py-2.5 bg-[#FAFAFA]">
                        <option value="" {{ !request('durum') ? 'selected' : '' }}>Tümü</option>
                        <option value="borclu" {{ request('durum') === 'borclu' ? 'selected' : '' }}>Borçlu</option>
                        <option value="kapali" {{ request('durum') === 'kapali' ? 'selected' : '' }}>Borcu Yok</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Kalan Aralığı (₺)</label>
                    <div class="flex items-center gap-1.5 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] focus-within:border-[#C96A2B] focus-within:ring focus-within:ring-[#C96A2B]/10 transition col-span-2">
                        <input type="number" name="min_bakiye" value="{{ request('min_bakiye') }}" min="0" step="0.01" placeholder="Min"
                               aria-label="Minimum kalan bakiye"
                               class="flex-1 min-w-0 text-sm border-0 bg-transparent focus:ring-0 p-2.5">
                        <span class="text-[#D1D5DB] text-xs">—</span>
                        <input type="number" name="max_bakiye" value="{{ request('max_bakiye') }}" min="0" step="0.01" placeholder="Max"
                               aria-label="Maksimum kalan bakiye"
                               class="flex-1 min-w-0 text-sm border-0 bg-transparent focus:ring-0 p-2.5">
                    </div>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-[#C96A2B] hover:bg-[#b05c24] text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Filtrele
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- TABLO --}}
    <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FAFAFA] border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider">
                        <th class="px-5 py-3.5">Hasta</th>
                        <th class="px-5 py-3.5">Telefon</th>
                        <th class="px-5 py-3.5 text-right">Toplam Borç</th>
                        <th class="px-5 py-3.5 text-right">Tahsil Edilen</th>
                        <th class="px-5 py-3.5 text-right">Kalan Bakiye</th>
                        <th class="px-5 py-3.5 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3F4F6] text-sm text-[#111827]">
                    @forelse($hastalar as $hasta)
                        <tr class="hover:bg-[#FAFAFA]/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center text-[11px] font-bold flex-shrink-0">
                                        {{ mb_strtoupper(mb_substr($hasta->ad_soyad, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-[#111827]">{{ $hasta->ad_soyad }}</p>
                                        <p class="text-[11px] text-[#9CA3AF]">{{ $hasta->e_posta ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-[#6B7280] whitespace-nowrap">{{ $hasta->telefon ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-right font-semibold text-[#4B5563] whitespace-nowrap">
                                {{ number_format($hasta->toplam_borc, 2, ',', '.') }} ₺
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-emerald-600 whitespace-nowrap">
                                {{ number_format($hasta->toplam_odenen, 2, ',', '.') }} ₺
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($hasta->kalan_bakiye > 0.009)
                                    <span class="font-bold text-rose-600 whitespace-nowrap">{{ number_format($hasta->kalan_bakiye, 2, ',', '.') }} ₺</span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                        </svg>
                                        Borcu Yok
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('hekim.finans.hasta-hesap', $hasta->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#FFF7ED] border border-[#FED7AA] text-[#C96A2B] hover:bg-[#C96A2B] hover:text-white hover:border-[#C96A2B] text-xs font-bold transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                    </svg>
                                    Cari Hesap
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16">
                                <div class="flex flex-col items-center text-center">
                                    <div class="w-14 h-14 rounded-full bg-[#FAFAFA] text-[#9CA3AF] flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-[#111827]">Hasta bulunamadı</p>
                                    <p class="text-xs text-[#6B7280] mt-1">Seçili filtrelere uygun hasta yok.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
