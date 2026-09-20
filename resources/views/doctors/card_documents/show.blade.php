@extends('layouts.doctor')
@section('title', $config['title'] . ' ' . $document->number)
@section('assets')
    @parent
    @include('doctors.card_documents.partials.assets')
@endsection
@section('main')
    <main class="mcd-page">
        <header class="mcd-hero">
            <div class="mcd-eyebrow">{{ $document->number }} · {{ $document->status === 'draft' ? 'Черновик' : 'Оформлен' }}</div>
            <h1>{{ $config['title'] }}</h1>
            <p>{{ $document->patient_snapshot['name'] ?? '' }}</p>
            <div class="mcd-actions">
                <a class="mcd-button mcd-button--light" href="{{ route('doctors.card_documents.index', ['patient_id' => $document->patient_id]) }}"><i class="bi bi-arrow-left"></i> В журнал</a>
                @if($document->status === 'draft' && (string) $document->author_id === (string) auth()->id())
                    <a class="mcd-button mcd-button--light" href="{{ route('doctors.card_documents.edit', $document) }}"><i class="bi bi-pencil-square"></i> Редактировать</a>
                @endif
                <a class="mcd-button" href="{{ route('doctors.card_documents.print', $document) }}" target="_blank" rel="noopener"><i class="bi bi-printer"></i> Печать / PDF</a>
                <a class="mcd-button mcd-button--light" href="{{ route('doctors.card_documents.create', ['type' => $document->type, 'patient_id' => $document->patient_id]) }}">Новый документ</a>
            </div>
        </header>
        @if(session('mcd_success'))<div class="mcd-alert" role="status">{{ session('mcd_success') }}</div>@endif
        @if($document->status === 'final')<p class="mcd-muted">Сохранённый документ не изменяется. Сведения в нём зафиксированы на момент оформления. Электронная подпись не накладывалась.</p>@endif
        @include('doctors.card_documents.partials.document')
    </main>
@endsection
