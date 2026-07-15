@extends('hekim.layout')

@section('baslik', 'Randevu Ayarları - Hekim Paneli')
@section('sayfa_baslik', 'Randevu Ayarları')

@section('icerik')
    <style>
        /* Custom date and time picker wrapper */
        .custom-picker-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .custom-picker-input {
            position: relative;
            padding-right: 2.75rem !important;
            cursor: pointer;
        }

        /* Hide the native calendar/clock icons in Webkit browsers */
        .custom-picker-input::-webkit-calendar-picker-indicator {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }

        /* For working hours compact view */
        .compact-picker-input {
            position: relative;
            cursor: pointer;
        }
        .compact-picker-input::-webkit-calendar-picker-indicator {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }
    </style>

    <!-- Tabs Header -->
    <div class="flex border-b border-[#E5E7EB] mb-8 gap-8 overflow-x-auto whitespace-nowrap scrollbar-none">
        <button onclick="switchTab('genel-ayarlar')" id="tab-btn-genel-ayarlar" 
                class="pb-4 text-xs font-bold uppercase tracking-wider font-display border-b-2 border-[#C96A2B] text-[#C96A2B] transition-all duration-300 cursor-pointer outline-none hover:text-[#C96A2B]">
            Genel Ayarlar
        </button>
        <button onclick="switchTab('calisma-saatleri')" id="tab-btn-calisma-saatleri" 
                class="pb-4 text-xs font-bold uppercase tracking-wider font-display border-b-2 border-transparent text-[#6B7280] hover:text-[#1F2937] hover:border-[#E5E7EB] transition-all duration-300 cursor-pointer outline-none">
            Çalışma Saatleri
        </button>
        <button onclick="switchTab('izinler-tatiller')" id="tab-btn-izinler-tatiller" 
                class="pb-4 text-xs font-bold uppercase tracking-wider font-display border-b-2 border-transparent text-[#6B7280] hover:text-[#1F2937] hover:border-[#E5E7EB] transition-all duration-300 cursor-pointer outline-none">
            Tatil / İzin Günleri
        </button>
    </div>

    <!-- Tab 1: General Settings -->
    <div id="tab-content-genel-ayarlar" class="tab-content">
        <div class="max-w-5xl">
            <div class="p-8 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)]">
                <div class="flex items-center gap-3 border-b border-[#E5E7EB] pb-5 mb-6">
                    <span class="text-[#C96A2B] text-lg font-bold">⚙️</span>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">
                        Genel Randevu Ayarları
                    </h3>
                </div>

                <form action="{{ route('hekim.randevu.ayarlar.post') }}" method="POST" class="space-y-8">
                    @csrf

                    <!-- Status iOS Toggles Group -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="p-5 rounded-2xl bg-[#FAFAFA] border border-[#E5E7EB] flex flex-col justify-between gap-4 hover:border-[#E7B58A]/30 transition-all duration-300">
                            <div class="space-y-1">
                                <span class="block text-xs font-bold text-[#111827] font-display">Online Randevu Kabulü</span>
                                <span class="block text-[11px] text-[#6B7280] leading-relaxed">Hastaların profilinizden online randevu almasını sağlar.</span>
                            </div>
                            
                            <div class="flex justify-end pt-2">
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" name="aktif_mi" value="1" 
                                           {{ $ayarlar->aktif_mi ? 'checked' : '' }} 
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                                </label>
                            </div>
                        </div>

                        <div class="p-5 rounded-2xl bg-[#FAFAFA] border border-[#E5E7EB] flex flex-col justify-between gap-4 hover:border-[#E7B58A]/30 transition-all duration-300">
                            <div class="space-y-1">
                                <span class="block text-xs font-bold text-[#111827] font-display">Online Randevu İptali</span>
                                <span class="block text-[11px] text-[#6B7280] leading-relaxed">Hastaların kendi panellerinden randevularını iptal etmesine izin verir.</span>
                            </div>
                            
                            <div class="flex justify-end pt-2">
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" name="randevu_iptal_aktif_mi" value="1" 
                                           {{ $ayarlar->randevu_iptal_aktif_mi ? 'checked' : '' }} 
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                                </label>
                            </div>
                        </div>

                        <div class="p-5 rounded-2xl bg-[#FAFAFA] border border-[#E5E7EB] flex flex-col justify-between gap-4 hover:border-[#E7B58A]/30 transition-all duration-300">
                            <div class="space-y-1">
                                <span class="block text-xs font-bold text-[#111827] font-display">E-Posta Bildirimleri</span>
                                <span class="block text-[11px] text-[#6B7280] leading-relaxed">Yeni randevu ve iptal taleplerinde bilgilendirme e-postası alırsınız.</span>
                            </div>
                            
                            <div class="flex justify-end pt-2">
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" name="email_bildirimleri" value="1" 
                                           {{ $ayarlar->email_bildirimleri ? 'checked' : '' }} 
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                                </label>
                            </div>
                        </div>

                        <div class="p-5 rounded-2xl bg-[#FAFAFA] border border-[#E5E7EB] flex flex-col justify-between gap-4 hover:border-[#E7B58A]/30 transition-all duration-300">
                            <div class="space-y-1">
                                <span class="block text-xs font-bold text-[#111827] font-display">SMS Bildirimleri</span>
                                <span class="block text-[11px] text-[#6B7280] leading-relaxed">Yeni randevu ve onay/iptal durumlarında SMS ile bildirim alırsınız.</span>
                            </div>
                            
                            <div class="flex justify-end pt-2">
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" name="sms_bildirimleri" value="1" 
                                           {{ $ayarlar->sms_bildirimleri ? 'checked' : '' }} 
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Type Selection -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">Randevu Onay Tipi</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <label class="p-5 rounded-2xl border border-[#E5E7EB] bg-[#FAFAFA] hover:bg-white hover:border-[#C96A2B] cursor-pointer flex items-start gap-4 transition-all duration-300 shadow-sm">
                                <input type="radio" name="randevu_onay_tipi" value="manuel" 
                                       {{ $ayarlar->randevu_onay_tipi === 'manuel' ? 'checked' : '' }} 
                                       class="mt-1 accent-[#C96A2B] w-4 h-4">
                                <div class="space-y-1">
                                    <span class="block text-xs font-bold text-[#111827] font-display">Manuel Onay</span>
                                    <span class="block text-[11px] text-[#6B7280] leading-relaxed">Gelen randevu talepleri sizin tarafınızdan panelden onaylanana kadar "Beklemede" kalır.</span>
                                </div>
                            </label>

                            <label class="p-5 rounded-2xl border border-[#E5E7EB] bg-[#FAFAFA] hover:bg-white hover:border-[#C96A2B] cursor-pointer flex items-start gap-4 transition-all duration-300 shadow-sm">
                                <input type="radio" name="randevu_onay_tipi" value="otomatik" 
                                       {{ $ayarlar->randevu_onay_tipi === 'otomatik' ? 'checked' : '' }} 
                                       class="mt-1 accent-[#C96A2B] w-4 h-4">
                                <div class="space-y-1">
                                    <span class="block text-xs font-bold text-[#111827] font-display">Otomatik Onay</span>
                                    <span class="block text-[11px] text-[#6B7280] leading-relaxed">Hastanın seçtiği müsait saat dilimi sistem tarafından anında onaylanır.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Limits inputs -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label for="en_erken_randevu_saati" class="block text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">En Erken Randevu Zamanı (Saat)</label>
                            <input type="number" name="en_erken_randevu_saati" id="en_erken_randevu_saati" 
                                   value="{{ $ayarlar->en_erken_randevu_saati }}" min="0" required
                                   class="w-full px-4 py-3 rounded-xl border border-[#E5E7EB] bg-white text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                            <p class="text-[10px] text-[#6B7280] leading-relaxed">Hastanın en erken kaç saat sonrası için randevu alabileceğini belirtir. (örn: 2 saat)</p>
                        </div>

                        <div class="space-y-2">
                            <label for="en_gec_randevu_gunu" class="block text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">En Geç Randevu Zamanı (Gün)</label>
                            <input type="number" name="en_gec_randevu_gunu" id="en_gec_randevu_gunu" 
                                   value="{{ $ayarlar->en_gec_randevu_gunu }}" min="1" required
                                   class="w-full px-4 py-3 rounded-xl border border-[#E5E7EB] bg-white text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                            <p class="text-[10px] text-[#6B7280] leading-relaxed">Hastanın en fazla kaç gün sonrası için randevu alabileceğini belirtir. (örn: 30 gün)</p>
                        </div>

                        <div class="space-y-2">
                            <label for="randevu_periyodu" class="block text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">Randevu Periyodu (Süresi)</label>
                            <select name="randevu_periyodu" id="randevu_periyodu" required
                                    class="w-full px-4 py-3 rounded-xl border border-[#E5E7EB] bg-white text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                                <option value="15" {{ $ayarlar->randevu_periyodu == 15 ? 'selected' : '' }}>15 Dakika</option>
                                <option value="20" {{ $ayarlar->randevu_periyodu == 20 ? 'selected' : '' }}>20 Dakika</option>
                                <option value="30" {{ $ayarlar->randevu_periyodu == 30 ? 'selected' : '' }}>30 Dakika</option>
                                <option value="45" {{ $ayarlar->randevu_periyodu == 45 ? 'selected' : '' }}>45 Dakika</option>
                                <option value="60" {{ $ayarlar->randevu_periyodu == 60 ? 'selected' : '' }}>60 Dakika</option>
                            </select>
                            <p class="text-[10px] text-[#6B7280] leading-relaxed">Takvimdeki her bir randevu diliminin süresini belirtir. (örn: 30 dakika)</p>
                        </div>

                        <div class="space-y-2">
                            <label for="iptal_saat_limiti" class="block text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">İptal Limit Süresi (Saat)</label>
                            <input type="number" name="iptal_saat_limiti" id="iptal_saat_limiti" 
                                   value="{{ $ayarlar->iptal_saat_limiti }}" min="0" required
                                   class="w-full px-4 py-3 rounded-xl border border-[#E5E7EB] bg-white text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                            <p class="text-[10px] text-[#6B7280] leading-relaxed">Randevuya en az kaç saat kala iptal hakkının kapatılacağını belirtir. (örn: 24 saat, 0 = limitsiz)</p>
                        </div>

                        <div class="space-y-2">
                            <label for="gunluk_maksimum_randevu" class="block text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">Günlük Maksimum Randevu</label>
                            <input type="number" name="gunluk_maksimum_randevu" id="gunluk_maksimum_randevu" 
                                   value="{{ $ayarlar->gunluk_maksimum_randevu }}" min="0" required
                                   class="w-full px-4 py-3 rounded-xl border border-[#E5E7EB] bg-white text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                            <p class="text-[10px] text-[#6B7280] leading-relaxed">Hekimin günlük alabileceği maksimum randevu sayısıdır. (0 = limitsiz)</p>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-end border-t border-[#E5E7EB] pt-6">
                        <button type="submit" class="px-8 py-3.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-300 font-display shadow-[0_4px_12px_rgba(201,106,43,0.15)] hover:shadow-[0_6px_20px_rgba(201,106,43,0.25)] cursor-pointer">
                            Ayarları Güncelle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tab 2: Working Hours -->
    <div id="tab-content-calisma-saatleri" class="tab-content hidden">
        <div class="max-w-6xl space-y-6">
            <div class="p-8 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)]">
                <div class="flex items-center gap-3 border-b border-[#E5E7EB] pb-5 mb-5">
                    <span class="text-[#C96A2B] text-lg font-bold">📅</span>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Haftalık Çalışma Saatleri</h3>
                </div>
                <p class="text-xs text-[#6B7280] leading-relaxed max-w-3xl">
                    Haftalık çalışma günlerinizi ve saatlerinizi buradan planlayabilirsiniz. Aktif edilmeyen günlerde hastalarınız sistem üzerinden randevu talep edemezler. Öğle arası aktif edildiğinde ise belirlenen saat aralığı randevuya kapatılır.
                </p>
            </div>

            <form action="{{ route('hekim.randevu.calisma-saatleri.post') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="space-y-4">
                    <!-- Table Header for Grid Alignment on Large Screens -->
                    <div class="hidden lg:grid lg:grid-cols-12 gap-6 px-8 py-3 bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                        <div class="lg:col-span-3">Gün / Durum</div>
                        <div class="lg:col-span-3 text-center">Çalışma Saatleri</div>
                        <div class="lg:col-span-3 text-center">Öğle Arası Durumu</div>
                        <div class="lg:col-span-3 text-center">Öğle Arası Saatleri</div>
                    </div>

                    @foreach($calismaSaatleri as $cs)
                        <div class="p-6 bg-white border border-[#E5E7EB] rounded-2xl shadow-[0_4px_24px_rgba(31,41,55,0.02)] hover:border-[#E7B58A]/30 transition-all duration-300">
                            <div class="grid grid-cols-1 lg:grid-cols-12 items-center gap-6">
                                
                                <!-- Day Name and Enable/Disable Day Toggle Switch -->
                                <div class="lg:col-span-3 flex items-center justify-between lg:justify-start gap-4">
                                    <span class="text-xs font-bold font-display text-[#111827] min-w-[70px]">{{ $cs->gun_adi }}</span>
                                    
                                    <!-- iOS Style Toggle Switch -->
                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                        <input type="hidden" name="saatler[{{ $cs->id }}][gun]" value="{{ $cs->gun }}">
                                        <input type="checkbox" name="saatler[{{ $cs->id }}][aktif_mi]" value="1" 
                                               {{ $cs->aktif_mi ? 'checked' : '' }} 
                                               class="sr-only peer" id="aktif_day_{{ $cs->id }}"
                                               onchange="toggleDayInputs({{ $cs->id }}, this.checked)">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                                        <span class="ml-2.5 text-[10px] font-bold uppercase tracking-wider text-[#6B7280] peer-checked:text-[#C96A2B] font-display min-w-[45px]" id="label_day_{{ $cs->id }}">
                                            {{ $cs->aktif_mi ? 'Aktif' : 'Kapalı' }}
                                        </span>
                                    </label>
                                </div>

                                <!-- Shift start and end time -->
                                <div id="day_inputs_{{ $cs->id }}" class="lg:col-span-3 flex items-center justify-between lg:justify-center gap-3 {{ $cs->aktif_mi ? '' : 'opacity-40 pointer-events-none' }} transition-all duration-200">
                                    <div class="flex-1 max-w-[110px] relative">
                                        <label class="block lg:hidden text-[9px] font-bold text-[#6B7280] uppercase tracking-wider mb-1 font-display">Mesai Başlangıç</label>
                                        <input type="time" name="saatler[{{ $cs->id }}][mesai_baslangic]" 
                                               value="{{ substr($cs->mesai_baslangic, 0, 5) }}" required
                                               class="compact-picker-input w-full px-3 py-2 text-center rounded-lg border border-[#E5E7EB] bg-[#FAFAFA] text-xs focus:outline-none focus:border-[#C96A2B] focus:bg-white transition-all font-semibold">
                                    </div>
                                    <span class="text-[#6B7280] font-medium">-</span>
                                    <div class="flex-1 max-w-[110px] relative">
                                        <label class="block lg:hidden text-[9px] font-bold text-[#6B7280] uppercase tracking-wider mb-1 font-display">Mesai Bitiş</label>
                                        <input type="time" name="saatler[{{ $cs->id }}][mesai_bitis]" 
                                               value="{{ substr($cs->mesai_bitis, 0, 5) }}" required
                                               class="compact-picker-input w-full px-3 py-2 text-center rounded-lg border border-[#E5E7EB] bg-[#FAFAFA] text-xs focus:outline-none focus:border-[#C96A2B] focus:bg-white transition-all font-semibold">
                                    </div>
                                </div>

                                <!-- Lunch Break Toggle Switch -->
                                <div id="lunch_toggle_container_{{ $cs->id }}" class="lg:col-span-3 flex items-center justify-between lg:justify-center gap-3 {{ $cs->aktif_mi ? '' : 'opacity-40 pointer-events-none' }} transition-all duration-200">
                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" name="saatler[{{ $cs->id }}][ogle_arasi_aktif_mi]" value="1" 
                                               {{ $cs->ogle_arasi_aktif_mi ? 'checked' : '' }} 
                                               class="sr-only peer" id="aktif_lunch_{{ $cs->id }}"
                                               onchange="toggleLunchInputs({{ $cs->id }}, this.checked)">
                                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                                        <span class="ml-2 text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display peer-checked:text-[#C96A2B]">Öğle Molası</span>
                                    </label>
                                </div>

                                <!-- Lunch Break Times -->
                                <div id="lunch_times_{{ $cs->id }}" class="lg:col-span-3 flex items-center justify-between lg:justify-center gap-3 {{ ($cs->aktif_mi && $cs->ogle_arasi_aktif_mi) ? '' : 'opacity-40 pointer-events-none' }} transition-all duration-200">
                                    <div class="flex-1 max-w-[110px] relative">
                                        <label class="block lg:hidden text-[9px] font-bold text-[#6B7280] uppercase tracking-wider mb-1 font-display">Mola Başlangıç</label>
                                        <input type="time" name="saatler[{{ $cs->id }}][ogle_baslangic]" 
                                               value="{{ $cs->ogle_baslangic ? substr($cs->ogle_baslangic, 0, 5) : '12:00' }}"
                                               class="compact-picker-input w-full px-3 py-2 text-center rounded-lg border border-[#E5E7EB] bg-[#FAFAFA] text-xs focus:outline-none focus:border-[#C96A2B] focus:bg-white transition-all font-semibold">
                                    </div>
                                    <span class="text-[#6B7280] font-medium">-</span>
                                    <div class="flex-1 max-w-[110px] relative">
                                        <label class="block lg:hidden text-[9px] font-bold text-[#6B7280] uppercase tracking-wider mb-1 font-display">Mola Bitiş</label>
                                        <input type="time" name="saatler[{{ $cs->id }}][ogle_bitis]" 
                                               value="{{ $cs->ogle_bitis ? substr($cs->ogle_bitis, 0, 5) : '13:00' }}"
                                               class="compact-picker-input w-full px-3 py-2 text-center rounded-lg border border-[#E5E7EB] bg-[#FAFAFA] text-xs focus:outline-none focus:border-[#C96A2B] focus:bg-white transition-all font-semibold">
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-end pt-4">
                    <button type="submit" class="px-8 py-3.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-300 font-display shadow-[0_4px_12px_rgba(201,106,43,0.15)] hover:shadow-[0_6px_20px_rgba(201,106,43,0.25)] cursor-pointer">
                        Çalışma Saatlerini Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab 3: Holidays / Leaves -->
    <div id="tab-content-izinler-tatiller" class="tab-content hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left 2 Columns: Leaves List -->
            <div class="lg:col-span-2 space-y-6">
                <div class="p-8 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)]">
                    <div class="flex items-center gap-3 border-b border-[#E5E7EB] pb-5 mb-6">
                        <span class="text-[#C96A2B] text-lg font-bold">🌴</span>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">
                            Kayıtlı İzin / Tatil Günleri
                        </h3>
                    </div>

                    @if($izinler->isEmpty())
                        <div class="p-8 text-center text-[#6B7280] text-xs bg-[#FAFAFA] rounded-xl border border-dashed border-[#E5E7EB]">
                            Tanımlanmış herhangi bir izin veya tatil bulunmuyor.
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-[#E5E7EB]">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-[#FAFAFA] border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                                        <th class="px-6 py-4">Başlangıç Zamanı</th>
                                        <th class="px-6 py-4">Bitiş Zamanı</th>
                                        <th class="px-6 py-4">Açıklama</th>
                                        <th class="px-6 py-4 text-right">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5E7EB] text-[#4B5563]">
                                    @foreach($izinler as $izin)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4 font-medium text-[#111827]">
                                                {{ $izin->baslangic_zaman->translatedFormat('d F Y - H:i') }}
                                            </td>
                                            <td class="px-6 py-4 font-medium text-[#111827]">
                                                {{ $izin->bitis_zaman->translatedFormat('d F Y - H:i') }}
                                            </td>
                                            <td class="px-6 py-4 text-[#6B7280]">
                                                {{ $izin->aciklama ?? 'Belirtilmedi' }}
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <form action="{{ route('hekim.randevu.izin-sil', $izin->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="onayModalAc(event, this.form, 'Bu izin/tatil dönemini silmek istediğinize emin misiniz?')"
                                                            class="text-red-600 hover:text-red-700 font-bold text-[10px] uppercase tracking-wider font-display cursor-pointer hover:underline transition-all">
                                                        Sil
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Add Leave Form -->
            <div class="space-y-6">
                <div class="p-8 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)]">
                    <div class="flex items-center gap-3 border-b border-[#E5E7EB] pb-5 mb-6">
                        <span class="text-[#C96A2B] text-lg font-bold">➕</span>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">
                            Yeni İzin / Tatil Ekle
                        </h3>
                    </div>

                    <form action="{{ route('hekim.randevu.izin-ekle') }}" method="POST" class="space-y-5">
                        @csrf

                        <!-- Start DateTime -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Başlangıç Tarihi</label>
                                <div class="custom-picker-container">
                                    <input type="date" name="baslangic_tarih" id="izin_bas_tarih" value="{{ old('baslangic_tarih', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required
                                           onchange="izinTarihleriniFormatla()"
                                           class="custom-picker-input w-full px-4 py-3 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] text-xs focus:outline-none focus:border-[#C96A2B] focus:bg-white transition-all font-semibold">
                                    <span class="absolute right-3.5 text-gray-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Başlangıç Saati</label>
                                <div class="custom-picker-container">
                                    <input type="time" name="baslangic_saat" id="izin_bas_saat" value="{{ old('baslangic_saat', '09:00') }}" required
                                           onchange="izinTarihleriniFormatla()"
                                           class="custom-picker-input w-full px-4 py-3 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] text-xs focus:outline-none focus:border-[#C96A2B] focus:bg-white transition-all font-semibold">
                                    <span class="absolute right-3.5 text-gray-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div class="col-span-2 text-[10px] font-semibold text-[#C96A2B] font-display mt-0.5" id="izin_baslangic_formatli"></div>
                        </div>

                        <!-- End DateTime -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Bitiş Tarihi</label>
                                <div class="custom-picker-container">
                                    <input type="date" name="bitis_tarih" id="izin_bit_tarih" value="{{ old('bitis_tarih', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required
                                           onchange="izinTarihleriniFormatla()"
                                           class="custom-picker-input w-full px-4 py-3 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] text-xs focus:outline-none focus:border-[#C96A2B] focus:bg-white transition-all font-semibold">
                                    <span class="absolute right-3.5 text-gray-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Bitiş Saati</label>
                                <div class="custom-picker-container">
                                    <input type="time" name="bitis_saat" id="izin_bit_saat" value="{{ old('bitis_saat', '17:00') }}" required
                                           onchange="izinTarihleriniFormatla()"
                                           class="custom-picker-input w-full px-4 py-3 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] text-xs focus:outline-none focus:border-[#C96A2B] focus:bg-white transition-all font-semibold">
                                    <span class="absolute right-3.5 text-gray-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div class="col-span-2 text-[10px] font-semibold text-[#C96A2B] font-display mt-0.5" id="izin_bitis_formatli"></div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <label for="aciklama" class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Açıklama (Opsiyonel)</label>
                            <textarea id="aciklama" name="aciklama" rows="3" placeholder="Örn: Yıllık İzin, Kongre Katılımı..." 
                                      class="w-full px-4 py-3 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] text-xs placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:bg-white transition-all resize-none leading-relaxed">{{ old('aciklama') }}</textarea>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="w-full py-3.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-300 shadow-[0_4px_12px_rgba(201,106,43,0.15)] hover:shadow-[0_6px_20px_rgba(201,106,43,0.25)] cursor-pointer font-display">
                            İzin Dönemini Tanımla
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script Block for Tabs switching and toggling work inputs -->
    <script>
        function switchTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.add('hidden');
            });
            
            // Show selected tab content
            document.getElementById(`tab-content-${tabId}`).classList.remove('hidden');
            
            // Reset all tab button styles
            document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
                btn.classList.remove('border-[#C96A2B]', 'text-[#C96A2B]');
                btn.classList.add('border-transparent', 'text-[#6B7280]');
            });
            
            // Set active tab button style
            const activeBtn = document.getElementById(`tab-btn-${tabId}`);
            activeBtn.classList.remove('border-transparent', 'text-[#6B7280]');
            activeBtn.classList.add('border-[#C96A2B]', 'text-[#C96A2B]');

            // Store active tab in localStorage
            localStorage.setItem('activeSettingsTab', tabId);
        }

        function toggleDayInputs(id, isChecked) {
            const inputsDiv = document.getElementById(`day_inputs_${id}`);
            const lunchToggleDiv = document.getElementById(`lunch_toggle_container_${id}`);
            const lunchDiv = document.getElementById(`lunch_times_${id}`);
            const labelSpan = document.getElementById(`label_day_${id}`);
            
            if (isChecked) {
                inputsDiv.classList.remove('opacity-40', 'pointer-events-none');
                lunchToggleDiv.classList.remove('opacity-40', 'pointer-events-none');
                labelSpan.innerText = 'Aktif';
                
                // Also toggle lunch inputs based on lunch checkbox state
                const lunchCheckbox = document.getElementById(`aktif_lunch_${id}`);
                if (lunchCheckbox && lunchCheckbox.checked) {
                    lunchDiv.classList.remove('opacity-40', 'pointer-events-none');
                }
            } else {
                inputsDiv.classList.add('opacity-40', 'pointer-events-none');
                lunchToggleDiv.classList.add('opacity-40', 'pointer-events-none');
                lunchDiv.classList.add('opacity-40', 'pointer-events-none');
                labelSpan.innerText = 'Kapalı';
            }
        }

        function toggleLunchInputs(id, isChecked) {
            const lunchDiv = document.getElementById(`lunch_times_${id}`);
            if (isChecked) {
                lunchDiv.classList.remove('opacity-40', 'pointer-events-none');
            } else {
                lunchDiv.classList.add('opacity-40', 'pointer-events-none');
            }
        }

        function izinTarihleriniFormatla() {
            const basTarihVal = document.querySelector('input[name="baslangic_tarih"]')?.value;
            const basSaatVal = document.querySelector('input[name="baslangic_saat"]')?.value;
            const bitTarihVal = document.querySelector('input[name="bitis_tarih"]')?.value;
            const bitSaatVal = document.querySelector('input[name="bitis_saat"]')?.value;

            const formatBas = document.getElementById('izin_baslangic_formatli');
            const formatBit = document.getElementById('izin_bitis_formatli');

            if (basTarihVal && formatBas) {
                const parts = basTarihVal.split('-');
                const d = new Date(parts[0], parts[1] - 1, parts[2]);
                const tarihStr = d.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short', year: 'numeric', weekday: 'short' });
                formatBas.innerText = `${tarihStr} - Saat ${basSaatVal || ''}`;
            }

            if (bitTarihVal && formatBit) {
                const parts = bitTarihVal.split('-');
                const d = new Date(parts[0], parts[1] - 1, parts[2]);
                const tarihStr = d.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short', year: 'numeric', weekday: 'short' });
                formatBit.innerText = `${tarihStr} - Saat ${bitSaatVal || ''}`;
            }
        }

        // Restore active tab on load
        document.addEventListener('DOMContentLoaded', function() {
            let activeTab = "{{ session('active_tab') }}";
            if (!activeTab) {
                activeTab = localStorage.getItem('activeSettingsTab') || 'genel-ayarlar';
            }
            if (document.getElementById(`tab-content-${activeTab}`)) {
                switchTab(activeTab);
            }
            
            // Format izin dates initially
            izinTarihleriniFormatla();
        });
    </script>
@endsection
