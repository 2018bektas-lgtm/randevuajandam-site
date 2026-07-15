@extends('layouts.personel')

@section('baslik', $hasta->ad_soyad . ' - Hasta Detayı')
@section('sayfa_baslik', 'Hasta Profil Detayı')

@section('icerik')
    <div class="space-y-6">
        <!-- Back button and title -->
        <div class="flex items-center justify-between">
            <a href="{{ route('personel.hastalar.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#6B7280] hover:text-[#111827] transition-colors">
                ← Listeye Geri Dön
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Patient Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                    <div class="flex flex-col items-center text-center pb-6 border-b border-[#F5F5F4] mb-6">
                        <div class="w-16 h-16 rounded-full bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-xl font-bold font-display mb-3">
                            {{ mb_strtoupper(mb_substr($hasta->ad_soyad, 0, 2)) }}
                        </div>
                        <h3 class="text-base font-bold font-display text-[#111827]">{{ $hasta->ad_soyad }}</h3>
                        <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FAFAFA] border border-[#E5E7EB] text-[#4B5563]">Klinik Kayıtlı Hasta</span>
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
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider block">Sisteme Katılım</span>
                            <span class="text-[#6B7280] mt-1 block">{{ $hasta->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 2 Columns: Appointments History in Clinic -->
            <div class="lg:col-span-2 space-y-6">
                <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                    <h3 class="text-base font-bold font-display text-[#111827] mb-5">Klinik Randevu Geçmişi</h3>

                    @if($randevular->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                                        <th class="pb-3 font-display">Hekim</th>
                                        <th class="pb-3 font-display">Hizmet</th>
                                        <th class="pb-3 font-display">Tarih & Saat</th>
                                        <th class="pb-3 font-display">Ücret</th>
                                        <th class="pb-3 font-display">Durum</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5E7EB]">
                                    @foreach($randevular as $randevu)
                                        <tr class="text-sm text-[#4B5563]">
                                            <td class="py-3.5 font-semibold text-[#111827]">
                                                {{ $randevu->doktor->unvan ? $randevu->doktor->unvan . ' ' : '' }}{{ $randevu->doktor->ad_soyad }}
                                            </td>
                                            <td class="py-3.5 text-xs">
                                                {{ $randevu->hizmet?->ad ?? 'Genel Hizmet' }}
                                            </td>
                                            <td class="py-3.5 text-xs font-medium">
                                                <div>{{ \Carbon\Carbon::parse($randevu->tarih)->format('d.m.Y') }}</div>
                                                <div class="text-[#C96A2B] mt-0.5">{{ substr($randevu->saat, 0, 5) }}</div>
                                            </td>
                                            <td class="py-3.5 text-xs font-semibold text-[#111827]">
                                                ₺{{ number_format($randevu->ucret, 2, ',', '.') }}
                                            </td>
                                            <td class="py-3.5">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase border
                                                    @if($randevu->durum === 'onaylandi') bg-emerald-50 text-emerald-700 border-emerald-200
                                                    @elseif($randevu->durum === 'tamamlandi') bg-blue-50 text-blue-700 border-blue-200
                                                    @elseif($randevu->durum === 'iptal') bg-red-50 text-red-700 border-red-200
                                                    @else bg-amber-50 text-amber-700 border-amber-200 @endif">
                                                    @if($randevu->durum === 'onaylandi') Onaylandı
                                                    @elseif($randevu->durum === 'tamamlandi') Tamamlandı
                                                    @elseif($randevu->durum === 'iptal') İptal Edildi
                                                    @else Onay Bekliyor @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-[#6B7280] py-6 text-center">Bu hastanın klinikte geçmiş randevusu bulunmamaktadır.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
