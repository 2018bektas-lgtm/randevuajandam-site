@extends('hekim.layout')

@section('baslik', 'Kişisel Web Sitesi Entegrasyonu - Hekim Paneli')

@section('icerik')
<div class="space-y-8">
    <!-- Header section -->
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 font-display">Kişisel Web Sitesi Entegrasyonu</h1>
            <p class="text-sm text-gray-500">Kendi özel alan adınız (dr-adiniz.com) ile Randevu Ajandam arasındaki veri senkronizasyonunu yönetin.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('basarili'))
        <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('basarili') }}</span>
        </div>
    @endif

    @if(session('hata'))
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <span>{{ session('hata') }}</span>
        </div>
    @endif

    {{-- Platform vitrin görünürlüğü (web paketi) --}}
    @if($canHide ?? false)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 md:p-8">
                <div class="flex items-start gap-4 mb-5">
                    <div class="p-3 bg-slate-100 text-slate-700 rounded-xl shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-bold text-gray-900 font-display">Randevu Ajandam vitrini</h2>
                        <p class="text-sm text-gray-500 mt-1 leading-relaxed">
                            Kendi web sitenizi kullanıyorsanız ana sitede listelenmeyi kapatabilirsiniz.
                            <strong class="text-gray-700">Hekim paneliniz ve kişisel web siteniz çalışmaya devam eder.</strong>
                            Arama sonuçları, hekim listesi, sitemap ve ana sitedeki profil URL’si gizlenir.
                        </p>
                    </div>
                    <span class="shrink-0 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide rounded-full border
                        {{ ($platformdaGorunur ?? true) ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                        {{ ($platformdaGorunur ?? true) ? 'Listeleniyor' : 'Gizli' }}
                    </span>
                </div>

                <form method="POST" action="{{ route('hekim.web-sitesi.platform-gorunurluk') }}" class="space-y-4">
                    @csrf
                    <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-100 bg-gray-50/80 cursor-pointer hover:border-orange-200 transition-colors">
                        <input type="checkbox" name="platformda_gorunur" value="1" class="mt-1 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                               @checked($platformdaGorunur ?? true)>
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">Ana platformda listelen</span>
                            <span class="block text-xs text-gray-500 mt-0.5">Kapalıyken hastalar sizi randevuajandam aramasında bulamaz; kendi domain’inizden randevu almaya devam eder.</span>
                        </span>
                    </label>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold shadow-sm transition-all">
                        Görünürlüğü kaydet
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if($webSite)
        <!-- Registered Web Site & API Keys Display -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-orange-50/40 to-amber-50/10">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-orange-100 text-orange-600 rounded-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.98 0-5.682-1.089-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-.778.099-1.533.284-2.253m0 0L3 12"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-xl font-bold text-gray-900 font-display">Alan Adı Kaydınız Aktif!</h2>
                                <span class="px-2.5 py-0.5 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Aktif</span>
                            </div>
                            <p class="text-gray-500 text-sm mt-1">Hekim web siteniz sisteme tanımlandı ve API bağlantısı hazırlandı.</p>
                        </div>
                    </div>
                    <a href="http://{{ $webSite->domain }}" target="_blank" class="px-5 py-2.5 bg-gray-900 text-white rounded-xl hover:bg-gray-800 transition-all font-medium text-sm flex items-center gap-2">
                        <span>Sitenizi Ziyaret Edin</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="p-8 space-y-6">
                <!-- Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-5 border border-gray-100 rounded-xl bg-gray-50/50">
                        <span class="text-xs text-gray-400 block font-medium uppercase tracking-wider">Kayıtlı Alan Adı (Custom Domain)</span>
                        <span class="text-base font-semibold text-gray-800 block mt-1.5">{{ $webSite->domain }}</span>
                    </div>
                    <div class="p-5 border border-gray-100 rounded-xl bg-gray-50/50">
                        <span class="text-xs text-gray-400 block font-medium uppercase tracking-wider">Bağlantı Türü</span>
                        <span class="text-base font-semibold text-gray-800 block mt-1.5 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-pulse"></span>
                            Güvenli API Entegrasyonu (HMAC)
                        </span>
                    </div>
                </div>

                <!-- API Integration Keys Section -->
                <div class="border-t border-gray-100 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 font-display">API Entegrasyon Anahtarları</h3>
                            <p class="text-xs text-gray-500">Kendi web sitenizdeki yönetim panelinde API ayarları bölümüne girmek için aşağıdaki anahtarları kullanın.</p>
                        </div>
                        
                        @if($apiKey)
                            <form action="{{ route('hekim.web-sitesi.api-anahtari.post') }}" method="POST" onsubmit="return confirm('API anahtarlarını yeniden oluşturmak mevcut sitenizin veri senkronizasyonunu kesecektir. Emin misiniz?')">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-semibold border border-red-200 transition-all flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.656 48.656 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M7.5 12l-3 3m3-3l3 3"></path>
                                    </svg>
                                    Anahtarları Yeniden Üret
                                </button>
                            </form>
                        @endif
                    </div>

                    @if($apiKey)
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-4 border border-gray-200 rounded-xl bg-gray-50 flex items-center justify-between">
                                    <div class="flex-1 min-w-0 pr-4">
                                        <span class="text-xs text-gray-400 block font-medium uppercase tracking-wider">X-API-Key</span>
                                        <code class="text-sm font-semibold text-gray-800 font-mono block mt-1 truncate" id="apiKeyVal">{{ $apiKey->api_key }}</code>
                                    </div>
                                    <button onclick="copyToClipboard('apiKeyVal')" class="p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-200 rounded-lg transition-all" title="Kopyala">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="p-4 border border-gray-200 rounded-xl bg-gray-50 flex items-center justify-between">
                                    <div class="flex-1 min-w-0 pr-4">
                                        <span class="text-xs text-gray-400 block font-medium uppercase tracking-wider">X-Api-Secret</span>
                                        @if(!empty($plainSecret))
                                            <div class="mt-1 space-y-1">
                                                <p class="text-[11px] font-bold text-amber-700">Yalnızca bu sefer gösterilir — hemen kopyalayın!</p>
                                                <code class="text-sm font-semibold text-gray-800 font-mono block break-all" id="secretKeyVal">{{ $plainSecret }}</code>
                                                <input type="hidden" id="realSecretKey" value="{{ $plainSecret }}">
                                            </div>
                                        @else
                                            <code class="text-sm font-semibold text-gray-500 font-mono block mt-1">•••••••• (hash’li — tekrar görülemez)</code>
                                            <p class="text-[11px] text-gray-400 mt-1">Kayıp secret için “Anahtarları Yeniden Üret” kullanın.</p>
                                        @endif
                                    </div>
                                    @if(!empty($plainSecret))
                                    <button onclick="copyToClipboard('realSecretKey', true)" class="p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-200 rounded-lg transition-all" title="Kopyala">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 text-xs bg-gray-50 border border-gray-100 p-4 rounded-xl">
                                <div class="flex items-center gap-2 text-gray-500">
                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    <span>API entegrasyonu rehberini, HMAC imzalama adımlarını ve webhook kılavuzunu incelemek ister misiniz?</span>
                                </div>
                                <a href="/randevuajandam-yeni/doc/" target="_blank" class="text-orange-600 hover:text-orange-700 font-bold transition-all flex items-center gap-1 shrink-0">
                                    <span>Geliştirici Dokümantasyonu</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="p-6 border border-dashed border-gray-200 rounded-xl text-center">
                            <p class="text-sm text-gray-500 mb-4">Henüz bir API anahtarı oluşturmadınız. Entegrasyonu başlatmak için bir anahtar çifti oluşturmalısınız.</p>
                            <form action="{{ route('hekim.web-sitesi.api-anahtari.post') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-semibold transition-all shadow-sm">
                                    API Anahtarı Oluştur
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <!-- No Website Configured: Form to Save Custom Domain -->
        <form action="{{ route('hekim.web-sitesi.kurulum.post') }}" method="POST" class="space-y-8">
            @csrf

            <!-- Card 1: Domain input -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-sm">1</div>
                    <h3 class="text-lg font-bold text-gray-900 font-display">Kişisel Web Sitesi Adresinizi Belirleyin</h3>
                </div>
                
                <div class="max-w-xl">
                    <label for="domain" class="block text-sm font-medium text-gray-700 mb-2">Web Sitenizin Alan Adı (Domain)</label>
                    <div class="flex rounded-xl shadow-sm">
                        <input type="text" name="domain" id="domain" required value="{{ old('domain') }}" placeholder="örnek: dr-ahmet-yilmaz.com" class="flex-1 min-w-0 block w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-orange-500 focus:border-orange-500 text-sm">
                    </div>
                    @error('domain')
                        <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-2">Sitenizi yayına alacağınız alan adını girin (Örn: <code>doktoradi.com</code>). Girilen alan adı için API anahtarları otomatik olarak üretilecektir.</p>
                </div>
            </div>

            <!-- Submit actions -->
            <div class="flex justify-end gap-4">
                <button type="submit" class="px-6 py-3.5 bg-orange-600 text-white font-semibold rounded-xl hover:bg-orange-700 shadow-sm transition-all text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span>Alan Adını Kaydet ve API Anahtarları Üret</span>
                </button>
            </div>
        </form>
    @endif
</div>

<script>
    function toggleSecretKey() {
        const secretCode = document.getElementById('secretKeyVal');
        const realSecret = document.getElementById('realSecretKey').value;
        const toggleBtn = document.getElementById('toggleSecretBtn');
        
        if (secretCode.textContent.includes('***')) {
            secretCode.textContent = realSecret;
            toggleBtn.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"></path>
                </svg>
            `;
        } else {
            secretCode.textContent = '****************************************';
            toggleBtn.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            `;
        }
    }

    function copyToClipboard(elementId, isValue = false) {
        let textToCopy = '';
        if (isValue) {
            textToCopy = document.getElementById(elementId).value;
        } else {
            textToCopy = document.getElementById(elementId).textContent;
        }

        navigator.clipboard.writeText(textToCopy).then(() => {
            alert('Kopyalandı!');
        }).catch(err => {
            console.error('Kopyalama hatası: ', err);
        });
    }
</script>
@endsection
