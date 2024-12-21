@extends('layouts.admin')
@section('title', "Редактирование статьи \"{$topic->name}\"")
@section('assets')

@endsection
@section('main')
    <div class="container-fluid">
        <h1>Редактирование статьи "{{ $topic->name }}"</h1>
    </div>
@endsection
