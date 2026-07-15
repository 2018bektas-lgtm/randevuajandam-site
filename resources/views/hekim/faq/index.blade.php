@extends('hekim.layout')

@section('baslik', 'Sıkça Sorulan Sorular - Hekim Paneli')
@section('sayfa_baslik', 'Sıkça Sorulan Sorular (SSS)')

@section('icerik')
<div class="mb-6 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
    <p class="text-sm text-[#6B7280]">
        Hekim profil sayfanızda görüntülenecek Sıkça Sorulan Soruları buradan yönetebilirsiniz.
    </p>
    <button onclick="toggleModal('addFaqModal')" class="px-5 py-2.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-200 shadow-sm font-display flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
        </svg>
        Yeni Soru Ekle
    </button>
</div>

@if(session('basarili'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">{{ session('basarili') }}</div>
@endif

<div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] overflow-hidden">
    @if($faqs->isEmpty())
        <div class="p-12 text-center">
            <div class="w-16 h-16 bg-[#FFF7ED] text-[#C96A2B] rounded-full flex items-center justify-center mx-auto mb-4 border border-[#E7B58A]/30">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"></path>
                </svg>
            </div>
            <h3 class="text-base font-bold font-display text-[#111827]">Henüz Soru Eklemediniz</h3>
            <p class="text-xs text-[#6B7280] mt-1.5 max-w-sm mx-auto">
                Profilinizde hastalarınızdan gelen en yaygın soruları ve cevaplarını paylaşarak bilgilendirme yapabilirsiniz.
            </p>
            <button onclick="toggleModal('addFaqModal')" class="inline-flex mt-5 px-4 py-2 bg-[#1F2937] hover:bg-[#111827] text-white text-xs font-bold rounded-lg transition-colors font-display">
                İlk Soruyu Ekle
            </button>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#E5E7EB] bg-slate-50/70">
                        <th class="px-6 py-4 text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display" style="width: 80px;">Sıra</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display">Soru / Cevap</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display" style="width: 120px;">Durum</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#6B7280] uppercase tracking-wider font-display text-right" style="width: 150px;">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @foreach($faqs as $faq)
                        <tr class="hover:bg-slate-50/40 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#4B5563] font-semibold">
                                #{{ $faq->sira }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="font-bold text-[#111827] text-sm font-display">{{ $faq->soru }}</div>
                                    <div class="text-xs text-[#6B7280] line-clamp-2">{{ $faq->cevap }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($faq->aktif)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wide font-display">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-gray-55 bg-gray-50 text-gray-500 border border-gray-200 uppercase tracking-wide font-display">
                                        Pasif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="editFaqModal({{ json_encode($faq) }})" class="p-2 text-gray-500 hover:text-[#C96A2B] hover:bg-slate-50 rounded-lg transition-all" title="Düzenle">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"></path>
                                        </svg>
                                    </button>
                                    
                                    <form action="{{ route('hekim.faqs.toggle', $faq->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 text-gray-500 hover:text-amber-600 hover:bg-slate-50 rounded-lg transition-all" title="{{ $faq->aktif ? 'Pasife Al' : 'Aktife Al' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9"></path>
                                            </svg>
                                        </button>
                                    </form>

                                    <form action="{{ route('hekim.faqs.destroy', $faq->id) }}" method="POST" class="inline" onsubmit="return confirm('Bu soruyu silmek istediğinize emin misiniz?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-500 hover:text-red-650 hover:text-red-600 hover:bg-slate-50 rounded-lg transition-all" title="Sil">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Modal: Yeni Soru Ekle -->
<div id="addFaqModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" onclick="handleModalBackdropClick(event, 'addFaqModal')">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="modal-content relative z-10 bg-white rounded-2xl shadow-xl w-full max-w-lg border border-[#E5E7EB]" onclick="event.stopPropagation()">
            <form action="{{ route('hekim.faqs.store') }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-base font-bold font-display text-[#111827]">Yeni Soru Ekle</h3>
                        <button type="button" onclick="closeModal('addFaqModal')" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Soru</label>
                            <input type="text" name="soru" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="Örn: Muayene ücretleriniz nedir?">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Cevap</label>
                            <textarea name="cevap" required rows="4" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]" placeholder="Sorunun ayrıntılı cevabını buraya yazın..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Sıra No (Görüntüleme Sırası)</label>
                            <input type="number" name="sira" value="0" min="0" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                    <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#C96A2B] text-white hover:bg-[#b05c24] transition-all">Kaydet</button>
                    <button type="button" onclick="closeModal('addFaqModal')" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-white border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-50 transition-all">Vazgeç</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Soru Düzenle -->
<div id="editFaqModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" onclick="handleModalBackdropClick(event, 'editFaqModal')">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="modal-content relative z-10 bg-white rounded-2xl shadow-xl w-full max-w-lg border border-[#E5E7EB]" onclick="event.stopPropagation()">
            <form id="editFaqForm" method="POST">
                @csrf
                <div class="p-6">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-base font-bold font-display text-[#111827]">Soru Düzenle</h3>
                        <button type="button" onclick="closeModal('editFaqModal')" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Soru</label>
                            <input type="text" name="soru" id="edit_soru" required class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Cevap</label>
                            <textarea name="cevap" id="edit_cevap" required rows="4" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#4B5563] uppercase tracking-wider mb-2">Sıra No (Görüntüleme Sırası)</label>
                            <input type="number" name="sira" id="edit_sira" min="0" class="w-full text-sm rounded-xl border-[#E5E7EB] focus:border-[#C96A2B] focus:ring focus:ring-[#C96A2B]/10 p-2.5 bg-[#FAFAFA]">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                    <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#C96A2B] text-white hover:bg-[#b05c24] transition-all">Güncelle</button>
                    <button type="button" onclick="closeModal('editFaqModal')" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-white border border-[#E5E7EB] text-[#4B5563] hover:bg-gray-50 transition-all">Vazgeç</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function handleModalBackdropClick(event, modalId) {
        if (event.target === document.getElementById(modalId) || event.target.classList.contains('bg-gray-500')) {
            closeModal(modalId);
        }
    }

    function editFaqModal(faq) {
        document.getElementById('editFaqForm').action = `/hekim/sss/${faq.id}/guncelle`;
        document.getElementById('edit_soru').value = faq.soru;
        document.getElementById('edit_cevap').value = faq.cevap;
        document.getElementById('edit_sira').value = faq.sira;
        
        toggleModal('editFaqModal');
    }
</script>
@endsection
