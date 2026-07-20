{{-- Tüm paket fiyatları KDV dahil gösterilir --}}
@php
    $kdvClass = $class ?? 'text-[10px] font-semibold text-slate-500 mt-1.5';
@endphp
<p class="{{ $kdvClass }}">Fiyatlara KDV dahildir.</p>
