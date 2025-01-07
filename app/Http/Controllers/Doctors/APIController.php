<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use App\Models\Patient;
use Illuminate\Http\Request;

class APIController extends Controller
{
    public function searchPatient(Request $request)
    {
        $search = $request->query('q'); // Получаем текст из поиска

        // Получаем пациентов, удовлетворяющих критериям
        $patients = Patient::where('surname', 'ilike', "%{$search}%")
                           ->orWhere('name', 'ilike', "%{$search}%")
                           ->orWhere('patronym', 'ilike', "%{$search}%")
                           ->orWhereRaw("CONCAT(surname, ' ', LEFT(name, 1), '.', LEFT(patronym, 1), '.') ILIKE ?", ["%{$search}%"])
                           ->limit(10)
                           ->get()
                           ->map(function ($patient) {
                               return [
                                   'id' => $patient->id,
                                   'text' => "{$patient->surname} " . mb_substr($patient->name, 0, 1) . "." . mb_substr($patient->patronym, 0, 1) . ".",
                                   'birth_at' => $patient->birth_at
                               ];
                           });

        return response()->json(['results' => $patients]);
    }

    public function searchDrugs(Request $request)
    {
        $search = $request->query('q'); // Получаем текст из поиска
        $drugs = Drug::where('name', 'ilike', "%$search%")
                     ->orWhere('latin_name', 'ilike', "%{$search}%")
                     ->limit(10)
                     ->get()
                     ->map(function ($drug) {
                         return [
                             'id' => $drug->id, // Используем latin_name как value
                             'text' => $drug->name, // Используем title как отображаемый текст
                             'latin_name' => $drug->latin_name
                         ];
                     });

        return response()->json(['results' => $drugs]);
    }

    //public function

    public function getDrugForms($drugId)
    {
        $drug = Drug::where('latin_name', 'ilike', "%$drugId%")
                    ->orWhere('name', $drugId)
                    ->first();
        $drugStrict = $drug->strict;

        if (!$drug) {
            return [];
        }

        // Обработка данных из поля forms
        $forms = collect(json_decode($drug->forms, true))->map(function ($formDetails, $formName) use ($drugStrict){
            $formattedForms = [];
            foreach ($formDetails as $dose => $counts) {
                if (is_array($counts)) {
                    foreach($counts as $count) {
                        $formattedForms[] = [
                            'form'  => match($formName) {
                                'tablets' => 'Таблетки',
                                'ampules' => 'Ампулы',
                                'dragees' => 'Драже'
                            },
                            'dose'  => $dose,
                            'count' => $count,
                            'strict' => $drugStrict
                        ];
                    }
                } else {
                    $formattedForms[] = [
                        'form' => match($formName) {
                            'tablets' => 'Таблетки',
                            'ampules' => 'Ампулы',
                            'dragees' => 'Драже'
                        },
                        'dose' => $dose,
                        'count' => $counts,
                        'strict' => $drugStrict
                    ];
                }
            }
            return $formattedForms;
        })->flatten(1);

        return response()->json($forms);
    }
}
