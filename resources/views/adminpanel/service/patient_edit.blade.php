@extends('layouts.admin')
@section('title', "Редактирование пациента \"{$patient->surname} {$patient->name}\"")
@section('assets')
@endsection
@section('main')
    <h1>Редактирование пациента</h1>
    {{ Breadcrumbs::render('admin.users.patient_new') }}
    @if($errors->isNotEmpty())
        <div class="alert alert-danger">
            Возникли следующие ошибки:
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="container-fluid">
        <form class="row" method="post" action="{{ route('admin.users.patients.save', $patient->id) }}">
            @csrf
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="surname-input">Фамилия</label>
                    <input required class="form-control" id="surname-input" name="surname" type="text" value="{{ old('surname', $patient->surname) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="name-input">Имя</label>
                    <input required class="form-control" id="name-input" name="name" type="text" value="{{ old('name', $patient->name) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="patronym-input">Отчество</label>
                    <input required class="form-control" id="patronym-input" name="patronym" type="text" value="{{ old('patronym', $patient->patronym) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="birth_day-input">Дата рождения</label>
                    <input required class="form-control" id="birth_day-input" name="birth_at" type="date" value="{{ old('birth_at', \Illuminate\Support\Carbon::parse($patient->birth_at)->format('Y-m-d')) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="married-input">Семейное положение</label>
                    <select class="form-control" id="married-input" name="married">
                        <option value="0" @if( old('married', $patient->married) == false) selected @endif>Не женат/не замужем</option>
                        <option value="1" @if( old('married', $patient->married) == true) selected @endif>Женат/замужем</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="address_registration-input">Адрес регистрации</label>
                    <input required class="form-control" id="address_registration-input" name="address_registration" type="text" value="{{ old('address_registration', $patient->address_registration) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="address_registration-input">Адрес проживания</label>
                    <input required class="form-control" id="address_registration-input" name="address_residence" type="text" value="{{ old('address_residence', $patient->address_residence) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="address_job-input">Адрес работы</label>
                    <input class="form-control" id="address_job-input" name="address_job" type="text" value="{{ old('address_job', $patient->address_job) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="profession-input">Профессия</label>
                    <input class="form-control" id="profession-input" name="profession" type="text" value="{{ old('profession', $patient->profession) }}">
                </div>
                <div class="mb-3">
                    <input id="socially_dangerous-input" name="socially_dangerous" type="checkbox" @if(old('socially_dangerous', $patient->socially_dangerous)) checked @endif">
                    <label class="form-label" for="socially_dangerous-input">Социально опасен</label>
                </div>
                <div class="mb-3">
                    <input id="disability-input" name="disability" type="checkbox" @if(old('disability', $patient->disability)) checked @endif>
                    <label class="form-label" for="disability-input">Инвалидность</label>
                </div>
                <div class="btn-group col-md-6">
                    <button class="btn btn-success" type="submit"><i class="bi bi-check"></i> Создать пациента</button>
                </div>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[required]').forEach(e => {
                var star = document.createElement('span');
                star.innerText = '*';
                star.style.color = 'red';

                e.parentElement.querySelector('label').append(star);
            })
        });
    </script>
@endsection
