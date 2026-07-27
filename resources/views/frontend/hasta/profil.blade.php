@extends('frontend.layouts.app-tw')

@section('baslik', 'Profil Bilgilerim - Randevu Ajandam')

@section('icerik')
<section class="fe-page relative bg-[#FAFAFA] overflow-hidden">
    <!-- Background lights -->
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-[#E7B58A]/8 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-[#C96A2B]/3 blur-[120px] pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-6 relative z-10">
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Sidebar (E-Ticaret Tarzı) -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 shadow-sm text-center lg:text-left space-y-4">
                    <!-- Profile Initials/Avatar -->
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#FFF7ED] to-[#FFFBEB] border border-[#E7B58A]/40 text-[#C96A2B] flex items-center justify-center font-extrabold font-display text-xl mx-auto lg:mx-0 select-none shadow-sm">
                        {{ mb_strtoupper(mb_substr($hasta->ad, 0, 1)) }}{{ mb_strtoupper(mb_substr($hasta->soyad, 0, 1)) }}
                    </div>
                    
                    <div class="space-y-0.5">
                        <h3 class="text-sm font-bold font-display text-[#111827]">{{ $hasta->ad_soyad }}</h3>
                        <p class="text-[11px] text-[#6B7280]">{{ $hasta->e_posta }}</p>
                    </div>
                </div>

                <!-- Navigation menu -->
                <div class="bg-white border border-[#E5E7EB] rounded-3xl overflow-hidden shadow-sm">
                    <nav class="flex flex-col">
                        <a href="{{ route('frontend.hasta.dashboard') }}"
                           class="flex items-center gap-3 px-5 py-4 text-xs font-bold font-display uppercase tracking-wider border-b border-slate-100 transition-colors {{ request()->routeIs('frontend.hasta.dashboard') ? 'bg-slate-50 text-[#C96A2B]' : 'text-[#4B5563] hover:text-[#C96A2B] hover:bg-slate-50/50' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"></path>
                            </svg>
                            Panelim
                        </a>
                        <a href="{{ route('frontend.hasta.profil') }}"
                           class="flex items-center gap-3 px-5 py-4 text-xs font-bold font-display uppercase tracking-wider border-b border-slate-100 transition-colors {{ request()->routeIs('frontend.hasta.profil') ? 'bg-slate-50 text-[#C96A2B]' : 'text-[#4B5563] hover:text-[#C96A2B] hover:bg-slate-50/50' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                            </svg>
                            Profil Bilgilerim
                        </a>
                        <a href="{{ route('frontend.hasta.randevular') }}"
                           class="flex items-center gap-3 px-5 py-4 text-xs font-bold font-display uppercase tracking-wider border-b border-slate-100 transition-colors {{ request()->routeIs('frontend.hasta.randevular') ? 'bg-slate-50 text-[#C96A2B]' : 'text-[#4B5563] hover:text-[#C96A2B] hover:bg-slate-50/50' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"></path>
                            </svg>
                            Randevularım
                        </a>
                        <form action="{{ route('frontend.hasta.cikis') }}" method="POST" class="w-full" onsubmit="return confirm('Çıkış yapmak istediğinize emin misiniz?');">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-3 px-5 py-4 text-xs font-bold font-display uppercase tracking-wider text-red-500 hover:text-red-700 hover:bg-slate-50/50 transition-colors border-none text-left cursor-pointer bg-transparent">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"></path>
                                </svg>
                                Güvenli Çıkış
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            <!-- Content Area -->
            <div class="lg:col-span-3">
                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                    
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-lg font-bold font-display text-[#111827]">Profil Bilgilerim</h2>
                        <p class="text-xs text-[#6B7280]">Kişisel bilgilerinizi ve şifrenizi güncelleyebilirsiniz.</p>
                    </div>

                    @if(session('basarili'))
                        <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-xs text-emerald-700 font-medium flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ session('basarili') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="p-4 bg-red-50 border border-red-100 rounded-2xl text-xs text-red-600 space-y-1">
                            @foreach($errors->all() as $error)
                                <p class="flex items-center gap-1.5 font-medium">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                                    </svg>
                                    {{ $error }}
                                </p>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('frontend.hasta.profil.post') }}" method="POST" class="space-y-5">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Name -->
                            <div class="space-y-1">
                                <label for="ad" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Adınız</label>
                                <input type="text" name="ad" id="ad" required value="{{ old('ad', $hasta->ad) }}"
                                       class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                            </div>

                            <!-- Surname -->
                            <div class="space-y-1">
                                <label for="soyad" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Soyadınız</label>
                                <input type="text" name="soyad" id="soyad" required value="{{ old('soyad', $hasta->soyad) }}"
                                       class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Email (Readonly) -->
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display opacity-60">E-Posta Adresi (Değiştirilemez)</label>
                                <input type="email" readonly value="{{ $hasta->e_posta }}"
                                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-400 cursor-not-allowed outline-none select-none">
                            </div>

                            <!-- Phone -->
                            <div class="space-y-1">
                                <label for="telefon" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Telefon Numarası</label>
                                <input type="tel" name="telefon" id="telefon" required value="{{ old('telefon', $hasta->telefon) }}" placeholder="0 (555) 123 45 67"
                                       class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 space-y-4">
                            <h3 class="text-xs font-bold font-display text-[#111827] uppercase tracking-wider">Şifre Değiştir (İsteğe Bağlı)</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Password -->
                                <div class="space-y-1">
                                    <label for="sifre" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Yeni Şifre</label>
                                    <input type="password" name="sifre" id="sifre" placeholder="••••••••"
                                           class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                                </div>

                                <!-- Password confirmation -->
                                <div class="space-y-1">
                                    <label for="sifre_confirmation" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Yeni Şifre Tekrar</label>
                                    <input type="password" name="sifre_confirmation" id="sifre_confirmation" placeholder="••••••••"
                                           class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-xs text-[#111827] focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" 
                                    class="px-6 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                                Bilgileri Kaydet
                            </button>
                        </div>

                    </form>
                </div>

                <!-- Bildirim Tercihleri -->
                <div id="bildirim-tercihleri" class="bg-white border border-[#E5E7EB] rounded-3xl p-6 md:p-8 shadow-sm space-y-5">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-lg font-bold font-display text-[#111827]">Bildirim Tercihleri</h2>
                        <p class="text-xs text-[#6B7280]">Randevu hatırlatma ve bildirim kanallarınızı yönetin.</p>
                    </div>

                    @if($errors->hasBag('default') && session('_old_input.bildirim_email') !== null)
                        {{-- validation errors from notification form --}}
                    @endif

                    <form action="{{ route('frontend.hasta.bildirim-tercihleri') }}" method="POST" class="space-y-4">
                        @csrf
                        <label class="flex items-center justify-between gap-4 py-3 border-b border-slate-50 cursor-pointer group">
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-[#111827] group-hover:text-[#C96A2B] transition-colors font-display">E-Posta Bildirimleri</p>
                                <p class="text-[10px] text-[#6B7280]">Randevu onayı, hatırlatma ve iptal bilgilendirmeleri</p>
                            </div>
                            <div class="relative shrink-0">
                                <input type="checkbox" name="bildirim_email" value="1" id="bildirimEmail"
                                       class="sr-only peer" {{ $hasta->bildirim_email ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-checked:bg-[#C96A2B] rounded-full transition-colors peer-focus:ring-2 peer-focus:ring-[#C96A2B]/30"></div>
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform peer-checked:translate-x-5"></div>
                            </div>
                        </label>

                        <label class="flex items-center justify-between gap-4 py-3 cursor-pointer group">
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-[#111827] group-hover:text-[#C96A2B] transition-colors font-display">SMS Bildirimleri</p>
                                <p class="text-[10px] text-[#6B7280]">Kısa mesaj ile randevu hatırlatmaları</p>
                            </div>
                            <div class="relative shrink-0">
                                <input type="checkbox" name="bildirim_sms" value="1" id="bildirimSms"
                                       class="sr-only peer" {{ $hasta->bildirim_sms ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-checked:bg-[#C96A2B] rounded-full transition-colors peer-focus:ring-2 peer-focus:ring-[#C96A2B]/30"></div>
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform peer-checked:translate-x-5"></div>
                            </div>
                        </label>

                        <div class="pt-2 flex justify-end">
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all shadow-sm cursor-pointer font-display">
                                Kaydet
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Hesap Silme (KVKK) -->
                <div id="hesap-sil" class="bg-white border border-red-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-5">
                    <div class="border-b border-red-50 pb-4">
                        <h2 class="text-lg font-bold font-display text-red-700">Hesabımı Sil</h2>
                        <p class="text-xs text-red-500">Bu işlem geri alınamaz. KVKK kapsamında kişisel verilerinizin silinmesini talep edebilirsiniz.</p>
                    </div>

                    <div class="p-4 bg-red-50 border border-red-100 rounded-2xl text-xs text-red-700 space-y-1">
                        <p class="font-bold">Dikkat: Hesap silme işlemi hakkında</p>
                        <ul class="list-disc list-inside space-y-0.5 text-red-600">
                            <li>Talebiniz alındıktan sonra hesabınız 30 gün içinde kalıcı olarak silinecektir.</li>
                            <li>Bu süre içinde tekrar giriş yaparsanız silme talebiniz iptal edilir.</li>
                            <li>Aktif randevularınız varsa hekiminiz bilgilendirilecektir.</li>
                            <li>Geçmiş randevu ve yorum kayıtlarınız sistemden kaldırılacaktır.</li>
                        </ul>
                    </div>

                    <form action="{{ route('frontend.hasta.hesap-sil-talep') }}" method="POST"
                          onsubmit="return confirm('Hesabınızı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')"
                          class="space-y-4">
                        @csrf
                        <div class="space-y-1">
                            <label for="sifre_sil" class="block text-[10px] font-bold text-red-700 uppercase tracking-wider font-display">Şifrenizle Onaylayın</label>
                            <input type="password" name="sifre" id="sifre_sil" required placeholder="••••••••"
                                   class="w-full max-w-xs px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-red-200 text-xs text-[#111827] focus:outline-none focus:border-red-400 focus:ring-1 focus:ring-red-300 transition-all">
                            @error('sifre_sil')
                                <p class="text-[10px] text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-sm cursor-pointer font-display">
                                Hesabımı Sil
                            </button>
                        </div>
                    </form>
                </div>

            </div>

        </div>

    </div>
</section>

<!-- Phone Mask JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('telefon');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
            if (!x[2] && x[1] !== '') {
                e.target.value = x[1] === '0' ? '0' : '0 (' + x[1];
            } else {
                e.target.value = !x[3] ? '0 (' + x[2] : '0 (' + x[2] + ') ' + x[3] + (x[4] ? ' ' + x[4] : '') + (x[5] ? ' ' + x[5] : '');
            }
        });
    }
});
</script>
@endsection
