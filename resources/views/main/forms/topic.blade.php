@extends('layouts.welcome')
@section('title', $topic->name)
@section('body_class', 'nh-public-page nh-topic-page')

@section('page_header')
    <section class="nh-public-hero nh-public-hero--article">
        <div class="nh-public-shell nh-article-heading">
            <div class="nh-public-kicker">Блог Норхнейма</div>
            <h1>{{ $topic->name }}</h1>
            <div class="nh-article-heading__meta">
                <span>{{ $topic->created_at }}</span>
                <span>{{ $topic->user->login }}</span>
            </div>
        </div>
    </section>
@endsection

@section('main')
    <section class="nh-public-section nh-public-section--article">
        <div class="nh-public-shell">
            <article class="nh-prose-card">
                <div class="nh-prose">{!! $topic->content !!}</div>
                @if($topic->created_at != $topic->updated_at)
                    <footer class="nh-prose-card__footer">
                        <span>Изменено {{ $topic->updated_at }}</span>
                    </footer>
                @endif
            </article>
        </div>
    </section>
@endsection
