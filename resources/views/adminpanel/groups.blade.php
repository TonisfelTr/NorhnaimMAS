@extends('layouts.admin')
@section('title', 'Группы')

@section('assets')
@endsection
@section('main')
    <div class="container-fluid">
        <h1>Группы</h1>
        {{ Breadcrumbs::render('admin.groups') }}
        <div class="col-md-12 mb-3">
            @if(session()->has('status') && session()->get('status') == 'groups.success')
                <div class="alert alert-success">
                    {{ session()->get('message') }}
                </div>
            @endif
            <div class="d-inline-flex justify-content-between w-100">
                <div class="left-side">
                    <button id="dropdownHeadBulkAction" class="btn btn-outline-warning dropdown-button">C выделенными</button>
                    <div id="dropdownHeadBulkContent" class="dropdown-content" style="display: none">
                        <a href="#">Удалить</a>
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
                        Название
                    </th>
                    <th>
                        Количество участников
                    </th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($groups as $group)
                    <tr>
                        <td><input type="checkbox" name="selected[]" title="Выделить" value="{{ $group->id }}" /></td>
                        <td>{{ $group->id }}</td>
                        <td>{{ $group->name }}</td>
                        <td>{{ $group->getMembersCount() }}</td>
                        <td>
                            <a class="btn btn-info btn-sm" href="{{ route('admin.groups.edit', $group->id) }}">Редактирование</a>
                            @if(!in_array($group->id, [1,2,3]))
                                <button class="btn btn-danger btn-sm user-delete-btn" type="button" data-bs-toggle="modal" data-bs-target="#group-delete-modal">Удалить</button>
                            @endif
                        </td>
                        <td></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $groups->links() }}
            <x-danger-dialog-component title="Удаление" message="Вы действительно хотите удалить эти группы?" button=".group-delete-btn" message-box="group-delete-modal"/>
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
