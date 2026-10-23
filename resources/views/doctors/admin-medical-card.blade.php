@extends('layouts.doctor')
@section('title', 'Медицинская карта — просмотр')
@section('main')
    <style>
        /* Self-contained admin card styles. No changes to the sidebar/layout or app.sass. */
        .nh-card-readonly { padding: 24px; color: #17354a; max-width: 1480px; margin: 0 auto; }
        .nh-card-readonly, .nh-card-readonly * { box-sizing: border-box; }
        .nh-card-readonly__top {
            display: flex; gap: 14px; align-items: center; flex-wrap: wrap;
            min-height: 60px; padding: 11px 24px;
            background: linear-gradient(100deg, #0792a3, #0797a7 52%, #0b9eac);
            color: #fff;
        }
        .nh-card-readonly__back { text-decoration: none; font-weight: 650; color: #fff; }
        .nh-card-readonly__back:hover { color: #e6fdff; }
        .nh-card-readonly__read-pill {
            border-radius: 30px; padding: 5px 12px;
            background: rgba(255,255,255,.18); font-size: 12px; color: #fff;
        }
        .nh-card-readonly__heading { padding: 24px; border: 1px solid #dbe9ee; border-radius: 18px; background: #f0f9fb; margin-bottom: 20px; }
        .nh-card-readonly__heading h1 { font-size: clamp(23px,3vw,30px); font-weight: 760; margin: 0 0 7px; }
        .nh-card-readonly__sub { margin: 0; color: #557688; font-size: 14px; }
        .nh-card-readonly__grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 14px; }
        .nh-card-readonly__panel { border: 1px solid #dce8ec; border-radius: 15px; background: #fff; padding: 20px; min-width: 0; box-shadow: 0 2px 9px rgba(9,67,83,.04); }
        .nh-card-readonly__panel--wide { grid-column: 1 / -1; }
        .nh-card-readonly__panel h2 { font-size: 17px; margin: 0 0 15px; font-weight: 730; color: #174559; }
        .nh-card-readonly__pairs { display: grid; grid-template-columns: minmax(140px,1fr) 2fr; gap: 8px 12px; margin: 0; }
        .nh-card-readonly__pairs dt { font-size: 12px; color: #668193; font-weight: 650; }
        .nh-card-readonly__pairs dd { font-size: 13px; margin: 0; overflow-wrap: anywhere; }
        .nh-card-readonly__entry + .nh-card-readonly__entry { border-top: 1px solid #e8eff2; margin-top: 14px; padding-top: 14px; }
        .nh-card-readonly__date { color: #688b9b; font-size: 12px; margin-bottom: 6px; }
        .nh-card-readonly__text { font-size: 13px; line-height: 1.55; white-space: pre-wrap; overflow-wrap: anywhere; margin: 0; }
        .nh-card-readonly__empty { font-size: 13px; color: #8597a5; margin: 0; }
        .nh-card-readonly__results { margin: 8px 0; width: 100%; border-collapse: collapse; font-size: 12px; }
        .nh-card-readonly__results td { border-top: 1px solid #edf1f4; padding: 7px 4px; vertical-align: top; }
        .nh-card-readonly__results td:first-child { width: 45%; }
        .nh-card-readonly__note { margin: 18px 0 0; color: #678695; font-size: 12px; }
        @media (max-width: 1100px) { .nh-card-readonly__grid { grid-template-columns: repeat(2, minmax(0,1fr)); } }
        @media (max-width: 680px) {
            .nh-card-readonly { padding: 12px; }
            .nh-card-readonly__top { padding: 12px; }
            .nh-card-readonly__grid { grid-template-columns: 1fr; }
            .nh-card-readonly__pairs { grid-template-columns: 1fr; gap: 3px; }
            .nh-card-readonly__pairs dd { margin-bottom: 10px; }
        }
    </style>

    <div class="nh-card-readonly__top">
        <a class="nh-card-readonly__back" href="{{ route('doctors.main', ['tab' => 'patients', 'scope' => 'department']) }}">← К списку пациентов</a>
        <span class="nh-card-readonly__read-pill">Просмотр без редактирования</span>
    </div>

    <div class="nh-card-readonly">
        <header class="nh-card-readonly__heading">
            <h1>{{ $patientInfo['full_name'] }}</h1>
            <p class="nh-card-readonly__sub">Медицинская карта · только чтение · текущая госпитализация</p>
        </header>

        <div class="nh-card-readonly__grid">
            <section class="nh-card-readonly__panel">
                <h2>Пациент</h2>
                <dl class="nh-card-readonly__pairs">
                    <dt>Дата рождения</dt><dd>{{ $patientInfo['birth_date'] }}</dd>
                    <dt>Пол</dt><dd>{{ $patientInfo['gender'] }}</dd>
                    <dt>№ медкарты</dt><dd>{{ $patientInfo['medcard_number'] }}</dd>
                    <dt>СНИЛС</dt><dd>{{ $patientInfo['snils'] }}</dd>
                    <dt>Полис ОМС</dt><dd>{{ $patientInfo['oms'] }}</dd>
                    <dt>Адрес</dt><dd>{{ $patientInfo['address'] }}</dd>
                </dl>
            </section>

            <section class="nh-card-readonly__panel">
                <h2>Госпитализация</h2>
                <dl class="nh-card-readonly__pairs">
                    <dt>Отделение</dt><dd>{{ $patientInfo['department'] }}</dd>
                    <dt>Палата</dt><dd>{{ $patientInfo['room'] }}</dd>
                    <dt>Койка</dt><dd>{{ $patientInfo['bed'] }}</dd>
                    <dt>Поступил</dt><dd>{{ $patientInfo['admitted_at'] }}</dd>
                    <dt>Лечащий врач</dt><dd>{{ $patientInfo['doctor'] }}</dd>
                </dl>
            </section>

            <section class="nh-card-readonly__panel">
                <h2>Режим доступа</h2>
                <p class="nh-card-readonly__text">Ты можешь читать информацию о текущей госпитализации и доступные записи медкарты. Изменение данных, назначений, исследований и документов здесь не предусмотрено.</p>
                <p class="nh-card-readonly__note">Открытие карты фиксируется в журнале доступа.</p>
            </section>

            <section class="nh-card-readonly__panel nh-card-readonly__panel--wide">
                <h2>Анамнез · последние 30 записей</h2>
                @forelse($anamneses as $entry)
                    <div class="nh-card-readonly__entry">
                        <div class="nh-card-readonly__date">{{ $entry['date'] }}</div>
                        <p class="nh-card-readonly__text">{{ $entry['text'] }}</p>
                    </div>
                @empty
                    <p class="nh-card-readonly__empty">Записей нет.</p>
                @endforelse
            </section>

            <section class="nh-card-readonly__panel nh-card-readonly__panel--wide">
                <h2>Эпикризы · последние 30 записей</h2>
                @forelse($epicrises as $entry)
                    <div class="nh-card-readonly__entry">
                        <div class="nh-card-readonly__date">{{ $entry['date'] }}</div>
                        <p class="nh-card-readonly__text">{{ $entry['text'] }}</p>
                    </div>
                @empty
                    <p class="nh-card-readonly__empty">Записей нет.</p>
                @endforelse
            </section>

            <section class="nh-card-readonly__panel nh-card-readonly__panel--wide">
                <h2>Лабораторные исследования · последние 20</h2>
                @forelse($researches as $entry)
                    <div class="nh-card-readonly__entry">
                        <div class="nh-card-readonly__date">{{ $entry['date'] }} · {{ $entry['title'] }}</div>
                        @if(count($entry['values']))
                            <table class="nh-card-readonly__results">
                                <tbody>
                                @foreach($entry['values'] as $value)
                                    <tr><td>{{ $value['parameter'] }}</td><td>{{ $value['value'] }} {{ $value['unit'] }} @if($value['reference'] !== '') <span class="text-muted">· {{ $value['reference'] }}</span> @endif</td></tr>
                                @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="nh-card-readonly__empty">Результаты не внесены.</p>
                        @endif
                    </div>
                @empty
                    <p class="nh-card-readonly__empty">Исследований нет.</p>
                @endforelse
            </section>

            <section class="nh-card-readonly__panel nh-card-readonly__panel--wide">
                <h2>Инструментальные исследования · последние 30</h2>
                @forelse($instrumentals as $entry)
                    <div class="nh-card-readonly__entry">
                        <div class="nh-card-readonly__date">{{ $entry['date'] }} · {{ $entry['type'] }}</div>
                        <p class="nh-card-readonly__text">{{ $entry['result'] }}</p>
                    </div>
                @empty
                    <p class="nh-card-readonly__empty">Исследований нет.</p>
                @endforelse
            </section>

            <section class="nh-card-readonly__panel">
                <h2>Назначения · последние 30</h2>
                @forelse($prescriptions as $entry)
                    <div class="nh-card-readonly__entry">
                        <div class="nh-card-readonly__date">{{ $entry['date'] }}</div>
                        <p class="nh-card-readonly__text">{{ $entry['drug'] }} · {{ $entry['dose'] }}</p>
                    </div>
                @empty
                    <p class="nh-card-readonly__empty">Назначений нет.</p>
                @endforelse
            </section>

            <section class="nh-card-readonly__panel">
                <h2>Документы · последние 30</h2>
                @forelse($documents as $entry)
                    <div class="nh-card-readonly__entry">
                        <div class="nh-card-readonly__date">{{ $entry['date'] }}</div>
                        <p class="nh-card-readonly__text">{{ $entry['name'] }}</p>
                    </div>
                @empty
                    <p class="nh-card-readonly__empty">Документов нет.</p>
                @endforelse
                <p class="nh-card-readonly__note">В этой версии доступен список документов без скачивания вложений.</p>
            </section>
        </div>
        <p class="nh-card-readonly__note">Отображаются последние записи каждого раздела. Полный исторический журнал и защищённое чтение вложений требуют отдельного интерфейса и проверки прав.</p>
    </div>
@endsection
