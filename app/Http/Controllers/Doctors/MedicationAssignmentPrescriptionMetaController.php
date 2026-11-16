<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MedicationAssignmentPrescriptionMetaController extends Controller
{
    public function show(
        Request $request,
        Patient $patient
    ): JsonResponse {
        $data = $request->validate([
            'drug_id' => ['required', 'integer', 'exists:drugs,id'],
        ]);

        $user = $request->user();

        $doctor = $user && method_exists($user, 'doctor')
            ? $user->doctor()->first(['id', 'clinic_id'])
            : null;

        abort_unless(
            $doctor && (int) ($doctor->clinic_id ?? 0) > 0,
            403,
            'У текущего врача не определена клиника.'
        );

        $clinicId = (int) $doctor->clinic_id;

        $drug = Drug::query()
            ->findOrFail((int) $data['drug_id']);

        $names = array_values(
            array_filter(
                array_unique([
                    trim((string) ($drug->latin_name ?? '')),
                    trim((string) ($drug->name ?? '')),
                ]),
                static fn (string $value): bool => $value !== ''
            )
        );

        $query = DB::table('medical_prescriptions as mp')
            ->leftJoin(
                'doctors as d',
                'd.id',
                '=',
                'mp.doctor_id'
            )
            ->where(
                'mp.patient_id',
                (int) $patient->id
            )
            ->whereNull('mp.deleted_at')
            ->where(function ($q) use ($names): void {
                foreach ($names as $index => $name) {
                    if ($index === 0) {
                        $q->whereRaw(
                            'LOWER(mp.generic_name) = LOWER(?)',
                            [$name]
                        );
                    } else {
                        $q->orWhereRaw(
                            'LOWER(mp.generic_name) = LOWER(?)',
                            [$name]
                        );
                    }
                }
            });

        if (
            Schema::hasColumn(
                'medical_prescriptions',
                'clinic_id'
            )
        ) {
            $query->where(
                function ($q) use ($clinicId): void {
                    $q->where(
                        'mp.clinic_id',
                        $clinicId
                    )->orWhere(
                        function ($legacy) use ($clinicId): void {
                            $legacy
                                ->whereNull('mp.clinic_id')
                                ->where(
                                    'd.clinic_id',
                                    $clinicId
                                );
                        }
                    );
                }
            );
        } else {
            $query->where(
                'd.clinic_id',
                $clinicId
            );
        }

        $first = $query
            ->orderByRaw(
                'COALESCE(mp.issued_at, mp.created_at) ASC'
            )
            ->orderBy('mp.id')
            ->select([
                'mp.id',
                'mp.issued_at',
                'mp.created_at',
                'mp.clinic_id',
            ])
            ->first();

        if ($first) {
            $value =
                $first->issued_at
                ?? $first->created_at;

            $date = Carbon::parse(
                $value
            )->toDateString();

            $source = 'existing_prescription';
        } else {
            $date = today()->toDateString();
            $source = 'first_prescription_today';
        }

        return response()->json([
            'patient_id' => (int) $patient->id,
            'drug_id' => (int) $drug->id,
            'clinic_id' => $clinicId,
            'starts_at' => $date,
            'source' => $source,
            'prescription_id' =>
                $first?->id
                ? (int) $first->id
                : null,
        ]);
    }
}
