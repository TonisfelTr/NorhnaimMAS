@extends('layouts.admin')
@section('title', 'Группы')
@section('assets')
    @vite(['resources/js/mass_delete.js'])
@endsection
@section('main')
    <div class="container-fluid">
        <h1>Группы</h1>
        {{ Breadcrumbs::render('admin.groups') }}
        <div class="col-md-12 mb-3">
            @if(session()->has('status') && session()->get('status') == 'group.success')
                <div class="alert alert-success">
                    {{ session()->get('message') }}
                </div>
            @elseif($errors->isNotEmpty())
                <div class="alert alert-danger">
                    Возникли следующие ошибки:
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
                    <div id="dropdownHeadBulkContent" class="dropdown-content" style="display: none; position: absolute; background: #fff; border: 1px solid #ddd; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); z-index: 1000;">
                        @permission('group_remove')
                        <button type="button" class="dropdown-item bulk-action-btn" data-action="{{ route('admin.groups.mass-delete') }}" style="width: 100%; text-align: left; padding: 10px; border: none; background: none; cursor: pointer;">
                            Удалить
                        </button>
                        @endpermission
                    </div>
                    @permission('group_add')
                    <a class="btn btn-success" href="{{ route('admin.groups.new') }}"><i class="bi bi-file-plus"></i> Создать группу</a>
                    @endpermission
                </div>
                <form class="right-side" method="get" enctype="multipart/form-data">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Поиск" aria-label="Поиск" aria-describedby="basic-addon1" name="search">
                        <button class="btn btn-outline-secondary" type="button">Искать</button>
                    </div>
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
                        <td>@if(!in_array($group->id, [1, 2, 3])) <input type="checkbox" name="selected[]" title="Выделить" value="{{ $group->id }}" /> @else <i class="bi bi-ban"></i >@endif</td>
                        <td>{{ $group->id }}</td>
                        <td>{{ $group->name }}</td>
                        <td>{{ $group->getMembersCount() }}</td>
                        <td>
                            @permission('group_edit')
                                <a class="btn btn-light btn-sm" href="{{ route('admin.groups.edit', $group->id) }}"><i class="bi bi-pen"></i></a>
                            @endpermission
                            @permission('group_remove')
                                @if(!in_array($group->id, [1,2,3]))
                                    <a class="btn btn-light btn-sm delete-btn" href="{{ route('admin.groups.delete', $group->id) }}">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                @endif
                            @endpermission
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $groups->links('pagination::bootstrap-5') }}
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
<x-danger-dialog-component title="Удаление" message="Вы действительно хотите удалить эту группу?" button=".delete-btn" message-box="delete-modal"/>
