@extends('yonetim.layout')

@section('baslik', 'Yönetici Düzenle - Randevu Ajandam')
@section('sayfa_baslik', 'Yönetici Yönetimi')

@section('icerik')
    <div class="max-w-2xl mx-auto">
        <!-- Top Action Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#E5E7EB]">
            <div>
                <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight flex items-center gap-2.5">
                    <span class="w-1.5 h-7 rounded-full bg-[#C96A2B] block"></span>
                    Yönetici Düzenle
                </h2>
                <p class="text-xs text-[#6B7280] mt-1.5 ml-4">Yönetici bilgilerini güncelleyin. Şifre alanını boş bırakırsanız mevcut şifre korunur.</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('yonetim.yoneticiler.index') }}" 
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
        <form action="{{ route('yonetim.yoneticiler.update', $hedefYonetici->id) }}" method="POST" class="space-y-6">
            @csrf

            <!-- Name and Surname -->
            <div>
                <label for="ad_soyad" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">İsim Soyisim</label>
                <input type="text" name="ad_soyad" id="ad_soyad" value="{{ old('ad_soyad', $hedefYonetici->ad_soyad) }}" placeholder="Ahmet Yılmaz" required
                    class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
            </div>

            <!-- Email Address -->
            <div>
                <label for="e_posta" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">E-Posta Adresi</label>
                <input type="email" name="e_posta" id="e_posta" value="{{ old('e_posta', $hedefYonetici->e_posta) }}" placeholder="ahmet@randevuajandam.com" required
                    class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
            </div>

            <!-- Phone Number -->
            <div>
                <label for="telefon" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Telefon Numarası</label>
                <input type="text" name="telefon" id="telefon" value="{{ old('telefon', $hedefYonetici->telefon) }}" placeholder="0 (5XX) XXX XX XX"
                    class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
            </div>

            <!-- Password -->
            <div>
                <label for="sifre" class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-2 font-display">Şifre (Değiştirmek istemiyorsanız boş bırakın)</label>
                <input type="password" name="sifre" id="sifre" placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B] text-sm transition-all duration-200">
                <span class="block text-[11px] text-[#6B7280] mt-1.5">Şifreyi güncellemek istiyorsanız en az 6 karakterden oluşmalıdır.</span>
            </div>

            <!-- Perfect iOS Toggle Switch -->
            <div class="flex items-center justify-between p-4.5 bg-slate-50/50 border border-[#E5E7EB] rounded-xl">
                <div>
                    <span class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display">Hesap Durumu</span>
                    <span class="block text-[11px] text-[#6B7280] mt-0.5">
                        @if($hedefYonetici->id === $yonetici->id)
                            Kendi yöneticilik hesabınızın durumunu askıya alamazsınız.
                        @else
                            Yöneticinin sisteme giriş izni aktif olsun mu?
                        @endif
                    </span>
                </div>
                <label class="relative inline-flex items-center {{ $hedefYonetici->id === $yonetici->id ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }} select-none">
                    <input type="checkbox" name="aktif_mi" value="1" 
                           {{ $hedefYonetici->aktif_mi ? 'checked' : '' }} 
                           {{ $hedefYonetici->id === $yonetici->id ? 'disabled' : '' }} 
                           class="sr-only peer">
                    <!-- iOS-style Switch (Relative thumb container) -->
                    <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-[#C96A2B] transition-colors duration-300 after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all after:duration-300 peer-checked:after:translate-x-5 shadow-inner"></div>
                </label>
            </div>

            <!-- Submit buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#E5E7EB]">
                <a href="{{ route('yonetim.yoneticiler.index') }}" 
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
</div>

<script>
    // Turkish phone number formatting helper
    function formatTurkishPhoneNumber(value) {
        let numbers = value.replace(/\D/g, '');
        if (numbers.length === 0) return '';
        if (numbers[0] !== '0') numbers = '0' + numbers;
        if (numbers.length > 1 && numbers[1] !== '5') numbers = '0';
        numbers = numbers.substring(0, 11);
        
        let formatted = '0';
        if (numbers.length > 1) {
            formatted += ' (';
            formatted += numbers.substring(1, Math.min(numbers.length, 4));
            if (numbers.length >= 4) formatted += ')';
        }
        if (numbers.length > 4) {
            formatted += ' ';
            formatted += numbers.substring(4, Math.min(numbers.length, 7));
        }
        if (numbers.length > 7) {
            formatted += ' ';
            formatted += numbers.substring(7, Math.min(numbers.length, 9));
        }
        if (numbers.length > 9) {
            formatted += ' ';
            formatted += numbers.substring(9, numbers.length);
        }
        return formatted;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const telefonInput = document.getElementById('telefon');
        if (telefonInput) {
            telefonInput.value = formatTurkishPhoneNumber(telefonInput.value);
            telefonInput.addEventListener('input', function() {
                const start = this.selectionStart;
                const prevLen = this.value.length;
                this.value = formatTurkishPhoneNumber(this.value);
                const diff = this.value.length - prevLen;
                this.setSelectionRange(start + diff, start + diff);
            });
            telefonInput.addEventListener('keydown', function(e) {
                const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
                if (allowedKeys.includes(e.key) || (e.ctrlKey && ['c', 'v', 'a', 'x'].includes(e.key.toLowerCase()))) {
                    return;
                }
                if (!/^[0-9]$/.test(e.key)) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
@endsection
