<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\DirectionPrintRequest;
use App\Http\Requests\StoreLabResearchRequest;
use App\Models\Clinic;
use App\Models\LabResearch;
use App\Models\LabResearchResult;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ResearchController extends Controller
{
    public function store(StoreLabResearchRequest $request, Patient $patient)
    {
        $labResearch = new LabResearch();
        $labResearch->planned_at = $request->planned_at;
        $labResearch->priority = $request->priority;
        $labResearch->status = $request->status;
        $labResearch->laboratory = $request->laboratory ?: 'Анализ от ' . Carbon::now()->format('d.m.Y H:i');
        $labResearch->comment = $request->comment;
        $labResearch->parameters = $request->param_ids;
        $labResearch->patient_id = $patient->id;
        $labResearch->doctor_id = auth()->user()->doctor()?->first()->id ?? 18;
        $labResearch->sample_type = $request->sampleType;
        $labResearch->save();

        return redirect()->back()->with('success', 'Запись анализа успешно создана!');
    }

    public function update(Request $request, Patient $patient, LabResearch $labResearch)
    {
        $params = $request->params;

        DB::beginTransaction();
        try {
            foreach ($params as $param) {
                $entityParam = new LabResearchResult();
                $entityParam->lab_research_id = $labResearch->id;
                $entityParam->lab_parameter_id = $param['id'];
                $entityParam->value = $param['value'];
                $entityParam->patient_id = $patient->id;
                $entityParam->save();
            }
        } catch (\Exception $e) {
            DB::rollback();
            $labResearch->status = $request->status;
            $labResearch->save();
        }
        DB::commit();


        $labResearch->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Исследование было изменено!');
    }

    public function delete(Request $request, LabResearch $labResearch)
    {
        DB::beginTransaction();

        try {
            LabResearchResult::where('lab_research_id', $labResearch->id)->delete();

            $labResearch->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Анализ удалён',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Не удалось удалить анализ: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function print(DirectionPrintRequest $request, LabResearch $labResearch, Patient $patient)
    {
        $doctor = $patient->doctor()->first();

        if (!$doctor && auth()->check()) {
            $doctor = auth()->user()->doctor()->first();
        }

        $clinic = null;

        if ($doctor) {
            if (method_exists($doctor, 'clinic')) {
                $clinic = $doctor->clinic()->first();
            }

            if (!$clinic && !empty($doctor->clinic_id)) {
                $clinic = Clinic::find($doctor->clinic_id);
            }
        }

        $row = DB::select("
            SELECT
                jsonb_agg(
                    jsonb_build_object(
                        'parameter_id', p.id,
                        'parameter_name', p.name,
                        'unit', p.unit,
                        'data_type', p.data_type,
                        'normal_values', COALESCE(p.normal_values, '[]'::jsonb),
                    'result_raw', r.value
                ) ORDER BY p.\"group\", p.name
                ) AS parameters
            FROM lab_research_results r
                JOIN lab_parameters p ON r.lab_parameter_id = p.id
            WHERE r.lab_research_id = ?
            GROUP BY r.lab_research_id
        ", [$labResearch->id]);

        $rawParameters = $row[0]->parameters ?? null;

        if (empty($rawParameters)) {
            $parametersPrepared = [];
        } else {
            $decoded = is_string($rawParameters) ? json_decode($rawParameters) : $rawParameters;

            // определяем пол пациента (M/F/any) и возраст в годах (если есть)
            $patientGender = strtoupper($patient->gender ?? ($patient->sex ?? 'any'));
            if ($patientGender !== 'M' && $patientGender !== 'F') {
                $patientGender = 'ANY';
            }

            // поиск даты рождения в нескольких возможных полях
            $dob = null;
            foreach (['dob', 'birth_date', 'date_of_birth', 'birthday'] as $f) {
                if (!empty($patient->$f)) {
                    $dob = $patient->$f;
                    break;
                }
            }
            $patientAge = null;
            if ($dob) {
                try {
                    $patientAge = \Carbon\Carbon::parse($dob)->age;
                } catch (\Exception $e) {
                    $patientAge = null;
                }
            }

            $parametersPrepared = [];
            foreach ($decoded as $p) {
                $paramId = $p->parameter_id ?? null;
                $name = $p->parameter_name ?? '';
                $unit = $p->unit ?? '';
                $dataType = $p->data_type ?? '';
                $resultRaw = $p->result_raw ?? null;
                $referenceRanges = $p->reference_ranges ?? [];
                $normalValues = $p->normal_values ?? [];

                // приводим result к числу если numeric и валидно
                $resultValue = $resultRaw;
                if ($dataType === 'numeric' && is_string($resultRaw) && preg_match('/^[+-]?[0-9]+(\\.[0-9]+)?$/', $resultRaw)) {
                    $resultValue = (float) $resultRaw;
                } elseif ($dataType === 'numeric' && is_numeric($resultRaw)) {
                    $resultValue = (float) $resultRaw;
                }

                // форматирование числа: максимум 4 знака после запятой, без лишних нулей
                $formatNumber = function ($v) {
                    if ($v === null || $v === '') return '';
                    if (!is_numeric($v)) return (string)$v;
                    return rtrim(rtrim(sprintf('%.4f', (float)$v), '0'), '.');
                };

                $formattedResult = $formatNumber($resultValue);
                if ($formattedResult === '' && $resultRaw !== null) {
                    $formattedResult = (string)$resultRaw;
                }

                // проверка пола
                $sexMatches = function ($rrSex, $patientGender) {
                    if ($rrSex === null) return true;
                    $rrSexUp = strtoupper((string)$rrSex);
                    if ($rrSexUp === 'ANY' || $rrSexUp === '') return true;
                    return $rrSexUp === strtoupper($patientGender);
                };

                // собираем matchingRanges (по полу и возрасту)
                $matchingRanges = [];
                if (is_array($referenceRanges) || is_object($referenceRanges)) {
                    foreach ($referenceRanges as $rr) {
                        $rrSex = $rr->sex ?? ($rr['sex'] ?? null);
                        if (!$sexMatches($rrSex, $patientGender)) continue;

                        $ageMin = isset($rr->age_min_y) ? (int)$rr->age_min_y : null;
                        $ageMax = isset($rr->age_max_y) ? (int)$rr->age_max_y : null;

                        if ($patientAge !== null) {
                            if ($ageMin !== null && $patientAge < $ageMin) continue;
                            if ($ageMax !== null && $patientAge > $ageMax) continue;
                        }

                        $matchingRanges[] = $rr;
                    }
                }

                $referenceUsed = '';
                if (empty($matchingRanges)) {
                    // fallback: сначала sex == any
                    $fallback = [];
                    foreach ($referenceRanges as $rr) {
                        $rrSex = $rr->sex ?? ($rr['sex'] ?? null);
                        $rrSexUp = strtoupper((string)$rrSex);
                        if ($rrSexUp === 'ANY' || $rrSexUp === '') {
                            $fallback[] = $rr;
                        }
                    }
                    if (!empty($fallback)) {
                        $matchingRanges = $fallback;
                        $referenceUsed = 'fallback_any_sex';
                    } else {
                        if (!empty($referenceRanges)) {
                            $matchingRanges = (array)$referenceRanges;
                            $referenceUsed = 'fallback_all';
                        } else {
                            $matchingRanges = [];
                        }
                    }
                }

                // --- НОВОЕ: формируем reference_text только из числовых диапазонов min-max ---
                $rangeParts = [];
                foreach ($matchingRanges as $rr) {
                    $min = $rr->min ?? ($rr['min'] ?? null);
                    $max = $rr->max ?? ($rr['max'] ?? null);

                    if ($min === null || $max === null) {
                        // пропускаем неполные записи (нет min или max)
                        continue;
                    }

                    // форматируем числа и соединяем через дефис (мин-мах)
                    $minF = $formatNumber($min);
                    $maxF = $formatNumber($max);

                    if ($minF !== '' && $maxF !== '') {
                        // используем '-' как разделитель (пример: 4.2-5.6)
                        $rangeParts[] = $minF . '-' . $maxF;
                    }
                }

                // reference_text — чистые диапазоны, объединённые запятой
                $referenceTextOnlyRanges = empty($rangeParts) ? '' : implode(', ', $rangeParts);

                // вычисляем out_of_range по первому matching range (если numeric)
                $outOfRange = null;
                if ($dataType === 'numeric' && is_numeric($resultValue) && !empty($matchingRanges)) {
                    $first = $matchingRanges[0];
                    $min = $first->min ?? ($first['min'] ?? null);
                    $max = $first->max ?? ($first['max'] ?? null);
                    if (is_numeric($min) && $resultValue < (float)$min) $outOfRange = true;
                    if (is_numeric($max) && $resultValue > (float)$max) $outOfRange = true;
                    if ($outOfRange === null) $outOfRange = false;
                }

                $parametersPrepared[] = [
                    'parameter_id'     => $paramId,
                    'parameter_name'   => $name,
                    'unit'             => $unit,
                    'data_type'        => $dataType,
                    'result_raw'       => $resultRaw,
                    'result_value'     => $resultValue,
                    'formatted_result' => $formattedResult,
                    'reference_ranges' => $referenceRanges,
                    // здесь — только числа вида "4.2-5.6" или пустая строка
                    'reference_text'   => $referenceTextOnlyRanges,
                    'reference_used'   => $referenceUsed,
                    'out_of_range'     => $outOfRange,
                    'normal_values'    => $normalValues,
                ];
            }
        }

        return view('doctors.prescriptions.direction', [
            'doctor' => $doctor,
            'clinic' => $clinic,
            'patient' => $patient,
            'parameters' => $parametersPrepared,
            'labResearch' => $labResearch,
        ]);
    }


    /**
     * Отметить результат лабораторного исследования
     * как просмотренный врачом.
     */
    public function markAsRead(LabResearch $labResearch)
    {
        $markedNow = false;

        if (is_null($labResearch->result_showed_at)) {
            $labResearch->result_showed_at = now();
            $labResearch->save();

            $markedNow = true;
        }

        $labResearch->refresh();

        return response()->json([
            'success' => true,
            'marked_now' => $markedNow,
            'research_id' => $labResearch->id,
            'result_showed_at' => $labResearch->result_showed_at,
        ]);
    }

    public function changeResultValue(
        Request $request,
        LabResearch $labResearch,
        int $parameter
    ) {
        /*
         * Проверка permission ОБЯЗАТЕЛЬНО на сервере.
         * Скрытия поля в Blade недостаточно.
         */
        abort_unless(
            auth()->check()
            && auth()->user()->can('doctor.analyses.change'),
            403,
            'Недостаточно прав для изменения результата анализа.'
        );

        $request->validate([
            'value' => ['required'],
        ]);

        $value = $request->input('value');

        if (!is_scalar($value)) {
            return response()->json([
                'success' => false,
                'message' => 'Некорректное значение.',
            ], 422);
        }

        $value = trim((string) $value);

        if ($value === '') {
            return response()->json([
                'success' => false,
                'message' => 'Значение не может быть пустым.',
            ], 422);
        }

        if (mb_strlen($value) > 255) {
            return response()->json([
                'success' => false,
                'message' => 'Значение слишком длинное.',
            ], 422);
        }

        /*
         * Ищем конкретный результат этого параметра
         * именно в указанном исследовании.
         */
        $result = LabResearchResult::query()
            ->where('lab_research_id', $labResearch->id)
            ->where('lab_parameter_id', $parameter)
            ->orderByDesc('id')
            ->firstOrFail();

        $oldValue = $result->value;

        $result->value = $value;
        $result->save();

        return response()->json([
            'success' => true,
            'message' => 'Значение изменено.',
            'research_id' => $labResearch->id,
            'parameter_id' => $parameter,
            'result_id' => $result->id,
            'old_value' => $oldValue,
            'value' => $result->value,
        ]);
    }

    public function fillMissingResultValue(
        Request $request,
        LabResearch $labResearch,
        int $parameter
    ) {
        $doctorId = auth()->user()
            ?->doctor()
            ->first()
            ?->id;

        abort_unless(
            $doctorId,
            403,
            'Профиль врача не найден.'
        );

        $patient = Patient::query()
            ->findOrFail($labResearch->patient_id);

        /*
         * Только лечащий врач пациента.
         */
        abort_unless(
            (int) $patient->doctor_id === (int) $doctorId,
            403,
            'Заполнять отсутствующий результат может только лечащий врач пациента.'
        );

        /*
         * Параметр должен входить в это исследование.
         */
        $parameterIds = collect($labResearch->parameters ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique();

        abort_unless(
            $parameterIds->contains($parameter),
            404,
            'Параметр не относится к данному исследованию.'
        );

        $data = $request->validate([
            'value' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $value = trim((string) $data['value']);

        if ($value === '') {
            return response()->json([
                'success' => false,
                'message' => 'Введите значение результата.',
            ], 422);
        }

        $result = DB::transaction(function () use (
            $labResearch,
            $patient,
            $parameter,
            $value
        ) {
            $result = LabResearchResult::query()
                ->where(
                    'lab_research_id',
                    $labResearch->id
                )
                ->where(
                    'lab_parameter_id',
                    $parameter
                )
                ->lockForUpdate()
                ->first();

            /*
             * Уже заполненное значение этим методом
             * изменять нельзя.
             */
            if ($result) {
                $currentValue = trim(
                    (string) $result->value
                );

                $isMissing = in_array(
                    $currentValue,
                    ['', '-', '—'],
                    true
                );

                if (!$isMissing) {
                    abort(
                        409,
                        'Результат уже заполнен.'
                    );
                }
            }

            /*
             * Если строки результата ещё нет —
             * создаём.
             */
            if (!$result) {
                $result = new LabResearchResult();

                $result->lab_research_id =
                    $labResearch->id;

                $result->lab_parameter_id =
                    $parameter;

                $result->patient_id =
                    $patient->id;
            }

            $result->value = $value;
            $result->save();

            return $result;
        });

        return response()->json([
            'success' => true,
            'research_id' => $labResearch->id,
            'parameter_id' => $parameter,
            'result_id' => $result->id,
            'value' => $result->value,
            'message' => 'Значение сохранено.',
        ]);
    }
}
