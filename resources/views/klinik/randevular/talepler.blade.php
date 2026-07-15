@extends('klinik.layout')
@section('baslik', 'Randevu Talepleri')
@section('sayfa_baslik', 'Onay Bekleyen Randevu Talepleri')

@section('icerik')
<div class="space-y-6">
    <!-- Filter Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm">
        <form method="GET" action="{{ route('hekim.klinik.randevular.talepler') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="space-y-1">
                <label for="doktor_id" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Hekim Filtresi</label>
                <select name="doktor_id" id="doktor_id" class="w-full">
                    <option value="">Tüm Hekimler</option>
                    @foreach($doktorlar as $doc)
                        <option value="{{ $doc->id }}" {{ $seciliDoktorId == $doc->id ? 'selected' : '' }}>
                            {{ $doc->unvan ? $doc->unvan . ' ' : '' }}{{ $doc->ad_soyad }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label for="tarih" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Tarih</label>
                <input type="date" name="tarih" id="tarih" value="{{ $seciliTarih }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-2.5 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-[#1E3A5F] hover:bg-[#152a47] text-white font-bold text-xs uppercase tracking-wider py-3 px-6 rounded-xl transition-all">Filtrele</button>
                <a href="{{ route('hekim.klinik.randevular.talepler') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs uppercase tracking-wider py-3 px-6 rounded-xl transition-all flex items-center justify-center">Sıfırla</a>
            </div>
        </form>
    </div>

    <!-- Requests Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Onay Bekleyen Talepler</h3>
                <span class="text-xs text-gray-500 mt-1 block">Toplam {{ $talepler->total() }} bekleyen talep</span>
            </div>
            @if(!$talepler->isEmpty())
                <div class="flex items-center gap-2">
                    <button type="button" onclick="topluIslem('onay')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-sm">Seçilenleri Onayla</button>
                    <button type="button" onclick="topluIslem('red')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-sm">Seçilenleri Reddet</button>
                </div>
            @endif
        </div>

        @if($talepler->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-[#111827] font-display">Tebrikler! Onay bekleyen hiçbir talep bulunmuyor.</h4>
                <p class="text-xs text-[#6B7280] mt-1">Yeni randevu talepleri geldiğinde bu alanda görünecektir.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                            <th class="px-6 py-4 w-12 text-center">
                                <input type="checkbox" id="check-all" class="w-4 h-4 rounded text-[#1E3A5F] focus:ring-[#1E3A5F] border-gray-300">
                            </th>
                            <th class="px-6 py-4">Hasta Bilgileri</th>
                            <th class="px-6 py-4">Hekim</th>
                            <th class="px-6 py-4">Talep Edilen Hizmet</th>
                            <th class="px-6 py-4">İstediği Gün & Saat</th>
                            <th class="px-6 py-4">Hasta Notu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] text-xs text-[#4B5563]">
                        @foreach($talepler as $talep)
                            <tr class="hover:bg-[#FAFAFA]/75 transition-colors">
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" name="talep_ids[]" value="{{ $talep->id }}" class="talep-check w-4 h-4 rounded text-[#1E3A5F] focus:ring-[#1E3A5F] border-gray-300">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[#111827] font-display">{{ $talep->ad }} {{ $talep->soyad }}</div>
                                    <div class="text-[10px] text-[#6B7280] mt-0.5">{{ $talep->telefon }}</div>
                                    <div class="text-[10px] text-[#6B7280]">{{ $talep->e_posta }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-[#1E3A5F]">{{ $talep->doktor->unvan ? $talep->doktor->unvan . ' ' : '' }}{{ $talep->doktor->ad_soyad }}</div>
                                    <div class="text-[10px] text-[#6B7280] mt-0.5">{{ $talep->doktor->uzmanlik_alani }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-[#111827]">{{ $talep->hizmet?->ad ?? 'Genel Hizmet' }}</div>
                                    <div class="text-[10px] text-[#C96A2B] font-semibold font-display mt-0.5">{{ $talep->hizmet?->sure ?? '-' }} Dakika</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[#111827] font-display">
                                        {{ \Carbon\Carbon::parse($talep->tarih)->translatedFormat('d F Y') }}
                                    </div>
                                    <div class="text-xs text-[#C96A2B] font-bold font-display mt-0.5">
                                        {{ substr($talep->saat, 0, 5) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 max-w-[200px]">
                                    @if($talep->not)
                                        <p class="leading-relaxed text-[#6B7280] italic" title="{{ $talep->not }}">{{ $talep->not }}</p>
                                    @else
                                        <span class="text-slate-300">Belirtilmedi</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($talepler->hasPages())
                <div class="p-6 border-t border-[#E5E7EB]">
                    {{ $talepler->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

@section('extra_js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('check-all');
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                document.querySelectorAll('.talep-check').forEach(cb => cb.checked = this.checked);
            });
        }
    });

    function topluIslem(tip) {
        const selectedIds = Array.from(document.querySelectorAll('.talep-check:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) {
            toastAc('Lütfen işlem yapmak istediğiniz randevu taleplerini seçin.', 'uyari');
            return;
        }

        const url = tip === 'onay' 
            ? '{{ route("hekim.klinik.randevular.toplu-onay") }}' 
            : '{{ route("hekim.klinik.randevular.toplu-red") }}';
        const msg = tip === 'onay'
            ? `${selectedIds.length} adet talebi onaylamak istediğinize emin misiniz?`
            : `${selectedIds.length} adet talebi reddetmek istediğinize emin misiniz?`;

        if (confirm(msg)) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ids: selectedIds
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    toastAc(data.message, 'basarili');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    mesajModalAc(data.message, 'hata');
                }
            })
            .catch(err => {
                console.error(err);
                mesajModalAc('İşlem sırasında sunucu taraflı bir hata oluştu.', 'hata');
            });
        }
    }
</script>
@endsection
