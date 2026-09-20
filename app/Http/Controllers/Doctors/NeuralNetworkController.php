<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\AnamnesisAnalyzeService;
use App\Services\MedicalDocuments\ContinueTreatmentAiService;
use App\Services\MedicalDocuments\EpicrisisAiService;
use App\Services\MedicalDocuments\MedicalCertificateAiService;
use App\Services\MedicalDocuments\MedicalExtractAiService;
use App\Services\MedicalDocuments\PsychiatristConclusionAiService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class NeuralNetworkController extends Controller
{
    public function analyzeAnamnesis(Request $request): JsonResponse
    {
        $mode = (string) $request->input('mode');

        if ($mode === 'psychiatrist_conclusion') {
            return $this->generatePsychiatristConclusion($request);
        }

        if ($mode === 'medical_extract') {
            return $this->generateMedicalExtract($request);
        }

        if ($mode === 'epicrisis') {
            return $this->generateEpicrisis($request);
        }

        if ($mode === 'medical_certificate') {
            return $this->generateMedicalCertificate($request);
        }

        if ($mode === 'continue_treatment') {
            return $this->generateContinueTreatment($request);
        }

        $data = $request->validate([
            'text' => ['required', 'string', 'min:3'],
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
        ]);

        $text = trim((string) $data['text']);
        $patientId = $this->resolvePatientId($request, $data);

        // Регистр исходного анамнеза сохраняем: сервис сам выполняет нормализацию,
        // а дословные evidence должны совпадать с исходным текстом.
        $serviceResult = app(AnamnesisAnalyzeService::class)->process(
            $text,
            $patientId
        );

        if ($serviceResult === [] || isset($serviceResult['error'])) {
            $status = match ((string) ($serviceResult['error'] ?? '')) {
                'ANAMNESIS_EMPTY',
                'ANAMNESIS_TOO_SHORT',
                'ANAMNESIS_TOO_LARGE',
                'ANAMNESIS_INVALID_ENCODING',
                'ANAMNESIS_SUSPICIOUS_INPUT',
                'PATIENT_NOT_FOUND' => 422,
                default => 500,
            };

            return response()->json([
                'summary' => null,
                'flags' => ['Ошибка анализа'],
                'diagnoses' => [],
                'matched_symptoms' => [],
                'warnings' => (array) ($serviceResult['warnings'] ?? []),
                'message' => (string) (
                    $serviceResult['message']
                    ?? 'ИИ-анализатор не вернул корректный результат.'
                ),
                'raw' => $serviceResult,
            ], $status);
        }

        $matchedSymptoms = collect(
            Arr::get($serviceResult, 'matched_symptoms', [])
        )
            ->filter(static fn ($row): bool => is_array($row))
            ->values();

        $diagnoses = $this->normalizedDiagnoses($serviceResult);
        $primary = $diagnoses->first();

        $symptomTitles = $matchedSymptoms
            ->pluck('title')
            ->filter(static fn ($title): bool => is_string($title) && trim($title) !== '')
            ->map(static fn (string $title): string => trim($title))
            ->unique()
            ->take(8)
            ->values();

        $summary = $primary
            ? sprintf(
                'Предположительно: %s (%s%s). Ключевые симптомы: %s.',
                (string) ($primary['title'] ?: 'без названия'),
                (string) ($primary['code'] ?: '—'),
                $primary['score'] !== null
                    ? ', ' . $primary['score'] . '%'
                    : '',
                $symptomTitles->isNotEmpty()
                    ? $symptomTitles->implode(', ')
                    : '—'
            )
            : (
            $symptomTitles->isNotEmpty()
                ? 'Ключевые симптомы: ' . $symptomTitles->implode(', ') . '.'
                : 'Сводка не получена.'
            );

        /*
         * Не ищем слова «суицид», «галлюцинации», «агрессия» простым substring:
         * в анамнезе они часто встречаются в явном отрицании. Берём только
         * детерминированные risk_flags, уже прошедшие проверку отрицания.
         */
        $flags = collect(Arr::get($serviceResult, 'risk_flags', []))
            ->filter(static fn ($row): bool => is_array($row))
            ->map(static function (array $row): string {
                $evidence = trim((string) ($row['evidence'] ?? ''));
                $type = trim((string) ($row['type'] ?? 'Риск'));

                return $evidence !== ''
                    ? $type . ': ' . $evidence
                    : $type;
            })
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'summary' => $summary,
            'flags' => $flags,
            'diagnoses' => $diagnoses->take(5)->values(),
            'matched_symptoms' => $matchedSymptoms,
            'medical_review' => Arr::get($serviceResult, 'medical_review'),
            'warnings' => array_values(array_unique((array) Arr::get(
                $serviceResult,
                'warnings',
                []
            ))),
            'raw' => $serviceResult,
        ]);
    }

    private function generatePsychiatristConclusion(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
        ]);

        $patient = Patient::query()->findOrFail((int) $data['patient_id']);

        $result = app(PsychiatristConclusionAiService::class)->generate($patient);

        if (isset($result['error'])) {
            $status = match ((string) $result['error']) {
                'NO_CLINICAL_DATA' => 422,
                'LLM_UNAVAILABLE' => 503,
                'LLM_INVALID_JSON' => 502,
                default => 500,
            };

            return response()->json([
                'message' => (string) (
                    $result['message']
                    ?? 'Не удалось сформировать заключение психиатра.'
                ),
                'error' => $result['error'],
            ], $status);
        }

        return response()->json([
            'draft' => $result,
        ]);
    }

    private function generateMedicalExtract(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'period_mode' => ['nullable', 'in:all,range'],
            'date_from' => ['nullable', 'date', 'required_if:period_mode,range'],
            'date_to' => ['nullable', 'date', 'required_if:period_mode,range', 'after_or_equal:date_from'],
        ]);

        $patient = Patient::query()->findOrFail((int) $data['patient_id']);
        $periodMode = (string) ($data['period_mode'] ?? 'all');

        $result = app(MedicalExtractAiService::class)->generate(
            $patient,
            $periodMode,
            $data['date_from'] ?? null,
            $data['date_to'] ?? null,
        );

        return $this->medicalDocumentAiResponse(
            $result,
            'Не удалось сформировать выписку из медицинской карты.'
        );
    }

    private function generateEpicrisis(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'epicrisis_kind' => ['required', 'in:primary,stage,discharge'],
        ]);

        $patient = Patient::query()->findOrFail((int) $data['patient_id']);

        $result = app(EpicrisisAiService::class)->generate(
            $patient,
            (string) $data['epicrisis_kind'],
        );

        return $this->medicalDocumentAiResponse(
            $result,
            'Не удалось сформировать эпикриз.'
        );
    }

    private function generateMedicalCertificate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'certificate_kind' => ['nullable', 'in:general,observation,treatment'],
            'include_diagnosis' => ['nullable', 'boolean'],
            'recipient' => ['nullable', 'string', 'max:500'],
            'purpose' => ['nullable', 'string', 'max:500'],
        ]);

        $patient = Patient::query()->findOrFail((int) $data['patient_id']);

        $result = app(MedicalCertificateAiService::class)->generate(
            $patient,
            (string) ($data['certificate_kind'] ?? 'general'),
            (bool) ($data['include_diagnosis'] ?? false),
            $data['recipient'] ?? null,
            $data['purpose'] ?? null,
        );

        return $this->medicalDocumentAiResponse(
            $result,
            'Не удалось сформировать медицинскую справку.'
        );
    }

    private function generateContinueTreatment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'recipient_organization' => ['nullable', 'string', 'max:500'],
            'recipient_doctor' => ['nullable', 'string', 'max:500'],
            'transfer_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $patient = Patient::query()->findOrFail((int) $data['patient_id']);

        $result = app(ContinueTreatmentAiService::class)->generate(
            $patient,
            $data['recipient_organization'] ?? null,
            $data['recipient_doctor'] ?? null,
            $data['transfer_reason'] ?? null,
        );

        return $this->medicalDocumentAiResponse(
            $result,
            'Не удалось сформировать сведения для продолжения лечения.'
        );
    }

    private function medicalDocumentAiResponse(array $result, string $defaultMessage): JsonResponse
    {
        if (isset($result['error'])) {
            $status = match ((string) $result['error']) {
                'NO_CLINICAL_DATA' => 422,
                'LLM_UNAVAILABLE' => 503,
                'LLM_INVALID_JSON' => 502,
                default => 500,
            };

            return response()->json([
                'message' => (string) ($result['message'] ?? $defaultMessage),
                'error' => $result['error'],
            ], $status);
        }

        return response()->json([
            'draft' => $result,
        ]);
    }

    private function resolvePatientId(Request $request, array $validated): ?int
    {
        if (isset($validated['patient_id']) && is_numeric($validated['patient_id'])) {
            return (int) $validated['patient_id'];
        }

        foreach (['patient', 'patient_id'] as $parameter) {
            $routeValue = $request->route($parameter);

            if ($routeValue instanceof Model) {
                return (int) $routeValue->getKey();
            }

            if (is_numeric($routeValue)) {
                return (int) $routeValue;
            }
        }

        return null;
    }

    private function normalizedDiagnoses(array $serviceResult): Collection
    {
        $rows = collect(Arr::get($serviceResult, 'scored', []));

        if ($rows->isEmpty()) {
            $primary = Arr::get($serviceResult, 'primary_diagnosis');
            $differential = Arr::get($serviceResult, 'differential', []);
            $rows = collect([$primary])->merge(collect($differential));
        }

        return $rows
            ->map(fn ($row): ?array => $this->normalizeDiagnosisRow($row))
            ->filter(static fn (?array $row): bool => (
                is_array($row)
                && $row['code'] !== ''
                && $row['title'] !== ''
            ))
            ->unique('code')
            ->values();
    }

    private function normalizeDiagnosisRow(mixed $row): ?array
    {
        if ($row instanceof Model) {
            $row = $row->toArray();
        } elseif (is_object($row) && method_exists($row, 'toArray')) {
            $row = $row->toArray();
        }

        if (!is_array($row)) {
            return null;
        }

        $diagnosis = $row['diagnosis'] ?? [];
        if ($diagnosis instanceof Model) {
            $diagnosis = $diagnosis->toArray();
        } elseif (is_object($diagnosis) && method_exists($diagnosis, 'toArray')) {
            $diagnosis = $diagnosis->toArray();
        }
        if (!is_array($diagnosis)) {
            $diagnosis = [];
        }

        $code = trim((string) ($row['code'] ?? $diagnosis['code'] ?? ''));
        $title = trim((string) ($row['title'] ?? $diagnosis['title'] ?? ''));

        $score = $row['score'] ?? null;
        if (!is_numeric($score)) {
            $scoreRaw = $row['score_raw']
                ?? $row['totalScore']
                ?? $row['total_score']
                ?? null;

            $score = is_numeric($scoreRaw)
                ? (int) round(max(0.0, min(1.0, (float) $scoreRaw)) * 100)
                : null;
        } else {
            $score = (float) $score;
            // Старый сервис отдавал totalScore в диапазоне 0..1.
            if ($score >= 0.0 && $score <= 1.0) {
                $score = round($score * 100);
            }
            $score = (int) round($score);
        }

        $requiredMatched = $this->firstInteger($row, [
            'required_matched',
            'requiredMatched',
            'requiredCount',
        ]);
        $relativeMatched = $this->firstInteger($row, [
            'relative_matched',
            'relativeMatched',
            'relativeCount',
        ]);
        $requiredNeeded = $this->firstInteger($row, [
            'required_needed',
            'requiredCriteria',
            'required_criteria',
        ], (int) ($diagnosis['required_criteria'] ?? 0));
        $relativeNeeded = $this->firstInteger($row, [
            'relative_needed',
            'relativeCriteria',
            'relative_criteria',
        ], (int) ($diagnosis['relative_criteria'] ?? 0));

        return [
            'id' => $row['id'] ?? $diagnosis['id'] ?? null,
            'code' => $code,
            'title' => $title,
            'score' => $score,
            'required_matched' => $requiredMatched,
            'required_needed' => $requiredNeeded,
            'relative_matched' => $relativeMatched,
            'relative_needed' => $relativeNeeded,
            'criteria_passed' => (
                ($requiredNeeded <= 0 || $requiredMatched >= $requiredNeeded)
                && ($relativeNeeded <= 0 || $relativeMatched >= $relativeNeeded)
            ),
            'matched_symptoms' => array_values(array_unique(array_filter(
                (array) ($row['matched_symptoms'] ?? [])
            ))),
        ];
    }

    private function firstInteger(
        array $row,
        array $keys,
        int $default = 0
    ): int {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && is_numeric($row[$key])) {
                return (int) $row[$key];
            }
        }

        return $default;
    }
}
