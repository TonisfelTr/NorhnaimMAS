@extends('layouts.admin')
@section('title', 'Пациенты')
@section('assets')
    @vite(['resources/js/mass_delete.js'])
@endsection
@section('main')
    <h1>Пациенты</h1>
    {{ Breadcrumbs::render('admin.users.patients') }}
    <div class="col-md-12 mb-3">
        @if(session()->has('status') && session()->get('status') == 'patients.success')
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @elseif($errors->isNotEmpty())
            <div class="alert alert-danger">
                Вышли следующие ошибки:
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="d-inline-flex justify-content-between w-100">
            <div class="left-side">
                <button id="dropdownHeadBulkAction" class="btn btn-outline-warning dropdown-button">C выделенными</button>
                <div id="dropdownHeadBulkContent" class="dropdown-content" style="display: none">
                    <a href="#">Удалить</a>
                    <a href="#">Заблокировать</a>
                </div>
                <a class="btn btn-success" href="{{ route('admin.users.patients.new') }}"><i class="bi bi-file-plus"></i> Создать запись</a>
            </div>
            <div class="right-side">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" placeholder="Поиск" aria-label="Поиск" aria-describedby="basic-addon1">
                    <button class="btn btn-outline-secondary" type="button">Искать</button>
                </div>
            </div>
        </div>
    </div>
    <form id="bulk-action-form" method="post" enctype="multipart/form-data">
        @csrf
        <table class="table">
            <thead>
            <tr>
                <th><input type="checkbox" id="select_all" title="Выделить все"></th>
                <th>
                    ID
                </th>
                <th>
                    Полное имя
                </th>
                <th>
                    День рождения
                </th>
                <th>
                    Диагноз
                </th>
                <th>
                    Инвалидность
                </th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @if($patients->isEmpty())
                <tr>
                    <td colspan="6" class="text-center"><i class="bi bi-info-circle-fill"></i> Нет записей</td>
                </tr>
            @else
                @foreach($patients as $patient)
                    <tr>
                        <td><input type="checkbox" name="selected[]" title="Выделить" value="{{ $patient->id }}" /></td>
                        <td>{{ $patient->id }}</td>
                        <td>{{ $patient->fullName() }}</td>
                        <td>{{ $patient->birth_at}}</td>
                        <td>{{ $patient->diagnose?->decipher() ?? 'не поставлен' }}</td>
                        <td>{{ $patient->disability ? 'есть': 'нет'}}</td>
                        <td>
                            <a class="btn btn-light btn-sm" href="{{ route('admin.users.patients.edit', $patient->id) }}"><i class="bi bi-pen"></i></a>
                            <button class="btn btn-light btn-sm delete-btn" type="button" data-bs-toggle="modal" data-bs-target="#delete-modal"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
        {{ $patients->links('pagination::bootstrap-5') }}
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('select_all').addEventListener('change', function () {
                let checkboxes = document.querySelectorAll('input[type="checkbox"][name="selected[]"]');

                checkboxes.forEach(e => {
                    if (this.checked && !e.checked) {
                        e.checked = true;
                    } else if (!this.checked && e.checked) {
                        e.checked = false;
                    }
                });
            });

            const dropdownButton = document.getElementById("dropdownHeadBulkAction");

            // Получаем элемент выпадающего списка
            const dropdownContent = document.getElementById("dropdownHeadBulkContent");

            // Добавляем обработчик события клика по кнопке
            dropdownButton.addEventListener('click', () => {
                // Переключаем отображение выпадающего списка
                dropdownContent.style.display = dropdownContent.style.display === "none" ? "block" : "none";
            });

            // Добавляем обработчик клика по документу, чтобы закрыть dropdown, если кликнули вне его
            window.addEventListener('click', (event) => {
                if (!event.target.matches('.dropdown-button')) {
                    if (dropdownContent.style.display === "block") {
                        dropdownContent.style.display = "none";
                    }
                }
            });
        });
    </script>
@endsection
<x-danger-dialog-component title="Удаление" message="Вы действительно хотите удалить этих пациентов?" button=".delete-btn" message-box="delete-modal"/>
