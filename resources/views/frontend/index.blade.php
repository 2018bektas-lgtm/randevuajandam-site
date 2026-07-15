@extends('frontend.layouts.app')

@section('baslik', 'Randevu Ajandam - Uzman Doktor ve Randevu Platformu')

@section('icerik')
    <!-- Hero Section -->
    <section class="relative bg-white border-b border-[#E5E7EB] pt-16 pb-20 md:pt-24 md:pb-28 overflow-hidden select-none">
        <!-- Background Ambient Lights -->
        <div class="absolute top-[-30%] right-[-10%] w-[550px] h-[550px] rounded-full bg-[#E7B58A]/10 blur-[130px] pointer-events-none"></div>
        <div class="absolute bottom-[-20%] left-[-10%] w-[550px] h-[550px] rounded-full bg-[#C96A2B]/4 blur-[130px] pointer-events-none"></div>

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
                Psikologlardan diyetisyenlere, çocuk gelişimcilerinden fizyoterapistlere kadar yüzlerce profesyonel arasından size en uygun olanını seçin.
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

            <!-- Popular Quick Tags -->
            <div class="mt-5 flex items-center justify-center flex-wrap gap-2 text-xs">
                <span class="text-[#6B7280] font-medium mr-1.5">Popüler:</span>
                <button type="button" onclick="setSearch('Klinik Psikolog')" class="px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-slate-50 hover:bg-[#FFF7ED] hover:text-[#C96A2B] hover:border-[#E7B58A]/30 transition-all font-semibold cursor-pointer">Klinik Psikolog</button>
                <button type="button" onclick="setSearch('Diyetisyen')" class="px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-slate-50 hover:bg-[#FFF7ED] hover:text-[#C96A2B] hover:border-[#E7B58A]/30 transition-all font-semibold cursor-pointer">Diyetisyen</button>
                <button type="button" onclick="setSearch('Aile Danışmanı')" class="px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-slate-50 hover:bg-[#FFF7ED] hover:text-[#C96A2B] hover:border-[#E7B58A]/30 transition-all font-semibold cursor-pointer">Aile Danışmanı</button>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="hizmetler" class="max-w-7xl mx-auto px-6 py-20 select-none">
        <div class="text-center max-w-xl mx-auto mb-16">
            <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Kategorilere Göre Keşfedin</h2>
            <p class="text-sm text-[#6B7280] mt-2.5">Aradığınız desteği ve uzmanlığı kategorilerimiz üzerinden hızlıca listeleyebilirsiniz.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Cat 1 -->
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.03)] hover:-translate-y-0.5 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center mb-5 transition-transform group-hover:scale-105">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold font-display text-[#111827] mb-1.5">Psikoloji & Terapi</h3>
                <p class="text-xs text-[#6B7280] leading-relaxed mb-4">Depresyon, kaygı, aile problemleri için profesyonel klinik destek.</p>
                <span class="text-xs font-semibold text-[#C96A2B] flex items-center gap-1 group-hover:underline cursor-pointer font-display">
                    Uzmanları Gör →
                </span>
            </div>

            <!-- Cat 2 -->
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.03)] hover:-translate-y-0.5 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center mb-5 transition-transform group-hover:scale-105">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold font-display text-[#111827] mb-1.5">Diyetisyen & Beslenme</h3>
                <p class="text-xs text-[#6B7280] leading-relaxed mb-4">Sağlıklı beslenme, kilo yönetimi, hastalıklara özel beslenme programları.</p>
                <span class="text-xs font-semibold text-[#C96A2B] flex items-center gap-1 group-hover:underline cursor-pointer font-display">
                    Uzmanları Gör →
                </span>
            </div>

            <!-- Cat 3 -->
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.03)] hover:-translate-y-0.5 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center mb-5 transition-transform group-hover:scale-105">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold font-display text-[#111827] mb-1.5">Çocuk Gelişimi</h3>
                <p class="text-xs text-[#6B7280] leading-relaxed mb-4">Pedagojik danışmanlık, oyun terapileri ve child zihinsel/bedensel takip.</p>
                <span class="text-xs font-semibold text-[#C96A2B] flex items-center gap-1 group-hover:underline cursor-pointer font-display">
                    Uzmanları Gör →
                </span>
            </div>

            <!-- Cat 4 -->
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.03)] hover:-translate-y-0.5 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center mb-5 transition-transform group-hover:scale-105">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold font-display text-[#111827] mb-1.5">Kişisel Gelişim</h3>
                <p class="text-xs text-[#6B7280] leading-relaxed mb-4">Yaşam koçluğu, kariyer danışmanlığı, mindfulness ve stres kontrolü.</p>
                <span class="text-xs font-semibold text-[#C96A2B] flex items-center gap-1 group-hover:underline cursor-pointer font-display">
                    Uzmanları Gör →
                </span>
            </div>
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
                        Binlerce hekim ve danışan arasından filtreleri kullanarak aradığınız uzmanı bulun.
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

    <!-- Doctors / Experts Grid -->
    <section id="doktorlar" class="max-w-7xl mx-auto px-6 py-20 select-none">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-16">
            <div>
                <h2 class="text-3xl font-bold font-display text-[#111827] tracking-tight">Öne Çıkan Uzmanlarımız</h2>
                <p class="text-sm text-[#6B7280] mt-2.5">Danışan memnuniyeti en yüksek olan bazı aktif uzman kadromuz.</p>
            </div>
            <div>
                <button class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#C96A2B] hover:text-[#B55A20] transition-colors font-display">
                    Tümünü Gör
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="expertsGrid">
            <!-- Doctor 1 -->
            <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-[0_4px_24px_rgba(31,41,55,0.03)] hover:-translate-y-0.5 transition-all duration-300 p-6 flex flex-col justify-between" data-name="Derin Yılmaz" data-branch="Klinik Psikolog">
                <div>
                    <!-- Doctor Header Info -->
                    <div class="flex gap-4 mb-6">
                        <!-- Avatar Monogram -->
                        <div class="w-14 h-14 rounded-full bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center font-extrabold text-sm font-display flex-shrink-0">
                            DY
                        </div>
                        <div>
                            <span class="inline-block px-2.5 py-0.5 bg-[#FFF7ED] text-[#C96A2B] text-[10px] font-bold rounded-full font-display uppercase tracking-wider">Klinik Psikolog</span>
                            <h3 class="text-base font-bold font-display text-[#111827] mt-1.5">Uzm. Psk. Derin Yılmaz</h3>
                            <p class="text-[11px] text-[#6B7280] mt-0.5">Yetişkin Terapisi & Kaygı Bozuklukları</p>
                        </div>
                    </div>

                    <!-- Statistics & Reviews -->
                    <div class="grid grid-cols-2 gap-4 py-3 border-t border-b border-[#E5E7EB] mb-6 text-xs font-semibold">
                        <div>
                            <span class="text-[10px] text-[#6B7280] block font-bold uppercase font-display">Memnuniyet</span>
                            <span class="text-[#111827] mt-1 block flex items-center gap-1">
                                <span class="text-[#C96A2B]">★</span> 4.9 <span class="text-[#6B7280] font-normal">(124 Değerlendirme)</span>
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#6B7280] block font-bold uppercase font-display">Deneyim</span>
                            <span class="text-[#111827] mt-1 block">9 Yıl</span>
                        </div>
                    </div>
                </div>

                <a href="#randevu-al" class="w-full text-center py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm block font-display">
                    Online Randevu Al
                </a>
            </div>

            <!-- Doctor 2 -->
            <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-[0_4px_24px_rgba(31,41,55,0.03)] hover:-translate-y-0.5 transition-all duration-300 p-6 flex flex-col justify-between" data-name="Mert Kaan" data-branch="Diyetisyen">
                <div>
                    <!-- Doctor Header Info -->
                    <div class="flex gap-4 mb-6">
                        <!-- Avatar Monogram -->
                        <div class="w-14 h-14 rounded-full bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center font-extrabold text-sm font-display flex-shrink-0">
                            MK
                        </div>
                        <div>
                            <span class="inline-block px-2.5 py-0.5 bg-[#FFF7ED] text-[#C96A2B] text-[10px] font-bold rounded-full font-display uppercase tracking-wider">Diyetisyen</span>
                            <h3 class="text-base font-bold font-display text-[#111827] mt-1.5">Dyt. Mert Kaan</h3>
                            <p class="text-[11px] text-[#6B7280] mt-0.5">Kilo Kontrolü & Sporcu Beslenmesi</p>
                        </div>
                    </div>

                    <!-- Statistics & Reviews -->
                    <div class="grid grid-cols-2 gap-4 py-3 border-t border-b border-[#E5E7EB] mb-6 text-xs font-semibold">
                        <div>
                            <span class="text-[10px] text-[#6B7280] block font-bold uppercase font-display">Memnuniyet</span>
                            <span class="text-[#111827] mt-1 block flex items-center gap-1">
                                <span class="text-[#C96A2B]">★</span> 4.8 <span class="text-[#6B7280] font-normal">(98 Değerlendirme)</span>
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#6B7280] block font-bold uppercase font-display">Deneyim</span>
                            <span class="text-[#111827] mt-1 block">6 Yıl</span>
                        </div>
                    </div>
                </div>

                <a href="#randevu-al" class="w-full text-center py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm block font-display">
                    Online Randevu Al
                </a>
            </div>

            <!-- Doctor 3 -->
            <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-[0_4px_24px_rgba(31,41,55,0.03)] hover:-translate-y-0.5 transition-all duration-300 p-6 flex flex-col justify-between" data-name="Sinem Atasoy" data-branch="Aile Danışmanı">
                <div>
                    <!-- Doctor Header Info -->
                    <div class="flex gap-4 mb-6">
                        <!-- Avatar Monogram -->
                        <div class="w-14 h-14 rounded-full bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center font-extrabold text-sm font-display flex-shrink-0">
                            SA
                        </div>
                        <div>
                            <span class="inline-block px-2.5 py-0.5 bg-[#FFF7ED] text-[#C96A2B] text-[10px] font-bold rounded-full font-display uppercase tracking-wider">Aile Danışmanı</span>
                            <h3 class="text-base font-bold font-display text-[#111827] mt-1.5">Uzm. Dan. Sinem Atasoy</h3>
                            <p class="text-[11px] text-[#6B7280] mt-0.5">Evlilik ve Çift Terapisi</p>
                        </div>
                    </div>

                    <!-- Statistics & Reviews -->
                    <div class="grid grid-cols-2 gap-4 py-3 border-t border-b border-[#E5E7EB] mb-6 text-xs font-semibold">
                        <div>
                            <span class="text-[10px] text-[#6B7280] block font-bold uppercase font-display">Memnuniyet</span>
                            <span class="text-[#111827] mt-1 block flex items-center gap-1">
                                <span class="text-[#C96A2B]">★</span> 5.0 <span class="text-[#6B7280] font-normal">(152 Değerlendirme)</span>
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#6B7280] block font-bold uppercase font-display">Deneyim</span>
                            <span class="text-[#111827] mt-1 block">12 Yıl</span>
                        </div>
                    </div>
                </div>

                <a href="#randevu-al" class="w-full text-center py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm block font-display">
                    Online Randevu Al
                </a>
            </div>
        </div>
    </section>
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
