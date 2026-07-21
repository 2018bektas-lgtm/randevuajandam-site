@extends('yonetim.layout')

@section('baslik', 'Paketi Düzenle - Randevu Ajandam')
@section('sayfa_baslik', 'Paket Yönetimi')

@section('icerik')
    <div class="max-w-2xl mx-auto">
        <!-- Top Action Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
            <div>
                <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                    <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                    Paketi Düzenle
                </h2>
                <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Mevcut abonelik paketinin özelliklerini ve fiyatlandırmasını güncelleyin.</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('yonetim.paketler.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] hover:border-[#E7B58A]/40 transition-all duration-200 shadow-sm select-none group">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                    </svg>
                    Listeye Dön
                </a>
            </div>
        </div>



        <!-- Form Card -->
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8">
            <form action="{{ route('yonetim.paketler.update', $paket->id) }}" method="POST" class="space-y-6">
                @csrf

                <!-- Package Name -->
                <div>
                    <label for="ad" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Paket Adı</label>
                    <input type="text" name="ad" id="ad" value="{{ old('ad', $paket->ad) }}" placeholder="Örn: Profesyonel Hekim Paketi" required
                        class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                </div>

                <!-- Paket Türü -->
                <div>
                    <label for="tur" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Paket Türü</label>
                    <select name="tur" id="tur" required
                        class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                        <option value="bireysel" {{ old('tur', $paket->tur) === 'bireysel' ? 'selected' : '' }}>Bireysel Hekim Paketi</option>
                        <option value="klinik" {{ old('tur', $paket->tur) === 'klinik' ? 'selected' : '' }}>Klinik Yönetim Paketi</option>
                    </select>
                </div>

                <!-- Sıralama Sira -->
                <div>
                    <label for="sira" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Görüntüleme Sırası (Sıra)</label>
                    <input type="number" name="sira" id="sira" value="{{ old('sira', $paket->sira ?? 0) }}" placeholder="0" required
                        class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                </div>

                <!-- Klinik Paketine Özel Alanlar -->
                <div id="clinicFields" class="hidden space-y-6 p-6 border border-[#E5E7EB] rounded-2xl bg-[#FFF7ED]/30">
                    <h3 class="text-sm font-bold text-[#C96A2B] uppercase tracking-wider font-display pb-3 border-b border-orange-100">Klinik Yönetim Özellikleri</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="max_doktor_sayisi" class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Maksimum Hekim Sayısı</label>
                            <input type="number" name="max_doktor_sayisi" id="max_doktor_sayisi" value="{{ old('max_doktor_sayisi', $paket->max_doktor_sayisi ?? 3) }}" min="1"
                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                        </div>
                        <div>
                            <label for="max_personel_sayisi" class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Maksimum Personel Sayısı</label>
                            <input type="number" name="max_personel_sayisi" id="max_personel_sayisi" value="{{ old('max_personel_sayisi', $paket->max_personel_sayisi ?? 3) }}" min="1"
                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider font-display">Klinik Modül Yetkileri</label>
                        
                        <!-- Finans Toggle -->
                        <div class="flex items-center justify-between py-2 border-b border-[#E5E7EB]">
                            <div>
                                <span class="block text-xs font-bold text-[#374151]">Merkezi Finans + Muhasebeci Girişi</span>
                                <span class="block text-[10px] text-[#6B7280]">Klinik finans modülü ve muhasebeci personel rolü (giriş + finans yetkisi).</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" name="merkezi_finans_mi" value="1" {{ old('merkezi_finans_mi', $paket->merkezi_finans_mi) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[2.5px] after:left-[2.5px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all after:duration-300 peer-checked:after:translate-x-4 shadow-inner"></div>
                            </label>
                        </div>

                        <!-- Toplu Randevu Toggle -->
                        <div class="flex items-center justify-between py-2 border-b border-[#E5E7EB]">
                            <div>
                                <span class="block text-xs font-bold text-[#374151]">Toplu Randevu Yönetimi</span>
                                <span class="block text-[10px] text-[#6B7280]">Tüm hekimlerin randevularını tek bir merkezi takvimde yönetme.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" name="toplu_randevu_mi" value="1" {{ old('toplu_randevu_mi', $paket->toplu_randevu_mi) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[2.5px] after:left-[2.5px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all after:duration-300 peer-checked:after:translate-x-4 shadow-inner"></div>
                            </label>
                        </div>

                        <!-- Raporlama Toggle -->
                        <div class="flex items-center justify-between py-2 border-b border-[#E5E7EB]">
                            <div>
                                <span class="block text-xs font-bold text-[#374151]">Gelişmiş Raporlama</span>
                                <span class="block text-[10px] text-[#6B7280]">Klinik doluluk oranları, randevu grafikleri ve PDF raporları.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" name="raporlama_mi" value="1" {{ old('raporlama_mi', $paket->raporlama_mi) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[2.5px] after:left-[2.5px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all after:duration-300 peer-checked:after:translate-x-4 shadow-inner"></div>
                            </label>
                        </div>

                        <!-- Ortak Hasta Havuzu Toggle -->
                        <div class="flex items-center justify-between py-2">
                            <div>
                                <span class="block text-xs font-bold text-[#374151]">Ortak Hasta Havuzu</span>
                                <span class="block text-[10px] text-[#6B7280]">Tüm kliniğe kayıtlı hastaların tek bir havuzda birleştirilmesi.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" name="hasta_havuzu_mi" value="1" {{ old('hasta_havuzu_mi', $paket->hasta_havuzu_mi) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[2.5px] after:left-[2.5px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all after:duration-300 peer-checked:after:translate-x-4 shadow-inner"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="aciklama" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Paket Açıklaması</label>
                    <textarea name="aciklama" id="aciklama" rows="3" placeholder="Paket hakkında kısa açıklama..."
                        class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">{{ old('aciklama', $paket->aciklama) }}</textarea>
                </div>

                <!-- Vitrin etiket / öne çıkan -->
                <div class="space-y-4 p-5 border border-[#E5E7EB] rounded-2xl bg-[#FFF7ED]/40">
                    <h3 class="text-xs font-bold text-[#C96A2B] uppercase tracking-wider font-display">Vitrin &amp; etiketler</h3>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Paket seçim sayfasında (ve mobilde) görünen şerit. Örn. <strong>Popüler</strong>, <strong>Önerilen</strong>, <strong>Web sitesi</strong>.
                        Boş bırakırsanız şerit gösterilmez (öne çıkan açıksa varsayılan “Popüler / Önerilen” kullanılır).
                    </p>
                    <div class="flex items-center justify-between py-2 border-b border-orange-100">
                        <div>
                            <span class="block text-xs font-bold text-[#374151]">Öne çıkan kart</span>
                            <span class="block text-[10px] text-[#6B7280]">Turuncu çerçeve / vurgulu stil (featured).</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="one_cikan_mi" value="1" {{ old('one_cikan_mi', $paket->one_cikan_mi) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[2.5px] after:left-[2.5px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all after:duration-300 peer-checked:after:translate-x-4 shadow-inner"></div>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-[#4B5563] uppercase mb-1">Etiket metni</label>
                            <input type="text" name="etiket" maxlength="40" value="{{ old('etiket', $paket->etiket) }}"
                                   list="etiket_onerileri"
                                   class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs"
                                   placeholder="Popüler, Önerilen, Web sitesi…">
                            <datalist id="etiket_onerileri">
                                <option value="Popüler"></option>
                                <option value="Önerilen"></option>
                                <option value="Web sitesi"></option>
                                <option value="Web sitesi dahil"></option>
                                <option value="Ücretsiz"></option>
                                <option value="14 gün deneme"></option>
                                <option value="En çok tercih"></option>
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-[#4B5563] uppercase mb-1">Etiket stili</label>
                            <select name="etiket_stil" class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs">
                                @php $stil = old('etiket_stil', $paket->etiket_stil); @endphp
                                <option value="" {{ $stil === null || $stil === '' ? 'selected' : '' }}>Otomatik</option>
                                <option value="popular" {{ $stil === 'popular' ? 'selected' : '' }}>Popüler (turuncu dolu)</option>
                                <option value="web" {{ $stil === 'web' ? 'selected' : '' }}>Web (açık turuncu)</option>
                                <option value="free" {{ $stil === 'free' ? 'selected' : '' }}>Ücretsiz (yeşil)</option>
                                <option value="trial" {{ $stil === 'trial' ? 'selected' : '' }}>Deneme (yeşil)</option>
                                <option value="custom" {{ $stil === 'custom' ? 'selected' : '' }}>Özel</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- deneme + domain (kart: PayTR) -->
                <div class="space-y-4 p-5 border border-[#E5E7EB] rounded-2xl bg-slate-50/50">
                    <h3 class="text-xs font-bold text-[#C96A2B] uppercase tracking-wider font-display">Deneme &amp; domain</h3>
                    <p class="text-[11px] text-slate-500">Kartlı ödeme yalnızca <strong>PayTR</strong>. Eski iyzico plan kodları kullanılmaz.</p>
                    <input type="hidden" name="iyzico_plan_aylik" value="{{ old('iyzico_plan_aylik', $paket->iyzico_plan_aylik) }}">
                    <input type="hidden" name="iyzico_plan_yillik" value="{{ old('iyzico_plan_yillik', $paket->iyzico_plan_yillik) }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-[#4B5563] uppercase mb-1">Deneme günü</label>
                            <input type="number" name="deneme_gun" min="0" max="90" value="{{ old('deneme_gun', $paket->deneme_gun) }}"
                                   class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs" placeholder="0 = yok, 14 = başlangıç">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-[#4B5563] uppercase mb-1">Domain dahil TLD</label>
                            <input type="text" name="domain_dahil_tlds" value="{{ old('domain_dahil_tlds', is_array($paket->domain_dahil_tlds) ? implode(',', $paket->domain_dahil_tlds) : '') }}"
                                   class="w-full px-3 py-2 rounded-xl border border-[#E5E7EB] text-xs" placeholder="com,net">
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" name="domain_dahil_mi" value="1" @checked(old('domain_dahil_mi', $paket->domain_dahil_mi)) class="rounded border-slate-300 text-[#C96A2B]">
                        Domain pakete dahil (1 yıl com/net)
                    </label>
                    <input type="hidden" name="domain_dahil_yil" value="{{ old('domain_dahil_yil', $paket->domain_dahil_yil ?? 1) }}">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-4.5 bg-slate-50/30 border border-[#E5E7EB] rounded-xl">
                    <!-- Aylık Fiyat Grubu -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold text-[#C96A2B] uppercase tracking-wider font-display pb-2 border-b border-orange-100">Aylık Plan Fiyatları</h4>
                        <div>
                            <label for="aylik_fiyat" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Aylık Fiyat (TL)</label>
                            <input type="number" name="aylik_fiyat" id="aylik_fiyat" value="{{ old('aylik_fiyat', $paket->aylik_fiyat) }}" step="0.01" min="0" placeholder="4799,00" required
                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                        </div>
                        <div>
                            <label for="aylik_indirimli_fiyat" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Aylık İndirimli Fiyat (TL)</label>
                            <input type="number" name="aylik_indirimli_fiyat" id="aylik_indirimli_fiyat" value="{{ old('aylik_indirimli_fiyat', $paket->aylik_indirimli_fiyat) }}" step="0.01" min="0" placeholder="3999,00"
                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                        </div>
                    </div>

                    <!-- Yıllık Fiyat Grubu -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold text-[#C96A2B] uppercase tracking-wider font-display pb-2 border-b border-orange-100">Yıllık Plan Fiyatları</h4>
                        <div>
                            <label for="yillik_fiyat" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Yıllık Fiyat (TL)</label>
                            <input type="number" name="yillik_fiyat" id="yillik_fiyat" value="{{ old('yillik_fiyat', $paket->yillik_fiyat) }}" step="0.01" min="0" placeholder="47990,00" required
                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                        </div>
                        <div>
                            <label for="yillik_indirimli_fiyat" class="block text-[11px] font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Yıllık İndirimli Fiyat (TL)</label>
                            <input type="number" name="yillik_indirimli_fiyat" id="yillik_indirimli_fiyat" value="{{ old('yillik_indirimli_fiyat', $paket->yillik_indirimli_fiyat) }}" step="0.01" min="0" placeholder="39990,00"
                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                        </div>
                    </div>
                </div>

                <!-- Package Features (Dynamic inputs) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display">Paket Özellikleri</label>
                        <button type="button" id="addFeatureBtn"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-[#C96A2B] text-[#C96A2B] hover:bg-[#FFF7ED] text-xs font-bold transition-all duration-150 cursor-pointer">
                            + Özellik Ekle
                        </button>
                    </div>
                    
                    <!-- Dynamic inputs wrapper -->
                    <div id="featuresContainer" class="space-y-2.5">
                        @if(is_array($paket->ozellikler) && count($paket->ozellikler) > 0)
                            @foreach($paket->ozellikler as $index => $ozellik)
                                <div class="flex items-center gap-2 feature-row">
                                    <input type="text" name="ozellikler[]" value="{{ $ozellik }}" placeholder="Örn: Aylık 100 Randevu Limiti" required
                                        class="flex-grow px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                                    <button type="button" class="p-2.5 text-slate-400 hover:text-red-500 rounded-xl hover:bg-red-50 transition-colors remove-feature-btn cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="flex items-center gap-2 feature-row">
                                <input type="text" name="ozellikler[]" placeholder="Örn: Aylık 100 Randevu Limiti" required
                                    class="flex-grow px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                                <button type="button" class="p-2.5 text-slate-400 hover:text-red-500 rounded-xl hover:bg-red-50 transition-colors remove-feature-btn cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Perfect iOS Toggle Switch -->
                <div class="flex items-center justify-between p-4.5 bg-slate-50/50 border border-[#E5E7EB] rounded-xl">
                    <div>
                        <span class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display">Paket Durumu</span>
                        <span class="block text-[11px] text-[#6B7280] mt-0.5">Oluşturulan paket doktorlar tarafından seçilebilsin mi?</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="aktif_mi" value="1" {{ $paket->aktif_mi ? 'checked' : '' }} class="sr-only peer">
                        <!-- iOS-style Switch -->
                        <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all after:duration-300 peer-checked:after:translate-x-5 shadow-inner"></div>
                    </label>
                </div>

                <!-- Submit buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#E5E7EB]">
                    <a href="{{ route('yonetim.paketler.index') }}" 
                       class="px-5 py-2.5 rounded-xl border border-[#E5E7EB] hover:bg-slate-50 text-[#6B7280] font-bold text-sm transition-all duration-200 select-none">
                        İptal Et
                    </a>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-sm transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer select-none">
                        Değişiklikleri Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script for Dynamic Inputs -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addFeatureBtn = document.getElementById('addFeatureBtn');
            const featuresContainer = document.getElementById('featuresContainer');
            const turSelect = document.getElementById('tur');
            const clinicFields = document.getElementById('clinicFields');

            // Clinic Fields Toggle
            function toggleClinicFields() {
                if (turSelect.value === 'klinik') {
                    clinicFields.classList.remove('hidden');
                } else {
                    clinicFields.classList.add('hidden');
                }
            }

            if (turSelect) {
                turSelect.addEventListener('change', toggleClinicFields);
                toggleClinicFields();
            }

            // Add dynamic input row
            addFeatureBtn.addEventListener('click', function() {
                const row = document.createElement('div');
                row.className = 'flex items-center gap-2 feature-row';
                row.innerHTML = `
                    <input type="text" name="ozellikler[]" placeholder="Örn: Yeni özellik açıklaması" required
                        class="flex-grow px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                    <button type="button" class="p-2.5 text-slate-400 hover:text-red-500 rounded-xl hover:bg-red-50 transition-colors remove-feature-btn cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                `;
                featuresContainer.appendChild(row);
                
                // Add remove listener to new button
                row.querySelector('.remove-feature-btn').addEventListener('click', function() {
                    row.remove();
                });
            });

            // Set up event delegation for remove buttons (to work with initial & new rows)
            featuresContainer.addEventListener('click', function(e) {
                const btn = e.target.closest('.remove-feature-btn');
                if (btn) {
                    const row = btn.closest('.feature-row');
                    if (document.querySelectorAll('.feature-row').length > 1) {
                        row.remove();
                    } else {
                        mesajModalAc('Lütfen en az bir özellik bırakın.', 'hata');
                    }
                }
            });
        });
    </script>
@endsection
