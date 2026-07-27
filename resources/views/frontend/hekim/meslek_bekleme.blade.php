@extends('frontend.layouts.app')

@section('baslik', 'Meslek Belgesi Onayı - Randevu Ajandam')

@section('icerik')
<section class="fe-page relative bg-[#FAFAFA] overflow-hidden">
    <div class="fe-container max-w-xl">
        @if(session('basarili'))
            <div class="mb-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-xs text-emerald-800 font-semibold">{{ session('basarili') }}</div>
        @endif
        @if(session('hata'))
            <div class="mb-4 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-xs text-red-700 font-semibold">{{ session('hata') }}</div>
        @endif

        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 sm:p-8 shadow-sm space-y-5">
            @if($doktor->isMeslekBeklemede())
                <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-700 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-[#111827] font-display tracking-tight">Belgeleriniz inceleniyor</h1>
                    <p class="mt-2 text-sm text-[#6B7280] leading-relaxed">
                        Kaydınız alındı. Yönetici ekibimiz T.C. kimlik ve diploma/hekimlik belgenizi kontrol ederek
                        <strong class="text-[#111827]">gerçek bir hekim kaydı olup olmadığını</strong> doğrular.
                        Onaylanınca seçtiğiniz paket için doğrudan ödemeye geçersiniz.
                    </p>
                </div>
                @if($doktor->kayitPaketi)
                    <div class="rounded-2xl border border-[#E7B58A]/40 bg-[#FFF7ED] px-4 py-3 text-xs">
                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-[#C96A2B]">Seçilen paket</p>
                        <p class="mt-1 font-bold text-[#111827]">{{ $doktor->kayitPaketi->ad }}</p>
                        <p class="mt-0.5 text-slate-600">
                            {{ ($doktor->kayit_periyot ?? 'aylik') === 'yillik' ? 'Yıllık' : 'Aylık' }} · Fiyatlara KDV dahildir
                        </p>
                    </div>
                @endif
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                        <dt class="text-[10px] font-bold uppercase text-slate-500">T.C. Kimlik</dt>
                        <dd class="mt-1 font-mono font-semibold text-slate-800">{{ $doktor->tc_kimlik_no ?: '—' }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                        <dt class="text-[10px] font-bold uppercase text-slate-500">Diploma / tescil no</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $doktor->diploma_no ?: '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2 rounded-xl bg-slate-50 border border-slate-100 p-3">
                        <dt class="text-[10px] font-bold uppercase text-slate-500">e-Devlet barkod</dt>
                        <dd class="mt-1 font-mono font-semibold text-slate-800">{{ $doktor->edevlet_barkod ?: 'Girilmedi' }}</dd>
                    </div>
                    <div class="sm:col-span-2 rounded-xl bg-slate-50 border border-slate-100 p-3">
                        <dt class="text-[10px] font-bold uppercase text-slate-500">Yüklenen belge</dt>
                        <dd class="mt-1">
                            @if($doktor->meslek_belge_yolu)
                                <a href="{{ route('frontend.hekim.meslek.belge') }}" target="_blank" class="text-[#C96A2B] font-bold underline">Belgeyi görüntüle</a>
                            @else
                                <span class="text-slate-500">Yüklenmemiş</span>
                            @endif
                        </dd>
                    </div>
                </dl>
                <p class="text-[11px] text-[#9CA3AF]">Onaylandığında bu sayfa otomatik olarak ödeme (veya domain) adımına yönlendirilir. E-posta ile de bilgilendirileceksiniz.</p>
                <p id="meslek-poll-status" class="text-[11px] text-amber-700 font-semibold">Durum otomatik kontrol ediliyor…</p>
                <a href="{{ route('frontend.hekim.meslek.bekleme') }}" class="inline-flex w-full justify-center py-3 rounded-xl bg-[#C96A2B] text-white text-xs font-bold uppercase tracking-wider">Durumu yenile</a>

                <script>
                    (function () {
                        const statusEl = document.getElementById('meslek-poll-status');
                        const url = @json(route('frontend.hekim.meslek.durum'));
                        let ticks = 0;
                        async function check() {
                            try {
                                const res = await fetch(url, {
                                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                    credentials: 'same-origin'
                                });
                                if (!res.ok) return;
                                const data = await res.json();
                                ticks++;
                                if (statusEl) {
                                    statusEl.textContent = data.can_proceed
                                        ? 'Onaylandı — yönlendiriliyorsunuz…'
                                        : ('Beklemede · son kontrol ' + new Date().toLocaleTimeString('tr-TR'));
                                }
                                if (data.can_proceed && data.redirect) {
                                    window.location.href = data.redirect;
                                    return;
                                }
                            } catch (e) { /* ignore */ }
                            setTimeout(check, 20000);
                        }
                        setTimeout(check, 8000);
                    })();
                </script>

            @elseif($doktor->isMeslekReddedildi())
                <div class="w-12 h-12 rounded-2xl bg-red-50 border border-red-100 text-red-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-[#111827] font-display tracking-tight">Belge onaylanmadı</h1>
                    <p class="mt-2 text-sm text-[#6B7280] leading-relaxed">
                        İnceleme sonucu kaydınız reddedildi. Lütfen net okunan diploma/hekimlik belgesi ve doğru kimlik bilgileriyle yeniden gönderin.
                    </p>
                    @if($doktor->meslek_dogrulama_notu)
                        <p class="mt-2 text-xs font-semibold text-red-700 bg-red-50 border border-red-100 rounded-xl px-3 py-2">
                            Not: {{ $doktor->meslek_dogrulama_notu }}
                        </p>
                    @endif
                </div>

                <form action="{{ route('frontend.hekim.meslek.yenile') }}" method="POST" enctype="multipart/form-data" class="space-y-4 pt-2 border-t border-slate-100">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-[#4B5563] uppercase mb-1.5">T.C. Kimlik No</label>
                        <input type="text" name="tc_kimlik_no" value="{{ old('tc_kimlik_no', $doktor->tc_kimlik_no) }}" maxlength="11" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs font-mono">
                        @error('tc_kimlik_no')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#4B5563] uppercase mb-1.5">Diploma / tescil no</label>
                        <input type="text" name="diploma_no" value="{{ old('diploma_no', $doktor->diploma_no) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#4B5563] uppercase mb-1.5">e-Devlet barkod (opsiyonel)</label>
                        <input type="text" name="edevlet_barkod" value="{{ old('edevlet_barkod', $doktor->edevlet_barkod) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs font-mono uppercase">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#4B5563] uppercase mb-1.5">Yeni belge (PDF/JPG/PNG)</label>
                        <input type="file" name="meslek_belgesi" accept=".pdf,.jpg,.jpeg,.png" required class="w-full text-xs">
                    </div>
                    <button type="submit" class="w-full py-3 rounded-xl bg-[#C96A2B] text-white text-xs font-bold uppercase tracking-wider">Yeniden gönder</button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection
