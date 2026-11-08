<?php

namespace App\Services;

use App\Models\BenefitPrescription;
use App\Models\Clinic;
use App\Models\Drug;
use App\Models\Patient;
use App\Support\HospitalPatientAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class BenefitPrescriptionService
{
    public function __construct(private readonly HospitalPatientAccess $access) {}

    private const BASE_FIELDS = ['drug_id', 'drug_form', 'dosage', 'quantity', 'standard'];
    private const BENEFIT_FIELDS = [
        'category_code', 'diagnosis_code', 'program', 'eligibility_basis',
        'eligibility_confirmed', 'nsu_medicines_retained', 'funding', 'payment',
        'payment_percent', 'series', 'number', 'snils', 'oms', 'medcard_number',
        'rp', 'dtd', 'signa', 'treatment_days', 'doses_per_day', 'units_per_dose',
        'validity_days', 'validity_basis', 'extended_validity_confirmed',
        'companion_form', 'companion_reference', 'companion_confirmed', 'clinical_confirmed',
    ];

    public function available(): bool
    {
        return (bool) config('benefit_prescriptions.enabled', false)
            && Schema::hasTable('benefit_prescriptions')
            && Schema::hasTable('benefit_prescription_events');
    }

    /** All patient visibility is delegated to the application's existing access service. */
    private function reader(Request $request, Patient $patient): void
    {
        abort_unless($request->user() && $this->access->canRead($request->user(), $patient), 403);
    }

    private function writable(Request $request, Patient $patient): bool
    {
        $user = $request->user();
        $doctor = $user?->doctor;
        return $user !== null && $doctor !== null && filled($doctor->clinic_id)
            && !$user->hasRole('admins') && $this->access->canEdit($user, $patient);
    }

    private function writer(Request $request, Patient $patient): void
    {
        $this->reader($request, $patient);
        abort_unless($this->writable($request, $patient), 403,
            'Для оформления необходим доступ врача к изменению карты и привязка к клинике.');
    }

    private function ready(): void
    {
        abort_unless($this->available(), 503, 'Модуль льготных рецептов пока не подготовлен к работе.');
    }

    private function scoped(Request $request, Patient $patient): Builder
    {
        $this->reader($request, $patient);
        $query = BenefitPrescription::query()->where('patient_id', $patient->getKey());
        $user = $request->user();
        if (!$this->access->isDebugAdmin($user)) {
            $clinicId = $user->doctor?->clinic_id;
            $clinicId ? $query->where('clinic_id', $clinicId) : $query->whereRaw('1 = 0');
        }
        return $query;
    }

    private function profile(Request $request): array
    {
        $clinicId = $request->user()?->doctor?->clinic_id;
        $clinic = $clinicId ? Clinic::query()->find($clinicId) : null;

        return $clinic !== null
            ? ClinicBenefitPrescriptionSettings::profile($clinic)
            : [];
    }

    public function cardContext(Request $request, Patient $patient, array $disability): array
    {
        $this->reader($request, $patient);
        $available = $this->available()
            && Route::has('doctors.prescriptions.benefit.store')
            && Route::has('doctors.prescriptions.benefit.print');
        $canWrite = $available && $this->writable($request, $patient);
        $profile = $this->profile($request);
        $clinicId = $request->user()?->doctor?->clinic_id;
        $clinicName = $profile['name'] ?? ($clinicId ? Clinic::query()->find($clinicId)?->name : null);
        // clinic-benefit-requisites:card-context-setup:v1:begin
        $setupErrors = BenefitPrescriptionRules::setupErrors($profile);

        if (!$available) {
            $setupErrors = ['module' => 'Модуль льготных рецептов пока не подготовлен к работе.'] + $setupErrors;
        }

        if (!$clinicId) {
            $setupErrors = ['clinic' => 'В карточке врача не указана клиника.'] + $setupErrors;
        }

        $setupMessage = implode(' ', array_values($setupErrors));
        // clinic-benefit-requisites:card-context-setup:v1:end
        $records = [];
        $pagination = null;
        if ($available) {
            $page = $this->scoped($request, $patient)->orderByDesc('created_at')->orderByDesc('id')
                ->paginate(10, ['*'], 'benefit_page')->withQueryString();
            $records = $page->getCollection()->map(fn ($record) => $this->row($request, $patient, $record, $canWrite))->all();
            $pagination = ['total' => $page->total(), 'from' => $page->firstItem(), 'to' => $page->lastItem(),
                'previous_url' => $page->previousPageUrl(), 'next_url' => $page->nextPageUrl()];
        }
        return [
            'available' => $available, 'can_write' => $canWrite,
            // clinic-benefit-requisites:card-context-result:v1:begin
            'can_issue' => $canWrite && $setupErrors === [],
            'setup_errors' => array_values($setupErrors), 'setup_message' => $setupMessage,
            // clinic-benefit-requisites:card-context-result:v1:end
            'store_url' => $available ? route('doctors.prescriptions.benefit.store', $patient) : '',
            'drug_url' => $available ? route('doctors.prescriptions.benefit.drug', $patient) : '',
            'records' => $records, 'pagination' => $pagination,
            'defaults' => [
                'diagnosis_code' => (string) (data_get($patient, 'prescription_diagnosis_code') ?: $patient->diagnose?->code),
                'snils' => (string) $patient->snils, 'oms' => (string) $patient->oms,
                'medcard_number' => (string) $patient->medcard_number,
                'clinic_name' => (string) $clinicName, 'series' => (string) ($profile['series'] ?? ''),
                'okato_prefix' => (string) ($profile['okato_prefix'] ?? ''),
                'disability_label' => ($disability['status'] ?? '') === 'present'
                    ? ($disability['group_label'] ?? 'Группа не указана') . (($disability['expired'] ?? false) ? ' — срок истёк' : '')
                    : 'Актуальные сведения об инвалидности не указаны',
                'disability_verified' => (bool) ($disability['verified'] ?? false),
                'disability_group' => $disability['group'] ?? null,
                'patient_name' => $this->fullName($patient),
                'patient_birth_date' => $this->birthDate($patient)?->format('d.m.Y') ?? '',
                'request_key' => (string) Str::uuid(),
            ],
        ];
    }

    private function row(Request $request, Patient $patient, BenefitPrescription $record, bool $canWrite): array
    {
        $own = $canWrite && (string) $record->created_by_user_id === (string) $request->user()->getKey()
            && (string) $record->doctor_id === (string) $request->user()->doctor?->getKey()
            && (string) $record->clinic_id === (string) $request->user()->doctor?->clinic_id;
        $params = ['patient' => $patient, 'benefit' => $record->getKey()];
        return [
            'id' => $record->getKey(), 'version' => $record->version, 'status' => $record->status,
            'status_label' => ['draft' => 'Черновик', 'issued' => 'Подготовлен к печати', 'void' => 'Аннулирован'][$record->status] ?? $record->status,
            'medicine_label' => data_get($record->snapshot, 'recipe.medicine_name') ?: 'Препарат не указан',
            'created_label' => $record->created_at?->format('d.m.Y H:i'),
            'issued_label' => $record->issued_at?->format('d.m.Y'),
            'number_label' => $record->series ? $record->series . ' № ' . $record->number : 'Номер не присвоен',
            'doctor_name' => data_get($record->snapshot, 'doctor.name', ''),
            'print_url' => $record->status === 'issued' ? route('doctors.prescriptions.benefit.print', $params) : null,
            'edit_url' => $own && $record->status === 'draft' ? route('doctors.prescriptions.benefit.edit', $params) : null,
            'void_url' => $own && $record->status === 'issued' ? route('doctors.prescriptions.benefit.void', $params) : null,
        ];
    }

    public function drug(Request $request, Patient $patient): array
    {
        $this->ready();
        $this->writer($request, $patient);
        $data = Validator::make($request->only('drug_id'), ['drug_id' => ['required', 'integer', 'min:1']])->validate();
        $drug = Drug::query()->findOrFail($data['drug_id']);
        return $this->drugData($drug);
    }

    private function drugData(?Drug $drug): ?array
    {
        return $drug ? ['id' => $drug->getKey(), 'name' => (string) $drug->name,
            'generic_name' => (string) ($drug->latin_name ?: $drug->name), 'strict' => (bool) $drug->strict] : null;
    }

    public function edit(Request $request, Patient $patient, string $id): array
    {
        $this->ready();
        $this->writer($request, $patient);
        $record = $this->scoped($request, $patient)->whereKey($id)->firstOrFail();
        $this->own($request, $record);
        abort_unless($record->status === 'draft', 409, 'Этот рецепт уже завершён. Его нельзя редактировать.');
        $base = $record->payload['base'] ?? [];
        $benefit = $record->payload['benefit'] ?? [];
        // A reopened draft always needs fresh clinical/eligibility confirmations.
        foreach (['eligibility_confirmed', 'nsu_medicines_retained', 'clinical_confirmed',
            'extended_validity_confirmed', 'companion_confirmed'] as $field) {
            $benefit[$field] = false;
        }
        return [
            'base' => $base,
            'benefit' => array_merge($benefit, ['id' => $record->getKey(), 'version' => $record->version,
                'request_key' => $record->request_key, 'use_saved_clinical' => true]),
            'drug' => !empty($base['drug_id']) ? $this->drugData(Drug::query()->find($base['drug_id'])) : null,
        ];
    }

    private function own(Request $request, BenefitPrescription $record): void
    {
        abort_unless((string) $record->created_by_user_id === (string) $request->user()->getKey()
            && (string) $record->clinic_id === (string) $request->user()->doctor?->clinic_id
            && (string) $record->doctor_id === (string) $request->user()->doctor?->getKey(), 403);
    }

    private function fullName($person): string
    {
        return trim(implode(' ', array_filter([(string) $person->surname, (string) $person->name, (string) $person->patronym],
            static fn ($part) => trim($part) !== '')));
    }

    private function shortName($person): string
    {
        $parts = [trim((string) $person->surname)];
        foreach (['name', 'patronym'] as $field) {
            if (filled($person->{$field})) {
                $parts[] = mb_substr(trim((string) $person->{$field}), 0, 1, 'UTF-8') . '.';
            }
        }
        return trim(implode(' ', $parts));
    }

    private function birthDate(Patient $patient): ?Carbon
    {
        $raw = $patient->getRawOriginal('birth_at') ?: $patient->birth_at;
        try {
            $date = $raw ? Carbon::parse($raw)->startOfDay() : null;
            return $date && !$date->isFuture() ? $date : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function disabilityContext(Patient $patient): array
    {
        $record = Schema::hasTable('patient_disability_records')
            ? DB::table('patient_disability_records')->where('patient_id', $patient->getKey())->first() : null;
        $current = $record && $record->status === 'present'
            && filled($record->established_at) && (string) $record->established_at <= today()->toDateString()
            && (in_array($record->indefinite, [true, 1, '1', 't', 'true'], true)
                || filled($record->valid_until) && (string) $record->valid_until >= today()->toDateString());
        return ['disability_group' => $record?->disability_group,
            'disability_current' => (bool) $current];
    }

    /** Save or finalize one draft atomically. No queue hand-off, and no guessed queue job ID. */
    public function save(Request $request, Patient $patient): array
    {
        $this->ready();
        $this->writer($request, $patient);
        $header = Validator::make($request->all(), [
            'benefit' => ['required', 'array'],
            'benefit.id' => ['nullable', 'uuid'],
            'benefit.version' => ['nullable', 'required_with:benefit.id', 'integer', 'min:1'],
            'benefit.request_key' => ['required', 'uuid'],
            'benefit.action' => ['required', Rule::in(['draft', 'issue'])],
            'benefit.use_saved_clinical' => ['nullable', 'boolean'],
        ])->validate()['benefit'];
        $issue = $header['action'] === 'issue';
        $doctor = $request->user()->doctor;
        // Hash only the request's intentional fields, never CSRF or unrelated legacy form inputs.
        $submitted = ['base' => $request->only(self::BASE_FIELDS),
            'benefit' => Arr::only($request->input('benefit', []), array_merge(self::BENEFIT_FIELDS,
                ['action', 'id', 'version', 'use_saved_clinical']))];
        $fingerprint = hash('sha256', json_encode($submitted, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        try {
            $record = DB::transaction(function () use ($request, $patient, $doctor, $header, $issue, $fingerprint): BenefitPrescription {
                $record = null;
                if (!empty($header['id'])) {
                    $record = $this->scoped($request, $patient)->whereKey($header['id'])->lockForUpdate()->firstOrFail();
                    $this->own($request, $record);
                    abort_unless(hash_equals($record->request_key, $header['request_key']), 409, 'Черновик не соответствует текущей форме. Откройте его заново.');
                    if (hash_equals($record->last_request_hash, $fingerprint)) {
                        return $record; // Retry after a lost response does not issue a new number/version.
                    }
                    abort_unless($record->status === 'draft', 409, 'Рецепт уже завершён. Изменять выданный номер и данные нельзя.');
                    abort_unless($record->version === (int) $header['version'], 409, 'Черновик изменён в другом окне. Откройте его заново.');
                } else {
                    $record = $this->scoped($request, $patient)
                        ->where('clinic_id', $doctor->clinic_id)
                        ->where('created_by_user_id', $request->user()->getKey())
                        ->where('request_key', $header['request_key'])->lockForUpdate()->first();
                    if ($record) {
                        abort_unless(hash_equals($record->last_request_hash, $fingerprint), 409,
                            'Эта форма уже сохранена. Откройте её черновик или начните новый рецепт.');
                        return $record;
                    }
                }
                $base = $request->only(self::BASE_FIELDS);
                if (BenefitPrescriptionRules::confirmed($header['use_saved_clinical'] ?? false)) {
                    abort_unless($record !== null, 422, 'Для использования сохранённого назначения откройте черновик.');
                    $base = $record->payload['base'] ?? [];
                }
                $benefit = Arr::only($request->input('benefit', []), self::BENEFIT_FIELDS);
                [$base, $benefit] = $this->validateData($base, $benefit, $issue);
                $drug = !empty($base['drug_id']) ? Drug::query()->find($base['drug_id']) : null;
                if (!empty($base['drug_id']) && !$drug) {
                    throw ValidationException::withMessages(['drug_id' => 'Препарат больше не найден в справочнике. Выберите актуальную запись.']);
                }
                $profile = $this->profile($request);
                $birthDate = $this->birthDate($patient);
                $disability = $this->disabilityContext($patient);
                if ($issue) {
                    $errors = BenefitPrescriptionRules::issueErrors($benefit, array_merge($disability, [
                        'profile' => $profile, 'drug_strict' => (bool) $drug?->strict,
                        'patient_birth_date' => $birthDate?->toDateString(),
                        'patient_name' => filled($patient->surname) && filled($patient->name),
                        'doctor_name' => filled($doctor->surname) && filled($doctor->name),
                        'is_minor' => $birthDate !== null && $birthDate->age < 18,
                    ]));
                    if ($errors) {
                        throw ValidationException::withMessages($errors);
                    }
                }
                $isNew = $record === null;
                if ($isNew) {
                    $record = new BenefitPrescription();
                    $record->id = (string) Str::uuid();
                    $record->patient_id = $patient->getKey();
                    $record->clinic_id = $doctor->clinic_id;
                    $record->doctor_id = $doctor->getKey();
                    $record->created_by_user_id = $request->user()->getKey();
                    $record->request_key = $header['request_key'];
                    $record->version = 1;
                } else {
                    $record->version++;
                }
                $record->last_request_hash = $fingerprint;
                $record->status = $issue ? 'issued' : 'draft';
                $record->issued_at = $issue ? now() : null;
                $record->series = $issue ? $benefit['series'] : null;
                $record->number = $issue ? $benefit['number'] : null;
                $record->payload = ['base' => $base, 'benefit' => $benefit];
                $record->snapshot = $this->snapshot($request, $patient, $drug, $base, $benefit, $profile, $issue,
                    $record->issued_at?->format('d.m.Y') ?? '');
                $record->save();
                $this->event($record, $request, $issue ? 'issued' : ($isNew ? 'draft_created' : 'draft_updated'));
                return $record;
            }, 3);
        } catch (QueryException $exception) {
            if (!in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                throw $exception;
            }
            // A concurrent identical create may have won the idempotency unique constraint.
            $record = $this->scoped($request, $patient)->where('clinic_id', $doctor->clinic_id)
                ->where('created_by_user_id', $request->user()->getKey())
                ->where('request_key', $header['request_key'])->first();
            if ($record && hash_equals($record->last_request_hash, $fingerprint)) {
                return $this->saveResponse($patient, $record);
            }
            throw ValidationException::withMessages([
                'benefit.number' => 'Эта серия и номер уже использованы в клинике либо форма уже сохранена. Проверьте реквизиты и список рецептов.',
            ]);
        }
        return $this->saveResponse($patient, $record);
    }

    private function validateData(array $base, array $benefit, bool $issue): array
    {
        foreach (['series', 'number', 'diagnosis_code'] as $field) {
            if (isset($benefit[$field]) && is_string($benefit[$field])) {
                $benefit[$field] = BenefitPrescriptionRules::normalizeNumber($benefit[$field]);
            }
        }
        if (isset($benefit['snils']) && is_string($benefit['snils'])) {
            $benefit['snils'] = preg_replace('/[\s-]+/u', '', $benefit['snils']);
        }
        $required = $issue ? 'required' : 'nullable';
        $rules = [
            'drug_id' => [$required, 'integer', 'min:1'],
            'drug_form' => [$required, 'string', 'max:100'],
            'dosage' => [$required, 'string', 'max:100'],
            'quantity' => [$required, 'numeric', 'gt:0', 'max:100000'],
            'standard' => [$required, 'integer', 'min:1', 'max:10000'],
            'benefit.category_code' => [$required, 'regex:/^\d{3}$/D'],
            'benefit.diagnosis_code' => [$required, 'regex:/^[A-Z][0-9]{2}(?:\.[0-9A-Z]{1,4})?$/D'],
            'benefit.program' => [$required, Rule::in(['federal_nsu', 'regional', 'municipal', 'other'])],
            'benefit.eligibility_basis' => [$required, 'string', 'max:1000'],
            'benefit.funding' => [$required, Rule::in([1, 2, 3])],
            'benefit.payment' => [$required, Rule::in([1, 2, 3])],
            'benefit.payment_percent' => ['nullable', 'numeric', 'between:0,100'],
            'benefit.series' => [$required, 'string', 'max:20', 'regex:/^\d{2}[0-9A-ZА-ЯЁ\/-]*$/uD'],
            'benefit.number' => [$required, 'string', 'max:20', 'regex:/^[0-9A-ZА-ЯЁ\/-]+$/uD'],
            'benefit.snils' => ['nullable', 'string', 'regex:/^\d{11}$/D'],
            'benefit.oms' => [$required, 'string', 'max:32'],
            'benefit.medcard_number' => [$required, 'string', 'max:40'],
            'benefit.rp' => [$required, 'string', 'max:240'],
            'benefit.dtd' => [$required, 'string', 'max:180'],
            'benefit.signa' => [$required, 'string', 'max:600'],
            'benefit.treatment_days' => [$required, 'integer', 'between:1,180'],
            'benefit.doses_per_day' => [$required, 'string', 'max:32'],
            'benefit.units_per_dose' => [$required, 'string', 'max:32'],
            'benefit.validity_days' => [$required, Rule::in([15, 30, 90])],
            'benefit.validity_basis' => [$required, Rule::in(['standard', 'disability_i', 'disabled_child', 'pension_age', 'chronic_long', 'controlled'])],
            'benefit.companion_form' => [$required, Rule::in(['none', '148_88', '107_np', '107_1'])],
            'benefit.companion_reference' => ['nullable', 'string', 'max:300'],
        ];
        foreach (['eligibility_confirmed', 'clinical_confirmed'] as $field) {
            $rules['benefit.' . $field] = $issue ? ['required', 'accepted'] : ['nullable', 'boolean'];
        }
        foreach (['nsu_medicines_retained', 'extended_validity_confirmed', 'companion_confirmed'] as $field) {
            $rules['benefit.' . $field] = ['nullable', 'boolean'];
        }
        $attributes = [
            'drug_id' => 'препарат', 'drug_form' => 'форма препарата', 'dosage' => 'дозировка',
            'quantity' => 'количество лекарственной формы', 'standard' => 'количество стандартов',
            'benefit.category_code' => 'код категории льготника', 'benefit.diagnosis_code' => 'код диагноза МКБ',
            'benefit.program' => 'программа льготного обеспечения', 'benefit.eligibility_basis' => 'основание права на льготу',
            'benefit.funding' => 'источник финансирования', 'benefit.payment' => 'вид оплаты',
            'benefit.series' => 'серия', 'benefit.number' => 'номер', 'benefit.snils' => 'СНИЛС',
            'benefit.oms' => 'полис ОМС', 'benefit.medcard_number' => 'номер амбулаторной карты',
            'benefit.rp' => 'Rp', 'benefit.dtd' => 'D.t.d.', 'benefit.signa' => 'способ применения',
            'benefit.treatment_days' => 'длительность лечения', 'benefit.doses_per_day' => 'число приёмов в день',
            'benefit.units_per_dose' => 'количество на один приём', 'benefit.validity_days' => 'срок действия рецепта',
            'benefit.validity_basis' => 'основание срока действия', 'benefit.companion_form' => 'дополнительная форма рецепта',
            'benefit.eligibility_confirmed' => 'подтверждение права на льготу',
            'benefit.clinical_confirmed' => 'проверка назначения врачом',
        ];
        $validated = Validator::make(array_merge($base, ['benefit' => $benefit]), $rules, [
            'required' => 'Заполните поле «:attribute».', 'accepted' => 'Необходимо: :attribute.',
            'regex' => 'Проверьте формат поля «:attribute».', 'in' => 'Выберите допустимое значение поля «:attribute».',
            'max' => 'Слишком большое значение поля «:attribute» (максимум :max).',
            'integer' => 'Поле «:attribute» должно содержать целое число.',
            'numeric' => 'Поле «:attribute» должно содержать число.',
            'string' => 'Проверьте текст поля «:attribute».',
        ], $attributes)->validate();
        return [Arr::only($validated, self::BASE_FIELDS), Arr::only($validated['benefit'] ?? [], self::BENEFIT_FIELDS)];
    }

    private function snapshot(Request $request, Patient $patient, ?Drug $drug, array $base, array $benefit, array $profile, bool $issued, string $issuedDate): array
    {
        $organization = Arr::only($profile, ['name', 'address', 'phone', 'ogrn', 'code', 'kind',
            'license_number', 'license_date', 'license_authority']);
        if (!empty($organization['license_date'])) {
            try {
                $organization['license_date'] = Carbon::parse($organization['license_date'])->format('d.m.Y');
            } catch (\Throwable) {
                if ($issued) {
                    throw ValidationException::withMessages(['benefit.setup' => 'Проверьте дату лицензии в реквизитах клиники.']);
                }
                $organization['license_date'] = '';
            }
        }
        $payment = (int) ($benefit['payment'] ?? 0);
        return [
            'organization' => array_merge(['name' => '', 'address' => '', 'phone' => '', 'ogrn' => '',
                'code' => '', 'kind' => 'organization', 'license_number' => '', 'license_date' => '', 'license_authority' => ''], $organization),
            'patient' => [
                'name' => $this->shortName($patient), 'full_name' => $this->fullName($patient),
                'birth_date' => $this->birthDate($patient)?->format('d.m.Y') ?? '',
                'snils' => (string) ($benefit['snils'] ?? ''), 'oms' => (string) ($benefit['oms'] ?? ''),
                'card_number' => (string) ($benefit['medcard_number'] ?? ''),
            ],
            'doctor' => ['name' => $this->shortName($request->user()->doctor)],
            'recipe' => [
                'series' => (string) ($benefit['series'] ?? ''), 'number' => (string) ($benefit['number'] ?? ''),
                'issued_date' => $issuedDate,
                'category_code' => (string) ($benefit['category_code'] ?? ''),
                'diagnosis_code' => (string) ($benefit['diagnosis_code'] ?? ''),
                'funding' => (int) ($benefit['funding'] ?? 0), 'payment' => $payment,
                'payment_percent' => $payment === 1 ? 0 : ($payment === 2 ? 50 : ($benefit['payment_percent'] ?? '')),
                'validity_days' => (int) ($benefit['validity_days'] ?? 30),
                'rp' => (string) ($benefit['rp'] ?? ''), 'dtd' => (string) ($benefit['dtd'] ?? ''),
                'signa' => (string) ($benefit['signa'] ?? ''), 'treatment_days' => $benefit['treatment_days'] ?? '',
                'doses_per_day' => (string) ($benefit['doses_per_day'] ?? ''), 'units_per_dose' => (string) ($benefit['units_per_dose'] ?? ''),
                'medicine_name' => (string) ($drug?->name ?? ''), 'dosage' => (string) ($base['dosage'] ?? ''),
                'companion_form' => (string) ($benefit['companion_form'] ?? 'none'),
            ],
            'evidence' => [
                'program' => $benefit['program'] ?? null, 'basis' => $benefit['eligibility_basis'] ?? null,
                'checked_by_user_id' => $issued ? $request->user()->getKey() : null,
                'checked_at' => $issued ? now()->toIso8601String() : null,
                'check_method' => 'manual_clinician_confirmation',
                'nsu_medicines_retained' => BenefitPrescriptionRules::confirmed($benefit['nsu_medicines_retained'] ?? null),
                'validity_basis' => $benefit['validity_basis'] ?? null,
                'companion_reference' => $benefit['companion_reference'] ?? null,
                'numbering_source' => $profile['numbering_source'] ?? null,
            ],
        ];
    }

    private function saveResponse(Patient $patient, BenefitPrescription $record): array
    {
        $params = ['patient' => $patient, 'benefit' => $record->getKey()];
        return [
            'message' => $record->status === 'issued'
                ? 'Рецепт сохранён. Проверьте бланк, распечатайте и заверьте подписью и печатями.'
                : ($record->status === 'void' ? 'Рецепт аннулирован.' : 'Черновик сохранён. Официальный номер ещё не занят.'),
            'record' => ['id' => $record->getKey(), 'version' => $record->version, 'status' => $record->status],
            'print_url' => $record->status === 'issued' ? route('doctors.prescriptions.benefit.print', $params) : null,
            'edit_url' => $record->status === 'draft' ? route('doctors.prescriptions.benefit.edit', $params) : null,
            'card_url' => route('doctors.patients.medical_card', $patient) . '?benefit_saved=1#pane-presc',
        ];
    }

    public function printable(Request $request, Patient $patient, string $id): array
    {
        $this->ready();
        $record = $this->scoped($request, $patient)->whereKey($id)->firstOrFail();
        abort_unless($record->status === 'issued', 409,
            $record->status === 'void' ? 'Рецепт аннулирован.' : 'Черновик нельзя печатать как выданный рецепт.');
        $this->access->auditRead($request->user(), $patient, 'benefit-prescription-print');
        $this->event($record, $request, 'print_opened');
        return $record->snapshot;
    }

    public function void(Request $request, Patient $patient, string $id): array
    {
        $this->ready();
        $this->writer($request, $patient);
        $data = Validator::make($request->only(['version', 'reason']), [
            'version' => ['required', 'integer', 'min:1'], 'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], ['reason.required' => 'Укажите причину аннулирования.', 'reason.min' => 'Опишите причину аннулирования.'])->validate();
        $record = DB::transaction(function () use ($request, $patient, $id, $data): BenefitPrescription {
            $record = $this->scoped($request, $patient)->whereKey($id)->lockForUpdate()->firstOrFail();
            $this->own($request, $record);
            abort_unless($record->status === 'issued' && $record->version === (int) $data['version'], 409,
                'Статус рецепта изменился. Обновите медицинскую карту.');
            $record->status = 'void';
            $record->version++;
            $record->voided_at = now();
            $record->voided_by_user_id = $request->user()->getKey();
            $record->void_reason = $data['reason'];
            $record->save();
            $this->event($record, $request, 'voided');
            return $record;
        });
        return $this->saveResponse($patient, $record);
    }

    private function event(BenefitPrescription $record, Request $request, string $action): void
    {
        DB::table('benefit_prescription_events')->insert([
            'benefit_prescription_id' => $record->getKey(), 'actor_user_id' => $request->user()->getKey(),
            'action' => $action, 'metadata' => json_encode(['version' => $record->version, 'status' => $record->status], JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }
}
