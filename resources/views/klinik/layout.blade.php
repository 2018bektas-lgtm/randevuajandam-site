<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('baslik', 'Klinik Yönetim Paneli - Randevu Ajandam')</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/logo.png') }}" type="image/png">
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Flatpickr Datepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* Select2 Premium Theme Override */
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 12px !important;
            background-color: #FAFAFA !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 10px !important;
            font-size: 0.875rem !important;
            font-family: 'Inter', sans-serif !important;
            color: #111827 !important;
            transition: border-color 0.15s, box-shadow 0.15s !important;
        }
        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #C96A2B !important;
            box-shadow: 0 0 0 3px rgba(201, 106, 43, 0.1) !important;
            outline: none !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #111827 !important;
            line-height: 42px !important;
            padding-left: 0 !important;
            padding-right: 24px !important;
            font-size: 0.875rem !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            flex: 1 !important;
            min-width: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9CA3AF !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 10px !important;
        }
        .select2-dropdown {
            border: 1px solid #E5E7EB !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #E5E7EB !important;
            border-radius: 8px !important;
            padding: 6px 10px !important;
            outline: none !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #C96A2B !important;
        }
        .select2 {
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
        }

        /* Scrollbar styles for sidebar */
        .scrollbar-thin::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Brand Font */
        .font-display {
            font-family: 'Outfit', sans-serif;
        }
    </style>
    @yield('extra_css')
@include('partials.sidebar-ysb-theme')
</head>
<body class="text-[#4B5563] antialiased h-screen overflow-hidden flex flex-col md:flex-row relative bg-[#F5F5F4]">

    <!-- Preloader -->
    <div id="preloader" class="fixed inset-0 bg-[#F5F5F4] z-50 flex flex-col items-center justify-center transition-opacity duration-500 ease-out select-none">
        <div class="relative w-28 h-28 flex items-center justify-center">
            <div class="w-full h-full flex items-center justify-center">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Randevu Ajandam"
                     style="mix-blend-mode: multiply;"
                     class="w-full h-full object-contain">
            </div>
        </div>
        <div class="mt-6 flex flex-col items-center gap-2">
            <span class="text-xs font-bold font-display tracking-widest text-[#1F2937] uppercase opacity-75">Klinik Paneli Yükleniyor</span>
            <div class="w-24 h-1 bg-[#E5E7EB] rounded-full overflow-hidden relative">
                <div class="absolute inset-y-0 left-0 bg-[#C96A2B] rounded-full w-12 animate-pulse"></div>
            </div>
        </div>
    </div>

    <!-- Mobile Header -->
    <header class="md:hidden w-full h-16 bg-white border-b border-[#E5E7EB] text-[#111827] flex items-center justify-between px-6 z-30 relative overflow-hidden">
        <div class="flex items-center gap-2.5 relative">
            <span class="font-bold font-display tracking-tight text-base select-none">🏥 Klinik Paneli</span>
        </div>
        <div class="flex items-center gap-2">
            <button id="menuToggle" class="p-2 rounded-lg hover:bg-slate-50 text-[#6B7280] cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </header>

    <!-- Sidebar Overlay for mobile -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/40 z-35 hidden md:hidden backdrop-blur-sm"></div>

    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="ysb fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:static w-[18rem] h-full md:h-screen md:shrink-0 flex flex-col z-40 overflow-hidden">
        <div class="ysb-brand shrink-0">
            <div class="ysb-brand-row">
                @php $klinikBrand = auth('doktor')->user()->klinik; @endphp
                <div class="ysb-brand-mark">
                    @if($klinikBrand && $klinikBrand->logo)
                        <img src="{{ asset($klinikBrand->logo) }}" alt="{{ $klinikBrand->ad }}" style="mix-blend-mode:normal;width:100%;height:100%;object-fit:cover;border-radius:.85rem">
                    @else
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Klinik">
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="ysb-brand-title truncate">{{ $klinikBrand ? $klinikBrand->ad : 'Klinik' }}</div>
                    <div class="ysb-brand-sub">Klinik paneli</div>
                </div>
            </div>
        </div>
        @include('partials.sidebar-nav-klinik')

        <!-- Profile Footer -->
        @php
            $doktorUser = auth('doktor')->user();
            $kisaAd = '';
            if ($doktorUser && $doktorUser->ad_soyad) {
                $words = explode(' ', $doktorUser->ad_soyad);
                $kisaAd = mb_strtoupper(mb_substr($words[0], 0, 1));
                if (count($words) > 1) {
                    $kisaAd .= mb_strtoupper(mb_substr(end($words), 0, 1));
                }
            } else {
                $kisaAd = 'HE';
            }
        @endphp
        <div class="ysb-footer shrink-0">
            <div class="ysb-footer-row">
                <div class="ysb-avatar">{{ $kisaAd }}</div>
                <div class="min-w-0">
                    <div class="ysb-user-name" title="{{ $doktorUser->ad_soyad }}">{{ $doktorUser->ad_soyad }}</div>
                    <div class="ysb-user-role">{{ $doktorUser->klinikSahibiMi() ? 'Klinik Sahibi' : 'Hekim' }}</div>
                </div>
                <div class="ysb-footer-actions">
                    <a href="{{ route('hekim.panel') }}" class="ysb-icon-btn" title="Hekim paneli">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Panel Wrapper -->
    <div class="flex-1 flex flex-col h-full overflow-hidden relative">

        <!-- Header -->
        <header class="hidden md:flex w-full h-20 bg-white border-b border-[#E5E7EB] items-center justify-between px-10 z-20 shrink-0">
            <div>
                <h1 class="text-lg font-bold font-display text-[#111827] tracking-tight">@yield('sayfa_baslik', 'Klinik Yönetimi')</h1>
            </div>

            <!-- Profile & Actions -->
            <div class="flex items-center gap-6">
                <!-- Profile Dropdown -->
                <div class="relative" id="profileDropdownContainer">
                    <button type="button" id="profileDropdownBtn" class="flex items-center gap-2.5 focus:outline-none cursor-pointer group">
                        @if($doktorUser->profil_resmi)
                            <img src="{{ asset($doktorUser->profil_resmi) }}" alt="{{ $doktorUser->ad_soyad }}" class="w-9 h-9 rounded-full object-cover border border-[#E5E7EB] group-hover:border-[#C96A2B]/40 transition-colors">
                        @else
                            <div class="w-9 h-9 rounded-full bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-xs font-bold font-display group-hover:bg-[#FFF2E2] transition-colors">
                                {{ $kisaAd }}
                            </div>
                        @endif
                        <span class="text-xs font-bold text-[#111827] font-display group-hover:text-[#C96A2B] transition-colors max-w-[120px] truncate">
                            {{ $doktorUser->ad_soyad }}
                        </span>
                        <svg class="w-4 h-4 text-[#6B7280] group-hover:text-[#C96A2B] transition-all" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu Box -->
                    <div id="profileDropdownMenu" class="absolute right-0 mt-2.5 w-48 bg-white border border-[#E5E7EB] rounded-2xl shadow-xl py-2 z-50 hidden transition-all transform scale-95 opacity-0 duration-150">
                        <div class="px-4 py-2 border-b border-[#E5E7EB] text-left">
                            <span class="block text-xs font-bold text-[#111827] truncate font-display">{{ $doktorUser->unvan ? $doktorUser->unvan . ' ' : '' }}{{ $doktorUser->ad_soyad }}</span>
                            <span class="block text-[10px] text-[#C96A2B] uppercase tracking-wide font-semibold font-display mt-0.5 truncate">Klinik Sahibi</span>
                        </div>

                        <a href="{{ route('hekim.profil') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-[#4B5563] hover:text-[#C96A2B] hover:bg-[#FFF7ED]/35 font-display transition-colors">
                            Profilimi Düzenle
                        </a>

                        <a href="{{ route('hekim.sifre') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-[#4B5563] hover:text-[#C96A2B] hover:bg-[#FFF7ED]/35 font-display transition-colors">
                            Şifre Değiştir
                        </a>

                        <div class="border-t border-[#E5E7EB] my-1"></div>

                        <!-- Logout -->
                        <form action="{{ route('hekim.cikis') }}" method="POST" class="w-full" onsubmit="onayModalAc(event, this, 'Çıkış yapmak istediğinize emin misiniz?');">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 font-display transition-colors cursor-pointer">
                                Çıkış Yap
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 p-6 md:p-10 overflow-y-auto">
            @yield('icerik')
        </main>
    </div>

    <!-- Script triggers for layouts, toasts, alert modals -->
    <!-- Alert Modals -->
    <div id="alertModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
        <div id="alertModalContainer" class="bg-white rounded-2xl sm:rounded-3xl border border-[#E5E7EB] shadow-[0_25px_60px_-15px_rgba(31,41,55,0.2)] w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]">
            <div class="p-5 sm:p-8 text-center space-y-4 sm:space-y-5 overflow-y-auto flex-1">
                <div id="alertSuccessIcon" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-[#ECFDF5] text-emerald-500 border border-emerald-100 flex items-center justify-center mx-auto shadow-[0_10px_25px_-5px_rgba(16,185,129,0.15)] transition-all duration-300 transform scale-75 opacity-0 hidden">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                    </svg>
                </div>
                <div id="alertErrorIcon" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-[#FEF2F2] text-red-500 border border-red-100 flex items-center justify-center mx-auto shadow-[0_10px_25px_-5px_rgba(239,68,68,0.15)] transition-all duration-300 transform scale-75 opacity-0 hidden">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                    </svg>
                </div>
                <div class="space-y-2">
                    <h3 id="alertModalTitle" class="text-lg font-bold font-display text-[#111827] tracking-tight">Mesaj</h3>
                    <div id="alertModalContent" class="text-sm text-[#4B5563] leading-relaxed font-sans px-2 break-words"></div>
                </div>
            </div>
            <div class="p-4 sm:p-6 bg-slate-50 border-t border-[#E5E7EB] text-center shrink-0">
                <button id="closeAlertBtn" class="w-full py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display shadow-md shadow-orange-500/10 outline-none">
                    Tamam
                </button>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div id="confirmModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
        <div id="confirmModalContainer" class="bg-white rounded-2xl sm:rounded-3xl border border-[#E5E7EB] shadow-[0_25px_60px_-15px_rgba(31,41,55,0.2)] w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]">
            <div class="p-5 sm:p-8 text-center space-y-4 sm:space-y-5 overflow-y-auto flex-1">
                <div class="space-y-2">
                    <h3 class="text-lg font-bold font-display text-[#111827] tracking-tight">Emin misiniz?</h3>
                    <p id="confirmModalMessage" class="text-sm text-[#4B5563] leading-relaxed font-sans px-2">Bu işlemi gerçekleştirmek istediğinize emin misiniz?</p>
                </div>
            </div>
            <div class="p-4 sm:p-6 bg-slate-50 border-t border-[#E5E7EB] flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 shrink-0">
                <button id="confirmCancelBtn" class="w-full sm:flex-1 py-3 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-100 text-[#4B5563] font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display outline-none shadow-sm">
                    İptal Et
                </button>
                <button id="confirmSubmitBtn" class="w-full sm:flex-1 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display shadow-md shadow-orange-500/10 outline-none">
                    Evet, Onayla
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/tr.js"></script>

    <script>
        // Preloader
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.classList.add('opacity-0');
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 500);
            }
        });

        // Mobile Sidebar Toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if(menuToggle && sidebar && overlay) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            });
            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }

        // Profile Dropdown
        const profileDropdownBtn = document.getElementById('profileDropdownBtn');
        const profileDropdownMenu = document.getElementById('profileDropdownMenu');

        if (profileDropdownBtn && profileDropdownMenu) {
            profileDropdownBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (profileDropdownMenu.classList.contains('hidden')) {
                    profileDropdownMenu.classList.remove('hidden');
                    setTimeout(() => {
                        profileDropdownMenu.classList.remove('scale-95', 'opacity-0');
                        profileDropdownMenu.classList.add('scale-100', 'opacity-100');
                    }, 50);
                } else {
                    closeProfileDropdown();
                }
            });

            function closeProfileDropdown() {
                profileDropdownMenu.classList.remove('scale-100', 'opacity-100');
                profileDropdownMenu.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    profileDropdownMenu.classList.add('hidden');
                }, 150);
            }

            document.addEventListener('click', (e) => {
                if (!profileDropdownBtn.contains(e.target) && !profileDropdownMenu.contains(e.target)) {
                    closeProfileDropdown();
                }
            });
        }

        // Message Modals
        function mesajModalAc(mesaj, tip = 'basarili') {
            const modal = document.getElementById('alertModal');
            const container = document.getElementById('alertModalContainer');
            const successIcon = document.getElementById('alertSuccessIcon');
            const errorIcon = document.getElementById('alertErrorIcon');
            const title = document.getElementById('alertModalTitle');
            const content = document.getElementById('alertModalContent');

            if (modal && container && title && content) {
                // Clear icons and styles
                successIcon.classList.add('hidden', 'scale-75', 'opacity-0');
                successIcon.classList.remove('scale-100', 'opacity-100');
                errorIcon.classList.add('hidden', 'scale-75', 'opacity-0');
                errorIcon.classList.remove('scale-100', 'opacity-100');

                if (tip === 'basarili') {
                    successIcon.classList.remove('hidden');
                    title.innerText = 'İşlem Başarılı';
                    title.className = 'text-lg font-bold font-display text-emerald-700';
                    setTimeout(() => {
                        successIcon.classList.remove('scale-75', 'opacity-0');
                        successIcon.classList.add('scale-100', 'opacity-100');
                    }, 50);
                } else {
                    errorIcon.classList.remove('hidden');
                    title.innerText = 'Bir Hata Oluştu';
                    title.className = 'text-lg font-bold font-display text-red-700';
                    setTimeout(() => {
                        errorIcon.classList.remove('scale-75', 'opacity-0');
                        errorIcon.classList.add('scale-100', 'opacity-100');
                    }, 50);
                }

                if (Array.isArray(mesaj)) {
                    content.innerHTML = mesaj.map(m => `<p>${m}</p>`).join('');
                } else {
                    content.innerHTML = `<p>${mesaj}</p>`;
                }

                modal.classList.remove('hidden');
                setTimeout(() => {
                    container.classList.remove('scale-95', 'opacity-0');
                    container.classList.add('scale-100', 'opacity-100');
                }, 50);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('alertModal');
            const container = document.getElementById('alertModalContainer');
            const closeBtn = document.getElementById('closeAlertBtn');

            if (modal && container && closeBtn) {
                closeBtn.addEventListener('click', () => {
                    container.classList.remove('scale-100', 'opacity-100');
                    container.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                });
            }

            @if(session('basari'))
                mesajModalAc("{{ session('basari') }}", 'basarili');
            @endif
            @if(session('basarili'))
                mesajModalAc("{{ session('basarili') }}", 'basarili');
            @endif
            @if(session('hata'))
                mesajModalAc("{{ session('hata') }}", 'hata');
            @endif

            @if($errors->any())
                const errors = [];
                @foreach($errors->all() as $error)
                    errors.push("{{ $error }}");
                @endforeach
                mesajModalAc(errors, 'hata');
            @endif
        });

        // Confirmation Modal
        let activeFormToSubmit = null;
        let activeConfirmCallback = null;

        function onayModalAc(event, form, message, callback = null) {
            if (event) event.preventDefault();
            activeFormToSubmit = form;
            activeConfirmCallback = callback;

            const modal = document.getElementById('confirmModal');
            const container = document.getElementById('confirmModalContainer');
            const msgElement = document.getElementById('confirmModalMessage');

            if (modal && container && msgElement) {
                msgElement.innerText = message;
                modal.classList.remove('hidden');
                setTimeout(() => {
                    container.classList.remove('scale-95', 'opacity-0');
                    container.classList.add('scale-100', 'opacity-100');
                }, 50);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('confirmModal');
            const container = document.getElementById('confirmModalContainer');
            const cancelBtn = document.getElementById('confirmCancelBtn');
            const submitBtn = document.getElementById('confirmSubmitBtn');

            if (modal && container) {
                function closeConfirmModal() {
                    container.classList.remove('scale-100', 'opacity-100');
                    container.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                    activeFormToSubmit = null;
                    activeConfirmCallback = null;
                }

                if (cancelBtn) cancelBtn.addEventListener('click', closeConfirmModal);
                if (submitBtn) {
                    submitBtn.addEventListener('click', function() {
                        if (activeFormToSubmit) {
                            activeFormToSubmit.submit();
                        } else if (activeConfirmCallback) {
                            activeConfirmCallback();
                        }
                        closeConfirmModal();
                    });
                }
            }
        });

        // Initialize flatpickr on date inputs
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('input[type="date"]', {
                locale: 'tr',
                dateFormat: 'Y-m-d',
                minDate: 'today'
            });
            flatpickr('input[type="time"]', {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true
            });
        });

        // Global Select2 Initialization
        $(document).ready(function() {
            $('select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible') || $(this).hasClass('no-select2') || $(this).data('no-select2')) {
                    return;
                }
                $(this).select2({
                    width: '100%',
                    placeholder: $(this).attr('placeholder') || 'Seçiniz...'
                });
            });
        });
    </script>
    @yield('extra_js')

</body>
</html>
