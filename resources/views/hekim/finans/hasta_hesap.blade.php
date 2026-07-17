@extends('hekim.layout')

@section('baslik', $hasta->ad_soyad.' — Hasta Hesabı')
@section('sayfa_baslik', 'Hasta Hesabı')

@section('icerik')
    {{-- Nav --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('hekim.finans.index') }}" class="px-4 py-2 rounded-xl text-sm font-semibold bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]">📊 Genel Bakış</a>
            <a href="{{ route('hekim.finans.gelirler') }}" class="px-4 py-2 rounded-xl text-sm font-semibold bg-[#FAFAFA] text-[#4B5563] hover:bg-[#F3F4F6]">💵 Gelirler</a>
            <a href="{{ route('hekim.finans.hasta-bakiyeleri') }}" class="px-4 py-2 rounded-xl text-sm font-semibold bg-[#C96A2B] text-white shadow-sm">👥 Hasta Bakiyeleri</a>
        </div>
        <a href="{{ route('hekim.finans.hasta-bakiyeleri') }}" class="text-sm font-semibold text-[#C96A2B] hover:underline">← Bakiye listesine dön</a>
    </div>

    @if(session('basarili'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">{{ session('basarili') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Patient header --}}
    <div class="mb-6 p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-[#9CA3AF]">Doktor hesabı · cari</p>
            <h2 class="text-2xl font-bold font-display text-[#111827] mt-1">{{ $hasta->ad_soyad }}</h2>
            <p class="text-sm text-[#6B7280] mt-1">
                {{ $hasta->telefon ?? 'Telefon yok' }}
                @if($hasta->e_posta) · {{ $hasta->e_posta }} @endif
            </p>
        </div>
        <div class="text-right">
            <p class="text-xs font-bold text-[#9CA3AF] uppercase">Kalan bakiye</p>
            <p class="text-3xl font-bold font-display {{ $kalanBakiye > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                {{ number_format($kalanBakiye, 2, ',', '.') }} ₺
            </p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <span class="text-xs font-bold text-[#6B7280] uppercase">Toplam borç</span>
            <p class="text-2xl font-bold text-[#111827] mt-1">{{ number_format($toplamBorc, 2, ',', '.') }} ₺</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <span class="text-xs font-bold text-[#6B7280] uppercase">Ödenen</span>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($toplamOdenen, 2, ',', '.') }} ₺</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <span class="text-xs font-bold text-[#6B7280] uppercase">Kalan</span>
            <p class="text-2xl font-bold {{ $kalanBakiye > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">{{ number_format($kalanBakiye, 2, ',', '.') }} ₺</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
        {{-- Left: timeline --}}
        <div class="xl:col-span-3 space-y-4">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-4">Hesap hareketleri</h3>
                @forelse($hareketler as $h)
                    <div class="border border-[#E5E7EB] rounded-xl p-4 mb-3 last:mb-0">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <p class="text-xs text-[#9CA3AF] font-semibold">{{ \Carbon\Carbon::parse($h['tarih'])->format('d.m.Y') }}</p>
                                <p class="font-semibold text-[#111827] mt-0.5">{{ $h['baslik'] }}</p>
                                @if($h['aciklama'])
                                    <p class="text-xs text-[#6B7280] mt-1">{{ $h['aciklama'] }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-[#4B5563]">Borç: {{ number_format($h['tutar'], 2, ',', '.') }} ₺</p>
                                <p class="text-sm font-semibold text-emerald-600">Ödenen: {{ number_format($h['odenen'], 2, ',', '.') }} ₺</p>
                                <p class="text-sm font-bold {{ $h['kalan'] > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                                    Kalan: {{ number_format($h['kalan'], 2, ',', '.') }} ₺
                                </p>
                                <span class="inline-block mt-1 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full
                                    @if($h['durum']==='odendi') bg-emerald-50 text-emerald-700
                                    @elseif($h['durum']==='kismi_odeme') bg-amber-50 text-amber-700
                                    @elseif($h['durum']==='iptal') bg-gray-100 text-gray-500
                                    @else bg-rose-50 text-rose-700 @endif">
                                    {{ str_replace('_', ' ', $h['durum']) }}
                                </span>
                            </div>
                        </div>
                        @if($h['kalemler']->count())
                            <div class="mt-3 pt-3 border-t border-dashed border-[#E5E7EB]">
                                <p class="text-[11px] font-bold text-[#9CA3AF] uppercase mb-2">Parça ödemeler</p>
                                <ul class="space-y-1.5">
                                    @foreach($h['kalemler'] as $k)
                                        <li class="flex justify-between text-xs text-[#4B5563]">
                                            <span>{{ $k->tarih?->format('d.m.Y') }} · {{ $k->odeme_yontemi }} @if($k->not) — {{ $k->not }} @endif</span>
                                            <span class="font-bold text-emerald-600">{{ number_format($k->tutar, 2, ',', '.') }} ₺</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-[#6B7280] text-center py-8">Bu hastaya ait finans hareketi yok. Sağdan borç veya tahsilat ekleyebilirsiniz.</p>
                @endforelse
            </div>
        </div>

        {{-- Right: actions --}}
        <div class="xl:col-span-2 space-y-4">
            {{-- Tahsilat --}}
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-1">Tahsilat al</h3>
                <p class="text-xs text-[#6B7280] mb-4">Parça veya tam ödeme — açık faturaya yazılır.</p>
                @if($acikFaturalar->isEmpty())
                    <p class="text-sm text-[#9CA3AF]">Açık fatura yok. Önce borç ekleyin veya randevuyu tamamlayın.</p>
                @else
                    <form method="POST" action="{{ route('hekim.finans.hasta-tahsilat', $hasta->id) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-[#6B7280] mb-1">Açık fatura</label>
                            <select name="odeme_id" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring-[#C96A2B]/20 bg-[#FAFAFA]">
                                @foreach($acikFaturalar as $f)
                                    @php $k = max(0, (float)$f->tutar - (float)$f->odenen_tutar); @endphp
                                    <option value="{{ $f->id }}">
                                        #{{ $f->id }} · {{ $f->hizmet?->ad ?? ($f->aciklama ?: 'Fatura') }}
                                        · kalan {{ number_format($k, 2, ',', '.') }} ₺
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-[#6B7280] mb-1">Tutar (₺)</label>
                                <input type="number" step="0.01" min="0.01" name="tutar" required value="{{ old('tutar') }}"
                                       class="w-full text-sm rounded-xl border-[#E5E7EB] bg-[#FAFAFA] focus:border-[#C96A2B] focus:ring-[#C96A2B]/20">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#6B7280] mb-1">Tarih</label>
                                <input type="date" name="tarih" required value="{{ old('tarih', now()->toDateString()) }}"
                                       class="w-full text-sm rounded-xl border-[#E5E7EB] bg-[#FAFAFA] focus:border-[#C96A2B] focus:ring-[#C96A2B]/20">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#6B7280] mb-1">Yöntem</label>
                            <select name="odeme_yontemi" class="w-full text-sm rounded-xl border-[#E5E7EB] bg-[#FAFAFA] focus:border-[#C96A2B] focus:ring-[#C96A2B]/20">
                                <option value="nakit">Nakit</option>
                                <option value="kredi_karti">Kredi kartı</option>
                                <option value="havale">Havale</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#6B7280] mb-1">Not</label>
                            <input type="text" name="not" value="{{ old('not') }}" placeholder="Opsiyonel"
                                   class="w-full text-sm rounded-xl border-[#E5E7EB] bg-[#FAFAFA] focus:border-[#C96A2B] focus:ring-[#C96A2B]/20">
                        </div>
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-[#C96A2B] text-white text-sm font-bold hover:bg-[#b05c24]">
                            Tahsilatı kaydet
                        </button>
                    </form>
                @endif
            </div>

            {{-- Yeni borç --}}
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-1">Borç ekle</h3>
                <p class="text-xs text-[#6B7280] mb-4">Manuel fatura (randevu dışı tedavi vb.).</p>
                <form method="POST" action="{{ route('hekim.finans.hasta-borc', $hasta->id) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-[#6B7280] mb-1">Toplam tutar (₺)</label>
                        <input type="number" step="0.01" min="0.01" name="tutar" required class="w-full text-sm rounded-xl border-[#E5E7EB] bg-[#FAFAFA] focus:border-[#C96A2B] focus:ring-[#C96A2B]/20">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#6B7280] mb-1">Tarih</label>
                        <input type="date" name="odeme_tarihi" required value="{{ now()->toDateString() }}" class="w-full text-sm rounded-xl border-[#E5E7EB] bg-[#FAFAFA] focus:border-[#C96A2B] focus:ring-[#C96A2B]/20">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#6B7280] mb-1">Hizmet (opsiyonel)</label>
                        <select name="hizmet_id" class="w-full text-sm rounded-xl border-[#E5E7EB] bg-[#FAFAFA] focus:border-[#C96A2B] focus:ring-[#C96A2B]/20">
                            <option value="">—</option>
                            @foreach($hizmetler as $hz)
                                <option value="{{ $hz->id }}">{{ $hz->ad }} @if($hz->fiyat) ({{ number_format($hz->fiyat, 0, ',', '.') }} ₺) @endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#6B7280] mb-1">Açıklama</label>
                        <input type="text" name="aciklama" class="w-full text-sm rounded-xl border-[#E5E7EB] bg-[#FAFAFA] focus:border-[#C96A2B] focus:ring-[#C96A2B]/20">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-[#6B7280] mb-1">İlk ödeme (₺)</label>
                            <input type="number" step="0.01" min="0" name="ilk_odeme_tutar" value="0" class="w-full text-sm rounded-xl border-[#E5E7EB] bg-[#FAFAFA] focus:border-[#C96A2B] focus:ring-[#C96A2B]/20">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#6B7280] mb-1">Yöntem</label>
                            <select name="ilk_odeme_yontemi" class="w-full text-sm rounded-xl border-[#E5E7EB] bg-[#FAFAFA] focus:border-[#C96A2B] focus:ring-[#C96A2B]/20">
                                <option value="nakit">Nakit</option>
                                <option value="kredi_karti">Kart</option>
                                <option value="havale">Havale</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-xl border border-[#C96A2B] text-[#C96A2B] text-sm font-bold hover:bg-[#C96A2B]/5">
                        Borç kaydı oluştur
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
