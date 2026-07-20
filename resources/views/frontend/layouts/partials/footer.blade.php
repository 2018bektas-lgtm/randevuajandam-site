<footer class="relative bg-white border-t border-[#E5E7EB] mt-auto">
    {{-- Üst CTA şeridi --}}
    <div class="border-b border-[#E5E7EB] bg-gradient-to-r from-[#FFF7ED] via-white to-[#FFF7ED]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5 sm:py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-wider text-[#C96A2B] font-display">Randevu Ajandam</p>
                <h2 class="mt-0.5 text-base sm:text-lg font-extrabold font-display text-[#111827] tracking-tight">
                    Uzmanınızı bulun, randevunuzu kolayca alın.
                </h2>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <a href="{{ route('frontend.hekimler') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-[11px] font-bold uppercase tracking-wider font-display shadow-sm shadow-orange-500/15 transition-colors">
                    Randevu al
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
                <a href="{{ route('frontend.paketler') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:border-[#C96A2B]/40 hover:bg-[#FFF7ED] text-[#4B5563] hover:text-[#C96A2B] text-[11px] font-bold uppercase tracking-wider font-display transition-colors">
                    Hekim / klinik kaydı
                </a>
            </div>
        </div>
    </div>

    {{-- 5 kolon: Marka | Hastalar | Hekim | Branşlar | Yasal --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 md:py-9">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-x-5 gap-y-7 lg:gap-x-6">
            {{-- 1. Marka --}}
            <div class="col-span-2 sm:col-span-3 lg:col-span-1 space-y-3 min-w-0">
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
                <p class="text-[11px] text-[#6B7280] leading-relaxed">
                    Danışanları uzman hekimlerle buluşturan randevu platformu.
                </p>
                <div class="flex flex-col gap-1.5">
                    <a href="https://wa.me/{{ config('company.whatsapp', '905319912427') }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 font-display">
                        <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.257 5.26 0 11.722 0c3.13 0 6.073 1.22 8.286 3.433 2.213 2.213 3.431 5.158 3.43 8.288-.003 6.465-5.26 11.721-11.721 11.721-2.001-.001-3.97-.51-5.733-1.485L0 24zm6.49-4.22c1.657.982 3.267 1.48 4.966 1.481 5.36 0 9.72-4.36 9.723-9.723.001-2.597-1.01-5.038-2.85-6.877-1.839-1.839-4.281-2.85-6.877-2.852-5.364 0-9.723 4.359-9.726 9.723 0 1.762.474 3.415 1.373 4.908L1.96 22.04l4.587-1.26z"/></svg>
                        WhatsApp destek
                    </a>
                    <a href="mailto:{{ config('company.email', 'info@randevuajandam.com') }}"
                       class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#6B7280] hover:text-[#C96A2B] font-display truncate">
                        {{ config('company.email', 'info@randevuajandam.com') }}
                    </a>
                </div>
            </div>

            {{-- 2. Hastalar --}}
            <div class="min-w-0">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-[#111827] font-display mb-3">Hastalar</h3>
                <ul class="space-y-2 text-[12px] text-[#6B7280]">
                    <li><a href="{{ route('frontend.hekimler') }}" class="hover:text-[#C96A2B] transition-colors">Uzman bul</a></li>
                    <li><a href="{{ route('frontend.hasta.kayit') }}" class="hover:text-[#C96A2B] transition-colors">Hasta kaydı</a></li>
                    <li><a href="{{ route('frontend.hasta.giris') }}" class="hover:text-[#C96A2B] transition-colors">Hasta girişi</a></li>
                    <li><a href="{{ route('frontend.hasta.randevular') }}" class="hover:text-[#C96A2B] transition-colors">Randevularım</a></li>
                    <li><a href="{{ route('frontend.blog.index') }}" class="hover:text-[#C96A2B] transition-colors">Blog</a></li>
                </ul>
            </div>

            {{-- 3. Hekim & Klinik --}}
            <div class="min-w-0">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-[#111827] font-display mb-3">Hekim &amp; Klinik</h3>
                <ul class="space-y-2 text-[12px] text-[#6B7280]">
                    <li><a href="{{ route('frontend.paketler') }}" class="hover:text-[#C96A2B] transition-colors">Paketler</a></li>
                    <li><a href="{{ route('frontend.hekim.kayit') }}" class="hover:text-[#C96A2B] transition-colors">Hekim kaydı</a></li>
                    <li><a href="{{ route('frontend.hekim.giris') }}" class="hover:text-[#C96A2B] transition-colors">Hekim girişi</a></li>
                    @auth('doktor')
                        <li><a href="{{ route('hekim.panel') }}" class="hover:text-[#C96A2B] transition-colors">Hekim paneli</a></li>
                    @endauth
                    <li><a href="{{ route('frontend.egitimler.index') }}" class="hover:text-[#C96A2B] transition-colors">Eğitimler</a></li>
                </ul>
            </div>

            {{-- 4. Popüler branşlar (DB slug + uzmanlik filtresi) --}}
            <div class="min-w-0">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-[#111827] font-display mb-3">Popüler branşlar</h3>
                <ul class="space-y-2 text-[12px] text-[#6B7280]">
                    @forelse(($footerBranslar ?? collect()) as $brans)
                        <li>
                            <a href="{{ route('frontend.hekimler', ['uzmanlik' => $brans->ad, 'brans' => $brans->slug]) }}"
                               class="hover:text-[#C96A2B] transition-colors line-clamp-1"
                               title="{{ $brans->ad }}">
                                {{ $brans->ad }}
                            </a>
                        </li>
                    @empty
                        <li>
                            <a href="{{ route('frontend.hekimler') }}" class="hover:text-[#C96A2B] transition-colors">Tüm uzmanlar</a>
                        </li>
                    @endforelse
                    <li>
                        <a href="{{ route('frontend.hekimler') }}" class="hover:text-[#C96A2B] transition-colors font-semibold text-[#C96A2B]/90">
                            Tüm uzmanlar →
                        </a>
                    </li>
                </ul>
            </div>

            {{-- 5. Yasal --}}
            <div class="min-w-0">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-[#111827] font-display mb-3">Yasal</h3>
                <ul class="space-y-2 text-[12px] text-[#6B7280]">
                    <li><a href="{{ route('frontend.legal.hakkimizda') }}" class="hover:text-[#C96A2B] transition-colors">Hakkımızda</a></li>
                    <li><a href="{{ route('frontend.legal.iletisim') }}" class="hover:text-[#C96A2B] transition-colors">İletişim</a></li>
                    <li><a href="{{ route('frontend.legal.kullanim') }}" class="hover:text-[#C96A2B] transition-colors">Kullanım koşulları</a></li>
                    <li><a href="{{ route('frontend.legal.gizlilik') }}" class="hover:text-[#C96A2B] transition-colors">Gizlilik</a></li>
                    <li><a href="{{ route('frontend.legal.kvkk') }}" class="hover:text-[#C96A2B] transition-colors">KVKK</a></li>
                    <li><a href="{{ route('frontend.legal.mesafeli') }}" class="hover:text-[#C96A2B] transition-colors">Mesafeli satış</a></li>
                    <li><a href="{{ route('frontend.legal.iade') }}" class="hover:text-[#C96A2B] transition-colors">İade &amp; iptal</a></li>
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

        {{-- iyzico ödeme logoları --}}
        <div class="mt-8 pt-6 border-t border-[#E5E7EB]">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                @include('frontend.layouts.partials.payment-methods')
                <div class="text-[11px] text-[#9CA3AF] md:text-right leading-relaxed shrink-0">
                    <p class="font-semibold text-[#6B7280]">İletişim</p>
                    <p>
                        <a href="mailto:{{ config('company.email', 'info@randevuajandam.com') }}" class="hover:text-[#C96A2B]">
                            {{ config('company.email', 'info@randevuajandam.com') }}
                        </a>
                    </p>
                    <p>
                        <a href="https://wa.me/{{ config('company.whatsapp', '905319912427') }}" class="hover:text-[#C96A2B]" target="_blank" rel="noopener">
                            {{ config('company.telefon', '+90 531 991 24 27') }}
                        </a>
                    </p>
                </div>
            </div>
        </div>

        {{-- Alt bar --}}
        <div class="mt-6 pt-5 border-t border-[#E5E7EB] flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-[#9CA3AF]">
            <p class="text-center sm:text-left">
                © {{ date('Y') }} <span class="font-semibold text-[#6B7280]">Randevu Ajandam</span>. Tüm hakları saklıdır.
            </p>
            <p class="text-center sm:text-right">
                Bu web sitesi
                <a href="https://lunzasoft.com" target="_blank" rel="noopener noreferrer"
                   class="font-semibold text-[#6B7280] hover:text-[#C96A2B] underline-offset-2 hover:underline">
                    LunzaSoft
                </a>
                tarafından geliştirilmiştir.
            </p>
        </div>
    </div>
</footer>
