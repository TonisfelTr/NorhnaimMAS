<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageDiagnoseRequest;
use App\Models\Diagnose;
use App\Models\DiagnoseSymptom;
use App\Models\Symptom;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DiagnoseController extends Controller
{
    public function index(): View {
        $diagnoses = Diagnose::orderBy('code')->paginate(20);

        return view('adminpanel.sub-dictionaries.diagnoses', compact('diagnoses'));
    }

    public function edit(int $diagnoseID): View {
        $diagnose = Diagnose::with(['requiredSymptoms', 'relativeSymptoms'])->findOrFail($diagnoseID);
        $symptoms = Symptom::all();

        return view('adminpanel.service.diagnose_edit', compact('diagnose', 'symptoms'));
    }

    public function save(ManageDiagnoseRequest $request, int $diagnoseID): RedirectResponse {
        $diagnose = Diagnose::findOrFail($diagnoseID);
        $diagnose->code = $request->post('code');
        $diagnose->title = $request->post('title');
        $diagnose->description = $request->post('description');
        $diagnose->save();

        DiagnoseSymptom::where('diagnose_id', $diagnoseID)->delete();

        foreach ($request->post('symptoms') as $symptomID) {
            DiagnoseSymptom::create([
                'diagnose_id' => $diagnoseID,
                'symptom_id' => $symptomID
            ]);
        }

        return redirect()->route('admin.dictionary.diagnoses')->with([
            'status' => 200,
            'message' => "Диагноз \"{$diagnose->title}\" был успешно отредактирован."
        ]);
    }
}
