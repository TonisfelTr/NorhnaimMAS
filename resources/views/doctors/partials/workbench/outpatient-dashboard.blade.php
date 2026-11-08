{{-- Read-only outpatient dashboard. All figures are calculated in App\Support\OutpatientDashboard. --}}
<style>
    .nh-out { display: grid; gap: 18px; }
    .nh-out, .nh-out * { box-sizing: border-box; }
    .nh-out__toolbar { display: flex; align-items: end; justify-content: space-between; gap: 18px; flex-wrap: wrap; }
    .nh-out__intro { min-width: 220px; }
    .nh-out__label { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 7px; font-size: 11px; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; color: #078a99; }
    .nh-out__label::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #22c4bf; }
    .nh-out__heading { color: #1e4355; font-size: 18px; font-weight: 790; margin: 0 0 6px; }
    .nh-out__sub { margin: 0; color: #728d9b; font-size: 12px; line-height: 1.65; }
    .nh-out__filters { display: flex; align-items: end; justify-content: flex-end; gap: 9px; flex-wrap: wrap; }
    .nh-out__field { min-width: 150px; }
    .nh-out__field--clinic { min-width: 250px; max-width: 380px; }
    .nh-out__field label { display: block; font-weight: 700; font-size: 11px; color: #577888; margin-bottom: 5px; }
    .nh-out__field .form-select { height: 38px; font-size: 12px; border-color: #d7e5e9; border-radius: 10px; }
    .nh-out__submit { min-height: 38px; padding-inline: 16px; white-space: nowrap; font-size: 12px; font-weight: 750; border-radius: 10px; }
    .nh-out__context { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; padding: 12px 15px; border: 1px solid #d8eaf0; background: #f1fbfc; border-radius: 12px; }
    .nh-out__context-name { display: flex; align-items: center; gap: 8px; min-width: 0; font-weight: 770; color: #146b7f; font-size: 12px; }
    .nh-out__context-name i { font-size: 17px; }
    .nh-out__context-date { color: #638593; font-weight: 650; font-size: 12px; }
    .nh-out__metrics { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 15px; }
    .nh-out__metric { position: relative; min-height: 155px; padding: 22px; border: 1px solid #e2eaee; background: #fff; border-radius: 18px; box-shadow: 0 7px 22px rgba(24,64,84,.04); }
    .nh-out__metric-top { display: flex; justify-content: space-between; gap: 9px; align-items: flex-start; }
    .nh-out__metric-title { font-size: 12px; font-weight: 760; color: #117f90; }
    .nh-out__metric-icon { display: flex; align-items: center; justify-content: center; background: #eaf8f9; width: 40px; height: 40px; border-radius: 12px; flex: 0 0 40px; color: #0e94a2; }
    .nh-out__metric-icon i { font-size: 18px; }
    .nh-out__metric-value { display: block; color: #184456; font-size: 32px; font-weight: 800; line-height: 1.15; margin: 7px 0 5px; }
    .nh-out__metric-note { color: #7b95a1; font-size: 11.5px; line-height: 1.5; }
    .nh-out__panels { display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(270px, 1fr); gap: 16px; align-items: stretch; }
    .nh-out__panel { padding: 22px; background: #fff; border: 1px solid #e1eaf0; border-radius: 18px; box-shadow: 0 7px 22px rgba(24,64,84,.035); min-width: 0; }
    .nh-out__panel-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
    .nh-out__panel-title { margin: 0 0 5px; font-size: 17px; font-weight: 790; color: #1b4658; }
    .nh-out__panel-sub { margin: 0; color: #7a939e; font-size: 11.5px; line-height: 1.55; }
    .nh-out__link { color: #078895; font-weight: 750; font-size: 12px; text-decoration: none; white-space: nowrap; }
    .nh-out__link:hover { text-decoration: underline; }
    .nh-out__table-wrap { overflow-x: auto; }
    .nh-out__table { border-collapse: collapse; width: 100%; min-width: 630px; }
    .nh-out__table th { padding: 10px 9px; text-align: left; white-space: nowrap; font-size: 11px; font-weight: 750; color: #6e8896; border-bottom: 1px solid #e7eff2; background: #f7fafb; }
    .nh-out__table td { padding: 13px 9px; font-size: 12px; color: #32586a; border-bottom: 1px solid #edf2f5; vertical-align: middle; }
    .nh-out__table tbody tr:last-child td { border-bottom: 0; }
    .nh-out__patient { font-weight: 750; color: #235569; }
    .nh-out__doctor { color: #526e7e; }
    .nh-out__time { white-space: nowrap; font-weight: 750; color: #214f61; }
    .nh-out__date { display: block; margin-bottom: 3px; font-size: 10px; color: #819aa7; }
    .nh-out__chip { display: inline-block; border: 1px solid #e3eaf0; border-radius: 100px; padding: 5px 8px; font-size: 10.5px; font-weight: 700; white-space: nowrap; color: #648494; background: #f5f9fb; }
    .nh-out__chip--success { background: #e8f8ef; border-color: #d4f2df; color: #28845a; }
    .nh-out__chip--warning { background: #fff7e8; border-color: #f5e3ba; color: #996c24; }
    .nh-out__chip--info { background: #e9f6fc; border-color: #cfedf9; color: #2784a2; }
    .nh-out__empty { padding: 34px 18px; display: grid; justify-items: center; text-align: center; gap: 8px; color: #79909c; }
    .nh-out__empty i { font-size: 28px; color: #8abfc7; }
    .nh-out__empty strong { font-size: 13px; color: #446779; }
    .nh-out__attention { display: grid; gap: 10px; }
    .nh-out__notice { display: flex; align-items: flex-start; gap: 11px; padding: 13px 14px; background: #f6fafb; border: 1px solid #e8f0f3; border-radius: 12px; }
    .nh-out__notice > i { color: #1897a4; font-size: 18px; line-height: 1.2; }
    .nh-out__notice--warning { background: #fffbf3; border-color: #f5e9d2; }
    .nh-out__notice--warning > i { color: #c88d33; }
    .nh-out__notice-title { display: block; font-size: 12px; font-weight: 770; color: #2e5669; margin-bottom: 4px; }
    .nh-out__notice-desc { margin: 0; font-size: 11.5px; line-height: 1.6; color: #6f8a98; }
    .nh-out__actions { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 9px; margin-top: 16px; }
    .nh-out__action { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 10px; border-radius: 10px; border: 1px solid #cfe5eb; color: #087e8f; background: #f2fafc; text-decoration: none; font-size: 12px; font-weight: 750; cursor: pointer; }
    .nh-out__action:hover { background: #e7f7fa; color: #05768a; }
    .nh-out__footnote { font-size: 11.5px; color: #79909c; line-height: 1.6; margin: 0; }
    @media(max-width: 1150px) { .nh-out__metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); } .nh-out__panels { grid-template-columns: 1fr; } }
    @media(max-width: 700px) { .nh-out__metrics { grid-template-columns: 1fr; } .nh-out__toolbar { align-items: stretch; } .nh-out__filters { width: 100%; justify-content: stretch; } .nh-out__field, .nh-out__field--clinic { flex: 1 1 100%; min-width: 0; max-width: none; } .nh-out__submit { width: 100%; } .nh-out__actions { grid-template-columns: 1fr; } .nh-out__panel, .nh-out__metric { padding: 17px; } }
</style>

<section class="nh-out" aria-labelledby="nhOutTitle">
    <div class="nh-out__toolbar">
        <div class="nh-out__intro">
            <span class="nh-out__label">Амбулаторный приём</span>
            <h2 class="nh-out__heading" id="nhOutTitle">Рабочий обзор</h2>
            <p class="nh-out__sub">Реальные записи, врачебная нагрузка и результаты приёмов.</p>
        </div>
        <form method="get" action="{{ route('doctors.main') }}" class="nh-out__filters" aria-label="Фильтры амбулаторного обзора">
            <input type="hidden" name="mode" value="outpatient">
            <input type="hidden" name="tab" value="overview">
            @if($outpatientDashboard['is_admin'])
                <div class="nh-out__field nh-out__field--clinic">
                    <label for="nhOutClinic">Клиника</label>
                    <select id="nhOutClinic" name="clinic_id" class="form-select" aria-label="Выбор амбулаторной клиники">
                        <option value="">Все амбулатории</option>
                        @foreach($outpatientDashboard['clinic_options'] as $clinic)
                            <option value="{{ $clinic->id }}" @selected((int) $clinic->id === $outpatientDashboard['selected_clinic_id'])>{{ $clinic->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="nh-out__field">
                <label for="nhOutPeriod">Период</label>
                <select id="nhOutPeriod" name="period" class="form-select" aria-label="Период статистики">
                    <option value="today" @selected($outpatientDashboard['period'] === 'today')>Сегодня</option>
                    <option value="week" @selected($outpatientDashboard['period'] === 'week')>Неделя</option>
                    <option value="month" @selected($outpatientDashboard['period'] === 'month')>Месяц</option>
                </select>
            </div>
            <button type="submit" class="btn btn-info text-white nh-out__submit">Показать</button>
        </form>
    </div>

    <div class="nh-out__context">
        <span class="nh-out__context-name"><i class="bi bi-building" aria-hidden="true"></i> {{ $outpatientDashboard['selected_clinic_name'] }}</span>
        <span class="nh-out__context-date">{{ $outpatientDashboard['period_title'] }} · {{ $outpatientDashboard['period_dates'] }}</span>
    </div>

    <div class="nh-out__metrics" aria-label="Статистика амбулаторных записей">
        <article class="nh-out__metric">
            <div class="nh-out__metric-top"><span class="nh-out__metric-title">Всего записей</span><span class="nh-out__metric-icon"><i class="bi bi-calendar2-week" aria-hidden="true"></i></span></div>
            <strong class="nh-out__metric-value">{{ $outpatientDashboard['appointments'] }}</strong>
            <span class="nh-out__metric-note">Записей за выбранный период</span>
        </article>
        <article class="nh-out__metric">
            <div class="nh-out__metric-top"><span class="nh-out__metric-title">Предстоящие приёмы</span><span class="nh-out__metric-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span></div>
            <strong class="nh-out__metric-value">{{ $outpatientDashboard['upcoming_count'] }}</strong>
            <span class="nh-out__metric-note">Время записи ещё не наступило</span>
        </article>
        <article class="nh-out__metric">
            <div class="nh-out__metric-top"><span class="nh-out__metric-title">Завершённые приёмы</span><span class="nh-out__metric-icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></span></div>
            <strong class="nh-out__metric-value">{{ $outpatientDashboard['completed_count'] ?? '—' }}</strong>
            <span class="nh-out__metric-note">Подтверждены в журнале приёмов</span>
        </article>
        <article class="nh-out__metric">
            <div class="nh-out__metric-top"><span class="nh-out__metric-title">Неявки</span><span class="nh-out__metric-icon"><i class="bi bi-person-x" aria-hidden="true"></i></span></div>
            <strong class="nh-out__metric-value">{{ $outpatientDashboard['no_show_count'] ?? '—' }}</strong>
            <span class="nh-out__metric-note">Отметка «Пациент не пришёл»</span>
        </article>
        <article class="nh-out__metric">
            <div class="nh-out__metric-top"><span class="nh-out__metric-title">Телемедицина</span><span class="nh-out__metric-icon"><i class="bi bi-camera-video" aria-hidden="true"></i></span></div>
            <strong class="nh-out__metric-value">{{ $outpatientDashboard['telemedicine_count'] ?? '—' }}</strong>
            <span class="nh-out__metric-note">Записей в формате видеоконсультации</span>
        </article>
        <article class="nh-out__metric">
            <div class="nh-out__metric-top"><span class="nh-out__metric-title">Врачи с записями</span><span class="nh-out__metric-icon"><i class="bi bi-people" aria-hidden="true"></i></span></div>
            <strong class="nh-out__metric-value">{{ $outpatientDashboard['doctors_with_appointments'] }}</strong>
            <span class="nh-out__metric-note">Уникальные врачи по журналу записей</span>
        </article>
    </div>

    <div class="nh-out__panels">
        <section class="nh-out__panel" aria-labelledby="nhOutUpcoming">
            <div class="nh-out__panel-head">
                <div>
                    <h3 class="nh-out__panel-title" id="nhOutUpcoming">Ближайшие приёмы</h3>
                    <p class="nh-out__panel-sub">Следующие записи в рамках выбранного периода</p>
                </div>
                @if(!$outpatientDashboard['is_admin'] || $outpatientDashboard['selected_clinic_id'] !== null)
                    <a class="nh-out__link" href="{{ route('doctors.reception.index', $outpatientDashboard['is_admin'] ? ['clinic_id' => $outpatientDashboard['selected_clinic_id']] : []) }}">Регистратура →</a>
                @endif
            </div>
            @if(count($outpatientDashboard['upcoming']))
                <div class="nh-out__table-wrap">
                    <table class="nh-out__table">
                        <thead>
                        <tr>
                            <th scope="col">Когда</th>
                            <th scope="col">Пациент</th>
                            <th scope="col">Врач</th>
                            @if($outpatientDashboard['show_clinic_column'])
                                <th scope="col">Клиника</th>
                            @endif
                            <th scope="col">Формат</th>
                            <th scope="col">Статус</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($outpatientDashboard['upcoming'] as $appointment)
                            <tr>
                                <td class="nh-out__time"><span class="nh-out__date">{{ $appointment['date'] }}</span>{{ $appointment['time'] }}</td>
                                <td class="nh-out__patient">{{ $appointment['patient'] }}</td>
                                <td class="nh-out__doctor">{{ $appointment['doctor'] }}</td>
                                @if($outpatientDashboard['show_clinic_column'])
                                    <td>{{ $appointment['clinic'] }}</td>
                                @endif
                                <td>{{ $appointment['format'] }}</td>
                                <td><span class="nh-out__chip nh-out__chip--{{ $appointment['status_tone'] }}">{{ $appointment['status'] }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="nh-out__empty" role="status">
                    <i class="bi bi-calendar2-check" aria-hidden="true"></i>
                    <strong>Предстоящих записей нет</strong>
                    <span class="nh-out__sub">За выбранный период новые приёмы пока не запланированы.</span>
                </div>
            @endif
        </section>

        <aside class="nh-out__panel" aria-labelledby="nhOutAttention">
            <div class="nh-out__panel-head">
                <div>
                    <h3 class="nh-out__panel-title" id="nhOutAttention">Требуют внимания</h3>
                    <p class="nh-out__panel-sub">Показатели по выбранной клинике и периоду</p>
                </div>
            </div>
            <div class="nh-out__attention">
                <div class="nh-out__notice {{ $outpatientDashboard['without_doctor_count'] > 0 ? 'nh-out__notice--warning' : '' }}">
                    <i class="bi bi-person-exclamation" aria-hidden="true"></i>
                    <div>
                        <span class="nh-out__notice-title">Без назначенного врача: {{ $outpatientDashboard['without_doctor_count'] }}</span>
                        <p class="nh-out__notice-desc">Записи, в которых не указан врач.</p>
                    </div>
                </div>
                @if($outpatientDashboard['encounters_available'])
                    <div class="nh-out__notice {{ $outpatientDashboard['not_held_count'] > 0 ? 'nh-out__notice--warning' : '' }}">
                        <i class="bi bi-clipboard2-pulse" aria-hidden="true"></i>
                        <div>
                            <span class="nh-out__notice-title">Несостоявшиеся приёмы: {{ $outpatientDashboard['not_held_count'] }}</span>
                            <p class="nh-out__notice-desc">Отмечены врачом как «Сеанс не состоялся».</p>
                        </div>
                    </div>
                @else
                    <div class="nh-out__notice">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        <div>
                            <span class="nh-out__notice-title">Журнал результатов приёмов не подключён</span>
                            <p class="nh-out__notice-desc">Показатели завершений и неявок будут доступны после миграции medical_encounters. Прочерк — не ноль.</p>
                        </div>
                    </div>
                @endif
                @if(!$outpatientDashboard['format_available'])
                    <div class="nh-out__notice">
                        <i class="bi bi-camera-video" aria-hidden="true"></i>
                        <div>
                            <span class="nh-out__notice-title">Формат приёма не определён</span>
                            <p class="nh-out__notice-desc">Поле appointment_format в записях пока недоступно.</p>
                        </div>
                    </div>
                @endif
            </div>
            <div class="nh-out__actions">
                @if($outpatientDashboard['is_admin'] && $outpatientDashboard['selected_clinic_id'] === null)
                    <button type="button" class="nh-out__action" data-bs-toggle="modal" data-bs-target="#workbenchOutpatientNavigationModal"><i class="bi bi-people" aria-hidden="true"></i> Регистратура</button>
                    <button type="button" class="nh-out__action" data-bs-toggle="modal" data-bs-target="#workbenchOutpatientNavigationModal"><i class="bi bi-person-plus" aria-hidden="true"></i> Записать</button>
                @else
                    <a class="nh-out__action" href="{{ route('doctors.reception.index', $outpatientDashboard['is_admin'] ? ['clinic_id' => $outpatientDashboard['selected_clinic_id']] : []) }}"><i class="bi bi-people" aria-hidden="true"></i> Регистратура</a>
                    <a class="nh-out__action" href="{{ route('doctors.reception.create', $outpatientDashboard['is_admin'] ? ['clinic_id' => $outpatientDashboard['selected_clinic_id']] : []) }}"><i class="bi bi-person-plus" aria-hidden="true"></i> Записать</a>
                @endif
            </div>
        </aside>
    </div>
    <p class="nh-out__footnote">Показатели рассчитаны по фактическим записям амбулаторных клиник. «Врачи с записями» не означает «врачи на смене». Сведения об исходе приёма берутся только из отметок медкарты.</p>
</section>
