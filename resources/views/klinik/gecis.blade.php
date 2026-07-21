@extends('klinik.layout')

@section('baslik', 'Kliniğe Geç (Paket Yükselt) - Hekim Paneli')
@section('sayfa_baslik', 'Kliniğe Geçiş & Paket Yükseltme')

@section('icerik')
<div class="p-6 md:p-10 space-y-8 select-none max-w-5xl mx-auto overflow-y-auto h-full pb-32">
    <!-- Header Card -->
    <div class="bg-gradient-to-r from-[#C96A2B] to-[#E7B58A] rounded-3xl p-8 text-white relative overflow-hidden shadow-md">
        <div class="absolute right-[-10%] top-[-30%] w-96 h-96 rounded-full bg-white/10 blur-[80px] pointer-events-none"></div>
        <div class="relative z-10 space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/10 rounded-full text-[10px] font-extrabold uppercase tracking-widest">
                Klinik Yönetim Modülü
            </span>
            <h2 class="text-2xl md:text-3xl font-extrabold font-display leading-tight">Muayenehanenizi Dijital Kliniğe Dönüştürün</h2>
            <p class="text-xs md:text-sm text-white/80 max-w-2xl leading-relaxed">
                Diğer doktorları kliniğinize davet edin, sekreter ve personellerinizi tanımlayın, tüm randevu ve finans süreçlerinizi tek bir çatı altından yönetin.
            </p>
        </div>
    </div>

    <!-- Main Layout -->
    <form action="{{ route('frontend.hekim.klinik.gecis.post') }}" method="POST" id="transitionForm" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        @csrf

        <!-- Sol Sütun: Form Alanları -->
        <div class="lg:col-span-8 bg-white border border-[#E5E7EB] rounded-3xl p-6 sm:p-8 shadow-sm space-y-8">
            
            <!-- Step 1: Klinik Bilgileri -->
            <div class="space-y-5">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <span class="w-7 h-7 rounded-full bg-[#FFF7ED] text-[#C96A2B] font-bold text-xs flex items-center justify-center font-display border border-[#E7B58A]/30">1</span>
                    <h3 class="text-sm font-bold font-display text-[#111827] uppercase tracking-wider">Klinik Bilgileri</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label for="klinik_adi" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Klinik Adı *</label>
                        <input type="text" name="klinik_adi" id="klinik_adi" value="{{ old('klinik_adi', $doktor->klinik_adi) }}" 
                               class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                               placeholder="Örn: Şifa Polikliniği">
                        @error('klinik_adi') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="telefon" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Klinik Telefonu *</label>
                        <input type="text" name="telefon" id="telefon" value="{{ old('telefon', $doktor->telefon) }}" 
                               class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                               placeholder="Örn: 0 (212) 123 45 67">
                        @error('telefon') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="e_posta" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Klinik E-posta</label>
                        <input type="email" name="e_posta" id="e_posta" value="{{ old('e_posta', $doktor->e_posta) }}" 
                               class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium"
                               placeholder="Örn: info@sifaklinik.com">
                        @error('e_posta') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="il_id" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">İl *</label>
                        <select name="il_id" id="il_id" class="w-full select2-select">
                            <option value="">İl Seçin</option>
                            @foreach($iller as $il)
                                <option value="{{ $il->id }}" {{ old('il_id', $doktor->il_id) == $il->id ? 'selected' : '' }}>{{ $il->ad }}</option>
                            @endforeach
                        </select>
                        @error('il_id') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="ilce_id" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">İlçe *</label>
                        <select name="ilce_id" id="ilce_id" class="w-full select2-select" disabled>
                            <option value="">Önce İl Seçin</option>
                        </select>
                        @error('ilce_id') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="adres" class="block text-[11px] font-bold text-[#1F2937] uppercase tracking-wider mb-2">Açık Adres *</label>
                        <textarea name="adres" id="adres" rows="3" 
                                  class="w-full text-xs bg-slate-50 border border-gray-200 focus:border-[#C96A2B] focus:bg-white rounded-xl p-3 outline-none transition-all font-medium resize-none"
                                  placeholder="Kliniğinizin açık adresi...">{{ old('adres', $doktor->adres) }}</textarea>
                        @error('adres') <span class="text-[10px] text-red-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Step 2: PayTR bilgilendirme -->
            <div class="space-y-5">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <span class="w-7 h-7 rounded-full bg-[#FFF7ED] text-[#C96A2B] font-bold text-xs flex items-center justify-center font-display border border-[#E7B58A]/30">2</span>
                    <h3 class="text-sm font-bold font-display text-[#111827] uppercase tracking-wider">Güvenli ödeme (PayTR)</h3>
                </div>
                <div class="rounded-2xl border border-[#E7B58A]/40 bg-[#FFF7ED]/50 p-5 text-xs text-slate-600 space-y-2">
                    <p class="font-semibold text-[#C96A2B] text-sm">Kart bilgisi sitede toplanmaz.</p>
                    <p>Onayladığınızda PayTR güvenli ödeme sayfasına (3D Secure) yönlendirilirsiniz. Ödeme sonrası klinik hesabınız otomatik açılır.</p>
                    @error('paket_id') <span class="text-red-500 font-semibold block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-8 py-3.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-xs font-bold text-white shadow-sm hover:shadow transition-all uppercase tracking-wider font-display">
                    PayTR ile öde &amp; kliniğe geç
                </button>
            </div>
        </div>

        <!-- Sağ Sütun: Paket Seçimi & Özet -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Paket Seçimi -->
            <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm">
                <h3 class="text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display mb-4">Klinik Paketleri</h3>
                
                <div class="space-y-3.5">
                    @foreach($paketler as $pkg)
                        <label class="block relative border border-[#E5E7EB] rounded-xl p-4 cursor-pointer hover:bg-slate-50/50 transition-colors {{ $loop->first ? 'ring-1 ring-[#C96A2B] bg-[#FFF7ED]/10' : '' }}" id="package-label-{{ $pkg->id }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="paket_id" value="{{ $pkg->id }}" class="sr-only peer" {{ $loop->first ? 'checked' : '' }} onchange="selectPackage({{ $pkg->id }}, {{ $pkg->aylik_indirimli_fiyat ?? $pkg->aylik_fiyat }}, {{ $pkg->yillik_indirimli_fiyat ?? $pkg->yillik_fiyat }})">
                                    <div class="w-4 h-4 rounded-full border border-gray-300 flex items-center justify-center peer-checked:border-[#C96A2B] peer-checked:bg-[#C96A2B] transition-all">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                    </div>
                                    <span class="text-xs font-bold text-[#111827] font-display">{{ $pkg->ad }}</span>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    @if($pkg->aylik_indirimli_fiyat)
                                        <span class="block text-[9px] text-[#9CA3AF] line-through">₺{{ number_format($pkg->aylik_fiyat, 0, ',', '.') }}</span>
                                        <span class="block text-xs font-extrabold text-[#C96A2B] font-display">₺{{ number_format($pkg->aylik_indirimli_fiyat, 0, ',', '.') }}<span class="text-[9px] font-medium text-[#6B7280]">/ay</span></span>
                                    @else
                                        <span class="block text-xs font-extrabold text-[#C96A2B] font-display">₺{{ number_format($pkg->aylik_fiyat, 0, ',', '.') }}<span class="text-[9px] font-medium text-[#6B7280]">/ay</span></span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-2 text-[10px] text-[#6B7280] flex justify-between items-center">
                                <span>{{ $pkg->max_doktor_sayisi }} Hekim | {{ $pkg->max_personel_sayisi }} Personel</span>
                                @if($pkg->yillik_indirimli_fiyat && $pkg->yillik_indirimli_fiyat < $pkg->yillik_fiyat)
                                    <span class="text-[9px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded font-display">Yıllıkta İndirim</span>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Ödeme Özeti -->
            <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm space-y-4">
                <h3 class="text-xs font-bold text-[#1F2937] uppercase tracking-wider font-display border-b border-slate-100 pb-3">Ödeme Özeti</h3>
                
                <!-- Ödeme Periyodu Toggle -->
                <div class="grid grid-cols-2 gap-2 bg-slate-50 p-1.5 rounded-xl">
                    <label class="text-center py-2 rounded-lg cursor-pointer text-[10px] font-bold uppercase tracking-wider transition-all select-none bg-white text-[#111827] shadow-sm" id="labelPeriodMonthly">
                        <input type="radio" name="odeme_periyodu" value="aylik" class="sr-only" checked onchange="togglePeriod('aylik')">
                        Aylık
                    </label>
                    <label class="text-center py-2 rounded-lg cursor-pointer text-[10px] font-bold uppercase tracking-wider transition-all select-none text-[#6B7280]" id="labelPeriodYearly">
                        <input type="radio" name="odeme_periyodu" value="yillik" class="sr-only" onchange="togglePeriod('yillik')">
                        Yıllık
                    </label>
                </div>

                <div class="space-y-3 text-xs pt-2">
                    <div class="flex items-center justify-between text-[#6B7280]">
                        <span>Abonelik Fiyatı</span>
                        <span class="font-bold text-[#111827]" id="summaryPrice">₺0,00</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100 pt-3 text-sm">
                        <span class="font-bold text-[#1F2937]">Toplam Tutar</span>
                        <span class="font-extrabold text-[#C96A2B] font-display" id="summaryTotal">₺0,00</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('extra_js')
<script>
    let activePackageId = null;
    let period = 'aylik';
    let packagePrices = {};

    $(document).ready(function() {
        // Load packages data
        @foreach($paketler as $pkg)
            packagePrices[{{ $pkg->id }}] = {
                aylik: {{ $pkg->aylik_indirimli_fiyat ?? $pkg->aylik_fiyat }},
                yillik: {{ $pkg->yillik_indirimli_fiyat ?? $pkg->yillik_fiyat }}
            };
        @endforeach

        // Select first package by default
        const firstPkgId = $('input[name="paket_id"]:checked').val();
        if (firstPkgId) {
            selectPackage(firstPkgId, packagePrices[firstPkgId].aylik, packagePrices[firstPkgId].yillik);
        }

        // AJAX İlceler loading based on selected İl
        $('#il_id').on('change', function() {
            const ilId = $(this).val();
            const ilceSelect = $('#ilce_id');
            
            ilceSelect.html('<option value="">Yükleniyor...</option>').prop('disabled', true);
            
            if (!ilId) {
                ilceSelect.html('<option value="">Önce İl Seçin</option>').prop('disabled', true);
                return;
            }
            
            $.ajax({
                url: `/iller/${ilId}/ilceler`,
                type: 'GET',
                success: function(response) {
                    let options = '<option value="">İlçe Seçin</option>';
                    response.forEach(function(ilce) {
                        options += `<option value="${ilce.ad}">${ilce.ad}</option>`;
                    });
                    ilceSelect.html(options).prop('disabled', false);
                    ilceSelect.select2('destroy').select2({ width: '100%' });
                },
                error: function() {
                    ilceSelect.html('<option value="">Hata Oluştu, Tekrar Deneyin</option>').prop('disabled', false);
                }
            });
        });

        // Trigger city change if already selected
        const selectedCity = $('#il_id').val();
        if (selectedCity) {
            $('#il_id').trigger('change');
            setTimeout(() => {
                $('#ilce_id').val("{{ old('ilce_id', $doktor->ilce?->ad) }}").trigger('change');
            }, 500);
        }

        // Phone mask
        $('#telefon').on('input', function() {
            let val = $(this).val().replace(/\D/g, '');
            if (val.startsWith('0')) val = val.substring(1);
            let formatted = '';
            if (val.length > 0) formatted += '0 (' + val.substring(0, 3);
            if (val.length > 3) formatted += ') ' + val.substring(3, 6);
            if (val.length > 6) formatted += ' ' + val.substring(6, 8);
            if (val.length > 8) formatted += ' ' + val.substring(8, 10);
            $(this).val(formatted);
        });
    });

    function selectPackage(pkgId, monthlyVal, yearlyVal) {
        activePackageId = pkgId;
        
        // Visual indicators update
        $('[id^="package-label-"]').removeClass('ring-1 ring-[#C96A2B] bg-[#FFF7ED]/10');
        $(`#package-label-${pkgId}`).addClass('ring-1 ring-[#C96A2B] bg-[#FFF7ED]/10');
        $(`input[name="paket_id"][value="${pkgId}"]`).prop('checked', true);

        updatePrices();
    }

    function togglePeriod(cycle) {
        period = cycle;
        
        const labelMonthly = document.getElementById('labelPeriodMonthly');
        const labelYearly = document.getElementById('labelPeriodYearly');

        if (cycle === 'aylik') {
            labelMonthly.classList.add('bg-white', 'text-[#111827]', 'shadow-sm');
            labelMonthly.classList.remove('text-[#6B7280]');
            labelYearly.classList.remove('bg-white', 'text-[#111827]', 'shadow-sm');
            labelYearly.classList.add('text-[#6B7280]');
        } else {
            labelYearly.classList.add('bg-white', 'text-[#111827]', 'shadow-sm');
            labelYearly.classList.remove('text-[#6B7280]');
            labelMonthly.classList.remove('bg-white', 'text-[#111827]', 'shadow-sm');
            labelMonthly.classList.add('text-[#6B7280]');
        }

        updatePrices();
    }

    function updatePrices() {
        if (!activePackageId) return;

        const price = packagePrices[activePackageId][period];
        const formatted = new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(price);

        document.getElementById('summaryPrice').innerHTML = formatted;
        document.getElementById('summaryTotal').innerHTML = formatted;
    }
</script>
@endsection
