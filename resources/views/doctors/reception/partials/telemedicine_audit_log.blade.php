<section class="mc-audit-log" aria-labelledby="mcTelemedicineLogTitle">
    <div class="mc-audit-log__hero">
        <div class="mc-audit-log__hero-icon"><i class="bi bi-camera-video" aria-hidden="true"></i></div>
        <div>
            <h2 id="mcTelemedicineLogTitle">Журнал телемедицины</h2>
            <p>История видеосеансов, технических подключений и врачебных подтверждений по каждой записи в расписании.</p>
        </div>
    </div>

    {{-- NORHNAIM_TELEMED_JOURNAL_V1 --}}
    @include('doctors.reception.partials.telemedicine_journal_toolbar')
    {{-- clinic-telemedicine-timezones:timezone-journal-display-control:v1:begin --}}
    @include('shared.appointment_timezone_control')
    {{-- clinic-telemedicine-timezones:timezone-journal-display-control:v1:end --}}

    @if(!$medicalVisitAuditReady)
        <div class="alert alert-warning" role="status">Журнал ещё не создан. Необходимо выполнить миграции.</div>
    @else
        @if(!$telemedicineEventHookConfigured)
            <div class="alert alert-info" role="note">
                <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                Нет подтверждённой связи signaling-сервера с Laravel (не получен подписанный heartbeat за последние две минуты). Проверяйте настройки аудита и контейнер WebRTC. Отметки врача сохраняются независимо.
            </div>
        @endif
        @forelse($telemedicineLog as $call)
            <div class="mc-tm-entry">
            <details class="mc-audit-session" id="telemed-call-{{ $call['id'] }}" @if(request('telemed_open') == $call['id']) open @endif>
                <summary class="mc-audit-session__summary">
                    <span class="mc-audit-session__date"><i class="bi bi-calendar-event me-1" aria-hidden="true"></i>{{ $call['date'] }}</span>
                    <span class="mc-audit-session__name">{{ $call['doctor'] }} <small>Приём № {{ $call['id'] }}</small></span>
                    <span class="mc-audit-session__status">
                        <span class="mc-audit-status mc-tm-status--{{ $call['journal']['state'] }}">{{ $call['journal']['label'] }}</span>
                    </span>
                    <span class="mc-audit-session__count">{{ $call['event_count'] }} событий <i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                </summary>
                <div class="mc-audit-session__body">
                    {{-- clinic-telemedicine-timezones:timezone-journal-display:v1:begin --}}
                    @include('shared.appointment_timezone_display', ['appointmentTime' => $call['appointment_time']])
                    {{-- clinic-telemedicine-timezones:timezone-journal-display:v1:end --}}
                    <div class="mc-audit-summary">
                        <div class="mc-audit-summary__item">
                            <span>Врач подключался</span>
                            <strong>{{ $call['doctor_joined'] ? 'Зафиксировано' : 'Нет данных' }}</strong>
                        </div>
                        <div class="mc-audit-summary__item">
                            <span>Пациент подключался</span>
                            <strong>{{ $call['patient_joined'] ? 'Зафиксировано' : 'Нет данных' }}</strong>
                        </div>
                        <div class="mc-audit-summary__item">
                            <span>Оба устройства сообщили о медиасоединении</span>
                            <strong>{{ $call['both_media_connected'] ? 'Сообщили оба устройства' : 'Нет данных' }}</strong>
                        </div>
                        <div class="mc-audit-summary__item">
                            <span>Медицинский результат</span>
                            <strong>
                                @if($call['confirmation_outcome'] === 'held') Подтверждён врачом
                                @elseif($call['confirmation_outcome'] === 'not_held') Не состоялся
                                @else Не подтверждён @endif
                            </strong>
                        </div>
                    </div>
                    @if($call['confirmation_at'])
                        <p class="mc-audit-session__confirmed">
                            <i class="bi bi-person-check me-1" aria-hidden="true"></i>
                            {{ $call['confirmation_by'] }} · {{ $call['confirmation_at'] }}
                            @if($call['confirmation_reason']) · Причина: {{ $call['confirmation_reason'] }} @endif
                        </p>
                    @endif
                    @if($call['deleted'])
                        <div class="alert alert-secondary">Запись в расписании отменена. Архив технических событий доступен только для просмотра.</div>
                    @endif
                    @if($call['can_confirm'])
                        <div class="mc-audit-session__action-bar">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal" data-bs-target="#modalConfirmMedicalVisit"
                                    data-medical-visit-confirm
                                    data-visit-id="{{ $call['id'] }}"
                                    data-visit-date="{{ $call['date'] }}"
                                    data-visit-doctor="{{ $call['doctor'] }}"
                                    data-visit-previous-outcome="{{ $call['confirmation_outcome'] ?? '' }}"
                                    data-visit-previous-reason="{{ $call['confirmation_reason'] ?? '' }}">
                                <i class="bi bi-patch-check me-1" aria-hidden="true"></i>
                                {{ $call['confirmation_outcome'] ? 'Исправить подтверждение' : 'Подтвердить результат приёма' }}
                            </button>
                        </div>
                    @endif
                    {{-- clinic-telemedicine-timezones:provider-journal-action:v1:begin --}}
                    @include('doctors.reception.partials.telemedicine_provider_action')
                    {{-- clinic-telemedicine-timezones:provider-journal-action:v1:end --}}
                    <div class="mc-audit-session__timeline-title">Хронология событий</div>
                    @if($call['events_truncated'])
                        <p class="small text-muted">Показаны последние 60 событий этого приёма; более ранние записи сохранены в базе.</p>
                    @endif
                    @if(empty($call['events']))
                        <div class="mc-audit-log__empty">Технических событий и отметок врача ещё нет. Это не доказательство того, что подключений не было.</div>
                    @else
                        <ol class="mc-audit-timeline">
                            @foreach($call['events'] as $event)
                                <li class="mc-audit-timeline__item">
                                    <time>{{ $event['at'] }}</time>
                                    <span class="mc-audit-timeline__marker mc-audit-timeline__marker--{{ $event['source'] === 'signaling' ? 'tech' : 'manual' }}"></span>
                                    <div class="mc-audit-timeline__description">
                                        <strong>{{ $event['label'] }}</strong>
                                        <span>{{ match ($event['source']) { 'signaling' => 'Сервер WebRTC', 'staff' => 'Управление сеансом', 'patient_link' => 'Приглашение пациента', 'system' => 'Система', default => 'Отметка врача' } }} · {{ $event['role'] }}</span>
                                        @if($event['is_correction'])
                                            <span class="mc-audit-timeline__correction">Исправление ранее сохранённого результата</span>
                                            @if($event['correction_reason'])
                                                <span>Причина исправления: {{ $event['correction_reason'] }}</span>
                                            @endif
                                        @endif
                                        @if(!empty($event['management_reason']))<span>Причина удаления: {{ $event['management_reason'] }}</span>@endif
                                        @if($event['reason_code'])<span>Причина соединения: {{ $event['reason_code'] }}</span>@endif
                                        @if($event['duration_seconds'] !== null)<span>Длительность события: {{ $event['duration_seconds'] }} сек.</span>@endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                    <p class="mc-audit-session__disclaimer">События WebRTC подтверждают только техническое соединение. Факт проведения консультации подтверждает врач отдельной отметкой.</p>
                </div>
            </details>
            @include('doctors.reception.partials.telemedicine_journal_action')
            </div>
        @empty
            <div class="mc-audit-log__empty"><i class="bi bi-camera-video-off" aria-hidden="true"></i> {{ $telemedManager['show_archive'] ? 'В архиве нет удалённых сеансов.' : 'Действующих записей на телемедицинские приёмы нет.' }}</div>
        @endforelse
        @if($telemedicineLog->hasPages())
            <div class="mt-3">{{ $telemedicineLog->links() }}</div>
        @endif
    @endif
</section>
@include('doctors.reception.partials.telemedicine_journal_modals')
