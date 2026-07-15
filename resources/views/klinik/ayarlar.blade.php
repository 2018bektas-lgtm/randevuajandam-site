@extends('klinik.layout')

@section('baslik', 'Klinik Ayarları - ' . $klinik->ad)
@section('sayfa_baslik', 'Klinik Ayarları')

@section('icerik')
    @if(session('basari'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
            {!! session('basari') !!}
        </div>
    @endif

    @if(session('hata') || $errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold">
            {{ session('hata') ?? $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left 2 Columns: Ayarlar Formu -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Tabs Menu -->
            <div class="flex border-b border-[#E5E7EB] bg-white rounded-t-2xl px-6 pt-4 border-t border-x">
                <button type="button" class="tab-btn py-3 px-4 text-sm font-semibold text-[#C96A2B] border-b-2 border-[#C96A2B] outline-none transition-all" data-tab="genel">Klinik Bilgileri</button>
                <button type="button" class="tab-btn py-3 px-4 text-sm font-medium text-[#6B7280] hover:text-[#111827] outline-none transition-all" data-tab="saatler">Çalışma Saatleri</button>
                <button type="button" class="tab-btn py-3 px-4 text-sm font-medium text-[#6B7280] hover:text-[#111827] outline-none transition-all" data-tab="seo">SEO Ayarları</button>
            </div>

            <form action="{{ route('hekim.klinik.ayarlar.post') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <!-- Tab: Genel Bilgiler -->
                <div id="tab-genel" class="tab-content">
                    <div class="p-6 rounded-b-2xl rounded-tr-2xl bg-white border border-[#E5E7EB] shadow-sm space-y-6">
                        <h3 class="text-lg font-bold font-display text-[#111827] mb-6">Klinik Bilgileri</h3>

                        <!-- Logo Section -->
                        <div class="flex items-center gap-5 pb-4 border-b border-[#F5F5F4]">
                            @if($klinik->logo)
                                <img src="{{ asset($klinik->logo) }}" alt="Logo" class="w-16 h-16 rounded-xl object-cover border border-[#E5E7EB]" id="logo-preview">
                            @else
                                <div class="w-16 h-16 rounded-xl bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-xl font-bold font-display" id="logo-placeholder">
                                    {{ mb_strtoupper(mb_substr($klinik->ad, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <label class="block text-xs font-semibold text-[#4B5563] mb-1.5">Klinik Logosu</label>
                                <input type="file" name="logo" id="logo" accept="image/*" class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FFF7ED] file:text-[#C96A2B] hover:file:bg-[#FFF2E0] file:cursor-pointer">
                                <p class="text-[10px] text-[#9CA3AF] mt-1">Maksimum 2MB, sadece resim dosyaları</p>
                            </div>
                        </div>

                        <!-- General Information -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="ad" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Klinik Adı</label>
                                <input type="text" name="ad" id="ad" required value="{{ old('ad', $klinik->ad) }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                            </div>

                            <div>
                                <label for="telefon" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Telefon Numarası</label>
                                <input type="text" name="telefon" id="telefon" required value="{{ old('telefon', $klinik->telefon) }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                            </div>
                        </div>

                        <div>
                            <label for="e_posta" class="block text-xs font-semibold text-[#4B5563] mb-1.5">E-posta Adresi (Genel)</label>
                            <input type="email" name="e_posta" id="e_posta" value="{{ old('e_posta', $klinik->e_posta) }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                        </div>

                        <!-- Location Selection -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="il_id" class="block text-xs font-semibold text-[#4B5563] mb-1.5">İl</label>
                                <select name="il_id" id="il_id" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                                    <option value="">İl Seçin</option>
                                    @foreach($iller as $il)
                                        <option value="{{ $il->id }}" {{ old('il_id', $klinik->il_id) == $il->id ? 'selected' : '' }}>{{ $il->ad }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="ilce_id" class="block text-xs font-semibold text-[#4B5563] mb-1.5">İlçe</label>
                                <select name="ilce_id" id="ilce_id" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                                    @if($klinik->ilce)
                                        <option value="{{ $klinik->ilce->ad }}" selected>{{ $klinik->ilce->ad }}</option>
                                    @else
                                        <option value="">Önce İl Seçin</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="adres" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Açık Adres</label>
                            <textarea name="adres" id="adres" rows="3" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none resize-none">{{ old('adres', $klinik->adres) }}</textarea>
                        </div>

                        <div>
                            <label for="aciklama" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Klinik Hakkında / Açıklama</label>
                            <textarea name="aciklama" id="aciklama" rows="4" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none resize-none">{{ old('aciklama', $klinik->aciklama) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Tab: Çalışma Saatleri -->
                <div id="tab-saatler" class="tab-content hidden">
                    @include('klinik.ayarlar.calisma-saatleri')
                </div>

                <!-- Tab: SEO Ayarları -->
                <div id="tab-seo" class="tab-content hidden">
                    @include('klinik.ayarlar.seo')
                </div>

                <!-- Submit Button -->
                <div class="p-6 bg-white rounded-2xl border border-[#E5E7EB] shadow-sm">
                    <button type="submit" class="w-full bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl transition-all duration-200 hover:scale-[1.01]">
                        Tüm Değişiklikleri Kaydet
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Column: Abonelik Bilgileri -->
        <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold font-display text-[#111827]">Abonelik Detayları</h3>
                    <a href="{{ route('frontend.paketler') }}" class="text-xs font-semibold text-[#C96A2B] hover:underline">Paketleri İncele</a>
                </div>

                @if($klinik->paket)
                    <div class="space-y-4">
                        <div class="p-4 rounded-xl bg-amber-50/50 border border-amber-100/60">
                            <span class="text-[10px] uppercase font-bold text-[#C96A2B] block">Aktif Paket</span>
                            <span class="text-lg font-bold text-[#111827] mt-1 block">{{ $klinik->paket->ad }}</span>
                        </div>

                        <div class="text-xs space-y-2.5">
                            <div class="flex justify-between py-1.5 border-b border-[#F5F5F4]">
                                <span class="text-[#6B7280]">Ödeme Periyodu:</span>
                                <span class="font-semibold text-[#111827] capitalize">{{ $klinik->odeme_periyodu === 'yillik' ? 'Yıllık' : 'Aylık' }}</span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-[#F5F5F4]">
                                <span class="text-[#6B7280]">Maksimum Hekim:</span>
                                <span class="font-semibold text-[#111827]">{{ $klinik->max_doktor_sayisi }} Hekim</span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-[#F5F5F4]">
                                <span class="text-[#6B7280]">Maksimum Personel:</span>
                                <span class="font-semibold text-[#111827]">{{ $klinik->paket->max_personel_sayisi ?: '-' }} Personel</span>
                            </div>
                            <div class="flex justify-between py-1.5">
                                <span class="text-[#6B7280]">Üyelik Bitiş Tarihi:</span>
                                <span class="font-semibold text-[#111827]">
                                    {{ $klinik->uyelik_bitis ? ($klinik->uyelik_bitis instanceof \DateTime ? $klinik->uyelik_bitis->format('d.m.Y') : \Carbon\Carbon::parse($klinik->uyelik_bitis)->format('d.m.Y')) : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-[#6B7280] py-2">Kayıtlı paket bulunamadı.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Script for AJAX district loading & Tab switching -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab Switching Logic
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.dataset.tab;

                    // Update button classes
                    tabButtons.forEach(b => {
                        b.classList.remove('text-[#C96A2B]', 'border-b-2', 'border-[#C96A2B]', 'font-semibold');
                        b.classList.add('text-[#6B7280]', 'font-medium');
                    });
                    this.classList.remove('text-[#6B7280]', 'font-medium');
                    this.classList.add('text-[#C96A2B]', 'border-b-2', 'border-[#C96A2B]', 'font-semibold');

                    // Update content visibility
                    tabContents.forEach(content => {
                        if (content.id === `tab-${tabId}`) {
                            content.classList.remove('hidden');
                        } else {
                            content.classList.add('hidden');
                        }
                    });
                });
            });

            // District Loading Logic
            const ilSelect = document.getElementById('il_id');
            const ilceSelect = document.getElementById('ilce_id');

            ilSelect.addEventListener('change', function() {
                const ilId = this.value;
                if (!ilId) {
                    ilceSelect.innerHTML = '<option value="">Önce İl Seçin</option>';
                    return;
                }

                ilceSelect.innerHTML = '<option value="">Yükleniyor...</option>';

                fetch(`/iller/${ilId}/ilceler`)
                    .then(response => response.json())
                    .then(data => {
                        ilceSelect.innerHTML = '<option value="">İlçe Seçin</option>';
                        data.forEach(ilce => {
                            ilceSelect.innerHTML += `<option value="${ilce.ad}">${ilce.ad}</option>`;
                        });
                    })
                    .catch(err => {
                        console.error('İlçeler yüklenirken hata oluştu:', err);
                        ilceSelect.innerHTML = '<option value="">Hata oluştu</option>';
                    });
            });
        });
    </script>
@endsection
