<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\LabResearchTemplate;
use App\Models\Patient;
class MedicalCardController extends Controller {
    public function __invoke(Patient $patient)
    {
        $anamneses = $patient->anamneses()->orderBy('created_at', 'desc')->get();
        $researches = $patient->labResearches()->orderBy('created_at', 'desc')->get();
//        $psyTests

        if (config('app.debug')) {
            $labTemplates = LabResearchTemplate::orderBy('name', 'desc')->get();
        } else {
            $labTemplates = LabResearchTemplate::whereIn('doctor_id', [0, auth()->user()->doctor()->first()?->id])
                ->orderBy('name', 'desc')
                ->get();
        }
        $templatesMap = $labTemplates->mapWithKeys(function($t){
            $ids = is_array($t->lab_parameters) ? $t->lab_parameters : (json_decode($t->lab_parameters, true) ?: []);
            return [$t->id => array_values(array_unique(array_map('intval', $ids)))];
        });

        return view('doctors.reception.medical_card', compact('patient', 'anamneses', 'labTemplates',
                'researches', 'templatesMap'));
    }
}
