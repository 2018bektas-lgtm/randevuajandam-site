@extends('hekim.layout')

@section('baslik', 'Faturalarım - Randevu Ajandam')
@section('sayfa_baslik', 'Faturalarım')

@section('icerik')
    {{-- Header --}}
    <div class="mb-6 p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold font-display text-[#111827]">Faturalarım</h2>
                <p class="text-sm text-[#6B7280] mt-0.5">
                    Randevu Ajandam'a ödediğiniz paket üyeliklerinin faturaları.
                </p>
            </div>
        </div>
        <a href="{{ route('hekim.uyelik') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-white border border-[#E5E7EB] text-[#4B5563] hover:border-[#C96A2B] hover:text-[#C96A2B] transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 8.25V6a2.25 2.25 0 00-2.25-2.25H6A2.25 2.25 0 003.75 6v8.25A2.25 2.25 0 006 16.5h2.25m8.25-8.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-7.5A2.25 2.25 0 018.25 18v-1.5m8.25-8.25h-6a2.25 2.25 0 00-2.25 2.25v6"/>
            </svg>
            Üyelik & Abonelik
        </a>
    </div>

    {{-- Özet kartlar --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            <p class="text-[11px] font-bold uppercase text-[#9CA3AF] tracking-wide">Toplam Ödeme</p>
            <p class="text-2xl font-bold text-[#111827] font-display mt-1">{{ $ozet['toplam_odeme'] }}</p>
            <p class="text-[11px] text-[#9CA3AF] mt-1">kayıt</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-emerald-100 shadow-sm bg-emerald-50/30">
            <p class="text-[11px] font-bold uppercase text-emerald-600 tracking-wide">Fatura Kesilen</p>
            <p class="text-2xl font-bold text-emerald-700 font-display mt-1">{{ $ozet['fatura_kesilen'] }}</p>
            <p class="text-[11px] text-emerald-500 mt-1">indirebilirsiniz</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-amber-100 shadow-sm bg-amber-50/30">
            <p class="text-[11px] font-bold uppercase text-amber-600 tracking-wide">Fatura Bekleyen</p>
            <p class="text-2xl font-bold text-amber-700 font-display mt-1">{{ $ozet['fatura_bekleyen'] }}</p>
            <p class="text-[11px] text-amber-500 mt-1">hazırlanıyor</p>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-[#FED7AA] shadow-sm bg-[#FFF7ED]/40">
            <p class="text-[11px] font-bold uppercase text-[#C96A2B] tracking-wide">Toplam Tutar</p>
            <p class="text-2xl font-bold text-[#C96A2B] font-display mt-1">{{ number_format($ozet['toplam_tutar'], 0, ',', '.') }} ₺</p>
            <p class="text-[11px] text-[#C96A2B]/70 mt-1">onaylı ödemeler</p>
        </div>
    </div>

    {{-- Faturalar tablosu --}}
    <div class="rounded-2xl bg-white border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-[#F3F4F6] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zM3.75 12h.007v.008H3.75V12zm.375 5.25h.007v.008H3.75v-.008z"/>
                    </svg>
                </span>
                <h3 class="text-base font-bold font-display text-[#111827]">Ödemeler ve Faturalar</h3>
            </div>
            <span class="text-xs text-[#9CA3AF]">{{ $odemeler->total() }} kayıt</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#FAFAFA] border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider">
                        <th class="px-5 py-3.5">Tarih</th>
                        <th class="px-5 py-3.5">Paket</th>
                        <th class="px-5 py-3.5">Yöntem</th>
                        <th class="px-5 py-3.5 text-right">Tutar</th>
                        <th class="px-5 py-3.5">Ödeme Durumu</th>
                        <th class="px-5 py-3.5">Fatura No</th>
                        <th class="px-5 py-3.5 text-right">Fatura</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3F4F6] text-sm text-[#111827]">
                    @forelse($odemeler as $od)
                        <tr class="hover:bg-[#FAFAFA]/60 transition-colors">
                            <td class="px-5 py-3.5 text-[#6B7280] whitespace-nowrap">
                                {{ $od->created_at?->format('d.m.Y') }}
                                <span class="block text-[10px] text-[#9CA3AF]">{{ $od->created_at?->format('H:i') }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-[#111827]">{{ $od->paket?->ad ?? '—' }}</p>
                                @if($od->odeme_periyodu)
                                    <p class="text-[11px] text-[#9CA3AF] mt-0.5 capitalize">{{ str_replace('_', ' ', $od->odeme_periyodu) }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @php
                                    $yontem = match(true) {
                                        $od->odeme_yontemi === 'havale' || $od->provider === 'banka' => ['Havale/EFT', 'bg-blue-50 text-blue-700 border-blue-200'],
                                        $od->odeme_yontemi === 'paytr' || $od->provider === 'paytr'   => ['PayTR', 'bg-indigo-50 text-indigo-700 border-indigo-200'],
                                        $od->provider === 'iyzico'                                   => ['iyzico', 'bg-purple-50 text-purple-700 border-purple-200'],
                                        default => [$od->odeme_yontemi ?? '—', 'bg-gray-50 text-gray-700 border-gray-200'],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $yontem[1] }}">{{ $yontem[0] }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-[#111827] whitespace-nowrap">
                                {{ number_format((float) $od->tutar, 2, ',', '.') }} ₺
                            </td>
                            <td class="px-5 py-3.5">
                                @php
                                    $durum = match($od->durum) {
                                        'beklemede'   => ['Beklemede',  'bg-amber-50 text-amber-800 border-amber-200'],
                                        'onaylandi'   => ['Onaylandı',  'bg-emerald-50 text-emerald-800 border-emerald-200'],
                                        'reddedildi'  => ['Reddedildi', 'bg-rose-50 text-rose-800 border-rose-200'],
                                        default       => [$od->durum,   'bg-gray-50 text-gray-700 border-gray-200'],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $durum[1] }}">{{ $durum[0] }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-[#6B7280] text-xs font-mono">
                                {{ $od->fatura_no ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($od->fatura_url)
                                    <a href="{{ str_starts_with($od->fatura_url, 'http') ? $od->fatura_url : asset($od->fatura_url) }}"
                                       target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#FFF7ED] border border-[#FED7AA] text-[#C96A2B] hover:bg-[#C96A2B] hover:text-white hover:border-[#C96A2B] text-xs font-bold transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                        </svg>
                                        Faturayı İndir
                                    </a>
                                    @if($od->fatura_kesildi_at)
                                        <p class="text-[10px] text-[#9CA3AF] mt-1">
                                            {{ $od->fatura_kesildi_at->format('d.m.Y') }}
                                        </p>
                                    @endif
                                @elseif($od->durum === 'onaylandi')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-[11px] font-bold">
                                        <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                        </svg>
                                        Hazırlanıyor
                                    </span>
                                    <p class="text-[10px] text-[#9CA3AF] mt-1">Fatura en geç 7 gün içinde iletilir</p>
                                @else
                                    <span class="text-[11px] text-[#9CA3AF]">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16">
                                <div class="flex flex-col items-center text-center">
                                    <div class="w-14 h-14 rounded-full bg-[#FAFAFA] text-[#9CA3AF] flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-[#111827]">Henüz ödeme kaydınız yok</p>
                                    <p class="text-xs text-[#6B7280] mt-1 max-w-sm mb-4">
                                        Paket satın aldığınızda ödeme ve fatura kayıtlarınız burada listelenir.
                                    </p>
                                    <a href="{{ route('frontend.hekim.paket_sec') }}"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-[#C96A2B] hover:bg-[#b05c24] text-white transition-colors">
                                        Paket Seç
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($odemeler->hasPages())
        <div class="mt-4">{{ $odemeler->links() }}</div>
    @endif

    {{-- Fatura süreç bilgisi --}}
    <div class="mt-6 p-4 rounded-xl bg-blue-50/50 border border-blue-100 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
        </svg>
        <div class="text-xs text-blue-900 leading-relaxed">
            <p class="font-bold mb-1">Fatura süreci hakkında</p>
            <p>
                Ödemeniz onaylandıktan sonra faturanız <strong>Üyelik &amp; Abonelik</strong> sayfasında girdiğiniz fatura bilgileriyle kesilir ve genellikle
                <strong>1-7 iş günü</strong> içinde bu sayfadan indirilebilir hale gelir. Fatura bilgilerinizi güncellemek için
                <a href="{{ route('hekim.uyelik') }}" class="font-bold underline">Üyelik sayfasına</a> gidebilirsiniz.
            </p>
        </div>
    </div>
@endsection
