<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Illuminate\Support\Facades\Route;

// For some reason it does not work, if this condition is not present.
// It has said that the admin.main breadcrumbs is already registered.
if (!Breadcrumbs::exists('admin.main')) {
    Breadcrumbs::for('admin.main', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
    });

    Breadcrumbs::for('admin.settings', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Настройки', route('admin.settings'));
    });

    Breadcrumbs::for('admin.users', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Пользователи', route('admin.users'));
    });

    Breadcrumbs::for('admin.user_edit', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Пользователи', route('admin.users'));
        $trail->push('Редактирование записи', route('admin.user_edit', Route::getCurrentRoute()->parameter('user_id')));
    });

    Breadcrumbs::for('admin.users.patients', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Пользователи', route('admin.users'));
        $trail->push('Пациенты', route('admin.users.patients'));
    });

    Breadcrumbs::for('admin.users.doctors', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Пользователи', route('admin.users'));
        $trail->push('Доктора', route('admin.users.doctors'));
    });

    Breadcrumbs::for('admin.users.patient_new', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Пользователи', route('admin.users'));
        $trail->push('Пациенты', route('admin.users.patients'));
        $trail->push('Создание записи пациента', route('admin.users.patients.new'));
    });

    Breadcrumbs::for('admin.users.banned', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Пользователи', route('admin.users'));
        $trail->push('Заблокированные', route('admin.users.banned'));
    });

    Breadcrumbs::for('admin.groups', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Группы пользователей', route('admin.groups'));
    });

    Breadcrumbs::for('admin.dictionary', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Регистратура и словари', route('admin.dictionary'));
    });

    Breadcrumbs::for('admin.dictionary.clinics', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Регистратура и словари', route('admin.dictionary'));
        $trail->push('Клиники', route('admin.dictionary.clinics'));
    });

    Breadcrumbs::for('admin.dictionary.drugs', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Регистратура и словари', route('admin.dictionary'));
        $trail->push('Лекарства', route('admin.dictionary.drugs'));
    });

    Breadcrumbs::for('admin.dictionary.drugs.edit', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Регистратура и словари', route('admin.dictionary'));
        $trail->push('Лекарства', route('admin.dictionary.drugs'));
        $trail->push('Редактирование');
    });

    Breadcrumbs::for('admin.dictionary.drugs.new', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Регистратура и словари', route('admin.dictionary'));
        $trail->push('Лекарства', route('admin.dictionary.drugs'));
        $trail->push('Добавление нового');
    });

    Breadcrumbs::for('admin.dictionary.diagnoses', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Регистратура и словари', route('admin.dictionary'));
        $trail->push('Диагнозы', route('admin.dictionary.diagnoses'));
    });

    Breadcrumbs::for('admin.dictionary.diagnoses_edit', function (BreadcrumbTrail $trail) {
       $trail->push('Главная', route('admin.main'));
       $trail->push('Регистратура и словари', route('admin.dictionary'));
       $trail->push('Диагнозы', route('admin.dictionary.diagnoses'));
       $trail->push('Редактирование', route('admin.dictionary.diagnoses.edit', 1));
    });
}
