<?php

namespace App\Notifications;

use App\Notifications\Concerns\NotifiesDoktorApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DoktorUyelikBitisBildirimi extends Notification implements ShouldQueue
{
    use NotifiesDoktorApp;
    use Queueable;

    public function __construct(
        public int $kalanGun,
        public bool $otomatikYenileme = false,
        public ?float $tahminiTutar = null,
        public string $periyotLabel = 'aylık'
    ) {}

    public function via(object $notifiable): array
    {
        return $this->doktorAppChannels(['mail']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ad = (string) ($notifiable->ad_soyad ?? 'Hekim');
        $bitis = $notifiable->uyelik_bitis?->format('d.m.Y') ?? '—';
        $tutarStr = $this->tahminiTutar !== null
            ? '₺'.number_format($this->tahminiTutar, 2, ',', '.')
            : null;

        if ($this->kalanGun <= 0) {
            if ($this->otomatikYenileme) {
                $mail = (new MailMessage)
                    ->subject('Üyeliğiniz bugün yenileniyor — Randevu Ajandam')
                    ->greeting('Sayın '.$ad.',')
                    ->line('Paket üyeliğinizin süresi **'.$bitis.'** itibarıyla doluyor / doldu.')
                    ->line('Kayıtlı kartınızdan **3D Secure olmadan** otomatik yenileme denemesi yapılacaktır'
                        .($tutarStr ? ' (yaklaşık **'.$tutarStr.'**, '.$this->periyotLabel.')' : '').'.');
            } else {
                $mail = (new MailMessage)
                    ->subject('Üyeliğiniz sona erdi — Randevu Ajandam')
                    ->greeting('Sayın '.$ad.',')
                    ->line('Paket üyeliğinizin süresi **'.$bitis.'** itibarıyla dolmuştur.')
                    ->line('Kesintisiz kullanım için lütfen paket seçip ödemeyi tamamlayın. Fiyatlara KDV dahildir.');
            }

            return $mail->action('Üyelik / paket', route('hekim.uyelik'));
        }

        if ($this->otomatikYenileme) {
            return (new MailMessage)
                ->subject($this->kalanGun.' gün sonra otomatik yenileme — Randevu Ajandam')
                ->greeting('Sayın '.$ad.',')
                ->line('Paket üyeliğiniz **'.$bitis.'** tarihinde sona erecek (**'.$this->kalanGun.' gün** kaldı).')
                ->line('Otomatik yenileme **açık**. Süre dolunca kayıtlı kartınızdan **3D’siz** olarak'
                    .($tutarStr ? ' yaklaşık **'.$tutarStr.'** ('.$this->periyotLabel.' paket)' : ' paket ücreti')
                    .' tahsil edilecektir.')
                ->line('İstemiyorsanız dönem bitmeden panelden aboneliği iptal ederek yenilemeyi kapatabilirsiniz.')
                ->action('Üyelik detayı / iptal', route('hekim.uyelik'))
                ->line('Bu e-posta bilgilendirme amaçlıdır; ödeme onayını ayrıca göreceksiniz.');
        }

        return (new MailMessage)
            ->subject('Üyeliğiniz '.$this->kalanGun.' gün sonra bitiyor — Randevu Ajandam')
            ->greeting('Sayın '.$ad.',')
            ->line('Paket üyeliğiniz **'.$bitis.'** tarihinde sona erecek (yaklaşık **'.$this->kalanGun.' gün**).')
            ->line('Kayıtlı kart / otomatik yenileme yok veya kapalı. Hizmet kesintisi yaşamamak için paketinizi yenileyebilirsiniz. Fiyatlara KDV dahildir.')
            ->action('Paket / üyelik', route('hekim.uyelik'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $bitis = $notifiable->uyelik_bitis?->format('d.m.Y') ?? '—';

        if ($this->otomatikYenileme) {
            if ($this->kalanGun <= 0) {
                return [
                    'type' => 'uyelik_yenileme',
                    'title' => 'Otomatik yenileme bugün',
                    'body' => 'Üyeliğiniz bugün yenileniyor (kayıtlı kart, 3D’siz).',
                    'baslik' => 'Otomatik yenileme',
                    'mesaj' => 'Üyeliğiniz ('.$bitis.') kayıtlı kartınızdan otomatik yenilenecek.',
                    'link' => route('hekim.uyelik'),
                    'deep_link' => 'randevuajandam-doktor://packages',
                ];
            }

            return [
                'type' => 'uyelik_yenileme',
                'title' => $this->kalanGun.' gün sonra otomatik yenileme',
                'body' => 'Bitiş '.$bitis.' · kayıtlı karttan 3D’siz çekim',
                'baslik' => 'Otomatik yenileme hatırlatması',
                'mesaj' => 'Üyeliğiniz '.$this->kalanGun.' gün sonra doluyor; kayıtlı kartınızdan otomatik yenilenecek.',
                'link' => route('hekim.uyelik'),
                'deep_link' => 'randevuajandam-doktor://packages',
            ];
        }

        if ($this->kalanGun <= 0) {
            return [
                'type' => 'uyelik_bitis',
                'title' => 'Üyeliğiniz sona erdi',
                'body' => 'Devam için paket ödeyin.',
                'baslik' => 'Üyelik sona erdi',
                'mesaj' => 'Üyeliğiniz ('.$bitis.') sona erdi. Paket seçip ödeme yapın.',
                'link' => route('frontend.hekim.paket_sec'),
                'deep_link' => 'randevuajandam-doktor://packages',
            ];
        }

        return [
            'type' => 'uyelik_bitis',
            'title' => 'Üyelik bitiş hatırlatması',
            'body' => $this->kalanGun.' gün kaldı · '.$bitis,
            'baslik' => 'Üyelik bitiyor',
            'mesaj' => 'Üyeliğinizin bitmesine '.$this->kalanGun.' gün kaldı ('.$bitis.').',
            'link' => route('hekim.uyelik'),
            'deep_link' => 'randevuajandam-doktor://packages',
        ];
    }
}
