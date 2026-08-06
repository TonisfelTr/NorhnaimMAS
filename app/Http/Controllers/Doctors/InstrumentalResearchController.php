<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\InstrumentalResearchActionRequest;
use App\Http\Requests\InstrumentalResearchMediaRequest;
use App\Http\Requests\StoreInstrumentalResearchesRequest;
use App\Http\Requests\StoreInstrumentalResearchResultRequest;
use App\Http\Requests\UpdateInstrumentalResearchRequest;
use App\Models\InstrumentalResearch;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InstrumentalResearchController extends Controller
{
    /**
     * Создаёт новое инструментальное исследование пациента.
     */
    public function store(
        StoreInstrumentalResearchesRequest $request,
        Patient $patient
    ): RedirectResponse {
        InstrumentalResearch::query()->create(
            $request->validatedData($patient)
        );

        return $this->redirectToPatientCard(
            patientId: (int) $patient->id,
            message: 'Инструментальное исследование назначено.'
        );
    }

    /**
     * Обновляет параметры назначенного исследования.
     *
     * Маршрут update содержит только {instrumentalResearch},
     * поэтому patient_id берётся из самого исследования.
     */
    public function update(
        UpdateInstrumentalResearchRequest $request,
        InstrumentalResearch $instrumentalResearch
    ): RedirectResponse {
        $instrumentalResearch->update($request->validated());

        return $this->redirectToPatientCard(
            patientId: (int) $instrumentalResearch->patient_id,
            message: 'Исследование обновлено.'
        );
    }

    /**
     * Сохраняет результат исследования и прикреплённые файлы.
     */
    public function result(
        StoreInstrumentalResearchResultRequest $request,
        Patient $patient,
        InstrumentalResearch $instrumentalResearch
    ): RedirectResponse {
        $this->ensureBelongsToPatient($patient, $instrumentalResearch);

        $instrumentalResearch->update($request->validatedData());

        $doctorId = $request->user()?->doctor?->id;

        foreach ($request->file('result_files', []) as $uploadedFile) {
            $fileAdder = $instrumentalResearch
                ->addMedia($uploadedFile)
                ->usingName($uploadedFile->getClientOriginalName());

            if ($doctorId !== null) {
                $fileAdder->withCustomProperties([
                    'uploaded_by_doctor_id' => (int) $doctorId,
                ]);
            }

            $fileAdder->toMediaCollection(
                InstrumentalResearch::MEDIA_COLLECTION_RESULTS
            );
        }

        return $this->redirectToPatientCard(
            patientId: (int) $patient->id,
            message: 'Заключение и цифровые материалы исследования сохранены.',
            instrumentalResearchId: (int) $instrumentalResearch->id
        );
    }

    /**
     * Фиксирует просмотр готового результата.
     */
    public function viewed(
        InstrumentalResearchActionRequest $request,
        Patient $patient,
        InstrumentalResearch $instrumentalResearch
    ): JsonResponse {
        $this->ensureBelongsToPatient($patient, $instrumentalResearch);

        if (
            $instrumentalResearch->status === 'ready'
            && $instrumentalResearch->result_showed_at === null
        ) {
            $instrumentalResearch->update([
                'result_showed_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Удаляет инструментальное исследование.
     */
    public function destroy(
        InstrumentalResearchActionRequest $request,
        Patient $patient,
        InstrumentalResearch $instrumentalResearch
    ): RedirectResponse {
        $this->ensureBelongsToPatient($patient, $instrumentalResearch);

        $instrumentalResearch->delete();

        return $this->redirectToPatientCard(
            patientId: (int) $patient->id,
            message: 'Исследование удалено.'
        );
    }

    /**
     * Возвращает прикреплённый файл для просмотра в браузере.
     */
    public function viewMedia(
        InstrumentalResearchMediaRequest $request,
        Patient $patient,
        InstrumentalResearch $instrumentalResearch,
        Media $media
    ): StreamedResponse {
        $this->ensureBelongsToPatient($patient, $instrumentalResearch);
        $this->ensureMediaBelongsToResearch($instrumentalResearch, $media);

        return $media->toInlineResponse($request);
    }

    /**
     * Проверяет, что исследование относится к пациенту из URL.
     */
    private function ensureBelongsToPatient(
        Patient $patient,
        InstrumentalResearch $instrumentalResearch
    ): void {
        abort_unless(
            (int) $instrumentalResearch->patient_id === (int) $patient->id,
            404
        );
    }

    /**
     * Проверяет, что файл принадлежит указанному исследованию.
     */
    private function ensureMediaBelongsToResearch(
        InstrumentalResearch $instrumentalResearch,
        Media $media
    ): void {
        abort_unless(
            (int) $media->model_id === (int) $instrumentalResearch->id
            && (string) $media->model_type
            === (string) $instrumentalResearch->getMorphClass(),
            404
        );
    }

    /**
     * Возвращает врача в раздел инструментальных исследований медкарты.
     */
    private function redirectToPatientCard(
        int $patientId,
        string $message,
        ?int $instrumentalResearchId = null
    ): RedirectResponse {
        $parameters = [
            'patient' => $patientId,
            'tab' => 'labs',
            'section' => 'instrumental',
        ];

        if ($instrumentalResearchId !== null) {
            $parameters['instrumental_research'] =
                $instrumentalResearchId;
        }

        return redirect()
            ->route('doctors.patients.medical_card', $parameters)
            ->with('success', $message);
    }
}
