@extends('hekim.layout')

@section('baslik', 'Randevu Talepleri - Hekim Paneli')
@section('sayfa_baslik', 'Onay Bekleyen Randevu Talepleri')

@section('icerik')
    <!-- Requests Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-[0_4px_24px_rgba(31,41,55,0.04)] overflow-hidden">
        <div class="p-6 border-b border-[#E5E7EB] flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Onay Bekleyen Randevular</h3>
            <span class="text-xs text-[#C96A2B] font-bold font-display bg-[#FFF7ED] px-3 py-1 rounded-full border border-[#E7B58A]/30">
                {{ $talepler->total() }} Bekleyen Talep
            </span>
        </div>

        @if($talepler->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-[#111827] font-display">Harika! Onay bekleyen hiçbir randevu talebi bulunmuyor.</h4>
                <p class="text-xs text-[#6B7280] mt-1">Hastalarınızdan yeni talepler geldiğinde bu alanda listelenecektir.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                            <th class="px-6 py-4">Hasta Bilgileri</th>
                            <th class="px-6 py-4">Talep Edilen Hizmet</th>
                            <th class="px-6 py-4">İstediği Gün & Saat</th>
                            <th class="px-6 py-4">Görüşme</th>
                            <th class="px-6 py-4">Hasta Notu</th>
                            <th class="px-6 py-4 text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] text-xs text-[#4B5563]">
                        @foreach($talepler as $talep)
                            <tr class="hover:bg-[#FAFAFA]/75 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[#111827] font-display">{{ $talep->ad }} {{ $talep->soyad }}</div>
                                    <div class="text-[10px] text-[#6B7280] mt-0.5">{{ $talep->telefon }}</div>
                                    <div class="text-[10px] text-[#6B7280]">{{ $talep->e_posta }}</div>
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
                                <td class="px-6 py-4">
                                    @if(($talep->gorusme_tipi ?? 'yuz_yuze') === 'online')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-sky-50 text-sky-700 border border-sky-200 font-display">
                                            📹 Online
                                        </span>
                                        <p class="text-[10px] text-sky-800/80 mt-1 leading-snug">Onay sonrası platform odası oluşur</p>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-50 text-slate-600 border border-slate-200 font-display">
                                            Yüz yüze
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 max-w-[200px]">
                                    @if($talep->not)
                                        <p class="leading-relaxed text-[#6B7280] italic" title="{{ $talep->not }}">{{ $talep->not }}</p>
                                    @else
                                        <span class="text-slate-300">Belirtilmedi</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Approve Action Form -->
                                        <form action="{{ route('hekim.randevu.durum-guncelle', $talep->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="durum" value="onaylandi">
                                            <button type="submit" onclick="onayModalAc(event, this.form, 'Bu randevu talebini onaylamak istediğinize emin misiniz?')"
                                                    class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs tracking-wider transition-all duration-200 cursor-pointer font-display">
                                                Onayla
                                            </button>
                                        </form>

                                        <!-- Cancel/Reject Action Button (Requires Note Modal) -->
                                        <button type="button" onclick="talepReddetModalAc({{ json_encode($talep) }})"
                                                class="px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50 text-red-600 font-bold text-xs tracking-wider transition-all duration-200 cursor-pointer font-display">
                                            Reddet / İptal
                                        </button>
                                    </div>
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

    <!-- Reject/Cancel Request Modal -->
    <div id="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-300">
        <div id="rejectModalContainer" class="bg-white rounded-2xl border border-[#E5E7EB] shadow-2xl max-w-sm w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
            <div class="px-6 py-4 bg-slate-50 border-b border-[#E5E7EB] flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display text-red-600">Randevu Talebini Reddet</h3>
                <button onclick="talepReddetModalKapat()" class="text-[#6B7280] hover:text-[#111827]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="rejectForm" action="" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="durum" value="iptal">

                <div class="p-4 rounded-xl bg-red-50/50 border border-red-100 text-xs text-red-700">
                    <p><strong>Hasta:</strong> <span id="rHastaAd" class="font-bold"></span></p>
                    <p class="mt-1">Randevu talebi iptal edilecek ve hastaya bilgilendirme yapılacaktır. Lütfen iptal nedenini aşağıdaki alana yazınız.</p>
                </div>

                <div class="space-y-1">
                    <label for="rHekimNotu" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Ret / İptal Gerekçesi</label>
                    <textarea id="rHekimNotu" name="hekim_notu" rows="3" required placeholder="İptal edilme gerekçesini belirtin (örn: Hekimimiz ameliyattadır)..." 
                              class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] placeholder-gray-400 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 text-xs transition-all resize-none"></textarea>
                </div>

                <div class="pt-4 flex items-center gap-3 border-t border-slate-100">
                    <button type="button" onclick="talepReddetModalKapat()" class="flex-1 py-2.5 rounded-xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#6B7280] font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display">
                        Geri Dön
                    </button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer font-display">
                        Talebi Reddet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function talepReddetModalAc(talep) {
            const modal = document.getElementById('rejectModal');
            const container = document.getElementById('rejectModalContainer');
            const form = document.getElementById('rejectForm');

            // Populate fields
            document.getElementById('rHastaAd').innerText = `${talep.ad} ${talep.soyad}`;
            document.getElementById('rHekimNotu').value = '';

            // Update action route dynamically
            let route = "{{ route('hekim.randevu.durum-guncelle', ':id') }}";
            form.action = route.replace(':id', talep.id);

            // Open Modal
            modal.classList.remove('hidden');
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        function talepReddetModalKapat() {
            const modal = document.getElementById('rejectModal');
            const container = document.getElementById('rejectModalContainer');

            if(modal && container) {
                container.classList.remove('scale-100', 'opacity-100');
                container.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        }

        // Close on overlay click
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('rejectModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        talepReddetModalKapat();
                    }
                });
            }
        });
    </script>
@endsection
