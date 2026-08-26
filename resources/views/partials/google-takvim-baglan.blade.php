@php
    /**
     * Google Takvim baglantı kartı (hekim + klinik ortak).
     *
     * Beklenen degiskenler:
     *   $gcalOwner       : 'hekim' | 'klinik'
     *   $gcalConfig      : array|null (google_calendar_config; refresh_token doluysa bagli)
     *   $gcalBaglandiAt  : datetime|null
     *   $gcalBaglanUrl   : GET route (OAuth start)
     *   $gcalAyirUrl     : POST route (baglantiyi sil)
     *   $gcalEnabled     : (bool) config('google_calendar.enabled')
     */
    $bagli = ! empty($gcalConfig['refresh_token'] ?? null);
    $email = $gcalConfig['email'] ?? null;
    $sonBaglanti = $gcalBaglandiAt
        ? (\Carbon\Carbon::parse($gcalBaglandiAt)->format('d.m.Y H:i'))
        : null;
@endphp

<div class="bg-white rounded-2xl border border-[#E5E7EB] p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h3 class="text-base font-bold font-display text-[#111827] flex items-center gap-2">
                <span class="inline-flex w-6 h-6 rounded-full bg-sky-100 items-center justify-center text-sky-600 text-xs">📅</span>
                Google Takvim
            </h3>
            <p class="text-xs text-[#6B7280] mt-1">
                Onaylanmış randevular Google Takvim'inize otomatik yazılır. Google'daki manuel etkinlikler slotlarınızı kapatır.
            </p>
        </div>

        @if($bagli)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase tracking-wider border border-emerald-200">
                Bağlı
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-50 text-slate-600 text-[10px] font-bold uppercase tracking-wider border border-slate-200">
                Bağlı değil
            </span>
        @endif
    </div>

    @if($bagli)
        <div class="mt-4 space-y-2.5 text-xs">
            <div class="flex justify-between py-1.5 border-b border-[#F5F5F4]">
                <span class="text-[#6B7280]">Google hesabı:</span>
                <span class="font-semibold text-[#111827]">{{ $email ?: '—' }}</span>
            </div>
            @if($sonBaglanti)
                <div class="flex justify-between py-1.5">
                    <span class="text-[#6B7280]">Bağlantı tarihi:</span>
                    <span class="font-semibold text-[#111827]">{{ $sonBaglanti }}</span>
                </div>
            @endif
        </div>

        <div class="mt-4 flex items-center gap-2">
            <form action="{{ $gcalAyirUrl }}" method="POST"
                  onsubmit="return confirm('Google Takvim bağlantısını kaldırmak istediğinize emin misiniz? Bundan sonra randevular Google\'a yazılmaz.');">
                @csrf
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 text-xs font-bold transition">
                    Bağlantıyı Kaldır
                </button>
            </form>
            <span class="text-[10px] text-[#6B7280]">Google'daki eski etkinlikler silinmez.</span>
        </div>
    @else
        <div class="mt-4">
            @if($gcalEnabled)
                <a href="{{ $gcalBaglanUrl }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold transition shadow-sm">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M21.35 11.1h-9.17v2.98h5.28c-.24 1.37-1.63 4.02-5.28 4.02-3.18 0-5.77-2.63-5.77-5.87s2.6-5.87 5.77-5.87c1.81 0 3.02.77 3.71 1.44l2.53-2.44C16.99 3.9 14.85 3 12.18 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c5.2 0 8.64-3.65 8.64-8.79 0-.59-.06-1.04-.14-1.5z"/>
                    </svg>
                    Google Hesabınla Bağla
                </a>
                <p class="text-[10px] text-[#6B7280] mt-2">Google'ın onay ekranına yönlendirileceksiniz. RandevuAjandam sadece takviminizi görüntüleyip düzenler.</p>
            @else
                <button type="button" disabled
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-200 text-slate-500 text-xs font-bold cursor-not-allowed">
                    <span>⏳</span> Google Takvim Yakında
                </button>
                <p class="text-[10px] text-[#6B7280] mt-2">Google Cloud onay süreci devam ediyor. Bu özellik kısa süre içinde aktif olacaktır.</p>
            @endif
        </div>
    @endif
</div>
