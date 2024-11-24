@extends('layouts.admin')
@section('title', 'Пользователи')
@section('assets')

@endsection

@section('main')
    <div class="container-fluid">
        <h1>Пользователи</h1>
        {{ Breadcrumbs::render('admin.users') }}
        <div class="col-md-12 mb-3">
            @if(session()->has('status') && session()->get('status') == 'users.success')
                <div class="alert alert-success">
                    {{ session()->get('message') }}
                </div>
            @endif
            <div class="d-inline-flex justify-content-between w-100">
                <div class="left-side">
                    <button id="dropdownHeadBulkAction" class="btn btn-outline-warning dropdown-button">C выделенными</button>
                    <div id="dropdownHeadBulkContent" class="dropdown-content" style="display: none">
                        <a href="#">Удалить</a>
                        <a href="#">Заблокировать</a>
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
                            Электронная почта
                        </th>
                        <th>
                            Логин
                        </th>
                        <th>
                            Тип пользователя
                        </th>
                        <th>
                            Группа
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td><input type="checkbox" name="selected[]" title="Выделить" value="{{ $user->id }}" /></td>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->login }}</td>
                        <td>{{ $user->getUserType() }}</td>
                        <td>{{ $user->group->name }}</td>
                        <td>
                            <a class="btn btn-info btn-sm" href="{{ route('admin.user_edit', $user->id) }}">Редактирование</a>
                            <button class="btn btn-danger btn-sm user-delete-btn" type="button">Удалить</button>
                        </td>
                        <td></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $users->links() }}
            <x-danger-dialog-component title="Удаление" message="Вы действительно хотите удалить этих пользователей?" button=".user-delete-btn" message-box="user-delete-modal"/>
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
