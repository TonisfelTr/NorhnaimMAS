@extends('doctors.reception')

@section('title', 'Архив регистратуры')

@section('assets')
    <style>
        :root {
            --archive-primary: #048696;
            --archive-primary-dark: #036b78;
            --archive-primary-soft: #e8f8fa;
            --archive-bg: #f4f7fb;
            --archive-card: #ffffff;
            --archive-border: #e3e9ef;
            --archive-text: #1f2937;
            --archive-muted: #6b7280;
        }

        .archive-page {
            min-height: calc(100vh - 80px);
            padding: 28px;
            background:
                radial-gradient(circle at top left, rgba(4, 134, 150, .10), transparent 34%),
                var(--archive-bg);
        }

        .archive-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 24px;
            padding: 28px;
            border: 1px solid var(--archive-border);
            border-radius: 26px;
            background: linear-gradient(135deg, #ffffff 0%, #f0fbfc 100%);
            box-shadow: 0 18px 45px rgba(15, 23, 42, .07);
        }

        .archive-header h1 {
            margin: 0 0 10px;
            color: var(--archive-text);
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .archive-header p {
            max-width: 760px;
            margin: 0;
            color: var(--archive-muted);
            font-size: 15px;
            line-height: 1.55;
        }

        .archive-header-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 999px;
            color: var(--archive-primary-dark);
            background: var(--archive-primary-soft);
            font-size: 14px;
            font-weight: 800;
            white-space: nowrap;
        }

        .archive-search-card,
        .archive-table-panel {
            position: relative;
            border: 1px solid var(--archive-border);
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 16px 42px rgba(15, 23, 42, .06);
        }

        .archive-search-card {
            z-index: 30;
            margin-bottom: 22px;
            overflow: visible;
        }

        .archive-search-header,
        .archive-table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 24px;
            border-bottom: 1px solid var(--archive-border);
            background: #fff;
        }

        .archive-search-header {
            overflow: hidden;
            border-radius: 24px 24px 0 0;
        }

        .archive-search-title-wrap {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .archive-search-title-icon {
            display: grid;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            place-items: center;
            border-radius: 13px;
            color: var(--archive-primary-dark);
            background: var(--archive-primary-soft);
            font-size: 18px;
        }

        .archive-search-title,
        .archive-table-title {
            margin: 0;
            color: var(--archive-text);
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .archive-search-subtitle,
        .archive-table-subtitle {
            margin-top: 4px;
            color: var(--archive-muted);
            font-size: 13px;
        }

        .archive-search-body {
            position: relative;
            overflow: visible;
            padding: 24px;
            border-radius: 0 0 24px 24px;
        }

        .archive-search-card .form-label {
            margin-bottom: 7px;
            color: #374151;
            font-size: 13px;
            font-weight: 700;
        }

        .archive-search-card .form-control {
            min-height: 42px;
            border: 1px solid #d8e0e5;
            border-radius: 13px;
            font-size: 14px;
        }

        .archive-search-card .form-control:focus {
            border-color: var(--archive-primary);
            box-shadow: 0 0 0 .2rem rgba(4, 134, 150, .14);
        }

        .archive-search-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 18px;
        }

        .archive-btn-primary,
        .archive-btn-light {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: 12px;
            font-weight: 800;
            text-decoration: none;
            transition: .18s ease;
        }

        .archive-btn-primary {
            border: 0;
            color: #fff;
            background: var(--archive-primary);
        }

        .archive-btn-primary:hover {
            color: #fff;
            background: var(--archive-primary-dark);
        }

        .archive-btn-light {
            border: 1px solid var(--archive-border);
            color: #334155;
            background: #fff;
        }

        .archive-btn-light:hover {
            color: #111827;
            background: #f3f6f9;
        }

        .archive-picker,
        .archive-date-picker,
        .archive-address-wrap {
            position: relative;
        }

        .archive-input-wrap {
            position: relative;
        }

        .archive-input-icon,
        .archive-address-icon {
            position: absolute;
            z-index: 2;
            top: 50%;
            left: 14px;
            color: #8a98a8;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .archive-search-input,
        .archive-address-input {
            padding-right: 42px !important;
            padding-left: 40px !important;
        }

        .archive-clear {
            position: absolute;
            z-index: 4;
            top: 50%;
            right: 9px;
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border: 0;
            border-radius: 9px;
            color: #7b8794;
            background: transparent;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .archive-clear:hover {
            color: var(--archive-primary-dark);
            background: #eef8fa;
        }

        .archive-dropdown,
        .archive-address-suggestions {
            position: absolute;
            z-index: 5000;
            top: calc(100% + 7px);
            right: 0;
            left: 0;
            max-height: 330px;
            overflow-y: auto;
            padding: 7px;
            border: 1px solid #dce5ea;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 20px 48px rgba(15, 23, 42, .16);
        }

        .archive-option,
        .archive-address-suggestion {
            width: 100%;
            border: 0;
            border-radius: 11px;
            color: #243142;
            background: transparent;
            text-align: left;
            cursor: pointer;
        }

        .archive-option {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr);
            gap: 10px;
            align-items: center;
            padding: 9px 10px;
        }

        .archive-option:hover,
        .archive-option.is-active,
        .archive-address-suggestion:hover,
        .archive-address-suggestion.is-active {
            background: #eefafb;
        }

        .archive-option__icon {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 11px;
            color: var(--archive-primary-dark);
            background: var(--archive-primary-soft);
            font-size: 14px;
            font-weight: 800;
        }

        .archive-option__body {
            min-width: 0;
        }

        .archive-option__body strong,
        .archive-option__body small {
            display: block;
        }

        .archive-option__body strong {
            overflow: hidden;
            font-size: 13px;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .archive-option__body small {
            margin-top: 2px;
            color: #7b8794;
            font-size: 11px;
            font-weight: 650;
        }

        .archive-dropdown-state {
            padding: 17px 12px;
            color: #7b8794;
            font-size: 12px;
            text-align: center;
        }

        .archive-date-trigger {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            color: #334155;
            background: #fff;
            text-align: left;
            cursor: pointer;
        }

        .archive-date-trigger.is-placeholder {
            color: #8a98a8;
        }

        .archive-date-trigger i {
            color: #6f7d8c;
        }

        .archive-calendar {
            position: absolute;
            z-index: 5100;
            top: calc(100% + 7px);
            right: 0;
            width: 330px;
            padding: 15px;
            border: 1px solid #dce5ea;
            border-radius: 17px;
            background: #fff;
            box-shadow: 0 22px 52px rgba(15, 23, 42, .18);
        }

        .archive-calendar__head {
            display: grid;
            grid-template-columns: 36px minmax(0, 1fr) 36px;
            gap: 8px;
            align-items: center;
            margin-bottom: 12px;
        }

        .archive-calendar__nav {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border: 0;
            border-radius: 10px;
            color: #596979;
            background: #f6f9fa;
            cursor: pointer;
        }

        .archive-calendar__nav:hover {
            color: var(--archive-primary-dark);
            background: var(--archive-primary-soft);
        }

        .archive-calendar__month {
            color: #243142;
            font-size: 14px;
            font-weight: 850;
            text-align: center;
            text-transform: capitalize;
        }

        .archive-calendar__weekdays,
        .archive-calendar__days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .archive-calendar__weekdays {
            margin-bottom: 5px;
            color: #7c8996;
            font-size: 10px;
            font-weight: 800;
            text-align: center;
        }

        .archive-calendar__weekdays span {
            padding: 5px 0;
        }

        .archive-calendar__weekdays .is-weekend,
        .archive-calendar__day.is-weekend:not(.is-selected) {
            color: #e25050;
        }

        .archive-calendar__day {
            display: grid;
            aspect-ratio: 1;
            place-items: center;
            border: 0;
            border-radius: 11px;
            color: #283849;
            background: transparent;
            font-size: 12px;
            cursor: pointer;
        }

        .archive-calendar__day:hover:not(:disabled) {
            color: var(--archive-primary-dark);
            background: var(--archive-primary-soft);
        }

        .archive-calendar__day.is-selected {
            color: #fff;
            background: #099dac;
            font-weight: 900;
            box-shadow: 0 7px 18px rgba(9, 157, 172, .25);
        }

        .archive-calendar__day.is-other-month {
            color: #c1cbd3;
        }

        .archive-calendar__day:disabled {
            color: #cbd3d9;
            cursor: not-allowed;
        }

        .archive-calendar__footer {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-top: 12px;
            padding-top: 11px;
            border-top: 1px solid #edf1f4;
        }

        .archive-calendar__footer button {
            padding: 7px 10px;
            border: 0;
            border-radius: 9px;
            color: #526273;
            background: #f3f7f8;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
        }

        .archive-calendar__footer button:hover {
            color: var(--archive-primary-dark);
            background: var(--archive-primary-soft);
        }

        .archive-address-suggestion {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 11px;
        }

        .archive-address-suggestion > i {
            margin-top: 2px;
            color: var(--archive-primary);
        }

        .archive-address-suggestion strong,
        .archive-address-suggestion small {
            display: block;
        }

        .archive-address-suggestion strong {
            font-size: 12px;
            font-weight: 800;
            line-height: 1.35;
        }

        .archive-address-suggestion small {
            margin-top: 3px;
            color: #7b8794;
            font-size: 10px;
            line-height: 1.35;
        }

        .archive-table-panel {
            z-index: 1;
            overflow: hidden;
        }

        .archive-table-count {
            padding: 7px 12px;
            border-radius: 999px;
            color: var(--archive-primary-dark);
            background: var(--archive-primary-soft);
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }

        .archive-table-wrap {
            overflow-x: auto;
        }

        .archive-table {
            width: 100%;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .archive-table thead th {
            padding: 15px 18px;
            border-bottom: 1px solid var(--archive-border);
            color: #667085;
            background: #f8fafc;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .045em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .archive-table tbody td {
            padding: 17px 18px;
            border-bottom: 1px solid #eef2f6;
            color: var(--archive-text);
            font-size: 14px;
            vertical-align: middle;
        }

        .archive-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .archive-patient {
            display: flex;
            min-width: 250px;
            align-items: center;
            gap: 12px;
        }

        .archive-patient__avatar {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 14px;
            color: #fff;
            background: linear-gradient(135deg, var(--archive-primary), #10b7c6);
            font-size: 13px;
            font-weight: 900;
        }

        .archive-primary-text {
            color: #243142;
            font-weight: 800;
        }

        .archive-secondary-text {
            margin-top: 3px;
            color: #7b8794;
            font-size: 12px;
        }

        .archive-doctor-profile {
            color: var(--archive-primary-dark);
            font-size: 12px;
            font-weight: 800;
        }

        .archive-status {
            display: inline-flex;
            padding: 6px 9px;
            border-radius: 999px;
            color: #5f6b76;
            background: #eef2f5;
            font-size: 11px;
            font-weight: 800;
        }

        .archive-status.is-trashed {
            color: #8a4d0f;
            background: #fff5df;
        }

        .archive-actions {
            display: flex;
            justify-content: flex-end;
        }

        .archive-action {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            color: #475569;
            background: #fff;
            text-decoration: none;
        }

        .archive-action:hover {
            color: var(--archive-primary-dark);
            background: var(--archive-primary-soft);
            border-color: #b7dce2;
        }

        .archive-empty {
            padding: 48px 24px;
            color: #7c8a98;
            text-align: center;
        }

        .archive-pagination {
            padding: 18px 22px;
            border-top: 1px solid var(--archive-border);
        }

        @media (max-width: 768px) {
            .archive-page {
                padding: 18px 14px;
            }

            .archive-header,
            .archive-search-header,
            .archive-table-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .archive-header {
                padding: 22px;
            }

            .archive-header h1 {
                font-size: 28px;
            }

            .archive-search-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .archive-btn-primary,
            .archive-btn-light {
                width: 100%;
            }

            .archive-calendar {
                right: auto;
                left: 0;
                width: min(330px, calc(100vw - 56px));
            }
        }
    </style>
@endsection

@section('sub-main')
    @php
        $selectedPatientName = '';
        $selectedPatientMeta = '';

        if ($selectedArchivePatient) {
            $selectedPatientName = trim(implode(' ', array_filter([
                $selectedArchivePatient->surname ?? null,
                $selectedArchivePatient->name ?? null,
                $selectedArchivePatient->patronym ?? null,
            ])));

            $selectedPatientMeta = collect([
                $selectedArchivePatient->birth_at
                    ? \Illuminate\Support\Carbon::parse($selectedArchivePatient->birth_at)->format('d.m.Y')
                    : null,
                $selectedArchivePatient->address_registration ?: null,
            ])->filter()->implode(' · ');
        }
    @endphp

    <div class="archive-page">
        <div class="archive-header">
            <div>
                <h1>Архив регистратуры</h1>
                <p>
                    Поиск завершённых и архивных записей по пациенту, врачу, дате рождения
                    и месту регистрации.
                </p>
            </div>

            <div class="archive-header-badge">
                <i class="bi bi-archive"></i>
                Архив
            </div>
        </div>

        <form class="archive-search-card"
              id="archiveSearchForm"
              method="get"
              action="{{ route('doctors.reception.archives') }}">
            <div class="archive-search-header">
                <div class="archive-search-title-wrap">
                    <div class="archive-search-title-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <div>
                        <h2 class="archive-search-title">Поиск пациента</h2>
                        <div class="archive-search-subtitle">
                            Выберите пациента или используйте дополнительные фильтры.
                        </div>
                    </div>
                </div>
            </div>

            <div class="archive-search-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="archivePatientSearch" class="form-label">Полное имя</label>

                        <div class="archive-picker" id="archivePatientPicker">
                            <input type="hidden"
                                   name="patient_id"
                                   id="archivePatientId"
                                   value="{{ request('patient_id') }}">

                            <div class="archive-input-wrap">
                                <i class="bi bi-search archive-input-icon"></i>
                                <input type="text"
                                       class="form-control archive-search-input"
                                       id="archivePatientSearch"
                                       autocomplete="off"
                                       placeholder="Начните вводить ФИО пациента"
                                       value="{{ $selectedPatientName }}">
                                <button type="button"
                                        class="archive-clear {{ request('patient_id') ? '' : 'd-none' }}"
                                        id="archivePatientClear"
                                        aria-label="Очистить пациента">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <div class="archive-dropdown d-none" id="archivePatientDropdown">
                                <div class="archive-dropdown-state" id="archivePatientState">
                                    Начните вводить минимум 2 символа.
                                </div>
                                <div id="archivePatientResults"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="archiveDoctorSearch" class="form-label">Врач</label>

                        <div class="archive-picker" id="archiveDoctorPicker">
                            <select class="d-none" id="archiveDoctorId" name="doctor_id">
                                <option value="">— Все врачи —</option>
                                @foreach($doctors as $doctor)
                                    @php
                                        $doctorFullName = trim(implode(' ', array_filter([
                                            $doctor->surname ?? null,
                                            $doctor->name ?? null,
                                            $doctor->patronym ?? null,
                                        ])));
                                        $doctorProfile = trim((string) ($doctor->status ?? ''));
                                        $doctorProfession = trim((string) ($doctor->profession ?? ''));

                                        if ($doctorProfile === '') {
                                            $doctorProfile = $doctorProfession !== ''
                                                ? $doctorProfession
                                                : 'Профиль не указан';
                                        }
                                    @endphp
                                    <option value="{{ $doctor->id }}"
                                            data-name="{{ $doctorFullName }}"
                                            data-profile="{{ $doctorProfile }}"
                                            {{ (string) request('doctor_id') === (string) $doctor->id ? 'selected' : '' }}>
                                        {{ $doctorFullName }} — {{ $doctorProfile }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="archive-input-wrap">
                                <i class="bi bi-search archive-input-icon"></i>
                                <input type="text"
                                       class="form-control archive-search-input"
                                       id="archiveDoctorSearch"
                                       autocomplete="off"
                                       placeholder="Все врачи — начните вводить ФИО или профиль">
                                <button type="button"
                                        class="archive-clear d-none"
                                        id="archiveDoctorClear"
                                        aria-label="Очистить врача">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <div class="archive-dropdown d-none" id="archiveDoctorDropdown">
                                <button type="button"
                                        class="archive-option"
                                        data-doctor-id=""
                                        data-search="все врачи">
                                    <span class="archive-option__icon"><i class="bi bi-people"></i></span>
                                    <span class="archive-option__body">
                                        <strong>Все врачи</strong>
                                        <small>Не ограничивать архив конкретным специалистом</small>
                                    </span>
                                </button>

                                @foreach($doctors as $doctor)
                                    @php
                                        $doctorFullName = trim(implode(' ', array_filter([
                                            $doctor->surname ?? null,
                                            $doctor->name ?? null,
                                            $doctor->patronym ?? null,
                                        ])));
                                        $doctorProfile = trim((string) ($doctor->status ?? ''));
                                        $doctorProfession = trim((string) ($doctor->profession ?? ''));

                                        if ($doctorProfile === '') {
                                            $doctorProfile = $doctorProfession !== ''
                                                ? $doctorProfession
                                                : 'Профиль не указан';
                                        }
                                    @endphp
                                    <button type="button"
                                            class="archive-option"
                                            data-doctor-id="{{ $doctor->id }}"
                                            data-search="{{ mb_strtolower($doctorFullName . ' ' . $doctorProfile) }}">
                                        <span class="archive-option__icon"><i class="bi bi-person-badge"></i></span>
                                        <span class="archive-option__body">
                                            <strong>{{ $doctorFullName }}</strong>
                                            <small>{{ $doctorProfile }}</small>
                                        </span>
                                    </button>
                                @endforeach

                                <div class="archive-dropdown-state d-none" id="archiveDoctorEmpty">
                                    Врачи по этому запросу не найдены.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="archiveBirthDateTrigger" class="form-label">Дата рождения</label>

                        <div class="archive-date-picker" id="archiveBirthDatePicker">
                            <input type="hidden"
                                   id="archiveBirthAt"
                                   name="birth_at"
                                   value="{{ request('birth_at') }}">

                            <button type="button"
                                    class="form-control archive-date-trigger"
                                    id="archiveBirthDateTrigger">
                                <span id="archiveBirthDateText">дд.мм.гггг</span>
                                <i class="bi bi-calendar3"></i>
                            </button>

                            <div class="archive-calendar d-none" id="archiveBirthCalendar">
                                <div class="archive-calendar__head">
                                    <button type="button"
                                            class="archive-calendar__nav"
                                            id="archiveBirthPrev"
                                            aria-label="Предыдущий месяц">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>

                                    <div class="archive-calendar__month" id="archiveBirthMonth"></div>

                                    <button type="button"
                                            class="archive-calendar__nav"
                                            id="archiveBirthNext"
                                            aria-label="Следующий месяц">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>

                                <div class="archive-calendar__weekdays">
                                    <span>Пн</span><span>Вт</span><span>Ср</span><span>Чт</span><span>Пт</span>
                                    <span class="is-weekend">Сб</span><span class="is-weekend">Вс</span>
                                </div>

                                <div class="archive-calendar__days" id="archiveBirthDays"></div>

                                <div class="archive-calendar__footer">
                                    <button type="button" id="archiveBirthToday">Сегодня</button>
                                    <button type="button" id="archiveBirthClear">Очистить</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="archiveAddressRegistration" class="form-label">Место прописки</label>

                        <div class="archive-address-wrap">
                            <i class="bi bi-geo-alt archive-address-icon"></i>
                            <input type="text"
                                   class="form-control archive-address-input"
                                   id="archiveAddressRegistration"
                                   name="address_registration"
                                   autocomplete="off"
                                   placeholder="Начните вводить адрес"
                                   value="{{ request('address_registration') }}">
                            <div class="archive-address-suggestions d-none"
                                 id="archiveAddressSuggestions"></div>
                        </div>
                    </div>
                </div>

                <div class="archive-search-actions">
                    <a href="{{ route('doctors.reception.archives') }}" class="archive-btn-light">
                        <i class="bi bi-x-circle"></i>
                        Сбросить
                    </a>

                    <button type="submit" class="archive-btn-primary">
                        <i class="bi bi-search"></i>
                        Найти
                    </button>
                </div>
            </div>
        </form>

        <section class="archive-table-panel">
            <div class="archive-table-header">
                <div>
                    <h2 class="archive-table-title">Архивные записи</h2>
                    <div class="archive-table-subtitle">
                        Завершённые и перенесённые в архив записи регистратуры.
                    </div>
                </div>

                <div class="archive-table-count">
                    {{ $records->total() }} записей
                </div>
            </div>

            @if($records->count())
                <div class="archive-table-wrap">
                    <table class="archive-table">
                        <thead>
                        <tr>
                            <th>Пациент</th>
                            <th>Дата рождения</th>
                            <th>Врач</th>
                            <th>Дата приёма</th>
                            <th>Место прописки</th>
                            <th>Статус</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($records as $record)
                            @php
                                $patient = $record->patient;
                                $doctor = $record->doctor;
                                $patientName = $patient
                                    ? trim(implode(' ', array_filter([
                                        $patient->surname ?? null,
                                        $patient->name ?? null,
                                        $patient->patronym ?? null,
                                    ])))
                                    : 'Пациент не найден';
                                $patientInitials = $patient
                                    ? mb_strtoupper(
                                        mb_substr((string) ($patient->surname ?? ''), 0, 1)
                                        . mb_substr((string) ($patient->name ?? ''), 0, 1)
                                    )
                                    : '—';
                                $doctorName = $doctor
                                    ? trim(implode(' ', array_filter([
                                        $doctor->surname ?? null,
                                        $doctor->name ?? null,
                                        $doctor->patronym ?? null,
                                    ])))
                                    : 'Врач не указан';
                                $doctorProfile = trim((string) ($doctor?->status ?? ''));

                                if ($doctorProfile === '') {
                                    $doctorProfile = trim((string) ($doctor?->profession ?? '')) ?: 'Профиль не указан';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="archive-patient">
                                        <div class="archive-patient__avatar">{{ $patientInitials ?: '—' }}</div>
                                        <div>
                                            <div class="archive-primary-text">{{ $patientName }}</div>
                                            <div class="archive-secondary-text">Запись #{{ $record->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ $patient?->birth_at ? \Illuminate\Support\Carbon::parse($patient->birth_at)->format('d.m.Y') : '—' }}
                                </td>
                                <td>
                                    <div class="archive-doctor-profile">{{ $doctorProfile }}</div>
                                    <div class="archive-secondary-text">{{ $doctorName }}</div>
                                </td>
                                <td>
                                    <div class="archive-primary-text">
                                        {{ $record->for_datetime ? \Illuminate\Support\Carbon::parse($record->for_datetime)->format('d.m.Y') : '—' }}
                                    </div>
                                    <div class="archive-secondary-text">
                                        {{ $record->for_datetime ? \Illuminate\Support\Carbon::parse($record->for_datetime)->format('H:i') : '' }}
                                    </div>
                                </td>
                                <td>{{ $patient?->address_registration ?: '—' }}</td>
                                <td>
                                    <span class="archive-status {{ $record->trashed() ? 'is-trashed' : '' }}">
                                        {{ $record->trashed() ? 'В архиве' : 'Завершена' }}
                                    </span>
                                </td>
                                <td>
                                    @if($patient)
                                        <div class="archive-actions">
                                            <a href="{{ route('doctors.patients.medical_card', $patient->id) }}"
                                               class="archive-action"
                                               title="Открыть медицинскую карту">
                                                <i class="bi bi-person-lines-fill"></i>
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if($records->hasPages())
                    <div class="archive-pagination">
                        {{ $records->links() }}
                    </div>
                @endif
            @else
                <div class="archive-empty">
                    <i class="bi bi-archive fs-2 d-block mb-2"></i>
                    По выбранным условиям архивных записей не найдено.
                </div>
            @endif
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const patientPicker = document.getElementById('archivePatientPicker');
            const patientId = document.getElementById('archivePatientId');
            const patientSearch = document.getElementById('archivePatientSearch');
            const patientClear = document.getElementById('archivePatientClear');
            const patientDropdown = document.getElementById('archivePatientDropdown');
            const patientResults = document.getElementById('archivePatientResults');
            const patientState = document.getElementById('archivePatientState');
            const patientSearchUrl = @json(route('doctors.reception.archives'));
            let patientTimer = null;
            let patientAbort = null;

            function closePatientDropdown() {
                patientDropdown?.classList.add('d-none');
            }

            function showPatientState(text) {
                if (!patientState) return;
                patientState.textContent = text;
                patientState.classList.remove('d-none');
            }

            function renderPatients(items) {
                if (!patientResults || !patientState) return;

                patientResults.innerHTML = '';

                if (!items.length) {
                    showPatientState('Пациенты по этому запросу в архиве не найдены.');
                    return;
                }

                patientState.classList.add('d-none');

                items.forEach(item => {
                    const button = document.createElement('button');
                    const initials = String(item.name || '')
                        .split(/\s+/)
                        .filter(Boolean)
                        .slice(0, 2)
                        .map(word => word.charAt(0).toUpperCase())
                        .join('');
                    const meta = [item.birth_at, item.address_registration]
                        .filter(Boolean)
                        .join(' · ');

                    button.type = 'button';
                    button.className = 'archive-option';
                    button.innerHTML = `
                        <span class="archive-option__icon"></span>
                        <span class="archive-option__body">
                            <strong></strong>
                            <small></small>
                        </span>
                    `;
                    button.querySelector('.archive-option__icon').textContent = initials || 'П';
                    button.querySelector('strong').textContent = item.name || `Пациент #${item.id}`;
                    button.querySelector('small').textContent = meta || 'Архивная запись';

                    button.addEventListener('mousedown', event => event.preventDefault());
                    button.addEventListener('click', function () {
                        if (patientId) patientId.value = String(item.id || '');
                        if (patientSearch) {
                            patientSearch.value = item.name || '';
                            patientSearch.setCustomValidity('');
                        }
                        patientClear?.classList.toggle('d-none', !patientId?.value);
                        closePatientDropdown();
                    });

                    patientResults.appendChild(button);
                });
            }

            async function searchArchivePatients(query) {
                if (!patientDropdown) return;

                patientDropdown.classList.remove('d-none');

                if (query.length < 2) {
                    patientResults.innerHTML = '';
                    showPatientState('Начните вводить минимум 2 символа.');
                    return;
                }

                patientAbort?.abort();
                patientAbort = new AbortController();
                showPatientState('Ищем пациентов…');
                patientResults.innerHTML = '';

                try {
                    const url = new URL(patientSearchUrl, window.location.origin);
                    url.searchParams.set('_lookup', 'patient');
                    url.searchParams.set('q', query);

                    const response = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: patientAbort.signal
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const payload = await response.json();
                    renderPatients(Array.isArray(payload.results) ? payload.results : []);
                } catch (error) {
                    if (error.name === 'AbortError') return;
                    console.error('Archive patient search:', error);
                    showPatientState('Не удалось выполнить поиск пациентов.');
                }
            }

            patientSearch?.addEventListener('focus', function () {
                patientDropdown?.classList.remove('d-none');
                const query = patientSearch.value.trim();
                if (query.length >= 2 && !patientId?.value) {
                    searchArchivePatients(query);
                }
            });

            patientSearch?.addEventListener('input', function () {
                if (patientId) patientId.value = '';
                patientSearch.setCustomValidity('');
                patientClear?.classList.toggle('d-none', !patientSearch.value.trim());
                window.clearTimeout(patientTimer);
                const query = patientSearch.value.trim();
                patientTimer = window.setTimeout(() => searchArchivePatients(query), 250);
            });

            patientClear?.addEventListener('click', function () {
                if (patientId) patientId.value = '';
                if (patientSearch) {
                    patientSearch.value = '';
                    patientSearch.focus();
                }
                patientClear.classList.add('d-none');
                patientResults.innerHTML = '';
                patientDropdown?.classList.remove('d-none');
                showPatientState('Начните вводить минимум 2 символа.');
            });

            const doctorPicker = document.getElementById('archiveDoctorPicker');
            const doctorSelect = document.getElementById('archiveDoctorId');
            const doctorSearch = document.getElementById('archiveDoctorSearch');
            const doctorClear = document.getElementById('archiveDoctorClear');
            const doctorDropdown = document.getElementById('archiveDoctorDropdown');
            const doctorEmpty = document.getElementById('archiveDoctorEmpty');
            const doctorOptions = doctorDropdown
                ? Array.from(doctorDropdown.querySelectorAll('.archive-option'))
                : [];

            function selectedDoctorOption() {
                return doctorSelect?.options[doctorSelect.selectedIndex] || null;
            }

            function syncDoctorText() {
                if (!doctorSearch || !doctorSelect) return;
                const option = selectedDoctorOption();
                const hasDoctor = Boolean(doctorSelect.value && option);
                doctorSearch.value = hasDoctor
                    ? `${option.dataset.name || ''} — ${option.dataset.profile || ''}`
                    : '';
                doctorClear?.classList.toggle('d-none', !hasDoctor);
            }

            function filterDoctors() {
                const query = String(doctorSearch?.value || '')
                    .toLocaleLowerCase('ru-RU')
                    .trim();
                let visible = 0;

                doctorOptions.forEach(option => {
                    const search = String(option.dataset.search || '').toLocaleLowerCase('ru-RU');
                    const isAll = String(option.dataset.doctorId || '') === '';
                    const show = isAll || query === '' || search.includes(query);
                    option.classList.toggle('d-none', !show);
                    if (show) visible++;
                });

                doctorEmpty?.classList.toggle('d-none', visible > 0);
            }

            doctorSearch?.addEventListener('focus', function () {
                if (doctorSelect?.value) doctorSearch.select();
                doctorDropdown?.classList.remove('d-none');
                filterDoctors();
            });

            doctorSearch?.addEventListener('input', function () {
                if (doctorSelect) doctorSelect.value = '';
                doctorClear?.classList.add('d-none');
                doctorDropdown?.classList.remove('d-none');
                filterDoctors();
            });

            doctorOptions.forEach(option => {
                option.addEventListener('mousedown', event => event.preventDefault());
                option.addEventListener('click', function () {
                    if (!doctorSelect) return;
                    doctorSelect.value = option.dataset.doctorId || '';
                    syncDoctorText();
                    doctorDropdown?.classList.add('d-none');
                });
            });

            doctorClear?.addEventListener('click', function () {
                if (doctorSelect) doctorSelect.value = '';
                syncDoctorText();
                doctorSearch?.focus();
                doctorDropdown?.classList.remove('d-none');
                filterDoctors();
            });

            const birthInput = document.getElementById('archiveBirthAt');
            const birthTrigger = document.getElementById('archiveBirthDateTrigger');
            const birthText = document.getElementById('archiveBirthDateText');
            const birthCalendar = document.getElementById('archiveBirthCalendar');
            const birthMonth = document.getElementById('archiveBirthMonth');
            const birthDays = document.getElementById('archiveBirthDays');
            const birthPrev = document.getElementById('archiveBirthPrev');
            const birthNext = document.getElementById('archiveBirthNext');
            const birthToday = document.getElementById('archiveBirthToday');
            const birthClear = document.getElementById('archiveBirthClear');
            const monthNames = [
                'январь', 'февраль', 'март', 'апрель', 'май', 'июнь',
                'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'
            ];

            function parseIsoDate(value) {
                if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) return null;
                const [year, month, day] = value.split('-').map(Number);
                const date = new Date(year, month - 1, day);
                return Number.isNaN(date.getTime()) ? null : date;
            }

            function isoDate(date) {
                return [
                    date.getFullYear(),
                    String(date.getMonth() + 1).padStart(2, '0'),
                    String(date.getDate()).padStart(2, '0')
                ].join('-');
            }

            function displayDate(date) {
                return [
                    String(date.getDate()).padStart(2, '0'),
                    String(date.getMonth() + 1).padStart(2, '0'),
                    date.getFullYear()
                ].join('.');
            }

            const selectedBirth = parseIsoDate(birthInput?.value || '');
            let birthView = selectedBirth
                ? new Date(selectedBirth.getFullYear(), selectedBirth.getMonth(), 1)
                : new Date(new Date().getFullYear(), new Date().getMonth(), 1);

            function syncBirthText() {
                if (!birthText || !birthTrigger) return;
                const selected = parseIsoDate(birthInput?.value || '');
                birthText.textContent = selected ? displayDate(selected) : 'дд.мм.гггг';
                birthTrigger.classList.toggle('is-placeholder', !selected);
            }

            function renderBirthCalendar() {
                if (!birthDays || !birthMonth) return;
                birthDays.innerHTML = '';
                birthMonth.textContent = `${monthNames[birthView.getMonth()]} ${birthView.getFullYear()}`;

                const year = birthView.getFullYear();
                const month = birthView.getMonth();
                const firstDay = new Date(year, month, 1);
                const mondayIndex = (firstDay.getDay() + 6) % 7;
                const gridStart = new Date(year, month, 1 - mondayIndex);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const selected = parseIsoDate(birthInput?.value || '');

                for (let index = 0; index < 42; index++) {
                    const date = new Date(gridStart);
                    date.setDate(gridStart.getDate() + index);
                    date.setHours(0, 0, 0, 0);

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'archive-calendar__day';
                    button.textContent = String(date.getDate());

                    if (date.getMonth() !== month) button.classList.add('is-other-month');
                    if (date.getDay() === 0 || date.getDay() === 6) button.classList.add('is-weekend');
                    if (selected && isoDate(selected) === isoDate(date)) button.classList.add('is-selected');
                    if (date > today) button.disabled = true;

                    button.addEventListener('click', function () {
                        if (!birthInput) return;
                        birthInput.value = isoDate(date);
                        birthView = new Date(date.getFullYear(), date.getMonth(), 1);
                        syncBirthText();
                        renderBirthCalendar();
                        birthCalendar?.classList.add('d-none');
                    });

                    birthDays.appendChild(button);
                }
            }

            birthTrigger?.addEventListener('click', function () {
                const selected = parseIsoDate(birthInput?.value || '');
                if (selected) birthView = new Date(selected.getFullYear(), selected.getMonth(), 1);
                renderBirthCalendar();
                birthCalendar?.classList.toggle('d-none');
                closePatientDropdown();
                doctorDropdown?.classList.add('d-none');
            });

            birthPrev?.addEventListener('click', function () {
                birthView = new Date(birthView.getFullYear(), birthView.getMonth() - 1, 1);
                renderBirthCalendar();
            });

            birthNext?.addEventListener('click', function () {
                const candidate = new Date(birthView.getFullYear(), birthView.getMonth() + 1, 1);
                const currentMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
                if (candidate <= currentMonth) {
                    birthView = candidate;
                    renderBirthCalendar();
                }
            });

            birthToday?.addEventListener('click', function () {
                const today = new Date();
                if (birthInput) birthInput.value = isoDate(today);
                birthView = new Date(today.getFullYear(), today.getMonth(), 1);
                syncBirthText();
                renderBirthCalendar();
                birthCalendar?.classList.add('d-none');
            });

            birthClear?.addEventListener('click', function () {
                if (birthInput) birthInput.value = '';
                syncBirthText();
                birthCalendar?.classList.add('d-none');
            });

            const dadataToken = @json(config('dadata.dadata_api'));
            const addressInput = document.getElementById('archiveAddressRegistration');
            const addressSuggestions = document.getElementById('archiveAddressSuggestions');
            let addressTimer = null;
            let addressAbort = null;
            let addressActiveIndex = -1;

            function addressButtons() {
                return addressSuggestions
                    ? Array.from(addressSuggestions.querySelectorAll('.archive-address-suggestion'))
                    : [];
            }

            function hideAddressSuggestions() {
                addressSuggestions?.classList.add('d-none');
                addressActiveIndex = -1;
            }

            function chooseAddress(button) {
                if (!addressInput || !button) return;
                addressInput.value = button.dataset.value || '';
                hideAddressSuggestions();
            }

            function setAddressActive(index) {
                const buttons = addressButtons();
                if (!buttons.length) {
                    addressActiveIndex = -1;
                    return;
                }
                addressActiveIndex = Math.max(0, Math.min(index, buttons.length - 1));
                buttons.forEach((button, buttonIndex) => {
                    button.classList.toggle('is-active', buttonIndex === addressActiveIndex);
                });
                buttons[addressActiveIndex]?.scrollIntoView({block: 'nearest'});
            }

            async function loadAddressSuggestions(query) {
                if (!addressSuggestions || !dadataToken || query.length < 3) {
                    hideAddressSuggestions();
                    return;
                }

                addressAbort?.abort();
                addressAbort = new AbortController();

                try {
                    const response = await fetch(
                        'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address',
                        {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Token ${dadataToken}`
                            },
                            body: JSON.stringify({query, count: 8}),
                            signal: addressAbort.signal
                        }
                    );

                    if (!response.ok) throw new Error(`DaData HTTP ${response.status}`);

                    const payload = await response.json();
                    addressSuggestions.innerHTML = '';
                    addressActiveIndex = -1;

                    (payload.suggestions || []).forEach(suggestion => {
                        const button = document.createElement('button');
                        const data = suggestion.data || {};
                        const meta = [data.region_with_type, data.city_with_type || data.settlement_with_type]
                            .filter(Boolean)
                            .filter((value, index, array) => array.indexOf(value) === index)
                            .join(' · ');

                        button.type = 'button';
                        button.className = 'archive-address-suggestion';
                        button.dataset.value = suggestion.value || '';
                        button.innerHTML = `
                            <i class="bi bi-geo-alt"></i>
                            <span><strong></strong><small></small></span>
                        `;
                        button.querySelector('strong').textContent = suggestion.value || '';
                        button.querySelector('small').textContent = meta;
                        button.addEventListener('mousedown', event => event.preventDefault());
                        button.addEventListener('click', () => chooseAddress(button));
                        addressSuggestions.appendChild(button);
                    });

                    addressSuggestions.classList.toggle('d-none', !addressButtons().length);
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error('DaData archive address search:', error);
                    }
                }
            }

            addressInput?.addEventListener('input', function () {
                window.clearTimeout(addressTimer);
                const query = addressInput.value.trim();
                addressTimer = window.setTimeout(() => loadAddressSuggestions(query), 250);
            });

            addressInput?.addEventListener('keydown', function (event) {
                const buttons = addressButtons();
                if (event.key === 'ArrowDown' && buttons.length) {
                    event.preventDefault();
                    setAddressActive(addressActiveIndex + 1);
                } else if (event.key === 'ArrowUp' && buttons.length) {
                    event.preventDefault();
                    setAddressActive(addressActiveIndex <= 0 ? buttons.length - 1 : addressActiveIndex - 1);
                } else if (event.key === 'Enter' && addressActiveIndex >= 0 && buttons[addressActiveIndex]) {
                    event.preventDefault();
                    chooseAddress(buttons[addressActiveIndex]);
                } else if (event.key === 'Escape') {
                    hideAddressSuggestions();
                }
            });

            document.getElementById('archiveSearchForm')?.addEventListener('submit', function (event) {
                if (patientSearch?.value.trim() && !patientId?.value) {
                    event.preventDefault();
                    patientSearch.setCustomValidity('Выберите пациента из списка.');
                    patientSearch.reportValidity();
                    patientSearch.focus();
                    searchArchivePatients(patientSearch.value.trim());
                    return;
                }

                patientSearch?.setCustomValidity('');
            });

            document.addEventListener('click', function (event) {
                if (patientPicker && !patientPicker.contains(event.target)) closePatientDropdown();
                if (doctorPicker && !doctorPicker.contains(event.target)) {
                    doctorDropdown?.classList.add('d-none');
                    syncDoctorText();
                }
                if (birthCalendar && birthTrigger && !birthCalendar.contains(event.target) && !birthTrigger.contains(event.target)) {
                    birthCalendar.classList.add('d-none');
                }
                if (addressInput && addressSuggestions && !addressSuggestions.contains(event.target) && event.target !== addressInput) {
                    hideAddressSuggestions();
                }
            });

            syncDoctorText();
            syncBirthText();
        });
    </script>
@endsection
