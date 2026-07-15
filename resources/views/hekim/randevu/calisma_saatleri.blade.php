@extends('hekim.layout')

@section('baslik', 'Çalışma Saatleri Planı - Hekim Paneli')
@section('sayfa_baslik', 'Çalışma Saatleri Planı')

@section('icerik')
    <div class="mb-6 p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display border-b border-slate-100 pb-3 mb-3">Çalışma Planınızı Düzenleyin</h3>
        <p class="text-xs text-[#6B7280] leading-relaxed">
            Haftalık çalışma günlerinizi ve saatlerinizi buradan planlayabilirsiniz. Aktif edilmeyen günlerde hastalarınız sistem üzerinden randevu talep edemezler. Öğle arası aktif edildiğinde ise belirlenen saat aralığı randevuya kapatılır.
        </p>
    </div>

    <form action="{{ route('hekim.randevu.calisma-saatleri.post') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="space-y-4">
            @foreach($calismaSaatleri as $cs)
                <div class="p-6 bg-white border border-[#E5E7EB] rounded-2xl shadow-[0_4px_24px_rgba(31,41,55,0.02)] hover:border-[#E7B58A]/30 transition-all duration-300">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        
                        <!-- Day Name and Enable/Disable Day Toggle Switch -->
                        <div class="flex items-center justify-between md:justify-start gap-4 min-w-[200px]">
                            <span class="text-sm font-bold font-display text-[#111827]">{{ $cs->gun_adi }}</span>
                            
                            <!-- iOS Style Toggle Switch -->
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="hidden" name="saatler[{{ $cs->id }}][gun]" value="{{ $cs->gun }}">
                                <input type="checkbox" name="saatler[{{ $cs->id }}][aktif_mi]" value="1" 
                                       {{ $cs->aktif_mi ? 'checked' : '' }} 
                                       class="sr-only peer" id="aktif_day_{{ $cs->id }}"
                                       onchange="toggleDayInputs({{ $cs->id }}, this.checked)">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                                <span class="ml-2.5 text-[11px] font-semibold text-[#6B7280] peer-checked:text-[#C96A2B]" id="label_day_{{ $cs->id }}">
                                    {{ $cs->aktif_mi ? 'Aktif' : 'Kapalı' }}
                                </span>
                            </label>
                        </div>

                        <!-- Shift Times and Lunch break controls (wrapped in a div for toggling) -->
                        <div id="day_inputs_{{ $cs->id }}" class="flex-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 {{ $cs->aktif_mi ? '' : 'opacity-40 pointer-events-none' }} transition-all duration-200">
                            
                            <!-- Shift start and end time -->
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider mb-1 font-display">Mesai Başlangıç</label>
                                    <input type="time" name="saatler[{{ $cs->id }}][mesai_baslangic]" 
                                           value="{{ substr($cs->mesai_baslangic, 0, 5) }}" required
                                           class="w-full px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-[#FAFAFA] text-xs focus:outline-none focus:border-[#C96A2B] transition-all">
                                </div>
                                <span class="mt-4 text-[#6B7280]">-</span>
                                <div class="flex-1">
                                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider mb-1 font-display">Mesai Bitiş</label>
                                    <input type="time" name="saatler[{{ $cs->id }}][mesai_bitis]" 
                                           value="{{ substr($cs->mesai_bitis, 0, 5) }}" required
                                           class="w-full px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-[#FAFAFA] text-xs focus:outline-none focus:border-[#C96A2B] transition-all">
                                </div>
                            </div>

                            <!-- Lunch Break Toggle Switch -->
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer select-none mt-4">
                                    <input type="checkbox" name="saatler[{{ $cs->id }}][ogle_arasi_aktif_mi]" value="1" 
                                           {{ $cs->ogle_arasi_aktif_mi ? 'checked' : '' }} 
                                           class="sr-only peer" id="aktif_lunch_{{ $cs->id }}"
                                           onchange="toggleLunchInputs({{ $cs->id }}, this.checked)">
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                                    <span class="ml-2 text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display peer-checked:text-[#C96A2B]">Öğle Molası</span>
                                </label>
                            </div>

                            <!-- Lunch Break Times -->
                            <div id="lunch_times_{{ $cs->id }}" class="flex items-center gap-2 {{ $cs->ogle_arasi_aktif_mi ? '' : 'opacity-40 pointer-events-none' }} transition-all duration-200">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider mb-1 font-display">Mola Başlangıç</label>
                                    <input type="time" name="saatler[{{ $cs->id }}][ogle_baslangic]" 
                                           value="{{ $cs->ogle_baslangic ? substr($cs->ogle_baslangic, 0, 5) : '12:00' }}"
                                           class="w-full px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-[#FAFAFA] text-xs focus:outline-none focus:border-[#C96A2B] transition-all">
                                </div>
                                <span class="mt-4 text-[#6B7280]">-</span>
                                <div class="flex-1">
                                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider mb-1 font-display">Mola Bitiş</label>
                                    <input type="time" name="saatler[{{ $cs->id }}][ogle_bitis]" 
                                           value="{{ $cs->ogle_bitis ? substr($cs->ogle_bitis, 0, 5) : '13:00' }}"
                                           class="w-full px-3 py-1.5 rounded-lg border border-[#E5E7EB] bg-[#FAFAFA] text-xs focus:outline-none focus:border-[#C96A2B] transition-all">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-end">
            <button type="submit" class="px-8 py-3.5 bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all font-display shadow-sm hover:shadow-md cursor-pointer">
                Değişiklikleri Kaydet
            </button>
        </div>
    </form>

    <script>
        function toggleDayInputs(id, isChecked) {
            const inputsDiv = document.getElementById(`day_inputs_${id}`);
            const labelSpan = document.getElementById(`label_day_${id}`);
            if (isChecked) {
                inputsDiv.classList.remove('opacity-40', 'pointer-events-none');
                labelSpan.innerText = 'Aktif';
            } else {
                inputsDiv.classList.add('opacity-40', 'pointer-events-none');
                labelSpan.innerText = 'Kapalı';
            }
        }

        function toggleLunchInputs(id, isChecked) {
            const lunchDiv = document.getElementById(`lunch_times_${id}`);
            if (isChecked) {
                lunchDiv.classList.remove('opacity-40', 'pointer-events-none');
            } else {
                lunchDiv.classList.add('opacity-40', 'pointer-events-none');
            }
        }
    </script>
@endsection
