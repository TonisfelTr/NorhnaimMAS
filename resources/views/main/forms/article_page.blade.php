@extends('layouts.welcome')
@section('title', "Статья \"{$article->name}\"")
@section('assets')
@endsection
@section('main')
    <section class="article-page">
        <div class="container">
            <h1>{{ $article->name }}</h1>
            <div class="py-4">
                {{ $article->content }}
            </div>
            <p class="text-secondary"><strong>Авторы статьи:</strong> {{ $article->authors }}</p>
            <p class="text-secondary">Хештеги: @foreach($article->hashtags()->get() as $hashtag) <a href="{{ route('main.articles') }}?search={{ $hashtag->hashtag }}">#{{ $hashtag->hashtag }}</a> @endforeach</p>
            <p class="text-secondary">{{ $article->created_at }}</p>
        </div>
    </section>
@endsection
