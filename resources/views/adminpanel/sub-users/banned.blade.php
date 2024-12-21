@extends('layouts.admin')
@section('title', 'Блокировка пользователей')
@section('assets')
    @vite(['resources/js/mass_delete.js'])
@endsection
@section('main')
    <div class="container-fluid">
        <h1>Заблокированные пользователи</h1>
        {{ Breadcrumbs::render('admin.users.banned') }}
        <div class="col-md-12 mb-3">
            @if(session()->has('status') && session()->get('status') == 'banned.success')
                <div class="alert alert-success">
                    {{ session()->get('message') }}
                </div>
            @elseif($errors->isNotEmpty())
                <div class="alert alert-danger">
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
                    @permission('banning_remove')
                        <div id="dropdownHeadBulkContent" class="dropdown-content" style="display: none; position: absolute; background: #fff; border: 1px solid #ddd; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); z-index: 1000;">
                            <button type="button" class="dropdown-item bulk-action-btn" data-action="{{ route('admin.users.banned.mass-delete') }}" style="width: 100%; text-align: left; padding: 10px; border: none; background: none; cursor: pointer;">
                                Удалить
                            </button>
                        </div>
                    @endif
                    @permission('banning_user')
                        <a class="btn btn-success" href="{{ route('admin.users.banned.create') }}"><i class="bi bi-file-plus"></i> Создать запись</a>
                    @endif
                </div>
                <form method="get" enctype="multipart/form-data" class="right-side">
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
                            Пользователь
                        </th>
                        <th>
                            Заблокировал
                        </th>
                        <th>
                            Нарушение правила
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @if($banneds->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center"><i class="bi bi-info-circle-fill"></i> Нет заблокированных пользователей</td>
                        </tr>
                    @else
                        @foreach($banneds as $banned)
                            <tr>
                                <td><input type="checkbox" name="selected[]" title="Выделить" value="{{ $banned->id }}" /></td>
                                <td>{{ $banned->id }}</td>
                                <td>{{ $banned->user->email }}</td>
                                <td>{{ $banned->admin->email }} ({{ $banned->admin->login }})</td>
                                <td>{{ $banned->rule->point }}. {{ $banned->rule->text }}</td>
                                <td>
                                    @permission('banning_edit')
                                        <a class="btn btn-light btn-sm" href="{{ route('admin.users.banned.edit', $banned->id) }}"><i class="bi bi-pen"></i></a>
                                    @endif
                                    @permission('banning_remove')
                                        <a class="btn btn-light btn-sm delete-btn" href="{{ route('admin.users.banned.delete', $banned->id) }}">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            {{ $banneds->links() }}
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
<x-danger-dialog-component title="Удаление" message="Вы действительно хотите разблокировать этих пользователей?" button=".delete-btn" message-box="delete-btn"/>
