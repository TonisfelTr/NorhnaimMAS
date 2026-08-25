@extends('layouts.welcome')
@section('title', 'Научные статьи')
@section('body_class', 'nh-public-page nh-library-page')

@section('page_header')
    <section class="nh-public-hero">
        <div class="nh-public-shell nh-public-hero__grid">
            <div>
                <div class="nh-public-kicker">Библиотека Норхнейма</div>
                <h1>Научная библиотека</h1>
                <p>
                    Мы собираем различные статьи и Вы, если вы врач, можете опубликовать свои труды, основывающиеся на своих
                    навыках. Также, здесь могут быть опубликованы труды учёных - исследования, в том числе переводы с других
                    языков.
                </p>
            </div>
            <form class="nh-search" method="get" action="">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Поиск по библиотеке" value="{{ request()->get('search') }}">
                <button type="submit">Найти</button>
            </form>
        </div>
    </section>
@endsection

@section('main')
    <section class="nh-public-section">
        <div class="nh-public-shell">
            @if ($articles->isNotEmpty())
                <div class="nh-reading-list">
                    @foreach($articles as $article)
                        <article class="nh-reading-card">
                            <div class="nh-reading-card__meta">
                                <span>{{ $article->created_at }}</span>
                                <span>{{ $article->user->login }}</span>
                            </div>
                            <a class="nh-reading-card__title" href="{{ route('main.articles.show', $article->id) }}">
                                {{ $article->name }}
                            </a>
                            <p>{{ Str::limit(strip_tags($article->content), 190) }}</p>
                            <div class="nh-reading-card__footer">
                                <div class="nh-tags">
                                    @foreach ($article->hashtags()->get() as $hashtag)
                                        <span>#{{ $hashtag->hashtag }}</span>
                                    @endforeach
                                </div>
                                <span class="nh-reading-card__author">{{ $article->authors }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="nh-pagination">{{ $articles->links('pagination::bootstrap-5') }}</div>
            @else
                <div class="nh-empty-state">
                    <i class="bi bi-journal-x"></i>
                    <h2>Материалы не найдены</h2>
                    <p>Попробуйте изменить поисковый запрос или вернуться к библиотеке позже.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
