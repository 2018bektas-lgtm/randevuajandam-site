@extends('hekim.layout')

@section('baslik', 'Gider Kayıtları - Randevu Ajandam')
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
        <button onclick="openAddGiderModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#C96A2B] text-white hover:bg-[#b05c24] transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Yeni Gider Kaydı
        </button>
    </div>

    @if(session('basarili'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">{{ session('basarili') }}</div>
    @endif

    @if($giderKategorileri->isEmpty())
        <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <span>Henüz gider kategorisi eklemediniz. <a href="{{ route('hekim.finans.kategoriler') }}" class="font-bold underline">Kategoriler sayfasından</a> ekleyebilirsiniz.</span>
        </div>
    @endif

    <!-- Filters -->
    <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm mb-6">
        <form method="GET" action="{{ route('hekim.finans.giderler') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kategori</label>
                <select name="finans_kategori_id" class="select2-filter w-full">
                    <option value="">Tümü</option>
                    @foreach($giderKategorileri as $kat)
                        <option value="{{ $kat->id }}" {{ request('finans_kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->ad }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Başlangıç Tarihi</label>
                <input type="date" name="tarih_baslangic" value="{{ request('tarih_baslangic') }}" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Bitiş Tarihi</label>
                <input type="date" name="tarih_bitis" value="{{ request('tarih_bitis') }}" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
            </div>
            <button type="submit" class="w-full py-2.5 bg-[#C96A2B] text-white text-sm font-semibold rounded-xl hover:bg-[#b05c24] transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Filtrele
            </button>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FAFAFA] border-b border-[#E5E7EB] text-xs font-bold text-[#4B5563] uppercase tracking-wider">
                        <th class="p-4">Gider Başlığı</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4 text-right">Tutar</th>
                        <th class="p-4">Tarih</th>
                        <th class="p-4">Açıklama</th>
                        <th class="p-4">Belge</th>
                        <th class="p-4 text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-sm text-[#111827]">
                    @forelse($giderler as $gider)
                        <tr class="hover:bg-[#FAFAFA]/50 transition-colors">
                            <td class="p-4 font-semibold">{{ $gider->baslik }}</td>
                            <td class="p-4">
                                @if($gider->finansKategori)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full" style="background-color: {{ $gider->finansKategori->renk }}22; color: {{ $gider->finansKategori->renk }}">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $gider->finansKategori->renk }}"></span>
                                        {{ $gider->finansKategori->ad }}
                                    </span>
                                @else
                                    <span class="text-xs text-[#9CA3AF]">Kategorisiz</span>
                                @endif
                            </td>
                            <td class="p-4 text-right font-bold text-rose-600">-{{ number_format($gider->tutar, 2, ',', '.') }} ₺</td>
                            <td class="p-4 text-[#6B7280]">{{ $gider->tarih->format('d.m.Y') }}</td>
                            <td class="p-4 text-[#6B7280] max-w-xs truncate" title="{{ $gider->aciklama }}">{{ $gider->aciklama ?? '-' }}</td>
                            <td class="p-4">
                                @if($gider->belge_yolu)
                                    <a href="{{ asset($gider->belge_yolu) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#C96A2B] hover:underline">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                        Görüntüle
                                    </a>
                                @else
                                    <span class="text-xs text-[#9CA3AF]">Yok</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="editGiderModal({{ json_encode($gider) }})" class="p-1.5 text-[#6B7280] hover:text-[#C96A2B] transition-colors" title="Düzenle">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    </button>
                                    <form action="{{ route('hekim.finans.giderler.destroy', $gider->id) }}" method="POST" onsubmit="return confirm('Bu gider kaydını silmek istediğinize emin misiniz?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-[#6B7280] hover:text-red-600 transition-colors" title="Sil">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-sm text-[#6B7280]">Kayıtlı gider bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $giderler->links() }}</div>

    <!-- Modal: Yeni Gider -->
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
                                <input type="text" name="baslik" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="Örn: Haziran Kirası, Tıbbi Malzeme">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kategori</label>
                                <select name="finans_kategori_id" id="add_gider_kategori" class="select2-modal w-full">
                                    <option value="">-- Kategorisiz --</option>
                                    @foreach($giderKategorileri as $kat)
                                        <option value="{{ $kat->id }}">{{ $kat->ad }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Tutar (₺)</label>
                                    <input type="number" name="tutar" step="0.01" min="0.01" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Gider Tarihi</label>
                                    <input type="date" name="tarih" value="{{ date('Y-m-d') }}" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Fatura / Makbuz</label>
                                <input type="file" name="belge" accept=".pdf,.png,.jpg,.jpeg" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FFF7ED] file:text-[#C96A2B] hover:file:bg-amber-100 border border-[#E5E7EB] rounded-xl p-1 bg-[#FAFAFA]">
                                <span class="block text-[10px] text-[#9CA3AF] mt-1">PDF, PNG, JPG — Maks. 4MB</span>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Açıklama</label>
                                <textarea name="aciklama" rows="2" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="Opsiyonel not..."></textarea>
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

    <!-- Modal: Gider Düzenle -->
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
                                <input type="text" name="baslik" id="edit_gider_baslik" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Kategori</label>
                                <select name="finans_kategori_id" id="edit_gider_kategori" class="select2-modal w-full">
                                    <option value="">-- Kategorisiz --</option>
                                    @foreach($giderKategorileri as $kat)
                                        <option value="{{ $kat->id }}">{{ $kat->ad }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Tutar (₺)</label>
                                    <input type="number" name="tutar" id="edit_gider_tutar" step="0.01" min="0.01" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Gider Tarihi</label>
                                    <input type="date" name="tarih" id="edit_gider_tarih" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Açıklama</label>
                                <textarea name="aciklama" id="edit_gider_aciklama" rows="2" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]"></textarea>
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
