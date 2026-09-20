<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRegistryRecordRequest;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\RegistryRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ReceptionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isDebug = app()->hasDebugModeEnabled();

        $doctor = $user && method_exists($user, 'doctor')
            ? $user->doctor()->first()
            : null;

        $nurse = $user && method_exists($user, 'nurse')
            ? $user->nurse()->first()
            : null;

        $currentDoctorId = $doctor?->id
            ? (int) $doctor->id
            : null;

        $currentClinicId = $doctor?->clinic_id
            ? (int) $doctor->clinic_id
            : ($nurse?->clinic_id ? (int) $nurse->clinic_id : null);

        /*
         * В старой базе частная практика могла иметь clinic_id = 0.
         * В новой — clinic_id может быть NULL. Оба варианта считаем
         * частной практикой.
         */
        $isPrivateDoctor = $doctor !== null
            && ((int) ($doctor->clinic_id ?? 0) === 0);

        /*
         |--------------------------------------------------------------------------
         | Доступные врачи
         |--------------------------------------------------------------------------
         */
        $doctorsQuery = Doctor::query()
            ->orderBy('surname')
            ->orderBy('name')
            ->orderBy('patronym');

        if (!$isDebug) {
            if ($isPrivateDoctor && $currentDoctorId) {
                $doctorsQuery->whereKey($currentDoctorId);
            } elseif ($currentClinicId) {
                $doctorsQuery->where('clinic_id', $currentClinicId);
            } else {
                $doctorsQuery->whereRaw('1 = 0');
            }
        }

        $doctors = $doctorsQuery->get();

        /*
         |--------------------------------------------------------------------------
         | Текущие записи регистратуры
         |--------------------------------------------------------------------------
         */
        $receptionsQuery = RegistryRecord::query()
            ->with(['patient', 'doctor'])
            ->orderByDesc('for_datetime');

        if (!$isDebug) {
            $receptionsQuery->whereBetween('for_datetime', [
                Carbon::now()->startOfDay(),
                Carbon::now()->endOfDay(),
            ]);

            if ($isPrivateDoctor && $currentDoctorId) {
                $receptionsQuery->where('doctor_id', $currentDoctorId);
            } elseif ($currentClinicId) {
                $receptionsQuery->where('clinic_id', $currentClinicId);
            } else {
                $receptionsQuery->whereRaw('1 = 0');
            }
        }

        if ($request->filled('doctor_id')) {
            $receptionsQuery->where(
                'doctor_id',
                $request->integer('doctor_id')
            );
        }

        if (
            $request->filled('fullname')
            || $request->filled('birth_at')
            || $request->filled('address_residence')
        ) {
            $receptionsQuery->whereHas(
                'patient',
                function ($query) use ($request): void {
                    $this->applyPatientSearchFilters($query, $request);
                }
            );
        }

        $receptions = $receptionsQuery
            ->paginate(30, ['*'], 'receptions_page')
            ->withQueryString();

        /*
         |--------------------------------------------------------------------------
         | Поиск непосредственно по patients
         |--------------------------------------------------------------------------
         |
         | Раньше поиск шёл только через registry_records. Поэтому пациент,
         | существующий в patients, но ещё не имеющий registry_records,
         | не мог быть найден.
         */
        $patientsQuery = Patient::query()
            ->with(['doctor', 'diagnose']);

        if (!$isDebug) {
            if ($isPrivateDoctor && $currentDoctorId) {
                $patientsQuery->where(
                    function ($query) use ($currentDoctorId): void {
                        $query
                            ->where('doctor_id', $currentDoctorId)
                            ->orWhereIn(
                                'id',
                                RegistryRecord::query()
                                    ->select('patient_id')
                                    ->where('doctor_id', $currentDoctorId)
                            );
                    }
                );
            } elseif ($currentClinicId) {
                $patientsQuery->where(
                    function ($query) use ($currentClinicId): void {
                        $query
                            ->whereIn(
                                'doctor_id',
                                Doctor::query()
                                    ->select('id')
                                    ->where('clinic_id', $currentClinicId)
                            )
                            ->orWhereIn(
                                'id',
                                RegistryRecord::query()
                                    ->select('patient_id')
                                    ->where('clinic_id', $currentClinicId)
                            );
                    }
                );
            } else {
                $patientsQuery->whereRaw('1 = 0');
            }
        }

        $this->applyPatientSearchFilters($patientsQuery, $request);

        if ($request->filled('doctor_id')) {
            $selectedDoctorId = $request->integer('doctor_id');

            $patientsQuery->where(
                function ($query) use ($selectedDoctorId): void {
                    $query
                        ->where('doctor_id', $selectedDoctorId)
                        ->orWhereIn(
                            'id',
                            RegistryRecord::query()
                                ->select('patient_id')
                                ->where('doctor_id', $selectedDoctorId)
                        );
                }
            );
        }

        $patients = $patientsQuery
            ->orderBy('surname')
            ->orderBy('name')
            ->orderBy('patronym')
            ->paginate(30, ['*'], 'patients_page')
            ->withQueryString();

        /*
         |--------------------------------------------------------------------------
         | Данные владельца регистратуры
         |--------------------------------------------------------------------------
         */
        if ($isDebug) {
            $hostClinic = Clinic::query()
                ->orderBy('id')
                ->first();

            $name = 'Администратор';
            $surname = 'Дебаггеров';
            $patronym = 'Тестович';
            $profession = 'дебаггер';
        } else {
            $hostClinic = $currentClinicId
                ? Clinic::query()->find($currentClinicId)
                : null;

            $name = $doctor?->name ?? $nurse?->name ?? '';
            $surname = $doctor?->surname ?? $nurse?->surname ?? '';
            $patronym = $doctor?->patronym ?? $nurse?->patronym ?? '';
            $profession = $nurse?->profession
                ?? $doctor?->profession
                ?? 'врач';
        }

        $futureReceptionsQuery = RegistryRecord::query()
            ->where(
                'for_datetime',
                '>',
                Carbon::now()->addDay()->startOfDay()
            );

        if (!$isDebug) {
            if ($isPrivateDoctor && $currentDoctorId) {
                $futureReceptionsQuery->where(
                    'doctor_id',
                    $currentDoctorId
                );
            } elseif ($currentClinicId) {
                $futureReceptionsQuery->where(
                    'clinic_id',
                    $currentClinicId
                );
            } else {
                $futureReceptionsQuery->whereRaw('1 = 0');
            }
        }

        $futureReceptions = $futureReceptionsQuery->count();

        return view('doctors.reception.table', [
            'receptions' => $receptions,
            'patients' => $patients,
            'hostClinic' => $hostClinic,
            'name' => $name,
            'surname' => $surname,
            'patronym' => $patronym,
            'profession' => $profession,
            'doctors' => $doctors,
            'futureReceptions' => $futureReceptions,
            'isDebug' => $isDebug,
            'isPrivateDoctor' => $isPrivateDoctor,
        ]);
    }

    /**
     * Общие фильтры поиска пациента.
     */
    private function applyPatientSearchFilters(
        $query,
        Request $request
    ): void {
        if ($request->filled('fullname')) {
            $fullName = trim((string) $request->get('fullname'));

            $searchParts = preg_split(
                '/[\s,]+/u',
                $fullName,
                -1,
                PREG_SPLIT_NO_EMPTY
            ) ?: [];

            foreach ($searchParts as $part) {
                $searchValue = '%' . $part . '%';

                $query->where(
                    function ($patientQuery) use (
                        $searchValue,
                        $part
                    ): void {
                        $patientQuery
                            ->where('surname', 'ilike', $searchValue)
                            ->orWhere('name', 'ilike', $searchValue)
                            ->orWhere('patronym', 'ilike', $searchValue)
                            ->orWhereRaw(
                                "CONCAT(LEFT(name, 1), '.', LEFT(patronym, 1), '.') ILIKE ?",
                                ['%' . $part . '%']
                            );
                    }
                );
            }
        }

        if ($request->filled('birth_at')) {
            $query->whereDate(
                'birth_at',
                Carbon::parse($request->get('birth_at'))
            );
        }

        if ($request->filled('address_residence')) {
            $address = '%' . trim(
                (string) $request->get('address_residence')
            ) . '%';

            $query->where(
                function ($addressQuery) use ($address): void {
                    $addressQuery
                        ->where('address_registration', 'ilike', $address)
                        ->orWhere('address_residence', 'ilike', $address);
                }
            );
        }
    }

    public function create(): View
    {
        if (!app()->hasDebugModeEnabled()) {
            $patients = auth()
                ->user()
                ->doctor
                ->clinic->pluck('name', 'id') ??
                auth()
                    ->user()
                    ->nurse
                    ->clinic->pluck('name', 'id');
            $doctors = Doctor::where('clinic_id', auth()
                ->user()
                ->doctor
                ->clinic
                ->id ?? auth()->user()->nurse->clinic->id);
        } else {
            $patients = Patient::orderBy('surname')->get();
            $doctors = Doctor::orderBy('surname')->get();
        }

        return view('doctors.reception.create', compact('patients', 'doctors'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'patient_id' => 'nullable|integer|exists:patients,id',
                'surname' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'patronym' => 'nullable|string|max:255',
                'birth_at' => 'required|date|before_or_equal:today',
                'gender' => 'required|in:M,F',
                'appointment_datetime' => 'required|date|after_or_equal:now',
                'address_registration' => 'required|string',
                'address_residence' => 'required|string',
                'birth_place' => 'required|string',
                'snils' => 'required|string|max:14',
                'oms' => 'required|string|max:16',
                'doctor_id' => 'required|exists:doctors,id',

                'patient_document_type' => 'nullable|in:passport_rf,birth_certificate,foreign_passport,residence_permit,temporary_identity,foreign_identity,other',
                'patient_document_name' => 'nullable|string|max:255',
                'patient_document_series' => 'nullable|string|max:50',
                'patient_document_number' => 'nullable|string|max:100',
                'patient_document_department_code' => 'nullable|string|max:20',
                'patient_document_issued_by' => 'nullable|string',
                'patient_document_issued_at' => 'nullable|date|before_or_equal:today',

                'representative_type' => 'nullable|in:mother,father,adoptive_parent,guardian,trustee,authorized,other',
                'representative_surname' => 'nullable|string|max:255',
                'representative_name' => 'nullable|string|max:255',
                'representative_patronym' => 'nullable|string|max:255',
                'representative_birth_at' => 'nullable|date|before_or_equal:today',
                'representative_snils' => 'nullable|string|max:14',
                'representative_phone' => 'nullable|string|max:50',
                'representative_email' => 'nullable|email|max:255',

                'representative_identity_document_type' => 'nullable|in:passport_rf,foreign_passport,residence_permit,temporary_identity,foreign_identity,other',
                'representative_identity_document_name' => 'nullable|string|max:255',
                'representative_identity_series' => 'nullable|string|max:50',
                'representative_identity_number' => 'nullable|string|max:100',
                'representative_identity_department_code' => 'nullable|string|max:20',
                'representative_identity_issued_by' => 'nullable|string',
                'representative_identity_issued_at' => 'nullable|date|before_or_equal:today',

                'representative_authority_document_type' => 'nullable|in:guardianship_order,power_of_attorney,other',
                'representative_authority_document_name' => 'nullable|string|max:255',
                'representative_authority_series' => 'nullable|string|max:50',
                'representative_authority_number' => 'nullable|string|max:100',
                'representative_authority_issued_by' => 'nullable|string',
                'representative_authority_issued_at' => 'nullable|date|before_or_equal:today',
            ],
            [
                'required' => 'Поле ":attribute" обязательно для заполнения.',
                'string' => 'Поле ":attribute" должно быть строкой.',
                'integer' => 'Поле ":attribute" должно быть числом.',
                'max' => 'Поле ":attribute" не должно превышать :max символов.',
                'date' => 'Поле ":attribute" должно быть датой.',
                'email' => 'Поле ":attribute" должно содержать корректный e-mail.',
                'in' => 'Для поля ":attribute" выбрано недопустимое значение.',
                'before_or_equal' => 'Поле ":attribute" не может содержать будущую дату.',
                'after_or_equal' => 'Поле ":attribute" должно быть датой не раньше текущего момента.',
                'exists' => 'Значение поля ":attribute" не зарегистрировано в базе данных.',
            ],
            [
                'patient_id' => 'Пациент',
                'surname' => 'Фамилия',
                'name' => 'Имя',
                'patronym' => 'Отчество',
                'birth_at' => 'Дата рождения',
                'gender' => 'Пол',
                'appointment_datetime' => 'Дата приёма',
                'address_registration' => 'Адрес регистрации',
                'address_residence' => 'Адрес проживания',
                'birth_place' => 'Место рождения',
                'snils' => 'СНИЛС',
                'oms' => 'ОМС',
                'doctor_id' => 'Лечащий врач',
                'patient_document_type' => 'Тип документа пациента',
                'patient_document_name' => 'Наименование документа пациента',
                'patient_document_number' => 'Номер документа пациента',
                'patient_document_department_code' => 'Код подразделения документа пациента',
                'patient_document_issued_by' => 'Кем выдан документ пациента',
                'patient_document_issued_at' => 'Дата выдачи документа пациента',
                'representative_type' => 'Тип представителя',
                'representative_surname' => 'Фамилия представителя',
                'representative_name' => 'Имя представителя',
                'representative_birth_at' => 'Дата рождения представителя',
                'representative_identity_document_type' => 'Тип документа представителя',
                'representative_identity_document_name' => 'Наименование документа представителя',
                'representative_identity_number' => 'Номер документа представителя',
                'representative_identity_department_code' => 'Код подразделения документа представителя',
                'representative_identity_issued_by' => 'Кем выдан документ представителя',
                'representative_identity_issued_at' => 'Дата выдачи документа представителя',
                'representative_authority_document_type' => 'Тип документа о полномочиях',
                'representative_authority_document_name' => 'Наименование документа о полномочиях',
                'representative_authority_number' => 'Номер документа о полномочиях',
                'representative_authority_issued_by' => 'Кем выдан документ о полномочиях',
                'representative_authority_issued_at' => 'Дата выдачи документа о полномочиях',
            ]
        );

        $validator->after(function ($validator) use ($request): void {
            /*
             * При выборе существующего пациента документы уже находятся
             * в patient_identity_documents / patient_representatives.
             * Повторно заставлять регистратуру вводить их не нужно.
             */
            if ($request->filled('patient_id')) {
                return;
            }

            foreach ([
                'patient_document_type' => 'Тип документа пациента',
                'patient_document_number' => 'Номер документа пациента',
                'patient_document_issued_by' => 'Кем выдан документ пациента',
                'patient_document_issued_at' => 'Дата выдачи документа пациента',
            ] as $field => $label) {
                if (!$request->filled($field)) {
                    $validator->errors()->add($field, "Поле \"{$label}\" обязательно для заполнения.");
                }
            }

            if (
                $request->input('patient_document_type') === 'passport_rf'
                && !$request->filled('patient_document_department_code')
            ) {
                $validator->errors()->add(
                    'patient_document_department_code',
                    'Для паспорта гражданина РФ необходимо указать код подразделения.'
                );
            }

            if (
                $request->input('patient_document_type') === 'other'
                && !$request->filled('patient_document_name')
            ) {
                $validator->errors()->add(
                    'patient_document_name',
                    'Для другого типа документа укажите его наименование.'
                );
            }

            if (!$request->filled('birth_at')) {
                return;
            }

            try {
                $age = Carbon::parse($request->input('birth_at'))->age;
            } catch (\Throwable) {
                return;
            }

            if ($age >= 18) {
                return;
            }

            /*
             * В психиатрическом сценарии законный представитель обязателен
             * для пациента младше 15 лет. В 15–17 лет блок допускается
             * оставить пустым, но если его начали заполнять — данные должны
             * быть согласованными и достаточными.
             */
            $representativeRequiredByAge = $age < 15;
            $representativeStarted = collect([
                'representative_surname',
                'representative_name',
                'representative_birth_at',
                'representative_phone',
                'representative_identity_number',
            ])->contains(fn (string $field): bool => $request->filled($field));

            if (!$representativeRequiredByAge && !$representativeStarted) {
                return;
            }

            foreach ([
                'representative_type' => 'Тип представителя',
                'representative_surname' => 'Фамилия представителя',
                'representative_name' => 'Имя представителя',
                'representative_birth_at' => 'Дата рождения представителя',
                'representative_phone' => 'Телефон представителя',
                'representative_identity_document_type' => 'Тип документа представителя',
                'representative_identity_number' => 'Номер документа представителя',
                'representative_identity_issued_by' => 'Кем выдан документ представителя',
                'representative_identity_issued_at' => 'Дата выдачи документа представителя',
            ] as $field => $label) {
                if (!$request->filled($field)) {
                    $message = $representativeRequiredByAge
                        ? "Поле \"{$label}\" обязательно для пациента младше 15 лет."
                        : "Если представитель указан, заполните поле \"{$label}\".";

                    $validator->errors()->add($field, $message);
                }
            }

            if (
                $request->input('representative_identity_document_type') === 'passport_rf'
                && !$request->filled('representative_identity_department_code')
            ) {
                $validator->errors()->add(
                    'representative_identity_department_code',
                    'Для паспорта представителя необходимо указать код подразделения.'
                );
            }

            if (
                $request->input('representative_identity_document_type') === 'other'
                && !$request->filled('representative_identity_document_name')
            ) {
                $validator->errors()->add(
                    'representative_identity_document_name',
                    'Для другого типа документа представителя укажите его наименование.'
                );
            }

            $needsAuthorityDocument = in_array(
                $request->input('representative_type'),
                ['guardian', 'trustee', 'authorized', 'other'],
                true
            );

            if (!$needsAuthorityDocument) {
                return;
            }

            foreach ([
                'representative_authority_document_type' => 'Тип документа о полномочиях',
                'representative_authority_number' => 'Номер документа о полномочиях',
                'representative_authority_issued_by' => 'Кем выдан документ о полномочиях',
                'representative_authority_issued_at' => 'Дата выдачи документа о полномочиях',
            ] as $field => $label) {
                if (!$request->filled($field)) {
                    $validator->errors()->add($field, "Поле \"{$label}\" обязательно для выбранного типа представителя.");
                }
            }

            if (
                $request->input('representative_authority_document_type') === 'other'
                && !$request->filled('representative_authority_document_name')
            ) {
                $validator->errors()->add(
                    'representative_authority_document_name',
                    'Укажите наименование документа, подтверждающего полномочия.'
                );
            }
        });

        $validatedData = $validator->validate();
        $isDebug = app()->hasDebugModeEnabled();
        $selectedDoctor = Doctor::findOrFail($validatedData['doctor_id']);

        if ($isDebug) {
            $doctorId = $selectedDoctor->id;
            $clinicId = $selectedDoctor->clinic_id ?? null;
        } else {
            $user = $request->user();
            $currentDoctor = $user && method_exists($user, 'doctor')
                ? $user->doctor()->first()
                : null;
            $currentNurse = $user && method_exists($user, 'nurse')
                ? $user->nurse()->first()
                : null;

            $clinicId = $currentDoctor?->clinic_id ?? $currentNurse?->clinic_id;

            if ($clinicId !== null && (int) $selectedDoctor->clinic_id !== (int) $clinicId) {
                abort(403, 'Нельзя записать пациента к врачу другой клиники.');
            }

            if ($clinicId === null && $currentDoctor && (int) $selectedDoctor->id !== (int) $currentDoctor->id) {
                abort(403, 'Нельзя записать пациента к другому врачу.');
            }

            $doctorId = $selectedDoctor->id;
        }

        DB::transaction(function () use (
            $validatedData,
            $doctorId,
            $clinicId
        ): void {
            if (!empty($validatedData['patient_id'])) {
                $patient = Patient::findOrFail($validatedData['patient_id']);
            } else {
                /*
                 * Паспорт больше не является ключом пациента.
                 * Для нового ввода сначала пытаемся найти человека по СНИЛС.
                 */
                $patient = Patient::query()
                    ->where('snils', $validatedData['snils'])
                    ->first() ?? new Patient();
            }

            $patient->fill([
                'surname' => $validatedData['surname'],
                'name' => $validatedData['name'],
                'patronym' => $validatedData['patronym'] ?? null,
                'birth_at' => $validatedData['birth_at'],
                'gender' => $validatedData['gender'],
                'address_registration' => $validatedData['address_registration'],
                'address_residence' => $validatedData['address_residence'],
                'birth_place' => $validatedData['birth_place'],
                'snils' => $validatedData['snils'],
                'oms' => $validatedData['oms'],
                'doctor_id' => $doctorId,
                'serial' => $validatedData['patient_document_series'],
                'number' => $validatedData['patient_document_number'],
            ]);
            $patient->save();

            if (empty($validatedData['patient_id'])) {
                $this->upsertPrimaryPatientIdentityDocument(
                    $patient->id,
                    $validatedData
                );

                $patientAge = Carbon::parse($validatedData['birth_at'])->age;
                $hasRepresentative = !empty($validatedData['representative_surname'])
                    && !empty($validatedData['representative_name']);

                if ($patientAge < 15 || ($patientAge < 18 && $hasRepresentative)) {
                    $representativeId = $this->upsertPrimaryPatientRepresentative(
                        $patient->id,
                        $validatedData
                    );

                    $this->upsertPrimaryRepresentativeIdentityDocument(
                        $representativeId,
                        $validatedData
                    );

                    if (in_array(
                        $validatedData['representative_type'],
                        ['guardian', 'trustee', 'authorized', 'other'],
                        true
                    )) {
                        $this->upsertPrimaryRepresentativeAuthorityDocument(
                            $representativeId,
                            $validatedData
                        );
                    }
                }
            }

            RegistryRecord::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctorId,
                'for_datetime' => Carbon::parse($validatedData['appointment_datetime']),
                'appointment' => false,
                'clinic_id' => $clinicId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('doctors.reception.index')
            ->with('success', 'Пациент успешно записан в регистратуру.');
    }

    private function upsertPrimaryPatientIdentityDocument(
        int $patientId,
        array $data
    ): void {
        DB::table('patient_identity_documents')
            ->where('patient_id', $patientId)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->update([
                'is_primary' => false,
                'updated_at' => now(),
            ]);

        $documentName = $this->identityDocumentName(
            $data['patient_document_type'],
            $data['patient_document_name'] ?? null
        );

        $existingDocument = DB::table('patient_identity_documents')
            ->where('patient_id', $patientId)
            ->where('document_type', $data['patient_document_type'])
            ->where('number', $data['patient_document_number'])
            ->first();

        $values = [
            'document_name' => $documentName,
            'series' => $data['patient_document_series'] ?? null,
            'number' => $data['patient_document_number'],
            'issued_by' => $data['patient_document_issued_by'],
            'issued_at' => $data['patient_document_issued_at'],
            'department_code' => $data['patient_document_department_code'] ?? null,
            'is_primary' => true,
            'is_active' => true,
            'updated_at' => now(),
        ];

        if ($existingDocument) {
            DB::table('patient_identity_documents')
                ->where('id', $existingDocument->id)
                ->update($values);

            return;
        }

        DB::table('patient_identity_documents')->insert(array_merge(
            $values,
            [
                'patient_id' => $patientId,
                'document_type' => $data['patient_document_type'],
                'created_at' => now(),
            ]
        ));
    }

    private function upsertPrimaryPatientRepresentative(
        int $patientId,
        array $data
    ): int {
        DB::table('patient_representatives')
            ->where('patient_id', $patientId)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->update([
                'is_primary' => false,
                'updated_at' => now(),
            ]);

        $representativeQuery = DB::table('patient_representatives')
            ->where('patient_id', $patientId)
            ->where('representative_type', $data['representative_type'])
            ->where('surname', $data['representative_surname'])
            ->where('name', $data['representative_name']);

        if (!empty($data['representative_birth_at'])) {
            $representativeQuery->whereDate(
                'birth_at',
                $data['representative_birth_at']
            );
        }

        $representative = $representativeQuery->first();

        $values = [
            'representative_type' => $data['representative_type'],
            'surname' => $data['representative_surname'],
            'name' => $data['representative_name'],
            'patronym' => $data['representative_patronym'] ?? null,
            'birth_at' => $data['representative_birth_at'] ?? null,
            'snils' => $data['representative_snils'] ?? null,
            'phone' => $data['representative_phone'] ?? null,
            'email' => $data['representative_email'] ?? null,
            'is_primary' => true,
            'is_active' => true,
            'updated_at' => now(),
        ];

        if ($representative) {
            DB::table('patient_representatives')
                ->where('id', $representative->id)
                ->update($values);

            return (int) $representative->id;
        }

        return (int) DB::table('patient_representatives')->insertGetId(
            array_merge(
                $values,
                [
                    'patient_id' => $patientId,
                    'created_at' => now(),
                ]
            )
        );
    }

    private function upsertPrimaryRepresentativeIdentityDocument(
        int $representativeId,
        array $data
    ): void {
        $this->upsertPrimaryRepresentativeDocument(
            $representativeId,
            'identity',
            [
                'document_type' => $data['representative_identity_document_type'],
                'document_name' => $this->identityDocumentName(
                    $data['representative_identity_document_type'],
                    $data['representative_identity_document_name'] ?? null
                ),
                'series' => $data['representative_identity_series'] ?? null,
                'number' => $data['representative_identity_number'],
                'issued_by' => $data['representative_identity_issued_by'],
                'issued_at' => $data['representative_identity_issued_at'],
                'department_code' => $data['representative_identity_department_code'] ?? null,
            ]
        );
    }

    private function upsertPrimaryRepresentativeAuthorityDocument(
        int $representativeId,
        array $data
    ): void {
        $documentName = match ($data['representative_authority_document_type']) {
            'guardianship_order' => 'Документ об установлении опеки/попечительства',
            'power_of_attorney' => 'Доверенность',
            default => $data['representative_authority_document_name'] ?? 'Другой документ',
        };

        $this->upsertPrimaryRepresentativeDocument(
            $representativeId,
            'authority',
            [
                'document_type' => $data['representative_authority_document_type'],
                'document_name' => $documentName,
                'series' => $data['representative_authority_series'] ?? null,
                'number' => $data['representative_authority_number'],
                'issued_by' => $data['representative_authority_issued_by'],
                'issued_at' => $data['representative_authority_issued_at'],
                'department_code' => null,
            ]
        );
    }

    private function upsertPrimaryRepresentativeDocument(
        int $representativeId,
        string $purpose,
        array $document
    ): void {
        DB::table('patient_representative_documents')
            ->where('representative_id', $representativeId)
            ->where('document_purpose', $purpose)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->update([
                'is_primary' => false,
                'updated_at' => now(),
            ]);

        $existingDocument = DB::table('patient_representative_documents')
            ->where('representative_id', $representativeId)
            ->where('document_purpose', $purpose)
            ->where('document_type', $document['document_type'])
            ->where('number', $document['number'])
            ->first();

        $values = array_merge(
            $document,
            [
                'is_primary' => true,
                'is_active' => true,
                'updated_at' => now(),
            ]
        );

        if ($existingDocument) {
            DB::table('patient_representative_documents')
                ->where('id', $existingDocument->id)
                ->update($values);

            return;
        }

        DB::table('patient_representative_documents')->insert(array_merge(
            $values,
            [
                'representative_id' => $representativeId,
                'document_purpose' => $purpose,
                'created_at' => now(),
            ]
        ));
    }

    private function identityDocumentName(
        string $type,
        ?string $customName = null
    ): string {
        return match ($type) {
            'passport_rf' => 'Паспорт гражданина РФ',
            'birth_certificate' => 'Свидетельство о рождении',
            'foreign_passport' => 'Иностранный паспорт',
            'residence_permit' => 'Вид на жительство',
            'temporary_identity' => 'Временное удостоверение личности',
            'foreign_identity' => 'Иностранный документ, удостоверяющий личность',
            default => $customName ?: 'Другой документ',
        };
    }

    public function archives(Request $request): View
    {
        $now = Carbon::now();
        $user = auth()->user();
        $isDebug = app()->hasDebugModeEnabled();
        $recordsQuery = RegistryRecord::where(function ($query) use ($now) {
            $query->where('appointment', true)
                ->orWhere('for_datetime', '<=', $now);
        });

        $doctors = Doctor::orderBy('surname');

        if (!$isDebug) {
            $doctor = $user->doctor()->first();
            $nurse = $user->nurse()->first();
            $clinics = $doctor?->clinic ?? $nurse?->clinic;

            $recordsQuery->whereIn('clinic_id', $clinics?->pluck('id') ?? []);
            $hostClinic = $clinics?->pluck('name');
            $name = $doctor?->name ?? $nurse?->name ?? '';
            $surname = $doctor?->surname ?? $nurse?->surname ?? '';
            $patronym = $doctor?->patronym ?? $nurse?->patronym ?? '';
            $profession = $nurse?->profession ?? 'врач';

            // отфильтровать докторов по клинике
            $doctors = $doctors->where('clinic_id', $doctor->clinic_id ?? $nurse->clinic_id)->get();
        } else {
            $hostClinic = Clinic::inRandomOrder()->first();
            $name = 'Администратор';
            $surname = 'Дебаггеров';
            $patronym = 'Тестович';
            $profession = 'дебаггер';
            $doctors = $doctors->get();
        }

        // 👇 фильтрация по врачу
        if ($request->filled('doctor_id')) {
            $recordsQuery->where('doctor_id', $request->get('doctor_id'));
        }

        $records = $recordsQuery->withTrashed()->paginate(30);

        return view('doctors.reception.archives', compact(
            'records',
            'hostClinic',
            'name',
            'surname',
            'patronym',
            'profession',
            'doctors'
        ));
    }

    public function edit($id)
    {
        $record = RegistryRecord::with('patient')->findOrFail($id);
        $doctors = Doctor::orderBy('surname')->get();

        return view('doctors.reception.edit', compact('record', 'doctors'));
    }

    public function update(UpdateRegistryRecordRequest $request, $id)
    {
        $record = RegistryRecord::with('patient')->findOrFail($id);

        $record->doctor_id = $request->doctor_id;
        $record->for_datetime = $request->appointment_datetime;
        $record->save();

        $patient = $record->patient;
        $patient->fill($request->only([
            'surname', 'name', 'patronym', 'birth_at', 'gender',
            'address_registration', 'address_residence',
            'serial', 'number', 'department_code',
            'issued_by', 'issued_at', 'birth_place',
            'snils', 'oms'
        ]));
        $patient->save();

        return redirect()
            ->route('doctors.reception.index')
            ->with('success', 'Запись успешно обновлена.');
    }

    public function destroy($id)
    {
        $record = RegistryRecord::findOrFail($id);
        $record->delete(); // Soft delete — удаляется через `deleted_at`

        return redirect()
            ->route('doctors.reception.index')
            ->with('success', 'Запись успешно перенесена в архив.');
    }
}
