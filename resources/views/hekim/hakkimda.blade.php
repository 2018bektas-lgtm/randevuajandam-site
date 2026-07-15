@extends('hekim.layout')

@section('baslik', 'Hakkımda Sayfam - Randevu Ajandam')
@section('sayfa_baslik', 'Hakkımda Sayfam')

@section('head')
    <style>
        /* Custom Premium Multiselect Widget Styles */
        .multiselect-dropdown-open {
            border-color: #C96A2B !important;
            box-shadow: 0 0 0 4px rgba(201, 106, 43, 0.1) !important;
        }
        .multiselect-badge {
            animation: badge-appear 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes badge-appear {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        /* Dropdown transition animation */
        #multiselect-dropdown {
            opacity: 0;
            transform: translateY(-8px) scale(0.98);
            transition: opacity 0.18s cubic-bezier(0.16, 1, 0.3, 1), transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
        }
        #multiselect-dropdown.show {
            display: flex !important;
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }
    </style>
@endsection

@section('icerik')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('hekim.hakkimda.post') }}" method="POST" class="space-y-8">
            @csrf

            <!-- Professional Info Card -->
            <div class="p-8 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-6 pb-3 border-b border-slate-100">Özgeçmiş & Uzmanlık Bilgileri</h3>
                
                <div class="grid grid-cols-1 gap-5 mb-5">
                    <!-- Branşlar / Uzmanlık Alanları (Özel Çoklu Seçim Widget) -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Branş / Uzmanlık Alanı (Birden Fazla Seçebilirsiniz)</label>
                        @php
                            $selectedBranslar = $doktor->branslar->pluck('id')->toArray();
                        @endphp
                        <div class="relative w-full" id="custom-multiselect-container">
                            <!-- Input Box (Trigger) -->
                            <div id="multiselect-trigger" class="w-full min-h-[46px] pl-4 pr-10 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus-within:border-[#C96A2B] focus-within:ring-1 focus-within:ring-[#C96A2B] text-xs transition-all flex flex-wrap items-center gap-2 cursor-pointer select-none relative">
                                <span class="text-gray-400 py-1" id="multiselect-placeholder">Branş seçin veya aratın...</span>
                                
                                <!-- Chevron Icon -->
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-[#9CA3AF] transition-transform duration-200" id="multiselect-chevron">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Dropdown Menu -->
                            <div id="multiselect-dropdown" class="absolute left-0 right-0 mt-2 bg-white border border-[#E5E7EB] rounded-2xl shadow-xl z-50 hidden max-h-64 overflow-y-auto flex flex-col p-2">
                                <!-- Search Input Container -->
                                <div class="p-1 border-b border-slate-100 relative">
                                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
                                    </svg>
                                    <input type="text" id="multiselect-search" placeholder="Branş adı ile ara..." 
                                           class="w-full pl-9 pr-3 py-2.5 rounded-xl bg-slate-50 border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] text-xs transition-all">
                                </div>
                                <!-- Options -->
                                <div class="flex-grow overflow-y-auto p-1 space-y-0.5 mt-1" id="multiselect-options">
                                    @foreach($branslar as $brans)
                                        <div class="multiselect-option flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs text-slate-700 hover:bg-slate-50 cursor-pointer select-none transition-colors" 
                                             data-id="{{ $brans->id }}" data-name="{{ $brans->ad }}">
                                            <span>{{ $brans->ad }}</span>
                                            <!-- Check icon -->
                                            <svg class="w-4 h-4 text-[#C96A2B] hidden" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                            </svg>
                                        </div>
                                    @endforeach
                                    <!-- No Results -->
                                    <div id="multiselect-no-results" class="p-4 text-center text-xs text-gray-400 hidden">
                                        Eşleşen branş bulunamadı.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden Fields -->
                            <div id="multiselect-hidden-inputs"></div>
                        </div>
                    </div>

                    <!-- Mezuniyet / Eğitim Bilgileri (Etiket Sistemi) -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Mezuniyet / Eğitim Bilgisi (Etiket Sistemi)</label>
                        <div class="flex gap-2">
                            <input type="text" id="mezuniyet_input" placeholder="Örn: Hacettepe Üniversitesi Tıp Fakültesi (2005) (Yazıp Ekle'ye basın)"
                                   class="flex-grow px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                            <button type="button" onclick="addMezuniyetTag()"
                                    class="px-4 py-2.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all select-none">
                                Ekle
                            </button>
                        </div>
                        <div id="mezuniyet_tags_container" class="flex flex-wrap gap-2 p-3 border border-[#E5E7EB] rounded-xl bg-slate-50/50 min-h-[50px]">
                            <!-- Dinamik etiketler buraya gelecektir -->
                        </div>
                        <div id="mezuniyet_hidden_fields"></div>
                    </div>

                    <!-- Klinik / Muayenehane Adı (Kaldırıldı) -->
                    <input type="hidden" name="klinik_adi" id="klinik_adi" value="">
                </div>

                <!-- Biyografi / Özgeçmiş (CKEditor) -->
                <div class="space-y-1.5">
                    <label for="biyografi" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Özgeçmiş / Detaylı Bilgi (Hakkımda)</label>
                    <textarea name="biyografi" id="biyografi" rows="10" 
                              class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] text-xs transition-all">{{ old('biyografi', $doktor->biyografi) }}</textarea>
                </div>
            </div>

            <!-- Form Submission Action -->
            <div class="flex justify-end gap-3.5">
                <a href="{{ route('hekim.panel') }}" 
                   class="px-6 py-3 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#6B7280] font-bold text-xs uppercase tracking-wider transition-all font-display text-center select-none shadow-sm cursor-pointer">
                    Geri Dön
                </a>
                <button type="submit" 
                        class="px-8 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                    Bilgilerimi Kaydet
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof CKEDITOR !== 'undefined') {
                CKEDITOR.config.versionCheck = false;
                CKEDITOR.replace('biyografi', {
                    language: 'tr',
                    height: 300,
                    removeButtons: 'About',
                    uiColor: '#FFFFFF',
                    allowedContent: true
                });
            }

            // Load existing graduation tags
            @if(is_array($doktor->mezuniyet))
                @foreach($doktor->mezuniyet as $tag)
                    createMezuniyetTag("{{ $tag }}");
                @endforeach
            @endif

            // Support enter key in tag input
            document.getElementById('mezuniyet_input')?.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addMezuniyetTag();
                }
            });
        });

        // Tag based graduation info system
        function addMezuniyetTag() {
            const input = document.getElementById('mezuniyet_input');
            const value = input.value.trim();
            if (!value) return;

            createMezuniyetTag(value);
            input.value = '';
        }

        function createMezuniyetTag(text) {
            const container = document.getElementById('mezuniyet_tags_container');
            const hiddenContainer = document.getElementById('mezuniyet_hidden_fields');

            // Create tag badge
            const tag = document.createElement('span');
            tag.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-50 border border-orange-100 text-xs font-semibold text-[#C96A2B] transition-all';
            tag.innerHTML = `
                <span>${text}</span>
                <button type="button" class="text-orange-400 hover:text-orange-600 focus:outline-none font-bold ml-1" onclick="removeMezuniyetTag(this, '${text.replace(/'/g, "\\'")}')">×</button>
            `;

            // Create hidden input
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'mezuniyet[]';
            hiddenInput.value = text;

            container.appendChild(tag);
            hiddenContainer.appendChild(hiddenInput);
        }

        function removeMezuniyetTag(btn, text) {
            const tag = btn.parentElement;
            tag.remove();

            const hiddenContainer = document.getElementById('mezuniyet_hidden_fields');
            const inputs = hiddenContainer.querySelectorAll('input');
            inputs.forEach(input => {
                if (input.value === text) {
                    input.remove();
                }
            });
        }
    </script>

    <!-- Custom Searchable Multiselect JS logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('custom-multiselect-container');
            if (!container) return;

            const trigger = document.getElementById('multiselect-trigger');
            const dropdown = document.getElementById('multiselect-dropdown');
            const searchInput = document.getElementById('multiselect-search');
            const optionsContainer = document.getElementById('multiselect-options');
            const placeholder = document.getElementById('multiselect-placeholder');
            const hiddenInputsContainer = document.getElementById('multiselect-hidden-inputs');
            const chevron = document.getElementById('multiselect-chevron');
            const noResults = document.getElementById('multiselect-no-results');
            
            let selectedItems = []; // Array of { id, name }

            // Turkish characters helper function
            function trNormalize(str) {
                return str
                    .replace(/İ/g, 'i')
                    .replace(/I/g, 'ı')
                    .replace(/Ş/g, 'ş')
                    .replace(/Ğ/g, 'ğ')
                    .replace(/Ü/g, 'ü')
                    .replace(/Ö/g, 'ö')
                    .replace(/Ç/g, 'ç')
                    .toLowerCase();
            }

            // Toggle dropdown
            trigger.addEventListener('click', function(e) {
                if (e.target.closest('.badge-remove')) return; // Don't open if clicked remove button
                
                const isOpen = dropdown.classList.contains('show');
                if (isOpen) {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            });

            function openDropdown() {
                dropdown.classList.remove('hidden');
                setTimeout(() => {
                    dropdown.classList.add('show');
                    trigger.classList.add('multiselect-dropdown-open');
                    if (chevron) chevron.style.transform = 'translateY(-50%) rotate(180deg)';
                    searchInput.focus();
                }, 10);
            }

            function closeDropdown() {
                dropdown.classList.remove('show');
                trigger.classList.remove('multiselect-dropdown-open');
                if (chevron) chevron.style.transform = 'translateY(-50%) rotate(0deg)';
                setTimeout(() => {
                    if (!dropdown.classList.contains('show')) {
                        dropdown.classList.add('hidden');
                    }
                }, 180);
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!container.contains(e.target)) {
                    closeDropdown();
                }
            });

            // Search filter
            searchInput.addEventListener('input', function(e) {
                const query = trNormalize(e.target.value.trim());
                const options = optionsContainer.querySelectorAll('.multiselect-option');
                let hasVisible = false;

                options.forEach(opt => {
                    const name = trNormalize(opt.getAttribute('data-name'));
                    if (name.includes(query)) {
                        opt.style.display = 'flex';
                        hasVisible = true;
                    } else {
                        opt.style.display = 'none';
                    }
                });

                if (hasVisible) {
                    if (noResults) noResults.classList.add('hidden');
                } else {
                    if (noResults) noResults.classList.remove('hidden');
                }
            });

            // Handle option click
            optionsContainer.querySelectorAll('.multiselect-option').forEach(opt => {
                opt.addEventListener('click', function() {
                    const id = opt.getAttribute('data-id');
                    const name = opt.getAttribute('data-name');
                    toggleSelection(id, name, opt);
                });
            });

            function toggleSelection(id, name, element) {
                const index = selectedItems.findIndex(item => item.id === id);
                if (index === -1) {
                    // Add item
                    selectedItems.push({ id, name });
                    element.querySelector('svg').classList.remove('hidden');
                    element.classList.add('bg-orange-50/70', 'text-[#C96A2B]', 'font-semibold');
                } else {
                    // Remove item
                    selectedItems.splice(index, 1);
                    element.querySelector('svg').classList.add('hidden');
                    element.classList.remove('bg-orange-50/70', 'text-[#C96A2B]', 'font-semibold');
                }
                updateUI();
            }

            function updateUI() {
                // Clear previous trigger badges except placeholder
                const badges = trigger.querySelectorAll('.multiselect-badge');
                badges.forEach(b => b.remove());

                // Clear hidden inputs
                hiddenInputsContainer.innerHTML = '';

                if (selectedItems.length === 0) {
                    placeholder.classList.remove('hidden');
                } else {
                    placeholder.classList.add('hidden');
                    selectedItems.forEach(item => {
                        // Add badge to trigger
                        const badge = document.createElement('span');
                        badge.className = 'multiselect-badge inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-orange-50 border border-orange-100 text-[11px] font-semibold text-[#C96A2B] select-none';
                        badge.innerHTML = `
                            <span>${item.name}</span>
                            <span class="badge-remove text-orange-400 hover:text-orange-600 font-bold cursor-pointer text-xs ml-0.5" data-id="${item.id}">×</span>
                        `;
                        trigger.insertBefore(badge, placeholder);

                        // Add click listener to remove badge
                        badge.querySelector('.badge-remove').addEventListener('click', function(e) {
                            e.stopPropagation();
                            const opt = optionsContainer.querySelector(`.multiselect-option[data-id="${item.id}"]`);
                            toggleSelection(item.id, item.name, opt);
                        });

                        // Add hidden input
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'branslar[]';
                        input.value = item.id;
                        hiddenInputsContainer.appendChild(input);
                    });
                }
            }

            // Initialize with currently selected branches
            const initialBranslar = @json($selectedBranslar);
            if (initialBranslar && initialBranslar.length > 0) {
                initialBranslar.forEach(id => {
                    const opt = optionsContainer.querySelector(`.multiselect-option[data-id="${id}"]`);
                    if (opt) {
                        const name = opt.getAttribute('data-name');
                        toggleSelection(id.toString(), name, opt);
                    }
                });
            }
        });
    </script>
@endsection
