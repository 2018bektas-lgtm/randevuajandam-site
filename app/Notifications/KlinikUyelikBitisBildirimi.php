<?php

namespace App\Notifications;

use App\Models\Klinik;
use App\Notifications\Concerns\NotifiesDoktorApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KlinikUyelikBitisBildirimi extends Notification implements ShouldQueue
{
    use NotifiesDoktorApp;
    use Queueable;

    public function __construct(
        public Klinik $klinik,
        public int $kalanGun,
        public bool $otomatikYenileme = false,
        public ?float $tahminiTutar = null,
        public string $periyotLabel = 'aylık'
    ) {}

    /**
     * @return array<int, string|class-string>
     */
    public function via(object $notifiable): array
    {
        return $this->doktorAppChannels(['mail']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ad = $this->klinik->sahipDoktor?->ad_soyad ?? 'Klinik Yetkilisi';
        $bitis = $this->klinik->uyelik_bitis
            ? \Carbon\Carbon::parse($this->klinik->uyelik_bitis)->format('d.m.Y')
            : '—';
        $tutarStr = $this->tahminiTutar !== null
            ? '₺'.number_format($this->tahminiTutar, 2, ',', '.')
            : null;
        $url = route('hekim.uyelik');

        if ($this->kalanGun <= 0) {
            if ($this->otomatikYenileme) {
                return (new MailMessage)
                    ->subject('Klinik üyeliği bugün yenileniyor — Randevu Ajandam')
                    ->greeting('Sayın '.$ad.',')
                    ->line('**'.$this->klinik->ad.'** klinik üyeliğinizin süresi **'.$bitis.'** itibarıyla doluyor / doldu.')
                    ->line('Kayıtlı karttan **3D’siz** otomatik yenileme denemesi yapılacaktır'
                        .($tutarStr ? ' (yaklaşık **'.$tutarStr.'**)' : '').'.')
                    ->action('Üyelik detayı', $url);
            }

            return (new MailMessage)
                ->subject('Klinik üyeliğiniz sona erdi — Randevu Ajandam')
                ->greeting('Sayın '.$ad.',')
                ->line('**'.$this->klinik->ad.'** kliniğinizin üyelik süresi bugün itibarıyla sona ermiştir.')
                ->line('Panel erişiminin kesilmemesi için lütfen üyeliğinizi yenileyiniz.')
                ->action('Paket / üyelik', $url);
        }

        if ($this->otomatikYenileme) {
            return (new MailMessage)
                ->subject($this->kalanGun.' gün sonra klinik otomatik yenileme — Randevu Ajandam')
                ->greeting('Sayın '.$ad.',')
                ->line('**'.$this->klinik->ad.'** klinik üyeliğiniz **'.$bitis.'** tarihinde sona erecek (**'.$this->kalanGun.' gün**).')
                ->line('Otomatik yenileme **açık**. Süre dolunca kayıtlı karttan **3D’siz**'
                    .($tutarStr ? ' yaklaşık **'.$tutarStr.'** ('.$this->periyotLabel.')' : ' paket ücreti')
                    .' tahsil edilecektir.')
                ->line('İstemiyorsanız dönem bitmeden panelden aboneliği iptal edebilirsiniz.')
                ->action('Üyelik / iptal', $url);
        }

        return (new MailMessage)
            ->subject('Klinik üyeliği '.$this->kalanGun.' gün sonra bitiyor — Randevu Ajandam')
            ->greeting('Sayın '.$ad.',')
            ->line('**'.$this->klinik->ad.'** kliniğinizin üyelik süresi **'.$this->kalanGun.' gün sonra** (**'.$bitis.'**) sona erecektir.')
            ->line('Otomatik yenileme yok veya kapalı. Hizmet kesintisi yaşamamak için aboneliğinizi yenileyiniz.')
            ->action('Abonelik ayarları', $url)
            ->line('Sağlıklı günler dileriz.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->otomatikYenileme) {
            if ($this->kalanGun <= 0) {
                return [
                    'type' => 'klinik_uyelik_yenileme',
                    'title' => 'Klinik otomatik yenileme bugün',
                    'body' => $this->klinik->ad.' · kayıtlı karttan 3D’siz',
                    'baslik' => 'Klinik otomatik yenileme',
                    'mesaj' => $this->klinik->ad.' üyeliği bugün otomatik yenilenecek.',
                    'link' => route('hekim.uyelik'),
                    'deep_link' => 'randevuajandam-doktor://packages',
                ];
            }

            return [
                'type' => 'klinik_uyelik_yenileme',
                'title' => $this->kalanGun.' gün sonra klinik yenileme',
                'body' => $this->klinik->ad.' · otomatik çekim',
                'baslik' => 'Klinik otomatik yenileme',
                'mesaj' => $this->klinik->ad.' üyeliğinin bitmesine '.$this->kalanGun.' gün kaldı; otomatik yenilenecek.',
                'link' => route('hekim.uyelik'),
                'deep_link' => 'randevuajandam-doktor://packages',
            ];
        }

        if ($this->kalanGun <= 0) {
            return [
                'type' => 'klinik_uyelik',
                'title' => 'Klinik üyeliği sona erdi',
                'body' => $this->klinik->ad.' üyeliği bugün sona erdi. Lütfen yenileyin.',
                'baslik' => 'Klinik Üyeliğiniz Sona Erdi!',
                'mesaj' => $this->klinik->ad.' isimli kliniğinizin üyelik süresi bugün sona erdi. Panel erişimi için lütfen yenileyin.',
                'link' => route('hekim.uyelik'),
                'deep_link' => 'randevuajandam-doktor://packages',
            ];
        }

        return [
            'type' => 'klinik_uyelik',
            'title' => 'Üyelik bitiş hatırlatması',
            'body' => $this->klinik->ad.' · '.$this->kalanGun.' gün kaldı',
            'baslik' => 'Klinik Üyeliği Bitiş Hatırlatması',
            'mesaj' => $this->klinik->ad.' isimli kliniğinizin üyelik süresinin bitmesine '.$this->kalanGun.' gün kaldı.',
            'link' => route('hekim.uyelik'),
            'deep_link' => 'randevuajandam-doktor://packages',
        ];
    }
}
