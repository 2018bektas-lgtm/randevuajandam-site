@extends('hekim.layout')

@section('baslik', 'Finans Kategorileri - Randevu Ajandam')
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
        <button onclick="toggleModal('addKategoriModal')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#C96A2B] text-white hover:bg-[#b05c24] transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Yeni Kategori
        </button>
    </div>

    @if(session('basarili'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">{{ session('basarili') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Gelir Kategorileri -->
        <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#E5E7EB] flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                <h2 class="text-sm font-bold text-[#111827] font-display">Gelir Kategorileri</h2>
                <span class="ml-auto text-xs text-[#6B7280]">{{ $gelirKategorileri->count() }} kategori</span>
            </div>
            <div class="divide-y divide-[#E5E7EB]">
                @forelse($gelirKategorileri as $kategori)
                    <div class="flex items-center gap-3 px-6 py-3.5">
                        <span class="w-4 h-4 rounded-full flex-shrink-0 border border-white shadow-sm" style="background-color: {{ $kategori->renk }}"></span>
                        <span class="flex-1 text-sm font-medium text-[#111827]">{{ $kategori->ad }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $kategori->aktif_mi ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $kategori->aktif_mi ? 'Aktif' : 'Pasif' }}
                        </span>
                        <div class="flex items-center gap-1">
                            <button onclick="editKategoriModal({{ $kategori->id }}, '{{ addslashes($kategori->ad) }}', '{{ $kategori->renk }}', '{{ $kategori->tur }}')" class="p-1.5 text-[#6B7280] hover:text-[#C96A2B] transition-colors" title="Düzenle">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                            </button>
                            <form action="{{ route('hekim.finans.kategoriler.toggle', $kategori->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 text-[#6B7280] hover:text-amber-600 transition-colors" title="{{ $kategori->aktif_mi ? 'Pasife Al' : 'Aktife Al' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9"/></svg>
                                </button>
                            </form>
                            <form action="{{ route('hekim.finans.kategoriler.destroy', $kategori->id) }}" method="POST" onsubmit="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-[#6B7280] hover:text-red-600 transition-colors" title="Sil">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-[#6B7280]">Henüz gelir kategorisi eklenmedi.</div>
                @endforelse
            </div>
        </div>

        <!-- Gider Kategorileri -->
        <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#E5E7EB] flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span>
                <h2 class="text-sm font-bold text-[#111827] font-display">Gider Kategorileri</h2>
                <span class="ml-auto text-xs text-[#6B7280]">{{ $giderKategorileri->count() }} kategori</span>
            </div>
            <div class="divide-y divide-[#E5E7EB]">
                @forelse($giderKategorileri as $kategori)
                    <div class="flex items-center gap-3 px-6 py-3.5">
                        <span class="w-4 h-4 rounded-full flex-shrink-0 border border-white shadow-sm" style="background-color: {{ $kategori->renk }}"></span>
                        <span class="flex-1 text-sm font-medium text-[#111827]">{{ $kategori->ad }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $kategori->aktif_mi ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $kategori->aktif_mi ? 'Aktif' : 'Pasif' }}
                        </span>
                        <div class="flex items-center gap-1">
                            <button onclick="editKategoriModal({{ $kategori->id }}, '{{ addslashes($kategori->ad) }}', '{{ $kategori->renk }}', '{{ $kategori->tur }}')" class="p-1.5 text-[#6B7280] hover:text-[#C96A2B] transition-colors" title="Düzenle">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                            </button>
                            <form action="{{ route('hekim.finans.kategoriler.toggle', $kategori->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 text-[#6B7280] hover:text-amber-600 transition-colors" title="{{ $kategori->aktif_mi ? 'Pasife Al' : 'Aktife Al' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9"/></svg>
                                </button>
                            </form>
                            <form action="{{ route('hekim.finans.kategoriler.destroy', $kategori->id) }}" method="POST" onsubmit="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-[#6B7280] hover:text-red-600 transition-colors" title="Sil">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-[#6B7280]">Henüz gider kategorisi eklenmedi.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal: Yeni Kategori -->
    <div id="addKategoriModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" onclick="handleModalBackdropClick(event, 'addKategoriModal')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            <div class="modal-content relative z-10 bg-white rounded-2xl shadow-xl w-full max-w-sm border border-[#E5E7EB]" onclick="event.stopPropagation()">
                <form action="{{ route('hekim.finans.kategoriler.store') }}" method="POST">
                    @csrf
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-base font-bold font-display text-[#111827]">Yeni Kategori</h3>
                            <button type="button" onclick="closeModal('addKategoriModal')" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Tür</label>
                                <select name="tur" id="add_tur" class="select2-modal w-full">
                                    <option value="gelir">💵 Gelir Kategorisi</option>
                                    <option value="gider">💸 Gider Kategorisi</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kategori Adı</label>
                                <input type="text" name="ad" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="Örn: Muayene Ücreti, Kira...">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Renk</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="renk" value="#C96A2B" class="w-10 h-10 rounded-lg border border-[#E5E7EB] cursor-pointer p-0.5 bg-white">
                                    <span class="text-xs text-[#6B7280]">Listede görünecek renk</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                        <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#C96A2B] text-white hover:bg-[#b05c24] transition-all">Kaydet</button>
                        <button type="button" onclick="closeModal('addKategoriModal')" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-white border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-50 transition-all">Vazgeç</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Kategori Düzenle -->
    <div id="editKategoriModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" onclick="handleModalBackdropClick(event, 'editKategoriModal')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            <div class="modal-content relative z-10 bg-white rounded-2xl shadow-xl w-full max-w-sm border border-[#E5E7EB]" onclick="event.stopPropagation()">
                <form id="editKategoriForm" method="POST">
                    @csrf
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-base font-bold font-display text-[#111827]">Kategori Düzenle</h3>
                            <button type="button" onclick="closeModal('editKategoriModal')" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kategori Adı</label>
                                <input type="text" name="ad" id="edit_kategori_ad" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Renk</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="renk" id="edit_kategori_renk" class="w-10 h-10 rounded-lg border border-[#E5E7EB] cursor-pointer p-0.5 bg-white">
                                    <span class="text-xs text-[#6B7280]">Listede görünecek renk</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                        <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#C96A2B] text-white hover:bg-[#b05c24] transition-all">Güncelle</button>
                        <button type="button" onclick="closeModal('editKategoriModal')" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-white border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-50 transition-all">Vazgeç</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function initModalSelect2(modalId) {
            const $modal = $('#' + modalId);
            $modal.find('.select2-modal').each(function () {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        dropdownParent: $modal,
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

        function editKategoriModal(id, ad, renk, tur) {
            document.getElementById('editKategoriForm').action = `/hekim/finans/kategoriler/${id}/guncelle`;
            document.getElementById('edit_kategori_ad').value = ad;
            document.getElementById('edit_kategori_renk').value = renk;
            const modal = document.getElementById('editKategoriModal');
            modal.classList.remove('hidden');
        }
    </script>
@endsection
