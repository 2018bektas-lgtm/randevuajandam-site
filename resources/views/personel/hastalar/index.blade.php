@extends('layouts.personel')

@section('baslik', 'Hasta Yönetimi - Personel Paneli')
@section('sayfa_baslik', 'Klinik Hasta Havuzu')

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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left 2 Columns: Patients List -->
        <div class="lg:col-span-2 space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <!-- Search Filter -->
                <form method="GET" action="{{ route('personel.hastalar.index') }}" class="mb-6 flex gap-3">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Hasta adı, e-posta veya telefon ile ara..." class="flex-1 bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-2.5 text-xs outline-none focus:border-[#C96A2B] focus:ring-1 focus:ring-[#C96A2B]">
                    <button type="submit" class="bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl transition-colors">
                        Filtrele
                    </button>
                    @if(!empty($q))
                        <a href="{{ route('personel.hastalar.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl transition-colors flex items-center justify-center">
                            Temizle
                        </a>
                    @endif
                </form>

                @if($hastalar->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                                    <th class="pb-3 font-display">Ad Soyad</th>
                                    <th class="pb-3 font-display">İletişim</th>
                                    <th class="pb-3 font-display">Kayıt Tarihi</th>
                                    <th class="pb-3 text-right font-display">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB]">
                                @foreach($hastalar as $hasta)
                                    <tr class="text-sm text-[#4B5563]">
                                        <td class="py-3.5 font-semibold text-[#111827]">{{ $hasta->ad_soyad }}</td>
                                        <td class="py-3.5 text-xs">
                                            <div>{{ $hasta->telefon }}</div>
                                            <div class="text-[#9CA3AF] mt-0.5">{{ $hasta->e_posta }}</div>
                                        </td>
                                        <td class="py-3.5 text-xs text-[#6B7280]">
                                            @if($hasta->pivot && $hasta->pivot->kayit_tarihi)
                                                {{ \Carbon\Carbon::parse($hasta->pivot->kayit_tarihi)->format('d.m.Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-3.5 text-right">
                                            <a href="{{ route('personel.hastalar.detay', $hasta->id) }}" class="px-3 py-1.5 bg-[#FFF7ED] hover:bg-[#FFEADB] text-[#C96A2B] text-xs font-bold rounded-lg transition-colors">
                                                Geçmiş ve Detay
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $hastalar->appends(request()->query())->links() }}
                    </div>
                @else
                    <p class="text-xs text-[#6B7280] py-6 text-center">Klinik ortak hasta havuzunda kayıtlı hasta bulunmamaktadır.</p>
                @endif
            </div>
        </div>

        <!-- Right Column: Add Patient Form -->
        <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-base font-bold font-display text-[#111827] mb-5">Yeni Hasta Kaydet</h3>
                
                <form action="{{ route('personel.hastalar.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="ad_soyad" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Ad Soyad</label>
                        <input id="ad_soyad" name="ad_soyad" type="text" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-xs focus:border-[#C96A2B] outline-none">
                    </div>

                    <div>
                        <label for="e_posta" class="block text-xs font-semibold text-[#4B5563] mb-1.5">E-posta</label>
                        <input id="e_posta" name="e_posta" type="email" required class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-xs focus:border-[#C96A2B] outline-none">
                    </div>

                    <div>
                        <label for="telefon" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Telefon</label>
                        <input id="telefon" name="telefon" type="text" required placeholder="05551234567" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-xs focus:border-[#C96A2B] outline-none">
                    </div>

                    <div>
                        <label for="sifre" class="block text-xs font-semibold text-[#4B5563] mb-1.5">Hasta Şifresi (İsteğe Bağlı)</label>
                        <input id="sifre" name="sifre" type="password" placeholder="Belirtilmezse varsayılan: 123456" class="w-full bg-[#FAFAFA] border border-[#E5E7EB] rounded-xl px-4 py-3 text-xs focus:border-[#C96A2B] outline-none">
                    </div>

                    <button type="submit" class="w-full bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl transition-all duration-200">
                        Hasta Kaydet
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
