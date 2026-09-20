@extends('doctors.card_documents.print_layout')
@section('print_title', 'Эпикриз')
@section('print_body')
    <article class="mcd-paper">
        <header class="mcd-paper-heading"><h1>Эпикриз</h1><p>от {{ $date ?: '________________' }}</p></header>
        <div class="mcd-paper-patient">
            <p><strong>Пациент:</strong> {{ $snapshot['name'] }}</p>
            <p><strong>Дата рождения:</strong> {{ $snapshot['birth_at'] ?: '—' }}<span class="mcd-paper-gap"></span><strong>Медкарта №</strong> {{ $snapshot['medcard_number'] ?: '—' }}</p>
        </div>
        @if($epicrisis->mkb10 || $epicrisis->diagnosis_title_display)
            <section class="mcd-paper-section"><h2>Диагноз</h2><div class="mcd-text">{{ $epicrisis->mkb10 }} {{ $epicrisis->diagnosis_title_display }}</div></section>
        @endif
        <section class="mcd-paper-section"><div class="mcd-text">{{ $text }}</div></section>
        <footer class="mcd-paper-signature"><div>Врач</div><div>___________________<small>подпись</small></div><div>{{ $epicrisis->doctor_name_display ?? '' }}</div></footer>
    </article>
@endsection
