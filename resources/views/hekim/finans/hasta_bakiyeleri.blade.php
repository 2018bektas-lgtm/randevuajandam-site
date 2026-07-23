@extends('hekim.layout')

@section('baslik', 'Hasta Cari Hesapları - Randevu Ajandam')
@section('sayfa_baslik', 'Finansal Yönetim')

@section('icerik')
    {{-- Finans Nav --}}
    <div class="mb-6 flex items-center gap-2 overflow-x-auto pb-1 p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <a href="{{ route('hekim.finans.index') }}" class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('hekim.finans.index') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">📊 Genel Bakış</a>
        <a href="{{ route('hekim.finans.gelirler') }}" class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('hekim.finans.gelirler') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">💵 Gelirler</a>
        <a href="{{ route('hekim.finans.giderler') }}" class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('hekim.finans.giderler') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">💸 Giderler</a>
        <a href="{{ route('hekim.finans.hasta-bakiyeleri') }}" class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('hekim.finans.hasta-bakiyeleri') || request()->routeIs('hekim.finans.hasta-hesap') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">👥 Hasta Cari</a>
    </div>

    {{-- Filtre Paneli --}}
    <form method="GET" action="{{ route('hekim.finans.hasta-bakiyeleri') }}">
        <div class="mb-6 p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-[#9CA3AF] mb-4">Filtreler</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Arama --}}
                <div class="relative">
                    <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Ad / Soyad / Telefon</label>
                    <div class="relative">
                        <input type="text" name="arama" value="{{ request('arama') }}"
                            placeholder="Ara..."
                            class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 pl-9 pr-4 py-2.5 bg-[#FAFAFA]">
                        <svg class="w-4 h-4 text-[#9CA3AF] absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Bakiye Durumu --}}
                <div>
                    <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Bakiye Durumu</label>
                    <select name="durum" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 py-2.5 bg-[#FAFAFA]">
                        <option value="" {{ !request('durum') ? 'selected' : '' }}>Tümü</option>
                        <option value="borclu" {{ request('durum') === 'borclu' ? 'selected' : '' }}>Borçlu (Kalan Bakiye > 0)</option>
                        <option value="kapali" {{ request('durum') === 'kapali' ? 'selected' : '' }}>Borcu Yok</option>
                    </select>
                </div>

                {{-- Min Bakiye --}}
                <div>
                    <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Min. Kalan Bakiye (₺)</label>
                    <input type="number" name="min_bakiye" value="{{ request('min_bakiye') }}" min="0" step="0.01"
                        placeholder="0,00"
                        class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 py-2.5 bg-[#FAFAFA]">
                </div>

                {{-- Max Bakiye --}}
                <div>
                    <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Max. Kalan Bakiye (₺)</label>
                    <input type="number" name="max_bakiye" value="{{ request('max_bakiye') }}" min="0" step="0.01"
                        placeholder="Sınırsız"
                        class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 py-2.5 bg-[#FAFAFA]">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-4 pt-4 border-t border-[#F3F4F6]">
                <a href="{{ route('hekim.finans.hasta-bakiyeleri') }}" class="px-4 py-2 text-xs font-bold text-[#6B7280] bg-[#F3F4F6] hover:bg-[#E5E7EB] rounded-xl transition-colors">
                    Sıfırla
                </a>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-[#C96A2B] hover:bg-[#b05c24] rounded-xl transition-colors">
                    Filtrele
                </button>
            </div>
        </div>
    </form>

    {{-- Özet Satırı --}}
    @if($hastalar->count())
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm text-center">
            <p class="text-[11px] font-bold uppercase text-[#9CA3AF] tracking-wide">Toplam Hasta</p>
            <p class="text-2xl font-bold text-[#111827] mt-1">{{ $hastalar->count() }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm text-center">
            <p class="text-[11px] font-bold uppercase text-[#9CA3AF] tracking-wide">Toplam Borç</p>
            <p class="text-2xl font-bold text-[#111827] mt-1">{{ number_format($hastalar->sum('toplam_borc'), 2, ',', '.') }} ₺</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-rose-100 shadow-sm text-center bg-rose-50/40">
            <p class="text-[11px] font-bold uppercase text-rose-400 tracking-wide">Toplam Kalan</p>
            <p class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($hastalar->sum('kalan_bakiye'), 2, ',', '.') }} ₺</p>
        </div>
    </div>
    @endif

    {{-- Tablo --}}
    <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FAFAFA] border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider">
                        <th class="px-5 py-4">Hasta</th>
                        <th class="px-5 py-4">Telefon</th>
                        <th class="px-5 py-4 text-right">Toplam Borç</th>
                        <th class="px-5 py-4 text-right">Ödenen</th>
                        <th class="px-5 py-4 text-right">Kalan Bakiye</th>
                        <th class="px-5 py-4 text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-sm text-[#111827]">
                    @forelse($hastalar as $hasta)
                        <tr class="hover:bg-[#FAFAFA]/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="font-semibold">{{ $hasta->ad_soyad }}</p>
                                <p class="text-[11px] text-[#9CA3AF] mt-0.5">{{ $hasta->e_posta ?? '—' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-[#6B7280]">{{ $hasta->telefon }}</td>
                            <td class="px-5 py-3.5 text-right font-semibold text-[#4B5563]">
                                {{ number_format($hasta->toplam_borc, 2, ',', '.') }} ₺
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-emerald-600">
                                {{ number_format($hasta->toplam_odenen, 2, ',', '.') }} ₺
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($hasta->kalan_bakiye > 0.009)
                                    <span class="font-bold text-rose-600">{{ number_format($hasta->kalan_bakiye, 2, ',', '.') }} ₺</span>
                                @else
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Borcu Yok</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <a href="{{ route('hekim.finans.hasta-hesap', $hasta->id) }}"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#C96A2B] text-white text-xs font-bold hover:bg-[#b05c24] transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
                                    </svg>
                                    Cari Hesap
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-[#9CA3AF]">
                                Seçili filtrelere uygun hasta bulunamadı.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
