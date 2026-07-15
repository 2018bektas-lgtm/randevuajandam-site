@extends('klinik.layout')

@section('baslik', 'Klinik Bilgileri - ' . $klinik->ad)
@section('sayfa_baslik', 'Klinik Bilgileri')

@section('icerik')
    @if(session('basari'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
            {{ session('basari') }}
        </div>
    @endif

    @if(session('hata'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold">
            {{ session('hata') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left 2 Columns: Klinik Genel Kartı -->
        <div class="lg:col-span-2 space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <div class="flex items-center gap-5 pb-6 border-b border-[#F5F5F4] mb-6">
                    @if($klinik->logo)
                        <img src="{{ asset($klinik->logo) }}" alt="Logo" class="w-20 h-20 rounded-xl object-cover border border-[#E5E7EB]">
                    @else
                        <div class="w-20 h-20 rounded-xl bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-2xl font-bold font-display">
                            {{ mb_strtoupper(mb_substr($klinik->ad, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <h3 class="text-xl font-bold font-display text-[#111827]">{{ $klinik->ad }}</h3>
                        <p class="text-xs text-[#6B7280] mt-1">Klinik Üyesi Hesabı</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase">Telefon</span>
                        <p class="font-semibold text-[#111827] mt-1">{{ $klinik->telefon }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase">E-posta</span>
                        <p class="font-semibold text-[#111827] mt-1">{{ $klinik->e_posta ?: '-' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-xs font-semibold text-gray-400 uppercase">Adres</span>
                        <p class="font-semibold text-[#111827] mt-1">
                            {{ $klinik->adres }}<br>
                            {{ $klinik->ilce->ad ?? '' }} / {{ $klinik->il->ad ?? '' }}
                        </p>
                    </div>
                    @if($klinik->aciklama)
                        <div class="sm:col-span-2">
                            <span class="text-xs font-semibold text-gray-400 uppercase">Klinik Hakkında</span>
                            <p class="text-sm text-[#4B5563] mt-1.5 leading-relaxed whitespace-pre-line">{{ $klinik->aciklama }}</p>
                        </div>
                    @endif
                </div>

                <!-- Leaving Clinic Action (Only for non-owners) -->
                @if(auth('doktor')->user()->id !== $klinik->sahip_doktor_id)
                    <div class="mt-8 pt-6 border-t border-[#F5F5F4] flex justify-end">
                        <form action="{{ route('hekim.klinik.uye.ayril') }}" method="POST" onsubmit="return confirm('Bu klinikten ayrılmak istediğinize emin misiniz? Ayrıldıktan sonra kendi bireysel hekimliğinize geri dönebilir ve paket satın alabilirsiniz.');">
                            @csrf
                            <button type="submit" class="px-5 py-2.5 bg-red-50 hover:bg-red-100 border border-red-200 text-red-600 font-semibold text-xs rounded-xl transition-colors">
                                Kliniği Bırak / Ayrıl
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Klinik Doktorları -->
        <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-4">Klinik Çalışma Arkadaşları</h3>
                
                <div class="space-y-4">
                    @foreach($doktorlar as $doc)
                        @php
                            $kisaAd = '';
                            if ($doc->ad_soyad) {
                                $words = explode(' ', $doc->ad_soyad);
                                $kisaAd = mb_strtoupper(mb_substr($words[0], 0, 1));
                                if (count($words) > 1) {
                                    $kisaAd .= mb_strtoupper(mb_substr(end($words), 0, 1));
                                }
                            } else {
                                $kisaAd = 'HE';
                            }
                        @endphp
                        <div class="flex items-center gap-3">
                            @if($doc->profil_resmi)
                                <img src="{{ asset($doc->profil_resmi) }}" alt="{{ $doc->ad_soyad }}" class="w-9 h-9 rounded-full object-cover border border-[#E7B58A]/20 shrink-0">
                            @else
                                <div class="w-9 h-9 rounded-full bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/20 text-[#C96A2B] text-xs font-bold font-display shrink-0">
                                    {{ $kisaAd }}
                                </div>
                            @endif
                            <div>
                                <h4 class="text-xs font-bold text-[#111827]">{{ $doc->unvan ? $doc->unvan . ' ' : '' }}{{ $doc->ad_soyad }}</h4>
                                <p class="text-[10px] text-[#6B7280] mt-0.5">{{ $doc->uzmanlik_alani }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
