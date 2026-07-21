<footer class="relative bg-white border-t border-[#E5E7EB] mt-auto">
    {{-- Üst CTA --}}
    <div class="border-b border-[#E5E7EB] bg-gradient-to-r from-[#FFF7ED] via-white to-[#FFF7ED]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-wider text-[#C96A2B] font-display">Randevu Ajandam</p>
                <p class="mt-0.5 text-sm sm:text-base font-extrabold font-display text-[#111827] tracking-tight">
                    Uzmanınızı bulun, randevunuzu kolayca alın.
                </p>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <a href="{{ route('frontend.hekimler') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-[11px] font-bold uppercase tracking-wider font-display shadow-sm shadow-orange-500/15 transition-colors">
                    Randevu al
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
                <a href="{{ route('frontend.paketler') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:border-[#C96A2B]/40 hover:bg-[#FFF7ED] text-[#4B5563] hover:text-[#C96A2B] text-[11px] font-bold uppercase tracking-wider font-display transition-colors">
                    Hekim / klinik kaydı
                </a>
            </div>
        </div>
    </div>

    {{-- Ana grid: Marka | Hastalar | Hekim | Branşlar | Yasal --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-x-6 gap-y-8">
            {{-- 1. Marka + iletişim (tek yer) --}}
            <div class="col-span-2 md:col-span-3 lg:col-span-1 space-y-3">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 relative group w-fit">
                    <div class="logo-ambient-glow"></div>
                    <div class="relative w-9 h-9 select-none flex-shrink-0 logo-breathing-small-animate">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Randevu Ajandam"
                             class="w-full h-full object-contain relative z-10"
                             style="mix-blend-mode: multiply;"
                             width="36" height="36">
                        <div class="shimmer-overlay-small z-20"></div>
                    </div>
                    <div class="z-10 flex flex-col justify-center select-none">
                        <span class="block font-extrabold text-sm leading-none font-display brand-text-shimmer">Randevu</span>
                        <span class="block font-bold text-xs mt-0.5 font-display brand-text-shimmer">Ajandam</span>
                    </div>
                </a>
                <p class="text-[11px] text-[#6B7280] leading-relaxed max-w-[220px]">
                    Danışanları uzman hekimlerle buluşturan randevu platformu.
                </p>
                <ul class="space-y-1.5 text-[11px] text-[#6B7280]">
                    <li>
                        <a href="{{ route('frontend.legal.hakkimizda') }}" class="hover:text-[#C96A2B] transition-colors">Hakkımızda</a>
                    </li>
                    <li>
                        <a href="{{ route('frontend.legal.iletisim') }}" class="hover:text-[#C96A2B] transition-colors">İletişim</a>
                    </li>
                    <li>
                        <a href="mailto:{{ config('company.email', 'info@randevuajandam.com') }}"
                           data-meta-event="Contact"
                           data-meta-params='{"content_name":"Footer e-posta"}'
                           class="hover:text-[#C96A2B] transition-colors break-all">
                            {{ config('company.email', 'info@randevuajandam.com') }}
                        </a>
                    </li>
                    <li>
                        <a href="https://wa.me/{{ config('company.whatsapp', '905319912427') }}"
                           target="_blank" rel="noopener noreferrer"
                           data-meta-event="Contact"
                           data-meta-params='{"content_name":"Footer WhatsApp"}'
                           class="inline-flex items-center gap-1 font-semibold text-emerald-700 hover:text-emerald-800">
                            <svg class="w-3 h-3 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.257 5.26 0 11.722 0c3.13 0 6.073 1.22 8.286 3.433 2.213 2.213 3.431 5.158 3.43 8.288-.003 6.465-5.26 11.721-11.721 11.721-2.001-.001-3.97-.51-5.733-1.485L0 24zm6.49-4.22c1.657.982 3.267 1.48 4.966 1.481 5.36 0 9.72-4.36 9.723-9.723.001-2.597-1.01-5.038-2.85-6.877-1.839-1.839-4.281-2.85-6.877-2.852-5.364 0-9.723 4.359-9.726 9.723 0 1.762.474 3.415 1.373 4.908L1.96 22.04l4.587-1.26z"/></svg>
                            {{ config('company.telefon', '+90 531 991 24 27') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- 2. Hastalar --}}
            <nav class="min-w-0" aria-label="Hastalar">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-[#111827] font-display mb-3">Hastalar</h3>
                <ul class="space-y-2 text-[12px] text-[#6B7280]">
                    <li><a href="{{ route('frontend.hekimler') }}" class="hover:text-[#C96A2B] transition-colors">Uzman bul</a></li>
                    <li><a href="{{ route('frontend.hasta.kayit') }}" class="hover:text-[#C96A2B] transition-colors">Hasta kaydı</a></li>
                    <li><a href="{{ route('frontend.hasta.giris') }}" class="hover:text-[#C96A2B] transition-colors">Hasta girişi</a></li>
                    <li><a href="{{ route('frontend.hasta.randevular') }}" class="hover:text-[#C96A2B] transition-colors">Randevularım</a></li>
                    <li><a href="{{ route('frontend.blog.index') }}" class="hover:text-[#C96A2B] transition-colors">Blog</a></li>
                </ul>
            </nav>

            {{-- 3. Hekim & Klinik --}}
            <nav class="min-w-0" aria-label="Hekim ve klinik">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-[#111827] font-display mb-3">Hekim &amp; Klinik</h3>
                <ul class="space-y-2 text-[12px] text-[#6B7280]">
                    <li><a href="{{ route('frontend.paketler') }}" class="hover:text-[#C96A2B] transition-colors">Paketler</a></li>
                    <li><a href="{{ route('frontend.paketler') }}" class="hover:text-[#C96A2B] transition-colors">Hekim kaydı / paketler</a></li>
                    <li><a href="{{ route('frontend.hekim.giris') }}" class="hover:text-[#C96A2B] transition-colors">Hekim girişi</a></li>
                    <li><a href="{{ route('frontend.egitimler.index') }}" class="hover:text-[#C96A2B] transition-colors">Eğitimler</a></li>
                    @auth('doktor')
                        <li><a href="{{ route('hekim.panel') }}" class="hover:text-[#C96A2B] transition-colors">Hekim paneli</a></li>
                    @endauth
                </ul>
            </nav>

            {{-- 4. Popüler branşlar --}}
            <nav class="min-w-0" aria-label="Popüler branşlar">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-[#111827] font-display mb-3">Popüler branşlar</h3>
                <ul class="space-y-2 text-[12px] text-[#6B7280]">
                    @forelse(($footerBranslar ?? collect())->take(4) as $brans)
                        <li>
                            <a href="{{ route('frontend.hekimler', ['uzmanlik' => $brans->ad, 'brans' => $brans->slug]) }}"
                               class="hover:text-[#C96A2B] transition-colors break-words"
                               title="{{ $brans->ad }}">
                                {{ $brans->ad }}
                            </a>
                        </li>
                    @empty
                    @endforelse
                    <li>
                        <a href="{{ route('frontend.hekimler') }}"
                           class="font-semibold text-[#C96A2B] hover:text-[#B55A20] transition-colors">
                            Tüm uzmanlar →
                        </a>
                    </li>
                </ul>
            </nav>

            {{-- 5. Yasal (yalnızca hukuki sayfalar) --}}
            <nav class="min-w-0" aria-label="Yasal">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-[#111827] font-display mb-3">Yasal</h3>
                <ul class="space-y-2 text-[12px] text-[#6B7280]">
                    <li><a href="{{ route('frontend.legal.kullanim') }}" class="hover:text-[#C96A2B] transition-colors">Kullanım koşulları</a></li>
                    <li><a href="{{ route('frontend.legal.gizlilik') }}" class="hover:text-[#C96A2B] transition-colors">Gizlilik politikası</a></li>
                    <li><a href="{{ route('frontend.legal.kvkk') }}" class="hover:text-[#C96A2B] transition-colors">KVKK aydınlatma</a></li>
                    <li><a href="{{ route('frontend.legal.mesafeli') }}" class="hover:text-[#C96A2B] transition-colors">Mesafeli satış</a></li>
                    <li><a href="{{ route('frontend.legal.iade') }}" class="hover:text-[#C96A2B] transition-colors">İade &amp; iptal</a></li>
                </ul>
            </nav>
        </div>

        {{-- Ödeme + alt bar --}}
        <div class="mt-8 pt-6 border-t border-[#E5E7EB]">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">
                <div class="min-w-0 flex-1">
                    @include('frontend.layouts.partials.payment-methods')
                </div>
                <div class="text-[11px] text-[#9CA3AF] lg:text-right leading-relaxed shrink-0 space-y-1">
                    <p>
                        © {{ date('Y') }} <span class="font-semibold text-[#6B7280]">Randevu Ajandam</span>.
                        Tüm hakları saklıdır.
                    </p>
                    <p>
                        Bu web sitesi
                        <a href="https://lunzasoft.com" target="_blank" rel="noopener noreferrer"
                           class="font-semibold text-[#6B7280] hover:text-[#C96A2B] underline-offset-2 hover:underline">
                            LunzaSoft
                        </a>
                        tarafından geliştirilmiştir.
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>
