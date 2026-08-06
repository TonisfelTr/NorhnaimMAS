@extends('layouts.welcome')
@section('title', $topic->name)
@section('assets')
@endsection
@section('main')
    <div class="container">
        <h1>{{ $topic->name }}</h1>
        {!! $topic->content !!}
        <p class="text-secondary pt-4">
            {{ $topic->created_at }} @if($topic->created_at != $topic->updated_at), изменено {{ $topic->updated_at }} @endif| {{ $topic->user->login }}
        </p>
    </div>
@endsection
