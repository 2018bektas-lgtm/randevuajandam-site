{{-- Ticari kimlik — config/company.php veya .env (COMPANY_*) --}}
@php
    $c = config('company', []);
    $adresTam = trim(implode(', ', array_filter([
        $c['adres'] ?? '',
        $c['ilce'] ?? '',
        $c['il'] ?? '',
        $c['posta_kodu'] ?? '',
        $c['ulke'] ?? 'Türkiye',
    ])));
    $rows = [
        ['Ticari unvan', trim((string) ($c['unvan'] ?? ''))],
        ['Adres (yurt içi)', $adresTam],
        ['Vergi dairesi', trim((string) ($c['vergi_dairesi'] ?? ''))],
        ['Vergi no', trim((string) ($c['vergi_no'] ?? ''))],
        ['MERSİS', trim((string) ($c['mersis'] ?? ''))],
        ['VERBİS', trim((string) ($c['verbis'] ?? ''))],
        ['E-posta', trim((string) ($c['email'] ?? 'info@randevuajandam.com'))],
        ['Telefon', trim((string) ($c['telefon'] ?? ''))],
    ];
@endphp
<div class="not-prose my-5 rounded-2xl border border-slate-200 bg-slate-50/90 overflow-hidden shadow-sm">
    <div class="px-4 py-2.5 border-b border-slate-200 bg-white flex items-center justify-between gap-2">
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Ticari / iletişim bilgileri</p>
    </div>
    <dl class="divide-y divide-slate-100">
        @foreach($rows as [$label, $value])
            @php $empty = $value === ''; @endphp
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-3 px-4 py-2.5 text-xs">
                <dt class="text-slate-500 font-medium sm:pt-0.5">{{ $label }}</dt>
                <dd class="sm:col-span-2 font-semibold break-words {{ $empty ? 'text-slate-400 font-normal' : 'text-slate-800' }}">
                    @if($label === 'E-posta' && ! $empty)
                        <a href="mailto:{{ $value }}" class="text-[#C96A2B] hover:underline">{{ $value }}</a>
                    @elseif($label === 'Telefon' && ! $empty)
                        <a href="tel:{{ preg_replace('/\s+/', '', $value) }}" class="text-[#C96A2B] hover:underline">{{ $value }}</a>
                    @else
                        {{ $empty ? '—' : $value }}
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>
</div>
