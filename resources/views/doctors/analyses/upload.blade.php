@extends('doctors.analyses')

@section('analysis-title', 'Загрузка лабораторного результата')

@section('analysis-content')
    @php
        $labBase = url('/doctors/analyses');
    @endphp

    <div class="lab-page">
        <div class="lab-container">
            <section class="lab-hero">
                <div class="lab-hero__content">
                    <div class="lab-kicker">Импорт результата</div>
                    <h1 class="lab-title">Загрузить лабораторный результат</h1>
                    <p class="lab-description">
                        Прикрепите файл результата и свяжите его с пациентом и назначением.
                    </p>
                </div>

                <div class="lab-hero__actions">
                    <a class="lab-button lab-button--secondary" href="{{ $labBase }}/journal">
                        Отмена
                    </a>
                </div>
            </section>

            @if($errors->any())
                <div class="lab-note lab-note--danger mb-4">
                    <strong>Не удалось загрузить результат.</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ $uploadUrl ?? '#' }}"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="lab-detail-grid">
                    <main>
                        <section class="lab-panel">
                            <div class="lab-panel__header">
                                <h2 class="lab-panel__title">Данные результата</h2>
                            </div>

                            <div class="lab-panel__body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="lab-label">Пациент</label>
                                        <select class="lab-control" name="patient_id" required>
                                            <option value="">Выберите пациента</option>
                                            @foreach($patients ?? [] as $patient)
                                                <option
                                                    value="{{ data_get($patient, 'id') }}"
                                                    @selected(
                                                        (string) request('patient_id')
                                                        ===
                                                        (string) data_get($patient, 'id')
                                                    )
                                                >
                                                    {{ trim(collect([
                                                        data_get($patient, 'surname'),
                                                        data_get($patient, 'name'),
                                                        data_get($patient, 'patronym'),
                                                    ])->filter()->implode(' ')) }}

                                                    @if(data_get($patient, 'birth_at'))
                                                        ({{ \Carbon\Carbon::parse(
                data_get($patient, 'birth_at')
            )->format('d.m.Y') }})
                                                    @endif
                                                </option>
                                            @endforeach

                                            Получится:
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="lab-label">Связанное назначение</label>
                                        <select class="lab-control" name="assignment_id">
                                            <option value="">Без назначения</option>
                                            @foreach($openAssignments ?? [] as $assignment)
                                                <option
                                                    value="{{ data_get($assignment, 'id') }}"
                                                    @selected((string) old('assignment_id') === (string) data_get($assignment, 'id'))
                                                >
                                                    {{ data_get($assignment, 'label') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="lab-label">Название исследования</label>
                                        <input
                                            class="lab-control"
                                            type="text"
                                            name="research_name"
                                            value="{{ old('research_name') }}"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-3">
                                        <label class="lab-label">Дата результата</label>
                                        <input
                                            class="lab-control"
                                            type="date"
                                            name="result_date"
                                            value="{{ old('result_date') }}"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-3">
                                        <label class="lab-label">Лаборатория</label>
                                        <input
                                            class="lab-control"
                                            type="text"
                                            name="laboratory_name"
                                            value="{{ old('laboratory_name') }}"
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label class="lab-label">Файл результата</label>
                                        <input
                                            class="lab-control"
                                            type="file"
                                            name="result_file"
                                            accept=".pdf,.jpg,.jpeg,.png,.xml,.json"
                                            required
                                        >
                                        <div class="lab-secondary-text">
                                            PDF, изображение, XML или JSON.
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="lab-label">Комментарий</label>
                                        <textarea
                                            class="lab-control"
                                            name="comment"
                                            rows="4"
                                        >{{ old('comment') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </main>

                    <aside>
                        <section class="lab-panel">
                            <div class="lab-panel__header">
                                <h2 class="lab-panel__title">Обработка</h2>
                            </div>

                            <div class="lab-panel__body">
                                <div class="lab-note">
                                    На следующем этапе сюда можно добавить разбор XML/JSON,
                                    предварительный просмотр показателей и ручное сопоставление
                                    референсных значений.
                                </div>
                            </div>
                        </section>

                        <section class="lab-panel">
                            <div class="lab-panel__body">
                                <button class="lab-button lab-button--primary w-100" type="submit">
                                    Загрузить результат
                                </button>

                                <a class="lab-button lab-button--secondary w-100 mt-2" href="{{ $labBase }}/journal">
                                    Отмена
                                </a>
                            </div>
                        </section>
                    </aside>
                </div>
            </form>
        </div>
    </div>
@endsection
