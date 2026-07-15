@extends('yonetim.layout')

@section('baslik', 'Yorum Moderasyonu - Yönetim Paneli')
@section('sayfa_baslik', 'Yorum Moderasyonu')

@section('icerik')
<div class="space-y-8">

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#6B7280] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Toplam</p>
                    <p class="text-xl font-extrabold text-[#111827] font-display">{{ $istatistikler['toplam'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Beklemede</p>
                    <p class="text-xl font-extrabold text-amber-600 font-display">{{ $istatistikler['beklemede'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Yayında</p>
                    <p class="text-xl font-extrabold text-emerald-600 font-display">{{ $istatistikler['onaylandi'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Reddedildi</p>
                    <p class="text-xl font-extrabold text-rose-600 font-display">{{ $istatistikler['reddedildi'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm">
        <form method="GET" action="{{ route('yonetim.yorumlar.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="space-y-1 flex-1 min-w-[180px]">
                <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Arama</label>
                <input type="text" name="arama" value="{{ request('arama') }}" placeholder="Hasta, doktor veya yorum..."
                       class="w-full px-3 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] placeholder-gray-400">
            </div>
            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Durum</label>
                <select name="durum" class="px-3 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
                    <option value="">Tümü</option>
                    <option value="beklemede" {{ request('durum') === 'beklemede' ? 'selected' : '' }}>Beklemede</option>
                    <option value="onaylandi" {{ request('durum') === 'onaylandi' ? 'selected' : '' }}>Onaylandı</option>
                    <option value="reddedildi" {{ request('durum') === 'reddedildi' ? 'selected' : '' }}>Reddedildi</option>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Puan</label>
                <select name="puan" class="px-3 py-2 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] text-xs focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
                    <option value="">Tümü</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('puan') == $i ? 'selected' : '' }}>{{ $i }} Yıldız</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all cursor-pointer font-display">
                Filtrele
            </button>
            @if(request()->hasAny(['durum', 'puan', 'arama']))
                <a href="{{ route('yonetim.yorumlar.index') }}" class="px-4 py-2 border border-[#E5E7EB] hover:bg-slate-50 text-[#6B7280] font-bold text-xs uppercase tracking-wider rounded-xl transition-all font-display">
                    Temizle
                </a>
            @endif
        </form>
    </div>

    <!-- Reviews List -->
    <div class="space-y-4">
        @forelse($yorumlar as $yorum)
            <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm space-y-4 hover:shadow-md transition-shadow">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <!-- Hasta -->
                        <div class="w-10 h-10 rounded-full bg-[#FFF7ED] border border-[#E7B58A]/30 text-[#C96A2B] flex items-center justify-center text-xs font-bold font-display shrink-0">
                            {{ mb_strtoupper(mb_substr($yorum->hasta->ad, 0, 1)) }}{{ mb_strtoupper(mb_substr($yorum->hasta->soyad, 0, 1)) }}
                        </div>
                        <div class="space-y-0.5">
                            <p class="text-sm font-bold text-[#111827] font-display">{{ $yorum->hasta->ad_soyad }}</p>
                            <p class="text-[10px] text-[#6B7280]">
                                {{ $yorum->created_at->translatedFormat('d M Y, H:i') }}
                            </p>
                            <p class="text-[10px] text-[#6B7280]">
                                Hekim:
                                <span class="font-semibold text-[#C96A2B]">
                                    {{ $yorum->doktor->unvan ? $yorum->doktor->unvan . ' ' : '' }}{{ $yorum->doktor->ad_soyad }}
                                </span>
                                @if($yorum->randevu && $yorum->randevu->hizmet)
                                    &middot; {{ $yorum->randevu->hizmet->ad }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <!-- Stars -->
                        <div class="flex items-center gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $yorum->puan ? 'text-[#C96A2B]' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path>
                                </svg>
                            @endfor
                        </div>
                        <!-- Status Badge -->
                        @if($yorum->onay_durumu === 'beklemede')
                            <span class="inline-block px-2.5 py-1 text-[9px] uppercase font-bold tracking-wider rounded-full bg-amber-50 text-amber-700 border border-amber-200">Beklemede</span>
                        @elseif($yorum->onay_durumu === 'onaylandi')
                            <span class="inline-block px-2.5 py-1 text-[9px] uppercase font-bold tracking-wider rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Yayında</span>
                        @elseif($yorum->onay_durumu === 'reddedildi')
                            <span class="inline-block px-2.5 py-1 text-[9px] uppercase font-bold tracking-wider rounded-full bg-rose-50 text-rose-700 border border-rose-200">Reddedildi</span>
                        @endif
                    </div>
                </div>

                <!-- Review Text -->
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
                    <p class="text-xs text-[#4B5563] leading-relaxed">{{ $yorum->yorum }}</p>
                </div>

                <!-- Doctor Reply (if any) -->
                @if($yorum->doktor_yaniti)
                    <div class="bg-[#FFF7ED] border border-[#E7B58A]/30 rounded-xl p-4">
                        <p class="text-[10px] font-bold text-[#C96A2B] uppercase tracking-wider font-display mb-1">Hekim Yanıtı</p>
                        <p class="text-xs text-[#4B5563] leading-relaxed">{{ $yorum->doktor_yaniti }}</p>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-slate-100">
                    @if($yorum->onay_durumu !== 'onaylandi')
                        <form action="{{ route('yonetim.yorumlar.onayla', $yorum->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[10px] uppercase tracking-wider rounded-xl transition-colors cursor-pointer border border-emerald-200 font-display">
                                ✓ Onayla
                            </button>
                        </form>
                    @endif

                    @if($yorum->onay_durumu !== 'reddedildi')
                        <form action="{{ route('yonetim.yorumlar.reddet', $yorum->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-[10px] uppercase tracking-wider rounded-xl transition-colors cursor-pointer border border-rose-200 font-display">
                                ✕ Reddet
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('yonetim.yorumlar.sil', $yorum->id) }}" method="POST" class="inline"
                          onsubmit="return onayModalAc(event, this, 'Bu yorumu kalıcı olarak silmek istediğinize emin misiniz?')">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold text-[10px] uppercase tracking-wider rounded-xl transition-colors cursor-pointer border border-slate-200 font-display">
                            Sil
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white border border-[#E5E7EB] rounded-2xl p-12 shadow-sm text-center space-y-4">
                <div class="w-16 h-16 bg-[#FFF7ED] text-[#C96A2B] rounded-2xl flex items-center justify-center mx-auto border border-[#E7B58A]/20">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"></path>
                    </svg>
                </div>
                <div class="space-y-1">
                    <p class="text-sm font-bold text-[#111827] font-display">Yorum Bulunamadı</p>
                    <p class="text-xs text-[#6B7280]">Seçilen filtrelere uygun yorum bulunmuyor.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($yorumlar->hasPages())
        <div class="pt-2">
            {{ $yorumlar->links() }}
        </div>
    @endif

</div>
@endsection
