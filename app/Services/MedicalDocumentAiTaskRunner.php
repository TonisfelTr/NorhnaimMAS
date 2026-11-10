<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Controllers\Doctors\NeuralNetworkController;
use App\Models\AiMedicalDocumentTask;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class MedicalDocumentAiTaskRunner
{
    public function __construct(
        private readonly AnamnesisStructuringService $structurer,
        private readonly AnamnesisAnalyzeService $anamnesisAnalyzer,
    ) {
    }

    public function run(string $taskId): int
    {
        $task = AiMedicalDocumentTask::query()->find($taskId);

        if (!$task || $task->cancellationRequested()) {
            return 0;
        }

        $task->forceFill([
            'status' => AiMedicalDocumentTask::STATUS_RUNNING,
            'progress' => 15,
            'started_at' => now(),
            'error_message' => null,
        ])->save();

        $user = User::query()->find($task->user_id);
        $patient = Patient::query()->find($task->patient_id);

        if (!$user || !$patient) {
            $this->failTask(
                $task,
                'Пользователь или пациент больше не существует.'
            );

            return 1;
        }

        Auth::guard()->setUser($user);

        try {
            $payload = (array) $task->request_payload;
            $payload['patient_id'] = (int) $patient->id;

            $request = Request::create(
                '/internal/ai-medical-document',
                'POST',
                $payload
            );

            $request->setUserResolver(
                static fn () => $user
            );

            $response = app(
                NeuralNetworkController::class
            )->analyzeAnamnesis($request);

            $status = method_exists(
                $response,
                'getStatusCode'
            )
                ? (int) $response->getStatusCode()
                : 500;

            $content = method_exists(
                $response,
                'getContent'
            )
                ? $response->getContent()
                : null;

            $document = is_string($content)
                ? json_decode($content, true)
                : null;

            if (
                $status >= 400
                || !is_array($document)
            ) {
                $message = is_array($document)
                    ? (string) (
                        $document['message']
                        ?? 'ИИ-анализатор вернул ошибку.'
                    )
                    : 'ИИ-анализатор вернул некорректный ответ.';

                throw new \RuntimeException($message);
            }

            if ($this->cancelled($task)) {
                return 0;
            }

            /*
             * NORHNAIM_MEDICAL_DOCUMENT_ANALYZER_REFINEMENT_V2
             *
             * Общая постобработка клинических документов:
             * - диагноз берём из primary_diagnosis анализатора;
             * - жалобы заключения психиатра — из matched_symptoms;
             * - выписка получает компактный anamnesis morbi.
             */
            $mode = (string) $task->mode;

            if (
                $mode === 'continue_treatment'
                && is_array($document['draft'] ?? null)
            ) {
                $document = $this->refineContinueTreatment(
                    $document,
                    $patient
                );
            }

            if (
                in_array(
                    $mode,
                    [
                        'psychiatrist_conclusion',
                        'medical_extract',
                        'epicrisis',
                    ],
                    true
                )
                && is_array($document['draft'] ?? null)
            ) {
                $document = $this->applyAnalyzerResult(
                    $document,
                    $patient,
                    $mode
                );
            }

            if ($this->cancelled($task)) {
                return 0;
            }

            $task->forceFill([
                'progress' => 62,
            ])->save();

            $structured = [
                'epicrisis' => [],
                'anamneses' => [],
            ];

            if (
                in_array(
                    $mode,
                    ['epicrisis', 'medical_extract'],
                    true
                )
            ) {
                $structured = $this->structurer->structure(
                    $patient,
                    $document
                );

                if ($this->cancelled($task)) {
                    return 0;
                }

                $draft = is_array(
                    $document['draft'] ?? null
                )
                    ? $document['draft']
                    : [];

                $structuredEpicrisis = (array) (
                    $structured['epicrisis'] ?? []
                );

                if ($mode === 'epicrisis') {
                    foreach (
                        $structuredEpicrisis
                        as $key => $value
                    ) {
                        $value = trim((string) $value);

                        if ($value !== '') {
                            $draft[$key] = $value;
                        }
                    }
                } else {
                    /*
                     * Выписке нужен только анамнез заболевания,
                     * а не vitae/social/disability одной простынёй.
                     */
                    $morbi = trim(
                        (string) (
                            $structuredEpicrisis['anamnesis']
                            ?? ''
                        )
                    );

                    if ($morbi !== '') {
                        $draft['anamnesis'] = $morbi;
                    }
                }

                $document['draft'] = $draft;

                /*
                 * После сокращения morbi ещё раз уточняем диагноз
                 * по уже нормализованному анамнезу.
                 */
                $document = $this->applyAnalyzerResult(
                    $document,
                    $patient,
                    $mode
                );
            }

            if ($this->cancelled($task)) {
                return 0;
            }

            $task->forceFill([
                'status' =>
                    AiMedicalDocumentTask::STATUS_COMPLETED,
                'progress' => 100,
                'result_payload' => [
                    'document' => $document,
                    'structured_anamneses' =>
                        $mode === 'epicrisis'
                            ? array_values(
                                (array) (
                                    $structured['anamneses']
                                    ?? []
                                )
                            )
                            : [],
                ],
                'finished_at' => now(),
            ])->save();

            return 0;
        } catch (\Throwable $e) {
            $task->refresh();

            if (!$task->cancellationRequested()) {
                $this->failTask(
                    $task,
                    mb_substr(
                        trim($e->getMessage())
                            ?: 'Не удалось сформировать документ.',
                        0,
                        4000
                    )
                );
            }

            Log::error(
                'AI medical document child task failed',
                [
                    'task_id' => $taskId,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]
            );

            return 1;
        } finally {
            Auth::forgetGuards();
        }
    }

    private function applyAnalyzerResult(
        array $document,
        Patient $patient,
        string $mode
    ): array {
        $draft = is_array($document['draft'] ?? null)
            ? $document['draft']
            : [];

        $anamnesisText = trim(
            (string) ($draft['anamnesis'] ?? '')
        );

        if ($anamnesisText === '') {
            return $document;
        }

        $analysis = $this->anamnesisAnalyzer->process(
            $anamnesisText,
            (int) $patient->id
        );

        if (
            !is_array($analysis)
            || isset($analysis['error'])
        ) {
            return $document;
        }

        $primary = $analysis['primary_diagnosis']
            ?? null;

        if (!$primary) {
            $scored = $analysis['scored'] ?? [];

            if (
                $scored
                instanceof \Illuminate\Support\Collection
            ) {
                $primary = $scored->first();
            } elseif (is_array($scored)) {
                $primary = $scored[0] ?? null;
            }
        }

        $code = trim(
            (string) data_get(
                $primary,
                'code',
                ''
            )
        );

        $title = trim(
            (string) data_get(
                $primary,
                'title',
                ''
            )
        );

        $diagnosis = implode(
            ' — ',
            array_values(
                array_filter(
                    [$code, $title],
                    static fn (string $value): bool =>
                        $value !== ''
                )
            )
        );

        if ($diagnosis !== '') {
            /*
             * Для этих трёх форм диагноз всегда определяется
             * существующим анализатором, а не свободным LLM-текстом.
             */
            $draft['diagnosis'] = $diagnosis;
        }

        if ($mode === 'psychiatrist_conclusion') {
            $matched = collect(
                $analysis['matched_symptoms'] ?? []
            )
                ->filter(
                    static fn ($row): bool =>
                        is_array($row)
                        && empty($row['negated'])
                )
                ->filter(
                    static fn (array $row): bool =>
                        trim(
                            (string) (
                                $row['sentence'] ?? ''
                            )
                        ) !== ''
                );

            $complaints = $matched
                ->pluck('title')
                ->filter(
                    static fn ($value): bool =>
                        is_string($value)
                        && trim($value) !== ''
                )
                ->map(
                    static fn (string $value): string =>
                        trim($value)
                )
                ->unique(
                    static fn (string $value): string =>
                        mb_strtolower($value)
                )
                ->take(10)
                ->implode(', ');

            if ($complaints !== '') {
                $first = mb_substr(
                    $complaints,
                    0,
                    1
                );

                $draft['complaints'] =
                    mb_strtoupper($first)
                    . mb_substr(
                        $complaints,
                        1
                    )
                    . '.';
            }
        }

        $draft['analyzer_meta'] = [
            'primary_diagnosis' => [
                'code' => $code,
                'title' => $title,
                'score' => data_get(
                    $primary,
                    'score'
                ),
            ],
            'source' => 'anamnesis_analyzer',
        ];

        $document['draft'] = $draft;

        return $document;
    }

    private function refineContinueTreatment(
        array $document,
        Patient $patient
    ): array {
        // NORHNAIM_CONTINUE_TREATMENT_AI_REFINEMENT_V1
        return app(
            \App\Services\ContinueTreatmentRefinementService::class
        )->refine(
            $patient,
            $document,
            $this->anamnesisAnalyzer
        );
    }

    private function cancelled(
        AiMedicalDocumentTask $task
    ): bool {
        $task->refresh();

        return $task->cancellationRequested();
    }

    private function failTask(
        AiMedicalDocumentTask $task,
        string $message
    ): void {
        $task->forceFill([
            'status' =>
                AiMedicalDocumentTask::STATUS_FAILED,
            'error_message' => $message,
            'finished_at' => now(),
        ])->save();
    }
}
