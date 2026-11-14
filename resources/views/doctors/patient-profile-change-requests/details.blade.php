@php
    $payload = (array) $changeRequest->payload;
    $patientData = (array) ($payload['patient'] ?? []);
    $documentData = (array) ($payload['document'] ?? []);
    $documentKeys = [
        'document_type' => 'document_type',
        'document_series' => 'series',
        'document_number' => 'number',
        'document_issued_by' => 'issued_by',
        'document_issued_at' => 'issued_at',
        'document_department_code' => 'department_code',
    ];
@endphp

<details class="mt-2">
    <summary class="small fw-semibold text-primary">Посмотреть предлагаемые значения</summary>
    <div class="row g-2 mt-1">
        @foreach($changeRequest->changed_fields ?? [] as $field)
            @php
                $value = isset($documentKeys[$field])
                    ? ($documentData[$documentKeys[$field]] ?? null)
                    : ($patientData[$field] ?? null);
                if ($field === 'gender') {
                    $value = $value === 'M' ? 'Мужской' : ($value === 'F' ? 'Женский' : $value);
                }
                if ($field === 'document_type') {
                    $value = \App\Models\PatientIdentityDocument::typeOptions()[$value] ?? $value;
                }
            @endphp
            <div class="col-md-6">
                <div class="rounded border bg-white p-2">
                    <small class="text-secondary d-block">{{ $fieldLabels[$field] ?? $field }}</small>
                    <strong class="small text-break">{{ filled($value) ? $value : '—' }}</strong>
                </div>
            </div>
        @endforeach
    </div>
</details>
