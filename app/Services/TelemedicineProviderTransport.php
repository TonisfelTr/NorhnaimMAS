<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

/** Protocol adapter only. Authorization and appointment checks belong to the session service. */
final class TelemedicineProviderTransport
{
    public const TELEMOST_CREATE_URL = 'https://cloud-api.yandex.net/v1/telemost-api/conferences';
    private const WEBRTC_AUDIENCE = 'norhnaim-webrtc-dev-v1';

    public static function webrtcPage(string $url): string
    {
        $url = trim($url);
        $parts = parse_url($url);
        if (strlen($url) > 2048 || filter_var($url, FILTER_VALIDATE_URL) === false
            || !is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https'
            || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])
            || isset($parts['query']) || isset($parts['fragment'])) {
            throw new InvalidArgumentException('Укажите полный HTTPS-адрес страницы WebRTC без параметров, фрагмента и учётных данных.');
        }
        return $url;
    }

    public static function telemostJoinUrl(#[\SensitiveParameter] string $url): string
    {
        $url = trim($url);
        // Official documented conference-link shape; no arbitrary redirects or URL fetches.
        if (strlen($url) > 2048 || !preg_match('~\Ahttps://telemost(?:\.360)?\.yandex\.ru/j/[0-9]+\z~', $url)) {
            throw new InvalidArgumentException('Нужна ссылка отдельной встречи вида https://telemost.yandex.ru/j/1234567890.');
        }
        return $url;
    }

    public static function telemostUrlHash(#[\SensitiveParameter] string $url): string
    {
        $url = self::telemostJoinUrl($url);
        // Both official hosts refer to the same namespace of conference IDs.
        return hash('sha256', str_replace('https://telemost.360.yandex.ru/', 'https://telemost.yandex.ru/', $url));
    }

    public static function signingKey(#[\SensitiveParameter] string $hex): string
    {
        if (strlen($hex) < 64 || strlen($hex) % 2 !== 0 || !ctype_xdigit($hex)) {
            throw new InvalidArgumentException('На сервере не настроен общий ключ подписи WebRTC.');
        }
        $key = hex2bin($hex);
        if ($key === false) throw new InvalidArgumentException('Некорректный ключ подписи WebRTC.');
        return $key;
    }

    /** The existing Python server uses a two-part HMAC invitation, NOT a JWT. */
    public static function webrtcToken(
        #[\SensitiveParameter] string $secretHex,
        string $room,
        int $appointmentId,
        string $role,
        int $expiresAt,
        int $appointmentStart,
        ?int $issuedAt = null
    ): string {
        $issuedAt ??= time();
        $ttl = $expiresAt - $issuedAt;
        if (!preg_match('/\A[A-Za-z0-9_-]{16,100}\z/', $room)
            || !in_array($role, ['doctor', 'patient'], true) || $appointmentId <= 0 || $appointmentStart <= 0
            || $ttl < 60 || $ttl > 14400) {
            throw new InvalidArgumentException('Некорректные параметры приглашения WebRTC.');
        }
        $payload = [
            'aud' => self::WEBRTC_AUDIENCE,
            'room' => $room,
            'role' => $role,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'jti' => bin2hex(random_bytes(18)),
            'registry_record_id' => $appointmentId,
            'appointment_start' => $appointmentStart,
        ];
        ksort($payload);
        $encoded = self::base64Url(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $encoded, self::signingKey($secretHex), true);
        return $encoded . '.' . self::base64Url($signature);
    }

    /** Exactly one request; an uncertain result must not trigger an automatic duplicate meeting. */
    public function createTelemost(#[\SensitiveParameter] string $oauthToken): array
    {
        if ($oauthToken === '' || preg_match('/[\r\n]/', $oauthToken)) {
            throw new RuntimeException('Не настроен действующий OAuth-токен Телемоста.');
        }
        try {
            $response = Http::withHeaders(['Authorization' => 'OAuth ' . $oauthToken])
                ->acceptJson()->asJson()->withoutRedirecting()->connectTimeout(5)
                ->timeout(max(5, min(30, (int) config('clinic_telemedicine.api_timeout_seconds', 20))))
                ->post(self::TELEMOST_CREATE_URL, [
                    'waiting_room_level' => 'ADMINS',
                    'is_auto_summarization_enabled' => false,
                ]);
        } catch (\Throwable) {
            // Do not retain HTTP exceptions: request headers may contain the OAuth secret.
            throw new RuntimeException('Результат создания встречи не подтверждён. Проверьте Телемост.');
        }
        if ($response->status() !== 201) {
            throw new RuntimeException('Телемост не подтвердил создание встречи. Проверьте настройки интеграции.');
        }
        $data = $response->json();
        if (!is_array($data) || !is_string($data['id'] ?? null)
            || trim($data['id']) === '' || strlen($data['id']) > 128
            || !is_string($data['join_url'] ?? null)) {
            throw new RuntimeException('Телемост вернул неполные данные встречи. Проверьте созданную встречу вручную.');
        }
        try {
            $url = self::telemostJoinUrl($data['join_url']);
        } catch (InvalidArgumentException) {
            throw new RuntimeException('Адрес встречи в ответе Телемоста не распознан. Проверьте встречу вручную.');
        }
        return ['id' => $data['id'], 'join_url' => $url];
    }

    private static function base64Url(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
