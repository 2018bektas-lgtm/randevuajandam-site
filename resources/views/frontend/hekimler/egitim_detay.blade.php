@extends('frontend.layouts.app')
@section('baslik', $egitim->baslik.' - '.($doktor->unvan ? $doktor->unvan.' ' : '').$doktor->ad_soyad)
@section('meta_aciklama', Str::limit(strip_tags($egitim->ozet ?? $egitim->icerik ?? ''), 150))

@section('icerik')
<section class="py-10 sm:py-14">
    <div class="max-w-5xl mx-auto px-4 grid lg:grid-cols-5 gap-8">
        <div class="lg:col-span-3 space-y-5">
            <a href="{{ route('frontend.hekim.egitimler', ['il_slug' => $doktor->il?->slug ?? 'il', 'ilce_slug' => $doktor->ilce?->slug ?? 'ilce', 'brans_slug' => $doktor->branslar->first()?->slug ?? 'hekim', 'doctor_slug' => $doktor->slug]) }}"
               class="text-xs font-bold text-slate-500 hover:text-[#C96A2B]">← Tüm eğitimler</a>
            @if($egitim->kapak)
                <img src="{{ asset('storage/'.$egitim->kapak) }}" alt="" class="w-full rounded-3xl object-cover max-h-72">
            @endif
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#C96A2B] font-display">{{ $egitim->tip }}</span>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold font-display text-slate-900">{{ $egitim->baslik }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $doktor->unvan }} {{ $doktor->ad_soyad }}</p>
            </div>
            <dl class="grid sm:grid-cols-2 gap-3 text-sm">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <dt class="text-[10px] font-bold uppercase text-slate-400">Tarih</dt>
                    <dd class="font-semibold text-slate-800">{{ $egitim->baslangic_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <dt class="text-[10px] font-bold uppercase text-slate-400">Ücret (bilgi)</dt>
                    <dd class="font-semibold text-slate-800">
                        @if($egitim->fiyat === null || (float)$egitim->fiyat <= 0) Ücretsiz / hekimden sorunuz
                        @else {{ number_format((float)$egitim->fiyat, 2, ',', '.') }} ₺ <span class="text-[10px] font-normal text-slate-500">(ödeme siteden alınmaz)</span>
                        @endif
                    </dd>
                </div>
                @if($egitim->mekan)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 sm:col-span-2">
                    <dt class="text-[10px] font-bold uppercase text-slate-400">Mekan</dt>
                    <dd class="font-semibold text-slate-800">{{ $egitim->mekan }}</dd>
                </div>
                @endif
            </dl>
            @if($egitim->ozet)
                <p class="text-sm text-slate-600 leading-relaxed">{{ $egitim->ozet }}</p>
            @endif
            @if($egitim->icerik)
                <div class="prose prose-sm max-w-none text-slate-700">{!! nl2br(e($egitim->icerik)) !!}</div>
            @endif
        </div>

        <div class="lg:col-span-2">
            <div class="sticky top-24 bg-white border border-slate-200 rounded-3xl p-6 shadow-md">
                <h2 class="text-sm font-bold uppercase tracking-wider font-display text-slate-900 border-b border-slate-100 pb-3 mb-4">Başvuru formu</h2>

                @if(session('basarili'))
                    <div class="mb-3 p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-[11px] text-emerald-800">{{ session('basarili') }}</div>
                @endif
                @if(session('hata'))
                    <div class="mb-3 p-3 rounded-xl bg-red-50 border border-red-100 text-[11px] text-red-700">{{ session('hata') }}</div>
                @endif

                @if($egitim->basvuruAlinabilirMi())
                    <form method="POST" action="{{ route('frontend.egitim.basvuru') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="egitim_id" value="{{ $egitim->id }}">
                        <div class="hidden" aria-hidden="true">
                            <input type="text" name="{{ config('randevu.honeypot_field', 'website_url') }}" value="" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500">Ad</label>
                                <input name="ad" required value="{{ old('ad', auth('hasta')->user()->ad ?? '') }}" class="mt-1 w-full px-3 py-2 rounded-xl border text-xs focus:border-[#C96A2B] focus:outline-none">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500">Soyad</label>
                                <input name="soyad" required value="{{ old('soyad', auth('hasta')->user()->soyad ?? '') }}" class="mt-1 w-full px-3 py-2 rounded-xl border text-xs focus:border-[#C96A2B] focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase text-slate-500">Telefon</label>
                            <input name="telefon" required value="{{ old('telefon', auth('hasta')->user()->telefon ?? '') }}" class="mt-1 w-full px-3 py-2 rounded-xl border text-xs focus:border-[#C96A2B] focus:outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase text-slate-500">E-posta</label>
                            <input type="email" name="e_posta" value="{{ old('e_posta', auth('hasta')->user()->e_posta ?? '') }}" class="mt-1 w-full px-3 py-2 rounded-xl border text-xs focus:border-[#C96A2B] focus:outline-none">
                        </div>

                        @foreach($egitim->formAlanlari as $alan)
                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500">{{ $alan->etiket }} @if($alan->zorunlu_mu)*@endif</label>
                                @if($alan->tip === 'textarea')
                                    <textarea name="alan[{{ $alan->id }}]" @if($alan->zorunlu_mu) required @endif rows="2"
                                              placeholder="{{ $alan->placeholder }}"
                                              class="mt-1 w-full px-3 py-2 rounded-xl border text-xs focus:border-[#C96A2B] focus:outline-none">{{ old('alan.'.$alan->id) }}</textarea>
                                @elseif($alan->tip === 'select')
                                    <select name="alan[{{ $alan->id }}]" @if($alan->zorunlu_mu) required @endif class="mt-1 w-full px-3 py-2 rounded-xl border text-xs">
                                        <option value="">Seçin</option>
                                        @foreach(($alan->secenekler ?? []) as $opt)
                                            <option value="{{ $opt }}" @selected(old('alan.'.$alan->id) == $opt)>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                @elseif($alan->tip === 'checkbox')
                                    <label class="mt-1 flex items-center gap-2 text-xs text-slate-600">
                                        <input type="checkbox" name="alan[{{ $alan->id }}]" value="1" @if($alan->zorunlu_mu) required @endif>
                                        {{ $alan->placeholder ?: $alan->etiket }}
                                    </label>
                                @else
                                    <input type="{{ $alan->tip === 'phone' ? 'tel' : ($alan->tip === 'number' ? 'number' : ($alan->tip === 'date' ? 'date' : ($alan->tip === 'email' ? 'email' : 'text'))) }}"
                                           name="alan[{{ $alan->id }}]"
                                           value="{{ old('alan.'.$alan->id) }}"
                                           @if($alan->zorunlu_mu) required @endif
                                           placeholder="{{ $alan->placeholder }}"
                                           class="mt-1 w-full px-3 py-2 rounded-xl border text-xs focus:border-[#C96A2B] focus:outline-none">
                                @endif
                            </div>
                        @endforeach

                        <label class="flex items-start gap-2 text-[11px] text-slate-600">
                            <input type="checkbox" name="kvkk_onay" value="1" required class="mt-0.5">
                            <span>Kişisel verilerimin eğitim başvurusu amacıyla işlenmesini kabul ediyorum.</span>
                        </label>

                        @if($egitim->odeme_notu)
                            <p class="text-[11px] text-amber-800 bg-amber-50 border border-amber-100 rounded-xl p-3">{{ $egitim->odeme_notu }}</p>
                        @endif

                        <button type="submit" class="w-full py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider font-display">
                            Başvur
                        </button>
                    </form>
                @else
                    <p class="text-sm text-slate-500 leading-relaxed">Bu eğitime şu an başvuru alınmıyor (kontenjan dolu veya süre bitti).</p>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
