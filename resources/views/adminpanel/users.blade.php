@extends('layouts.admin')
@section('title', 'Пользователи')
@section('assets')
    @vite(['resources/js/mass_delete.js'])
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
        </div>
        <div class="d-inline-flex justify-content-between w-100">
            <div class="left-side">
                <button id="dropdownHeadBulkAction" class="btn btn-outline-warning dropdown-button">C выделенными</button>
                <div id="dropdownHeadBulkContent" class="dropdown-content" style="display: none; position: absolute; background: #fff; border: 1px solid #ddd; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); z-index: 1000;">
                    <button type="button" class="dropdown-item bulk-action-btn delete-btn" data-action="{{ route('admin.users.mass-delete') }}" style="width: 100%; text-align: left; padding: 10px; border: none; background: none; cursor: pointer;">
                        Удалить
                    </button>
                </div>
                <a class="btn btn-success" href="{{ route('admin.users.create') }}"><i class="bi bi-file-plus"></i> Создать запись</a>
            </div>
            <div class="right-side">
                <form class="input-group mb-3" method="get" enctype="multipart/form-data" action="{{ route('admin.users') }}">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input name="search" type="text" class="form-control" placeholder="Поиск" aria-label="Поиск" aria-describedby="basic-addon1" value="{{ request()->get('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">Искать</button>
                </form>
            </div>
        </div>
        <form id="bulk-action-form" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="bulk-method" value="POST">
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
                    @forelse($users as $user)
                    <tr>
                        <td><input type="checkbox" name="selected[]" title="Выделить" value="{{ $user->id }}" /></td>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->login }}</td>
                        <td>{{ $user->getUserType() }}</td>
                        <td>{{ $user->group->name }}</td>
                        <td>
                            <a class="btn btn-light btn-sm" href="{{ route('admin.users.edit', $user->id) }}"><i class="bi bi-pen"></i></a>
                            @if ($user->id != 1)
                                <a class="btn btn-light btn-sm delete-btn" href="{{ route('admin.users.delete', $user->id) }}">
                                    <i class="bi bi-trash"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td class="text-center" colspan="7"><i class="bi bi-info-circle-fill"></i> Записей не найдено.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $users->links() }}
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
<x-danger-dialog-component title="Удаление" message="Вы действительно хотите удалить этих пользователей?" button=".delete-btn" message-box="delete-modal"/>
