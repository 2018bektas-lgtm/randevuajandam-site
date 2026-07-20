@extends('frontend.layouts.app')

@section('baslik', 'Randevularım - Randevu Ajandam')

@section('icerik')
<section class="fe-page relative bg-[#FAFAFA] overflow-hidden">
    <!-- Background lights -->
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-[#C96A2B]/3 blur-[120px] pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-6 relative z-10">
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Sidebar (E-Ticaret Tarzı) -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-sm text-center lg:text-left space-y-4">
                    <!-- Profile Initials/Avatar -->
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#FFF7ED] to-[#FFFBEB] border border-[#E7B58A]/40 text-[#C96A2B] flex items-center justify-center font-extrabold font-display text-xl mx-auto lg:mx-0 select-none shadow-sm">
                        {{ mb_strtoupper(mb_substr($hasta->ad, 0, 1)) }}{{ mb_strtoupper(mb_substr($hasta->soyad, 0, 1)) }}
                    </div>
                    
                    <div class="space-y-0.5">
                        <h3 class="text-sm font-bold font-display text-[#111827]">{{ $hasta->ad_soyad }}</h3>
                        <p class="text-[11px] text-[#6B7280]">{{ $hasta->e_posta }}</p>
                    </div>
                </div>

                <!-- Navigation menu -->
                <div class="bg-white border border-[#E5E7EB] rounded-3xl overflow-hidden shadow-sm">
                    <nav class="flex flex-col">
                        <a href="{{ route('frontend.hasta.profil') }}" 
                           class="flex items-center gap-3 px-5 py-4 text-xs font-bold font-display uppercase tracking-wider border-b border-slate-100 transition-colors {{ request()->routeIs('frontend.hasta.profil') ? 'bg-slate-50 text-[#C96A2B]' : 'text-[#4B5563] hover:text-[#C96A2B] hover:bg-slate-50/50' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                            </svg>
                            Profil Bilgilerim
                        </a>
                        <a href="{{ route('frontend.hasta.randevular') }}" 
                           class="flex items-center gap-3 px-5 py-4 text-xs font-bold font-display uppercase tracking-wider border-b border-slate-100 transition-colors {{ request()->routeIs('frontend.hasta.randevular') ? 'bg-slate-50 text-[#C96A2B]' : 'text-[#4B5563] hover:text-[#C96A2B] hover:bg-slate-50/50' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"></path>
                            </svg>
                            Randevularım
                        </a>
                        <form action="{{ route('frontend.hasta.cikis') }}" method="POST" class="w-full" onsubmit="return confirm('Çıkış yapmak istediğinize emin misiniz?');">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center gap-3 px-5 py-4 text-xs font-bold font-display uppercase tracking-wider text-red-500 hover:text-red-700 hover:bg-slate-50/50 transition-colors border-none text-left cursor-pointer bg-transparent">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"></path>
                                </svg>
                                Güvenli Çıkış
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            <!-- Content Area -->
            <div class="lg:col-span-3">
                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                    
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-lg font-bold font-display text-[#111827]">Randevu Geçmişim</h2>
                        <p class="text-xs text-[#6B7280]">Aktif ve tamamlanmış tüm randevularınızı buradan takip edebilirsiniz.</p>
                    </div>

                    @if(session('basarili'))
                        <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-xs text-emerald-700 font-medium flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ session('basarili') }}
                        </div>
                    @endif

                    @if(session('hata'))
                        <div class="p-4 bg-red-50 border border-red-100 rounded-2xl text-xs text-red-600 font-medium flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                            </svg>
                            {{ session('hata') }}
                        </div>
                    @endif

                    @if($randevular->isEmpty())
                        <div class="text-center py-12 space-y-4">
                            <div class="w-16 h-16 bg-[#FFF7ED] text-[#C96A2B] rounded-2xl flex items-center justify-center mx-auto border border-[#E7B58A]/20">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-bold text-[#111827] font-display">Henüz Randevunuz Bulunmuyor</p>
                                <p class="text-xs text-[#6B7280]">Hemen uzman hekimlerimizi inceleyip ilk randevunuzu oluşturabilirsiniz.</p>
                            </div>
                            <div class="pt-2">
                                <a href="{{ route('frontend.hekimler') }}" 
                                   class="inline-flex px-5 py-2.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-sm font-display">
                                    Uzman Hekim Bul
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto border border-[#E5E7EB] rounded-2xl">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-[#E5E7EB] text-[#1F2937] font-bold">
                                        <th class="p-4 font-display uppercase tracking-wider">Hekim</th>
                                        <th class="p-4 font-display uppercase tracking-wider">Hizmet / Tedavi</th>
                                        <th class="p-4 font-display uppercase tracking-wider">Tarih & Saat</th>
                                        <th class="p-4 font-display uppercase tracking-wider text-center">Durum</th>
                                        <th class="p-4 font-display uppercase tracking-wider text-right">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5E7EB] text-[#4B5563]">
                                    @foreach($randevular as $randevu)
                                        <tr class="hover:bg-slate-50/40 transition-colors">
                                            <!-- Doctor Details -->
                                            <td class="p-4 font-semibold text-[#111827]">
                                                <a href="{{ $randevu->doktor->profil_url }}" class="hover:text-[#C96A2B] transition-colors">
                                                    {{ $randevu->doktor->unvan ? $randevu->doktor->unvan . ' ' : '' }}{{ $randevu->doktor->ad_soyad }}
                                                </a>
                                                <span class="block text-[10px] text-[#6B7280] font-normal mt-0.5">{{ $randevu->doktor->uzmanlik_alani }}</span>
                                            </td>
                                            
                                            <!-- Service Details -->
                                            <td class="p-4 font-medium">
                                                <a href="{{ $randevu->hizmet->url }}" class="hover:text-[#C96A2B] transition-colors">
                                                    {{ $randevu->hizmet->ad }}
                                                </a>
                                                <span class="block text-[10px] text-[#6B7280] font-normal mt-0.5">{{ $randevu->hizmet->sure }} dk. süre</span>
                                            </td>
                                            
                                            <!-- Date and Time -->
                                            <td class="p-4">
                                                <span class="block font-semibold text-[#111827]">{{ $randevu->tarih->translatedFormat('d M Y') }}</span>
                                                <span class="block text-[10px] text-[#6B7280] mt-0.5">{{ substr($randevu->saat, 0, 5) }}</span>
                                            </td>
                                            
                                            <!-- Status Badge -->
                                            <td class="p-4 text-center">
                                                <div class="flex flex-col items-center gap-1">
                                                @if($randevu->durum === 'beklemede')
                                                    <span class="inline-block px-2.5 py-1 text-[9px] uppercase font-bold tracking-wider rounded-full bg-amber-50 text-amber-700 border border-amber-200">Beklemede</span>
                                                @elseif($randevu->durum === 'onaylandi')
                                                    <span class="inline-block px-2.5 py-1 text-[9px] uppercase font-bold tracking-wider rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Onaylandı</span>
                                                @elseif($randevu->durum === 'iptal')
                                                    <span class="inline-block px-2.5 py-1 text-[9px] uppercase font-bold tracking-wider rounded-full bg-rose-50 text-rose-700 border border-rose-200">İptal Edildi</span>
                                                @elseif($randevu->durum === 'tamamlandi')
                                                    <span class="inline-block px-2.5 py-1 text-[9px] uppercase font-bold tracking-wider rounded-full bg-blue-50 text-blue-700 border border-blue-200">Tamamlandı</span>
                                                @endif
                                                @if(($randevu->gorusme_tipi ?? '') === 'online')
                                                    <span class="inline-block px-2 py-0.5 text-[8px] uppercase font-bold tracking-wider rounded-full bg-sky-50 text-sky-700 border border-sky-200">Online</span>
                                                @endif
                                                </div>
                                            </td>

                                            <!-- Actions -->
                                            <td class="p-4 text-right">
                                                <div class="inline-flex flex-col sm:flex-row items-end gap-1.5 justify-end">
                                                @if(($randevu->gorusme_tipi ?? '') === 'online' && $randevu->durum === 'onaylandi' && $randevu->meeting_join_token)
                                                    <a href="{{ route('frontend.gorusme.join', $randevu->meeting_join_token) }}"
                                                       class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white font-bold font-display uppercase text-[9px] tracking-wider rounded-lg transition-colors">
                                                        Katıl
                                                    </a>
                                                @endif
                                                @if(in_array($randevu->durum, ['beklemede', 'onaylandi']))
                                                    <form action="{{ route('frontend.hasta.randevu.iptal', $randevu->id) }}" method="POST" 
                                                          onsubmit="return confirm('Bu randevuyu iptal etmek istediğinize emin misiniz?')" class="inline-block">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-700 font-bold font-display uppercase text-[9px] tracking-wider rounded-lg transition-colors cursor-pointer border-none">
                                                            İptal Et
                                                        </button>
                                                    </form>
                                                @elseif($randevu->durum === 'tamamlandi')
                                                    @if($randevu->yorum)
                                                        <span class="inline-flex items-center gap-1 text-[10px] text-emerald-600 font-bold font-display uppercase tracking-wider select-none">
                                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path>
                                                            </svg>
                                                            Yorum Yapıldı
                                                        </span>
                                                    @else
                                                        <button type="button" onclick="yorumModalAc({{ $randevu->id }}, '{{ addslashes($randevu->doktor->ad_soyad) }}')"
                                                                class="px-3 py-1.5 bg-[#FFF7ED] hover:bg-[#FFEDD5] text-[#C96A2B] hover:text-[#B55A20] font-bold font-display uppercase text-[9px] tracking-wider rounded-lg transition-colors cursor-pointer border border-[#E7B58A]/30">
                                                            ⭐ Yorum Yaz
                                                        </button>
                                                    @endif
                                                @else
                                                    <span class="text-[10px] text-slate-400 font-medium font-display uppercase tracking-wider select-none">İşlem Yok</span>
                                                @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="pt-4">
                            {{ $randevular->links() }}
                        </div>
                    @endif

                </div>
            </div>

        </div>

    </div>
</section>
<!-- Yorum Yazma Modal -->
<div id="yorumModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
    <div id="yorumModalContainer" class="bg-white rounded-2xl sm:rounded-3xl border border-[#E5E7EB] shadow-2xl max-w-md w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]">
        <div class="p-5 sm:p-6 border-b border-[#E5E7EB] shrink-0">
            <h3 class="text-lg font-bold font-display text-[#111827]">Yorum ve Puanlama</h3>
            <p class="text-xs text-[#6B7280] mt-1">
                <span id="yorumDoktorAdi" class="font-semibold text-[#C96A2B]"></span> hakkında deneyiminizi paylaşın.
            </p>
        </div>

        <form id="yorumForm" action="{{ route('frontend.hasta.yorum.kaydet') }}" method="POST" class="flex flex-col flex-1 min-h-0">
            @csrf
            <input type="hidden" name="randevu_id" id="yorumRandevuId">

            <div class="p-5 sm:p-6 space-y-5 overflow-y-auto flex-1">
                <!-- Star Rating -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Puanınız</label>
                    <div class="flex items-center gap-1" id="starRating">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" data-puan="{{ $i }}" onclick="puanSec({{ $i }})"
                                    class="star-btn p-1 rounded-lg hover:bg-[#FFF7ED] transition-colors cursor-pointer border-none bg-transparent">
                                <svg class="w-8 h-8 text-slate-200 transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path>
                                </svg>
                            </button>
                        @endfor
                    </div>
                    <input type="hidden" name="puan" id="puanInput" required>
                    <p id="puanText" class="text-[10px] text-[#6B7280] font-display">Lütfen bir puan seçin</p>
                </div>

                <!-- Review Text -->
                <div class="space-y-1">
                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Yorumunuz</label>
                    <textarea name="yorum" rows="4" required minlength="10" maxlength="1000" placeholder="Deneyiminizi paylaşın... (en az 10 karakter)"
                              class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] placeholder-gray-400 resize-none transition-all"></textarea>
                    <p class="text-[10px] text-[#6B7280]">Yorumunuz platform yönetimi onayından sonra, adınız A*** O*** formatında maskelenerek yayınlanır. Hekimler yorumları panellerinden onaylayamaz.</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="p-4 sm:p-5 bg-slate-50 border-t border-[#E5E7EB] flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 shrink-0">
                <button type="button" onclick="yorumModalKapat()"
                        class="w-full sm:flex-1 py-2.5 border border-[#E5E7EB] bg-white hover:bg-slate-100 text-[#4B5563] font-bold text-xs uppercase tracking-wider rounded-xl transition-all cursor-pointer font-display shadow-sm">
                    Vazgeç
                </button>
                <button type="submit"
                        class="w-full sm:flex-1 py-2.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all cursor-pointer font-display shadow-md shadow-orange-500/10">
                    Gönder
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const puanLabels = {
        1: 'Çok Kötü',
        2: 'Kötü',
        3: 'Orta',
        4: 'İyi',
        5: 'Mükemmel'
    };

    function yorumModalAc(randevuId, doktorAdi) {
        document.getElementById('yorumRandevuId').value = randevuId;
        document.getElementById('yorumDoktorAdi').innerText = doktorAdi;
        document.getElementById('puanInput').value = '';
        document.getElementById('puanText').innerText = 'Lütfen bir puan seçin';

        // Reset stars
        document.querySelectorAll('.star-btn svg').forEach(svg => {
            svg.classList.remove('text-[#C96A2B]');
            svg.classList.add('text-slate-200');
        });

        const modal = document.getElementById('yorumModal');
        const container = document.getElementById('yorumModalContainer');
        modal.classList.remove('hidden');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 50);
    }

    function yorumModalKapat() {
        const modal = document.getElementById('yorumModal');
        const container = document.getElementById('yorumModalContainer');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function puanSec(puan) {
        document.getElementById('puanInput').value = puan;
        document.getElementById('puanText').innerText = puan + ' Yıldız — ' + puanLabels[puan];

        document.querySelectorAll('.star-btn').forEach(btn => {
            const btnPuan = parseInt(btn.dataset.puan);
            const svg = btn.querySelector('svg');
            if (btnPuan <= puan) {
                svg.classList.remove('text-slate-200');
                svg.classList.add('text-[#C96A2B]');
            } else {
                svg.classList.remove('text-[#C96A2B]');
                svg.classList.add('text-slate-200');
            }
        });
    }

    // Close on overlay click
    document.getElementById('yorumModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            yorumModalKapat();
        }
    });
</script>
@endsection
