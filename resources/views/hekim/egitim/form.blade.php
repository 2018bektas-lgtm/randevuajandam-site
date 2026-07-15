@extends('hekim.layout')
@section('baslik', ($egitim ? 'Eğitim Düzenle' : 'Yeni Eğitim').' - Hekim Paneli')
@section('sayfa_baslik', $egitim ? 'Eğitim Düzenle' : 'Yeni Eğitim')

@section('icerik')
@php
    $isEdit = (bool) $egitim;
    $kapakUrl = null;
    if (! empty($egitim?->kapak)) {
        $kapakUrl = str_starts_with($egitim->kapak, 'http')
            ? $egitim->kapak
            : asset('storage/'.$egitim->kapak);
    }
@endphp

<style>
    .egf-input {
        width: 100%;
        margin-top: .35rem;
        padding: .7rem .9rem;
        border-radius: .85rem;
        border: 1px solid #e7e5e4;
        background: #fff;
        font-size: .875rem;
        color: #1c1917;
        transition: border-color .15s, box-shadow .15s;
    }
    .egf-input:focus {
        outline: none;
        border-color: #c96a2b;
        box-shadow: 0 0 0 3px rgba(201,106,43,.12);
    }
    .egf-label {
        display: block;
        font-size: .65rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #78716c;
        font-family: Outfit, Inter, sans-serif;
    }
    .egf-hint { font-size: .72rem; color: #a8a29e; margin-top: .3rem; }
    .egf-card {
        background: #fff;
        border: 1px solid #e7e5e4;
        border-radius: 1.15rem;
        overflow: hidden;
    }
    .egf-card-head {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid #f5f5f4;
        background: linear-gradient(180deg, #fafaf9, #fff);
    }
    .egf-card-icon {
        width: 2.4rem;
        height: 2.4rem;
        border-radius: .75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff7ed;
        color: #c96a2b;
        border: 1px solid #fed7aa;
        flex-shrink: 0;
    }
    .egf-card-icon svg { width: 1.15rem; height: 1.15rem; }
    .egf-card-title {
        font-family: Outfit, Inter, sans-serif;
        font-weight: 800;
        font-size: .95rem;
        color: #1c1917;
        letter-spacing: -.02em;
    }
    .egf-card-sub { font-size: .75rem; color: #a8a29e; margin-top: .15rem; }
    .egf-card-body { padding: 1.25rem; }
    .egf-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem .65rem;
        border-radius: 999px;
        font-size: .65rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    /* Toggle (standart hekim paneli) */
    .egf-toggle {
        position: relative;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        user-select: none;
        flex-shrink: 0;
    }
    .egf-toggle input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    .egf-toggle-track {
        width: 2.75rem;
        height: 1.5rem;
        background: #e7e5e4;
        border-radius: 999px;
        position: relative;
        transition: background .2s;
    }
    .egf-toggle-track::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 1.25rem;
        height: 1.25rem;
        background: #fff;
        border: 1px solid #d6d3d1;
        border-radius: 999px;
        transition: transform .2s;
        box-shadow: 0 1px 2px rgba(0,0,0,.08);
    }
    .egf-toggle input:checked + .egf-toggle-track {
        background: #C96A2B;
    }
    .egf-toggle input:checked + .egf-toggle-track::after {
        transform: translateX(1.25rem);
        border-color: #fff;
    }
    .egf-toggle input:focus-visible + .egf-toggle-track {
        box-shadow: 0 0 0 3px rgba(201,106,43,.18);
    }
    .egf-toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        border-radius: .9rem;
        border: 1px solid #e7e5e4;
        background: #fafaf9;
    }
    .egf-field-card {
        position: relative;
        padding: 1rem 1rem 1rem;
        border-radius: 1rem;
        border: 1px solid #e7e5e4;
        background: #fff;
        box-shadow: 0 1px 2px rgba(28,25,23,.03);
    }
    .egf-field-card .select2-container {
        margin-top: .35rem;
        width: 100% !important;
    }
    .egf-secenek-box {
        display: none;
        margin-top: .75rem;
        padding: .85rem 1rem;
        border-radius: .85rem;
        border: 1px solid #fed7aa;
        background: #fff7ed;
    }
    .egf-secenek-box.is-open { display: block; }
</style>

{{-- Flash --}}
@if(session('basarili'))
    <div class="mb-5 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-100 text-sm text-emerald-800 flex items-start gap-2.5">
        <span class="mt-0.5">✓</span>
        <span>{{ session('basarili') }}</span>
    </div>
@endif
@if($errors->any())
    <div class="mb-5 p-3.5 rounded-2xl bg-red-50 border border-red-100 text-sm text-red-700">
        <div class="font-bold mb-1">Lütfen kontrol edin</div>
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

{{-- Page header --}}
<div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2 mb-2">
            <a href="{{ route('hekim.egitimler.index') }}" class="text-xs font-bold text-slate-500 hover:text-[#C96A2B] transition">← Eğitimler</a>
            @if($isEdit)
                <span class="text-slate-300">/</span>
                <span class="text-xs font-semibold text-slate-600 truncate max-w-[220px]">{{ $egitim->baslik }}</span>
            @endif
        </div>
        <h2 class="text-xl sm:text-2xl font-extrabold font-display text-slate-900 tracking-tight">
            {{ $isEdit ? 'Eğitimi düzenle' : 'Yeni eğitim oluştur' }}
        </h2>
        <p class="mt-1.5 text-sm text-slate-500 max-w-2xl leading-relaxed">
            Kurs, webinar veya workshop vitrini. Ödeme siteden alınmaz; başvuranları listeden takip edip “ödeme alındı” derseniz finansa yansır.
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        @if($isEdit)
            <span class="egf-chip
                @if(($egitim->durum ?? '') === 'yayinda') bg-emerald-50 text-emerald-800 border border-emerald-100
                @elseif(($egitim->durum ?? '') === 'arsiv') bg-slate-100 text-slate-600 border border-slate-200
                @else bg-amber-50 text-amber-800 border border-amber-100 @endif">
                {{ ['taslak' => 'Taslak', 'yayinda' => 'Yayında', 'arsiv' => 'Arşiv'][$egitim->durum] ?? $egitim->durum }}
            </span>
            <a href="{{ route('hekim.egitimler.basvurular', $egitim->id) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-sky-200 bg-sky-50 text-sky-800 text-xs font-bold hover:bg-sky-100 transition">
                Başvurular
            </a>
        @endif
    </div>
</div>

<form method="POST"
      action="{{ $isEdit ? route('hekim.egitimler.update', $egitim->id) : route('hekim.egitimler.store') }}"
      enctype="multipart/form-data"
      class="pb-28"
      id="egitimForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="grid xl:grid-cols-12 gap-5">
        {{-- Sol ana kolon --}}
        <div class="xl:col-span-8 space-y-5">

            {{-- Genel --}}
            <section class="egf-card">
                <div class="egf-card-head">
                    <div class="egf-card-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/></svg>
                    </div>
                    <div>
                        <div class="egf-card-title">Genel bilgiler</div>
                        <div class="egf-card-sub">Başlık, özet ve vitrinde görünen içerik</div>
                    </div>
                </div>
                <div class="egf-card-body space-y-4">
                    <div>
                        <label class="egf-label">Başlık <span class="text-[#C96A2B]">*</span></label>
                        <input type="text" name="baslik" value="{{ old('baslik', $egitim->baslik ?? '') }}" required
                               placeholder="Örn. İleri Endodonti Masterclass"
                               class="egf-input">
                    </div>
                    <div>
                        <label class="egf-label">Kısa özet</label>
                        <textarea name="ozet" rows="2" placeholder="Liste ve kartlarda görünen kısa açıklama"
                                  class="egf-input resize-y">{{ old('ozet', $egitim->ozet ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="egf-label" for="icerik">Detay içerik</label>
                        <p class="egf-hint mb-2">Program, hedefler, kimler katılabilir — zengin metin editörü ile düzenleyin.</p>
                        <textarea name="icerik" id="icerik" rows="10"
                                  class="egf-input">{{ old('icerik', $egitim->icerik ?? '') }}</textarea>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="egf-label">Tip</label>
                            <select name="tip" class="egf-select" data-placeholder="Eğitim tipi seçin">
                                @foreach(['yuz_yuze' => 'Yüz yüze', 'online' => 'Online', 'hibrit' => 'Hibrit'] as $k => $v)
                                    <option value="{{ $k }}" @selected(old('tip', $egitim->tip ?? 'yuz_yuze') === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="egf-label">Yayın durumu</label>
                            <select name="durum" class="egf-select" data-placeholder="Durum seçin">
                                @foreach(['taslak' => 'Taslak', 'yayinda' => 'Yayında', 'arsiv' => 'Arşiv'] as $k => $v)
                                    <option value="{{ $k }}" @selected(old('durum', $egitim->durum ?? 'taslak') === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                            <p class="egf-hint">Yayında seçilince public sitede görünür.</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Program & ücret --}}
            <section class="egf-card">
                <div class="egf-card-head">
                    <div class="egf-card-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    </div>
                    <div>
                        <div class="egf-card-title">Program & ücret</div>
                        <div class="egf-card-sub">Tarih, mekan ve bilgilendirme fiyatı — platform tahsilat yapmaz</div>
                    </div>
                </div>
                <div class="egf-card-body space-y-4">
                    <div class="rounded-xl bg-amber-50/80 border border-amber-100 px-3.5 py-2.5 text-[12px] text-amber-900 leading-relaxed">
                        <strong class="font-bold">Ödeme notu:</strong> Fiyat yalnızca bilgilendirme. Ödeme sizin kanalınızdan (havale, klinik vb.); panelden “ödeme alındı” deyince finansa yazılır.
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="egf-label">Başlangıç</label>
                            <input type="datetime-local" name="baslangic_at"
                                   value="{{ old('baslangic_at', isset($egitim->baslangic_at) ? $egitim->baslangic_at->format('Y-m-d\TH:i') : '') }}"
                                   class="egf-input">
                        </div>
                        <div>
                            <label class="egf-label">Bitiş</label>
                            <input type="datetime-local" name="bitis_at"
                                   value="{{ old('bitis_at', isset($egitim->bitis_at) ? $egitim->bitis_at->format('Y-m-d\TH:i') : '') }}"
                                   class="egf-input">
                        </div>
                        <div>
                            <label class="egf-label">Mekan</label>
                            <input type="text" name="mekan" value="{{ old('mekan', $egitim->mekan ?? '') }}"
                                   placeholder="Klinik adresi veya salon"
                                   class="egf-input">
                        </div>
                        <div>
                            <label class="egf-label">Online link <span class="normal-case tracking-normal font-semibold text-slate-400">(sadece siz)</span></label>
                            <input type="text" name="online_url" value="{{ old('online_url', $egitim->online_url ?? '') }}"
                                   placeholder="Zoom / Meet — public’te gösterilmez"
                                   class="egf-input">
                        </div>
                        <div>
                            <label class="egf-label">Fiyat (₺)</label>
                            <input type="number" step="0.01" min="0" name="fiyat" value="{{ old('fiyat', $egitim->fiyat ?? '') }}"
                                   placeholder="Boş = ücretsiz"
                                   class="egf-input">
                        </div>
                        <div>
                            <label class="egf-label">Kontenjan</label>
                            <input type="number" min="1" name="kontenjan" value="{{ old('kontenjan', $egitim->kontenjan ?? '') }}"
                                   placeholder="Sınırsız için boş"
                                   class="egf-input">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="egf-label">Ödeme notu</label>
                            <input type="text" name="odeme_notu" value="{{ old('odeme_notu', $egitim->odeme_notu ?? '') }}"
                                   placeholder="Havale IBAN, açıklama metni..."
                                   class="egf-input">
                        </div>
                        <div>
                            <label class="egf-label">Son başvuru</label>
                            <input type="datetime-local" name="basvuru_bitis_at"
                                   value="{{ old('basvuru_bitis_at', isset($egitim->basvuru_bitis_at) ? $egitim->basvuru_bitis_at->format('Y-m-d\TH:i') : '') }}"
                                   class="egf-input">
                        </div>
                        <div class="flex items-end">
                            <div class="egf-toggle-row w-full">
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-slate-800 font-display">Başvuru formu açık</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">Kapalıysa yeni başvuru alınmaz</div>
                                </div>
                                <label class="egf-toggle">
                                    <input type="checkbox" name="basvuru_acik_mi" value="1"
                                           @checked(old('basvuru_acik_mi', $egitim->basvuru_acik_mi ?? true))>
                                    <span class="egf-toggle-track"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- SEO --}}
            <section class="egf-card" id="seoAlanlari">
                <div class="egf-card-head">
                    <div class="egf-card-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </div>
                    <div>
                        <div class="egf-card-title">SEO bilgileri</div>
                        <div class="egf-card-sub">Arama motorları ve sosyal paylaşımlar için (opsiyonel)</div>
                    </div>
                </div>
                <div class="egf-card-body space-y-4">
                    <div class="rounded-xl bg-slate-50 border border-slate-100 px-3.5 py-2.5 text-[12px] text-slate-600 leading-relaxed">
                        Boş bırakırsanız sayfa başlığı ve özet otomatik kullanılır. Doldurursanız Google sonuçlarında bunlar görünür.
                    </div>
                    <div>
                        <label class="egf-label" for="meta_baslik">Meta başlık (title)</label>
                        <input type="text" name="meta_baslik" id="meta_baslik" maxlength="255"
                               value="{{ old('meta_baslik', $egitim->meta_baslik ?? '') }}"
                               placeholder="Örn. İleri Endodonti Eğitimi | Dr. Ad Soyad"
                               class="egf-input"
                               data-counter="meta_baslik_count" data-max="60">
                        <div class="flex justify-between mt-1">
                            <p class="egf-hint">Öneri: 50–60 karakter</p>
                            <span class="text-[10px] font-bold text-slate-400 tabular-nums" id="meta_baslik_count">0</span>
                        </div>
                    </div>
                    <div>
                        <label class="egf-label" for="meta_aciklama">Meta açıklama (description)</label>
                        <textarea name="meta_aciklama" id="meta_aciklama" rows="3" maxlength="500"
                                  placeholder="Eğitimin kısa özeti — arama sonuçlarında alt satırda çıkar"
                                  class="egf-input resize-y"
                                  data-counter="meta_aciklama_count" data-max="160">{{ old('meta_aciklama', $egitim->meta_aciklama ?? '') }}</textarea>
                        <div class="flex justify-between mt-1">
                            <p class="egf-hint">Öneri: 140–160 karakter</p>
                            <span class="text-[10px] font-bold text-slate-400 tabular-nums" id="meta_aciklama_count">0</span>
                        </div>
                    </div>
                    <div>
                        <label class="egf-label">Anahtar kelimeler (etiketler)</label>
                        <input type="hidden" name="meta_anahtar_kelimeler" id="meta_anahtar_kelimeler"
                               value="{{ old('meta_anahtar_kelimeler', $egitim->meta_anahtar_kelimeler ?? '') }}">
                        <div id="tagContainer"
                             class="mt-1 w-full px-3 py-2 rounded-[.85rem] bg-white border border-[#E5E7EB] text-[#111827] focus-within:border-[#C96A2B] focus-within:ring-1 focus-within:ring-[#C96A2B] text-xs transition-all flex flex-wrap gap-2 items-center min-h-[46px] cursor-text">
                            <input type="text" id="tagInput" placeholder="Kelime ekleyin..."
                                   class="flex-grow bg-transparent border-0 focus:border-0 focus:ring-0 focus:outline-none outline-none text-xs py-0.5 placeholder-gray-400 min-w-[120px] shadow-none">
                        </div>
                        <p class="egf-hint">Kelime yazıp <strong>Enter</strong> veya <strong>virgül (,)</strong> ile ekleyin. Rozete tıklayarak silebilirsiniz.</p>
                    </div>
                </div>
            </section>

            {{-- Başvuru formu --}}
            <section class="egf-card" id="formAlanlari">
                <div class="egf-card-head">
                    <div class="egf-card-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="egf-card-title">Başvuru formu nasıl çalışır?</div>
                        <div class="egf-card-sub">Katılımcı formu doldururken önce kimlik bilgilerini verir; siz isterseniz ek sorular ekleyebilirsiniz.</div>
                    </div>
                </div>
                <div class="egf-card-body space-y-5">
                    {{-- Sabit alanlar --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 text-[10px] font-extrabold uppercase tracking-wide border border-emerald-100">Otomatik</span>
                            <span class="text-xs font-bold text-slate-800 font-display">Her başvuruda mutlaka sorulan alanlar</span>
                        </div>
                        <p class="text-[12px] text-slate-500 leading-relaxed mb-3">
                            Bu 4 alan sistemde hazır gelir, silinemez veya kapatılamaz. Katılımcı her zaman bunları doldurur.
                        </p>
                        <div class="grid sm:grid-cols-2 gap-2.5">
                            @foreach([
                                ['Ad', 'Zorunlu'],
                                ['Soyad', 'Zorunlu'],
                                ['Telefon', 'Zorunlu'],
                                ['E-posta', 'İsteğe bağlı'],
                            ] as $sabit)
                                <div class="flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                        <span class="text-xs font-semibold text-slate-800 truncate">{{ $sabit[0] }}</span>
                                    </div>
                                    <span class="text-[10px] font-bold {{ $sabit[1] === 'Zorunlu' ? 'text-emerald-700' : 'text-slate-400' }} shrink-0">{{ $sabit[1] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2">Bu alanlar otomatik gelir; siz bunları silmez / değiştiremezsiniz.</p>
                    </div>

                    <div class="border-t border-dashed border-slate-200"></div>

                    {{-- Ek alanlar --}}
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-2.5">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#FFF7ED] text-[#C96A2B] text-[10px] font-extrabold uppercase tracking-wide border border-[#FED7AA]">İsteğe bağlı</span>
                                    <span class="text-xs font-bold text-slate-800 font-display">Sizin ekleyeceğiniz sorular</span>
                                </div>
                                <p class="text-[12px] text-slate-500 leading-relaxed max-w-xl">
                                    Örn. “Kurum adı”, “Branş”, “Katılmak istediği gün” gibi ekstra sorular.
                                    Hiç eklemezseniz form sadece ad / telefon / e-posta ile çalışır — bu yeterlidir.
                                </p>
                            </div>
                            <button type="button" onclick="alanEkle()"
                                    class="shrink-0 inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#C96A2B] text-white text-[11px] font-bold font-display hover:bg-[#B55A20] transition shadow-sm">
                                + Ek soru ekle
                            </button>
                        </div>

                        <div id="alanListesi" class="space-y-3"></div>
                        <div id="alanBos" class="hidden rounded-xl border border-dashed border-slate-200 bg-slate-50/80 px-4 py-6 text-center">
                            <div class="text-sm font-bold text-slate-700 font-display">Henüz ek soru yok</div>
                            <p class="text-[12px] text-slate-500 mt-1 max-w-sm mx-auto leading-relaxed">
                                Zorunlu değil. İsterseniz “Ek soru ekle” ile formunuza özel alanlar ekleyin.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- Sağ yan kolon: kapak + ipuçları birlikte sticky --}}
        <div class="xl:col-span-4">
            <div class="xl:sticky xl:top-4 space-y-5 xl:max-h-[calc(100vh-6rem)] xl:overflow-y-auto xl:overscroll-contain xl:pb-4"
                 style="scrollbar-width:thin">
                <section class="egf-card">
                    <div class="egf-card-head">
                        <div class="egf-card-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        </div>
                        <div>
                            <div class="egf-card-title">Kapak görseli</div>
                            <div class="egf-card-sub">Liste ve detay kartı</div>
                        </div>
                    </div>
                    <div class="egf-card-body space-y-4">
                        <div id="kapakPreview"
                             class="relative aspect-[16/10] rounded-xl border border-dashed border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center">
                            @if($kapakUrl)
                                <img src="{{ $kapakUrl }}" alt="" class="absolute inset-0 w-full h-full object-cover" id="kapakImg">
                            @else
                                <div class="text-center px-4" id="kapakPlaceholder">
                                    <div class="text-2xl mb-1 opacity-40">🖼</div>
                                    <div class="text-[11px] font-semibold text-slate-400">Görsel seçilmedi</div>
                                </div>
                                <img src="" alt="" class="absolute inset-0 w-full h-full object-cover hidden" id="kapakImg">
                            @endif
                        </div>
                        <div>
                            <label class="egf-label">Dosya yükle</label>
                            <input type="file" name="kapak" accept="image/*" id="kapakInput"
                                   class="mt-1 block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-[#FFF7ED] file:text-[#C96A2B] file:text-xs file:font-bold hover:file:bg-[#FFEDD5]">
                        </div>
                        @if($kapakUrl)
                            <div class="egf-toggle-row !bg-red-50/50 !border-red-100">
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-red-800 font-display">Mevcut kapağı sil</div>
                                    <div class="text-[11px] text-red-600/80 mt-0.5">Kaydedince görsel kaldırılır</div>
                                </div>
                                <label class="egf-toggle">
                                    <input type="checkbox" name="kapak_sil" value="1">
                                    <span class="egf-toggle-track"></span>
                                </label>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="egf-card">
                    <div class="egf-card-head">
                        <div class="egf-card-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
                        </div>
                        <div>
                            <div class="egf-card-title">Hızlı ipuçları</div>
                            <div class="egf-card-sub">Kayıt ve yayın adımları</div>
                        </div>
                    </div>
                    <div class="egf-card-body space-y-3">
                        <ul class="text-[12px] text-slate-500 space-y-2.5 leading-relaxed">
                            <li class="flex gap-2.5">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#FFF7ED] text-[10px] font-extrabold text-[#C96A2B]">1</span>
                                <span>Taslak kaydedip form alanlarını netleştirin.</span>
                            </li>
                            <li class="flex gap-2.5">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#FFF7ED] text-[10px] font-extrabold text-[#C96A2B]">2</span>
                                <span>Yayın durumunu “Yayında” yapın.</span>
                            </li>
                            <li class="flex gap-2.5">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#FFF7ED] text-[10px] font-extrabold text-[#C96A2B]">3</span>
                                <span>Başvuruları Eğitimler → Başvurular’dan yönetin.</span>
                            </li>
                        </ul>
                    </div>
                </section>

                @if($isEdit)
                    <div class="rounded-2xl border border-red-100 bg-red-50/50 p-4">
                        <div class="text-xs font-bold text-red-800 mb-1">Tehlikeli alan</div>
                        <p class="text-[11px] text-red-700/80 mb-3 leading-relaxed">Eğitim silinince kapak ve form alanları da kalkar. Başvuru kayıtları modele bağlıysa etkilenir.</p>
                        <button type="button" onclick="document.getElementById('silForm').requestSubmit()"
                                class="w-full px-4 py-2.5 rounded-xl border border-red-200 bg-white text-red-600 text-xs font-bold hover:bg-red-50 transition">
                            Eğitimi sil
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sticky actions --}}
    <div class="fixed bottom-0 left-0 right-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur-md">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 py-3 flex flex-wrap items-center justify-between gap-3">
            <p class="text-[11px] text-slate-400 hidden sm:block">Kaydetmeden önce zorunlu alanları doldurun.</p>
            <div class="flex items-center gap-2 ml-auto">
                <a href="{{ route('hekim.egitimler.index') }}"
                   class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    İptal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-[#C96A2B] text-white text-xs font-bold uppercase tracking-wide font-display hover:bg-[#B55A20] transition shadow-md shadow-[#C96A2B]/20">
                    {{ $isEdit ? 'Güncelle' : 'Kaydet' }}
                </button>
            </div>
        </div>
    </div>
</form>

@if($isEdit)
    <form id="silForm" method="POST" action="{{ route('hekim.egitimler.destroy', $egitim->id) }}"
          onsubmit="return confirm('Bu eğitim kalıcı olarak silinsin mi?');" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@php
    $alanlar = old('alanlar');
    if (! is_array($alanlar)) {
        $alanlar = ($egitim?->formAlanlari ?? collect())->map(fn ($a) => [
            'id' => $a->id,
            'etiket' => $a->etiket,
            'tip' => $a->tip,
            'zorunlu_mu' => $a->zorunlu_mu,
            'secenekler' => is_array($a->secenekler) ? implode("\n", $a->secenekler) : '',
            'placeholder' => $a->placeholder,
        ])->values()->all();
    }
@endphp

<script>
(function () {
    const alanListesi = document.getElementById('alanListesi');
    let alanIndex = 0;
    const mevcut = @json($alanlar ?? []);
    const tipLabels = {
        text: 'Kısa metin',
        textarea: 'Uzun metin',
        email: 'E-posta',
        phone: 'Telefon',
        number: 'Sayı',
        select: 'Seçim listesi (açılır menü)',
        checkbox: 'Evet / Hayır (onay)',
        date: 'Tarih'
    };

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
    }

    function initSelect2($el) {
        if (!window.jQuery || !jQuery.fn.select2 || !$el || !$el.length) return;
        if ($el.data('select2')) {
            $el.select2('destroy');
        }
        $el.select2({
            width: '100%',
            minimumResultsForSearch: 8,
            language: {
                noResults: function () { return 'Sonuç bulunamadı'; },
                searching: function () { return 'Aranıyor...'; }
            },
            placeholder: $el.data('placeholder') || $el.attr('data-placeholder') || 'Seçiniz...',
            allowClear: false
        });
    }

    function syncSecenekBox(wrap) {
        const tip = wrap.querySelector('select.egf-tip-select');
        const box = wrap.querySelector('.egf-secenek-box');
        if (!tip || !box) return;
        const isSelect = tip.value === 'select';
        box.classList.toggle('is-open', isSelect);
        const ta = box.querySelector('textarea');
        if (ta) ta.disabled = !isSelect;
    }

    function alanSatiri(data = {}, i = null) {
        if (i === null) i = alanIndex++;
        else alanIndex = Math.max(alanIndex, i + 1);

        const tip = data.tip || 'text';
        const wrap = document.createElement('div');
        wrap.className = 'egf-field-card';
        wrap.dataset.alanIndex = String(i);

        const tipOpts = Object.keys(tipLabels).map(t =>
            `<option value="${t}" ${tip === t ? 'selected' : ''}>${tipLabels[t]}</option>`
        ).join('');

        wrap.innerHTML = `
            ${data.id ? `<input type="hidden" name="alanlar[${i}][id]" value="${esc(data.id)}">` : ''}
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="min-w-0">
                    <div class="text-xs font-extrabold font-display text-slate-900">Ek soru #${i + 1}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Katılımcı formunda görünecek soru</div>
                </div>
                <button type="button" class="egf-remove-btn text-[11px] font-bold text-red-500 hover:text-red-700 shrink-0 px-2 py-1 rounded-lg hover:bg-red-50 transition">
                    Kaldır
                </button>
            </div>
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <label class="egf-label">Soru metni (etiket)</label>
                    <input name="alanlar[${i}][etiket]" value="${esc(data.etiket || '')}"
                           placeholder="Örn. Kurum adı"
                           class="egf-input">
                    <p class="egf-hint">Formda bu yazı görünür.</p>
                </div>
                <div>
                    <label class="egf-label">Alan tipi</label>
                    <select name="alanlar[${i}][tip]" class="egf-tip-select" data-placeholder="Tip seçin">
                        ${tipOpts}
                    </select>
                    <p class="egf-hint">Cevabın nasıl girileceğini belirler.</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="egf-label">Boş alan ipucu (placeholder)</label>
                    <input name="alanlar[${i}][placeholder]" value="${esc(data.placeholder || '')}"
                           placeholder="Örn. Kurumunuzun resmi adını yazın"
                           class="egf-input">
                    <p class="egf-hint">Kutunun içinde soluk görünen yardım metni (opsiyonel).</p>
                </div>
            </div>

            <div class="egf-secenek-box ${tip === 'select' ? 'is-open' : ''}">
                <label class="egf-label !text-[#9a3412]">Seçenek listesi</label>
                <p class="text-[11px] text-amber-900/80 mt-1 mb-2 leading-relaxed">
                    Alan tipi <strong>Seçim listesi</strong> olduğu için seçenekleri yazın.
                    Her satıra <strong>bir</strong> seçenek koyun.
                </p>
                <textarea name="alanlar[${i}][secenekler]" rows="3"
                          placeholder="Örnek:&#10;Sabah&#10;Öğlen&#10;Akşam"
                          class="egf-input !mt-0 resize-y" ${tip === 'select' ? '' : 'disabled'}>${esc(data.secenekler || '')}</textarea>
            </div>

            <div class="egf-toggle-row mt-3 !bg-white">
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-800 font-display">Bu soru zorunlu olsun</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Açıksa katılımcı boş bırakamaz</div>
                </div>
                <label class="egf-toggle">
                    <input type="checkbox" name="alanlar[${i}][zorunlu_mu]" value="1" ${data.zorunlu_mu ? 'checked' : ''}>
                    <span class="egf-toggle-track"></span>
                </label>
            </div>
        `;

        alanListesi.appendChild(wrap);

        const tipSelect = wrap.querySelector('select.egf-tip-select');
        tipSelect?.addEventListener('change', function () {
            syncSecenekBox(wrap);
        });
        wrap.querySelector('.egf-remove-btn')?.addEventListener('click', function () {
            const $tip = window.jQuery ? jQuery(wrap).find('select.egf-tip-select') : null;
            if ($tip && $tip.data('select2')) $tip.select2('destroy');
            wrap.remove();
            if (window.alanBosGuncelle) window.alanBosGuncelle();
        });

        syncSecenekBox(wrap);

        // Select2 (layout yüklendikten sonra da çalışsın)
        setTimeout(function () {
            if (window.jQuery) initSelect2(jQuery(tipSelect));
        }, 0);
    }

    const alanBos = document.getElementById('alanBos');
    window.alanBosGuncelle = function () {
        if (!alanBos) return;
        alanBos.classList.toggle('hidden', alanListesi.children.length > 0);
    };

    window.alanEkle = function () {
        alanSatiri({});
        window.alanBosGuncelle();
    };

    mevcut.forEach((d, i) => alanSatiri(d, i));
    window.alanBosGuncelle();

    // Sayfa select'leri (tip / durum) + sonradan eklenenler
    function bootSelects() {
        if (!window.jQuery || !jQuery.fn.select2) return;
        jQuery('select.egf-select, select.egf-tip-select').each(function () {
            initSelect2(jQuery(this));
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(bootSelects, 50);
        });
    } else {
        setTimeout(bootSelects, 50);
    }
    // Layout select2 de init ediyor; çakışmayı toparla
    setTimeout(bootSelects, 300);

    // Kapak önizleme
    const input = document.getElementById('kapakInput');
    const img = document.getElementById('kapakImg');
    const ph = document.getElementById('kapakPlaceholder');
    input?.addEventListener('change', function () {
        const f = this.files && this.files[0];
        if (!f || !img) return;
        const url = URL.createObjectURL(f);
        img.src = url;
        img.classList.remove('hidden');
        if (ph) ph.classList.add('hidden');
    });

    // SEO karakter sayacı
    function bindCounter(el) {
        if (!el) return;
        const id = el.getAttribute('data-counter');
        const max = parseInt(el.getAttribute('data-max') || '0', 10);
        const out = id ? document.getElementById(id) : null;
        function tick() {
            if (!out) return;
            const n = (el.value || '').length;
            out.textContent = max ? (n + ' / ~' + max) : String(n);
            out.classList.toggle('text-amber-600', max && n > max);
            out.classList.toggle('text-slate-400', !(max && n > max));
        }
        el.addEventListener('input', tick);
        tick();
    }
    bindCounter(document.getElementById('meta_baslik'));
    bindCounter(document.getElementById('meta_aciklama'));
})();
</script>

<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // CKEditor
    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.config.versionCheck = false;
        CKEDITOR.replace('icerik', {
            language: 'tr',
            height: 360,
            removeButtons: 'About',
            uiColor: '#FFFFFF',
            allowedContent: true
        });
    }

    const form = document.getElementById('egitimForm');
    form?.addEventListener('submit', function () {
        if (typeof CKEDITOR !== 'undefined') {
            for (const name in CKEDITOR.instances) {
                if (Object.prototype.hasOwnProperty.call(CKEDITOR.instances, name)) {
                    CKEDITOR.instances[name].updateElement();
                }
            }
        }
        // Tag input'ta yazılı kalan metni de kaydet
        if (typeof window.egfFlushTags === 'function') {
            window.egfFlushTags();
        }
    });

    // Tag sistemi (blog ile aynı mantık)
    const hiddenInput = document.getElementById('meta_anahtar_kelimeler');
    const tagContainer = document.getElementById('tagContainer');
    const tagInput = document.getElementById('tagInput');
    if (!hiddenInput || !tagContainer || !tagInput) return;

    let tags = [];
    if (hiddenInput.value.trim() !== '') {
        tags = hiddenInput.value.split(',').map(t => t.trim()).filter(Boolean);
    }

    function renderTags() {
        tagContainer.querySelectorAll('.tag-badge').forEach(b => b.remove());
        tags.forEach((tag, index) => {
            const badge = document.createElement('span');
            badge.className = 'tag-badge inline-flex items-center gap-1.5 px-2.5 py-1 bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/35 rounded-full text-[10px] font-bold font-display select-none transition-all hover:bg-[#FFF2E2]';
            badge.innerHTML = `
                <span></span>
                <button type="button" class="tag-remove text-[#C96A2B] hover:text-red-600 transition-colors focus:outline-none font-bold text-xs leading-none" data-index="${index}" aria-label="Sil">&times;</button>
            `;
            badge.querySelector('span').textContent = tag;
            tagContainer.insertBefore(badge, tagInput);
        });
        hiddenInput.value = tags.join(',');
    }

    function addTag() {
        const val = tagInput.value.trim().replace(/,/g, '');
        if (val !== '' && !tags.includes(val)) {
            tags.push(val);
            renderTags();
        }
        tagInput.value = '';
    }

    window.egfFlushTags = function () {
        addTag();
        hiddenInput.value = tags.join(',');
    };

    tagInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addTag();
        } else if (e.key === 'Backspace' && tagInput.value === '' && tags.length) {
            tags.pop();
            renderTags();
        }
    });
    tagInput.addEventListener('blur', addTag);
    tagContainer.addEventListener('click', function (e) {
        const btn = e.target.closest('.tag-remove');
        if (btn) {
            e.preventDefault();
            const idx = parseInt(btn.getAttribute('data-index'), 10);
            if (!isNaN(idx)) {
                tags.splice(idx, 1);
                renderTags();
            }
            return;
        }
        tagInput.focus();
    });

    renderTags();
});
</script>
@endsection
