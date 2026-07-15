<div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
    <h3 class="text-lg font-bold font-display text-[#111827] mb-2">SEO Ayarları</h3>
    <p class="text-xs text-[#6B7280] mb-6">Kliniğinizin Google ve diğer arama motorlarında nasıl görüntüleneceğini yapılandırın.</p>

    <div class="space-y-6">
        <div>
            <label for="meta_baslik" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Meta Başlık (SEO Title)</label>
            <input type="text" name="meta_baslik" id="meta_baslik" value="{{ old('meta_baslik', $klinik->meta_baslik) }}" placeholder="{{ $klinik->ad }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none seo-input" data-target="preview-title" max="60">
            <div class="flex justify-between mt-1">
                <span class="text-[10px] text-[#9CA3AF]">Google aramalarında mavi kalın başlık olarak gösterilir.</span>
                <span class="text-[10px] text-[#9CA3AF] char-counter" data-source="meta_baslik">0 / 60 karakter</span>
            </div>
        </div>

        <div>
            <label for="meta_aciklama" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Meta Açıklama (SEO Description)</label>
            <textarea name="meta_aciklama" id="meta_aciklama" rows="3" placeholder="Kliniğimiz hakkında kısa, ilgi çekici arama motoru açıklaması." class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none resize-none seo-input" data-target="preview-desc" max="160">{{ old('meta_aciklama', $klinik->meta_aciklama) }}</textarea>
            <div class="flex justify-between mt-1">
                <span class="text-[10px] text-[#9CA3AF]">Arama sonuçlarında başlığın altındaki açıklama metnidir.</span>
                <span class="text-[10px] text-[#9CA3AF] char-counter" data-source="meta_aciklama">0 / 160 karakter</span>
            </div>
        </div>

        <!-- Google Search Live Preview -->
        <div class="p-5 rounded-xl border border-[#E5E7EB] bg-[#F9FAFB]">
            <span class="text-[10px] font-bold text-[#9CA3AF] uppercase tracking-wider block mb-3 font-display">Arama Motoru Önizlemesi</span>
            <div class="space-y-1 font-sans">
                <!-- URL/Breadcrumb -->
                <div class="flex items-center gap-1.5 text-xs text-[#202124]">
                    <span class="font-normal">{{ url('/') }}</span>
                    <span class="text-[#5f6368]">></span>
                    <span class="text-[#5f6368]">{{ $klinik->slug }}</span>
                </div>
                <!-- Title -->
                <div class="text-lg text-[#1a0dab] hover:underline cursor-pointer font-medium leading-snug" id="preview-title">
                    {{ $klinik->meta_baslik ?: $klinik->ad }}
                </div>
                <!-- Description -->
                <div class="text-xs text-[#4d5156] leading-relaxed break-words" id="preview-desc">
                    {{ $klinik->meta_aciklama ?: 'Klinik hakkında detaylı bilgi, randevu saatleri, hizmetler ve iletişim bilgileri.' }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.seo-input');

        function updatePreview(input) {
            const targetId = input.dataset.target;
            const targetEl = document.getElementById(targetId);
            const counter = document.querySelector(`.char-counter[data-source="${input.id}"]`);
            
            let val = input.value.trim();
            const maxVal = input.getAttribute('max');

            if (!val) {
                if (input.id === 'meta_baslik') {
                    val = "{{ $klinik->ad }}";
                } else {
                    val = "Klinik hakkında detaylı bilgi, randevu saatleri, hizmetler ve iletişim bilgileri.";
                }
            }

            targetEl.textContent = val;
            
            if (counter) {
                counter.textContent = `${input.value.length} / ${maxVal} karakter`;
                if (input.value.length > maxVal) {
                    counter.classList.add('text-red-500');
                    counter.classList.remove('text-[#9CA3AF]');
                } else {
                    counter.classList.remove('text-red-500');
                    counter.classList.add('text-[#9CA3AF]');
                }
            }
        }

        inputs.forEach(input => {
            updatePreview(input);
            input.addEventListener('input', () => updatePreview(input));
        });
    });
</script>
