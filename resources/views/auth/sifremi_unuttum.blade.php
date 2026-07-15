<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifremi Unuttum - Randevu Ajandam</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/logo.png') }}" type="image/png">
    
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
    </style>
</head>
<body class="text-[#4B5563] antialiased min-h-screen flex flex-col justify-center items-center p-4 sm:p-6 relative overflow-x-hidden overflow-y-auto select-none">
    
    <!-- Background Ambient Lights -->
    <div class="absolute top-[-30%] right-[-20%] w-[800px] h-[800px] rounded-full bg-[#E7B58A]/10 blur-[130px] pointer-events-none"></div>
    <div class="absolute bottom-[-30%] left-[-20%] w-[800px] h-[800px] rounded-full bg-[#C96A2B]/4 blur-[130px] pointer-events-none"></div>

    <div class="w-full max-w-[420px] flex flex-col items-center gap-8 z-10">
        <!-- Logo Section -->
        <div class="flex flex-col items-center gap-4 text-center">
            <div class="w-24 h-24 flex items-center justify-center relative">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Randevu Ajandam" 
                     style="mix-blend-mode: multiply;" 
                     class="w-full h-full object-contain">
            </div>
            
            <div class="flex flex-col gap-1.5 mt-2">
                <h1 class="text-2xl font-black font-display tracking-tight text-[#111827] leading-none">
                    @if($type === 'hekim')
                        Hekim Şifre Sıfırlama
                    @elseif($type === 'hasta')
                        Hasta Şifre Sıfırlama
                    @else
                        Yönetici Şifre Sıfırlama
                    @endif
                </h1>
                <p class="text-[10px] text-[#6B7280] font-bold tracking-wide uppercase">Randevu Ajandam</p>
            </div>
        </div>

        <!-- Reset Card -->
        <div class="w-full bg-white border border-[#E5E7EB] rounded-3xl shadow-[0_12px_36px_rgba(31,41,55,0.03)] p-8">
            
            <!-- Success Message -->
            @if(session('basarili'))
                <div class="mb-5 p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-xs text-emerald-700 font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                    </svg>
                    <span>{{ session('basarili') }}</span>
                </div>
            @endif

            <!-- Error Messages -->
            @if($errors->any())
                <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-100 text-red-600 text-xs space-y-1">
                    @foreach($errors->all() as $error)
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                
                <div>
                    <label for="e_posta" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">E-Posta Adresiniz</label>
                    <input type="email" name="e_posta" id="e_posta" value="{{ old('e_posta') }}" placeholder="isim@ornek.com" required
                           class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                    <p class="text-[10px] text-gray-400 mt-1.5">Şifre sıfırlama bağlantısını bu e-posta adresine göndereceğiz.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-3.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                        Sıfırlama Bağlantısı Gönder
                    </button>
                </div>
            </form>
        </div>

        <!-- Back to Login Link -->
        @php
            $backRoute = 'frontend.hekim.giris';
            if($type === 'hasta') $backRoute = 'frontend.hasta.giris';
            if($type === 'yonetici') $backRoute = 'yonetim.giris';
        @endphp
        <a href="{{ route($backRoute) }}" class="text-xs font-bold text-[#6B7280] hover:text-[#C96A2B] transition-colors font-display flex items-center gap-1.5">
            ← Giriş Sayfasına Dön
        </a>
    </div>
</body>
</html>
