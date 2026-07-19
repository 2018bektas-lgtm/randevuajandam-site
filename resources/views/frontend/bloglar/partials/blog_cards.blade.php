@if($bloglar->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($bloglar as $blog)
            @php
                $doktor = $blog->doktor;
                $ilSlug = $doktor->il?->slug ?? 'il';
                $ilceSlug = $doktor->ilce?->slug ?? 'ilce';
                $bransSlug = $doktor->branslar->first()?->slug ?? 'hekim';
                $detailUrl = route('frontend.hekim.blog.detay', [
                    'il_slug' => $ilSlug,
                    'ilce_slug' => $ilceSlug,
                    'brans_slug' => $bransSlug,
                    'doctor_slug' => $doktor->slug,
                    'blog_slug' => $blog->slug
                ]);
                
                $blogImage = null;
                if ($blog->resim) {
                    if (\Illuminate\Support\Str::startsWith($blog->resim, 'http')) {
                        $blogImage = $blog->resim;
                    } elseif (\Illuminate\Support\Str::startsWith($blog->resim, 'uploads')) {
                        $blogImage = asset($blog->resim);
                    } else {
                        $blogImage = asset($blog->resim);
                    }
                }
            @endphp
            <article class="flex flex-col bg-white rounded-3xl border border-[#E5E7EB] overflow-hidden hover:shadow-[0_20px_40px_rgba(0,0,0,0.04)] hover:border-[#C96A2B]/20 transition-all duration-300 group relative">
                <!-- Blog Image -->
                <div class="relative h-52 overflow-hidden bg-slate-100 shrink-0">
                    @if($blogImage)
                        <img src="{{ $blogImage }}" alt="{{ $blog->baslik }}" 
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <!-- Premium gradient fallback -->
                        <div class="w-full h-full bg-gradient-to-br from-[#FFF7ED] to-[#FDF2E9] flex items-center justify-center relative">
                            <span class="text-4xl">✍️</span>
                            <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                        </div>
                    @endif
                    <!-- Branch Badge -->
                    <span class="absolute top-4 left-4 px-3 py-1.5 rounded-full bg-white/90 backdrop-blur-sm text-[10px] font-bold text-[#C96A2B] tracking-wider uppercase shadow-sm">
                        {{ $doktor->branslar->first()?->ad ?? 'Genel Sağlık' }}
                    </span>
                </div>

                <!-- Blog Body -->
                <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <!-- Meta Date & Views -->
                        <div class="flex items-center gap-3 text-[11px] text-gray-400 font-semibold font-display">
                            <time datetime="{{ $blog->created_at->toW3cString() }}" class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-[#E7B58A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path>
                                </svg>
                                {{ $blog->created_at->locale('tr')->translatedFormat('d M Y') }}
                            </time>
                            <span>•</span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-[#E7B58A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $blog->okunma_sayisi ?? 0 }} Okunma
                            </span>
                        </div>
                        
                        <!-- Blog Title -->
                        <h2 class="text-lg font-bold text-[#111827] font-display line-clamp-2 hover:text-[#C96A2B] transition-colors leading-tight">
                            <a href="{{ $detailUrl }}">{{ $blog->baslik }}</a>
                        </h2>

                        <!-- Excerpt -->
                        <p class="text-xs text-gray-500 leading-relaxed line-clamp-3">
                            {{ Str::limit(strip_tags($blog->icerik), 120) }}
                        </p>
                    </div>

                    <!-- Author & Footer -->
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100 shrink-0">
                        <a href="{{ $doktor->profil_url }}" class="flex items-center gap-2.5 group/author min-w-0">
                            @if($doktor->profil_resmi)
                                <img src="{{ asset($doktor->profil_resmi) }}" alt="{{ $doktor->ad_soyad }}" 
                                     class="w-8 h-8 rounded-full object-cover border border-slate-100 group-hover/author:border-[#C96A2B]/40 transition-colors shrink-0">
                            @else
                                <div class="w-8 h-8 rounded-full bg-[#FFF7ED] text-[#C96A2B] text-[10px] font-bold flex items-center justify-center border border-[#E7B58A]/20 shrink-0">
                                    {{ mb_strtoupper(mb_substr($doktor->ad_soyad, 0, 2)) }}
                                </div>
                            @endif
                            <div class="min-w-0 leading-tight">
                                <span class="block text-[11px] font-bold text-[#111827] group-hover/author:text-[#C96A2B] transition-colors truncate">
                                    {{ $doktor->unvan ? $doktor->unvan . ' ' : '' }}{{ $doktor->ad_soyad }}
                                </span>
                                <span class="block text-[9px] text-[#6B7280] truncate">
                                    {{ $doktor->klinik_adi ?? 'Bireysel Muayenehane' }}
                                </span>
                            </div>
                        </a>
                        
                        <a href="{{ $detailUrl }}" class="p-2 bg-slate-50 hover:bg-[#FFF7ED] text-slate-500 hover:text-[#C96A2B] rounded-xl transition-all duration-200" title="Yazıyı Oku">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-12 flex justify-center">
        {{ $bloglar->links() }}
    </div>
@else
    <div class="bg-white border border-[#E5E7EB] rounded-3xl p-12 text-center max-w-lg mx-auto space-y-4">
        <span class="text-4xl block">🔍</span>
        <h3 class="text-base font-bold text-[#111827]">Eşleşen Blog Yazısı Bulunamadı</h3>
        <p class="text-xs text-[#6B7280]">Farklı bir arama terimi deneyebilir veya diğer branş filtrelerini seçebilirsiniz.</p>
    </div>
@endif
