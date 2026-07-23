@extends('yonetim.layout')

@section('baslik', 'Ödeme Ayarları - Randevu Ajandam')
@section('sayfa_baslik', 'Ödeme Ayarları')

@section('icerik')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('yonetim.odeme-ayarlari.post') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Sağlayıcı seçimi --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <h3 class="border-b border-slate-100 pb-3 text-lg font-bold text-slate-900">Aktif ödeme sağlayıcısı</h3>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">
                    Tüm kartlı ödemeler seçilen sağlayıcı üzerinden gerçekleşir.
                    İkinci sağlayıcının kimlik bilgilerini kaydetmiş olsanız bile yalnızca seçili olanı aktif olur.
                </p>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 px-4 py-3 transition
                        {{ ($ayarlar->odeme_saglayici ?? 'paytr') === 'paytr' ? 'border-[#C96A2B] bg-orange-50' : 'border-slate-200' }}"
                        id="label-paytr">
                        <input type="radio" name="odeme_saglayici" value="paytr"
                               {{ ($ayarlar->odeme_saglayici ?? 'paytr') === 'paytr' ? 'checked' : '' }}
                               class="mt-0.5 text-[#C96A2B]" onchange="switchProvider('paytr')">
                        <span>
                            <span class="block text-xs font-bold text-slate-800">PayTR</span>
                            <span class="block text-[11px] text-slate-500 leading-snug">iFrame, güvenli kart girişi. Recurring ödeme destekli.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 px-4 py-3 transition
                        {{ ($ayarlar->odeme_saglayici ?? '') === 'iyzico' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200' }}"
                        id="label-iyzico">
                        <input type="radio" name="odeme_saglayici" value="iyzico"
                               {{ ($ayarlar->odeme_saglayici ?? '') === 'iyzico' ? 'checked' : '' }}
                               class="mt-0.5 text-indigo-600" onchange="switchProvider('iyzico')">
                        <span>
                            <span class="block text-xs font-bold text-slate-800">iyzico</span>
                            <span class="block text-[11px] text-slate-500 leading-snug">Abonelik API, otomatik yenileme. API anahtarları aşağıda.</span>
                        </span>
                    </label>
                </div>
                <input type="hidden" name="iyzico_enabled" value="0">
                <input type="hidden" name="iyzico_enabled" value="{{ ($ayarlar->odeme_saglayici ?? '') === 'iyzico' ? '1' : '0' }}" id="iyzico_enabled_field">
            </div>

            {{-- PayTR --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm" id="section-paytr">
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

            {{-- iyzico --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm" id="section-iyzico"
                 style="{{ ($ayarlar->odeme_saglayici ?? 'paytr') !== 'iyzico' ? 'display:none' : '' }}">
                <h3 class="border-b border-slate-100 pb-3 text-lg font-bold text-slate-900">iyzico (abonelik API)</h3>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">
                    iyzico merchant paneli → API bilgileri. Webhook URL:
                    <code class="text-[11px] bg-slate-50 px-1 rounded">{{ url('/api/iyzico/webhook') }}</code><br>
                    Base URL: <strong>Sandbox</strong> → <code class="text-[11px]">https://sandbox-api.iyzipay.com</code> |
                    <strong>Canlı</strong> → <code class="text-[11px]">https://api.iyzipay.com</code>
                </p>
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">API Key</label>
                        <input type="password" name="iyzico_api_key" autocomplete="new-password"
                               placeholder="{{ ($ayarlar->iyzico_api_key ?? '') ? 'Kayıtlı API key — değiştirmek için girin' : 'sandbox-...' }}"
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">Secret Key</label>
                        <input type="password" name="iyzico_secret_key" autocomplete="new-password"
                               placeholder="{{ ($ayarlar->iyzico_secret_key ?? '') ? 'Kayıtlı secret — değiştirmek için girin' : 'sandbox-...' }}"
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-700">Base URL</label>
                        <input type="url" name="iyzico_base_url"
                               value="{{ old('iyzico_base_url', $ayarlar->iyzico_base_url ?? 'https://sandbox-api.iyzipay.com') }}"
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-mono">
                    </div>
                </div>
            </div>

            {{-- Banka havalesi --}}
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

            <div class="flex justify-end"><button class="rounded-xl bg-[#C96A2B] px-7 py-3 text-xs font-bold uppercase tracking-wider text-white">Ödeme ayarlarını kaydet</button></div>
        </form>
    </div>

    <script>
    function switchProvider(val) {
        document.getElementById('section-paytr').style.display = val === 'paytr' ? '' : 'none';
        document.getElementById('section-iyzico').style.display = val === 'iyzico' ? '' : 'none';
        document.getElementById('iyzico_enabled_field').value = val === 'iyzico' ? '1' : '0';
        document.getElementById('label-paytr').className = document.getElementById('label-paytr').className
            .replace(/border-\[#C96A2B\] bg-orange-50|border-slate-200/, val === 'paytr' ? 'border-[#C96A2B] bg-orange-50' : 'border-slate-200');
        document.getElementById('label-iyzico').className = document.getElementById('label-iyzico').className
            .replace(/border-indigo-500 bg-indigo-50|border-slate-200/, val === 'iyzico' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200');
    }
    </script>
@endsection
