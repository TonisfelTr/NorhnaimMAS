<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateMedicalDocumentAiTask;
use App\Models\AiMedicalDocumentTask;
use App\Models\Anamnesis;
use App\Models\Patient;
use App\Support\HospitalPatientAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class MedicalDocumentAiTaskController extends Controller
{
    private const MODES = [
        'psychiatrist_conclusion',
        'medical_extract',
        'epicrisis',
        'medical_certificate',
        'continue_treatment',
    ];

    private const ANAMNESIS_CATEGORIES = [
        'morbi',
        'vitae',
        'social',
        'family',
        'drug',
        'allergic',
        'epidemiological',
        'psychiatric',
        'other',
    ];

    public function store(
        Request $request,
        Patient $patient,
        HospitalPatientAccess $access
    ): JsonResponse {
        $user = $request->user();

        abort_unless(
            $user && $access->canRead($user, $patient),
            403
        );

        $data = $request->validate([
            'mode' => ['required', 'string', Rule::in(self::MODES)],
            'epicrisis_kind' => ['nullable', 'string', 'max:32'],
            'period_mode' => ['nullable', 'string', 'max:32'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'certificate_kind' => ['nullable', 'string', 'max:64'],
            'include_diagnosis' => ['nullable', 'boolean'],
            'recipient' => ['nullable', 'string', 'max:1000'],
            'purpose' => ['nullable', 'string', 'max:2000'],
            'recipient_organization' => ['nullable', 'string', 'max:1000'],
            'recipient_doctor' => ['nullable', 'string', 'max:1000'],
            'transfer_reason' => ['nullable', 'string', 'max:4000'],
        ]);

        $task = AiMedicalDocumentTask::query()->create([
            'patient_id' => (int) $patient->id,
            'user_id' => (int) $user->id,
            'mode' => (string) $data['mode'],
            'status' => AiMedicalDocumentTask::STATUS_QUEUED,
            'progress' => 5,
            'request_payload' => $data,
        ]);

        GenerateMedicalDocumentAiTask::dispatch((string) $task->id);

        return response()->json(
            $this->payload($task, $patient),
            202
        );
    }

    public function show(
        Request $request,
        Patient $patient,
        AiMedicalDocumentTask $task
    ): JsonResponse {
        $this->authorizeTask($request, $patient, $task);

        return response()
            ->json($this->payload($task->fresh(), $patient))
            ->header('Cache-Control', 'private, no-store');
    }

    public function cancel(
        Request $request,
        Patient $patient,
        AiMedicalDocumentTask $task
    ): JsonResponse {
        $this->authorizeTask($request, $patient, $task);

        if (in_array($task->status, [
            AiMedicalDocumentTask::STATUS_QUEUED,
            AiMedicalDocumentTask::STATUS_RUNNING,
        ], true)) {
            $task->forceFill([
                'status' => AiMedicalDocumentTask::STATUS_CANCELLED,
                'cancel_requested_at' => now(),
                'finished_at' => now(),
            ])->save();
        }

        return response()->json([
            'ok' => true,
            'status' => $task->fresh()->status,
        ]);
    }

    public function confirmAnamneses(
        Request $request,
        Patient $patient,
        AiMedicalDocumentTask $task,
        HospitalPatientAccess $access
    ): JsonResponse {
        $this->authorizeTask($request, $patient, $task);

        $user = $request->user();

        abort_unless(
            $user && $access->canEdit($user, $patient),
            403
        );

        if ($task->status !== AiMedicalDocumentTask::STATUS_COMPLETED) {
            throw ValidationException::withMessages([
                'task' => 'ИИ-задача ещё не завершена.',
            ]);
        }

        if ($task->anamneses_saved_at !== null) {
            throw ValidationException::withMessages([
                'task' => 'Предложенные анамнезы этой задачи уже сохранены.',
            ]);
        }

        $doctorId = $user->doctor()->value('id');

        if (!$doctorId) {
            throw ValidationException::withMessages([
                'doctor' =>
                    'Сохранить медицинские записи может только пользователь '
                    . 'с профилем врача.',
            ]);
        }

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.category' => [
                'required',
                'string',
                Rule::in(self::ANAMNESIS_CATEGORIES),
            ],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.text' => ['required', 'string', 'min:3', 'max:20000'],
            'items.*.period' => ['nullable', 'string', 'max:120'],
        ]);

        $created = DB::transaction(function () use (
            $data,
            $patient,
            $doctorId,
            $task
        ): array {
            $ids = [];

            foreach ($data['items'] as $item) {
                $title = trim((string) $item['title']);
                $period = trim((string) ($item['period'] ?? ''));

                if ($period !== '' && !str_contains($title, $period)) {
                    $title .= ' — ' . $period;
                }

                $anamnesis = new Anamnesis();
                $anamnesis->patient_id = (int) $patient->id;
                $anamnesis->doctor_id = (int) $doctorId;
                $anamnesis->text = trim((string) $item['text']);
                $anamnesis->category = (string) $item['category'];
                $anamnesis->title = mb_substr($title, 0, 255);
                $anamnesis->source = 'ai';
                $anamnesis->diagnose_id = null;
                $anamnesis->is_current = false;
                $anamnesis->save();

                $ids[] = (int) $anamnesis->id;
            }

            $task->forceFill([
                'anamneses_saved_at' => now(),
            ])->save();

            return $ids;
        });

        return response()->json([
            'ok' => true,
            'created_ids' => $created,
            'count' => count($created),
        ]);
    }

    private function authorizeTask(
        Request $request,
        Patient $patient,
        AiMedicalDocumentTask $task
    ): void {
        abort_unless(
            (int) $task->patient_id === (int) $patient->id
            && (int) $task->user_id === (int) $request->user()?->id,
            404
        );
    }

    private function payload(
        AiMedicalDocumentTask $task,
        Patient $patient
    ): array {
        $result = $task->status === AiMedicalDocumentTask::STATUS_COMPLETED
            ? (array) $task->result_payload
            : null;

        return [
            'id' => (string) $task->id,
            'status' => (string) $task->status,
            'progress' => (int) $task->progress,
            'message' => $this->message($task),
            'result' => $result,
            'poll_url' => route(
                'doctors.patients.ai-document-tasks.show',
                ['patient' => $patient->id, 'task' => $task->id]
            ),
            'cancel_url' => route(
                'doctors.patients.ai-document-tasks.cancel',
                ['patient' => $patient->id, 'task' => $task->id]
            ),
            'anamneses_confirm_url' => route(
                'doctors.patients.ai-document-tasks.anamneses.confirm',
                ['patient' => $patient->id, 'task' => $task->id]
            ),
        ];
    }

    private function message(AiMedicalDocumentTask $task): string
    {
        return match ($task->status) {
            AiMedicalDocumentTask::STATUS_QUEUED =>
                'Задача поставлена в очередь.',
            AiMedicalDocumentTask::STATUS_RUNNING =>
                $task->progress >= 68
                    ? 'Документ сформирован. ИИ распределяет анамнез.'
                    : 'ИИ анализирует медицинскую карту.',
            AiMedicalDocumentTask::STATUS_COMPLETED =>
                'Черновик сформирован.',
            AiMedicalDocumentTask::STATUS_CANCELLED =>
                'Генерация отменена.',
            AiMedicalDocumentTask::STATUS_FAILED =>
                (string) ($task->error_message ?: 'Генерация завершилась ошибкой.'),
            default => 'Обработка задачи.',
        };
    }
}
