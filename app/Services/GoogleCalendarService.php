<?php

namespace App\Services;

use App\Models\Doktor;
use App\Models\DoktorGoogleBlok;
use App\Models\Klinik;
use App\Models\Randevu;
use Carbon\CarbonImmutable;
use Google\Client as GoogleClient;
use Google\Service\Calendar as CalendarService;
use Google\Service\Calendar\Channel;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Exception as GoogleServiceException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Google Calendar entegrasyonu icin ince sarmalayici.
 *
 * Ilkeler:
 *  - Her hekim / klinik icin ayri OAuth client uretiriz (per-user).
 *  - Access token 1 saatte biter. refreshTokenIfNeeded() cagrisi otomatik yeniler.
 *  - Refresh basarisiz olursa (401 invalid_grant) config'i clear ederiz.
 *  - Timezone: config('google_calendar.timezone') — Europe/Istanbul.
 *  - Idempotent push: randevu.google_event_id doluysa update, degilse insert.
 */
class GoogleCalendarService
{
    public function enabled(): bool
    {
        return (bool) config('google_calendar.enabled', false)
            && filled(config('google_calendar.client_id'))
            && filled(config('google_calendar.client_secret'));
    }

    /**
     * Hekim / klinik icin OAuth URL uretir (Bagla butonu).
     */
    public function authUrl(string $guard, string $state): string
    {
        $client = $this->baseClient($guard);
        $client->setState($state);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setIncludeGrantedScopes(true);

        return $client->createAuthUrl();
    }

    /**
     * OAuth callback -> code'u access+refresh token'a cevirir, kullanicinin birincil
     * takvim ID'sini ve e-postasini alir.
     *
     * @return array{
     *   access_token: string, refresh_token: string, expires_at: int,
     *   calendar_id: string, email: ?string
     * }
     */
    public function exchangeCode(string $guard, string $code): array
    {
        $client = $this->baseClient($guard);
        $token = $client->fetchAccessTokenWithAuthCode($code);
        if (isset($token['error'])) {
            throw new RuntimeException('Google token alinamadi: '.($token['error_description'] ?? $token['error']));
        }
        if (empty($token['refresh_token'])) {
            throw new RuntimeException('Google refresh token dondurulmedi. Kullanici uygulamayi Google hesap ayarlarindan kaldirip tekrar denesin.');
        }

        $client->setAccessToken($token);
        $calendar = new CalendarService($client);

        // Kullanicinin birincil takvim id + e-postasi
        $primary = $calendar->calendars->get('primary');
        $email = $primary->getId(); // Google'da primary calendar id = kullanici e-postasi

        return [
            'access_token' => $token['access_token'],
            'refresh_token' => $token['refresh_token'],
            'expires_at' => time() + (int) ($token['expires_in'] ?? 3600),
            'calendar_id' => $primary->getId(),
            'email' => $email,
        ];
    }

    /**
     * Randevuyu Google'a push eder. Idempotent — mevcutsa update, degilse insert.
     * Sonra randevu.google_event_id + google_synced_at'i gunceller.
     */
    public function pushRandevu(Randevu $randevu): ?Randevu
    {
        $doktor = $randevu->doktor;
        if (! $doktor) {
            return null;
        }
        $ayar = $doktor->googleTakvimAyari();
        if (! $ayar || ! $this->enabled()) {
            return null;
        }

        $calendar = $this->calendarFor($doktor);
        $calendarId = $ayar['calendar_id'] ?? 'primary';

        $event = $this->buildEventFromRandevu($randevu);

        try {
            if (! empty($randevu->google_event_id)) {
                $saved = $calendar->events->update($calendarId, $randevu->google_event_id, $event);
            } else {
                $saved = $calendar->events->insert($calendarId, $event);
            }
        } catch (GoogleServiceException $e) {
            // Insert varsa ama event silinmis olabilir — tek retry ile insert dene.
            if (! empty($randevu->google_event_id) && $e->getCode() === 404) {
                $randevu->google_event_id = null;
                $saved = $calendar->events->insert($calendarId, $event);
            } else {
                throw $e;
            }
        }

        $randevu->forceFill([
            'google_event_id' => $saved->getId(),
            'google_synced_at' => now(),
        ])->saveQuietly();

        return $randevu;
    }

    /**
     * Randevu iptal edilince Google'dan sil.
     */
    public function deleteRandevu(Randevu $randevu): void
    {
        if (empty($randevu->google_event_id)) {
            return;
        }
        $doktor = $randevu->doktor;
        if (! $doktor) {
            return;
        }
        $ayar = $doktor->googleTakvimAyari();
        if (! $ayar || ! $this->enabled()) {
            return;
        }

        $calendar = $this->calendarFor($doktor);
        $calendarId = $ayar['calendar_id'] ?? 'primary';

        try {
            $calendar->events->delete($calendarId, $randevu->google_event_id);
        } catch (GoogleServiceException $e) {
            // 404/410 -> zaten yok, sorun degil
            if (! in_array($e->getCode(), [404, 410], true)) {
                throw $e;
            }
        }

        $randevu->forceFill([
            'google_event_id' => null,
            'google_synced_at' => now(),
        ])->saveQuietly();
    }

    /**
     * Google -> DB pull. Icremental (syncToken varsa) yoksa initial (timeMin=today).
     * Cektigi eventleri doktor_google_bloklari tablosuna upsert eder.
     * randevular tablosuna DOKUNMAZ. Kendi olusturdugumuz event'leri (extendedProperties.private.ra_randevu_id)
     * atlariz — cift blok olmasin.
     */
    public function pullBloklar(Doktor $doktor): int
    {
        $ayar = $doktor->googleTakvimAyari();
        if (! $ayar || ! $this->enabled()) {
            return 0;
        }

        $calendar = $this->calendarFor($doktor);
        $calendarId = $ayar['calendar_id'] ?? 'primary';

        $params = [
            'singleEvents' => true,        // recurring event'ler expand edilir
            'maxResults' => 250,
            'showDeleted' => true,
        ];
        if (! empty($ayar['sync_token'])) {
            $params['syncToken'] = $ayar['sync_token'];
        } else {
            // Ilk cekim: bugunden 6 ay ileri
            $params['timeMin'] = now()->startOfDay()->toRfc3339String();
            $params['timeMax'] = now()->addMonths(6)->endOfDay()->toRfc3339String();
        }

        $imported = 0;
        $pageToken = null;
        $newSyncToken = null;

        try {
            do {
                if ($pageToken) {
                    $params['pageToken'] = $pageToken;
                }
                $list = $calendar->events->listEvents($calendarId, $params);
                foreach ($list->getItems() as $event) {
                    $imported += $this->applyEventToBloklar($doktor, $event) ? 1 : 0;
                }
                $pageToken = $list->getNextPageToken();
                $newSyncToken = $list->getNextSyncToken() ?: $newSyncToken;
            } while ($pageToken);
        } catch (GoogleServiceException $e) {
            // syncToken expired -> full resync
            if ($e->getCode() === 410) {
                $this->persistConfig($doktor, array_merge($ayar, ['sync_token' => null]));
                return $this->pullBloklar($doktor);
            }
            throw $e;
        }

        if ($newSyncToken) {
            $this->persistConfig($doktor, array_merge($this->currentConfig($doktor), ['sync_token' => $newSyncToken]));
        }

        return $imported;
    }

    /**
     * Google'a push notification kanali ac (watch). 7 gun sonra otomatik dolar,
     * google-calendar:renew-channels komutu yeniler.
     */
    public function watchChannel(Doktor $doktor): ?array
    {
        $ayar = $doktor->googleTakvimAyari();
        $webhookUrl = (string) config('google_calendar.webhook_url');
        $webhookToken = (string) config('google_calendar.webhook_token');

        if (! $ayar || ! $this->enabled() || $webhookUrl === '' || $webhookToken === '') {
            return null;
        }

        $calendar = $this->calendarFor($doktor);
        $calendarId = $ayar['calendar_id'] ?? 'primary';

        $channel = new Channel([
            'id' => (string) Str::uuid(),
            'type' => 'web_hook',
            'address' => $webhookUrl,
            'token' => $webhookToken,
        ]);

        $result = $calendar->events->watch($calendarId, $channel);

        $newConfig = array_merge($this->currentConfig($doktor), [
            'channel_id' => $result->getId(),
            'channel_resource_id' => $result->getResourceId(),
            'channel_expires_at' => $result->getExpiration() ? (int) ($result->getExpiration() / 1000) : null,
        ]);
        $this->persistConfig($doktor, $newConfig);

        return [
            'id' => $result->getId(),
            'resource_id' => $result->getResourceId(),
            'expires_at' => $newConfig['channel_expires_at'],
        ];
    }

    /**
     * Aktif watch kanalini kapat (bagla-> kes akisinda).
     */
    public function stopWatch(Doktor $doktor): void
    {
        $ayar = $doktor->googleTakvimAyari();
        if (! $ayar || empty($ayar['channel_id']) || empty($ayar['channel_resource_id'])) {
            return;
        }

        try {
            $calendar = $this->calendarFor($doktor);
            $channel = new Channel([
                'id' => $ayar['channel_id'],
                'resourceId' => $ayar['channel_resource_id'],
            ]);
            $calendar->channels->stop($channel);
        } catch (Throwable $e) {
            Log::warning('Google Calendar watch stop hata', [
                'doktor_id' => $doktor->id,
                'msg' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Baglantiyi tamamen sil (kes butonu). Watch kapatilir, config null'lanir,
     * eski google_event_id'ler bosaltilir (Google tarafinda kalsin isterse
     * hekim manuel sildırir).
     */
    public function disconnect(Doktor|Klinik $entity): void
    {
        if ($entity instanceof Doktor) {
            $this->stopWatch($entity);
            DoktorGoogleBlok::where('doktor_id', $entity->id)->delete();
            Randevu::where('doktor_id', $entity->id)
                ->whereNotNull('google_event_id')
                ->update(['google_event_id' => null, 'google_synced_at' => now()]);
        }

        $entity->forceFill([
            'google_calendar_config' => null,
            'google_calendar_baglandi_at' => null,
        ])->save();
    }

    /* ================================================================
     * Internals
     * =============================================================== */

    protected function baseClient(string $guard): GoogleClient
    {
        $client = new GoogleClient();
        $client->setApplicationName('RandevuAjandam');
        $client->setClientId((string) config('google_calendar.client_id'));
        $client->setClientSecret((string) config('google_calendar.client_secret'));
        $client->setRedirectUri($this->redirectFor($guard));
        $client->setScopes(config('google_calendar.scopes', []));
        $client->setAccessType('offline');

        return $client;
    }

    protected function redirectFor(string $guard): string
    {
        return $guard === 'klinik'
            ? (string) config('google_calendar.redirect_uri_klinik')
            : (string) config('google_calendar.redirect_uri_hekim');
    }

    /**
     * Hekim (veya kliniginin) config'inden Calendar servisi uret.
     * Refresh gerekiyorsa yapar, yeni token'i persiste eder.
     */
    protected function calendarFor(Doktor $doktor): CalendarService
    {
        $ayar = $doktor->googleTakvimAyari();
        if (! $ayar) {
            throw new RuntimeException('Hekimin Google Takvim bagli degil.');
        }

        // Hangi entity'nin config'i? (kendi mi klinigi mi)
        $entity = ! empty(($doktor->google_calendar_config ?? [])['refresh_token'])
            ? $doktor
            : $doktor->klinik;

        $guard = $entity instanceof Klinik ? 'klinik' : 'hekim';
        $client = $this->baseClient($guard);

        $client->setAccessToken([
            'access_token' => $ayar['access_token'] ?? null,
            'refresh_token' => $ayar['refresh_token'],
            'expires_in' => max(1, ($ayar['expires_at'] ?? 0) - time()),
            'created' => ($ayar['expires_at'] ?? time()) - 3600,
        ]);

        if ($client->isAccessTokenExpired()) {
            $refreshed = $client->fetchAccessTokenWithRefreshToken($ayar['refresh_token']);
            if (isset($refreshed['error'])) {
                Log::warning('Google Calendar refresh basarisiz — baglanti kesildi', [
                    'entity' => $entity ? get_class($entity).':'.$entity->id : null,
                    'error' => $refreshed['error'],
                ]);
                if ($entity) {
                    $this->disconnect($entity);
                }
                throw new RuntimeException('Google Takvim baglantisi suresi doldu, lutfen tekrar baglayin.');
            }

            $newAyar = array_merge($ayar, [
                'access_token' => $refreshed['access_token'],
                'expires_at' => time() + (int) ($refreshed['expires_in'] ?? 3600),
            ]);
            // refresh_token nadiren donerse guncelle
            if (! empty($refreshed['refresh_token'])) {
                $newAyar['refresh_token'] = $refreshed['refresh_token'];
            }
            if ($entity) {
                $this->persistConfig($entity, $newAyar);
            }
        }

        return new CalendarService($client);
    }

    protected function currentConfig(Doktor|Klinik $entity): array
    {
        return (array) ($entity->google_calendar_config ?? []);
    }

    protected function persistConfig(Doktor|Klinik $entity, array $config): void
    {
        $entity->forceFill([
            'google_calendar_config' => $config,
        ])->saveQuietly();
    }

    /**
     * Randevu -> Google Event. iCalUID deterministik: idempotent update icin sart.
     */
    protected function buildEventFromRandevu(Randevu $randevu): Event
    {
        $tz = (string) config('google_calendar.timezone', 'Europe/Istanbul');
        $tarih = $randevu->tarih instanceof \DateTimeInterface
            ? $randevu->tarih->format('Y-m-d')
            : substr((string) $randevu->tarih, 0, 10);
        $saat = substr((string) $randevu->saat, 0, 5);

        $start = CarbonImmutable::parse($tarih.' '.$saat, $tz);
        // Hizmet suresi varsa onu al, yoksa 30dk varsayilan.
        $sureDk = (int) ($randevu->hizmet?->sure ?? 30);
        $end = $start->addMinutes(max(5, $sureDk));

        $baslik = trim(
            'Randevu — '
            .trim(($randevu->ad ?? '').' '.($randevu->soyad ?? ''))
        );
        if ($baslik === 'Randevu —') {
            $baslik = 'Randevu';
        }

        $aciklamaSatirlari = array_filter([
            $randevu->hizmet?->ad ? 'Hizmet: '.$randevu->hizmet->ad : null,
            $randevu->telefon ? 'Telefon: '.$randevu->telefon : null,
            $randevu->e_posta ? 'E-posta: '.$randevu->e_posta : null,
            $randevu->not ? "\nNot: ".$randevu->not : null,
            "\n(RandevuAjandam #".$randevu->id.')',
        ]);

        return new Event([
            'iCalUID' => 'randevu-'.$randevu->id.'@randevuajandam.com',
            'summary' => $baslik,
            'description' => implode("\n", $aciklamaSatirlari),
            'start' => new EventDateTime([
                'dateTime' => $start->toRfc3339String(),
                'timeZone' => $tz,
            ]),
            'end' => new EventDateTime([
                'dateTime' => $end->toRfc3339String(),
                'timeZone' => $tz,
            ]),
            // Cift blok engeli: pull sirasinda bu isareti gorursek atlariz.
            'extendedProperties' => [
                'private' => [
                    'ra_randevu_id' => (string) $randevu->id,
                ],
            ],
        ]);
    }

    /**
     * Google event'ini bloklar tablosuna upsert eder. Return: gerçekten eklendi/güncellendi mi.
     */
    protected function applyEventToBloklar(Doktor $doktor, Event $event): bool
    {
        // Bizim yazdigimiz event'i atla (cift blok olmasin)
        $extended = $event->getExtendedProperties();
        if ($extended && isset(($extended->getPrivate() ?? [])['ra_randevu_id'])) {
            return false;
        }

        $status = $event->getStatus();
        $eventId = $event->getId();
        if (! $eventId) {
            return false;
        }

        // Silinen event -> bloklardan da kaldir
        if ($status === 'cancelled') {
            DoktorGoogleBlok::where('doktor_id', $doktor->id)
                ->where('google_event_id', $eventId)
                ->delete();
            return true;
        }

        $start = $event->getStart();
        $end = $event->getEnd();
        if (! $start || ! $end) {
            return false;
        }

        $tz = (string) config('google_calendar.timezone', 'Europe/Istanbul');
        $hepsiGun = ! empty($start->getDate());
        if ($hepsiGun) {
            $baslangic = CarbonImmutable::parse($start->getDate().' 00:00:00', $tz);
            $bitis = CarbonImmutable::parse($end->getDate().' 00:00:00', $tz);
        } else {
            $baslangic = CarbonImmutable::parse($start->getDateTime())->setTimezone($tz);
            $bitis = CarbonImmutable::parse($end->getDateTime())->setTimezone($tz);
        }

        DoktorGoogleBlok::updateOrCreate(
            [
                'doktor_id' => $doktor->id,
                'google_event_id' => $eventId,
                'baslangic_at' => $baslangic->toDateTimeString(),
            ],
            [
                'bitis_at' => $bitis->toDateTimeString(),
                'baslik' => $event->getSummary() ? mb_substr((string) $event->getSummary(), 0, 255) : null,
                'hepsi_gun_mu' => $hepsiGun,
            ]
        );

        return true;
    }
}
