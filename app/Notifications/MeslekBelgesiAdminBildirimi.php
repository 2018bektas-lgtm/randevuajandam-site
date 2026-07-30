<?php

namespace App\Notifications;

use App\Models\Doktor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Meslek belgesi yüklendiğinde yalnızca yapılandırılmış admin e-postasına gider.
 */
class MeslekBelgesiAdminBildirimi extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Doktor $doktor,
        /** kayit | yenile | mobil_kayit */
        public string $kaynak = 'kayit'
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $d = $this->doktor->fresh() ?? $this->doktor;
        $ad = (string) ($d->ad_soyad ?? 'Hekim');
        $eposta = (string) ($d->e_posta ?? '—');
        $tel = (string) ($d->telefon ?? '—');
        $kaynakLabel = match ($this->kaynak) {
            'yenile' => 'Red sonrası yeniden belge yükledi',
            'mobil_kayit' => 'Mobil uygulamadan kayıt oldu ve belge yükledi',
            default => 'Kayıt oldu ve meslek belgesi yükledi',
        };

        $kuyrukUrl = route('yonetim.doktorlar.meslek-kuyruk');

        return (new MailMessage)
            ->subject('Meslek belgesi kontrolü — '.$ad)
            ->greeting('Merhaba,')
            ->line('Sisteme yeni bir hekim başvurusu düştü. Lütfen meslek belgesini kontrol edin.')
            ->line('**Durum:** '.$kaynakLabel)
            ->line('**Hekim:** '.$ad)
            ->line('**E-posta:** '.$eposta)
            ->line('**Telefon:** '.$tel)
            ->line('**T.C.:** '.($d->tc_kimlik_no ? substr((string) $d->tc_kimlik_no, 0, 3).'********' : '—'))
            ->line('**Diploma / tescil no:** '.((string) ($d->diploma_no ?? '—')))
            ->action('Meslek belgesi kuyruğunu aç', $kuyrukUrl)
            ->line('Bu e-posta yalnızca meslek onayı sorumlusu admine gönderilir.');
    }

    /**
     * Yalnızca config’teki adrese mail atar (varsayılan: ozcetinbektas@gmail.com).
     */
    public static function notifyAdmin(Doktor $doktor, string $kaynak = 'kayit'): void
    {
        $email = trim((string) config('randevu.meslek_admin_email', 'ozcetinbektas@gmail.com'));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            NotificationFacade::route('mail', $email)
                ->notify(new self($doktor, $kaynak));
        } catch (\Throwable $e) {
            Log::warning('Meslek belgesi admin e-postası gönderilemedi', [
                'doktor_id' => $doktor->id,
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
