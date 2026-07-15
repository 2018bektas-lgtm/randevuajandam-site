@extends('layouts.personel')

@section('baslik', 'Ödeme İşlemleri - Personel Paneli')
@section('sayfa_baslik', 'Ödeme Girişi & Kayıtları')

@section('icerik')



    @if(session('basari'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
            {{ session('basari') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Total Income Card -->
    <div class="mb-8 p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Seçilen Tarihteki Toplam Gelir</span>
            <span class="text-3xl font-extrabold font-display text-[#111827] mt-1.5 block">₺{{ number_format($toplamGelir, 2, ',', '.') }}</span>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.268-.118a5.5 5.5 0 007.478-4.992c0-3.037-2.463-5.5-5.5-5.5L9 3m3 3L9 6"></path>
            </svg>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left 2 Columns: Payments Log -->
        <div class="lg:col-span-2 space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <!-- Advanced Filter Form -->
                <form method="GET" action="{{ route('personel.odemeler.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label for="filterTarih" class="block text-[10px] font-bold text-[#9CA3AF] uppercase mb-1">Tarih</label>
                        <input type="date" id="filterTarih" name="tarih" value="{{ $tarih }}" onchange="this.form.submit()" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-2.5 text-xs outline-none focus:border-[#C96A2B]">
                    </div>

                    <div>
                        <label for="filterDoktor" class="block text-[10px] font-bold text-[#9CA3AF] uppercase mb-1">Hekim</label>
                        <select id="filterDoktor" name="doktor_id" onchange="this.form.submit()" class="w-full select2-select">
                            <option value="">Tüm Hekimler</option>
                            @foreach($doktorlar as $doc)
                                <option value="{{ $doc->id }}" {{ $secilenDoktorId == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->unvan ? $doc->unvan . ' ' : '' }}{{ $doc->ad_soyad }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filterDurum" class="block text-[10px] font-bold text-[#9CA3AF] uppercase mb-1">Durum</label>
                        <select id="filterDurum" name="durum" onchange="this.form.submit()" class="w-full select2-select">
                            <option value="">Tüm Durumlar</option>
                            <option value="odendi" {{ $durum == 'odendi' ? 'selected' : '' }}>Ödendi</option>
                            <option value="beklemede" {{ $durum == 'beklemede' ? 'selected' : '' }}>Onay Bekliyor</option>
                            <option value="kismi_odeme" {{ $durum == 'kismi_odeme' ? 'selected' : '' }}>Kısmi Ödeme</option>
                            <option value="iptal" {{ $durum == 'iptal' ? 'selected' : '' }}>İptal Edildi</option>
                        </select>
                    </div>
                </form>

                @if($odemeler->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                                    <th class="pb-3 font-display">Tarih</th>
                                    <th class="pb-3 font-display">Hasta</th>
                                    <th class="pb-3 font-display">Hekim</th>
                                    <th class="pb-3 font-display">Yöntem</th>
                                    <th class="pb-3 font-display text-right">Tutar</th>
                                    <th class="pb-3 font-display">Durum</th>
                                    <th class="pb-3 text-right font-display">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB]">
                                @foreach($odemeler as $odeme)
                                    <tr class="text-sm text-[#4B5563]">
                                        <td class="py-3.5 text-xs font-medium">
                                            {{ $odeme->odeme_tarihi->format('d.m.Y') }}
                                        </td>
                                        <td class="py-3.5">
                                            <div class="font-semibold text-[#111827]">{{ $odeme->hasta->ad_soyad }}</div>
                                            <div class="text-[10px] text-[#9CA3AF] mt-0.5">{{ $odeme->hasta->telefon }}</div>
                                        </td>
                                        <td class="py-3.5 text-xs font-semibold text-[#111827]">
                                            {{ $odeme->doktor->unvan ? $odeme->doktor->unvan . ' ' : '' }}{{ $odeme->doktor->ad_soyad }}
                                        </td>
                                        <td class="py-3.5 text-xs capitalize">
                                            @if($odeme->odeme_yontemi === 'kredi_karti')
                                                Kredi Kartı
                                            @else
                                                {{ $odeme->odeme_yontemi }}
                                            @endif
                                        </td>
                                        <td class="py-3.5 text-xs font-bold text-right text-[#111827]">
                                            ₺{{ number_format($odeme->odenen_tutar, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase border
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
                                        <td class="py-3.5 text-right">
                                            @if($odeme->durum !== 'iptal')
                                                <form action="{{ route('personel.odemeler.destroy', $odeme->id) }}" method="POST" onsubmit="return confirm('Bu ödeme kaydını iptal etmek istediğinize emin misiniz?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-bold uppercase">
                                                        İptal Et
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-400 font-medium">İptal Edildi</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $odemeler->appends(request()->query())->links() }}
                    </div>
                @else
                    <p class="text-xs text-[#6B7280] py-6 text-center">Filtre kriterlerine uygun ödeme kaydı bulunamadı.</p>
                @endif
            </div>
        </div>

        <!-- Right Column: Record Payment Form -->
        <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-5">Ödeme Al / Kaydet</h3>
                
                <form action="{{ route('personel.odemeler.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Patient Select2 ajax -->
                    <div class="space-y-1.5">
                        <label for="formDanisanSelect" class="text-xs font-semibold text-[#4B5563] block">Hasta</label>
                        <select id="formDanisanSelect" name="hasta_id" class="w-full" required></select>
                    </div>

                    <!-- Doctor Selection -->
                    <div class="space-y-1.5">
                        <label for="formDoktorSelect" class="text-xs font-semibold text-[#4B5563] block">Hekim</label>
                        <select id="formDoktorSelect" name="doktor_id" class="w-full select2-select" required>
                            <option value="">Hekim Seçin</option>
                            @foreach($doktorlar as $doc)
                                <option value="{{ $doc->id }}">
                                    {{ $doc->unvan ? $doc->unvan . ' ' : '' }}{{ $doc->ad_soyad }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date -->
                    <div>
                        <label for="odeme_tarihi" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Ödeme Tarihi</label>
                        <input id="odeme_tarihi" name="odeme_tarihi" type="date" value="{{ Carbon\Carbon::today()->toDateString() }}" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-xs focus:border-[#C96A2B] outline-none">
                    </div>

                    <!-- Amount -->
                    <div>
                        <label for="tutar" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Tutar (₺)</label>
                        <input id="tutar" name="tutar" type="number" step="0.01" required min="0.01" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-xs focus:border-[#C96A2B] outline-none" placeholder="0.00">
                    </div>

                    <!-- Payment Method -->
                    <div class="space-y-1.5">
                        <label for="odeme_yontemi" class="text-xs font-semibold text-[#4B5563] block">Ödeme Yöntemi</label>
                        <select id="odeme_yontemi" name="odeme_yontemi" class="w-full select2-select" required>
                            <option value="nakit">Nakit</option>
                            <option value="kredi_karti">Kredi Kartı</option>
                            <option value="havale">Havale</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="aciklama" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Açıklama</label>
                        <textarea id="aciklama" name="aciklama" rows="3" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-xs focus:border-[#C96A2B] outline-none" placeholder="Ödeme açıklaması..."></textarea>
                    </div>

                    <button type="submit" class="w-full bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl transition-all duration-200">
                        Ödeme Kaydet
                    </button>
                </form>
            </div>
        </div>
    </div>

<!-- Scripts -->
<script>
    $(document).ready(function() {
        // Initialize standard Select2 components
        $('.select2-select').select2({
            width: '100%',
            minimumResultsForSearch: Infinity
        });

        // Initialize patient autocomplete
        $('#formDanisanSelect').select2({
            ajax: {
                url: '{{ route("personel.randevular.hastalar-ara") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data.results };
                },
                cache: true
            },
            placeholder: 'Hasta seçin (ad veya telefon)...',
            minimumInputLength: 2,
            width: '100%'
        });
    });
</script>
@endsection
