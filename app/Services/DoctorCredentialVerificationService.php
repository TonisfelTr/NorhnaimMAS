<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DoctorCredential;
use Illuminate\Support\Facades\Http;

final class DoctorCredentialVerificationService
{
    public function verify(DoctorCredential $credential): array
    {
        /*
         * Сейчас личный кабинет готов к программной проверке,
         * но не делает вид, что проверил документ без официального источника.
         *
         * После подключения N3/ЕГИСЗ или другого официального шлюза
         * достаточно прописать endpoint/token в config/doctor_credentials.php.
         */

        $provider = $credential->kind === DoctorCredential::KIND_ACCREDITATION
            ? 'frmr'
            : 'frdo';

        $config = (array) config(
            'doctor_credentials.providers.' . $provider,
            []
        );

        $endpoint = trim(
            (string) ($config['endpoint'] ?? '')
        );

        if ($endpoint === '') {
            return [
                'status' => DoctorCredential::STATUS_UNAVAILABLE,
                'source' => $provider,
                'message' => $provider === 'frmr'
                    ? 'Официальный источник ФРМР/ЕГИСЗ пока не подключён. Документ сохранён, но автоматическая проверка не выполнялась.'
                    : 'Официальный источник ФИС ФРДО пока не подключён. Документ сохранён, но автоматическая проверка не выполнялась.',
                'payload' => null,
                'verified_at' => null,
            ];
        }

        try {
            $request = Http::acceptJson()
                ->asJson()
                ->connectTimeout(
                    (int) ($config['connect_timeout'] ?? 5)
                )
                ->timeout(
                    (int) ($config['timeout'] ?? 20)
                );

            $token = trim(
                (string) ($config['token'] ?? '')
            );

            if ($token !== '') {
                $request = $request->withToken($token);
            }

            $response = $request->post(
                $endpoint,
                $this->payload($credential, $provider)
            );
        } catch (\Throwable $e) {
            return [
                'status' => DoctorCredential::STATUS_UNAVAILABLE,
                'source' => $provider,
                'message' => 'Не удалось подключиться к сервису проверки: '
                    . mb_substr($e->getMessage(), 0, 500),
                'payload' => null,
                'verified_at' => null,
            ];
        }

        if (!$response->successful()) {
            return [
                'status' => DoctorCredential::STATUS_UNAVAILABLE,
                'source' => $provider,
                'message' => 'Сервис проверки вернул HTTP '
                    . $response->status() . '.',
                'payload' => [
                    'http_status' => $response->status(),
                ],
                'verified_at' => null,
            ];
        }

        $data = $response->json();

        if (!is_array($data)) {
            return [
                'status' => DoctorCredential::STATUS_MANUAL_REVIEW,
                'source' => $provider,
                'message' => 'Сервис проверки вернул неизвестный формат ответа.',
                'payload' => null,
                'verified_at' => null,
            ];
        }

        $status = match ((string) ($data['status'] ?? '')) {
            'verified',
            'valid',
            'found' =>
                DoctorCredential::STATUS_VERIFIED,

            'not_found' =>
                DoctorCredential::STATUS_NOT_FOUND,

            'mismatch',
            'conflict' =>
                DoctorCredential::STATUS_MISMATCH,

            default =>
                DoctorCredential::STATUS_MANUAL_REVIEW,
        };

        return [
            'status' => $status,
            'source' => $provider,
            'message' => trim(
                (string) (
                    $data['message']
                    ?? $this->defaultMessage($status)
                )
            ),
            'payload' => $data,
            'verified_at' => $status === DoctorCredential::STATUS_VERIFIED
                ? now()
                : null,
        ];
    }

    private function payload(
        DoctorCredential $credential,
        string $provider
    ): array {
        $doctor = $credential->doctor;

        return [
            'provider' => $provider,
            'doctor' => [
                'surname' => $doctor->surname,
                'name' => $doctor->name,
                'patronym' => $doctor->patronym,
                'birth_date' => optional($doctor->birth_at)
                    ->format('Y-m-d'),
                'email' => $doctor->email,
            ],
            'credential' => [
                'kind' => $credential->kind,
                'title' => $credential->title,
                'series' => $credential->series,
                'number' => $credential->number,
                'issued_at' => optional($credential->issued_at)
                    ->format('Y-m-d'),
                'valid_until' => optional($credential->valid_until)
                    ->format('Y-m-d'),
                'issuer' => $credential->issuer,
                'speciality' => $credential->speciality,
                'registry_number' => $credential->registry_number,
            ],
        ];
    }

    private function defaultMessage(string $status): string
    {
        return match ($status) {
            DoctorCredential::STATUS_VERIFIED =>
                'Сведения подтверждены официальным источником.',

            DoctorCredential::STATUS_NOT_FOUND =>
                'Сведения не найдены в официальном источнике.',

            DoctorCredential::STATUS_MISMATCH =>
                'Найдены расхождения с официальным источником.',

            default =>
                'Результат требует дополнительной проверки.',
        };
    }
}
