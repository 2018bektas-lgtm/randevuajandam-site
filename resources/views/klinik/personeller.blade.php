@extends('klinik.layout')

@section('baslik', 'Personel Yönetimi - ' . $klinik->ad)
@section('sayfa_baslik', 'Personel Yönetimi')

@section('icerik')
    @if(session('basari'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
            {{ session('basari') }}
        </div>
    @endif

    @if(session('hata') || $errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold">
            {{ session('hata') ?? $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left 2 Columns: Personel Listesi -->
        <div class="lg:col-span-2 space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold font-display text-[#111827]">Klinik Çalışanları</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FFF7ED] text-[#C96A2B]">
                        {{ $personeller->count() }} Personel
                    </span>
                </div>

                <div class="space-y-4">
                    @forelse($personeller as $personel)
                        <div class="p-5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-bold text-[#111827]">{{ $personel->ad_soyad }}</h4>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider font-display border
                                        @if($personel->rol === 'sekreter') bg-blue-50 text-blue-700 border-blue-200/50
                                        @elseif($personel->rol === 'muhasebeci') bg-purple-50 text-purple-700 border-purple-200/50
                                        @else bg-green-50 text-green-700 border-green-200/50 @endif">
                                        {{ ucfirst($personel->rol) }}
                                    </span>
                                </div>
                                <div class="text-xs text-[#6B7280] space-y-1">
                                    <p class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path>
                                        </svg>
                                        {{ $personel->e_posta }}
                                    </p>
                                    @if($personel->telefon)
                                        <p class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.824-1.557-5.148-3.882-6.703-6.702l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"></path>
                                            </svg>
                                            {{ $personel->telefon }}
                                        </p>
                                    @endif
                                </div>
                                <!-- Permissions Badge Display -->
                                <div class="flex flex-wrap gap-1.5 pt-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ ($personel->yetkiler['randevu'] ?? false) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-50 text-gray-400 border-gray-150' }}">Randevu ✓</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ ($personel->yetkiler['hasta'] ?? false) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-50 text-gray-400 border-gray-150' }}">Hasta ✓</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ ($personel->yetkiler['odeme'] ?? false) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-50 text-gray-400 border-gray-150' }}">Ödeme ✓</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ ($personel->yetkiler['finans'] ?? false) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-50 text-gray-400 border-gray-150' }}">Finans ✓</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                                <!-- Status Toggle Form -->
                                <form action="{{ route('hekim.klinik.personeller.durum', $personel->id) }}" method="POST" class="flex items-center">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all
                                        {{ $personel->aktif_mi ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-amber-50 border-amber-200 text-amber-700' }}">
                                        {{ $personel->aktif_mi ? 'Aktif' : 'Pasif' }}
                                    </button>
                                </form>

                                <!-- Edit Link -->
                                <a href="{{ route('hekim.klinik.personeller.duzenle', $personel->id) }}" class="p-2 text-gray-500 hover:text-[#C96A2B] transition-colors" title="Düzenle">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.83 19.13a4.5 4.5 0 01-1.897 1.13L2.685 20.8a.75.75 0 01-.905-.905l.55-2.21a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"></path>
                                    </svg>
                                </a>

                                <!-- Delete Form -->
                                <form action="{{ route('hekim.klinik.personeller.destroy', $personel->id) }}" method="POST" onsubmit="return confirm('Bu personeli silmek istediğinize emin misiniz?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:text-red-700 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-[#6B7280] py-6 text-center">Kliniğe kayıtlı henüz bir personel bulunmamaktadır.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Personel Ekle Formu -->
        <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-4">Yeni Personel Ekle</h3>
                
                <form action="{{ route('hekim.klinik.personeller.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="ad_soyad" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Adı Soyadı</label>
                            <input type="text" name="ad_soyad" id="ad_soyad" required placeholder="Eda Yılmaz" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                        </div>

                        <div>
                            <label for="e_posta" class="block text-xs font-semibold text-[#4B5563] mb-1.5">E-posta Adresi</label>
                            <input type="email" name="e_posta" id="e_posta" required placeholder="eda@klinik.com" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                        </div>

                        <div>
                            <label for="telefon" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Telefon Numarası</label>
                            <input type="text" name="telefon" id="telefon" placeholder="05001234567" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                        </div>

                        <div>
                            <label for="sifre" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Giriş Şifresi</label>
                            <input type="password" name="sifre" id="sifre" required placeholder="••••••••" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                        </div>

                        <div>
                            <label for="rol" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Rolü / Görevi</label>
                            <select name="rol" id="rol" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                                <option value="sekreter">Sekreter (Varsayılan Yetkiler)</option>
                                <option value="resepsiyonist">Resepsiyonist</option>
                                <option value="muhasebeci">Muhasebeci (Finans Yetkileri Dahil)</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl transition-all duration-200 hover:scale-[1.01]">
                            Personel Ekle
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Information Box -->
            <div class="p-6 rounded-2xl bg-[#FFF7ED]/50 border border-[#E7B58A]/30">
                <h4 class="text-xs font-bold text-[#C96A2B] uppercase tracking-wider font-display mb-2">Çalışan Rolleri ve Yetkiler</h4>
                <ul class="space-y-2 text-xs text-[#4B5563]">
                    <li><strong>Sekreter:</strong> Randevuları ve hastaları yönetebilir.</li>
                    <li><strong>Resepsiyonist:</strong> Randevu, hasta yönetebilir ve ödeme alabilir.</li>
                    <li><strong>Muhasebeci:</strong> Tüm finansal modüllere, giderlere ve gelirlere erişim sağlayabilir.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
