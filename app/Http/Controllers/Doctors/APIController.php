<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use App\Models\LabParameter;
use App\Models\LabResearchTemplate;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

    public function getDrugForms($drugId)
    {
        $drug = Drug::where('latin_name', 'ilike', "%$drugId%")
            ->orWhere('name', $drugId)
            ->first();

        if (!$drug) {
            return response()->json([]);
        }

        $drugStrict = $drug->strict;

        $formTranslations = [
            'tablets' => 'Таблетки',
            'ampules' => 'Ампулы',
            'dragees' => 'Драже',
            'capsules' => 'Капсулы',
        ];

        $forms = collect($drug->forms)->flatMap(function ($formDetails, $formKey) use ($formTranslations, $drugStrict) {
            $formName = $formTranslations[$formKey] ?? ucfirst($formKey);

            return collect($formDetails)->flatMap(function ($value, $dose) use ($formName, $drugStrict) {
                if (is_array($value)) {
                    return collect($value)->map(function ($qty, $volume) use ($formName, $dose, $drugStrict) {
                        return [
                            'form'   => $formName,
                            'dose'   => $dose,
                            'volume' => $volume,
                            'count'  => $qty,
                            'strict' => $drugStrict
                        ];
                    });
                } else {
                    // Значение — просто число: "25" => 10
                    return [[
                        'form'   => $formName,
                        'dose'   => $dose,
                        'volume' => null,
                        'count'  => $value,
                        'strict' => $drugStrict
                    ]];
                }
            });
        })->values();

        return response()->json($forms);
    }

    public function searchPatientFor(int $id)
    {
        $patient = Patient::findOrFail($id);

        return response()->json([
            'id' => $patient->id,
            'surname' => $patient->surname,
            'name' => $patient->name,
            'patronym' => $patient->patronym,
            'birth_at' => $patient->birth_at ? Carbon::parse($patient->birth_at)->format('Y-m-d') : '',
            'address_registration' => $patient->address_registration,
            'address_residence' => $patient->address_residence,
            'serial' => $patient->serial,
            'number' => $patient->number,
            'department_code' => $patient->department_code,
            'issued_by' => $patient->issued_by,
            'issued_at' => $patient->issued_at ? Carbon::parse($patient->issued_at)->format('Y-m-d') : '',
            'birth_place' => $patient->birth_place,
            'snils' => $patient->snils,
            'oms' => $patient->oms,
        ]);
    }

    public function searchLabParameterGroup(Request $request): JsonResponse
    {
        $groups = LabParameter::where('sample_type', $request->material)
            ->distinct()
            ->pluck('group');

        return response()->json([
            'groups' => $groups
        ]);
    }

    public function searchLabParameter(Request $request): JsonResponse
    {
        $params = LabParameter::where('name', 'ilike', '%' . $request->get('q') . '%');

        if ($group = $request->get('group')) {
            $params = $params->where('group', $group);
        }

        if ($sampleType = $request->get('sample_type')) {
            $params = $params->where('sample_type', $sampleType);
        }

        $params = $params->get();

        return response()->json($params);
    }

    public function storeLabTemplate(Request $request): JsonResponse
    {
        $template = new LabResearchTemplate();
        $template->name = $request->name;
        $template->lab_parameters = collect($request->lab_parameters)->map(fn ($current) => intval($current));
        $template->doctor_id = auth()->user()->doctor?->id ?? 18;
        $template->save();

        return response()->json($template);
    }
}
