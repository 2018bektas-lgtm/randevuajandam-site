<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hekim Girişi - Randevu Ajandam</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F5F4;
        }
        .font-display {
            font-family: 'Outfit', sans-serif;
        }

        /* 3D Tilt Effect & Container */
        .logo-container {
            perspective: 600px;
            position: relative;
            z-index: 10;
        }
        .logo-image-wrapper {
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            animation: logo-breathing 4s ease-in-out infinite;
        }
        .logo-container:hover .logo-image-wrapper {
            transform: rotateY(18deg) rotateX(-12deg) scale(1.08);
            animation-play-state: paused;
        }

        /* Logo Breathing Animation */
        @keyframes logo-breathing {
            0%, 100% { transform: scale(1) translateY(0); filter: drop-shadow(0 4px 10px rgba(31, 41, 55, 0.05)); }
            50% { transform: scale(1.03) translateY(-3px); filter: drop-shadow(0 12px 24px rgba(201, 106, 43, 0.18)); }
        }

        /* Metallic Shimmer Sweep */
        .shimmer-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                115deg,
                transparent 35%,
                rgba(255, 255, 255, 0.75) 48%,
                rgba(255, 255, 255, 0.9) 50%,
                rgba(255, 255, 255, 0.75) 52%,
                transparent 65%
            );
            background-size: 200% 100%;
            background-position: -200% 0;
            mix-blend-mode: overlay;
            pointer-events: none;
            border-radius: 50%;
            animation: shimmer-sweep-img 5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
        @keyframes shimmer-sweep-img {
            0% { background-position: -200% 0; }
            25% { background-position: 180% 0; }
            100% { background-position: 180% 0; }
        }

        /* Floating Sparkle/Pixel Particles */
        @keyframes float-particle-up-1 {
            0% { transform: translateY(20px) translateX(0) scale(0); opacity: 0; }
            30% { opacity: 0.8; }
            80% { opacity: 0.4; }
            100% { transform: translateY(-30px) translateX(-15px) scale(1.2) rotate(45deg); opacity: 0; }
        }
        @keyframes float-particle-up-2 {
            0% { transform: translateY(30px) translateX(5px) scale(0); opacity: 0; }
            20% { opacity: 0.9; }
            70% { opacity: 0.3; }
            100% { transform: translateY(-40px) translateX(10px) scale(1) rotate(-35deg); opacity: 0; }
        }
        @keyframes float-particle-up-3 {
            0% { transform: translateY(40px) translateX(-5px) scale(0); opacity: 0; }
            40% { opacity: 0.7; }
            90% { opacity: 0.2; }
            100% { transform: translateY(-20px) translateX(-20px) scale(1.4) rotate(90deg); opacity: 0; }
        }

        .float-particle-1 {
            animation: float-particle-up-1 4.5s ease-in-out infinite;
            top: 25%;
            left: 20%;
        }
        .float-particle-2 {
            animation: float-particle-up-2 3.5s ease-in-out infinite;
            animation-delay: 1.2s;
            top: 45%;
            left: 15%;
        }
        .float-particle-3 {
            animation: float-particle-up-3 5.2s ease-in-out infinite;
            animation-delay: 2.3s;
            top: 35%;
            left: 30%;
        }

        /* Pulsating Ambient Light Glow Behind Logo */
        @keyframes pulse-glow {
            0%, 100% { transform: scale(1); opacity: 0.45; }
            50% { transform: scale(1.15); opacity: 0.65; }
        }
        .logo-glow-back {
            position: absolute;
            width: 130px;
            height: 130px;
            background: radial-gradient(circle, rgba(201, 106, 43, 0.4) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
            animation: pulse-glow 3s ease-in-out infinite;
            filter: blur(10px);
        }

        /* Logo Mark Pulse (Entire R Logo breathing) */
        @keyframes logo-pulse {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 4px 10px rgba(31, 41, 55, 0.05)); }
            50% { transform: scale(1.04); filter: drop-shadow(0 12px 28px rgba(201, 106, 43, 0.18)); }
        }
        .logo-pulse-animate {
            animation: logo-pulse 4s ease-in-out infinite;
        }

        /* Shimmering Text Gradient Animation */
        @keyframes text-shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .brand-text-shimmer {
            background: linear-gradient(
                120deg,
                #111827 20%,
                #C96A2B 45%,
                #E7B58A 50%,
                #C96A2B 55%,
                #111827 80%
            );
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: text-shimmer 5s linear infinite;
        }
    </style>
</head>
<body class="text-[#4B5563] antialiased min-h-screen flex items-center justify-center p-4 sm:p-6 relative overflow-x-hidden overflow-y-auto selection:bg-[#C96A2B] selection:text-white">

    <!-- Premium Preloader -->
    <div id="preloader" class="fixed inset-0 bg-[#F5F5F4] z-50 flex flex-col items-center justify-center transition-opacity duration-500 ease-out select-none">
        <div class="relative w-20 h-20 sm:w-28 sm:h-28 flex items-center justify-center logo-container">
            <!-- Ambient Pulsating Light Glow -->
            <div class="logo-glow-back"></div>
            
            <!-- Floating Particle Pixels -->
            <div class="absolute inset-0 pointer-events-none z-20">
                <span class="absolute w-2 h-2 bg-[#C96A2B] rounded-full opacity-0 float-particle-1"></span>
                <span class="absolute w-1.5 h-1.5 bg-[#E7B58A] rounded-sm opacity-0 float-particle-2"></span>
                <span class="absolute w-2.5 h-2.5 bg-[#C96A2B] rounded-sm opacity-0 float-particle-3"></span>
            </div>

            <!-- Logo Image Wrapper -->
            <div class="logo-image-wrapper w-full h-full flex items-center justify-center">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Randevu Ajandam" 
                     style="mix-blend-mode: multiply;" 
                     class="w-full h-full object-contain">
                <div class="shimmer-overlay"></div>
            </div>
        </div>
        
        <!-- Loading Bar -->
        <div class="mt-6 flex flex-col items-center gap-2">
            <span class="text-xs font-bold font-display tracking-widest text-[#1F2937] uppercase opacity-75">Yükleniyor</span>
            <div class="w-24 h-1 bg-[#E5E7EB] rounded-full overflow-hidden relative">
                <div class="absolute inset-y-0 left-0 bg-[#C96A2B] rounded-full loader-progress-bar"></div>
            </div>
        </div>
    </div>

    <!-- Decorative Ambient Glows -->
    <div class="absolute top-[-10%] left-[-10%] w-[600px] h-[600px] rounded-full bg-[#E7B58A]/8 blur-[130px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[600px] h-[600px] rounded-full bg-[#C96A2B]/4 blur-[130px] pointer-events-none"></div>
    <!-- Login Card Container -->
    <div class="relative w-[calc(100%-2rem)] sm:w-full max-w-md bg-white border border-[#E5E7EB] rounded-2xl p-5 sm:p-8 shadow-2xl shadow-slate-200/50 transition-all duration-300 hover:border-[#C96A2B]/20 group mx-auto">

        <!-- Top Colored Accent Bar -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#C96A2B] rounded-t-2xl"></div>

        <!-- Header Section with Animated Motion Logo -->
        <div class="text-center mt-2 mb-6 sm:mb-10 flex flex-col items-center relative">
            <!-- Premium Image-based Logo Wrapper with 3D Tilt and Particles -->
            <div class="relative w-20 h-20 sm:w-28 sm:h-28 mb-4 sm:mb-5 logo-container">
                <!-- Ambient Pulsating Light Glow -->
                <div class="logo-glow-back"></div>
                
                <!-- Floating Particle Pixels (overlay) -->
                <div class="absolute inset-0 pointer-events-none z-20">
                    <span class="absolute w-2 h-2 bg-[#C96A2B] rounded-full opacity-0 float-particle-1"></span>
                    <span class="absolute w-1.5 h-1.5 bg-[#E7B58A] rounded-sm opacity-0 float-particle-2"></span>
                    <span class="absolute w-2.5 h-2.5 bg-[#C96A2B] rounded-sm opacity-0 float-particle-3"></span>
                </div>

                <!-- 3D Tilting Wrapper -->
                <div class="logo-image-wrapper w-full h-full flex items-center justify-center">
                    <!-- The Original Logo Image with multiply blend mode to blend white bg -->
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Randevu Ajandam" 
                         style="mix-blend-mode: multiply;" 
                         class="w-full h-full object-contain transition-all duration-300 select-none">
                    
                    <!-- Metallic Shimmer Sweep Overlay -->
                    <div class="shimmer-overlay"></div>
                </div>
            </div>

            <!-- Brand Title with Shimmer Animation -->
            <h1 class="text-2xl sm:text-3xl font-extrabold font-display tracking-tight brand-text-shimmer z-10 select-none">
                Randevu Ajandam
            </h1>
            <p class="text-[10px] text-[#6B7280] font-bold uppercase tracking-widest mt-2 z-10 font-display opacity-80">
                HEKİM GİRİŞİ
            </p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-500/5 border border-red-500/10 text-red-600 text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                        <span class="font-medium">{{ $error }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('frontend.hekim.giris.post') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Email Input -->
            <div>
                <label for="e_posta" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">E-Posta Adresi</label>
                <input type="email" name="e_posta" id="e_posta" value="{{ old('e_posta') }}" placeholder="hekim@eposta.com" required
                    class="w-full px-4 py-3.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 text-sm transition-all duration-200">
            </div>

            <!-- Password Input -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label for="sifre" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display">Şifre</label>
                    <a href="{{ route('password.request', ['type' => 'hekim']) }}" class="text-[10px] font-bold text-[#C96A2B] hover:underline uppercase tracking-wide font-display">Şifremi Unuttum</a>
                </div>
                <input type="password" name="sifre" id="sifre" placeholder="••••••••" required
                    class="w-full px-4 py-3.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 text-sm transition-all duration-200">
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2.5 cursor-pointer select-none group/check">
                    <input type="checkbox" name="hatirla" class="sr-only peer">
                    <!-- iOS-style Switch -->
                    <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[2.5px] after:left-[2.5px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all after:duration-300 peer-checked:after:translate-x-4 shadow-inner"></div>
                    <span class="text-xs font-semibold text-[#6B7280] group-hover/check:text-[#4B5563] transition-colors">Beni hatırla</span>
                </label>
            </div>

            <!-- Submit Button (Primary Copper Button) -->
            <button type="submit" class="w-full py-3.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-sm tracking-wide transition-all duration-300 hover:shadow-lg hover:shadow-[#C96A2B]/10 active:scale-[0.98] cursor-pointer font-display">
                Giriş Yap
            </button>
        </form>

        <!-- Back to Homepage & Personnel Redirect -->
        <div class="w-full flex items-center justify-between mt-6 select-none">
            <a href="/" class="text-xs font-bold text-[#6B7280] hover:text-[#C96A2B] transition-colors font-display flex items-center gap-1.5">
                ← Ana Sayfaya Dön
            </a>
            <a href="{{ route('personel.giris') }}" class="text-xs font-bold text-[#C96A2B] hover:underline font-display">
                Personel Girişi →
            </a>
        </div>
    </div>

    <!-- Premium Alert Modal -->
    <div id="alertModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
        <div id="alertModalContainer" class="bg-white rounded-2xl sm:rounded-3xl border border-[#E5E7EB] shadow-2xl w-[calc(100%-2rem)] sm:w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[85vh] mx-auto">
            <!-- Modal Body -->
            <div class="p-5 sm:p-8 text-center space-y-4 sm:space-y-5 overflow-y-auto flex-1">
                <div id="alertSuccessIcon" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-[#ECFDF5] text-[#10B981] border border-[#A7F3D0]/50 flex items-center justify-center mx-auto transition-all duration-300 transform scale-75 opacity-0 hidden">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                    </svg>
                </div>
                <div id="alertErrorIcon" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-[#FEF2F2] text-[#EF4444] border border-[#FEE2E2]/50 flex items-center justify-center mx-auto transition-all duration-300 transform scale-75 opacity-0 hidden">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                    </svg>
                </div>
                <h3 id="alertModalTitle" class="text-lg font-bold font-display text-[#111827] tracking-tight">Mesaj</h3>
                <div id="alertModalContent" class="text-sm text-[#4B5563] leading-relaxed font-sans px-2 break-words"></div>
            </div>
            <!-- Modal Footer -->
            <div class="p-4 sm:p-6 bg-slate-50 border-t border-[#E5E7EB] text-center shrink-0">
                <button id="closeAlertBtn" class="w-full py-3 rounded-xl bg-[#1F2937] hover:bg-[#111827] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display outline-none shadow-md shadow-gray-500/10">
                    Kapat
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Modal Script -->
    <script>
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
                    const msgLower = String(mesaj).toLowerCase();
                    if (msgLower.includes('çıkış') || msgLower.includes('cikis')) {
                        title.innerText = 'Çıkış Başarılı';
                    } else if (msgLower.includes('giriş') || msgLower.includes('giris')) {
                        title.innerText = 'Giriş Başarılı';
                    } else {
                        title.innerText = 'İşlem Başarılı';
                    }
                    title.className = 'text-lg font-bold font-display text-[#111827]';
                    setTimeout(() => {
                        successIcon.classList.remove('scale-75', 'opacity-0');
                        successIcon.classList.add('scale-100', 'opacity-100');
                    }, 50);
                } else {
                    errorIcon.classList.remove('hidden');
                    title.innerText = 'Hata';
                    title.className = 'text-lg font-bold font-display text-red-600';
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

            if (modal && container) {
                function closeModal() {
                    container.classList.remove('scale-100', 'opacity-100');
                    container.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                }

                if (closeBtn) {
                    closeBtn.addEventListener('click', closeModal);
                }

                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            }

            @if(session('basarili'))
                {
                    const sessionMsg = "{{ session('basarili') }}";
                    const msgLower = sessionMsg.toLowerCase();
                    if (!msgLower.includes('çıkış') && !msgLower.includes('cikis')) {
                        mesajModalAc(sessionMsg, 'basarili');
                    }
                }
            @endif

            @if($errors->any())
                const errors = [];
                @foreach($errors->all() as $error)
                    errors.push("{{ $error }}");
                @endforeach
                mesajModalAc(errors, 'hata');
            @endif
        });

        // Hide preloader on page load
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.classList.add('opacity-0');
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 500);
            }
        });
    </script>
</body>
</html>
