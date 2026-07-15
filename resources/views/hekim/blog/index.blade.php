@extends('hekim.layout')

@section('baslik', 'Blog Yazılarım - Hekim Paneli')
@section('sayfa_baslik', 'Blog Yazılarım')

@section('icerik')
<div class="mb-6 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
    <p class="text-sm text-[#6B7280]">
        Hekim profil sayfanızda yayınlanacak blog yazılarını buradan yönetebilirsiniz.
    </p>
    <a href="{{ route('hekim.bloglar.create') }}" class="px-5 py-2.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-200 shadow-sm font-display flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
        </svg>
        Yeni Yazı Ekle
    </a>
</div>

<div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] overflow-hidden">
    @if($bloglar->isEmpty())
        <div class="p-12 text-center">
            <div class="w-16 h-16 bg-[#FFF7ED] text-[#C96A2B] rounded-full flex items-center justify-center mx-auto mb-4 border border-[#E7B58A]/30">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12"></path>
                </svg>
            </div>
            <h3 class="text-base font-bold font-display text-[#111827]">Henüz Blog Yazısı Eklemediniz</h3>
            <p class="text-xs text-[#6B7280] mt-1.5 max-w-sm mx-auto">
                Profilinizde uzmanlık alanınızla ilgili faydalı makaleler paylaşarak hastalarınızın dikkatini çekebilirsiniz.
            </p>
            <a href="{{ route('hekim.bloglar.create') }}" class="inline-flex mt-5 px-4 py-2 bg-[#1F2937] hover:bg-[#111827] text-white text-xs font-bold rounded-lg transition-colors font-display">
                İlk Blog Yazısını Ekle
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#E5E7EB] bg-slate-50/70">
                        <th class="px-6 py-4 text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">Görsel</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">Başlık / Detay</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">Okunma</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">Yayın Tarihi</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">Durum</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @foreach($bloglar as $blog)
                        <tr class="hover:bg-slate-50/40 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($blog->resim)
                                    <img src="{{ asset($blog->resim) }}" alt="{{ $blog->baslik }}" class="w-16 h-10 object-cover rounded-lg border border-slate-100 shadow-sm">
                                @else
                                    <div class="w-16 h-10 bg-slate-100 rounded-lg flex items-center justify-center border border-slate-200 text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-md">
                                    <a href="{{ $blog->url }}" target="_blank" class="block font-bold text-[#111827] text-sm hover:text-[#C96A2B] transition-colors truncate font-display">
                                        {{ $blog->baslik }}
                                    </a>
                                    <span class="block text-[11px] text-[#6B7280] mt-0.5 truncate">
                                        {{ Str::limit(strip_tags($blog->icerik), 80) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#4B5563] font-medium font-display">
                                {{ $blog->okunma_sayisi }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-[#6B7280]">
                                {{ $blog->created_at->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($blog->aktif_mi)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wide font-display">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-slate-50 text-slate-500 border border-slate-200 uppercase tracking-wide font-display">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Taslak
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium space-x-2">
                                <a href="{{ route('hekim.bloglar.edit', $blog->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-[#E5E7EB] rounded-lg text-xs font-bold text-[#4B5563] hover:text-[#C96A2B] hover:bg-[#FFF7ED] hover:border-[#E7B58A]/30 transition-all font-display">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"></path>
                                    </svg>
                                    Düzenle
                                </a>

                                <form action="{{ route('hekim.bloglar.destroy', $blog->id) }}" method="POST" class="inline" onsubmit="return onayModalAc(event, this, 'Bu blog yazısını silmek istediğinize emin misiniz? Bu işlem geri alınamaz.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-red-100 rounded-lg text-xs font-bold text-red-600 hover:bg-red-50 hover:border-red-200 transition-all font-display cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path>
                                        </svg>
                                        Sil
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($bloglar->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB] bg-slate-50/50">
                {{ $bloglar->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
