<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Değiştir - Randevu Ajandam</title>
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
            <div class="w-16 h-16 rounded-2xl bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-2xl font-bold font-display mx-auto mb-4">
                RA
            </div>
            <h2 class="text-center text-2xl font-extrabold font-display text-[#111827]">
                İlk Giriş: Şifre Belirleyin
            </h2>
            <p class="mt-2 text-center text-xs text-[#6B7280]">
                Hesap güvenliğiniz için ilk girişinizde geçici şifrenizi kalıcı bir şifre ile değiştirmeniz zorunludur.
            </p>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('personel.sifre-degistir.post') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="sifre" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Yeni Güçlü Şifre</label>
                    <input id="sifre" name="sifre" type="password" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none" placeholder="••••••••">
                    <p class="text-[10px] text-[#9CA3AF] mt-1 leading-relaxed">
                        Şifreniz en az 8 karakter uzunluğunda olmalı, en az bir büyük harf, bir küçük harf, bir sayı ve bir özel karakter içermelidir.
                    </p>
                </div>
                
                <div>
                    <label for="sifre_confirmation" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Yeni Şifre (Tekrar)</label>
                    <input id="sifre_confirmation" name="sifre_confirmation" type="password" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none" placeholder="••••••••">
                </div>
            </div>

            <div>
                <button type="submit" class="w-full bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl transition-all duration-200 hover:scale-[1.01]">
                    Şifreyi Güncelle & Devam Et
                </button>
            </div>
        </form>
    </div>
</body>
</html>
