<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorCredential;
use App\Models\DoctorProfileEvent;
use App\Services\DoctorCredentialVerificationService;
use App\Services\DoctorProfileAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DoctorProfileController extends Controller
{
public function index(Request $request): View
{
    $doctor = $this->doctor(
        $request,
        false
    );

    if (!$doctor) {
        return view(
            'doctors.profile.index',
            [
                'doctor' => null,
                'credentials' => collect(),
                'events' => collect(),
                'credentialKinds' =>
                    DoctorCredential::kindOptions(),
                'rating' => 0.0,
                'feedbackCount' => 0,
                'marks' => array_fill(1, 5, 0),
                'verificationSummary' =>
                    $this->emptyVerificationSummary(),
                'profileContext' => null,
            ]
        );
    }

    $credentials = DoctorCredential::query()
        ->where(
            'doctor_id',
            $doctor->id
        )
        ->with('media')
        ->orderByDesc('created_at')
        ->get();

    $events = DoctorProfileEvent::query()
        ->where(
            'doctor_id',
            $doctor->id
        )
        ->with('actor:id,login')
        ->orderByDesc('occurred_at')
        ->limit(100)
        ->get();

    $feedbackQuery = $doctor->feedbacks();

    $rating = round(
        (float) (
            (clone $feedbackQuery)->avg('mark')
            ?? 0
        ),
        2
    );

    $feedbackCount =
        (clone $feedbackQuery)->count();

    $marks = array_fill(1, 5, 0);

    foreach (
        (clone $feedbackQuery)
            ->selectRaw(
                'mark, COUNT(*) as aggregate'
            )
            ->groupBy('mark')
            ->pluck('aggregate', 'mark')
            ->all()
        as $mark => $count
    ) {
        $marks[(int) $mark] =
            (int) $count;
    }

    return view(
        'doctors.profile.index',
        [
            'doctor' => $doctor,
            'credentials' => $credentials,
            'events' => $events,
            'credentialKinds' =>
                DoctorCredential::kindOptions(),
            'rating' => $rating,
            'feedbackCount' => $feedbackCount,
            'marks' => $marks,
            'verificationSummary' =>
                $this->verificationSummary(
                    $doctor,
                    $credentials
                ),
            'profileContext' =>
                $this->profileContext($doctor),
        ]
    );
}



public function update(
    \App\Http\Requests\Doctors\DoctorProfileUpdateRequest $request
): RedirectResponse {
    $doctor = $this->doctor($request);
    $context = $this->profileContext(
        $doctor
    );

    $data = $request->validated();

    $payload = [
        'surname' => trim(
            (string) $data['surname']
        ),

        'name' => trim(
            (string) $data['name']
        ),

        'patronym' => $this->nullableText(
            $data['patronym'] ?? null
        ),

        'birth_at' => $data['birth_at'],

        'email' => $this->nullableText(
            $data['email'] ?? null
        ),

        'experience_years' =>
            $data['experience_years'] ?? null,

        'experience_months' =>
            $data['experience_months'] ?? null,

        'about' => $this->nullableText(
            $data['about'] ?? null
        ),

        'snils' =>
            $data['snils'] ?? null,

        'ready_for_telemedicine' =>
            (bool) $data['ready_for_telemedicine'],
    ];

    /*
     * Телефон кабинета является источником истины.
     * Даже подмена readonly-поля в браузере не позволит
     * перезаписать doctors.phone, пока врач привязан к кабинету.
     */
    if (!$context['phone_locked']) {
        $payload['phone'] =
            $this->nullableText(
                $data['phone'] ?? null
            );
    }

    /*
     * Для врача клиники город и рабочий адрес
     * определяются настройками Clinic.
     */
    if (!$context['city_locked']) {
        $payload['city'] =
            $this->nullableText(
                $data['city'] ?? null
            );
    }

    if (!$context['work_address_locked']) {
        $payload['address_job'] =
            trim(
                (string) (
                    $data['address_job']
                    ?? ''
                )
            );
    }

    /*
     * profession/status здесь намеренно отсутствуют:
     * должность и специальность управляются
     * настройками клиники.
     */
    $doctor->fill(
        $payload
    )->save();

    return redirect()
        ->route(
            'doctors.profile.index',
            ['tab' => 'profile']
        )
        ->with(
            'success',
            'Профиль врача сохранён.'
        );
}



    public function photo(
        Request $request,
        DoctorProfileAuditService $audit
    ): RedirectResponse {
        $doctor = $this->doctor($request);

        $request->validate([
            'photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:8192',
                'dimensions:min_width=250,min_height=250,max_width=5000,max_height=5000',
            ],
        ], [
            'photo.required' =>
                'Выберите фотографию.',

            'photo.image' =>
                'Файл должен быть изображением.',

            'photo.max' =>
                'Размер фотографии не должен превышать 8 МБ.',

            'photo.dimensions' =>
                'Размер фотографии должен быть от 250×250 до 5000×5000 пикселей.',
        ]);

        $doctor
            ->addMediaFromRequest('photo')
            ->usingName('Фотография врача')
            ->toMediaCollection('photos');

        $audit->record(
            $doctor,
            'photo_uploaded',
            'Обновлена фотография профиля'
        );

        return back()->with(
            'success',
            'Фотография обновлена.'
        );
    }

public function photoDestroy(
    Request $request,
    DoctorProfileAuditService $audit
): RedirectResponse {
    $doctor = $this->doctor($request);

    if (
        $doctor->getFirstMedia('photos')
        !== null
    ) {
        $doctor->clearMediaCollection(
            'photos'
        );

        $audit->record(
            $doctor,
            'photo_deleted',
            'Удалена фотография профиля'
        );
    }

    return redirect()
        ->route(
            'doctors.profile.index',
            ['tab' => 'profile']
        )
        ->with(
            'success',
            'Фотография удалена.'
        );
}

    public function credentialStore(
        Request $request,
        DoctorCredentialVerificationService $verifier,
        DoctorProfileAuditService $audit
    ): RedirectResponse {
        $doctor = $this->doctor($request);

        $data = $request->validate([
            'kind' => [
                'required',
                'string',
                Rule::in(
                    array_keys(
                        DoctorCredential::kindOptions()
                    )
                ),
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'series' => [
                'nullable',
                'string',
                'max:100',
            ],
            'number' => [
                'nullable',
                'string',
                'max:160',
            ],
            'issued_at' => [
                'nullable',
                'date',
            ],
            'valid_until' => [
                'nullable',
                'date',
                'after_or_equal:issued_at',
            ],
            'issuer' => [
                'nullable',
                'string',
                'max:500',
            ],
            'speciality' => [
                'nullable',
                'string',
                'max:255',
            ],
            'registry_number' => [
                'nullable',
                'string',
                'max:255',
            ],
            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:15360',
            ],
        ], [
            'document.required' =>
                'Выберите файл документа.',

            'document.mimes' =>
                'Разрешены PDF, JPG, JPEG, PNG и WEBP.',

            'document.max' =>
                'Размер документа не должен превышать 15 МБ.',
        ]);

        if (
            in_array(
                $data['kind'],
                [
                    DoctorCredential::KIND_DIPLOMA,
                    DoctorCredential::KIND_ACCREDITATION,
                ],
                true
            )
            && trim(
                (string) ($data['number'] ?? '')
            ) === ''
        ) {
            throw ValidationException::withMessages([
                'number' =>
                    'Для диплома и аккредитации укажите номер документа.',
            ]);
        }

        $credential = DB::transaction(
            function () use (
                $doctor,
                $data
            ): DoctorCredential {
                $credential =
                    DoctorCredential::query()
                        ->create([
                            'doctor_id' =>
                                $doctor->id,

                            'kind' =>
                                $data['kind'],

                            'title' =>
                                $data['title'],

                            'series' =>
                                $data['series']
                                ?? null,

                            'number' =>
                                $data['number']
                                ?? null,

                            'issued_at' =>
                                $data['issued_at']
                                ?? null,

                            'valid_until' =>
                                $data['valid_until']
                                ?? null,

                            'issuer' =>
                                $data['issuer']
                                ?? null,

                            'speciality' =>
                                $data['speciality']
                                ?? null,

                            'registry_number' =>
                                $data['registry_number']
                                ?? null,

                            'verification_status' =>
                                DoctorCredential::STATUS_PENDING,
                        ]);

                $credential
                    ->addMediaFromRequest(
                        'document'
                    )
                    ->usingName(
                        $credential->title
                    )
                    ->toMediaCollection(
                        'document'
                    );

                return $credential;
            }
        );

        $audit->record(
            $doctor,
            'credential_uploaded',
            'Загружен документ: '
                . $credential->kindLabel(),
            null,
            [
                'credential_id' =>
                    $credential->id,

                'title' =>
                    $credential->title,
            ]
        );

        $this->applyVerification(
            $credential,
            $verifier,
            $audit
        );

        return back()->with(
            'success',
            'Документ загружен.'
        );
    }

    public function credentialVerify(
        Request $request,
        DoctorCredential $credential,
        DoctorCredentialVerificationService $verifier,
        DoctorProfileAuditService $audit
    ): RedirectResponse {
        $doctor = $this->doctor($request);

        abort_unless(
            (int) $credential->doctor_id
                === (int) $doctor->id,
            404
        );

        $this->applyVerification(
            $credential,
            $verifier,
            $audit
        );

        return back()->with(
            'success',
            'Проверка документа выполнена.'
        );
    }

    public function credentialDownload(
        Request $request,
        DoctorCredential $credential
    ): BinaryFileResponse {
        $doctor = $this->doctor($request);

        abort_unless(
            (int) $credential->doctor_id
                === (int) $doctor->id,
            404
        );

        $media =
            $credential->getFirstMedia(
                'document'
            );

        abort_unless(
            $media,
            404
        );

        return response()->file(
            $media->getPath(),
            [
                'Content-Type' =>
                    $media->mime_type
                    ?: 'application/octet-stream',

                'Content-Disposition' =>
                    'inline; filename="'
                    . addslashes(
                        $media->file_name
                    )
                    . '"',

                'Cache-Control' =>
                    'private, no-store',

                'X-Content-Type-Options' =>
                    'nosniff',
            ]
        );
    }

    private function applyVerification(
        DoctorCredential $credential,
        DoctorCredentialVerificationService $verifier,
        DoctorProfileAuditService $audit
    ): void {
        $credential->forceFill([
            'verification_requested_at' =>
                now(),
        ])->save();

        $result = $verifier->verify(
            $credential->loadMissing(
                'doctor'
            )
        );

        $credential->forceFill([
            'verification_status' =>
                $result['status'],

            'verification_source' =>
                $result['source'],

            'verification_message' =>
                $result['message'],

            'verification_payload' =>
                $result['payload'],

            'verified_at' =>
                $result['verified_at'],
        ])->save();

        if (
            $credential->kind
                === DoctorCredential::KIND_ACCREDITATION
            && $credential->verification_status
                === DoctorCredential::STATUS_VERIFIED
        ) {
            $credential->doctor->forceFill([
                'accreditation_status' =>
                    DoctorCredential::STATUS_VERIFIED,

                'accreditation_number' =>
                    $credential->registry_number
                    ?: $credential->number,

                'accreditation_speciality' =>
                    $credential->speciality,

                'accreditation_valid_from' =>
                    $credential->issued_at,

                'accreditation_valid_until' =>
                    $credential->valid_until,

                'professional_verification_source' =>
                    $credential->verification_source,

                'professional_verified_at' =>
                    $credential->verified_at
                    ?: now(),

                'professional_verification_payload' =>
                    $credential->verification_payload,
            ])->save();
        }

        $audit->record(
            $credential->doctor,
            'credential_verified',
            'Проверка документа: '
                . $credential->kindLabel(),
            null,
            [
                'credential_id' =>
                    $credential->id,

                'status' =>
                    $credential->verification_status,

                'source' =>
                    $credential->verification_source,

                'message' =>
                    $credential->verification_message,
            ]
        );
    }

private function profileContext(
    Doctor $doctor
): array {
    $clinic = $doctor->clinic;

    $cabinetQuery =
        \App\Models\ClinicCabinet::query()
            ->where(
                'doctor_id',
                $doctor->getKey()
            );

    if ($doctor->clinic_id) {
        $cabinetQuery->where(
            'clinic_id',
            $doctor->clinic_id
        );
    }

    $cabinet = $cabinetQuery
        ->orderByDesc('enabled')
        ->orderBy('id')
        ->first();

    $photo = $doctor->getFirstMedia(
        'photos'
    );

    $nameInitial = trim(
        (string) $doctor->name
    ) !== ''
        ? mb_strtoupper(
            mb_substr(
                (string) $doctor->name,
                0,
                1
            )
        )
        : '';

    $surnameInitial = trim(
        (string) $doctor->surname
    ) !== ''
        ? mb_strtoupper(
            mb_substr(
                (string) $doctor->surname,
                0,
                1
            )
        )
        : '';

    $cabinetLabel = 'кабинета';

    if ($cabinet) {
        $parts = [];

        if (
            trim(
                (string) $cabinet->cabinet
            ) !== ''
        ) {
            $parts[] =
                'кабинета №'
                . trim(
                    (string) $cabinet->cabinet
                );
        }

        if (
            trim(
                (string) $cabinet->name
            ) !== ''
        ) {
            $parts[] =
                '«'
                . trim(
                    (string) $cabinet->name
                )
                . '»';
        }

        if ($parts !== []) {
            $cabinetLabel =
                implode(' ', $parts);
        }
    }

    return [
        'cabinet' => $cabinet,

        'has_cabinet' =>
            $cabinet !== null,

        'cabinet_label' =>
            $cabinetLabel,

        'phone_locked' =>
            $cabinet !== null,

        'phone' =>
            $cabinet !== null
                ? (string) (
                    $cabinet->phone
                    ?? ''
                )
                : (string) (
                    $doctor->phone
                    ?? ''
                ),

        'city_locked' =>
            $clinic !== null,

        'city' =>
            $clinic !== null
                ? (string) (
                    $clinic->city
                    ?? ''
                )
                : (string) (
                    $doctor->city
                    ?? ''
                ),

        'work_address_locked' =>
            $clinic !== null,

        'work_address' =>
            $clinic !== null
                ? (string) (
                    $clinic->address
                    ?? ''
                )
                : (string) (
                    $doctor->address_job
                    ?? ''
                ),

        'clinic_name' =>
            $clinic?->name
            ?: 'частной практики',

        'has_photo' =>
            $photo !== null,

        'photo_url' =>
            $photo?->getUrl(),

        'initials' =>
            ($nameInitial . $surnameInitial)
            ?: 'ВР',
    ];
}

    private function verificationSummary(
        Doctor $doctor,
        $credentials
    ): array {
        $accreditation = $this->doctorAccreditationSummary(
            $doctor
        );

        if ($accreditation === null) {
            $accreditation = $this->summaryForKind(
                $credentials,
                DoctorCredential::KIND_ACCREDITATION,
                true
            );
        }

        return [
            'diploma' =>
                $this->summaryForKind(
                    $credentials,
                    DoctorCredential::KIND_DIPLOMA
                ),

            'accreditation' =>
                $accreditation,
        ];
    }

    private function doctorAccreditationSummary(
        Doctor $doctor
    ): ?array {
        $status = trim(
            (string) $doctor->accreditation_status
        );

        if ($status === '') {
            return null;
        }

        $expired = $doctor->accreditation_valid_until
            && $doctor->accreditation_valid_until->isPast();

        if ($expired) {
            return [
                'status' => 'expired',
                'label' => 'Истекла',
                'class' => 'danger',
            ];
        }

        return match ($status) {
            DoctorCredential::STATUS_VERIFIED,
            'valid',
            'active' => [
                'status' => 'verified',
                'label' => 'Действует',
                'class' => 'success',
            ],

            DoctorCredential::STATUS_NOT_FOUND,
            DoctorCredential::STATUS_MISMATCH,
            'invalid',
            'expired' => [
                'status' => 'problem',
                'label' => 'Требует проверки',
                'class' => 'danger',
            ],

            DoctorCredential::STATUS_UNAVAILABLE => [
                'status' => 'unavailable',
                'label' => 'Проверка не подключена',
                'class' => 'secondary',
            ],

            default => [
                'status' => 'pending',
                'label' => 'Ожидает проверки',
                'class' => 'info',
            ],
        };
    }

    private function summaryForKind(
        $credentials,
        string $kind,
        bool $checkExpiry = false
    ): array {
        $items = $credentials
            ->where('kind', $kind)
            ->values();

        if ($items->isEmpty()) {
            return [
                'status' => 'missing',
                'label' => 'Не загружен',
                'class' => 'secondary',
            ];
        }

        $verified = $items
            ->where(
                'verification_status',
                DoctorCredential::STATUS_VERIFIED
            )
            ->when(
                $checkExpiry,
                static fn ($collection) =>
                    $collection->filter(
                        static fn (
                            DoctorCredential $item
                        ): bool =>
                            !$item->isExpired()
                    )
            );

        if ($verified->isNotEmpty()) {
            return [
                'status' => 'verified',
                'label' => $checkExpiry
                    ? 'Действует'
                    : 'Подтверждён',
                'class' => 'success',
            ];
        }

        if (
            $items->contains(
                static fn (
                    DoctorCredential $item
                ): bool =>
                    $item->verification_status
                        === DoctorCredential::STATUS_UNAVAILABLE
            )
        ) {
            return [
                'status' => 'unavailable',
                'label' => 'Проверка не подключена',
                'class' => 'secondary',
            ];
        }

        if (
            $items->contains(
                static fn (
                    DoctorCredential $item
                ): bool =>
                    in_array(
                        $item->verification_status,
                        [
                            DoctorCredential::STATUS_NOT_FOUND,
                            DoctorCredential::STATUS_MISMATCH,
                        ],
                        true
                    )
            )
        ) {
            return [
                'status' => 'problem',
                'label' => 'Требует проверки',
                'class' => 'danger',
            ];
        }

        return [
            'status' => 'pending',
            'label' => 'Ожидает проверки',
            'class' => 'info',
        ];
    }

    private function emptyVerificationSummary(): array
    {
        return [
            'diploma' => [
                'status' => 'missing',
                'label' => 'Не загружен',
                'class' => 'secondary',
            ],
            'accreditation' => [
                'status' => 'missing',
                'label' => 'Не загружена',
                'class' => 'secondary',
            ],
        ];
    }

private function nullableText(
    mixed $value
): ?string {
    $value = trim(
        (string) ($value ?? '')
    );

    return $value !== ''
        ? $value
        : null;
}



    private function doctor(
        Request $request,
        bool $required = true
    ): ?Doctor {
        $doctor = Doctor::query()
            ->where(
                'user_id',
                $request->user()?->id
            )
            ->with('clinic')
            ->first();

        if ($required) {
            abort_unless(
                $doctor,
                422,
                'К этому пользователю не привязан профиль врача.'
            );
        }

        return $doctor;
    }
}
