@extends('frontend.layouts.app')

@section('baslik', ($hizmet->meta_baslik ?? $hizmet->ad) . ' - ' . ($doktor->unvan ? $doktor->unvan . ' ' : '') . $doktor->ad_soyad . ' - Randevu Ajandam')
@section('meta_aciklama', $hizmet->meta_aciklama ?? Str::limit(strip_tags($hizmet->aciklama), 150))
@section('meta_anahtar_kelimeler', $hizmet->meta_anahtar_kelimeler ?? '')
@section('og_image', $hizmet->resim ? asset($hizmet->resim) : asset('assets/images/logo.png'))
@section('og_type', 'website')

@section('icerik')
<section class="relative bg-[#FAFAFA] py-16 md:py-24 overflow-hidden min-h-[85vh]">
    <!-- Background Ambient Lights -->
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-[#C96A2B]/3 blur-[120px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        
        <!-- Back Navigation Link -->
        <div class="mb-8">
            <a href="{{ $doktor->profil_url }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#6B7280] hover:text-[#C96A2B] transition-colors font-display uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                </svg>
                Hekim Profiline Dön
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: Service Details -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Service Card -->
                <article class="bg-white border border-[#E5E7EB] rounded-3xl overflow-hidden shadow-[0_8px_30px_rgba(31,41,55,0.02)]">
                    @if($hizmet->resim)
                        <div class="w-full h-[300px] md:h-[380px] overflow-hidden relative">
                            <img src="{{ asset($hizmet->resim) }}" alt="{{ $hizmet->ad }}" class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="p-8 md:p-10 space-y-6">
                        <!-- Meta Info -->
                        <div class="flex flex-wrap items-center gap-3.5 text-[11px] font-bold font-display uppercase tracking-wider text-[#C96A2B] border-b border-slate-100 pb-5">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $hizmet->sure }} Dakika Süre
                            </span>
                            <span class="text-slate-300">•</span>
                            <span>Hizmet ve Tedavi Bilgisi</span>
                        </div>

                        <!-- Title -->
                        <h1 class="text-2xl md:text-3xl font-extrabold font-display text-[#111827] tracking-tight leading-tight">
                            {{ $hizmet->ad }}
                        </h1>

                        <!-- Content -->
                        @if($hizmet->aciklama)
                            <div class="text-sm text-[#4B5563] leading-relaxed space-y-4 font-normal prose prose-sm max-w-none">
                                {!! $hizmet->aciklama !!}
                            </div>
                        @else
                            <p class="text-sm text-gray-400">Bu hizmet hakkında detaylı açıklama bulunmamaktadır.</p>
                        @endif
                    </div>
                </article>

                <!-- Doctor Info Card -->
                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6 relative overflow-hidden">
                    <div class="flex items-center gap-4 text-center sm:text-left flex-col sm:flex-row">
                        @php
                            $kisaAd = '';
                            if ($doktor->ad_soyad) {
                                $words = explode(' ', $doktor->ad_soyad);
                                $kisaAd = mb_strtoupper(mb_substr($words[0], 0, 1));
                                if (count($words) > 1) {
                                    $kisaAd .= mb_strtoupper(mb_substr(end($words), 0, 1));
                                }
                            } else {
                                $kisaAd = 'DR';
                            }
                        @endphp
                        <div class="w-16 h-16 rounded-2xl overflow-hidden bg-[#FFF7ED] border border-[#E7B58A]/30 text-[#C96A2B] flex items-center justify-center font-extrabold font-display text-lg shadow-sm shrink-0 select-none">
                            @if($doktor->profil_resmi)
                                <img src="{{ asset($doktor->profil_resmi) }}" alt="{{ $doktor->ad_soyad }}" class="w-full h-full object-cover">
                            @else
                                {{ $kisaAd }}
                            @endif
                        </div>
                        <div>
                            <h4 class="text-base font-bold font-display text-[#111827]">
                                {{ $doktor->unvan ? $doktor->unvan . ' ' : '' }}{{ $doktor->ad_soyad }}
                            </h4>
                            <p class="text-xs font-semibold text-[#C96A2B] font-display uppercase tracking-wider mt-0.5">
                                {{ $doktor->uzmanlik_alani ?? 'Genel Branş Hekimi' }}
                            </p>
                            <p class="text-[11px] text-[#6B7280] mt-1">
                                {{ $doktor->il?->ad }}{{ $doktor->ilce?->ad ? ' / ' . $doktor->ilce->ad : '' }}
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <a href="{{ $doktor->profil_url }}" class="inline-flex px-6 py-3 bg-[#1F2937] hover:bg-[#111827] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-200 shadow-sm font-display items-center gap-2">
                            Profili Görüntüle
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                 <!-- Right Column: Booking Widget -->
            <div class="space-y-8">
                @if($doktor->randevuya_acik_mi)
                    @if(Auth::guard('hasta')->check())
                        <!-- Online Booking Form (Real) -->
                        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-md relative overflow-hidden">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display border-b border-slate-100 pb-3.5 mb-4">
                                Online Randevu Planla
                            </h3>

                            @if(session('basarili'))
                                <div class="p-3 mb-4 bg-emerald-50 border border-emerald-100 rounded-xl text-[11px] text-emerald-700 font-medium">
                                    {{ session('basarili') }}
                                </div>
                            @endif

                            @if(session('hata'))
                                <div class="p-3 mb-4 bg-red-50 border border-red-100 rounded-xl text-[11px] text-red-700 font-medium">
                                    {{ session('hata') }}
                                </div>
                            @endif
                            
                            <form action="{{ route('frontend.hasta.randevu.kaydet') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="doktor_id" value="{{ $doktor->id }}">

                                <!-- Select Service (Locked/Preselected but selectable if multiple exist) -->
                                <div class="space-y-1">
                                    <label for="p_hizmet" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Almak İstediğiniz Hizmet</label>
                                    <select id="p_hizmet" name="hizmet_id" required 
                                            class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                                        @foreach($doktor->hizmetler as $hiz)
                                            @if($hiz->aktif_mi)
                                                <option value="{{ $hiz->id }}" {{ old('hizmet_id', $hizmet->id) == $hiz->id ? 'selected' : '' }}>
                                                    {{ $hiz->ad }} ({{ $hiz->sure }} dk)
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Confirm Patient Data -->
                                <div class="p-3 bg-slate-50 border border-slate-100 rounded-2xl space-y-1 text-xs">
                                    <p class="text-[9px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Randevu Sahibi</p>
                                    <p class="font-bold text-[#111827]">{{ Auth::guard('hasta')->user()->ad_soyad }}</p>
                                    <p class="text-[10px] text-[#6B7280]">{{ Auth::guard('hasta')->user()->telefon }} • {{ Auth::guard('hasta')->user()->e_posta }}</p>
                                </div>

                                <!-- Date -->
                                <div class="space-y-1">
                                    <label for="p_tarih" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Tercih Edilen Tarih</label>
                                    <input type="date" name="tarih" id="p_tarih" required value="{{ old('tarih', date('Y-m-d', strtotime('+1 day'))) }}" min="{{ date('Y-m-d') }}"
                                           class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                                </div>

                                <!-- Time Slot -->
                                <div class="space-y-1">
                                    <label for="p_saat" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Tercih Edilen Saat</label>
                                    <select name="saat" id="p_saat" required 
                                            class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all">
                                        <option value="" disabled selected>Önce Tarih Seçin...</option>
                                    </select>
                                </div>

                                <!-- Notes -->
                                <div class="space-y-1">
                                    <label for="p_not" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Şikayet / Not (Opsiyonel)</label>
                                    <textarea id="p_not" name="not" rows="2" placeholder="Şikayetiniz veya belirtmek istediğiniz notlar..." 
                                              class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all resize-none">{{ old('not') }}</textarea>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" 
                                        class="w-full py-3.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                                    Randevu Talebi Oluştur
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Guest Login Prompt Widget -->
                        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-md relative overflow-hidden text-center space-y-4">
                            <div class="w-12 h-12 bg-orange-50 text-[#C96A2B] rounded-2xl flex items-center justify-center mx-auto border border-[#E7B58A]/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path>
                                </svg>
                            </div>
                            <div class="space-y-1.5">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">
                                    Randevu Almak İçin
                                </h3>
                                <p class="text-xs text-[#6B7280] leading-relaxed">
                                    Hekimimizden online randevu alabilmek için hasta hesabınıza giriş yapmalı veya yeni bir hesap oluşturmalısınız.
                                </p>
                            </div>
                            <div class="pt-4 border-t border-slate-100 flex flex-col gap-2">
                                <a href="{{ route('frontend.hasta.giris') }}" class="w-full py-2.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-sm font-display text-center">
                                    Giriş Yap
                                </a>
                                <a href="{{ route('frontend.hasta.kayit') }}" class="w-full py-2.5 border border-[#E5E7EB] hover:bg-slate-50 text-[#1F2937] font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 font-display text-center">
                                    Hesap Oluştur
                                </a>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Online Booking Closed / Contact Info Widget -->
                    <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-md relative overflow-hidden text-center space-y-4">
                        <div class="w-12 h-12 bg-amber-50 text-[#C96A2B] rounded-2xl flex items-center justify-center mx-auto border border-[#E7B58A]/30">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z"></path>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">
                                Randevu Al
                            </h3>
                            <p class="text-xs text-[#6B7280] leading-relaxed">
                                Hekimimiz online randevu alımına geçici olarak kapalıdır. Randevu bilgisi ve detaylar için lütfen iletişime geçiniz.
                            </p>
                        </div>
                        <div class="pt-4 border-t border-slate-100 space-y-2">
                            <p class="text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">İLETİŞİME GEÇ</p>
                            @if($doktor->telefon)
                                <a href="tel:{{ $doktor->telefon }}" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all font-display">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.622l1.5-1.5a2.25 2.25 0 013.182 0l1.287 1.287a2.25 2.25 0 010 3.182l-1.07 1.07a11.94 11.94 0 005.176 5.176l1.07-1.07a2.25 2.25 0 013.182 0l1.287 1.287a2.25 2.25 0 010 3.182l-2.872 2.872a2.25 2.25 0 01-3.182 0C5.172 18.828 2.25 11.895 2.25 6.622z"></path>
                                    </svg>
                                    {{ $doktor->telefon }}
                                </a>
                            @endif
                            <a href="mailto:{{ $doktor->e_posta }}" class="flex items-center justify-center gap-2 px-4 py-2.5 border border-[#E5E7EB] hover:bg-slate-50 text-[#1F2937] font-bold text-xs uppercase tracking-wider rounded-xl transition-all font-display">
                                E-Posta ile İletişim
                            </a>
                        </div>
                    </div>
                @endif
            </div>

        </div>

    </div>
    <script>
    const calismaSaatleri = @json($doktor->calismaSaatleri);
    const periyot = {{ $doktor->randevuAyari ? $doktor->randevuAyari->randevu_periyodu : 30 }};

    function generateTimeSlots() {
        const dateInput = document.getElementById('p_tarih');
        const saatSelect = document.getElementById('p_saat');
        if (!dateInput || !saatSelect) return;

        const selectedDateVal = dateInput.value;
        if (!selectedDateVal) {
            saatSelect.innerHTML = '<option value="" disabled selected>Önce Tarih Seçin...</option>';
            return;
        }

        const selectedDate = new Date(selectedDateVal);
        let dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
        let dbDay = dayOfWeek === 0 ? 7 : dayOfWeek; // 1 = Pazartesi, 7 = Pazar

        const cs = calismaSaatleri.find(item => item.gun === dbDay);
        
        if (!cs || !cs.aktif_mi) {
            saatSelect.innerHTML = '<option value="" disabled selected>Hekim Bu Gün Çalışmamaktadır</option>';
            return;
        }

        // Parse time helper: "09:00:00" -> minutes from midnight
        function timeToMins(timeStr) {
            const parts = timeStr.split(':');
            return parseInt(parts[0]) * 60 + parseInt(parts[1]);
        }

        // Format minutes from midnight to "HH:MM"
        function minsToTime(mins) {
            const h = Math.floor(mins / 60).toString().padStart(2, '0');
            const m = (mins % 60).toString().padStart(2, '0');
            return `${h}:${m}`;
        }

        const startMins = timeToMins(cs.mesai_baslangic);
        const endMins = timeToMins(cs.mesai_bitis);
        const lunchStart = cs.ogle_arasi_aktif_mi && cs.ogle_baslangic ? timeToMins(cs.ogle_baslangic) : null;
        const lunchEnd = cs.ogle_arasi_aktif_mi && cs.ogle_bitis ? timeToMins(cs.ogle_bitis) : null;

        let current = startMins;
        saatSelect.innerHTML = '<option value="" disabled selected>Saat Seçin...</option>';

        while (current < endMins) {
            let slotEnd = current + periyot;
            if (slotEnd > endMins) {
                break;
            }

            // Check if slot falls in lunch break
            let isLunch = false;
            if (lunchStart !== null && lunchEnd !== null) {
                if (current >= lunchStart && current < lunchEnd) {
                    isLunch = true;
                }
            }

            if (!isLunch) {
                const timeStr = minsToTime(current);
                const opt = document.createElement('option');
                opt.value = timeStr;
                opt.textContent = timeStr;
                saatSelect.appendChild(opt);
            }

            current += periyot;
        }

        if (saatSelect.options.length <= 1) {
            saatSelect.innerHTML = '<option value="" disabled selected>Bu Güne Müsait Saat Bulunmuyor</option>';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('p_tarih');
        if (dateInput) {
            dateInput.addEventListener('change', generateTimeSlots);
            generateTimeSlots(); // Trigger on page load
        }
    });
</script>
</section>
@endsection
