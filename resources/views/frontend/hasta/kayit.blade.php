@extends('frontend.layouts.app')

@section('baslik', 'Kayıt Ol - Randevu Ajandam')

@section('icerik')
<section class="relative bg-[#FAFAFA] py-16 md:py-24 overflow-hidden min-h-[75vh] flex items-center">
    <!-- Ambient light glow effects -->
    <div class="absolute top-[-10%] right-[-10%] w-[400px] h-[400px] rounded-full bg-[#E7B58A]/8 blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[400px] h-[400px] rounded-full bg-[#C96A2B]/4 blur-[100px] pointer-events-none"></div>

    <div class="max-w-md w-full mx-auto px-6 relative z-10">
        <div class="bg-white border border-[#E5E7EB] rounded-3xl p-8 md:p-10 shadow-[0_8px_30px_rgba(31,41,55,0.02)] space-y-6">
            
            <!-- Header section -->
            <div class="text-center space-y-2">
                <div class="w-12 h-12 bg-[#FFF7ED] text-[#C96A2B] rounded-2xl flex items-center justify-center mx-auto border border-[#E7B58A]/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235A8.91 8.91 0 019 18a8.91 8.91 0 015 1.236A7.5 7.5 0 0012 12.75a7.5 7.5 0 00-7.999 5.235z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold font-display text-[#111827]">Hasta Hesabı Oluştur</h2>
                <p class="text-xs text-[#6B7280]">Hekimlerimizden hızlıca randevu almak için hesabınızı oluşturun.</p>
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

            <!-- Form -->
            <form id="hasta-kayit-form" action="{{ route('frontend.hasta.kayit.post') }}" method="POST" class="space-y-4">
                @csrf
                @include('frontend.layouts.partials.recaptcha-form', ['formId' => 'hasta-kayit-form', 'recaptchaAction' => 'hasta_kayit'])
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- Name -->
                    <div class="space-y-1">
                        <label for="ad" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Adınız</label>
                        <input type="text" name="ad" id="ad" required value="{{ old('ad') }}" placeholder="Ahmet"
                               class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-xs text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                    </div>

                    <!-- Surname -->
                    <div class="space-y-1">
                        <label for="soyad" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Soyadınız</label>
                        <input type="text" name="soyad" id="soyad" required value="{{ old('soyad') }}" placeholder="Yılmaz"
                               class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-xs text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                    </div>
                </div>

                <!-- Email -->
                <div class="space-y-1">
                    <label for="e_posta" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">E-Posta Adresi</label>
                    <input type="email" name="e_posta" id="e_posta" required value="{{ old('e_posta') }}" placeholder="ahmet@domain.com"
                           class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-xs text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                </div>

                <!-- Phone -->
                <div class="space-y-1">
                    <label for="telefon" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Telefon Numarası</label>
                    <input type="tel" name="telefon" id="telefon" required value="{{ old('telefon') }}" placeholder="0 (555) 123 45 67"
                           class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-xs text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Password -->
                    <div class="space-y-1">
                        <label for="sifre" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Şifre</label>
                        <input type="password" name="sifre" id="sifre" required placeholder="••••••••"
                               class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-xs text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                    </div>

                    <!-- Password confirmation -->
                    <div class="space-y-1">
                        <label for="sifre_confirmation" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Şifre Tekrar</label>
                        <input type="password" name="sifre_confirmation" id="sifre_confirmation" required placeholder="••••••••"
                               class="w-full px-4 py-2.5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] text-xs text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] transition-all">
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" 
                        class="w-full py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer font-display">
                    Kayıt Ol ve Giriş Yap
                </button>
            </form>

            <!-- Bottom info -->
            <div class="text-center pt-4 border-t border-slate-100">
                <p class="text-xs text-[#6B7280]">
                    Zaten üye misiniz? 
                    <a href="{{ route('frontend.hasta.giris') }}" class="font-bold text-[#C96A2B] hover:underline">Giriş Yapın</a>
                </p>
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
