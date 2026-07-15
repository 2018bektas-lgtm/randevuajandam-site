@extends('yonetim.layout')

@section('baslik', 'Yönetim Paneli - Randevu Ajandam')
@section('sayfa_baslik', 'Panel Özeti')

@section('icerik')
    <div class="mb-8 p-6 sm:p-8 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight">Tekrar hoş geldiniz, {{ $yonetici->ad_soyad }}</h2>
        <p class="text-sm text-[#6B7280] mt-1.5">Platform geneli özet. Bu panel hekim paneli değil; yönetici operasyon merkezidir.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <span class="text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Aktif hekim</span>
            <span class="text-3xl font-bold font-display text-[#111827] mt-2 block">{{ $stats['doktor_aktif'] }}</span>
            <span class="text-[11px] text-[#6B7280] mt-1 block">Toplam {{ $stats['doktor_toplam'] }} kayıt</span>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <span class="text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Klinik</span>
            <span class="text-3xl font-bold font-display text-[#111827] mt-2 block">{{ $stats['klinik_toplam'] }}</span>
            <span class="text-[11px] text-[#6B7280] mt-1 block">Kayıtlı klinik</span>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <span class="text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Hasta</span>
            <span class="text-3xl font-bold font-display text-[#111827] mt-2 block">{{ $stats['hasta_toplam'] }}</span>
            <span class="text-[11px] text-[#6B7280] mt-1 block">Tüm platform</span>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <span class="text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Toplam randevu</span>
            <span class="text-3xl font-bold font-display text-[#111827] mt-2 block">{{ $stats['randevu_toplam'] }}</span>
            <span class="text-[11px] text-[#C96A2B] mt-1 block font-medium">Bugün: {{ $stats['randevu_bugun'] }}</span>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-10">
        <a href="{{ route('yonetim.randevular', ['durum' => 'beklemede']) }}" class="p-5 rounded-2xl bg-amber-50/80 border border-amber-100 hover:border-amber-200 transition-colors">
            <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider font-display">Bekleyen randevu</span>
            <span class="text-2xl font-bold font-display text-amber-900 mt-2 block">{{ $stats['randevu_beklemede'] }}</span>
        </a>
        <a href="{{ route('yonetim.yorumlar.index') }}" class="p-5 rounded-2xl bg-sky-50/80 border border-sky-100 hover:border-sky-200 transition-colors">
            <span class="text-[10px] font-bold text-sky-800 uppercase tracking-wider font-display">Bekleyen yorum</span>
            <span class="text-2xl font-bold font-display text-sky-900 mt-2 block">{{ $stats['yorum_beklemede'] }}</span>
        </a>
        <a href="{{ route('yonetim.uyelikler', ['filtre' => 'yakinda']) }}" class="p-5 rounded-2xl bg-orange-50/80 border border-orange-100 hover:border-orange-200 transition-colors">
            <span class="text-[10px] font-bold text-orange-800 uppercase tracking-wider font-display">Üyelik 7 gün içinde</span>
            <span class="text-2xl font-bold font-display text-orange-900 mt-2 block">{{ $stats['uyelik_biten_7gun'] }}</span>
        </a>
        <a href="{{ route('yonetim.uyelikler', ['filtre' => 'biten']) }}" class="p-5 rounded-2xl bg-red-50/80 border border-red-100 hover:border-red-200 transition-colors">
            <span class="text-[10px] font-bold text-red-800 uppercase tracking-wider font-display">Süresi dolmuş (aktif)</span>
            <span class="text-2xl font-bold font-display text-red-900 mt-2 block">{{ $stats['uyelik_suresi_dolmus'] }}</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        <div class="lg:col-span-2 p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold font-display text-[#111827]">Son randevular</h3>
                <a href="{{ route('yonetim.randevular') }}" class="text-xs font-bold text-[#C96A2B] hover:underline font-display">Tümü →</a>
            </div>
            @if($sonRandevular->isEmpty())
                <p class="text-sm text-slate-500 py-8 text-center">Henüz randevu kaydı yok.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-wider text-slate-400 font-display border-b border-slate-100">
                                <th class="pb-2 pr-3">Hasta</th>
                                <th class="pb-2 pr-3">Hekim</th>
                                <th class="pb-2 pr-3">Tarih</th>
                                <th class="pb-2">Durum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($sonRandevular as $r)
                                <tr>
                                    <td class="py-2.5 pr-3 font-semibold text-slate-800">{{ $r->ad }} {{ $r->soyad }}</td>
                                    <td class="py-2.5 pr-3 text-slate-600">{{ $r->doktor?->ad_soyad ?? '—' }}</td>
                                    <td class="py-2.5 pr-3 text-slate-600 whitespace-nowrap">
                                        {{ $r->tarih?->format('d.m.Y') }} {{ \Illuminate\Support\Str::substr($r->saat, 0, 5) }}
                                    </td>
                                    <td class="py-2.5">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold
                                            @if($r->durum === 'beklemede') bg-amber-50 text-amber-800
                                            @elseif($r->durum === 'onaylandi') bg-emerald-50 text-emerald-800
                                            @elseif($r->durum === 'iptal') bg-red-50 text-red-700
                                            @else bg-slate-100 text-slate-600 @endif">{{ $r->durum }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-sm font-bold font-display text-[#111827] mb-4 uppercase tracking-wider">Hızlı erişim</h3>
                <div class="space-y-2">
                    <a href="{{ route('yonetim.randevular') }}" class="flex items-center gap-3 p-3 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:border-[#C96A2B] text-sm font-semibold text-[#111827] transition-colors">
                        Randevular
                    </a>
                    <a href="{{ route('yonetim.hastalar') }}" class="flex items-center gap-3 p-3 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:border-[#C96A2B] text-sm font-semibold text-[#111827] transition-colors">
                        Hastalar
                    </a>
                    <a href="{{ route('yonetim.uyelikler') }}" class="flex items-center gap-3 p-3 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:border-[#C96A2B] text-sm font-semibold text-[#111827] transition-colors">
                        Üyelikler / abonelik
                    </a>
                    <a href="{{ route('yonetim.doktorlar.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:border-[#C96A2B] text-sm font-semibold text-[#111827] transition-colors">
                        Doktor yönetimi
                    </a>
                    <a href="{{ route('yonetim.paketler.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:border-[#C96A2B] text-sm font-semibold text-[#111827] transition-colors">
                        Paketler
                    </a>
                    <a href="{{ route('yonetim.yorumlar.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:border-[#C96A2B] text-sm font-semibold text-[#111827] transition-colors">
                        Yorum moderasyonu
                    </a>
                </div>
            </div>

            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm text-xs text-slate-600 space-y-2">
                <p><span class="font-bold text-slate-800">Aktif paket tanımı:</span> {{ $stats['paket_aktif'] }}</p>
                <p><span class="font-bold text-slate-800">Aktif blog:</span> {{ $stats['blog_aktif'] }}</p>
                <p><span class="font-bold text-slate-800">Vitrinden gizli hekim:</span> {{ $stats['platform_gizli'] }}</p>
            </div>
        </div>
    </div>

    <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold font-display text-[#111827]">Son kayıt olan hekimler</h3>
            <a href="{{ route('yonetim.doktorlar.index') }}" class="text-xs font-bold text-[#C96A2B] hover:underline font-display">Tümü →</a>
        </div>
        @if($sonDoktorlar->isEmpty())
            <p class="text-sm text-slate-500">Henüz hekim yok.</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($sonDoktorlar as $d)
                    <li class="py-3 flex items-center justify-between gap-3 text-sm">
                        <div>
                            <span class="font-bold text-slate-900 font-display">{{ $d->unvan }} {{ $d->ad_soyad }}</span>
                            <span class="block text-[11px] text-slate-500">{{ $d->e_posta }} · {{ $d->paket?->ad ?? 'Paketsiz' }}</span>
                        </div>
                        <a href="{{ route('yonetim.doktorlar.duzenle', $d->id) }}" class="text-xs font-bold text-[#C96A2B] hover:underline shrink-0">Düzenle</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
