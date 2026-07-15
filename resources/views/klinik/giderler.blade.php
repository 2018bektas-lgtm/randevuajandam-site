@extends('klinik.layout')

@section('baslik', 'Klinik Giderleri - ' . $klinik->ad)
@section('sayfa_baslik', 'Klinik Gider Yönetimi')

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
        <!-- Left 2 Columns: Giderler Listesi -->
        <div class="lg:col-span-2 space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold font-display text-[#111827]">Gider Kayıtları</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FFF7ED] text-[#C96A2B]">
                        Toplam Gider: ₺{{ number_format($giderler->sum('tutar'), 2, ',', '.') }}
                    </span>
                </div>

                @if($giderler->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                                    <th class="pb-3 font-display">Tarih</th>
                                    <th class="pb-3 font-display">Kategori</th>
                                    <th class="pb-3 font-display">Gider Başlığı</th>
                                    <th class="pb-3 font-display">Tutar</th>
                                    <th class="pb-3 font-display">Açıklama</th>
                                    <th class="pb-3 font-display text-right">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB]">
                                @foreach($giderler as $gider)
                                    <tr class="text-sm text-[#4B5563]">
                                        <td class="py-3.5 text-xs text-[#6B7280]">
                                            {{ $gider->tarih instanceof \DateTime ? $gider->tarih->format('d.m.Y') : \Carbon\Carbon::parse($gider->tarih)->format('d.m.Y') }}
                                        </td>
                                        <td class="py-3.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider font-display border
                                                @if($gider->kategori === 'kira') bg-blue-50 text-blue-700 border-blue-200/50
                                                @elseif($gider->kategori === 'personel_maas') bg-purple-50 text-purple-700 border-purple-200/50
                                                @elseif($gider->kategori === 'fatura') bg-orange-50 text-orange-700 border-orange-200/50
                                                @elseif($gider->kategori === 'malzeme') bg-teal-50 text-teal-700 border-teal-200/50
                                                @elseif($gider->kategori === 'bakim_onarim') bg-yellow-50 text-yellow-700 border-yellow-200/50
                                                @elseif($gider->kategori === 'sigorta') bg-indigo-50 text-indigo-700 border-indigo-200/50
                                                @else bg-gray-50 text-gray-700 border-gray-200/50 @endif">
                                                @if($gider->kategori === 'kira') Kira
                                                @elseif($gider->kategori === 'personel_maas') Personel Maaşı
                                                @elseif($gider->kategori === 'fatura') Fatura
                                                @elseif($gider->kategori === 'malzeme') Malzeme
                                                @elseif($gider->kategori === 'bakim_onarim') Bakım & Onarım
                                                @elseif($gider->kategori === 'sigorta') Sigorta
                                                @else Diğer @endif
                                            </span>
                                        </td>
                                        <td class="py-3.5 font-semibold text-[#111827]">{{ $gider->baslik }}</td>
                                        <td class="py-3.5 font-bold text-red-600">₺{{ number_format($gider->tutar, 2, ',', '.') }}</td>
                                        <td class="py-3.5 text-xs text-[#6B7280] max-w-[150px] truncate" title="{{ $gider->aciklama }}">{{ $gider->aciklama ?: '-' }}</td>
                                        <td class="py-3.5 text-right">
                                            <form action="{{ route('hekim.klinik.giderler.destroy', $gider->id) }}" method="POST" onsubmit="return confirm('Bu gider kaydını silmek istediğinize emin misiniz?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-xs">Sil</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-xs text-[#6B7280] py-6 text-center">Kliniğe ait henüz bir gider kaydı bulunmamaktadır.</p>
                @endif
            </div>
        </div>

        <!-- Right Column: Gider Ekle Formu -->
        <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-4">Gider Ekle</h3>

                <form action="{{ route('hekim.klinik.giderler.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="baslik" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Gider Başlığı</label>
                            <input type="text" name="baslik" id="baslik" required placeholder="Temmuz Ayı Dükkan Kirası" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                        </div>

                        <div>
                            <label for="kategori" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Gider Kategorisi</label>
                            <select name="kategori" id="kategori" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                                <option value="kira">Kira</option>
                                <option value="personel_maas">Personel Maaşı</option>
                                <option value="fatura">Fatura</option>
                                <option value="malzeme">Medikal / Ofis Malzemesi</option>
                                <option value="bakim_onarim">Bakım & Onarım</option>
                                <option value="sigorta">Sigorta</option>
                                <option value="diger">Diğer</option>
                            </select>
                        </div>

                        <div>
                            <label for="tutar" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Gider Tutarı (₺)</label>
                            <input type="number" name="tutar" id="tutar" step="0.01" min="0.01" required placeholder="12500.00" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                        </div>

                        <div>
                            <label for="tarih" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Gider Tarihi</label>
                            <input type="date" name="tarih" id="tarih" required value="{{ date('Y-m-d') }}" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                        </div>

                        <div>
                            <label for="aciklama" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Ek Açıklama (Opsiyonel)</label>
                            <textarea name="aciklama" id="aciklama" rows="3" placeholder="Gidere dair notlar..." class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none resize-none"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl transition-all duration-200 hover:scale-[1.01]">
                            Gideri Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
