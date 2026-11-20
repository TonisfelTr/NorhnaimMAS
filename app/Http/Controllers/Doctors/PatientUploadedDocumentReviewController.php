<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\MedicineDocument;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientUploadedDocumentReviewController extends Controller
{
    public function store(Request $request, Patient $patient, MedicineDocument $document): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user && $user->can('doctor_document_upload'), 403);

        $doctor = $user->doctor()->firstOrFail();

        abort_unless(
            (int) $document->patient_id === (int) $patient->id
            && $document->source === 'patient',
            404
        );

        $document->reviewed_at = now();
        $document->reviewed_by_doctor_id = $doctor->id;
        $document->save();

        return back()->with('success', 'Отметка об ознакомлении с документом сохранена.');
    }
}
