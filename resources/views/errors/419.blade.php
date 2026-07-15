<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oturum Süresi Doldu (419) - Randevu Ajandam</title>
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
            <span class="text-9xl font-black font-display tracking-tighter brand-text-shimmer leading-none">419</span>
            <div class="absolute bottom-[-15px] p-2.5 rounded-2xl bg-white border border-[#E5E7EB] shadow-md icon-breathe">
                <svg class="w-8 h-8 text-[#C96A2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path>
                </svg>
            </div>
        </div>

        <!-- Message Details -->
        <div class="space-y-3 pt-4">
            <h1 class="text-2xl font-bold font-display text-[#111827] tracking-tight">Oturum Süreniz Doldu</h1>
            <p class="text-xs text-[#6B7280] leading-relaxed max-w-sm mx-auto">
                Uzun süre işlem yapmadığınız için güvenlik nedeniyle oturum süreniz doldu. Lütfen sayfayı yenileyip tekrar giriş yapmayı deneyin.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 items-center w-full pt-2">
            <button onclick="window.history.back(); setTimeout(() => { window.location.reload(); }, 200);" 
                    class="w-full sm:flex-1 py-3.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#6B7280] hover:text-[#111827] font-bold text-xs uppercase tracking-wider transition-all font-display text-center select-none shadow-sm cursor-pointer">
                Yenile & Geri Git
            </button>
            <a href="/" 
               class="w-full sm:flex-1 py-3.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all font-display text-center select-none shadow-sm hover:shadow-md">
                Ana Sayfa
            </a>
        </div>
    </div>

</body>
</html>
