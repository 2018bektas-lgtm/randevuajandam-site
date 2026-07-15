<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public function __construct(
        protected Google2FA $google2fa = new Google2FA
    ) {}

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    /**
     * @return list<string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(4).'-'.Str::random(4));
        }

        return $codes;
    }

    public function qrCodeSvg(string $company, string $email, string $secret, int $size = 200): string
    {
        $url = $this->google2fa->getQRCodeUrl($company, $email, $secret);
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);

        return $writer->writeString($url);
    }

    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';
        if ($code === '' || strlen($code) < 6) {
            return false;
        }

        try {
            return (bool) $this->google2fa->verifyKey($secret, $code, 1);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Verify TOTP or a recovery code. Consumes recovery code on success.
     */
    public function verifyUser(Model $user, string $code): bool
    {
        $secret = (string) ($user->two_factor_secret ?? '');
        if ($secret !== '' && $this->verify($secret, $code)) {
            return true;
        }

        return $this->consumeRecoveryCode($user, $code);
    }

    public function consumeRecoveryCode(Model $user, string $code): bool
    {
        $normalized = strtoupper(trim((string) $code));
        $normalizedCompact = str_replace([' ', '-'], '', $normalized);
        $codes = is_array($user->two_factor_recovery_codes) ? $user->two_factor_recovery_codes : [];
        $idx = false;

        foreach ($codes as $i => $stored) {
            $s = strtoupper(trim((string) $stored));
            $sCompact = str_replace([' ', '-'], '', $s);
            if ($s === $normalized || $sCompact === $normalizedCompact) {
                $idx = $i;
                break;
            }
        }

        if ($idx === false) {
            return false;
        }

        unset($codes[$idx]);
        $user->forceFill([
            'two_factor_recovery_codes' => array_values($codes),
        ])->save();

        return true;
    }

    /**
     * Start setup: store pending secret in session, return QR + secret.
     *
     * @return array{secret: string, qr_svg: string}
     */
    public function beginSetup(Model $user, string $sessionKey = 'two_factor.setup_secret'): array
    {
        $secret = $this->generateSecret();
        session([$sessionKey => $secret]);

        $email = (string) ($user->e_posta ?? $user->email ?? 'user');
        $company = config('app.name', 'Randevu Ajandam');

        return [
            'secret' => $secret,
            'qr_svg' => $this->qrCodeSvg($company, $email, $secret),
        ];
    }

    /**
     * Confirm setup with TOTP code; persist secret + recovery codes.
     *
     * @return list<string>|null  recovery codes once, or null on failure
     */
    public function confirmSetup(Model $user, string $code, string $sessionKey = 'two_factor.setup_secret'): ?array
    {
        $secret = (string) session($sessionKey, '');
        if ($secret === '' || ! $this->verify($secret, $code)) {
            return null;
        }

        $recovery = $this->generateRecoveryCodes();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recovery,
            'two_factor_confirmed_at' => now(),
        ])->save();

        session()->forget($sessionKey);

        return $recovery;
    }

    public function disable(Model $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }
}
