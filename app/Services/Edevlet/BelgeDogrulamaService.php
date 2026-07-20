<?php

namespace App\Services\Edevlet;

use App\Models\EdevletDogrulamaLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * e-Devlet barkodlu belge doğrulama (YÖK mezun belgesi).
 * Resmi API yok; HTML form adımları. Feature flag ile kapatılabilir.
 */
class BelgeDogrulamaService
{
    public function __construct(
        protected YokMezunBelgesiParser $parser
    ) {}

    public function isEnabled(): bool
    {
        return (bool) config('services.edevlet.auto_verify', true);
    }

    /**
     * @return array{
     *   ok:bool, parsed:?array, pdf_path:?string, log_id:?int, error:?string, sure_ms:int
     * }
     */
    public function dogrulaMezunBelgesi(string $barkod, string $tc, ?string $ip = null): array
    {
        $start = hrtime(true);
        $barkod = strtoupper(trim($barkod));
        $tc = preg_replace('/\D/', '', $tc) ?? '';

        if (! $this->isEnabled()) {
            return $this->fail('Otomatik e-Devlet doğrulama kapalı.', $barkod, $tc, $ip, $start);
        }
        if (! preg_match('/^[A-Z0-9\-]{8,64}$/', $barkod)) {
            return $this->fail('Geçersiz barkod formatı.', $barkod, $tc, $ip, $start);
        }
        if (strlen($tc) !== 11) {
            return $this->fail('T.C. kimlik 11 hane olmalıdır.', $barkod, $tc, $ip, $start);
        }

        try {
            $cookieJar = new \GuzzleHttp\Cookie\CookieJar;
            $client = Http::withOptions([
                'cookies' => $cookieJar,
                'timeout' => (int) config('services.edevlet.timeout', 25),
                'connect_timeout' => 10,
                'allow_redirects' => true,
                'verify' => true,
            ])->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'tr-TR,tr;q=0.9',
            ]);

            $base = 'https://www.turkiye.gov.tr';

            // 1) Form sayfası + token
            $r1 = $client->get($base.'/belge-dogrulama');
            if (! $r1->successful()) {
                return $this->fail('e-Devlet sayfasına erişilemedi.', $barkod, $tc, $ip, $start);
            }
            $token = $this->extractToken($r1->body());
            if (! $token) {
                return $this->fail('e-Devlet form token alınamadı.', $barkod, $tc, $ip, $start);
            }

            // 2) Barkod
            $r2 = $client->asForm()->withHeaders([
                'Referer' => $base.'/belge-dogrulama',
            ])->post($base.'/belge-dogrulama?submit', [
                'token' => $token,
                'sorgulananBarkod' => $barkod,
                'btn' => 'Devam Et',
            ]);
            $token = $this->extractToken($r2->body()) ?? $token;
            if (! str_contains($r2->body(), 'ikinciAlan') && ! str_contains($r2->body(), 'T.C. Kimlik')) {
                return $this->fail('Barkod kabul edilmedi veya belge bulunamadı.', $barkod, $tc, $ip, $start, [
                    'step' => 2,
                ]);
            }

            // 3) TC
            $r3 = $client->asForm()->withHeaders([
                'Referer' => $base.'/belge-dogrulama?submit',
            ])->post($base.'/belge-dogrulama?islem=dogrulama&submit', [
                'token' => $token,
                'ikinciAlan' => $tc,
                'btn' => 'Devam Et',
            ]);
            $token = $this->extractToken($r3->body()) ?? $token;
            if (! str_contains($r3->body(), 'chkOnay') && ! str_contains($r3->body(), 'bilgilendirme')) {
                // Bazen doğrudan sonuç
                if (! str_contains($r3->body(), 'belge=goster')) {
                    return $this->fail('TC ile doğrulama adımı başarısız.', $barkod, $tc, $ip, $start, [
                        'step' => 3,
                    ]);
                }
            }

            // 4) Onay checkbox
            if (str_contains($r3->body(), 'chkOnay') || str_contains($r3->body(), 'islem=onay')) {
                $r4 = $client->asForm()->post($base.'/belge-dogrulama?islem=onay&submit', [
                    'token' => $token,
                    'chkOnay' => '1',
                    'btn' => 'Devam Et',
                ]);
                if (! $r4->successful() && ! str_contains($r4->body(), 'belge=goster')) {
                    return $this->fail('Onay adımı başarısız.', $barkod, $tc, $ip, $start, ['step' => 4]);
                }
            }

            // 5) PDF
            $pdfRes = $client->withHeaders([
                'Accept' => 'application/pdf,*/*',
                'Referer' => $base.'/belge-dogrulama',
            ])->get($base.'/belge-dogrulama?belge=goster&goster=1');

            $body = $pdfRes->body();
            if (! str_starts_with($body, '%PDF')) {
                return $this->fail('Mezun belgesi PDF alınamadı.', $barkod, $tc, $ip, $start, ['step' => 5]);
            }

            $rel = 'private/edevlet-mezun/'.date('Y/m').'/'.$barkod.'_'.Str::random(6).'.pdf';
            Storage::disk('local')->put($rel, $body);
            $abs = Storage::disk('local')->path($rel);

            $parsed = $this->parser->parsePdfFile($abs);
            if (empty($parsed['barkod'])) {
                $parsed['barkod'] = $barkod;
            }
            if (empty($parsed['tc'])) {
                $parsed['tc'] = $tc;
            }

            $ms = (int) ((hrtime(true) - $start) / 1e6);
            $log = EdevletDogrulamaLog::create([
                'barkod' => $barkod,
                'tc_maskeli' => EdevletDogrulamaLog::maskTc($tc),
                'durum' => 'basarili',
                'sure_ms' => $ms,
                'hata' => null,
                'ip' => $ip,
                'meta' => [
                    'program' => $parsed['program'] ?? null,
                    'ad' => $parsed['ad_soyad'] ?? null,
                ],
            ]);

            return [
                'ok' => true,
                'parsed' => $parsed,
                'pdf_path' => $rel,
                'log_id' => $log->id,
                'error' => null,
                'sure_ms' => $ms,
            ];
        } catch (\Throwable $e) {
            Log::warning('e-Devlet doğrulama hata', [
                'barkod' => $barkod,
                'message' => $e->getMessage(),
            ]);

            return $this->fail(
                'Doğrulama sırasında hata: '.$e->getMessage(),
                $barkod,
                $tc,
                $ip,
                $start
            );
        }
    }

    /**
     * Sadece yüklenen PDF’i parse et (e-Devlet çağrısı yok) — yedek / test.
     *
     * @return array{ok:bool,parsed:?array,pdf_path:?string,error:?string}
     */
    public function parseYuklenenPdf(string $absolutePath, ?string $storeRel = null): array
    {
        try {
            $parsed = $this->parser->parsePdfFile($absolutePath);

            return [
                'ok' => true,
                'parsed' => $parsed,
                'pdf_path' => $storeRel,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'parsed' => null,
                'pdf_path' => $storeRel,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function extractToken(string $html): ?string
    {
        if (preg_match('/name="token"\s+value="([^"]+)"/', $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES);
        }
        if (preg_match('/name="token" value="([^"]+)"/', $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array{ok:bool,parsed:null,pdf_path:null,log_id:?int,error:string,sure_ms:int}
     */
    protected function fail(string $msg, string $barkod, string $tc, ?string $ip, int $start, array $meta = []): array
    {
        $ms = (int) ((hrtime(true) - $start) / 1e6);
        $log = null;
        try {
            $log = EdevletDogrulamaLog::create([
                'barkod' => $barkod ?: null,
                'tc_maskeli' => EdevletDogrulamaLog::maskTc($tc),
                'durum' => 'basarisiz',
                'sure_ms' => $ms,
                'hata' => Str::limit($msg, 500),
                'ip' => $ip,
                'meta' => $meta ?: null,
            ]);
        } catch (\Throwable) {
            // ignore log fail
        }

        return [
            'ok' => false,
            'parsed' => null,
            'pdf_path' => null,
            'log_id' => $log?->id,
            'error' => $msg,
            'sure_ms' => $ms,
        ];
    }
}
