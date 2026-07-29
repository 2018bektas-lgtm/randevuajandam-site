@extends('yonetim.layout')

@section('baslik', 'Web Siteleri & Domain - Randevu Ajandam')
@section('sayfa_baslik', 'Web siteleri / domain')

@section('icerik')
<div class="mb-6">
    <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
        <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
        Hekim &amp; klinik web siteleri
    </h2>
    <p class="text-xs text-[#6B7280] mt-1.5 ml-4">
        Domain siparişleri ve kurulu web sitesi kayıtları (salt okunur operasyon görünümü).
    </p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <div class="p-4 rounded-2xl bg-white border border-[#E5E7EB] text-center">
        <div class="text-[10px] font-bold uppercase text-slate-400 font-display">Hekim sitesi</div>
        <div class="text-2xl font-bold text-slate-900 font-display mt-1">{{ $ozet['hekim_site'] }}</div>
    </div>
    <div class="p-4 rounded-2xl bg-white border border-[#E5E7EB] text-center">
        <div class="text-[10px] font-bold uppercase text-slate-400 font-display">Klinik sitesi</div>
        <div class="text-2xl font-bold text-slate-900 font-display mt-1">{{ $ozet['klinik_site'] }}</div>
    </div>
    <div class="p-4 rounded-2xl bg-white border border-[#E5E7EB] text-center">
        <div class="text-[10px] font-bold uppercase text-slate-400 font-display">Domain sipariş</div>
        <div class="text-2xl font-bold text-slate-900 font-display mt-1">{{ $ozet['domain_toplam'] }}</div>
    </div>
    <div class="p-4 rounded-2xl bg-white border border-amber-100 text-center">
        <div class="text-[10px] font-bold uppercase text-amber-700 font-display">Hatalı / bekleyen</div>
        <div class="text-2xl font-bold text-amber-800 font-display mt-1">{{ $ozet['domain_sorunlu'] }}</div>
    </div>
</div>

{{-- Domain siparişleri --}}
<div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden mb-8">
    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-sm font-bold font-display text-slate-900">Domain siparişleri</h3>
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="durum" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold">
                <option value="">Tüm durumlar</option>
                @foreach(['draft','purchasing','active','dns_pending','failed'] as $d)
                    <option value="{{ $d }}" @selected(request('durum') === $d)>{{ $d }}</option>
                @endforeach
            </select>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Domain ara..."
                   class="px-3 py-2 rounded-xl border border-slate-200 text-xs w-40">
            <button class="px-3 py-2 rounded-xl bg-[#C96A2B] text-white text-xs font-bold">Filtrele</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 border-b text-[10px] uppercase tracking-wider text-slate-500 font-display">
                <tr>
                    <th class="px-4 py-3">Domain</th>
                    <th class="px-4 py-3">Sahip</th>
                    <th class="px-4 py-3">Kaynak</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3">Paket</th>
                    <th class="px-4 py-3">Tarih</th>
                    <th class="px-4 py-3">Not / hata</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($domainOrders as $ord)
                    @php
                        $ownerLabel = '—';
                        if ($ord->owner_type && str_contains($ord->owner_type, 'Doktor') && $ord->owner) {
                            $ownerLabel = trim(($ord->owner->unvan ?? '').' '.($ord->owner->ad_soyad ?? '')).' (hekim #'.$ord->owner_id.')';
                        } elseif ($ord->owner_type && str_contains($ord->owner_type, 'Klinik') && $ord->owner) {
                            $ownerLabel = ($ord->owner->ad ?? 'Klinik').' (klinik #'.$ord->owner_id.')';
                        } elseif ($ord->owner_id) {
                            $ownerLabel = class_basename((string) $ord->owner_type).' #'.$ord->owner_id;
                        }
                    @endphp
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            @if($ord->domain)
                                <a href="https://{{ ltrim(preg_replace('#^https?://#i', '', $ord->domain), '/') }}" target="_blank" rel="noopener" class="text-[#C96A2B] hover:underline">
                                    {{ $ord->domain }}
                                </a>
                            @else
                                —
                            @endif
                            @if($ord->tld)
                                <span class="block text-[10px] text-slate-400">.{{ ltrim($ord->tld, '.') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $ownerLabel }}</td>
                        <td class="px-4 py-3">{{ $ord->kaynak }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border
                                @if($ord->durum === 'active') bg-emerald-50 text-emerald-800 border-emerald-100
                                @elseif($ord->durum === 'failed') bg-red-50 text-red-700 border-red-100
                                @elseif(in_array($ord->durum, ['purchasing','dns_pending','draft'], true)) bg-amber-50 text-amber-900 border-amber-100
                                @else bg-slate-100 text-slate-600 border-slate-200 @endif">
                                {{ $ord->durum }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $ord->paket?->ad ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                            {{ $ord->created_at?->format('d.m.Y H:i') }}
                            @if($ord->registered_at)
                                <span class="block text-[10px]">Kayıt: {{ $ord->registered_at->format('d.m.Y') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500 max-w-[220px]">
                            <span class="line-clamp-2" title="{{ $ord->error_message ?: $ord->dns_check_message }}">
                                {{ $ord->error_message ?: $ord->dns_check_message ?: '—' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-400">Domain siparişi yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($domainOrders, 'links'))
        <div class="px-4 py-3 border-t border-slate-100">{{ $domainOrders->links() }}</div>
    @endif
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    {{-- Hekim web --}}
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold font-display text-slate-900">Hekim web siteleri</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-display">
                    <tr>
                        <th class="px-4 py-2.5">Domain</th>
                        <th class="px-4 py-2.5">Hekim</th>
                        <th class="px-4 py-2.5">Tema</th>
                        <th class="px-4 py-2.5">Durum</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($hekimSiteleri as $s)
                        <tr>
                            <td class="px-4 py-2.5 font-semibold">
                                @if($s->domain)
                                    <a class="text-[#C96A2B] hover:underline" href="https://{{ ltrim(preg_replace('#^https?://#i', '', $s->domain), '/') }}" target="_blank" rel="noopener">{{ $s->domain }}</a>
                                @else — @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @if($s->doktor)
                                    <a href="{{ route('yonetim.doktorlar.duzenle', $s->doktor->id) }}" class="hover:text-[#C96A2B] font-semibold">
                                        {{ $s->doktor->ad_soyad }}
                                    </a>
                                @else
                                    #{{ $s->doktor_id }}
                                @endif
                            </td>
                            <td class="px-4 py-2.5">{{ $s->tema }}</td>
                            <td class="px-4 py-2.5">
                                <span class="text-[10px] font-bold {{ $s->durum === 'aktif' || $s->durum === 'active' ? 'text-emerald-700' : 'text-amber-700' }}">{{ $s->durum }}</span>
                                @if($s->hata_mesaji)
                                    <span class="block text-[10px] text-red-600 line-clamp-1" title="{{ $s->hata_mesaji }}">{{ $s->hata_mesaji }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Kayıt yok</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Klinik web --}}
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold font-display text-slate-900">Klinik web siteleri</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-display">
                    <tr>
                        <th class="px-4 py-2.5">Domain</th>
                        <th class="px-4 py-2.5">Klinik</th>
                        <th class="px-4 py-2.5">Tema</th>
                        <th class="px-4 py-2.5">Durum</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($klinikSiteleri as $s)
                        <tr>
                            <td class="px-4 py-2.5 font-semibold">
                                @if($s->domain)
                                    <a class="text-[#C96A2B] hover:underline" href="{{ method_exists($s, 'siteUrl') ? $s->siteUrl() : 'https://'.$s->domain }}" target="_blank" rel="noopener">{{ $s->domain }}</a>
                                @else — @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @if($s->klinik)
                                    <a href="{{ route('yonetim.klinikler.duzenle', $s->klinik->id) }}" class="hover:text-[#C96A2B] font-semibold">{{ $s->klinik->ad }}</a>
                                @else
                                    #{{ $s->klinik_id }}
                                @endif
                            </td>
                            <td class="px-4 py-2.5">{{ $s->tema }}</td>
                            <td class="px-4 py-2.5">
                                <span class="text-[10px] font-bold {{ in_array($s->durum, ['aktif','active'], true) ? 'text-emerald-700' : 'text-amber-700' }}">{{ $s->durum }}</span>
                                @if($s->hata_mesaji)
                                    <span class="block text-[10px] text-red-600 line-clamp-1">{{ $s->hata_mesaji }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Kayıt yok</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
