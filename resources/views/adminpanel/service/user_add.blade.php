@extends('layouts.admin')
@section('title', 'Создание пользователя')
@section('assets')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.bootstrap5.min.css">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.default.min.css"
        integrity="sha512-pTaEn+6gF1IeWv3W1+7X7eM60TFu/agjgoHmYhAfLEU8Phuf6JKiiE8YmsNC0aCgQv4192s4Vai8YZ6VNM6vyQ=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
        <h1>Создание пользователя</h1>
        {{ Breadcrumbs::render('admin.users.new') }}
        <div class="container-fluid">
            <div class="row">
                <form class="col-md-6 mt-5" action="{{ route('admin.users.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @recaptcha
                    @if(session()->has('success'))
                        <div class="alert alert-success">
                            {{ session()->get('success') }}
                        </div>
                    @elseif($errors->isNotEmpty())
                        <div class="alert alert-danger">
                            При создании пользователя произошли следующие ошибки:
                            <ol>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                    <h3>Основные данные</h3>
                    <div class="mb-3">
                        <label for="email-field" class="form-label">Адрес электронной почты</label>
                        <input id="email-field" class="form-control" type="email" name="email" placeholder="{{ fake()->email() }}" value="{{ old('email') }}" required/>
                    </div>
                    <div class="mb-3">
                        <label for="login-field" class="form-label">Логин</label>
                        <input id="login-field" class="form-control" type="text" name="login" placeholder="{{ fake()->word() }}" value="{{ old('login') }}" required/>
                    </div>
                    <label for="email_verified_at-field" class="form-label">Активирован</label>
                    <div class="mb-3 input-group">
                        <input id="email_verified_at-field" class="form-control" type="datetime-local" name="email_verified_at" placeholder="{{ fake()->date() }}" value="{{ old('email_verified_at') }}"/>
                    </div>
                    <div class="mb-3">
                        <label for="group_id-field" class="form-label">Группа</label>
                        <select class="form-control" name="group_id">
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" @if($group->id == old('group_id')) selected @endif >{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="user-balance" class="form-label">Баланс пользователя</label>
                        <input class="form-control" type="number" step="0.05" id="user-balance" name="balance" value="{{ old('balance', 0) }}">
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button class="btn btn-outline-success btn-sm" type="submit"><i class="bi bi-box-arrow-down"></i> Сохранить изменения</button>
                    </div>
                </form>
                <form class="col-md-6 mt-5" action="#" method="post" enctype="multipart/form-data">
                    <h3>Смена пароля</h3>
                    <div class="mb-3">
                        <label for="password-field" class="form-label">Новый пароль</label>
                        <input id="password-field" class="form-control" type="password" name="password" placeholder="{{ $password = fake()->password(12) }}" autocomplete="new-password" required/>
                    </div>
                    <div class="mb-3">
                        <label for="password-confirm-field" class="form-label">Повторите пароль</label>
                        <input id="password-confirm-field" class="form-control" type="password" name="password-confirm" placeholder="{{ $password }}" autocomplete="new-password" required/>
                    </div>
                    <p><strong>Сгенерированный пароль:</strong> {{ $password }}</p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button class="btn btn-outline-success btn-sm" type="submit"><i class="bi bi-arrow-clockwise"></i> Обновить пароль</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        grecaptcha.ready(function () {
            function onFormSubmit(event) {
                let form = this;

                event.preventDefault();
                grecaptcha.execute('{{ env('RECAPTCHAV3_SITEKEY') }}', {action: 'submit'}).then(function (token) {
                    form.querySelectorAll('[name="g-recaptcha-response"]').forEach(captchaInput => captchaInput.value = token);
                    form.submit();
                });
            }

            document.querySelectorAll('form').forEach(e => e.addEventListener('submit', onFormSubmit));
        })
    </script>
@endsection
