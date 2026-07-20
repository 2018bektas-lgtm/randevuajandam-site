@extends('frontend.layouts.app')

@section('baslik', 'Hesap Oluştur')

@section('icerik')
<section class="fe-page">
    <div class="max-w-lg mx-auto px-4">
        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 sm:p-8 shadow-md">
            <p class="text-[10px] font-bold uppercase tracking-wider text-[#C96A2B] font-display">Soft account</p>
            <h1 class="mt-2 text-xl font-bold text-[#111827] font-display">Hesabınızı tamamlayın</h1>
            <p class="mt-2 text-sm text-slate-500">
                Randevunuz zaten sistemde. E-posta ve şifre belirleyerek randevularınızı panelden yönetebilirsiniz.
            </p>

            @if(session('hata'))
                <div class="mt-4 p-3 bg-red-50 border border-red-100 rounded-xl text-sm text-red-700">{{ session('hata') }}</div>
            @endif

            <form method="POST" action="{{ route('frontend.randevu.yonet.hesap.post', $token) }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">E-posta</label>
                    <input type="email" name="e_posta" required value="{{ old('e_posta', $placeholder ? '' : $hasta?->e_posta) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#C96A2B]"
                           placeholder="ornek@mail.com">
                    @error('e_posta') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Şifre</label>
                    <input type="password" name="sifre" required minlength="6"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#C96A2B]">
                    @error('sifre') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Şifre (tekrar)</label>
                    <input type="password" name="sifre_confirmation" required minlength="6"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-[#C96A2B]">
                </div>
                <button type="submit" class="w-full py-3 rounded-xl bg-[#C96A2B] text-white font-bold text-xs uppercase tracking-wider">
                    Hesabı Oluştur ve Giriş Yap
                </button>
            </form>

            <a href="{{ route('frontend.randevu.yonet', $token) }}" class="block text-center text-xs text-slate-500 mt-4">← Randevu özetine dön</a>
        </div>
    </div>
</section>
@endsection
