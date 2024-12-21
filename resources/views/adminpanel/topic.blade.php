@extends('layouts.admin')
@section('title', "Статьи")
@section('assets')

@endsection
@section('main')
    <div class="container-fluid">
        <h1>Cтатьи</h1>
        {{ Breadcrumbs::render('admin.blog.topics') }}
        <div class="col-md-12 mb-3">
            @if(session()->has('status') && session()->get('status') == 'success')
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
                    <a class="btn btn-success" href="{{ route('admin.groups.new') }}"><i class="bi bi-file-plus"></i> Создать группу</a>
                </div>
                <div class="right-side">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Поиск" aria-label="Поиск" aria-describedby="basic-addon1">
                        <button class="btn btn-outline-secondary" type="button">Искать</button>
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
                                Время создания
                            </th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($topics as $topic)
                            <tr>
                                <td><input type="checkbox" name="selected[]" title="Выделить" value="{{ $topic->id }}" /></td>
                                <td>{{ $topic->id }}</td>
                                <td>{{ $topic->name }}</td>
                                <td>{{ $topic->created_at }}</td>
                                <td>
                                    <a class="btn btn-light btn-sm" href="{{ route('admin.blog.topics.edit', $topic->id) }}"><i class="bi bi-pen"></i></a>
                                    <button class="btn btn-light btn-sm user-delete-btn" type="button" data-bs-toggle="modal" data-bs-target="#group-delete-modal"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{ $topics->links('pagination::bootstrap-5') }}
                    <x-danger-dialog-component title="Удаление" message="Вы действительно хотите удалить эти группы?" button=".group-delete-btn" message-box="group-delete-modal"/>
                </form>
        </div>
    </div>
@endsection
