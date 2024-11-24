@extends('layouts.admin')
@section('title', 'Диагнозы')
@section('assets')
@endsection

@section('main')
    <div class="container-fluid">
        <h1>Диагнозы</h1>
        {{ Breadcrumbs::render('admin.dictionary.diagnoses') }}
        <div class="col-md-12 mb-3">
            @if(session()->has('status') && session()->get('status') == 'diagnoses.success')
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
                <form class="right-side" method="get">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Поиск" aria-label="Поиск" aria-describedby="basic-addon1" name="search">
                        <button class="btn btn-outline-secondary" type="button">Искать</button>
                    </div>
                </form>
            </div>
        </div>
        @if (session()->get('status') == 200)
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif
        <form action="#" method="post">
            @csrf
            <table class="table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select_all" title="Выделить все"></th>
                        <th>
                            ID
                        </th>
                        <th>
                            Шифр
                        </th>
                        <th>
                            Название
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @if($diagnoses->isEmpty())
                    <tr>
                        <td colspan="5" class="text-center">
                            <i class="bi bi-info-circle-fill"></i> Не зарегистрировано ни одного диагноза
                        </td>
                    </tr>
                @else
                    @foreach($diagnoses as $diagnose)
                        <tr>
                            <td><input type="checkbox" name="selected[]" title="Выделить" value="{{ $diagnose->id }}" /></td>
                            <td>{{ $diagnose->id }}</td>
                            <td>{{ $diagnose->code }}</td>
                            <td>{{ $diagnose->title }}</td>
                            <td>
                                <a class="btn btn-info btn-sm" href="{{ route('admin.dictionary.diagnoses.edit', $diagnose->id) }}">Редактирование</a>
                                <button class="btn btn-danger btn-sm diagnose-delete-btn" type="button" data-bs-toggle="modal" data-bs-target="#diagnose-delete-modal">Удалить</button>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
            {{ $diagnoses->links('pagination::bootstrap-5') }}
            <x-danger-dialog-component title="Удаление" message="Вы действительно хотите удалить эти группы?" button=".diagnose-delete-btn" message-box="diagnose-delete-modal"/>
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

