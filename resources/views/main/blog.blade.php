@extends('layouts.welcome')
@section('title', 'Блог')
@section('assets')
    <script src="{{ asset('assets/js/blog-searching.js') }}" defer></script>
@endsection
@section('main')
    <section class="blog">
        <div class="container">
            <div class="row pt-4">
                <div class="col-md-8">
                    <h1><span>Блог</span></h1>
                    <p>Тематические обсуждения и новости</p>
                </div>
                <div class="col-md-4">
                    <form class="input-group">
                        <span class="input-group-text border-0 bg-transparent magnifier-block">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 with-btn-filter" name="search" placeholder="Поиск..." aria-label="Поиск" />
                        <button class="btn btn-filter" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                            <i class="bi bi-filter"></i>
                        </button>
                        <button class="btn btn-outline-primary" type="submit">
                            Искать
                        </button>
                        <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
                            <div class="offcanvas-header">
                                <h5 class="offcanvas-title" id="filterOffcanvasLabel">Фильтры</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
                            </div>
                            <div class="offcanvas-body">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Категория</label>
                                    <select class="form-select" id="category">
                                        <option value="">Все категории</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="dateRangeStart" class="form-label">Диапазон дат</label>
                                    <input type="date" class="form-control" id="dateRangeStart">
                                    <input type="date" class="form-control mt-2" id="dateRangeEnd">
                                </div>
                                <div class="mb-3">
                                    <label for="author" class="form-label">Автор</label>
                                    <input type="text" class="form-control" id="author">
                                </div>
                                <button type="button" class="btn btn-primary w-100">Применить</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <section class="categories">
        <div class="container-fluid">
            <div class="row px-4 categories-list">
                <div class="d-flex justify-content-center">
                    @foreach ($categories as $category)
                        <a class="link-category @if($category_id == $category->id) active @endif" href="{{ route('main.blog.category', $category->id) }}">{{ $category->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @if($topics->isNotEmpty())
    <section class="topics">
        <div class="container">
            <div class="row pt-4">
                @foreach($topics as $topic)
                    <div class="col-md-6 pt-4">
                        <a class="blog__card link-remove-underline" href="{{ route('main.blog.topic', $topic->id) }}">
                            <div class="d-inline-flex">
                                <picture class="blog__image">
                                    <source srcset="{{ $topic->webpPhoto() }}" type="image/webp">
                                    <img src="{{ $topic->photo() }}">
                                    <p class="text-center link-secondary pt-3">Admin</p>
                                </picture>
                                <div class="col-md-6 px-3">
                                    <h4>{{ $topic->name }}</h4>
                                    <p><a href="{{ route('main.blog.category', $topic->topics_category_id) }}">{{ $topic->topics_category->name }}</a></p>
                                    <p>{{ $topic->description }}</p>
                                    <p class="text-secondary">{{ $topic->created_at }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            {{ $topics->links('pagination::bootstrap-5') }}
        </div>
    </section>
    @else
        <div class="container">
            <div class="row not-found">
                <div class="col-md-3">
                    <picture>
                        <source srcset="{{ asset('assets/images/doctors/no-found-doctor.webp') }}" type="image/webp">
                        <img src="{{ asset('assets/images/doctors/no-found-doctor.png') }}">
                    </picture>
                </div>
                <div class="col-md-9 pt-5">
                    <h1>Здесь пусто</h1>
                    <p>Не смогли ничего найти. Попробуйте позже, возможно тогда мы добавим записи.</p>
                </div>
            </div>
        </div>
        @endif
@endsection
