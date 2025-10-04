<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\DirectionPrintRequest;
use App\Models\LabResearch;
use App\Models\LabResearchResult;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ResearchController extends Controller
{
    public function store(Request $request, Patient $patient)
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

    public function delete(Patient $patient, LabResearch $labResearch)
    {
        if ($labResearch->status != 'ordered') {
            return redirect()->back()->withErrors('Нельзя удалить запись анализа с этим статусом.');
        }

        $labResearch->delete();

        return redirect()->back();
    }

    public function print(DirectionPrintRequest $request, Patient $patient, LabResearch $labResearch)
    {
        $doctor = $patient->doctor()->first();
        $clinic = $doctor ? $doctor->clinic()->first() : null;

        $row = DB::select("
        SELECT
            jsonb_agg(
                jsonb_build_object(
                    'parameter_id', p.id,
                    'parameter_name', p.name,
                    'unit', p.unit,
                    'data_type', p.data_type,
                    'result_raw', r.value,
                    'reference_ranges', COALESCE(rrs.ranges, '[]'::jsonb)
                ) ORDER BY p.\"group\", p.name
            ) AS parameters
        FROM lab_research_results r
            JOIN lab_parameters p ON r.lab_parameter_id = p.id
            LEFT JOIN LATERAL (
                SELECT jsonb_agg(
                    jsonb_build_object(
                        'sex', rr.sex,
                        'age_min_y', rr.age_min_y,
                        'age_max_y', rr.age_max_y,
                        'min', rr.min,
                        'max', rr.max
                    ) ORDER BY rr.age_min_y, rr.sex
                ) AS ranges
                FROM lab_reference_ranges rr
                WHERE rr.parameter_id = p.id
            ) rrs ON true
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
}
