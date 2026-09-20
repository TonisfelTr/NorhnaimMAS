@extends('layouts.doctor')
@section('title', $config['title'] . ' — выбор пациента')
@section('assets')
    @parent
    @include('doctors.card_documents.partials.assets')
@endsection
@section('main')
    <main class="mcd-page">
        <header class="mcd-hero">
            <div class="mcd-eyebrow">{{ $config['hint'] }}</div>
            <h1>{{ $config['title'] }}</h1>
            <p>Выберите пациента для заполнения документа.</p>
            <div class="mcd-actions">
                <a class="mcd-button mcd-button--light" href="{{ route('doctors.card_documents.index') }}">Журнал документов</a>
                <a class="mcd-button mcd-button--light" href="{{ route('doctors.card_documents.blank', $type) }}" target="_blank" rel="noopener">
                    <i class="bi bi-printer"></i> Чистый бланк для заполнения от руки
                </a>
            </div>
        </header>
        <section class="mcd-card">
            <form method="get" action="{{ route('doctors.card_documents.create', $type) }}" class="mcd-search">
                <label for="mcdPatientSearch" class="mcd-sr-only">ФИО или номер медкарты</label>
                <input id="mcdPatientSearch" class="form-control" name="q" type="search" maxlength="100" value="{{ $q }}" placeholder="ФИО или номер медкарты" autocomplete="off">
                <button type="submit" class="mcd-button">Найти</button>
            </form>
            <p class="mcd-muted">В поиске показаны пациенты, у которых вы указаны лечащим врачом. Переход из конкретной медкарты дополнительно учитывает политику доступа проекта.</p>
            @forelse($patients as $patient)
                <div class="mcd-journal-row">
                    <div class="mcd-menu-icon"><i class="bi bi-person-vcard"></i></div>
                    <div class="mcd-grow"><strong>{{ $patient->full_name }}</strong><small>Карта № {{ $patient->medcard_number ?: '—' }}</small></div>
                    <a class="mcd-button" href="{{ route('doctors.card_documents.create', ['type' => $type, 'patient_id' => $patient->id]) }}">Выбрать</a>
                </div>
            @empty
                <div class="mcd-empty"><h3>Пациенты не найдены</h3><p>Проверьте запрос и назначение лечащего врача.</p></div>
            @endforelse
            <div class="mt-3">{{ $patients->links() }}</div>
        </section>
    </main>
@endsection
