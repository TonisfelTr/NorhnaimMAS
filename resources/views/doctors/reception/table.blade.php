@extends('doctors.reception')
@section('title', 'Текущая регистратура')
@section('assets')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('confirmArchiveModal');
            modal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const recordId = button.getAttribute('data-record-id');

                const form = document.getElementById('archiveForm');
                form.action = `reception/delete/${recordId}`;
            });
        });
    </script>
@endsection
@section('sub-main')
    <div class="p-4">
        <h1>Регистратура</h1>
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
                <div>
                    <strong class="mx-2">Записей на сегодня: </strong><span>{{ $receptions->count() }}</span>
                </div>
                <div>
                    <strong class="mx-2">Записи в ожидании: </strong><span>{{ $futureReceptions }}</span>
                </div>
            </div>
            <table class="styled-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Дата рождения</th>
                    <th>Врач</th>
                    <th>Адрес прописки</th>
                    <th>Адрес проживания</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse($receptions as $reception)
                    <tr>
                        <td>{{ $reception->id }}</td>
                        <td>{{ $reception->patient->fullname }}</td>
                        <td>{{ $reception->patient->birth_at }}</td>
                        <td>{{ $reception->doctor->profession }} {{ $reception->doctor->surname }} {{ $reception->doctor->name }} {{ $reception->doctor->patronym }}</td>
                        <td>{{ $reception->patient->address_registration }}</td>
                        <td>{{ $reception->patient->address_residence }}</td>
                        <td class="text-center">
                            <a href="{{ route('doctors.patients.medical_card', $reception->patient->id) }}"
                               class="text-decoration-none mx-1"
                               title="Перейти к карточке пациента">
                                <i class="bi bi-person-lines-fill"></i>
                            </a>

                            <a href="{{ route('doctors.reception.edit', $reception->id) }}"
                               class="text-decoration-none mx-1"
                               title="Редактировать запись">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <button type="button"
                                    class="btn btn-link text-danger p-0 m-0"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmArchiveModal"
                                    data-record-id="{{ $reception->id }}"
                                    title="Перенести в архив">
                                <i class="bi bi-archive"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">
                            <i class="bi bi-info-circle-fill"></i> Пока нет записей в регистратуру
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="confirmArchiveModal" tabindex="-1" aria-labelledby="confirmArchiveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="archiveForm" action="">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmArchiveModalLabel">Подтверждение действия</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        Вы действительно хотите перенести запись в архив?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-danger">Да, перенести</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
