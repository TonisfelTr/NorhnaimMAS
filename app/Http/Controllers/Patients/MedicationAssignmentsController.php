<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\PatientMedicationAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicationAssignmentsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('patients'), 403);

        $patient = $user->patient()->first();

        $assignments = $patient
            ? PatientMedicationAssignment::query()
                ->where('patient_id', (int) $patient->id)
                ->orderByRaw(
                    "CASE status "
                    . "WHEN 'active' THEN 0 "
                    . "WHEN 'completed' THEN 1 "
                    . "ELSE 2 END"
                )
                ->orderByDesc('starts_on')
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('patients.assignments.index', [
            'patient' => $patient,
            'activeAssignments' => $assignments->where(
                'status',
                PatientMedicationAssignment::STATUS_ACTIVE
            )->values(),
            'historyAssignments' => $assignments->where(
                'status',
                '!=',
                PatientMedicationAssignment::STATUS_ACTIVE
            )->values(),
        ]);
    }
}
