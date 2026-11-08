<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

final class ClinicTelemedicineSettings
{
    /** Settings and new connection endpoints require an explicitly saved format. */
    public static function enabled(Clinic $clinic): bool
    {
        $services = $clinic->getRawOriginal('services');

        if (is_string($services)) {
            $services = json_decode($services, true);
        }

        return is_array($services)
            && in_array('telemedicine', $services, true);
    }

    public static function storageReady(): bool
    {
        return Schema::hasColumn('clinics', 'telemedicine_settings')
            && Schema::hasColumn('clinics', 'telemedicine_telemost_token');
    }

    /** Only non-secret data may leave this method or be passed to a view. */
    public static function profile(Clinic $clinic): array
    {
        [$stored, $valid] = self::stored($clinic);
        $provider = self::text($stored['provider'] ?? 'webrtc');
        $mode = self::text($stored['telemost_mode'] ?? 'api');
        $url = self::text(array_key_exists('webrtc_url', $stored)
            ? $stored['webrtc_url']
            : config('clinic_telemedicine.webrtc_url', ''));
        $ciphertext = $clinic->getRawOriginal('telemedicine_telemost_token');
        $hasToken = is_string($ciphertext) && $ciphertext !== '';
        $errors = [];

        if (!$valid) {
            $errors[] = 'Сохранённые настройки телемедицины повреждены. Заполните форму и сохраните её заново.';
        }

        if (!in_array($provider, ['webrtc', 'telemost'], true)) {
            $errors[] = 'Выберите способ проведения консультаций.';
        }

        if ($provider === 'webrtc') {
            if (!self::validWebrtcUrl($url)) {
                $errors[] = 'Укажите HTTPS-адрес страницы подключения к собственной видеосвязи.';
            }

            $secret = self::signalingSecret($clinic);

            if (strlen($secret) < 64 || strlen($secret) % 2 !== 0 || !ctype_xdigit($secret)) {
                $errors[] = 'Сервер собственной видеосвязи ещё не настроен. Обратитесь к администратору сервера.';
            }
        }

        if ($provider === 'telemost') {
            if (!in_array($mode, ['api', 'manual'], true)) {
                $errors[] = 'Выберите способ создания встреч в Яндекс Телемосте.';
            } elseif ($mode === 'api' && !$hasToken) {
                $errors[] = 'Для автоматического создания встреч сохраните OAuth-токен Яндекс Телемоста.';
            }
        }

        return [
            'provider' => $provider,
            'webrtc_url' => $url,
            'telemost_mode' => $mode,
            'has_telemost_token' => $hasToken,
            'settings_errors' => $errors,
        ];
    }

    /** Assigns attributes only. The caller owns the clinic transaction and save. */
    public static function store(Clinic $clinic, array $settings, #[\SensitiveParameter] ?string $token, bool $clearToken): void
    {
        [$stored] = self::stored($clinic);

        foreach (['provider', 'webrtc_url', 'telemost_mode'] as $field) {
            $stored[$field] = $settings[$field];
        }

        $clinic->setAttribute('telemedicine_settings', json_encode(
            $stored,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ));

        if ($clearToken) {
            $clinic->setAttribute('telemedicine_telemost_token', null);
        } elseif ($token !== null && $token !== '') {
            try {
                $ciphertext = Crypt::encryptString($token);
            } catch (\Throwable) {
                // Do not chain an encryption exception with the secret in its trace.
                throw ValidationException::withMessages([
                    'telemost_token' => 'Не удалось защитить токен для хранения. Обратитесь к администратору системы.',
                ]);
            }
            $clinic->setAttribute('telemedicine_telemost_token', $ciphertext);
        }
    }

    /** Runtime-only credential accessor. Never add its result to profile or logs. */
    public static function telemostToken(Clinic $clinic): string
    {
        $ciphertext = $clinic->getRawOriginal('telemedicine_telemost_token');

        if (!is_string($ciphertext) || $ciphertext === '') {
            return '';
        }

        try {
            return Crypt::decryptString($ciphertext);
        } catch (DecryptException) {
            throw ValidationException::withMessages([
                'telemedicine' => 'Не удалось прочитать сохранённый токен Телемоста. Ответственный сотрудник должен сохранить новый токен.',
            ]);
        }
    }

    /** The shared signaling server uses its existing global hex-encoded key. */
    public static function signalingSecret(Clinic $clinic): string
    {
        $value = config('clinic_telemedicine.signaling_secret', '');

        return is_string($value) ? $value : '';
    }

    public static function validWebrtcUrl(string $url): bool
    {
        if ($url === '' || strlen($url) > 2048 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($url);

        return is_array($parts)
            && strtolower((string) ($parts['scheme'] ?? '')) === 'https'
            && !empty($parts['host'])
            && !isset($parts['user'])
            && !isset($parts['pass'])
            && !isset($parts['query'])
            && !isset($parts['fragment']);
    }

    private static function stored(Clinic $clinic): array
    {
        $value = $clinic->getRawOriginal('telemedicine_settings');

        if ($value === null || $value === '') {
            return [[], true];
        }

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return is_array($value) ? [$value, true] : [[], false];
    }

    private static function text(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }
}
