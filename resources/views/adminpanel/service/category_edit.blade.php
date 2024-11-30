@extends('layouts.admin')
@section('title', "Редактирование категории {$category->name}")
@section('assets')
@endsection
@section('main')
    <div class="container-fluid">
        <h1>Редактирование категории {{ $category->name }}</h1>
        {{ Breadcrumbs::render('admin.blog.categories.edit') }}
        <form class="row" action="{{ route('admin.blog.categories.save', $category->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="col-md-6">
                <div class="pt-3">
                    <label for="name" class="form-label">Название</label>
                    <input id="name" name="name" class="form-control" type="text" value="{{ old('name') ?? $category->name }}">
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3">
                    <button class="btn btn-outline-success btn-sm" type="submit"><i
                            class="bi bi-box-arrow-down"></i> Сохранить изменения
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
