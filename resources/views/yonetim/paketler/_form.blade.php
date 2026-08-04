@php
    /** @var \App\Models\Paket|null $paket */
    $p = $paket ?? null;
    $secili = collect($seciliOzellikler ?? [])->flip();
    $val = function (string $key, $default = null) use ($p) {
        return old($key, $p?->{$key} ?? $default);
    };
@endphp

<div class="space-y-6">
    {{-- Temel --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Paket Adı</label>
            <input type="text" name="ad" value="{{ $val('ad') }}" required
                   class="w-full px-4 py-3 rounded-xl border border-[#E5E7EB] text-sm focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]"
                   placeholder="Örn: Başlangıç, Klinik Plus">
        </div>
        <div>
            <label class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Tür</label>
            <select name="tur" id="tur" required
                    class="w-full px-4 py-3 rounded-xl border border-[#E5E7EB] text-sm focus:border-[#C96A2B]">
                <option value="bireysel" @selected($val('tur', 'bireysel') === 'bireysel')>Bireysel hekim</option>
                <option value="klinik" @selected($val('tur') === 'klinik')>Klinik</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Sıra</label>
            <input type="number" name="sira" value="{{ $val('sira', 0) }}"
                   class="w-full px-4 py-3 rounded-xl border border-[#E5E7EB] text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Açıklama</label>
            <textarea name="aciklama" rows="2" class="w-full px-4 py-3 rounded-xl border border-[#E5E7EB] text-sm">{{ $val('aciklama') }}</textarea>
        </div>
    </div>

    {{-- Fiyat --}}
    <div class="p-5 border border-[#E5E7EB] rounded-2xl bg-slate-50/40 space-y-4">
        <h3 class="text-xs font-bold text-[#C96A2B] uppercase tracking-wider font-display">Fiyatlar (KDV dahil)</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Aylık</label>
                <input type="number" step="0.01" min="0" name="aylik_fiyat" value="{{ $val('aylik_fiyat') }}" required class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Aylık indirimli</label>
                <input type="number" step="0.01" min="0" name="aylik_indirimli_fiyat" value="{{ $val('aylik_indirimli_fiyat') }}" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Yıllık</label>
                <input type="number" step="0.01" min="0" name="yillik_fiyat" value="{{ $val('yillik_fiyat') }}" required class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Yıllık indirimli</label>
                <input type="number" step="0.01" min="0" name="yillik_indirimli_fiyat" value="{{ $val('yillik_indirimli_fiyat') }}" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
        </div>
        <p class="text-[10px] text-slate-400">Yıllık öneri: aylık × 12 × 0,80 (%20 indirim).</p>
    </div>

    {{-- Limitler --}}
    <div class="p-5 border border-[#E5E7EB] rounded-2xl space-y-4">
        <h3 class="text-xs font-bold text-[#C96A2B] uppercase tracking-wider font-display">Limitler &amp; kontör</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Max randevu</label>
                <input type="number" min="0" name="max_randevu_sayisi" value="{{ $val('max_randevu_sayisi') }}"
                       placeholder="Boş = limitsiz" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
                <p class="text-[9px] text-slate-400 mt-0.5">Vitrin: 10</p>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Max hasta</label>
                <input type="number" min="0" name="max_hasta_sayisi" value="{{ $val('max_hasta_sayisi') }}"
                       placeholder="Boş = limitsiz" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">SMS aylık kontör</label>
                <input type="number" min="0" name="sms_aylik_kontor" value="{{ $val('sms_aylik_kontor') }}"
                       placeholder="250 / 750 / …" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Max hizmet</label>
                <input type="number" min="0" name="max_hizmet_sayisi" value="{{ $val('max_hizmet_sayisi') }}"
                       placeholder="Vitrin: 3" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Max biyografi karakter</label>
                <input type="number" min="0" name="max_biyografi_karakter" value="{{ $val('max_biyografi_karakter') }}"
                       placeholder="Vitrin: 300" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Max galeri foto</label>
                <input type="number" min="0" name="max_profil_foto" value="{{ $val('max_profil_foto') }}"
                       placeholder="1 / 3 / 10 / 30" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Max personel (dahil)</label>
                <input type="number" min="0" name="max_personel_sayisi" value="{{ $val('max_personel_sayisi') }}"
                       placeholder="0 / 1 / 2…" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Deneme günü</label>
                <input type="number" min="0" max="90" name="deneme_gun" value="{{ $val('deneme_gun') }}"
                       placeholder="14" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Listeleme öncelik</label>
                <select name="listeleme_oncelik" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
                    @foreach([0 => '0 — Alt sıra (Vitrin)', 1 => '1 — Standart', 2 => '2 — Öncelikli', 3 => '3 — En üst'] as $k => $lab)
                        <option value="{{ $k }}" @selected((int)$val('listeleme_oncelik', 1) === $k)>{{ $lab }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Ek personel ₺/ay</label>
                <input type="number" step="0.01" min="0" name="ek_personel_aylik_fiyat" value="{{ $val('ek_personel_aylik_fiyat', 300) }}" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Ek personel ₺/yıl</label>
                <input type="number" step="0.01" min="0" name="ek_personel_yillik_fiyat" value="{{ $val('ek_personel_yillik_fiyat', 2880) }}" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
        </div>
    </div>

    {{-- Klinik --}}
    <div id="clinicFields" class="hidden p-5 border border-orange-100 rounded-2xl bg-[#FFF7ED]/40 space-y-4">
        <h3 class="text-xs font-bold text-[#C96A2B] uppercase tracking-wider font-display">Klinik kapasite &amp; bayraklar</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Max hekim</label>
                <input type="number" min="1" name="max_doktor_sayisi" value="{{ $val('max_doktor_sayisi', 3) }}" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Max ek hekim koltuğu</label>
                <input type="number" min="0" name="max_ek_doktor" value="{{ $val('max_ek_doktor') }}" placeholder="3 / 4 / 10" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Ek hekim ₺/ay</label>
                <input type="number" step="0.01" min="0" name="ek_doktor_aylik_fiyat" value="{{ $val('ek_doktor_aylik_fiyat', 650) }}" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Ek hekim ₺/yıl</label>
                <input type="number" step="0.01" min="0" name="ek_doktor_yillik_fiyat" value="{{ $val('ek_doktor_yillik_fiyat', 6240) }}" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach([
                'merkezi_finans_mi' => ['Merkezi finans + muhasebeci', 'Klinik finans / hakediş'],
                'toplu_randevu_mi' => ['Toplu randevu', 'Çoklu hekim toplu işlem'],
                'raporlama_mi' => ['Gelişmiş raporlama', 'PDF performans raporları'],
                'hasta_havuzu_mi' => ['Ortak hasta havuzu', 'Klinik geneli hasta havuzu'],
            ] as $name => [$title, $sub])
                <label class="flex items-center justify-between gap-3 p-3 rounded-xl border border-[#E5E7EB] bg-white cursor-pointer">
                    <span>
                        <span class="block text-xs font-bold text-slate-800">{{ $title }}</span>
                        <span class="block text-[10px] text-slate-500">{{ $sub }}</span>
                    </span>
                    <input type="checkbox" name="{{ $name }}" value="1" @checked($val($name)) class="rounded border-slate-300 text-[#C96A2B] focus:ring-[#C96A2B]">
                </label>
            @endforeach
        </div>
    </div>

    {{-- Domain & vitrin etiket --}}
    <div class="p-5 border border-[#E5E7EB] rounded-2xl space-y-4">
        <h3 class="text-xs font-bold text-[#C96A2B] uppercase tracking-wider font-display">Domain &amp; vitrin etiketi</h3>
        <div class="flex flex-wrap gap-4 items-center">
            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700">
                <input type="checkbox" name="domain_dahil_mi" value="1" @checked($val('domain_dahil_mi')) class="rounded text-[#C96A2B]">
                Domain pakete dahil
            </label>
            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700">
                <input type="checkbox" name="one_cikan_mi" value="1" @checked($val('one_cikan_mi')) class="rounded text-[#C96A2B]">
                Öne çıkan kart
            </label>
            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700">
                <input type="checkbox" name="aktif_mi" value="1" @checked(old('aktif_mi', $p?->aktif_mi ?? true)) class="rounded text-[#C96A2B]">
                Aktif (satışta)
            </label>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Domain yıl</label>
                <input type="number" min="1" max="5" name="domain_dahil_yil" value="{{ $val('domain_dahil_yil', 1) }}" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">TLD listesi</label>
                <input type="text" name="domain_dahil_tlds" value="{{ old('domain_dahil_tlds', is_array($p?->domain_dahil_tlds) ? implode(',', $p->domain_dahil_tlds) : ($p?->domain_dahil_tlds ?? 'com,net')) }}" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs" placeholder="com,net">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Etiket metni</label>
                <input type="text" name="etiket" maxlength="40" value="{{ $val('etiket') }}" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs" placeholder="Popüler, Ücretsiz…">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Etiket stil</label>
                <select name="etiket_stil" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
                    <option value="">Otomatik</option>
                    @foreach(['popular' => 'Popüler', 'web' => 'Web', 'free' => 'Ücretsiz', 'trial' => 'Deneme', 'custom' => 'Özel'] as $k => $lab)
                        <option value="{{ $k }}" @selected($val('etiket_stil') === $k)>{{ $lab }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <input type="hidden" name="iyzico_plan_aylik" value="{{ $val('iyzico_plan_aylik') }}">
        <input type="hidden" name="iyzico_plan_yillik" value="{{ $val('iyzico_plan_yillik') }}">
    </div>

    {{-- Sistem özellikleri — checkbox kataloğu --}}
    <div class="p-5 border border-[#E5E7EB] rounded-2xl space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-xs font-bold text-[#C96A2B] uppercase tracking-wider font-display">Sistem özellikleri (yetki)</h3>
                <p class="text-[11px] text-slate-500 mt-1 max-w-xl">
                    Manuel özellik yazılmaz. Aşağıdakilerden işaretledikleriniz paneli açar ve vitrin listesine otomatik eklenir.
                    Excel matrisi ile uyumludur.
                </p>
            </div>
            <div class="flex gap-2">
                <button type="button" id="btnOzellikHepsiniSec" class="px-3 py-1.5 rounded-lg border border-[#E5E7EB] text-[11px] font-bold text-slate-600 hover:bg-slate-50">Tümünü seç</button>
                <button type="button" id="btnOzellikTemizle" class="px-3 py-1.5 rounded-lg border border-[#E5E7EB] text-[11px] font-bold text-slate-600 hover:bg-slate-50">Temizle</button>
            </div>
        </div>

        @foreach($ozellikGruplari as $grupAd => $ozellikler)
            <div class="space-y-2">
                <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-1">{{ $grupAd }}</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($ozellikler as $oz)
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-[#E5E7EB] hover:border-[#E7B58A]/60 hover:bg-[#FFF7ED]/30 cursor-pointer transition-colors">
                            <input type="checkbox" name="sistem_ozellikleri[]" value="{{ $oz->kod }}"
                                   class="mt-0.5 rounded border-slate-300 text-[#C96A2B] focus:ring-[#C96A2B] ozellik-cb"
                                   @checked($secili->has($oz->kod))>
                            <span class="min-w-0">
                                <span class="block text-xs font-bold text-slate-800">{{ $oz->ad }}</span>
                                @if($oz->aciklama)
                                    <span class="block text-[10px] text-slate-500 leading-snug mt-0.5">{{ $oz->aciklama }}</span>
                                @endif
                                <span class="inline-block mt-1 text-[9px] font-mono text-slate-400">{{ $oz->kod }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tur = document.getElementById('tur');
    const clinic = document.getElementById('clinicFields');
    function toggleClinic() {
        if (!tur || !clinic) return;
        clinic.classList.toggle('hidden', tur.value !== 'klinik');
    }
    if (tur) {
        tur.addEventListener('change', toggleClinic);
        toggleClinic();
    }
    document.getElementById('btnOzellikHepsiniSec')?.addEventListener('click', () => {
        document.querySelectorAll('.ozellik-cb').forEach(cb => { cb.checked = true; });
    });
    document.getElementById('btnOzellikTemizle')?.addEventListener('click', () => {
        document.querySelectorAll('.ozellik-cb').forEach(cb => { cb.checked = false; });
    });
});
</script>
