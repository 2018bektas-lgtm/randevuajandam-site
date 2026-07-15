@php
    $hastaUser = Auth::guard('hasta')->user();
@endphp
<div class="bg-white border border-dashed border-[#E7B58A]/60 rounded-3xl p-6 shadow-sm relative overflow-hidden">
    <div class="flex items-start gap-3 mb-4">
        <div class="w-10 h-10 rounded-2xl bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/40 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Bekleme Listesi</h3>
            <p class="text-[11px] text-[#6B7280] mt-1 leading-relaxed">
                Uygun saat bulamadıysanız kaydolun. Randevu açıldığında e-posta ile bilgilendirilirsiniz.
            </p>
        </div>
    </div>

    @if(session('basarili') && str_contains(session('basarili'), 'Bekleme'))
        <div class="p-3 mb-4 bg-emerald-50 border border-emerald-100 rounded-xl text-[11px] text-emerald-700 font-medium">
            {{ session('basarili') }}
        </div>
    @endif
    @if(session('hata') && old('bekleme_form'))
        <div class="p-3 mb-4 bg-red-50 border border-red-100 rounded-xl text-[11px] text-red-700 font-medium">
            {{ session('hata') }}
        </div>
    @endif

    <form action="{{ route('frontend.bekleme-listesi.katil') }}" method="POST" class="space-y-3">
        @csrf
        <input type="hidden" name="doktor_id" value="{{ $doktor->id }}">
        <input type="hidden" name="bekleme_form" value="1">
        <div class="hidden" aria-hidden="true">
            <input type="text" name="{{ config('randevu.honeypot_field', 'website_url') }}" value="" tabindex="-1" autocomplete="off">
        </div>

        @if($doktor->hizmetler->isNotEmpty())
            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Hizmet (opsiyonel)</label>
                <select name="hizmet_id" class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
                    <option value="">Fark etmez</option>
                    @foreach($doktor->hizmetler as $hizmet)
                        @if($hizmet->aktif_mi)
                            <option value="{{ $hizmet->id }}" @selected(old('hizmet_id') == $hizmet->id)>{{ $hizmet->ad }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        @endif

        @unless($hastaUser)
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Ad</label>
                    <input type="text" name="ad" required value="{{ old('ad') }}"
                           class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
                </div>
                <div class="space-y-1">
                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Soyad</label>
                    <input type="text" name="soyad" required value="{{ old('soyad') }}"
                           class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
                </div>
            </div>
            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Telefon</label>
                <input type="tel" name="telefon" required value="{{ old('telefon') }}" placeholder="05xx xxx xx xx"
                       class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
            </div>
            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">E-posta (önerilir)</label>
                <input type="email" name="e_posta" value="{{ old('e_posta') }}"
                       class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
            </div>
        @else
            <div class="p-3 bg-slate-50 border border-slate-100 rounded-2xl text-xs">
                <p class="text-[9px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Kayıt sahibi</p>
                <p class="font-bold text-[#111827]">{{ $hastaUser->ad_soyad }}</p>
                <p class="text-[10px] text-[#6B7280]">{{ $hastaUser->telefon }} · {{ $hastaUser->e_posta }}</p>
            </div>
        @endunless

        <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Tercih günü</label>
                <input type="date" name="tercih_tarih" value="{{ old('tercih_tarih') }}" min="{{ date('Y-m-d') }}"
                       class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
            </div>
            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Tercih saati</label>
                <input type="time" name="tercih_saat" value="{{ old('tercih_saat') }}"
                       class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none">
            </div>
        </div>
        <p class="text-[10px] text-slate-400 -mt-1">Boş bırakırsanız esnek tercih sayılır.</p>

        <div class="space-y-1">
            <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Not (opsiyonel)</label>
            <textarea name="not" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-[#E5E7EB] text-xs focus:border-[#C96A2B] focus:outline-none resize-none">{{ old('not') }}</textarea>
        </div>

        <label class="flex items-start gap-2 text-[11px] text-slate-600 cursor-pointer">
            <input type="checkbox" name="kvkk_onay" value="1" required class="mt-0.5">
            <span>Kişisel verilerimin bekleme listesi amacıyla işlenmesini kabul ediyorum.</span>
        </label>

        <button type="submit"
                class="w-full py-3 rounded-xl border-2 border-[#C96A2B] text-[#C96A2B] hover:bg-[#FFF7ED] font-bold text-xs uppercase tracking-wider transition-all font-display">
            Bekleme Listesine Kaydol
        </button>
    </form>
</div>
