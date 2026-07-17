<footer class="bg-[#1F2937] text-slate-300 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-6 py-12 md:py-16">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
            <!-- Col 1: Brand -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 relative overflow-hidden group">
                    <div class="logo-ambient-glow" style="left:-15px; top:-15px;"></div>
                    
                    <!-- Animated R Logo for Footer -->
                    <div class="relative w-10 h-10 select-none bg-white rounded-xl p-1 flex-shrink-0 logo-breathing-small-animate">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Randevu Ajandam" 
                             class="w-full h-full object-contain relative z-10">
                        <div class="shimmer-overlay-small z-20"></div>
                    </div>
                    
                    <div class="z-10 flex flex-col justify-center select-none brand-text-shimmer-light">
                        <span class="block font-extrabold text-white text-base leading-none font-display">Randevu</span>
                        <span class="block font-bold text-[13px] mt-0.5 font-display">Ajandam</span>
                    </div>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Randevu Ajandam, uzmanlar ile danışanları en hızlı ve prestijli şekilde buluşturan, ajanda ve randevu süreçlerini mükemmelleştiren modern bir platformdur.
                </p>
            </div>

            <!-- Col 2: Hizmetler -->
            <div>
                <h4 class="text-white font-bold font-display text-sm tracking-wider uppercase mb-4">Popüler Uzmanlıklar</h4>
                <ul class="space-y-2.5 text-xs text-slate-400">
                    <li><a href="#" class="hover:text-[#E7B58A] transition-colors">Psikoloji & Terapi</a></li>
                    <li><a href="#" class="hover:text-[#E7B58A] transition-colors">Diyetisyen & Beslenme</a></li>
                    <li><a href="#" class="hover:text-[#E7B58A] transition-colors">Kişisel Gelişim</a></li>
                    <li><a href="#" class="hover:text-[#E7B58A] transition-colors">Çocuk Gelişimi</a></li>
                </ul>
            </div>

            <!-- Col 3: Hızlı Menü -->
            <div>
                <h4 class="text-white font-bold font-display text-sm tracking-wider uppercase mb-4">Hızlı Menü</h4>
                <ul class="space-y-2.5 text-xs text-slate-400">
                    <li><a href="{{ route('frontend.paketler') }}" class="hover:text-[#E7B58A] transition-colors">Paketler</a></li>
                    <li><a href="{{ route('frontend.hekimler') }}" class="hover:text-[#E7B58A] transition-colors">Hekimler</a></li>
                    <li><a href="{{ route('frontend.legal.kullanim') }}" class="hover:text-[#E7B58A] transition-colors">Kullanım Koşulları</a></li>
                    <li><a href="{{ route('frontend.legal.gizlilik') }}" class="hover:text-[#E7B58A] transition-colors">Gizlilik Politikası</a></li>
                    <li><a href="{{ route('frontend.legal.kvkk') }}" class="hover:text-[#E7B58A] transition-colors">KVKK</a></li>
                </ul>
            </div>

            <!-- Col 4: Platform Durumu -->
            <div>
                <h4 class="text-white font-bold font-display text-sm tracking-wider uppercase mb-4">Platform Durumu</h4>
                <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-700/50 space-y-3">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-emerald-400 font-semibold">Tüm Servisler Çevrimiçi</span>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-normal">
                        Sistemimiz 7/24 kesintisiz aktif randevu alımına açıktır.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <span>© 2026 Randevu Ajandam. Tüm Hakları Saklıdır.</span>
            <div class="flex items-center gap-6">
                <span class="font-semibold text-slate-400">Premium Sağlık & Randevu Çözümleri</span>
            </div>
        </div>
    </div>
</footer>
