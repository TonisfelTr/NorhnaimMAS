@extends('layouts.admin')
@section('title', 'Создание новой записи пациента')
@section('assets')

@endsection

@section('main')
    <h1>Создание новой записи пациента</h1>
    {{ Breadcrumbs::render('admin.users.patient_new') }}
    <div class="container-fluid">
        <form class="row" method="post" action="{{ route('admin.users.patients.new') }}">
            @csrf
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="surname-input">Фамилия</label>
                    <input required class="form-control" id="surname-input" name="surname" type="text">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="name-input">Имя</label>
                    <input required class="form-control" id="name-input" name="name" type="text">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="patronym-input">Отчество</label>
                    <input required class="form-control" id="patronym-input" name="patronym" type="text">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="birth_day-input">Дата рождения</label>
                    <input required class="form-control" id="birth_day-input" name="birth_at" type="date">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="married-input">Семейное положение</label>
                    <select class="form-control" id="married-input" name="married">
                        <option value="0">Не женат/не замужем</option>
                        <option value="1">Женат/замужем</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="address_registration-input">Адрес регистрации</label>
                    <input required class="form-control" id="address_registration-input" name="address_registration" type="text">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="address_registration-input">Адрес проживания</label>
                    <input required class="form-control" id="address_registration-input" name="address_residence" type="text">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="address_job-input">Адрес работы</label>
                    <input class="form-control" id="address_job-input" name="address_job" type="text">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="profession-input">Профессия</label>
                    <input class="form-control" id="profession-input" name="profession" type="text">
                </div>
                <div class="btn-group col-md-6">
                    <button class="btn btn-success" type="submit"><i class="bi bi-check"></i> Создать пациента</button>
                </div>
            </div>
        </form>
    </div>
    <script>

    </script>
@endsection
