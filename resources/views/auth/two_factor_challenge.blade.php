<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İki Adımlı Doğrulama - Randevu Ajandam</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; background: #F5F5F4; }
        .font-display { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white border border-[#E5E7EB] rounded-3xl shadow-lg p-8">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#C96A2B] font-display">Güvenlik</p>
        <h1 class="mt-2 text-xl font-bold text-[#111827] font-display">İki adımlı doğrulama</h1>
        <p class="mt-2 text-sm text-slate-500 leading-relaxed">
            Authenticator uygulamanızdaki 6 haneli kodu veya bir yedek kodu girin.
        </p>

        @if($errors->any())
            <div class="mt-4 p-3 rounded-xl bg-red-50 border border-red-100 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('two-factor.challenge.post') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="code" class="block text-[10px] font-bold uppercase tracking-wider text-slate-700 font-display mb-1.5">Doğrulama kodu</label>
                <input type="text" name="code" id="code" inputmode="numeric" autocomplete="one-time-code" autofocus required
                       placeholder="000000"
                       class="w-full px-4 py-3 rounded-xl border border-[#E5E7EB] text-center text-lg tracking-[0.3em] font-semibold focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
            </div>
            <button type="submit"
                    class="w-full py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider font-display">
                Doğrula ve Giriş Yap
            </button>
        </form>

        <form method="POST" action="{{ route('two-factor.challenge.cancel') }}" class="mt-3">
            @csrf
            <button type="submit" class="w-full py-2.5 text-xs text-slate-500 hover:text-slate-800 font-display">
                Girişe dön
            </button>
        </form>
    </div>
</body>
</html>
