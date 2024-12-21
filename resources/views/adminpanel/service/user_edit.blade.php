@extends('layouts.admin')
@section('title', 'Редактирование пользователя ' . $user->email)
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
        <h1>Пользователь {{ $user->login }}</h1>
        {{ Breadcrumbs::render('admin.users.edit') }}
        <div class="container-fluid">
            <div class="row">
                <form class="col-md-6 mt-5" action="{{ route('admin.users.save', $user->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @recaptcha
                    @if(session()->has('success'))
                        <div class="alert alert-success">
                            {{ session()->get('success') }}
                        </div>
                    @elseif($errors->isNotEmpty())
                        <div class="alert alert-danger">
                            При изменении профиля произошли следующие ошибки:
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
                        <input id="email-field" class="form-control" type="email" name="email" placeholder="{{ fake()->email() }}" value="{{ $user->email }}" required/>
                    </div>
                    <div class="mb-3">
                        <label for="login-field" class="form-label">Логин</label>
                        <input id="login-field" class="form-control" type="text" name="login" placeholder="{{ fake()->word() }}" value="{{ $user->login }}" required/>
                    </div>
                    <label for="email_verified_at-field" class="form-label">Активирован</label>
                    <div class="mb-3 input-group">
                        <input id="email_verified_at-field" class="form-control" type="datetime-local" name="email_verified_at" placeholder="{{ fake()->date() }}" value="{{ $user->email_verified_at }}"/>
                        @if($user->id != 1)
                            <button class="btn btn-danger" id="dtl-unset-btn" type="button" data-picker="#email_verified_at-field">Деактивировать</button>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="group_id-field" class="form-label">Группа</label>
                        <select class="form-control" name="group_id">
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" @if($group->id == $user->group_id) selected @endif >{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="profession-field" class="form-label">Тип прилегающей сущности</label>
                        <select id="profession-field" class="form-control" name="userable_type">
                            <option value="\App\Models\Doctor" @if($user->userable_type == '\App\Models\Doctor') selected @endif >Доктор</option>
                            <option value="\App\Models\Patient" @if($user->userable_type == '\App\Models\Patient') selected @endif>Пациент</option>
                            <option value="administrators" @if($user->userable_type == 'administrators') selected @endif>Администратор</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="person-field" class="form-label">Соответствующая запись</label>
                        <select id="person-field" class="form-control" name="userable_id">
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->surname }}, {{ $doctor->name }} {{ $doctor->patronym }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="user-balance" class="form-label">Баланс пользователя</label>
                        <input class="form-control" type="number" step="0.05" id="user-balance" name="balance" value="{{ old('balance', $user->balance) }}">
                    </div>
                    <div class="mb-3">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td class="text-center" colspan="3">Транзакции с балансом</td>
                                </tr>
                                <tr>
                                    <td class="text-center">Причина</td>
                                    <td class="text-center">Старый баланс</td>
                                    <td class="text-center">Новый баланс</td>
                                </tr>
                            </thead>
                            <tbody>
                                @if($transactions->isEmpty())
                                    <tr>
                                        <td colspan="3" class="text-center"><span class="bi bi-patch-question"></span> История транзакций пуста</td>
                                    </tr>
                                @else
                                    @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->reason }}</td>
                                        <td>{{ $transaction->old_balance }}</td>
                                        <td>{{ $transaction->new_balance }}</td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        @if($user->id != 1)
                            <button class="btn btn-danger me-md-2" id="user-delete-btn" type="button" data-bs-toggle="modal" data-bs-target="#user-delete-modal"><i class="bi bi-trash3"></i> Удалить пользователя</button>
                        @endif
                        <button class="btn btn-outline-success btn-sm" type="submit"><i class="bi bi-box-arrow-down"></i> Сохранить изменения</button>
                    </div>
                </form>
                <form class="col-md-6 mt-5" action="{{ route('admin.users.edit.password', $user->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <h3>Смена пароля</h3>
                    <div class="mb-3">
                        <label for="password-field" class="form-label">Новый пароль</label>
                        <input id="password-field" class="form-control" type="password" name="password" placeholder="{{ $password = fake()->password(12) }}" autocomplete="new-password" required/>
                    </div>
                    <div class="mb-3">
                        <label for="password-confirm-field" class="form-label">Повторите пароль</label>
                        <input id="password-confirm-field" class="form-control" type="password" name="password_confirmation" placeholder="{{ $password }}" autocomplete="new-password" required/>
                    </div>
                    <p><strong>Сгенерированный пароль:</strong> {{ $password }}</p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button class="btn btn-outline-success btn-sm" type="submit"><i class="bi bi-arrow-clockwise"></i> Обновить пароль</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-danger-dialog-component title="Удаление" message="Вы действительно хотите удалить этого пользователя?" button="#user-delete-btn" message-box="user-delete-modal"/>
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

            $('#person-field').selectize({
                persist: false,
                maxItems: null,
                valueField: 'id',
                labelField: 'name',
                placeholder: 'Не выбрано',
                searchField: ['name', 'id', 'email'],
                name: 'userable_id',
                options: [
                    @foreach ($doctors as $doctor) { email: '{{ $doctor->email }}', name: '{{ $doctor->name }}', id: {{ $doctor->id }} }, @endforeach
                    @foreach ($patients as $patient) { email: '{{ $patient->email }}', name: '{{ $patient->name }}', id: {{ $patient->id }} } @endforeach
                ]
            })

            document.querySelector('#profession-field').addEventListener('change', async function () {
                var professionId = this.value;

                switch (professionId) {
                    case "\\App\\Models\\Doctor": {
                        const promise = await fetch('{{ route('api-get-doctors') }}')
                            .then((response) => response.json())
                            .then(items => {
                                var selectize = $('#person-field')[0].selectize;
                                selectize.clearOptions();
                                selectize.addOption(items.items);
                                selectize.refreshOptions();
                            });
                        break;
                    }
                    case "\\App\\Models\\Patient": {
                        const promise = await fetch('{{ route('api-get-patients') }}')
                            .then((response) => response.json())
                            .then(items => {
                                var selectize = $('#person-field')[0].selectize;
                                selectize.clearOptions();
                                selectize.addOption(items.items);
                                selectize.refreshOptions();
                            })
                        break;
                    }
                    default: {
                        var selectize = $('#person-field')[0].selectize;
                        selectize.clearOptions();
                        break;
                    }
                }
            });
        })
    </script>
@endsection
