{{-- NORHNAIM_DISABILITY_VERIFICATION_ACTION_V1 --}}

@if(session('disability_verification_success'))
    <div class="alert alert-success mt-3 mb-0" role="status">
        <i class="bi bi-shield-check me-1" aria-hidden="true"></i>
        {{ session('disability_verification_success') }}
    </div>
@endif

@if($errors->has('disability_verification'))
    <div class="alert alert-danger mt-3 mb-0" role="alert">
        <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>
        {{ $errors->first('disability_verification') }}
    </div>
@endif

@if(
    ($patientDisability['available'] ?? false)
    && ($patientDisability['status'] ?? 'unknown') === 'present'
)
    <div class="mt-3 border rounded-3 p-3 bg-light-subtle">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="fw-semibold">
                    <i class="bi bi-shield-check me-1"></i>
                    Проверка документа МСЭ
                </div>

                @if($patientDisability['verified'] ?? false)
                    <div class="small text-success mt-1">
                        Документ проверен сотрудником
                        @if($patientDisability['verified_at_label'] ?? null)
                            · {{ $patientDisability['verified_at_label'] }}
                        @endif
                    </div>
                @else
                    <div class="small text-muted mt-1">
                        Подтверждение означает, что сотрудник клиники сверил
                        внесённые сведения с документом МСЭ.
                    </div>
                @endif
            </div>

            @if($patientDisability['verified'] ?? false)
                <span class="badge rounded-pill text-bg-success px-3 py-2">
                    <i class="bi bi-check2-circle me-1"></i>
                    Подтверждено
                </span>
            @elseif($patientDisability['can_verify'] ?? false)
                <form method="post"
                      action="{{ route('doctors.patients.disability.verify-document', ['patient' => $patient->id]) }}"
                      onsubmit="return confirm('Подтвердить, что документ МСЭ проверен сотрудником клиники?');">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-shield-check me-1"></i>
                        Подтвердить документ МСЭ
                    </button>
                </form>
            @else
                <button type="button"
                        class="btn btn-outline-secondary btn-sm"
                        disabled>
                    <i class="bi bi-shield-exclamation me-1"></i>
                    Подтвердить документ МСЭ
                </button>
            @endif
        </div>

        @if(
            !($patientDisability['verified'] ?? false)
            && !($patientDisability['can_verify'] ?? false)
        )
            <div class="alert alert-warning py-2 px-3 mt-3 mb-0">
                <div class="fw-semibold mb-1">Пока подтвердить нельзя.</div>

                <ul class="mb-0 ps-3 small">
                    @if($patientDisability['expired'] ?? false)
                        <li>Срок инвалидности истёк.</li>
                    @endif

                    @if(empty($patientDisability['group']))
                        <li>Не указана группа инвалидности.</li>
                    @endif

                    @if(empty($patientDisability['established_at']))
                        <li>Не указана дата установления инвалидности.</li>
                    @endif

                    @if(
                        !($patientDisability['indefinite'] ?? false)
                        && empty($patientDisability['valid_until'])
                    )
                        <li>Не указан срок действия инвалидности.</li>
                    @endif

                    @if(
                        ($patientDisability['source_type'] ?? '') === 'electronic_mse'
                        && empty($patientDisability['evidence_document_id'])
                    )
                        <li>
                            Для электронной справки МСЭ с подписью необходимо
                            привязать загруженный документ-основание.
                        </li>
                    @elseif(
                        ($patientDisability['source_type'] ?? '') === 'mse_certificate'
                        && empty($patientDisability['evidence_document_id'])
                        && (
                            empty($patientDisability['document_number'])
                            || empty($patientDisability['issued_by'])
                        )
                    )
                        <li>
                            Для бумажной справки укажите номер и кем она выдана
                            либо привяжите загруженный документ.
                        </li>
                    @elseif(!in_array(
                        ($patientDisability['source_type'] ?? ''),
                        ['electronic_mse', 'mse_certificate'],
                        true
                    ))
                        <li>
                            Источник должен быть «Бумажная справка МСЭ»
                            или «Электронная справка МСЭ с подписью».
                        </li>
                    @endif

                    @if(!($patientDisability['can_edit'] ?? false))
                        <li>
                            У текущего пользователя нет права изменять
                            эту медицинскую карту.
                        </li>
                    @endif
                </ul>
            </div>
        @endif
    </div>
@endif
