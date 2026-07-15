<?php

namespace App\Http\Controllers;

use App\Models\Doktor;
use App\Models\Yonetici;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class TwoFactorController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactor
    ) {}

    /**
     * Challenge form after password login.
     */
    public function challengeForm(Request $request)
    {
        if (! $request->session()->has('two_factor.pending_id')) {
            return redirect()->to($this->loginRouteForGuard($request->session()->get('two_factor.guard')));
        }

        $guard = (string) $request->session()->get('two_factor.guard', 'doktor');

        return view('auth.two_factor_challenge', compact('guard'));
    }

    /**
     * Verify TOTP / recovery code and complete login.
     */
    public function challengeVerify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:20'],
        ], [
            'code.required' => 'Doğrulama kodu zorunludur.',
        ]);

        $pendingId = $request->session()->get('two_factor.pending_id');
        $guard = (string) $request->session()->get('two_factor.guard', 'doktor');
        $remember = (bool) $request->session()->get('two_factor.remember', false);

        if (! $pendingId) {
            return redirect()->to($this->loginRouteForGuard($guard))
                ->withErrors(['code' => 'Oturum süresi doldu. Lütfen tekrar giriş yapın.']);
        }

        $throttleKey = '2fa-challenge:'.$guard.':'.$pendingId.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 8)) {
            $s = RateLimiter::availableIn($throttleKey);

            return back()->withErrors(['code' => "Çok fazla deneme. {$s} saniye sonra tekrar deneyin."]);
        }

        $user = $this->resolveUser($guard, (int) $pendingId);
        if (! $user || ! method_exists($user, 'hasTwoFactorEnabled') || ! $user->hasTwoFactorEnabled()) {
            $request->session()->forget(['two_factor.pending_id', 'two_factor.guard', 'two_factor.remember']);

            return redirect()->to($this->loginRouteForGuard($guard))
                ->withErrors(['e_posta' => 'İki adımlı doğrulama bulunamadı. Tekrar giriş yapın.']);
        }

        if (! $this->twoFactor->verifyUser($user, $request->input('code'))) {
            RateLimiter::hit($throttleKey, 300);

            return back()->withErrors(['code' => 'Kod hatalı. Authenticator veya yedek kodu kontrol edin.']);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->forget(['two_factor.pending_id', 'two_factor.guard', 'two_factor.remember']);
        $request->session()->regenerate();

        Auth::guard($guard)->login($user, $remember);

        return redirect()->intended($this->homeRouteForGuard($guard));
    }

    public function challengeCancel(Request $request)
    {
        $guard = (string) $request->session()->get('two_factor.guard', 'doktor');
        $request->session()->forget(['two_factor.pending_id', 'two_factor.guard', 'two_factor.remember']);

        return redirect()->to($this->loginRouteForGuard($guard));
    }

    /**
     * Setup page (hekim / yönetim).
     */
    public function setupForm(Request $request)
    {
        $user = $this->authenticatedUser($request);
        if (! $user) {
            abort(403);
        }

        $enabled = $user->hasTwoFactorEnabled();
        $setup = null;
        if (! $enabled) {
            $setup = $this->twoFactor->beginSetup($user);
        }

        $guard = Auth::guard('doktor')->check() ? 'doktor' : 'yonetici';
        $layout = $guard === 'doktor' ? 'hekim.layout' : 'yonetim.layout';

        return view('auth.two_factor_setup', [
            'user' => $user,
            'enabled' => $enabled,
            'setup' => $setup,
            'guard' => $guard,
            'layout' => $layout,
        ]);
    }

    public function setupConfirm(Request $request)
    {
        $user = $this->authenticatedUser($request);
        if (! $user) {
            abort(403);
        }

        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:12'],
        ], [
            'code.required' => 'Authenticator kodu zorunludur.',
        ]);

        $recovery = $this->twoFactor->confirmSetup($user, $request->input('code'));
        if ($recovery === null) {
            return back()->withErrors(['code' => 'Kod doğrulanamadı. Uygulamadaki 6 haneli kodu girin.']);
        }

        return redirect()
            ->route($this->setupRouteName())
            ->with('basarili', 'İki adımlı doğrulama açıldı. Yedek kodları güvenli bir yere kaydedin.')
            ->with('two_factor_recovery_codes', $recovery);
    }

    public function disable(Request $request)
    {
        $user = $this->authenticatedUser($request);
        if (! $user) {
            abort(403);
        }

        $request->validate([
            'sifre' => ['required', 'string'],
            'code' => ['required', 'string', 'min:6', 'max:20'],
        ], [
            'sifre.required' => 'Mevcut şifreniz gerekli.',
            'code.required' => 'Authenticator veya yedek kod gerekli.',
        ]);

        if (! Hash::check($request->input('sifre'), $user->sifre)) {
            return back()->withErrors(['sifre' => 'Şifre hatalı.']);
        }

        if (! $this->twoFactor->verifyUser($user, $request->input('code'))) {
            return back()->withErrors(['code' => 'Doğrulama kodu hatalı.']);
        }

        $this->twoFactor->disable($user);

        return redirect()
            ->route($this->setupRouteName())
            ->with('basarili', 'İki adımlı doğrulama kapatıldı.');
    }

    public function regenerateRecovery(Request $request)
    {
        $user = $this->authenticatedUser($request);
        if (! $user || ! $user->hasTwoFactorEnabled()) {
            abort(403);
        }

        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:20'],
        ]);

        // regenerate must use TOTP only (not consume recovery)
        $secret = (string) $user->two_factor_secret;
        if (! $this->twoFactor->verify($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Authenticator kodu hatalı.']);
        }

        $codes = $this->twoFactor->generateRecoveryCodes();
        $user->forceFill(['two_factor_recovery_codes' => $codes])->save();

        return redirect()
            ->route($this->setupRouteName())
            ->with('basarili', 'Yeni yedek kodlar oluşturuldu. Eski kodlar geçersiz.')
            ->with('two_factor_recovery_codes', $codes);
    }

    protected function authenticatedUser(Request $request): Doktor|Yonetici|null
    {
        if (Auth::guard('doktor')->check()) {
            return Auth::guard('doktor')->user();
        }
        if (Auth::guard('yonetici')->check()) {
            return Auth::guard('yonetici')->user();
        }

        return null;
    }

    protected function resolveUser(string $guard, int $id): Doktor|Yonetici|null
    {
        return match ($guard) {
            'doktor' => Doktor::find($id),
            'yonetici' => Yonetici::find($id),
            default => null,
        };
    }

    protected function loginRouteForGuard(?string $guard): string
    {
        return match ($guard) {
            'yonetici' => route('yonetim.giris'),
            default => route('frontend.hekim.giris'),
        };
    }

    protected function homeRouteForGuard(string $guard): string
    {
        return match ($guard) {
            'yonetici' => route('yonetim.panel'),
            default => route('hekim.panel'),
        };
    }

    protected function setupRouteName(): string
    {
        if (Auth::guard('yonetici')->check() && ! Auth::guard('doktor')->check()) {
            return 'yonetim.two-factor';
        }

        return 'hekim.two-factor';
    }

    /**
     * Shared helper used by login controllers.
     */
    public static function beginChallenge(string $guard, int $userId, bool $remember): void
    {
        session([
            'two_factor.pending_id' => $userId,
            'two_factor.guard' => $guard,
            'two_factor.remember' => $remember,
        ]);
    }
}
