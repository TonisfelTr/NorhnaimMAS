<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientDiagnosisHistory;
use App\Services\PatientClinicDiagnosisService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

final class PatientDiagnosesController extends Controller
{
    private const PER_PAGE = 10;

    public function index(Request $request, PatientClinicDiagnosisService $diagnoses): View
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('patients'), 403);

        /** @var Patient $patient */
        $patient = $user->patient()->firstOrFail();

        $clinics = $patient->clinicMemberships()
            ->where('is_active', true)
            ->with('clinic')
            ->orderByDesc('is_primary')
            ->orderBy('clinic_id')
            ->get()
            ->filter(static fn ($membership): bool => $membership->clinic !== null)
            ->unique('clinic_id')
            ->values();

        $raw = $request->query('clinic_id');
        if ($raw === null || $raw === '') {
            $clinicId = $clinics->first() ? (int) $clinics->first()->clinic_id : null;
        } else {
            abort_unless(is_scalar($raw)
                && preg_match('/^[1-9][0-9]*$/D', (string) $raw) === 1, 404);
            $clinicId = (int) $raw;
            // A manually entered clinic_id is NOT permission to see its diagnoses.
            abort_unless($clinics->contains(static fn ($m): bool => (int) $m->clinic_id === $clinicId), 404);
        }

        $selectedClinic = $clinicId !== null
            ? $clinics->firstWhere('clinic_id', $clinicId)?->clinic
            : null;

        $current = $clinicId !== null
            ? $diagnoses->currentForClinic((int) $patient->getKey(), $clinicId)
            : null;

        $history = $clinicId !== null && Schema::hasTable('patient_diagnosis_history')
            ? PatientDiagnosisHistory::query()
                ->with('diagnose')
                ->where('patient_id', (int) $patient->getKey())
                ->where('clinic_id', $clinicId)
                ->whereNotNull('diagnose_id')
                ->whereHas('diagnose')
                ->orderByDesc('set_at')
                ->orderByDesc('id')
                ->paginate(self::PER_PAGE, ['*'], 'diagnoses_page')
                ->withQueryString()
            : $this->emptyPage($request, 'diagnoses_page');

        $visitDiagnoses = $clinicId !== null
            && Schema::hasTable('medical_encounters')
            && Schema::hasTable('registry_records')
            ? DB::table('medical_encounters as me')
                ->join('registry_records as rr', 'rr.id', '=', 'me.registry_record_id')
                ->leftJoin('doctors as d', 'd.id', '=', 'rr.doctor_id')
                ->where('rr.patient_id', (int) $patient->getKey())
                ->where('rr.clinic_id', $clinicId)
                ->whereNull('rr.deleted_at')
                ->where('me.attendance_status', 'present')
                ->where('me.consultation_status', 'completed')
                ->where(static function ($query): void {
                    $query->where('me.diagnosis_code', '!=', '')
                        ->orWhere('me.diagnosis_title', '!=', '');
                })
                ->select([
                    'me.id', 'me.diagnosis_code', 'me.diagnosis_title',
                    'rr.for_datetime as visit_date',
                    'd.surname as doctor_surname',
                    'd.name as doctor_name',
                    'd.patronym as doctor_patronym',
                ])
                ->orderByDesc('rr.for_datetime')
                ->orderByDesc('me.id')
                ->paginate(self::PER_PAGE, ['*'], 'visit_diagnoses_page')
                ->withQueryString()
            : $this->emptyPage($request, 'visit_diagnoses_page');

        $visitDiagnoses->getCollection()->transform(static function ($visit): array {
            try {
                $date = $visit->visit_date
                    ? Carbon::parse($visit->visit_date)->format('d.m.Y H:i')
                    : 'Дата не указана';
            } catch (Throwable) {
                $date = 'Дата не указана';
            }

            $doctor = trim(implode(' ', array_filter([
                $visit->doctor_surname,
                $visit->doctor_name,
                $visit->doctor_patronym,
            ])));

            return [
                'code' => trim((string) ($visit->diagnosis_code ?? '')),
                'title' => trim((string) ($visit->diagnosis_title ?? '')),
                'date' => $date,
                'doctor' => $doctor !== '' ? $doctor : 'Врач не указан',
            ];
        });

        return view('patients.diagnoses.index', compact(
            'patient', 'clinics', 'clinicId', 'selectedClinic',
            'current', 'history', 'visitDiagnoses'
        ));
    }

    private function emptyPage(Request $request, string $pageName): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, self::PER_PAGE, 1, [
            'path' => $request->url(),
            'pageName' => $pageName,
            'query' => $request->query(),
        ]);
    }
}
