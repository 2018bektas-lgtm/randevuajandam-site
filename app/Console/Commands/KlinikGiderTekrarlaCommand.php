<?php

namespace App\Console\Commands;

use App\Models\KlinikGider;
use Illuminate\Console\Command;
use Carbon\Carbon;

class KlinikGiderTekrarlaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'klinik:gider-tekrarla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tekrarlı klinik giderlerini yeni ay için otomatik kopyalar.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $count = 0;

        // 1. Aylık tekrarlı giderler
        $recurrentExpenses = KlinikGider::where('tekrarli_mi', true)
            ->where('tekrar_periyodu', 'aylik')
            ->get();

        foreach ($recurrentExpenses as $gider) {
            // Orijinal kaydı kopyalarken onun yeni kopyalarını tekrar kopyalamamak için
            // sadece en son oluşturulmuş / orijinal olanı baz almalıyız, ancak double-run korumasıyla 
            // bu ay için o gider adına kayıt var mı diye bakarız.
            $exists = KlinikGider::where('klinik_id', $gider->klinik_id)
                ->where('baslik', $gider->baslik)
                ->whereMonth('tarih', $today->month)
                ->whereYear('tarih', $today->year)
                ->exists();

            if (!$exists) {
                KlinikGider::create([
                    'klinik_id' => $gider->klinik_id,
                    'kategori' => $gider->kategori,
                    'baslik' => $gider->baslik,
                    'tutar' => $gider->tutar,
                    'tarih' => $today->toDateString(),
                    'aciklama' => $gider->aciklama,
                    'tekrarli_mi' => true,
                    'tekrar_periyodu' => 'aylik',
                ]);
                $count++;
            }
        }

        // 2. Yıllık tekrarlı giderler
        $yearlyRecurrentExpenses = KlinikGider::where('tekrarli_mi', true)
            ->where('tekrar_periyodu', 'yillik')
            ->get();

        foreach ($yearlyRecurrentExpenses as $gider) {
            $giderTarih = Carbon::parse($gider->tarih);
            if ($giderTarih->month === $today->month) {
                $exists = KlinikGider::where('klinik_id', $gider->klinik_id)
                    ->where('baslik', $gider->baslik)
                    ->whereYear('tarih', $today->year)
                    ->exists();

                if (!$exists) {
                    KlinikGider::create([
                        'klinik_id' => $gider->klinik_id,
                        'kategori' => $gider->kategori,
                        'baslik' => $gider->baslik,
                        'tutar' => $gider->tutar,
                        'tarih' => $today->toDateString(),
                        'aciklama' => $gider->aciklama,
                        'tekrarli_mi' => true,
                        'tekrar_periyodu' => 'yillik',
                    ]);
                    $count++;
                }
            }
        }

        $this->info("{$count} adet tekrarlı klinik gideri oluşturuldu.");
    }
}
