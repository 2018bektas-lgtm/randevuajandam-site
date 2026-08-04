@extends('yonetim.layout')

@section('baslik', 'Paketi Düzenle - Randevu Ajandam')
@section('sayfa_baslik', 'Paket Yönetimi')

@section('icerik')
    <div class="max-w-4xl mx-auto">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
            <div>
                <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                    <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                    Paketi Düzenle
                </h2>
                <p class="text-xs text-[#6B7280] mt-1.5 ml-4">{{ $paket->ad }} · özellikler tıklanarak yetkilendirilir.</p>
            </div>
            <a href="{{ route('yonetim.paketler.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E5E7EB] bg-white text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] shadow-sm">
                Listeye Dön
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-800">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8">
            <form action="{{ route('yonetim.paketler.update', $paket->id) }}" method="POST">
                @csrf
                @include('yonetim.paketler._form', ['paket' => $paket, 'ozellikGruplari' => $ozellikGruplari, 'seciliOzellikler' => $seciliOzellikler])
                <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-[#E5E7EB]">
                    <a href="{{ route('yonetim.paketler.index') }}" class="px-5 py-2.5 rounded-xl border border-[#E5E7EB] text-sm font-bold text-slate-600">İptal</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-sm shadow-sm">Değişiklikleri Kaydet</button>
                </div>
            </form>
        </div>
    </div>
@endsection
