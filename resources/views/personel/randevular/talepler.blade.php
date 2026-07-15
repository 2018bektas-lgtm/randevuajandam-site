@extends('layouts.personel')

@section('baslik', 'Randevu Talepleri - Personel Paneli')
@section('sayfa_baslik', 'Bekleyen Randevu Talepleri')

@section('icerik')
    @if(session('basari'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
            {{ session('basari') }}
        </div>
    @endif

    <div class="space-y-6">
        <!-- Hekim Filtresi ve Bilgilendirme -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold font-display text-[#111827]">Randevu Talepleri</h3>
                <p class="text-xs text-[#6B7280] mt-1">Hastalar tarafından online olarak alınan onay bekleyen randevuları buradan yönetebilirsiniz.</p>
            </div>
            
            <form method="GET" action="{{ route('personel.randevular.talepler') }}" class="flex items-center gap-2 shrink-0">
                <label for="filterDoktor" class="text-xs font-bold text-[#111827] font-display">Hekim Filtresi:</label>
                <select id="filterDoktor" name="doktor_id" onchange="this.form.submit()" class="bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-2.5 text-xs font-semibold focus:border-[#C96A2B] outline-none min-w-[200px] cursor-pointer">
                    <option value="">Tüm Hekimler</option>
                    @foreach($doktorlar as $doc)
                        <option value="{{ $doc->id }}" {{ $secilenDoktorId == $doc->id ? 'selected' : '' }}>
                            {{ $doc->unvan ? $doc->unvan . ' ' : '' }}{{ $doc->ad_soyad }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Talepler Listesi -->
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
            @if($talepler->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                                <th class="pb-3 font-display">Hasta Adı</th>
                                <th class="pb-3 font-display">İletişim</th>
                                <th class="pb-3 font-display">Hekim</th>
                                <th class="pb-3 font-display">Hizmet</th>
                                <th class="pb-3 font-display">Tarih & Saat</th>
                                <th class="pb-3 font-display">Not</th>
                                <th class="pb-3 text-right font-display">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB]">
                            @foreach($talepler as $talep)
                                <tr class="text-sm text-[#4B5563]">
                                    <td class="py-3.5 font-semibold text-[#111827]">
                                        {{ $talep->ad }} {{ $talep->soyad }}
                                    </td>
                                    <td class="py-3.5 text-xs">
                                        <div>{{ $talep->telefon }}</div>
                                        <div class="text-[#9CA3AF] mt-0.5">{{ $talep->e_posta }}</div>
                                    </td>
                                    <td class="py-3.5 text-xs font-medium text-[#111827]">
                                        {{ $talep->doktor->unvan ? $talep->doktor->unvan . ' ' : '' }}{{ $talep->doktor->ad_soyad }}
                                    </td>
                                    <td class="py-3.5 text-xs">
                                        {{ $talep->hizmet?->ad ?? 'Genel Hizmet' }}
                                    </td>
                                    <td class="py-3.5 text-xs font-medium">
                                        <div>{{ \Carbon\Carbon::parse($talep->tarih)->format('d.m.Y') }}</div>
                                        <div class="text-[#C96A2B] mt-0.5">{{ substr($talep->saat, 0, 5) }}</div>
                                    </td>
                                    <td class="py-3.5 text-xs text-[#6B7280] max-w-[150px] truncate" title="{{ $talep->not }}">
                                        {{ $talep->not ?: '-' }}
                                    </td>
                                    <td class="py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Onayla Form -->
                                            <form action="{{ route('personel.randevular.talep-onayla', $talep->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-lg transition-colors">
                                                    Onayla
                                                </button>
                                            </form>

                                            <!-- Reddet/İptal Form -->
                                            <form action="{{ route('personel.randevular.talep-reddet', $talep->id) }}" method="POST" onsubmit="return confirm('Bu randevu talebini reddetmek istediğinize emin misiniz?');">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 border border-red-200 text-red-600 text-xs font-bold rounded-lg transition-colors">
                                                    Reddet
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $talepler->appends(request()->query())->links() }}
                </div>
            @else
                <div class="py-8 text-center text-xs text-[#6B7280]">
                    Kriterlere uygun onay bekleyen herhangi bir randevu talebi bulunmamaktadır.
                </div>
            @endif
        </div>
    </div>
@endsection
