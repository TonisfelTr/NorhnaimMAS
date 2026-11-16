<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateMedicationAssignmentMedGemmaJob;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class MedicationAssignmentAiController extends Controller
{
    public function start(
        Request $request,
        Patient $patient
    ): JsonResponse {
        $data = $request->validate([
            'patient_id' => ['required', 'integer'],
            'drug_id' => ['required', 'integer'],
            'drug_name' => ['required', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:1000'],
            'regimen' => ['nullable', 'string', 'max:2000'],
            'diagnosis_code' => ['nullable', 'string', 'max:64'],
            'birth_at' => ['nullable', 'string', 'max:64'],
            'dosage_unit' => ['nullable', 'string', 'max:50'],
            'drug_form' => ['nullable', 'string', 'max:255'],
        ]);

        if ((int) $data['patient_id'] !== (int) $patient->id) {
            return response()->json([
                'message' =>
                    'Пациент запроса не совпадает с пациентом медкарты.',
            ], 422);
        }

        $taskId = (string) Str::uuid();
        $userId = (int) ($request->user()?->id ?? 0);

        $status = [
            'task_id' => $taskId,
            'status' => 'queued',
            'progress' => 5,
            'stage' => 'queued',
            'stage_label' => 'В очереди',
            'message' => 'Задание поставлено в очередь.',
            'patient_id' => (int) $patient->id,
            'user_id' => $userId,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

        $this->statusStore()->put(
            GenerateMedicationAssignmentMedGemmaJob::cacheKey($taskId),
            $status,
            now()->addMinutes(
                (int) config(
                    'medgemma.status_ttl_minutes',
                    30
                )
            )
        );

        try {
            GenerateMedicationAssignmentMedGemmaJob::dispatch(
                $taskId,
                (int) $patient->id,
                $userId,
                $data
            )
                ->onConnection(
                    (string) config(
                        'medgemma.queue_connection',
                        'redis'
                    )
                )
                ->onQueue(
                    (string) config(
                        'medgemma.queue',
                        'medgemma'
                    )
                );
        } catch (Throwable $e) {
            report($e);

            $status['status'] = 'failed';
            $status['progress'] = 5;
            $status['stage'] = 'queue_error';
            $status['stage_label'] = 'Ошибка очереди';
            $status['error'] =
                'Не удалось поставить задание в очередь.';
            $status['updated_at'] =
                now()->toIso8601String();

            $this->statusStore()->put(
                GenerateMedicationAssignmentMedGemmaJob::cacheKey($taskId),
                $status,
                now()->addMinutes(
                    (int) config(
                        'medgemma.status_ttl_minutes',
                        30
                    )
                )
            );

            return response()->json([
                'message' =>
                    'Не удалось запустить формирование инструкции.',
            ], 500);
        }

        return response()->json($status, 202);
    }

    public function status(
        Request $request,
        Patient $patient,
        string $task
    ): JsonResponse {
        $status = $this->statusStore()->get(
            GenerateMedicationAssignmentMedGemmaJob::cacheKey($task)
        );

        if (!is_array($status)) {
            return response()->json([
                'message' =>
                    'Задание не найдено или срок его действия истёк.',
            ], 404);
        }

        if (
            (int) ($status['patient_id'] ?? 0)
            !== (int) $patient->id
        ) {
            return response()->json([
                'message' => 'Задание относится к другому пациенту.',
            ], 404);
        }

        $userId = (int) ($request->user()?->id ?? 0);

        if (
            (int) ($status['user_id'] ?? 0) !== 0
            && (int) ($status['user_id'] ?? 0) !== $userId
        ) {
            return response()->json([
                'message' => 'Нет доступа к этому заданию.',
            ], 403);
        }

        return response()->json($status);
    }

    /*
     * Старый синхронный endpoint из V8 оставляем недоступным
     * для нового UI, но метод сохраняем для обратной совместимости.
     */
    public function generate(
        Request $request,
        Patient $patient
    ): JsonResponse {
        return response()->json([
            'message' =>
                'Формирование инструкции выполняется в фоновом режиме.',
        ], 409);
    }

    private function statusStore()
    {
        return Cache::store(
            (string) config(
                'medgemma.status_store',
                'redis'
            )
        );
    }
}
