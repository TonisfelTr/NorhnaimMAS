@php($payload = (array) $request->payload)

<div class="space-y-4 text-sm">
    <div>
        <h3 class="mb-2 text-base font-semibold">Данные из заявки</h3>
        <dl class="grid grid-cols-1 gap-2 md:grid-cols-2">
            <div><dt class="text-gray-500">ФИО</dt><dd>{{ trim(implode(' ', array_filter([$payload['surname'] ?? null, $payload['name'] ?? null, $payload['patronym'] ?? null]))) }}</dd></div>
            <div><dt class="text-gray-500">Дата рождения</dt><dd>{{ $payload['birth_at'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Пол</dt><dd>{{ ($payload['gender'] ?? null) === 'M' ? 'Мужской' : 'Женский' }}</dd></div>
            <div><dt class="text-gray-500">Место рождения</dt><dd>{{ $payload['birth_place'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Телефон</dt><dd>{{ $payload['phone'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Контактный email</dt><dd>{{ $payload['email'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Адрес регистрации</dt><dd>{{ $payload['address_registration'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Адрес проживания</dt><dd>{{ $payload['address_residence'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">СНИЛС</dt><dd>{{ $payload['snils'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Полис ОМС</dt><dd>{{ $payload['oms'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Страховая компания</dt><dd>{{ $payload['insurance_company'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Тип документа</dt><dd>{{ \App\Models\PatientIdentityDocument::typeOptions()[$payload['document_type'] ?? ''] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Серия и номер</dt><dd>{{ trim(($payload['document_series'] ?? '') . ' ' . ($payload['document_number'] ?? '')) ?: '—' }}</dd></div>
            <div><dt class="text-gray-500">Кем выдан</dt><dd>{{ $payload['document_issued_by'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Дата выдачи</dt><dd>{{ $payload['document_issued_at'] ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Код подразделения</dt><dd>{{ $payload['document_department_code'] ?? '—' }}</dd></div>
        </dl>
    </div>

    @if($request->review_note)
        <div class="rounded-lg bg-warning-50 p-3 text-warning-800">
            {{ $request->review_note }}
        </div>
    @endif

    <p class="text-gray-500">
        Сопоставьте данные заявки с документами и медицинской картой.
        Привязка не изменяет существующие данные карты, если отдельно не включить соответствующую опцию.
    </p>
</div>
