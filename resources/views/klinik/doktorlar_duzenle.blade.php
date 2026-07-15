@extends('klinik.layout')

@section('baslik', $doc->ad_soyad . ' - Hekim Klinik Ayarları')
@section('sayfa_baslik', 'Hekim Klinik Ayarları Düzenleme')

@section('icerik')
    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <a href="{{ route('hekim.klinik.doktorlar') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#6B7280] hover:text-[#111827] transition-colors">
                ← Hekim Listesine Dön
            </a>
        </div>

        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm space-y-6">
            <div class="flex items-center gap-4 pb-4 border-b border-[#F5F5F4]">
                <div class="w-12 h-12 rounded-2xl bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-lg font-bold font-display">
                    {{ mb_strtoupper(mb_substr($doc->ad_soyad, 0, 2)) }}
                </div>
                <div>
                    <h3 class="text-base font-bold font-display text-[#111827]">{{ $doc->unvan ? $doc->unvan . ' ' : '' }}{{ $doc->ad_soyad }}</h3>
                    <p class="text-xs text-[#6B7280] mt-0.5">{{ $doc->uzmanlik_alani }}</p>
                </div>
            </div>

            <form action="{{ route('hekim.klinik.doktorlar.guncelle', $doc->id) }}" method="POST" class="space-y-5">
                @csrf
                
                <!-- Klinik Rolü -->
                <div>
                    <label for="klinik_rolu" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Klinik İçi Rolü</label>
                    <select id="klinik_rolu" name="klinik_rolu" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-xs focus:border-[#C96A2B] outline-none cursor-pointer">
                        <option value="doktor" {{ $doc->klinik_rolu === 'doktor' ? 'selected' : '' }}>Hekim / Doktor (Standart Üye - Yetkileri Seçin)</option>
                        <option value="ortak" {{ $doc->klinik_rolu === 'ortak' ? 'selected' : '' }}>Klinik Ortağı / Eş-Sahip (Tüm Yetkiler)</option>
                        <option value="sahip" {{ $doc->klinik_rolu === 'sahip' ? 'selected' : '' }}>Klinik Sahibi / Yönetici (Tüm Yetkiler)</option>
                    </select>
                    <p class="text-[10px] text-gray-400 mt-1">Klinik sahibi ve ortak hekimler klinik ayarlarını yapabilir, diğer hekimleri ve personelleri yönetebilir.</p>
                </div>

                <!-- Yetkiler Bölümü -->
                <div id="yetkiler_alani" class="p-5 rounded-2xl border border-[#E5E7EB] bg-slate-50/30 space-y-4 transition-all {{ in_array($doc->klinik_rolu, ['sahip', 'ortak']) ? 'opacity-50 pointer-events-none' : '' }}">
                    <span class="block text-xs font-bold text-[#111827] uppercase tracking-wider font-display">Hekim Yetkileri</span>
                    <p class="text-[10px] text-[#6B7280]">Hekim rolündeki kullanıcı için izin verilen modülleri seçin:</p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="yetkiler[yonetim_paneli]" value="1" {{ $doc->hasClinicPermission('yonetim_paneli') ? 'checked' : '' }} class="mt-0.5 rounded text-[#C96A2B] focus:ring-[#C96A2B]/20">
                            <div>
                                <span class="block text-xs font-semibold text-[#111827]">Klinik Yönetim Paneli</span>
                                <span class="block text-[10px] text-gray-400">Yönetim özet paneline giriş izni</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="yetkiler[klinik_ayarlari]" value="1" {{ $doc->hasClinicPermission('klinik_ayarlari') ? 'checked' : '' }} class="mt-0.5 rounded text-[#C96A2B] focus:ring-[#C96A2B]/20">
                            <div>
                                <span class="block text-xs font-semibold text-[#111827]">Klinik Ayarları</span>
                                <span class="block text-[10px] text-gray-400">Klinik künye, logo ve çalışma saati düzenleme</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="yetkiler[hekim_yonetimi]" value="1" {{ $doc->hasClinicPermission('hekim_yonetimi') ? 'checked' : '' }} class="mt-0.5 rounded text-[#C96A2B] focus:ring-[#C96A2B]/20">
                            <div>
                                <span class="block text-xs font-semibold text-[#111827]">Hekim Yönetimi</span>
                                <span class="block text-[10px] text-gray-400">Hekim davet etme ve çıkarma yetkisi</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="yetkiler[personel_yonetimi]" value="1" {{ $doc->hasClinicPermission('personel_yonetimi') ? 'checked' : '' }} class="mt-0.5 rounded text-[#C96A2B] focus:ring-[#C96A2B]/20">
                            <div>
                                <span class="block text-xs font-semibold text-[#111827]">Personel Yönetimi</span>
                                <span class="block text-[10px] text-gray-400">Klinik personeli ekleme/düzenleme/silme</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="yetkiler[finans_yonetimi]" value="1" {{ $doc->hasClinicPermission('finans_yonetimi') ? 'checked' : '' }} class="mt-0.5 rounded text-[#C96A2B] focus:ring-[#C96A2B]/20">
                            <div>
                                <span class="block text-xs font-semibold text-[#111827]">Gider & Finans Yönetimi</span>
                                <span class="block text-[10px] text-gray-400">Klinik giderleri, gelir ve raporları görme</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="yetkiler[hakedis_yonetimi]" value="1" {{ $doc->hasClinicPermission('hakedis_yonetimi') ? 'checked' : '' }} class="mt-0.5 rounded text-[#C96A2B] focus:ring-[#C96A2B]/20">
                            <div>
                                <span class="block text-xs font-semibold text-[#111827]">Hakediş Yönetimi</span>
                                <span class="block text-[10px] text-gray-400">Tüm hekim hakedişlerini hesaplama/ödeme</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="yetkiler[ortak_hasta_havuzu]" value="1" {{ $doc->hasClinicPermission('ortak_hasta_havuzu') ? 'checked' : '' }} class="mt-0.5 rounded text-[#C96A2B] focus:ring-[#C96A2B]/20">
                            <div>
                                <span class="block text-xs font-semibold text-[#111827]">Ortak Hasta Havuzu</span>
                                <span class="block text-[10px] text-gray-400">Tüm klinik hastalarını görüntüleme</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="yetkiler[duyuru_yonetimi]" value="1" {{ $doc->hasClinicPermission('duyuru_yonetimi') ? 'checked' : '' }} class="mt-0.5 rounded text-[#C96A2B] focus:ring-[#C96A2B]/20">
                            <div>
                                <span class="block text-xs font-semibold text-[#111827]">Duyuru Yönetimi</span>
                                <span class="block text-[10px] text-gray-400">Kliniğin iç duyurularını oluşturma/silme</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Komisyon Oranı -->
                <div>
                    <label for="komisyon_orani" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Hakediş Komisyon Oranı (%)</label>
                    <input id="komisyon_orani" name="komisyon_orani" type="number" step="0.01" min="0" max="100" value="{{ old('komisyon_orani', $doc->komisyon_orani) }}" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-xs focus:border-[#C96A2B] outline-none">
                    <p class="text-[10px] text-gray-400 mt-1">Hakediş hesaplamalarında hekime ödenecek ücretten kesilecek klinik komisyon oranıdır. Varsayılan: %0.</p>
                </div>

                <button type="submit" class="w-full bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl transition-all duration-200 shadow-md shadow-orange-500/10">
                    Ayarları Kaydet
                </button>
            </form>
        </div>
    </div>
@endsection

@section('extra_js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const klinikRolu = document.getElementById('klinik_rolu');
            const yetkilerAlani = document.getElementById('yetkiler_alani');

            if (klinikRolu && yetkilerAlani) {
                klinikRolu.addEventListener('change', function() {
                    const selectValue = this.value;
                    if (selectValue === 'sahip' || selectValue === 'ortak') {
                        yetkilerAlani.classList.add('opacity-50', 'pointer-events-none');
                        // Tick all checkboxes when owner/partner is selected
                        yetkilerAlani.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                            cb.checked = true;
                        });
                    } else {
                        yetkilerAlani.classList.remove('opacity-50', 'pointer-events-none');
                    }
                });
            }
        });
    </script>
@endsection
