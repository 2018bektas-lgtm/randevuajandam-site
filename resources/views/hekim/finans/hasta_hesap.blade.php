@extends('hekim.layout')

@section('baslik', $hasta->ad_soyad . ' — Cari Hesap')
@section('sayfa_baslik', 'Finansal Yönetim')

@section('icerik')
    {{-- FINANS NAV --}}
    @include('hekim.finans.partials._nav')

    @if(session('basarili'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('basarili') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <ul class="list-disc pl-4 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- HASTA BAŞLIK + AKSIYONLAR --}}
    <div class="mb-6 p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center flex-shrink-0 font-bold text-lg font-display">
                {{ mb_strtoupper(mb_substr($hasta->ad_soyad, 0, 2)) }}
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-[#9CA3AF]">Cari Hesap</p>
                <h2 class="text-xl font-bold font-display text-[#111827]">{{ $hasta->ad_soyad }}</h2>
                <p class="text-xs text-[#6B7280] mt-0.5">
                    {{ $hasta->telefon ?? '—' }}
                    @if($hasta->e_posta) &middot; {{ $hasta->e_posta }} @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('hekim.finans.hasta-bakiyeleri') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-[#6B7280] bg-white hover:bg-[#F3F4F6] border border-[#E5E7EB] rounded-xl transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                Listeye Dön
            </a>
            <button type="button" onclick="modalAc('borcModal')"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-[#C96A2B] bg-white border border-[#C96A2B] hover:bg-[#FFF7ED] rounded-xl transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Borç Ekle
            </button>
            <button type="button" onclick="modalAc('tahsilatModal')" {{ $acikFaturalar->isEmpty() ? 'disabled' : '' }}
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75"/>
                </svg>
                Tahsilat Al
            </button>
        </div>
    </div>

    {{-- ÖZET KARTLAR --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <div class="flex items-start justify-between mb-1.5">
                <p class="text-[11px] font-bold uppercase tracking-wide text-[#9CA3AF]">Toplam Borç</p>
                <span class="w-7 h-7 rounded-lg bg-[#F3F4F6] text-[#6B7280] flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-[#111827] font-display">{{ number_format($toplamBorc, 2, ',', '.') }} ₺</p>
            <p class="text-[11px] text-[#9CA3AF] mt-1">Tüm alacak kayıtları toplamı</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <div class="flex items-start justify-between mb-1.5">
                <p class="text-[11px] font-bold uppercase tracking-wide text-emerald-600">Tahsil Edilen</p>
                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-emerald-700 font-display">{{ number_format($toplamOdenen, 2, ',', '.') }} ₺</p>
            <p class="text-[11px] text-[#9CA3AF] mt-1">Tüm ödemeler toplamı</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <div class="flex items-start justify-between mb-1.5">
                <p class="text-[11px] font-bold uppercase tracking-wide {{ $kalanBakiye > 0 ? 'text-rose-600' : 'text-emerald-600' }}">Kalan Borç</p>
                <span class="w-7 h-7 rounded-lg flex items-center justify-center {{ $kalanBakiye > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        @if($kalanBakiye > 0)
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.008h.008v.008H12v-.008zM10.5 20.25h3a2.25 2.25 0 002.25-2.25V4.5a2.25 2.25 0 00-2.25-2.25h-3A2.25 2.25 0 008.25 4.5v13.5a2.25 2.25 0 002.25 2.25z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @endif
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold font-display {{ $kalanBakiye > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                {{ number_format($kalanBakiye, 2, ',', '.') }} ₺
            </p>
            <p class="text-[11px] mt-1 {{ $kalanBakiye > 0 ? 'text-rose-500' : 'text-emerald-500' }}">
                {{ $kalanBakiye > 0 ? 'Tahsil edilmedi' : 'Borcu yok' }}
            </p>
        </div>
    </div>

    {{-- HESAP HAREKETLERİ --}}
    <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-[#F3F4F6] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                    </svg>
                </span>
                <h3 class="text-base font-bold font-display text-[#111827]">Hesap Hareketleri</h3>
            </div>
            <span class="text-xs text-[#9CA3AF]">{{ count($hareketler) }} kayıt</span>
        </div>

        @if(count($hareketler) === 0)
            <div class="py-14 text-center">
                <div class="w-14 h-14 mx-auto rounded-full bg-[#FAFAFA] text-[#9CA3AF] flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-[#111827]">Bu hastaya ait hareket yok</p>
                <p class="text-xs text-[#6B7280] mt-1 mb-4">Yukarıdaki "Borç Ekle" butonuyla başlayın.</p>
                <button type="button" onclick="modalAc('borcModal')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#C96A2B] hover:bg-[#b05c24] transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Borç Ekle
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FAFAFA] border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider">
                            <th class="px-5 py-3.5">Tarih</th>
                            <th class="px-5 py-3.5">Açıklama</th>
                            <th class="px-5 py-3.5 text-right">Borç</th>
                            <th class="px-5 py-3.5 text-right">Ödenen</th>
                            <th class="px-5 py-3.5 text-right">Kalan</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-[#374151]">
                        @foreach($hareketler as $h)
                            <tr class="border-b border-[#F3F4F6] hover:bg-[#FAFAFA]/60 transition-colors">
                                <td class="px-5 py-3.5 text-xs text-[#6B7280] whitespace-nowrap align-top">
                                    {{ \Carbon\Carbon::parse($h['tarih'])->format('d.m.Y') }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-[#111827]">{{ $h['baslik'] }}</p>
                                    @if($h['aciklama'])
                                        <p class="text-xs text-[#9CA3AF] mt-0.5">{{ $h['aciklama'] }}</p>
                                    @endif
                                    @if($h['kalemler']->count())
                                        <div class="mt-2 space-y-1">
                                            @foreach($h['kalemler'] as $k)
                                                <div class="flex items-center gap-2 text-[11px] text-[#6B7280]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
                                                    <span>{{ $k->tarih?->format('d.m.Y') }}</span>
                                                    <span class="text-[#D1D5DB]">·</span>
                                                    <span>{{ ucfirst(str_replace('_', ' ', $k->odeme_yontemi)) }}</span>
                                                    @if($k->not)
                                                        <span class="text-[#D1D5DB]">·</span>
                                                        <span class="truncate">{{ $k->not }}</span>
                                                    @endif
                                                    <span class="font-bold text-emerald-600 ml-auto whitespace-nowrap">+{{ number_format($k->tutar, 2, ',', '.') }} ₺</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-[#4B5563] whitespace-nowrap align-top">
                                    {{ number_format($h['tutar'], 2, ',', '.') }} ₺
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-emerald-600 whitespace-nowrap align-top">
                                    {{ number_format($h['odenen'], 2, ',', '.') }} ₺
                                </td>
                                <td class="px-5 py-3.5 text-right font-bold whitespace-nowrap align-top {{ $h['kalan'] > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ number_format($h['kalan'], 2, ',', '.') }} ₺
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ───── MODAL: Borç Ekle ───── --}}
    <div id="borcModal" class="fixed inset-0 z-50 hidden items-center justify-center">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="modalKapat('borcModal')"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
            <div class="bg-[#C96A2B] px-6 py-4 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white font-display">Borç Ekle</h3>
                <button onclick="modalKapat('borcModal')" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('hekim.finans.hasta-borc', $hasta->id) }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-[11px] font-bold text-[#6B7280] uppercase mb-1.5">Toplam Tutar (₺) *</label>
                        <input type="number" step="0.01" min="0.01" name="tutar" required
                               class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 bg-[#FAFAFA] py-2.5">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-[11px] font-bold text-[#6B7280] uppercase mb-1.5">Tarih *</label>
                        <input type="date" name="odeme_tarihi" required value="{{ now()->toDateString() }}"
                               class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 bg-[#FAFAFA] py-2.5">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#6B7280] uppercase mb-1.5">Hizmet</label>
                    <select name="hizmet_id" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 bg-[#FAFAFA] py-2.5">
                        <option value="">— Seçiniz —</option>
                        @foreach($hizmetler as $hz)
                            <option value="{{ $hz->id }}">{{ $hz->ad }}{{ $hz->fiyat ? ' ('.number_format($hz->fiyat, 0, ',', '.').' ₺)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#6B7280] uppercase mb-1.5">Açıklama</label>
                    <input type="text" name="aciklama" placeholder="Opsiyonel not..."
                           class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 bg-[#FAFAFA] py-2.5">
                </div>
                <div class="pt-4 border-t border-[#F3F4F6]">
                    <p class="text-[11px] font-bold text-[#9CA3AF] uppercase mb-3">İlk Ödeme (Opsiyonel)</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-[#6B7280] uppercase mb-1.5">Tutar (₺)</label>
                            <input type="number" step="0.01" min="0" name="ilk_odeme_tutar" value="0"
                                   class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 bg-[#FAFAFA] py-2.5">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-[#6B7280] uppercase mb-1.5">Yöntem</label>
                            <select name="ilk_odeme_yontemi" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 bg-[#FAFAFA] py-2.5">
                                <option value="nakit">Nakit</option>
                                <option value="kredi_karti">Kredi Kartı</option>
                                <option value="havale">Havale</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="modalKapat('borcModal')"
                            class="px-4 py-2 text-xs font-bold text-[#374151] bg-white border border-[#E5E7EB] hover:bg-[#F3F4F6] rounded-xl transition-colors">İptal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-[#C96A2B] hover:bg-[#b05c24] rounded-xl transition-colors">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ───── MODAL: Tahsilat Al ───── --}}
    <div id="tahsilatModal" class="fixed inset-0 z-50 hidden items-center justify-center">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="modalKapat('tahsilatModal')"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
            <div class="bg-emerald-600 px-6 py-4 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white font-display">Tahsilat Al</h3>
                <button onclick="modalKapat('tahsilatModal')" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('hekim.finans.hasta-tahsilat', $hasta->id) }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-[#6B7280] uppercase mb-1.5">Açık Borç *</label>
                    <select name="odeme_id" required
                            class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-emerald-500 focus:ring focus:ring-emerald-500/10 bg-[#FAFAFA] py-2.5">
                        @foreach($acikFaturalar as $f)
                            @php $k = max(0, (float)$f->tutar - (float)$f->odenen_tutar); @endphp
                            <option value="{{ $f->id }}">
                                #{{ $f->id }} · {{ $f->hizmet?->ad ?? ($f->aciklama ?: 'Kayıt') }} · Kalan {{ number_format($k, 2, ',', '.') }} ₺
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-[#6B7280] uppercase mb-1.5">Tutar (₺) *</label>
                        <input type="number" step="0.01" min="0.01" name="tutar" required
                               class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-emerald-500 focus:ring focus:ring-emerald-500/10 bg-[#FAFAFA] py-2.5">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#6B7280] uppercase mb-1.5">Tarih *</label>
                        <input type="date" name="tarih" required value="{{ now()->toDateString() }}"
                               class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-emerald-500 focus:ring focus:ring-emerald-500/10 bg-[#FAFAFA] py-2.5">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#6B7280] uppercase mb-1.5">Ödeme Yöntemi *</label>
                    <select name="odeme_yontemi" required
                            class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-emerald-500 focus:ring focus:ring-emerald-500/10 bg-[#FAFAFA] py-2.5">
                        <option value="nakit">Nakit</option>
                        <option value="kredi_karti">Kredi Kartı</option>
                        <option value="havale">Havale</option>
                        <option value="online">Online</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#6B7280] uppercase mb-1.5">Not</label>
                    <input type="text" name="not" placeholder="Opsiyonel..."
                           class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-emerald-500 focus:ring focus:ring-emerald-500/10 bg-[#FAFAFA] py-2.5">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="modalKapat('tahsilatModal')"
                            class="px-4 py-2 text-xs font-bold text-[#374151] bg-white border border-[#E5E7EB] hover:bg-[#F3F4F6] rounded-xl transition-colors">İptal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors">Tahsilatı Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function modalAc(id) {
            const el = document.getElementById(id);
            el.classList.remove('hidden');
            el.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }
        function modalKapat(id) {
            const el = document.getElementById(id);
            el.classList.add('hidden');
            el.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') { ['borcModal','tahsilatModal'].forEach(modalKapat); } });

        @if($errors->any())
            @if(old('tutar') !== null && old('odeme_id') !== null)
                modalAc('tahsilatModal');
            @elseif(old('tutar') !== null)
                modalAc('borcModal');
            @endif
        @endif
    </script>
@endsection
