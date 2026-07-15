@extends('hekim.layout')
@php
    $tumu = $tumu ?? false;
    $sayfaBaslik = $tumu
        ? 'Eğitim başvuruları'
        : ('Başvurular: '.($egitim->baslik ?? ''));
    $durumEtiket = [
        'beklemede' => 'Beklemede',
        'onaylandi' => 'Onaylandı',
        'reddedildi' => 'Reddedildi',
        'iptal' => 'İptal',
    ];
    $ucretEtiket = [
        'yok' => 'Ücretsiz',
        'bekliyor' => 'Ödeme bekliyor',
        'kismi' => 'Kısmi ödeme',
        'odendi' => 'Ödendi',
    ];
    $durumRenk = [
        'beklemede' => 'bg-amber-50 text-amber-800 border-amber-100',
        'onaylandi' => 'bg-emerald-50 text-emerald-800 border-emerald-100',
        'reddedildi' => 'bg-red-50 text-red-700 border-red-100',
        'iptal' => 'bg-slate-100 text-slate-600 border-slate-200',
    ];
    $ucretRenk = [
        'yok' => 'bg-slate-50 text-slate-600 border-slate-200',
        'bekliyor' => 'bg-orange-50 text-orange-800 border-orange-100',
        'kismi' => 'bg-sky-50 text-sky-800 border-sky-100',
        'odendi' => 'bg-emerald-50 text-emerald-800 border-emerald-100',
    ];
@endphp
@section('baslik', $sayfaBaslik.' - Hekim Paneli')
@section('sayfa_baslik', $sayfaBaslik)

@section('icerik')
<style>
    .eb-stat { background:#fff; border:1px solid #e7e5e4; border-radius:1rem; padding:1rem 1.15rem; }
    .eb-chip {
        display:inline-flex; align-items:center; padding:.35rem .75rem; border-radius:.65rem;
        font-size:.7rem; font-weight:800; border:1px solid transparent; text-decoration:none;
        transition:background .15s,color .15s,border-color .15s;
    }
    .eb-chip.is-on { background:#C96A2B; color:#fff; border-color:#C96A2B; }
    .eb-chip.is-off { background:#fff; color:#57534e; border-color:#e7e5e4; }
    .eb-chip.is-off:hover { border-color:#fdba74; color:#9a3412; }
    .eb-card { background:#fff; border:1px solid #e7e5e4; border-radius:1.15rem; overflow:hidden; }
    .eb-input {
        width:100%; padding:.55rem .75rem; border-radius:.75rem; border:1px solid #e7e5e4;
        font-size:.75rem; background:#fff;
    }
    .eb-input:focus { outline:none; border-color:#C96A2B; box-shadow:0 0 0 3px rgba(201,106,43,.12); }
    .eb-badge {
        display:inline-flex; align-items:center; padding:.2rem .55rem; border-radius:999px;
        font-size:.65rem; font-weight:800; border:1px solid;
    }
</style>

{{-- Header --}}
<div class="mb-6 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2 text-xs mb-2">
            <a href="{{ route('hekim.egitimler.index') }}" class="font-bold text-slate-500 hover:text-[#C96A2B] transition">← Eğitimler</a>
            @if(! $tumu && $egitim)
                <span class="text-slate-300">/</span>
                <a href="{{ route('hekim.egitimler.edit', $egitim->id) }}" class="font-semibold text-slate-600 hover:text-[#C96A2B] truncate max-w-[200px]">{{ $egitim->baslik }}</a>
                <span class="text-slate-300">/</span>
                <span class="font-semibold text-slate-800">Başvurular</span>
            @endif
        </div>
        <h2 class="text-xl sm:text-2xl font-extrabold font-display text-slate-900 tracking-tight">
            {{ $tumu ? 'Tüm eğitim başvuruları' : 'Eğitim başvuruları' }}
        </h2>
        <p class="mt-1.5 text-sm text-slate-500 max-w-2xl leading-relaxed">
            @if($tumu)
                Tüm eğitimlerinizden gelen başvurular. Durum güncelleyin, ödeme alındı deyin — finansa yansır.
            @else
                <strong class="text-slate-700">{{ $egitim->baslik }}</strong> için gelen başvurular.
            @endif
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if(! $tumu && $egitim)
            <a href="{{ route('hekim.egitimler.basvurular.tumu') }}"
               class="inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                Tüm başvurular
            </a>
            <a href="{{ route('hekim.egitimler.edit', $egitim->id) }}"
               class="inline-flex items-center px-3.5 py-2 rounded-xl border border-[#FED7AA] bg-[#FFF7ED] text-xs font-bold text-[#C96A2B] hover:bg-[#FFEDD5]">
                Eğitimi düzenle
            </a>
        @else
            <a href="{{ route('hekim.egitimler.create') }}"
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-[#C96A2B] text-white text-xs font-bold hover:bg-[#B55A20]">
                + Yeni eğitim
            </a>
        @endif
    </div>
</div>

@if(session('basarili'))
    <div class="mb-4 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-100 text-sm text-emerald-800">{{ session('basarili') }}</div>
@endif
@if(session('hata'))
    <div class="mb-4 p-3.5 rounded-2xl bg-red-50 border border-red-100 text-sm text-red-700">{{ session('hata') }}</div>
@endif

{{-- Özet kartları --}}
@php $ozet = $ozet ?? ['toplam' => 0, 'beklemede' => 0, 'onaylandi' => 0, 'odeme_bekleyen' => 0]; @endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="eb-stat">
        <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Toplam</div>
        <div class="mt-1 text-2xl font-extrabold font-display text-slate-900">{{ $ozet['toplam'] }}</div>
    </div>
    <div class="eb-stat">
        <div class="text-[10px] font-extrabold uppercase tracking-wider text-amber-600">Beklemede</div>
        <div class="mt-1 text-2xl font-extrabold font-display text-amber-800">{{ $ozet['beklemede'] }}</div>
    </div>
    <div class="eb-stat">
        <div class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600">Onaylı</div>
        <div class="mt-1 text-2xl font-extrabold font-display text-emerald-800">{{ $ozet['onaylandi'] }}</div>
    </div>
    <div class="eb-stat">
        <div class="text-[10px] font-extrabold uppercase tracking-wider text-orange-600">Ödeme bekleyen</div>
        <div class="mt-1 text-2xl font-extrabold font-display text-orange-800">{{ $ozet['odeme_bekleyen'] }}</div>
    </div>
</div>

{{-- Filtreler --}}
<div class="eb-card mb-5">
    <form method="GET" class="p-4 space-y-3">
        <div class="flex flex-wrap gap-2">
            @foreach(['' => 'Tümü', 'beklemede' => 'Beklemede', 'onaylandi' => 'Onaylı', 'reddedildi' => 'Red', 'iptal' => 'İptal'] as $k => $v)
                @php
                    $params = request()->except('page');
                    if ($k === '') unset($params['durum']); else $params['durum'] = $k;
                    $href = $tumu
                        ? route('hekim.egitimler.basvurular.tumu', $params)
                        : route('hekim.egitimler.basvurular', array_merge(['id' => $egitim->id], $params));
                @endphp
                <a href="{{ $href }}" class="eb-chip {{ request('durum', '') === $k ? 'is-on' : 'is-off' }}">{{ $v }}</a>
            @endforeach
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Ara</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Ad, telefon, e-posta..." class="eb-input">
            </div>
            @if($tumu && isset($egitimler) && $egitimler->count())
                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Eğitim</label>
                    <select name="egitim_id" class="eb-input">
                        <option value="">Tüm eğitimler</option>
                        @foreach($egitimler as $e)
                            <option value="{{ $e->id }}" @selected((string) request('egitim_id') === (string) $e->id)>{{ $e->baslik }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Ücret durumu</label>
                <select name="ucret" class="eb-input">
                    <option value="">Tümü</option>
                    @foreach($ucretEtiket as $k => $v)
                        <option value="{{ $k }}" @selected(request('ucret') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            @if(request('durum'))
                <input type="hidden" name="durum" value="{{ request('durum') }}">
            @endif
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800">Filtrele</button>
                @if(request()->hasAny(['q','durum','ucret','egitim_id']))
                    <a href="{{ $tumu ? route('hekim.egitimler.basvurular.tumu') : route('hekim.egitimler.basvurular', $egitim->id) }}"
                       class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-500 hover:bg-slate-50">Temizle</a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Liste --}}
<div class="space-y-4">
    @forelse($basvurular as $b)
        @php
            $eg = $b->egitim ?? $egitim;
            $cevaplar = is_array($b->cevaplar) ? $b->cevaplar : [];
        @endphp
        <article class="eb-card">
            <div class="p-4 sm:p-5 grid lg:grid-cols-12 gap-4">
                {{-- Katılımcı --}}
                <div class="lg:col-span-4 min-w-0">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#FFF7ED] border border-[#FED7AA] text-[#C96A2B] font-extrabold font-display text-xs flex items-center justify-center shrink-0">
                            {{ mb_strtoupper(mb_substr($b->ad ?? '?', 0, 1).mb_substr($b->soyad ?? '', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-extrabold font-display text-slate-900 truncate">{{ $b->ad_soyad }}</div>
                            <div class="text-[12px] text-slate-600 mt-0.5">
                                <a href="tel:{{ $b->telefon }}" class="hover:text-[#C96A2B] font-semibold">{{ $b->telefon }}</a>
                                @if($b->e_posta)
                                    <span class="text-slate-300">·</span>
                                    <a href="mailto:{{ $b->e_posta }}" class="hover:text-[#C96A2B]">{{ $b->e_posta }}</a>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1">{{ $b->created_at?->format('d.m.Y H:i') }}</div>
                            @if($tumu && $eg)
                                <a href="{{ route('hekim.egitimler.basvurular', $eg->id) }}"
                                   class="inline-flex mt-2 text-[11px] font-bold text-[#C96A2B] hover:underline">
                                    {{ $eg->baslik }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-1.5 mt-3">
                        <span class="eb-badge {{ $durumRenk[$b->durum] ?? 'bg-slate-50 text-slate-600 border-slate-200' }}">
                            {{ $durumEtiket[$b->durum] ?? $b->durum }}
                        </span>
                        <span class="eb-badge {{ $ucretRenk[$b->ucret_durumu] ?? 'bg-slate-50 text-slate-600 border-slate-200' }}">
                            {{ $ucretEtiket[$b->ucret_durumu] ?? $b->ucret_durumu }}
                        </span>
                        @if($b->ucret_tutari)
                            <span class="eb-badge bg-white text-slate-700 border-slate-200">
                                {{ number_format((float) $b->ucret_tutari, 2, ',', '.') }} ₺
                            </span>
                        @endif
                        @if((float) $b->odenen_tutar > 0)
                            <span class="eb-badge bg-emerald-50 text-emerald-800 border-emerald-100">
                                Ödenen {{ number_format((float) $b->odenen_tutar, 2, ',', '.') }} ₺
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Form cevapları --}}
                <div class="lg:col-span-4 min-w-0">
                    <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">Form cevapları</div>
                    @if(count($cevaplar))
                        <dl class="space-y-1.5 max-h-40 overflow-y-auto pr-1" style="scrollbar-width:thin">
                            @foreach($cevaplar as $aid => $val)
                                @php
                                    $etiket = $alanEtiketleri[$aid]->etiket ?? ('Alan #'.$aid);
                                    $goster = is_bool($val) ? ($val ? 'Evet' : 'Hayır') : (is_array($val) ? implode(', ', $val) : $val);
                                @endphp
                                <div class="rounded-lg bg-slate-50 border border-slate-100 px-2.5 py-1.5">
                                    <dt class="text-[10px] font-bold text-slate-400">{{ $etiket }}</dt>
                                    <dd class="text-[12px] font-semibold text-slate-800 break-words">{{ $goster !== '' && $goster !== null ? $goster : '—' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @else
                        <p class="text-[12px] text-slate-400">Ek form cevabı yok (yalnızca kimlik bilgileri).</p>
                    @endif
                    @if($b->hekim_notu)
                        <div class="mt-2 rounded-lg bg-amber-50 border border-amber-100 px-2.5 py-1.5">
                            <div class="text-[10px] font-bold text-amber-700">Hekim notu</div>
                            <div class="text-[12px] text-amber-900">{{ $b->hekim_notu }}</div>
                        </div>
                    @endif
                </div>

                {{-- İşlemler --}}
                <div class="lg:col-span-4 space-y-3">
                    <form method="POST" action="{{ route('hekim.egitimler.basvuru.durum', [$b->egitim_id, $b->id]) }}" class="space-y-2 p-3 rounded-xl bg-slate-50 border border-slate-100">
                        @csrf
                        <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Başvuru durumu</div>
                        <select name="durum" class="eb-input">
                            @foreach($durumEtiket as $k => $v)
                                <option value="{{ $k }}" @selected($b->durum === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                        <textarea name="hekim_notu" rows="2" class="eb-input resize-y" placeholder="İç not (opsiyonel)">{{ $b->hekim_notu }}</textarea>
                        <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-white text-[11px] font-bold hover:bg-slate-800">
                            Durumu kaydet
                        </button>
                    </form>

                    @if($b->ucret_durumu !== 'yok' && $b->ucret_durumu !== 'odendi')
                        <form method="POST" action="{{ route('hekim.egitimler.basvuru.odeme', [$b->egitim_id, $b->id]) }}"
                              class="space-y-2 p-3 rounded-xl bg-emerald-50/60 border border-emerald-100"
                              onsubmit="return confirm('Ödeme alındı olarak işaretlensin ve finansa eklensin mi?');">
                            @csrf
                            <div class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Ödeme alındı → Finans</div>
                            <input type="number" step="0.01" min="0.01" name="odenen_tutar" required
                                   value="{{ $b->ucret_tutari ?? ($eg->fiyat ?? '') }}"
                                   class="eb-input" placeholder="Tutar (₺)">
                            <input type="text" name="odeme_yontemi" class="eb-input" placeholder="Havale / nakit / kart...">
                            <button type="submit" class="w-full py-2 rounded-xl bg-emerald-600 text-white text-[11px] font-bold hover:bg-emerald-700">
                                Ödeme alındı kaydet
                            </button>
                        </form>
                    @elseif($b->ucret_durumu === 'odendi')
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-[12px] text-emerald-800 font-semibold">
                            ✓ Ödeme finansa işlendi
                            @if($b->odeme_yontemi)
                                <span class="font-normal text-emerald-700">({{ $b->odeme_yontemi }})</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="eb-card px-6 py-16 text-center">
            <div class="text-3xl mb-2 opacity-40">📋</div>
            <div class="font-extrabold font-display text-slate-800 text-lg">Henüz başvuru yok</div>
            <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                Eğitim yayındaysa public siteden gelen başvurular burada listelenir.
            </p>
            <a href="{{ route('hekim.egitimler.index') }}"
               class="inline-flex mt-4 px-4 py-2 rounded-xl bg-[#C96A2B] text-white text-xs font-bold hover:bg-[#B55A20]">
                Eğitimlerime git
            </a>
        </div>
    @endforelse
</div>

@if($basvurular->hasPages())
    <div class="mt-5">{{ $basvurular->links() }}</div>
@endif
@endsection
