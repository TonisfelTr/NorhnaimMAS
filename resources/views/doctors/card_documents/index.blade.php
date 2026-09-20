@extends('layouts.doctor')
@section('title', 'Заключения и документы')
@section('assets')
    @parent
    @include('doctors.card_documents.partials.assets')
@endsection
@section('main')
    <main class="mcd-page">
        <header class="mcd-hero">
            <div class="mcd-eyebrow">Документы врача</div>
            <h1>Заключения и документы</h1>
            <p>{{ $patient ? 'Документы выбранного пациента' : 'Ваши документы по пациентам, которых вы ведёте' }}. Эпикризы доступны в существующем разделе медкарты.</p>
            @if($patient)
                <a class="mcd-button mcd-button--light" href="{{ route('doctors.patients.medical_card', $patient->id) }}">
                    <i class="bi bi-arrow-left"></i> Медкарта: {{ $patient->full_name }}
                </a>
            @endif
        </header>
        <div class="mcd-menu-grid mcd-menu-grid--journal">
            @foreach(\App\Support\CardDocuments\DocumentCatalog::menu() as $type => $item)
                <a class="mcd-menu-item" href="{{ route('doctors.card_documents.create', ['type' => $type, 'patient_id' => $patient?->id]) }}">
                    <span class="mcd-menu-icon"><i class="bi {{ $item['icon'] }}"></i></span>
                    <span><strong>{{ $item['title'] }}</strong><small>{{ $item['hint'] }}</small></span>
                </a>
            @endforeach
        </div>
        <section class="mcd-card">
            <div class="mcd-head">
                <div><h2>Журнал документов</h2><p>Новые записи отображаются первыми.</p></div>
                <form class="mcd-filters" method="get" action="{{ route('doctors.card_documents.index') }}">
                    @if($patient)<input type="hidden" name="patient_id" value="{{ $patient->id }}">@endif
                    <label><span class="mcd-sr-only">Вид документа</span>
                        <select name="type" class="form-select">
                            <option value="">Все виды</option>
                            @foreach(\App\Support\CardDocuments\DocumentCatalog::TYPES as $type => $item)
                                <option value="{{ $type }}" @selected(request('type') === $type)>{{ $item['title'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label><span class="mcd-sr-only">Статус</span>
                        <select name="status" class="form-select">
                            <option value="">Все статусы</option>
                            <option value="draft" @selected(request('status') === 'draft')>Черновик</option>
                            <option value="final" @selected(request('status') === 'final')>Оформлен</option>
                        </select>
                    </label>
                    <button type="submit" class="mcd-button">Показать</button>
                </form>
            </div>
            @forelse($documents as $document)
                <article class="mcd-journal-row">
                    <div class="mcd-menu-icon"><i class="bi {{ \App\Support\CardDocuments\DocumentCatalog::get($document->type)['icon'] }}"></i></div>
                    <div class="mcd-grow">
                        <a class="mcd-document-title" href="{{ route('doctors.card_documents.show', $document) }}">
                            {{ \App\Support\CardDocuments\DocumentCatalog::get($document->type)['title'] }}
                        </a>
                        <div>{{ $document->patient_snapshot['name'] ?? 'Пациент' }}</div>
                        <small>{{ $document->number }} · {{ $document->issued_at->format('d.m.Y') }} · {{ $document->author_name }}</small>
                    </div>
                    <span class="mcd-badge {{ $document->status === 'draft' ? 'mcd-badge--draft' : '' }}">{{ $document->status === 'draft' ? 'Черновик' : 'Оформлен' }}</span>
                    <a class="mcd-button mcd-button--light" href="{{ route('doctors.card_documents.show', $document) }}">Открыть</a>
                </article>
            @empty
                <div class="mcd-empty"><i class="bi bi-file-earmark-plus"></i><h3>Документов пока нет</h3><p>Выберите вид документа выше. Существующие загруженные файлы и эпикризы остаются в своих разделах.</p></div>
            @endforelse
            <div class="mt-3">{{ $documents->links() }}</div>
        </section>
    </main>
@endsection
