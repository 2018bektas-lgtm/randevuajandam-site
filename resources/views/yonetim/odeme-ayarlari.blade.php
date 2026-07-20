@extends('yonetim.layout')

@section('baslik', 'Ödeme Ayarları - Randevu Ajandam')
@section('sayfa_baslik', 'Ödeme Ayarları')

@section('icerik')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('yonetim.odeme-ayarlari.post') }}" method="POST" class="space-y-6">
            @csrf

            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <h3 class="border-b border-slate-100 pb-3 text-lg font-bold text-slate-900">PayTR (kartlı ödeme)</h3>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">
                    Mağaza paneli → <strong>Bilgi</strong> sayfasından <code>merchant_id</code>, <code>merchant_key</code> ve <code>merchant_salt</code> alın.
                    Bildirim URL: <code class="text-[11px] bg-slate-50 px-1 rounded">{{ url('/api/paytr/notify') }}</code>
                </p>
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">Merchant ID</label>
                        <input type="text" name="paytr_merchant_id" value="{{ old('paytr_merchant_id', $ayarlar->paytr_merchant_id) }}"
                               placeholder="Mağaza no" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="paytr_test_mode" value="1" class="rounded border-slate-300 text-[#C96A2B]"
                                   {{ old('paytr_test_mode', $ayarlar->paytr_test_mode ?? true) ? 'checked' : '' }}>
                            Test modu (PayTR test işlemleri)
                        </label>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">Merchant Key</label>
                        <input type="password" name="paytr_merchant_key" autocomplete="new-password"
                               placeholder="{{ $ayarlar->paytr_merchant_key ? 'Kayıtlı key — değiştirmek için girin' : 'merchant_key' }}"
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">Merchant Salt</label>
                        <input type="password" name="paytr_merchant_salt" autocomplete="new-password"
                               placeholder="{{ $ayarlar->paytr_merchant_salt ? 'Kayıtlı salt — değiştirmek için girin' : 'merchant_salt' }}"
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono">
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <h3 class="border-b border-slate-100 pb-3 text-lg font-bold text-slate-900">Banka havalesi</h3>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">Bu bilgiler ödeme sayfasında gösterilir. Hekimin gönderdiği havale referansı yönetici onayı bekleyen üyelik ödemelerine eklenir.</p>
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">Banka adı</label><input type="text" name="banka_adi" value="{{ old('banka_adi', $ayarlar->banka_adi) }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs"></div>
                    <div><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">Hesap sahibi</label><input type="text" name="banka_hesap_sahibi" value="{{ old('banka_hesap_sahibi', $ayarlar->banka_hesap_sahibi) }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs"></div>
                    <div class="sm:col-span-2"><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">IBAN</label><input type="text" name="banka_iban" value="{{ old('banka_iban', $ayarlar->banka_iban) }}" placeholder="TR000000000000000000000000" maxlength="34" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono uppercase">@error('banka_iban')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror</div>
                    <div class="sm:col-span-2"><label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">Havale notu</label><textarea name="banka_aciklama" rows="3" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs">{{ old('banka_aciklama', $ayarlar->banka_aciklama) }}</textarea></div>
                </div>
            </div>

            <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-5 text-[11px] text-slate-500 leading-relaxed">
                <strong class="text-slate-700">Not:</strong> Eski iyzico alanları veritabanında durur ancak kartlı ödeme artık PayTR üzerinden yapılır.
                Abonelik otomatik yenilenmez; dönem sonunda hekim yeniden ödeme alır.
            </div>

            <div class="flex justify-end"><button class="rounded-xl bg-[#C96A2B] px-7 py-3 text-xs font-bold uppercase tracking-wider text-white">Ödeme ayarlarını kaydet</button></div>
        </form>
    </div>
@endsection
