@extends('layouts.doctor')

@section('title', 'Личный кабинет врача')

@section('assets')
<style>
    .doctor-profile-page {
        --cab-bg: #f2f6f9;
        --cab-card: #ffffff;
        --cab-border: #dce6ed;
        --cab-muted: #718397;
        --cab-text: #11283f;
        --cab-accent: #078b9e;
        --cab-accent-soft: #e9f7f8;
        --cab-green: #18855d;
        min-height: 100vh;
        padding: 28px;
        background:
            radial-gradient(circle at 10% 0%, rgba(31, 179, 190, .08), transparent 28rem),
            var(--cab-bg);
        color: var(--cab-text);
    }

    .cab-shell {
        width: min(1320px, 100%);
        margin: 0 auto;
    }

    .cab-hero {
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 24px;
        align-items: center;
        padding: 26px 28px;
        border: 1px solid rgba(191, 216, 226, .8);
        border-radius: 24px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fcfd 62%, #e9f8f8 100%);
        box-shadow: 0 15px 45px rgba(29, 57, 77, .07);
    }

    .cab-hero::after {
        content: '';
        position: absolute;
        width: 230px;
        height: 230px;
        right: -90px;
        top: -110px;
        border-radius: 50%;
        background: rgba(4, 149, 161, .09);
        pointer-events: none;
    }

    .cab-hero__eyebrow {
        margin-bottom: 6px;
        color: var(--cab-accent);
        font-size: 11px;
        font-weight: 850;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .cab-hero h1 {
        margin: 0;
        font-size: clamp(25px, 3vw, 36px);
        font-weight: 850;
        letter-spacing: -.025em;
    }

    .cab-hero p {
        max-width: 760px;
        margin: 7px 0 0;
        color: var(--cab-muted);
        font-size: 14px;
    }

    .cab-tabs {
        display: flex;
        gap: 6px;
        margin: 20px 0;
        padding: 6px;
        border: 1px solid var(--cab-border);
        border-radius: 16px;
        background: rgba(255, 255, 255, .82);
        backdrop-filter: blur(12px);
        box-shadow: 0 7px 24px rgba(25, 51, 70, .045);
        overflow-x: auto;
    }

    .cab-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 8px 17px;
        border-radius: 11px;
        color: #52697d;
        font-size: 13px;
        font-weight: 750;
        text-decoration: none;
        white-space: nowrap;
        transition: .16s ease;
    }

    .cab-tab:hover {
        color: var(--cab-accent);
        background: #f4fafb;
    }

    .cab-tab.is-active {
        color: #fff;
        background: linear-gradient(135deg, #0796a9, #087b92);
        box-shadow: 0 7px 17px rgba(7, 139, 158, .2);
    }

    .cab-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 330px;
        gap: 20px;
        align-items: start;
    }

    .cab-stack {
        display: grid;
        gap: 20px;
    }

    .cab-card {
        overflow: hidden;
        border: 1px solid var(--cab-border);
        border-radius: 20px;
        background: var(--cab-card);
        box-shadow: 0 9px 30px rgba(21, 50, 69, .045);
    }

    .cab-card__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 20px 22px;
        border-bottom: 1px solid #e8eef2;
    }

    .cab-card__head h2 {
        margin: 0;
        font-size: 17px;
        font-weight: 820;
    }

    .cab-card__head p {
        margin: 5px 0 0;
        color: var(--cab-muted);
        font-size: 12px;
        line-height: 1.55;
    }

    .cab-card__body {
        padding: 22px;
    }

    .cab-avatar-wrap {
        display: grid;
        grid-template-columns: 138px minmax(0, 1fr);
        gap: 22px;
        align-items: center;
    }

    .cab-avatar {
        width: 138px;
        height: 138px;
        overflow: hidden;
        border: 5px solid #fff;
        border-radius: 25px;
        background: #e7eff3;
        box-shadow: 0 0 0 1px var(--cab-border), 0 12px 28px rgba(27, 57, 77, .12);
    }

    .cab-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cab-avatar-copy h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 850;
    }

    .cab-avatar-copy p {
        margin: 6px 0 0;
        color: var(--cab-muted);
    }

    .cab-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 13px;
    }

    .cab-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border: 1px solid #dce7ec;
        border-radius: 999px;
        background: #f8fafb;
        color: #4f6577;
        font-size: 11px;
        font-weight: 700;
    }

    .cab-form-section + .cab-form-section {
        margin-top: 26px;
        padding-top: 23px;
        border-top: 1px solid #e8eef2;
    }

    .cab-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 16px;
        font-size: 14px;
        font-weight: 820;
    }

    .cab-section-title i {
        display: grid;
        place-items: center;
        width: 29px;
        height: 29px;
        border-radius: 9px;
        color: var(--cab-accent);
        background: var(--cab-accent-soft);
    }

    .cab-form-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 15px;
    }

    .cab-col-12 { grid-column: span 12; }
    .cab-col-8 { grid-column: span 8; }
    .cab-col-6 { grid-column: span 6; }
    .cab-col-4 { grid-column: span 4; }
    .cab-col-3 { grid-column: span 3; }

    .cab-field label {
        display: block;
        margin-bottom: 7px;
        color: #344e62;
        font-size: 12px;
        font-weight: 750;
    }

    .cab-field .form-control,
    .cab-field .form-select {
        min-height: 45px;
        border-color: #d4e0e7;
        border-radius: 11px;
        font-size: 13px;
        box-shadow: none;
    }

    .cab-field textarea.form-control {
        min-height: 118px;
        resize: vertical;
    }

    .cab-field .form-control:focus,
    .cab-field .form-select:focus {
        border-color: #58b9c4;
        box-shadow: 0 0 0 3px rgba(7, 139, 158, .09);
    }

    .cab-field .form-control[readonly] {
        background: #f6f8fa;
        color: #788b9a;
    }

    .cab-field__hint {
        margin-top: 6px;
        color: var(--cab-muted);
        font-size: 11px;
    }

    .cab-switch {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        min-height: 68px;
        padding: 14px 15px;
        border: 1px solid #dfe8ed;
        border-radius: 13px;
        background: #fafcfd;
    }

    .cab-switch strong {
        display: block;
        font-size: 13px;
    }

    .cab-switch small {
        display: block;
        margin-top: 3px;
        color: var(--cab-muted);
        font-size: 11px;
    }

    .cab-savebar {
        position: sticky;
        bottom: 14px;
        z-index: 15;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-top: 22px;
        padding: 13px 14px 13px 18px;
        border: 1px solid rgba(192, 216, 225, .9);
        border-radius: 15px;
        background: rgba(255, 255, 255, .94);
        backdrop-filter: blur(14px);
        box-shadow: 0 15px 35px rgba(19, 48, 68, .12);
    }

    .cab-savebar span {
        color: var(--cab-muted);
        font-size: 11px;
    }

    .cab-primary {
        border: 0;
        border-radius: 10px;
        padding: 10px 17px;
        color: #fff;
        background: linear-gradient(135deg, #0797aa, #08798f);
        font-size: 12px;
        font-weight: 800;
        box-shadow: 0 7px 18px rgba(7, 139, 158, .18);
    }

    .cab-summary-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 0;
        border-bottom: 1px solid #edf1f4;
        font-size: 12px;
    }

    .cab-summary-row:last-child {
        border-bottom: 0;
    }

    .cab-summary-row > span {
        color: var(--cab-muted);
    }

    .cab-summary-row > strong {
        text-align: right;
        overflow-wrap: anywhere;
    }

    .cab-rating-value {
        display: flex;
        align-items: baseline;
        gap: 7px;
    }

    .cab-rating-value strong {
        font-size: 42px;
        line-height: 1;
    }

    .cab-stars {
        margin: 8px 0 14px;
        color: #d79a00;
        letter-spacing: 2px;
    }

    .cab-rating-row {
        display: grid;
        grid-template-columns: 30px 1fr 30px;
        gap: 8px;
        align-items: center;
        margin-top: 7px;
        font-size: 11px;
    }

    .cab-rating-track {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #edf1f4;
    }

    .cab-rating-track > span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #0ba4b2, #087e92);
    }

    .cab-doc-form {
        padding: 18px;
        border: 1px solid #dfe8ed;
        border-radius: 16px;
        background: #f9fbfc;
    }

    .cab-doc-list {
        display: grid;
        gap: 12px;
        margin-top: 18px;
    }

    .cab-doc {
        padding: 17px;
        border: 1px solid #dce6ed;
        border-radius: 15px;
        background: #fff;
    }

    .cab-doc__top {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
    }

    .cab-doc__title {
        font-size: 13px;
        font-weight: 820;
    }

    .cab-doc__meta {
        margin-top: 6px;
        color: var(--cab-muted);
        font-size: 11px;
        line-height: 1.55;
    }

    .cab-history {
        position: relative;
        padding-left: 22px;
    }

    .cab-history::before {
        content: '';
        position: absolute;
        top: 8px;
        bottom: 8px;
        left: 6px;
        width: 1px;
        background: #d9e4e9;
    }

    .cab-event {
        position: relative;
        padding: 0 0 22px 15px;
    }

    .cab-event:last-child {
        padding-bottom: 0;
    }

    .cab-event::before {
        content: '';
        position: absolute;
        left: -20px;
        top: 5px;
        width: 11px;
        height: 11px;
        border: 3px solid #fff;
        border-radius: 50%;
        background: var(--cab-accent);
        box-shadow: 0 0 0 1px #9bcbd0;
    }

    .cab-event__title {
        font-size: 13px;
        font-weight: 800;
    }

    .cab-event__meta {
        margin-top: 3px;
        color: var(--cab-muted);
        font-size: 11px;
    }

    .cab-event ul {
        margin: 8px 0 0;
        padding-left: 18px;
        color: #526879;
        font-size: 11px;
    }

    .cab-empty {
        padding: 32px 20px;
        text-align: center;
        color: var(--cab-muted);
    }

    @media (max-width: 1050px) {
        .cab-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .doctor-profile-page {
            padding: 18px 12px;
        }

        .cab-hero {
            grid-template-columns: 1fr;
            padding: 21px;
        }

        .cab-avatar-wrap {
            grid-template-columns: 1fr;
        }

        .cab-col-8,
        .cab-col-6,
        .cab-col-4,
        .cab-col-3 {
            grid-column: span 12;
        }

        .cab-savebar {
            align-items: stretch;
            flex-direction: column;
        }

        .cab-primary {
            width: 100%;
        }
    }

    .cab-avatar-wrap {
        grid-template-columns: 190px minmax(0, 1fr);
        align-items: center;
    }

    .cab-photo-shell {
        position: relative;
        width: 190px;
    }

    .cab-photo-frame {
        position: relative;
        width: 190px;
        aspect-ratio: 4 / 5;
        overflow: hidden;
        border: 5px solid #fff;
        border-radius: 20px;
        background:
            linear-gradient(145deg, #e9f6f7, #dcecf0);
        box-shadow:
            0 0 0 1px var(--cab-border),
            0 14px 32px rgba(27, 57, 77, .14);
        cursor: context-menu;
        user-select: none;
    }

    .cab-photo-frame img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
    }

    .cab-photo-placeholder {
        display: grid;
        place-items: center;
        align-content: center;
        gap: 8px;
        width: 100%;
        height: 100%;
        color: #476575;
        text-align: center;
        background:
            radial-gradient(circle at 50% 25%, rgba(255,255,255,.9), transparent 42%),
            linear-gradient(160deg, #edf8f8, #dceef1);
    }

    .cab-photo-placeholder strong {
        font-size: 36px;
        font-weight: 850;
        letter-spacing: .03em;
        color: #0d8291;
    }

    .cab-photo-placeholder span {
        font-size: 11px;
        color: #78909d;
    }

    .cab-photo-hint {
        margin-top: 9px;
        color: var(--cab-muted);
        font-size: 10px;
        text-align: center;
    }

    .cab-photo-menu {
        position: fixed;
        z-index: 2100;
        min-width: 190px;
        padding: 6px;
        border: 1px solid #d7e2e8;
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 18px 45px rgba(16, 43, 61, .22);
    }

    .cab-photo-menu[hidden] {
        display: none !important;
    }

    .cab-photo-menu button {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 9px 10px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: #334e61;
        font-size: 12px;
        font-weight: 650;
        text-align: left;
    }

    .cab-photo-menu button:hover {
        background: #eef8f8;
        color: #087d8c;
    }

    .cab-photo-menu button.is-danger {
        color: #b24955;
    }

    .cab-photo-menu button.is-danger:hover {
        background: #fff1f2;
        color: #a52f3d;
    }

    .cab-photo-lightbox {
        position: fixed;
        inset: 0;
        z-index: 2200;
        display: grid;
        place-items: center;
        padding: 26px;
        background: rgba(5, 18, 28, .78);
        backdrop-filter: blur(7px);
    }

    .cab-photo-lightbox[hidden] {
        display: none !important;
    }

    .cab-photo-lightbox__dialog {
        position: relative;
        max-width: min(760px, 92vw);
        max-height: 90vh;
    }

    .cab-photo-lightbox__dialog img {
        display: block;
        max-width: 100%;
        max-height: 88vh;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 25px 70px rgba(0,0,0,.35);
    }

    .cab-photo-lightbox__close {
        position: absolute;
        top: -14px;
        right: -14px;
        display: grid;
        place-items: center;
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 50%;
        background: #fff;
        color: #263f50;
        box-shadow: 0 8px 20px rgba(0,0,0,.22);
    }

    .cab-field.is-locked .form-control {
        background: #f4f7f9;
        color: #687e8e;
        cursor: default;
    }

    .cab-source-note {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        color: #6e8796;
        font-size: 10px;
    }

    .cab-source-note i {
        color: #0c8b9d;
    }

    @media (max-width: 767.98px) {
        .cab-avatar-wrap {
            grid-template-columns: 1fr;
        }

        .cab-photo-shell {
            width: min(210px, 72vw);
        }

        .cab-photo-frame {
            width: 100%;
        }
    }

</style>
@endsection

@section('main')

<main class="doctor-profile-page">
    <div class="cab-shell">
        <header class="cab-hero">
            <div>
                <div class="cab-hero__eyebrow">Личный кабинет врача</div>
                <h1>
                    @if($doctor)
                        {{ $doctor->surname }} {{ $doctor->name }} {{ $doctor->patronym }}
                    @else
                        Профиль врача
                    @endif
                </h1>
                <p>
                    Управление персональными и профессиональными данными, документами и историей изменений профиля.
                </p>
            </div>

            <a href="{{ route('main.index') }}"
               class="btn btn-outline-secondary position-relative">
                <i class="bi bi-box-arrow-up-right me-1"></i>
                На сайт
            </a>
        </header>

        <nav class="cab-tabs" aria-label="Разделы личного кабинета">
            <a href="{{ route('doctors.profile.index', ['tab' => 'profile']) }}"
               class="cab-tab {{ !in_array(request('tab'), ['documents', 'history'], true) ? 'is-active' : '' }}">
                <i class="bi bi-person"></i>
                Профиль
            </a>

            <a href="{{ route('doctors.profile.index', ['tab' => 'documents']) }}"
               class="cab-tab {{ request('tab') === 'documents' ? 'is-active' : '' }}">
                <i class="bi bi-folder2-open"></i>
                Документы
                @if($credentials->count())
                    <span class="badge rounded-pill text-bg-light text-dark">
                        {{ $credentials->count() }}
                    </span>
                @endif
            </a>

            <a href="{{ route('doctors.profile.index', ['tab' => 'history']) }}"
               class="cab-tab {{ request('tab') === 'history' ? 'is-active' : '' }}">
                <i class="bi bi-clock-history"></i>
                История
            </a>
        </nav>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">
                <i class="bi bi-check-circle me-1"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger shadow-sm">
                <div class="fw-semibold mb-1">Проверьте заполнение формы</div>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!$doctor)
            <div class="alert alert-warning">
                К текущему пользователю не привязан профиль врача.
            </div>
        @elseif(!in_array(request('tab'), ['documents', 'history'], true))
            <div class="cab-grid">
                <div class="cab-stack">
                    <section class="cab-card">
                        <div class="cab-card__body">
                            <div class="cab-avatar-wrap">
                                <div class="cab-photo-shell">
                                    <div id="doctorProfilePhotoFrame"
                                         class="cab-photo-frame"
                                         tabindex="0"
                                         aria-label="Фотография врача. Правой кнопкой мыши открыть меню действий.">
                                        @if($profileContext['has_photo'])
                                            <img id="doctorProfilePhotoImage"
                                                 src="{{ $profileContext['photo_url'] }}"
                                                 alt="Фотография врача">
                                        @else
                                            <div class="cab-photo-placeholder">
                                                <strong>{{ $profileContext['initials'] }}</strong>
                                                <span>Фотография не загружена</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="cab-photo-hint">
                                        Правый клик по фотографии — действия
                                    </div>

                                    <div id="doctorPhotoContextMenu"
                                         class="cab-photo-menu"
                                         role="menu"
                                         hidden>
                                        @if($profileContext['has_photo'])
                                            <button type="button"
                                                    data-photo-action="zoom"
                                                    role="menuitem">
                                                <i class="bi bi-arrows-fullscreen"></i>
                                                Увеличить
                                            </button>
                                        @endif

                                        <button type="button"
                                                data-photo-action="upload"
                                                role="menuitem">
                                            <i class="bi bi-upload"></i>
                                            {{ $profileContext['has_photo'] ? 'Загрузить другую' : 'Загрузить' }}
                                        </button>

                                        @if($profileContext['has_photo'])
                                            <button type="button"
                                                    class="is-danger"
                                                    data-photo-action="delete"
                                                    role="menuitem">
                                                <i class="bi bi-trash3"></i>
                                                Удалить
                                            </button>
                                        @endif
                                    </div>

                                    <form id="doctorPhotoUploadForm"
                                          method="post"
                                          action="{{ route('doctors.profile.photo') }}"
                                          enctype="multipart/form-data"
                                          class="d-none">
                                        @csrf
                                        <input id="doctorProfilePhotoInput"
                                               type="file"
                                               name="photo"
                                               accept="image/png,image/jpeg,image/webp">
                                    </form>

                                    @if($profileContext['has_photo'])
                                        <form id="doctorPhotoDeleteForm"
                                              method="post"
                                              action="{{ route('doctors.profile.photo.destroy') }}"
                                              class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </div>

                                <div class="cab-avatar-copy">
                                    <h2>
                                        {{ $doctor->surname }}
                                        {{ $doctor->name }}
                                        {{ $doctor->patronym }}
                                    </h2>

                                    <p>
                                        {{ $doctor->profession }}
                                        ·
                                        {{ $doctor->status }}
                                    </p>

                                    <div class="cab-badges">
                                        <span class="cab-badge">
                                            <i class="bi bi-building"></i>
                                            {{ $doctor->clinic?->name ?: 'Частная практика' }}
                                        </span>

                                        @if($doctor->ready_for_telemedicine)
                                            <span class="cab-badge">
                                                <i class="bi bi-camera-video"></i>
                                                Телемедицина
                                            </span>
                                        @endif

                                        @if($doctor->professional_verified_at)
                                            <span class="cab-badge">
                                                <i class="bi bi-patch-check"></i>
                                                Профиль проверен
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="cab-card">
                        <div class="cab-card__head">
                            <div>
                                <h2>Редактирование профиля</h2>
                                <p>
                                    Эти сведения используются внутри МИС и в публичном профиле врача.
                                    Все изменения автоматически записываются в историю.
                                </p>
                            </div>
                        </div>

                        <div class="cab-card__body">
                            <form method="post"
                                  action="{{ route('doctors.profile.update') }}">
                                @csrf
                                @method('PATCH')

                                <div class="cab-form-section">
                                    <div class="cab-section-title">
                                        <i class="bi bi-person-vcard"></i>
                                        Личные данные
                                    </div>

                                    <div class="cab-form-grid">
                                        <div class="cab-field cab-col-4">
                                            <label for="profileSurname">Фамилия</label>
                                            <input id="profileSurname"
                                                   type="text"
                                                   name="surname"
                                                   class="form-control"
                                                   maxlength="255"
                                                   autocomplete="family-name"
                                                   value="{{ old('surname', $doctor->surname) }}"
                                                   required>
                                        </div>

                                        <div class="cab-field cab-col-4">
                                            <label for="profileName">Имя</label>
                                            <input id="profileName"
                                                   type="text"
                                                   name="name"
                                                   class="form-control"
                                                   maxlength="255"
                                                   autocomplete="given-name"
                                                   value="{{ old('name', $doctor->name) }}"
                                                   required>
                                        </div>

                                        <div class="cab-field cab-col-4">
                                            <label for="profilePatronym">Отчество</label>
                                            <input id="profilePatronym"
                                                   type="text"
                                                   name="patronym"
                                                   class="form-control"
                                                   maxlength="255"
                                                   value="{{ old('patronym', $doctor->patronym) }}">
                                        </div>

                                        <div class="cab-field cab-col-4">
                                            <label for="profileBirthAt">Дата рождения</label>
                                            <input id="profileBirthAt"
                                                   type="date"
                                                   name="birth_at"
                                                   class="form-control"
                                                   value="{{ old('birth_at', $doctor->birth_at?->format('Y-m-d')) }}"
                                                   required>
                                        </div>

                                        <div class="cab-field cab-col-4">
                                            <label for="profileSnils">СНИЛС</label>
                                            <input id="profileSnils"
                                                   type="text"
                                                   name="snils"
                                                   class="form-control"
                                                   maxlength="14"
                                                   inputmode="numeric"
                                                   placeholder="000-000-000 00"
                                                   value="{{ old('snils', $doctor->snils) }}">
                                            <div class="cab-field__hint">
                                                Нужен для будущей синхронизации с ФРМР/ЕГИСЗ.
                                            </div>
                                        </div>

                                        <div class="cab-field cab-col-4">
                                            <label>ФРМР ID</label>
                                            <input type="text"
                                                   class="form-control"
                                                   value="{{ $doctor->frmr_id }}"
                                                   placeholder="Появится после синхронизации"
                                                   readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="cab-form-section">
                                    <div class="cab-section-title">
                                        <i class="bi bi-telephone"></i>
                                        Контакты и местоположение
                                    </div>

                                    <div class="cab-form-grid">
                                        <div class="cab-field cab-col-4 {{ $profileContext['phone_locked'] ? 'is-locked' : '' }}">
                                            <label for="profilePhone">Телефон</label>
                                            <input id="profilePhone"
                                                   type="tel"
                                                   name="phone"
                                                   class="form-control"
                                                   maxlength="255"
                                                   autocomplete="tel"
                                                   value="{{ $profileContext['phone_locked'] ? $profileContext['phone'] : old('phone', $profileContext['phone']) }}"
                                                   @readonly($profileContext['phone_locked'])>

                                            @if($profileContext['phone_locked'])
                                                <div class="cab-source-note">
                                                    <i class="bi bi-lock"></i>
                                                    Телефон берётся из настроек {{ $profileContext['cabinet_label'] }}.
                                                </div>
                                            @else
                                                <div class="cab-field__hint">
                                                    Врач не привязан к кабинету — телефон можно указать вручную.
                                                </div>
                                            @endif
                                        </div>

                                        <div class="cab-field cab-col-4">
                                            <label for="profileEmail">E-mail</label>
                                            <input id="profileEmail"
                                                   type="email"
                                                   name="email"
                                                   class="form-control"
                                                   maxlength="255"
                                                   autocomplete="email"
                                                   value="{{ old('email', $doctor->email) }}">
                                        </div>

                                        <div class="cab-field cab-col-4 {{ $profileContext['city_locked'] ? 'is-locked' : '' }}">
                                            <label for="profileCity">Город</label>
                                            <input id="profileCity"
                                                   type="text"
                                                   name="city"
                                                   class="form-control"
                                                   maxlength="255"
                                                   autocomplete="address-level2"
                                                   value="{{ $profileContext['city_locked'] ? $profileContext['city'] : old('city', $profileContext['city']) }}"
                                                   @readonly($profileContext['city_locked'])>

                                            @if($profileContext['city_locked'])
                                                <div class="cab-source-note">
                                                    <i class="bi bi-lock"></i>
                                                    Определяется настройками клиники.
                                                </div>
                                            @endif
                                        </div>

                                        <div class="cab-field cab-col-12 {{ $profileContext['work_address_locked'] ? 'is-locked' : '' }}">
                                            <label for="profileAddressJob">Место работы / рабочий адрес</label>
                                            <input id="profileAddressJob"
                                                   type="text"
                                                   name="address_job"
                                                   class="form-control"
                                                   maxlength="2000"
                                                   value="{{ $profileContext['work_address_locked'] ? $profileContext['work_address'] : old('address_job', $profileContext['work_address']) }}"
                                                   @readonly($profileContext['work_address_locked'])
                                                   @required(!$profileContext['work_address_locked'])>

                                            @if($profileContext['work_address_locked'])
                                                <div class="cab-source-note">
                                                    <i class="bi bi-lock"></i>
                                                    Адрес берётся из настроек {{ $profileContext['clinic_name'] }}.
                                                </div>
                                            @else
                                                <div class="cab-field__hint">
                                                    Для частной практики адрес указывается вручную.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="cab-form-section">
                                    <div class="cab-section-title">
                                        <i class="bi bi-briefcase"></i>
                                        Дополнительные сведения
                                    </div>

                                    <div class="cab-form-grid">
                                        <div class="cab-field cab-col-3">
                                            <label for="profileExperienceYears">Стаж, лет</label>
                                            <input id="profileExperienceYears"
                                                   type="number"
                                                   name="experience_years"
                                                   class="form-control"
                                                   min="0"
                                                   max="80"
                                                   value="{{ old('experience_years', $doctor->experience_years) }}">
                                        </div>

                                        <div class="cab-field cab-col-3">
                                            <label for="profileExperienceMonths">Доп. месяцев</label>
                                            <input id="profileExperienceMonths"
                                                   type="number"
                                                   name="experience_months"
                                                   class="form-control"
                                                   min="0"
                                                   max="11"
                                                   value="{{ old('experience_months', $doctor->experience_months) }}">
                                        </div>

                                        <div class="cab-col-6">
                                            <input type="hidden"
                                                   name="ready_for_telemedicine"
                                                   value="0">

                                            <label class="cab-switch"
                                                   for="profileTelemedicine">
                                                <span>
                                                    <strong>Готов к телемедицине</strong>
                                                    <small>
                                                        Разрешить использование врача в телемедицинских сценариях.
                                                    </small>
                                                </span>

                                                <span class="form-check form-switch m-0">
                                                    <input id="profileTelemedicine"
                                                           class="form-check-input"
                                                           type="checkbox"
                                                           name="ready_for_telemedicine"
                                                           value="1"
                                                           @checked(old('ready_for_telemedicine', $doctor->ready_for_telemedicine))>
                                                </span>
                                            </label>
                                        </div>

                                        <div class="cab-field cab-col-12">
                                            <label for="profileAbout">О себе</label>
                                            <textarea id="profileAbout"
                                                      name="about"
                                                      class="form-control"
                                                      maxlength="5000"
                                                      placeholder="Профессиональные интересы, направления работы, дополнительная информация о враче">{{ old('about', $doctor->about) }}</textarea>
                                            <div class="cab-field__hint">
                                                Должность и специальность задаются в настройках клиники и здесь не редактируются.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="cab-savebar">
                                    <span>
                                        <i class="bi bi-clock-history me-1"></i>
                                        После сохранения изменения появятся во вкладке «История».
                                    </span>

                                    <button type="submit"
                                            class="cab-primary">
                                        <i class="bi bi-check2-circle me-1"></i>
                                        Сохранить изменения
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>

                <aside class="cab-stack">
                    <section class="cab-card">
                        <div class="cab-card__head">
                            <div>
                                <h2>Рейтинг</h2>
                                <p>По отзывам пациентов.</p>
                            </div>
                        </div>

                        <div class="cab-card__body">
                            <div class="cab-rating-value">
                                <strong>{{ number_format($rating, 1, ',', '') }}</strong>
                                <span class="text-muted">/ 5</span>
                            </div>

                            <div class="cab-stars">★★★★★</div>

                            <div class="small text-muted mb-3">
                                Отзывов: {{ $feedbackCount }}
                            </div>

                            @for($mark = 5; $mark >= 1; $mark--)
                                <div class="cab-rating-row">
                                    <span>{{ $mark }}★</span>

                                    <div class="cab-rating-track">
                                        <span style="width: {{ $feedbackCount > 0 ? round(($marks[$mark] / $feedbackCount) * 100) : 0 }}%"></span>
                                    </div>

                                    <span class="text-end">{{ $marks[$mark] }}</span>
                                </div>
                            @endfor
                        </div>
                    </section>

                    <section class="cab-card">
                        <div class="cab-card__head">
                            <div>
                                <h2>Служебные данные</h2>
                                <p>Данные организации и проверки не меняются вручную.</p>
                            </div>
                        </div>

                        <div class="cab-card__body">
                            <div class="cab-summary-row">
                                <span>Клиника</span>
                                <strong>{{ $doctor->clinic?->name ?: 'Частная практика' }}</strong>
                            </div>

                            <div class="cab-summary-row">
                                <span>Диплом</span>
                                <strong>
                                    <span class="badge text-bg-{{ $verificationSummary['diploma']['class'] }}">
                                        {{ $verificationSummary['diploma']['label'] }}
                                    </span>
                                </strong>
                            </div>

                            <div class="cab-summary-row">
                                <span>Аккредитация</span>
                                <strong>
                                    <span class="badge text-bg-{{ $verificationSummary['accreditation']['class'] }}">
                                        {{ $verificationSummary['accreditation']['label'] }}
                                    </span>
                                </strong>
                            </div>

                            <div class="cab-summary-row">
                                <span>Номер аккредитации</span>
                                <strong>{{ $doctor->accreditation_number ?: '—' }}</strong>
                            </div>

                            <div class="cab-summary-row">
                                <span>Срок действия</span>
                                <strong>
                                    {{ $doctor->accreditation_valid_until?->format('d.m.Y') ?: '—' }}
                                </strong>
                            </div>

                            <div class="cab-summary-row">
                                <span>Последняя проверка</span>
                                <strong>
                                    {{ $doctor->professional_verified_at?->format('d.m.Y H:i') ?: '—' }}
                                </strong>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>
        @elseif(request('tab') === 'documents')
            <section class="cab-card">
                <div class="cab-card__head">
                    <div>
                        <h2>Документы врача</h2>
                        <p>
                            Дипломы, аккредитации, аттестаты, повышение квалификации,
                            переподготовка и другие подтверждающие документы.
                        </p>
                    </div>
                </div>

                <div class="cab-card__body">
                    <form method="post"
                          action="{{ route('doctors.profile.credentials.store') }}"
                          enctype="multipart/form-data"
                          class="cab-doc-form">
                        @csrf

                        <div class="cab-form-grid">
                            <div class="cab-field cab-col-4">
                                <label>Тип документа</label>
                                <select name="kind"
                                        class="form-select"
                                        required>
                                    @foreach($credentialKinds as $value => $label)
                                        <option value="{{ $value }}">
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="cab-field cab-col-8">
                                <label>Название</label>
                                <input type="text"
                                       name="title"
                                       class="form-control"
                                       maxlength="255"
                                       required>
                            </div>

                            <div class="cab-field cab-col-3">
                                <label>Серия</label>
                                <input type="text"
                                       name="series"
                                       class="form-control"
                                       maxlength="100">
                            </div>

                            <div class="cab-field cab-col-3">
                                <label>Номер</label>
                                <input type="text"
                                       name="number"
                                       class="form-control"
                                       maxlength="160">
                            </div>

                            <div class="cab-field cab-col-3">
                                <label>Дата выдачи</label>
                                <input type="date"
                                       name="issued_at"
                                       class="form-control">
                            </div>

                            <div class="cab-field cab-col-3">
                                <label>Действителен до</label>
                                <input type="date"
                                       name="valid_until"
                                       class="form-control">
                            </div>

                            <div class="cab-field cab-col-6">
                                <label>Кем выдан</label>
                                <input type="text"
                                       name="issuer"
                                       class="form-control"
                                       maxlength="500">
                            </div>

                            <div class="cab-field cab-col-3">
                                <label>Специальность</label>
                                <input type="text"
                                       name="speciality"
                                       class="form-control"
                                       maxlength="255">
                            </div>

                            <div class="cab-field cab-col-3">
                                <label>Номер в реестре</label>
                                <input type="text"
                                       name="registry_number"
                                       class="form-control"
                                       maxlength="255">
                            </div>

                            <div class="cab-field cab-col-8">
                                <label>Файл документа</label>
                                <input type="file"
                                       name="document"
                                       class="form-control"
                                       accept=".pdf,image/png,image/jpeg,image/webp"
                                       required>
                                <div class="cab-field__hint">
                                    PDF, JPG, PNG или WEBP до 15 МБ.
                                </div>
                            </div>

                            <div class="cab-col-4 d-flex align-items-end">
                                <button type="submit"
                                        class="cab-primary w-100">
                                    <i class="bi bi-upload me-1"></i>
                                    Добавить документ
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="cab-doc-list">
                        @forelse($credentials as $credential)
                            <article class="cab-doc">
                                <div class="cab-doc__top">
                                    <div>
                                        <div class="cab-doc__title">
                                            {{ $credential->kindLabel() }}
                                            — {{ $credential->title }}
                                        </div>

                                        <div class="cab-doc__meta">
                                            @if($credential->series || $credential->number)
                                                Серия / номер:
                                                {{ trim(($credential->series ?: '') . ' ' . ($credential->number ?: '')) }}
                                                <br>
                                            @endif

                                            @if($credential->issued_at)
                                                Выдан:
                                                {{ $credential->issued_at->format('d.m.Y') }}
                                            @endif

                                            @if($credential->valid_until)
                                                · до
                                                {{ $credential->valid_until->format('d.m.Y') }}
                                            @endif

                                            @if($credential->issuer)
                                                <br>{{ $credential->issuer }}
                                            @endif

                                            @if($credential->speciality)
                                                <br>
                                                Специальность:
                                                {{ $credential->speciality }}
                                            @endif
                                        </div>
                                    </div>

                                    <span class="badge text-bg-{{ $credential->verificationBadgeClass() }}">
                                        {{ $credential->verificationLabel() }}
                                    </span>
                                </div>

                                @if($credential->verification_message)
                                    <div class="small text-muted mt-3">
                                        {{ $credential->verification_message }}
                                    </div>
                                @endif

                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    @if($credential->getFirstMedia('document'))
                                        <a href="{{ route('doctors.profile.credentials.download', $credential) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           target="_blank">
                                            <i class="bi bi-file-earmark-arrow-down me-1"></i>
                                            Открыть
                                        </a>
                                    @endif

                                    <form method="post"
                                          action="{{ route('doctors.profile.credentials.verify', $credential) }}">
                                        @csrf

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-shield-check me-1"></i>
                                            Проверить
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="cab-empty">
                                <i class="bi bi-folder2-open fs-2 d-block mb-2"></i>
                                Документы пока не загружены.
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        @else
            <section class="cab-card">
                <div class="cab-card__head">
                    <div>
                        <h2>История изменений</h2>
                        <p>
                            Неизменяемый журнал обновлений профиля, фотографий,
                            документов и результатов проверок.
                        </p>
                    </div>

                    <span class="badge rounded-pill text-bg-light border">
                        {{ $events->count() }} записей
                    </span>
                </div>

                <div class="cab-card__body">
                    @forelse($events as $event)
                        <div class="cab-history">
                            <article class="cab-event">
                                <div class="cab-event__title">
                                    {{ $event->title }}
                                </div>

                                <div class="cab-event__meta">
                                    {{ $event->occurred_at?->format('d.m.Y H:i:s') }}

                                    @if($event->actor)
                                        · {{ $event->actor->login }}
                                    @endif
                                </div>

                                @if($event->changeLines())
                                    <ul>
                                        @foreach($event->changeLines() as $line)
                                            <li>{{ $line }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </article>
                        </div>
                    @empty
                        <div class="cab-empty">
                            <i class="bi bi-clock-history fs-2 d-block mb-2"></i>
                            История пока пуста.
                        </div>
                    @endforelse
                </div>
            </section>
        @endif
    </div>

        @if($doctor && $profileContext['has_photo'])
            <div id="doctorPhotoLightbox"
                 class="cab-photo-lightbox"
                 hidden
                 aria-hidden="true">
                <div class="cab-photo-lightbox__dialog"
                     role="dialog"
                     aria-modal="true"
                     aria-label="Увеличенная фотография врача">
                    <button type="button"
                            class="cab-photo-lightbox__close"
                            data-photo-lightbox-close
                            aria-label="Закрыть">
                        <i class="bi bi-x-lg"></i>
                    </button>

                    <img src="{{ $profileContext['photo_url'] }}"
                         alt="Фотография врача">
                </div>
            </div>
        @endif
</main>
@endsection

@push('scripts')
<script>
(() => {
    const frame = document.getElementById('doctorProfilePhotoFrame');
    const menu = document.getElementById('doctorPhotoContextMenu');

    if (!frame || !menu) {
        return;
    }

    const uploadInput = document.getElementById('doctorProfilePhotoInput');
    const uploadForm = document.getElementById('doctorPhotoUploadForm');
    const deleteForm = document.getElementById('doctorPhotoDeleteForm');
    const lightbox = document.getElementById('doctorPhotoLightbox');

    const closeMenu = () => {
        menu.hidden = true;
    };

    const openMenu = (x, y) => {
        menu.hidden = false;

        const rect = menu.getBoundingClientRect();
        const gap = 10;

        const left = Math.max(
            gap,
            Math.min(
                x,
                window.innerWidth - rect.width - gap
            )
        );

        const top = Math.max(
            gap,
            Math.min(
                y,
                window.innerHeight - rect.height - gap
            )
        );

        menu.style.left = `${left}px`;
        menu.style.top = `${top}px`;
    };

    frame.addEventListener('contextmenu', event => {
        event.preventDefault();
        event.stopPropagation();

        openMenu(
            event.clientX,
            event.clientY
        );
    });

    menu.addEventListener('click', event => {
        const action = event.target
            .closest('[data-photo-action]')
            ?.dataset
            ?.photoAction;

        if (!action) {
            return;
        }

        closeMenu();

        if (action === 'upload') {
            uploadInput?.click();
            return;
        }

        if (action === 'zoom') {
            if (lightbox) {
                lightbox.hidden = false;
                lightbox.setAttribute(
                    'aria-hidden',
                    'false'
                );
                document.body.style.overflow = 'hidden';
            }
            return;
        }

        if (
            action === 'delete'
            && deleteForm
            && window.confirm(
                'Удалить фотографию врача?'
            )
        ) {
            deleteForm.submit();
        }
    });

    uploadInput?.addEventListener('change', () => {
        if (
            uploadInput.files?.length
            && uploadForm
        ) {
            uploadForm.submit();
        }
    });

    const closeLightbox = () => {
        if (!lightbox) {
            return;
        }

        lightbox.hidden = true;
        lightbox.setAttribute(
            'aria-hidden',
            'true'
        );
        document.body.style.overflow = '';
    };

    lightbox?.addEventListener('click', event => {
        if (
            event.target === lightbox
            || event.target.closest(
                '[data-photo-lightbox-close]'
            )
        ) {
            closeLightbox();
        }
    });

    document.addEventListener('click', event => {
        if (
            !menu.hidden
            && !menu.contains(event.target)
        ) {
            closeMenu();
        }
    });

    window.addEventListener(
        'blur',
        closeMenu
    );

    window.addEventListener(
        'resize',
        closeMenu
    );

    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') {
            return;
        }

        closeMenu();
        closeLightbox();
    });
})();
</script>
@endpush

