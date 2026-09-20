<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\CommissionDocument;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommissionDocumentController extends Controller
{
    public function military(Request $request): View
    {
        return $this->page(
            $request,
            CommissionDocument::TYPE_MILITARY,
            'doctors.commissions.military'
        );
    }

    public function mse(Request $request): View
    {
        return $this->page(
            $request,
            CommissionDocument::TYPE_MSE,
            'doctors.commissions.mse'
        );
    }

    public function storeMilitary(Request $request): RedirectResponse
    {
        return $this->store($request, CommissionDocument::TYPE_MILITARY);
    }

    public function storeMse(Request $request): RedirectResponse
    {
        return $this->store($request, CommissionDocument::TYPE_MSE);
    }

    private function page(Request $request, string $type, string $view): View
    {
        $selectedPatient = null;
        $activeDocument = null;
        $documents = collect();
        $patientId = $request->integer('patient_id');

        if ($patientId > 0) {
            $selectedPatient = Patient::query()
                ->with(['doctor', 'diagnose'])
                ->find($patientId);

            if ($selectedPatient) {
                $doctorId = $this->currentDoctorId();

                $query = CommissionDocument::query()
                    ->where('patient_id', $selectedPatient->id)
                    ->where('type', $type)
                    ->when(
                        $doctorId !== null,
                        static fn ($builder) => $builder->where('doctor_id', $doctorId)
                    );

                $requestedDocumentId = $request->integer('document_id');

                if ($requestedDocumentId > 0) {
                    $activeDocument = (clone $query)
                        ->whereKey($requestedDocumentId)
                        ->first();
                }

                $activeDocument ??= (clone $query)
                    ->where('status', CommissionDocument::STATUS_DRAFT)
                    ->latest('updated_at')
                    ->first();

                $documents = (clone $query)
                    ->latest('created_at')
                    ->limit(8)
                    ->get();
            }
        }

        return view($view, [
            'commissionType' => $type,
            'selectedPatient' => $selectedPatient,
            'activeDocument' => $activeDocument,
            'documents' => $documents,
        ]);
    }

    private function store(Request $request, string $type): RedirectResponse
    {
        $common = $request->validate([
            'document_id' => ['nullable', 'integer', 'min:1'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'status' => ['required', Rule::in([
                CommissionDocument::STATUS_DRAFT,
                CommissionDocument::STATUS_PREPARED,
            ])],
            'document_date' => ['nullable', 'date'],
        ]);

        $specific = $request->validate(
            $type === CommissionDocument::TYPE_MILITARY
                ? $this->militaryRules()
                : $this->mseRules()
        );

        $patient = Patient::query()
            ->with('diagnose')
            ->findOrFail((int) $common['patient_id']);

        $doctorId = $this->currentDoctorId();
        $documentId = (int) ($common['document_id'] ?? 0);
        $document = null;

        if ($documentId > 0) {
            $document = CommissionDocument::query()
                ->whereKey($documentId)
                ->where('patient_id', $patient->id)
                ->where('type', $type)
                ->when(
                    $doctorId !== null,
                    static fn ($builder) => $builder->where('doctor_id', $doctorId)
                )
                ->first();
        }

        $document ??= new CommissionDocument();

        $data = Arr::except($specific, [
            'written_consent',
            'examination_conditions',
            'purposes',
            'attachments',
        ]);

        if ($type === CommissionDocument::TYPE_MILITARY) {
            $data['attachments'] = array_values((array) $request->input('attachments', []));
        } else {
            $data['written_consent'] = $request->boolean('written_consent');
            $data['examination_conditions'] = array_values((array) $request->input('examination_conditions', []));
            $data['purposes'] = array_values((array) $request->input('purposes', []));
        }

        // Диагноз не дублируем отдельным выбором в форме: сохраняем снимок
        // диагноза из карточки пациента на момент формирования документа.
        $data['diagnosis_snapshot'] = [
            'id' => data_get($patient, 'diagnose.id'),
            'code' => data_get($patient, 'diagnose.code'),
            'title' => data_get($patient, 'diagnose.title')
                ?? data_get($patient, 'diagnose.name'),
        ];

        $document->fill([
            'patient_id' => $patient->id,
            'doctor_id' => $doctorId,
            'type' => $type,
            'status' => (string) $common['status'],
            'document_date' => $common['document_date'] ?? now()->toDateString(),
            'data' => $data,
        ]);
        $document->save();

        if (!$document->number) {
            $prefix = $type === CommissionDocument::TYPE_MILITARY ? 'ВВК' : 'МСЭ';
            $document->number = sprintf(
                '%s-%s-%06d',
                $prefix,
                now()->format('Y'),
                $document->id
            );
            $document->save();
        }

        $route = $type === CommissionDocument::TYPE_MILITARY
            ? 'doctors.commissions.military'
            : 'doctors.commissions.mse';

        return redirect()
            ->route($route, [
                'patient_id' => $patient->id,
                'document_id' => $document->id,
            ])
            ->with([
                'status' => 'success',
                'message' => $common['status'] === CommissionDocument::STATUS_PREPARED
                    ? 'Документ подготовлен и сохранён.'
                    : 'Черновик сохранён.',
            ]);
    }

    private function militaryRules(): array
    {
        return [
            'recipient' => ['nullable', 'string', 'max:500'],
            'request_number' => ['nullable', 'string', 'max:100'],
            'request_date' => ['nullable', 'date'],
            'purpose' => ['nullable', 'string', 'max:100'],
            'purpose_other' => ['nullable', 'string', 'max:500'],
            'commission_question' => ['nullable', 'string', 'max:3000'],
            'complaints_anamnesis' => ['nullable', 'string', 'max:12000'],
            'objective_status' => ['nullable', 'string', 'max:12000'],
            'examination_results' => ['nullable', 'string', 'max:12000'],
            'treatment_dynamics' => ['nullable', 'string', 'max:12000'],
            'functional_limitations' => ['nullable', 'string', 'max:8000'],
            'medical_information' => ['nullable', 'string', 'max:12000'],
            'additional_notes' => ['nullable', 'string', 'max:8000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['string', Rule::in([
                'outpatient_extract',
                'discharge_summaries',
                'laboratory',
                'instrumental',
                'specialists',
                'treatment_documents',
            ])],
        ];
    }

    private function mseRules(): array
    {
        return [
            'vk_protocol_number' => ['nullable', 'string', 'max:100'],
            'vk_protocol_date' => ['nullable', 'date'],
            'written_consent' => ['nullable'],
            'consent_date' => ['nullable', 'date'],
            'examination_conditions' => ['nullable', 'array'],
            'examination_conditions.*' => ['string', Rule::in([
                'home',
                'palliative',
                'post_amputation',
            ])],
            'purposes' => ['nullable', 'array'],
            'purposes.*' => ['string', Rule::in([
                'disability_group',
                'disabled_child',
                'cause_disability',
                'onset_time',
                'disability_term',
                'professional_loss_degree',
                'outside_care',
                'ipr',
                'rehabilitation_program',
                'other',
            ])],
            'purpose_other' => ['nullable', 'string', 'max:1000'],
            'clinical_history' => ['nullable', 'string', 'max:12000'],
            'complications_concomitant' => ['nullable', 'string', 'max:8000'],
            'objective_status' => ['nullable', 'string', 'max:12000'],
            'functional_disorders' => ['nullable', 'string', 'max:12000'],
            'examination_results' => ['nullable', 'string', 'max:12000'],
            'treatment_rehabilitation' => ['nullable', 'string', 'max:12000'],
            'rehabilitation_results' => ['nullable', 'string', 'max:8000'],
            'clinical_prognosis' => ['nullable', 'string', 'max:4000'],
            'rehabilitation_potential' => ['nullable', 'string', 'max:4000'],
            'employment_info' => ['nullable', 'string', 'max:5000'],
            'recommendations' => ['nullable', 'string', 'max:8000'],
            'additional_notes' => ['nullable', 'string', 'max:8000'],
        ];
    }

    private function currentDoctorId(): ?int
    {
        $user = auth()->user();

        if (!$user || !method_exists($user, 'doctor')) {
            return null;
        }

        return $user->doctor()->value('id');
    }
}
