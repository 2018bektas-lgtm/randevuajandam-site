<?php

namespace App\Support;

/**
 * Meta (Facebook) Pixel standart olay kuyruğu.
 * tracking.blade.php içinde PageView sonrası çekilir ve fbq('track', ...) ile gönderilir.
 */
class MetaPixel
{
    public const SESSION_KEY = 'meta_pixel_events';

    public const ONCE_KEY = 'meta_pixel_once';

    /**
     * Olayı oturuma ekle (aynı istek veya bir sonraki sayfa yüklemesinde fire edilir).
     *
     * @param  array<string, mixed>  $params
     */
    public static function queue(string $event, array $params = []): void
    {
        $event = trim($event);
        if ($event === '') {
            return;
        }

        $events = session()->get(self::SESSION_KEY, []);
        if (! is_array($events)) {
            $events = [];
        }

        $events[] = [
            'event' => $event,
            'params' => self::sanitizeParams($params),
        ];

        session()->put(self::SESSION_KEY, $events);
    }

    /**
     * Aynı anahtar için yalnızca bir kez kuyruğa al (Purchase yenilemede çift sayım olmasın).
     *
     * @param  array<string, mixed>  $params
     */
    public static function queueOnce(string $dedupeKey, string $event, array $params = []): void
    {
        $dedupeKey = trim($dedupeKey);
        if ($dedupeKey === '') {
            self::queue($event, $params);

            return;
        }

        $done = session()->get(self::ONCE_KEY, []);
        if (! is_array($done)) {
            $done = [];
        }

        if (isset($done[$dedupeKey])) {
            return;
        }

        $done[$dedupeKey] = true;
        // Oturum boyu tut; aşırı şişmesin diye son 80 anahtarı sakla
        if (count($done) > 80) {
            $done = array_slice($done, -80, null, true);
        }
        session()->put(self::ONCE_KEY, $done);

        self::queue($event, $params);
    }

    /**
     * Kuyruğu al ve temizle (tracking partial).
     *
     * @return list<array{event: string, params: array<string, mixed>}>
     */
    public static function pull(): array
    {
        $events = session()->pull(self::SESSION_KEY, []);

        if (! is_array($events)) {
            return [];
        }

        $out = [];
        foreach ($events as $item) {
            if (! is_array($item) || empty($item['event'])) {
                continue;
            }
            $out[] = [
                'event' => (string) $item['event'],
                'params' => is_array($item['params'] ?? null) ? $item['params'] : [],
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public static function sanitizeParams(array $params): array
    {
        $clean = [];
        foreach ($params as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }
            if (is_array($value)) {
                $clean[$key] = array_values(array_map(
                    static fn ($v) => is_scalar($v) || $v === null ? $v : (string) $v,
                    $value
                ));
            } elseif (is_bool($value) || is_int($value) || is_float($value) || is_string($value) || $value === null) {
                $clean[$key] = $value;
            } else {
                $clean[$key] = (string) $value;
            }
        }

        return $clean;
    }

    /**
     * Para birimli değer parametreleri (Purchase / Subscribe / StartTrial).
     *
     * @return array{value: float, currency: string}
     */
    public static function money(float $value, string $currency = 'TRY'): array
    {
        return [
            'value' => round(max(0, $value), 2),
            'currency' => $currency !== '' ? strtoupper($currency) : 'TRY',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function content(
        string $name,
        string $type = 'product',
        ?string $id = null,
        ?float $value = null,
        string $currency = 'TRY',
        array $extra = []
    ): array {
        $params = array_merge([
            'content_name' => $name,
            'content_type' => $type,
        ], $extra);

        if ($id !== null && $id !== '') {
            $params['content_ids'] = [(string) $id];
        }

        if ($value !== null) {
            $params = array_merge($params, self::money($value, $currency));
        }

        return $params;
    }
}
