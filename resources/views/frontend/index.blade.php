@extends('frontend.layouts.app')

@section('baslik', 'Randevu Ajandam - Uzman Doktor ve Randevu Platformu')

@section('icerik')
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
        .animate-float-delayed {
            animation: float 4s ease-in-out infinite;
            animation-delay: 2s;
        }
        .animate-float-slow {
            animation: float-slow 6s ease-in-out infinite;
        }
        .animate-float-slow-delayed {
            animation: float-slow 6s ease-in-out infinite;
            animation-delay: 3s;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white border-b border-[#E5E7EB] pt-12 pb-16 md:pt-24 md:pb-24 overflow-hidden select-none">
        <!-- Background Ambient Lights -->
        <div class="absolute top-[-30%] right-[-10%] w-[550px] h-[550px] rounded-full bg-[#E7B58A]/10 blur-[130px] pointer-events-none"></div>
        <div class="absolute bottom-[-20%] left-[-10%] w-[550px] h-[550px] rounded-full bg-[#C96A2B]/4 blur-[130px] pointer-events-none"></div>

        <!-- Floating Stats in 4 Corners -->
        @if(isset($istatistikler))
        <div class="absolute inset-0 pointer-events-none hidden lg:block z-20" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%;">
            <!-- Sol Üst (Top-Left): Aktif Uzman -->
            <div class="absolute bg-white/90 backdrop-blur-md border border-[#E7B58A]/30 p-4 rounded-2xl shadow-lg shadow-slate-200/50 flex items-center gap-3 animate-float-slow pointer-events-auto transition-all duration-300 hover:scale-105" style="position: absolute; top: 2rem; left: 1.5rem;">
                <div class="w-10 h-10 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center font-bold flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-base font-extrabold font-display text-[#C96A2B] leading-none">{{ number_format($istatistikler['doktor_sayisi']) }}+</div>
                    <div class="text-[9px] font-bold text-[#6B7280] uppercase tracking-wider mt-1.5 leading-none">Aktif Uzman</div>
                </div>
            </div>

            <!-- Sol Alt (Bottom-Left): Hasta Yorumu -->
            <div class="absolute bg-white/90 backdrop-blur-md border border-[#E5E7EB] p-4 rounded-2xl shadow-lg shadow-slate-200/50 flex items-center gap-3 animate-float-delayed pointer-events-auto transition-all duration-300 hover:scale-105" style="position: absolute; bottom: 2rem; left: 1.5rem;">
                <div class="w-10 h-10 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center font-bold flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-base font-extrabold font-display text-[#C96A2B] leading-none">{{ number_format($istatistikler['yorum_sayisi']) }}+</div>
                    <div class="text-[9px] font-bold text-[#6B7280] uppercase tracking-wider mt-1.5 leading-none">Hasta Yorumu</div>
                </div>
            </div>

            <!-- Sağ Üst (Top-Right): Tamamlanan Randevu -->
            <div class="absolute bg-white/90 backdrop-blur-md border border-[#E5E7EB] p-4 rounded-2xl shadow-lg shadow-slate-200/50 flex items-center gap-3 animate-float pointer-events-auto transition-all duration-300 hover:scale-105" style="position: absolute; top: 2rem; right: 1.5rem;">
                <div class="w-10 h-10 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center font-bold flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-base font-extrabold font-display text-[#C96A2B] leading-none">{{ number_format($istatistikler['randevu_sayisi']) }}+</div>
                    <div class="text-[9px] font-bold text-[#6B7280] uppercase tracking-wider mt-1.5 leading-none">Tamamlanan Randevu</div>
                </div>
            </div>

            <!-- Sağ Alt (Bottom-Right): Uzmanlık Alanı -->
            <div class="absolute bg-white/90 backdrop-blur-md border border-[#E7B58A]/30 p-4 rounded-2xl shadow-lg shadow-slate-200/50 flex items-center gap-3 animate-float-slow-delayed pointer-events-auto transition-all duration-300 hover:scale-105" style="position: absolute; bottom: 2rem; right: 1.5rem;">
                <div class="w-10 h-10 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center font-bold flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-base font-extrabold font-display text-[#C96A2B] leading-none">{{ $istatistikler['brans_sayisi'] }}</div>
                    <div class="text-[9px] font-bold text-[#6B7280] uppercase tracking-wider mt-1.5 leading-none">Uzmanlık Alanı</div>
                </div>
            </div>
        </div>
        @endif

        <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 rounded-full text-xs font-bold font-display uppercase tracking-wider mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#C96A2B] animate-pulse"></span>
                    Türkiye'nin Seçkin Uzman Ağı
                </span>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold font-display text-[#111827] tracking-tight leading-tight md:leading-none">
                    Aradığınız Uzmanı Bulun, <br class="hidden md:inline">
                    <span class="text-[#C96A2B]">Kolayca Randevu</span> Alın.
                </h1>

                <p class="text-base text-[#6B7280] max-w-xl mx-auto mt-5 leading-relaxed">
                    @if(isset($branslar) && $branslar->count() > 0)
                        {{ $branslar->take(4)->pluck('ad')->implode(', ') }} ve daha birçok alanda yüzlerce profesyonel arasından size en uygun olanını seçin.
                    @else
                        Psikologlardan diyetisyenlere, çocuk gelişimcilerinden fizyoterapistlere kadar yüzlerce profesyonel arasından size en uygun olanını seçin.
                    @endif
                </p>

                <!-- Search Area -->
                <form action="{{ route('frontend.hekimler') }}" method="GET" class="max-w-2xl mx-auto mt-10 p-2 bg-white rounded-2xl border border-[#E5E7EB] shadow-lg shadow-slate-200/50 flex flex-col sm:flex-row gap-2">
                    <div class="flex-grow relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#6B7280]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input type="text" name="arama" id="searchBar" placeholder="Uzman adı, branş veya şikayet yazın..."
                               class="w-full pl-11 pr-4 py-4 rounded-xl bg-transparent text-[#111827] placeholder-[#9CA3AF] focus:outline-none text-sm font-medium">
                    </div>

                    <button type="submit" class="sm:px-8 py-4 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-sm tracking-wide transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                        Uzman Ara
                    </button>
                </form>

                <!-- Popular Quick Tags (Dinamik) -->
                <div class="mt-5 flex items-center justify-center flex-wrap gap-2 text-xs">
                    <span class="text-[#6B7280] font-medium mr-1.5">Popüler:</span>
                    @foreach($populerAramalar as $arama)
                        <button type="button" onclick="setSearch('{{ $arama }}')" class="px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-slate-50 hover:bg-[#FFF7ED] hover:text-[#C96A2B] hover:border-[#E7B58A]/30 transition-all font-semibold cursor-pointer">{{ $arama }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section (Dinamik) -->
    <section id="hizmetler" class="max-w-7xl mx-auto px-6 py-20 select-none">
        <div class="text-center max-w-xl mx-auto mb-16">
            <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Kategorilere Göre Keşfedin</h2>
            <p class="text-sm text-[#6B7280] mt-2.5">Aradığınız desteği ve uzmanlığı kategorilerimiz üzerinden hızlıca listeleyebilirsiniz.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($branslar->take(8) as $brans)
                <a href="{{ route('frontend.hekimler', ['brans' => $brans->slug]) }}"
                   class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.03)] hover:-translate-y-0.5 transition-all duration-300 group block no-underline">
                    <div class="w-12 h-12 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center mb-5 transition-transform group-hover:scale-105">
                        @include('frontend.partials.brans_ikon', ['slug' => $brans->slug])
                    </div>
                    <h3 class="text-base font-bold font-display text-[#111827] mb-1.5">{{ $brans->ad }}</h3>
                    <p class="text-xs text-[#6B7280] leading-relaxed mb-4">
                        @if($brans->aciklama)
                            {{ Str::limit($brans->aciklama, 80) }}
                        @else
                            {{ $brans->doktorlar_count }} aktif uzman bu alanda hizmet veriyor.
                        @endif
                    </p>
                    <span class="text-xs font-semibold text-[#C96A2B] flex items-center gap-1 group-hover:underline cursor-pointer font-display">
                        {{ $brans->doktorlar_count }} Uzmanı Gör →
                    </span>
                </a>
            @empty
                <div class="col-span-4 text-center text-[#6B7280] py-10">
                    Henüz branş eklenmemiş.
                </div>
            @endforelse
        </div>
    </section>

    <!-- How it works -->
    <section id="nasil-calisir" class="bg-white border-t border-b border-[#E5E7EB] py-20 select-none">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-xl mx-auto mb-16">
                <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Nasıl Çalışır?</h2>
                <p class="text-sm text-[#6B7280] mt-2.5">Sadece 3 adımda dilediğiniz uzmanla görüşmenizi planlayın.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Step 1 -->
                <div class="text-center space-y-4">
                    <div class="w-12 h-12 rounded-full bg-[#FFF7ED] text-[#C96A2B] font-bold font-display text-lg flex items-center justify-center mx-auto shadow-inner">1</div>
                    <h3 class="text-base font-bold font-display text-[#111827]">Uzmanını Seç</h3>
                    <p class="text-xs text-[#6B7280] leading-relaxed max-w-xs mx-auto">
                        @if(isset($istatistikler))
                            {{ number_format($istatistikler['doktor_sayisi']) }} uzman ve {{ $istatistikler['brans_sayisi'] }} farklı branş arasından filtreleri kullanarak aradığınız uzmanı bulun.
                        @else
                            Binlerce hekim ve danışan arasından filtreleri kullanarak aradığınız uzmanı bulun.
                        @endif
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center space-y-4">
                    <div class="w-12 h-12 rounded-full bg-[#FFF7ED] text-[#C96A2B] font-bold font-display text-lg flex items-center justify-center mx-auto shadow-inner">2</div>
                    <h3 class="text-base font-bold font-display text-[#111827]">Günü ve Saati Belirle</h3>
                    <p class="text-xs text-[#6B7280] leading-relaxed max-w-xs mx-auto">
                        Uzmanın güncel ajandasına doğrudan erişerek size en uygun seansı seçin.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center space-y-4">
                    <div class="w-12 h-12 rounded-full bg-[#FFF7ED] text-[#C96A2B] font-bold font-display text-lg flex items-center justify-center mx-auto shadow-inner">3</div>
                    <h3 class="text-base font-bold font-display text-[#111827]">Randevunu Tamamla</h3>
                    <p class="text-xs text-[#6B7280] leading-relaxed max-w-xs mx-auto">
                        Kaydınızı tamamlayın. Onay SMS ve e-postanız anında cebinize gelsin.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctors / Experts Grid (Dinamik) -->
    <section id="doktorlar" class="max-w-7xl mx-auto px-6 py-20 select-none">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-16">
            <div>
                <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Öne Çıkan Uzmanlarımız</h2>
                <p class="text-sm text-[#6B7280] mt-2.5">Danışan memnuniyeti en yüksek olan bazı aktif uzman kadromuz.</p>
            </div>
            <div>
                <a href="{{ route('frontend.hekimler') }}" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] transition-colors font-display no-underline">
                    Tümünü Gör
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="expertsGrid">
            @forelse($oneCikanDoktorlar as $doktor)
                @php
                    $brans = $doktor->branslar->first();
                    $bransAd = $brans ? $brans->ad : 'Uzman';
                    $initials = collect(explode(' ', $doktor->ad_soyad))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
                    $ortalamaPuan = $doktor->ortalama_puan_cache ?? $doktor->ortalama_puan ?? 0;
                    $yorumSayisi = $doktor->yorum_sayisi_cache ?? 0;
                @endphp
                <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-[0_4px_24px_rgba(31,41,55,0.03)] hover:-translate-y-0.5 transition-all duration-300 p-6 flex flex-col justify-between">
                    <div>
                        <!-- Doctor Header Info -->
                        <div class="flex gap-4 mb-6">
                            <!-- Avatar -->
                            @if($doktor->profil_resmi)
                                <img src="{{ asset('storage/' . $doktor->profil_resmi) }}"
                                     alt="{{ $doktor->ad_soyad }}"
                                     class="w-14 h-14 rounded-full object-cover border border-[#E7B58A]/30 flex-shrink-0">
                            @else
                                <div class="w-14 h-14 rounded-full bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center font-extrabold text-sm font-display flex-shrink-0">
                                    {{ $initials }}
                                </div>
                            @endif
                            <div>
                                <span class="inline-block px-2.5 py-0.5 bg-[#FFF7ED] text-[#C96A2B] text-[10px] font-bold rounded-full font-display uppercase tracking-wider">{{ $bransAd }}</span>
                                <h3 class="text-base font-bold font-display text-[#111827] mt-1.5">
                                    {{ $doktor->unvan ? $doktor->unvan . ' ' : '' }}{{ $doktor->ad_soyad }}
                                </h3>
                                <p class="text-[11px] text-[#6B7280] mt-0.5">
                                    {{ $doktor->uzmanlik_alani ?? $bransAd }}
                                    @if($doktor->il)
                                        · {{ $doktor->il->ad }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Statistics & Reviews -->
                        <div class="grid grid-cols-2 gap-4 py-3 border-t border-b border-[#E5E7EB] mb-6 text-xs font-semibold">
                            <div>
                                <span class="text-[10px] text-[#6B7280] block font-bold uppercase font-display">Memnuniyet</span>
                                <span class="text-[#111827] mt-1 block flex items-center gap-1">
                                    <span class="text-[#C96A2B]">★</span> {{ $ortalamaPuan }} <span class="text-[#6B7280] font-normal">({{ $yorumSayisi }} Değerlendirme)</span>
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] text-[#6B7280] block font-bold uppercase font-display">Konum</span>
                                <span class="text-[#111827] mt-1 block">
                                    {{ $doktor->il ? $doktor->il->ad : '—' }}{{ $doktor->ilce ? ', ' . $doktor->ilce->ad : '' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ $doktor->profil_url }}" class="w-full text-center py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm block font-display no-underline">
                        Online Randevu Al
                    </a>
                </div>
            @empty
                <div class="col-span-3 text-center text-[#6B7280] py-10">
                    Henüz uzman kaydı bulunmamaktadır.
                </div>
            @endforelse
        </div>
    </section>

    <!-- Son Yorumlar / Testimonials (Dinamik) -->
    @if(isset($sonYorumlar) && $sonYorumlar->count() > 0)
    <section class="bg-white border-t border-b border-[#E5E7EB] py-20 select-none">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-xl mx-auto mb-16">
                <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Hastalarımız Ne Diyor?</h2>
                <p class="text-sm text-[#6B7280] mt-2.5">Platformumuzdan hizmet alan hastaların değerlendirmeleri.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($sonYorumlar->take(3) as $yorum)
                    <div class="p-6 rounded-2xl bg-[#FAFAFA] border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.03)]">
                        <!-- Yıldızlar -->
                        <div class="flex items-center gap-0.5 mb-4">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="{{ $i <= $yorum->puan ? 'text-[#C96A2B]' : 'text-[#D1D5DB]' }} text-sm">★</span>
                            @endfor
                        </div>

                        <!-- Yorum -->
                        <p class="text-sm text-[#374151] leading-relaxed mb-5 italic">
                            "{{ Str::limit($yorum->yorum, 150) }}"
                        </p>

                        <!-- Hasta ve Doktor bilgisi -->
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-[#111827]">
                                    {{ $yorum->hasta ? $yorum->hasta->maskeli_ad : 'Anonim Hasta' }}
                                </p>
                                <p class="text-[10px] text-[#6B7280] mt-0.5">
                                    {{ $yorum->doktor ? ($yorum->doktor->unvan ? $yorum->doktor->unvan . ' ' : '') . $yorum->doktor->ad_soyad : '' }}
                                </p>
                            </div>
                            <span class="text-[10px] text-[#9CA3AF]">{{ $yorum->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Son Blog Yazıları (Dinamik) -->
    @if(isset($sonBloglar) && $sonBloglar->count() > 0)
    <section class="max-w-7xl mx-auto px-6 py-20 select-none">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-16">
            <div>
                <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Uzman Yazıları</h2>
                <p class="text-sm text-[#6B7280] mt-2.5">Uzmanlarımızın kaleme aldığı güncel sağlık ve bilgilendirme yazıları.</p>
            </div>
            <div>
                <a href="{{ route('frontend.blog.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] transition-colors font-display no-underline">
                    Tüm Yazılar
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($sonBloglar as $blog)
                <a href="{{ $blog->url }}" class="rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.03)] hover:-translate-y-0.5 transition-all duration-300 overflow-hidden group block no-underline">
                    @if($blog->resim)
                        <div class="aspect-video overflow-hidden">
                            <img src="{{ asset($blog->resim) }}"
                                 alt="{{ $blog->baslik }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                    @else
                        <div class="aspect-video bg-gradient-to-br from-[#FFF7ED] to-[#FEE2C5] flex items-center justify-center">
                            <svg class="w-12 h-12 text-[#C96A2B]/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                            </svg>
                        </div>
                    @endif
                    <div class="p-5">
                        <h3 class="text-sm font-bold font-display text-[#111827] mb-2 group-hover:text-[#C96A2B] transition-colors line-clamp-2">{{ $blog->baslik }}</h3>
                        <div class="flex items-center justify-between text-[10px] text-[#6B7280]">
                            <span>{{ $blog->doktor ? ($blog->doktor->unvan ? $blog->doktor->unvan . ' ' : '') . $blog->doktor->ad_soyad : '' }}</span>
                            <span>{{ $blog->created_at->format('d.m.Y') }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif
@endsection

@section('script')
    <script>
        function setSearch(val) {
            const bar = document.getElementById('searchBar');
            if(bar) {
                bar.value = val;
                performSearch();
            }
        }

        function performSearch() {
            const query = document.getElementById('searchBar').value.trim();
            if (query !== '') {
                window.location.href = "{{ route('frontend.hekimler') }}?arama=" + encodeURIComponent(query);
            } else {
                window.location.href = "{{ route('frontend.hekimler') }}";
            }
        }
    </script>
@endsection
