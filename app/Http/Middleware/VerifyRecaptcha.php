<?php

namespace App\Http\Middleware;

use App\Services\RecaptchaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRecaptcha
{
    public function handle(Request $request, Closure $next, string $action = ''): Response
    {
        $result = app(RecaptchaService::class)->verify(
            $request->input('recaptcha_token'),
            $action,
            $request->ip()
        );

        if (! $result['ok']) {
            return back()
                ->withInput($request->except(['sifre', 'sifre_confirmation', 'recaptcha_token']))
                ->withErrors(['recaptcha_token' => $result['message'] ?? 'Güvenlik doğrulaması başarısız.']);
        }

        return $next($request);
    }
}
