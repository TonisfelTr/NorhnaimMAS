<?php

namespace App\Support\CardDocuments;

use App\Models\LabParameter;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

final class DocumentSources
{
    // Не возвращаем тихо усечённую выписку: просим уменьшить период.
    public const MAX_ROWS_PER_SOURCE = 500;
    public const MAX_SNAPSHOT_BYTES = 2000000;

    public function patientSnapshot(Patient $patient): array
    {
        return [
            'name' => trim(implode(' ', array_filter([
                $patient->surname, $patient->name, $patient->patronym,
            ], static fn ($value) => $value !== null && $value !== ''))),
            'birth_at' => $this->date($patient->getRawOriginal('birth_at')),
            'medcard_number' => (string) ($patient->medcard_number ?? ''),
        ];
    }

    public function date(mixed $value): string
    {
        if (!$value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('d.m.Y');
        } catch (\Throwable) {
            return '';
        }
    }

    public function doctorName(Model $doctor): string
    {
        return trim(implode(' ', array_filter([
            $doctor->surname, $doctor->name, $doctor->patronym,
        ]))) ?: (string) ($doctor->full_name ?? 'Врач #' . $doctor->getKey());
    }

    public function build(Patient $patient, array $options): array
    {
        $selected = $options['source_options'] ?? [];
        if ($selected === []) {
            return [];
        }

        $range = ($options['period_mode'] ?? 'all') === 'range';
        $from = $range ? Carbon::parse($options['period_from'])->startOfDay() : null;
        $to = Carbon::parse($range ? $options['period_to'] : $options['issued_at'])->endOfDay();
        $result = [];

        foreach ($selected as $key) {
            $label = DocumentCatalog::SOURCES[$key] ?? null;
            abort_unless($label, 422, 'Неизвестный источник.');
            [$relationName, $dateColumns] = match ($key) {
                'anamneses' => ['anamneses', ['created_at']],
                'epicrises' => ['epicrises', ['created_at']],
                'prescriptions' => ['prescriptions', ['created_at']],
                'labs' => ['labResearches', ['collected_at', 'planned_at', 'created_at']],
                'instrumentals' => ['instrumentalResearches', ['result_at', 'performed_at', 'created_at']],
            };

            $query = $patient->{$relationName}();
            $table = $query->getRelated()->getTable();
            $columns = array_map(static fn (string $column): string => $table . '.' . $column, $dateColumns);
            $dateSql = count($columns) === 1 ? $columns[0] : 'COALESCE(' . implode(', ', $columns) . ')';
            $exclusiveEnd = $to->copy()->addDay()->startOfDay();
            $query->reorder()->whereRaw($dateSql . ' < ?', [$exclusiveEnd->toDateTimeString()]);
            if ($from) {
                $query->whereRaw($dateSql . ' >= ?', [$from->toDateTimeString()]);
            }
            if (in_array($key, ['labs', 'instrumentals'], true)) {
                $query->where($table . '.status', 'ready');
            }
            if ($key === 'labs') {
                $query->with('results');
            }
            if ($key === 'prescriptions') {
                $query->with('drug');
            }
            $rows = $query->orderByRaw($dateSql . ' ASC')->orderBy($table . '.id')
                ->limit(self::MAX_ROWS_PER_SOURCE + 1)->get();
            if ($rows->count() > self::MAX_ROWS_PER_SOURCE) {
                throw ValidationException::withMessages([
                    'source_options' => 'В разделе «' . $label . '» больше '
                        . self::MAX_ROWS_PER_SOURCE . ' записей. Уменьшите период выписки.',
                ]);
            }

            $parameters = collect();
            if ($key === 'labs') {
                $ids = $rows->flatMap(fn ($row) => $row->results->pluck('lab_parameter_id'))->unique();
                $parameters = LabParameter::query()->whereIn('id', $ids)->get()->keyBy('id');
            }

            $items = [];
            foreach ($rows as $row) {
                $rawDate = null;
                foreach ($dateColumns as $column) {
                    $rawDate = $row->getRawOriginal($column);
                    if ($rawDate) {
                        break;
                    }
                }
                $parts = [];
                $title = $label;
                if ($key === 'anamneses') {
                    $title = $row->title ?: 'Запись анамнеза';
                    $parts['Текст записи'] = DocumentText::plain($row->text);
                } elseif ($key === 'epicrises') {
                    $title = 'Эпикриз';
                    $parts['Текст'] = DocumentText::plain($row->text);
                    $parts['Диагноз в записи'] = trim(implode(' ', array_filter([
                        $row->mkb10, $row->diagnosis_title_display ?? $row->diagnosis_title,
                    ])));
                } elseif ($key === 'prescriptions') {
                    $title = (string) ($row->drug?->name ?: $row->generic_name ?: 'Рецепт');
                    $parts['Указания в рецепте'] = DocumentText::plain($row->usage_instructions);
                    $parts['Рецептурный бланк'] = (string) ($row->prescription_form ?? '');
                } elseif ($key === 'labs') {
                    $title = (string) ($row->laboratory ?: 'Лабораторный анализ');
                    $parts['Материал'] = (string) ($row->sample_type ?? '');
                    foreach ($row->results as $labResult) {
                        $param = $parameters->get($labResult->lab_parameter_id);
                        $name = (string) ($param?->name ?: 'Показатель #' . $labResult->lab_parameter_id);
                        // id предотвращает потерю строк с одинаковым названием показателя.
                        $parts[$name . ' [#' . $labResult->lab_parameter_id . ']'] =
                            DocumentText::plain($labResult->value) . ($param?->unit ? ' ' . $param->unit : '');
                    }
                    $parts['Комментарий'] = DocumentText::plain($row->comment);
                } elseif ($key === 'instrumentals') {
                    $title = (string) ($row->name ?: 'Инструментальное исследование');
                    foreach ([
                        'organization' => 'Организация', 'specialist_name' => 'Специалист',
                        'description' => 'Описание', 'conclusion' => 'Заключение',
                        'recommendations' => 'Рекомендации',
                    ] as $field => $partLabel) {
                        $parts[$partLabel] = DocumentText::plain($row->{$field});
                    }
                }

                $items[] = [
                    'id' => $row->getKey(), 'title' => DocumentText::plain($title),
                    'date' => $this->date($rawDate),
                    'parts' => array_filter($parts, static fn ($value) => $value !== ''),
                ];
            }
            $result[] = [
                'key' => $key, 'label' => $label, 'items' => $items,
                'note' => $key === 'prescriptions'
                    ? 'Это история выписанных рецептов, а не подтверждённая актуальная терапия.' : '',
            ];
            if (strlen(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) > self::MAX_SNAPSHOT_BYTES) {
                throw ValidationException::withMessages([
                    'source_options' => 'Слишком большой объём исходных записей. Уменьшите период или число источников.',
                ]);
            }
        }

        return $result;
    }
}
