<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Şifre Belirle - Randevu Ajandam</title>
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
<body class="text-[#4B5563] antialiased min-h-screen flex flex-col justify-center items-center p-6 relative overflow-hidden select-none">
    
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
                    Yeni Şifre Belirle
                </h1>
                <p class="text-[10px] text-[#6B7280] font-bold tracking-wide uppercase">Randevu Ajandam</p>
            </div>
        </div>

        <!-- Reset Card -->
        <div class="w-full bg-white border border-[#E5E7EB] rounded-3xl shadow-[0_12px_36px_rgba(31,41,55,0.03)] p-8">
            
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

            <form id="sifre-guncelleme-form" action="{{ route('password.update') }}" method="POST" class="space-y-5">
                @csrf
                @include('frontend.layouts.partials.recaptcha-form', ['formId' => 'sifre-guncelleme-form', 'recaptchaAction' => 'sifre_guncelleme'])
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="type" value="{{ $type }}">
                
                <!-- Email -->
                <div>
                    <label for="e_posta" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">E-Posta Adresiniz</label>
                    <input type="email" name="e_posta" id="e_posta" value="{{ old('e_posta', $email) }}" placeholder="isim@ornek.com" required
                           class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                </div>

                <!-- New Password -->
                <div>
                    <label for="sifre" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Yeni Şifre</label>
                    <input type="password" name="sifre" id="sifre" placeholder="••••••••" required
                           class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="sifre_confirmation" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Yeni Şifre Tekrarı</label>
                    <input type="password" name="sifre_confirmation" id="sifre_confirmation" placeholder="••••••••" required
                           class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-3.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                        Şifremi Sıfırla
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
