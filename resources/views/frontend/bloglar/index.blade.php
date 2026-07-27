@extends('frontend.layouts.app')

@section('baslik', \App\Support\SeoMeta::blogIndexTitle())
@section('meta_aciklama', \App\Support\SeoMeta::blogIndexDescription())
@section('meta_anahtar_kelimeler', \App\Support\SeoMeta::keywords([
    'sağlık blogu', 'hekim yazıları', 'tıbbi bilgilendirme', 'online randevu', 'randevu ajandam',
]))

@section('icerik')
<style>
    /* Premium Shimmer Sweep */
    .shimmer-sweep {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            115deg,
            transparent 35%,
            rgba(255, 255, 255, 0.4) 48%,
            rgba(255, 255, 255, 0.6) 50%,
            rgba(255, 255, 255, 0.4) 52%,
            transparent 65%
        );
        background-size: 200% 100%;
        background-position: -200% 0;
        mix-blend-mode: overlay;
        pointer-events: none;
        transition: all 0.5s ease;
    }
    .group:hover .shimmer-sweep {
        animation: sweep 1.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    @keyframes sweep {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
</style>

<section class="fe-page relative bg-[#FAFAFA] overflow-hidden">
    <!-- Ambient Background Light Glows -->
    <div class="absolute top-[-10%] right-[-10%] w-[600px] h-[600px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[600px] h-[600px] rounded-full bg-[#C96A2B]/3 blur-[120px] pointer-events-none"></div>

    <div class="fe-container relative z-10">

        <!-- Header Text -->
        <div class="max-w-3xl mx-auto text-center space-y-3 mb-8 md:mb-10">
            <span class="text-xs font-bold text-[#C96A2B] uppercase tracking-widest font-display block">Uzman Hekim Yazıları</span>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-black font-display text-[#111827] tracking-tight leading-tight">
                Hekim Blogları & Sağlık Rehberi
            </h1>
            <p class="text-sm md:text-base text-[#6B7280] leading-relaxed">
                Kayıtlı uzman hekimlerimizin kaleme aldığı özgün sağlık yazılarını, rehberleri ve tedavi süreçlerine dair bilgilendirici içerikleri keşfedin.
            </p>
        </div>

        <!-- Mobile Filter Toggle Button -->
        <button id="mobileFilterToggle" class="lg:hidden w-full mb-6 py-3 px-4 rounded-xl border border-[#E5E7EB] bg-white text-[#111827] font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-sm cursor-pointer">
            <svg class="w-4 h-4 text-[#C96A2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"></path>
            </svg>
            Filtreleri Göster / Gizle
        </button>

        <!-- Main Content Area with Sidebar Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Sidebar Filter Panel (Left Column) -->
            <aside id="filterSidebar" class="hidden lg:block lg:col-span-3 bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-[0_8px_30px_rgba(31,41,55,0.02)] sticky top-6">
                <h3 class="text-sm font-bold text-[#111827] mb-5 uppercase tracking-wider font-display flex items-center gap-2 border-b border-slate-100 pb-3">
                    <svg class="w-4 h-4 text-[#C96A2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"></path>
                    </svg>
                    Arama & Filtreleme
                </h3>

                <form id="filterForm" action="{{ route('frontend.blog.index') }}" method="GET" class="space-y-5">
                    <!-- Search Input -->
                    <div class="space-y-1.5">
                        <label for="arama" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider font-display">Arama</label>
                        <input type="text" name="arama" id="arama" value="{{ request('arama') }}" placeholder="Başlık, içerik veya yazar..."
                               class="w-full px-3 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                    </div>

                    <!-- Specialty/Branch Dropdown -->
                    <div class="space-y-1.5">
                        <label for="brans" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider font-display">Branş / Kategori</label>
                        <select name="brans" id="brans"
                                class="w-full px-3 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                            <option value="">Tüm Branşlar</option>
                            @foreach($branslar as $bransAd)
                                <option value="{{ $bransAd }}" {{ request('brans') == $bransAd ? 'selected' : '' }}>{{ $bransAd }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sorting Dropdown -->
                    <div class="space-y-1.5">
                        <label for="sirala" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider font-display">Sıralama</label>
                        <select name="sirala" id="sirala"
                                class="w-full px-3 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                            <option value="yeni" {{ request('sirala') == 'yeni' ? 'selected' : '' }}>En Yeni Yazılar</option>
                            <option value="populer" {{ request('sirala') == 'populer' ? 'selected' : '' }}>En Çok Okunanlar</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    @if(request()->anyFilled(['arama', 'brans', 'sirala']))
                        <div class="flex flex-col gap-2 pt-2 border-t border-slate-100">
                            <a href="{{ route('frontend.blog.index') }}"
                               class="w-full py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#C96A2B] hover:border-[#C96A2B] font-bold text-xs uppercase tracking-wider transition-all font-display text-center flex items-center justify-center cursor-pointer shadow-sm clear-filters-btn">
                                Filtreleri Temizle
                            </a>
                        </div>
                    @endif

                </form>
            </aside>

            <!-- Listing Results Area (Right Column) -->
            <main class="col-span-1 lg:col-span-9 space-y-6">
                <!-- Toolbar Panel -->
                <div class="bg-white border border-[#E5E7EB] rounded-2xl px-5 py-4 shadow-[0_4px_20px_rgba(0,0,0,0.01)] flex items-center justify-between">
                    <span class="text-xs text-[#6B7280] font-bold font-display uppercase tracking-wider">
                        Toplam <strong id="blogResultsCount" class="text-[#111827] font-extrabold">{{ $toplamBlogSayisi }}</strong> Yazı Bulundu
                    </span>
                </div>

                <div id="resultsListWrapper" class="space-y-6">
                    <div id="resultsListContainer">
                        @include('frontend.bloglar.partials.blog_cards')
                    </div>
                </div>
            </main>
        </div>

    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile Sidebar Filter Toggle
        const mobileFilterToggle = document.getElementById('mobileFilterToggle');
        const filterSidebar = document.getElementById('filterSidebar');

        if (mobileFilterToggle && filterSidebar) {
            mobileFilterToggle.addEventListener('click', function() {
                filterSidebar.classList.toggle('hidden');

                // Smooth scroll to sidebar if opening
                if (!filterSidebar.classList.contains('hidden')) {
                    filterSidebar.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }

        // Active AJAX request abort controller to prevent race conditions
        let activeAbortController = null;

        // Auto-submit form on filter changes
        let formInitialized = false;
        setTimeout(function() {
            formInitialized = true;
        }, 300);

        function triggerFormSubmit() {
            if (formInitialized) {
                fetchBlogData();
            }
        }

        // Form Submit Blocker
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            fetchBlogData();
        });

        // 1. Text Search Input (debounced)
        let searchTimeout;
        $('#arama').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                triggerFormSubmit();
            }, 600);
        });

        // 2. Dropdowns
        $('#brans, #sirala').on('change', function() {
            triggerFormSubmit();
        });

        // 3. Dynamic AJAX search logic
        function fetchBlogData(url = null) {
            if (activeAbortController) {
                activeAbortController.abort();
            }
            activeAbortController = new AbortController();
            const { signal } = activeAbortController;

            if (!url) {
                const params = $('#filterForm').serialize();
                url = `${window.location.pathname}?${params}`;
            }

            // Update browser history URL
            history.pushState(null, '', url);

            // Shimmer state
            $('#resultsListWrapper').addClass('opacity-50 pointer-events-none');

            fetch(url, {
                signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update blog list
                $('#resultsListContainer').html(data.html);

                // Update count
                $('#blogResultsCount').text(data.total);

                // Restore loading states
                $('#resultsListWrapper').removeClass('opacity-50 pointer-events-none');

                // Dynamic visibility of Clear Filters button
                const filterFormValues = $('#filterForm').serializeArray().filter(item => {
                    return item.value !== '' && item.name !== '_token';
                });

                if (filterFormValues.length > 0) {
                    if ($('#filterForm a.clear-filters-btn').length === 0) {
                        $('#filterForm').append(`
                            <div class="flex flex-col gap-2 pt-2 border-t border-slate-100">
                                <a href="${window.location.pathname}"
                                   class="w-full py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#C96A2B] hover:border-[#C96A2B] font-bold text-xs uppercase tracking-wider transition-all font-display text-center flex items-center justify-center cursor-pointer shadow-sm clear-filters-btn">
                                    Filtreleri Temizle
                                </a>
                            </div>
                        `);
                    }
                } else {
                    $('#filterForm .clear-filters-btn').parent().remove();
                }
            })
            .catch(err => {
                if (err.name === 'AbortError') {
                    return;
                }
                console.error('AJAX filtering failed:', err);
                $('#resultsListWrapper').removeClass('opacity-50 pointer-events-none');
            });
        }

        // Intercept AJAX pagination link clicks
        $(document).on('click', '#resultsListContainer .pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            fetchBlogData(url);
        });

        // Dynamic Clear Filters click handler
        $(document).on('click', '.clear-filters-btn', function(e) {
            e.preventDefault();
            $('#arama').val('');
            $('#brans').val('');
            $('#sirala').val('yeni');
            fetchBlogData();
        });
    });
</script>
@endsection
