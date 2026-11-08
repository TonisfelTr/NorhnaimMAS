{{-- Formal clinical attestation is independent of WebRTC transport events. --}}
<section class="mc-audit-panel mb-4" aria-labelledby="mcVisitConfirmTitle">
    <div class="mc-audit-panel__head">
        <span class="mc-audit-panel__icon"><i class="bi bi-patch-check" aria-hidden="true"></i></span>
        <div class="mc-audit-panel__heading">
            <h2 id="mcVisitConfirmTitle">Подтверждение состоявшихся приёмов</h2>
            <p>Приём считается подтверждённым только после отметки врача. Запись в расписании и подключение к видеосвязи сами по себе не подтверждают консультацию.</p>
        </div>
    </div>

    @if(session('visit_confirmation_success'))
        <div class="alert alert-success mx-3 mt-3" role="status">{{ session('visit_confirmation_success') }}</div>
    @endif

    @if($errors->visitConfirmation->any())
        <div class="alert alert-danger mx-3 mt-3" role="alert">
            <strong>Не удалось сохранить подтверждение:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->visitConfirmation->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!$medicalVisitAuditReady)
        <div class="alert alert-warning m-3" role="status">
            Журнал подтверждений ещё не подключён. Сначала выполните миграции из комплекта.
        </div>
    @elseif($visitHistory->count() === 0)
        <div class="mc-audit-panel__empty">Для выбранных фильтров записей на приём нет.</div>
    @else
        <div class="mc-audit-appointments">
            @foreach($visitHistory as $visit)
                <div class="mc-audit-appointment" id="confirm-visit-{{ $visit['id'] }}">
                    <div class="mc-audit-appointment__details">
                        <div class="mc-audit-appointment__date">
                            <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>
                            {{ $visit['date'] }} в {{ $visit['time'] }}
                            <span class="mc-audit-appointment__id">№ {{ $visit['id'] }}</span>
                        </div>
                        <div class="mc-audit-appointment__meta">
                            {{ $visit['doctor_name'] }} · {{ $visit['format_label'] }}
                        </div>
                        @if($visit['confirmation_at'])
                            <div class="mc-audit-appointment__meta">
                                {{ $visit['confirmation_by'] }} · {{ $visit['confirmation_at'] }}
                            </div>
                        @endif
                        @if($visit['confirmation_outcome'] === 'not_held' && $visit['confirmation_reason'])
                            <div class="mc-audit-appointment__note">Причина: {{ $visit['confirmation_reason'] }}</div>
                        @endif
                        @if(count($visit['confirmation_history']))
                            <details class="mc-audit-confirmation-history">
                                <summary>История подтверждений ({{ count($visit['confirmation_history']) }})</summary>
                                <ol>
                                    @foreach($visit['confirmation_history'] as $entry)
                                        <li>
                                            {{ $entry['at'] }} · {{ $entry['by'] }}: {{ $entry['outcome'] }}
                                            @if($entry['is_correction']) — исправление @endif
                                            @if($entry['correction_reason'])
                                                <span>Причина: {{ $entry['correction_reason'] }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ol>
                            </details>
                        @endif
                    </div>
                    <div class="mc-audit-appointment__actions">
                        @if($visit['confirmation_outcome'] === 'held')
                            <span class="mc-audit-status mc-audit-status--held">
                                <i class="bi bi-check-circle" aria-hidden="true"></i> Подтверждён врачом
                            </span>
                        @elseif($visit['confirmation_outcome'] === 'not_held')
                            <span class="mc-audit-status mc-audit-status--notheld">
                                <i class="bi bi-x-circle" aria-hidden="true"></i> Не состоялся
                            </span>
                        @else
                            <span class="mc-audit-status mc-audit-status--pending">Не подтверждён</span>
                        @endif

                        @if($visit['can_confirm_visit'])
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal" data-bs-target="#modalConfirmMedicalVisit"
                                    data-medical-visit-confirm
                                    data-visit-id="{{ $visit['id'] }}"
                                    data-visit-date="{{ $visit['scheduled_start_label'] }}"
                                    data-visit-doctor="{{ $visit['doctor_name'] }}"
                                    data-visit-previous-outcome="{{ $visit['confirmation_outcome'] ?? '' }}"
                                    data-visit-previous-reason="{{ $visit['confirmation_reason'] ?? '' }}">
                                <i class="bi bi-{{ $visit['confirmation_outcome'] ? 'pencil-square' : 'patch-check' }} me-1" aria-hidden="true"></i>
                                {{ $visit['confirmation_outcome'] ? 'Исправить отметку' : 'Указать результат' }}
                            </button>
                        @elseif($visit['is_future'])
                            <small class="text-muted">Доступно после начала приёма</small>
                        @elseif($visit['record_deleted'])
                            <small class="text-muted">Запись отменена</small>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <p class="mc-audit-panel__footnote">Показаны приёмы текущей страницы истории с выбранными фильтрами. Срок и результат фиксируются отдельно от протокола осмотра.</p>
    @endif
</section>

