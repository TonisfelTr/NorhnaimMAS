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

    Breadcrumbs::for('admin.users.new', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Пользователи', route('admin.users'));
        $trail->push('Создание пользователя', route('admin.users.create'));
    });

    Breadcrumbs::for('admin.users.edit', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Пользователи', route('admin.users'));
        $trail->push('Редактирование записи', route('admin.users.edit', Route::getCurrentRoute()->parameter('user_id')));
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

    Breadcrumbs::for('admin.users.doctors.edit', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Пользователи', route('admin.users'));
        $trail->push('Доктора', route('admin.users.doctors'));
        $trail->push('Редактирование');
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

    Breadcrumbs::for('admin.users.banned.new', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Пользователи', route('admin.users'));
        $trail->push('Заблокированные', route('admin.users.banned'));
        $trail->push('Блокировка пользователя');
    });

    Breadcrumbs::for('admin.groups', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Группы пользователей', route('admin.groups'));
    });

    Breadcrumbs::for('admin.group.edit', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Группы', route('admin.groups'));
        $trail->push('Редактирование группы');
    });

    Breadcrumbs::for('admin.group.new', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Группы', route('admin.groups'));
        $trail->push('Создание группы');
    });

    Breadcrumbs::for('admin.dictionary', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Регистратура', route('admin.dictionary.registration'));
    });

    Breadcrumbs::for('admin.dictionary.create', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Регистратура', route('admin.dictionary.registration'));
        $trail->push('Создание записи');
    });

    Breadcrumbs::for('admin.dictionary.edit', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Регистратура', route('admin.dictionary.registration'));
        $trail->push('Редактирование записи');
    });

    Breadcrumbs::for('admin.dictionary.clinics', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Клиники', route('admin.dictionary.clinics'));
    });

    Breadcrumbs::for('admin.dictionary.clinics.create', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Клиники', route('admin.dictionary.clinics'));
        $trail->push('Создание клиники', route('admin.dictionary.clinics.create'));
    });

    Breadcrumbs::for('admin.dictionary.clinics.edit', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Клиники', route('admin.dictionary.clinics'));
        $trail->push('Редактирование клиники');
    });

    Breadcrumbs::for('admin.dictionary.drugs', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Лекарства', route('admin.dictionary.drugs'));
    });

    Breadcrumbs::for('admin.dictionary.drugs.edit', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Лекарства', route('admin.dictionary.drugs'));
        $trail->push('Редактирование');
    });

    Breadcrumbs::for('admin.dictionary.drugs.new', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Лекарства', route('admin.dictionary.drugs'));
        $trail->push('Добавление нового');
    });

    Breadcrumbs::for('admin.dictionary.diagnoses', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Диагнозы', route('admin.dictionary.diagnoses'));
    });

    Breadcrumbs::for('admin.dictionary.diagnoses_edit', function (BreadcrumbTrail $trail) {
       $trail->push('Главная', route('admin.main'));
       $trail->push('Диагнозы', route('admin.dictionary.diagnoses'));
       $trail->push('Редактирование', route('admin.dictionary.diagnoses.edit', 1));
    });

    Breadcrumbs::for('admin.blog.categories', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Категории блога', route('admin.blog.categories'));
    });

    Breadcrumbs::for('admin.blog.categories.new', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Категории блога', route('admin.blog.categories'));
        $trail->push('Создание категории', route('admin.blog.categories.new'));
    });

    Breadcrumbs::for('admin.blog.categories.edit', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Категории блога', route('admin.blog.categories'));
        $trail->push('Редактирование категории');
    });

    Breadcrumbs::for('admin.blog.topics', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Статьи');
    });

    Breadcrumbs::for('admin.jurisprudence.lawyers', function (BreadcrumbTrail $trail) {
        $trail->push('Главная', route('admin.main'));
        $trail->push('Юристы');
    });

    Breadcrumbs::for('admin.jurisprudence.lawyers.new', function (BreadcrumbTrail $trail) {
       $trail->push('Главная', route('admin.main'));
       $trail->push('Юристы', route('admin.jurisprudence.lawyers'));
       $trail->push('Создание записи юриста');
    });
}
