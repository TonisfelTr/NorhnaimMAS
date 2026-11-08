{{-- Отдельная, проверяемая запись об инвалидности. Ни один флажок пациента сам по себе не подтверждает документ. --}}
<style>
    #patientDisabilityCard {
        --pdis-ink: #152a40;
        --pdis-muted: #63768d;
        --pdis-line: #dce9ee;
        margin: 18px 0 22px;
        border: 1px solid #d4e6ec;
        border-radius: 20px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 8px 28px rgba(17, 63, 83, .045);
    }
    #patientDisabilityCard .pdis-head {
        padding: 20px 23px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        background: linear-gradient(110deg, #f2fafc 0%, #f7f9ff 100%);
        border-bottom: 1px solid var(--pdis-line);
    }
    #patientDisabilityCard .pdis-title-row { display: flex; align-items: center; gap: 12px; }
    #patientDisabilityCard .pdis-mark {
        display: inline-grid; place-items: center; flex: 0 0 43px;
        width: 43px; height: 43px; border-radius: 13px;
        background: #deeff4; color: #057d96; font-size: 22px;
    }
    #patientDisabilityCard .pdis-title { margin: 0; color: var(--pdis-ink); font-size: 18px; font-weight: 800; }
    #patientDisabilityCard .pdis-sub { margin-top: 3px; color: var(--pdis-muted); font-size: 12px; line-height: 1.5; }
    #patientDisabilityCard .pdis-head-badges {
        display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
    }
    #patientDisabilityCard .pdis-badge {
        display: inline-flex; gap: 6px; align-items: center;
        padding: 8px 12px; border-radius: 100px; font-weight: 750; font-size: 12px;
        white-space: nowrap;
    }
    #patientDisabilityCard .pdis-badge--ok { background: #dff7e9; color: #176b47; border: 1px solid #b6e6cb; }
    #patientDisabilityCard .pdis-badge--warn { background: #fff4df; color: #995b14; border: 1px solid #f6dfa9; }
    #patientDisabilityCard .pdis-badge--none { background: #edf2f7; color: #5d6d7c; border: 1px solid #dce5ed; }
    #patientDisabilityCard .pdis-body { padding: 20px 23px; }
    #patientDisabilityCard .pdis-state-grid {
        display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px;
        margin-bottom: 17px;
    }
    #patientDisabilityCard .pdis-state-panel {
        min-width: 0; padding: 15px 17px; background: #f7fafc;
        border-radius: 14px; border: 1px solid #e0e9ef;
    }
    #patientDisabilityCard .pdis-state-panel--present { background: #effaf8; border-color: #d4ece6; }
    #patientDisabilityCard .pdis-state-panel--verified { background: #f0fbf4; border-color: #ccebd9; }
    #patientDisabilityCard .pdis-state-panel--unverified { background: #fffaf0; border-color: #f0e3c6; }
    #patientDisabilityCard .pdis-state-label { font-size: 11px; font-weight: 700; color: #687d8b; margin-bottom: 6px; }
    #patientDisabilityCard .pdis-state-value {
        font-size: 14px; font-weight: 800; color: #203d50; display: flex;
        align-items: start; gap: 8px; overflow-wrap: anywhere;
    }
    #patientDisabilityCard .pdis-state-note { font-size: 12px; line-height: 1.55; margin-top: 7px; color: #63778a; }
    #patientDisabilityCard .pdis-summary {
        display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px;
    }
    #patientDisabilityCard .pdis-fact {
        min-width: 0; padding: 13px 15px; border: 1px solid #e5edf2;
        border-radius: 13px; background: #fafcfd;
    }
    #patientDisabilityCard .pdis-fact-title { color: #6c7b8c; font-size: 11px; font-weight: 650; margin-bottom: 6px; }
    #patientDisabilityCard .pdis-fact-value { color: #20364b; font-size: 14px; font-weight: 700; overflow-wrap: anywhere; }
    #patientDisabilityCard .pdis-hint {
        margin-top: 14px; border: 1px solid #d7e6ef; padding: 11px 13px;
        background: #f6fafc; border-radius: 12px; color: #52697c;
        font-size: 12px; line-height: 1.55;
    }
    #patientDisabilityCard .pdis-info { margin-top: 12px; font-size: 13px; color: #425a71; line-height: 1.55; }
    #patientDisabilityCard .pdis-detail-head { font-weight: 800; font-size: 13px; color: #233e55; }
    #patientDisabilityCard .pdis-editor { margin-top: 16px; border: 1px solid var(--pdis-line); border-radius: 14px; overflow: hidden; }
    #patientDisabilityCard .pdis-editor > summary {
        cursor: pointer; list-style: none; display: flex; align-items: center;
        gap: 9px; padding: 14px 17px; background: #f2f9fb;
        font-size: 13px; font-weight: 750; color: #08748a;
    }
    #patientDisabilityCard .pdis-editor > summary::-webkit-details-marker { display: none; }
    #patientDisabilityCard .pdis-editor > summary::after { content: '▾'; margin-left: auto; font-size: 15px; }
    #patientDisabilityCard .pdis-editor[open] > summary::after { content: '▴'; }
    #patientDisabilityCard .pdis-form { padding: 18px; background: #fff; }
    #patientDisabilityCard .pdis-form-grid {
        display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px;
    }
    #patientDisabilityCard .pdis-full { grid-column: 1/-1; }
    #patientDisabilityCard label:not(.pdis-checkbox) {
        display: block; margin-bottom: 5px; color: #32485c; font-size: 12px; font-weight: 700;
    }
    #patientDisabilityCard .form-control, #patientDisabilityCard .form-select {
        min-height: 41px; border-radius: 11px; border-color: #d7e2ea;
    }
    #patientDisabilityCard .pdis-checkbox { font-size: 12px; color: #43566c; display: flex; align-items: center; gap: 8px; margin-top: 10px; }
    #patientDisabilityCard .pdis-actions {
        display: flex; flex-wrap: wrap; gap: 11px; align-items: center; justify-content: space-between;
        margin-top: 16px; padding-top: 15px; border-top: 1px solid #e7eef3;
    }
    #patientDisabilityCard .pdis-verify {
        margin-top: 16px; padding: 14px 16px; border-radius: 13px;
        border: 1px solid #d4e7df; background: #f6fcf9;
    }
    #patientDisabilityCard .pdis-verify form { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; }
    #patientDisabilityCard .pdis-verify label { flex: 1 1 240px; font-size: 12px; color: #3b6254; line-height: 1.5; }
    @media (max-width: 1050px) {
        #patientDisabilityCard .pdis-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 650px) {
        #patientDisabilityCard .pdis-head, #patientDisabilityCard .pdis-body { padding: 15px; }
        #patientDisabilityCard .pdis-summary, #patientDisabilityCard .pdis-form-grid,
        #patientDisabilityCard .pdis-state-grid { grid-template-columns: 1fr; }
        #patientDisabilityCard .pdis-badge { white-space: normal; }
        #patientDisabilityCard .pdis-form { padding: 13px; }
        #patientDisabilityCard .pdis-actions > button { width: 100%; }
    }
</style>

<section id="patientDisabilityCard" aria-labelledby="patientDisabilityTitle">
    <div class="pdis-head">
        <div class="pdis-title-row">
            <div class="pdis-mark" aria-hidden="true"><i class="bi bi-person-badge"></i></div>
            <div>
                <h2 id="patientDisabilityTitle" class="pdis-title">Инвалидность</h2>
                <div class="pdis-sub">Отдельно показываем сведения об установлении инвалидности и проверку документа МСЭ</div>
            </div>
        </div>
        <div class="pdis-head-badges" aria-label="Два независимых статуса инвалидности">
        <span class="pdis-badge pdis-badge--none" title="Сведения из представленного источника, а не официальная проверка в реестре">
            <i class="bi bi-person-lines-fill"></i>
            {{ $patientDisability['presence_short_label'] }}
        </span>
        <span class="pdis-badge {{ $patientDisability['verified'] ? 'pdis-badge--ok' : 'pdis-badge--warn' }}"
              title="Отметка о ручной проверке справки МСЭ">
            <i class="bi bi-{{ $patientDisability['verified'] ? 'shield-check' : 'shield-exclamation' }}"></i>
            Документ: {{ $patientDisability['document_check_short_label'] }}
        </span>
        </div>
    </div>

    <div class="pdis-body">
        @if($errors->disability->any())
            <div class="alert alert-danger" role="alert">
                <strong>Не удалось сохранить сведения об инвалидности.</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->disability->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="pdis-state-grid" role="group" aria-label="Наличие инвалидности и проверка документа">
            <div class="pdis-state-panel {{ $patientDisability['status'] === 'present' ? 'pdis-state-panel--present' : '' }}">
                <div class="pdis-state-label">1. Наличие инвалидности — по представленным сведениям</div>
                <div class="pdis-state-value">
                    <i class="bi bi-person-vcard" aria-hidden="true"></i>
                    {{ $patientDisability['presence_label'] }}
                </div>
                <div class="pdis-state-note">{{ $patientDisability['presence_hint'] }}</div>
            </div>
            <div class="pdis-state-panel {{ $patientDisability['verified'] ? 'pdis-state-panel--verified' : 'pdis-state-panel--unverified' }}">
                <div class="pdis-state-label">2. Проверка документа МСЭ сотрудником</div>
                <div class="pdis-state-value">
                    <i class="bi bi-{{ $patientDisability['verified'] ? 'shield-check' : 'shield-exclamation' }}" aria-hidden="true"></i>
                    {{ $patientDisability['document_check_label'] }}
                </div>
                <div class="pdis-state-note">{{ $patientDisability['document_check_hint'] }}</div>
            </div>
        </div>

        @if($patientDisability['status'] === 'present')
            <div class="pdis-summary">
                <div class="pdis-fact">
                    <div class="pdis-fact-title">Группа / категория</div>
                    <div class="pdis-fact-value">{{ $patientDisability['group_label'] }}</div>
                </div>
                <div class="pdis-fact">
                    <div class="pdis-fact-title">Установлена</div>
                    <div class="pdis-fact-value">{{ $patientDisability['established_label'] }}</div>
                </div>
                <div class="pdis-fact">
                    <div class="pdis-fact-title">Срок действия</div>
                    <div class="pdis-fact-value">{{ $patientDisability['valid_until_label'] }}</div>
                </div>
                <div class="pdis-fact">
                    <div class="pdis-fact-title">Источник сведений</div>
                    <div class="pdis-fact-value">{{ $patientDisability['has_record'] ? $patientDisability['source_label'] : 'Старая отметка в анкете' }}</div>
                </div>
            </div>
            @if($patientDisability['source_type'] === 'gosuslugi_screen')
                <div class="pdis-hint">
                    <i class="bi bi-info-circle me-1"></i>
                    Данные перенесены с экрана Госуслуг. Это подтверждает только наличие сведений в представленном источнике,
                    но <strong>не означает, что клиника проверила справку МСЭ или её электронную подпись</strong>.
                </div>
            @endif
            @if($patientDisability['cause'])
                <div class="pdis-info"><strong>Причина:</strong> {{ $patientDisability['cause'] }}</div>
            @endif
            @if($patientDisability['document_name'] || $patientDisability['document_number'] || $patientDisability['evidence_document_name'])
                <div class="pdis-info">
                    <span class="pdis-detail-head">Документ-основание:</span>
                    {{ $patientDisability['document_name'] ?: ($patientDisability['evidence_document_name'] ?: 'Документ МСЭ') }}
                    @if($patientDisability['document_number'])
                        · № {{ $patientDisability['document_number'] }}
                    @endif
                    @if($patientDisability['document_issued_at'])
                        · {{ $patientDisability['document_issued_label'] }}
                    @endif
                    @if($patientDisability['issued_by'])
                        · {{ $patientDisability['issued_by'] }}
                    @endif
                </div>
            @endif
            @if($patientDisability['expired'])
                <div class="pdis-hint"><i class="bi bi-clock-history me-1"></i> Указанный срок инвалидности истёк. Требуется повторная проверка документа.</div>
            @elseif($patientDisability['legacy_unverified'])
                <div class="pdis-hint"><i class="bi bi-info-circle me-1"></i> Это прежняя отметка об инвалидности в анкете. Она не является подтверждением. Укажите группу и документ-основание.</div>
            @elseif($patientDisability['verified'])
                <div class="pdis-hint">
                    <i class="bi bi-shield-check me-1"></i>
                    <strong>Документ МСЭ проверен сотрудником.</strong>
                    {{ $patientDisability['verified_at_label'] }}
                    @if($patientDisability['verified_by_user_id'])
                        · Сотрудник № {{ $patientDisability['verified_by_user_id'] }}
                    @endif
                    Это не автоматическая проверка через Госуслуги или государственный реестр.
                </div>
            @else
                <div class="pdis-hint"><i class="bi bi-exclamation-triangle me-1"></i> Документ МСЭ ещё не проверен сотрудником. Даже если источник — Госуслуги, сами перенесённые данные не считаются проверенной справкой для льгот или юридически значимых действий.</div>
            @endif
            @if($patientDisability['notes'])
                <div class="pdis-info"><strong>Примечание:</strong> {{ $patientDisability['notes'] }}</div>
            @endif
        @elseif($patientDisability['status'] === 'absent')
            <div class="pdis-hint mt-0"><i class="bi bi-info-circle me-1"></i> В медкарте указано, что сведений о действующей инвалидности нет. Это не результат автоматической проверки в государственном реестре.</div>
        @else
            <div class="pdis-hint mt-0"><i class="bi bi-info-circle me-1"></i> Сведения об инвалидности не внесены. Отсутствие записи не означает, что инвалидность отсутствует.</div>
        @endif

        @if(!$patientDisability['available'])
            <div class="alert alert-warning mt-3 mb-0" role="alert">Для сохранения подробных сведений требуется выполнить миграцию <code>patient_disability_records</code>.</div>
        @elseif($patientDisability['can_edit'])
            <details class="pdis-editor" @if($errors->disability->any()) open @endif>
                <summary><i class="bi bi-pencil-square"></i> {{ $patientDisability['has_record'] ? 'Уточнить сведения и документ' : 'Внести сведения об инвалидности' }}</summary>
                <form class="pdis-form" method="post" action="{{ route('doctors.patients.disability.save', $patient->id) }}">
                    @csrf
                    <div class="pdis-form-grid">
                        <div>
                            <label for="pdisStatus">Наличие инвалидности по представленным сведениям</label>
                            <select id="pdisStatus" name="status" class="form-select" required>
                                <option value="unknown" @selected(old('status', $patientDisability['status']) === 'unknown')>Нет сведений</option>
                                <option value="present" @selected(old('status', $patientDisability['status']) === 'present')>Указано, что инвалидность установлена</option>
                                <option value="absent" @selected(old('status', $patientDisability['status']) === 'absent')>В представленных сведениях не указана</option>
                            </select>
                        </div>
                        <div>
                            <label for="pdisGroup">Группа / категория</label>
                            <select id="pdisGroup" name="disability_group" class="form-select">
                                <option value="">Не указана</option>
                                <option value="I" @selected(old('disability_group', $patientDisability['group']) === 'I')>I группа</option>
                                <option value="II" @selected(old('disability_group', $patientDisability['group']) === 'II')>II группа</option>
                                <option value="III" @selected(old('disability_group', $patientDisability['group']) === 'III')>III группа</option>
                                <option value="child" @selected(old('disability_group', $patientDisability['group']) === 'child')>Ребёнок-инвалид</option>
                            </select>
                        </div>
                        <div>
                            <label for="pdisEstablished">Дата установления</label>
                            <input id="pdisEstablished" type="date" name="established_at" class="form-control"
                                   value="{{ old('established_at', $patientDisability['established_at']) }}">
                        </div>
                        <div>
                            <label for="pdisValidUntil">Действует до</label>
                            <input id="pdisValidUntil" type="date" name="valid_until" class="form-control"
                                   value="{{ old('valid_until', $patientDisability['valid_until']) }}">
                            <label class="pdis-checkbox" for="pdisIndefinite">
                                <input type="hidden" name="indefinite" value="0">
                                <input type="checkbox" id="pdisIndefinite" name="indefinite" value="1"
                                    @checked((bool) old('indefinite', $patientDisability['indefinite']))>
                                Бессрочно
                            </label>
                        </div>
                        <div class="pdis-full">
                            <label for="pdisCause">Причина инвалидности (по документу)</label>
                            <input id="pdisCause" type="text" name="cause" maxlength="500" class="form-control"
                                   value="{{ old('cause', $patientDisability['cause']) }}" placeholder="Например: общее заболевание">
                        </div>
                        <div>
                            <label for="pdisSource">Источник сведений</label>
                            <select id="pdisSource" class="form-select" name="source_type">
                                <option value="patient_statement" @selected(old('source_type', $patientDisability['source_type']) === 'patient_statement')>Со слов пациента</option>
                                <option value="gosuslugi_screen" @selected(old('source_type', $patientDisability['source_type']) === 'gosuslugi_screen')>Экран / скриншот Госуслуг (без подписанной справки)</option>
                                <option value="mse_certificate" @selected(old('source_type', $patientDisability['source_type']) === 'mse_certificate')>Бумажная справка МСЭ</option>
                                <option value="electronic_mse" @selected(old('source_type', $patientDisability['source_type']) === 'electronic_mse')>Электронная справка МСЭ с подписью (файл)</option>
                                <option value="medical_document" @selected(old('source_type', $patientDisability['source_type']) === 'medical_document')>Медицинская документация</option>
                                <option value="other" @selected(old('source_type', $patientDisability['source_type']) === 'other')>Другой источник</option>
                            </select>
                        </div>
                        <div>
                            <label for="pdisDocumentName">Название документа</label>
                            <input id="pdisDocumentName" class="form-control" name="document_name" maxlength="255"
                                   value="{{ old('document_name', $patientDisability['document_name']) }}" placeholder="Справка об инвалидности">
                        </div>
                        <div>
                            <label for="pdisDocumentNumber">Номер документа</label>
                            <input id="pdisDocumentNumber" class="form-control" name="document_number" maxlength="100"
                                   value="{{ old('document_number', $patientDisability['document_number']) }}">
                        </div>
                        <div>
                            <label for="pdisDocumentIssued">Дата выдачи документа</label>
                            <input id="pdisDocumentIssued" class="form-control" type="date" name="document_issued_at"
                                   value="{{ old('document_issued_at', $patientDisability['document_issued_at']) }}">
                        </div>
                        <div class="pdis-full">
                            <label for="pdisIssuedBy">Кем выдан</label>
                            <input id="pdisIssuedBy" class="form-control" name="issued_by" maxlength="255"
                                   value="{{ old('issued_by', $patientDisability['issued_by']) }}" placeholder="Учреждение МСЭ">
                        </div>
                        <div class="pdis-full">
                            <label for="pdisEvidenceDocument">Файл документа из медкарты (необязательно)</label>
                            <select id="pdisEvidenceDocument" class="form-select" name="evidence_document_id">
                                <option value="">Не прикреплён</option>
                                @foreach($patientDisability['documents'] as $documentOption)
                                    <option value="{{ $documentOption['id'] }}"
                                        @selected((string) old('evidence_document_id', $patientDisability['evidence_document_id']) === (string) $documentOption['id'])>
                                        #{{ $documentOption['id'] }} · {{ $documentOption['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Подписанную электронную справку МСЭ сначала загрузите как файл во вкладке «Документы» и укажите здесь. Бумажную справку можно проверить по оригиналу и реквизитам. Один скриншот Госуслуг — не основание для отметки «Документ проверен».</div>
                        </div>
                        <div class="pdis-full">
                            <label for="pdisNotes">Примечание</label>
                            <textarea id="pdisNotes" name="notes" class="form-control" rows="2" maxlength="2000" placeholder="Дополнительные сведения">{{ old('notes', $patientDisability['notes']) }}</textarea>
                        </div>
                    </div>
                    <div class="pdis-actions">
                        <span class="text-muted small">Если изменить ранее проверенные сведения, отметка о проверке документа будет снята.</span>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Сохранить сведения</button>
                    </div>
                </form>
            </details>

            @if($patientDisability['has_record'] && $patientDisability['status'] === 'present')
                <div class="pdis-verify">
                    @if($patientDisability['verified'])
                        <form method="post" action="{{ route('doctors.patients.disability.verification', $patient->id) }}">
                            @csrf
                            <input type="hidden" name="action" value="revoke">
                            <span class="small text-muted">Если документ или его проверка более не актуальны, отзовите отметку.</span>
                            <button type="submit" class="btn btn-outline-danger btn-sm">Отозвать отметку проверки</button>
                        </form>
                    @elseif($patientDisability['can_verify'])
                        <form method="post" action="{{ route('doctors.patients.disability.verification', $patient->id) }}">
                            @csrf
                            <input type="hidden" name="action" value="confirm">
                            <label for="pdisDocumentReviewed">
                                <input id="pdisDocumentReviewed" type="checkbox" name="reviewed_document" value="1" required>
                                Я сверил(а) сведения и принадлежность документа пациенту: видел(а) оригинал бумажной справки МСЭ либо проверил(а) электронный документ и его подпись. Не только скриншот Госуслуг.
                            </label>
                            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-shield-check me-1"></i> Документ проверен</button>
                        </form>
                    @else
                        <div class="small text-muted"><i class="bi bi-info-circle me-1"></i> Для отметки «Документ проверен» укажите группу и дату установления, действующий срок (или «Бессрочно»). Для бумажной справки нужны реквизиты/файл; для подписанной электронной справки — прикреплённый файл с проверенной подписью. Экран Госуслуг не подходит.</div>
                        @if($patientDisability['previously_verified'])
                            <form class="mt-2" method="post" action="{{ route('doctors.patients.disability.verification', $patient->id) }}">
                                @csrf
                                <input type="hidden" name="action" value="revoke">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Отозвать прежнюю проверку</button>
                            </form>
                        @endif
                    @endif
                </div>
            @endif
        @endif
    </div>
</section>
