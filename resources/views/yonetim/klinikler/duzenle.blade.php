@extends('yonetim.layout')

@section('baslik', 'Klinik Düzenle - Randevu Ajandam')
@section('sayfa_baslik', 'Klinik Yönetimi')

@section('icerik')
    <div class="max-w-2xl mx-auto">
        <!-- Top Action Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
            <div>
                <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                    <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                    Klinik Bilgilerini Düzenle
                </h2>
                <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Kliniğin abonelik paketi, üyelik tarihleri ve temel iletişim ayarları.</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('yonetim.klinikler.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-xs font-semibold text-[#6B7280] hover:text-[#C96A2B] hover:border-[#E7B58A]/40 transition-all duration-200 shadow-sm select-none group">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                    </svg>
                    Listeye Dön
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8">
            <form action="{{ route('yonetim.klinikler.update', $klinik->id) }}" method="POST" class="space-y-6">
                @csrf

                <!-- Clinic Name -->
                <div>
                    <label for="ad" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Klinik Adı</label>
                    <input type="text" name="ad" id="ad" value="{{ old('ad', $klinik->ad) }}" placeholder="Klinik Adı" required
                        class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                </div>

                <!-- Package & Max Doctor Limit -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-4.5 bg-slate-50/30 border border-[#E5E7EB] rounded-xl">
                    <div>
                        <label for="paket_id" class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Abonelik Paketi</label>
                        <select name="paket_id" id="paket_id" required
                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                            @foreach($paketler as $p)
                                <option value="{{ $p->id }}" {{ old('paket_id', $klinik->paket_id) == $p->id ? 'selected' : '' }}>
                                    {{ $p->ad }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="max_doktor_sayisi" class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Maksimum Doktor Limiti</label>
                        <input type="number" name="max_doktor_sayisi" id="max_doktor_sayisi" value="{{ old('max_doktor_sayisi', $klinik->max_doktor_sayisi) }}" min="1" required
                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                    </div>
                </div>

                <!-- Subscription Dates -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-4.5 bg-slate-50/30 border border-[#E5E7EB] rounded-xl">
                    <div>
                        <label for="uyelik_baslangic" class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Üyelik Başlangıcı</label>
                        <input type="date" name="uyelik_baslangic" id="uyelik_baslangic" 
                            value="{{ old('uyelik_baslangic', $klinik->uyelik_baslangic ? \Carbon\Carbon::parse($klinik->uyelik_baslangic)->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                    </div>
                    <div>
                        <label for="uyelik_bitis" class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2 font-display">Üyelik Bitişi</label>
                        <input type="date" name="uyelik_bitis" id="uyelik_bitis" 
                            value="{{ old('uyelik_bitis', $klinik->uyelik_bitis ? \Carbon\Carbon::parse($klinik->uyelik_bitis)->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-xs transition-all duration-200">
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="telefon" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Klinik Telefonu</label>
                        <input type="text" name="telefon" id="telefon" value="{{ old('telefon', $klinik->telefon) }}" placeholder="Örn: 0212XXXXXXX"
                            class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                    </div>
                    <div>
                        <label for="e_posta" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Klinik Genel E-Posta</label>
                        <input type="email" name="e_posta" id="e_posta" value="{{ old('e_posta', $klinik->e_posta) }}" placeholder="Örn: klinik@example.com"
                            class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label for="adres" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Klinik Adresi</label>
                    <textarea name="adres" id="adres" rows="3" placeholder="Klinik açık adresi..."
                        class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">{{ old('adres', $klinik->adres) }}</textarea>
                </div>

                <!-- iOS Toggle for Clinic Status -->
                <div class="flex items-center justify-between p-4.5 bg-slate-50/50 border border-[#E5E7EB] rounded-xl">
                    <div>
                        <span class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display">Klinik Durumu</span>
                        <span class="block text-[11px] text-[#6B7280] mt-0.5">Pasif durumdaki klinikler ve bağlı doktorlar panele giriş yapamaz.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="aktif_mi" value="1" {{ $klinik->aktif_mi ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all after:duration-300 peer-checked:after:translate-x-5 shadow-inner"></div>
                    </label>
                </div>

                <!-- Submit buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#E5E7EB]">
                    <a href="{{ route('yonetim.klinikler.index') }}" 
                       class="px-5 py-2.5 rounded-xl border border-[#E5E7EB] hover:bg-slate-50 text-[#6B7280] font-bold text-sm transition-all duration-200 select-none">
                        İptal Et
                    </a>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-sm transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer select-none">
                        Değişiklikleri Kaydet
                    </button>
                </div>
            </form>
        </div>

        <!-- Klinik Hekimleri -->
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8 mt-6">
            <h3 class="text-lg font-bold font-display text-[#111827] mb-4">Klinik Hekimleri ({{ $klinik->doktorlar->count() }})</h3>
            
            @if($klinik->doktorlar->count() > 0)
                <div class="divide-y divide-[#E5E7EB]">
                    @foreach($klinik->doktorlar as $doc)
                        <div class="flex items-center justify-between py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-[#FFF7ED] text-[#C96A2B] border border-[#E7B58A]/30 flex items-center justify-center font-bold text-xs font-display">
                                    {{ mb_strtoupper(mb_substr($doc->ad_soyad, 0, 2)) }}
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-[#111827] font-display">{{ $doc->ad_soyad }}</span>
                                    <span class="block text-[11px] text-[#6B7280] capitalize font-medium font-sans mt-0.5">{{ $doc->klinik_rolu === 'sahip' ? 'Klinik Sahibi' : 'Doktor' }}</span>
                                </div>
                            </div>
                            
                            @if($doc->id !== $klinik->sahip_doktor_id)
                                <form action="{{ route('yonetim.klinikler.doktor-cikar', [$klinik->id, $doc->id]) }}" method="POST" onsubmit="return confirm('Bu hekimi klinikten çıkarmak istediğinize emin misiniz?');">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 hover:border-red-300 rounded-lg text-xs font-bold font-display transition-all cursor-pointer">
                                        Klinikten Çıkar
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-amber-600 font-bold font-display bg-amber-50 px-2.5 py-1 rounded-full border border-amber-100">Klinik Sahibi</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-[#6B7280] py-2">Klinikte henüz kayıtlı hekim bulunmuyor.</p>
            @endif
        </div>
    </div>
@endsection
