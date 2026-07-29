@extends('yonetim.layout')

@section('baslik', 'Faturalar - Randevu Ajandam')
@section('sayfa_baslik', 'Üyelik faturaları (manuel GİB)')

@section('icerik')
@php
    $labelMap = [
        'tip' => 'Tip',
        'unvan' => 'Unvan / ad soyad',
        'tc_vkn' => 'TC / VKN',
        'vergi_dairesi' => 'Vergi dairesi',
        'adres' => 'Adres',
        'il' => 'İl',
        'ilce' => 'İlçe',
        'posta_kodu' => 'Posta kodu',
        'email' => 'E-posta',
        'telefon' => 'Telefon',
        'fatura_tipi' => 'Tip',
        'fatura_unvan' => 'Unvan',
        'fatura_tc_vkn' => 'TC / VKN',
        'fatura_vergi_dairesi' => 'Vergi dairesi',
        'fatura_adres' => 'Adres',
        'fatura_il' => 'İl',
        'fatura_ilce' => 'İlçe',
        'fatura_posta_kodu' => 'Posta kodu',
        'fatura_email' => 'E-posta',
        'fatura_telefon' => 'Telefon',
    ];
@endphp

<div class="mb-6">
    <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
        <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
        Manuel fatura kuyruğu
    </h2>
    <p class="text-xs text-[#6B7280] mt-1.5 ml-4">
        Ödeme anında kaydedilen fatura bilgileri. GİB’den elle kesip buradan «Kesildi» işaretleyin. Otomatik e-fatura yok.
    </p>
</div>

<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('yonetim.faturalar', ['fatura' => 'bekliyor']) }}"
       class="px-3 py-2 rounded-xl text-xs font-bold border {{ ($durum ?? '') === 'bekliyor' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-white border-slate-200 text-slate-600' }}">
        Fatura bekliyor
    </a>
    <a href="{{ route('yonetim.faturalar', ['fatura' => 'kesildi']) }}"
       class="px-3 py-2 rounded-xl text-xs font-bold border {{ ($durum ?? '') === 'kesildi' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-slate-200 text-slate-600' }}">
        Kesildi
    </a>
    <a href="{{ route('yonetim.faturalar', ['fatura' => 'onayli_odeme']) }}"
       class="px-3 py-2 rounded-xl text-xs font-bold border {{ ($durum ?? '') === 'onayli_odeme' ? 'bg-slate-100 border-slate-300 text-slate-800' : 'bg-white border-slate-200 text-slate-600' }}">
        Tüm onaylı ödemeler
    </a>
</div>

@if(session('basarili'))
    <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-xs text-emerald-800 font-semibold">{{ session('basarili') }}</div>
@endif

<div class="space-y-4">
    @forelse($odemeler as $o)
        @php
            $fb = is_array($o->fatura_bilgisi) ? $o->fatura_bilgisi : [];
            if ($fb === [] && $o->doktor) {
                $fb = array_filter([
                    'tip' => $o->doktor->fatura_tipi,
                    'unvan' => $o->doktor->fatura_unvan ?: $o->doktor->ad_soyad,
                    'tc_vkn' => $o->doktor->fatura_tc_vkn ?: $o->doktor->tc_kimlik_no,
                    'vergi_dairesi' => $o->doktor->fatura_vergi_dairesi,
                    'adres' => $o->doktor->fatura_adres ?: $o->doktor->adres,
                    'il' => $o->doktor->fatura_il,
                    'ilce' => $o->doktor->fatura_ilce,
                    'posta_kodu' => $o->doktor->fatura_posta_kodu,
                    'email' => $o->doktor->fatura_email ?: $o->doktor->e_posta,
                    'telefon' => $o->doktor->fatura_telefon ?: $o->doktor->telefon,
                ], fn ($v) => filled($v));
                $kaynakNot = 'Hekim profilindeki fatura alanları (ödeme anı snapshot yok)';
            } else {
                $kaynakNot = $fb === [] ? 'Fatura bilgisi kayıtlı değil' : 'Ödeme anı snapshot';
            }
            $copyLines = [];
            foreach ($fb as $k => $v) {
                if (! is_scalar($v) || ! filled($v)) {
                    continue;
                }
                $lab = $labelMap[$k] ?? $k;
                $copyLines[] = $lab.': '.$v;
            }
            $copyText = implode("\n", $copyLines);
            $fd = $o->fatura_durumu ?? 'bekliyor';
        @endphp
        <article class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 border-b border-slate-100">
                <div class="min-w-0 space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-bold text-slate-900 font-display">
                            {{ $o->doktor?->unvan }} {{ $o->doktor?->ad_soyad ?? '—' }}
                        </span>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold
                            {{ $fd === 'kesildi' ? 'bg-emerald-50 text-emerald-800 border border-emerald-100' : 'bg-amber-50 text-amber-900 border border-amber-100' }}">
                            {{ $fd === 'kesildi' ? 'Fatura kesildi' : 'Fatura bekliyor' }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500">
                        {{ $o->doktor?->e_posta }}
                        @if($o->doktor)
                            · <a href="{{ route('yonetim.doktorlar.duzenle', $o->doktor->id) }}" class="text-[#C96A2B] font-semibold hover:underline">Doktor kartı</a>
                        @endif
                    </p>
                    <p class="text-xs text-slate-700">
                        <span class="font-semibold">{{ $o->paket?->ad ?? 'Paket' }}</span>
                        · {{ $o->odeme_periyodu }}
                        · ₺{{ number_format((float) $o->tutar, 2, ',', '.') }}
                        · {{ $o->provider ?: $o->odeme_yontemi }}
                        · ödeme: {{ $o->durum }}
                    </p>
                    <p class="text-[10px] text-slate-400">
                        Ödeme #{{ $o->id }}
                        · {{ $o->onaylandi_at?->format('d.m.Y H:i') ?: $o->created_at?->format('d.m.Y H:i') }}
                        @if($o->merchant_oid)
                            · {{ $o->merchant_oid }}
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if($copyText !== '')
                        <button type="button"
                                class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[11px] font-bold text-slate-700 hover:border-[#C96A2B] hover:text-[#C96A2B] transition-colors"
                                data-copy="{{ e($copyText) }}"
                                onclick="yonetimFaturaKopyala(this)">
                            Bilgileri kopyala
                        </button>
                    @endif
                    @if($fd !== 'kesildi')
                        <form method="POST" action="{{ route('yonetim.faturalar.guncelle', $o->id) }}">
                            @csrf
                            <input type="hidden" name="fatura_durumu" value="kesildi">
                            <button class="px-3 py-2 rounded-xl bg-emerald-600 text-white text-[11px] font-bold hover:bg-emerald-700">
                                Kesildi işaretle
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('yonetim.faturalar.guncelle', $o->id) }}">
                            @csrf
                            <input type="hidden" name="fatura_durumu" value="bekliyor">
                            <button class="px-3 py-2 rounded-xl border border-slate-200 text-[11px] font-bold text-slate-600 hover:bg-slate-50">
                                Bekliyor’a al
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="p-4 sm:p-5 bg-slate-50/50">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-slate-500 font-display">Fatura bilgileri (GİB)</h3>
                    <span class="text-[10px] text-slate-400">{{ $kaynakNot }}</span>
                </div>
                @if($fb === [])
                    <p class="text-xs text-amber-800 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2">
                        Bu ödemede fatura formu kaydı yok. Hekim profilindeki kimlik / e-posta ile ilerleyin veya hekimden bilgi isteyin.
                    </p>
                @else
                    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($fb as $k => $v)
                            @if(is_scalar($v) && filled($v))
                                <div class="rounded-xl bg-white border border-slate-200 px-3 py-2.5">
                                    <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $labelMap[$k] ?? $k }}</dt>
                                    <dd class="mt-0.5 text-xs font-semibold text-slate-800 break-words {{ in_array($k, ['tc_vkn', 'fatura_tc_vkn'], true) ? 'font-mono tracking-wide' : '' }}">{{ $v }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                @endif
            </div>
        </article>
    @empty
        <div class="bg-white border border-[#E5E7EB] rounded-2xl px-4 py-12 text-center text-sm text-slate-400">
            Bu filtrede kayıt yok.
        </div>
    @endforelse
</div>

<p class="mt-4 text-[10px] text-slate-400">Fiyatlara KDV dahildir. Sistem fatura kesmez; yalnızca bilgi ve durum takibi sağlar.</p>

<script>
function yonetimFaturaKopyala(btn) {
    const text = btn.getAttribute('data-copy') || '';
    if (!text) return;
    const done = () => {
        const old = btn.textContent;
        btn.textContent = 'Kopyalandı';
        setTimeout(() => { btn.textContent = old; }, 1500);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(done).catch(() => {
            window.prompt('Kopyalamak için Ctrl+C:', text);
        });
    } else {
        window.prompt('Kopyalamak için Ctrl+C:', text);
    }
}
</script>
@endsection
