<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $driver;

    protected ?string $netgsmUser;

    protected ?string $netgsmPass;

    protected ?string $netgsmHeader;

    protected ?string $iletimerkeziKey;

    protected ?string $iletimerkeziHash;

    protected ?string $iletimerkeziSender;

    public function __construct()
    {
        $this->driver = config('sms.driver', env('SMS_DRIVER', 'log'));

        $this->netgsmUser = config('sms.netgsm.user', env('NETGSM_USER'));
        $this->netgsmPass = config('sms.netgsm.pass', env('NETGSM_PASS'));
        $this->netgsmHeader = config('sms.netgsm.header', env('NETGSM_HEADER'));

        $this->iletimerkeziKey = config('sms.iletimerkezi.key', env('ILETIMERKEZI_KEY'));
        $this->iletimerkeziHash = config('sms.iletimerkezi.hash', env('ILETIMERKEZI_HASH'));
        $this->iletimerkeziSender = config('sms.iletimerkezi.sender', env('ILETIMERKEZI_SENDER'));
    }

    /**
     * Send SMS to a specific number.
     *
     * @param  string  $phone  Phone number (e.g. 5551234567)
     * @param  string  $message  Message content
     */
    public function send(string $phone, string $message): bool
    {
        $normalizedPhone = $this->normalizePhone($phone);

        if (empty($normalizedPhone)) {
            Log::error('SMS Gönderilemedi: Geçersiz telefon numarası formatı.', ['phone' => $phone]);

            return false;
        }

        switch ($this->driver) {
            case 'netgsm':
                return $this->sendNetgsm($normalizedPhone, $message);
            case 'iletimerkezi':
                return $this->sendIletimerkezi($normalizedPhone, $message);
            case 'log':
                if (app()->environment('production')) {
                    Log::error('SMS_DRIVER=log production ortamında kullanılamaz. Netgsm veya İleti Merkezi yapılandırın.');

                    return false;
                }

                return $this->sendLog($normalizedPhone, $message);
            default:
                Log::error('Bilinmeyen SMS driver: '.$this->driver);

                return app()->environment('production')
                    ? false
                    : $this->sendLog($normalizedPhone, $message);
        }
    }

    /**
     * Normalize phone numbers to standard 10 digit (5XXXXXXXXX) or 12 digit (905XXXXXXXXX)
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // If it starts with 0090, strip the 00
        if (str_starts_with($digits, '0090')) {
            $digits = substr($digits, 2);
        }

        // If it starts with 0, strip the 0
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = substr($digits, 1);
        }

        // If it starts with 90 and is 12 digits, return it (standard Turkish with country code)
        if (str_starts_with($digits, '90') && strlen($digits) === 12) {
            return $digits;
        }

        // If it is 10 digits (starts with 5), prepend 90 for Netgsm/providers
        if (strlen($digits) === 10 && str_starts_with($digits, '5')) {
            return '90'.$digits;
        }

        return $digits; // fallback
    }

    /**
     * Send SMS using Netgsm XML API
     */
    protected function sendNetgsm(string $phone, string $message): bool
    {
        if (empty($this->netgsmUser) || empty($this->netgsmPass)) {
            Log::warning('Netgsm SMS gönderilemedi: Kullanıcı adı veya şifre boş.');

            // Production: never pretend SMS was sent
            if (app()->environment('production')) {
                return false;
            }

            return $this->sendLog($phone, $message.' (Netgsm Auth Missing Fallback)');
        }

        $xmlData = '<?xml version="1.0" encoding="UTF-8"?>
        <mainbody>
            <header>
                <company>NETGSM</company>
                <usercode>'.htmlspecialchars($this->netgsmUser).'</usercode>
                <password>'.htmlspecialchars($this->netgsmPass).'</password>
                <type>1:n</type>
                <msgheader>'.htmlspecialchars($this->netgsmHeader ?? 'NETGSM').'</msgheader>
            </header>
            <body>
                <msg><![CDATA['.$message.']]></msg>
                <no>'.$phone.'</no>
            </body>
        </mainbody>';

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'text/xml',
            ])->send('POST', 'https://api.netgsm.com.tr/xmlbulkhttppost.asp', [
                'body' => $xmlData,
            ]);

            if ($response->successful()) {
                $body = $response->body();
                Log::info('Netgsm SMS başarıyla gönderildi.', ['phone' => $phone, 'response' => $body]);

                // Netgsm returns "00" or similar on success, otherwise code like "20", "30", etc.
                return str_starts_with($body, '00');
            }

            Log::error('Netgsm API Hatası: ', ['status' => $response->status(), 'body' => $response->body()]);

            return false;
        } catch (\Exception $e) {
            Log::error('Netgsm Bağlantı Hatası: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Send SMS using İleti Merkezi JSON API
     */
    protected function sendIletimerkezi(string $phone, string $message): bool
    {
        if (empty($this->iletimerkeziKey) || empty($this->iletimerkeziHash)) {
            Log::warning('İleti Merkezi SMS gönderilemedi: API Key veya Hash boş.');

            if (app()->environment('production')) {
                return false;
            }

            return $this->sendLog($phone, $message.' (İleti Merkezi Auth Missing Fallback)');
        }

        // İleti Merkezi usually expects phone numbers starting with 90 or 5 (API works with 905XXXXXXXXX)
        $data = [
            'request' => [
                'authentication' => [
                    'key' => $this->iletimerkeziKey,
                    'hash' => $this->iletimerkeziHash,
                ],
                'order' => [
                    'sender' => $this->iletimerkeziSender ?? 'ILETIMERKEZ',
                    'sendDateTime' => '',
                    'message' => [
                        'text' => $message,
                        'recipients' => [
                            'number' => [$phone],
                        ],
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://api.iletimerkezi.com/v1/send-sms/json', $data);

            if ($response->successful()) {
                $resData = $response->json();
                $statusCode = $resData['response']['status']['code'] ?? null;

                if ($statusCode == 200) {
                    Log::info('İleti Merkezi SMS başarıyla gönderildi.', ['phone' => $phone]);

                    return true;
                }

                Log::error('İleti Merkezi API Hatası: ', ['response' => $resData]);

                return false;
            }

            Log::error('İleti Merkezi HTTP Hatası: ', ['status' => $response->status()]);

            return false;
        } catch (\Exception $e) {
            Log::error('İleti Merkezi Bağlantı Hatası: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Log SMS for local development
     */
    protected function sendLog(string $phone, string $message): bool
    {
        Log::channel('stack')->info('SMS GÖNDERİLDİ (LOG MODU)', [
            'phone' => $phone,
            'message' => $message,
        ]);

        return true;
    }
}
