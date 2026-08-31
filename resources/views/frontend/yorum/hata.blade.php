@extends('frontend.layouts.app')

@section('baslik', 'Bağlantı kullanılamıyor · Randevu Ajandam')
@section('robots', 'noindex, nofollow')

@section('icerik')
<div class="max-w-xl mx-auto px-4 py-16 sm:py-24 text-center">
    <div class="mx-auto w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center">
        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M10.34 3.94l-7.6 13.16A1.5 1.5 0 004.04 19.5h15.92a1.5 1.5 0 001.3-2.4l-7.6-13.16a1.5 1.5 0 00-2.6 0z"/>
        </svg>
    </div>

    <h1 class="mt-6 text-2xl font-bold font-display text-[#111827]">{{ $baslik }}</h1>
    <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ $mesaj }}</p>

    <a href="{{ url('/') }}"
       class="mt-8 inline-flex items-center justify-center gap-2 rounded-xl border border-[#E5E7EB] bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:border-[#C96A2B] hover:text-[#C96A2B] transition-colors">
        Randevu Ajandam'a git
    </a>
</div>
@endsection
