@extends('layouts.admin')
@section('title', "Редактирование записи доктора \"{$doctor->surname}, {$doctor->name}\"")
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
    <div class="container-fluid">
        <h1>Редактирование записи доктора "{{ $doctor->surname }}, {{ $doctor->name }}"</h1>
        {{ Breadcrumbs::render('admin.users.doctors.edit') }}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form class="row" enctype="multipart/form-data" method="post" action="{{ route('admin.users.doctors.update', $doctor->id) }}">
            @csrf
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="surname" class="form-label">Фамилия</label>
                    <input class="form-control" id="surname" name="surname" type="text" value="{{ old('surname', $doctor->surname) }}">
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Имя</label>
                    <input class="form-control" id="name" name="name" type="text" value="{{ old('name', $doctor->name) }}">
                </div>
                <div class="mb-3">
                    <label for="patronym" class="form-label">Отчество</label>
                    <input class="form-control" id="patronym" name="patronym" type="text" value="{{ old('patronym', $doctor->patronym) }}">
                </div>
                <div class="mb-3">
                    <label for="birth_at" class="form-label">Дата рождения</label>
                    <input class="form-control" id="birth_at" name="birth_at" type="date" value="{{ old('birth_at', $doctor->birth_at_for_input) }}">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Профессия</label>
                    <input class="form-control" id="status" name="status" type="text" value="{{ old('status', $doctor->status) }}">
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3">
                    <button class="btn btn-outline-success btn-sm" type="submit"><i
                            class="bi bi-box-arrow-down"></i> Сохранить изменения
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="address_job" class="form-label">Адрес</label>
                    <input class="form-control" id="address_job" name="address_job" type="text" value="{{ old('address_job', $doctor->address_job) }}">
                </div>
                <div class="mb-3">
                    <label for="clinic_id" class="form-label">Клиника</label>
                    <select id="clinic_id" class="form-control" name="clinic_id">
                        <option value="0" @if ($doctor->clinic_id == 0) selected @endif>Частная практика</option>
                        @foreach($clinics as $clinic)
                            <option value="{{ $clinic->id }}" @if($doctor->clinic_id == $clinic->id) selected @endif>{{ $clinic->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <div class="form-group" id="work_experience">
                        <label for="experience" class="form-label">Опыт работы</label>
                        <div class="d-flex gap-0">
                            <div class="w-50">
                                <label for="experience_years" class="form-label">Года</label>
                                <input
                                    id="experience_years"
                                    name="experience_years"
                                    type="number"
                                    class="form-control"
                                    placeholder="Года"
                                    min="0"
                                />
                            </div>
                            <div class="w-50">
                                <label for="experience_months" class="form-label">Месяцы</label>
                                <input
                                    id="experience_months"
                                    name="experience_months"
                                    type="number"
                                    class="form-control"
                                    placeholder="Месяцы"
                                    min="0"
                                    max="11"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div id="doctor-pricelist">
            <doctor-pricelist :initial-rows='@json($pricelist)'></doctor-pricelist>
        </div>
    </div>
    <script>
        $('#clinic_id').selectize({
            plugins: ["clear_button", "remove_button"],
            create: false,        // Разрешает ввод новых значений
            persist: false,      // Убирает созданные элементы из поля, если они удалены
            maxItems: 1,      // Позволяет множественный выбор, можно установить число для ограничения
            placeholder: 'Введите или выберите значение',
        });
    </script>
@endsection
