@extends('frontend.layouts.app')

@section('baslik', 'Değerlendirmenizi paylaşın · Randevu Ajandam')
@section('robots', 'noindex, nofollow')

@section('icerik')
<div class="max-w-2xl mx-auto px-4 py-10 sm:py-16">

    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-[0_1px_2px_rgba(16,24,40,.04),0_12px_36px_rgba(16,24,40,.05)] overflow-hidden">

        {{-- Başlık --}}
        <div class="px-6 sm:px-8 pt-7 pb-6 border-b border-[#F1F2F4] bg-gradient-to-b from-[#FCFCFD] to-white">
            <p class="text-[11px] font-bold uppercase tracking-[.12em] text-[#C96A2B]">Değerlendirme</p>
            <h1 class="mt-2 text-xl sm:text-2xl font-bold font-display text-[#111827] leading-snug">
                @if($doktor)
                    {{ trim(($doktor->unvan ? $doktor->unvan.' ' : '').$doktor->ad_soyad) }} ile
                    deneyiminiz nasıldı?
                @else
                    Deneyiminiz nasıldı?
                @endif
            </h1>
            @if($randevu)
                <p class="mt-2 text-sm text-slate-500">
                    {{ \Carbon\Carbon::parse($randevu->tarih)->translatedFormat('d F Y') }}
                    tarihli randevunuz için.
                </p>
            @endif
        </div>

        <form method="POST" action="{{ route('yorum.davet.kaydet', ['token' => $token]) }}"
              class="px-6 sm:px-8 py-7 space-y-7">
            @csrf

            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $hata)
                            <li>{{ $hata }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Puan --}}
            <fieldset>
                <legend class="block text-xs font-bold uppercase tracking-[.07em] text-slate-500 mb-3">
                    Puanınız
                </legend>
                {{-- Radio + label: JS olmadan da calisir; yildizlar CSS ile boyanir --}}
                <div class="yd-yildizlar inline-flex flex-row-reverse gap-1.5" role="radiogroup">
                    @for($i = 5; $i >= 1; $i--)
                        <input type="radio" id="puan{{ $i }}" name="puan" value="{{ $i }}"
                               class="peer/p{{ $i }} sr-only" @checked(old('puan') == $i) required>
                        <label for="puan{{ $i }}" title="{{ $i }} yıldız"
                               class="yd-yildiz cursor-pointer text-slate-300 transition-colors">
                            <span class="sr-only">{{ $i }} yıldız</span>
                            <svg class="w-9 h-9" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 2.5l2.9 5.88 6.49.95-4.7 4.58 1.11 6.46L12 17.33l-5.8 3.04 1.1-6.46-4.69-4.58 6.49-.95L12 2.5z"/>
                            </svg>
                        </label>
                    @endfor
                </div>
            </fieldset>

            {{-- Yorum --}}
            <div>
                <label for="yorum" class="block text-xs font-bold uppercase tracking-[.07em] text-slate-500 mb-2">
                    Değerlendirmeniz
                </label>
                <textarea id="yorum" name="yorum" rows="5" required minlength="10" maxlength="1000"
                          placeholder="Deneyiminizi birkaç cümleyle anlatın. Diğer danışanlara yardımcı olacaktır."
                          class="w-full rounded-xl border border-[#E5E7EB] px-4 py-3 text-sm text-[#111827] placeholder:text-slate-400 focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/15 outline-none transition">{{ old('yorum') }}</textarea>
                <p class="mt-2 text-xs text-slate-500">En az 10, en fazla 1000 karakter.</p>
            </div>

            <div class="rounded-xl bg-[#FAFBFC] border border-[#EEF0F2] px-4 py-3 text-xs text-slate-500 leading-relaxed">
                Değerlendirmeniz platform yönetimi tarafından incelendikten sonra hekimin
                herkese açık profilinde yayınlanır. Bu bağlantı size özeldir ve bir kez kullanılabilir.
            </div>

            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#C96A2B] px-5 py-3.5 text-sm font-bold text-white hover:bg-[#b25c23] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Değerlendirmeyi gönder
            </button>
        </form>
    </div>

    <p class="mt-6 text-center text-xs text-slate-400">
        Bu sayfa <a href="{{ url('/') }}" class="font-semibold text-slate-500 hover:text-[#C96A2B]">Randevu Ajandam</a> tarafından sunulmaktadır.
    </p>
</div>

<style>
    /* Ters sirali flex + peer: isaretli yildiz ve SAGINDAKILER (gorsel olarak
       solundakiler) boyanir. Ayrica uzerine gelince onizleme yapilir. */
    .yd-yildizlar input:checked + label,
    .yd-yildizlar input:checked + label ~ label { color: #F59E0B; }
    .yd-yildizlar:hover label { color: #CBD5E1; }
    .yd-yildizlar label:hover,
    .yd-yildizlar label:hover ~ label { color: #FBBF24; }
    .yd-yildizlar input:focus-visible + label { outline: 2px solid #C96A2B; outline-offset: 3px; border-radius: 6px; }
</style>
@endsection
