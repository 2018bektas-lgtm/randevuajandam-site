@extends('klinik.layout')

@section('baslik', 'Doktor Yönetimi - ' . $klinik->ad)
@section('sayfa_baslik', 'Hekim Yönetimi')

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

    @if($doktorlar->count() > $klinik->efektifDoktorLimiti())
        <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                <div>
                    <p class="font-bold">Hekim kotanız aşıldı ({{ $doktorlar->count() }}/{{ $klinik->efektifDoktorLimiti() }})</p>
                    <p class="text-xs mt-1">Yeni hekim daveti gönderemezsiniz. Ek hekim koltuğu satın alabilir, paketinizi yükseltebilir veya hekim sayısını düşürebilirsiniz.</p>
                </div>
            </div>
        </div>
    @endif
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left 2 Columns: Hekim Listesi -->
        <div class="lg:col-span-2 space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-bold font-display text-[#111827]">Klinik Hekimleri</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FFF7ED] text-[#C96A2B]">
                            {{ $doktorlar->count() }} / {{ $klinik->efektifDoktorLimiti() }} Hekim
                        </span>
                    </div>
                    <a href="{{ route('hekim.klinik.doktorlar.calisma-saatleri') }}" class="px-3 py-1.5 bg-gray-50 border border-gray-200 text-[#4B5563] text-xs font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                        ⏰ Çalışma Saatleri Tablosu
                    </a>
                </div>

                <div class="space-y-4">
                    @foreach($doktorlar as $doktorItem)
                        @php
                            $kisaAd = '';
                            if ($doktorItem->ad_soyad) {
                                $words = explode(' ', $doktorItem->ad_soyad);
                                $kisaAd = mb_strtoupper(mb_substr($words[0], 0, 1));
                                if (count($words) > 1) {
                                    $kisaAd .= mb_strtoupper(mb_substr(end($words), 0, 1));
                                }
                            } else {
                                $kisaAd = 'HE';
                            }
                        @endphp
                        <div class="p-5 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                @if($doktorItem->profil_resmi)
                                    <img src="{{ asset($doktorItem->profil_resmi) }}" alt="{{ $doktorItem->ad_soyad }}" class="w-12 h-12 rounded-full object-cover border border-[#E7B58A]/30 shrink-0">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-[#FFF7ED] flex items-center justify-center border border-[#E7B58A]/30 text-[#C96A2B] text-sm font-bold font-display shrink-0">
                                        {{ $kisaAd }}
                                    </div>
                                @endif
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-base font-bold text-[#111827]">{{ $doktorItem->unvan ? $doktorItem->unvan . ' ' : '' }}{{ $doktorItem->ad_soyad }}</h4>
                                        @if($doktorItem->id === $klinik->sahip_doktor_id)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-[#FFF7ED] text-[9px] font-extrabold text-[#C96A2B] uppercase tracking-wider font-display border border-[#E7B58A]/30">Klinik Sahibi</span>
                                        @elseif($doktorItem->klinik_rolu === 'ortak')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-orange-100 text-[9px] font-extrabold text-orange-700 uppercase tracking-wider font-display border border-orange-200">Klinik Ortağı</span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-gray-100 text-[9px] font-extrabold text-[#6B7280] uppercase tracking-wider font-display">Hekim</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-[#6B7280] mt-0.5">
                                        {{ $doktorItem->uzmanlik_alani ?: 'Branş Belirtilmemiş' }}
                                    </p>
                                    <p class="text-[11px] text-[#9CA3AF] mt-1">
                                        Katılım Tarihi: {{ $doktorItem->klinik_katilma_tarihi ? ($doktorItem->klinik_katilma_tarihi instanceof \DateTime ? $doktorItem->klinik_katilma_tarihi->format('d.m.Y') : \Carbon\Carbon::parse($doktorItem->klinik_katilma_tarihi)->format('d.m.Y')) : '-' }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('hekim.klinik.doktorlar.detay', $doktorItem->id) }}" class="px-3 py-2 bg-gray-50 hover:bg-gray-100 border border-[#E5E7EB] text-[#4B5563] text-xs font-semibold rounded-xl transition-colors">
                                    Detay & Performans
                                </a>
                                <a href="{{ route('hekim.klinik.doktorlar.duzenle', $doktorItem->id) }}" class="px-3 py-2 bg-orange-50 hover:bg-orange-100 border border-orange-150 text-orange-700 text-xs font-semibold rounded-xl transition-colors">
                                    Klinik Ayarları
                                </a>
                                @if($doktorItem->id !== $klinik->sahip_doktor_id)
                                    <form action="{{ route('hekim.klinik.doktorlar.durum-toggle', $doktorItem->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-2 {{ $doktorItem->klinik_aktif_mi ? 'bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100' }} border text-xs font-semibold rounded-xl transition-colors">
                                            {{ $doktorItem->klinik_aktif_mi ? 'Pasifleştir' : 'Aktifleştir' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('hekim.klinik.doktorlar.cikar', $doktorItem->id) }}" method="POST" class="inline" onsubmit="return confirm('Bu hekimi klinikten çıkarmak istediğinize emin misiniz? Hekimin bireysel statüye geri dönmesi gerekecektir.');">
                                        @csrf
                                        <button type="submit" class="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-600 font-semibold text-xs rounded-xl border border-red-200 transition-colors">
                                            Klinikten Çıkar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Bekleyen Davetiyeler -->
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-4">Bekleyen Davetiyeler</h3>

                @if($davetiyeler->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                                    <th class="pb-3 font-display">E-posta</th>
                                    <th class="pb-3 font-display">Tarih</th>
                                    <th class="pb-3 font-display">Son Kullanma</th>
                                    <th class="pb-3 font-display">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB]">
                                @foreach($davetiyeler as $davetiye)
                                    <tr class="text-sm text-[#4B5563]">
                                        <td class="py-3.5 font-semibold text-[#111827]">{{ $davetiye->davet_edilen_eposta }}</td>
                                        <td class="py-3.5 text-xs">{{ $davetiye->created_at->format('d.m.Y H:i') }}</td>
                                        <td class="py-3.5 text-xs text-amber-600">{{ $davetiye->son_kullanma_tarihi->format('d.m.Y') }}</td>
                                        <td class="py-3.5">
                                            <form action="{{ route('hekim.klinik.davetiye.iptal', $davetiye->id) }}" method="POST" onsubmit="return confirm('Bu davetiyeyi iptal etmek istiyor musunuz?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-xs">İptal Et</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-xs text-[#6B7280] py-4 text-center">Bekleyen aktif davetiye bulunmamaktadır.</p>
                @endif
            </div>
        </div>

        <!-- Right Column: Davet Gönder Formu -->
        <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-2">Hekim Davet Et</h3>
                <p class="text-xs text-[#6B7280] mb-5 leading-relaxed">
                    Sisteme kayıtlı olan veya olmayan hekimleri e-posta adresleri üzerinden kliniğinize davet edebilirsiniz. Daveti kabul ettiklerinde klinik üyeniz olurlar.
                </p>

                <form action="{{ route('hekim.klinik.doktorlar.davet') }}" method="POST" onsubmit="return submitDavetForm(this)">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="e_posta" class="block text-xs font-semibold text-[#4B5563] mb-1.5">E-posta Adresi</label>
                            <input type="email" name="e_posta" id="e_posta" required placeholder="ornek@hekim.com" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-sm focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/10 outline-none">
                        </div>

                        <button type="submit" class="w-full bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl transition-all duration-200 hover:scale-[1.01]">
                            Davet Gönder
                        </button>
                    </div>
                </form>
            </div>

            <!-- Limit Info -->
            <div class="p-6 rounded-2xl bg-[#FFF7ED]/50 border border-[#E7B58A]/30">
                <h4 class="text-xs font-bold text-[#C96A2B] uppercase tracking-wider font-display mb-3">Hekim Kotası</h4>
                <div class="space-y-2 mb-4">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[#4B5563]">Paket dahil</span>
                        <span class="font-bold text-[#111827]">{{ $klinik->dahilDoktorLimiti() }} hekim</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[#4B5563]">Ek koltuk</span>
                        <span class="font-bold text-[#111827]">{{ (int) $klinik->ek_doktor_koltuk_sayisi }} hekim</span>
                    </div>
                    <div class="flex items-center justify-between text-xs pt-2 border-t border-[#E7B58A]/30">
                        <span class="text-[#4B5563] font-semibold">Efektif limit</span>
                        <span class="font-bold text-[#C96A2B]">{{ $klinik->efektifDoktorLimiti() }} hekim</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[#4B5563]">Kullanılan</span>
                        <span class="font-bold text-[#111827]">{{ $doktorlar->count() }} hekim</span>
                    </div>
                </div>

                @if($klinik->doktorLimitiDolduMu())
                    <a href="{{ route('hekim.klinik.ek-koltuk') }}"
                       class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 hover:scale-[1.01] mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Ek Hekim Koltuğu Al
                    </a>
                @endif

                <ul class="space-y-2.5 text-xs text-[#4B5563]">
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#C96A2B] mt-1.5"></span>
                        <span>Klinik sahibi olan siz de bu limite dahilsiniz.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#C96A2B] mt-1.5"></span>
                        <span>Limitinizi artırmak için ek koltuk alabilir veya paketinizi yükseltebilirsiniz.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('extra_js')
    <script>
        function submitDavetForm(form) {
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btn.classList.remove('hover:scale-[1.01]');
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="animation: spin 1s linear infinite; vertical-align: middle; width: 16px; height: 16px;">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity: 0.25;"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" style="opacity: 0.75;"></path>
                </svg>
                Davet Gönderiliyor...
            `;
            return true;
        }
    </script>
@endsection
