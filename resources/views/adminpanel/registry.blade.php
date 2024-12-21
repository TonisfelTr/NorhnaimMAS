@extends('layouts.admin')
@section('title', 'Регистратура')
@section('assets')
    @vite(['resources/js/mass_delete.js'])
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
        </div>
        <div class="d-inline-flex justify-content-between w-100">
            <div class="left-side">
                <button id="dropdownHeadBulkAction" class="btn btn-outline-warning dropdown-button">C выделенными</button>
                <div id="dropdownHeadBulkContent" class="dropdown-content" style="display: none; position: absolute; background: #fff; border: 1px solid #ddd; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); z-index: 1000;">
                    <button type="button" class="dropdown-item bulk-action-btn" data-action="{{ route('admin.dictionary.registration.mass-delete') }}" style="width: 100%; text-align: left; padding: 10px; border: none; background: none; cursor: pointer;">
                        Отменить запись
                    </button>
                </div>
                <a class="btn btn-success" href="{{ route('admin.dictionary.registration.create') }}"><i class="bi bi-file-plus"></i> Создать запись</a>
            </div>
            <div class="right-side">
                <form class="input-group mb-3" method="get" enctype="multipart/form-data" action="{{ route('admin.dictionary.registration') }}">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input name="search" type="text" class="form-control" placeholder="Поиск" aria-label="Поиск" aria-describedby="basic-addon1" value="{{ request()->get('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">Искать</button>
                </form>
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
                        <td colspan="7" class="text-center"><i class="bi bi-info-circle-fill"></i> Нет записей в регистратуру.</td>
                    </tr>
                @endif
                @foreach($records as $record)
                    <tr>
                        <td><input type="checkbox" name="selected[]" title="Выделить" value="{{ $record->id }}" /></td>
                        <td>{{ $record->id }}</td>
                        <td>{{ $record->patient->fullName() }}</td>
                        <td>{{ $record->doctor->fullName() }}</td>
                        <td>{{ $record->for_datetime }}</td>
                        <td>{{ $record->appointment ? 'принят' : 'не принят' }}</td>
                        <td>
                            <a class="btn btn-light btn-sm" href="{{ route('admin.dictionary.registration.edit', $record->id) }}"><i class="bi bi-pen"></i></a>
                            <a class="btn btn-light btn-sm delete-btn" href="{{ route('admin.dictionary.registration.delete', $record->id) }}">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $records->links('pagination::bootstrap-5') }}
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
<x-danger-dialog-component title="Удаление" message="Вы действительно хотите удалить эти записи?" button=".delete-btn" message-box="delete-modal"/>
