@extends('doctors.reception')
@section('title', 'Редактировать запись')
@section('assets')
    {{-- Стили и Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            padding: 4px 6px !important;
            font-size: 1rem !important;
            line-height: 1.5 !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 6px !important;
        }
    </style>
@endsection

@section('sub-main')
    <div class="container py-4">
        <h3 class="mb-4">Редактировать запись №{{ $record->id }}</h3>

        <form method="POST" action="{{ route('doctors.reception.update', $record->id) }}">
            @csrf
            <div class="card shadow-sm p-4">
                {{-- Врач --}}
                <div class="mb-3">
                    <label class="form-label">Врач</label>
                    <select class="form-select" id="doctor_id" name="doctor_id" required>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ $record->doctor_id == $doctor->id ? 'selected' : '' }}>
                                {{ $doctor->surname }} {{ $doctor->name }} {{ $doctor->patronym }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- ФИО и дата рождения --}}
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Фамилия</label>
                        <input class="form-control" name="surname" value="{{ $record->patient->surname }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Имя</label>
                        <input class="form-control" name="name" value="{{ $record->patient->name }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Отчество</label>
                        <input class="form-control" name="patronym" value="{{ $record->patient->patronym }}" required>
                    </div>
                </div>

                {{-- Дата рождения и приёма --}}
                <div class="row g-3 pt-3">
                    <div class="col-md-6">
                        <label class="form-label">Дата рождения</label>
                        <input class="form-control" type="date" name="birth_at" value="{{ \Carbon\Carbon::parse($record->patient->birth_at)->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Дата и время приёма</label>
                        <input class="form-control" type="datetime-local" name="appointment_datetime" value="{{ \Carbon\Carbon::parse($record->for_datetime)->format('Y-m-d\TH:i') }}" required>
                    </div>
                </div>

                {{-- Адреса --}}
                <div class="row g-3 pt-3">
                    <div class="col-md-6">
                        <label class="form-label">Адрес регистрации</label>
                        <input class="form-control" name="address_registration" value="{{ $record->patient->address_registration }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Адрес проживания</label>
                        <input class="form-control" name="address_residence" value="{{ $record->patient->address_residence }}" required>
                    </div>
                </div>

                {{-- Паспорт --}}
                <div class="row g-3 pt-3">
                    <div class="col-md-4">
                        <label class="form-label">Серия паспорта</label>
                        <input class="form-control" name="serial" value="{{ $record->patient->serial }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Номер паспорта</label>
                        <input class="form-control" name="number" value="{{ $record->patient->number }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Код подразделения</label>
                        <input class="form-control" name="department_code" value="{{ $record->patient->department_code }}" required>
                    </div>
                </div>

                {{-- Кем и когда выдан --}}
                <div class="row g-3 pt-3">
                    <div class="col-md-6">
                        <label class="form-label">Кем выдан</label>
                        <input class="form-control" name="issued_by" value="{{ $record->patient->issued_by }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Дата выдачи</label>
                        <input class="form-control" type="date" name="issued_at" value="{{ \Carbon\Carbon::parse($record->patient->issued_at)->format('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="row align-items-center mt-4">
                    <div class="col-md-8">
                        <span class="text-danger fs-6 pb-1 d-inline-block border-bottom border-danger w-100">
                            <i class="bi bi-info-circle"></i> Все поля обязательны к заполнению.
                        </span>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="submit" class="btn btn-success">Сохранить изменения</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
