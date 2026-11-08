{{-- One patient, one chronological record: booking, arrival, clinical outcome, structured protocol. --}}
<style>
    #pane-visits .vh {
        --vh-ink: #182c3b;
        --vh-muted: #65798a;
        --vh-line: #dfe9ed;
        --vh-accent: #07849a;
        --vh-soft: #eaf7f9;
        max-width: 1540px;
        margin: 0 auto;
        color: var(--vh-ink);
        font-size: 14px;
    }
    #pane-visits .vh * { box-sizing: border-box; }
    #pane-visits .vh-head {
        display: flex; gap: 18px; align-items: center; justify-content: space-between;
        margin-bottom: 20px; padding: 23px 26px; background: #fff;
        border: 1px solid var(--vh-line); border-radius: 19px;
        box-shadow: 0 3px 14px rgba(20, 54, 76, .025);
    }
    #pane-visits .vh-heading { display: flex; align-items: center; gap: 16px; min-width: 0; }
    #pane-visits .vh-heading-icon {
        width: 48px; height: 48px; border-radius: 15px; flex: 0 0 48px;
        display: grid; place-items: center; font-size: 23px;
        color: var(--vh-accent); background: var(--vh-soft);
    }
    #pane-visits .vh-title { font-size: clamp(20px, 1.5vw, 24px); font-weight: 800; line-height: 1.2; margin: 0; }
    #pane-visits .vh-subtitle { margin: 6px 0 0; color: var(--vh-muted); line-height: 1.5; }
    #pane-visits .vh-header-hint {
        white-space: nowrap; font-size: 12px; font-weight: 750; color: #167b8e;
        border: 1px solid #c7e9ee; background: #f0fafb; padding: 9px 12px;
        border-radius: 999px;
    }
    #pane-visits .vh-metrics { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 12px; margin-bottom: 16px; }
    #pane-visits .vh-metric {
        border: 1px solid var(--vh-line); background: #fff; border-radius: 15px;
        padding: 17px 19px; display: flex; justify-content: space-between; align-items: center;
        gap: 12px; min-width: 0;
    }
    #pane-visits .vh-metric-label { font-size: 12px; font-weight: 650; color: var(--vh-muted); }
    #pane-visits .vh-metric-num { font-size: 27px; font-weight: 800; line-height: 1.1; margin: 6px 0 0; font-variant-numeric: tabular-nums; }
    #pane-visits .vh-metric-icon {
        width: 43px; height: 43px; flex: 0 0 43px; border-radius: 13px; display: grid; place-items: center;
        background: #f1f7fa; color: #427a89; font-size: 19px;
    }
    #pane-visits .vh-metric.success .vh-metric-icon { background: #e7f7ed; color: #278650; }
    #pane-visits .vh-metric.warning .vh-metric-icon { background: #fff0ed; color: #c5634a; }
    #pane-visits .vh-metric.video .vh-metric-icon { background: #f0ebff; color: #6f56ab; }
    #pane-visits .vh-toolbar {
        display: flex; align-items: end; gap: 12px; flex-wrap: wrap;
        background: #fff; border: 1px solid var(--vh-line); border-radius: 15px;
        padding: 15px 17px; margin-bottom: 23px;
    }
    #pane-visits .vh-field { display: flex; flex-direction: column; gap: 6px; flex: 1 1 185px; min-width: 160px; }
    #pane-visits .vh-field > label { color: #485f70; font-weight: 750; font-size: 12px; }
    #pane-visits .vh .form-control, #pane-visits .vh .form-select {
        border-radius: 10px; border-color: #d4e0e6; min-height: 42px; font-size: 13px; background-color: #fff;
    }
    #pane-visits .vh .form-control:focus, #pane-visits .vh .form-select:focus {
        border-color: #4bafc0; box-shadow: 0 0 0 3px rgba(11, 136, 156, .11);
    }
    #pane-visits .vh-toolbar .btn { min-height: 42px; border-radius: 10px; }
    #pane-visits .vh-toolbar .btn-primary, #pane-visits .vh-save { background: #07849a; border-color: #07849a; color: #fff; }
    #pane-visits .vh-list-head { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin: 2px 0 12px; }
    #pane-visits .vh-list-head h3 { font-size: 16px; font-weight: 800; margin: 0; }
    #pane-visits .vh-count { font-weight: 700; color: #70859a; font-size: 12px; }
    #pane-visits .vh-list { display: grid; gap: 10px; }
    #pane-visits .vh-entry {
        background: #fff; border: 1px solid var(--vh-line); border-radius: 17px;
        box-shadow: 0 3px 14px rgba(26, 52, 74, .028); overflow: hidden;
    }
    #pane-visits .vh-entry[open] { border-color: #a8dce3; box-shadow: 0 8px 24px rgba(17, 84, 102, .065); }
    #pane-visits .vh-entry-summary {
        display: grid; grid-template-columns: 75px minmax(0,1fr) auto 17px;
        gap: 16px; align-items: center; padding: 16px 18px; cursor: pointer; list-style: none;
    }
    #pane-visits .vh-entry-summary::-webkit-details-marker { display: none; }
    #pane-visits .vh-entry-summary::marker { content: ''; }
    #pane-visits .vh-entry-summary:hover { background: #fcfefe; }
    #pane-visits .vh-entry-summary:focus-visible { outline: 3px solid #5badbb; outline-offset: -4px; }
    #pane-visits .vh-date {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        min-height: 76px; border-radius: 12px; color: #315b72; background: #eff5f8;
        font-variant-numeric: tabular-nums;
    }
    #pane-visits .vh-entry.telemedicine .vh-date { background: #f1edfc; color: #6b58a4; }
    #pane-visits .vh-date-day { font-size: 28px; font-weight: 800; line-height: 1; }
    #pane-visits .vh-date-month { font-size: 11px; font-weight: 700; margin-top: 7px; }
    #pane-visits .vh-entry-main { min-width: 0; }
    #pane-visits .vh-entry-eyebrow { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; font-size: 12px; color: #547385; font-weight: 750; margin-bottom: 5px; }
    #pane-visits .vh-entry-eyebrow i { color: #08879b; font-size: 15px; }
    #pane-visits .vh-entry.telemedicine .vh-entry-eyebrow i { color: #745ec0; }
    #pane-visits .vh-entry-name { font-size: 16px; line-height: 1.3; font-weight: 800; color: #183348; }
    #pane-visits .vh-entry-meta { display: flex; flex-wrap: wrap; gap: 5px 18px; color: var(--vh-muted); font-size: 12px; margin-top: 6px; }
    #pane-visits .vh-entry-meta span { display: inline-flex; gap: 6px; align-items: start; }
    #pane-visits .vh-entry-meta i { color: #879daf; }
    #pane-visits .vh-entry-preview { margin-top: 7px; color: #577083; font-size: 12px; line-height: 1.4; max-width: 880px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    #pane-visits .vh-status-stack { display: flex; align-items: end; flex-direction: column; gap: 6px; text-align: right; }
    #pane-visits .vh-badge { font-size: 11px; font-weight: 750; border: 1px solid transparent; padding: 5px 9px; border-radius: 999px; line-height: 1.25; }
    #pane-visits .vh-badge.done { background: #e6f7eb; color: #1d824a; border-color: #c6edd3; }
    #pane-visits .vh-badge.failed { background: #fff0ed; color: #b7503a; border-color: #f6d2c9; }
    #pane-visits .vh-badge.progress { background: #fff7dc; color: #977008; border-color: #fae9a4; }
    #pane-visits .vh-badge.upcoming { background: #edf4ff; color: #2463aa; border-color: #d2e2f6; }
    #pane-visits .vh-badge.pending { background: #f2f5f7; color: #617587; border-color: #e2e9ee; }
    #pane-visits .vh-attendance { color: #718394; font-size: 11px; font-weight: 700; }
    #pane-visits .vh-chevron { font-size: 14px; color: #95a8b4; transition: transform .15s; }
    #pane-visits .vh-entry[open] .vh-chevron { transform: rotate(180deg); }
    #pane-visits .vh-panel { padding: 20px 23px 23px; border-top: 1px solid #e5eef2; background: linear-gradient(180deg,#fbfdfd, #fff 90px); }
    #pane-visits .vh-panel-heading { display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap; margin-bottom: 14px; }
    #pane-visits .vh-panel-heading h4 { font-size: 16px; font-weight: 800; margin: 0; }
    #pane-visits .vh-panel-caption { font-size: 12px; color: var(--vh-muted); margin-top: 3px; }
    #pane-visits .vh-section { margin-top: 15px; border: 1px solid #e0e9ee; border-radius: 14px; background: #fff; padding: 18px; }
    #pane-visits .vh-section-title { font-size: 13px; font-weight: 800; display: flex; gap: 9px; align-items: center; margin: 0 0 14px; }
    #pane-visits .vh-section-title i { color: var(--vh-accent); font-size: 16px; }
    #pane-visits .vh-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 13px; }
    #pane-visits .vh-grid .vh-field { min-width: 0; }
    #pane-visits .vh-grid .vh-span-all { grid-column: 1 / -1; }
    #pane-visits .vh .form-control[type='text'] { font-size: 13px; }
    #pane-visits .vh textarea.form-control { min-height: 92px; resize: vertical; line-height: 1.5; padding: 10px 12px; }
    #pane-visits .vh-marked { border-left: 3px solid #5ba9ba; padding: 10px 13px; margin-top: 10px; background: #f2fafb; font-size: 12px; color: #45697a; border-radius: 0 10px 10px 0; }
    #pane-visits .vh-marked.warn { border-left-color: #db9b42; background: #fff9ec; color: #88682e; }
    #pane-visits .vh-actions { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-top: 18px; }
    #pane-visits .vh-actions small { color: var(--vh-muted); font-size: 12px; max-width: 740px; }
    #pane-visits .vh-actions .btn { border-radius: 10px; padding: 9px 18px; font-weight: 750; }
    #pane-visits .vh-empty { text-align: center; padding: 40px 20px; border-radius: 16px; border: 1px dashed #ccdfe4; background: #f9fcfd; }
    #pane-visits .vh-empty i { color: #8bb5bf; font-size: 31px; }
    #pane-visits .vh-empty h4 { font-size: 16px; font-weight: 800; margin: 13px 0 5px; }
    #pane-visits .vh-help { display: flex; gap: 10px; align-items: start; margin-top: 16px; padding: 13px 16px; font-size: 12px; color: #66808e; background: #f1f7f9; border: 1px solid #dbe8ed; border-radius: 12px; }
    #pane-visits .vh [hidden] { display: none !important; }
    #pane-visits .vh-inactive-note { border: 1px solid #d6e6ed; border-radius: 13px; padding: 15px 17px; margin-top: 14px; background: #f4f9fc; color: #426073; font-size: 13px; line-height: 1.5; }
    #pane-visits .vh-inactive-note i { margin-right: 8px; color: #4384a1; }
    #pane-visits .vh-future-lock {
        display: flex; align-items: flex-start; gap: 13px;
        padding: 21px; border: 1px solid #d9e7ef; border-radius: 14px;
        background: linear-gradient(115deg, #f5f9fd, #f7fbfc);
        color: #395d70;
    }
    #pane-visits .vh-future-lock-icon {
        flex: 0 0 42px; width: 42px; height: 42px;
        display: grid; place-items: center; border-radius: 12px;
        color: #277f96; background: #e5f2f7; font-size: 19px;
    }
    #pane-visits .vh-future-lock-title {
        font-size: 15px; font-weight: 800; color: #19374a; margin-bottom: 6px;
    }
    #pane-visits .vh-future-lock p { margin: 0 0 7px; line-height: 1.55; }
    #pane-visits .vh-future-lock .vh-future-lock-hint {
        color: #678192; font-size: 12px; margin: 0;
    }
    #pane-visits .vh-read-row { margin: 0 0 12px; }
    #pane-visits .vh-read-row b { display: block; font-size: 12px; color: #607488; margin-bottom: 4px; }
    #pane-visits .vh-read-row p { white-space: pre-wrap; margin: 0; line-height: 1.6; }
    #pane-visits .pagination { flex-wrap: wrap; }
    @media (max-width: 1030px) {
        #pane-visits .vh-metrics { grid-template-columns: repeat(2,minmax(0,1fr)); }
    }
    @media (max-width: 720px) {
        #pane-visits .vh-head { padding: 17px; align-items: start; }
        #pane-visits .vh-header-hint { display: none; }
        #pane-visits .vh-entry-summary { grid-template-columns: 58px minmax(0,1fr) 16px; gap: 11px; padding: 13px; }
        #pane-visits .vh-date { min-height: 64px; }
        #pane-visits .vh-date-day { font-size: 23px; }
        #pane-visits .vh-status-stack { grid-column: 2; align-items: start; text-align: left; flex-direction: row; flex-wrap: wrap; }
        #pane-visits .vh-chevron { grid-column: 3; grid-row: 1 / span 2; }
        #pane-visits .vh-grid { grid-template-columns: 1fr; }
        #pane-visits .vh-panel { padding: 15px 13px 17px; }
        #pane-visits .vh-section { padding: 14px; }
        #pane-visits .vh-heading-icon { display: none; }
    }
    @media (max-width: 480px) {
        #pane-visits .vh-metric { padding: 13px 12px; }
        #pane-visits .vh-metric-num { font-size: 23px; }
        #pane-visits .vh-metric-icon { width: 34px; height: 34px; flex-basis: 34px; font-size: 15px; }
        #pane-visits .vh-entry-preview { white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    }
</style>

<div class="vh" data-has-errors="{{ $errors->getBag('encounter')->any() ? '1' : '0' }}">
    <div class="vh-head">
        <div class="vh-heading">
            <div class="vh-heading-icon"><i class="bi bi-clipboard2-pulse"></i></div>
            <div>
                <h2 class="vh-title">История приёмов и телемедицины</h2>
                <p class="vh-subtitle">Запись, явка пациента, результат консультации и медицинский протокол — в одном месте.</p>
            </div>
        </div>
        <span class="vh-header-hint"><i class="bi bi-journal-check me-1"></i> Единая история пациента</span>
    </div>

    <div class="vh-metrics">
        <div class="vh-metric">
            <div><div class="vh-metric-label">Записей всего</div><div class="vh-metric-num">{{ $visitHistoryTotals['all'] }}</div></div>
            <div class="vh-metric-icon"><i class="bi bi-calendar2-week"></i></div>
        </div>
        <div class="vh-metric success">
            <div><div class="vh-metric-label">Консультации состоялись</div><div class="vh-metric-num">{{ $visitHistoryTotals['completed'] }}</div></div>
            <div class="vh-metric-icon"><i class="bi bi-check2-circle"></i></div>
        </div>
        <div class="vh-metric warning">
            <div><div class="vh-metric-label">Неявки</div><div class="vh-metric-num">{{ $visitHistoryTotals['absent'] }}</div></div>
            <div class="vh-metric-icon"><i class="bi bi-person-x"></i></div>
        </div>
        <div class="vh-metric video">
            <div><div class="vh-metric-label">Записей на телемедицину</div><div class="vh-metric-num">{{ $visitHistoryTotals['telemedicine'] }}</div></div>
            <div class="vh-metric-icon"><i class="bi bi-camera-video"></i></div>
        </div>
    </div>

    @if(session('visit_notice'))
        <div class="alert alert-success border-0" role="status"><i class="bi bi-check-circle me-1"></i>{{ session('visit_notice') }}</div>
    @endif

    @if(!$visitHistoryAvailable)
        <div class="alert alert-info">Данные регистратуры пока недоступны.</div>
    @else
        @if(!$visitHistoryHasEncounters)
            <div class="alert alert-warning" role="status">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Для отметки явки и сохранения протоколов нужно применить миграцию <code>medical_encounters</code>.
                Пока доступен просмотр записей.
            </div>
        @endif
        @if(!$visitHistoryHasFormat)
            <div class="alert alert-light border">Для прежних записей формат приёма не сохранён — они показаны без предположений.</div>
        @endif

        <form class="vh-toolbar" method="get" action="{{ route('doctors.patients.medical_card', $patient->id) }}">
            <input type="hidden" name="tab" value="visits">
            <div class="vh-field">
                <label for="vhFormat">Формат</label>
                <select class="form-select" id="vhFormat" name="visit_format" @disabled(!$visitHistoryHasFormat)>
                    <option value="all" @selected($visitHistoryFilters['format'] === 'all')>Все форматы</option>
                    <option value="in_person" @selected($visitHistoryFilters['format'] === 'in_person')>Очный</option>
                    <option value="telemedicine" @selected($visitHistoryFilters['format'] === 'telemedicine')>Телемедицина</option>
                </select>
                @unless($visitHistoryHasFormat)<input type="hidden" name="visit_format" value="all">@endunless
            </div>
            <div class="vh-field">
                <label for="vhOutcome">Результат</label>
                <select class="form-select" id="vhOutcome" name="visit_outcome" @disabled(!$visitHistoryHasEncounters)>
                    <option value="all" @selected($visitHistoryFilters['outcome'] === 'all')>Любой</option>
                    <option value="completed" @selected($visitHistoryFilters['outcome'] === 'completed')>Состоялся</option>
                    <option value="not_held" @selected($visitHistoryFilters['outcome'] === 'not_held')>Не состоялся</option>
                    <option value="absent" @selected($visitHistoryFilters['outcome'] === 'absent')>Неявка</option>
                    <option value="unconfirmed" @selected($visitHistoryFilters['outcome'] === 'unconfirmed')>Не подтверждён</option>
                </select>
                @unless($visitHistoryHasEncounters)<input type="hidden" name="visit_outcome" value="all">@endunless
            </div>
            <div class="vh-field">
                <label for="vhPeriod">Период</label>
                <select class="form-select" id="vhPeriod" name="visit_period">
                    <option value="all" @selected($visitHistoryFilters['period'] === 'all')>Все даты</option>
                    <option value="past" @selected($visitHistoryFilters['period'] === 'past')>Прошедшие</option>
                    <option value="upcoming" @selected($visitHistoryFilters['period'] === 'upcoming')>Предстоящие</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Применить</button>
            <a class="btn btn-outline-secondary" href="{{ route('doctors.patients.medical_card', ['patient' => $patient->id, 'tab' => 'visits']) }}">Сбросить</a>
        </form>

        <div class="vh-list-head">
            <h3>Хронология обращений</h3>
            <div class="vh-count">Найдено: {{ $visitHistory->total() }} · предстоящих: {{ $visitHistoryTotals['upcoming'] }}</div>
        </div>

        @if($visitHistory->count() > 0)
            <div class="vh-list">
                @foreach($visitHistory as $visit)
                    <details class="vh-entry {{ $visit['format'] === 'telemedicine' ? 'telemedicine' : '' }}"
                             id="visit-entry-{{ $visit['id'] }}"
                             @if((string) request()->query('visit_open') === (string) $visit['id'] || (string) old('_visit_id') === (string) $visit['id']) open @endif>
                        <summary class="vh-entry-summary">
                            <div class="vh-date">
                                <span class="vh-date-day">{{ $visit['day'] }}</span>
                                <span class="vh-date-month">{{ $visit['month'] }}</span>
                            </div>
                            <div class="vh-entry-main">
                                <div class="vh-entry-eyebrow">
                                    <i class="bi {{ $visit['format'] === 'telemedicine' ? 'bi-camera-video' : 'bi-person-check' }}"></i>
                                    {{ $visit['format_label'] }}
                                    <span>·</span>
                                    <span>{{ $visit['visit_type_label'] }}</span>
                                    @if($visit['record_deleted'])
                                        <span class="text-danger">· Запись удалена</span>
                                    @endif
                                </div>
                                <div class="vh-entry-name">{{ $visit['doctor_name'] }}</div>
                                <div class="vh-entry-meta">
                                    <span><i class="bi bi-clock"></i>{{ $visit['time'] }}, {{ $visit['date'] }}</span>
                                    <span><i class="bi bi-building"></i>{{ $visit['clinic_name'] }}</span>
                                    <span><i class="bi bi-hash"></i>Запись № {{ $visit['id'] }}</span>
                                </div>
                                @if($visit['protocol_preview'])
                                    <div class="vh-entry-preview"><i class="bi bi-file-earmark-text me-1"></i>{{ $visit['protocol_preview'] }}</div>
                                @endif
                            </div>
                            <div class="vh-status-stack">
                                <span class="vh-badge {{ $visit['consultation_style'] }}">{{ $visit['consultation_label'] }}</span>
                                <span class="vh-attendance">{{ $visit['attendance_label'] }}</span>
                            </div>
                            <i class="bi bi-chevron-down vh-chevron" aria-hidden="true"></i>
                        </summary>

                        <div class="vh-panel">
                            <div class="vh-panel-heading">
                                <div>
                                    <h4>Карта приёма</h4>
                                    <div class="vh-panel-caption">Статус и протокол относятся только к этой записи.</div>
                                </div>
                                @if($visit['has_protocol'])
                                    <a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener"
                                       href="{{ route('doctors.patients.visits.protocol.print', ['patient' => $patient->id, 'registryRecord' => $visit['id']]) }}">
                                        <i class="bi bi-printer me-1"></i>Печатная форма
                                    </a>
                                @endif
                            </div>

                            @if(!$visit['record_deleted'] && !$visit['can_edit_now'])
                                <div class="vh-future-lock" role="status">
                                    <div class="vh-future-lock-icon" aria-hidden="true"><i class="bi bi-lock"></i></div>
                                    <div>
                                        <div class="vh-future-lock-title">Карта приёма пока закрыта для редактирования</div>
                                        @if($visit['is_future'])
                                            <p>Приём назначен на <strong>{{ $visit['scheduled_start_label'] }}</strong>. До этого времени нельзя отметить явку, неявку или результат консультации, а также заполнить протокол.</p>
                                            <p class="vh-future-lock-hint">После наступления времени приёма обновите страницу. Результат консультации не проставляется автоматически: его подтвердит сотрудник.</p>
                                        @else
                                            <p>У этой записи нет корректной даты и времени начала. До уточнения даты карта приёма закрыта для редактирования.</p>
                                            <p class="vh-future-lock-hint">Уточните запись в регистратуре, затем обновите страницу.</p>
                                        @endif
                                    </div>
                                </div>
                            @elseif($visitHistoryHasEncounters && $visitHistoryCanEdit && !$visit['record_deleted'])
                                <form method="post" data-vh-encounter-form data-vh-format="{{ $visit['format'] }}" action="{{ route('doctors.patients.visits.encounter.save', ['patient' => $patient->id, 'registryRecord' => $visit['id']]) }}">
                                    @csrf
                                    <input type="hidden" name="_visit_id" value="{{ $visit['id'] }}">

                                    @if((string) old('_visit_id') === (string) $visit['id'] && $errors->getBag('encounter')->any())
                                        <div class="alert alert-danger" role="alert">
                                            <strong>Изменения не сохранены.</strong>
                                            <ul class="mb-0 mt-2">
                                                @foreach($errors->getBag('encounter')->all() as $message)
                                                    <li>{{ $message }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <section class="vh-section mt-0">
                                        <h5 class="vh-section-title"><i class="bi bi-person-check"></i>Явка и факт проведения</h5>
                                        <div class="vh-grid">
                                            <div class="vh-field">
                                                <label for="vh-attend-{{ $visit['id'] }}">Пациент</label>
                                                <select id="vh-attend-{{ $visit['id'] }}" name="attendance_status" class="form-select" data-vh-attendance required>
                                                    <option value="unknown" @selected($visit['form_attendance_status'] === 'unknown')>Явка ещё не отмечена</option>
                                                    <option value="present" @selected($visit['form_attendance_status'] === 'present')>{{ $visit['format'] === 'telemedicine' ? 'Подключился к консультации' : 'Пришёл на приём' }}</option>
                                                    <option value="absent" @selected($visit['form_attendance_status'] === 'absent')>{{ $visit['format'] === 'telemedicine' ? 'Не подключился' : 'Не явился' }}</option>
                                                </select>
                                            </div>
                                            <div class="vh-field">
                                                <label for="vh-outcome-{{ $visit['id'] }}">Консультация</label>
                                                <select id="vh-outcome-{{ $visit['id'] }}" name="consultation_status" class="form-select" data-vh-outcome required>
                                                    <option value="not_started" @selected($visit['form_consultation_status'] === 'not_started')>Не началась / результат не отмечен</option>
                                                    <option value="in_progress" @selected($visit['form_consultation_status'] === 'in_progress')>Проводится</option>
                                                    <option value="completed" @selected($visit['form_consultation_status'] === 'completed')>Состоялась</option>
                                                    <option value="not_held" @selected($visit['form_consultation_status'] === 'not_held')>Не состоялась</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="vh-field mt-3" data-vh-reason-wrap @if($visit['form_consultation_status'] !== 'not_held') hidden @endif>
                                            <label for="vh-noncompletion-{{ $visit['id'] }}">Причина несостоявшегося приёма <span data-vh-reason-optional>(при неявке пояснение необязательно)</span></label>
                                            <input class="form-control" type="text" id="vh-noncompletion-{{ $visit['id'] }}" name="noncompletion_reason" data-vh-reason maxlength="1000" placeholder="Неявка, технический сбой, перенос по инициативе врача" value="{{ $visit['form']['noncompletion_reason'] }}">
                                        </div>
                                        @if($visit['format'] === 'telemedicine')
                                            <div class="vh-marked warn"><i class="bi bi-camera-video me-1"></i>Подключение и факт консультации отмечаются сотрудником вручную. Эти отметки не заменяют технические журналы WebRTC.</div>
                                        @else
                                            <div class="vh-marked"><i class="bi bi-info-circle me-1"></i>Отметка регистратуры{{ $visit['registry_marked'] ? ' есть' : ' отсутствует' }}; сама по себе она не доказывает, что медицинский приём состоялся.</div>
                                        @endif
                                    </section>

                                    <div class="vh-inactive-note" data-vh-inactive-note role="status" aria-live="polite"
                                         @if($visit['form_attendance_status'] === 'present' && in_array($visit['form_consultation_status'], ['in_progress', 'completed'], true)) hidden @endif>
                                        <i class="bi bi-info-circle"></i><span data-vh-inactive-text>Приём не проводится — протокол осмотра не требуется. Сохраните статус записи.</span>
                                    </div>

                                    <section class="vh-section" data-vh-protocol @if(!($visit['form_attendance_status'] === 'present' && in_array($visit['form_consultation_status'], ['in_progress', 'completed'], true))) hidden @endif>
                                        <h5 class="vh-section-title"><i class="bi bi-clipboard2-pulse"></i>Протокол осмотра / консультации</h5>
                                        <div class="vh-grid">
                                            <div class="vh-field">
                                                <label for="vh-type-{{ $visit['id'] }}">Вид обращения</label>
                                                <select id="vh-type-{{ $visit['id'] }}" name="visit_type" class="form-select">
                                                    <option value="">Не выбран</option>
                                                    <option value="initial" @selected($visit['form']['visit_type'] === 'initial')>Первичный</option>
                                                    <option value="repeat" @selected($visit['form']['visit_type'] === 'repeat')>Повторный</option>
                                                </select>
                                            </div>
                                            <div class="vh-field">
                                                <label for="vh-condition-{{ $visit['id'] }}">Состояние пациента</label>
                                                <input class="form-control" type="text" id="vh-condition-{{ $visit['id'] }}" name="patient_condition" maxlength="255" placeholder="По данным осмотра / дистанционной оценки" value="{{ $visit['form']['patient_condition'] }}">
                                            </div>
                                            <div class="vh-field">
                                                <label for="vh-disability-{{ $visit['id'] }}">Группа инвалидности (при наличии)</label>
                                                <input class="form-control" type="text" id="vh-disability-{{ $visit['id'] }}" name="disability_group" maxlength="50" placeholder="Не указана" value="{{ $visit['form']['disability_group'] }}">
                                            </div>
                                            <div class="vh-field">
                                                <label for="vh-disability-details-{{ $visit['id'] }}">Сведения об инвалидности</label>
                                                <input class="form-control" type="text" id="vh-disability-details-{{ $visit['id'] }}" name="disability_details" maxlength="500" placeholder="Срок, причина, особенности" value="{{ $visit['form']['disability_details'] }}">
                                            </div>
                                            <div class="vh-field vh-span-all">
                                                <label for="vh-complaints-{{ $visit['id'] }}">Жалобы</label>
                                                <textarea class="form-control" id="vh-complaints-{{ $visit['id'] }}" name="complaints" maxlength="8000" rows="3" placeholder="Жалобы пациента в рамках этого обращения">{{ $visit['form']['complaints'] }}</textarea>
                                            </div>
                                            <div class="vh-field">
                                                <label for="vh-disease-{{ $visit['id'] }}">Анамнез заболевания</label>
                                                <textarea class="form-control" id="vh-disease-{{ $visit['id'] }}" name="disease_history" maxlength="16000" rows="4">{{ $visit['form']['disease_history'] }}</textarea>
                                            </div>
                                            <div class="vh-field">
                                                <label for="vh-life-{{ $visit['id'] }}">Анамнез жизни</label>
                                                <textarea class="form-control" id="vh-life-{{ $visit['id'] }}" name="life_history" maxlength="16000" rows="4">{{ $visit['form']['life_history'] }}</textarea>
                                            </div>
                                            <div class="vh-field">
                                                <label for="vh-social-{{ $visit['id'] }}">Социальный анамнез</label>
                                                <textarea class="form-control" id="vh-social-{{ $visit['id'] }}" name="social_history" maxlength="8000" rows="4">{{ $visit['form']['social_history'] }}</textarea>
                                            </div>
                                            <div class="vh-field">
                                                <label for="vh-mental-{{ $visit['id'] }}">Психический статус / наблюдения</label>
                                                <textarea class="form-control" id="vh-mental-{{ $visit['id'] }}" name="mental_status" maxlength="16000" rows="4" placeholder="Отметьте ограничения дистанционной оценки при телемедицине">{{ $visit['form']['mental_status'] }}</textarea>
                                            </div>
                                            <div class="vh-field">
                                                <label for="vh-diagnosis-code-{{ $visit['id'] }}">Диагноз — код МКБ</label>
                                                <input class="form-control" type="text" id="vh-diagnosis-code-{{ $visit['id'] }}" name="diagnosis_code" maxlength="24" value="{{ $visit['form']['diagnosis_code'] }}">
                                            </div>
                                            <div class="vh-field">
                                                <label for="vh-diagnosis-title-{{ $visit['id'] }}">Формулировка диагноза</label>
                                                <input class="form-control" type="text" id="vh-diagnosis-title-{{ $visit['id'] }}" name="diagnosis_title" maxlength="255" value="{{ $visit['form']['diagnosis_title'] }}">
                                            </div>
                                            <div class="vh-field vh-span-all">
                                                <label for="vh-conclusion-{{ $visit['id'] }}">Заключение консультации</label>
                                                <textarea class="form-control" id="vh-conclusion-{{ $visit['id'] }}" name="conclusion" maxlength="16000" rows="5" placeholder="Основные результаты осмотра и клиническое заключение">{{ $visit['form']['conclusion'] }}</textarea>
                                            </div>
                                            <div class="vh-field vh-span-all">
                                                <label for="vh-recs-{{ $visit['id'] }}">Рекомендации по наблюдению, обследованию и лечению</label>
                                                <textarea class="form-control" id="vh-recs-{{ $visit['id'] }}" name="recommendations" maxlength="16000" rows="4">{{ $visit['form']['recommendations'] }}</textarea>
                                            </div>
                                            <div class="vh-field">
                                                <label for="vh-service-code-{{ $visit['id'] }}">Код оказанной услуги</label>
                                                <input class="form-control" type="text" id="vh-service-code-{{ $visit['id'] }}" name="service_code" maxlength="32" placeholder="По классификатору клиники" value="{{ $visit['form']['service_code'] }}">
                                            </div>
                                            <div class="vh-field">
                                                <label for="vh-service-name-{{ $visit['id'] }}">Наименование услуги</label>
                                                <input class="form-control" type="text" id="vh-service-name-{{ $visit['id'] }}" name="service_name" maxlength="255" value="{{ $visit['form']['service_name'] }}">
                                            </div>
                                        </div>
                                    </section>
                                    <div class="vh-actions">
                                        <small><i class="bi bi-shield-exclamation me-1"></i>Сохранение не создаёт электронную подпись и не подтверждает технический факт видеосвязи. Изменения фиксируются с указанием пользователя.</small>
                                        <button class="btn vh-save" type="submit"><i class="bi bi-check2-circle me-1"></i><span data-vh-save-text>Сохранить отметку</span></button>
                                    </div>
                                </form>
                            @elseif($visit['has_encounter'])
                                <div class="vh-section mt-0">
                                    <div class="vh-read-row"><b>Явка</b><p>{{ $visit['attendance_label'] }}</p></div>
                                    <div class="vh-read-row"><b>Консультация</b><p>{{ $visit['consultation_label'] }}</p></div>
                                    @if($visit['consultation_status'] === 'not_held')
                                        <div class="vh-read-row"><b>Причина несостоявшейся консультации</b><p>{{ $visit['protocol']['noncompletion_reason'] ?: ($visit['attendance_status'] === 'absent' ? $visit['attendance_label'] : 'Не указана') }}</p></div>
                                        <div class="vh-marked warn">Консультация не состоялась. Протокол медицинского осмотра не оформляется.</div>
                                    @elseif($visit['can_show_clinical_data'])
                                        @foreach([
                                            'Тип обращения' => 'visit_type',
                                            'Состояние пациента' => 'patient_condition',
                                            'Группа инвалидности' => 'disability_group',
                                            'Сведения об инвалидности' => 'disability_details',
                                            'Жалобы' => 'complaints',
                                            'Анамнез заболевания' => 'disease_history',
                                            'Анамнез жизни' => 'life_history',
                                            'Социальный анамнез' => 'social_history',
                                            'Психический статус / наблюдения' => 'mental_status',
                                            'Код МКБ' => 'diagnosis_code',
                                            'Диагноз' => 'diagnosis_title',
                                            'Заключение' => 'conclusion',
                                            'Рекомендации' => 'recommendations',
                                            'Код услуги' => 'service_code',
                                            'Оказанная услуга' => 'service_name',
                                        ] as $label => $key)
                                            @if($visit['protocol'][$key] !== '')
                                                <div class="vh-read-row"><b>{{ $label }}</b><p>{{ $key === 'visit_type' ? $visit['visit_type_label'] : $visit['protocol'][$key] }}</p></div>
                                            @endif
                                        @endforeach
                                        @unless($visit['has_protocol'])
                                            <div class="vh-marked">Это текущий черновик консультации. Печатная форма доступна после её завершения.</div>
                                        @endunless
                                    @else
                                        <div class="vh-marked">Факт проведения консультации не подтверждён. Протокол не требуется.</div>
                                    @endif
                                </div>
                            @else
                                <div class="vh-marked">Консультация ещё не отмечена как состоявшаяся. Протокол не требуется до начала осмотра.</div>
                            @endif
                            @if($visit['updated_at'])
                                <div class="text-muted mt-3 small"><i class="bi bi-clock-history me-1"></i>Последнее изменение: {{ $visit['updated_at'] }} · пользователь № {{ $visit['updated_by_user_id'] ?? '—' }}</div>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
            @if($visitHistory->hasPages())
                <div class="mt-4">{{ $visitHistory->links('pagination::bootstrap-5') }}</div>
            @endif
        @else
            <div class="vh-empty">
                <i class="bi bi-calendar-x"></i>
                <h4>По выбранным параметрам ничего не найдено</h4>
                <div class="text-muted">Попробуйте изменить фильтры либо сбросить их.</div>
            </div>
        @endif

        <div class="vh-help"><i class="bi bi-info-circle"></i><span>Прошедшая дата записи, отметка регистратуры и ручной статус — разные данные. Для подтверждения подключения к WebRTC понадобится связать журнал с серверными событиями видеосвязи; пока техническая длительность и успешность соединения не вычисляются.</span></div>
    @endif
</div>

<script>
    // UI only: the server independently validates attendance, outcome and
    // clinical fields. Keeping a draft hidden does not send it to Laravel.
    (function () {
        'use strict';

        function initEncounterForm(form) {
            if (form.dataset.vhInitialized === '1') return;
            form.dataset.vhInitialized = '1';

            const attendance = form.querySelector('[data-vh-attendance]');
            const outcome = form.querySelector('[data-vh-outcome]');
            const protocol = form.querySelector('[data-vh-protocol]');
            const reasonWrap = form.querySelector('[data-vh-reason-wrap]');
            const reason = form.querySelector('[data-vh-reason]');
            const reasonOptional = form.querySelector('[data-vh-reason-optional]');
            const note = form.querySelector('[data-vh-inactive-note]');
            const noteText = form.querySelector('[data-vh-inactive-text]');
            const saveText = form.querySelector('[data-vh-save-text]');
            if (!attendance || !outcome || !protocol || !reasonWrap || !reason) return;

            function refresh() {
                const absent = attendance.value === 'absent';
                const present = attendance.value === 'present';

                if (absent) outcome.value = 'not_held';
                for (const option of Array.from(outcome.options)) {
                    option.disabled = !present && (option.value === 'in_progress' || option.value === 'completed');
                    if (absent && option.value !== 'not_held') option.disabled = true;
                }
                if (!present && ['in_progress', 'completed'].includes(outcome.value)) {
                    outcome.value = absent ? 'not_held' : 'not_started';
                }

                const notHeld = outcome.value === 'not_held';
                const clinical = present && ['in_progress', 'completed'].includes(outcome.value);

                protocol.hidden = !clinical;
                // Hidden inputs must never be submitted or validated by the browser.
                protocol.querySelectorAll('input, select, textarea').forEach(function (field) {
                    field.disabled = !clinical;
                });
                const visitType = protocol.querySelector('[name="visit_type"]');
                const conclusion = protocol.querySelector('[name="conclusion"]');
                if (visitType) visitType.required = clinical && outcome.value === 'completed';
                if (conclusion) conclusion.required = clinical && outcome.value === 'completed';

                reasonWrap.hidden = !notHeld;
                reason.disabled = !notHeld;
                reason.required = notHeld && !absent;
                if (reasonOptional) reasonOptional.textContent = absent
                    ? '(пояснение необязательно)'
                    : '(обязательно)';
                note.hidden = clinical;

                const isTelemedicine = form.dataset.vhFormat === 'telemedicine';
                if (absent) {
                    noteText.textContent = isTelemedicine
                        ? 'Пациент не подключился. Достаточно сохранить неявку и, при необходимости, пояснение. Протокол не нужен.'
                        : 'Пациент не явился. Достаточно сохранить неявку и, при необходимости, пояснение. Протокол не нужен.';
                    saveText.textContent = 'Зафиксировать неявку';
                } else if (notHeld) {
                    noteText.textContent = 'Консультация не состоялась. Укажите причину и сохраните отметку без протокола.';
                    saveText.textContent = 'Сохранить причину';
                } else if (!present) {
                    noteText.textContent = 'Сначала подтвердите явку или подключение пациента и начало консультации. До этого протокол не заполняется.';
                    saveText.textContent = 'Сохранить статус';
                } else if (outcome.value === 'not_started') {
                    noteText.textContent = 'Пациент присутствует, но консультация ещё не началась. Протокол пока не требуется.';
                    saveText.textContent = 'Сохранить статус';
                } else {
                    saveText.textContent = outcome.value === 'completed'
                        ? 'Завершить и сохранить протокол'
                        : 'Сохранить черновик приёма';
                }
            }

            attendance.addEventListener('change', refresh);
            outcome.addEventListener('change', refresh);
            refresh();
        }

        function init() {
            document.querySelectorAll('#pane-visits [data-vh-encounter-form]')
                .forEach(initEncounterForm);
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, { once: true });
        } else {
            init();
        }
    })();
</script>
