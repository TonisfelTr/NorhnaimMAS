@extends('layouts.doctor')
@section('title', $config['title'])
@section('assets')
    @parent
    @include('doctors.card_documents.partials.assets')
@endsection
@section('main')
    @php
        $mcdValue = static function ($key, $fallback = '') use ($document) {
            $value = old($key, data_get($document, $key, $fallback));
            return is_scalar($value) || $value === null ? $value : $fallback;
        };
        $mcdSelectedSources = old('source_options', $document->source_options ?? []);
        $mcdSelectedSources = is_array($mcdSelectedSources) ? $mcdSelectedSources : [];
    @endphp
    <main class="mcd-page">
        <header class="mcd-hero">
            <div class="mcd-eyebrow">{{ $document->exists ? $document->number . ' · Редактирование черновика' : 'Новый документ' }}</div>
            <h1>{{ $config['title'] }}</h1>
            <p>{{ $config['hint'] }}. Медицинские выводы и рекомендации заполняет врач.</p>
            <a class="mcd-button mcd-button--light" href="{{ route('doctors.patients.medical_card', $patient->id) }}"><i class="bi bi-arrow-left"></i> В медицинскую карту</a>
        </header>
        @if($errors->any())
            <div class="mcd-alert mcd-alert--error" role="alert">
                <strong>Документ не сохранён.</strong>
                @foreach($errors->all() as $message)<div>{{ $message }}</div>@endforeach
            </div>
        @endif
        <form id="mcdForm" class="mcd-form" method="post" autocomplete="off"
              action="{{ $document->exists ? route('doctors.card_documents.update', $document) : route('doctors.card_documents.store', $patient->id) }}"
              data-mcd-sources-url="{{ route('doctors.card_documents.sources', $patient->id) }}">
            @csrf
            @if($document->exists)@method('PUT')@endif
            <input type="hidden" name="type" value="{{ $document->type }}">
            <input type="hidden" name="version" value="{{ old('version', $document->version) }}">
            <div class="mcd-form-layout">
                <aside class="mcd-sidebar">
                    <section class="mcd-card">
                        <div class="mcd-eyebrow">Пациент</div>
                        <h2>{{ $document->patient_snapshot['name'] ?? $patient->full_name }}</h2>
                        <p>Дата рождения: {{ $document->patient_snapshot['birth_at'] ?: '—' }}<br>Медкарта № {{ $document->patient_snapshot['medcard_number'] ?: '—' }}</p>
                        <small>При сохранении регистрационные сведения обновляются из карты. Сама карта не изменяется.</small>
                    </section>
                    <section class="mcd-card">
                        <h2>Реквизиты документа</h2>
                        <label class="mcd-field">Дата документа <span aria-hidden="true">*</span>
                            <input class="form-control" type="date" name="issued_at" required max="{{ today()->format('Y-m-d') }}" value="{{ old('issued_at', $document->issued_at?->format('Y-m-d')) }}">
                        </label>
                        <label class="mcd-field">Медицинская организация
                            <input class="form-control" type="text" name="organization" maxlength="255" value="{{ $mcdValue('organization') }}" placeholder="Наименование организации">
                        </label>
                        <label class="mcd-field">Адрес, телефон и другие реквизиты
                            <textarea class="form-control" name="organization_details" rows="3" maxlength="1000">{{ $mcdValue('organization_details') }}</textarea>
                        </label>
                        <label class="mcd-field">Должность врача
                            <input class="form-control" type="text" name="author_position" maxlength="255" value="{{ $mcdValue('author_position') }}">
                        </label>
                        <p class="mcd-muted">Автор: {{ $document->author_name }}</p>
                        <label class="mcd-field">{{ $document->type === 'continuation' ? 'Принимающая организация / врач' : 'Куда предоставляется' }}
                            <input class="form-control" type="text" name="recipient" maxlength="500" value="{{ $mcdValue('recipient') }}">
                        </label>
                        <label class="mcd-field">Цель выдачи документа
                            <textarea class="form-control" name="purpose" rows="2" maxlength="1000">{{ $mcdValue('purpose') }}</textarea>
                        </label>
                    </section>
                    <section class="mcd-card">
                        <h2>Диагноз в документе</h2>
                        <label class="mcd-check">
                            <input type="hidden" name="include_diagnosis" value="0">
                            <input type="checkbox" name="include_diagnosis" value="1" data-mcd-diagnosis-toggle @checked((bool) old('include_diagnosis', $document->include_diagnosis))>
                            <span>Включить диагноз в этот документ</span>
                        </label>
                        <div data-mcd-diagnosis-fields>
                            <label class="mcd-field">Код МКБ-10<input class="form-control" name="diagnosis_code" maxlength="64" value="{{ $mcdValue('diagnosis_code') }}"></label>
                            <label class="mcd-field">Формулировка диагноза<textarea class="form-control" name="diagnosis_title" rows="3" maxlength="2000">{{ $mcdValue('diagnosis_title') }}</textarea></label>
                            <small>Первичная подстановка — текущий диагноз из карты, а не диагноз на произвольную прошлую дату. Проверьте его перед оформлением.</small>
                        </div>
                        @if($document->type === 'certificate')<p class="mcd-muted">В справку диагноз по умолчанию не переносится.</p>@endif
                    </section>
                </aside>
                <div class="mcd-main">
                    <section class="mcd-card">
                        <div class="mcd-head"><div><h2>Содержание</h2><p>Только проверенные сведения. Отсутствующие данные не заменяются утверждениями о норме.</p></div></div>
                        @foreach($config['fields'] as $key => $label)
                            <label class="mcd-field">{{ $label }}
                                @if(in_array($key, $config['required'], true))<small>Обязательно при оформлении</small>@endif
                                <textarea class="form-control mcd-clinical-text" name="fields[{{ $key }}]" rows="{{ $key === 'body' ? 10 : 5 }}" maxlength="30000" placeholder="Заполните по данным осмотра и медицинской документации">{{ $mcdValue('fields.' . $key) }}</textarea>
                            </label>
                        @endforeach
                    </section>
                    <section class="mcd-card">
                        <h2>Приложить записи из медкарты</h2>
                        <p>Это дословные исходные записи, а не автоматически составленное заключение. Выбирайте только необходимые разделы.</p>
                        <div class="mcd-two-columns">
                            <label class="mcd-field">Период
                                <select class="form-select" name="period_mode" data-mcd-period-mode>
                                    <option value="all" @selected(old('period_mode', $document->period_mode) === 'all')>Всё время — по дату документа</option>
                                    <option value="range" @selected(old('period_mode', $document->period_mode) === 'range')>Указать период</option>
                                </select>
                            </label>
                            <div class="mcd-two-columns" data-mcd-period-range>
                                <label class="mcd-field">С<input class="form-control" name="period_from" type="date" value="{{ old('period_from', $document->period_from?->format('Y-m-d')) }}"></label>
                                <label class="mcd-field">По<input class="form-control" name="period_to" type="date" value="{{ old('period_to', $document->period_to?->format('Y-m-d')) }}"></label>
                            </div>
                        </div>
                        <div class="mcd-source-grid">
                            @foreach(\App\Support\CardDocuments\DocumentCatalog::SOURCES as $key => $label)
                                <label class="mcd-check"><input type="checkbox" name="source_options[]" value="{{ $key }}" @checked(in_array($key, $mcdSelectedSources, true))><span>{{ $label }}</span></label>
                            @endforeach
                        </div>
                        <div class="mcd-alert">История рецептов не подтверждает актуальную терапию. Записи приложения могут содержать диагнозы даже при выключенном поле диагноза выше.</div>
                        <div class="mcd-actions">
                            <button type="button" class="mcd-button mcd-button--light" data-mcd-preview-sources><i class="bi bi-eye"></i> Посмотреть выбранные записи</button>
                            <span class="mcd-muted" data-mcd-preview-count aria-live="polite"></span>
                        </div>
                        <div data-mcd-sources-error class="mcd-alert mcd-alert--error" role="alert" hidden></div>
                        <div data-mcd-sources-preview class="mcd-sources-preview" aria-live="polite"></div>
                        <small>При сохранении приложение заново собирается на сервере и сохраняется вместе с документом. После оформления оно не меняется при правках карты.</small>
                    </section>
                    <section class="mcd-card">
                        <h2>Сохранение</h2>
                        <label class="mcd-check"><input type="checkbox" name="reviewed" value="1"><span>Я проверил(а) содержание документа, реквизиты и выбранные исходные записи.</span></label>
                        <p class="mcd-muted">Черновик можно редактировать. После оформления документ сохраняется без возможности изменения. Электронная подпись не накладывается.</p>
                        <div class="mcd-actions">
                            <button type="submit" name="intent" value="draft" class="mcd-button mcd-button--light">Сохранить черновик</button>
                            <button type="submit" name="intent" value="final" class="mcd-button"><i class="bi bi-check2-circle"></i> Оформить документ</button>
                            <a class="mcd-button mcd-button--light" href="{{ route('doctors.card_documents.blank', $document->type) }}" target="_blank" rel="noopener"><i class="bi bi-printer"></i> Чистый бланк</a>
                        </div>
                        <noscript><p class="mcd-muted">JavaScript выключен: сохранение работает; предварительная загрузка источников недоступна.</p></noscript>
                    </section>
                </div>
            </div>
        </form>
    </main>
@endsection
