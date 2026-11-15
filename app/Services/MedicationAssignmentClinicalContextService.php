<?php

namespace App\Services;

// PATIENT_TREATMENT_WIZARD_V12_RABBITMQ_CONTEXT

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MedicationAssignmentClinicalContextService
{
    public function build(
        int $patientId,
        array $baseContext = []
    ): array {
        $patient = Patient::query()->findOrFail(
            $patientId
        );

        return [
            'patient' => [
                'id' => (int) $patient->id,
                'birth_at' =>
                    $baseContext['birth_at']
                    ?? $patient->birth_at
                    ?? null,
                'gender' =>
                    $patient->gender
                    ?? $patient->sex
                    ?? null,
                'diagnosis_code' =>
                    $baseContext['diagnosis_code']
                    ?? null,
            ],

            'anamneses' =>
                $this->collectAnamneses($patient),

            'epicrises' =>
                $this->collectEpicrises($patient),
        ];
    }

    private function collectAnamneses(
        Patient $patient
    ): array {
        $limit = (int) config(
            'medgemma.context.anamneses_limit',
            5
        );

        if ($limit <= 0) {
            return [];
        }

        try {
            if (method_exists($patient, 'anamneses')) {
                $rows = $patient
                    ->anamneses()
                    ->latest('created_at')
                    ->limit($limit)
                    ->get();

                return $this->normalizeModels(
                    $rows,
                    'anamnesis'
                );
            }
        } catch (Throwable $e) {
            report($e);
        }

        return $this->collectFromTables(
            patientId: (int) $patient->id,
            tables: [
                'anamneses',
                'patient_anamneses',
                'anamnesis',
            ],
            type: 'anamnesis',
            limit: $limit
        );
    }

    private function collectEpicrises(
        Patient $patient
    ): array {
        $limit = (int) config(
            'medgemma.context.epicrises_limit',
            5
        );

        if ($limit <= 0) {
            return [];
        }

        try {
            if (method_exists($patient, 'epicrises')) {
                $rows = $patient
                    ->epicrises()
                    ->latest('created_at')
                    ->limit($limit)
                    ->get();

                return $this->normalizeModels(
                    $rows,
                    'epicrisis'
                );
            }
        } catch (Throwable $e) {
            report($e);
        }

        return $this->collectFromTables(
            patientId: (int) $patient->id,
            tables: [
                'epicrises',
                'patient_epicrises',
                'epicrisis',
            ],
            type: 'epicrisis',
            limit: $limit
        );
    }

    private function normalizeModels(
        Collection $rows,
        string $type
    ): array {
        return $this->limitSection(
            $rows
                ->map(
                    fn ($row) =>
                        $this->normalizeRecord(
                            $row,
                            $type
                        )
                )
                ->filter()
                ->values()
                ->all()
        );
    }

    private function collectFromTables(
        int $patientId,
        array $tables,
        string $type,
        int $limit
    ): array {
        foreach ($tables as $table) {
            try {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $columns = Schema::getColumnListing(
                    $table
                );

                $patientColumn = collect([
                    'patient_id',
                    'patient',
                    'patientId',
                ])->first(
                    fn ($column) =>
                        in_array(
                            $column,
                            $columns,
                            true
                        )
                );

                if (!$patientColumn) {
                    continue;
                }

                $query = DB::table($table)
                    ->where(
                        $patientColumn,
                        $patientId
                    );

                if (
                    in_array(
                        'created_at',
                        $columns,
                        true
                    )
                ) {
                    $query->orderByDesc(
                        'created_at'
                    );
                } elseif (
                    in_array(
                        'id',
                        $columns,
                        true
                    )
                ) {
                    $query->orderByDesc('id');
                }

                $rows = $query
                    ->limit($limit)
                    ->get();

                if ($rows->isEmpty()) {
                    continue;
                }

                return $this->limitSection(
                    $rows
                        ->map(
                            fn ($row) =>
                                $this->normalizeRecord(
                                    $row,
                                    $type
                                )
                        )
                        ->filter()
                        ->values()
                        ->all()
                );
            } catch (Throwable $e) {
                report($e);
            }
        }

        return [];
    }

    private function normalizeRecord(
        mixed $record,
        string $type
    ): ?array {
        if ($record instanceof Model) {
            $data = $record->getAttributes();

            /*
             * Include accessors when they exist in the current project.
             */
            foreach ([
                'preview',
                'date_title',
                'doctor_name_display',
                'diagnosis_title_display',
            ] as $accessor) {
                try {
                    if (
                        isset($record->{$accessor})
                        && !array_key_exists(
                            $accessor,
                            $data
                        )
                    ) {
                        $data[$accessor] =
                            $record->{$accessor};
                    }
                } catch (Throwable $e) {
                    // Optional accessor.
                }
            }
        } elseif (is_object($record)) {
            $data = (array) $record;
        } elseif (is_array($record)) {
            $data = $record;
        } else {
            return null;
        }

        $text = $this->firstMeaningful(
            $data,
            [
                'text',
                'content',
                'body',
                'description',
                'summary',
                'preview',
                'conclusion',
                'recommendations',
            ]
        );

        if ($text === '') {
            return null;
        }

        $item = [
            'type' => $type,
            'date' => $this->firstMeaningful(
                $data,
                [
                    'created_at',
                    'date',
                    'recorded_at',
                    'issued_at',
                ]
            ),
            'title' => $this->firstMeaningful(
                $data,
                [
                    'title',
                    'name',
                    'date_title',
                ]
            ),
            'diagnosis_code' =>
                $this->firstMeaningful(
                    $data,
                    [
                        'mkb10',
                        'diagnosis_code',
                        'diagnose_code',
                    ]
                ),
            'diagnosis_title' =>
                $this->firstMeaningful(
                    $data,
                    [
                        'diagnosis_title',
                        'diagnosis_title_display',
                    ]
                ),
            'text' => $this->truncate(
                $this->cleanText($text),
                (int) config(
                    'medgemma.context.item_max_chars',
                    3500
                )
            ),
        ];

        return array_filter(
            $item,
            static fn ($value) =>
                $value !== null
                && $value !== ''
        );
    }

    private function firstMeaningful(
        array $data,
        array $keys
    ): string {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];

            if ($value instanceof \DateTimeInterface) {
                return $value->format(
                    'Y-m-d H:i:s'
                );
            }

            if (
                is_scalar($value)
                && trim((string) $value) !== ''
            ) {
                return trim((string) $value);
            }
        }

        return '';
    }

    private function cleanText(
        string $text
    ): string {
        $text = html_entity_decode(
            strip_tags($text),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $text = preg_replace(
            '/[ \t]+/u',
            ' ',
            $text
        ) ?? $text;

        $text = preg_replace(
            '/\R{3,}/u',
            "\n\n",
            $text
        ) ?? $text;

        return trim($text);
    }

    private function truncate(
        string $text,
        int $maxChars
    ): string {
        if (
            $maxChars <= 0
            || mb_strlen($text) <= $maxChars
        ) {
            return $text;
        }

        return rtrim(
            mb_substr(
                $text,
                0,
                $maxChars
            )
        ) . '…';
    }

    private function limitSection(
        array $items
    ): array {
        $maxChars = (int) config(
            'medgemma.context.section_max_chars',
            12000
        );

        if ($maxChars <= 0) {
            return $items;
        }

        $result = [];
        $used = 0;

        foreach ($items as $item) {
            $serialized = json_encode(
                $item,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );

            $length = mb_strlen(
                (string) $serialized
            );

            if (
                $result !== []
                && ($used + $length) > $maxChars
            ) {
                break;
            }

            $result[] = $item;
            $used += $length;
        }

        return $result;
    }
}
