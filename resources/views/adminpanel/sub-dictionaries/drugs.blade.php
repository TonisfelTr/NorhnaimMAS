@extends('layouts.admin')
@section('title', 'Лекарства')
@section('assets')
@endsection

@section('main')
    <div class="container-fluid">
        <h1>Лекарства</h1>
        {{ Breadcrumbs::render('admin.dictionary.drugs') }}
        <div class="col-md-12 mb-3">
            @if(session()->has('status') && session()->get('status') == 'drugs.success')
                <div class="alert alert-success">
                    {{ session()->get('message') }}
                </div>
            @endif
            <div class="d-inline-flex justify-content-between w-100">
                <div class="left-side">
                    <button id="dropdownHeadBulkAction" class="btn btn-outline-warning dropdown-button">C выделенными</button>
                    <div id="dropdownHeadBulkContent" class="dropdown-content" style="display: none">
                        <a href="#" id="popup-mass-delete-button" data-bs-toggle="modal" data-bs-target="#drug-delete-modal">Удалить</a>
                    </div>
                    <a class="btn btn-success" href="{{ route('admin.dictionary.drugs.new') }}">
                        <i class="bi bi-file-plus"></i> Добавить лекарство
                    </a>
                </div>
                <form class="right-side" method="get">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Поиск" aria-label="Поиск" aria-describedby="basic-addon1" name="search" value="{{ request()->get('search') }}">
                        <button class="btn btn-outline-secondary" type="button">Искать</button>
                    </div>
                </form>
            </div>
        </div>
        <form action="{{ route("admin.dictionary.drugs.delete.mass") }}" method="post" id="table-form">
            @csrf
            <table class="table">
                <thead>
                <tr>
                    <th><input type="checkbox" id="select_all" title="Выделить все"></th>
                    <th>
                        ID
                    </th>
                    <th>
                        Непатентованное название
                    </th>
                    <th>
                        Группа
                    </th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @if($drugs->isEmpty())
                    <tr>
                        <td colspan="5" class="text-center">
                            <i class="bi bi-info-circle-fill"></i> Не зарегистрировано ни одного диагноза
                        </td>
                    </tr>
                @else
                    @foreach($drugs as $drug)
                        <tr>
                            <td><input type="checkbox" name="selected[]" title="Выделить" value="{{ $drug->id }}" /></td>
                            <td>{{ $drug->id }}</td>
                            <td>{{ $drug->name }}</td>
                            <td>{{ $drug->groupName() }}</td>
                            <td>
                                <a class="btn btn-light btn-sm" href="{{ route('admin.dictionary.drugs.edit', $drug->id) }}"><i class="bi bi-pen"></i></a>
                                <a class="btn btn-light btn-sm drug-delete-btn" href="{{ route('admin.dictionary.drugs.delete', $drug->id) }}"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
            {{ $drugs->links('pagination::bootstrap-5') }}
            <x-danger-dialog-component title="Удаление" message="Вы действительно хотите удалить эти лекарства?" button=".drug-delete-btn" message-box="drug-delete-modal"/>
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
            const dropdownContent = document.getElementById("dropdownHeadBulkContent");
            dropdownButton.addEventListener('click', () => {
                dropdownContent.style.display = dropdownContent.style.display === "none" ? "block" : "none";
            });
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
