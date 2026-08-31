<?php

namespace App\Console\Commands;

use App\Models\Randevu;
use App\Models\Yorum;
use App\Models\YorumDaveti;
use App\Notifications\YorumDavetBildirimi;
use App\Support\PaketYetki;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Randevu saatinin 2 saat sonrasında hastaya girişsiz yorum daveti gönderir.
 *
 * Neden duruma değil ZAMANA bakıyor:
 * Davet daha önce yalnızca hekim randevuyu "tamamlandı" işaretlediğinde
 * gönderiliyordu. Sahada bu işaretleme neredeyse hiç yapılmıyor (canlıda
 * saati geçmiş 7 randevunun 4'ü hâlâ "onaylandı" durumundaydı), dolayısıyla
 * davet pratikte hiç çıkmıyor ve yorum tablosu boş kalıyordu. Artık saati
 * geçmiş ONAYLI randevular da gerçekleşmiş sayılır.
 *
 * İptal ve beklemede olanlar hariçtir; hekim gelmeyen hastanın randevusunu
 * iptale çekerse davet gitmez.
 */
class YorumDavetiGonder extends Command
{
    protected $signature = 'yorum:davet-gonder
                            {--saat=2 : Randevu saatinden kaç saat sonra gönderilsin}
                            {--gun-limit=14 : Kaç günden eski randevular atlansın}
                            {--kuru-calistir : Kayıt/gönderim yapmadan yalnızca listeler}';

    protected $description = 'Saati geçmiş randevular için hastaya girişsiz yorum daveti gönderir';

    public function handle(): int
    {
        $saat = max(0, (int) $this->option('saat'));
        $gunLimit = max(1, (int) $this->option('gun-limit'));
        $kuru = (bool) $this->option('kuru-calistir');

        // Üst sınır: özellik ilk açıldığında aylar öncesine ait randevulara
        // toplu davet gitmesin.
        $enEski = now()->subDays($gunLimit)->toDateString();
        $enYeni = now()->toDateString();

        $randevular = Randevu::query()
            ->whereIn('durum', ['onaylandi', 'tamamlandi'])
            ->whereBetween('tarih', [$enEski, $enYeni])
            ->whereDoesntHave('yorumDaveti')
            ->with(['hasta', 'doktor'])
            ->orderBy('tarih')
            ->get();

        $gonderilen = 0;
        $atlanan = 0;

        foreach ($randevular as $randevu) {
            $sebep = $this->atlamaSebebi($randevu, $saat);
            if ($sebep !== null) {
                $atlanan++;
                if ($this->output->isVerbose()) {
                    $this->line("  atlandı #{$randevu->id}: {$sebep}");
                }

                continue;
            }

            if ($kuru) {
                $this->line("  gönderilecek #{$randevu->id} → {$randevu->hasta->e_posta}");
                $gonderilen++;

                continue;
            }

            $davet = null;
            try {
                $token = YorumDaveti::tokenUret();
                $davet = YorumDaveti::create([
                    'randevu_id' => $randevu->id,
                    'hasta_id' => $randevu->hasta_id,
                    'doktor_id' => $randevu->doktor_id,
                    'token_hash' => YorumDaveti::tokenOzeti($token),
                    'gecerlilik_bitis' => now()->addDays(YorumDaveti::GECERLILIK_GUN),
                    'gonderildi_at' => now(),
                ]);

                $randevu->hasta->notify(new YorumDavetBildirimi($randevu, $token));
                $gonderilen++;
            } catch (\Throwable $e) {
                // Davet kaydı kalırsa randevu bir daha hiç denenmez
                // (sorgu davetin varlığına bakıyor), o yüzden geri alınır.
                // Not: bildirim kuyruğa atıldığı için SMTP arızası buraya
                // düşmez; buraya düşen hata yerel bir sorundur.
                $atlanan++;
                Log::error('Yorum daveti gönderilemedi (Randevu ID: '.$randevu->id.'): '.$e->getMessage());
                $davet?->delete();
            }
        }

        $this->info(($kuru ? '[kuru çalıştırma] ' : '')."Yorum daveti: {$gonderilen} gönderildi, {$atlanan} atlandı.");

        return self::SUCCESS;
    }

    /** Gönderilmeyecekse sebebi, gönderilecekse null. */
    private function atlamaSebebi(Randevu $randevu, int $saat): ?string
    {
        if (! $randevu->hasta || blank($randevu->hasta->e_posta)) {
            return 'hastanın e-postası yok';
        }

        if (! $randevu->doktor) {
            return 'hekim kaydı yok';
        }

        if (! PaketYetki::has($randevu->doktor, 'yorum_davet')) {
            return 'paket yorum davetini kapsamıyor';
        }

        if (blank($randevu->saat)) {
            return 'saat bilgisi yok';
        }

        $randevuZamani = Carbon::parse($randevu->tarih->toDateString().' '.$randevu->saat);
        if ($randevuZamani->copy()->addHours($saat)->isFuture()) {
            return 'randevu saati üzerinden '.$saat.' saat geçmedi';
        }

        // Hasta zaten yorum yaptıysa davet anlamsız
        $yorumVar = Yorum::query()
            ->where('hasta_id', $randevu->hasta_id)
            ->where('randevu_id', $randevu->id)
            ->exists();

        return $yorumVar ? 'zaten yorum yapılmış' : null;
    }
}
