@extends('hekim.layout')

@section('baslik', 'Hasta Kayıtları - Hekim Paneli')
@section('sayfa_baslik', 'Kayıtlı Hastalarım')

@section('icerik')
    <!-- Patients Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-[0_4px_24px_rgba(31,41,55,0.04)] overflow-hidden">
        <div class="p-6 border-b border-[#E5E7EB] flex items-center justify-between gap-3 flex-wrap">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display">Sistemde Kayıtlı Hasta Listeniz</h3>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs text-[#6B7280] font-medium">Toplam {{ $hastalar->total() }} hasta</span>
                @if(!empty($canExport))
                    <button type="button" onclick="tobuYukleAc()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-white bg-[#C96A2B] hover:bg-[#b05a22] rounded-lg transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5m0 0l5 5m-5-5v12"/></svg>
                        Toplu Yükle
                    </button>
                    <a href="{{ route('hekim.randevu.hastalar.export') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-white bg-[#1F2937] hover:bg-black rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M17 10l-5 5m0 0l-5-5m5 5V3"/></svg>
                        Excel İndir
                    </a>
                @else
                    <a href="{{ route('frontend.hekim.paket_sec', ['degistir' => 1]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-[#92400e] bg-[#FFF7ED] border border-[#FED7AA] rounded-lg">
                        🔒 Toplu yükle / dışa aktar (paket)
                    </a>
                @endif
            </div>
        </div>

        @if($hastalar->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-16 h-16 text-[#9CA3AF] mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A11.386 11.386 0 0110.089 21c-2.243 0-4.32-.647-6.07-1.758v-.19a6 6 0 0111.411-2.911M15 12a3 3 0 11-6 0 3 3 0 016 0zm6.375-1.5a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-8.25-3a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                </svg>
                <h4 class="text-sm font-bold text-[#111827] font-display">Henüz kayıtlı hastanız bulunmuyor</h4>
                <p class="text-xs text-[#6B7280] mt-1">Hastalarınız hekim profilinizden online randevu aldığında burada listelenecektir.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-[#E5E7EB] text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">
                            <th class="px-6 py-4">Hasta Adı Soyadı</th>
                            <th class="px-6 py-4">Telefon Numarası</th>
                            <th class="px-6 py-4">E-Posta Adresi</th>
                            <th class="px-6 py-4">Sizden Aldığı Randevu Sayısı</th>
                            <th class="px-6 py-4">Kayıt Durumu</th>
                            <th class="px-6 py-4">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] text-xs text-[#4B5563]">
                        @foreach($hastalar as $hasta)
                            <tr class="hover:bg-[#FAFAFA]/75 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[#111827] font-display">
                                        {{ $hasta->ad }} {{ $hasta->soyad }}
                                    </div>
                                    <div class="text-[10px] text-[#6B7280] mt-0.5">Üye No: #{{ $hasta->id }}</div>
                                </td>
                                <td class="px-6 py-4 font-medium">
                                    {{ $hasta->telefon }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $hasta->e_posta }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center justify-center px-3 py-1 text-xs font-bold text-[#C96A2B] bg-[#FFF7ED] rounded-full border border-[#E7B58A]/30 font-display">
                                        {{ $hasta->randevular_count }} Randevu
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($hasta->aktif_mi)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif Üye
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-50 text-slate-500 border border-slate-200">
                                            Pasif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @if(!empty($canTedavi))
                                            <a href="{{ route('hekim.randevu.hastalar.tedavi-gecmisi', $hasta->id) }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold text-[#1F2937] bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                                                Tedavi geçmişi
                                            </a>
                                        @endif
                                        <a href="{{ route('hekim.finans.hasta-hesap', $hasta->id) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-white bg-[#C96A2B] hover:bg-[#b05a22] rounded-lg transition-colors">
                                            Cari Hesap
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($hastalar->hasPages())
                <div class="p-6 border-t border-[#E5E7EB]">
                    {{ $hastalar->links() }}
                </div>
            @endif
        @endif
    </div>

    @if(!empty($canExport))
        {{-- Toplu Yükle Modal --}}
        <div id="topluYukleModal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
            <div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-2xl max-w-2xl w-full overflow-hidden flex flex-col max-h-[92vh]">
                <div class="p-5 sm:p-6 border-b border-[#E5E7EB] flex items-start justify-between gap-3 shrink-0">
                    <div>
                        <h3 class="text-lg font-bold font-display text-[#111827]">Excel ile Toplu Hasta Yükleme</h3>
                        <p class="text-xs text-[#6B7280] mt-1">CSV formatında hasta listenizi yükleyin. Şablonu indirip düzenleyerek başlayın.</p>
                    </div>
                    <button type="button" onclick="topluYukleKapat()" class="text-[#6B7280] hover:text-[#111827] transition p-1">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 sm:p-6 overflow-y-auto flex-1 space-y-5">
                    {{-- Adım 1: Örnek şablon indir --}}
                    <div>
                        <div class="text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display mb-2">Adım 1 — Örnek Şablonu İndir</div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <a href="{{ route('hekim.randevu.hastalar.sablon', ['tip' => 'bos']) }}"
                               class="flex flex-col items-start gap-1 p-3 rounded-xl border border-[#E5E7EB] hover:border-[#C96A2B] hover:bg-[#FFF7ED]/40 transition group">
                                <span class="text-[10px] font-bold text-[#C96A2B] uppercase tracking-wider">Boş Şablon</span>
                                <span class="text-[11px] text-[#111827] font-semibold">1 örnek satır</span>
                                <span class="text-[10px] text-[#6B7280]">Sıfırdan başlamak istiyorsanız</span>
                            </a>
                            <a href="{{ route('hekim.randevu.hastalar.sablon', ['tip' => 'az']) }}"
                               class="flex flex-col items-start gap-1 p-3 rounded-xl border border-[#E5E7EB] hover:border-[#C96A2B] hover:bg-[#FFF7ED]/40 transition">
                                <span class="text-[10px] font-bold text-[#C96A2B] uppercase tracking-wider">Az Örnekli</span>
                                <span class="text-[11px] text-[#111827] font-semibold">3 örnek satır</span>
                                <span class="text-[10px] text-[#6B7280]">Küçük klinikler için</span>
                            </a>
                            <a href="{{ route('hekim.randevu.hastalar.sablon', ['tip' => 'dolu']) }}"
                               class="flex flex-col items-start gap-1 p-3 rounded-xl border border-[#E5E7EB] hover:border-[#C96A2B] hover:bg-[#FFF7ED]/40 transition">
                                <span class="text-[10px] font-bold text-[#C96A2B] uppercase tracking-wider">Dolu Örnek</span>
                                <span class="text-[11px] text-[#111827] font-semibold">8 örnek satır</span>
                                <span class="text-[10px] text-[#6B7280]">Format görmek isteyenler</span>
                            </a>
                        </div>
                        <p class="text-[10px] text-[#6B7280] mt-2 leading-relaxed">
                            <strong class="text-[#111827]">Sütunlar:</strong> ad, soyad, telefon (zorunlu) — e_posta, notlar (opsiyonel). Excel'de düzenleyip <strong>"CSV UTF-8 (Virgülle ayrılmış)"</strong> olarak kaydedin.
                        </p>
                    </div>

                    {{-- Adım 2: Kurallar --}}
                    <div class="p-3 rounded-xl bg-amber-50/60 border border-amber-100 text-[11px] text-amber-900 leading-relaxed">
                        <strong>Kurallar:</strong>
                        <ul class="list-disc pl-4 mt-1 space-y-0.5">
                            <li>Tek dosyada en fazla 1.000 satır</li>
                            <li>Aynı e-posta veya telefondaki hasta varsa yeni yaratılmaz, havuzunuza eklenir</li>
                            <li>Telefon: 10-13 rakam (0532... veya +90532... kabul edilir)</li>
                            <li>Hatalı satırlar atlanır, sonuç raporunda gösterilir</li>
                        </ul>
                    </div>

                    {{-- Adım 3: Yükleme formu --}}
                    <form id="topluYukleForm" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <div class="text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display mb-2">Adım 2 — CSV Dosyanızı Yükleyin</div>
                            <label class="flex items-center justify-center w-full px-4 py-8 rounded-xl border-2 border-dashed border-[#E5E7EB] hover:border-[#C96A2B] bg-slate-50/50 hover:bg-[#FFF7ED]/40 transition cursor-pointer">
                                <input type="file" name="dosya" id="dosyaInput" accept=".csv,.txt" class="hidden" onchange="dosyaSecildi(event)">
                                <div class="text-center">
                                    <svg class="w-10 h-10 text-[#9CA3AF] mx-auto mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <div class="text-xs font-semibold text-[#111827]" id="dosyaAd">CSV dosyanızı seçin veya sürükleyin</div>
                                    <div class="text-[10px] text-[#6B7280] mt-1">Maksimum 5MB</div>
                                </div>
                            </label>
                        </div>
                    </form>

                    {{-- Sonuç alanı --}}
                    <div id="yuklemeSonuc" class="hidden"></div>
                </div>

                <div class="p-4 sm:p-5 border-t border-[#E5E7EB] flex items-center justify-end gap-2 shrink-0">
                    <button type="button" onclick="topluYukleKapat()"
                            class="px-4 py-2 rounded-xl border border-[#E5E7EB] text-[#6B7280] hover:bg-slate-50 text-xs font-bold transition">İptal</button>
                    <button type="button" id="yukleButonu" onclick="topluYukleGonder()" disabled
                            class="px-5 py-2 rounded-xl bg-[#C96A2B] hover:bg-[#b05a22] disabled:bg-slate-300 disabled:cursor-not-allowed text-white text-xs font-bold transition">
                        Yükle
                    </button>
                </div>
            </div>
        </div>

        <script>
        function tobuYukleAc()   { document.getElementById('topluYukleModal').classList.remove('hidden'); document.getElementById('topluYukleModal').classList.add('flex'); }
        function topluYukleKapat() {
            const m = document.getElementById('topluYukleModal');
            m.classList.add('hidden'); m.classList.remove('flex');
            document.getElementById('topluYukleForm').reset();
            document.getElementById('dosyaAd').textContent = 'CSV dosyanızı seçin veya sürükleyin';
            document.getElementById('yuklemeSonuc').classList.add('hidden');
            document.getElementById('yukleButonu').disabled = true;
        }
        function dosyaSecildi(e) {
            const f = e.target.files[0];
            document.getElementById('dosyaAd').textContent = f ? f.name : 'CSV dosyanızı seçin veya sürükleyin';
            document.getElementById('yukleButonu').disabled = !f;
        }
        async function topluYukleGonder() {
            const btn = document.getElementById('yukleButonu');
            const sonucDiv = document.getElementById('yuklemeSonuc');
            btn.disabled = true; btn.textContent = 'Yükleniyor...';

            const form = document.getElementById('topluYukleForm');
            const data = new FormData(form);

            try {
                const r = await fetch(@json(route('hekim.randevu.hastalar.import')), {
                    method: 'POST',
                    body: data,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const j = await r.json();
                if (!r.ok || !j.success) {
                    sonucDiv.className = 'p-3 rounded-xl bg-red-50 border border-red-200 text-[11px] text-red-800';
                    sonucDiv.textContent = j.message || 'Yükleme başarısız.';
                    sonucDiv.classList.remove('hidden');
                } else {
                    const s = j.sonuc || {};
                    let html = `<div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-[11px] text-emerald-900">
                        <strong>${j.message}</strong>
                        <div class="mt-1">Toplam: ${s.toplam} | Yeni: ${s.eklendi} | Mevcut: ${s.guncellendi} | Atlanan: ${s.atlanan}</div>
                    </div>`;
                    if (s.hatalar && s.hatalar.length) {
                        html += '<div class="mt-2 max-h-40 overflow-y-auto"><div class="text-[10px] font-bold text-red-800 mb-1">Hatalı Satırlar:</div>';
                        for (const h of s.hatalar.slice(0, 20)) {
                            html += `<div class="text-[10px] text-red-700 border-l-2 border-red-200 pl-2 py-0.5"><b>Satır ${h.satir}:</b> ${h.mesaj}</div>`;
                        }
                        html += '</div>';
                    }
                    sonucDiv.innerHTML = html;
                    sonucDiv.classList.remove('hidden');
                    setTimeout(() => { window.location.reload(); }, 2500);
                }
            } catch (err) {
                sonucDiv.className = 'p-3 rounded-xl bg-red-50 border border-red-200 text-[11px] text-red-800';
                sonucDiv.textContent = 'Sunucuya ulaşılamadı.';
                sonucDiv.classList.remove('hidden');
            }
            btn.disabled = false; btn.textContent = 'Yükle';
        }
        </script>
    @endif

@endsection
