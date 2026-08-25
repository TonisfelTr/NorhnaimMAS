@extends('layouts.welcome')
@section('title', 'Блог')
@section('body_class', 'nh-public-page nh-blog-page')
@section('assets')
    <script src="{{ asset('assets/js/blog-searching.js') }}" defer></script>
@endsection

@section('page_header')
    <section class="nh-public-hero">
        <div class="nh-public-shell nh-public-hero__grid">
            <div>
                <div class="nh-public-kicker">Блог</div>
                <h1>Блог</h1>
                <p>Тематические обсуждения и новости</p>
            </div>
            <form class="nh-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Поиск по блогу" aria-label="Поиск">
                <button class="nh-search__filter" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
                    <i class="bi bi-sliders"></i>
                </button>
                <button type="submit">Найти</button>

                <div class="offcanvas offcanvas-end nh-offcanvas" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
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
                        <button type="button" class="nh-public-button nh-public-button--primary w-100">Применить</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('main')
    <section class="nh-category-strip">
        <div class="nh-public-shell">
            <div class="nh-category-strip__scroll">
                @foreach ($categories as $category)
                    <a class="nh-category-pill @if($category_id == $category->id) is-active @endif" href="{{ route('main.blog.category', $category->id) }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="nh-public-section">
        <div class="nh-public-shell">
            @if($topics->isNotEmpty())
                <div class="nh-topic-grid">
                    @foreach($topics as $topic)
                        <a class="nh-topic-card" href="{{ route('main.blog.topic', $topic->id) }}">
                            <picture class="nh-topic-card__media">
                                <source srcset="{{ $topic->webpPhoto() }}" type="image/webp">
                                <img src="{{ $topic->photo() }}" alt="{{ $topic->name }}" loading="lazy">
                            </picture>
                            <div class="nh-topic-card__body">
                                <span class="nh-topic-card__category">{{ $topic->topics_category->name }}</span>
                                <h2>{{ $topic->name }}</h2>
                                <p>{{ $topic->description }}</p>
                                <div class="nh-topic-card__meta">
                                    <span>{{ $topic->created_at }}</span>
                                    <span>Admin</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="nh-pagination">{{ $topics->links('pagination::bootstrap-5') }}</div>
            @else
                <div class="nh-empty-state">
                    <i class="bi bi-chat-square-text"></i>
                    <h2>Публикаций пока нет</h2>
                    <p>В этой категории пока не опубликованы материалы.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
