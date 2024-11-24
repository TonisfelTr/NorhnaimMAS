<?php

namespace App\Http\Controllers\Adminpanel;

use App\Enums\MedicineTypesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDeleteRequest;
use App\Http\Requests\SaveDrugRequest;
use App\Http\Requests\StoreDrugRequest;
use App\Models\ContraindicationsType;
use App\Models\Diagnose;
use App\Models\Drug;
use app\Models\MedicineContraindication;
use app\Models\MedicineIndication;
use App\Models\SideEffect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DrugController extends Controller
{
    public function index()
    {
        $drugs = Drug::orderBy('id')->paginate(20);

        return view('adminpanel.sub-dictionaries.drugs', compact('drugs'));
    }

    public function store(StoreDrugRequest $request)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->post();

            unset($data['_token']);
            unset($data['g-recaptcha-response']);

            $sideEffects       = $data['side_effects'];
            $contraindications = $data['contraindications'];
            $dangerous         = $data['dangerous'];

            $forms          = $data['forms'];
            $concentrations = $data['concentration'];
            $doses          = $data['dose'];
            $count          = $data['count'];

            unset(
                $data['concentration'],
                $data['dose'],
                $data['count']
            );

            $arrayForms = [];
            foreach ($forms as $index => $form) {
                if ($form == 'ampules') {
                    $arrayForms['ampules'][] = [
                        (string)$concentrations[$index] => [
                            (string)$doses[$index] => $count[$index],
                        ],
                    ];
                } elseif ($form == 'tables') {
                    $arrayForms['tables'][] = [
                        (string)$doses[$index] => $count[$index],
                    ];
                } elseif ($form == 'dragees') {
                    $arrayForms['dragees'][] = [
                        (string)$doses[$index] => $count[$index],
                    ];
                }
            }
            $data['forms'] = $arrayForms;

            DB::beginTransaction();
            if ($drug = Drug::create($data)) {
                // Для противопоказаний
                foreach ($contraindications as $contraindication) {
                    $forContraindicationsData                        = [];
                    $forContraindicationsData['drug_id']             = $drug->id;
                    $forContraindicationsData['contraindication_id'] = $contraindication;
                    $forContraindicationsData['type']                = 1;

                    if (!MedicineContraindication::create($forContraindicationsData)) {
                        DB::rollback();

                        return redirect()->back()->withErrors(
                            [
                                'Не удалось добавить противопоказания к препарату.',
                            ]
                        );
                    }
                }

                // Для "с осторожностью"
                foreach ($dangerous as $dangerousItem) {
                    $forDangerousItem                        = [];
                    $forDangerousItem['drug_id']             = $drug->id;
                    $forDangerousItem['contraindication_id'] = $dangerousItem;
                    $forDangerousItem['type']                = 2;

                    if (MedicineContraindication::create($forDangerousItem)) {
                        DB::rollback();

                        return redirect()->back()->withErrors([
                            'Не удалось добавить противопоказания "с осторожностью"',
                        ]);
                    }
                }

                // Для побочных эффектов
                foreach ($sideEffects as $sideEffect) {
                    $forSideEffectsData                   = [];
                    $forSideEffectsData['drug_id']        = $drug->id;
                    $forSideEffectsData['side_effect_id'] = $sideEffect;

                    if (!(SideEffect::create($forSideEffectsData))) {
                        DB::rollback();

                        return redirect()->back()->with([
                            'Не удалось добавить побочные эффекты',
                        ]);
                    }
                }

                return redirect()->route('admin.dictionary.drugs')->with([
                    'status'  => 'drugs.success',
                    'message' => 'Лекарство "' .
                                 $request->post('name') .
                                 '" добавлено.',
                ]);
            }
        }

        $contraindications = ContraindicationsType::all();
        $sideEffects       = SideEffect::all();
        $dangerous         = ContraindicationsType::all();
        $drug              = Drug::newModelInstance();
        $groups            = MedicineTypesEnum::getAllMatches();

        return view(
            'adminpanel.service.drugs_new',
            compact('drug', 'dangerous', 'sideEffects', 'contraindications', 'groups')
        );
    }

    public function edit(int $drugID)
    {
        $drug = Drug::with([
            'contraindications' => function ($query) {
                $query->where('type', 0);
            },
            'dangerous'         => function ($query) {
                $query->where('type', 1);
            },
            'receptors',
            'side_effects',
            'diagnoses'
        ])->findOrFail($drugID);

        $groups = Drug::select('group')->distinct()->get();

        $contraindications         = ContraindicationsType::all();
        $dangerous                 = $contraindications;
        $side_effects              = SideEffect::all();
        $indications               = Diagnose::all();
        $generics                  = $drug->generics; // Приводим к массиву сразу
        $selectedSideEffects       = $drug->side_effects->pluck('id', 'name')->toArray();
        $selectedContraindications = $drug->contraindications->pluck('id', 'name')->toArray();
        $selectedDangerous         = $drug->dangerous->pluck('id', 'name')->toArray();
        $selectedIndications       = $drug->diagnoses->pluck('code', 'title')->toArray();

        return view(
            'adminpanel.service.drugs_edit',
            compact(
                'drug',
                'groups',
                'contraindications',
                'dangerous',
                'indications',
                'generics',
                'side_effects',
                'selectedSideEffects',
                'selectedContraindications',
                'selectedDangerous',
                'selectedIndications'
            )
        );
    }

    public function save(SaveDrugRequest $request, int $drugID): RedirectResponse
    {
        $drug = Drug::findOrFail($drugID);

        $drug->name           = $request->post('name');
        $drug->group          = $request->post('group');
        $drug->latin_name     = $request->post('latin_name');
        $drug->ht_output_to   = $request->post('ht_output_to');
        $drug->ht_output_from = $request->post('ht_output_from');
        $drug->preferential   = $request->post('preferential') ?? 0;
        $drug->description    = $request->post('description');
        $drug->strict         = $request->post('strict') ?? 0;

        $forms         = $request->post('forms');
        $concentration = $request->post('concentration');
        $dose          = $request->post('dose');
        $count         = $request->post('count');

        // Инициализация результирующего массива
        $result = [];

        foreach ($forms as $index => $form) {
            $doseValue  = $dose[$index];
            $countValue = $count[$index];

            if ($form === 'ampules') {
                $concentrationValue = $concentration[$index];

                if (!isset($result[$form])) {
                    $result[$form] = [];
                }

                if (!isset($result[$form][$concentrationValue])) {
                    $result[$form][$concentrationValue] = [];
                }

                // Используем массив для хранения нескольких значений количества для одной концентрации
                if (!isset($result[$form][$concentrationValue][$doseValue])) {
                    $result[$form][$concentrationValue][$doseValue] = [];
                }

                // Добавляем значения в виде массива
                $result[$form][$concentrationValue][$doseValue][] = $countValue;
            } else {
                if (!isset($result[$form])) {
                    $result[$form] = [];
                }

                // Если дозировка уже существует, добавляем в массив
                if (isset($result[$form][$doseValue])) {
                    if (is_array($result[$form][$doseValue])) {
                        $result[$form][$doseValue][] = $countValue;
                    } else {
                        $result[$form][$doseValue] = [$result[$form][$doseValue], $countValue];
                    }
                } else {
                    $result[$form][$doseValue] = [$countValue];
                }
            }
        }

        $drug->forms = json_encode($result, JSON_UNESCAPED_UNICODE);

        // Получаем идентификаторы диагнозов по переданным кодам
        $requestedDiagnoseIds = DB::table('diagnoses')
                                  ->whereIn('code', $request->post('indications'))
                                  ->pluck('id')
                                  ->toArray();

        // Получаем текущие диагнозы для данного препарата из таблицы medicine_indications
        $existingDiagnoseIds = DB::table('medicine_indications')
                                 ->where('medicine_id', $drugID)
                                 ->pluck('diagnose_id')
                                 ->toArray();

        // Определяем диагнозы, которые нужно добавить и удалить
        $toAdd = array_diff($requestedDiagnoseIds, $existingDiagnoseIds);
        $toRemove = array_diff($existingDiagnoseIds, $requestedDiagnoseIds);

        // Добавляем отсутствующие диагнозы
        $newIndications = array_map(fn($diagnoseId) => [
            'medicine_id' => $drugID,
            'diagnose_id' => $diagnoseId,
        ], $toAdd);

        DB::table('medicine_indications')->insert($newIndications);

        // Удаляем диагнозы, которых нет в запросе
        DB::table('medicine_indications')
          ->where('medicine_id', $drugID)
          ->whereIn('diagnose_id', $toRemove)
          ->delete();

        // Получаем список дженериков
        $generics = $request->post('generics');
        DB::table('generics')
            ->where('drug_id', $drugID)
            ->delete();

        foreach ($generics as $generic) {
            DB::table('generics')
                ->insert([
                    'drug_id' => $drugID,
                    'name' => $generic
                ]);
        }

        $contraindicationsFromRequest = $request->post('contraindications');
        $type = 1;
        DB::transaction(function () use ($drugID, $contraindicationsFromRequest, $type) {
            // Получаем текущие противопоказания для препарата
            $currentContraindications = DB::table('medicine_contraindications')
                                          ->where('drug_id', $drugID)
                                          ->pluck('contraindication_id')
                                          ->toArray();

            // Преобразуем противопоказания из запроса в массив
            $requestContraindications = collect($contraindicationsFromRequest);

            // Определяем противопоказания для добавления
            $toAdd = $requestContraindications->diff($currentContraindications);

            // Определяем противопоказания для удаления
            $toRemove = collect($currentContraindications)->diff($requestContraindications);

            // Добавляем новые противопоказания
            $toAdd->each(function ($contraindicationId) use ($drugID, $type) {
                DB::table('medicine_contraindications')->insert([
                    'drug_id' => $drugID,
                    'contraindication_id' => $contraindicationId,
                    'type' => $type,
                ]);
            });

            // Удаляем лишние противопоказания
            if ($toRemove->isNotEmpty()) {
                DB::table('medicine_contraindications')
                  ->where('drug_id', $drugID)
                  ->whereIn('contraindication_id', $toRemove)
                  ->delete();
            }
        });

        if ($drug->save()) {
            return redirect()->route('admin.dictionary.drugs')->with(
                ['status' => 'success', 'message' => 'Изменения в лекарстве были сохранены!']
            );
        } else {
            return redirect()->back()->withErrors(['message' => 'Не удалось сохранить изменения.']);
        }
    }


    public function massDelete(MassDeleteRequest $request)
    {
        Drug::whereIn('id', $request->selected)->delete();

        return redirect()->route('admin.dictionary.drugs')->with([
            'status'  => 'drug.success',
            'message' => 'Выбранные лекарства были удалены из словаря.',
        ]);
    }

    public function delete(int $drugID)
    {
        $drug     = Drug::whereId($drugID);
        $drugName = $drug->firstOrFail()->name;
        $drug->delete();

        return redirect()->route('admin.dictionary.drugs')->with([
            'status'  => 'drugs.success',
            'message' => "Лекарство \"{$drugName}\" было удалено.",
        ]);
    }
}
