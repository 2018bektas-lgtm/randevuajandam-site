@extends('klinik.layout')

@section('baslik', 'Klinik Web Sitesi - ' . $klinik->ad)
@section('sayfa_baslik', 'Klinik Web Sitesi')

@section('icerik')
<div class="space-y-6">
    @if(session('basari'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">{{ session('basari') }}</div>
    @endif
    @if(session('hata'))
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold">{{ session('hata') }}</div>
    @endif

    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 text-sm leading-relaxed">
        <strong class="block mb-1">Klinik Kurumsal paketi</strong>
        Özel web sitesi yalnızca en yüksek klinik paketinde sunulur. Domain tanımlayın, API anahtarlarını
        <code class="text-xs bg-white/80 px-1 rounded">kliniksitesi</code> entegrasyon sayfasına girin.
    </div>

    @if($webSite)
        <div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-sm overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-[#E5E7EB] bg-gradient-to-r from-orange-50/50 to-white">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-xl font-bold font-display text-[#111827]">Alan adı aktif</h2>
                            <span class="px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-700 rounded-full">{{ $webSite->durum }}</span>
                        </div>
                        <p class="text-sm text-[#6B7280] mt-1">Klinik web siteniz platforma bağlandı. Anahtarları sitedeki entegrasyon paneline yapıştırın.</p>
                    </div>
                    <a href="{{ $webSite->siteUrl() }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#111827] text-white text-sm font-semibold hover:bg-slate-800 transition">
                        Siteyi aç
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB]">
                        <span class="text-[10px] uppercase tracking-wider font-bold text-[#9CA3AF]">Domain</span>
                        <div class="text-sm font-semibold text-[#111827] mt-1">{{ $webSite->domain }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB]">
                        <span class="text-[10px] uppercase tracking-wider font-bold text-[#9CA3AF]">API Platform</span>
                        <div class="text-sm font-semibold text-[#111827] mt-1 font-mono text-xs break-all">{{ url('/api/v1') }} → api servisi (port 8001)</div>
                    </div>
                </div>

                <div class="border-t border-[#E5E7EB] pt-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div>
                            <h3 class="font-bold font-display text-[#111827]">API anahtarları</h3>
                            <p class="text-xs text-[#6B7280]">kliniksitesi → API Entegrasyon sayfasına girin.</p>
                        </div>
                        @if($apiKey)
                        <form action="{{ route('hekim.klinik.web-sitesi.api-anahtari') }}" method="POST"
                              onsubmit="return confirm('Anahtarlar yenilenecek; sitedeki entegrasyonu güncellemeniz gerekir. Devam?')">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-100">
                                Anahtarları yenile
                            </button>
                        </form>
                        @endif
                    </div>

                    @if($apiKey)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA]">
                                <span class="text-[10px] uppercase tracking-wider font-bold text-[#9CA3AF]">X-Api-Key</span>
                                <code class="block mt-1 text-sm font-mono font-semibold text-[#111827] break-all" id="apiKeyVal">{{ $apiKey->api_key }}</code>
                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('apiKeyVal').textContent)" class="mt-2 text-xs font-semibold text-[#C96A2B]">Kopyala</button>
                            </div>
                            <div class="p-4 rounded-xl border border-[#E5E7EB] bg-[#FAFAFA]">
                                <span class="text-[10px] uppercase tracking-wider font-bold text-[#9CA3AF]">X-Api-Secret</span>
                                @if(!empty($plainSecret))
                                    <p class="text-[11px] font-bold text-amber-700 mt-1">Yalnızca bu sefer — hemen kopyalayın!</p>
                                    <code class="block mt-1 text-sm font-mono font-semibold text-[#111827] break-all" id="secretKeyVal">{{ $plainSecret }}</code>
                                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('secretKeyVal').textContent)" class="mt-2 text-xs font-semibold text-[#C96A2B]">Kopyala</button>
                                @else
                                    <code class="block mt-1 text-sm font-mono font-semibold text-[#9CA3AF]">•••••••• (hash’li — tekrar görülemez)</code>
                                    <p class="text-[11px] text-[#9CA3AF] mt-1">Kayıpsa “Anahtarları yenile”.</p>
                                @endif
                            </div>
                        </div>
                        <p class="text-[11px] text-[#9CA3AF] mt-3">
                            Platform URL (kliniksitesi): <code class="bg-slate-100 px-1 rounded">http://127.0.0.1:8001/api/v1</code> (canlıda api domain’iniz)
                        </p>
                    @else
                        <form action="{{ route('hekim.klinik.web-sitesi.api-anahtari') }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#C96A2B] text-white text-sm font-bold">API anahtarı oluştur</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @else
        <form action="{{ route('hekim.klinik.web-sitesi.kurulum.post') }}" method="POST" class="bg-white rounded-2xl border border-[#E5E7EB] shadow-sm p-6 sm:p-8 space-y-6">
            @csrf
            <div>
                <h2 class="text-lg font-bold font-display text-[#111827]">Klinik domain tanımlayın</h2>
                <p class="text-sm text-[#6B7280] mt-1">Örn: kliniginiz.com — DNS’i sunucuya yönlendirdikten sonra siteyi deploy edin.</p>
            </div>
            <div>
                <label for="domain" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Alan adı</label>
                <input type="text" name="domain" id="domain" required value="{{ old('domain') }}"
                       placeholder="kliniginiz.com"
                       class="w-full max-w-xl bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                @error('domain')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="px-5 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-sm font-bold font-display transition">
                Kaydet ve API anahtarı oluştur
            </button>
        </form>
    @endif
</div>
@endsection
