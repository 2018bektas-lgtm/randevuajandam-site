@extends('frontend.layouts.app')

@section('baslik', 'Hasta Girişi - Randevu Ajandam')

@section('icerik')
<section class="fe-page fe-page--auth relative bg-[#FAFAFA] overflow-hidden">
    <!-- Ambient light glow effects -->
    <div class="absolute top-[-10%] right-[-10%] w-[400px] h-[400px] rounded-full bg-[#E7B58A]/8 blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[400px] h-[400px] rounded-full bg-[#C96A2B]/4 blur-[100px] pointer-events-none"></div>

    <div class="max-w-md w-full mx-auto px-6 relative z-10">
        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-8 md:p-10 shadow-[0_8px_30px_rgba(31,41,55,0.02)] space-y-6">
            
            <!-- Header section -->
            <div class="text-center space-y-2">
                <div class="w-12 h-12 bg-[#FFF7ED] text-[#C96A2B] rounded-2xl flex items-center justify-center mx-auto border border-[#E7B58A]/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold font-display text-[#111827]">Hasta Giriş Portalı</h2>
                <p class="text-xs text-[#6B7280]">Randevu planlamak ve geçmişinizi incelemek için giriş yapın.</p>
            </div>

            <!-- Errors and Status messages -->
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

            @if(session('basarili'))
                <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-xs text-emerald-700 font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ session('basarili') }}
                </div>
            @endif

            <!-- Form -->
            <form id="hasta-giris-form" action="{{ route('frontend.hasta.giris.post') }}" method="POST" class="space-y-4">
                @csrf
                @include('frontend.layouts.partials.recaptcha-form', ['formId' => 'hasta-giris-form', 'recaptchaAction' => 'hasta_giris'])
                
                <!-- Email -->
                <div class="space-y-1">
                    <label for="e_posta" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">E-Posta Adresi</label>
                    <input type="email" name="e_posta" id="e_posta" required value="{{ old('e_posta') }}" placeholder="isim@domain.com"
                           class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-xs text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                </div>

                <!-- Password -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label for="sifre" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Şifre</label>
                        <a href="{{ route('password.request', ['type' => 'hasta']) }}" class="text-[9px] font-bold text-[#C96A2B] hover:underline uppercase tracking-wide font-display">Şifremi Unuttum</a>
                    </div>
                    <input type="password" name="sifre" id="sifre" required placeholder="••••••••"
                           class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-xs text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <label class="relative flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="sr-only peer">
                        <div class="w-8 h-4.5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                        <span class="ml-2.5 text-[11px] font-medium text-[#6B7280]">Beni Hatırla</span>
                    </label>
                </div>

                <!-- Submit -->
                <button type="submit" 
                        class="w-full py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                    Giriş Yap
                </button>
            </form>

            <!-- Bottom info -->
            <div class="text-center pt-4 border-t border-slate-100 space-y-2">
                <p class="text-xs text-[#6B7280]">
                    Hesabınız yok mu? 
                    <a href="{{ route('frontend.hasta.kayit') }}" class="font-bold text-[#C96A2B] hover:underline">Şimdi Kayıt Olun</a>
                </p>
                <div class="flex items-center justify-center gap-3 pt-2 text-[10px] font-bold text-[#6B7280] select-none">
                    <a href="{{ route('frontend.hekim.giris') }}" class="hover:text-[#C96A2B]">Hekim Girişi</a>
                    <span>•</span>
                    <a href="{{ route('personel.giris') }}" class="hover:text-[#C96A2B]">Personel Girişi</a>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
