@extends('yonetim.layout')

@section('baslik', 'Meslek Belgesi Kuyruğu - Randevu Ajandam')
@section('sayfa_baslik', 'Meslek inceleme kuyruğu')

@section('icerik')
<div class="mb-6">
    <h2 class="text-xl font-bold font-display text-[#111827]">Meslek belgesi inceleme</h2>
    <p class="text-xs text-slate-500 mt-1">Otomatik onaya düşmeyen veya beklemede kalan kayıtlar. Her belgede TC, ad, program ve yüklenen dosyalar yan yana.</p>
</div>

@if(session('basarili'))
    <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-xs text-emerald-800 font-semibold">{{ session('basarili') }}</div>
@endif

@forelse($doktorlar as $d)
    <div class="mb-5 bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-sm font-bold text-[#111827] font-display">{{ $d->unvan }} {{ $d->ad_soyad }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $d->e_posta }} · {{ $d->telefon }}</p>
                <p class="text-[11px] font-mono text-slate-600 mt-1">TC: {{ $d->tc_kimlik_no ?: '—' }} · Dip: {{ $d->diploma_no ?: '—' }} · Barkod: {{ $d->edevlet_barkod ?: '—' }}</p>
                <p class="text-[10px] text-slate-400 mt-1">Kayıt: {{ $d->created_at?->format('d.m.Y H:i') }}
                    @if($d->kayitPaketi) · Paket niyeti: {{ $d->kayitPaketi->ad }} @endif
                </p>
                @if($d->meslek_dogrulama_notu)
                    <p class="text-[11px] text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-2 py-1 mt-2">Sistem notu: {{ $d->meslek_dogrulama_notu }}</p>
                @endif
            </div>
            <a href="{{ route('yonetim.doktorlar.duzenle', $d->id) }}" class="text-xs font-bold text-[#C96A2B] underline">Tam düzenle →</a>
        </div>

        @if($d->meslek_belge_yolu)
            <a href="{{ route('yonetim.doktorlar.meslek-belge', $d->id) }}" target="_blank" class="inline-flex text-xs font-bold text-[#C96A2B] underline">Ana meslek belgesi</a>
        @endif

        @if($d->mezuniyetBelgeleri->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($d->mezuniyetBelgeleri as $b)
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-[11px] space-y-1">
                        <p class="font-bold text-slate-800">{{ $b->ad_soyad_belge ?: 'Belge #'.$b->id }}</p>
                        <p class="text-slate-600">{{ $b->program ?: '—' }}</p>
                        <p class="font-mono text-slate-500">{{ $b->barkod }} · {{ $b->diploma_no }}</p>
                        <p class="text-slate-500">TC ok: {{ $b->eslesme_detay['tc_ok'] ?? '?' }} · Ad ok: {{ $b->eslesme_detay['ad_ok'] ?? '?' }} · Auto: {{ $b->auto_onay_uygun ? 'evet' : 'hayır' }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('yonetim.doktorlar.meslek-dogrula', $d->id) }}" method="POST" class="flex flex-col sm:flex-row gap-2 sm:items-end border-t border-slate-100 pt-4">
            @csrf
            <div class="flex-1">
                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Not (red için zorunlu)</label>
                <input type="text" name="not" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs" placeholder="Red gerekçesi veya onay notu">
            </div>
            <button type="submit" name="karar" value="onaylandi" class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold">Onayla</button>
            <button type="submit" name="karar" value="reddedildi" class="px-4 py-2.5 rounded-xl bg-red-600 text-white text-xs font-bold" onclick="if(!this.form.not.value.trim()){event.preventDefault();alert('Red için not girin');}">Reddet</button>
        </form>
    </div>
@empty
    <div class="bg-white border border-emerald-100 rounded-2xl p-10 text-center text-sm text-emerald-800">
        Bekleyen meslek belgesi yok. 🎉
    </div>
@endforelse
@endsection
