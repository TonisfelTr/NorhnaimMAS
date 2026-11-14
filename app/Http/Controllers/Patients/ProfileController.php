<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\PatientClinic;
use App\Models\PatientLinkRequest;
use App\Models\PatientProfileChangeRequest;
use App\Models\PatientProfileEvent;
use App\Services\PatientProfileAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole('patients'), 403);

        $patient = $user->patient()->first();

        if ($patient) {
            $patient->load([
                'identityDocuments' => static function ($query): void {
                    $query
                        ->orderByDesc('is_primary')
                        ->orderByDesc('is_active')
                        ->orderByDesc('updated_at');
                },
                'primaryIdentityDocument',
                'representatives',
                'doctor',
                'clinicMemberships' => static function ($query): void {
                    $query
                        ->where('is_active', true)
                        ->with(['clinic', 'doctor'])
                        ->orderByDesc('is_primary')
                        ->orderBy('id');
                },
            ]);
        }

        $tab = (string) $request->query('tab', 'profile');

        if (! in_array($tab, ['profile', 'identity', 'clinics', 'documents', 'history', 'security'], true)) {
            $tab = 'profile';
        }

        $linkRequest = PatientLinkRequest::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        $changeRequest = $patient
            ? PatientProfileChangeRequest::query()
                ->with(['reviewClinic', 'reviewDoctor'])
                ->where('patient_id', $patient->id)
                ->where('user_id', $user->id)
                ->latest('id')
                ->first()
            : null;

        $events = $patient
            ? PatientProfileEvent::query()
                ->where('patient_id', $patient->id)
                ->with('actor:id,login')
                ->orderByDesc('occurred_at')
                ->limit(50)
                ->get()
            : collect();

        $avatarUrl = $user->avatar
            ? Storage::disk('public')->url($user->avatar)
            : null;

        $name = $patient
            ? trim(implode(' ', array_filter([
                $patient->surname,
                $patient->name,
                $patient->patronym,
            ])))
            : (string) $user->login;

        $initials = mb_strtoupper(
            mb_substr((string) ($patient?->surname ?: $user->login), 0, 1)
            . mb_substr((string) ($patient?->name ?: ''), 0, 1)
        );

        $clinicCards = $patient
            ? $patient->clinicMemberships
                ->filter(static fn (PatientClinic $membership): bool =>
                    $membership->clinic !== null
                )
                ->map(function (PatientClinic $membership): array {
                    $clinic = $membership->clinic;
                    $doctor = $membership->doctor;

                    return [
                        'id' => (int) $membership->clinic_id,
                        'name' => (string) (
                            $clinic?->name
                            ?: 'Клиника #' . $membership->clinic_id
                        ),
                        'city' => trim((string) ($clinic?->city ?? '')),
                        'phones' => $this->clinicPhones($clinic),
                        'clinic_type' => (string) (
                            $clinic?->clinic_type
                            ?: Clinic::TYPE_OUTPATIENT
                        ),
                        'clinic_type_label' =>
                            $clinic?->clinic_type === Clinic::TYPE_INPATIENT
                                ? 'Стационар'
                                : 'Амбулатория',
                        'is_primary' => (bool) $membership->is_primary,
                        'doctor_id' => $doctor?->id,
                        'doctor_name' => $doctor
                            ? trim(implode(' ', array_filter([
                                $doctor->surname,
                                $doctor->name,
                                $doctor->patronym,
                            ])))
                            : null,
                        'doctor_speciality' => $this->doctorSpeciality($doctor),
                    ];
                })
                ->values()
            : collect();

        return view('patients.profile.index', [
            'patient' => $patient,
            'user' => $user,
            'tab' => $tab,
            'events' => $events,
            'linkRequest' => $linkRequest,
            'changeRequest' => $changeRequest,
            'primaryDocument' => $patient?->primaryIdentityDocument,
            'avatarUrl' => $avatarUrl,
            'patientName' => $name !== '' ? $name : 'Пациент',
            'initials' => $initials !== '' ? $initials : 'П',
            'birthAt' => $patient?->getRawOriginal('birth_at'),
            'clinicCards' => $clinicCards,
        ]);
    }

    private function doctorSpeciality(?Doctor $doctor): string
    {
        if (!$doctor) {
            return 'Лечащий врач не назначен';
        }

        $status = trim((string) ($doctor->status ?? ''));

        if ($status !== '') {
            $specialities = collect(
                preg_split('/\s*[,;|]\s*/u', $status, -1, PREG_SPLIT_NO_EMPTY) ?: []
            )
                ->map(static fn ($value): string => trim((string) $value))
                ->filter()
                ->reject(static fn (string $value): bool =>
                    mb_strtolower($value) === 'врач'
                )
                ->unique(static fn (string $value): string =>
                    mb_strtolower($value)
                )
                ->values()
                ->implode(', ');

            if ($specialities !== '') {
                return $specialities;
            }
        }

        foreach ([
            $doctor->accreditation_speciality ?? null,
            $doctor->profession ?? null,
        ] as $fallback) {
            $fallback = trim((string) $fallback);

            if ($fallback !== '' && mb_strtolower($fallback) !== 'врач') {
                return $fallback;
            }
        }

        return 'Специализация не указана';
    }

    private function clinicPhones(?Clinic $clinic): array
    {
        if (!$clinic) {
            return [];
        }

        $phones = $clinic->phones;

        if (is_string($phones)) {
            $decoded = json_decode($phones, true);
            $phones = is_array($decoded) ? $decoded : [$phones];
        }

        if (!is_array($phones)) {
            return [];
        }

        return collect($phones)
            ->map(static function ($phone): string {
                if (is_array($phone)) {
                    return trim((string) (
                        $phone['phone']
                        ?? $phone['number']
                        ?? $phone['value']
                        ?? ''
                    ));
                }

                return trim((string) $phone);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function update(Request $request): RedirectResponse
    {
        abort(403, 'Персональные данные медицинской карты изменяются только по согласованному запросу.');
    }

    public function avatar(
        Request $request,
        PatientProfileAuditService $audit
    ): RedirectResponse {
        abort_unless($request->user()?->hasRole('patients'), 403);

        $request->validate([
            'avatar' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                'dimensions:min_width=128,min_height=128,max_width=4000,max_height=4000',
            ],
        ], [
            'avatar.required' => 'Выберите фотографию.',
            'avatar.image' => 'Файл должен быть изображением.',
            'avatar.mimes' => 'Разрешены JPG, PNG и WEBP.',
            'avatar.max' => 'Размер фотографии не должен превышать 5 МБ.',
            'avatar.dimensions' => 'Размер изображения должен быть от 128×128 до 4000×4000 пикселей.',
        ]);

        $user = $request->user();
        $directory = 'avatars/patients/' . $user->id;
        $oldPath = (string) $user->avatar;

        $newPath = $request->file('avatar')->storePublicly($directory, 'public');

        $user->forceFill(['avatar' => $newPath])->save();

        if (
            $oldPath !== ''
            && Str::startsWith($oldPath, $directory . '/')
            && $oldPath !== $newPath
        ) {
            Storage::disk('public')->delete($oldPath);
        }

        $patient = $user->patient()->first();

        if ($patient) {
            $audit->record($patient, 'avatar_updated', 'Обновлена фотография профиля');
        }

        return redirect()
            ->route('patients.profile.index', ['tab' => 'profile'])
            ->with('success', 'Фотография профиля обновлена.');
    }

    public function avatarDestroy(
        Request $request,
        PatientProfileAuditService $audit
    ): RedirectResponse {
        abort_unless($request->user()?->hasRole('patients'), 403);

        $user = $request->user();
        $oldPath = (string) $user->avatar;
        $directory = 'avatars/patients/' . $user->id;

        $user->forceFill(['avatar' => null])->save();

        if ($oldPath !== '' && Str::startsWith($oldPath, $directory . '/')) {
            Storage::disk('public')->delete($oldPath);
        }

        $patient = $user->patient()->first();

        if ($patient) {
            $audit->record($patient, 'avatar_deleted', 'Удалена фотография профиля');
        }

        return redirect()
            ->route('patients.profile.index', ['tab' => 'profile'])
            ->with('success', 'Фотография профиля удалена.');
    }

    public function password(
        Request $request,
        PatientProfileAuditService $audit
    ): RedirectResponse {
        abort_unless($request->user()?->hasRole('patients'), 403);

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Введите текущий пароль.',
            'current_password.current_password' => 'Текущий пароль указан неверно.',
            'password.required' => 'Введите новый пароль.',
            'password.confirmed' => 'Новые пароли не совпадают.',
            'password.min' => 'Пароль должен содержать не менее :min символов.',
        ]);

        $user = $request->user();

        $user->forceFill([
            'password' => $data['password'],
            'remember_token' => Str::random(60),
        ])->save();

        $request->session()->regenerate();

        $patient = $user->patient()->first();

        if ($patient) {
            $audit->record($patient, 'password_changed', 'Изменён пароль учётной записи');
        }

        return redirect()
            ->route('patients.profile.index', ['tab' => 'security'])
            ->with('success', 'Пароль успешно изменён.');
    }
}
