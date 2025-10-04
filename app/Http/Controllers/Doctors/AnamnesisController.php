<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnamnesesStoreRequest;
use App\Models\Anamnesis;
use App\Models\Diagnose;
use App\Models\Patient;
use App\Models\PatientSymptom;
use app\Models\Symptom;
use Illuminate\Http\Request;

class AnamnesisController extends Controller
{
    public function show(Request $request, Patient $patient, Anamnesis $anamnesis)
    {
        abort_if($anamnesis->patient_id !== $patient->id, 403);
        $symptoms = $anamnesis->symptoms()->get();

        return view('doctors.reception.anamnesis', compact('anamnesis', 'patient', 'symptoms'));
    }

    public function store(AnamnesesStoreRequest $request, Patient $patient)
    {
        $user = auth()->user();
        $diagnose = Diagnose::where('code', $request->diagnosis_code)->pluck('id')->first();

        if ($setAsCurrent = $request->set_as_current == true) {
            Anamnesis::where('is_current', true)->update(['is_current' => false]);
        }

        $anamnesis = new Anamnesis();
        $anamnesis->patient_id = $patient->id;
        $anamnesis->doctor_id = config('app.debug') ? 1 : $user->doctor()?->first()->id;
        $anamnesis->text = $request->text;
        $anamnesis->category = $request->category;
        $anamnesis->title = $request->title;
        $anamnesis->source = $request->diag_source;
        $anamnesis->diagnose_id = $diagnose;
        $anamnesis->is_current = $setAsCurrent;
        $anamnesis->save();

        if ($request->boolean('set_as_current')) {
            $patient->diagnose_id = $diagnose;
            $patient->save();
        }

        $rawSymptoms = Symptom::whereIn('title', json_decode($request->symptoms_json))->pluck('id');

        foreach ($rawSymptoms as $_symptom) {
            PatientSymptom::firstOrCreate(
                [
                    'patient_id' => $patient->id,
                    'symptom_id' => $_symptom
                ],
                [
                    'anamnesis_id' => $anamnesis->id
                ]
            );
        }

        return redirect()->route('doctors.patients.medical_card', ['patient' => $patient->id])
            ->with('success', 'Анамнез был успешно сохранён!');
    }
}
