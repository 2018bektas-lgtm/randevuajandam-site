@extends('klinik.layout')

@section('baslik', 'Ek Hekim Koltuğu - ' . $klinik->ad)
@section('sayfa_baslik', 'Ek Hekim Koltuğu')

@section('icerik')
    @if(session('hata'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold">
            {{ session('hata') }}
        </div>
    @endif

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-[#FFF7ED] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#C96A2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold font-display text-[#111827]">Ek Hekim Koltuğu Satın Al</h2>
                    <p class="text-xs text-[#6B7280]">Paket limitiniz aşıldığında ek hekim koltuğu alarak kapasitenizi artırın.</p>
                </div>
            </div>

            {{-- Mevcut Kota --}}
            <div class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] mb-6">
                <h4 class="text-xs font-bold text-[#6B7280] uppercase tracking-wider mb-3">Mevcut Hekim Kotası</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="text-center">
                        <p class="text-lg font-bold text-[#111827]">{{ $klinik->dahilDoktorLimiti() }}</p>
                        <p class="text-[10px] text-[#6B7280]">Paket dahil</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-[#C96A2B]">{{ (int) $klinik->ek_doktor_koltuk_sayisi }}</p>
                        <p class="text-[10px] text-[#6B7280]">Ek koltuk</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-[#111827]">{{ $klinik->efektifDoktorLimiti() }}</p>
                        <p class="text-[10px] text-[#6B7280]">Efektif limit</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold {{ $klinik->doktorLimitiDolduMu() ? 'text-red-500' : 'text-emerald-600' }}">{{ $klinik->doktorlar()->count() }}</p>
                        <p class="text-[10px] text-[#6B7280]">Mevcut hekim</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('hekim.klinik.ek-koltuk.odeme') }}" method="POST" id="ekKoltukForm">
                @csrf

                {{-- Kaç koltuk --}}
                <div class="mb-6">
                    <label class="block text-xs font-bold text-[#1F2937] uppercase tracking-wider mb-3 font-display">Kaç ek koltuk almak istiyorsunuz?</label>
                    <div class="flex flex-wrap gap-2">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="adet" value="{{ $i }}" {{ $i === 1 ? 'checked' : '' }} class="peer sr-only" onchange="updateTotal()">
                                <div class="px-5 py-3 rounded-xl border-2 border-[#E5E7EB] text-sm font-bold text-[#4B5563] peer-checked:border-[#C96A2B] peer-checked:bg-[#FFF7ED] peer-checked:text-[#C96A2B] transition-all duration-200 hover:border-[#E7B58A]">
                                    {{ $i }}
                                </div>
                            </label>
                        @endfor
                    </div>
                </div>

                {{-- Periyot --}}
                <div class="mb-6 p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB]">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[#4B5563]">Periyot</span>
                        <span class="font-bold text-[#111827]">{{ $periyotLabel }} <span class="text-[10px] text-[#9CA3AF] font-normal">(üyeliğinizle aynı — değiştirilemez)</span></span>
                    </div>
                    <input type="hidden" name="periyot" value="{{ $periyot }}">
                </div>

                {{-- Fiyat hesaplama --}}
                <div class="mb-6 p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[#4B5563]">Birim fiyat</span>
                        <span class="font-bold text-[#111827]">{{ number_format($birimFiyat, 0, ',', '.') }} ₺</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[#4B5563]">Adet</span>
                        <span class="font-bold text-[#111827]" id="adetDisplay">1</span>
                    </div>
                    <div class="flex items-center justify-between text-sm pt-2 border-t border-[#E5E7EB]">
                        <span class="text-[#4B5563] font-semibold">Toplam</span>
                        <span class="text-lg font-bold text-[#C96A2B]" id="toplamDisplay">{{ number_format($birimFiyat, 0, ',', '.') }} ₺</span>
                    </div>
                </div>

                {{-- Üyelik bilgisi --}}
                <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 space-y-1">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-blue-700">Üyelik bitiş</span>
                        <span class="font-bold text-blue-900">{{ $uyelikBitis }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-blue-700">Kalan süre</span>
                        <span class="font-bold text-blue-900">{{ $kalanGun }} gün</span>
                    </div>
                </div>

                {{-- UYARI --}}
                <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        <div class="text-xs text-amber-800 leading-relaxed">
                            <p class="font-bold mb-1">Önemli Bilgilendirme</p>
                            <p>Bu tutar <strong>tam dönem birim fiyatıdır</strong>; kalan güne göre indirim uygulanmaz. Ek koltuk hakkınız üyelik bitiş tarihinize (<strong>{{ $uyelikBitis }}</strong>) kadar geçerlidir.</p>
                        </div>
                    </div>
                </div>

                {{-- Zorunlu Checkbox --}}
                <div class="mb-6">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input type="checkbox" id="okudum_anladim" name="okudum_anladim" class="mt-1 rounded border-[#E5E7EB] text-[#C96A2B] focus:ring-[#C96A2B] cursor-pointer" onchange="toggleSubmit()">
                        <span class="text-xs text-[#4B5563] group-hover:text-[#111827] transition-colors">Ek hekim koltuğu fiyatlandırması ve şartlarını okudum ve kabul ediyorum. Kalan süreye göre indirim uygulanmayacağını biliyorum.</span>
                    </label>
                </div>

                {{-- Butonlar --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('hekim.klinik.doktorlar') }}" class="px-5 py-3 rounded-xl border border-[#E5E7EB] hover:bg-slate-50 text-[#6B7280] font-bold text-sm transition-all duration-200">
                        Geri Dön
                    </a>
                    <button type="submit" id="submitBtn" disabled
                            class="flex-1 px-5 py-3 rounded-xl bg-[#C96A2B] text-white font-bold text-sm transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed enabled:hover:bg-[#B55A20] enabled:hover:shadow-md enabled:hover:scale-[1.01] cursor-pointer">
                        Ödemeye Geç
                    </button>
                </div>
            </form>

            {{-- Paket yükseltme linki --}}
            <div class="mt-6 text-center">
                <a href="{{ route('hekim.klinik.gecis') }}" class="text-xs text-[#C96A2B] hover:text-[#B55A20] font-semibold underline underline-offset-2">
                    veya paketinizi yükseltin →
                </a>
            </div>
        </div>
    </div>
@endsection

@section('extra_js')
    <script>
        const birimFiyat = {{ $birimFiyat }};

        function updateTotal() {
            const adet = parseInt(document.querySelector('input[name="adet"]:checked').value);
            document.getElementById('adetDisplay').textContent = adet;
            const toplam = adet * birimFiyat;
            document.getElementById('toplamDisplay').textContent = new Intl.NumberFormat('tr-TR').format(toplam) + ' ₺';
        }

        function toggleSubmit() {
            const checked = document.getElementById('okudum_anladim').checked;
            document.getElementById('submitBtn').disabled = !checked;
        }
    </script>
@endsection
