@php
    // Только данные уже открытой карты. Никаких новых записей и запросов сохранения.
    // Берём исходную дату: accessor Patient::birth_at возвращает формат d.m.Y.
    $certificateUiBirth = '';
    $certificateUiRawBirth = $patient->getRawOriginal('birth_at');

    if ($certificateUiRawBirth) {
        try {
            $certificateUiBirth = \Illuminate\Support\Carbon::parse($certificateUiRawBirth)->format('d.m.Y');
        } catch (\Throwable $exception) {
            $certificateUiBirth = '';
        }
    }

    $certificateUiDoctor = $patient->doctor;


    // Автозаполнение только из уже загруженных данных медицинской карты.
    $certificateUiLatestAnamnesis = collect($anamneses ?? [])->first();
    $certificateUiAnamnesis = '';

    if ($certificateUiLatestAnamnesis) {
        $certificateUiAnamnesis = trim(
            preg_replace(
                '/\\s+/u',
                ' ',
                strip_tags(
                    html_entity_decode(
                        (string) ($certificateUiLatestAnamnesis->text ?? ''),
                        ENT_QUOTES | ENT_HTML5,
                        'UTF-8'
                    )
                )
            ) ?? ''
        );
    }

    $certificateUiPrescriptionItems = collect($prescriptions ?? [])
        ->flatMap(static fn ($group) => collect($group));

    $certificateUiTherapy = $certificateUiPrescriptionItems
        ->map(static function ($prescription): string {
            $name = trim((string) (
                $prescription->drug?->name
                ?? $prescription->generic_name
                ?? ''
            ));

            $instructions = trim((string) ($prescription->usage_instructions ?? ''));

            return trim($name . ($instructions !== '' ? ' — ' . $instructions : ''));
        })
        ->filter()
        ->unique()
        ->values()
        ->implode("\n");

    $certificateUiLabResearches = collect($researches ?? [])
        ->map(static function ($research): string {
            $date = '';
            $dateValue = $research->collected_at ?? $research->planned_at ?? $research->created_at ?? null;

            if ($dateValue) {
                try {
                    $date = \Illuminate\Support\Carbon::parse($dateValue)->format('d.m.Y');
                } catch (\Throwable $exception) {
                    $date = '';
                }
            }

            $title = trim((string) (
                $research->laboratory
                ?: ($research->sample_type ? 'Лабораторное исследование (' . $research->sample_type . ')' : 'Лабораторное исследование')
            ));

            $resultLines = collect($research->params_payload ?? [])
                ->map(static function ($parameter) use ($research): string {
                    $parameter = is_array($parameter) ? $parameter : (array) $parameter;
                    $id = (string) ($parameter['id'] ?? '');
                    $name = trim((string) ($parameter['name'] ?? ''));
                    $unit = trim((string) ($parameter['unit'] ?? ''));
                    $valuesMap = is_array($research->values_map ?? null) ? $research->values_map : [];
                    $valueRow = $valuesMap[$id] ?? null;
                    $value = is_array($valueRow)
                        ? ($valueRow['result_value'] ?? $valueRow['value'] ?? null)
                        : null;

                    if ($name === '' || $value === null || $value === '') {
                        return '';
                    }

                    return $name . ': ' . $value . ($unit !== '' ? ' ' . $unit : '');
                })
                ->filter()
                ->implode('; ');

            $prefix = $date !== '' ? $date . ' — ' : '';
            return $prefix . $title . ($resultLines !== '' ? ': ' . $resultLines : '');
        })
        ->filter();

    $certificateUiInstrumentalResearches = collect($instrumentalResearches ?? [])
        ->map(static function ($research): string {
            $date = '';
            $dateValue = $research->result_at ?? $research->performed_at ?? $research->planned_at ?? $research->created_at ?? null;

            if ($dateValue) {
                try {
                    $date = \Illuminate\Support\Carbon::parse($dateValue)->format('d.m.Y');
                } catch (\Throwable $exception) {
                    $date = '';
                }
            }

            $title = trim((string) ($research->name ?? $research->type_label ?? 'Инструментальное исследование'));
            $conclusion = trim((string) ($research->conclusion ?? ''));
            $prefix = $date !== '' ? $date . ' — ' : '';

            return $prefix . $title . ($conclusion !== '' ? ': ' . $conclusion : '');
        })
        ->filter();

    $certificateUiResearches = $certificateUiLabResearches
        ->concat($certificateUiInstrumentalResearches)
        ->values()
        ->implode("\n");

    $certificateUiLatestEpicrisis = $patient->epicrises->first();
    $certificateUiEpicrisisText = $certificateUiLatestEpicrisis
        ? trim(strip_tags((string) ($certificateUiLatestEpicrisis->text ?? '')))
        : '';
    $certificateUiContext = [
        'patient_name' => trim(implode(' ', array_filter([
            $patient->surname,
            $patient->name,
            $patient->patronym,
        ]))),
        'birth_date' => $certificateUiBirth,
        'card_number' => (string) ($patient->medcard_number ?? ''),
        'doctor_name' => trim(implode(' ', array_filter([
            $certificateUiDoctor?->surname,
            $certificateUiDoctor?->name,
            $certificateUiDoctor?->patronym,
        ]))),
        'diagnosis' => trim(implode(' — ', array_filter([
            $currentDiagnosisCode ?? '',
            $currentDiagnosisTitle ?? '',
        ]))),
        'issued_date' => now()->format('Y-m-d'),
        'anamnesis' => $certificateUiAnamnesis,
        'therapy' => $certificateUiTherapy,
        'researches' => $certificateUiResearches,
        'latest_epicrisis' => $certificateUiEpicrisisText,
    ];
@endphp

@include('doctors.reception.certificates.modal_psychiatrist')
@include('doctors.reception.certificates.modal_extract')
@include('doctors.reception.certificates.modal_epicrisis')
@include('doctors.reception.certificates.modal_certificate')
@include('doctors.reception.certificates.modal_continue_treatment')
