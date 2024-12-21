@extends('layouts.welcome')
@section('title', 'Научные статьи')
@section('assets')
@endsection
@section('main')
    @if ($articles->isNotEmpty())
    <section class="article">
        <div class="container pt-3">
            <div class="row">
                <div class="col-md-8">
                    <h1><span>Научная</span> библиотека</h1>
                    <p class="text-secondary-emphasis articles__description">
                        Мы собираем различные статьи и Вы, если вы врач, можете опубликовать свои труды, основывающиеся на своих
                        навыках. Также, здесь могут быть опубликованы труды учёных - исследования, в том числе переводы с других
                        языков.
                    </p>
                </div>
                <form class="col-md-4" method="get" enctype="multipart/form-data" action="">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-transparent magnifier-block">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 with-btn-filter" name="search" placeholder="Поиск..." aria-label="Поиск" value="{{ request()->get('search') }}"/>
                        <button class="btn btn-outline-primary" type="submit">
                            Искать
                        </button>
                    </div>
                </form>
            </div>
            @foreach($articles as $article)
                <div class="row pt-3 pb-3">
                    <div class="col-md-12">
                        <div class="article-card__body">
                            <a href="{{ route('main.articles.show', $article->id) }}">
                                <h3 class="article-card__header">{{ $article->name }}</h3>
                            </a>
                            <p>
                                @foreach ($article->hashtags()->get() as $hashtag)
                                    #{{ $hashtag->hashtag }}
                                @endforeach
                            </p>
                            <p>{{ Str::limit(strip_tags($article->content), 150) }}</p>
                            <p class="mb-0"><strong>Авторы исследования:</strong> {{ $article->authors }}</p>
                            <p class="text-secondary pt-0">{{ $article->created_at }}, опубликовано {{ $article->user->login }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
            {{ $articles->links('pagination::bootstrap-5') }}
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
