@extends('layouts.admin')
@section('title', 'Создание записи в регистратуру')
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
    @vite(['resources/js/registry_script.js'])
@endsection
@section('main')
    <div class="container-fluid">
        <h1>Создание записи на приём</h1>
        {{ Breadcrumbs::render('admin.dictionary.create') }}
        <div class="container-fluid">
            <form class="col-md-6~ mt-5" action="{{ route('admin.dictionary.registration.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                @recaptcha
                @if(session()->has('success'))
                    <div class="alert alert-success">
                        {{ session()->get('success') }}
                    </div>
                @elseif($errors->isNotEmpty())
                    <div class="alert alert-danger">
                        При создании записи в регистратуру произошли следующие ошибки:
                        <ol>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ol>
                    </div>
                @endif
                <div class="row">
                    <div class="mb-3">
                        <label for="patient-field" class="form-label">Пациент</label>
                        <select id="patient-field" name="patient_id" class="form-control" required>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->surname }}, {{ $patient->name }} {{ $patient->patronym }}, д/р {{ $patient->birth_at }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="doctor-field" class="form-label">Доктор</label>
                        <select id="doctor-field" name="doctor_id" class="form-control" required>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->surname }}, {{ $doctor->name }} {{ $doctor->patronym }}, д/р {{ $doctor->birth_at }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="session-picker">
                        <label for="for_datetime" class="form-label">Время приёма</label>
                        <input type="text" name="for_datetime" id="for_datetime" placeholder="Выберите дату и время" class="form-control" />
                    </div>
                    <div class="mt-3">
                        <input type="checkbox" name="appointment" id="appointment">
                        <label for="appointment" class="form-label">Принят на приём</label>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3">
                        <button class="btn btn-outline-success btn-sm" type="submit"><i class="bi bi-box-arrow-down"></i> Сохранить изменения</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        grecaptcha.ready(function () {
            function onFormSubmit(event) {
                let form = this;

                event.preventDefault();
                grecaptcha.execute('{{ env('RECAPTCHAV3_SITEKEY') }}', {action: 'submit'}).then(function (token) {
                    form.querySelectorAll('[name="g-recaptcha-response"]').forEach(captchaInput => captchaInput.value = token);
                    form.submit();
                });
            }

            document.querySelectorAll('form').forEach(e => e.addEventListener('submit', onFormSubmit));
        })
    </script>
@endsection
<div class="modal fade" id="time-unavailable-modal" tabindex="-1" aria-labelledby="timeUnavailableLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="timeUnavailableLabel">Недоступное время</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body" id="modal-message">
                Выбранное время уже занято для выбранного врача.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ОК</button>
            </div>
        </div>
    </div>
</div>
