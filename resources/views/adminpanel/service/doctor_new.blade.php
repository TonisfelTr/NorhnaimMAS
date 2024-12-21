@extends('layouts.admin')
@section('title', 'Создание нового доктора')
@section('assets')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.bootstrap5.min.css">
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
        <h1>Создание нового доктора</h1>
        {{ Breadcrumbs::render('admin.users.doctors.create') }}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form class="row" enctype="multipart/form-data" method="post" action="{{ route('admin.users.doctors.store') }}">
            @csrf
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="surname" class="form-label">Фамилия</label>
                    <input required class="form-control" id="surname" name="surname" type="text" value="{{ old('surname') }}">
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Имя</label>
                    <input required class="form-control" id="name" name="name" type="text" value="{{ old('name') }}">
                </div>
                <div class="mb-3">
                    <label for="patronym" class="form-label">Отчество</label>
                    <input required class="form-control" id="patronym" name="patronym" type="text" value="{{ old('patronym') }}">
                </div>
                <div class="mb-3">
                    <label for="birth_at" class="form-label">Дата рождения</label>
                    <input required class="form-control" id="birth_at" name="birth_at" type="date" value="{{ old('birth_at') }}">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Профессия</label>
                    <input required class="form-control" id="status" name="status" type="text" value="{{ old('status') }}">
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3">
                    <button class="btn btn-outline-success btn-sm" type="submit">
                        <i class="bi bi-box-arrow-down"></i> Сохранить
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="address_job" class="form-label">Адрес</label>
                    <input class="form-control" id="address_job" name="address_job" type="text" value="{{ old('address_job') }}">
                </div>
                <div class="mb-3">
                    <label for="clinic_id" class="form-label">Клиника</label>
                    <select id="clinic_id" class="form-control" name="clinic_id">
                        <option value="0" @if (old('clinic_id') == 0) selected @endif>Частная практика</option>
                        @foreach($clinics as $clinic)
                            <option value="{{ $clinic->id }}" @if(old('clinic_id') == $clinic->id) selected @endif>{{ $clinic->name }}</option>
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
                                    value="{{ old('experience_years') }}"
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
                                    value="{{ old('experience_months') }}"
                                    min="0"
                                    max="11"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script>
        $('#clinic_id').selectize({
            plugins: ["clear_button", "remove_button"],
            create: false,
            persist: false,
            maxItems: 1,
            placeholder: 'Введите или выберите значение',
        });
    </script>
@endsection
