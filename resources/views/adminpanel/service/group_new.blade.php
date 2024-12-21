@extends('layouts.admin')
@section('title', "Создание группы")
@section('assets')
@endsection
@section('main')
    <form class="container-fluid" method="post" enctype="multipart/form-data" action="{{ route('admin.groups.store') }}">
        @csrf
        <h1>Создание группы</h1>
        {{ Breadcrumbs::render('admin.group.new') }}
        <div class="row">
            <div class="col-md-6">
                <div class="pt-4">
                    <label for="name" class="form-label">Название</label>
                    <input class="form-control" name="name" id="name" value="{{ old('name') }}">
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3">
                    <button class="btn btn-outline-success btn-sm" type="submit"><i
                            class="bi bi-box-arrow-down"></i> Сохранить изменения
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="pt-4">
                    <label for="slug" class="form-label">Техническое название</label>
                    <input class="form-control" name="slug" id="slug" value="{{ old('slug') }}">
                </div>
            </div>
        </div>
        <div class="row pt-5">
            <div class="col-md-6">
                <h2 class="group-edit__header">Админ-панель</h2>
                <div class="pt-4">
                    <label for="adminpanel_see" class="form-label">Доступ к админ-панели</label>
                    <select name="adminpanel_see" id="adminpanel_see" class="form-control">
                        <option value="0">Запрещено</option>
                        <option value="1">Разрешено</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <h2 class="group-edit__header">Пользователи</h2>
                <div class="pt-4">
                    <label for="user_edit" class="form-label">Редактирование пользователей</label>
                    <select name="user_edit" id="user_edit" class="form-control">
                        <option value="0">Запрещено</option>
                        <option value="1">Разрешено</option>
                    </select>
                </div>
                <div class="pt-4">
                    <label for="banning_user" class="form-label">Блокировка пользователей</label>
                    <select name="banning_user" id="user_banning" class="form-control">
                        <option value="0">Запрещено</option
                        ><option value="1">Разрешено</option>
                    </select>
                </div>
                <div class="pt-4">
                    <label for="user_change_role" class="form-label">Смена ролей пользователей</label>
                    <select name="user_change_role" id="user_change_role" class="form-control">
                        <option value="0">Запрещено</option><option value="1">Разрешено</option>
                    </select>
                </div>
                <div class="pt-4">
                    <label for="user_remove" class="form-label">Удаление пользователей</label>
                    <select name="user_remove" id="user_remove" class="form-control">
                        <option value="0">Запрещено</option><option value="1">Разрешено</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <h2 class="group-edit__header">Группы пользователей</h2>
                <div class="pt-4">
                    <label for="group_add" class="form-label">Добавление групп</label>
                    <select name="group_add" id="group_add" class="form-control">
                        <option value="0">Запрещено</option><option value="1">Разрешено</option>
                    </select>
                </div>
                <div class="pt-4">
                    <label for="group_remove" class="form-label">Удаление групп</label>
                    <select name="group_remove" id="group_remove" class="form-control">
                        <option value="0">Запрещено</option><option value="1">Разрешено</option>
                    </select>
                </div>
                <div class="pt-4">
                    <label for="group_edit" class="form-label">Редактирование групп</label>
                    <select name="group_edit" id="group_edit" class="form-control">
                        <option value="0">Запрещено</option><option value="1">Разрешено</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <h2 class="group-edit__header">Настройки портала</h2>
                <div class="pt-4">
                    <label for="settings_main" class="form-label">Основные настройки</label>
                    <select name="settings_main" id="settings_main" class="form-control">
                        <option value="0">Запрещено</option><option value="1">Разрешено</option>
                    </select>
                </div>
                <div class="pt-4">
                    <label for="settings_mail" class="form-label">Настройки почты</label>
                    <select name="settings_mail" id="settings_mail" class="form-control">
                        <option value="0">Запрещено</option><option value="1">Разрешено</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <h2 class="group-edit__header">Клиники</h2>
                <div class="pt-4">
                    <label for="clinic_add" class="form-label">Добавление клиники</label>
                    <select name="clinic_add" id="clinic_add" class="form-control">
                        <option value="0">Запрещено</option><option value="1">Разрешено</option>
                    </select>
                </div>
                <div class="pt-4">
                    <label for="clinic_edit" class="form-label">Редактирование клиники</label>
                    <select name="clinic_edit" id="clinic_edit" class="form-control">
                        <option value="0">Запрещено</option><option value="1">Разрешено</option>
                    </select>
                </div>
                <div class="pt-4">
                    <label for="clinic_remove" class="form-label">Удаление клиники</label>
                    <select name="clinic_remove" id="clinic_remove" class="form-control">
                        <option value="0">Запрещено</option><option value="1">Разрешено</option>
                    </select>
                </div>
            </div>
        </div>
    </form>
@endsection
