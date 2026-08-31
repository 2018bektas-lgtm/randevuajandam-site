@extends('frontend.layouts.app')

@section('baslik', 'Teşekkür ederiz · Randevu Ajandam')
@section('robots', 'noindex, nofollow')

@section('icerik')
<div class="max-w-xl mx-auto px-4 py-16 sm:py-24 text-center">
    <div class="mx-auto w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center">
        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h1 class="mt-6 text-2xl font-bold font-display text-[#111827]">Değerlendirmeniz alındı</h1>

    <p class="mt-3 text-sm text-slate-600 leading-relaxed">
        Paylaşımınız için teşekkür ederiz. Değerlendirmeniz platform yönetimi tarafından
        incelendikten sonra
        @if($doktor)
            {{ trim(($doktor->unvan ? $doktor->unvan.' ' : '').$doktor->ad_soyad) }}
        @endif
        profilinde yayınlanacaktır.
    </p>

    <a href="{{ url('/') }}"
       class="mt-8 inline-flex items-center justify-center gap-2 rounded-xl border border-[#E5E7EB] bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:border-[#C96A2B] hover:text-[#C96A2B] transition-colors">
        Randevu Ajandam'a git
    </a>
</div>
@endsection
