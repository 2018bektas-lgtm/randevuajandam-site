@extends('hekim.layout')
@section('baslik', 'Eğitimler - Hekim Paneli')
@section('sayfa_baslik', 'Eğitimlerim')

@section('icerik')
@php
    $durumEtiket = ['taslak' => 'Taslak', 'yayinda' => 'Yayında', 'arsiv' => 'Arşiv'];
    $tipEtiket = ['yuz_yuze' => 'Yüz yüze', 'online' => 'Online', 'hibrit' => 'Hibrit'];
@endphp

<div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
    <div>
        <h2 class="text-xl font-extrabold font-display text-slate-900 tracking-tight">Eğitimlerim</h2>
        <p class="mt-1 text-sm text-slate-500 max-w-xl">
            Kurs / webinar vitrini. Başvurular siteden gelir; ödeme sizin kanalınızdan — “Ödeme alındı” deyince finansa yazılır.
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('hekim.egitimler.basvurular.tumu') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-sky-200 bg-sky-50 text-sky-800 text-xs font-bold hover:bg-sky-100 transition">
            Tüm başvurular
        </a>
        <a href="{{ route('hekim.egitimler.create') }}"
           class="inline-flex justify-center px-5 py-2.5 rounded-xl bg-[#C96A2B] text-white text-xs font-bold uppercase tracking-wider font-display hover:bg-[#B55A20] transition shadow-sm">
            + Yeni Eğitim
        </a>
    </div>
</div>

@if(session('basarili'))
    <div class="mb-4 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-100 text-sm text-emerald-800">{{ session('basarili') }}</div>
@endif

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 font-display">
                <tr>
                    <th class="px-5 py-3">Eğitim</th>
                    <th class="px-5 py-3">Durum</th>
                    <th class="px-5 py-3">Başvurular</th>
                    <th class="px-5 py-3">Fiyat</th>
                    <th class="px-5 py-3 text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($egitimler as $e)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900 font-display">{{ $e->baslik }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $tipEtiket[$e->tip] ?? $e->tip }}
                                · {{ $e->baslangic_at?->format('d.m.Y H:i') ?? 'Tarih yok' }}
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border
                                @if($e->durum==='yayinda') bg-emerald-50 text-emerald-800 border-emerald-100
                                @elseif($e->durum==='taslak') bg-amber-50 text-amber-800 border-amber-100
                                @else bg-slate-100 text-slate-600 border-slate-200 @endif">
                                {{ $durumEtiket[$e->durum] ?? $e->durum }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('hekim.egitimler.basvurular', $e->id) }}"
                               class="inline-flex flex-col gap-0.5 group">
                                <span class="font-extrabold text-slate-900 group-hover:text-[#C96A2B] text-sm">
                                    {{ $e->basvurular_count }} başvuru
                                </span>
                                @if($e->bekleyen_basvuru)
                                    <span class="text-[10px] font-bold text-amber-700">
                                        {{ $e->bekleyen_basvuru }} beklemede →
                                    </span>
                                @else
                                    <span class="text-[10px] text-slate-400 group-hover:text-[#C96A2B]">Görüntüle →</span>
                                @endif
                            </a>
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-slate-700">
                            @if($e->fiyat === null || (float) $e->fiyat <= 0)
                                Ücretsiz
                            @else
                                {{ number_format((float) $e->fiyat, 2, ',', '.') }} ₺
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <a href="{{ route('hekim.egitimler.basvurular', $e->id) }}"
                               class="inline-flex px-2.5 py-1.5 rounded-lg bg-sky-50 text-sky-800 border border-sky-100 font-bold hover:bg-sky-100">
                                Başvurular
                            </a>
                            <a href="{{ route('hekim.egitimler.edit', $e->id) }}"
                               class="inline-flex px-2.5 py-1.5 rounded-lg bg-[#FFF7ED] text-[#C96A2B] border border-[#FED7AA] font-bold hover:bg-[#FFEDD5] ml-1">
                                Düzenle
                            </a>
                            @if($e->durum === 'yayinda' && ! empty($e->url))
                                <a href="{{ $e->url }}" target="_blank"
                                   class="inline-flex px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-500 font-bold hover:bg-slate-50 ml-1">
                                    Public
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-14 text-center">
                            <div class="text-slate-800 font-extrabold font-display">Henüz eğitim yok</div>
                            <p class="text-slate-500 text-sm mt-1">İlk eğitiminizi oluşturup yayınlayın.</p>
                            <a href="{{ route('hekim.egitimler.create') }}"
                               class="inline-flex mt-4 px-4 py-2 rounded-xl bg-[#C96A2B] text-white text-xs font-bold">
                                + Yeni Eğitim
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($egitimler->hasPages())
        <div class="p-4 border-t">{{ $egitimler->links() }}</div>
    @endif
</div>
@endsection
