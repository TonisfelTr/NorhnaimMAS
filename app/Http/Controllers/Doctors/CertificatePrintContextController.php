<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Support\HospitalPatientAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CertificatePrintContextController extends Controller
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

        $doctor = null;

        if ($user && method_exists($user, 'doctor')) {
            $doctor = $user->doctor()
                ->with('clinic')
                ->first();
        }

        if (!$doctor && method_exists($patient, 'doctor')) {
            $doctor = $patient->doctor()
                ->with('clinic')
                ->first();
        }

        $doctorName = $this->doctorName($doctor);
        $profession = trim((string) (
            $doctor?->profession
            ?: $doctor?->status
            ?: ''
        ));

        return response()
            ->json([
                'doctor_name' => $doctorName,
                'doctor_profession' => $profession,
                'doctor_label' => $doctorName !== ''
                    ? ($profession !== ''
                        ? $profession . ', ' . $doctorName
                        : $doctorName)
                    : '',
                'organization' => trim((string) (
                    $doctor?->clinic?->name ?? ''
                )),
                'clinic_id' => $doctor?->clinic_id,
            ])
            ->header('Cache-Control', 'private, no-store');
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
}
