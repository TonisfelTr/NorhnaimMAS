@extends('doctors.reception')

@section('title', 'Текущая регистратура')

@section('assets')
    <style>
        :root {
            --reception-primary: #048696;
            --reception-primary-dark: #036b78;
            --reception-primary-soft: #e8f8fa;
            --reception-bg: #f4f7fb;
            --reception-card: #ffffff;
            --reception-border: #e3e9ef;
            --reception-text: #1f2937;
            --reception-muted: #6b7280;
            --reception-danger: #dc3545;
            --reception-warning: #f59e0b;
        }

        .reception-page {
            min-height: calc(100vh - 80px);
            padding: 28px;
            background:
                radial-gradient(circle at top left, rgba(4, 134, 150, 0.12), transparent 34%),
                var(--reception-bg);
        }

        .reception-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 24px;
            padding: 28px;
            border: 1px solid var(--reception-border);
            border-radius: 26px;
            background: linear-gradient(135deg, #ffffff 0%, #f0fbfc 100%);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
        }

        .reception-header h1 {
            margin: 0 0 10px;
            color: var(--reception-text);
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .reception-header p {
            max-width: 760px;
            margin: 0;
            color: var(--reception-muted);
            font-size: 15px;
            line-height: 1.55;
        }

        .reception-header-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 999px;
            color: var(--reception-primary-dark);
            background: var(--reception-primary-soft);
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
        }

        .reception-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
            gap: 22px;
            margin-bottom: 22px;
        }

        .reception-card {
            border: 1px solid var(--reception-border);
            border-radius: 24px;
            background: var(--reception-card);
            box-shadow: 0 16px 42px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .reception-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 22px 24px;
            border-bottom: 1px solid var(--reception-border);
            background: #ffffff;
        }

        .reception-card-title {
            margin: 0;
            color: var(--reception-text);
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .reception-card-subtitle {
            margin-top: 4px;
            color: var(--reception-muted);
            font-size: 13px;
        }

        .reception-card-body {
            padding: 24px;
        }

        .reception-search .form-label {
            margin-bottom: 7px;
            color: #374151;
            font-size: 13px;
            font-weight: 700;
        }

        .reception-search .form-control,
        .reception-search .form-select {
            min-height: 42px;
            border-radius: 13px;
            border: 1px solid #d8e0e5;
            font-size: 14px;
        }

        .reception-search .form-control:focus,
        .reception-search .form-select:focus {
            border-color: var(--reception-primary);
            box-shadow: 0 0 0 0.2rem rgba(4, 134, 150, 0.14);
        }

        .reception-search-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
        }

        .reception-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 9px 16px;
            border: 0;
            border-radius: 12px;
            color: #ffffff;
            background: var(--reception-primary);
            font-weight: 700;
            text-decoration: none;
            transition: 0.18s ease;
        }

        .reception-btn-primary:hover {
            color: #ffffff;
            background: var(--reception-primary-dark);
        }

        .reception-btn-light {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 9px 16px;
            border: 1px solid var(--reception-border);
            border-radius: 12px;
            color: #334155;
            background: #ffffff;
            font-weight: 700;
            text-decoration: none;
            transition: 0.18s ease;
        }

        .reception-btn-light:hover {
            color: #111827;
            background: #f3f6f9;
        }

        .account-info-list {
            display: grid;
            gap: 14px;
        }

        .account-info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            border: 1px solid #eef2f6;
            border-radius: 16px;
            background: #f8fafc;
        }

        .account-info-icon {
            width: 38px;
            height: 38px;
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            color: var(--reception-primary-dark);
            background: var(--reception-primary-soft);
            font-size: 18px;
        }

        .account-info-label {
            margin-bottom: 2px;
            color: var(--reception-muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .account-info-value {
            color: var(--reception-text);
            font-size: 15px;
            font-weight: 700;
            line-height: 1.35;
        }

        .reception-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .reception-stat {
            padding: 18px;
            border-radius: 18px;
            background: linear-gradient(135deg, #f8fafc 0%, #eef8fa 100%);
            border: 1px solid #e3eef2;
        }

        .reception-stat-label {
            margin-bottom: 8px;
            color: var(--reception-muted);
            font-size: 13px;
            font-weight: 700;
        }

        .reception-stat-value {
            color: var(--reception-text);
            font-size: 30px;
            line-height: 1;
            font-weight: 800;
        }

        .reception-table-panel {
            border: 1px solid var(--reception-border);
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 16px 42px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .reception-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 22px 24px;
            border-bottom: 1px solid var(--reception-border);
            background: #ffffff;
        }

        .reception-table-header h2 {
            margin: 0;
            color: var(--reception-text);
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .reception-table-count {
            padding: 7px 12px;
            border-radius: 999px;
            color: var(--reception-primary-dark);
            background: var(--reception-primary-soft);
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }

        .reception-table-wrap {
            overflow-x: auto;
        }

        .reception-table {
            width: 100%;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .reception-table thead th {
            padding: 15px 18px;
            color: #667085;
            background: #f8fafc;
            border-bottom: 1px solid var(--reception-border);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.045em;
            white-space: nowrap;
        }

        .reception-table tbody td {
            padding: 17px 18px;
            border-bottom: 1px solid #eef2f6;
            color: var(--reception-text);
            font-size: 14px;
            vertical-align: middle;
        }

        .reception-table tbody tr {
            transition: 0.18s ease;
        }

        .reception-table tbody tr:hover {
            background: #f8fcfd;
        }

        .reception-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .reception-id {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            padding: 5px 9px;
            border-radius: 999px;
            color: #475569;
            background: #eef2f7;
            font-size: 13px;
            font-weight: 800;
        }

        .patient-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 240px;
        }

        .patient-avatar {
            width: 42px;
            height: 42px;
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            color: #ffffff;
            background: linear-gradient(135deg, var(--reception-primary), #10b7c6);
            font-size: 14px;
            font-weight: 800;
        }

        .patient-name {
            color: var(--reception-text);
            font-size: 15px;
            font-weight: 800;
            line-height: 1.25;
        }

        .patient-meta {
            margin-top: 3px;
            color: var(--reception-muted);
            font-size: 13px;
        }

        .doctor-cell {
            min-width: 260px;
        }

        .doctor-profession {
            color: var(--reception-primary-dark);
            font-size: 13px;
            font-weight: 800;
        }

        .doctor-name {
            margin-top: 3px;
            color: var(--reception-text);
            font-weight: 700;
        }

        .address-cell {
            min-width: 240px;
            color: #475569;
            line-height: 1.4;
        }

        .empty-muted {
            color: #94a3b8;
        }

        .table-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .table-action-btn {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            color: #475569;
            background: #ffffff;
            text-decoration: none;
            transition: 0.18s ease;
        }

        .table-action-btn:hover {
            color: var(--reception-primary-dark);
            background: var(--reception-primary-soft);
            border-color: #b7dce2;
        }

        .table-action-btn.danger {
            color: #b91c1c;
            background: #fff7f7;
            border-color: #fecaca;
        }

        .table-action-btn.danger:hover {
            color: #991b1b;
            background: #fee2e2;
        }

        .reception-empty {
            padding: 56px 24px;
            text-align: center;
        }

        .reception-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 24px;
            color: var(--reception-primary-dark);
            background: var(--reception-primary-soft);
            font-size: 34px;
        }

        .reception-empty h3 {
            margin: 0 0 8px;
            color: var(--reception-text);
            font-size: 22px;
            font-weight: 800;
        }

        .reception-empty p {
            max-width: 520px;
            margin: 0 auto;
            color: var(--reception-muted);
            font-size: 15px;
            line-height: 1.55;
        }

        .archive-modal .modal-content {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 28px 90px rgba(15, 23, 42, 0.22);
            overflow: hidden;
        }

        .archive-modal .modal-header {
            padding: 22px 24px;
            background: #fff7f7;
            border-bottom: 1px solid #fee2e2;
        }

        .archive-modal .modal-title {
            color: #991b1b;
            font-size: 20px;
            font-weight: 800;
        }

        .archive-modal .modal-body {
            padding: 24px;
            color: #374151;
            font-size: 15px;
            line-height: 1.55;
        }

        .archive-modal .modal-footer {
            padding: 18px 24px;
            background: #f8fafc;
            border-top: 1px solid var(--reception-border);
        }

        @media (max-width: 1100px) {
            .reception-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .reception-page {
                padding: 18px 14px;
            }

            .reception-header {
                flex-direction: column;
                padding: 22px;
            }

            .reception-header h1 {
                font-size: 28px;
            }

            .reception-card-header,
            .reception-table-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .reception-stats {
                grid-template-columns: 1fr;
            }

            .reception-search-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .reception-btn-primary,
            .reception-btn-light {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('confirmArchiveModal');

            if (!modal) {
                return;
            }

            modal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;

                if (!button) {
                    return;
                }

                const recordId = button.getAttribute('data-record-id');
                const patientName = button.getAttribute('data-patient-name') || 'эту запись';

                const form = document.getElementById('archiveForm');
                const modalPatientName = document.getElementById('archivePatientName');

                if (form && recordId) {
                    form.action = `reception/delete/${recordId}`;
                }

                if (modalPatientName) {
                    modalPatientName.textContent = patientName;
                }
            });
        });
    </script>
@endsection

@section('sub-main')
    <div class="reception-page">
        <div class="reception-header">
            <div>
                <h1>Регистратура</h1>
                <p>
                    Быстрый поиск пациентов, просмотр текущих записей и переход в медицинскую карту.
                    Используйте фильтры, чтобы найти пациента по ФИО, врачу, дате рождения или адресу прописки.
                </p>
            </div>

            <div class="reception-header-badge">
                <i class="bi bi-hospital"></i>
                Текущая регистратура
            </div>
        </div>

        <div class="reception-grid">
            <form class="reception-card reception-search" method="get">
                <div class="reception-card-header">
                    <div>
                        <h2 class="reception-card-title">Поиск пациента</h2>
                        <div class="reception-card-subtitle">
                            Можно искать по части ФИО, инициалам, врачу или адресу.
                        </div>
                    </div>

                    <i class="bi bi-search fs-4 text-secondary"></i>
                </div>

                <div class="reception-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="fullname" class="form-label">Полное имя</label>
                            <input id="fullname"
                                   name="fullname"
                                   class="form-control"
                                   placeholder="Например: Иванов И.И."
                                   value="{{ request('fullname') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="doctor_id" class="form-label">Врач</label>
                            <select id="doctor_id" name="doctor_id" class="form-select">
                                <option value="">— Все врачи —</option>

                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->profession }} {{ $doctor->surname }} {{ $doctor->name }} {{ $doctor->patronym }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="birth_at" class="form-label">Дата рождения</label>
                            <input id="birth_at"
                                   name="birth_at"
                                   class="form-control"
                                   type="date"
                                   value="{{ request('birth_at') }}">
                        </div>

                        <div class="col-12">
                            <label for="address_residence" class="form-label">Место прописки</label>
                            <input id="address_residence"
                                   name="address_residence"
                                   class="form-control"
                                   placeholder="Введите адрес или часть адреса"
                                   value="{{ request('address_residence') }}">
                        </div>
                    </div>

                    <div class="reception-search-actions">
                        <a href="{{ url()->current() }}" class="reception-btn-light">
                            Сбросить
                        </a>

                        <button type="submit" class="reception-btn-primary">
                            <i class="bi bi-search"></i>
                            Найти пациента
                        </button>
                    </div>
                </div>
            </form>

            <div class="reception-card">
                <div class="reception-card-header">
                    <div>
                        <h2 class="reception-card-title">Ваши данные</h2>
                        <div class="reception-card-subtitle">
                            Информация по текущему аккаунту и клинике.
                        </div>
                    </div>

                    <i class="bi bi-person-badge fs-4 text-secondary"></i>
                </div>

                <div class="reception-card-body">
                    <div class="account-info-list">
                        <div class="account-info-item">
                            <div class="account-info-icon">
                                <i class="bi bi-building"></i>
                            </div>

                            <div>
                                <div class="account-info-label">Клиника</div>
                                <div class="account-info-value">{{ $hostClinic->name }}</div>
                            </div>
                        </div>

                        <div class="account-info-item">
                            <div class="account-info-icon">
                                <i class="bi bi-person-circle"></i>
                            </div>

                            <div>
                                <div class="account-info-label">Хозяин аккаунта</div>
                                <div class="account-info-value">
                                    {{ $surname }} {{ $name }} {{ $patronym }}
                                    <span class="text-muted">({{ $profession }})</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="reception-stats">
                        <div class="reception-stat">
                            <div class="reception-stat-label">Записей на сегодня</div>
                            <div class="reception-stat-value">{{ $receptions->count() }}</div>
                        </div>

                        <div class="reception-stat">
                            <div class="reception-stat-label">В ожидании</div>
                            <div class="reception-stat-value">{{ $futureReceptions }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="reception-table-panel">
            <div class="reception-table-header">
                <h2>Текущие записи</h2>

                <div class="reception-table-count">
                    {{ $receptions->count() }} записей
                </div>
            </div>

            @if($receptions->count())
                <div class="reception-table-wrap">
                    <table class="reception-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Пациент</th>
                            <th>Дата рождения</th>
                            <th>Врач</th>
                            <th>Адрес прописки</th>
                            <th>Адрес проживания</th>
                            <th class="text-center">Действия</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($receptions as $reception)
                            @php
                                $patient = $reception->patient;
                                $doctor = $reception->doctor;

                                $patientFullname = $patient->fullname ?? 'Пациент не указан';

                                $initials = mb_substr($patient->surname ?? 'П', 0, 1)
                                    . mb_substr($patient->name ?? '', 0, 1);

                                $birthAt = $patient->birth_at ?? null;
                            @endphp

                            <tr>
                                <td>
                                    <span class="reception-id">#{{ $reception->id }}</span>
                                </td>

                                <td>
                                    <div class="patient-cell">
                                        <div class="patient-avatar">
                                            {{ $initials }}
                                        </div>

                                        <div>
                                            <div class="patient-name">
                                                {{ $patientFullname }}
                                            </div>

                                            <div class="patient-meta">
                                                ID пациента: {{ $patient->id ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if($birthAt)
                                        {{ \Carbon\Carbon::parse($birthAt)->format('d.m.Y') }}
                                    @else
                                        <span class="empty-muted">Не указана</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="doctor-cell">
                                        <div class="doctor-profession">
                                            {{ $doctor->profession ?? 'Врач' }}
                                        </div>

                                        <div class="doctor-name">
                                            {{ $doctor->surname ?? '' }}
                                            {{ $doctor->name ?? '' }}
                                            {{ $doctor->patronym ?? '' }}
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="address-cell">
                                        {{ $patient->address_registration ?: '—' }}
                                    </div>
                                </td>

                                <td>
                                    <div class="address-cell">
                                        {{ $patient->address_residence ?: '—' }}
                                    </div>
                                </td>

                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('doctors.patients.medical_card', $patient->id) }}"
                                           class="table-action-btn"
                                           title="Перейти к карточке пациента">
                                            <i class="bi bi-person-lines-fill"></i>
                                        </a>

                                        <a href="{{ route('doctors.reception.edit', $reception->id) }}"
                                           class="table-action-btn"
                                           title="Редактировать запись">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <button type="button"
                                                class="table-action-btn danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmArchiveModal"
                                                data-record-id="{{ $reception->id }}"
                                                data-patient-name="{{ $patientFullname }}"
                                                title="Перенести в архив">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="reception-empty">
                    <div class="reception-empty-icon">
                        <i class="bi bi-calendar-x"></i>
                    </div>

                    <h3>Пока нет записей в регистратуру</h3>

                    <p>
                        Когда появятся текущие записи пациентов, они будут отображаться в этой таблице.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade archive-modal"
         id="confirmArchiveModal"
         tabindex="-1"
         aria-labelledby="confirmArchiveModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="archiveForm" action="">
                @csrf

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmArchiveModalLabel">
                            Перенести запись в архив?
                        </h5>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Закрыть"></button>
                    </div>

                    <div class="modal-body">
                        Вы действительно хотите перенести в архив запись пациента
                        <strong id="archivePatientName">—</strong>?
                        <br>
                        Запись исчезнет из текущего списка регистратуры.
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="reception-btn-light" data-bs-dismiss="modal">
                            Отмена
                        </button>

                        <button type="submit" class="btn btn-danger rounded-3 fw-bold px-4">
                            Да, перенести
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
