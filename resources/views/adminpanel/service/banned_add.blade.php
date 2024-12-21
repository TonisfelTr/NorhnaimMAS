@extends('layouts.admin')
@section('title', 'Блокировка пользователя')
@section('assets')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.bootstrap5.min.css">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.default.min.css"
        integrity="sha512-pTaEn+6gF1IeWv3W1+7X7eM60TFu/agjgoHmYhAfLEU8Phuf6JKiiE8YmsNC0aCgQv4192s4Vai8YZ6VNM6vyQ=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    />
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/js/selectize.min.js"
        integrity="sha512-IOebNkvA/HZjMM7MxL0NYeLYEalloZ8ckak+NDtOViP7oiYzG5vn6WVXyrJDiJPhl4yRdmNAG49iuLmhkUdVsQ=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    ></script>
@endsection
@section('main')
    <div class="container-fluid">
        <h1>Блокировка пользователя</h1>
        {{ Breadcrumbs::render('admin.users.banned.new') }}
        <form class="row" action="{{ route('admin.users.banned.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="col-md-6">
                <div class="pt-3">
                    <label for="login" class="form-label">Логин</label>
                    <select id="login" name="login" class="form-control">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @if($user->id == old('login')) selected @endif>[{{ $user->id }}] {{ $user->login }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pt-3">
                    <label for="to" class="form-label">По</label>
                    <input id="to" name="to" type="date" class="form-control" value="{{ old('to') }}">
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3">
                    <button class="btn btn-outline-success btn-sm" type="submit"><i
                            class="bi bi-box-arrow-down"></i> Сохранить изменения
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="pt-3">
                    <label for="rule_id" class="form-label">Нарушенное правило</label>
                    <select id="rule_id" name="rule_id" class="form-control">
                        @foreach($rules as $rule)
                            <option value="{{ $rule->id }}" data-point="{{ $rule->point }}" data-text="{{ $rule->text }}">
                                {{ $rule->point }} {{ $rule->text }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toField = document.getElementById('to');

            // Устанавливаем минимальную дату для поля "По" (сегодняшний день + 1 день)
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(today.getDate() + 1);

            const formattedTomorrow = tomorrow.toISOString().split('T')[0];
            toField.setAttribute('min', formattedTomorrow);

            // Слушаем изменения в поле "По"
            toField.addEventListener('input', function () {
                const selectedDate = new Date(toField.value);
                if (selectedDate < tomorrow) {
                    alert('Дата в поле "По" должна быть больше сегодняшнего дня.');
                    toField.value = '';
                }
            });

            $('#rule_id').selectize({
                plugins: ["clear_button"], // Убираем "remove_button", так как выбор только одного элемента
                create: false,            // Нельзя создавать новые значения
                persist: false,           // Убираем созданные элементы, если они удалены
                maxItems: 1,              // Разрешается выбрать только один элемент
                placeholder: 'Введите или выберите значение',
                valueField: 'value',      // Поле для значения (ID правила)
                labelField: 'text',       // Поле для отображения текста
                searchField: ['point', 'text', 'value'], // Поля для поиска
                options: $('#rule_id option').map(function () { // Генерация данных для Selectize
                    return {
                        value: $(this).val(),
                        text: $(this).data('text') || $(this).text(), // Поддержка значения по умолчанию
                        point: $(this).data('point') || ''            // Поддержка значения по умолчанию
                    };
                }).get(),
                render: {
                    option: function(data, escape) {
                        return `<div class="px-2">
                <strong>${escape(data.point)}</strong> - ${escape(data.text)}
            </div>`;
                    },
                    item: function(data, escape) {
                        return `<div>${escape(data.point)} - ${escape(data.text)}</div>`;
                    }
                },
                load: function(query, callback) {
                    if (!query.length) return callback();
                    const options = $('#rule_id option').map(function () {
                        const id = $(this).val();
                        const point = $(this).data('point') || '';
                        const text = $(this).data('text') || $(this).text();

                        if (
                            id.includes(query) ||
                            point.includes(query) ||
                            text.toLowerCase().includes(query.toLowerCase())
                        ) {
                            return { value: id, text, point };
                        }
                    }).get();
                    callback(options);
                }
            });
        });
    </script>
@endsection
