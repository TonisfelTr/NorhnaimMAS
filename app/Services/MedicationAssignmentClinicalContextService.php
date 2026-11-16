<?php

namespace App\Services;

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
        $patient = Patient::query()
            ->findOrFail($patientId);

        $activeMedications = $this->collectActiveMedications(
            (int) $patient->id
        );

        $drugId = (int) ($baseContext['drug_id'] ?? 0);

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
                'diagnosis_title' =>
                    $baseContext['diagnosis_title']
                    ?? null,
            ],

            'diagnoses' =>
                $this->collectPatientDiagnoses(
                    (int) $patient->id,
                    $baseContext
                ),

            'active_medications' => $activeMedications,

            'drug_reference' =>
                $this->collectDrugReference(
                    $drugId,
                    $activeMedications
                ),

            'anamneses' =>
                $this->collectAnamneses($patient),

            'epicrises' =>
                $this->collectEpicrises($patient),
        ];
    }

    private function collectActiveMedications(
        int $patientId
    ): array {
        $table = 'patient_medication_assignments';

        if (!Schema::hasTable($table)) {
            return [];
        }

        $columns = Schema::getColumnListing($table);

        if (!in_array('patient_id', $columns, true)) {
            return [];
        }

        $limit = max(
            1,
            (int) config(
                'medgemma.context.active_medications_limit',
                30
            )
        );

        $query = DB::table($table . ' as pma')
            ->where('pma.patient_id', $patientId);

        if (in_array('deleted_at', $columns, true)) {
            $query->whereNull('pma.deleted_at');
        }

        foreach ([
            'completed_at',
            'cancelled_at',
            'canceled_at',
            'stopped_at',
            'discontinued_at',
            'archived_at',
            'ended_at',
        ] as $terminalColumn) {
            if (in_array($terminalColumn, $columns, true)) {
                $query->whereNull('pma.' . $terminalColumn);
            }
        }

        $terminalStatuses = [
            'completed', 'complete', 'finished',
            'cancelled', 'canceled', 'stopped', 'discontinued',
            'ended', 'archived', 'inactive',
            'draft', 'pending', 'awaiting_confirmation', 'rejected',
            'paused', 'suspended', 'expired',
            'завершено', 'отменено', 'остановлено',
            'черновик', 'ожидает', 'отклонено', 'приостановлено',
            'истекло', 'истёк',
        ];

        if (in_array('is_active', $columns, true)) {
            $query->where('pma.is_active', true);
        } elseif (in_array('active', $columns, true)) {
            $query->where('pma.active', true);
        }

        if (in_array('status', $columns, true)) {
            $query->where(function ($q) use ($terminalStatuses): void {
                $q->whereNull('pma.status')
                    ->orWhereNotIn(
                        DB::raw('LOWER(CAST(pma.status AS TEXT))'),
                        $terminalStatuses
                    );
            });
        }

        foreach ([
            ['starts_on', '<='],
            ['starts_at', '<='],
            ['start_at', '<='],
        ] as [$dateColumn, $operator]) {
            if (in_array($dateColumn, $columns, true)) {
                $query->where(function ($q) use ($dateColumn, $operator): void {
                    $q->whereNull('pma.' . $dateColumn)
                        ->orWhereDate('pma.' . $dateColumn, $operator, today());
                });
                break;
            }
        }

        foreach (['ends_on', 'ends_at', 'end_at'] as $dateColumn) {
            if (in_array($dateColumn, $columns, true)) {
                $query->where(function ($q) use ($dateColumn): void {
                    $q->whereNull('pma.' . $dateColumn)
                        ->orWhereDate('pma.' . $dateColumn, '>=', today());
                });
                break;
            }
        }

        if (in_array('starts_on', $columns, true)) {
            $query->orderByDesc('pma.starts_on');
        } elseif (in_array('starts_at', $columns, true)) {
            $query->orderByDesc('pma.starts_at');
        } elseif (in_array('created_at', $columns, true)) {
            $query->orderByDesc('pma.created_at');
        } elseif (in_array('id', $columns, true)) {
            $query->orderByDesc('pma.id');
        }

        $rows = $query->limit($limit)->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $drugIds = $rows
            ->map(static fn ($row) => (int) ($row->drug_id ?? 0))
            ->filter()
            ->unique()
            ->values();

        $drugs = collect();
        if ($drugIds->isNotEmpty() && Schema::hasTable('drugs')) {
            $drugQuery = DB::table('drugs')->whereIn('id', $drugIds->all());
            if (Schema::hasColumn('drugs', 'deleted_at')) {
                $drugQuery->whereNull('deleted_at');
            }
            $drugs = $drugQuery->get()->keyBy('id');
        }

        $clinicIds = $rows
            ->map(static fn ($row) => (int) ($row->clinic_id ?? 0))
            ->filter()
            ->unique()
            ->values();

        $clinics = collect();
        if ($clinicIds->isNotEmpty() && Schema::hasTable('clinics')) {
            $clinics = DB::table('clinics')
                ->whereIn('id', $clinicIds->all())
                ->get()
                ->keyBy('id');
        }

        $doctorIds = $rows
            ->map(static fn ($row) => (int) ($row->doctor_id ?? 0))
            ->filter()
            ->unique()
            ->values();

        $doctors = collect();
        if ($doctorIds->isNotEmpty() && Schema::hasTable('doctors')) {
            $doctors = DB::table('doctors')
                ->whereIn('id', $doctorIds->all())
                ->get()
                ->keyBy('id');
        }

        return $rows
            ->map(function ($row) use ($drugs, $clinics, $doctors): array {
                $data = (array) $row;

                $drugId = (int) ($data['drug_id'] ?? 0);
                $drug = $drugId > 0 ? $drugs->get($drugId) : null;

                $clinicId = (int) ($data['clinic_id'] ?? 0);
                $clinic = $clinicId > 0 ? $clinics->get($clinicId) : null;

                $doctorId = (int) ($data['doctor_id'] ?? 0);
                $doctor = $doctorId > 0 ? $doctors->get($doctorId) : null;

                $drugName = $this->firstMeaningful(
                    $data,
                    [
                        'drug_name_snapshot',
                        'drug_name',
                        'medication_name',
                        'name',
                        'generic_name_snapshot',
                    ]
                );

                if ($drugName === '' && $drug) {
                    $drugName = trim((string) ($drug->name ?? $drug->latin_name ?? ''));
                }

                $genericName = $this->firstMeaningful(
                    $data,
                    ['generic_name_snapshot']
                );

                $doctorName = '';
                if ($doctor) {
                    $doctorName = trim(implode(' ', array_filter([
                        $doctor->surname ?? null,
                        $doctor->name ?? null,
                        $doctor->patronym ?? null,
                    ])));
                }

                $clinicName = '';
                if ($clinic) {
                    $clinicName = trim((string) ($clinic->name ?? $clinic->title ?? ''));
                }

                return array_filter(
                    [
                        'assignment_id' => isset($data['id']) ? (int) $data['id'] : null,
                        'drug_id' => $drugId > 0 ? $drugId : null,
                        'drug_name' => $drugName,
                        'generic_name' => $genericName,
                        'drug_form' => $this->firstMeaningful(
                            $data,
                            ['drug_form', 'form', 'form_name']
                        ),
                        'dosage' => $this->firstMeaningful(
                            $data,
                            ['dosage', 'dose', 'dose_value']
                        ),
                        'dosage_unit' => $this->firstMeaningful(
                            $data,
                            ['dosage_unit', 'dose_unit', 'unit', 'measurement_unit']
                        ),
                        'regimen' => $this->firstMeaningful(
                            $data,
                            ['schedule', 'regimen', 'usage_instructions', 'scheme']
                        ),
                        'doctor_instructions' => $this->firstMeaningful(
                            $data,
                            ['doctor_instructions', 'doctor_instruction']
                        ),
                        'indication' => $this->firstMeaningful(
                            $data,
                            ['indication_text']
                        ),
                        'contraindications' => $this->firstMeaningful(
                            $data,
                            ['contraindications_text']
                        ),
                        'side_effects' => $this->firstMeaningful(
                            $data,
                            ['side_effects_text']
                        ),
                        'missed_dose_instructions' => $this->firstMeaningful(
                            $data,
                            ['missed_dose_instructions']
                        ),
                        'overdose_actions' => $this->firstMeaningful(
                            $data,
                            ['overdose_actions']
                        ),
                        'warning_signs' => $this->firstMeaningful(
                            $data,
                            ['warning_signs']
                        ),
                        'reference_snapshot' => $this->decodeReferenceSnapshotV28(
                            $data['reference_snapshot'] ?? null
                        ),
                        'status' => $this->firstMeaningful($data, ['status']),
                        'starts_on' => $this->firstMeaningful(
                            $data,
                            ['starts_on', 'starts_at', 'start_at', 'created_at']
                        ),
                        'ends_on' => $this->firstMeaningful(
                            $data,
                            ['ends_on', 'ends_at', 'end_at', 'ended_at']
                        ),
                        'clinic_id' => $clinicId > 0 ? $clinicId : null,
                        'clinic_name' => $clinicName,
                        'doctor_id' => $doctorId > 0 ? $doctorId : null,
                        'doctor_name' => $doctorName,
                    ],
                    static fn ($value) => $value !== null && $value !== ''
                );
            })
            ->filter(static fn (array $item) => !empty($item['drug_name']))
            ->values()
            ->all();
    }

    private function decodeReferenceSnapshotV28(
        mixed $value
    ): mixed {
        if (!is_string($value) || trim($value) === '') {
            return $value;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $encoded = json_encode(
                $decoded,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            if (is_string($encoded) && mb_strlen($encoded) > 5000) {
                return [
                    'truncated' => true,
                    'preview' => mb_substr($encoded, 0, 5000),
                ];
            }

            return $decoded;
        }

        return $this->truncate($value, 5000);
    }

    private function collectPatientDiagnoses(
        int $patientId,
        array $baseContext = []
    ): array {
        $selected = array_filter(
            [
                'diagnosis_code' => $baseContext['diagnosis_code'] ?? null,
                'diagnosis_title' => $baseContext['diagnosis_title'] ?? null,
                'diagnosis_id' => $baseContext['diagnosis_id']
                    ?? $baseContext['diagnose_id']
                    ?? null,
            ],
            static fn ($value) => $value !== null && $value !== ''
        );

        if ($selected !== []) {
            $selected['source'] = 'current_encounter';
        }

        if (
            !empty($selected['diagnosis_id'])
            && Schema::hasTable('diagnoses')
        ) {
            $diagnosisQuery = DB::table('diagnoses')
                ->where('id', (int) $selected['diagnosis_id']);

            if (Schema::hasColumn('diagnoses', 'deleted_at')) {
                $diagnosisQuery->whereNull('deleted_at');
            }

            $diagnosis = $diagnosisQuery->first();
            if ($diagnosis) {
                $selected['diagnosis_code'] = $diagnosis->code ?? null;
                $selected['diagnosis_title'] = $diagnosis->title ?? null;
                $selected['diagnosis_description'] = $diagnosis->description ?? null;
            }
        } elseif (
            !empty($selected['diagnosis_code'])
            && empty($selected['diagnosis_title'])
            && Schema::hasTable('diagnoses')
        ) {
            $diagnosisQuery = DB::table('diagnoses')
                ->where('code', $selected['diagnosis_code']);

            if (Schema::hasColumn('diagnoses', 'deleted_at')) {
                $diagnosisQuery->whereNull('deleted_at');
            }

            $diagnosis = $diagnosisQuery->first();
            if ($diagnosis) {
                $selected['diagnosis_title'] = $diagnosis->title ?? null;
                $selected['diagnosis_description'] = $diagnosis->description ?? null;
            }
        }

        $history = [];

        if (
            Schema::hasTable('patient_diagnosis_history')
            && Schema::hasTable('diagnoses')
            && Schema::hasColumn('patient_diagnosis_history', 'patient_id')
            && Schema::hasColumn('patient_diagnosis_history', 'diagnose_id')
        ) {
            $query = DB::table('patient_diagnosis_history as pdh')
                ->leftJoin('diagnoses as d', 'd.id', '=', 'pdh.diagnose_id')
                ->where('pdh.patient_id', $patientId)
                ->select([
                    'd.code as diagnosis_code',
                    'd.title as diagnosis_title',
                    'd.description as diagnosis_description',
                ]);

            foreach ([
                'source',
                'set_at',
                'clinic_name_snapshot',
                'doctor_name_snapshot',
            ] as $column) {
                if (Schema::hasColumn('patient_diagnosis_history', $column)) {
                    $query->addSelect('pdh.' . $column);
                }
            }

            if (Schema::hasColumn('patient_diagnosis_history', 'set_at')) {
                $query->orderByDesc('pdh.set_at');
            } elseif (Schema::hasColumn('patient_diagnosis_history', 'created_at')) {
                $query->orderByDesc('pdh.created_at');
            }

            $history = $query->limit(20)->get()
                ->map(static fn ($row) => array_filter(
                    (array) $row,
                    static fn ($value) => $value !== null && $value !== ''
                ))
                ->values()
                ->all();
        }

        return [
            'selected_encounter' => $selected,
            'history' => $history,
        ];
    }

    private function collectDrugReference(
        int $drugId,
        array $activeMedications = []
    ): array {
        if ($drugId <= 0 || !Schema::hasTable('drugs')) {
            return [];
        }

        $drugQuery = DB::table('drugs')->where('id', $drugId);
        if (Schema::hasColumn('drugs', 'deleted_at')) {
            $drugQuery->whereNull('deleted_at');
        }

        $drugRow = $drugQuery->first();
        if (!$drugRow) {
            return [];
        }

        $drug = (array) $drugRow;

        $forms = $drug['forms'] ?? null;
        if (is_string($forms) && trim($forms) !== '') {
            $decoded = json_decode($forms, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $forms = $decoded;
            }
        }

        $cytochromes = $drug['cytochromes'] ?? null;
        if (is_string($cytochromes) && trim($cytochromes) !== '') {
            $decoded = json_decode($cytochromes, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $cytochromes = $decoded;
            }
        }

        $reference = [
            'source' => 'local_structured_drug_reference',
            'source_note' => 'Данные локального справочника системы. Полная официальная инструкция к препарату в этих таблицах не хранится.',
            'drug' => [
                'id' => (int) ($drug['id'] ?? $drugId),
                'name' => $drug['name'] ?? null,
                'latin_name' => $drug['latin_name'] ?? null,
                'forms' => $forms,
                'description' => $drug['description'] ?? null,
                'pregnancy' => $drug['pregnancy'] ?? null,
                'lactation' => $drug['lactation'] ?? null,
                'liver' => $drug['liver'] ?? null,
                'kidneys' => $drug['kidneys'] ?? null,
                'cytochromes' => $cytochromes,
                'preferential' => $drug['preferential'] ?? null,
                'strict' => $drug['strict'] ?? null,
            ],
            'indications' => [],
            'contraindications' => [],
            'adverse_reactions' => [],
            'interactions' => [],
            'interactions_with_active_medications' => [],
            'condition_rules' => [],
            'dosage_rules' => [],
            'patient_leaflet' => [],
        ];

        if (
            Schema::hasTable('medicine_indications')
            && Schema::hasTable('diagnoses')
            && Schema::hasColumn('medicine_indications', 'medicine_id')
            && Schema::hasColumn('medicine_indications', 'diagnose_id')
        ) {
            $query = DB::table('medicine_indications as mi')
                ->leftJoin('diagnoses as d', 'd.id', '=', 'mi.diagnose_id')
                ->where('mi.medicine_id', $drugId)
                ->select([
                    'd.code as diagnosis_code',
                    'd.title as diagnosis_title',
                    'd.description as diagnosis_description',
                ]);

            foreach (['indicated_in_russia', 'indicated_by_fda'] as $column) {
                if (Schema::hasColumn('medicine_indications', $column)) {
                    $query->addSelect('mi.' . $column);
                }
            }

            if (Schema::hasColumn('medicine_indications', 'deleted_at')) {
                $query->whereNull('mi.deleted_at');
            }

            $reference['indications'] = $query->limit(100)->get()
                ->map(static fn ($row) => array_filter(
                    (array) $row,
                    static fn ($value) => $value !== null && $value !== ''
                ))
                ->values()
                ->all();
        }

        if (
            Schema::hasTable('medicine_contraindications')
            && Schema::hasTable('contraindications_types')
            && Schema::hasColumn('medicine_contraindications', 'drug_id')
            && Schema::hasColumn('medicine_contraindications', 'contraindication_id')
        ) {
            $query = DB::table('medicine_contraindications as mc')
                ->leftJoin(
                    'contraindications_types as ct',
                    'ct.id',
                    '=',
                    'mc.contraindication_id'
                )
                ->where('mc.drug_id', $drugId)
                ->select([
                    'ct.code',
                    'ct.name',
                    'ct.description',
                ]);

            if (Schema::hasColumn('medicine_contraindications', 'type')) {
                $query->addSelect('mc.type as relation_type');
            }

            $reference['contraindications'] = $query->limit(200)->get()
                ->map(static fn ($row) => array_filter(
                    (array) $row,
                    static fn ($value) => $value !== null && $value !== ''
                ))
                ->values()
                ->all();
        }

        if (
            Schema::hasTable('medicine_side_effects')
            && Schema::hasTable('side_effects')
            && Schema::hasColumn('medicine_side_effects', 'drug_id')
            && Schema::hasColumn('medicine_side_effects', 'side_effect_id')
        ) {
            $query = DB::table('medicine_side_effects as mse')
                ->leftJoin('side_effects as se', 'se.id', '=', 'mse.side_effect_id')
                ->where('mse.drug_id', $drugId)
                ->select([
                    'se.code',
                    'se.name',
                    'se.description',
                ]);

            $reference['adverse_reactions'] = $query->limit(200)->get()
                ->map(static fn ($row) => array_filter(
                    (array) $row,
                    static fn ($value) => $value !== null && $value !== ''
                ))
                ->values()
                ->all();
        }

        if (
            Schema::hasTable('medicine_interactions')
            && Schema::hasColumn('medicine_interactions', 'drug_id')
            && Schema::hasColumn('medicine_interactions', 'interacts_with_drug_id')
        ) {
            $query = DB::table('medicine_interactions as mi')
                ->where(function ($q) use ($drugId): void {
                    $q->where('mi.drug_id', $drugId)
                        ->orWhere('mi.interacts_with_drug_id', $drugId);
                });

            if (Schema::hasColumn('medicine_interactions', 'deleted_at')) {
                $query->whereNull('mi.deleted_at');
            }

            $interactionRows = $query->limit(300)->get();
            $otherIds = $interactionRows
                ->map(static function ($row) use ($drugId): int {
                    return (int) ($row->drug_id == $drugId
                        ? $row->interacts_with_drug_id
                        : $row->drug_id);
                })
                ->filter()
                ->unique()
                ->values();

            $otherDrugs = collect();
            if ($otherIds->isNotEmpty() && Schema::hasTable('drugs')) {
                $otherDrugs = DB::table('drugs')
                    ->whereIn('id', $otherIds->all())
                    ->get()
                    ->keyBy('id');
            }

            $reference['interactions'] = $interactionRows
                ->map(function ($row) use ($drugId, $otherDrugs): array {
                    $otherId = (int) ($row->drug_id == $drugId
                        ? $row->interacts_with_drug_id
                        : $row->drug_id);
                    $otherDrug = $otherDrugs->get($otherId);

                    return array_filter(
                        [
                            'interacting_drug_id' => $otherId,
                            'interacting_drug_name' => $otherDrug->name
                                ?? $otherDrug->latin_name
                                ?? null,
                            'severity' => $row->severity ?? null,
                            'comment' => $row->comment ?? null,
                        ],
                        static fn ($value) => $value !== null && $value !== ''
                    );
                })
                ->values()
                ->all();
        }

        foreach ([
            'drug_conditions_rules' => 'condition_rules',
            'drug_dosage_rules' => 'dosage_rules',
        ] as $table => $key) {
            if (
                !Schema::hasTable($table)
                || !Schema::hasColumn($table, 'drug_id')
            ) {
                continue;
            }

            $query = DB::table($table)->where('drug_id', $drugId);

            if (Schema::hasColumn($table, 'is_active')) {
                $query->where('is_active', true);
            }

            $reference[$key] = $query->limit(200)->get()
                ->map(static fn ($row) => (array) $row)
                ->values()
                ->all();
        }

        $activeDrugIds = collect($activeMedications)
            ->pluck('drug_id')
            ->filter()
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->all();

        if ($activeDrugIds !== []) {
            $reference['interactions_with_active_medications'] = array_values(
                array_filter(
                    $reference['interactions'],
                    static fn (array $item) => in_array(
                        (int) ($item['interacting_drug_id'] ?? 0),
                        $activeDrugIds,
                        true
                    )
                )
            );
        }

        return $reference;
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
            if (
                method_exists(
                    $patient,
                    'anamneses'
                )
            ) {
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
            if (
                method_exists(
                    $patient,
                    'epicrises'
                )
            ) {
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

                $columns =
                    Schema::getColumnListing($table);

                $patientColumn =
                    collect([
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

                $query =
                    DB::table($table)
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
                    $query->orderByDesc('created_at');
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
            $data =
                $record->getAttributes();
        } elseif (
            is_object($record)
        ) {
            $data =
                (array) $record;
        } elseif (
            is_array($record)
        ) {
            $data =
                $record;
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

        return array_filter(
            [
                'type' => $type,
                'date' =>
                    $this->firstMeaningful(
                        $data,
                        [
                            'created_at',
                            'date',
                            'recorded_at',
                            'issued_at',
                        ]
                    ),
                'title' =>
                    $this->firstMeaningful(
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
                'text' =>
                    $this->truncate(
                        $this->cleanText($text),
                        (int) config(
                            'medgemma.context.item_max_chars',
                            3500
                        )
                    ),
            ],
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
            if (
                !array_key_exists(
                    $key,
                    $data
                )
            ) {
                continue;
            }

            $value = $data[$key];

            if (
                $value instanceof
                \DateTimeInterface
            ) {
                return $value->format(
                    'Y-m-d H:i:s'
                );
            }

            if (
                is_scalar($value)
                && trim((string) $value) !== ''
            ) {
                return trim(
                    (string) $value
                );
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
