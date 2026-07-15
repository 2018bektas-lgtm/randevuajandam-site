@extends('hekim.layout')

@section('baslik', 'Gelir Kayıtları - Randevu Ajandam')
@section('sayfa_baslik', 'Finansal Yönetim')

@section('icerik')
    <!-- Finance Navigation Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0">
            <a href="{{ route('hekim.finans.index') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('hekim.finans.index') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">📊 Genel Bakış</a>
            <a href="{{ route('hekim.finans.gelirler') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('hekim.finans.gelirler') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">💵 Gelir Kayıtları</a>
            <a href="{{ route('hekim.finans.giderler') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('hekim.finans.giderler') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">💸 Gider Kayıtları</a>
            <a href="{{ route('hekim.finans.hasta-bakiyeleri') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('hekim.finans.hasta-bakiyeleri') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">👥 Hasta Bakiyeleri</a>
            <a href="{{ route('hekim.finans.kategoriler') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('hekim.finans.kategoriler') ? 'bg-[#C96A2B] text-white shadow-sm' : 'bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]' }}">🏷️ Kategoriler</a>
        </div>
        <button onclick="openAddGelirModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#C96A2B] text-white hover:bg-[#b05c24] transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Yeni Gelir Kaydı
        </button>
    </div>

    @if(session('basarili'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">{{ session('basarili') }}</div>
    @endif

    @if($gelirKategorileri->isEmpty())
        <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <span>Henüz gelir kategorisi eklemediniz. <a href="{{ route('hekim.finans.kategoriler') }}" class="font-bold underline">Kategoriler sayfasından</a> ekleyebilirsiniz.</span>
        </div>
    @endif

    <!-- Filters -->
    <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm mb-6">
        <form method="GET" action="{{ route('hekim.finans.gelirler') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Ödeme Durumu</label>
                <select name="durum" class="select2-filter w-full">
                    <option value="">Tümü</option>
                    <option value="beklemede" {{ request('durum') === 'beklemede' ? 'selected' : '' }}>Beklemede</option>
                    <option value="kismi_odeme" {{ request('durum') === 'kismi_odeme' ? 'selected' : '' }}>Kısmi Ödeme</option>
                    <option value="odendi" {{ request('durum') === 'odendi' ? 'selected' : '' }}>Ödendi</option>
                    <option value="iptal" {{ request('durum') === 'iptal' ? 'selected' : '' }}>İptal</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kategori</label>
                <select name="finans_kategori_id" class="select2-filter w-full">
                    <option value="">Tümü</option>
                    @foreach($gelirKategorileri as $kat)
                        <option value="{{ $kat->id }}" {{ request('finans_kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->ad }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Hasta</label>
                <select name="hasta_id" class="select2-hasta-filter w-full">
                    <option value="">Tüm Hastalar</option>
                    @foreach($hastalar as $hasta)
                        <option value="{{ $hasta->id }}" {{ request('hasta_id') == $hasta->id ? 'selected' : '' }}>{{ $hasta->ad_soyad }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Başlangıç</label>
                <input type="date" name="tarih_baslangic" value="{{ request('tarih_baslangic') }}" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
            </div>
            <div class="flex gap-2">
                <div class="grow">
                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Bitiş</label>
                    <input type="date" name="tarih_bitis" value="{{ request('tarih_bitis') }}" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                </div>
                <button type="submit" class="p-2.5 bg-[#C96A2B] text-white rounded-xl hover:bg-[#b05c24] transition-all self-end">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FAFAFA] border-b border-[#E5E7EB] text-xs font-bold text-[#4B5563] uppercase tracking-wider">
                        <th class="p-4">Hasta / Kaynak</th>
                        <th class="p-4">Kategori / Hizmet</th>
                        <th class="p-4 text-right">Toplam</th>
                        <th class="p-4 text-right">Tahsil Edilen</th>
                        <th class="p-4 text-right">Kalan</th>
                        <th class="p-4">Durum</th>
                        <th class="p-4 text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-[#111827]">
                    @forelse($odemeler as $odeme)
                        <tr class="border-b border-[#E5E7EB] hover:bg-[#FAFAFA]/50 transition-colors">
                            <td class="p-4">
                                <div class="font-semibold">{{ $odeme->hasta ? $odeme->hasta->ad_soyad : ($odeme->randevu ? $odeme->randevu->ad . ' ' . $odeme->randevu->soyad : 'Serbest Gelir') }}</div>
                                <div class="text-xs text-[#9CA3AF] mt-0.5">{{ $odeme->odeme_tarihi ? $odeme->odeme_tarihi->format('d.m.Y') : $odeme->created_at->format('d.m.Y') }}</div>
                            </td>
                            <td class="p-4">
                                @if($odeme->finansKategori)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full" style="background-color: {{ $odeme->finansKategori->renk }}22; color: {{ $odeme->finansKategori->renk }}">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $odeme->finansKategori->renk }}"></span>
                                        {{ $odeme->finansKategori->ad }}
                                    </span>
                                @endif
                                @if($odeme->hizmet)
                                    <div class="text-xs text-[#6B7280] mt-1">{{ $odeme->hizmet->ad }}</div>
                                @endif
                            </td>
                            <td class="p-4 text-right font-semibold">{{ number_format($odeme->tutar, 2, ',', '.') }} ₺</td>
                            <td class="p-4 text-right text-emerald-600 font-semibold">{{ number_format($odeme->odenen_tutar, 2, ',', '.') }} ₺</td>
                            <td class="p-4 text-right {{ ($odeme->tutar - $odeme->odenen_tutar) > 0 ? 'text-amber-600' : 'text-emerald-600' }} font-semibold">
                                {{ number_format($odeme->tutar - $odeme->odenen_tutar, 2, ',', '.') }} ₺
                            </td>
                            <td class="p-4">
                                @if($odeme->durum === 'beklemede')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">Beklemede</span>
                                @elseif($odeme->durum === 'kismi_odeme')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-800 border border-blue-200">Kısmi Ödeme</span>
                                @elseif($odeme->durum === 'odendi')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">Ödendi</span>
                                @elseif($odeme->durum === 'iptal')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-800 border border-red-200">İptal</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="kalemModalAc({{ $odeme->id }}, '{{ addslashes($odeme->hasta ? $odeme->hasta->ad_soyad : 'Serbest Gelir') }}', {{ $odeme->tutar }}, {{ $odeme->odenen_tutar }})"
                                        class="p-1.5 text-[#6B7280] hover:text-emerald-600 transition-colors" title="Ödeme Ekle">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                    <button onclick="kalemleriGoster({{ $odeme->id }})" class="p-1.5 text-[#6B7280] hover:text-blue-600 transition-colors" title="Ödeme Geçmişi ({{ $odeme->kalemler->count() }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                    </button>
                                    <button onclick="editGelirModal({{ json_encode($odeme) }})" class="p-1.5 text-[#6B7280] hover:text-[#C96A2B] transition-colors" title="Düzenle">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    </button>
                                    <form action="{{ route('hekim.finans.gelirler.destroy', $odeme->id) }}" method="POST" onsubmit="return confirm('Bu gelir kaydını silmek istediğinize emin misiniz?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-[#6B7280] hover:text-red-600 transition-colors" title="Sil">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <!-- Ödeme Kalemleri Satırı -->
                        <tr id="kalemler-{{ $odeme->id }}" class="hidden bg-blue-50/30">
                            <td colspan="7" class="px-6 pb-4 pt-2">
                                @if($odeme->kalemler->count() > 0)
                                    <div class="rounded-xl border border-blue-100 overflow-hidden">
                                        <div class="px-4 py-2 bg-blue-50 text-xs font-bold text-blue-700 uppercase tracking-wider">Ödeme Geçmişi — {{ $odeme->kalemler->count() }} kayıt</div>
                                        <table class="w-full text-xs">
                                            <thead class="bg-white border-b border-blue-100">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-[#6B7280] font-semibold">Tarih</th>
                                                    <th class="px-4 py-2 text-right text-[#6B7280] font-semibold">Tutar</th>
                                                    <th class="px-4 py-2 text-left text-[#6B7280] font-semibold">Yöntem</th>
                                                    <th class="px-4 py-2 text-left text-[#6B7280] font-semibold">Not</th>
                                                    <th class="px-4 py-2"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-blue-50">
                                                @foreach($odeme->kalemler as $kalem)
                                                    @php $yontemler = ['nakit' => 'Nakit', 'kredi_karti' => 'Kredi Kartı', 'havale' => 'Havale/EFT', 'online' => 'Online']; @endphp
                                                    <tr class="hover:bg-white/60">
                                                        <td class="px-4 py-2.5 text-[#4B5563] font-medium">{{ $kalem->tarih->format('d.m.Y') }}</td>
                                                        <td class="px-4 py-2.5 text-right font-bold text-emerald-600">{{ number_format($kalem->tutar, 2, ',', '.') }} ₺</td>
                                                        <td class="px-4 py-2.5 text-[#6B7280]">{{ $yontemler[$kalem->odeme_yontemi] ?? $kalem->odeme_yontemi }}</td>
                                                        <td class="px-4 py-2.5 text-[#9CA3AF]">{{ $kalem->not ?? '-' }}</td>
                                                        <td class="px-4 py-2.5 text-right">
                                                            <form action="{{ route('hekim.finans.gelirler.kalem.destroy', [$odeme->id, $kalem->id]) }}" method="POST" onsubmit="return confirm('Bu ödeme kalemini silmek istediğinize emin misiniz?')" class="inline">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="text-[#9CA3AF] hover:text-red-500 transition-colors" title="Kalemi Sil">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-xs text-[#9CA3AF] py-2 text-center">Henüz ödeme kalemi eklenmemiş.</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-sm text-[#6B7280]">Filtrelere uygun gelir kaydı bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $odemeler->links() }}</div>

    <!-- Modal: Yeni Gelir Kaydı -->
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
                                        <option value="">-- Serbest Gelir --</option>
                                        @foreach($hastalar as $hasta)
                                            <option value="{{ $hasta->id }}">{{ $hasta->ad_soyad }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kategori</label>
                                    <select name="finans_kategori_id" id="add_finans_kategori_id" class="select2-modal w-full">
                                        <option value="">-- Kategorisiz --</option>
                                        @foreach($gelirKategorileri as $kat)
                                            <option value="{{ $kat->id }}">{{ $kat->ad }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Hizmet (Opsiyonel)</label>
                                <select name="hizmet_id" id="add_hizmet_id" class="select2-modal w-full">
                                    <option value="">-- Hizmet Dışı --</option>
                                    @foreach($hizmetler as $hizmet)
                                        <option value="{{ $hizmet->id }}">{{ $hizmet->ad }} ({{ number_format($hizmet->fiyat, 2, ',', '.') }} ₺)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Toplam Tutar (₺)</label>
                                    <input type="number" name="tutar" step="0.01" min="0.01" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kayıt Tarihi</label>
                                    <input type="date" name="odeme_tarihi" value="{{ date('Y-m-d') }}" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                                </div>
                            </div>
                            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 space-y-3">
                                <p class="text-xs font-bold text-emerald-800 uppercase tracking-wider">💵 İlk Ödeme (Opsiyonel)</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#4B5563] mb-1.5">Ödenen Tutar</label>
                                        <input type="number" name="ilk_odeme_tutar" step="0.01" min="0" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-white" placeholder="Boş bırakılabilir">
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
                                <textarea name="aciklama" rows="2" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="Opsiyonel not..."></textarea>
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

    <!-- Modal: Gelir Düzenle -->
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
                                        <option value="">-- Serbest Gelir --</option>
                                        @foreach($hastalar as $hasta)
                                            <option value="{{ $hasta->id }}">{{ $hasta->ad_soyad }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kategori</label>
                                    <select name="finans_kategori_id" id="edit_finans_kategori_id" class="select2-modal w-full">
                                        <option value="">-- Kategorisiz --</option>
                                        @foreach($gelirKategorileri as $kat)
                                            <option value="{{ $kat->id }}">{{ $kat->ad }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Hizmet</label>
                                <select name="hizmet_id" id="edit_hizmet_id" class="select2-modal w-full">
                                    <option value="">-- Hizmet Dışı --</option>
                                    @foreach($hizmetler as $hizmet)
                                        <option value="{{ $hizmet->id }}">{{ $hizmet->ad }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Toplam Tutar (₺)</label>
                                    <input type="number" name="tutar" id="edit_tutar" step="0.01" min="0.01" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kayıt Tarihi</label>
                                    <input type="date" name="odeme_tarihi" id="edit_odeme_tarihi" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Açıklama</label>
                                <textarea name="aciklama" id="edit_aciklama" rows="2" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]"></textarea>
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

    <!-- Modal: Ödeme Kalemi Ekle -->
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
                                    <input type="number" name="tutar" step="0.01" min="0.01" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Ödeme Tarihi</label>
                                    <input type="date" name="tarih" value="{{ date('Y-m-d') }}" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
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
                                <input type="text" name="not" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="Örn: 2. taksit, peşinat...">
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
        });
    </script>
@endsection
