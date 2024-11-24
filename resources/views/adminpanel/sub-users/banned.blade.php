@extends('layouts.admin')
@section('title', 'Блокировка пользователей')
@section('assets')

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
            @endif
            <div class="d-inline-flex justify-content-between w-100">
                <div class="left-side">
                    <button id="dropdownHeadBulkAction" class="btn btn-outline-warning dropdown-button">C выделенными</button>
                    <div id="dropdownHeadBulkContent" class="dropdown-content" style="display: none">
                        <a href="#">Разблокировать</a>
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
                                <td>{{ $banned->rule->point }}</td>
                                <td>
                                    <button class="btn btn-danger btn-sm user-delete-btn" type="button" data-bs-toggle="modal" data-bs-target="#unban-user-modal">Разбанить</button>
                                </td>
                                <td></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            {{ $banneds->links() }}
            <x-danger-dialog-component title="Удаление" message="Вы действительно хотите разблокировать этих пользователей?" button=".unban-user-btn" message-box="unban-user-modal"/>
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
