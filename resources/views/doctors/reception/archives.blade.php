@extends('doctors.reception')
@section('title', 'Архив регистратуры')
@section('assets')
@endsection
@section('sub-main')
    <div class="p-4">
        <h1>Архивные записи</h1>
        <div class="row p-3">
            <form class="border rounded p-3 shadow-sm col-md-8 col-lg-6" method="get">
                <h4 class="mb-3">Поиск пациента</h4>
                <div class="row">
                    <div class="col-md-12 mb-2">
                        <label for="fullname" class="form-label">Полное имя (можно инициалы)</label>
                        <input id="fullname" name="fullname" class="form-control form-control-sm"
                               placeholder="Полное имя (можно инициалы)" value="{{ request('fullname') }}">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label for="doctor_id" class="form-label">Врач</label>
                        <select id="doctor_id" name="doctor_id" class="form-control form-control-sm">
                            <option value="">— Все врачи —</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->profession }} {{ $doctor->surname }} {{ $doctor->name }} {{ $doctor->patronym }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label for="birth_at" class="form-label">Дата рождения</label>
                        <input id="birth_at" name="birth_at" class="form-control form-control-sm" type="date"
                               value="{{ request('birth_at') }}">
                    </div>
                    <div class="col-md-12 mb-2">
                        <label for="address_residence" class="form-label">Место прописки</label>
                        <input id="address_residence" name="address_residence" class="form-control form-control-sm"
                               placeholder="Место прописки" value="{{ request('address_residence') }}">
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">Поиск</button>
                </div>
            </form>
        </div>
        <div class="row p-3">
            <div class="d-flex flex-column pb-3">
                <h3>Ваши данные</h3>
                <div>
                    <strong class="mx-2">Клиника: </strong>{{ $hostClinic->name }}
                </div>
                <div>
                    <strong class="mx-2">Хозяин аккаунта: </strong><span>{{ $surname }} {{ $name }} {{ $patronym }} ({{ $profession }})</span>
                </div>
            </div>
            <table class="styled-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Дата рождения</th>
                    <th>Врач</th>
                    <th>Дата приёма</th>
                    <th>Принят на приём</th>

                </tr>
                </thead>
                <tbody>
                @forelse($records as $reception)
                    <tr>
                        <td>{{ $reception->id }}</td>
                        <td>{{ $reception->patient->fullname }}</td>
                        <td>{{ $reception->patient->birth_at }}</td>
                        <td>{{ $reception->doctor->profession }} {{ $reception->doctor->surname }} {{ $reception->doctor->name }} {{ $reception->doctor->patronym }}</td>
                        <td>{{ $reception->for_datetime }}</td>
                        <td>
                            @if($reception->deleted_at)
                                <span class="badge text-bg-secondary">Отменено</span>
                            @elseif($reception->appointment)
                                <span class="badge text-bg-info">Посетил</span>
                            @else
                                <span class="badge text-bg-danger">Не посетил</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">
                            <i class="bi bi-info-circle-fill"></i> Архив пуст
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
