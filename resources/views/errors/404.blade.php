<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sayfa Bulunamadı (404) - Randevu Ajandam</title>
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

        /* Ambient Glow animation */
        @keyframes pulse-glow {
            0%, 100% { transform: scale(1); opacity: 0.35; }
            50% { transform: scale(1.1); opacity: 0.5; }
        }
        .bg-glow-1 {
            animation: pulse-glow 6s ease-in-out infinite;
        }
        .bg-glow-2 {
            animation: pulse-glow 8s ease-in-out infinite;
            animation-delay: 2s;
        }

        /* Error Code Shimmer */
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

        /* Icon breathing */
        @keyframes breathe {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .icon-breathe {
            animation: breathe 4s ease-in-out infinite;
        }
    </style>
</head>
<body class="text-[#4B5563] antialiased min-h-screen flex flex-col justify-center items-center p-6 relative overflow-hidden select-none">
    
    <!-- Background Ambient Lights -->
    <div class="absolute top-[-20%] right-[-10%] w-[600px] h-[600px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none bg-glow-1"></div>
    <div class="absolute bottom-[-20%] left-[-10%] w-[600px] h-[600px] rounded-full bg-[#C96A2B]/4 blur-[120px] pointer-events-none bg-glow-2"></div>

    <div class="w-full max-w-md text-center space-y-8 z-10">
        <!-- Error Code/Illustration -->
        <div class="relative flex flex-col items-center justify-center">
            <span class="text-9xl font-black font-display tracking-tighter brand-text-shimmer leading-none">404</span>
            <div class="absolute bottom-[-15px] p-2.5 rounded-2xl bg-white border border-[#E5E7EB] shadow-md icon-breathe">
                <svg class="w-8 h-8 text-[#C96A2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z"></path>
                </svg>
            </div>
        </div>

        <!-- Message Details -->
        <div class="space-y-3 pt-4">
            <h1 class="text-2xl font-bold font-display text-[#111827] tracking-tight">Aradığınız Sayfa Bulunamadı</h1>
            <p class="text-xs text-[#6B7280] leading-relaxed max-w-sm mx-auto">
                İstediğiniz sayfa taşınmış, silinmiş veya adresi değişmiş olabilir. Lütfen adresi kontrol ederek tekrar deneyin.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 items-center w-full pt-2">
            <button onclick="window.history.back()" 
                    class="w-full sm:flex-1 py-3.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#6B7280] hover:text-[#111827] font-bold text-xs uppercase tracking-wider transition-all font-display text-center select-none shadow-sm cursor-pointer">
                Geri Dön
            </button>
            <a href="/" 
               class="w-full sm:flex-1 py-3.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all font-display text-center select-none shadow-sm hover:shadow-md">
                Ana Sayfa
            </a>
        </div>
    </div>

</body>
</html>
