@php
    $payload = (array) $request->payload;
    $patientData = (array) ($payload['patient'] ?? []);
    $documentData = (array) ($payload['document'] ?? []);
    $labels = [
        'surname' => 'Фамилия', 'name' => 'Имя', 'patronym' => 'Отчество',
        'birth_at' => 'Дата рождения', 'gender' => 'Пол', 'phone' => 'Телефон',
        'email' => 'Контактный email', 'birth_place' => 'Место рождения',
        'address_registration' => 'Адрес регистрации', 'address_residence' => 'Адрес проживания',
        'snils' => 'СНИЛС', 'oms' => 'Полис ОМС', 'insurance_company' => 'Страховая компания',
        'document_type' => 'Тип документа', 'document_series' => 'Серия документа',
        'document_number' => 'Номер документа', 'document_issued_by' => 'Кем выдан документ',
        'document_issued_at' => 'Дата выдачи', 'document_department_code' => 'Код подразделения',
    ];
@endphp

<div class="space-y-4 text-sm">
    <div>
        <h3 class="mb-2 text-base font-semibold">Пациент</h3>
        <p>{{ $request->patient?->surname }} {{ $request->patient?->name }} {{ $request->patient?->patronym }}</p>
        <p class="text-gray-500">Аккаунт: {{ $request->user?->login }} · {{ $request->user?->email }}</p>
        <p class="text-gray-500">
            Клиника: {{ $request->reviewClinic?->name ?: 'Не указана' }}
        </p>
        <p class="text-gray-500">
            Направлено:
            {{ \App\Models\PatientProfileChangeRequest::targetOptions()[$request->review_target] ?? $request->review_target }}
            @if($request->reviewDoctor)
                — {{ $request->reviewDoctor->surname }} {{ $request->reviewDoctor->name }} {{ $request->reviewDoctor->patronym }}
            @endif
        </p>
    </div>

    <div>
        <h3 class="mb-2 text-base font-semibold">Запрошенные значения</h3>
        <dl class="grid grid-cols-1 gap-2 md:grid-cols-2">
            @foreach($request->changed_fields ?? [] as $field)
                @php
                    $documentKey = match ($field) {
                        'document_type' => 'document_type',
                        'document_series' => 'series',
                        'document_number' => 'number',
                        'document_issued_by' => 'issued_by',
                        'document_issued_at' => 'issued_at',
                        'document_department_code' => 'department_code',
                        default => null,
                    };
                    $value = $documentKey !== null
                        ? ($documentData[$documentKey] ?? null)
                        : ($patientData[$field] ?? null);
                    if ($field === 'gender') {
                        $value = $value === 'M' ? 'Мужской' : ($value === 'F' ? 'Женский' : $value);
                    }
                    if ($field === 'document_type') {
                        $value = \App\Models\PatientIdentityDocument::typeOptions()[$value] ?? $value;
                    }
                @endphp
                <div>
                    <dt class="text-gray-500">{{ $labels[$field] ?? $field }}</dt>
                    <dd class="font-medium break-words">{{ filled($value) ? $value : '—' }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    <div>
        <h3 class="mb-1 text-base font-semibold">Причина запроса</h3>
        <p class="whitespace-pre-line">{{ $request->reason }}</p>
    </div>
</div>
