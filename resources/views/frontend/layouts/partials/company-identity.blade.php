{{-- Ticari kimlik — config/company.php veya .env (COMPANY_*) --}}
@php
    $c = config('company', []);
    $row = function (string $label, ?string $value) {
        $v = trim((string) $value);
        return ['label' => $label, 'value' => $v !== '' ? $v : '—'];
    };
    $rows = [
        $row('Ticari unvan', $c['unvan'] ?? ''),
        $row('Adres', $c['adres'] ?? ''),
        $row('İl', $c['il'] ?? ''),
        $row('Vergi dairesi', $c['vergi_dairesi'] ?? ''),
        $row('Vergi no', $c['vergi_no'] ?? ''),
        $row('MERSİS', $c['mersis'] ?? ''),
        $row('VERBİS', $c['verbis'] ?? ''),
        $row('E-posta', $c['email'] ?? 'info@randevuajandam.com'),
        $row('Telefon', $c['telefon'] ?? ''),
    ];
@endphp
<div class="not-prose rounded-xl border border-slate-200 bg-slate-50/80 overflow-hidden my-4">
    <div class="px-4 py-2.5 border-b border-slate-200 bg-white">
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Ticari / iletişim bilgileri</p>
    </div>
    <dl class="divide-y divide-slate-100">
        @foreach($rows as $r)
            <div class="grid grid-cols-3 gap-2 px-4 py-2.5 text-xs">
                <dt class="text-slate-500 font-medium">{{ $r['label'] }}</dt>
                <dd class="col-span-2 text-slate-800 font-semibold {{ $r['value'] === '—' ? 'text-slate-400 font-normal' : '' }}">
                    {{ $r['value'] }}
                </dd>
            </div>
        @endforeach
    </dl>
</div>
