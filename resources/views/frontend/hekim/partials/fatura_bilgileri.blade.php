@php
    /** @var \App\Models\Doktor|null $doktor */
    $tip = old('fatura_tipi', $doktor->fatura_tipi ?? 'bireysel');
    if (! in_array($tip, ['bireysel', 'kurumsal'], true)) {
        $tip = 'bireysel';
    }
    $unvan = old('fatura_unvan', $doktor->fatura_unvan ?: ($doktor->ad_soyad ?? ''));
    $tcVkn = old('fatura_tc_vkn', $doktor->fatura_tc_vkn ?: ($doktor->tc_kimlik_no ?? ''));
    $vd = old('fatura_vergi_dairesi', $doktor->fatura_vergi_dairesi ?? '');
    $adres = old('fatura_adres', $doktor->fatura_adres ?: ($doktor->adres ?? ''));
    $il = old('fatura_il', $doktor->fatura_il ?: ($doktor->il?->ad ?? ''));
    $ilce = old('fatura_ilce', $doktor->fatura_ilce ?: ($doktor->ilce?->ad ?? ''));
    $pk = old('fatura_posta_kodu', $doktor->fatura_posta_kodu ?? '');
    $email = old('fatura_email', $doktor->fatura_email ?: ($doktor->e_posta ?? ''));
    $tel = old('fatura_telefon', $doktor->fatura_telefon ?: ($doktor->telefon ?? ''));
@endphp

<div class="space-y-5" id="fatura-bilgileri-block">
    <div class="pb-2 border-b border-[#E5E7EB]">
        <h3 class="text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display">
            Fatura bilgileri
        </h3>
        <p class="mt-1.5 text-[11px] text-slate-500 leading-relaxed">
            Ödeme faturası bu bilgilere düzenlenir. Kayıt bilgilerinizden bağımsızdır; buradan girebilir veya düzenleyebilirsiniz.
        </p>
    </div>

    {{-- Tip seçimi --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <label class="fatura-tip-card cursor-pointer rounded-2xl border p-4 {{ $tip === 'bireysel' ? 'border-2 border-[#C96A2B] bg-[#FFF7ED]' : 'border-[#E5E7EB] bg-white' }}">
            <input type="radio" name="fatura_tipi" value="bireysel" class="sr-only" {{ $tip === 'bireysel' ? 'checked' : '' }} onchange="toggleFaturaTipi()">
            <span class="block text-xs font-bold text-[#111827]">Bireysel (T.C. kimlik)</span>
            <span class="mt-1 block text-[11px] text-slate-500">Şahıs adına e-arşiv / fatura</span>
        </label>
        <label class="fatura-tip-card cursor-pointer rounded-2xl border p-4 {{ $tip === 'kurumsal' ? 'border-2 border-[#C96A2B] bg-[#FFF7ED]' : 'border-[#E5E7EB] bg-white' }}">
            <input type="radio" name="fatura_tipi" value="kurumsal" class="sr-only" {{ $tip === 'kurumsal' ? 'checked' : '' }} onchange="toggleFaturaTipi()">
            <span class="block text-xs font-bold text-[#111827]">Kurumsal (Vergi no)</span>
            <span class="mt-1 block text-[11px] text-slate-500">Şirket / muayenehane tüzel kişilik</span>
        </label>
    </div>
    @error('fatura_tipi')<p class="text-[11px] text-red-600">{{ $message }}</p>@enderror

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <label for="fatura_unvan" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display" id="fatura_unvan_label">
                {{ $tip === 'kurumsal' ? 'Ticari unvan' : 'Ad soyad' }}
            </label>
            <input type="text" name="fatura_unvan" id="fatura_unvan" value="{{ $unvan }}" required maxlength="255"
                   placeholder="{{ $tip === 'kurumsal' ? 'Örn. ABC Sağlık Hizmetleri Ltd. Şti.' : 'Faturada görünecek ad soyad' }}"
                   class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
            @error('fatura_unvan')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="fatura_tc_vkn" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display" id="fatura_tc_vkn_label">
                {{ $tip === 'kurumsal' ? 'Vergi no (VKN)' : 'T.C. kimlik no' }}
            </label>
            <input type="text" name="fatura_tc_vkn" id="fatura_tc_vkn" value="{{ $tcVkn }}" required
                   inputmode="numeric" maxlength="11" pattern="[0-9]{10,11}"
                   placeholder="{{ $tip === 'kurumsal' ? '10 haneli vergi no' : '11 haneli TC' }}"
                   class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs font-mono focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
            @error('fatura_tc_vkn')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>

        <div id="fatura_vd_wrap" class="{{ $tip === 'kurumsal' ? '' : 'opacity-60' }}">
            <label for="fatura_vergi_dairesi" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">
                Vergi dairesi <span id="fatura_vd_req" class="{{ $tip === 'kurumsal' ? '' : 'hidden' }} text-red-500">*</span>
            </label>
            <input type="text" name="fatura_vergi_dairesi" id="fatura_vergi_dairesi" value="{{ $vd }}" maxlength="120"
                   placeholder="Örn. Çankaya Vergi Dairesi"
                   class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
            @error('fatura_vergi_dairesi')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label for="fatura_adres" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Fatura adresi</label>
            <textarea name="fatura_adres" id="fatura_adres" rows="2" required maxlength="1000"
                      placeholder="Mahalle, cadde, no, daire…"
                      class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">{{ $adres }}</textarea>
            @error('fatura_adres')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="fatura_il" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">İl</label>
            <input type="text" name="fatura_il" id="fatura_il" value="{{ $il }}" required maxlength="80"
                   class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
            @error('fatura_il')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="fatura_ilce" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">İlçe</label>
            <input type="text" name="fatura_ilce" id="fatura_ilce" value="{{ $ilce }}" required maxlength="80"
                   class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
            @error('fatura_ilce')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="fatura_posta_kodu" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Posta kodu</label>
            <input type="text" name="fatura_posta_kodu" id="fatura_posta_kodu" value="{{ $pk }}" maxlength="10" inputmode="numeric"
                   placeholder="Opsiyonel"
                   class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
            @error('fatura_posta_kodu')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="fatura_email" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Fatura e-posta</label>
            <input type="email" name="fatura_email" id="fatura_email" value="{{ $email }}" required maxlength="190"
                   class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
            @error('fatura_email')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="fatura_telefon" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Fatura telefon</label>
            <input type="text" name="fatura_telefon" id="fatura_telefon" value="{{ $tel }}" required maxlength="40"
                   placeholder="0 (5XX) XXX XX XX"
                   class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
            @error('fatura_telefon')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<script>
function toggleFaturaTipi() {
    const tip = document.querySelector('input[name="fatura_tipi"]:checked')?.value || 'bireysel';
    const isKurum = tip === 'kurumsal';
    const labelUnvan = document.getElementById('fatura_unvan_label');
    const labelTc = document.getElementById('fatura_tc_vkn_label');
    const inputUnvan = document.getElementById('fatura_unvan');
    const inputTc = document.getElementById('fatura_tc_vkn');
    const vdWrap = document.getElementById('fatura_vd_wrap');
    const vdReq = document.getElementById('fatura_vd_req');
    const vd = document.getElementById('fatura_vergi_dairesi');

    if (labelUnvan) labelUnvan.textContent = isKurum ? 'Ticari unvan' : 'Ad soyad';
    if (labelTc) labelTc.textContent = isKurum ? 'Vergi no (VKN)' : 'T.C. kimlik no';
    if (inputUnvan) inputUnvan.placeholder = isKurum ? 'Örn. ABC Sağlık Hizmetleri Ltd. Şti.' : 'Faturada görünecek ad soyad';
    if (inputTc) {
        inputTc.placeholder = isKurum ? '10 haneli vergi no' : '11 haneli TC';
        inputTc.maxLength = isKurum ? 10 : 11;
        inputTc.pattern = isKurum ? '[0-9]{10}' : '[0-9]{11}';
    }
    if (vdWrap) vdWrap.classList.toggle('opacity-60', !isKurum);
    if (vdReq) vdReq.classList.toggle('hidden', !isKurum);
    if (vd) vd.required = isKurum;

    document.querySelectorAll('.fatura-tip-card').forEach(function (el) {
        const radio = el.querySelector('input[type=radio]');
        const on = radio && radio.checked;
        el.classList.toggle('border-2', on);
        el.classList.toggle('border-[#C96A2B]', on);
        el.classList.toggle('bg-[#FFF7ED]', on);
        el.classList.toggle('border-[#E5E7EB]', !on);
        el.classList.toggle('bg-white', !on);
    });
}
document.addEventListener('DOMContentLoaded', toggleFaturaTipi);
</script>
