@extends('klinik.layout')

@section('baslik', $personel->ad_soyad . ' - Personel Düzenle')
@section('sayfa_baslik', 'Personel Bilgileri Düzenleme')

@section('icerik')
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <a href="{{ route('hekim.klinik.personeller') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#6B7280] hover:text-[#111827] transition-colors">
                ← Personel Listesine Dön
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Left 2 Columns: Edit Form -->
            <div class="md:col-span-2 p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm space-y-6">
                <div class="flex items-center gap-4 pb-4 border-b border-[#F5F5F4]">
                    <div class="w-12 h-12 rounded-2xl bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-lg font-bold font-display">
                        {{ mb_strtoupper(mb_substr($personel->ad_soyad, 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="text-base font-bold font-display text-[#111827]">{{ $personel->ad_soyad }}</h3>
                        <span class="text-xs text-[#6B7280] mt-0.5 capitalize">{{ $personel->rol }}</span>
                    </div>
                </div>

                <form action="{{ route('hekim.klinik.personeller.guncelle', $personel->id) }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="ad_soyad" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Adı Soyadı</label>
                            <input id="ad_soyad" name="ad_soyad" type="text" value="{{ old('ad_soyad', $personel->ad_soyad) }}" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-xs focus:border-[#C96A2B] outline-none">
                        </div>

                        <div>
                            <label for="telefon" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Telefon Numarası</label>
                            <input id="telefon" name="telefon" type="text" value="{{ old('telefon', $personel->telefon) }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-xs focus:border-[#C96A2B] outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#4B5563] mb-1.5">E-posta Adresi (Değiştirilemez)</label>
                        <input type="email" disabled value="{{ $personel->e_posta }}" class="w-full bg-[#F3F4F6] border border-[#E5E7EB] rounded-xl px-4 py-3 text-xs text-gray-400 outline-none cursor-not-allowed">
                    </div>

                    <div>
                        <label for="rol" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Görevi / Rolü</label>
                        <select id="rol" name="rol" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-xs focus:border-[#C96A2B] outline-none cursor-pointer">
                            <option value="sekreter" {{ $personel->rol === 'sekreter' ? 'selected' : '' }}>Sekreter (Varsayılan)</option>
                            <option value="resepsiyonist" {{ $personel->rol === 'resepsiyonist' ? 'selected' : '' }}>Resepsiyonist</option>
                            <option value="muhasebeci" {{ $personel->rol === 'muhasebeci' ? 'selected' : '' }}>Muhasebeci</option>
                        </select>
                    </div>

                    <!-- Yetkiler (Checkbox List) -->
                    <div class="space-y-3 pt-2">
                        <label class="block text-xs font-bold text-[#111827] uppercase tracking-wider font-display">Kullanıcı Yetkileri</label>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-[#FAFAFA] p-4 rounded-xl border border-[#E5E7EB]">
                            <label class="flex items-center gap-2.5 cursor-pointer text-xs text-[#4B5563] font-medium">
                                <input type="checkbox" name="yetkiler[randevu]" value="1" {{ ($personel->yetkiler['randevu'] ?? false) ? 'checked' : '' }} class="rounded border-[#E5E7EB] text-[#C96A2B] focus:ring-[#C96A2B]">
                                Randevu Yönetimi
                            </label>

                            <label class="flex items-center gap-2.5 cursor-pointer text-xs text-[#4B5563] font-medium">
                                <input type="checkbox" name="yetkiler[hasta]" value="1" {{ ($personel->yetkiler['hasta'] ?? false) ? 'checked' : '' }} class="rounded border-[#E5E7EB] text-[#C96A2B] focus:ring-[#C96A2B]">
                                Hasta Yönetimi
                            </label>

                            <label class="flex items-center gap-2.5 cursor-pointer text-xs text-[#4B5563] font-medium">
                                <input type="checkbox" name="yetkiler[odeme]" value="1" {{ ($personel->yetkiler['odeme'] ?? false) ? 'checked' : '' }} class="rounded border-[#E5E7EB] text-[#C96A2B] focus:ring-[#C96A2B]">
                                Ödeme Alabilir
                            </label>

                            <label class="flex items-center gap-2.5 cursor-pointer text-xs text-[#4B5563] font-medium">
                                <input type="checkbox" name="yetkiler[finans]" value="1" {{ ($personel->yetkiler['finans'] ?? false) ? 'checked' : '' }} class="rounded border-[#E5E7EB] text-[#C96A2B] focus:ring-[#C96A2B]">
                                Finans Raporları
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl transition-all duration-200">
                        Bilgileri Güncelle
                    </button>
                </form>
            </div>

            <!-- Right Column: Reset Password -->
            <div class="space-y-6">
                <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm text-center space-y-4">
                    <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center border border-red-100 mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold font-display text-[#111827]">Şifre Sıfırlama</h4>
                        <p class="text-xs text-[#6B7280] mt-1 leading-relaxed">
                            Personel şifresini unuttuysa yeni bir geçici şifre üretebilirsiniz. Personel ilk girişinde bu şifreyi değiştirmeye zorlanacaktır.
                        </p>
                    </div>
                    
                    <form action="{{ route('hekim.klinik.personeller.sifre-sifirla', $personel->id) }}" method="POST" onsubmit="return confirm('Bu personelin şifresini sıfırlamak istediğinize emin misiniz?');">
                        @csrf
                        <button type="submit" class="w-full py-2.5 border border-red-200 hover:border-red-300 text-red-600 font-bold text-xs uppercase tracking-wider rounded-xl bg-red-50/50 hover:bg-red-50 transition-colors">
                            Geçici Şifre Oluştur
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
