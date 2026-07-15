@extends('yonetim.layout')

@section('baslik', 'SEO & Site Ayarları - Randevu Ajandam')
@section('sayfa_baslik', 'SEO & Site Ayarları')

@section('icerik')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('yonetim.seo.post') }}" method="POST" class="space-y-8">
            @csrf

            <!-- Meta SEO Card -->
            <div class="p-8 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-6 pb-3 border-b border-slate-100">Arama Motoru Optimizasyonu (SEO) Bilgileri</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <!-- Meta Başlık -->
                    <div class="space-y-1.5">
                        <label for="meta_baslik" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Meta Başlık (Title)</label>
                        <input type="text" name="meta_baslik" id="meta_baslik" value="{{ old('meta_baslik', $seo->meta_baslik) }}" 
                               placeholder="Sitenin varsayılan tarayıcı başlığı"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>

                    <!-- Meta Yazar -->
                    <div class="space-y-1.5">
                        <label for="meta_yazar" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Meta Yazar (Author)</label>
                        <input type="text" name="meta_yazar" id="meta_yazar" value="{{ old('meta_yazar', $seo->meta_yazar) }}" 
                               placeholder="Sitenin sahibi / geliştirici şirket"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>

                    <!-- Meta Anahtar Kelimeler (Tag System) -->
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Anahtar Kelimeler (Keywords)</label>
                        
                        <!-- Hidden Input -->
                        <input type="hidden" name="meta_anahtar_kelimeler" id="meta_anahtar_kelimeler" value="{{ old('meta_anahtar_kelimeler', $seo->meta_anahtar_kelimeler) }}">
                        
                        <!-- Visual Tag Container -->
                        <div id="tagContainer" 
                             class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus-within:border-[#C96A2B] focus-within:ring-1 focus-within:ring-[#C96A2B] text-xs transition-all flex flex-wrap gap-2 items-center min-h-[42px] cursor-text">
                            <!-- JavaScript will dynamically render tags here -->
                            <input type="text" id="tagInput" placeholder="Kelime ekleyin ve Enter'a basın..." 
                                   class="flex-grow bg-transparent border-0 focus:border-0 focus:ring-0 focus:ring-offset-0 focus:outline-none outline-none ring-0 text-xs py-0.5 placeholder-gray-400 min-w-[150px] shadow-none focus:shadow-none">
                        </div>
                        <span class="text-[10px] text-gray-400">Kelimeyi yazıp <strong>Enter</strong> veya <strong>Virgül (,)</strong> tuşuna basarak etikete dönüştürün.</span>
                    </div>

                    <!-- Meta Açıklama -->
                    <div class="space-y-1.5 sm:col-span-2">
                        <label for="meta_aciklama" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Meta Açıklama (Description)</label>
                        <textarea name="meta_aciklama" id="meta_aciklama" rows="4" 
                                  placeholder="Arama motoru sonuçlarında görünecek kısa site tanıtım açıklaması"
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all resize-none">{{ old('meta_aciklama', $seo->meta_aciklama) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Platform analitık / reklam (sadece ana site — senin kodların) -->
            <div class="p-8 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-2 pb-3 border-b border-slate-100">Analitik &amp; Reklam (Ana platform)</h3>
                <p class="text-xs text-[#6B7280] mb-6 leading-relaxed">
                    Bu kodlar <strong>yalnızca ana Randevu Ajandam sitesinde</strong> çalışır.
                    Hekim/klinik public siteleri kendi panellerinden ayrı kod girer; buradaki kodlar onlara kopyalanmaz.
                    GTM doluysa GA4/Meta genelde GTM içinden yönetilir (yine de doğrudan ID de ekleyebilirsiniz).
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label for="gtm_container_id" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Google Tag Manager</label>
                        <input type="text" name="gtm_container_id" id="gtm_container_id" value="{{ old('gtm_container_id', $seo->gtm_container_id) }}"
                               placeholder="GTM-XXXXXXX"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all font-mono">
                        @error('gtm_container_id')<p class="text-[11px] text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="ga4_measurement_id" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Google Analytics 4</label>
                        <input type="text" name="ga4_measurement_id" id="ga4_measurement_id" value="{{ old('ga4_measurement_id', $seo->ga4_measurement_id) }}"
                               placeholder="G-XXXXXXXXXX"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all font-mono">
                        @error('ga4_measurement_id')<p class="text-[11px] text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="meta_pixel_id" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Meta (Facebook) Pixel</label>
                        <input type="text" name="meta_pixel_id" id="meta_pixel_id" value="{{ old('meta_pixel_id', $seo->meta_pixel_id) }}"
                               placeholder="1234567890"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all font-mono">
                        @error('meta_pixel_id')<p class="text-[11px] text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="google_ads_id" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Google Ads</label>
                        <input type="text" name="google_ads_id" id="google_ads_id" value="{{ old('google_ads_id', $seo->google_ads_id) }}"
                               placeholder="AW-XXXXXXXXXX"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all font-mono">
                        @error('google_ads_id')<p class="text-[11px] text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100">
                    <h4 class="text-sm font-bold font-display text-[#111827] mb-1">Google reCAPTCHA v3</h4>
                    <p class="text-[11px] text-[#6B7280] mb-4 leading-relaxed">
                        Ana site randevu / kayıt formları için. Google reCAPTCHA admin panelinden <strong>v3</strong> anahtarı oluşturun
                        (domain: ana siteniz). Public hekim siteleri kendi panellerinden ayrı anahtar girer.
                    </p>
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 mb-4 cursor-pointer">
                        <input type="checkbox" name="recaptcha_enabled" value="1" class="rounded border-slate-300 text-[#C96A2B]"
                               @checked(old('recaptcha_enabled', $seo->recaptcha_enabled ?? true))>
                        reCAPTCHA aktif
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Site Key (v3)</label>
                            <input type="text" name="recaptcha_site_key" value="{{ old('recaptcha_site_key', $seo->recaptcha_site_key) }}"
                                   placeholder="6L..." class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs font-mono">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Secret Key (v3)</label>
                            <input type="password" name="recaptcha_secret_key" value="{{ old('recaptcha_secret_key', $seo->recaptcha_secret_key) }}"
                                   placeholder="6L..." autocomplete="new-password" class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs font-mono">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Submission Action -->
            <div class="flex justify-end gap-3.5">
                <a href="{{ route('yonetim.panel') }}" 
                   class="px-6 py-3 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#6B7280] font-bold text-xs uppercase tracking-wider transition-all font-display text-center select-none shadow-sm cursor-pointer">
                    Geri Dön
                </a>
                <button type="submit" 
                        class="px-8 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                    Ayarları Kaydet
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                    badge.className = 'tag-badge inline-flex items-center gap-1.5 px-3 py-1 bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/35 rounded-full text-[11px] font-bold font-display select-none transition-all hover:bg-[#FFF2E2]';
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
    </script>
@endsection
