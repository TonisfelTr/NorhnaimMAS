@extends('layouts.admin')
@section('title', "Редактирование препарата \"{$drug->name}\"")
@section('assets')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.bootstrap5.min.css">
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
    <script src="https://cdn.tiny.cloud/1/ad3kdu206n3z7da89roon6z9pg807w0gpxzakmx2c37gjwsg/tinymce/7/tinymce.min.js"
            referrerpolicy="origin"></script>
@endsection
@section('main')
    <div class="container-fluid">
        <h1>Лекарство "{{ $drug->name }}"</h1>
        {{ Breadcrumbs::render('admin.dictionary.drugs.edit') }}
        <div class="container-fluid">
            <div class="row">
                <form class="mt-5 row" action="{{ route('admin.dictionary.drugs.save', $drug->id) }}" method="post"
                      enctype="multipart/form-data">
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
                    <div class="col-md-6">
                        <h3>Основные данные</h3>
                        <div class="mb-3">
                            <label for="name-field" class="form-label">Название препарата</label>
                            <input id="name-field" class="form-control" type="text" name="name"
                                   placeholder="Хлорпромазин"
                                   value="{{ $drug->name }}" required/>
                        </div>
                        <div class="mb-3">
                            <label for="latin_name-field" class="form-label">Латинское название</label>
                            <input id="latin_name-field" class="form-control" type="text" name="latin_name"
                                   placeholder="Chlorpromazinum" value="{{ $drug->latin_name }}" required/>
                        </div>
                        <div class="mb-3">
                            <input type="checkbox" id="preferential" name="preferential" @if($drug->preferential) checked @endif >
                            <label for="preferential" class="form-label">Выдаётся по льготе</label>
                        </div>
                        <div class="mb-3">
                            <input type="checkbox" id="strict" name="strict" @if($drug->strict) checked @endif >
                            <label for="strict" class="form-label">Отпуск по рецепту формы 148-1/у-88</label>
                        </div>
                        <div class="mb-3">
                            <label for="generics" class="form-label">Дженерики</label>
                            <select class="form-control" id="generics" name="generics[]" multiple>
                                @foreach($generics as $generic)
                                    <option value="{{ $generic }}" selected>{{ $generic }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="group_id-field" class="form-label">Группа</label>
                            <select class="form-control" name="group">
                                @foreach($groups as $group)
                                    <option value="{{ $group->group }}"
                                            @if($group->group == $drug->group) selected @endif >{{ \App\Enums\MedicineTypesEnum::getMatched($group->group) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button class="btn btn-outline-success btn-sm" type="submit"><i
                                    class="bi bi-box-arrow-down"></i> Сохранить изменения
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h3>Фармакологические параметры</h3>
                        <div class="mb-3">
                            <label for="ht-block" class="form-label">Период полувыведения</label>
                            <div class="input-group" id="ht-block">
                                <input class="form-control" type="number" name="ht_output_from" placeholder="От"
                                       value="{{ $drug->ht_output_from }}">
                                <input class="form-control" type="number" name="ht_output_to" placeholder="До"
                                       value="{{ $drug->ht_output_to }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="indications" class="form-label">Показания</label>
                            <select class="form-control" id="indications" name="indications[]" multiple>
                                @foreach ($indications as $indication)
                                    <option value="{{ $indication->code }}"
                                        @if (in_array($indication->code, $selectedIndications)) selected @endif>
                                        {{ $indication->title }}
                                    </option>
                                @endforeach
                            </select>

                        </div>
                        <div class="mb-3">
                            <label for="contraindications" class="form-label">Противопоказания</label>
                            <select class="form-control" id="contraindications" name="contraindications[]" multiple="">
                                @foreach($contraindications as $contraindication)
                                    <option value="{{ $contraindication->code }}"
                                            @if(in_array($contraindication->id, $selectedContraindications)) selected @endif>
                                        {{ $contraindication->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="dangerous" class="form-label">Относительные противопоказания (с
                                                                      осторожностью)</label>
                            <select class="form-control" id="dangerous" name="dangerous[]" multiple>
                                @foreach($dangerous as $danger)
                                    <option value="{{ $danger->code }}"
                                            @if(in_array($danger->id, $selectedDangerous)) selected @endif>
                                        {{ $danger->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="side_effects" class="form-label">Побочные эффекты</label>
                            <select class="form-control" id="side_effects" name="side_effects[]" multiple="">
                                @foreach($side_effects as $sideEffect)
                                    <option value="{{ $sideEffect->id }}"
                                            @if(in_array($sideEffect->id, $selectedSideEffects)) selected @endif>
                                        {{ $sideEffect->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3" id="forms-line">
                            <label for="drug-form" class="form-label">Формы препарата</label>
                            <drug-form id="drug-form" :initial-forms='@json($drug->forms)'></drug-form>
                        </div>
                    </div>
                    <div class="col-md-12 mt-5">
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control" id="description"
                                      name="description">{{ $drug->description }}</textarea>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-danger-dialog-component title="Удаление" message="Вы действительно хотите удалить этого пользователя?"
                               button="#user-delete-btn" message-box="user-delete-modal"/>
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

            $('#generics').selectize({
                plugins: ["clear_button", "remove_button"],
                create: true,        // Разрешает ввод новых значений
                persist: false,      // Убирает созданные элементы из поля, если они удалены
                maxItems: null,      // Позволяет множественный выбор, можно установить число для ограничения
                placeholder: 'Введите или выберите значение',
            });
            $('#contraindications').selectize({
                plugins: ["clear_button", "remove_button"],
                delimiter: ",",
                persist: false,
                maxItems: null,
                valueField: 'code',
                labelField: 'name',
                searchField: ['code', 'name'],
                options: [
                        @foreach($contraindications as $ci)
                    {
                        code: '{{ $ci->code }}', name: '{{ $ci->name }}'
                    },
                    @endforeach
                ]
            });
            $('#dangerous').selectize({
                plugins: ["clear_button", "remove_button"],
                delimiter: ",",
                persist: false,
                maxItems: null,
                valueField: 'code',
                labelField: 'name',
                searchField: ['code', 'name'],
                options: [
                        @foreach($dangerous as $di)
                    {
                        code: '{{ $di->code }}', name: '{{ $di->name }}'
                    },
                    @endforeach
                ]
            });
            $('#side_effects').selectize({
                plugins: ["clear_button", "remove_button"],
                delimiter: ",",
                persist: false,
                maxItems: null,
                valueField: 'code',
                labelField: 'name',
                searchField: ['code', 'name'],
                options: [
                        @foreach($drug->side_effects as $se)
                    {
                        code: '{{ $se->id }}', name: '{{ $se->name }}'
                    },
                    @endforeach
                ]
            });
            $('#indications').selectize({
                plugins: ["clear_button", "remove_button"],
                delimiter: ",",
                persist: false,
                maxItems: null,
                valueField: 'code',
                labelField: 'title',
                searchField: ['code', 'title'],
                options: [
                        @foreach($indications as $indication)
                    {
                        code: '{{ $indication->code }}', title: '{{ $indication->title }}'
                    },
                    @endforeach
                ]
            });

            tinymce.init({
                selector: 'textarea',
                plugins: [
                    // Core editing features
                    'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'image', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
                    // Your account includes a free trial of TinyMCE premium features
                    // Try the most popular premium features until Nov 23, 2024:
                    'checklist', 'mediaembed', 'casechange', 'export', 'formatpainter', 'pageembed', 'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable', 'advcode', 'editimage', 'advtemplate', 'ai', 'mentions', 'tinycomments', 'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography', 'inlinecss', 'markdown',
                    // Early access to document converters
                    'importword', 'exportword', 'exportpdf'
                ],
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
                tinycomments_mode: 'embedded',
                tinycomments_author: 'Author name',
                mergetags_list: [
                    {value: 'First.Name', title: 'First Name'},
                    {value: 'Email', title: 'Email'},
                ],
                ai_request: (request, respondWith) => respondWith.string(() => Promise.reject('See docs to implement AI Assistant')),
                exportpdf_converter_options: {
                    'format': 'Letter',
                    'margin_top': '1in',
                    'margin_right': '1in',
                    'margin_bottom': '1in',
                    'margin_left': '1in'
                },
                exportword_converter_options: {'document': {'size': 'Letter'}},
                importword_converter_options: {
                    'formatting': {
                        'styles': 'inline',
                        'resets': 'inline',
                        'defaults': 'inline',
                    }
                },
            });
        });
    </script>
@endsection
