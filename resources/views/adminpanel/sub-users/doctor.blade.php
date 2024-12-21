@extends('layouts.admin')
@section('title', 'Доктора')
@section('assets')
    @vite(['resources/js/mass_delete.js'])
@endsection
@section('main')
    <h1>Доктора</h1>
    {{ Breadcrumbs::render('admin.users.doctors') }}
    <div class="col-md-12 mb-3">
        <div class="d-inline-flex justify-content-between w-100">
            <div class="left-side">
                <button id="dropdownHeadBulkAction" class="btn btn-outline-warning dropdown-button">C выделенными</button>
                <div id="dropdownHeadBulkContent" class="dropdown-content" style="display: none; position: absolute; background: #fff; border: 1px solid #ddd; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); z-index: 1000;">
                    @permission('doctors_remove')
                    <button type="button" class="dropdown-item bulk-action-btn" data-action="{{ route('admin.users.doctors.mass-delete') }}" style="width: 100%; text-align: left; padding: 10px; border: none; background: none; cursor: pointer;">
                        Удалить
                    </button>
                    @endpermission
                </div>
                @permission('doctors_add')
                    <a class="btn btn-success" href="{{ route('admin.users.doctors.new') }}"><i class="bi bi-file-plus"></i> Создать запись</a>
                @endpermission
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
                Адрес работы
            </th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @if($doctors->isEmpty())
            <tr>
                <td colspan="6" class="text-center"><i class="bi bi-info-circle-fill"></i> Нет записей</td>
            </tr>
        @else
            @foreach($doctors as $doctor)
                <tr>
                    <td><input type="checkbox" name="selected[]" title="Выделить" value="{{ $doctor->id }}" /></td>
                    <td>{{ $doctor->id }}</td>
                    <td>{{ $doctor->fullName() }}</td>
                    <td>{{ $doctor->birth_at }}</td>
                    <td>{{ $doctor->address_job }}</td>
                    <td>
                        @permission('doctors_edit')
                            <a class="btn btn-light btn-sm" href="{{ route('admin.users.doctors.edit', $doctor->id) }}"><i class="bi bi-pen"></i></a>
                        @endpermission
                        @permission('doctors_remove')
                        <a class="btn btn-light btn-sm delete-btn" href="{{ route('admin.users.doctors.delete', $doctor->id) }}">
                            <i class="bi bi-trash"></i>
                        </a>
                        @endpermission
                    </td>
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>
        {{ $doctors->links('pagination::bootstrap-5') }}
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
<x-danger-dialog-component title="Удаление" message="Вы действительно хотите удалить этих докторов?" button=".delete-btn" message-box="delete-modal"/>
