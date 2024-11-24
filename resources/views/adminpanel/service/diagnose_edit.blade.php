@extends('layouts.admin')
@section('title', 'Редактирование диагноза "' . $diagnose->title . '"')
@section('assets')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.bootstrap5.min.css">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.default.min.css"
        integrity="sha512-pTaEn+6gF1IeWv3W1+7X7eM60TFu/agjgoHmYhAfLEU8Phuf6JKiiE8YmsNC0aCgQv4192s4Vai8YZ6VNM6vyQ=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/js/selectize.min.js"
        integrity="sha512-IOebNkvA/HZjMM7MxL0NYeLYEalloZ8ckak+NDtOViP7oiYzG5vn6WVXyrJDiJPhl4yRdmNAG49iuLmhkUdVsQ=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    ></script>
@endsection
@section('main')
    <h1>Редактирование диагноза "{{ $diagnose->title }}"</h1>
    {{ Breadcrumbs::render('admin.dictionary.diagnoses_edit') }}
    <hr class="pt-3">
    <div class="container-fluid">
        <form class="row" method="post" enctype="multipart/form-data" action="{{ route('admin.dictionary.diagnoses.save', $diagnose->id) }}">
            @csrf
            <div class="col-md-6">
                <h3>Основная информация</h3>
                <div class="pt-3">
                    <div class="mb-3">
                        <label class="form-label" for="title">Название диагноза</label>
                        <input class="form-control" id="title" name="title" value="{{ old('title') ?? $diagnose->title }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="code">Код по МКБ-10</label>
                        <input class="form-control" id="code" name="code" value="{{ old('code') ?? $diagnose->code }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="required_symptoms">Обязательные симптомы</label>
                        <select class="form-control" id="required_symptoms" name="required_symptoms[]" multiple>
                            @foreach($symptoms as $symptom)
                                <option value="{{ $symptom->id }}" @if(in_array($symptom->id, $diagnose->requiredSymptoms->pluck('id')->toArray())) selected @endif >{{ $symptom->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="required_symptoms">Относительные симптомы</label>
                        <select class="form-control" id="relative_symptoms" name="relative_symptoms[]" multiple>
                            @foreach($symptoms as $symptom)
                                <option value="{{ $symptom->id }}" @if(in_array($symptom->id, $diagnose->relativeSymptoms->pluck('id')->toArray())) selected @endif >{{ $symptom->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <label for="required_criteria" class="form-label">Кол-во обязательных симптомов</label>
                            <input class="form-control" id="required_criteria" name="required_criteria" type="number" value="{{ old('required_criteria') ?? $diagnose->required_criteria }}">
                        </div>
                        <div class="col-md-6">
                            <label for="relative_criteria" class="form-label">Кол-во относительных симптомов</label>
                            <input class="form-control" id="relative_criteria" name="relative_criteria" type="number" value="{{ old('relative_criteria') ?? $diagnose->relative_criteria }}">
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button class="btn btn-outline-success btn-sm" type="submit"><i
                                class="bi bi-box-arrow-down"></i> Сохранить изменения
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h3>Описание</h3>
                <div class="mb-3">
                    <label for="description" class="form-label">Описание</label>
                    <textarea class="form-control" id="description"
                              name="description">{{ $diagnose->description }}</textarea>
                </div>
            </div>
        </form>
    </div>
    <script>
        $('#required_symptoms').selectize({
            plugins: ["clear_button", "remove_button"],
            create: true,        // Разрешает ввод новых значений
            persist: false,      // Убирает созданные элементы из поля, если они удалены
            maxItems: null,      // Позволяет множественный выбор, можно установить число для ограничения
            placeholder: 'Введите или выберите значение',
        });
        $('#relative_symptoms').selectize({
            plugins: ["clear_button", "remove_button"],
            create: true,        // Разрешает ввод новых значений
            persist: false,      // Убирает созданные элементы из поля, если они удалены
            maxItems: null,      // Позволяет множественный выбор, можно установить число для ограничения
            placeholder: 'Введите или выберите значение',
        });
    </script>
@endsection
