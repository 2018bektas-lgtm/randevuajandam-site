<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Görüşme — Randevu Ajandam</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center">
        <div class="text-3xl mb-3">📹</div>
        <h1 class="text-lg font-extrabold text-slate-900">Online görüşme</h1>
        <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ $mesaj ?? 'Görüşme şu an kullanılamıyor.' }}</p>
        @if(!empty($randevu))
            <p class="mt-4 text-xs text-slate-400">
                {{ $randevu->tarih?->format('d.m.Y') }} · {{ substr((string)$randevu->saat, 0, 5) }}
            </p>
        @endif
    </div>
</body>
</html>
