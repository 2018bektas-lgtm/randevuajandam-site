@if(request('sadece_klinik'))
    <!-- Clinic Section -->
    @if(isset($klinikler) && $klinikler->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($klinikler as $klinik)
                <div class="bg-white border border-[#EAEAEA] rounded-[24px] p-5 hover:shadow-[0_15px_40px_rgba(0,0,0,0.03)] hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between group relative overflow-hidden">
                    <!-- Ambient Glow inside Card -->
                    <div class="doctor-glow" style="left: 20%; top: 50%; transform: translate(-50%, -50%) scale(0.8);"></div>

                    <div class="flex items-start gap-4 relative z-10">
                        @if($klinik->logo)
                            <img src="{{ asset($klinik->logo) }}" alt="{{ $klinik->ad }}" class="w-16 h-16 rounded-2xl object-cover border border-[#E5E7EB] shrink-0">
                        @else
                            <div class="w-16 h-16 rounded-2xl bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-xl font-bold font-display shrink-0">
                                {{ mb_strtoupper(mb_substr($klinik->ad, 0, 2)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <span class="px-2 py-0.5 text-[8px] uppercase font-bold tracking-wider rounded bg-[#C96A2B]/10 text-[#C96A2B] border border-[#C96A2B]/20">
                                Klinik
                            </span>
                            <h4 class="font-bold text-[#111827] text-sm font-display mt-1.5 leading-snug group-hover:text-[#C96A2B] transition-colors truncate">
                                <a href="{{ route('frontend.klinik.profil', ['il_slug' => $klinik->il->slug ?? 'il', 'ilce_slug' => $klinik->ilce->slug ?? 'ilce', 'klinik_slug' => $klinik->slug]) }}">
                                    {{ $klinik->ad }}
                                </a>
                            </h4>
                            <p class="text-[10px] text-[#6B7280] font-medium font-display flex items-center gap-1 mt-1">
                                📍 {{ $klinik->il?->ad }}{{ $klinik->ilce?->ad ? ', ' . $klinik->ilce->ad : '' }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between relative z-10">
                        <span class="text-[10px] font-bold text-[#C96A2B] bg-[#FFF7ED] px-2.5 py-1 rounded-full">
                            {{ $klinik->doktorlar->count() }} Uzman Hekim
                        </span>
                        <a href="{{ route('frontend.klinik.profil', ['il_slug' => $klinik->il->slug ?? 'il', 'ilce_slug' => $klinik->ilce->slug ?? 'ilce', 'klinik_slug' => $klinik->slug]) }}" class="text-xs font-bold text-[#C96A2B] hover:underline flex items-center gap-1">
                            İncele →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State for Clinics -->
        <div class="max-w-md mx-auto text-center py-16 space-y-4 bg-white border border-[#E5E7EB] rounded-3xl p-8 shadow-sm">
            <div class="w-16 h-16 rounded-full bg-slate-50 border border-slate-100 text-[#9CA3AF] flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold font-display text-[#111827]">Kriterlere Uygun Klinik Bulunamadı</h3>
                <p class="text-xs text-[#6B7280] leading-relaxed">
                    Aradığınız kriterlere uygun kayıtlı klinik bulunmuyor. Farklı filtreler veya arama terimleri kullanmayı deneyebilirsiniz.
                </p>
            </div>
            <a href="{{ route('frontend.hekimler') }}" class="inline-block mt-2 px-4 py-2 bg-[#C96A2B] text-white font-semibold text-xs rounded-xl hover:bg-[#b05a20] transition-all font-display">
                Filtreleri Sıfırla
            </a>
        </div>
    @endif
@else
    <!-- Doctor Section -->
    @if($doktorlar->count() > 0)
        <div id="doctorListContainer" class="doctor-list-container layout-grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            @foreach($doktorlar as $doktor)
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
                <!-- Premium Doctor Card -->
                <div class="doctor-card bg-white border border-[#EAEAEA] rounded-[24px] p-5 hover:shadow-[0_15px_40px_rgba(0,0,0,0.03)] hover:-translate-y-0.5 transition-all duration-300 flex flex-col sm:flex-row gap-5 group relative overflow-hidden">
                    <!-- Ambient Glow inside Card -->
                    <div class="doctor-glow" style="left: 20%; top: 50%; transform: translate(-50%, -50%) scale(0.8);"></div>

                    <!-- Left Side / Top Side: Portrait Image -->
                    <div class="doctor-portrait relative w-32 h-44 rounded-2xl overflow-hidden bg-gradient-to-br from-[#FFF7ED] to-[#FFFBEB] border border-slate-100 group-hover:border-[#E7B58A]/35 shadow-inner shrink-0 mx-auto sm:mx-0">
                        @if($doktor->profil_resmi)
                            <img src="{{ asset($doktor->profil_resmi) }}" alt="{{ $doktor->ad_soyad }}" 
                                 class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500 ease-out">
                        @else
                            <div class="w-full h-full flex items-center justify-center font-extrabold font-display text-3xl text-[#C96A2B] select-none opacity-80 group-hover:scale-105 transition-transform duration-500 ease-out">
                                {{ $kisaAd }}
                            </div>
                        @endif
                        
                        <!-- Premium Shimmer overlay -->
                        <div class="shimmer-sweep"></div>
                        
                        <!-- Top Badges -->
                        <div class="absolute top-2.5 left-2.5 right-2.5 flex justify-between items-center pointer-events-none">
                            <span class="px-2 py-0.5 text-[8px] uppercase font-bold tracking-wider rounded bg-[#111827]/75 backdrop-blur-md text-white border border-white/5">
                                {{ $doktor->unvan ?? 'Dr.' }}
                            </span>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 border border-white animate-pulse" title="Online Randevuya Açık"></span>
                        </div>
                    </div>

                    <!-- Right Side: Details & Biography -->
                    <div class="doctor-details relative z-10 flex flex-col justify-between flex-grow text-left">
                        <div class="space-y-2">
                            <div>
                                <span class="text-[9px] font-bold text-[#C96A2B] font-display uppercase tracking-widest block">{{ $doktor->uzmanlik_alani ?? 'Uzman Hekim' }}</span>
                                <h3 class="text-base font-bold font-display text-[#111827] hover:text-[#C96A2B] transition-colors leading-tight">
                                    <a href="{{ $doktor->profil_url }}">
                                        {{ $doktor->ad_soyad }}
                                    </a>
                                </h3>
                            </div>

                            <!-- Location -->
                            @if($doktor->il_id)
                                <div class="info-row flex items-center gap-1.5 text-xs text-[#6B7280] font-medium font-display w-full mt-1">
                                    <svg class="w-3.5 h-3.5 text-[#C96A2B] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 1 1-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="truncate text-[11px]">{{ $doktor->il?->ad }}{{ $doktor->ilce?->ad ? ', ' . $doktor->ilce->ad : '' }}</span>
                                </div>
                            @endif

                            {{-- En yakın müsait randevu --}}
                            @if(!empty($doktor->en_yakin_randevu['label']))
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-[#FFF7ED] border border-[#E7B58A]/35 text-[10px] font-bold text-[#C96A2B] font-display">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                                        </svg>
                                        En yakın: {{ $doktor->en_yakin_randevu['label'] }}
                                    </span>
                                </div>
                            @elseif($doktor->randevuya_acik_mi)
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-50 border border-slate-100 text-[10px] font-semibold text-slate-400 font-display">
                                        Yakın müsait slot yok
                                    </span>
                                </div>
                            @endif

                            <!-- Short biography preview (Max 2 lines) -->
                            @if($doktor->biyografi)
                                <p class="text-[11px] text-[#6B7280] leading-relaxed line-clamp-2 pt-1 border-t border-slate-50">
                                    {{ strip_tags($doktor->biyografi) }}
                                </p>
                            @endif
                        </div>

                        <!-- Footer CTA -->
                        <div class="mt-4 pt-3 border-t border-slate-100 w-full">
                            <a href="{{ $doktor->profil_url }}"
                               class="w-full py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-[#C96A2B] hover:border-[#C96A2B] hover:text-white text-[#4B5563] hover:shadow-sm font-bold text-xs uppercase tracking-wider transition-all duration-200 font-display text-center select-none block">
                                Profili İncele & Randevu Al
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination Links -->
        <div class="mt-12 flex justify-center">
            {{ $doktorlar->links() }}
        </div>
    @else
        <!-- Empty State for Doctors -->
        <div class="max-w-md mx-auto text-center py-16 space-y-4 bg-white border border-[#E5E7EB] rounded-3xl p-8 shadow-sm">
            <div class="w-16 h-16 rounded-full bg-slate-50 border border-slate-100 text-[#9CA3AF] flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold font-display text-[#111827]">Kriterlere Uygun Hekim Bulunamadı</h3>
                <p class="text-xs text-[#6B7280] leading-relaxed">
                    Aradığınız kriterlere uygun kayıtlı hekim bulunmuyor. Farklı filtreler veya arama terimleri kullanmayı deneyebilirsiniz.
                </p>
            </div>
            <a href="{{ route('frontend.hekimler') }}" class="inline-block mt-2 px-4 py-2 bg-[#C96A2B] text-white font-semibold text-xs rounded-xl hover:bg-[#b05a20] transition-all font-display">
                Filtreleri Sıfırla
            </a>
        </div>
    @endif
@endif
