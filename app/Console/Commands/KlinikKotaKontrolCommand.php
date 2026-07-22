<?php

namespace App\Console\Commands;

use App\Models\Klinik;
use App\Notifications\KlinikKotaDolduNotification;
use Illuminate\Console\Command;

class KlinikKotaKontrolCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'klinik:kota-kontrol {--notify : Kota dolmuş klinik sahiplerine bildirim gönder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Klinik hekim kotalarını denetler ve kota dolum durumlarını raporlar';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Klinik kota denetimi başlatılıyor...');

        $klinikler = Klinik::query()
            ->where('aktif_mi', true)
            ->with(['sahip', 'doktorlar'])
            ->get();

        $rows = [];
        $dolanKlinikSayisi = 0;
        $asildiSayisi = 0;

        foreach ($klinikler as $klinik) {
            $dahil = $klinik->dahilDoktorLimiti();
            $ek = (int) $klinik->ek_doktor_koltuk_sayisi;
            $efektif = $klinik->efektifDoktorLimiti();
            $mevcut = $klinik->doktorlar()->count();
            $dolu = $klinik->doktorLimitiDolduMu();

            if ($dolu) {
                $dolanKlinikSayisi++;
                if ($mevcut > $efektif) {
                    $asildiSayisi++;
                }

                $status = $mevcut > $efektif ? 'AŞILDI' : 'DOLDU';

                $rows[] = [
                    'ID' => $klinik->id,
                    'Klinik' => $klinik->ad,
                    'Sahip' => $klinik->sahip?->ad_soyad ?? '-',
                    'Dahil' => $dahil,
                    'Ek Koltuk' => $ek,
                    'Efektif' => $efektif,
                    'Mevcut' => $mevcut,
                    'Durum' => $status,
                ];

                if ($this->option('notify') && $klinik->sahip) {
                    try {
                        $klinik->sahip->notify(new KlinikKotaDolduNotification($klinik));
                        $this->line(" -> Bildirim gönderildi: {$klinik->sahip->e_posta}");
                    } catch (\Throwable $e) {
                        $this->error(" -> Bildirim hatası ({$klinik->ad}): {$e->getMessage()}");
                    }
                }
            }
        }

        if ($rows !== []) {
            $this->table(['ID', 'Klinik', 'Sahip', 'Dahil', 'Ek Koltuk', 'Efektif', 'Mevcut', 'Durum'], $rows);
        }

        $this->info("Denetim tamamlandı. Toplam klinik: {$klinikler->count()}, Kota dolan: {$dolanKlinikSayisi}, Limit aşılan: {$asildiSayisi}.");

        return Command::SUCCESS;
    }
}
