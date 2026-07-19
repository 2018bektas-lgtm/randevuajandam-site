<footer class="relative bg-white border-t border-[#E5E7EB] mt-auto">
    {{-- Üst CTA şeridi --}}
    <div class="border-b border-[#E5E7EB] bg-gradient-to-r from-[#FFF7ED] via-white to-[#FFF7ED]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-10 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div class="max-w-xl">
                <p class="text-[10px] font-bold uppercase tracking-wider text-[#C96A2B] font-display">Randevu Ajandam</p>
                <h2 class="mt-1 text-lg sm:text-xl font-extrabold font-display text-[#111827] tracking-tight">
                    Uzmanınızı bulun, randevunuzu kolayca alın.
                </h2>
                <p class="mt-1.5 text-xs sm:text-sm text-[#6B7280] leading-relaxed">
                    Hastalar için online randevu; hekim ve klinikler için modern ajanda yönetimi.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2.5 shrink-0">
                <a href="{{ route('frontend.hekimler') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold uppercase tracking-wider font-display shadow-sm shadow-orange-500/15 transition-colors">
                    Randevu al
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
                <a href="{{ route('frontend.paketler') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-[#E5E7EB] bg-white hover:border-[#C96A2B]/40 hover:bg-[#FFF7ED] text-[#4B5563] hover:text-[#C96A2B] text-xs font-bold uppercase tracking-wider font-display transition-colors">
                    Hekim / klinik kaydı
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 md:py-12">
        <div class="grid grid-cols-2 md:grid-cols-12 gap-8 md:gap-6">
            {{-- Marka --}}
            <div class="col-span-2 md:col-span-4 space-y-4">
                <a href="/" class="inline-flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-[#FFF7ED] border border-[#E7B58A]/30 p-1.5 shrink-0">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Randevu Ajandam"
                             class="w-full h-full object-contain" style="mix-blend-mode: multiply;" loading="lazy" width="40" height="40">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-extrabold text-[#111827] text-base leading-none font-display">Randevu</span>
                        <span class="font-bold text-[13px] text-[#C96A2B] mt-0.5 font-display">Ajandam</span>
                    </div>
                </a>
                <p class="text-xs text-[#6B7280] leading-relaxed max-w-xs">
                    Danışanları uzman hekimlerle buluşturan, randevu ve ajanda süreçlerini sadeleştiren dijital platform.
                </p>
                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <a href="https://wa.me/905319912427" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 font-display">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.257 5.26 0 11.722 0c3.13 0 6.073 1.22 8.286 3.433 2.213 2.213 3.431 5.158 3.43 8.288-.003 6.465-5.26 11.721-11.721 11.721-2.001-.001-3.97-.51-5.733-1.485L0 24zm6.49-4.22c1.657.982 3.267 1.48 4.966 1.481 5.36 0 9.72-4.36 9.723-9.723.001-2.597-1.01-5.038-2.85-6.877-1.839-1.839-4.281-2.85-6.877-2.852-5.364 0-9.723 4.359-9.726 9.723 0 1.762.474 3.415 1.373 4.908L1.96 22.04l4.587-1.26z"/></svg>
                        WhatsApp destek
                    </a>
                    <span class="text-slate-200">|</span>
                    <a href="mailto:info@randevuajandam.com"
                       class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#6B7280] hover:text-[#C96A2B] font-display">
                        info@randevuajandam.com
                    </a>
                </div>
            </div>

            {{-- Hastalar --}}
            <div class="col-span-1 md:col-span-2">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-[#111827] font-display mb-3.5">Hastalar</h3>
                <ul class="space-y-2.5 text-xs text-[#6B7280]">
                    <li><a href="{{ route('frontend.hekimler') }}" class="hover:text-[#C96A2B] transition-colors">Uzman bul</a></li>
                    <li><a href="{{ route('frontend.hasta.kayit') }}" class="hover:text-[#C96A2B] transition-colors">Hasta kaydı</a></li>
                    <li><a href="{{ route('frontend.hasta.giris') }}" class="hover:text-[#C96A2B] transition-colors">Hasta girişi</a></li>
                    <li><a href="{{ route('frontend.hasta.randevular') }}" class="hover:text-[#C96A2B] transition-colors">Randevularım</a></li>
                    <li><a href="{{ route('frontend.blog.index') }}" class="hover:text-[#C96A2B] transition-colors">Blog</a></li>
                </ul>
            </div>

            {{-- Hekimler --}}
            <div class="col-span-1 md:col-span-2">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-[#111827] font-display mb-3.5">Hekim &amp; Klinik</h3>
                <ul class="space-y-2.5 text-xs text-[#6B7280]">
                    <li><a href="{{ route('frontend.paketler') }}" class="hover:text-[#C96A2B] transition-colors">Paketler</a></li>
                    <li><a href="{{ route('frontend.hekim.kayit') }}" class="hover:text-[#C96A2B] transition-colors">Hekim kaydı</a></li>
                    <li><a href="{{ route('frontend.hekim.giris') }}" class="hover:text-[#C96A2B] transition-colors">Hekim girişi</a></li>
                    @auth('doktor')
                        <li><a href="{{ route('hekim.panel') }}" class="hover:text-[#C96A2B] transition-colors">Hekim paneli</a></li>
                    @endauth
                    <li><a href="/#doktorlar" class="hover:text-[#C96A2B] transition-colors">Öne çıkan uzmanlar</a></li>
                </ul>
            </div>

            {{-- Popüler branşlar --}}
            <div class="col-span-1 md:col-span-2">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-[#111827] font-display mb-3.5">Popüler branşlar</h3>
                <ul class="space-y-2.5 text-xs text-[#6B7280]">
                    <li><a href="{{ route('frontend.hekimler', ['brans' => 'psikolog']) }}" class="hover:text-[#C96A2B] transition-colors">Psikolog</a></li>
                    <li><a href="{{ route('frontend.hekimler', ['brans' => 'diyetisyen']) }}" class="hover:text-[#C96A2B] transition-colors">Diyetisyen</a></li>
                    <li><a href="{{ route('frontend.hekimler', ['brans' => 'dis-hekimi']) }}" class="hover:text-[#C96A2B] transition-colors">Diş hekimi</a></li>
                    <li><a href="{{ route('frontend.hekimler', ['brans' => 'kadin-dogum']) }}" class="hover:text-[#C96A2B] transition-colors">Kadın doğum</a></li>
                    <li><a href="{{ route('frontend.hekimler') }}" class="hover:text-[#C96A2B] transition-colors font-semibold text-[#C96A2B]/90">Tüm uzmanlar →</a></li>
                </ul>
            </div>

            {{-- Yasal --}}
            <div class="col-span-1 md:col-span-2">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-[#111827] font-display mb-3.5">Yasal</h3>
                <ul class="space-y-2.5 text-xs text-[#6B7280]">
                    <li><a href="{{ route('frontend.legal.kullanim') }}" class="hover:text-[#C96A2B] transition-colors">Kullanım koşulları</a></li>
                    <li><a href="{{ route('frontend.legal.gizlilik') }}" class="hover:text-[#C96A2B] transition-colors">Gizlilik politikası</a></li>
                    <li><a href="{{ route('frontend.legal.kvkk') }}" class="hover:text-[#C96A2B] transition-colors">KVKK aydınlatma</a></li>
                    <li>
                        <a href="https://instagram.com/randevuajandam" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 hover:text-[#C96A2B] transition-colors">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                            Instagram
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Alt bar --}}
        <div class="mt-10 pt-6 border-t border-[#E5E7EB] flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-[#9CA3AF]">
            <p class="text-center sm:text-left">
                © {{ date('Y') }} <span class="font-semibold text-[#6B7280]">Randevu Ajandam</span>. Tüm hakları saklıdır.
            </p>
            <p class="text-center sm:text-right font-medium">
                Online randevu &amp; hekim ajanda platformu
            </p>
        </div>
    </div>
</footer>
