@extends('klinik.layout')
@section('baslik', 'Duyuru Yönetimi')
@section('sayfa_baslik', 'Duyuru Yönetimi')

@section('icerik')
<div class="space-y-6">
    <!-- Add Announcement Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6">
        <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display mb-4">📢 Yeni Duyuru Yayınla</h3>
        <form action="{{ route('hekim.klinik.duyurular.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="baslik" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Duyuru Başlığı</label>
                    <input type="text" name="baslik" id="baslik" required placeholder="Duyuru başlığını girin..." class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-2.5 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                </div>
                <div class="space-y-1">
                    <label for="onem_derecesi" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Önem Derecesi</label>
                    <select name="onem_derecesi" id="onem_derecesi" required class="w-full">
                        <option value="genel">Genel (Mavi)</option>
                        <option value="onemli">Önemli (Turuncu)</option>
                        <option value="acil">Acil (Kırmızı)</option>
                    </select>
                </div>
            </div>
            <div class="space-y-1">
                <label for="icerik" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Duyuru İçeriği</label>
                <textarea name="icerik" id="icerik" rows="4" required placeholder="Duyuru detaylarını buraya yazın..." class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-2.5 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none resize-none"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-[#1E3A5F] hover:bg-[#152a47] text-white font-bold text-xs uppercase tracking-wider py-3 px-6 rounded-xl transition-all">Duyuruyu Yayınla</button>
            </div>
        </form>
    </div>

    <!-- Announcements List Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-[#E5E7EB]">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Geçmiş Duyurular</h3>
        </div>

        @if($duyurular->isEmpty())
            <div class="p-12 text-center text-sm text-gray-500">
                Henüz yayınlanmış bir duyuru bulunmuyor.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                            <th class="px-6 py-4">Başlık & İçerik</th>
                            <th class="px-6 py-4 w-32">Önem Derecesi</th>
                            <th class="px-6 py-4 w-32">Durum</th>
                            <th class="px-6 py-4 w-28">Tarih</th>
                            <th class="px-6 py-4 w-28 text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] text-xs text-[#4B5563]">
                        @foreach($duyurular as $duyuru)
                            <tr class="hover:bg-[#FAFAFA]/75 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[#111827] font-display text-sm">{{ $duyuru->baslik }}</div>
                                    <div class="text-[11px] text-[#6B7280] mt-1 max-w-lg truncate">{{ Str::limit($duyuru->icerik, 80) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider font-display border
                                        @if($duyuru->onem_derecesi === 'acil') bg-red-50 text-red-700 border-red-200
                                        @elseif($duyuru->onem_derecesi === 'onemli') bg-amber-50 text-amber-700 border-amber-200
                                        @else bg-blue-50 text-blue-700 border-blue-200 @endif">
                                        {{ $duyuru->onem_derecesi }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <form action="{{ route('hekim.klinik.duyurular.toggle', $duyuru->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase transition-colors cursor-pointer border
                                            @if($duyuru->aktif_mi) bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 @else bg-gray-50 text-gray-500 border-gray-200 hover:bg-gray-150 @endif">
                                            {{ $duyuru->aktif_mi ? 'Aktif' : 'Pasif' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-gray-500 font-display">{{ $duyuru->created_at->format('d.m.Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('hekim.klinik.duyurular.edit', $duyuru->id) }}" class="p-2 text-gray-500 hover:text-[#C96A2B] transition-colors" title="Düzenle">
                                            ✏️
                                        </a>
                                        <form action="{{ route('hekim.klinik.duyurular.destroy', $duyuru->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?')" class="p-2 text-gray-500 hover:text-red-600 transition-colors cursor-pointer">
                                                ❌
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($duyurular->hasPages())
                <div class="p-6 border-t border-[#E5E7EB]">
                    {{ $duyurular->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
