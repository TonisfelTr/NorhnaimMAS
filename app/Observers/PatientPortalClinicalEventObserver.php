<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\InstrumentalResearch;
use App\Models\LabResearch;
use App\Models\LabResearchResult;
use App\Models\MedicineDocument;
use App\Models\PatientMedicationAssignment;
use App\Models\TestAssignment;
use App\Models\TestSession;
use App\Services\PatientPortalEventNotifier;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

/** Observes only patient-visible transitions; never sends raw medical values. */
final class PatientPortalClinicalEventObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Model $model): void
    {
        if ($model instanceof MedicineDocument) {
            $this->clinicDocument($model);
        } elseif ($model instanceof PatientMedicationAssignment) {
            $this->medicationCreated($model);
        } elseif ($model instanceof TestAssignment) {
            $this->testAssigned($model);
        } elseif ($model instanceof LabResearch || $model instanceof LabResearchResult) {
            $this->laboratoryReady($model);
        } elseif ($model instanceof InstrumentalResearch) {
            $this->instrumentalReady($model);
        } elseif ($model instanceof TestSession) {
            $this->testResultReady($model);
        }
    }

    public function updated(Model $model): void
    {
        if ($model instanceof MedicineDocument) {
            if ($model->wasChanged('source') || $model->wasChanged('patient_id')) {
                $this->clinicDocument($model);
            }
            if ($model->wasChanged('reviewed_at') && $model->reviewed_at !== null
                && (string) $model->source === 'patient') {
                $this->send((int) $model->patient_id, 'document:reviewed:' . $model->getKey(),
                    'document', 'Документ рассмотрен',
                    'Загруженный вами документ рассмотрен клиникой. Проверьте его в личном кабинете.',
                    '/patient/documents/' . (int) $model->getKey() . '/preview', 'file-earmark-check');
            }
        } elseif ($model instanceof PatientMedicationAssignment) {
            $this->medicationUpdated($model);
        } elseif ($model instanceof TestAssignment) {
            if ($model->wasChanged(['status', 'is_kiosk', 'patient_id', 'session_id'])) {
                $this->testAssigned($model);
                $this->testResultFromAssignment($model);
            }
        } elseif ($model instanceof LabResearch) {
            if ($model->wasChanged(['status', 'patient_id'])) {
                $this->laboratoryReady($model);
            }
        } elseif ($model instanceof LabResearchResult) {
            if ($model->wasChanged('value')) {
                $this->laboratoryReady($model);
            }
        } elseif ($model instanceof InstrumentalResearch) {
            if ($model->wasChanged(['status', 'patient_id'])) {
                $this->instrumentalReady($model);
            }
        } elseif ($model instanceof TestSession) {
            if ($model->wasChanged(['status', 'completed_at'])) {
                $this->testResultReady($model);
            }
        }
    }

    private function clinicDocument(MedicineDocument $document): void
    {
        if ($document->trashed() || (string) ($document->source ?? 'clinic') !== 'clinic') {
            return;
        }
        $this->send((int) $document->patient_id, 'document:clinic:' . $document->getKey(),
            'document', 'Добавлен документ',
            'Клиника добавила новый документ. Он доступен в разделе «Документы».',
            '/patient/documents/' . (int) $document->getKey() . '/preview', 'file-earmark-medical');
    }

    private function medicationCreated(PatientMedicationAssignment $assignment): void
    {
        if ((string) $assignment->status !== PatientMedicationAssignment::STATUS_ACTIVE) {
            return;
        }
        $this->send((int) $assignment->patient_id, 'medication:new:' . $assignment->getKey(),
            'assignment', 'Новое назначение',
            'Врач добавил назначение. Ознакомьтесь с указаниями в разделе «Назначения».',
            '/patient/assignments', 'capsule');
    }

    private function medicationUpdated(PatientMedicationAssignment $assignment): void
    {
        if ($assignment->wasChanged('status')) {
            $status = (string) $assignment->status;
            if ($status === PatientMedicationAssignment::STATUS_ACTIVE) {
                $this->medicationCreated($assignment);
            } elseif ($status === PatientMedicationAssignment::STATUS_CANCELLED) {
                $this->send((int) $assignment->patient_id, 'medication:cancelled:' . $assignment->getKey(),
                    'assignment', 'Назначение отменено',
                    'Врач отменил назначение. Проверьте актуальный план лечения.',
                    '/patient/assignments', 'clipboard-x');
            } elseif ($status === PatientMedicationAssignment::STATUS_COMPLETED) {
                $this->send((int) $assignment->patient_id, 'medication:completed:' . $assignment->getKey(),
                    'assignment', 'Статус назначения изменён',
                    'Назначение отмечено врачом как завершённое. История доступна в личном кабинете.',
                    '/patient/assignments', 'clipboard2-check');
            }
            return;
        }

        if ((string) $assignment->status !== PatientMedicationAssignment::STATUS_ACTIVE
            || !$assignment->wasChanged(['drug_id', 'drug_name_snapshot', 'starts_on', 'ends_on', 'dosage', 'schedule', 'doctor_instructions'])) {
            return;
        }
        $revision = sha1((string) json_encode([
            $assignment->drug_id, $assignment->drug_name_snapshot,
            (string) $assignment->starts_on, (string) $assignment->ends_on,
            $assignment->dosage, $assignment->schedule, $assignment->doctor_instructions,
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
        $this->send((int) $assignment->patient_id,
            'medication:updated:' . $assignment->getKey() . ':' . $revision,
            'assignment', 'Назначение изменено',
            'Врач обновил рекомендации по лечению. Откройте актуальное назначение.',
            '/patient/assignments', 'clipboard2-pulse');
    }

    private function testAssigned(TestAssignment $assignment): void
    {
        if ((bool) $assignment->is_kiosk || !in_array((string) $assignment->status,
            ['assigned', 'awaiting_unlock', 'started', 'in_progress'], true)) {
            return;
        }
        $this->send((int) $assignment->patient_id, 'test:assigned:' . $assignment->getKey(),
            'test', 'Назначено тестирование',
            'Врач назначил тестирование. Откройте раздел «Тестирования» для подробностей.',
            '/patient/tests?tab=assigned', 'clipboard-check');
    }

    private function testResultFromAssignment(TestAssignment $assignment): void
    {
        if ((bool) $assignment->is_kiosk || !$assignment->session_id) {
            return;
        }
        $session = TestSession::query()->find((int) $assignment->session_id);
        if ($session) {
            $this->testResultReady($session);
        }
    }

    private function testResultReady(TestSession $session): void
    {
        if (!in_array((string) $session->status, ['completed', 'complete', 'finished'], true)
            || $session->completed_at === null) {
            return;
        }
        $assignment = TestAssignment::query()->where('session_id', $session->getKey())->first();
        if (!$assignment || (bool) $assignment->is_kiosk
            || (int) $assignment->patient_id !== (int) $session->patient_id) {
            return;
        }
        $this->send((int) $session->patient_id, 'test:result:' . $assignment->getKey(),
            'test', 'Результат тестирования доступен',
            'Результат назначенного тестирования доступен в личном кабинете.',
            '/patient/tests?tab=assigned', 'clipboard-data');
    }

    private function laboratoryReady(LabResearch|LabResearchResult $model): void
    {
        $research = $model instanceof LabResearch ? $model : $model->research;
        if (!$research || strtolower((string) $research->status) !== 'ready') {
            return;
        }
        // An empty record is not a finished laboratory result.
        if (!$research->results()->exists()) {
            return;
        }
        $this->send((int) $research->patient_id, 'research:lab-ready:' . $research->getKey(),
            'research', 'Результаты исследования готовы',
            'Лабораторное исследование отмечено как готовое. Проверьте раздел «Исследования» или уточните доступность результатов в клинике.',
            '/patient/research', 'clipboard2-data');
    }

    private function instrumentalReady(InstrumentalResearch $research): void
    {
        if ((string) $research->status !== 'ready') {
            return;
        }
        $this->send((int) $research->patient_id, 'research:instrumental-ready:' . $research->getKey(),
            'research', 'Заключение исследования готово',
            'Врач отметил инструментальное исследование как готовое. Проверьте документы или уточните порядок получения заключения в клинике.',
            '/patient/documents?tab=clinic', 'activity');
    }

    private function send(int $patientId, string $key, string $category, string $title,
        string $message, string $url, string $icon): void
    {
        app(PatientPortalEventNotifier::class)->notifyOnce($patientId, $key, $category,
            $title, $message, $url, $icon);
    }
}
