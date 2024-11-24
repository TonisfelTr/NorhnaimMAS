@extends('layouts.admin')
@section('title', "Добавление нового препарата")
@section('assets')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/tagify.min.js') }}"></script>
@endsection
@section('main')
    <div class="container-fluid">
        <h1>Добавление нового препарата</h1>
        {{ Breadcrumbs::render('admin.dictionary.drugs.new') }}
        <div class="container-fluid">
            <div class="row">
                <form class="row" action="{{ route('admin.dictionary.drugs.new') }}" method="post" enctype="multipart/form-data">
                    <div class="col-md-6 mt-5 bg-light bg-gradient">
                        @csrf
                        @recaptcha
                        @if(session()->has('success'))
                            <div class="alert alert-success">
                                {{ session()->get('success') }}
                            </div>
                        @elseif($errors->isNotEmpty())
                            <div class="alert alert-danger">
                                При изменении записи препарата произошли следующие ошибки:
                                <ol>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        @endif
                        <h3>Основные данные</h3>
                        <div class="mb-3">
                            <label for="name-field" class="form-label">Название препарата</label>
                            <input id="name-field" class="form-control" type="text" name="name" placeholder="Хлорпромазин" value="{{ old('name') }}" required/>
                        </div>
                        <div class="mb-3">
                            <input type="checkbox" name="preferential" id="preferential" @if(old('preferential')) checked @endif>
                            <label for="preferential" class="form-check-label">Может быть выдан по инвалидности</label>
                        </div>
                        <div class="mb-3">
                            <input type="checkbox" name="strict" id="strict" @if(old('strict')) checked @endif>
                            <label for="strict" class="form-check-label">Строгий отпуск (по 148-у рецепту)</label>
                        </div>
                        <div class="mb-3">
                            <label for="latin_name-field" class="form-label">Латинское название</label>
                            <input id="latin_name-field" class="form-control" type="text" name="latin_name" placeholder="Chlorpromazinum" value="{{ old('latin_name') }}" required/>
                        </div>
                        <div class="mb-3">
                            <label for="group_id-field" class="form-label">Группа</label>
                            <select class="form-control" name="group" required>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" @if($group->id == old('group')) selected @endif >{{ $group->group }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="generics" class="form-label">Дженерики</label>
                            <input id="generics" name="generics" placeholder="Аминазин" class="form-control" value="{{ old('generics') }}">
                            <p class="hint">Перечислите дженерики через запятую</p>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button class="btn btn-outline-success btn-sm" type="submit"><i class="bi bi-box-arrow-down"></i> Сохранить изменения</button>
                        </div>
                    </div>
                    <div class="col-md-6 mt-5 bg-light bg-gradient">
                        <h3>Фармакологические параметры</h3>
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control" id="description" name="description" required>{{ old('description') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="hf-output-block" class="form-label">Период полувыведения</label>
                            <div id="hf-output-block" class="row row-cols-2">
                                <div class="col-md-6">
                                    <input class="form-control" name="ht_output_from" type="number" placeholder="От" required>
                                </div>
                                <div class="col-md-6">
                                    <input class="form-control" name="ht_output_to" type="number" placeholder="До" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="side_effects" class="form-label">Побочные эффекты</label>
                            <select class="form-control" id="side_effects" name="side_effects[]" required multiple>
                                @foreach($sideEffects as $sei)
                                    <option value="{{ $sei->id }}">{{ $sei->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="contraindications" class="form-label">Противопоказания</label>
                            <select class="form-control" id="contraindications" name="contraindications[]" required multiple>
                                @foreach($contraindications as $ci)
                                    <option value="{{ $ci->id }}">{{ $ci->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="dangerous" class="form-label">Относительные противопоказания (с осторожностью)</label>
                            <select class="form-control" id="dangerous" name="dangerous[]" required multiple>
                                @foreach($contraindications as $ci)
                                    <option value="{{ $ci->id }}">{{ $ci->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="receptors" class="form-label">Рецепторы</label>
                            <input placeholder="D2" class="form-control" name="receptors" id="receptors" required>
                        </div>
                        <div class="mb-3">
                            <label for="forms-line" class="form-label">Формы применения</label>
                            <div id="forms-line">
                                <drug-form></drug-form>
                            </div>
                        </div>
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

            $('#contraindications').select2({
                placeholder: "Выберите противопоказания",
                allowClear: true,
                width: '100%'
            });
            $('#dangerous').select2({
                placeholder: "Выберите относительные противопоказания",
                allowClear: true,
                width: '100%'
            });
            $('#side_effects').select2({
                placeholder: "Выберите побочные эффекты",
                allowClear: true,
                width: '100%'
            });

            var input = document.querySelector('input[name=generics]');
            new Tagify(input);
            var receptors = document.querySelector('input[name=receptors]');
            new Tagify(receptors, {
                whitelist: [
                    "D-1", "D-2", "D-3", "D-4", "D-5",
                    "5HT-1A", "5HT-1B", "5HT-1C", "5HT-1D", "5HT-1E", "5HT-1F", "5HT-2A", "5HT-2B", "5HT-2C",
                    "5HT-3", "5HT-4", "5HT-5A", "5HT-5B", "5HT-6", "5HT-7",
                    "a-1A", "a-1B", "a-1D", "a-2A", "a-2B", "a-2D", "b-1", "b-2", "b-3",
                    "H-1", "H-2", "H-3", "H-4",
                    "ACh-N", "ACh-M1", "ACh-M2", "ACh-M3", "ACh-M4", "ACh-M5",
                ],
                dropdown: {
                    enabled: 1,
                    maxItems: 5
                }
            })
        });
    </script>
@endsection
