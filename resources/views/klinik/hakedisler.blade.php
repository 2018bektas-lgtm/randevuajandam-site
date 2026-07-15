@extends('klinik.layout')

@section('baslik', 'Hakediş Yönetimi - ' . $klinik->ad)
@section('sayfa_baslik', 'Klinik Hekim Hakedişleri')

@section('icerik')
    @if(session('basari'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
            {{ session('basari') }}
        </div>
    @endif

    @if(session('hata') || $errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold">
            {{ session('hata') ?? $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left 2 Columns: Hakediş Kayıtları Listesi -->
        <div class="lg:col-span-2 space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-6">Hakediş Dönemleri</h3>

                @if($hakedisler->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                                    <th class="pb-3 font-display">Hekim</th>
                                    <th class="pb-3 font-display">Dönem</th>
                                    <th class="pb-3 font-display">Brüt Gelir</th>
                                    <th class="pb-3 font-display">Klinik Komisyonu</th>
                                    <th class="pb-3 font-display">Net Hakediş</th>
                                    <th class="pb-3 font-display">Durum</th>
                                    <th class="pb-3 font-display text-right">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB]">
                                @foreach($hakedisler as $hakedis)
                                    <tr class="text-sm text-[#4B5563]">
                                        <td class="py-3.5 font-semibold text-[#111827]">
                                            {{ $hakedis->doktor ? $hakedis->doktor->unvan . ' ' . $hakedis->doktor->ad_soyad : 'Kayıtsız Hekim' }}
                                        </td>
                                        <td class="py-3.5 text-xs text-[#6B7280]">
                                            {{ $hakedis->donem_baslangic instanceof \DateTime ? $hakedis->donem_baslangic->format('d.m.Y') : \Carbon\Carbon::parse($hakedis->donem_baslangic)->format('d.m.Y') }}
                                            <span class="mx-1">-</span>
                                            {{ $hakedis->donem_bitis instanceof \DateTime ? $hakedis->donem_bitis->format('d.m.Y') : \Carbon\Carbon::parse($hakedis->donem_bitis)->format('d.m.Y') }}
                                        </td>
                                        <td class="py-3.5 font-bold text-gray-900">₺{{ number_format($hakedis->toplam_gelir, 2, ',', '.') }}</td>
                                        <td class="py-3.5 text-xs text-red-600">
                                            ₺{{ number_format($hakedis->komisyon_tutari, 2, ',', '.') }}
                                            <span class="text-gray-400 font-semibold">({{ number_format($hakedis->komisyon_orani, 0) }}%)</span>
                                        </td>
                                        <td class="py-3.5 font-bold text-emerald-600">₺{{ number_format($hakedis->net_hakedis, 2, ',', '.') }}</td>
                                        <td class="py-3.5">
                                            @if($hakedis->durum === 'hesaplandi')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">Hesaplandı</span>
                                            @elseif($hakedis->durum === 'onaylandi')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">Onaylandı</span>
                                            @elseif($hakedis->durum === 'odendi')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Ödendi</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-50 text-gray-700">{{ $hakedis->durum }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 text-right">
                                            <form action="{{ route('hekim.klinik.hakedisler.durum', $hakedis->id) }}" method="POST" class="inline-flex items-center gap-1.5">
                                                @csrf
                                                <select name="durum" onchange="this.form.submit()" class="text-xs bg-[#FAFAFA] border border-[#E5E7EB] rounded-lg px-2 py-1 outline-none focus:border-[#C96A2B]">
                                                    <option value="hesaplandi" {{ $hakedis->durum === 'hesaplandi' ? 'selected' : '' }}>Hesaplandı</option>
                                                    <option value="onaylandi" {{ $hakedis->durum === 'onaylandi' ? 'selected' : '' }}>Onayla</option>
                                                    <option value="odendi" {{ $hakedis->durum === 'odendi' ? 'selected' : '' }}>Ödendi Yap</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-xs text-[#6B7280] py-6 text-center">Kliniğe ait henüz bir hakediş kaydı bulunmamaktadır.</p>
                @endif
            </div>
        </div>

        <!-- Right Column: Hakediş Hesapla Formu -->
        <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-2">Hakediş Hesapla</h3>
                <p class="text-xs text-[#6B7280] mb-5 leading-relaxed">
                    Belirlediğiniz bir tarih aralığında, hekimlerin toplam brüt kazançlarını ve kliniğinize ait komisyon oranlarını hesaba katarak net hakedişleri oluşturabilirsiniz.
                </p>

                <form action="{{ route('hekim.klinik.hakedisler.hesapla') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="doktor_id" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Hekim Seçimi</label>
                            <select name="doktor_id" id="doktor_id" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                                @foreach($doktorlar as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->unvan ? $doc->unvan . ' ' : '' }}{{ $doc->ad_soyad }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="donem_baslangic" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Dönem Başlangıç</label>
                                <input type="date" name="donem_baslangic" id="donem_baslangic" required value="{{ date('Y-m-01') }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                            </div>

                            <div>
                                <label for="donem_bitis" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Dönem Bitiş</label>
                                <input type="date" name="donem_bitis" id="donem_bitis" required value="{{ date('Y-m-t') }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                            </div>
                        </div>

                        <div>
                            <label for="komisyon_orani" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Klinik Komisyon Oranı (%)</label>
                            <input type="number" name="komisyon_orani" id="komisyon_orani" min="0" max="100" required placeholder="30" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                        </div>

                        <button type="submit" class="w-full bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl transition-all duration-200 hover:scale-[1.01]">
                            Hakediş Hesapla
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
