@extends('hekim.layout')

@section('baslik', 'Gelir Kayıtları - Randevu Ajandam')
@section('sayfa_baslik', 'Finansal Yönetim')

@section('icerik')
    {{-- FINANS NAV --}}
    @component('hekim.finans.partials._nav')
        @slot('aksiyon')
            <button type="button" onclick="openAddGelirModal()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#C96A2B] hover:bg-[#b05c24] text-white shadow-sm transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Yeni Gelir
            </button>
        @endslot
    @endcomponent

    @if(session('basarili'))
        <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('basarili') }}
        </div>
    @endif

    @if($gelirKategorileri->isEmpty())
        <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
            </svg>
            <span>Henüz gelir kategorisi eklemediniz. <a href="{{ route('hekim.finans.kategoriler') }}" class="font-bold underline">Kategoriler sayfasından</a> ekleyerek başlayın.</span>
        </div>
    @endif

    {{-- FİLTRELER --}}
    <div class="mb-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-[#F3F4F6] flex items-center gap-2">
            <svg class="w-4 h-4 text-[#9CA3AF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/>
            </svg>
            <span class="text-xs font-bold text-[#4B5563] uppercase tracking-wider">Filtreler</span>
        </div>
        <form method="GET" action="{{ route('hekim.finans.gelirler') }}" class="p-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Durum</label>
                <select name="durum" class="select2-filter w-full">
                    <option value="">Tümü</option>
                    <option value="beklemede"   {{ request('durum') === 'beklemede'   ? 'selected' : '' }}>Beklemede</option>
                    <option value="kismi_odeme" {{ request('durum') === 'kismi_odeme' ? 'selected' : '' }}>Kısmi Ödeme</option>
                    <option value="odendi"      {{ request('durum') === 'odendi'      ? 'selected' : '' }}>Ödendi</option>
                    <option value="iptal"       {{ request('durum') === 'iptal'       ? 'selected' : '' }}>İptal</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Kategori</label>
                <select name="finans_kategori_id" class="select2-filter w-full">
                    <option value="">Tümü</option>
                    @foreach($gelirKategorileri as $kat)
                        <option value="{{ $kat->id }}" {{ request('finans_kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->ad }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Hasta</label>
                <select name="hasta_id" class="select2-hasta-filter w-full">
                    <option value="">Tüm Hastalar</option>
                    @foreach($hastalar as $hasta)
                        <option value="{{ $hasta->id }}" {{ request('hasta_id') == $hasta->id ? 'selected' : '' }}>{{ $hasta->ad_soyad }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Başlangıç</label>
                <input type="date" name="tarih_baslangic" value="{{ request('tarih_baslangic') }}"
                       class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
            </div>
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Bitiş</label>
                    <input type="date" name="tarih_bitis" value="{{ request('tarih_bitis') }}"
                           class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                </div>
                <button type="submit" class="p-2.5 bg-[#C96A2B] hover:bg-[#b05c24] text-white rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    {{-- TABLO --}}
    <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden mb-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FAFAFA] border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider">
                        <th class="px-5 py-3.5">Hasta / Hizmet</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5 text-right">Toplam</th>
                        <th class="px-5 py-3.5 text-right">Tahsil</th>
                        <th class="px-5 py-3.5 text-right">Kalan</th>
                        <th class="px-5 py-3.5">Durum</th>
                        <th class="px-5 py-3.5 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-[#111827]">
                    @forelse($odemeler as $odeme)
                        @php
                            $kalan = (float) $odeme->tutar - (float) $odeme->odenen_tutar;
                            $durum = [
                                'beklemede'   => ['Beklemede', 'bg-amber-50 text-amber-800 border-amber-200'],
                                'kismi_odeme' => ['Kısmi',     'bg-blue-50 text-blue-800 border-blue-200'],
                                'odendi'      => ['Ödendi',    'bg-emerald-50 text-emerald-800 border-emerald-200'],
                                'iptal'       => ['İptal',     'bg-red-50 text-red-800 border-red-200'],
                            ][$odeme->durum] ?? ['-', 'bg-gray-50 text-gray-600 border-gray-200'];
                        @endphp
                        <tr class="border-b border-[#F3F4F6] hover:bg-[#FAFAFA]/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-[#111827]">
                                    {{ $odeme->hasta ? $odeme->hasta->ad_soyad : ($odeme->randevu ? $odeme->randevu->ad . ' ' . $odeme->randevu->soyad : 'Serbest Gelir') }}
                                </p>
                                <p class="text-[11px] text-[#6B7280] mt-0.5">
                                    {{ $odeme->hizmet?->ad ?? 'Hizmet dışı' }}
                                    · {{ ($odeme->odeme_tarihi ?? $odeme->created_at)->format('d.m.Y') }}
                                </p>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($odeme->finansKategori)
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2 py-0.5 rounded-full"
                                          style="background-color: {{ $odeme->finansKategori->renk }}18; color: {{ $odeme->finansKategori->renk }}">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $odeme->finansKategori->renk }}"></span>
                                        {{ $odeme->finansKategori->ad }}
                                    </span>
                                @else
                                    <span class="text-[11px] text-[#9CA3AF]">Kategorisiz</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-[#111827] whitespace-nowrap">
                                {{ number_format($odeme->tutar, 2, ',', '.') }} ₺
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-emerald-600 whitespace-nowrap">
                                {{ number_format($odeme->odenen_tutar, 2, ',', '.') }} ₺
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold whitespace-nowrap {{ $kalan > 0.009 ? 'text-rose-600' : 'text-[#9CA3AF]' }}">
                                {{ number_format($kalan, 2, ',', '.') }} ₺
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $durum[1] }}">{{ $durum[0] }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-0.5">
                                    <button type="button" title="Ödeme ekle"
                                            onclick="kalemModalAc({{ $odeme->id }}, '{{ addslashes($odeme->hasta ? $odeme->hasta->ad_soyad : 'Serbest Gelir') }}', {{ $odeme->tutar }}, {{ $odeme->odenen_tutar }})"
                                            class="p-1.5 text-[#9CA3AF] hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>
                                    @if($odeme->kalemler->count() > 0)
                                        <button type="button" title="Ödeme geçmişi ({{ $odeme->kalemler->count() }})"
                                                onclick="kalemleriGoster({{ $odeme->id }})"
                                                class="p-1.5 text-[#9CA3AF] hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors relative">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-blue-500 text-white text-[9px] font-bold flex items-center justify-center">
                                                {{ $odeme->kalemler->count() }}
                                            </span>
                                        </button>
                                    @endif
                                    <button type="button" title="Düzenle"
                                            onclick="editGelirModal({{ json_encode($odeme) }})"
                                            class="p-1.5 text-[#9CA3AF] hover:text-[#C96A2B] hover:bg-[#FFF7ED] rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('hekim.finans.gelirler.destroy', $odeme->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Bu gelir kaydını silmek istediğinize emin misiniz?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Sil" class="p-1.5 text-[#9CA3AF] hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- ÖDEME GEÇMİŞİ (Expansion) --}}
                        <tr id="kalemler-{{ $odeme->id }}" class="hidden">
                            <td colspan="7" class="p-0">
                                <div class="mx-5 my-2 mb-4 rounded-xl bg-blue-50/40 border border-blue-100 overflow-hidden">
                                    <div class="px-4 py-2 bg-blue-50 border-b border-blue-100 flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-[11px] font-bold text-blue-800 uppercase tracking-wide">
                                            Ödeme geçmişi — {{ $odeme->kalemler->count() }} kayıt
                                        </span>
                                    </div>
                                    <table class="w-full text-xs">
                                        <thead class="bg-white/60 border-b border-blue-100 text-[10px] uppercase tracking-wider text-[#6B7280]">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-semibold">Tarih</th>
                                                <th class="px-4 py-2 text-left font-semibold">Yöntem</th>
                                                <th class="px-4 py-2 text-left font-semibold">Not</th>
                                                <th class="px-4 py-2 text-right font-semibold">Tutar</th>
                                                <th class="px-4 py-2"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-blue-100/60">
                                            @php $yontemler = ['nakit' => 'Nakit', 'kredi_karti' => 'Kredi Kartı', 'havale' => 'Havale/EFT', 'online' => 'Online']; @endphp
                                            @foreach($odeme->kalemler as $kalem)
                                                <tr class="hover:bg-white/80">
                                                    <td class="px-4 py-2.5 text-[#4B5563] font-medium whitespace-nowrap">{{ $kalem->tarih->format('d.m.Y') }}</td>
                                                    <td class="px-4 py-2.5 text-[#6B7280]">{{ $yontemler[$kalem->odeme_yontemi] ?? $kalem->odeme_yontemi }}</td>
                                                    <td class="px-4 py-2.5 text-[#9CA3AF]">{{ $kalem->not ?? '—' }}</td>
                                                    <td class="px-4 py-2.5 text-right font-bold text-emerald-600 whitespace-nowrap">+{{ number_format($kalem->tutar, 2, ',', '.') }} ₺</td>
                                                    <td class="px-4 py-2.5 text-right">
                                                        <form action="{{ route('hekim.finans.gelirler.kalem.destroy', [$odeme->id, $kalem->id]) }}" method="POST" class="inline"
                                                              onsubmit="return confirm('Bu ödeme kalemini silmek istediğinize emin misiniz?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" title="Kalemi sil" class="text-[#9CA3AF] hover:text-rose-500 transition-colors">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16">
                                <div class="flex flex-col items-center text-center">
                                    <div class="w-14 h-14 rounded-full bg-[#FAFAFA] text-[#9CA3AF] flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-[#111827]">Kayıt bulunamadı</p>
                                    <p class="text-xs text-[#6B7280] mt-1">Filtrelere uygun gelir kaydı yok.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $odemeler->links() }}</div>

    {{-- Modal: Yeni Gelir Kaydı --}}
    <div id="addGelirModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" onclick="handleModalBackdropClick(event, 'addGelirModal')">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            <div class="modal-content relative z-10 bg-white rounded-2xl shadow-xl w-full max-w-lg border border-[#E5E7EB]" onclick="event.stopPropagation()">
                <form action="{{ route('hekim.finans.gelirler.store') }}" method="POST">
                    @csrf
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-lg font-bold font-display text-[#111827]">Yeni Gelir Kaydı</h3>
                            <button type="button" onclick="closeModal('addGelirModal')" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Hasta (Opsiyonel)</label>
                                    <select name="hasta_id" id="add_hasta_id" class="select2-modal w-full">
                                        <option value="">— Serbest Gelir —</option>
                                        @foreach($hastalar as $hasta)
                                            <option value="{{ $hasta->id }}">{{ $hasta->ad_soyad }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kategori</label>
                                    <select name="finans_kategori_id" id="add_finans_kategori_id" class="select2-modal w-full">
                                        <option value="">— Kategorisiz —</option>
                                        @foreach($gelirKategorileri as $kat)
                                            <option value="{{ $kat->id }}">{{ $kat->ad }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div id="add_tahsilat_wrap" class="hidden p-4 rounded-xl bg-amber-50 border border-amber-200 space-y-2">
                                <p class="text-xs font-bold text-amber-800 uppercase tracking-wider">Bu hastanın açık borcu var</p>
                                <label class="block text-xs font-semibold text-[#4B5563] mb-1.5">Kayıt tipi</label>
                                <select name="tahsilat_odeme_id" id="add_tahsilat_odeme_id" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-white">
                                    <option value="">Yeni borç / hizmet kaydı oluştur</option>
                                </select>
                                <p class="text-[11px] text-amber-700">
                                    Tahsilat seçerseniz yeni borç açılmaz; tutar seçtiğiniz faturanın kalanından düşer.
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Hizmet (Opsiyonel)</label>
                                <select name="hizmet_id" id="add_hizmet_id" class="select2-modal w-full">
                                    <option value="">— Hizmet Dışı —</option>
                                    @foreach($hizmetler as $hizmet)
                                        <option value="{{ $hizmet->id }}">{{ $hizmet->ad }} ({{ number_format($hizmet->fiyat, 2, ',', '.') }} ₺)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Toplam Tutar (₺)</label>
                                    <input type="number" name="tutar" step="0.01" min="0.01" required
                                           class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kayıt Tarihi</label>
                                    <input type="date" name="odeme_tarihi" value="{{ date('Y-m-d') }}" required
                                           class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                                </div>
                            </div>
                            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 space-y-3">
                                <p class="text-xs font-bold text-emerald-800 uppercase tracking-wider">İlk Ödeme (Opsiyonel)</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#4B5563] mb-1.5">Ödenen Tutar</label>
                                        <input type="number" name="ilk_odeme_tutar" step="0.01" min="0"
                                               class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-white" placeholder="Boş bırakılabilir">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-[#4B5563] mb-1.5">Ödeme Yöntemi</label>
                                        <select name="ilk_odeme_yontemi" id="add_ilk_odeme_yontemi" class="select2-modal w-full">
                                            <option value="nakit">Nakit</option>
                                            <option value="kredi_karti">Kredi Kartı</option>
                                            <option value="havale">Havale / EFT</option>
                                            <option value="online">Online Ödeme</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Açıklama</label>
                                <textarea name="aciklama" rows="2"
                                          class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="Opsiyonel not..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                        <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#C96A2B] text-white hover:bg-[#b05c24] transition-all">Kaydet</button>
                        <button type="button" onclick="closeModal('addGelirModal')" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-white border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-50 transition-all">Vazgeç</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Gelir Düzenle --}}
    <div id="editGelirModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" onclick="handleModalBackdropClick(event, 'editGelirModal')">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            <div class="modal-content relative z-10 bg-white rounded-2xl shadow-xl w-full max-w-lg border border-[#E5E7EB]" onclick="event.stopPropagation()">
                <form id="editGelirForm" method="POST">
                    @csrf
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-lg font-bold font-display text-[#111827]">Gelir Kaydı Düzenle</h3>
                            <button type="button" onclick="closeModal('editGelirModal')" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Hasta</label>
                                    <select name="hasta_id" id="edit_hasta_id" class="select2-modal w-full">
                                        <option value="">— Serbest Gelir —</option>
                                        @foreach($hastalar as $hasta)
                                            <option value="{{ $hasta->id }}">{{ $hasta->ad_soyad }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kategori</label>
                                    <select name="finans_kategori_id" id="edit_finans_kategori_id" class="select2-modal w-full">
                                        <option value="">— Kategorisiz —</option>
                                        @foreach($gelirKategorileri as $kat)
                                            <option value="{{ $kat->id }}">{{ $kat->ad }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Hizmet</label>
                                <select name="hizmet_id" id="edit_hizmet_id" class="select2-modal w-full">
                                    <option value="">— Hizmet Dışı —</option>
                                    @foreach($hizmetler as $hizmet)
                                        <option value="{{ $hizmet->id }}">{{ $hizmet->ad }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Toplam Tutar (₺)</label>
                                    <input type="number" name="tutar" id="edit_tutar" step="0.01" min="0.01" required
                                           class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kayıt Tarihi</label>
                                    <input type="date" name="odeme_tarihi" id="edit_odeme_tarihi" required
                                           class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Açıklama</label>
                                <textarea name="aciklama" id="edit_aciklama" rows="2"
                                          class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                        <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#C96A2B] text-white hover:bg-[#b05c24] transition-all">Güncelle</button>
                        <button type="button" onclick="closeModal('editGelirModal')" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-white border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-50 transition-all">Vazgeç</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Ödeme Kalemi Ekle --}}
    <div id="kalemModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" onclick="handleModalBackdropClick(event, 'kalemModal')">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            <div class="modal-content relative z-10 bg-white rounded-2xl shadow-xl w-full max-w-md border border-[#E5E7EB]" onclick="event.stopPropagation()">
                <form id="kalemForm" method="POST">
                    @csrf
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-base font-bold font-display text-[#111827]">Ödeme Ekle</h3>
                                <p id="kalem_hasta_adi" class="text-xs text-[#6B7280] mt-0.5"></p>
                            </div>
                            <button type="button" onclick="closeModal('kalemModal')" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="mb-4 p-3 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] grid grid-cols-3 gap-2 text-center text-xs">
                            <div><div class="font-bold text-[#111827]" id="kalem_toplam">-</div><div class="text-[#9CA3AF]">Toplam</div></div>
                            <div><div class="font-bold text-emerald-600" id="kalem_odenen">-</div><div class="text-[#9CA3AF]">Ödenen</div></div>
                            <div><div class="font-bold text-amber-600" id="kalem_kalan">-</div><div class="text-[#9CA3AF]">Kalan</div></div>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Ödeme Tutarı (₺)</label>
                                    <input type="number" name="tutar" step="0.01" min="0.01" required
                                           class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Ödeme Tarihi</label>
                                    <input type="date" name="tarih" value="{{ date('Y-m-d') }}" required
                                           class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Ödeme Yöntemi</label>
                                <select name="odeme_yontemi" id="kalem_odeme_yontemi" class="select2-modal w-full">
                                    <option value="nakit">Nakit</option>
                                    <option value="kredi_karti">Kredi Kartı</option>
                                    <option value="havale">Havale / EFT</option>
                                    <option value="online">Online Ödeme</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Not (Opsiyonel)</label>
                                <input type="text" name="not"
                                       class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="Örn: 2. taksit, peşinat...">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                        <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-all">Ödemeyi Kaydet</button>
                        <button type="button" onclick="closeModal('kalemModal')" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-white border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-50 transition-all">Vazgeç</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function kategoriUyarisiVeYonlendir(mesaj, redirectUrl) {
            mesajModalAc(mesaj, 'uyari');
            const closeBtn = document.getElementById('closeAlertBtn');
            if (closeBtn) {
                const newCloseBtn = closeBtn.cloneNode(true);
                closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
                newCloseBtn.addEventListener('click', function() {
                    const modal = document.getElementById('alertModal');
                    const container = document.getElementById('alertModalContainer');
                    container.classList.remove('scale-100', 'opacity-100');
                    container.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        window.location.href = redirectUrl;
                    }, 300);
                });
            }
        }

        function openAddGelirModal() {
            @if($gelirKategorileri->isEmpty())
                kategoriUyarisiVeYonlendir('Henüz gelir kategorisi eklemediniz. Gelir kaydı oluşturabilmek için lütfen önce en az bir kategori ekleyin.', '{{ route("hekim.finans.kategoriler") }}');
            @else
                toggleModal('addGelirModal');
            @endif
        }

        function initModalSelect2(modalId) {
            const $modal = $('#' + modalId);
            $modal.find('.select2-modal').each(function () {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        dropdownParent: $modal,
                        placeholder: 'Seçiniz...',
                        allowClear: true,
                        minimumResultsForSearch: 8,
                        language: { noResults: function() { return 'Sonuç bulunamadı'; } }
                    });
                }
            });
        }

        function destroyModalSelect2(modalId) {
            $('#' + modalId).find('.select2-modal').each(function () {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });
        }

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                initModalSelect2(modalId);
            } else {
                destroyModalSelect2(modalId);
                modal.classList.add('hidden');
            }
        }

        function closeModal(modalId) {
            destroyModalSelect2(modalId);
            document.getElementById(modalId).classList.add('hidden');
        }

        function handleModalBackdropClick(event, modalId) {
            if (event.target === document.getElementById(modalId) || event.target.classList.contains('bg-gray-500')) {
                closeModal(modalId);
            }
        }

        function editGelirModal(odeme) {
            document.getElementById('editGelirForm').action = `/hekim/finans/gelirler/${odeme.id}/guncelle`;
            document.getElementById('edit_tutar').value = odeme.tutar;
            document.getElementById('edit_aciklama').value = odeme.aciklama || '';

            if (odeme.odeme_tarihi) {
                const formattedDate = new Date(odeme.odeme_tarihi).toISOString().split('T')[0];
                const tarihEl = document.getElementById('edit_odeme_tarihi');
                tarihEl.value = formattedDate;
                if (tarihEl._flatpickr) { tarihEl._flatpickr.setDate(formattedDate); }
            }

            const modal = document.getElementById('editGelirModal');
            modal.classList.remove('hidden');
            initModalSelect2('editGelirModal');

            $('#edit_hasta_id').val(odeme.hasta_id || '').trigger('change');
            $('#edit_hizmet_id').val(odeme.hizmet_id || '').trigger('change');
            $('#edit_finans_kategori_id').val(odeme.finans_kategori_id || '').trigger('change');
        }

        function kalemModalAc(odemeId, hastaAdi, toplam, odenen) {
            document.getElementById('kalemForm').action = `/hekim/finans/gelirler/${odemeId}/kalem`;
            document.getElementById('kalem_hasta_adi').textContent = hastaAdi;
            document.getElementById('kalem_toplam').textContent = Number(toplam).toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
            document.getElementById('kalem_odenen').textContent = Number(odenen).toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
            document.getElementById('kalem_kalan').textContent = (Number(toplam) - Number(odenen)).toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';

            const modal = document.getElementById('kalemModal');
            modal.classList.remove('hidden');
            initModalSelect2('kalemModal');
        }

        function kalemleriGoster(odemeId) {
            document.getElementById('kalemler-' + odemeId).classList.toggle('hidden');
        }

        const ACIK_FATURALAR = @json($acikFaturalar ?? []);

        function tahsilatSecenekleriniGuncelle(hastaId) {
            const wrap = document.getElementById('add_tahsilat_wrap');
            const sel = document.getElementById('add_tahsilat_odeme_id');
            if (!wrap || !sel) return;

            const liste = ACIK_FATURALAR[hastaId] || [];
            sel.innerHTML = '<option value="">Yeni borç / hizmet kaydı oluştur</option>';

            if (!hastaId || liste.length === 0) {
                wrap.classList.add('hidden');
                return;
            }

            liste.forEach(function (f) {
                const kalan = Number(f.kalan).toLocaleString('tr-TR', { minimumFractionDigits: 2 });
                const opt = document.createElement('option');
                opt.value = f.id;
                opt.textContent = 'Tahsilat: ' + f.etiket + ' — kalan ' + kalan + ' ₺';
                sel.appendChild(opt);
            });
            wrap.classList.remove('hidden');
        }

        $(document).ready(function () {
            $('.select2-filter').select2({
                placeholder: 'Seçiniz...',
                allowClear: true,
                minimumResultsForSearch: 6,
                language: { noResults: function() { return 'Sonuç bulunamadı'; } }
            });
            $('.select2-hasta-filter').select2({
                placeholder: 'Hasta ara veya seçin...',
                allowClear: true,
                language: { noResults: function() { return 'Hasta bulunamadı'; } }
            });

            $(document).on('change', '#add_hasta_id', function () {
                tahsilatSecenekleriniGuncelle($(this).val());
            });

            $(document).on('change', '#add_tahsilat_odeme_id', function () {
                const tahsilatMi = !!$(this).val();
                $('input[name="ilk_odeme_tutar"]').prop('disabled', tahsilatMi).val('');
            });
        });
    </script>
@endsection
