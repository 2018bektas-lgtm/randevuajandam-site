<?php

namespace App\Services;

/**
 * Custom domain DNS doğrulama (A / CNAME) — kendi NS olmadan BYOD.
 */
class DnsVerificationService
{
    /**
     * @return array{
     *   ok: bool,
     *   domain: string,
     *   expected_a: string,
     *   found_a: list<string>,
     *   www_cname: list<string>,
     *   expected_cname: string,
     *   message: string,
     *   steps: list<array{adim:int,baslik:string,aciklama:string}>
     * }
     */
    public function check(string $domain): array
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://(www\.)?#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '/');
        $domain = preg_replace('/[^a-z0-9\.\-]/', '', $domain) ?? $domain;

        $expectedA = trim((string) config('services.hostinger.dns_a_record', env('DNS_A_RECORD', '')));
        $expectedCname = strtolower(trim((string) config('services.hostinger.dns_cname_target', env('DNS_CNAME_TARGET', 'proxy.randevuajandam.com'))));
        $expectedCname = rtrim($expectedCname, '.');

        $foundA = $this->lookupA($domain);
        $wwwCname = $this->lookupCname('www.'.$domain);

        $aOk = $expectedA !== '' && in_array($expectedA, $foundA, true);
        // www: CNAME hedefe veya A kaydı sunucu IP'sine
        $wwwOk = false;
        if ($wwwCname !== []) {
            foreach ($wwwCname as $c) {
                $c = rtrim(strtolower($c), '.');
                if ($c === $expectedCname || str_ends_with($c, '.'.$expectedCname) || $c === $domain) {
                    $wwwOk = true;
                    break;
                }
            }
        }
        if (! $wwwOk && $expectedA !== '') {
            $wwwA = $this->lookupA('www.'.$domain);
            $wwwOk = in_array($expectedA, $wwwA, true);
        }

        $ok = $aOk; // apex A zorunlu; www soft

        $message = $ok
            ? ($wwwOk
                ? 'DNS doğru görünüyor (@ A + www). SSL yayılımı 5–60 dk sürebilir.'
                : 'A kaydı doğru. www için CNAME ('.$expectedCname.') veya A eklemeniz önerilir.')
            : ($expectedA === ''
                ? 'Sunucu DNS_A_RECORD yapılandırılmamış. Yöneticiye bildirin.'
                : 'A kaydı henüz '.$expectedA.' göstermiyor. DNS güncelleyip 5–30 dk sonra tekrar kontrol edin.');

        return [
            'ok' => $ok,
            'domain' => $domain,
            'expected_a' => $expectedA,
            'found_a' => $foundA,
            'www_cname' => $wwwCname,
            'expected_cname' => $expectedCname,
            'www_ok' => $wwwOk,
            'message' => $message,
            'steps' => $this->steps($domain, $expectedA, $expectedCname),
        ];
    }

    /**
     * @return list<array{adim:int,baslik:string,aciklama:string}>
     */
    public function steps(string $domain, ?string $a = null, ?string $cname = null): array
    {
        $a = $a ?? trim((string) config('services.hostinger.dns_a_record', env('DNS_A_RECORD', '46.202.158.83')));
        $cname = $cname ?? trim((string) config('services.hostinger.dns_cname_target', 'proxy.randevuajandam.com'));
        $a = $a !== '' ? $a : '46.202.158.83';

        return [
            [
                'adim' => 1,
                'baslik' => 'Domain paneline girin',
                'aciklama' => "{$domain} kaydının olduğu yerde (Hostinger, GoDaddy, Cloudflare…) DNS / Zone Editor açın.",
            ],
            [
                'adim' => 2,
                'baslik' => 'A kaydı (@)',
                'aciklama' => "Tür: A · Ad: @ (veya boş) · Değer: {$a} · TTL: Auto/3600.",
            ],
            [
                'adim' => 3,
                'baslik' => 'www (önerilir)',
                'aciklama' => "Tür: CNAME · Ad: www · Değer: {$cname}  — veya Tür: A · www · {$a}.",
            ],
            [
                'adim' => 4,
                'baslik' => 'Kaydedin ve bekleyin',
                'aciklama' => 'Yayılım genelde 5–60 dakika. Ardından panelden «DNS doğrula» butonuna basın.',
            ],
            [
                'adim' => 5,
                'baslik' => 'Hostinger (bizim taraf)',
                'aciklama' => 'Domain kaydı sistemde. SSL ve site yayınını Hostinger + hekim web uygulaması yönetir; NS değiştirmeniz gerekmez (şimdilik A/CNAME yeterli).',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    protected function lookupA(string $host): array
    {
        $records = @dns_get_record($host, DNS_A) ?: [];
        $ips = [];
        foreach ($records as $r) {
            if (! empty($r['ip'])) {
                $ips[] = $r['ip'];
            }
        }

        // Fallback
        if ($ips === []) {
            $a = @gethostbynamel($host) ?: [];
            $ips = array_values(array_filter($a));
        }

        return array_values(array_unique($ips));
    }

    /**
     * @return list<string>
     */
    protected function lookupCname(string $host): array
    {
        $records = @dns_get_record($host, DNS_CNAME) ?: [];
        $out = [];
        foreach ($records as $r) {
            if (! empty($r['target'])) {
                $out[] = $r['target'];
            }
        }

        return array_values(array_unique($out));
    }
}
