@extends('hekim.layout')

@section('baslik', 'Finans Kategorileri - Randevu Ajandam')
@section('sayfa_baslik', 'Finansal Yönetim')

@section('icerik')
    {{-- FINANS NAV --}}
    @component('hekim.finans.partials._nav')
        @slot('aksiyon')
            <button type="button" onclick="toggleModal('addKategoriModal')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#C96A2B] hover:bg-[#b05c24] text-white shadow-sm transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Yeni Kategori
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- GELİR KATEGORİLERİ --}}
        <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#F3F4F6] flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </span>
                <h2 class="text-base font-bold text-[#111827] font-display flex-1">Gelir Kategorileri</h2>
                <span class="text-xs font-semibold text-[#6B7280] px-2 py-0.5 rounded-full bg-[#FAFAFA] border border-[#E5E7EB]">
                    {{ $gelirKategorileri->count() }}
                </span>
            </div>
            <div class="divide-y divide-[#F3F4F6]">
                @forelse($gelirKategorileri as $kategori)
                    <div class="flex items-center gap-3 px-6 py-3.5 hover:bg-[#FAFAFA]/50 transition-colors">
                        <span class="w-4 h-4 rounded-full flex-shrink-0 ring-2 ring-white shadow-sm" style="background-color: {{ $kategori->renk }}"></span>
                        <span class="flex-1 text-sm font-semibold text-[#111827]">{{ $kategori->ad }}</span>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $kategori->aktif_mi ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-[#F3F4F6] text-[#6B7280] border border-[#E5E7EB]' }}">
                            {{ $kategori->aktif_mi ? 'Aktif' : 'Pasif' }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" title="Düzenle"
                                    onclick="editKategoriModal({{ $kategori->id }}, '{{ addslashes($kategori->ad) }}', '{{ $kategori->renk }}', '{{ $kategori->tur }}')"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#FFF7ED] border border-[#FED7AA] text-[#C96A2B] hover:bg-[#C96A2B] hover:text-white hover:border-[#C96A2B] transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                </svg>
                            </button>
                            <form action="{{ route('hekim.finans.kategoriler.toggle', $kategori->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" title="{{ $kategori->aktif_mi ? 'Pasife Al' : 'Aktife Al' }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 hover:bg-amber-500 hover:text-white hover:border-amber-500 transition-all">
                                    @if($kategori->aktif_mi)
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                    @else
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </button>
                            </form>
                            <form action="{{ route('hekim.finans.kategoriler.destroy', $kategori->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Sil"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-[#111827]">Henüz kategori yok</p>
                        <p class="text-xs text-[#6B7280] mt-1">"Yeni Kategori" ile başlayın.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- GİDER KATEGORİLERİ --}}
        <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#F3F4F6] flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6"/>
                    </svg>
                </span>
                <h2 class="text-base font-bold text-[#111827] font-display flex-1">Gider Kategorileri</h2>
                <span class="text-xs font-semibold text-[#6B7280] px-2 py-0.5 rounded-full bg-[#FAFAFA] border border-[#E5E7EB]">
                    {{ $giderKategorileri->count() }}
                </span>
            </div>
            <div class="divide-y divide-[#F3F4F6]">
                @forelse($giderKategorileri as $kategori)
                    <div class="flex items-center gap-3 px-6 py-3.5 hover:bg-[#FAFAFA]/50 transition-colors">
                        <span class="w-4 h-4 rounded-full flex-shrink-0 ring-2 ring-white shadow-sm" style="background-color: {{ $kategori->renk }}"></span>
                        <span class="flex-1 text-sm font-semibold text-[#111827]">{{ $kategori->ad }}</span>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $kategori->aktif_mi ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-[#F3F4F6] text-[#6B7280] border border-[#E5E7EB]' }}">
                            {{ $kategori->aktif_mi ? 'Aktif' : 'Pasif' }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" title="Düzenle"
                                    onclick="editKategoriModal({{ $kategori->id }}, '{{ addslashes($kategori->ad) }}', '{{ $kategori->renk }}', '{{ $kategori->tur }}')"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#FFF7ED] border border-[#FED7AA] text-[#C96A2B] hover:bg-[#C96A2B] hover:text-white hover:border-[#C96A2B] transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                </svg>
                            </button>
                            <form action="{{ route('hekim.finans.kategoriler.toggle', $kategori->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" title="{{ $kategori->aktif_mi ? 'Pasife Al' : 'Aktife Al' }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 hover:bg-amber-500 hover:text-white hover:border-amber-500 transition-all">
                                    @if($kategori->aktif_mi)
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                    @else
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </button>
                            </form>
                            <form action="{{ route('hekim.finans.kategoriler.destroy', $kategori->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Sil"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-[#111827]">Henüz kategori yok</p>
                        <p class="text-xs text-[#6B7280] mt-1">"Yeni Kategori" ile başlayın.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Modal: Yeni Kategori --}}
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
                                    <option value="gelir">Gelir Kategorisi</option>
                                    <option value="gider">Gider Kategorisi</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kategori Adı</label>
                                <input type="text" name="ad" required
                                       class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="Örn: Muayene Ücreti, Kira...">
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

    {{-- Modal: Kategori Düzenle --}}
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
                                <input type="text" name="ad" id="edit_kategori_ad" required
                                       class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
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
