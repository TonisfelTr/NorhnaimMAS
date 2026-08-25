@extends('layouts.welcome')
@section('title', "Статья \"{$article->name}\"")
@section('body_class', 'nh-public-page nh-article-page')

@section('page_header')
    <section class="nh-public-hero nh-public-hero--article">
        <div class="nh-public-shell nh-article-heading">
            <div class="nh-public-kicker">Научная библиотека</div>
            <h1>{{ $article->name }}</h1>
            <div class="nh-article-heading__meta">
                <span>{{ $article->created_at }}</span>
                <span>{{ $article->authors }}</span>
            </div>
        </div>
    </section>
@endsection

@section('main')
    <section class="nh-public-section nh-public-section--article">
        <div class="nh-public-shell">
            <article class="nh-prose-card">
                <div class="nh-prose">
                    {{ $article->content }}
                </div>
                <footer class="nh-prose-card__footer">
                    <div><strong>Авторы:</strong> {{ $article->authors }}</div>
                    <div class="nh-tags">
                        @foreach($article->hashtags()->get() as $hashtag)
                            <a href="{{ route('main.articles') }}?search={{ $hashtag->hashtag }}">#{{ $hashtag->hashtag }}</a>
                        @endforeach
                    </div>
                </footer>
            </article>
        </div>
    </section>
@endsection
