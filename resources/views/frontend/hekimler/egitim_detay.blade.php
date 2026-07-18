@extends('frontend.layouts.app')
@section('baslik', ($egitim->meta_baslik ?? $egitim->baslik).' - '.($doktor->unvan ? $doktor->unvan.' ' : '').$doktor->ad_soyad)
@section('meta_aciklama', $egitim->meta_aciklama ?? Str::limit(strip_tags($egitim->ozet ?? $egitim->icerik ?? ''), 150))
@section('og_type', 'website')
@if($egitim->kapak)
    @section('og_image', asset('storage/'.$egitim->kapak))
@endif

@section('icerik')
@php
    $listeUrl = route('frontend.hekim.egitimler', [
        'il_slug' => $doktor->il?->slug ?? 'il',
        'ilce_slug' => $doktor->ilce?->slug ?? 'ilce',
        'brans_slug' => $doktor->branslar->first()?->slug ?? 'hekim',
        'doctor_slug' => $doktor->slug,
    ]);
    $fiyatYok = $egitim->fiyat === null || (float) $egitim->fiyat <= 0;
    $basvuruAcik = $egitim->basvuruAlinabilirMi();
    $tipLabel = $egitim->tip ? str_replace('_', ' ', $egitim->tip) : 'Eğitim';
@endphp

<section class="relative bg-[#FAFAFA] py-12 md:py-20 overflow-hidden min-h-[85vh]">
    <div class="absolute top-[-10%] right-[-10%] w-[480px] h-[480px] rounded-full bg-[#E7B58A]/20 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-15%] left-[-10%] w-[420px] h-[420px] rounded-full bg-[#C96A2B]/10 blur-[120px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10">
        {{-- Breadcrumb --}}
        <nav class="mb-8 flex flex-wrap items-center gap-2 text-[11px] font-bold font-display uppercase tracking-wider text-[#6B7280]">
            <a href="{{ $doktor->profil_url }}" class="hover:text-[#C96A2B] transition-colors inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Hekim profili
            </a>
            <span class="text-slate-300">/</span>
            <a href="{{ $listeUrl }}" class="hover:text-[#C96A2B] transition-colors">Eğitimler</a>
            <span class="text-slate-300">/</span>
            <span class="text-[#C96A2B] line-clamp-1 max-w-[14rem] sm:max-w-none">{{ $egitim->baslik }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
            {{-- Sol: içerik --}}
            <div class="lg:col-span-2 space-y-6">
                <article class="bg-white border border-[#E5E7EB] rounded-3xl overflow-hidden shadow-[0_8px_30px_rgba(31,41,55,0.03)]">
                    {{-- Kapak --}}
                    <div class="relative w-full aspect-[16/9] sm:aspect-[21/9] max-h-[360px] bg-gradient-to-br from-[#FFF7ED] via-[#FFE8D2] to-[#FDE6D0] overflow-hidden">
                        @if($egitim->kapak)
                            <img src="{{ asset('storage/'.$egitim->kapak) }}"
                                 alt="{{ $egitim->baslik }}"
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/55 via-slate-900/10 to-transparent"></div>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-[#C96A2B]/40">
                                <svg class="w-20 h-20" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
                                </svg>
                            </div>
                        @endif
                        <div class="absolute bottom-4 left-4 right-4 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider font-display bg-white/95 text-[#C96A2B] shadow-sm border border-white/60">
                                {{ $tipLabel }}
                            </span>
                            @if($basvuruAcik)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider font-display bg-emerald-500/95 text-white shadow-sm">
                                    Başvuru açık
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider font-display bg-slate-700/90 text-white shadow-sm">
                                    Başvuru kapalı
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="p-6 sm:p-8 md:p-10 space-y-6">
                        <header class="space-y-3 border-b border-slate-100 pb-6">
                            <h1 class="text-2xl sm:text-3xl font-extrabold font-display text-[#111827] tracking-tight leading-tight">
                                {{ $egitim->baslik }}
                            </h1>
                            <p class="text-sm text-[#6B7280]">
                                <a href="{{ $doktor->profil_url }}" class="font-semibold text-[#C96A2B] hover:text-[#B55A20] transition-colors">
                                    {{ $doktor->unvan ? $doktor->unvan.' ' : '' }}{{ $doktor->ad_soyad }}
                                </a>
                                @if($doktor->uzmanlik_alani)
                                    <span class="text-slate-300 mx-1.5">·</span>
                                    <span>{{ $doktor->uzmanlik_alani }}</span>
                                @endif
                            </p>
                        </header>

                        {{-- Bilgi kartları --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-3.5 sm:p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-display mb-1">Başlangıç</p>
                                <p class="text-sm font-bold text-[#111827] font-display leading-snug">
                                    {{ $egitim->baslangic_at?->translatedFormat('d M Y') ?? 'Yakında' }}
                                </p>
                                @if($egitim->baslangic_at)
                                    <p class="text-[11px] font-semibold text-[#C96A2B] mt-0.5">{{ $egitim->baslangic_at->format('H:i') }}</p>
                                @endif
                            </div>
                            @if($egitim->bitis_at)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-3.5 sm:p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-display mb-1">Bitiş</p>
                                <p class="text-sm font-bold text-[#111827] font-display leading-snug">
                                    {{ $egitim->bitis_at->translatedFormat('d M Y') }}
                                </p>
                                <p class="text-[11px] font-semibold text-[#C96A2B] mt-0.5">{{ $egitim->bitis_at->format('H:i') }}</p>
                            </div>
                            @endif
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-3.5 sm:p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-display mb-1">Ücret</p>
                                <p class="text-sm font-bold text-[#111827] font-display leading-snug">
                                    @if($fiyatYok)
                                        Ücretsiz / bilgi
                                    @else
                                        {{ number_format((float) $egitim->fiyat, 0, ',', '.') }} ₺
                                    @endif
                                </p>
                                @unless($fiyatYok)
                                    <p class="text-[10px] text-slate-500 mt-0.5">Siteden tahsil edilmez</p>
                                @endunless
                            </div>
                            @if($egitim->mekan)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-3.5 sm:p-4 col-span-2 sm:col-span-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-display mb-1">Mekân</p>
                                <p class="text-sm font-semibold text-[#111827] leading-relaxed">{{ $egitim->mekan }}</p>
                            </div>
                            @endif
                            @if($egitim->kontenjan)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-3.5 sm:p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-display mb-1">Kontenjan</p>
                                <p class="text-sm font-bold text-[#111827] font-display">{{ $egitim->kontenjan }} kişi</p>
                            </div>
                            @endif
                            @if($egitim->basvuru_bitis_at)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-3.5 sm:p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-display mb-1">Son başvuru</p>
                                <p class="text-sm font-bold text-[#111827] font-display">{{ $egitim->basvuru_bitis_at->translatedFormat('d M Y') }}</p>
                            </div>
                            @endif
                        </div>

                        @if($egitim->ozet)
                            <div class="rounded-2xl bg-[#FFF7ED]/70 border border-[#E7B58A]/25 px-4 py-3.5">
                                <p class="text-sm text-[#4B5563] leading-relaxed font-medium">{{ $egitim->ozet }}</p>
                            </div>
                        @endif

                        @if($egitim->icerik)
                            <div class="pt-2">
                                <h2 class="text-xs font-bold uppercase tracking-wider font-display text-[#C96A2B] mb-3">Eğitim içeriği</h2>
                                <div class="prose prose-sm max-w-none text-[#4B5563] leading-relaxed
                                            prose-headings:font-display prose-headings:text-[#111827]
                                            prose-a:text-[#C96A2B] prose-strong:text-[#111827]">
                                    {!! nl2br(e($egitim->icerik)) !!}
                                </div>
                            </div>
                        @endif

                        @if($egitim->online_url)
                            <div class="flex items-start gap-3 rounded-2xl border border-sky-100 bg-sky-50/80 p-4">
                                <div class="w-9 h-9 rounded-xl bg-white border border-sky-100 text-sky-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m9.86-2.36a4.5 4.5 0 00-1.242-7.244l-4.5-4.5a4.5 4.5 0 00-6.364 6.364L4.5 8.25"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-sky-700 font-display">Online bağlantı</p>
                                    <p class="text-xs text-sky-900/80 mt-0.5">Katılım bilgisi başvurunuz onaylandıktan sonra iletilir.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </article>

                {{-- Hekim kartı --}}
                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-5">
                    <div class="flex items-center gap-4 w-full sm:w-auto">
                        @php
                            $words = preg_split('/\s+/', trim((string) $doktor->ad_soyad)) ?: [];
                            $kisa = $words ? mb_strtoupper(mb_substr($words[0], 0, 1)) : 'H';
                            if (count($words) > 1) {
                                $kisa .= mb_strtoupper(mb_substr(end($words), 0, 1));
                            }
                        @endphp
                        <div class="w-14 h-14 rounded-2xl overflow-hidden bg-[#FFF7ED] border border-[#E7B58A]/30 text-[#C96A2B] flex items-center justify-center font-extrabold font-display text-base shadow-sm shrink-0">
                            @if($doktor->profil_resmi)
                                <img src="{{ asset($doktor->profil_resmi) }}" alt="" class="w-full h-full object-cover">
                            @else
                                {{ $kisa }}
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold font-display text-[#111827] truncate">
                                {{ $doktor->unvan ? $doktor->unvan.' ' : '' }}{{ $doktor->ad_soyad }}
                            </p>
                            <p class="text-[11px] font-semibold text-[#C96A2B] font-display uppercase tracking-wider mt-0.5">
                                {{ $doktor->uzmanlik_alani ?? 'Hekim' }}
                            </p>
                            @if($doktor->il?->ad)
                                <p class="text-[11px] text-[#6B7280] mt-0.5">
                                    {{ $doktor->il->ad }}{{ $doktor->ilce?->ad ? ' / '.$doktor->ilce->ad : '' }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ $doktor->profil_url }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:border-[#C96A2B]/40 hover:bg-[#FFF7ED] text-[11px] font-bold uppercase tracking-wider font-display text-[#4B5563] hover:text-[#C96A2B] transition-all">
                        Profili gör
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                </div>
            </div>

            {{-- Sağ: başvuru --}}
            <div class="lg:col-span-1">
                <div class="lg:sticky lg:top-24 bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-[0_12px_40px_rgba(31,41,55,0.06)] overflow-hidden">
                    <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-[#E7B58A] via-[#C96A2B] to-[#E7B58A]"></div>

                    <div class="flex items-start justify-between gap-3 mb-5 pt-1">
                        <div>
                            <h2 class="text-base font-extrabold font-display text-[#111827]">Başvuru formu</h2>
                            <p class="text-[11px] text-[#6B7280] mt-1 leading-relaxed">Bilgileriniz yalnızca bu eğitim için hekime iletilir.</p>
                        </div>
                    </div>

                    @if(session('basarili'))
                        <div class="mb-4 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-100 text-xs text-emerald-800 font-medium leading-relaxed">
                            {{ session('basarili') }}
                        </div>
                    @endif
                    @if(session('hata'))
                        <div class="mb-4 p-3.5 rounded-2xl bg-red-50 border border-red-100 text-xs text-red-700 font-medium leading-relaxed">
                            {{ session('hata') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="mb-4 p-3.5 rounded-2xl bg-red-50 border border-red-100 text-xs text-red-700 space-y-1">
                            @foreach($errors->all() as $err)
                                <p>{{ $err }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if($basvuruAcik)
                        <form method="POST" action="{{ route('frontend.egitim.basvuru') }}" class="space-y-3.5" id="egitim-basvuru-form">
                            @csrf
                            <input type="hidden" name="egitim_id" value="{{ $egitim->id }}">
                            <div class="hidden" aria-hidden="true">
                                <input type="text" name="{{ config('randevu.honeypot_field', 'website_url') }}" value="" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label for="eg-ad" class="block text-[10px] font-bold uppercase tracking-wider text-[#6B7280] font-display">Ad</label>
                                    <input id="eg-ad" name="ad" required maxlength="100"
                                           value="{{ old('ad', auth('hasta')->user()->ad ?? '') }}"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all"
                                           autocomplete="given-name">
                                </div>
                                <div class="space-y-1">
                                    <label for="eg-soyad" class="block text-[10px] font-bold uppercase tracking-wider text-[#6B7280] font-display">Soyad</label>
                                    <input id="eg-soyad" name="soyad" required maxlength="100"
                                           value="{{ old('soyad', auth('hasta')->user()->soyad ?? '') }}"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all"
                                           autocomplete="family-name">
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label for="eg-tel" class="block text-[10px] font-bold uppercase tracking-wider text-[#6B7280] font-display">Telefon</label>
                                <input id="eg-tel" name="telefon" type="tel" required
                                       inputmode="numeric" pattern="05[0-9]{9}" maxlength="11"
                                       placeholder="05xxxxxxxxx"
                                       value="{{ old('telefon', auth('hasta')->user()->telefon ?? '') }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all"
                                       autocomplete="tel-national"
                                       title="05 ile başlayan 11 haneli numara">
                                <p class="text-[10px] text-slate-400">05 ile başlayan 11 haneli numara</p>
                            </div>

                            <div class="space-y-1">
                                <label for="eg-mail" class="block text-[10px] font-bold uppercase tracking-wider text-[#6B7280] font-display">E-posta <span class="font-medium normal-case tracking-normal text-slate-400">(opsiyonel)</span></label>
                                <input id="eg-mail" type="email" name="e_posta"
                                       value="{{ old('e_posta', auth('hasta')->user()->e_posta ?? '') }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all"
                                       autocomplete="email">
                            </div>

                            @foreach($egitim->formAlanlari as $alan)
                                <div class="space-y-1">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-[#6B7280] font-display">
                                        {{ $alan->etiket }}
                                        @if($alan->zorunlu_mu)<span class="text-[#C96A2B]">*</span>@endif
                                    </label>
                                    @if($alan->tip === 'textarea')
                                        <textarea name="alan[{{ $alan->id }}]" @if($alan->zorunlu_mu) required @endif rows="3"
                                                  placeholder="{{ $alan->placeholder }}"
                                                  class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all resize-y">{{ old('alan.'.$alan->id) }}</textarea>
                                    @elseif($alan->tip === 'select')
                                        <select name="alan[{{ $alan->id }}]" @if($alan->zorunlu_mu) required @endif
                                                class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                                            <option value="">Seçin</option>
                                            @foreach(($alan->secenekler ?? []) as $opt)
                                                <option value="{{ $opt }}" @selected(old('alan.'.$alan->id) == $opt)>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($alan->tip === 'checkbox')
                                        <label class="flex items-start gap-2.5 text-xs text-[#4B5563] leading-relaxed cursor-pointer">
                                            <input type="checkbox" name="alan[{{ $alan->id }}]" value="1" @if($alan->zorunlu_mu) required @endif
                                                   class="mt-0.5 rounded border-slate-300 text-[#C96A2B] focus:ring-[#C96A2B]">
                                            <span>{{ $alan->placeholder ?: $alan->etiket }}</span>
                                        </label>
                                    @else
                                        <input type="{{ $alan->tip === 'phone' ? 'tel' : ($alan->tip === 'number' ? 'number' : ($alan->tip === 'date' ? 'date' : ($alan->tip === 'email' ? 'email' : 'text'))) }}"
                                               name="alan[{{ $alan->id }}]"
                                               value="{{ old('alan.'.$alan->id) }}"
                                               @if($alan->zorunlu_mu) required @endif
                                               placeholder="{{ $alan->placeholder }}"
                                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA] text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                                    @endif
                                </div>
                            @endforeach

                            @if($egitim->odeme_notu)
                                <div class="rounded-2xl bg-amber-50 border border-amber-100 px-3.5 py-3 text-[11px] text-amber-900 leading-relaxed">
                                    <span class="font-bold font-display uppercase tracking-wider text-[10px] text-amber-700 block mb-1">Ödeme notu</span>
                                    {{ $egitim->odeme_notu }}
                                </div>
                            @endif

                            <label class="flex items-start gap-2.5 text-[11px] text-[#4B5563] leading-relaxed cursor-pointer pt-1">
                                <input type="checkbox" name="kvkk_onay" value="1" required class="mt-0.5 rounded border-slate-300 text-[#C96A2B] focus:ring-[#C96A2B]">
                                <span>Kişisel verilerimin eğitim başvurusu amacıyla işlenmesini kabul ediyorum.</span>
                            </label>

                            <button type="submit"
                                    class="w-full py-3.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider font-display shadow-md shadow-orange-500/15 transition-all hover:shadow-lg active:scale-[0.99]">
                                Başvuruyu gönder
                            </button>
                        </form>
                    @else
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5 text-center space-y-2">
                            <div class="w-12 h-12 mx-auto rounded-2xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            </div>
                            <p class="text-sm font-bold font-display text-[#111827]">Başvuru alınmıyor</p>
                            <p class="text-xs text-[#6B7280] leading-relaxed">Kontenjan dolu, süre bitmiş veya hekim başvuruyu kapatmış olabilir.</p>
                            <a href="{{ $listeUrl }}" class="inline-flex text-[11px] font-bold text-[#C96A2B] hover:underline mt-2">Diğer eğitimlere bak</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@if($basvuruAcik)
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tel = document.getElementById('eg-tel');
    if (!tel) return;
    tel.addEventListener('input', function () {
        var v = tel.value.replace(/\D/g, '');
        if (v.length && v.charAt(0) === '5') v = '0' + v;
        if (v.length && v.charAt(0) !== '0') v = '0' + v.replace(/^0+/, '');
        if (v.length >= 2 && v.charAt(1) !== '5') v = '05' + v.slice(2);
        tel.value = v.slice(0, 11);
    });
    tel.addEventListener('keypress', function (e) {
        if (e.ctrlKey || e.metaKey || e.key.length > 1) return;
        if (!/[0-9]/.test(e.key)) e.preventDefault();
    });
});
</script>
@endif
@endsection
