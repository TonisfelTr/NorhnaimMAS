<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/** SMS.RU provider; never logs phones, links or API credentials. */
final class TelemedicineSmsGateway
{
    public function configured(): bool
    {
        return config('telemedicine_reminders.sms_driver') === 'smsru'
            && trim((string) config('telemedicine_reminders.smsru_api_id')) !== '';
    }

    public static function normalizePhone(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        $digits = preg_replace('/\D/', '', $raw);
        if (!is_string($digits)) return null;
        if (strlen($digits) === 11 && str_starts_with($digits, '8')) {
            $digits = '7' . substr($digits, 1);
        }
        // E.164 digits without +, as required by SMS.RU.
        return preg_match('/\A[1-9][0-9]{7,14}\z/D', $digits) === 1 ? $digits : null;
    }

    /** @throws RuntimeException On confirmed refusal or ambiguous transport failure. */
    public function send(string $number, string $message): void
    {
        if (!$this->configured()) {
            throw new RuntimeException('SMS provider is disabled');
        }
        $phone = self::normalizePhone($number);
        if (!$phone) throw new RuntimeException('Invalid SMS recipient number');

        $params = [
            'api_id' => (string) config('telemedicine_reminders.smsru_api_id'),
            'to' => $phone,
            'msg' => $message,
            'json' => 1,
        ];
        $sender = trim((string) config('telemedicine_reminders.smsru_sender'));
        if ($sender !== '') $params['from'] = $sender;

        $response = Http::asForm()->connectTimeout(3)->timeout(8)
            ->withoutRedirecting()->post('https://sms.ru/sms/send', $params);
        if (!$response->successful()) {
            throw new RuntimeException('SMS gateway HTTP failure');
        }
        $data = $response->json();
        $sms = is_array($data) ? ($data['sms'][$phone] ?? null) : null;
        if (!is_array($data) || ($data['status'] ?? null) !== 'OK'
            || !is_array($sms) || ($sms['status'] ?? null) !== 'OK') {
            throw new RuntimeException('SMS gateway did not accept the message');
        }
    }
}
