@extends('laboratory.layout')
@section('title', 'Назначенные исследования')
@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <div>
        <h1 class="h3 mb-1">Лабораторные исследования</h1>
        <p class="text-secondary mb-0">Назначения только вашей клиники. Новые направления появляются автоматически.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span id="lab-realtime-status" class="badge rounded-pill text-bg-secondary" role="status" aria-live="polite">
            Подключение…
        </span>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="lab-refresh-now">Обновить</button>
    </div>
</div>

<div id="lab-new-research-notice" class="alert alert-info d-none" role="status" aria-live="polite">
    Пришло новое лабораторное направление. Если действуют фильтры или открыта не первая страница,
    <a class="alert-link" href="{{ route('laboratory.index') }}">откройте общий список</a>.
</div>

<div class="nh-card p-3 mb-3">
    <form method="get" action="{{ route('laboratory.index') }}" class="row g-2">
        <div class="col-md-8">
            <label class="form-label" for="lab-search">Пациент (ФИО или ID), номер исследования</label>
            <input id="lab-search" name="q" class="form-control" value="{{ request('q') }}" maxlength="100" placeholder="Начните поиск">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="lab-status">Статус</label>
            <select id="lab-status" name="status" class="form-select">
                <option value="">Все</option>
                @foreach(['ordered'=>'Назначено', 'processing'=>'В работе', 'ready'=>'Готово'] as $code=>$label)
                    <option value="{{ $code }}" @selected(request('status') === $code)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button class="btn btn-primary w-100">Найти</button>
        </div>
    </form>
</div>

<div id="lab-realtime"
     data-clinic-id="{{ (int) auth()->user()->laboratory_clinic_id }}"
     data-csrf-token="{{ csrf_token() }}"
     data-fragment-url="{{ route('laboratory.researches.fragment') }}"
     data-channel-auth-url="{{ route('laboratory.broadcasting.auth') }}">
    <div id="lab-researches-list" aria-live="off">
        @include('laboratory.partials.researches-table', ['researches' => $researches])
    </div>
</div>

@vite('resources/js/laboratory-realtime.js')
@endsection
