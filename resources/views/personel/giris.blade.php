<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Girişi - Randevu Ajandam</title>
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
<body class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 p-8 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <div>
            <!-- Logo placeholder or text logo -->
            <div class="w-16 h-16 rounded-2xl bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-2xl font-bold font-display mx-auto mb-4">
                RA
            </div>
            <h2 class="text-center text-3xl font-extrabold font-display text-[#111827]">
                Klinik Personel Girişi
            </h2>
            <p class="mt-2 text-center text-sm text-[#6B7280]">
                Hesabınıza erişmek için bilgilerinizi giriniz.
            </p>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('basari'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
                {{ session('basari') }}
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('personel.giris.post') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="e_posta" class="block text-xs font-semibold text-[#4B5563] mb-1.5">E-posta Adresi</label>
                    <input id="e_posta" name="e_posta" type="email" required value="{{ old('e_posta') }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none" placeholder="adiniz@klinik.com">
                </div>
                
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="sifre" class="block text-xs font-semibold text-[#4B5563]">Şifre</label>
                        <a href="javascript:void(0)" onclick="showForgotPasswordAlert()" class="text-[10px] font-bold text-[#C96A2B] hover:underline uppercase tracking-wide font-display">Şifremi Unuttum</a>
                    </div>
                    <input id="sifre" name="sifre" type="password" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none" placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-[#C96A2B] focus:ring-[#C96A2B]/20 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-xs text-[#4B5563] font-medium selection:bg-transparent">
                        Beni Hatırla
                    </label>
                </div>
            </div>

            <div>
                <button type="submit" class="w-full bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl transition-all duration-200 hover:scale-[1.01]">
                    Giriş Yap
                </button>
            </div>
        </form>

        <!-- Hızlı Test Girişleri -->
        <div class="mt-6 bg-[#FAF8F5] border border-[#E7B58A]/30 rounded-2xl p-4 space-y-3">
            <h4 class="text-xs font-bold text-[#C96A2B] uppercase tracking-wider font-display flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                </svg>
                Hızlı Test Girişleri
            </h4>
            <div class="space-y-2">
                <!-- Sekreter (Selin Yılmaz) -->
                <button type="button" onclick="hizliDoldur('selin@test.com', 'sifre123')" 
                        class="w-full flex flex-col text-left p-2.5 rounded-xl border border-dashed border-[#E5E7EB] hover:border-[#C96A2B] hover:bg-[#FFF7ED] transition-all cursor-pointer group select-none">
                    <span class="text-xs font-bold text-[#1F2937] font-display flex items-center gap-1.5">
                        Selin Yılmaz
                        <span class="text-[9px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-sans uppercase font-extrabold">Randevu & Hasta Yetkili</span>
                    </span>
                    <span class="text-[10px] text-[#6B7280] mt-1 font-mono font-medium">selin@test.com / sifre123</span>
                </button>
                
                <!-- Muhasebeci (Murat Kaya) -->
                <button type="button" onclick="hizliDoldur('murat@test.com', 'sifre123')" 
                        class="w-full flex flex-col text-left p-2.5 rounded-xl border border-dashed border-[#E5E7EB] hover:border-[#C96A2B] hover:bg-[#FFF7ED] transition-all cursor-pointer group select-none">
                    <span class="text-xs font-bold text-[#1F2937] font-display flex items-center gap-1.5">
                        Murat Kaya
                        <span class="text-[9px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-sans uppercase font-extrabold">Ödeme Yetkili</span>
                    </span>
                    <span class="text-[10px] text-[#6B7280] mt-1 font-mono font-medium">murat@test.com / sifre123</span>
                </button>
            </div>
        </div>

        <script>
            function hizliDoldur(eposta, sifre) {
                document.getElementById('e_posta').value = eposta;
                document.getElementById('sifre').value = sifre;
            }
        </script>

        <!-- Back to Homepage & Doctor Redirect -->
        <div class="flex items-center justify-between pt-5 mt-5 border-t border-slate-100 select-none">
            <a href="/" class="text-xs font-bold text-[#6B7280] hover:text-[#C96A2B] transition-colors font-display flex items-center gap-1.5">
                ← Ana Sayfaya Dön
            </a>
            <a href="{{ route('frontend.hekim.giris') }}" class="text-xs font-bold text-[#C96A2B] hover:underline font-display">
                Hekim Girişi →
            </a>
        </div>
    </div>

    <!-- Forgot Password Modal Script -->
    <script>
        function showForgotPasswordAlert() {
            // Create modal elements dynamically
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm';
            overlay.id = 'temp-modal-overlay';
            
            overlay.innerHTML = `
                <div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-2xl max-w-sm w-full overflow-hidden p-6 text-center space-y-4">
                    <div class="w-12 h-12 rounded-full bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center mx-auto text-xl">
                        ℹ️
                    </div>
                    <h3 class="text-sm font-bold font-display text-[#111827]">Şifremi Unuttum</h3>
                    <p class="text-xs text-[#6B7280] leading-relaxed">
                        Şifrenizi sıfırlamak için lütfen klinik yöneticiniz (hekiminiz) ile iletişime geçin.<br><br>
                        Yöneticiniz hekim panelinden şifrenizi sıfırlayarak size yeni bir geçici şifre tanımlayabilir.
                    </p>
                    <div class="pt-2">
                        <button type="button" onclick="document.getElementById('temp-modal-overlay').remove()" class="w-full py-2.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 cursor-pointer font-display">
                            Kapat
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
        }
    </script>
</body>
</html>
