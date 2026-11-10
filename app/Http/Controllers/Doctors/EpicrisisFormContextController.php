<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Support\HospitalPatientAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class EpicrisisFormContextController extends Controller
{
    public function show(
        Request $request,
        Patient $patient,
        HospitalPatientAccess $access
    ): JsonResponse {
        $user = $request->user();

        abort_unless(
            $user && $access->canRead($user, $patient),
            403
        );

        $record = RegistryRecord::query()
            ->where('patient_id', $patient->getKey())
            ->whereNull('deleted_at')
            ->when(
                Schema::hasColumn('registry_records', 'appointment'),
                static fn ($query) => $query->where('appointment', true)
            )
            ->orderByDesc('for_datetime')
            ->orderByDesc('id')
            ->first();

        $encounter = null;

        if (
            $record
            && Schema::hasTable('medical_encounters')
            && Schema::hasColumn('medical_encounters', 'registry_record_id')
        ) {
            $encounter = DB::table('medical_encounters')
                ->where('registry_record_id', $record->getKey())
                ->first();
        }

        $doctor = $record?->doctor_id
            ? Doctor::query()->find($record->doctor_id)
            : null;

        if (!$doctor && $user && method_exists($user, 'doctor')) {
            $doctor = $user->doctor()->first();
        }

        $clinicId = (int) (
            $record?->clinic_id
            ?: $doctor?->clinic_id
            ?: 0
        );

        $clinic = $clinicId > 0
            ? Clinic::query()->find($clinicId)
            : null;

        $doctorName = $this->doctorName($doctor);
        $doctorLabel = $this->doctorLabel($doctor, $doctorName);

        return response()
            ->json([
                'sex' => $this->sexLabel($patient),
                'visit_type' => $this->visitType(
                    $patient,
                    $record,
                    $encounter
                ),
                'place' => $this->placeLabel($clinic, $record),
                'benefit_category' => $this->benefitCategory($patient),
                'service_datetime' => $this->serviceDateTime(
                    $record,
                    $doctor
                ),
                'service_name' => $this->serviceName($encounter),
                'medical_worker' => $doctorLabel,
                'document_author' => $doctorLabel,
                'document_verifier' => $doctorLabel,
            ])
            ->header('Cache-Control', 'private, no-store');
    }

    private function sexLabel(Patient $patient): string
    {
        $raw = strtoupper(trim((string) (
            $patient->getAttribute('gender')
            ?: $patient->getAttribute('sex')
            ?: ''
        )));

        return match ($raw) {
            'M', 'MALE', 'М', 'МУЖ', 'МУЖСКОЙ' => 'Мужской',
            'F', 'FEMALE', 'Ж', 'ЖЕН', 'ЖЕНСКИЙ' => 'Женский',
            default => '',
        };
    }

    private function visitType(
        Patient $patient,
        ?RegistryRecord $record,
        ?object $encounter
    ): string {
        $explicit = trim((string) ($encounter?->visit_type ?? ''));

        if ($explicit !== '') {
            return match (mb_strtolower($explicit)) {
                'primary', 'first', 'первичный' => 'Первичный',
                'repeat', 'repeated', 'secondary', 'повторный' => 'Повторный',
                default => $explicit,
            };
        }

        if (!$record) {
            return '';
        }

        $count = RegistryRecord::query()
            ->where('patient_id', $patient->getKey())
            ->whereNull('deleted_at')
            ->where('for_datetime', '<=', $record->for_datetime)
            ->when(
                Schema::hasColumn('registry_records', 'appointment'),
                static fn ($query) => $query->where('appointment', true)
            )
            ->count();

        if ($count <= 0) {
            return '';
        }

        return $count === 1 ? 'Первичный' : 'Повторный';
    }

    private function placeLabel(
        ?Clinic $clinic,
        ?RegistryRecord $record
    ): string {
        $format = trim((string) (
            $record?->getAttribute('appointment_format') ?? ''
        ));

        if ($format === 'telemedicine') {
            return 'Телемедицинская консультация';
        }

        $type = trim((string) ($clinic?->clinic_type ?? ''));

        return match ($type) {
            'inpatient' => 'Стационар',
            'outpatient' => 'Амбулаторно-поликлиническое учреждение',
            default => $clinic ? 'Медицинская организация' : '',
        };
    }

    private function benefitCategory(Patient $patient): string
    {
        if (!Schema::hasTable('patient_disability_records')) {
            return '';
        }

        $record = DB::table('patient_disability_records')
            ->where('patient_id', $patient->getKey())
            ->first();

        if (!$record || (string) ($record->status ?? '') !== 'present') {
            return '';
        }

        $group = trim((string) ($record->disability_group ?? ''));

        return match ($group) {
            'I' => 'Инвалиды I группы',
            'II' => 'Инвалиды II группы',
            'III' => 'Инвалиды III группы',
            'child' => 'Ребёнок-инвалид',
            default => '',
        };
    }

    private function serviceDateTime(
        ?RegistryRecord $record,
        ?Doctor $doctor
    ): string {
        if (!$record?->for_datetime) {
            return '';
        }

        try {
            if (
                class_exists(\App\Support\ClinicAppointmentTime::class)
                && method_exists(
                    \App\Support\ClinicAppointmentTime::class,
                    'startInstant'
                )
            ) {
                $instant = \App\Support\ClinicAppointmentTime::startInstant(
                    $record
                );

                if ($instant) {
                    $timezone = null;

                    if (
                        class_exists(\App\Support\DoctorAppointmentTimezone::class)
                        && method_exists(
                            \App\Support\DoctorAppointmentTimezone::class,
                            'resolve'
                        )
                    ) {
                        $timezone =
                            \App\Support\DoctorAppointmentTimezone::resolve(
                                $doctor
                            );
                    }

                    return Carbon::instance($instant)
                        ->setTimezone(
                            $timezone
                            ?: config('app.timezone', 'UTC')
                        )
                        ->format('Y-m-d\TH:i');
                }
            }

            return Carbon::parse($record->for_datetime)
                ->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return '';
        }
    }

    private function serviceName(?object $encounter): string
    {
        $code = trim((string) ($encounter?->service_code ?? ''));
        $name = trim((string) ($encounter?->service_name ?? ''));

        if ($code !== '' && $name !== '') {
            return $code . ' ' . $name;
        }

        return $name !== '' ? $name : $code;
    }

    private function doctorName(?Doctor $doctor): string
    {
        if (!$doctor) {
            return '';
        }

        return trim(implode(' ', array_filter([
            $doctor->surname,
            $doctor->name,
            $doctor->patronym,
        ])));
    }

    private function doctorLabel(
        ?Doctor $doctor,
        string $name
    ): string {
        if (!$doctor || $name === '') {
            return $name;
        }

        $profession = trim((string) (
            $doctor->profession
            ?: $doctor->status
            ?: ''
        ));

        return $profession !== ''
            ? $profession . ', ' . $name
            : $name;
    }
}
