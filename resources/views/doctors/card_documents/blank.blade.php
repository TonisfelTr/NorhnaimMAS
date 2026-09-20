@extends('doctors.card_documents.print_layout')
@section('print_title', $config['title'] . ' — чистый бланк')
@section('print_body')
    <article class="mcd-paper mcd-blank">
        <div class="mcd-blank-line">Медицинская организация: ____________________________________________________</div>
        <header class="mcd-paper-heading"><h1>{{ $config['title'] }}</h1><p>№ ________________ от «____» ________________ 20____ г.</p></header>
        <div class="mcd-blank-line">ФИО пациента: ______________________________________________________________</div>
        <div class="mcd-blank-line">Дата рождения: ________________________ Медкарта № __________________________</div>
        <div class="mcd-blank-line">Куда предоставляется: ________________________________________________________</div>
        <div class="mcd-blank-line">Цель выдачи: __________________________________________________________________</div>
        @foreach($config['fields'] as $label)
            <section class="mcd-blank-section"><h2>{{ $label }}</h2><div class="mcd-write-lines"></div></section>
        @endforeach
        <footer class="mcd-paper-signature"><div>Врач ____________________</div><div>____________________<small>подпись</small></div><div>________________________<small>ФИО</small></div></footer>
    </article>
@endsection
