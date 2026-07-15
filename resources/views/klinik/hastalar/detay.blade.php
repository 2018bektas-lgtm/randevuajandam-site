@extends('klinik.layout')

@section('baslik', $hasta->ad_soyad . ' - Hasta Klinik Geçmişi')
@section('sayfa_baslik', 'Klinik Hasta Detayı')

@section('icerik')
    <div class="space-y-6">
        <!-- Back Link -->
        <div>
            <a href="{{ route('hekim.klinik.hastalar.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#6B7280] hover:text-[#111827] transition-colors">
                ← Ortak Hasta Havuzuna Dön
            </a>
        </div>

        @if(session('basari'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
                {{ session('basari') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Patient Profile Card & Private Clinic Note -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Patient Profile -->
                <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                    <div class="flex flex-col items-center text-center pb-6 border-b border-[#F5F5F4] mb-6">
                        <div class="w-16 h-16 rounded-full bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-xl font-bold font-display mb-3">
                            {{ mb_strtoupper(mb_substr($hasta->ad_soyad, 0, 2)) }}
                        </div>
                        <h3 class="text-base font-bold font-display text-[#111827]">{{ $hasta->ad_soyad }}</h3>
                        <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FAFAFA] border border-[#E5E7EB] text-[#4B5563]">Klinik Ortak Hasta</span>
                    </div>

                    <div class="space-y-4 text-xs font-semibold">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider block">Telefon</span>
                            <span class="text-[#111827] mt-1 block">{{ $hasta->telefon }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider block">E-posta</span>
                            <span class="text-[#111827] mt-1 block">{{ $hasta->e_posta }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider block">Sisteme Kayıt</span>
                            <span class="text-[#6B7280] mt-1 block">{{ $hasta->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Private Clinic Note Form -->
                <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                    <h4 class="text-sm font-bold font-display text-[#111827] mb-3">🔒 Ortak Klinik Notu</h4>
                    <p class="text-[10px] text-[#6B7280] mb-4 leading-relaxed">Bu not klinikteki tüm hekimler ve yetkili personeller tarafından görülebilir ve güncellenebilir.</p>

                    <form action="{{ route('hekim.klinik.hastalar.not-guncelle', $hasta->id) }}" method="POST">
                        @csrf
                        <textarea name="notlar" rows="5" placeholder="Hasta ile ilgili önemli klinik notlar..." class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl p-3 text-xs outline-none focus:border-[#C96A2B] resize-none">{{ old('notlar', $hasta->pivot->notlar ?? '') }}</textarea>
                        
                        <button type="submit" class="w-full mt-3 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider py-2.5 rounded-xl transition-all duration-200">
                            Notu Kaydet
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right 2 Columns: History -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Appointment History -->
                <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                    <h3 class="text-base font-bold font-display text-[#111827] mb-4">Tüm Klinik Randevuları</h3>

                    @if($randevular->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                                        <th class="pb-3 font-display">Hekim</th>
                                        <th class="pb-3 font-display">Hizmet</th>
                                        <th class="pb-3 font-display">Tarih & Saat</th>
                                        <th class="pb-3 text-right font-display">Ücret</th>
                                        <th class="pb-3 text-center font-display">Durum</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5E7EB]">
                                    @foreach($randevular as $randevu)
                                        <tr class="text-xs text-[#4B5563]">
                                            <td class="py-3.5 font-semibold text-[#111827]">
                                                {{ $randevu->doktor->unvan ? $randevu->doktor->unvan . ' ' : '' }}{{ $randevu->doktor->ad_soyad }}
                                            </td>
                                            <td class="py-3.5">{{ $randevu->hizmet->ad ?? 'Genel Randevu' }}</td>
                                            <td class="py-3.5">
                                                {{ \Carbon\Carbon::parse($randevu->tarih)->format('d.m.Y') }}
                                                <span class="text-[#C96A2B] font-semibold">{{ substr($randevu->saat, 0, 5) }}</span>
                                            </td>
                                            <td class="py-3.5 text-right font-bold text-[#111827]">
                                                ₺{{ number_format($randevu->ucret, 2, ',', '.') }}
                                            </td>
                                            <td class="py-3.5 text-center">
                                                <span class="inline-block px-2 py-0.5 rounded text-[9px] font-extrabold uppercase border
                                                    @if($randevu->durum === 'onaylandi') bg-emerald-50 text-emerald-700 border-emerald-200
                                                    @elseif($randevu->durum === 'tamamlandi') bg-blue-50 text-blue-700 border-blue-200
                                                    @elseif($randevu->durum === 'iptal') bg-red-50 text-red-700 border-red-200
                                                    @else bg-amber-50 text-amber-700 border-amber-200 @endif">
                                                    @if($randevu->durum === 'onaylandi') Onaylı
                                                    @elseif($randevu->durum === 'tamamlandi') Tamamlandı
                                                    @elseif($randevu->durum === 'iptal') İptal
                                                    @else Bekliyor @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-[#6B7280] py-4 text-center">Hastanın henüz bir klinik randevusu bulunmamaktadır.</p>
                    @endif
                </div>

                <!-- Payments History -->
                <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                    <h3 class="text-base font-bold font-display text-[#111827] mb-4">Tüm Klinik Ödeme Geçmişi</h3>

                    @if($odemeler->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                                        <th class="pb-3 font-display">Tarih</th>
                                        <th class="pb-3 font-display">Hekim</th>
                                        <th class="pb-3 font-display">Yöntem</th>
                                        <th class="pb-3 text-right font-display">Tutar</th>
                                        <th class="pb-3 text-center font-display">Durum</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5E7EB]">
                                    @foreach($odemeler as $odeme)
                                        <tr class="text-xs text-[#4B5563]">
                                            <td class="py-3.5">{{ $odeme->odeme_tarihi->format('d.m.Y') }}</td>
                                            <td class="py-3.5 font-semibold text-[#111827]">
                                                {{ $odeme->doktor->unvan ? $odeme->doktor->unvan . ' ' : '' }}{{ $odeme->doktor->ad_soyad }}
                                            </td>
                                            <td class="py-3.5 capitalize">
                                                @if($odeme->odeme_yontemi === 'kredi_karti')
                                                    Kredi Kartı
                                                @else
                                                    {{ $odeme->odeme_yontemi }}
                                                @endif
                                            </td>
                                            <td class="py-3.5 text-right font-bold text-[#111827]">
                                                ₺{{ number_format($odeme->odenen_tutar, 2, ',', '.') }}
                                            </td>
                                            <td class="py-3.5 text-center">
                                                <span class="inline-block px-2 py-0.5 rounded text-[9px] font-extrabold uppercase border
                                                    @if($odeme->durum === 'odendi') bg-emerald-50 text-emerald-700 border-emerald-200
                                                    @elseif($odeme->durum === 'kismi_odeme') bg-blue-50 text-blue-700 border-blue-200
                                                    @elseif($odeme->durum === 'iptal') bg-red-50 text-red-700 border-red-200
                                                    @else bg-amber-50 text-amber-700 border-amber-200 @endif">
                                                    @if($odeme->durum === 'odendi') Ödendi
                                                    @elseif($odeme->durum === 'kismi_odeme') Kısmi
                                                    @elseif($odeme->durum === 'iptal') İptal
                                                    @else Beklemede @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-[#6B7280] py-4 text-center">Hastaya ait klinik ödeme kaydı bulunmamaktadır.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
