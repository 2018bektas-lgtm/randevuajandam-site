<div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
    <h3 class="text-lg font-bold font-display text-[#111827] mb-2">Çalışma Saatleri</h3>
    <p class="text-xs text-[#6B7280] mb-6">Kliniğinizin genel çalışma günlerini ve saat aralıklarını buradan düzenleyebilirsiniz.</p>

    <div class="space-y-4">
        @php
            $gunlerTr = [
                'pazartesi' => 'Pazartesi',
                'sali' => 'Salı',
                'carsamba' => 'Çarşamba',
                'persembe' => 'Perşembe',
                'cuma' => 'Cuma',
                'cumartesi' => 'Cumartesi',
                'pazar' => 'Pazar'
            ];
        @endphp

        @foreach($gunlerTr as $key => $label)
            @php
                $saat = $calismaSaatleri[$key] ?? ['acilis' => '09:00', 'kapanis' => '18:00', 'kapali' => false];
                $isOpen = !($saat['kapali'] ?? false);
            @endphp
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border border-[#F5F5F4] hover:border-[#E5E7EB] transition-all bg-[#FAFAFA]/50 day-row" data-day="{{ $key }}">
                <!-- Day Label and Active Switch -->
                <div class="flex items-center gap-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="calisma_saatleri[{{ $key }}][aktif]" value="1" class="sr-only peer day-toggle" {{ $isOpen ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                    </label>
                    <span class="text-sm font-bold font-display text-[#111827] w-24">{{ $label }}</span>
                </div>

                <!-- Hours Selectors -->
                <div class="flex items-center gap-3 hours-container {{ $isOpen ? '' : 'opacity-40 pointer-events-none' }}">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-[#6B7280]">Açılış:</span>
                        <select name="calisma_saatleri[{{ $key }}][acilis]" class="bg-white border border-[#E5E7EB] rounded-lg px-2 py-1.5 text-xs font-semibold text-[#111827] focus:border-[#C96A2B] outline-none">
                            @for($h = 0; $h < 24; $h++)
                                @foreach(['00', '30'] as $m)
                                    @php $timeStr = sprintf('%02d:%s', $h, $m); @endphp
                                    <option value="{{ $timeStr }}" {{ $saat['acilis'] == $timeStr ? 'selected' : '' }}>{{ $timeStr }}</option>
                                @endforeach
                            @endfor
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs text-[#6B7280]">Kapanış:</span>
                        <select name="calisma_saatleri[{{ $key }}][kapanis]" class="bg-white border border-[#E5E7EB] rounded-lg px-2 py-1.5 text-xs font-semibold text-[#111827] focus:border-[#C96A2B] outline-none">
                            @for($h = 0; $h < 24; $h++)
                                @foreach(['00', '30'] as $m)
                                    @php $timeStr = sprintf('%02d:%s', $h, $m); @endphp
                                    <option value="{{ $timeStr }}" {{ $saat['kapanis'] == $timeStr ? 'selected' : '' }}>{{ $timeStr }}</option>
                                @endforeach
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.day-toggle').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const row = this.closest('.day-row');
                const container = row.querySelector('.hours-container');
                if (this.checked) {
                    container.classList.remove('opacity-40', 'pointer-events-none');
                } else {
                    container.classList.add('opacity-40', 'pointer-events-none');
                }
            });
        });
    });
</script>
