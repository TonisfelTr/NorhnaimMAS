<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Publish only opaque, role-specific Norhnaim entry URLs to clck.ru.
 * Never send a WebRTC token, an invitation signature, a patient ID, or
 * an external provider join URL to the URL shortener.
 */
final class TelemedicineShortLinkService
{
    public function signature(string $meeting, string $role, int $startEpoch): string
    {
        return substr(hash_hmac('sha256',
            $role . ':' . $meeting . ':' . $startEpoch,
            (string) config('app.key')
        ), 0, 32);
    }

    public function url(string $meeting, string $role, int $startEpoch): ?string
    {
        if (!in_array($role, ['doctor', 'patient'], true)
            || preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\z/i', $meeting) !== 1
            || $startEpoch <= 0) {
            return null;
        }

        // Use configured public APP_URL, not localhost HTTP_HOST from an internal request.
        $base = rtrim((string) config('app.url'), '/');
        $parts = parse_url($base);
        if (!is_array($parts) || ($parts['scheme'] ?? '') !== 'https'
            || !isset($parts['host']) || (isset($parts['user']) || isset($parts['pass']))
            || isset($parts['query']) || isset($parts['fragment'])) {
            return null;
        }
        $host = strtolower((string) $parts['host']);
        if ($host === 'localhost' || str_ends_with($host, '.local')
            || str_ends_with($host, '.test') || str_ends_with($host, '.localhost')) {
            return null;
        }
        if (filter_var($host, FILTER_VALIDATE_IP)
            && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }
        if (!filter_var($base, FILTER_VALIDATE_URL)) {
            return null;
        }

        $route = $role === 'doctor'
            ? 'doctors.telemedicine-session.short'
            : 'patients.telemedicine.short';
        $path = route($route, [
            'meeting' => $meeting,
            'stamp' => $this->signature($meeting, $role, $startEpoch),
        ], false);
        $target = $base . $path;
        if (strlen($target) > 2000) {
            return null;
        }

        $cacheKey = 'norhnaim:telemedicine:clck:' . hash('sha256', $target);
        try {
            $cached = Cache::get($cacheKey);
            if (is_string($cached)) {
                return $cached !== '' ? $cached : null;
            }
            $response = Http::asForm()
                ->connectTimeout(2)
                ->timeout(4)
                ->withoutRedirecting()
                ->post('https://clck.ru/--', ['url' => $target]);
            $short = $response->successful() ? trim($response->body()) : '';
            if (!preg_match('~\Ahttps://clck\.ru/[A-Za-z0-9_-]{2,128}\z~', $short)) {
                // Do not expose arbitrary response URLs as trusted shortcuts.
                $short = '';
            }
            Cache::put($cacheKey, $short, $short !== '' ? now()->addDay() : now()->addMinute());
            return $short !== '' ? $short : null;
        } catch (Throwable) {
            // Clck.ru is an optional convenience. Failure never disables
            // the protected native POST join endpoints.
            return null;
        }
    }
}
