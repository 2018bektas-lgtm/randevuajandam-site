@extends('yonetim.layout')

@section('baslik', 'Ödeme Ayarları - Randevu Ajandam')
@section('sayfa_baslik', 'Ödeme Ayarları')

@section('icerik')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('yonetim.odeme-ayarlari.post') }}" method="POST" class="space-y-6">
            @csrf
            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <h3 class="border-b border-slate-100 pb-3 text-lg font-bold text-slate-900">iyzico kredi kartı</h3>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">API anahtarı ve secret key birlikte tanımlandığında hekimler ödeme ekranında kredi kartı veya havale seçeneklerini görür. Alanlar boş bırakılırsa mevcut kayıtlı anahtarlar korunur.</p>
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">API Key</label><input type="password" name="iyzico_api_key" autocomplete="new-password" placeholder="{{ $ayarlar->iyzico_api_key ? 'Kayıtlı anahtarı değiştirmek için girin' : 'iyzico API key' }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono"></div>
                    <div><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">Secret Key</label><input type="password" name="iyzico_secret_key" autocomplete="new-password" placeholder="{{ $ayarlar->iyzico_secret_key ? 'Kayıtlı anahtarı değiştirmek için girin' : 'iyzico secret key' }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono"></div>
                    <div class="sm:col-span-2"><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">API adresi</label><input type="url" name="iyzico_base_url" value="{{ old('iyzico_base_url', $ayarlar->iyzico_base_url) }}" placeholder="https://api.iyzipay.com" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono"><p class="mt-1 text-[11px] text-slate-400">Canlı: https://api.iyzipay.com · Test: https://sandbox-api.iyzipay.com</p></div>
                </div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <h3 class="border-b border-slate-100 pb-3 text-lg font-bold text-slate-900">Banka havalesi</h3>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">Bu bilgiler ödeme sayfasında gösterilir. Hekimin gönderdiği havale referansı yönetici onayı bekleyen üyelik ödemelerine eklenir.</p>
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">Banka adı</label><input type="text" name="banka_adi" value="{{ old('banka_adi', $ayarlar->banka_adi) }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs"></div>
                    <div><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">Hesap sahibi</label><input type="text" name="banka_hesap_sahibi" value="{{ old('banka_hesap_sahibi', $ayarlar->banka_hesap_sahibi) }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs"></div>
                    <div class="sm:col-span-2"><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">IBAN</label><input type="text" name="banka_iban" value="{{ old('banka_iban', $ayarlar->banka_iban) }}" placeholder="TR000000000000000000000000" maxlength="26" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono uppercase">@error('banka_iban')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror</div>
                    <div class="sm:col-span-2"><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">Havale notu</label><textarea name="banka_aciklama" rows="3" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs">{{ old('banka_aciklama', $ayarlar->banka_aciklama) }}</textarea></div>
                </div>
            </div>
            <div class="flex justify-end"><button class="rounded-xl bg-[#C96A2B] px-7 py-3 text-xs font-bold uppercase tracking-wider text-white">Ödeme ayarlarını kaydet</button></div>
        </form>
    </div>
@endsection
