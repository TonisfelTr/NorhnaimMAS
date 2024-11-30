@extends('layouts.admin')
@section('title', "Создание категории")
@section('assets')
@endsection
@section('main')
    <div class="container-fluid">
        <h1>Создание категории</h1>
        {{ Breadcrumbs::render('admin.blog.categories.new') }}
        <form class="row" action="{{ route('admin.blog.categories.create') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="col-md-6">
                <div class="pt-3">
                    <label for="name" class="form-label">Название</label>
                    <input id="name" name="name" class="form-control" type="text">
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3">
                    <button class="btn btn-outline-success btn-sm" type="submit"><i
                            class="bi bi-box-arrow-down"></i> Создать категорию
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
