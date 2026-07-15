@extends('frontend.layouts.app')

@section('baslik', 'Randevu Yönetimi')

@section('icerik')
<section class="py-12 sm:py-16">
    <div class="max-w-lg mx-auto px-4">
        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 sm:p-8 shadow-md">
            <p class="text-[10px] font-bold uppercase tracking-wider text-[#C96A2B] font-display">Randevu yönetimi</p>
            <h1 class="mt-2 text-xl font-bold text-[#111827] font-display">Talebiniz kaydedildi</h1>

            @if(session('basarili'))
                <div class="mt-4 p-3 bg-emerald-50 border border-emerald-100 rounded-xl text-sm text-emerald-800">
                    {{ session('basarili') }}
                </div>
            @endif
            @if(session('hata'))
                <div class="mt-4 p-3 bg-red-50 border border-red-100 rounded-xl text-sm text-red-700">
                    {{ session('hata') }}
                </div>
            @endif

            <dl class="mt-6 space-y-3 text-sm">
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Hekim</dt>
                    <dd class="font-semibold text-slate-900 text-right">{{ $randevu->doktor?->unvan }} {{ $randevu->doktor?->ad_soyad }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Hizmet</dt>
                    <dd class="font-semibold text-slate-900">{{ $randevu->hizmet?->ad ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Tarih / Saat</dt>
                    <dd class="font-semibold text-slate-900">
                        {{ $randevu->tarih instanceof \DateTimeInterface ? $randevu->tarih->format('d.m.Y') : $randevu->tarih }}
                        · {{ \Illuminate\Support\Str::substr($randevu->saat, 0, 5) }}
                    </dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Durum</dt>
                    <dd class="font-semibold text-slate-900 capitalize">{{ $randevu->durum }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Görüşme</dt>
                    <dd class="font-semibold text-slate-900">
                        {{ ($randevu->gorusme_tipi ?? 'yuz_yuze') === 'online' ? 'Online (platform)' : 'Yüz yüze' }}
                    </dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Hasta</dt>
                    <dd class="font-semibold text-slate-900">{{ $randevu->ad }} {{ $randevu->soyad }}</dd>
                </div>
            </dl>

            <div class="mt-8 space-y-3">
                @if(($randevu->gorusme_tipi ?? '') === 'online' && $randevu->durum === 'onaylandi' && $randevu->meeting_join_token)
                    <a href="{{ route('frontend.gorusme.join', $randevu->meeting_join_token) }}"
                       class="block w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider text-center shadow-sm">
                        Görüşmeye Katıl
                    </a>
                    <p class="text-[11px] text-center text-slate-500 -mt-1">
                        Görüntülü görüşme sitemiz üzerinden açılır (Zoom linki gerekmez). Randevu saatine yakın aktif olur.
                    </p>
                @elseif(($randevu->gorusme_tipi ?? '') === 'online' && $randevu->durum === 'beklemede')
                    <div class="p-3 rounded-xl bg-sky-50 border border-sky-100 text-xs text-sky-900 text-center">
                        Online görüşme odası, hekim onayından sonra hazırlanacaktır.
                    </div>
                @endif

                @if(! in_array($randevu->durum, ['iptal'], true))
                    <a href="{{ route('frontend.randevu.yonet.ical', $token) }}"
                       class="block w-full py-3 rounded-xl border border-slate-200 text-slate-800 font-bold text-xs uppercase tracking-wider text-center hover:bg-slate-50"
                       title="Google / Outlook takvimine ekle">
                        Takvime Ekle (iCal)
                    </a>
                @endif

                @if(in_array($randevu->durum, ['beklemede', 'onaylandi'], true))
                    <form method="POST" action="{{ route('frontend.randevu.yonet.iptal', $token) }}" onsubmit="return confirm('Randevuyu iptal etmek istediğinize emin misiniz?');">
                        @csrf
                        <button type="submit" class="w-full py-3 rounded-xl border border-red-200 text-red-700 font-bold text-xs uppercase tracking-wider hover:bg-red-50">
                            Randevuyu İptal Et
                        </button>
                    </form>
                @endif

                <a href="{{ route('frontend.randevu.yonet.hesap', $token) }}"
                   class="block w-full py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider text-center">
                    Hesap Oluştur / Randevularımı Yönet
                </a>

                <a href="{{ url('/') }}" class="block text-center text-xs text-slate-500 hover:text-slate-800 pt-2">
                    Ana sayfaya dön
                </a>
            </div>

            <p class="mt-6 text-[11px] text-slate-400 leading-relaxed">
                Bu sayfa size özel bir bağlantıdır. Güvenliğiniz için bağlantıyı başkalarıyla paylaşmayın.
            </p>
        </div>
    </div>
</section>
@endsection
