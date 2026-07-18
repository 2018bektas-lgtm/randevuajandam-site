<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Misafir GET HTML yanıtlarını kısa süre cache'ler (TTFB + PHP yükü).
 * Auth, AJAX, flash, POST sayfaları hariç.
 */
class CachePublicGet
{
    /** @var list<string> */
    protected array $paths = [
        '/',
        '/doktorlar',
        '/paketler',
        '/blog',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldCache($request)) {
            return $next($request);
        }

        $key = $this->cacheKey($request);

        /** @var array{body: string, content_type: string}|null $hit */
        $hit = Cache::get($key);
        if (is_array($hit) && isset($hit['body'])) {
            return response($hit['body'], 200)
                ->header('Content-Type', $hit['content_type'] ?? 'text/html; charset=UTF-8')
                ->header('X-Page-Cache', 'HIT')
                ->header('Cache-Control', 'public, max-age=30, s-maxage=60, stale-while-revalidate=120');
        }

        /** @var Response $response */
        $response = $next($request);

        if ($response->getStatusCode() === 200) {
            $contentType = (string) $response->headers->get('Content-Type', '');
            if (str_contains($contentType, 'text/html') || $contentType === '') {
                $body = $response->getContent();
                if (is_string($body) && $body !== '') {
                    Cache::put($key, [
                        'body' => $body,
                        'content_type' => $contentType !== '' ? $contentType : 'text/html; charset=UTF-8',
                    ], now()->addSeconds(45));
                    $response->headers->set('X-Page-Cache', 'MISS');
                    $response->headers->set('Cache-Control', 'public, max-age=30, s-maxage=60, stale-while-revalidate=120');
                }
            }
        }

        return $response;
    }

    protected function shouldCache(Request $request): bool
    {
        if ($request->method() !== 'GET') {
            return false;
        }
        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }
        // Oturumlu kullanıcıda kişiselleştirme / CSRF
        if (Auth::guard('hasta')->check()
            || Auth::guard('doktor')->check()
            || Auth::guard('yonetici')->check()
            || Auth::guard('personel')->check()) {
            return false;
        }
        // Flash mesaj varsa cache yok
        if ($request->session()->has('basarili')
            || $request->session()->has('hata')
            || $request->session()->has('status')
            || $request->session()->has('errors')) {
            return false;
        }

        $path = '/'.ltrim($request->path(), '/');
        if ($path === '//') {
            $path = '/';
        }

        foreach ($this->paths as $allowed) {
            if ($path === $allowed || str_starts_with($path, $allowed.'/')) {
                // Blog detay vb. de cache'lenebilir (public)
                return true;
            }
        }

        return false;
    }

    protected function cacheKey(Request $request): string
    {
        return 'public_html:v1:'.sha1($request->fullUrl());
    }
}
