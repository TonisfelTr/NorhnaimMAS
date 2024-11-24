@extends('layouts.admin')
@section('title', 'Регистратура')

@section('assets')
@endsection
@section('main')
    <div class="container-fluid">
        <h1>Регистратура</h1>
        {{ Breadcrumbs::render('admin.dictionary') }}
        <div class="col-md-12 mb-3">
            @if(session()->has('status') && session()->get('status') == 'record.success')
                <div class="alert alert-success">
                    {{ session()->get('message') }}
                </div>
            @endif
            <div class="d-inline-flex justify-content-between w-100">
                <div class="left-side">
                    <button id="dropdownHeadBulkAction" class="btn btn-outline-warning dropdown-button">C выделенными</button>
                    <div id="dropdownHeadBulkContent" class="dropdown-content" style="display: none">
                        <a href="#">Отменить запись</a>
                    </div>
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
        <form action="#" method="post">
            <table class="table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select_all" title="Выделить все"></th>
                        <th>
                            ID
                        </th>
                        <th>
                            Пациент
                        </th>
                        <th>
                            Доктор
                        </th>
                        <th>
                            Запись на...
                        </th>
                        <th>
                            Был на приёме?
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @if($records->isEmpty())
                    <tr>
                        <td colspan="6" class="text-center"><i class="bi bi-info-circle-fill"></i> Нет записей в регистратуру.</td>
                    </tr>
                @endif
                @foreach($records as $record)
                    <tr>
                        <td><input type="checkbox" name="selected[]" title="Выделить" value="{{ $record->id }}" /></td>
                        <td>{{ $record->id }}</td>
                        <td>{{ $record->patient->fullName() }}</td>
                        <td>{{ $record->doctor->fullName() }}</td>
                        <td>{{ $record->for_datetime }}</td>
                        <td><{{ $record->appointment }}/td>
                        <td>
                            <a class="btn btn-info btn-sm" href="{{ route('admin.registry.edit', $record->id) }}">Редактирование</a>
                            <button class="btn btn-danger btn-sm registry-delete-btn" type="button" data-bs-toggle="modal" data-bs-target="#registry-delete-modal">Отменить запись</button>
                        </td>
                        <td></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $records->links() }}
            <x-danger-dialog-component title="Удаление" message="Вы действительно хотите отменить эти группы?" button=".registry-delete-btn" message-box="registry-delete-modal"/>
        </form>
    </div>
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
