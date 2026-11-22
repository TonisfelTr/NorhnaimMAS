<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Diagnose;
use App\Models\Patient;
use App\Services\PatientClinicDiagnosisService;
use App\Support\HospitalPatientAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Search and assign a patient's current diagnosis in the authenticated
 * clinician's clinic. The client never selects the clinic ID.
 */
final class PatientClinicDiagnosisController extends Controller
{
    public function search(
        Request $request,
        Patient $patient,
        HospitalPatientAccess $access,
        PatientClinicDiagnosisService $diagnoses
    ): JsonResponse {
        $this->authorizeEditor($request, $patient, $access, $diagnoses);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $term = trim((string) ($validated['q'] ?? ''));

        // The selector initially lists F/G diagnoses, then filters by code or title.
        // No group is accepted from the client: the backend always fixes F/G.
        $query = DB::table('diagnoses')
            ->select(['id', 'code', 'title'])
            ->where(static function ($scope): void {
                $scope->whereRaw("UPPER(TRIM(code)) LIKE 'F%'")
                    ->orWhereRaw("UPPER(TRIM(code)) LIKE 'G%'");
            });

        if ($term !== '') {
            // Quote LIKE wildcard characters so a search cannot change the query pattern.
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($term));
            $like = '%' . $escaped . '%';
            $query->where(static function ($scope) use ($like): void {
                $scope->whereRaw("LOWER(code) LIKE ? ESCAPE '\\'", [$like])
                    ->orWhereRaw("LOWER(title) LIKE ? ESCAPE '\\'", [$like]);
            });
        }

        $results = $query->orderBy('code')->orderBy('id')->limit(80)->get()
            ->filter(static fn ($item): bool =>
                PatientClinicDiagnosisService::isAllowedCode((string) $item->code))
            ->take(25)
            ->map(static fn ($item): array => [
                'id' => (int) $item->id,
                'code' => trim((string) $item->code),
                'title' => (string) $item->title,
            ])
            ->values();

        return response()->json(['items' => $results])
            ->header('Cache-Control', 'private, no-store');
    }

    public function store(
        Request $request,
        Patient $patient,
        HospitalPatientAccess $access,
        PatientClinicDiagnosisService $diagnoses
    ): RedirectResponse {
        $this->authorizeEditor($request, $patient, $access, $diagnoses);

        $validated = $request->validateWithBag('clinicDiagnosis', [
            'diagnose_id' => ['required', 'integer', Rule::exists('diagnoses', 'id')],
        ]);

        $diagnose = Diagnose::query()->findOrFail((int) $validated['diagnose_id']);
        $changed = $diagnoses->assignForCurrentDoctor($patient, $request->user(), $diagnose);

        return redirect()->route('doctors.patients.medical_card', [
            'patient' => $patient->getKey(),
        ])->with('clinic_diagnosis_saved', $changed
            ? 'Основной диагноз клиники обновлён. Предыдущая запись сохранена в истории.'
            : 'Этот диагноз уже является текущим в вашей клинике.');
    }

    private function authorizeEditor(
        Request $request,
        Patient $patient,
        HospitalPatientAccess $access,
        PatientClinicDiagnosisService $diagnoses
    ): void {
        $user = $request->user();
        abort_unless($user !== null
            && $access->canRead($user, $patient)
            && $diagnoses->canAssign($user, $patient), 403);
    }
}
