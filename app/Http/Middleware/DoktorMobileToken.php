<?php

namespace App\Http\Middleware;

use App\Models\DoktorApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DoktorMobileToken
{
    /**
     * Üyelik kontrolünden muaf path parçaları (paket / oturum / bildirim).
     *
     * @var list<string>
     */
    protected array $membershipBypass = [
        '/auth/me',
        '/auth/logout',
        '/auth/device',
        '/packages',
        '/package-features',
        '/packages/subscribe',
        '/packages/prefer',
        '/packages/iap-confirm',
        '/notifications',
        '/profile',
        '/meta',
        '/password',
        '/two-factor',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $header = (string) $request->bearerToken();
        if ($header === '') {
            $header = (string) $request->header('X-Doktor-Token', '');
        }
        if ($header === '') {
            return response()->json(['success' => false, 'message' => 'Oturum gerekli.'], 401);
        }

        $row = DoktorApiToken::findByPlainToken($header);
        if (! $row) {
            return response()->json(['success' => false, 'message' => 'Geçersiz veya süresi dolmuş oturum.'], 401);
        }

        $row->loadMissing('doktor');
        $doktor = $row->doktor;
        if (! $row->isValid() || ! $doktor || ! $doktor->aktif_mi) {
            return response()->json(['success' => false, 'message' => 'Geçersiz veya süresi dolmuş oturum.'], 401);
        }

        $row->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('auth_doktor', $doktor);
        $request->attributes->set('auth_doktor_token', $row);

        if (! $this->pathBypassesMembership($request) && ! app()->environment('testing')) {
            $block = $this->membershipBlock($doktor);
            if ($block !== null) {
                return response()->json([
                    'success' => false,
                    'code' => $block['code'],
                    'message' => $block['message'],
                    'upgrade_url' => url('/hekim/paket-sec'),
                ], 403);
            }
        }

        return $next($request);
    }

    protected function pathBypassesMembership(Request $request): bool
    {
        $path = '/'.ltrim($request->path(), '/');
        // api/mobile/v1/doctor/...
        foreach ($this->membershipBypass as $fragment) {
            if (str_contains($path, '/doctor'.$fragment) || str_contains($path, $fragment)) {
                // Avoid matching /clinic/* as /packages accidentally
                if ($fragment === '/packages' && str_contains($path, '/clinic')) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @return array{code: string, message: string}|null
     */
    protected function membershipBlock($doktor): ?array
    {
        if (method_exists($doktor, 'klinikteMi') && $doktor->klinikteMi()) {
            $klinik = $doktor->klinik;
            if (! $klinik) {
                return ['code' => 'clinic_missing', 'message' => 'Klinik kaydı bulunamadı.'];
            }
            if (! $klinik->aktif_mi) {
                return ['code' => 'clinic_inactive', 'message' => 'Kliniğinizin üyeliği pasif.'];
            }
            if ($klinik->uyelik_bitis && $klinik->uyelik_bitis->isPast()) {
                return [
                    'code' => 'membership_expired',
                    'message' => 'Klinik üyelik süresi dolmuş. Klinik sahibi web’den PayTR ile yenilemeli.',
                ];
            }

            return null;
        }

        if (is_null($doktor->paket_id)) {
            return [
                'code' => 'no_package',
                'message' => 'Devam etmek için paket seçin. Ödeme web üzerinden PayTR ile yapılır.',
            ];
        }

        if ($doktor->uyelik_bitis && $doktor->uyelik_bitis->isPast()) {
            $wasTrial = $doktor->odeme_periyodu === 'deneme';

            return [
                'code' => 'membership_expired',
                'message' => $wasTrial
                    ? 'Ücretsiz denemeniz bitti. Web’den paket seçip PayTR ile ödeyin.'
                    : 'Üyelik süreniz dolmuş. Web’den PayTR ile yenileyin.',
            ];
        }

        return null;
    }
}
