@extends('doctors.prescriptions')

@section('prescription-title', 'Врачебные комиссии')

@section('sub-main')

    @include('doctors.commissions.partials.styles')

    <main class="commission-page commission-index-page">
        <div class="commission-container">

            <section class="commission-index-hero">
                <div class="commission-index-hero__main">
                    <div class="commission-index-hero__icon">
                        <i class="bi bi-clipboard2-pulse"></i>
                    </div>

                    <div>
                        <div class="commission-index-hero__eyebrow">
                            Комиссии и экспертизы
                        </div>

                        <h1>
                            Врачебные комиссии
                        </h1>

                        <p>
                            Единый журнал заседаний ВК: контроль статусов,
                            поиск по пациентам, оформление решений и печать протоколов.
                        </p>
                    </div>
                </div>

                <div class="commission-index-hero__actions">
                    <a class="commission-button commission-button--primary commission-button--hero"
                       href="{{ route('doctors.commissions.create') }}">
                        <i class="bi bi-plus-lg"></i>
                        Новая комиссия
                    </a>
                </div>
            </section>

            @if(session('status') === 'success')
                <div class="commission-alert commission-alert--success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            <section class="commission-index-stats">
                <article class="commission-index-stat">
                    <div class="commission-index-stat__icon commission-index-stat__icon--all">
                        <i class="bi bi-collection"></i>
                    </div>

                    <div class="commission-index-stat__copy">
                        <span>Всего заседаний</span>
                        <strong>{{ $stats['total'] }}</strong>
                    </div>
                </article>

                <article class="commission-index-stat">
                    <div class="commission-index-stat__icon commission-index-stat__icon--draft">
                        <i class="bi bi-pencil-square"></i>
                    </div>

                    <div class="commission-index-stat__copy">
                        <span>Черновики</span>
                        <strong>{{ $stats['draft'] }}</strong>
                    </div>
                </article>

                <article class="commission-index-stat">
                    <div class="commission-index-stat__icon commission-index-stat__icon--scheduled">
                        <i class="bi bi-calendar2-check"></i>
                    </div>

                    <div class="commission-index-stat__copy">
                        <span>Запланировано</span>
                        <strong>{{ $stats['scheduled'] }}</strong>
                    </div>
                </article>

                <article class="commission-index-stat">
                    <div class="commission-index-stat__icon commission-index-stat__icon--ready">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>

                    <div class="commission-index-stat__copy">
                        <span>Протокол оформлен</span>
                        <strong>{{ $stats['protocol_ready'] }}</strong>
                    </div>
                </article>
            </section>

            <section class="commission-index-filter">
                <div class="commission-index-filter__head">
                    <div>
                        <h2>Поиск и фильтры</h2>
                        <p>
                            Быстро найдите заседание по пациенту, номеру протокола,
                            вопросу или периоду.
                        </p>
                    </div>

                    @if(request()->hasAny([
                        'q',
                        'status',
                        'meeting_type',
                        'date_from',
                        'date_to'
                    ]))
                        <a class="commission-filter-reset"
                           href="{{ route('doctors.commissions.index') }}">
                            <i class="bi bi-x-circle"></i>
                            Сбросить
                        </a>
                    @endif
                </div>

                <form method="GET"
                      action="{{ route('doctors.commissions.index') }}"
                      class="commission-index-filter__grid">

                    <div class="commission-search-field">
                        <i class="bi bi-search"></i>

                        <input type="search"
                               name="q"
                               value="{{ request('q') }}"
                               placeholder="Пациент, протокол или вопрос ВК">
                    </div>

                    <div class="commission-index-filter__field">
                        <label for="commissionFilterStatus">
                            Статус
                        </label>

                        <select id="commissionFilterStatus"
                                name="status">
                            <option value="">
                                Все статусы
                            </option>

                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(request('status') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="commission-index-filter__field">
                        <label for="commissionFilterType">
                            Вид заседания
                        </label>

                        <select id="commissionFilterType"
                                name="meeting_type">
                            <option value="">
                                Все виды
                            </option>

                            @foreach($meetingTypeOptions as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(request('meeting_type') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="commission-index-filter__field">
                        <label for="commissionFilterFrom">
                            С даты
                        </label>

                        <input id="commissionFilterFrom"
                               type="date"
                               name="date_from"
                               value="{{ request('date_from') }}">
                    </div>

                    <div class="commission-index-filter__field">
                        <label for="commissionFilterTo">
                            По дату
                        </label>

                        <input id="commissionFilterTo"
                               type="date"
                               name="date_to"
                               value="{{ request('date_to') }}">
                    </div>

                    <button type="submit"
                            class="commission-button commission-button--primary commission-filter-submit">
                        <i class="bi bi-search"></i>
                        Найти
                    </button>
                </form>
            </section>

            <section class="commission-journal">
                <header class="commission-journal__head">
                    <div>
                        <h2>
                            Журнал заседаний
                        </h2>

                        <p>
                            {{ $commissions->total() }}
                            {{ $commissions->total() === 1 ? 'запись' : 'записей' }}
                        </p>
                    </div>

                    <div class="commission-journal__legend">
                        <i class="bi bi-info-circle"></i>
                        Нажмите на протокол или пациента для просмотра
                    </div>
                </header>

                @if($commissions->count())

                    <div class="commission-journal__table-wrap">
                        <table class="commission-journal__table">
                            <thead>
                            <tr>
                                <th>Протокол</th>
                                <th>Пациент</th>
                                <th>Заседание</th>
                                <th>Вопрос ВК</th>
                                <th>Статус</th>
                                <th>Состав</th>
                                <th></th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($commissions as $commission)
                                <tr>
                                    <td>
                                        <a class="commission-protocol-cell"
                                           href="{{ route(
                                               'doctors.commissions.show',
                                               $commission
                                           ) }}">
                                            <span class="commission-protocol-cell__icon">
                                                <i class="bi bi-file-earmark-medical"></i>
                                            </span>

                                            <span>
                                                <strong>
                                                    {{ $commission->protocol_number ?: 'Без номера' }}
                                                </strong>

                                                <small>
                                                    ID {{ $commission->id }}
                                                </small>
                                            </span>
                                        </a>
                                    </td>

                                    <td>
                                        <a class="commission-patient-cell"
                                           href="{{ route(
                                               'doctors.commissions.show',
                                               $commission
                                           ) }}">
                                            <span class="commission-patient-cell__avatar">
                                                {{ $commission->ui_patient_initials }}
                                            </span>

                                            <span class="commission-patient-cell__copy">
                                                <strong>
                                                    {{ $commission->ui_patient_name }}
                                                </strong>

                                                <small>
                                                    {{ $commission->ui_diagnosis }}
                                                </small>
                                            </span>
                                        </a>
                                    </td>

                                    <td>
                                        <div class="commission-date-cell">
                                            <strong>
                                                {{ $commission->ui_meeting_date }}
                                            </strong>

                                            <span>
                                                {{ $commission->ui_meeting_time }}
                                            </span>

                                            <small>
                                                {{ $commission->ui_meeting_type_label }}
                                            </small>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="commission-agenda-cell">
                                            {{ $commission->ui_agenda_preview }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="commission-status commission-status--{{ $commission->status }}">
                                            <i class="bi bi-circle-fill"></i>
                                            {{ $commission->ui_status_label }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="commission-members-chip">
                                            <i class="bi bi-people"></i>
                                            <strong>
                                                {{ $commission->ui_members_count }}
                                            </strong>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="commission-row-actions">
                                            <a class="commission-icon-button"
                                               href="{{ route(
                                                   'doctors.commissions.show',
                                                   $commission
                                               ) }}"
                                               title="Просмотреть комиссию"
                                               data-tooltip="Просмотреть комиссию"
                                               aria-label="Просмотреть комиссию">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a class="commission-icon-button"
                                               href="{{ route(
                                                   'doctors.commissions.edit',
                                                   $commission
                                               ) }}"
                                               title="Редактировать комиссию"
                                               data-tooltip="Редактировать комиссию"
                                               aria-label="Редактировать комиссию">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <a class="commission-icon-button"
                                               href="{{ route(
                                                   'doctors.commissions.print',
                                                   $commission
                                               ) }}"
                                               target="_blank"
                                               title="Распечатать протокол"
                                               data-tooltip="Распечатать протокол"
                                               aria-label="Распечатать протокол">
                                                <i class="bi bi-printer"></i>
                                            </a>

                                            <form method="POST"
                                                  action="{{ route(
                                                      'doctors.commissions.destroy',
                                                      $commission
                                                  ) }}"
                                                  class="commission-delete-form"
                                                  onsubmit="return confirm(
                                                      'Удалить эту врачебную комиссию?'
                                                  );">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="commission-icon-button commission-icon-button--danger"
                                                        title="Удалить комиссию"
                                                        data-tooltip="Удалить комиссию"
                                                        aria-label="Удалить комиссию">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <footer class="commission-journal__footer">
                        <span>
                            Показано
                            <strong>{{ $commissions->count() }}</strong>
                            из
                            <strong>{{ $commissions->total() }}</strong>
                        </span>

                        <div class="commission-journal__pagination">
                            {{ $commissions->links() }}
                        </div>
                    </footer>

                @else

                    <div class="commission-empty commission-empty--journal">
                        <div class="commission-empty__visual">
                            <span>
                                <i class="bi bi-clipboard2-check"></i>
                            </span>
                        </div>

                        <h3>
                            Врачебных комиссий не найдено
                        </h3>

                        <p>
                            Измените параметры фильтра или создайте новую запись ВК.
                        </p>

                        <div class="commission-empty__actions">
                            <a class="commission-button commission-button--primary"
                               href="{{ route('doctors.commissions.create') }}">
                                <i class="bi bi-plus-lg"></i>
                                Новая комиссия
                            </a>

                            @if(request()->hasAny([
                                'q',
                                'status',
                                'meeting_type',
                                'date_from',
                                'date_to'
                            ]))
                                <a class="commission-button commission-button--ghost"
                                   href="{{ route('doctors.commissions.index') }}">
                                    Сбросить фильтры
                                </a>
                            @endif
                        </div>
                    </div>

                @endif
            </section>

        </div>
    </main>

@endsection
