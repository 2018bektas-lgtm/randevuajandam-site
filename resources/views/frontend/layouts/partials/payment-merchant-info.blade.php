{{--
  PayTR sanal POS / abonelik: ödeme sayfalarında satıcı iletişim + yurt içi adres
  son kullanıcıya açıkça görünmeli.
--}}
@php
    $c = config('company', []);
    $unvan = trim((string) ($c['unvan'] ?? ''));
    $adres = trim((string) ($c['adres'] ?? ''));
    $ilce = trim((string) ($c['ilce'] ?? ''));
    $il = trim((string) ($c['il'] ?? ''));
    $posta = trim((string) ($c['posta_kodu'] ?? ''));
    $ulke = trim((string) ($c['ulke'] ?? 'Türkiye'));
    $telefon = trim((string) ($c['telefon'] ?? ''));
    $email = trim((string) ($c['email'] ?? 'info@randevuajandam.com'));
    $web = trim((string) ($c['web'] ?? 'https://randevuajandam.com'));
    $vergiD = trim((string) ($c['vergi_dairesi'] ?? ''));
    $vergiNo = trim((string) ($c['vergi_no'] ?? ''));

    $adresSatirlari = array_values(array_filter([
        $adres,
        trim(implode(' / ', array_filter([$ilce, $il, $posta]))),
        $ulke !== '' ? $ulke : 'Türkiye',
    ]));
    $adresTam = $adresSatirlari !== [] ? implode(', ', $adresSatirlari) : '';
    $eksikAdres = $adres === '' || $il === '';
@endphp

<aside class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm"
       aria-label="Satıcı iletişim ve adres bilgileri">
    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 mb-3">
        Satıcı / hizmet sağlayıcı — iletişim ve adres
    </p>

    @if($eksikAdres)
        <div class="mb-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] text-amber-900 leading-relaxed">
            <strong>Yönetici notu:</strong> PayTR yurt ici adres istiyor.
            <code class="text-[10px] bg-amber-100 px-1 rounded">COMPANY_ADRES</code>,
            <code class="text-[10px] bg-amber-100 px-1 rounded">COMPANY_IL</code> (ve unvan/vergi) .env veya config/company.php içinde doldurun.
        </div>
    @endif

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2.5 text-xs">
        <div class="sm:col-span-2">
            <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Unvan</dt>
            <dd class="mt-0.5 font-semibold text-slate-900">
                {{ $unvan !== '' ? $unvan : 'Randevu Ajandam' }}
            </dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Adres (yurt ici)</dt>
            <dd class="mt-0.5 font-semibold text-slate-900 leading-relaxed">
                @if($adresTam !== '')
                    {{ $adresTam }}
                @else
                    <span class="text-amber-700 font-medium">Adres tanımlanmadı — COMPANY_ADRES / COMPANY_IL ekleyin</span>
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Telefon</dt>
            <dd class="mt-0.5 font-semibold text-slate-900">
                @if($telefon !== '')
                    <a href="tel:{{ preg_replace('/\s+/', '', $telefon) }}" class="hover:text-[#C96A2B]">{{ $telefon }}</a>
                @else
                    —
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">E-posta</dt>
            <dd class="mt-0.5 font-semibold text-slate-900">
                <a href="mailto:{{ $email }}" class="hover:text-[#C96A2B]">{{ $email }}</a>
            </dd>
        </div>
        <div>
            <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Web</dt>
            <dd class="mt-0.5 font-semibold text-slate-900">
                <a href="{{ $web }}" class="hover:text-[#C96A2B]" target="_blank" rel="noopener">{{ parse_url($web, PHP_URL_HOST) ?: $web }}</a>
            </dd>
        </div>
        @if($vergiD !== '' || $vergiNo !== '')
            <div>
                <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Vergi</dt>
                <dd class="mt-0.5 font-semibold text-slate-900">
                    {{ trim($vergiD.($vergiD && $vergiNo ? ' / ' : '').$vergiNo) }}
                </dd>
            </div>
        @endif
    </dl>
    <p class="mt-3 text-[10px] text-slate-400 leading-relaxed">
        Kartlı ödemeler PayTR altyapısı ile alınır. Destek:
        <a href="{{ route('frontend.legal.iletisim') }}" class="text-[#C96A2B] font-semibold underline">İletişim</a>
        ·
        <a href="{{ route('frontend.legal.mesafeli') }}" class="text-[#C96A2B] font-semibold underline">Mesafeli satış</a>
    </p>
</aside>
