@extends('hekim.layout')

@section('baslik', 'Hasta Bakiyeleri - Randevu Ajandam')
@section('sayfa_baslik', 'Finansal Yönetim')

@section('icerik')
    <!-- Finance Navigation Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0">
            <a href="{{ route('hekim.finans.index') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('hekim.finans.index') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">
                📊 Genel Bakış
            </a>
            <a href="{{ route('hekim.finans.gelirler') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('hekim.finans.gelirler') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">
                💵 Gelir Kayıtları
            </a>
            <a href="{{ route('hekim.finans.giderler') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('hekim.finans.giderler') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">
                💸 Gider Kayıtları
            </a>
            <a href="{{ route('hekim.finans.hasta-bakiyeleri') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('hekim.finans.hasta-bakiyeleri') || request()->routeIs('hekim.finans.hasta-hesap') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">
                👥 Hasta Bakiyeleri
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm mb-6">
        <form method="GET" action="{{ route('hekim.finans.hasta-bakiyeleri') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="w-full sm:max-w-md relative">
                <input type="text" name="arama" value="{{ request('arama') }}" placeholder="Hasta adı, soyadı veya telefon numarası ile ara..." class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 pl-10 pr-4 py-2.5 bg-[#FAFAFA]">
                <div class="absolute left-3 top-3 text-[#9CA3AF]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                <label class="flex items-center gap-2 text-sm text-[#4B5563] cursor-pointer">
                    <input type="checkbox" name="sadece_borclular" value="1" {{ request('sadece_borclular') == '1' ? 'checked' : '' }} onchange="this.form.submit()" class="rounded border-[#E5E7EB] text-[#C96A2B] focus:ring-[#C96A2B]/10">
                    Sadece Borçlu Olanları Göster
                </label>
                <button type="submit" class="px-5 py-2.5 bg-[#C96A2B] text-white text-sm font-semibold rounded-xl hover:bg-[#b05c24] transition-all">
                    Sorgula
                </button>
            </div>
        </form>
    </div>

    <!-- Balances Table -->
    <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FAFAFA] border-b border-[#E5E7EB] text-xs font-bold text-[#4B5563] uppercase tracking-wider">
                        <th class="p-4">Hasta Adı Soyadı</th>
                        <th class="p-4">Telefon</th>
                        <th class="p-4">E-posta</th>
                        <th class="p-4 text-right">Toplam Borç (Hizmet)</th>
                        <th class="p-4 text-right">Ödenen Tutar</th>
                        <th class="p-4 text-right">Kalan Bakiye</th>
                        <th class="p-4 text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-sm text-[#111827]">
                    @forelse($hastalar as $hasta)
                        <tr class="hover:bg-[#FAFAFA]/50 transition-colors">
                            <td class="p-4 font-semibold">{{ $hasta->ad_soyad }}</td>
                            <td class="p-4 text-[#6B7280]">{{ $hasta->telefon }}</td>
                            <td class="p-4 text-[#6B7280]">{{ $hasta->e_posta ?? '-' }}</td>
                            <td class="p-4 text-right font-semibold text-[#4B5563]">{{ number_format($hasta->toplam_borc, 2, ',', '.') }} ₺</td>
                            <td class="p-4 text-right font-semibold text-emerald-600">{{ number_format($hasta->toplam_odenen, 2, ',', '.') }} ₺</td>
                            <td class="p-4 text-right">
                                @if($hasta->kalan_bakiye > 0)
                                    <span class="font-bold text-rose-600">{{ number_format($hasta->kalan_bakiye, 2, ',', '.') }} ₺</span>
                                @else
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200">Borcu Yok</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-3 flex-wrap">
                                    <a href="{{ route('hekim.finans.hasta-hesap', $hasta->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#C96A2B] text-white text-xs font-bold hover:bg-[#b05c24]" title="Hasta hesabı">
                                        Hesap sayfası
                                    </a>
                                    <a href="{{ route('hekim.finans.gelirler', ['hasta_id' => $hasta->id]) }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#C96A2B] hover:underline" title="Gelir listesi">
                                        Gelirler
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-sm text-[#6B7280]">
                                Finansal bakiye geçmişi olan hasta kaydı bulunamadı.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
