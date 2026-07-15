@extends('hekim.layout')

@section('baslik', 'Fotoğraf Galerisi - Hekim Paneli')
@section('sayfa_baslik', 'Fotoğraf Galerisi')

@section('icerik')
<div class="mb-8 flex flex-col md:flex-row justify-between md:items-center gap-4">
    <p class="text-sm text-[#6B7280]">
        Kliniğinize, muayenehanenize veya tedavi süreçlerinize ait fotoğrafları buradan yükleyebilir, başlıklarını düzenleyebilir ve sürükle-bırak yöntemiyle sıralayabilirsiniz.
    </p>
</div>

@if(session('basarili'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-sm">
        {{ session('basarili') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold shadow-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    
    <!-- Left: Photo Upload Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-[0_8px_30px_rgba(31,41,55,0.02)]">
        <h3 class="text-base font-bold font-display text-[#111827] mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-[#C96A2B]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Yeni Fotoğraflar Ekle
        </h3>
        
        <form action="{{ route('hekim.galeriler.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <!-- Drag & Drop Uploader -->
            <div id="dropzone" class="border-2 border-dashed border-[#E5E7EB] hover:border-[#C96A2B] rounded-2xl p-8 text-center cursor-pointer transition-all duration-200 bg-[#FAFAFA] hover:bg-orange-50/10 group relative">
                <input type="file" name="resimler[]" id="fileInput" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                <div class="space-y-3 pointer-events-none">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-slate-100 text-[#C96A2B] flex items-center justify-center mx-auto shadow-sm group-hover:scale-105 transition-transform duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path>
                        </svg>
                    </div>
                    <div class="text-xs font-bold text-[#111827] font-display">Tıklayın veya Dosyaları Sürükleyin</div>
                    <p class="text-[10px] text-[#9CA3AF] font-sans">En fazla 5MB boyutunda JPEG, PNG, JPG, WEBP formatları</p>
                </div>
            </div>
            
            <!-- Upload Queue / Previews Container -->
            <div id="previewContainer" class="space-y-3 hidden">
                <div class="flex justify-between items-center pb-2 border-b border-[#E5E7EB]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#6B7280] font-display">Seçilen Dosyalar</span>
                    <button type="button" id="clearFiles" class="text-[10px] font-bold text-red-500 hover:text-red-700 uppercase tracking-wide transition-colors">Temizle</button>
                </div>
                <div id="previewList" class="space-y-3 max-h-[250px] overflow-y-auto pr-1"></div>
            </div>
            
            <button type="submit" id="uploadBtn" disabled class="w-full py-3 bg-[#1F2937] hover:bg-[#111827] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-200 font-display shadow-md cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                Fotoğrafları Yükle
            </button>
        </form>
    </div>
    
    <!-- Right: Gallery Grid -->
    <div class="lg:col-span-2 bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-[0_8px_30px_rgba(31,41,55,0.02)]">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-base font-bold font-display text-[#111827]">Fotoğraflarım ({{ $galeriler->count() }})</h3>
            <span class="text-[10px] font-semibold bg-[#FFF7ED] text-[#C96A2B] px-2.5 py-1 rounded-full border border-[#E7B58A]/30 flex items-center gap-1 font-display">
                <span class="w-1.5 h-1.5 rounded-full bg-[#C96A2B] animate-pulse"></span>
                Sürükleyip Sıralayabilirsiniz
            </span>
        </div>
        
        @if($galeriler->isEmpty())
            <div class="p-12 text-center border border-dashed border-[#E5E7EB] rounded-2xl bg-[#FAFAFA]">
                <div class="w-16 h-16 bg-[#FFF7ED] text-[#C96A2B] rounded-full flex items-center justify-center mx-auto mb-4 border border-[#E7B58A]/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold font-display text-[#111827]">Galeriniz Henüz Boş</h3>
                <p class="text-xs text-[#6B7280] mt-1.5 max-w-sm mx-auto">
                    Muayenehane odalarınız, giriş salonu veya kullandığınız tıbbi ekipman fotoğraflarını yükleyerek hastalarınıza güven verin.
                </p>
            </div>
        @else
            <div id="gallery-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-5">
                @foreach($galeriler as $galeri)
                    <div data-id="{{ $galeri->id }}" class="group relative bg-[#FAFAFA] border border-[#E5E7EB] rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 cursor-grab active:cursor-grabbing">
                        <!-- Image Container -->
                        <div class="w-full aspect-[4/3] bg-slate-100 overflow-hidden relative">
                            <img src="{{ asset($galeri->resim_yolu) }}" alt="{{ $galeri->baslik ?? 'Hekim Galeri Resmi' }}" class="w-full h-full object-cover">
                            
                            <!-- Premium Overlay Actions -->
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2">
                                <!-- Edit Title Button -->
                                <button onclick="editModal('{{ $galeri->id }}', '{{ addslashes($galeri->baslik) }}')" class="p-2 bg-white rounded-lg text-gray-700 hover:text-[#C96A2B] shadow-sm transform translate-y-2 group-hover:translate-y-0 transition-all duration-200 cursor-pointer" title="Düzenle">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"></path>
                                    </svg>
                                </button>
                                
                                <!-- Delete Button -->
                                <form action="{{ route('hekim.galeriler.destroy', $galeri->id) }}" method="POST" class="inline" onsubmit="return confirm('Bu fotoğrafı silmek istediğinize emin misiniz?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-white rounded-lg text-gray-700 hover:text-red-600 shadow-sm transform translate-y-2 group-hover:translate-y-0 transition-all duration-200 cursor-pointer" title="Sil">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Order Number Badge -->
                            <div class="absolute top-3 left-3 bg-[#111827]/60 backdrop-blur-sm text-white px-2 py-0.5 rounded-lg text-[9px] font-bold font-display select-none">
                                Sıra: <span class="order-badge">{{ $galeri->sira }}</span>
                            </div>
                        </div>
                        
                        <!-- Details Area -->
                        <div class="p-3 border-t border-[#E5E7EB]">
                            <p class="text-xs font-bold text-[#111827] truncate font-display" title="{{ $galeri->baslik ?? 'Başlıksız Görsel' }}">
                                {{ $galeri->baslik ?? 'Başlıksız Görsel' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Edit Details Modal -->
<div id="editDetailsModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
    <div id="editModalContainer" class="bg-white rounded-3xl border border-[#E5E7EB] shadow-2xl max-w-md w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-[#E5E7EB] flex justify-between items-center bg-[#FAFAFA]">
            <h3 class="text-sm font-bold font-display text-[#111827] uppercase tracking-wider">Fotoğrafı Düzenle</h3>
            <button onclick="closeEditModal()" class="text-[#6B7280] hover:text-[#111827] cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="editForm" action="" method="POST">
            @csrf
            <div class="p-6 space-y-4 text-left">
                <!-- Title input -->
                <div class="space-y-1.5">
                    <label for="edit_baslik" class="text-xs font-bold text-[#4B5563] uppercase tracking-wider font-display">Açıklama / Başlık</label>
                    <input type="text" name="baslik" id="edit_baslik" class="w-full px-4 py-3 border border-[#E5E7EB] rounded-xl text-xs font-medium focus:ring-1 focus:ring-[#C96A2B] focus:border-[#C96A2B] bg-[#FAFAFA] font-sans outline-none transition-colors" placeholder="Fotoğraf için kısa bir açıklama yazın">
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-[#E5E7EB] flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-white hover:bg-slate-100 border border-[#E5E7EB] rounded-xl text-xs font-bold text-gray-700 transition-colors font-display cursor-pointer">Vazgeç</button>
                <button type="submit" class="px-4 py-2 bg-[#C96A2B] hover:bg-[#B55A20] rounded-xl text-xs font-bold text-white transition-colors font-display shadow-sm cursor-pointer">Güncelle</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('script')
<!-- Sortable.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
    // Preview selected files before upload with title input fields
    const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('previewContainer');
    const previewList = document.getElementById('previewList');
    const clearFiles = document.getElementById('clearFiles');
    const uploadBtn = document.getElementById('uploadBtn');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const files = Array.from(this.files);
            previewList.innerHTML = '';
            
            if (files.length > 0) {
                previewContainer.classList.remove('hidden');
                uploadBtn.disabled = false;
                
                files.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const item = document.createElement('div');
                        item.className = 'flex items-center gap-3 p-3 bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl text-left';
                        item.innerHTML = `
                            <div class="w-12 h-12 rounded-lg overflow-hidden bg-slate-100 border shrink-0">
                                <img src="${e.target.result}" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 space-y-1">
                                <div class="text-[10px] font-bold text-[#4B5563] truncate">${file.name}</div>
                                <input type="text" name="basliklar[${index}]" placeholder="Açıklama girin (opsiyonel)" class="w-full px-2.5 py-1.5 border border-[#E5E7EB] rounded-lg text-[10px] font-medium bg-white focus:border-[#C96A2B] outline-none">
                            </div>
                        `;
                        previewList.appendChild(item);
                    }
                    reader.readAsDataURL(file);
                });
            } else {
                resetUploadForm();
            }
        });
    }

    if (clearFiles) {
        clearFiles.addEventListener('click', resetUploadForm);
    }

    function resetUploadForm() {
        if (fileInput) fileInput.value = '';
        previewList.innerHTML = '';
        previewContainer.classList.add('hidden');
        uploadBtn.disabled = true;
    }

    // Sortable Grid Logic
    document.addEventListener('DOMContentLoaded', function() {
        const grid = document.getElementById('gallery-grid');
        if (grid) {
            Sortable.create(grid, {
                animation: 200,
                ghostClass: 'opacity-40',
                onEnd: function() {
                    const ids = [];
                    const items = grid.querySelectorAll('[data-id]');
                    items.forEach((item, index) => {
                        ids.push(item.getAttribute('data-id'));
                        
                        // Update order badge locally instantly
                        const badge = item.querySelector('.order-badge');
                        if (badge) badge.innerText = index + 1;
                    });
                    
                    // Send sorting order to the backend via AJAX
                    fetch('{{ route('hekim.galeriler.sirala') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: ids })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof toastAc === 'function') {
                                toastAc('Fotoğraf sıralaması başarıyla güncellendi.', 'basarili');
                            }
                        } else {
                            if (typeof toastAc === 'function') {
                                toastAc('Sıralama güncellenirken bir hata oluştu.', 'hata');
                            }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        if (typeof toastAc === 'function') {
                            toastAc('Sunucuyla bağlantı kurulamadı.', 'hata');
                        }
                    });
                }
            });
        }
    });

    // Edit Details Modal
    const editModalEl = document.getElementById('editDetailsModal');
    const editModalContainer = document.getElementById('editModalContainer');
    const editForm = document.getElementById('editForm');
    const editBaslikInput = document.getElementById('edit_baslik');

    function editModal(id, baslik) {
        if (editModalEl && editForm && editBaslikInput) {
            // Set action URL dynamically
            editForm.action = `/hekim/galeri/${id}/guncelle`;
            editBaslikInput.value = baslik || '';
            
            editModalEl.classList.remove('hidden');
            setTimeout(() => {
                editModalContainer.classList.remove('scale-95', 'opacity-0');
                editModalContainer.classList.add('scale-100', 'opacity-100');
            }, 50);
        }
    }

    function closeEditModal() {
        if (editModalEl && editModalContainer) {
            editModalContainer.classList.remove('scale-100', 'opacity-100');
            editModalContainer.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                editModalEl.classList.add('hidden');
            }, 150);
        }
    }
</script>
@endsection
