<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\EpicrisisStoreRequest;
use App\Models\Epicrisis;
use App\Models\Patient;
use Illuminate\Http\Request;

class EpicrisisController extends Controller
{
    public function store(EpicrisisStoreRequest $request, Patient $patient)
    {
        $epicrisis = new Epicrisis();
        $epicrisis->patient_id = $patient->id;
        $epicrisis->doctor_id = auth()->user()->doctor->id;
        $epicrisis->text = $request->text;
        $epicrisis->created_at = $request->created_at;
        $epicrisis->diagnose_id = $request->diagnose_id;
        $epicrisis->save();

        return redirect()->back()->with([
            'success' => 'ok',
            'message' => 'Эпикриз был успешно создан.'
        ]);
    }

    public function show(Patient $patient, Epicrisis $epicrisis)
    {
        return view('doctors.reception.epicrises.epicrisis', compact('patient', 'epicrisis'));
    }

    public function edit(Patient $patient, Epicrisis $epicrisis)
    {
        return view('doctors.reception.epicrises.edit', compact('patient', 'epicrisis'));
    }

    public function update(Request $request, Patient $patient, Epicrisis $epicrisis)
    {
        $epicrisis->text = $request->text;
        $epicrisis->diagnose_id = $request->diagnose_id;
        $epicrisis->created_at = $request->created_at;
        $epicrisis->save();

        return redirect()->route('doctors.patients.epicrisis.show', [$patient->id, $epicrisis->id])->with([
            'success' => 'ok',
            'message' => 'Эпикриз был успешно отредактирован.'
        ]);
    }
}
