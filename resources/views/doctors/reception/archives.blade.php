@extends('doctors.reception')

@section('title', 'Архив регистратуры')

@section('assets')
    <style>
        .reception-archive-page {
            padding: 28px 0 42px;
        }

        .reception-page-shell {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 16px;
        }

        .reception-archive-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
            padding: 24px 26px;
            border-radius: 24px;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.28), transparent 34%),
                linear-gradient(135deg, #048696 0%, #079aa9 100%);
            color: #fff;
            box-shadow: 0 20px 45px rgba(4, 134, 150, 0.20);
        }

        .reception-archive-hero-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .reception-archive-hero-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .reception-archive-hero h1 {
            margin: 0;
            font-size: 25px;
            font-weight: 800;
            line-height: 1.15;
        }

        .reception-archive-hero-subtitle {
            margin-top: 4px;
            color: rgba(255, 255, 255, 0.82);
            font-size: 14px;
            font-weight: 500;
        }

        .reception-archive-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 38px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
        }

        .reception-card {
            border: 0;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .reception-card-body {
            padding: 24px;
        }

        .reception-section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .reception-section-icon {
            width: 38px;
            height: 38px;
            border-radius: 13px;
            background: #e8f7f9;
            color: #048696;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .reception-section-title {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            color: #102a43;
        }

        .reception-section-subtitle {
            margin-top: 2px;
            font-size: 13px;
            color: #7b8794;
        }

        .form-label {
            font-size: 13px;
            font-weight: 700;
            color: #334e68;
            margin-bottom: 7px;
        }

        .form-control,
        .form-select {
            min-height: 42px;
            border-radius: 12px;
            border: 1px solid #d9e5e8;
            color: #102a43;
            font-size: 14px;
            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #048696;
            box-shadow: 0 0 0 4px rgba(4, 134, 150, 0.12) !important;
        }

        .archive-search-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 18px;
        }

        .btn-reception-primary {
            min-height: 42px;
            padding: 9px 18px;
            border: 0;
            border-radius: 13px;
            background: linear-gradient(135deg, #048696 0%, #079aa9 100%);
            color: #fff;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(4, 134, 150, 0.20);
        }

        .btn-reception-primary:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(4, 134, 150, 0.26);
        }

        .btn-reception-light {
            min-height: 42px;
            padding: 9px 18px;
            border-radius: 13px;
            border: 1px solid #d9e5e8;
            background: #fff;
            color: #334e68;
            font-weight: 800;
            text-decoration: none;
        }

        .btn-reception-light:hover {
            background: #f3fbfc;
            color: #047887;
            border-color: #bfe7ec;
        }

        .account-info-card {
            height: 100%;
            border: 1px solid #d9f0f4;
            border-radius: 24px;
            background: linear-gradient(135deg, #f0fbfd 0%, #ffffff 100%);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
            padding: 24px;
        }

        .account-info-item {
            display: flex;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #e8f1f3;
        }

        .account-info-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .account-info-label {
            min-width: 145px;
            color: #7b8794;
            font-size: 13px;
            font-weight: 700;
        }

        .account-info-value {
            color: #102a43;
            font-size: 14px;
            font-weight: 800;
        }

        .archive-table-card {
            margin-top: 22px;
            border: 0;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .archive-table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 22px 24px;
            border-bottom: 1px solid #e8f1f3;
            background: #fbfeff;
        }

        .archive-table-title {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: #102a43;
        }

        .archive-table-subtitle {
            margin-top: 3px;
            color: #7b8794;
            font-size: 13px;
        }

        .archive-count-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #e8f7f9;
            color: #047887;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }

        .archive-table {
            width: 100%;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .archive-table thead th {
            padding: 14px 16px;
            background: #f8fbfc;
            color: #52606d;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid #e8f1f3;
            white-space: nowrap;
        }

        .archive-table tbody td {
            padding: 15px 16px;
            color: #102a43;
            font-size: 14px;
            vertical-align: middle;
            border-bottom: 1px solid #edf2f7;
        }

        .archive-table tbody tr:hover {
            background: #fbfeff;
        }

        .archive-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .archive-id {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 28px;
            padding: 0 8px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
            font-size: 12px;
            font-weight: 800;
        }

        .patient-name {
            font-weight: 800;
            color: #102a43;
        }

        .doctor-name {
            color: #334e68;
            font-weight: 700;
        }

        .doctor-profession {
            display: block;
            color: #7b8794;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .status-cancelled {
            background: #f1f5f9;
            color: #64748b;
        }

        .status-visited {
            background: #e8f7f9;
            color: #047887;
        }

        .status-missed {
            background: #fff1f2;
            color: #be123c;
        }

        .archive-empty {
            padding: 44px 20px !important;
            text-align: center;
            color: #7b8794 !important;
            font-weight: 700;
        }

        .archive-empty i {
            display: block;
            margin-bottom: 10px;
            color: #048696;
            font-size: 28px;
        }

        @media (max-width: 768px) {
            .reception-archive-hero {
                align-items: flex-start;
                flex-direction: column;
                padding: 20px;
            }

            .reception-archive-hero-badge {
                white-space: normal;
            }

            .reception-card-body,
            .account-info-card {
                padding: 18px;
            }

            .archive-search-actions {
                flex-direction: column;
            }

            .btn-reception-primary,
            .btn-reception-light {
                width: 100%;
                text-align: center;
            }

            .archive-table-header {
                align-items: flex-start;
                flex-direction: column;
                padding: 18px;
            }

            .account-info-item {
                flex-direction: column;
                gap: 4px;
            }

            .account-info-label {
                min-width: auto;
            }
        }
    </style>
@endsection

@section('sub-main')
    <div class="reception-archive-page">
        <div class="reception-page-shell">

            <div class="reception-archive-hero">
                <div class="reception-archive-hero-title">
                    <div class="reception-archive-hero-icon">
                        <i class="bi bi-archive"></i>
                    </div>

                    <div>
                        <h1>Архив регистратуры</h1>
                        <div class="reception-archive-hero-subtitle">
                            Поиск и просмотр архивных записей пациентов
                        </div>
                    </div>
                </div>

                <div class="reception-archive-hero-badge">
                    <i class="bi bi-clock-history"></i>
                    Архивные записи
                </div>
            </div>

            <div class="row g-4 align-items-stretch">
                <div class="col-lg-8">
                    <div class="reception-card h-100">
                        <div class="reception-card-body">
                            <div class="reception-section-header">
                                <div class="reception-section-icon">
                                    <i class="bi bi-search"></i>
                                </div>

                                <div>
                                    <h4 class="reception-section-title">Поиск пациента</h4>
                                    <div class="reception-section-subtitle">
                                        Можно искать по ФИО, врачу, дате рождения или адресу регистрации
                                    </div>
                                </div>
                            </div>

                            <form method="get">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="fullname" class="form-label">Полное имя</label>
                                        <input id="fullname"
                                               name="fullname"
                                               class="form-control"
                                               placeholder="Например: Иванов И. И."
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

                                    <div class="col-md-12">
                                        <label for="address_residence" class="form-label">Место прописки</label>
                                        <input id="address_residence"
                                               name="address_residence"
                                               class="form-control"
                                               placeholder="Введите адрес или часть адреса"
                                               value="{{ request('address_residence') }}">
                                    </div>
                                </div>

                                <div class="archive-search-actions">
                                    <a href="{{ url()->current() }}" class="btn btn-reception-light">
                                        <i class="bi bi-x-circle"></i>
                                        Сбросить
                                    </a>

                                    <button type="submit" class="btn btn-reception-primary">
                                        <i class="bi bi-search"></i>
                                        Найти
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="account-info-card">
                        <div class="reception-section-header">
                            <div class="reception-section-icon">
                                <i class="bi bi-person-badge"></i>
                            </div>

                            <div>
                                <h4 class="reception-section-title">Ваши данные</h4>
                                <div class="reception-section-subtitle">
                                    Информация о текущем аккаунте
                                </div>
                            </div>
                        </div>

                        <div class="account-info-item">
                            <div class="account-info-label">Клиника</div>
                            <div class="account-info-value">
                                {{ $hostClinic?->name ?? '—' }}
                            </div>
                        </div>

                        <div class="account-info-item">
                            <div class="account-info-label">Хозяин аккаунта</div>
                            <div class="account-info-value">
                                {{ $surname }} {{ $name }} {{ $patronym }}
                            </div>
                        </div>

                        <div class="account-info-item">
                            <div class="account-info-label">Должность</div>
                            <div class="account-info-value">
                                {{ $profession }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="archive-table-card">
                <div class="archive-table-header">
                    <div>
                        <h3 class="archive-table-title">Найденные записи</h3>
                        <div class="archive-table-subtitle">
                            Список архивных записей по выбранным параметрам
                        </div>
                    </div>

                    <div class="archive-count-badge">
                        <i class="bi bi-list-check"></i>
                        Записей: {{ $records->count() }}
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="archive-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Дата рождения</th>
                            <th>Врач</th>
                            <th>Дата приёма</th>
                            <th>Статус</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($records as $reception)
                            <tr>
                                <td>
                                    <span class="archive-id">
                                        #{{ $reception->id }}
                                    </span>
                                </td>

                                <td>
                                    <span class="patient-name">
                                        {{ $reception->patient->fullname }}
                                    </span>
                                </td>

                                <td>
                                    {{ $reception->patient->birth_at }}
                                </td>

                                <td>
                                    <span class="doctor-profession">
                                        {{ $reception->doctor->profession }}
                                    </span>

                                    <span class="doctor-name">
                                        {{ $reception->doctor->surname }}
                                        {{ $reception->doctor->name }}
                                        {{ $reception->doctor->patronym }}
                                    </span>
                                </td>

                                <td>
                                    {{ $reception->for_datetime }}
                                </td>

                                <td>
                                    @if($reception->deleted_at)
                                        <span class="status-badge status-cancelled">
                                            <i class="bi bi-x-circle"></i>
                                            Отменено
                                        </span>
                                    @elseif($reception->appointment)
                                        <span class="status-badge status-visited">
                                            <i class="bi bi-check2-circle"></i>
                                            Посетил
                                        </span>
                                    @else
                                        <span class="status-badge status-missed">
                                            <i class="bi bi-exclamation-circle"></i>
                                            Не посетил
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="archive-empty">
                                    <i class="bi bi-info-circle-fill"></i>
                                    Архив пуст
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection
