<?php

namespace App\Services;

use App\Mail\PatientLinkVerificationMail;
use App\Models\Patient;
use App\Models\PatientLinkRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PatientLinkingService
{
    public function submit(User $user, array $payload): PatientLinkRequest
    {
        if ($user->patient()->exists()) {
            throw ValidationException::withMessages([
                'identity' => 'К этой учётной записи уже привязана медицинская карта.',
            ]);
        }

        $candidates = $this->findCandidates($payload);
        $candidate = $candidates->count() === 1
            ? $candidates->first()
            : null;

        $verificationEmail = null;
        $code = null;

        $candidateIsAdult = $candidate
            && $candidate->getRawOriginal('birth_at')
            && \Carbon\Carbon::parse(
                $candidate->getRawOriginal('birth_at')
            )->age >= 18;

        if (
            $candidateIsAdult
            && filled($candidate->email)
            && (
                blank($candidate->user_id)
                || (int) $candidate->user_id === (int) $user->id
            )
            && filter_var($candidate->email, FILTER_VALIDATE_EMAIL)
        ) {
            $verificationEmail = mb_strtolower(trim((string) $candidate->email));
            $code = (string) random_int(100000, 999999);
        }

        $linkRequest = DB::transaction(function () use (
            $user,
            $payload,
            $candidate,
            $verificationEmail,
            $code
        ): PatientLinkRequest {
            $lockedUser = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedUser->patient()->exists()) {
                throw ValidationException::withMessages([
                    'identity' => 'К этой учётной записи уже привязана медицинская карта.',
                ]);
            }

            $linkRequest = PatientLinkRequest::query()
                ->where('user_id', $user->id)
                ->whereIn('status', [
                    PatientLinkRequest::STATUS_PENDING_EMAIL,
                    PatientLinkRequest::STATUS_PENDING_REVIEW,
                ])
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $values = [
                'patient_id' => $candidate?->id,
                'status' => $verificationEmail && $code
                    ? PatientLinkRequest::STATUS_PENDING_EMAIL
                    : PatientLinkRequest::STATUS_PENDING_REVIEW,
                'payload' => $payload,
                'verification_email' => $verificationEmail,
                'verification_code_hash' => $code
                    ? Hash::make($code)
                    : null,
                'verification_expires_at' => $code
                    ? now()->addMinutes(15)
                    : null,
                'verification_attempts' => 0,
                'verification_channel' => 'email',
                'verified_at' => null,
                'reviewed_by_user_id' => null,
                'reviewed_at' => null,
                'review_note' => null,
            ];

            if ($linkRequest) {
                $linkRequest->fill($values);
                $linkRequest->save();

                return $linkRequest;
            }

            return PatientLinkRequest::query()->create(array_merge(
                $values,
                ['user_id' => $user->id]
            ));
        });

        if ($verificationEmail && $code) {
            try {
                Mail::to($verificationEmail)->send(
                    new PatientLinkVerificationMail($code)
                );
            } catch (Throwable $exception) {
                report($exception);

                $linkRequest->forceFill([
                    'status' => PatientLinkRequest::STATUS_PENDING_REVIEW,
                    'verification_email' => null,
                    'verification_code_hash' => null,
                    'verification_expires_at' => null,
                    'review_note' => 'Не удалось отправить код подтверждения. Требуется ручная проверка.',
                ])->save();
            }
        }

        return $linkRequest->fresh(['user', 'patient']);
    }

    public function verify(
        User $user,
        int $requestId,
        string $code
    ): string {
        return DB::transaction(function () use (
            $user,
            $requestId,
            $code
        ): string {
            $account = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $linkRequest = PatientLinkRequest::query()
                ->whereKey($requestId)
                ->where('user_id', $account->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($linkRequest->status !== PatientLinkRequest::STATUS_PENDING_EMAIL) {
                return 'manual';
            }

            if (
                ! $linkRequest->verification_expires_at
                || $linkRequest->verification_expires_at->isPast()
            ) {
                $linkRequest->forceFill([
                    'status' => PatientLinkRequest::STATUS_PENDING_REVIEW,
                    'verification_code_hash' => null,
                    'verification_expires_at' => null,
                    'review_note' => 'Истёк срок действия кода подтверждения.',
                ])->save();

                return 'manual';
            }

            if ((int) $linkRequest->verification_attempts >= 5) {
                $linkRequest->forceFill([
                    'status' => PatientLinkRequest::STATUS_PENDING_REVIEW,
                    'verification_code_hash' => null,
                    'verification_expires_at' => null,
                    'review_note' => 'Превышено количество попыток ввода кода.',
                ])->save();

                return 'manual';
            }

            if (
                ! $linkRequest->verification_code_hash
                || ! Hash::check($code, $linkRequest->verification_code_hash)
            ) {
                $attempts = (int) $linkRequest->verification_attempts + 1;

                $linkRequest->forceFill([
                    'verification_attempts' => $attempts,
                    'status' => $attempts >= 5
                        ? PatientLinkRequest::STATUS_PENDING_REVIEW
                        : PatientLinkRequest::STATUS_PENDING_EMAIL,
                    'verification_code_hash' => $attempts >= 5
                        ? null
                        : $linkRequest->verification_code_hash,
                    'verification_expires_at' => $attempts >= 5
                        ? null
                        : $linkRequest->verification_expires_at,
                    'review_note' => $attempts >= 5
                        ? 'Превышено количество попыток ввода кода.'
                        : $linkRequest->review_note,
                ])->save();

                return $attempts >= 5 ? 'manual' : 'invalid';
            }

            $patient = Patient::query()
                ->whereKey($linkRequest->patient_id)
                ->lockForUpdate()
                ->first();

            if (! $patient) {
                $linkRequest->forceFill([
                    'status' => PatientLinkRequest::STATUS_PENDING_REVIEW,
                    'verified_at' => now(),
                    'verification_code_hash' => null,
                    'verification_expires_at' => null,
                    'review_note' => 'Карта не найдена при завершении подтверждения.',
                ])->save();

                return 'manual';
            }

            if (
                ($patient->user_id && (int) $patient->user_id !== (int) $account->id)
                || $account->patient()->where('patients.id', '<>', $patient->id)->exists()
            ) {
                $linkRequest->forceFill([
                    'status' => PatientLinkRequest::STATUS_PENDING_REVIEW,
                    'verified_at' => now(),
                    'verification_code_hash' => null,
                    'verification_expires_at' => null,
                    'review_note' => 'Обнаружена существующая привязка. Требуется ручная проверка.',
                ])->save();

                return 'manual';
            }

            $this->applyMissingData($patient, (array) $linkRequest->payload);

            $patient->user_id = $account->id;
            $patient->save();

            $linkRequest->forceFill([
                'patient_id' => $patient->id,
                'status' => PatientLinkRequest::STATUS_APPROVED,
                'verified_at' => now(),
                'verification_code_hash' => null,
                'verification_expires_at' => null,
                'review_note' => null,
            ])->save();

            return 'linked';
        });
    }

    public function approveManually(
        PatientLinkRequest $request,
        Patient $patient,
        User $actor,
        bool $applySubmittedData = false
    ): void {
        DB::transaction(function () use (
            $request,
            $patient,
            $actor,
            $applySubmittedData
        ): void {
            $account = User::query()
                ->whereKey($request->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedRequest = PatientLinkRequest::query()
                ->whereKey($request->id)
                ->where('user_id', $account->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedRequest->isPending()) {
                throw ValidationException::withMessages([
                    'request' => 'Заявка уже обработана.',
                ]);
            }

            $lockedPatient = Patient::query()
                ->whereKey($patient->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedPatient->user_id
                && (int) $lockedPatient->user_id !== (int) $account->id
            ) {
                throw ValidationException::withMessages([
                    'patient_id' => 'Эта медицинская карта уже привязана к другой учётной записи.',
                ]);
            }

            if (
                $account->patient()
                    ->where('id', '<>', $lockedPatient->id)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'patient_id' => 'К учётной записи уже привязана другая медицинская карта.',
                ]);
            }

            if ($applySubmittedData) {
                $this->applySubmittedData(
                    $lockedPatient,
                    (array) $lockedRequest->payload
                );
            }

            $lockedPatient->user_id = $account->id;
            $lockedPatient->save();

            $lockedRequest->forceFill([
                'patient_id' => $lockedPatient->id,
                'status' => PatientLinkRequest::STATUS_APPROVED,
                'reviewed_by_user_id' => $actor->id,
                'reviewed_at' => now(),
                'verification_code_hash' => null,
                'verification_expires_at' => null,
            ])->save();
        });
    }

    public function rejectManually(
        PatientLinkRequest $request,
        User $actor,
        string $reason
    ): void {
        DB::transaction(function () use ($request, $actor, $reason): void {
            User::query()
                ->whereKey($request->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedRequest = PatientLinkRequest::query()
                ->whereKey($request->id)
                ->where('user_id', $request->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedRequest->isPending()) {
                throw ValidationException::withMessages([
                    'request' => 'Заявка уже обработана.',
                ]);
            }

            $lockedRequest->forceFill([
                'status' => PatientLinkRequest::STATUS_REJECTED,
                'reviewed_by_user_id' => $actor->id,
                'reviewed_at' => now(),
                'review_note' => trim($reason),
                'verification_code_hash' => null,
                'verification_expires_at' => null,
            ])->save();
        });
    }

    public function approveForDoctor(
        PatientLinkRequest $request,
        User $actor
    ): void {
        $doctor = $actor->doctor()->first();

        if (! $doctor) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
        }

        DB::transaction(function () use ($request, $actor, $doctor): void {
            $account = User::query()
                ->whereKey($request->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedRequest = PatientLinkRequest::query()
                ->whereKey($request->id)
                ->where('user_id', $account->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->status !== PatientLinkRequest::STATUS_PENDING_REVIEW) {
                throw ValidationException::withMessages([
                    'request' => 'Заявка уже обработана или ожидает подтверждения по email.',
                ]);
            }

            if (! $lockedRequest->patient_id) {
                throw ValidationException::withMessages([
                    'request' => 'Система не сопоставила заявку с конкретной медицинской картой. Передайте её администрации клиники.',
                ]);
            }

            $patient = Patient::query()
                ->whereKey($lockedRequest->patient_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $patient->doctor_id !== (int) $doctor->id) {
                throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
            }

            if ($patient->user_id && (int) $patient->user_id !== (int) $account->id) {
                throw ValidationException::withMessages([
                    'patient_id' => 'Эта медицинская карта уже привязана к другой учётной записи.',
                ]);
            }

            if ($account->patient()->where('patients.id', '<>', $patient->id)->exists()) {
                throw ValidationException::withMessages([
                    'patient_id' => 'К учётной записи уже привязана другая медицинская карта.',
                ]);
            }

            // Заполняем только пустые поля, не перезаписывая данные медкарты.
            $this->applyMissingData($patient, (array) $lockedRequest->payload);

            $patient->user_id = $account->id;
            $patient->save();

            $lockedRequest->forceFill([
                'patient_id' => $patient->id,
                'status' => PatientLinkRequest::STATUS_APPROVED,
                'reviewed_by_user_id' => $actor->id,
                'reviewed_at' => now(),
                'verification_code_hash' => null,
                'verification_expires_at' => null,
                'review_note' => null,
            ])->save();
        });
    }

    public function rejectForDoctor(
        PatientLinkRequest $request,
        User $actor,
        string $reason
    ): void {
        $doctor = $actor->doctor()->first();

        if (! $doctor) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
        }

        DB::transaction(function () use ($request, $actor, $doctor, $reason): void {
            User::query()
                ->whereKey($request->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedRequest = PatientLinkRequest::query()
                ->whereKey($request->id)
                ->where('user_id', $request->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->status !== PatientLinkRequest::STATUS_PENDING_REVIEW) {
                throw ValidationException::withMessages([
                    'request' => 'Заявка уже обработана или ожидает подтверждения по email.',
                ]);
            }

            if (! $lockedRequest->patient_id) {
                throw ValidationException::withMessages([
                    'request' => 'Заявка не сопоставлена с медицинской картой и должна быть рассмотрена администрацией.',
                ]);
            }

            $patient = Patient::query()
                ->whereKey($lockedRequest->patient_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $patient->doctor_id !== (int) $doctor->id) {
                throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
            }

            $lockedRequest->forceFill([
                'status' => PatientLinkRequest::STATUS_REJECTED,
                'reviewed_by_user_id' => $actor->id,
                'reviewed_at' => now(),
                'review_note' => trim($reason),
                'verification_code_hash' => null,
                'verification_expires_at' => null,
            ])->save();
        });
    }

    private function findCandidates(array $payload): Collection
    {
        $snils = $payload['snils'] ?? null;
        $oms = $payload['oms'] ?? null;
        $documentNumber = $this->normalizeDocument($payload['document_number'] ?? null);

        $query = Patient::query()
            ->whereDate('birth_at', $payload['birth_at'])
            ->whereRaw('LOWER(surname) = ?', [
                mb_strtolower(trim((string) $payload['surname'])),
            ])
            ->whereRaw('LOWER(name) = ?', [
                mb_strtolower(trim((string) $payload['name'])),
            ])
            ->where(function (Builder $builder) use ($payload): void {
                $builder
                    ->whereNull('gender')
                    ->orWhere('gender', $payload['gender']);
            })
            ->where(function (Builder $builder) use ($payload): void {
                $patronym = trim((string) ($payload['patronym'] ?? ''));

                if ($patronym === '') {
                    return;
                }

                $builder
                    ->whereNull('patronym')
                    ->orWhereRaw('LOWER(patronym) = ?', [
                        mb_strtolower($patronym),
                    ]);
            })
            ->where(function (Builder $builder) use (
                $snils,
                $oms,
                $documentNumber,
                $payload
            ): void {
                $hasCondition = false;

                if ($snils) {
                    $builder->whereRaw(
                        "regexp_replace(coalesce(snils, ''), '[^0-9]', '', 'g') = ?",
                        [$snils]
                    );
                    $hasCondition = true;
                }

                if ($documentNumber) {
                    $documentCondition = function (Builder $documentQuery) use (
                        $documentNumber,
                        $payload
                    ): void {
                        $documentQuery
                            ->where('document_type', $payload['document_type'])
                            ->whereRaw(
                                "regexp_replace(upper(coalesce(number, '')), '[^A-Z0-9]', '', 'g') = ?",
                                [$documentNumber]
                            );

                        $series = $this->normalizeDocument(
                            $payload['document_series'] ?? null
                        );

                        if ($series !== null) {
                            $documentQuery->whereRaw(
                                "regexp_replace(upper(coalesce(series, '')), '[^A-Z0-9]', '', 'g') = ?",
                                [$series]
                            );
                        }
                    };

                    if ($hasCondition) {
                        $builder->orWhereHas(
                            'identityDocuments',
                            $documentCondition
                        );
                    } else {
                        $builder->whereHas(
                            'identityDocuments',
                            $documentCondition
                        );
                    }

                    if (($payload['document_type'] ?? null) === 'passport_rf') {
                        $legacy = function (Builder $legacyQuery) use (
                            $documentNumber,
                            $payload
                        ): void {
                            $legacyQuery->whereRaw(
                                "regexp_replace(upper(coalesce(number, '')), '[^A-Z0-9]', '', 'g') = ?",
                                [$documentNumber]
                            );

                            $series = $this->normalizeDocument(
                                $payload['document_series'] ?? null
                            );

                            if ($series !== null) {
                                $legacyQuery->whereRaw(
                                    "regexp_replace(upper(coalesce(serial, '')), '[^A-Z0-9]', '', 'g') = ?",
                                    [$series]
                                );
                            }
                        };

                        $builder->orWhere($legacy);
                    }

                    $hasCondition = true;
                }

                if ($oms) {
                    $omsCondition = function (Builder $omsQuery) use ($oms): void {
                        $omsQuery->whereRaw(
                            "regexp_replace(upper(coalesce(oms, '')), '[^A-Z0-9]', '', 'g') = ?",
                            [$oms]
                        );
                    };

                    if ($hasCondition) {
                        $builder->orWhere($omsCondition);
                    } else {
                        $builder->where($omsCondition);
                    }
                }
            });

        return $query
            ->limit(50)
            ->get()
            ->filter(fn (Patient $patient): bool =>
                $this->identifiersAreCompatible($patient, $payload)
            )
            ->values();
    }

    private function identifiersAreCompatible(
        Patient $patient,
        array $payload
    ): bool {
        $snils = $payload['snils'] ?? null;
        $oms = $payload['oms'] ?? null;
        $documentNumber = $this->normalizeDocument(
            $payload['document_number'] ?? null
        );
        $documentSeries = $this->normalizeDocument(
            $payload['document_series'] ?? null
        );

        $snilsMatch = false;
        $omsMatch = false;
        $documentMatch = false;

        if ($snils && filled($patient->getRawOriginal('snils'))) {
            if ($this->digits($patient->getRawOriginal('snils')) !== $snils) {
                return false;
            }

            $snilsMatch = true;
        }

        if ($oms && filled($patient->getRawOriginal('oms'))) {
            if ($this->normalizeDocument($patient->getRawOriginal('oms')) !== $oms) {
                return false;
            }

            $omsMatch = true;
        }

        if ($documentNumber) {
            $documents = $patient->identityDocuments()->get();

            if ($documents->isNotEmpty()) {
                $documentMatch = $documents->contains(
                    function ($document) use (
                        $payload,
                        $documentNumber,
                        $documentSeries
                    ): bool {
                        if (
                            $document->document_type !== $payload['document_type']
                            || $this->normalizeDocument($document->number) !== $documentNumber
                        ) {
                            return false;
                        }

                        return $documentSeries === null
                            || $this->normalizeDocument($document->series) === $documentSeries;
                    }
                );

                if (! $documentMatch) {
                    return false;
                }
            } elseif (($payload['document_type'] ?? null) === 'passport_rf') {
                $legacyNumber = $this->normalizeDocument(
                    $patient->getRawOriginal('number')
                );
                $legacySeries = $this->normalizeDocument(
                    $patient->getRawOriginal('serial')
                );

                if ($legacyNumber || $legacySeries) {
                    $documentMatch = $legacyNumber === $documentNumber
                        && (
                            $documentSeries === null
                            || $legacySeries === $documentSeries
                        );

                    if (! $documentMatch) {
                        return false;
                    }
                }
            }
        }

        return $snilsMatch || $omsMatch || $documentMatch;
    }

    private function applyMissingData(Patient $patient, array $payload): void
    {
        $this->applyData($patient, $payload, false);
    }

    private function applySubmittedData(Patient $patient, array $payload): void
    {
        $this->applyData($patient, $payload, true);
    }

    private function applyData(
        Patient $patient,
        array $payload,
        bool $overwrite
    ): void {
        $fields = [
            'surname' => $payload['surname'] ?? null,
            'name' => $payload['name'] ?? null,
            'patronym' => $payload['patronym'] ?? null,
            'birth_at' => $payload['birth_at'] ?? null,
            'gender' => $payload['gender'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'email' => $payload['email'] ?? null,
            'birth_place' => $payload['birth_place'] ?? null,
            'address_registration' => $payload['address_registration'] ?? null,
            'address_residence' => $payload['address_residence'] ?? null,
            'snils' => $payload['snils'] ?? null,
            'oms' => $payload['oms'] ?? null,
            'insurance_company' => $payload['insurance_company'] ?? null,
        ];

        foreach ($fields as $field => $value) {
            if (! Schema::hasColumn('patients', $field) || blank($value)) {
                continue;
            }

            if ($overwrite || blank($patient->getRawOriginal($field))) {
                $patient->setAttribute($field, $value);
            }
        }

        $patient->save();

        if (
            blank($payload['document_number'] ?? null)
            || ! Schema::hasTable('patient_identity_documents')
        ) {
            return;
        }

        $type = (string) $payload['document_type'];
        $series = $payload['document_series'] ?? null;
        $number = (string) $payload['document_number'];

        $values = [
            'document_name' => $this->documentName(
                $type,
                $payload['document_name'] ?? null
            ),
            'series' => $series,
            'number' => $number,
            'issued_by' => $payload['document_issued_by'] ?? null,
            'issued_at' => $payload['document_issued_at'] ?? null,
            'department_code' => $payload['document_department_code'] ?? null,
            'is_primary' => true,
            'is_active' => true,
            'updated_at' => now(),
        ];

        $existing = DB::table('patient_identity_documents')
            ->where('patient_id', $patient->id)
            ->where('document_type', $type)
            ->where('number', $number)
            ->first();

        if (! $overwrite) {
            if ($existing) {
                return;
            }

            $hasPrimaryDocument = DB::table('patient_identity_documents')
                ->where('patient_id', $patient->id)
                ->where('is_primary', true)
                ->where('is_active', true)
                ->exists();

            if ($hasPrimaryDocument) {
                return;
            }
        }

        DB::table('patient_identity_documents')
            ->where('patient_id', $patient->id)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->update([
                'is_primary' => false,
                'updated_at' => now(),
            ]);

        if ($existing) {
            DB::table('patient_identity_documents')
                ->where('id', $existing->id)
                ->update($values);

            return;
        }

        DB::table('patient_identity_documents')->insert(array_merge(
            $values,
            [
                'patient_id' => $patient->id,
                'document_type' => $type,
                'created_at' => now(),
            ]
        ));
    }

    private function documentName(string $type, ?string $customName): string
    {
        return match ($type) {
            'passport_rf' => 'Паспорт гражданина РФ',
            'birth_certificate' => 'Свидетельство о рождении',
            'foreign_passport' => 'Паспорт иностранного гражданина',
            'residence_permit' => 'Вид на жительство',
            'temporary_identity' => 'Временное удостоверение личности',
            'foreign_identity' => 'Иностранный документ, удостоверяющий личность',
            default => $customName ?: 'Иной документ',
        };
    }

    private function digits(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }

    private function normalizeDocument(mixed $value): ?string
    {
        $normalized = strtoupper(preg_replace(
            '/[^0-9A-Z]/i',
            '',
            (string) $value
        ));

        return $normalized !== '' ? $normalized : null;
    }
}
