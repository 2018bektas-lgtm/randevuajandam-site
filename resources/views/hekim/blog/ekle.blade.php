@extends('hekim.layout')

@section('baslik', 'Yeni Blog Yazısı Ekle - Hekim Paneli')
@section('sayfa_baslik', 'Yeni Blog Yazısı Ekle')

@section('icerik')
<div class="mb-6">
    <a href="{{ route('hekim.bloglar.index') }}" class="inline-flex items-center gap-1 text-xs text-[#6B7280] hover:text-[#C96A2B] font-semibold transition-colors font-display">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"></path>
        </svg>
        Yazılara Geri Dön
    </a>
</div>

<div class="bg-white rounded-2xl border border-[#E5E7EB] p-8 shadow-sm">
    <form action="{{ route('hekim.bloglar.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Left & Center: Main details -->
            <div class="md:col-span-2 space-y-6">
                <!-- Title -->
                <div class="space-y-1.5">
                    <label for="baslik" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Makale Başlığı</label>
                    <input type="text" name="baslik" id="baslik" value="{{ old('baslik') }}" required placeholder="Başlık girin..."
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                </div>

                <!-- Content -->
                <div class="space-y-1.5">
                    <label for="icerik" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Makale İçeriği</label>
                    <textarea name="icerik" id="icerik" rows="12" required placeholder="Makalenizin içeriğini buraya yazın..."
                              class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all"></textarea>
                </div>
            </div>

            <!-- Right Column: Settings & SEO -->
            <div class="space-y-6">
                <!-- Yayın Durumu (iOS style toggle) -->
                <div class="space-y-1.5 flex items-center justify-between p-4.5 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="max-w-[150px]">
                        <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Yayın Durumu</label>
                        <span class="text-[9px] text-[#6B7280]">Profil sayfanızda hemen yayınlansın mı?</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="aktif_mi" id="aktif_mi" value="1" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                    </label>
                </div>

                <!-- Kapak Resmi -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Kapak Resmi</label>
                    <div class="flex flex-col items-center gap-4">
                        <!-- Image Preview Frame -->
                        <div class="w-full h-44 bg-slate-50 rounded-2xl border border-dashed border-[#E5E7EB] flex items-center justify-center overflow-hidden relative group">
                            <img id="previewBlogImage" src="" alt="Önizleme" class="w-full h-full object-cover hidden">
                            <div id="uploadPlaceholder" class="text-center p-6 flex flex-col items-center gap-1.5 cursor-pointer" onclick="document.getElementById('resim').click()">
                                <svg class="w-8 h-8 text-slate-400 group-hover:text-[#C96A2B] transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="block text-xs font-semibold text-slate-500 font-display">Resim Seçin</span>
                                <span class="block text-[9px] text-slate-400">JPG, PNG, WEBP veya GIF (Maks. 10MB)</span>
                            </div>
                            <button type="button" class="absolute right-3 top-3 bg-black/60 hover:bg-black/80 text-white p-1.5 rounded-lg text-xs hidden" id="removeImageBtn" onclick="clearSelectedImage()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <input type="file" name="resim" id="resim" accept="image/*" class="hidden" onchange="previewBlogImageHandler(this)">
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="bg-slate-50/50 rounded-2xl border border-[#E5E7EB] p-5 space-y-4">
                    <div class="flex items-center gap-2 border-b border-[#E5E7EB] pb-3">
                        <svg class="w-4.5 h-4.5 text-[#C96A2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.774 4.774zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-xs font-bold font-display text-[#111827] uppercase tracking-wider">SEO Ayarları</span>
                    </div>
                    
                    <!-- Meta Başlık -->
                    <div class="space-y-1.5">
                        <label for="meta_baslik" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Meta Başlık (Title)</label>
                        <input type="text" name="meta_baslik" id="meta_baslik" value="{{ old('meta_baslik') }}" placeholder="Arama motoru başlığı..."
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>

                    <!-- Meta Açıklama -->
                    <div class="space-y-1.5">
                        <label for="meta_aciklama" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Meta Açıklama (Description)</label>
                        <textarea name="meta_aciklama" id="meta_aciklama" rows="3" placeholder="Makalenin kısa arama sonucu özeti..."
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all resize-none"></textarea>
                    </div>

                    <!-- Meta Anahtar Kelimeler (Tag System) -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Anahtar Kelimeler (Keywords)</label>
                        
                        <!-- Hidden Input -->
                        <input type="hidden" name="meta_anahtar_kelimeler" id="meta_anahtar_kelimeler" value="{{ old('meta_anahtar_kelimeler') }}">
                        
                        <!-- Visual Tag Container -->
                        <div id="tagContainer" 
                             class="w-full px-3 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus-within:border-[#C96A2B] focus-within:ring-1 focus-within:ring-[#C96A2B] text-xs transition-all flex flex-wrap gap-2 items-center min-h-[42px] cursor-text">
                            <!-- JavaScript will dynamically render tags here -->
                            <input type="text" id="tagInput" placeholder="Kelime ekleyin..." 
                                   class="flex-grow bg-transparent border-0 focus:border-0 focus:ring-0 focus:ring-offset-0 focus:outline-none outline-none ring-0 text-xs py-0.5 placeholder-gray-400 min-w-[120px] shadow-none focus:shadow-none">
                        </div>
                        <span class="text-[9px] text-gray-400">Kelime yazıp <strong>Enter</strong> veya <strong>Virgül (,)</strong> tuşuna basarak ekleyin.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submission Buttons -->
        <div class="flex justify-end gap-3.5 pt-4 border-t border-[#E5E7EB]">
            <a href="{{ route('hekim.bloglar.index') }}" 
               class="px-6 py-3 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#6B7280] font-bold text-xs uppercase tracking-wider transition-all font-display text-center select-none shadow-sm cursor-pointer">
                Vazgeç
            </a>
            <button type="submit" 
                    class="px-8 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                Yazıyı Kaydet
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // CKEditor Initialization
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.config.versionCheck = false;
            CKEDITOR.replace('icerik', {
                language: 'tr',
                height: 350,
                removeButtons: 'About',
                uiColor: '#FFFFFF',
                allowedContent: true
            });
        }

        // Tag System Initialization
        const hiddenInput = document.getElementById('meta_anahtar_kelimeler');
        const tagContainer = document.getElementById('tagContainer');
        const tagInput = document.getElementById('tagInput');
        
        let tags = [];
        
        // Load existing tags from hidden input value
        if (hiddenInput && hiddenInput.value.trim() !== '') {
            tags = hiddenInput.value.split(',').map(tag => tag.trim()).filter(tag => tag !== '');
            renderTags();
        }
        
        function renderTags() {
            // Remove existing tag badges (keep the input field)
            const badges = tagContainer.querySelectorAll('.tag-badge');
            badges.forEach(badge => badge.remove());
            
            // Render new badges
            tags.forEach((tag, index) => {
                const badge = document.createElement('span');
                badge.className = 'tag-badge inline-flex items-center gap-1.5 px-2.5 py-1 bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/35 rounded-full text-[10px] font-bold font-display select-none transition-all hover:bg-[#FFF2E2]';
                badge.innerHTML = `
                    <span>${tag}</span>
                    <button type="button" class="tag-remove text-[#C96A2B] hover:text-red-600 transition-colors focus:outline-none font-bold text-xs" data-index="${index}">&times;</button>
                `;
                tagContainer.insertBefore(badge, tagInput);
            });
            
            // Update hidden input value
            hiddenInput.value = tags.join(',');
        }
        
        // Add tag when user presses Enter or Comma
        tagInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addTag();
            }
        });

        // Add tag on input blur (unfocused) if there is text
        tagInput.addEventListener('blur', function() {
            addTag();
        });
        
        function addTag() {
            const val = tagInput.value.trim().replace(/,/g, '');
            if (val !== '' && !tags.includes(val)) {
                tags.push(val);
                renderTags();
            }
            tagInput.value = '';
        }
        
        // Remove tag event listener on click
        tagContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('tag-remove')) {
                const index = parseInt(e.target.getAttribute('data-index'));
                tags.splice(index, 1);
                renderTags();
                tagInput.focus();
            }
        });
        
        // Focus text input when container is clicked
        tagContainer.addEventListener('click', function(e) {
            if (e.target === tagContainer) {
                tagInput.focus();
            }
        });
    });

    function previewBlogImageHandler(input) {
        const preview = document.getElementById('previewBlogImage');
        const placeholder = document.getElementById('uploadPlaceholder');
        const removeBtn = document.getElementById('removeImageBtn');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview && placeholder && removeBtn) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                    removeBtn.classList.remove('hidden');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function clearSelectedImage() {
        const fileInput = document.getElementById('resim');
        const preview = document.getElementById('previewBlogImage');
        const placeholder = document.getElementById('uploadPlaceholder');
        const removeBtn = document.getElementById('removeImageBtn');
        
        if (fileInput && preview && placeholder && removeBtn) {
            fileInput.value = '';
            preview.src = '';
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
            removeBtn.classList.add('hidden');
        }
    }
</script>
@endsection
