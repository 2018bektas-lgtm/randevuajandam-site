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
    <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-xs text-emerald-800 font-semibold flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('basarili') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-xs text-rose-800">
        <p class="font-bold mb-1">Yükleme başarısız — düzelt ve tekrar dene:</p>
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
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

                    {{-- Fatura yüklüyse: indir + değiştir/sil --}}
                    @if($o->fatura_url)
                        <a href="{{ str_starts_with($o->fatura_url, 'http') ? $o->fatura_url : asset($o->fatura_url) }}"
                           target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#FFF7ED] border border-[#FED7AA] text-[#C96A2B] hover:bg-[#C96A2B] hover:text-white hover:border-[#C96A2B] text-[11px] font-bold transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                            </svg>
                            Faturayı İndir
                            @if($o->fatura_no)
                                <span class="ml-1 font-mono text-[10px] opacity-80">#{{ $o->fatura_no }}</span>
                            @endif
                        </a>
                        <form method="POST" action="{{ route('yonetim.faturalar.guncelle', $o->id) }}"
                              onsubmit="return confirm('Yüklü faturayı silmek istiyor musunuz? Hekim artık indiremez.');">
                            @csrf
                            <input type="hidden" name="aksiyon" value="sil">
                            <button class="px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 text-[11px] font-bold hover:bg-rose-100">
                                Faturayı sil
                            </button>
                        </form>
                    @else
                        {{-- Fatura yok: yükle butonu (accordion açar) --}}
                        <button type="button"
                                onclick="faturaYukleAc({{ $o->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 text-white text-[11px] font-bold hover:bg-emerald-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12l4.5-4.5m0 0l4.5 4.5M12 7.5V21"/>
                            </svg>
                            Fatura yükle
                        </button>
                        {{-- Sadece durum işaretle (dosya olmadan) --}}
                        <form method="POST" action="{{ route('yonetim.faturalar.guncelle', $o->id) }}">
                            @csrf
                            <input type="hidden" name="aksiyon" value="durum">
                            <input type="hidden" name="fatura_durumu" value="kesildi">
                            <button class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[11px] font-bold text-slate-600 hover:bg-slate-50"
                                    title="Sadece 'Kesildi' işareti — PDF yüklemeden">
                                Sadece işaretle
                            </button>
                        </form>
                    @endif

                    {{-- Zaten kesildi işaretli ama PDF yok (eski davranış) → bekliyor'a al --}}
                    @if($fd === 'kesildi' && ! $o->fatura_url)
                        <form method="POST" action="{{ route('yonetim.faturalar.guncelle', $o->id) }}">
                            @csrf
                            <input type="hidden" name="aksiyon" value="durum">
                            <input type="hidden" name="fatura_durumu" value="bekliyor">
                            <button class="px-3 py-2 rounded-xl border border-slate-200 text-[11px] font-bold text-slate-600 hover:bg-slate-50">
                                Bekliyor'a al
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- FATURA YÜKLEME FORMU (accordion) --}}
            @if(! $o->fatura_url)
                <div id="fatura-yukle-{{ $o->id }}" class="hidden border-b border-slate-100 bg-emerald-50/30 px-4 sm:px-5 py-4">
                    <form method="POST" action="{{ route('yonetim.faturalar.guncelle', $o->id) }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                        @csrf
                        <input type="hidden" name="aksiyon" value="yukle">

                        <div class="sm:col-span-2">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Fatura PDF *</label>
                            <input type="file" name="fatura_pdf" accept="application/pdf" required
                                   class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200 border border-slate-200 rounded-lg bg-white cursor-pointer">
                            <p class="text-[10px] text-slate-400 mt-1">Max 8 MB — yalnızca PDF</p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Fatura No *</label>
                            <input type="text" name="fatura_no" required maxlength="100"
                                   placeholder="Örn: RAJ2026000123"
                                   class="w-full text-xs rounded-lg border-slate-200 focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 py-2 bg-white">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Kesim Tarihi</label>
                            <input type="date" name="fatura_kesildi_at" value="{{ now()->toDateString() }}"
                                   class="w-full text-xs rounded-lg border-slate-200 focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 py-2 bg-white">
                        </div>

                        <div class="sm:col-span-4 flex items-center justify-end gap-2 pt-1">
                            <button type="button" onclick="faturaYukleKapat({{ $o->id }})"
                                    class="px-3 py-2 rounded-lg border border-slate-200 text-[11px] font-bold text-slate-600 hover:bg-slate-50">
                                Vazgeç
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                                Yükle ve kesildi işaretle
                            </button>
                        </div>
                    </form>
                </div>
            @else
                {{-- Fatura yüklüyse detaylar --}}
                <div class="border-b border-slate-100 bg-emerald-50/30 px-4 sm:px-5 py-3 flex flex-wrap items-center gap-4 text-[11px]">
                    <span class="inline-flex items-center gap-1.5 text-emerald-800 font-bold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        Fatura yüklendi
                    </span>
                    @if($o->fatura_no)
                        <span class="text-slate-600">No: <span class="font-mono font-bold text-slate-800">{{ $o->fatura_no }}</span></span>
                    @endif
                    @if($o->fatura_kesildi_at)
                        <span class="text-slate-600">Kesim: <span class="font-semibold text-slate-800">{{ $o->fatura_kesildi_at->format('d.m.Y H:i') }}</span></span>
                    @endif
                </div>
            @endif

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

<p class="mt-4 text-[10px] text-slate-400">
    Fiyatlara KDV dahildir. Fatura GİB üzerinden manuel kesilir; PDF'i buradan yükleyince hekim
    <span class="font-semibold">Faturalarım</span> sayfasından indirebilir. Sadece durum işaretlemek isterseniz "Sadece işaretle" butonunu kullanın (PDF yüklemeden).
</p>

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

function faturaYukleAc(id) {
    const el = document.getElementById('fatura-yukle-' + id);
    if (!el) return;
    el.classList.remove('hidden');
    const input = el.querySelector('input[name="fatura_no"]');
    if (input) setTimeout(() => input.focus(), 50);
}
function faturaYukleKapat(id) {
    const el = document.getElementById('fatura-yukle-' + id);
    if (el) el.classList.add('hidden');
}

// Validasyon hatalarında formu otomatik aç
document.addEventListener('DOMContentLoaded', function () {
    @if($errors->any())
        // Hata varsa hangi kayıt için gönderildiği bilinemez; ilk kapalı formu aç
        const ilk = document.querySelector('[id^="fatura-yukle-"].hidden');
        if (ilk) ilk.classList.remove('hidden');
    @endif
});
</script>
@endsection
