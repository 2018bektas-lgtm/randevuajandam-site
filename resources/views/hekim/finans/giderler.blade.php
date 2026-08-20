@extends('hekim.layout')

@section('baslik', 'Gider Kayıtları - Randevu Ajandam')
@section('sayfa_baslik', 'Finansal Yönetim')

@section('icerik')
    {{-- FINANS NAV --}}
    @component('hekim.finans.partials._nav')
        @slot('aksiyon')
            <button type="button" onclick="openAddGiderModal()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#C96A2B] hover:bg-[#b05c24] text-white shadow-sm transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Yeni Gider
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

    @if($giderKategorileri->isEmpty())
        <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
            </svg>
            <span>Henüz gider kategorisi eklemediniz. <a href="{{ route('hekim.finans.kategoriler') }}" class="font-bold underline">Kategoriler sayfasından</a> ekleyerek başlayın.</span>
        </div>
    @endif

    {{-- FİLTRELER --}}
    @php
        $aktifFiltreSayisi = collect(['finans_kategori_id', 'tarih_baslangic', 'tarih_bitis'])
            ->filter(fn ($k) => filled(request($k)))->count();
    @endphp
    <form method="GET" action="{{ route('hekim.finans.giderler') }}" class="mb-5">
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
                    <a href="{{ route('hekim.finans.giderler') }}"
                       class="text-[11px] font-bold text-[#6B7280] hover:text-rose-600 transition-colors inline-flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Temizle
                    </a>
                @endif
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Kategori</label>
                    <select name="finans_kategori_id" class="select2-filter w-full">
                        <option value="">Tümü</option>
                        @foreach($giderKategorileri as $kat)
                            <option value="{{ $kat->id }}" {{ request('finans_kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->ad }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#6B7280] mb-1.5 uppercase tracking-wide">Tarih Aralığı</label>
                    <div class="flex items-center gap-1.5 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] focus-within:border-[#C96A2B] focus-within:ring focus-within:ring-[#C96A2B]/10 transition">
                        <input type="date" name="tarih_baslangic" value="{{ request('tarih_baslangic') }}"
                               aria-label="Başlangıç"
                               class="flex-1 min-w-0 text-sm border-0 bg-transparent focus:ring-0 p-2.5">
                        <span class="text-[#D1D5DB] text-xs">—</span>
                        <input type="date" name="tarih_bitis" value="{{ request('tarih_bitis') }}"
                               aria-label="Bitiş"
                               class="flex-1 min-w-0 text-sm border-0 bg-transparent focus:ring-0 p-2.5">
                    </div>
                </div>
            </div>
            <div class="px-5 py-3 bg-[#FAFAFA]/60 border-t border-[#F3F4F6] flex items-center justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-5 py-2 bg-[#C96A2B] hover:bg-[#b05c24] text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filtrele
                </button>
            </div>
        </div>
    </form>

    {{-- TABLO --}}
    <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden mb-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FAFAFA] border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider">
                        <th class="px-5 py-3.5">Gider</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5">Tarih</th>
                        <th class="px-5 py-3.5">Belge</th>
                        <th class="px-5 py-3.5 text-right">Tutar</th>
                        <th class="px-5 py-3.5 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3F4F6] text-sm text-[#111827]">
                    @forelse($giderler as $gider)
                        <tr class="hover:bg-[#FAFAFA]/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-[#111827]">{{ $gider->baslik }}</p>
                                @if($gider->aciklama)
                                    <p class="text-[11px] text-[#9CA3AF] mt-0.5 max-w-md truncate" title="{{ $gider->aciklama }}">{{ $gider->aciklama }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($gider->finansKategori)
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2 py-0.5 rounded-full"
                                          style="background-color: {{ $gider->finansKategori->renk }}18; color: {{ $gider->finansKategori->renk }}">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $gider->finansKategori->renk }}"></span>
                                        {{ $gider->finansKategori->ad }}
                                    </span>
                                @else
                                    <span class="text-[11px] text-[#9CA3AF]">Kategorisiz</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-[#6B7280] whitespace-nowrap">{{ $gider->tarih->format('d.m.Y') }}</td>
                            <td class="px-5 py-3.5">
                                @if($gider->belge_yolu)
                                    <a href="{{ asset($gider->belge_yolu) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-[11px] font-bold text-[#C96A2B] hover:underline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                        </svg>
                                        Görüntüle
                                    </a>
                                @else
                                    <span class="text-[11px] text-[#9CA3AF]">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-rose-600 whitespace-nowrap">
                                -{{ number_format($gider->tutar, 2, ',', '.') }} ₺
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" title="Düzenle"
                                            onclick="editGiderModal({{ json_encode($gider) }})"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#FFF7ED] border border-[#FED7AA] text-[#C96A2B] hover:bg-[#C96A2B] hover:text-white hover:border-[#C96A2B] transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('hekim.finans.giderler.destroy', $gider->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Bu gider kaydını silmek istediğinize emin misiniz?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Sil"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16">
                                <div class="flex flex-col items-center text-center">
                                    <div class="w-14 h-14 rounded-full bg-[#FAFAFA] text-[#9CA3AF] flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l2.25 2.25L15 9.75M9 8.25v-1.5a2.25 2.25 0 012.25-2.25h1.5a2.25 2.25 0 012.25 2.25v1.5m-6 0h6"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-[#111827]">Kayıt bulunamadı</p>
                                    <p class="text-xs text-[#6B7280] mt-1">Filtrelere uygun gider kaydı yok.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $giderler->links() }}</div>

    {{-- Modal: Yeni Gider --}}
    <div id="addGiderModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" onclick="handleModalBackdropClick(event, 'addGiderModal')">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            <div class="modal-content relative z-10 bg-white rounded-2xl shadow-xl w-full max-w-lg border border-[#E5E7EB]" onclick="event.stopPropagation()">
                <form action="{{ route('hekim.finans.giderler.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-lg font-bold font-display text-[#111827]">Yeni Gider Kaydı</h3>
                            <button type="button" onclick="closeModal('addGiderModal')" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Gider Başlığı</label>
                                <input type="text" name="baslik" required
                                       class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="Örn: Haziran Kirası, Tıbbi Malzeme">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kategori</label>
                                <select name="finans_kategori_id" id="add_gider_kategori" class="select2-modal w-full">
                                    <option value="">— Kategorisiz —</option>
                                    @foreach($giderKategorileri as $kat)
                                        <option value="{{ $kat->id }}">{{ $kat->ad }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Tutar (₺)</label>
                                    <input type="number" name="tutar" step="0.01" min="0.01" required
                                           class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Gider Tarihi</label>
                                    <input type="date" name="tarih" value="{{ date('Y-m-d') }}" required
                                           class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Fatura / Makbuz</label>
                                <input type="file" name="belge" accept=".pdf,.png,.jpg,.jpeg"
                                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FFF7ED] file:text-[#C96A2B] hover:file:bg-amber-100 border border-[#E5E7EB] rounded-xl p-1 bg-[#FAFAFA]">
                                <span class="block text-[10px] text-[#9CA3AF] mt-1">PDF, PNG, JPG — Maks. 4MB</span>
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
                        <button type="button" onclick="closeModal('addGiderModal')" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-white border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-50 transition-all">Vazgeç</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Gider Düzenle --}}
    <div id="editGiderModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" onclick="handleModalBackdropClick(event, 'editGiderModal')">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            <div class="modal-content relative z-10 bg-white rounded-2xl shadow-xl w-full max-w-lg border border-[#E5E7EB]" onclick="event.stopPropagation()">
                <form id="editGiderForm" method="POST">
                    @csrf
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-lg font-bold font-display text-[#111827]">Gider Kaydı Düzenle</h3>
                            <button type="button" onclick="closeModal('editGiderModal')" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Gider Başlığı</label>
                                <input type="text" name="baslik" id="edit_gider_baslik" required
                                       class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kategori</label>
                                <select name="finans_kategori_id" id="edit_gider_kategori" class="select2-modal w-full">
                                    <option value="">— Kategorisiz —</option>
                                    @foreach($giderKategorileri as $kat)
                                        <option value="{{ $kat->id }}">{{ $kat->ad }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Tutar (₺)</label>
                                    <input type="number" name="tutar" id="edit_gider_tutar" step="0.01" min="0.01" required
                                           class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Gider Tarihi</label>
                                    <input type="date" name="tarih" id="edit_gider_tarih" required
                                           class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Açıklama</label>
                                <textarea name="aciklama" id="edit_gider_aciklama" rows="2"
                                          class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                        <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#C96A2B] text-white hover:bg-[#b05c24] transition-all">Güncelle</button>
                        <button type="button" onclick="closeModal('editGiderModal')" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-white border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-50 transition-all">Vazgeç</button>
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

        function openAddGiderModal() {
            @if($giderKategorileri->isEmpty())
                kategoriUyarisiVeYonlendir('Henüz gider kategorisi eklemediniz. Gider kaydı oluşturabilmek için lütfen önce en az bir kategori ekleyin.', '{{ route("hekim.finans.kategoriler") }}');
            @else
                toggleModal('addGiderModal');
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
                        minimumResultsForSearch: Infinity,
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

        function editGiderModal(gider) {
            document.getElementById('editGiderForm').action = `/hekim/finans/giderler/${gider.id}/guncelle`;
            document.getElementById('edit_gider_baslik').value = gider.baslik;
            document.getElementById('edit_gider_tutar').value = gider.tutar;
            document.getElementById('edit_gider_aciklama').value = gider.aciklama || '';

            if (gider.tarih) {
                const tarihEl = document.getElementById('edit_gider_tarih');
                tarihEl.value = gider.tarih;
                if (tarihEl._flatpickr) { tarihEl._flatpickr.setDate(gider.tarih); }
            }

            const modal = document.getElementById('editGiderModal');
            modal.classList.remove('hidden');
            initModalSelect2('editGiderModal');

            $('#edit_gider_kategori').val(gider.finans_kategori_id || '').trigger('change');
        }

        $(document).ready(function () {
            $('.select2-filter').select2({
                placeholder: 'Seçiniz...',
                allowClear: true,
                minimumResultsForSearch: Infinity,
                language: { noResults: function() { return 'Sonuç bulunamadı'; } }
            });
        });
    </script>
@endsection
