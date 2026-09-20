<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHospitalizationReferralRequest;
use App\Models\HospitalizationReferral;
use App\Models\Diagnose;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\HospitalizationAnalyzeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class HospitalizationReferralController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('doctors.referrals.hospital.planned');
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('doctors.referrals.hospital.planned');
    }

    public function planned(Request $request): View
    {
        return $this->page($request, HospitalizationReferral::TYPE_PLANNED);
    }

    public function emergency(Request $request): View
    {
        return $this->page($request, HospitalizationReferral::TYPE_EMERGENCY);
    }


    /**
     * Пустой печатный бланк направления.
     * Никаких данных пациента/направления в представление не передаём.
     */
    public function printBlank(): Response
    {
        return $this->printDocumentResponse(
            $this->buildPrintViewData(
                referral: null,
                blank: true
            )
        );
    }

    /**
     * Печать уже сохранённого направления.
     */
    public function printFilled(
        HospitalizationReferral $referral
    ): Response
    {
        $doctorId = $this->currentDoctorId();

        if (
            $doctorId !== null
            && $referral->doctor_id !== null
            && (int)$referral->doctor_id !== $doctorId
        ) {
            abort(403);
        }

        $referral->loadMissing([
            'patient.diagnose',
            'doctor',
        ]);

        return $this->printDocumentResponse(
            $this->buildPrintViewData(
                referral: $referral,
                blank: false
            )
        );
    }

    /**
     * Формирует печатную 057/у из текущего содержимого формы.
     *
     * Для заполненного бланка нужен постоянный номер направления. Поэтому при
     * первом вызове печати создаём (или обновляем) черновик и используем его id
     * как стабильную основу номера вида Г-2026-000123.
     */
    public function printPreview(Request $request): Response
    {
        $data = $request->validate([
            'type' => ['required', 'in:planned,emergency'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'referral_id' => ['nullable', 'integer', 'min:1'],
            'source_mode' => ['nullable', 'string', 'max:32'],

            'referral_purpose' => ['nullable', 'string', 'max:100'],
            'care_form_code' => ['nullable', 'in:1,2,3'],
            'care_type' => ['nullable', 'in:1,2,3,4'],
            'care_conditions' => ['nullable', 'in:1,2,3'],
            'treatment_cycles' => ['nullable', 'integer', 'min:0', 'max:999'],

            'destination_organization' => ['nullable', 'string', 'max:255'],
            'destination_address' => ['nullable', 'string', 'max:500'],
            'department' => ['nullable', 'string', 'max:255'],

            'diagnosis_code' => ['required', 'string', 'max:32'],
            'diagnosis_title' => ['nullable', 'string', 'max:255'],

            'clinical_summary' => ['nullable', 'string', 'max:20000'],
            'reason' => ['nullable', 'string', 'max:12000'],

            'planned_at' => ['nullable'],
            'urgency_level' => ['nullable', 'string', 'max:32'],
            'transport_type' => ['nullable', 'string', 'max:64'],

            'treatment_goal' => ['nullable', 'string', 'max:3000'],
            'expected_duration' => ['nullable', 'string', 'max:255'],
            'prehospital_checklist' => ['nullable', 'string', 'max:5000'],

            'deterioration_started_at' => ['nullable'],
            'red_flags' => ['nullable', 'string', 'max:8000'],
            'stabilization_measures' => ['nullable', 'string', 'max:8000'],
            'ambulance_required' => ['nullable'],
            'consciousness' => ['nullable', 'string', 'max:255'],
            'blood_pressure' => ['nullable', 'string', 'max:64'],
            'pulse' => ['nullable'],
            'spo2' => ['nullable'],
            'temperature' => ['nullable'],
            'additional_notes' => ['nullable', 'string', 'max:10000'],
        ]);

        $diagnosis = $this->requireDiagnosis(
            $data['diagnosis_code'] ?? null
        );

// Наименование диагноза берём из справочника diagnoses.
        $data['diagnosis_code'] = $diagnosis->code;
        $data['diagnosis_title'] = $diagnosis->title;

        $patient = Patient::query()
            ->with('diagnose')
            ->findOrFail((int)$data['patient_id']);

        $doctorId = $this->currentDoctorId();
        $doctor = $doctorId !== null
            ? Doctor::query()->find($doctorId)
            : null;

        $type = (string)$data['type'];
        $careFormCode = (string)($data['care_form_code']
            ?? ($type === HospitalizationReferral::TYPE_EMERGENCY ? '1' : '3'));

        $referralId = (int)($data['referral_id'] ?? 0);
        $baseQuery = HospitalizationReferral::query()
            ->where('patient_id', $patient->id)
            ->where('type', $type)
            ->when(
                $doctorId !== null,
                static fn($query) => $query->where('doctor_id', $doctorId)
            );

        $referral = null;

        if ($referralId > 0) {
            $referral = (clone $baseQuery)
                ->whereKey($referralId)
                ->first();
        }

// Если hidden referral_id ещё не обновился после первого открытия печати,
// повторно используем последний черновик, а не создаём дубликаты.
        if (!$referral) {
            $referral = (clone $baseQuery)
                ->where('status', HospitalizationReferral::STATUS_DRAFT)
                ->latest('updated_at')
                ->first();
        }

        $referral ??= new HospitalizationReferral();

        $existingDetails = $this->normalizePrintDetails($referral->details);
        $details = array_merge($existingDetails, array_filter([
            'diagnosis_kind' => 'referral',
            'referral_purpose' => $data['referral_purpose'] ?? 'hospitalization',
            'care_form_code' => $careFormCode,
            'care_type' => (string)($data['care_type'] ?? '2'),
            'care_conditions' => (string)($data['care_conditions'] ?? '3'),
            'treatment_cycles' => $data['treatment_cycles'] ?? null,
            'treatment_goal' => $data['treatment_goal'] ?? null,
            'expected_duration' => $data['expected_duration'] ?? null,
            'prehospital_checklist' => $data['prehospital_checklist'] ?? null,
            'deterioration_started_at' => $data['deterioration_started_at'] ?? null,
            'red_flags' => $data['red_flags'] ?? null,
            'stabilization_measures' => $data['stabilization_measures'] ?? null,
            'ambulance_required' => $request->boolean('ambulance_required'),
            'consciousness' => $data['consciousness'] ?? null,
            'blood_pressure' => $data['blood_pressure'] ?? null,
            'pulse' => $data['pulse'] ?? null,
            'spo2' => $data['spo2'] ?? null,
            'temperature' => $data['temperature'] ?? null,
            'additional_notes' => $data['additional_notes'] ?? null,
        ], static fn(mixed $value): bool => $value !== null && $value !== ''));

        $referral->forceFill([
            'patient_id' => $patient->id,
            'doctor_id' => $doctorId,
            'type' => $type,
            'status' => HospitalizationReferral::STATUS_DRAFT,
            'source_mode' => (string)($data['source_mode'] ?? $referral->source_mode ?? 'manual'),
            'destination_organization' => $data['destination_organization'] ?? null,
            'destination_address' => $data['destination_address'] ?? null,
            'department' => $data['department'] ?? null,
            'diagnosis_code' => $data['diagnosis_code'] ?? null,
            'diagnosis_title' => $data['diagnosis_title'] ?? null,
            'clinical_summary' => $data['clinical_summary'] ?? null,
            'reason' => $data['reason'] ?? null,
            'planned_at' => $data['planned_at'] ?? null,
            'urgency_level' => $data['urgency_level'] ?? null,
            'transport_type' => $data['transport_type'] ?? null,
            'details' => $details !== [] ? $details : null,
        ]);

// Сохраняем черновик именно для получения постоянного номера 057/у.
        $referral->save();
        $referral->setRelation('patient', $patient);
        $referral->setRelation('doctor', $doctor);

        return $this->printDocumentResponse(
            $this->buildPrintViewData(
                referral: $referral,
                blank: false
            )
        );
    }

    /**
     * Сохраняет форму направления. Если передан referral_id, обновляется тот же
     * черновик, который был автоматически создан после ИИ-анализа.
     */
    public function store(
        StoreHospitalizationReferralRequest $request
    ): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

// Эти реквизиты 057/у пока храним в details JSON, поэтому отдельная
// миграция не требуется. Дополнительная валидация нужна даже если
// StoreHospitalizationReferralRequest ещё не содержит новых правил.
        $standardData = $request->validate([
            'referral_purpose' => ['nullable', 'string', 'max:100'],
            'care_form_code' => ['nullable', 'in:1,2,3'],
            'care_type' => ['nullable', 'in:1,2,3,4'],
            'care_conditions' => ['nullable', 'in:1,2,3'],
            'treatment_cycles' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);
        $data = array_merge($data, $standardData);

        $type = (string)$data['type'];
        $doctorId = $this->currentDoctorId();

        $diagnosis = $this->requireDiagnosis(
            $data['diagnosis_code'] ?? null
        );

// Название диагноза берём только из справочника diagnoses.
        $data['diagnosis_code'] = $diagnosis->code;
        $data['diagnosis_title'] = $diagnosis->title;

        $careFormCode = (string)($data['care_form_code']
            ?? ($type === HospitalizationReferral::TYPE_EMERGENCY ? '1' : '3'));

        $details = array_filter([
            'diagnosis_kind' => 'referral',
            'referral_purpose' => $data['referral_purpose'] ?? 'hospitalization',
            'care_form_code' => $careFormCode,
            'care_type' => (string)($data['care_type'] ?? '2'),
            'care_conditions' => (string)($data['care_conditions'] ?? '3'),
            'treatment_cycles' => $data['treatment_cycles'] ?? null,
            'treatment_goal' => $data['treatment_goal'] ?? null,
            'expected_duration' => $data['expected_duration'] ?? null,
            'prehospital_checklist' => $data['prehospital_checklist'] ?? null,
            'deterioration_started_at' => $data['deterioration_started_at'] ?? null,
            'red_flags' => $data['red_flags'] ?? null,
            'stabilization_measures' => $data['stabilization_measures'] ?? null,
            'ambulance_required' => (bool)($data['ambulance_required'] ?? false),
            'consciousness' => $data['consciousness'] ?? null,
            'blood_pressure' => $data['blood_pressure'] ?? null,
            'pulse' => isset($data['pulse']) ? (int)$data['pulse'] : null,
            'spo2' => isset($data['spo2']) ? (int)$data['spo2'] : null,
            'temperature' => isset($data['temperature'])
                ? (float)$data['temperature']
                : null,
            'additional_notes' => $data['additional_notes'] ?? null,
        ], static fn(mixed $value): bool => $value !== null && $value !== '');

        $aiAnalysis = null;
        if (!empty($data['ai_analysis'])) {
            $decoded = json_decode((string)$data['ai_analysis'], true);
            $aiAnalysis = is_array($decoded) ? $decoded : null;
        }

        $payload = [
            'patient_id' => (int)$data['patient_id'],
            'doctor_id' => $doctorId,
            'type' => $type,
            'status' => (string)$data['status'],
            'source_mode' => (string)$data['source_mode'],
            'destination_organization' => $data['destination_organization'] ?? null,
            'destination_address' => $data['destination_address'] ?? null,
            'department' => $data['department'] ?? null,
            'diagnosis_code' => $data['diagnosis_code'] ?? null,
            'diagnosis_title' => $data['diagnosis_title'] ?? null,
            'clinical_summary' => $data['clinical_summary'] ?? null,
            'reason' => $data['reason'] ?? null,
            'planned_at' => $data['planned_at'] ?? null,
            'urgency_level' => $data['urgency_level'] ?? null,
            'transport_type' => $data['transport_type'] ?? null,
            'details' => $details !== [] ? $details : null,
            'ai_analysis' => $aiAnalysis,
            'ai_analyzed_at' => $aiAnalysis ? now() : null,
            'sent_at' => ($data['status'] ?? null) === HospitalizationReferral::STATUS_SENT
                ? now()
                : null,
        ];

        $referralId = (int)($data['referral_id'] ?? 0);
        $referral = null;

        if ($referralId > 0) {
// Hidden referral_id может устареть после смены пациента/вкладки.
// Обновляем запись только если она действительно принадлежит
// текущему пациенту, типу направления и врачу.
            $referral = HospitalizationReferral::query()
                ->whereKey($referralId)
                ->where('patient_id', (int)$data['patient_id'])
                ->where('type', $type)
                ->when(
                    $doctorId !== null,
                    static fn($query) => $query->where('doctor_id', $doctorId)
                )
                ->first();
        }

        if ($referral) {
            $referral->fill($payload)->save();
        } else {
            $referral = HospitalizationReferral::create($payload);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $referral->id,
                'status' => $referral->status,
                'message' => 'Направление сохранено.',
            ]);
        }

        $route = $type === HospitalizationReferral::TYPE_EMERGENCY
            ? 'doctors.referrals.hospital.emergency'
            : 'doctors.referrals.hospital.planned';

        return redirect()
            ->route($route, [
                'patient_id' => $referral->patient_id,
            ])
            ->with([
                'status' => 'success',
                'message' => 'Направление на госпитализацию сохранено.',
            ]);
    }

    /**
     * Qwen + MedGemma. Успешный результат автоматически сохраняется в БД как
     * черновик, чтобы он не терялся при обновлении страницы.
     */
    public function analyze(
        Request                       $request,
        HospitalizationAnalyzeService $service
    ): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:planned,emergency'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'referral_id' => ['nullable', 'integer', 'min:1'],
            'clinical_text' => ['nullable', 'string', 'max:24000'],
            'sources' => ['nullable', 'array'],
            'sources.diagnosis' => ['nullable', 'boolean'],
            'sources.anamneses' => ['nullable', 'boolean'],
            'sources.prescriptions' => ['nullable', 'boolean'],
            'form' => ['nullable', 'array'],
            'form.destination_organization' => ['nullable', 'string', 'max:255'],
            'form.destination_address' => ['nullable', 'string', 'max:500'],
            'form.department' => ['nullable', 'string', 'max:255'],
        ]);

        $patient = Patient::query()
            ->with(['doctor', 'diagnose'])
            ->findOrFail((int)$data['patient_id']);

        $state = $this->patientContextState($patient);
        $requestedSources = (array)($data['sources'] ?? []);

        $sources = [
            'diagnosis' => (bool)($requestedSources['diagnosis'] ?? false)
                && $state['has_current_diagnosis'],
            'anamneses' => (bool)($requestedSources['anamneses'] ?? false)
                && $state['anamneses_count'] > 0,
            'prescriptions' => (bool)($requestedSources['prescriptions'] ?? false)
                && $state['prescriptions_count'] > 0,
        ];

        $result = $service->analyze(
            type: (string)$data['type'],
            patient: $patient,
            clinicalText: (string)($data['clinical_text'] ?? ''),
            sources: $sources
        );

        $result['patient_state'] = $state;

        if (isset($result['error'])) {
            return response()->json($result, 422);
        }

        $referral = $this->persistAnalysisDraft(
            patient: $patient,
            type: (string)$data['type'],
            result: $result,
            formSnapshot: (array)($data['form'] ?? []),
            referralId: (int)($data['referral_id'] ?? 0)
        );

        $result['referral_id'] = $referral->id;
        $result['saved_to_database'] = true;

        return response()->json($result);
    }

    public function patientContext(Patient $patient): JsonResponse
    {
        $patient->loadMissing('diagnose');

        return response()->json($this->patientContextState($patient));
    }

    private function persistAnalysisDraft(
        Patient $patient,
        string  $type,
        array   $result,
        array   $formSnapshot,
        int     $referralId = 0
    ): HospitalizationReferral
    {
        $doctorId = $this->currentDoctorId();

        $query = HospitalizationReferral::query()
            ->where('patient_id', $patient->id)
            ->where('type', $type)
            ->when(
                $doctorId !== null,
                static fn($builder) => $builder->where('doctor_id', $doctorId)
            );

        $referral = null;

        if ($referralId > 0) {
// Не доверяем referral_id из DOM без проверки владельца.
// Если id устарел, просто ищем актуальный AI-черновик.
            $referral = (clone $query)->whereKey($referralId)->first();
        }

        if (!$referral) {
            $referral = (clone $query)
                ->where('status', HospitalizationReferral::STATUS_DRAFT)
                ->whereIn('source_mode', [
                    HospitalizationReferral::SOURCE_AI,
                    HospitalizationReferral::SOURCE_COMBINED,
                ])
                ->latest('updated_at')
                ->first();
        }

        $referral ??= new HospitalizationReferral();

        $draft = (array)($result['draft'] ?? []);
        $existingDetails = is_array($referral->details) ? $referral->details : [];

        /*
        * Диагноз в 057/у подтверждает медицинский работник. ИИ может давать
        * клиническую гипотезу, но не должен сам заменять выбранный код МКБ.
        */
        $existingDiagnosis = $this->findDiagnosis(
            $referral->diagnosis_code
        );

        $details = array_merge($existingDetails, array_filter([
            'diagnosis_kind' => 'referral',
            'referral_purpose' => $existingDetails['referral_purpose'] ?? 'hospitalization',
            'care_form_code' => $existingDetails['care_form_code']
                ?? ($type === HospitalizationReferral::TYPE_EMERGENCY ? '1' : '3'),
            'care_type' => $existingDetails['care_type'] ?? '2',
            'care_conditions' => $existingDetails['care_conditions'] ?? '3',
            'treatment_goal' => $draft['treatment_goal'] ?? null,
            'red_flags' => $draft['red_flags'] ?? null,
            'stabilization_measures' => $draft['stabilization_measures'] ?? null,
            'review_verdict' => data_get($result, 'medical_review.verdict'),
            'qwen_confidence' => data_get($result, 'primary.confidence'),
            'medgemma_confidence' => data_get($result, 'medical_review.confidence'),
        ], static fn(mixed $value): bool => $value !== null && $value !== ''));

        $referral->fill([
            'patient_id' => $patient->id,
            'doctor_id' => $doctorId,
            'type' => $type,
            'status' => HospitalizationReferral::STATUS_DRAFT,
            'source_mode' => HospitalizationReferral::SOURCE_AI,
            'destination_organization' => $this->preferText(
                $formSnapshot['destination_organization'] ?? null,
                $referral->destination_organization
            ),
            'destination_address' => $this->preferText(
                $formSnapshot['destination_address'] ?? null,
                $referral->destination_address
            ),
            'department' => $this->preferText(
                $formSnapshot['department'] ?? ($draft['department'] ?? null),
                $referral->department
            ),
            'diagnosis_code' => $existingDiagnosis?->code,
            'diagnosis_title' => $existingDiagnosis?->title,
            'clinical_summary' => $this->preferText(
                $draft['clinical_summary'] ?? null,
                $referral->clinical_summary
            ),
            'reason' => $this->preferText(
                $draft['reason'] ?? null,
                $referral->reason
            ),
            'urgency_level' => $this->preferText(
                $draft['urgency_level'] ?? null,
                $referral->urgency_level
            ),
            'transport_type' => $this->preferText(
                $draft['transport_type'] ?? null,
                $referral->transport_type
            ),
            'details' => $details !== [] ? $details : null,
            'ai_analysis' => $result,
            'ai_analyzed_at' => now(),
        ]);

        $referral->save();

        return $referral;
    }

    /**
     * Возвращает диагноз из общего справочника МКБ.
     */
    private function findDiagnosis(mixed $code): ?Diagnose
    {
        $code = mb_strtoupper(
            trim((string)($code ?? ''))
        );

        if ($code === '') {
            return null;
        }

        return Diagnose::query()
            ->where('code', 'ilike', $code)
            ->first([
                'id',
                'code',
                'title',
            ]);
    }

    /**
     * Диагноз для 057/у обязателен и выбирается из справочника diagnoses.
     *
     * @throws ValidationException
     */
    private function requireDiagnosis(mixed $code): Diagnose
    {
        $diagnosis = $this->findDiagnosis($code);

        if ($diagnosis) {
            return $diagnosis;
        }

        throw ValidationException::withMessages([
            'diagnosis_code' =>
                'Выберите диагноз по МКБ из справочника.',
        ]);
    }


    private function preferText(mixed $candidate, mixed $fallback): ?string
    {
        $candidate = trim((string)($candidate ?? ''));
        if ($candidate !== '') {
            return $candidate;
        }

        $fallback = trim((string)($fallback ?? ''));

        return $fallback !== '' ? $fallback : null;
    }


    /**
     * Возвращает печатный HTML без браузерного/proxy-кэширования.
     *
     * Это критично для скрытого iframe: после правки Blade браузер не должен
     * повторно использовать ранее загруженный GET /print/blank.
     *
     * @param array<string, string> $data
     */
    private function printDocumentResponse(array $data): Response
    {
        return response()
            ->view(
                'doctors.referrals.hospitalizations.hospitalization',
                $data
            )
            ->header(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, max-age=0'
            )
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header(
                'X-Hospitalization-Print-Template',
                'HOSPITALIZATION_PRINT_TEMPLATE_057U_V5'
            );
    }


    /**
     * Подготавливает данные для официального печатного представления 057/у.
     *
     * Представление получает уже готовые строки и коды; медицинская логика и
     * чтение моделей в Blade не выполняются.
     *
     * @return array<string, string>
     */
    private function buildPrintViewData(
        ?HospitalizationReferral $referral,
        bool                     $blank
    ): array
    {
        if ($referral !== null) {
            $referral->loadMissing([
                'patient.diagnose',
                'doctor',
            ]);
        }

        $patient = $blank ? null : $referral?->patient;
        $doctor = $blank ? null : $referral?->doctor;
        $details = $blank
            ? []
            : $this->normalizePrintDetails($referral?->details);

        $doctorName = $this->printPersonName($doctor, $blank);

// Определяем, работает ли врач от медицинской организации или как
// частнопрактикующий специалист. Ошибка relation не должна ломать печать.
        $clinic = null;
        if ($doctor && method_exists($doctor, 'clinic')) {
            try {
                $clinic = $doctor->clinic()->first();
            } catch (\Throwable) {
                $clinic = null;
            }
        }

        if ($blank || $clinic) {
            $organizationName = $this->preferText(
                config('hospitalization.print.organization_name'),
                data_get($clinic, 'name')
                ?? data_get($clinic, 'title')
            );
            $organizationAddress = $this->preferText(
                config('hospitalization.print.organization_address'),
                data_get($clinic, 'address')
                ?? data_get($clinic, 'legal_address')
            );
            $organizationOgrn = $this->preferText(
                config('hospitalization.print.organization_ogrn'),
                data_get($clinic, 'ogrn')
                ?? data_get($clinic, 'OGRN')
            );
        } else {
            $organizationName = $this->preferText(
                data_get($doctor, 'organization_name'),
                $doctorName !== ''
                    ? 'Частная практика — ' . $doctorName
                    : 'Частная практика'
            );
            $organizationAddress = $this->printText(
                data_get($doctor, 'address_job')
                ?? data_get($doctor, 'work_address')
                ?? data_get($doctor, 'address')
            );
            $organizationOgrn = $this->printText(
                data_get($doctor, 'ogrnip')
                ?? data_get($doctor, 'OGRNIP')
                ?? data_get($doctor, 'ogrn')
            );
        }

        $issuedAt = $blank
            ? null
            : ($referral?->created_at ?? now());

        $documentNumber = '';
        if (!$blank && $referral?->id) {
            $year = $this->formatPrintDate($issuedAt, 'Y');
            $documentNumber = $this->printText(
                data_get($referral, 'direction_number')
                ?? data_get($referral, 'number')
            );

            if ($documentNumber === '') {
                $documentNumber = 'Г-' . $year . '-' . str_pad(
                        (string)$referral->id,
                        6,
                        '0',
                        STR_PAD_LEFT
                    );
            }
        }

        $omsIssuedAt = $blank
            ? null
            : (
                data_get($patient, 'oms_issued_at')
                ?? data_get($patient, 'oms_issue_date')
                ?? data_get($patient, 'policy_issued_at')
                ?? data_get($patient, 'insurance_policy_issued_at')
            );

        $destinationParts = $blank
            ? []
            : array_filter([
                $this->printText($referral?->destination_organization),
                $this->printText($referral?->department),
                $this->printText($referral?->destination_address),
            ], static fn(string $value): bool => $value !== '');

        $diagnosis = $blank
            ? ''
            : collect([
                $referral?->diagnosis_code,
                $referral?->diagnosis_title,
            ])
                ->filter(
                    static fn(mixed $value): bool => trim((string)($value ?? '')) !== ''
                )
                ->implode(' — ');

        $type = $blank
            ? ''
            : trim((string)($referral?->type ?? ''));

        $defaultCareForm = $type === HospitalizationReferral::TYPE_EMERGENCY
            ? '1'
            : ($type === HospitalizationReferral::TYPE_PLANNED ? '3' : '');

        $careFormCode = $this->normalizePrintCode(
            data_get($details, 'care_form_code', $defaultCareForm),
            ['1', '2', '3']
        );
        $careTypeCode = $this->normalizePrintCode(
            data_get($details, 'care_type', $blank ? '' : '2'),
            ['1', '2', '3', '4']
        );
        $careConditionsCode = $this->normalizePrintCode(
            data_get($details, 'care_conditions', $blank ? '' : '3'),
            ['1', '2', '3']
        );

        $reason = $this->printText(
            $blank ? null : $referral?->reason
        );
        $treatmentCycles = $this->printText(
            data_get($details, 'treatment_cycles')
        );
        if ($treatmentCycles !== '') {
            $reason = trim($reason . ($reason !== '' ? "\n" : '')
                . 'Число назначаемых курсов (циклов) лечения: '
                . $treatmentCycles);
        }

        $doctorPosition = $this->printText(
            $blank
                ? null
                : (
                data_get($doctor, 'position')
                ?? data_get($doctor, 'job_title')
            )
        );
        $doctorSpeciality = $this->printText(
            $blank
                ? null
                : (
                data_get($doctor, 'speciality')
                ?? data_get($doctor, 'specialty')
                ?? data_get($doctor, 'status')
            )
        );

        $doctorPositionSpeciality = collect([
            $doctorPosition,
            $doctorSpeciality,
        ])
            ->filter(static fn(string $value): bool => $value !== '')
            ->unique()
            ->implode(', ');

        return [
            'organizationName' => $this->printText($organizationName),
            'organizationAddress' => $this->printText($organizationAddress),
            'organizationOgrn' => $this->printText($organizationOgrn),

            'documentNumber' => $documentNumber,
            'issuedDay' => $this->formatPrintDate($issuedAt, 'd'),
            'issuedMonth' => $this->formatPrintDate($issuedAt, 'm'),
            'issuedYear' => $this->formatPrintDate($issuedAt, 'Y'),

            'destinationOrganization' => implode(', ', $destinationParts),

            'oms' => $this->printText(
                $blank
                    ? null
                    : (
                    data_get($patient, 'oms')
                    ?? data_get($patient, 'oms_policy')
                    ?? data_get($patient, 'insurance_policy')
                    ?? data_get($patient, 'policy')
                )
            ),
            'omsIssuedDay' => $this->formatPrintDate($omsIssuedAt, 'd'),
            'omsIssuedMonth' => $this->formatPrintDate($omsIssuedAt, 'm'),
            'omsIssuedYear' => $this->formatPrintDate($omsIssuedAt, 'Y'),
            'insuranceOrganization' => $this->printText(
                $blank
                    ? null
                    : (
                    data_get($patient, 'insurance_organization')
                    ?? data_get($patient, 'insurance_company')
                    ?? data_get($patient, 'smo')
                    ?? data_get($patient, 'smo_name')
                )
            ),

            'patientName' => $this->printPersonName($patient, $blank),
            'birthDate' => $this->formatPrintDate(
                $blank ? null : data_get($patient, 'birth_at'),
                'd.m.Y'
            ),
            'sexCode' => $this->printSexCode($patient, $blank),
            'registrationAddress' => $this->printText(
                $blank
                    ? null
                    : (
                    data_get($patient, 'registration_address')
                    ?? data_get($patient, 'address_registration')
                    ?? data_get($patient, 'address')
                )
            ),
            'stayAddress' => $this->printText(
                $blank
                    ? null
                    : (
                    data_get($patient, 'stay_address')
                    ?? data_get($patient, 'actual_address')
                    ?? data_get($patient, 'residential_address')
                )
            ),
            'localityCode' => $this->printLocalityCode($patient, $blank),
            'employmentCode' => $this->printEmploymentCode($patient, $blank),

            'diagnosis' => $diagnosis,
            'referralPurpose' => $this->printReferralPurpose(
                data_get($details, 'referral_purpose'),
                $blank
            ),
            'careFormCode' => $careFormCode,
            'careTypeCode' => $careTypeCode,
            'careConditionsCode' => $careConditionsCode,
            'reason' => $reason,

            'doctorPositionSpeciality' => $doctorPositionSpeciality,
            'doctorName' => $doctorName,
        ];
    }

    private function normalizePrintCode(mixed $value, array $allowed): string
    {
        $value = trim((string)($value ?? ''));

        return in_array($value, $allowed, true) ? $value : '';
    }

    private function printSexCode(mixed $patient, bool $blank): string
    {
        if ($blank || !$patient) {
            return '';
        }

        $raw = mb_strtolower(trim((string)(
            data_get($patient, 'sex')
            ?? data_get($patient, 'gender')
            ?? ''
        )));

        if (in_array($raw, ['m', 'male', 'м', 'муж', 'мужской', '1'], true)) {
            return '1';
        }

        if (in_array($raw, ['f', 'female', 'ж', 'жен', 'женский', '2'], true)) {
            return '2';
        }

        return '';
    }

    private function printLocalityCode(mixed $patient, bool $blank): string
    {
        if ($blank || !$patient) {
            return '';
        }

        $raw = data_get($patient, 'locality_code')
            ?? data_get($patient, 'locality_type')
            ?? data_get($patient, 'settlement_type')
            ?? null;

        if (is_bool(data_get($patient, 'is_rural'))) {
            return data_get($patient, 'is_rural') ? '2' : '1';
        }

        $raw = mb_strtolower(trim((string)($raw ?? '')));

        if (in_array($raw, ['1', 'urban', 'город', 'городская', 'городской'], true)) {
            return '1';
        }

        if (in_array($raw, ['2', 'rural', 'село', 'сельская', 'сельский'], true)) {
            return '2';
        }

        return '';
    }

    private function printEmploymentCode(mixed $patient, bool $blank): string
    {
        if ($blank || !$patient) {
            return '';
        }

        $raw = mb_strtolower(trim((string)(
            data_get($patient, 'employment_code')
            ?? data_get($patient, 'employment_status')
            ?? data_get($patient, 'employment')
            ?? ''
        )));

        if (in_array($raw, ['1', 'работает', 'working', 'employed'], true)) {
            return '1';
        }
        if (in_array($raw, ['2', 'military', 'военная служба', 'военнослужащий'], true)) {
            return '2';
        }
        if (in_array($raw, ['3', 'пенсионер', 'retired', 'pensioner'], true)) {
            return '3';
        }
        if (in_array($raw, ['4', 'обучающийся', 'студент', 'student'], true)) {
            return '4';
        }
        if (in_array($raw, ['5', 'не работает', 'unemployed'], true)) {
            return '5';
        }
        if (in_array($raw, ['6', 'прочие', 'other'], true)) {
            return '6';
        }

        return '';
    }

    private function printReferralPurpose(mixed $value, bool $blank): string
    {
        if ($blank) {
            return '';
        }

        $value = mb_strtolower(trim((string)($value ?? 'hospitalization')));

        return match ($value) {
            'hospitalization', 'госпитализация' => 'Госпитализация',
            'consultation', 'консультация' => 'Консультация',
            'examination', 'обследование' => 'Обследование',
            default => $this->printText($value),
        };
    }

    private function normalizePrintDetails(mixed $details): array
    {
        if (is_array($details)) {
            return $details;
        }

        if (is_string($details) && trim($details) !== '') {
            $decoded = json_decode($details, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function printText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Да' : 'Нет';
        }

        if (is_array($value)) {
            return collect($value)
                ->flatten()
                ->filter(
                    static fn(mixed $item): bool => trim((string)($item ?? '')) !== ''
                )
                ->implode(', ');
        }

        return trim((string)$value);
    }

    private function formatPrintDate(
        mixed  $value,
        string $format = 'd.m.Y'
    ): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (\Throwable) {
            return trim((string)$value);
        }
    }

    private function printPersonName(
        mixed $person,
        bool  $blank
    ): string
    {
        if ($blank || !$person) {
            return '';
        }

        return collect([
            data_get($person, 'surname'),
            data_get($person, 'name'),
            data_get($person, 'patronym'),
        ])
            ->filter(
                static fn(mixed $value): bool => trim((string)($value ?? '')) !== ''
            )
            ->implode(' ');
    }

    private function printPatientSex(
        mixed $patient,
        bool  $blank
    ): string
    {
        if ($blank || !$patient) {
            return '';
        }

        $raw = trim((string)(
            data_get($patient, 'sex')
            ?? data_get($patient, 'gender')
            ?? ''
        ));

        $normalized = mb_strtolower($raw);

        if (
            in_array(
                $normalized,
                ['m', 'male', 'м', 'муж', 'мужской', '1'],
                true
            )
        ) {
            return 'Мужской';
        }

        if (
            in_array(
                $normalized,
                ['f', 'female', 'ж', 'жен', 'женский', '2'],
                true
            )
        ) {
            return 'Женский';
        }

        return $raw;
    }

    private function page(Request $request, string $type): View
    {
        $selectedPatient = null;
        $selectedPatientContext = null;
        $activeDraft = null;
        $patientId = $request->integer('patient_id');

        if ($patientId > 0) {
            $selectedPatient = Patient::query()
                ->with(['doctor', 'diagnose'])
                ->find($patientId);

            if ($selectedPatient) {
                $selectedPatientContext = $this->patientContextState($selectedPatient);

                $activeDraft = HospitalizationReferral::query()
                    ->where('patient_id', $selectedPatient->id)
                    ->where('type', $type)
                    ->where('status', HospitalizationReferral::STATUS_DRAFT)
                    ->when(
                        $this->currentDoctorId() !== null,
                        fn($query) => $query->where('doctor_id', $this->currentDoctorId())
                    )
                    ->latest('updated_at')
                    ->first();
            }
        }

        $diagnoses = Diagnose::query()
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'title',
            ]);

// Оставляем старое имя переменной как fallback для Blade,
// но новая 057/у не ограничивает диагнозы классом Z.
        $zDiagnoses = $diagnoses->filter(
            static fn(Diagnose $diagnosis): bool => str_starts_with(mb_strtoupper((string)$diagnosis->code), 'Z')
        )->values();

        $referrals = HospitalizationReferral::query()
            ->with([
                'patient:id,surname,name,patronym,birth_at',
                'doctor:id,surname,name,patronym',
            ])
            ->ofType($type)
            ->when(
                $selectedPatient,
                static fn($query) => $query->where('patient_id', $selectedPatient->id)
            )
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return view(
            $type === HospitalizationReferral::TYPE_EMERGENCY
                ? 'doctors.referrals.hospitalizations.emergency'
                : 'doctors.referrals.hospitalizations.planned',
            [
                'type' => $type,
                'selectedPatient' => $selectedPatient,
                'selectedPatientContext' => $selectedPatientContext,
                'activeDraft' => $activeDraft,
                'referrals' => $referrals,
                'statusOptions' => $this->statusOptions(),
                'urgencyOptions' => $this->urgencyOptions(),
                'transportOptions' => $this->transportOptions(),
                'diagnoses' => $diagnoses,
                'zDiagnoses' => $zDiagnoses,
            ]
        );
    }

    private function patientContextState(Patient $patient): array
    {
        $patient->loadMissing('diagnose');

        $hasCurrentDiagnosis = (bool)(
            data_get($patient, 'diagnose.code')
            || data_get($patient, 'diagnose.title')
            || data_get($patient, 'diagnose.name')
            || $patient->diagnose_id
        );

        $anamnesesCount = 0;
        if (method_exists($patient, 'anamneses')) {
            try {
                $anamnesesCount = (int)$patient->anamneses()->count();
            } catch (\Throwable) {
                $anamnesesCount = 0;
            }
        }

        $prescriptionsCount = 0;
        if (method_exists($patient, 'prescriptions')) {
            try {
                $prescriptionsCount = (int)$patient->prescriptions()->count();
            } catch (\Throwable) {
                $prescriptionsCount = 0;
            }
        }

        $isFirstVisit = !$hasCurrentDiagnosis
            && $anamnesesCount === 0
            && $prescriptionsCount === 0;

        return [
            'has_current_diagnosis' => $hasCurrentDiagnosis,
            'anamneses_count' => $anamnesesCount,
            'prescriptions_count' => $prescriptionsCount,
            'is_first_visit' => $isFirstVisit,
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

    private function statusOptions(): array
    {
        return [
            HospitalizationReferral::STATUS_DRAFT => 'Черновик',
            HospitalizationReferral::STATUS_PREPARED => 'Подготовлено',
            HospitalizationReferral::STATUS_SENT => 'Направлено',
            HospitalizationReferral::STATUS_ADMITTED => 'Госпитализирован',
            HospitalizationReferral::STATUS_CANCELLED => 'Отменено',
        ];
    }

    private function urgencyOptions(): array
    {
        return [
            'immediate' => 'Немедленно',
            'within_2h' => 'В течение 2 часов',
            'within_24h' => 'В течение 24 часов',
            'urgent' => 'Срочно',
            'unknown' => 'Требует уточнения',
        ];
    }

    private function transportOptions(): array
    {
        return [
            'ambulance' => 'Бригада СМП',
            'resuscitation' => 'Реанимационная бригада',
            'medical_transport' => 'Медицинский транспорт',
            'accompanied' => 'В сопровождении',
            'self' => 'Самостоятельно',
            'unknown' => 'Требует уточнения',
        ];
    }
}

