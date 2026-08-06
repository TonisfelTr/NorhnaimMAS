@extends('doctors.prescriptions')
@section('title', 'Выписка рецептов')
@section('assets')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endsection
@section('sub-main')
    <div class="p-4">
        <div class="p-4">
            <h1>Выписка рецепта</h1>
            <div class="row">
                @if($errors->any())
                    <div class="alert alert-danger">
                        Возникли следующие ошибки:
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="col-md-8" method="post" action="{{ route('doctors.prescriptions.store') }}" enctype="multipart/form-data">
                    <input type="hidden" name="usage_instructions" id="usage_instructions__">
                    <input type="hidden" name="birth_at" id="birth_at__">
                    @csrf
                    <div class="mb-3">
                        <label for="patient_id" class="form-label">Пациент</label>
                        <select id="patient_id" name="patient_id" class="form-control w-100" required>
                            @foreach ($patients as $patient)
                                <option value="{{ $patient->latin_name }}">{{ $patient->surname }}, {{ substr($patient->name, 0, 1) }}. {{ substr($patient->patronym, 0, 1) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="drug_id" class="form-label">Лекарство</label>
                        <select id="drug_id" class="form-control w-100" name="drug_id">
                        </select>
                        <p id="strict_message" class="pt-2 d-none borderpb-2 mb-4 text-danger border-bottom border-danger">
                            <i class="bi bi-exclamation-triangle"></i> Данный препарат отпускается по рецепту 148-1/у-88
                        </p>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="drug_form" class="form-label">Форма</label>
                            <select id="drug_form" name="drug_form" class="form-control w-100" disabled>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="dosage" class="form-label">Дозировка</label>
                            <select id="dosage" name="dosage" class="form-control w-100" disabled>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="quantity" class="form-label">Кол-во лекарственной формы</label>
                            <select id="quantity" name="quantity" class="form-control w-100" disabled>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="standard" class="form-label">Кол-во стандартов</label>
                            <input class="form-control w-100" type="number" name="standard" id="standard" min="1" value="1" disabled>
                        </div>
                    </div>
                    <div class="my-3 d-flex flex-column">
                        <label for="usage_instructions" class="form-label">Схема приёма</label>
                        <div class="d-inline-flex justify-content-between" id="usage_instructions">
                            <span>По</span>
                            <input class="form-control" type="number" name="taking_drug" id="taking_drug" min="1" max="4" value="1" disabled>
                            <span id="drug_type"></span>
                            <select id="taking_count" name="taking_count" class="form-control" disabled>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                            <span id="taking_time"></span>
                            <span>в день на</span>
                            <span id="days_count"></span>
                            <span id="days_label"></span>
                            <select class="form-control" name="taking_time_meal" id="taking_time_meal" disabled>
                                <option value="1">после</option>
                                <option value="2">до</option>
                            </select>
                            <span>еды</span>
                        </div>
                    </div>
                    <div class="my-3" id="validity_block">
                        <label for="validity_period" class="form-label">Действителен до</label>
                        <select class="form-control" id="validity_period" name="validity_period">
                            <option value="60">60 дней</option>
                            <option value="365">1 год</option>
                        </select>
                    </div>
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-journal-check"></i> Выписать рецепт
                        </button>
                        <button id="printRecipeButton" type="button" class="btn btn-outline-success" disabled>
                            <i class="bi bi-printer"></i> Распечатать рецепт
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Асинхронная загрузка пациентов
            $('#patient_id').select2({
                placeholder: 'Выберите пациента',
                theme: 'bootstrap-5',
                ajax: {
                    url: '/api/doctors/search-patients',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: data.results.map(function (patient) {
                                return {
                                    id: patient.id,
                                    text: patient.text,
                                    birth_at: patient.birth_at
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            // Обработчик выбора пациента
            $('#patient_id').on('select2:select', function (e) {
                const selectedData = e.params.data;

                if (selectedData && selectedData.birth_at) {
                    // Заполняем скрытое поле датой рождения
                    document.getElementById('birth_at__').value = selectedData.birth_at;
                } else {
                    console.warn('Дата рождения не найдена для выбранного пациента.');
                    document.getElementById('birth_at__').value = ''; // Очищаем поле, если данных нет
                }
            });

            // Асинхронная загрузка препаратов
            $('#drug_id').select2({
                placeholder: 'Выберите препарат',
                theme: 'bootstrap-5',
                ajax: {
                    url: '/api/doctors/search-drugs',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return { results: data.results };
                    },
                    cache: true
                }
            });

            let drugData = []; // Данные о текущем лекарстве

            // Обновление зависимых селекторов при выборе препарата
            $('#drug_id').on('select2:select', function(e) {
                const drugLatinName = e.params.data.latin_name;

                if (!drugLatinName) {
                    alert('Не удалось определить название препарата.');
                    return;
                }

                // Выполняем AJAX-запрос для получения форм, доз и стандартов
                $.ajax({
                    url: `/api/doctors/search-drugs/${drugLatinName}/forms`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        drugData = data; // Сохраняем данные лекарства для дальнейшего использования

                        // Разблокировка полей
                        $('#drug_form, #dosage, #quantity, #standard, #taking_drug, #taking_time_meal').prop('disabled', false);

                        // Уникализация форм
                        const uniqueForms = [...new Set(data.map(item => item.form))];

                        // Очистка селектора форм
                        $('#drug_form').empty().append(new Option('', '', true, true));
                        uniqueForms.forEach(form => {
                            $('#drug_form').append(new Option(form, form));
                        });
                        $('#drug_form').trigger('change');

                        // Настройка поля "Кол-во стандартов"
                        const $standardField = $('#standard');
                        const $strictMessage = $('#strict_message');
                        const $validityBlock = $('#validity_block');
                        const strict = data.some(item => item.strict); // Проверяем наличие strict = true
                        if (strict) {
                            $standardField.attr('min', 1).attr('max', 3).prop('disabled', false);
                            $strictMessage.removeClass('d-none');
                            $validityBlock.addClass('d-none');
                        } else {
                            $standardField.attr('min', 1).prop('disabled', false);
                            $strictMessage.addClass('d-none');
                            $validityBlock.removeClass('d-none');
                        }

                        $('#standard').trigger('change');
                    },
                    error: function() {
                        alert('Ошибка при загрузке данных для выбранного препарата.');
                    }
                });
            });

            $('#quantity, #standard, #taking_time_meal, #taking_count').on('change', function () {
                const doseCount = parseInt($('#quantity').val(), 10) * parseInt($('#standard').val(), 10);
                const dosePerDay = parseInt($('#taking_drug').val(), 10) * parseInt($('#taking_count').val(), 10);

                let result = Math.floor(doseCount / dosePerDay);
                let daysCount = parseInt($('#quantity').val()) * parseInt($('#standard').val());

                $('#days_label').text(getDayWord(result));
                $('#days_count').text(result);

                let forHiddenText = `По ${$('#taking_drug').val()} ${$('#drug_type').text()} ${$('#taking_count').val()} ${$('#taking_time').text()} в день на ${$('#days_count').text()} ${$('#days_label').text()} ${$('#taking_time_meal').val() == 1 ? 'после' : 'до'} еды`;
                $('#usage_instructions__').val(forHiddenText);
            });

            // Обновление дозировок и количества при выборе формы
            $('#drug_form').on('change', function() {
                const selectedForm = $(this).val();
                const takingDrug = $('#taking_drug');
                const takingCount = $('#taking_count');

                if (!selectedForm) {
                    // Если форма не выбрана, очищаем и блокируем зависимые селекторы
                    $('#dosage, #quantity').empty().prop('disabled', true);
                    return;
                }

                // Фильтрация данных по выбранной форме
                const filteredData = drugData.filter(item => item.form === selectedForm);

                // Уникализация дозировок и количества
                const uniqueDoses = [...new Set(filteredData.map(item => item.dose))];
                const uniqueCounts = [...new Set(filteredData.map(item => item.count))];

                // Обновление селектора дозировок
                $('#dosage').empty();
                uniqueDoses.forEach(dose => {
                    $('#dosage').append(new Option(dose, dose));
                });
                $('#dosage').prop('disabled', false).trigger('change');

                // Обновление селектора количества
                $('#quantity').empty();
                uniqueCounts.forEach(count => {
                    $('#quantity').append(new Option(count, count));
                });
                $('#quantity').prop('disabled', false).trigger('change');

                takingDrug.change();
                takingCount.prop('disabled', false).change();

                const doseCount = parseInt($('#quantity').val(), 10) * parseInt($('#standard').val(), 10);
                const dosePerDay = parseInt($('#taking_drug').val(), 10) * parseInt($('#taking_count').val(), 10);

                $('#days_count').text((doseCount / dosePerDay).toString());

                let result = doseCount / dosePerDay;
                $('#days_label').text(getDayWord(result));

                $('#quantity, #standard, #taking_time_meal, #taking_count').change()
            });

            $('#taking_drug').on('change', function () {
                const value = $(this).val();
                const formWord = $('#drug_type');
                const formSelect = $('#drug_form').val();

                let word = '';

                switch (formSelect) {
                    case 'Таблетки':
                        switch (value) {
                            case '1':
                                word = 'таблетке';
                                break;
                            case '2':
                            case '3':
                            case '4':
                                word = 'таблетки';
                                break;
                        }
                        break;
                    case 'Драже':
                        word = 'драже';
                        break;
                    case 'Ампулы':
                        switch (value) {
                            case '1':
                                word = 'ампуле';
                                break;
                            case '2':
                            case '3':
                            case '4':
                                word = 'ампулы';
                                break;
                        }
                        break;
                    case 'Капсулы':
                        switch (value) {
                            case '1':
                                word = 'капсуле';
                                break;
                            case '2':
                            case '3':
                            case '4':
                                word = 'капсулы';
                                break;
                        }
                        break;
                    default:
                        console.warn('Не удалось определить тип лекарственной формы. Шаг: 5');
                        word = '?'
                        break;
                }

                formWord.text(word);
            })

            $('#taking_count').on('change', function () {
                const takingTime = $('#taking_time');
                const value = $(this).val();

                let content = '';

                switch (value) {
                    case '1':
                    case '5':
                        content = 'раз';
                        break;
                    case '2':
                    case '3':
                    case '4':
                        content = 'раза';
                        break;
                }

                takingTime.text(content);
            })

            function getDayWord(result) {
                // Преобразуем число в целое (на случай дробного результата)
                const count = Math.floor(result);

                // Определяем окончание
                if (count % 10 === 1 && count % 100 !== 11) {
                    return 'день';
                } else if ([2, 3, 4].includes(count % 10) && ![12, 13, 14].includes(count % 100)) {
                    return 'дня';
                } else {
                    return 'дней';
                }
            }

            // Обработчик нажатия на кнопку «Распечатать рецепт»
            const printRecipeButton = document.getElementById('printRecipeButton');

            printRecipeButton.addEventListener('click', function (e) {
                e.preventDefault(); // Отменяем стандартное поведение кнопки

                // Получаем данные формы
                const formData = new FormData(document.querySelector('form'));

                // Выполняем AJAX-запрос на маршрут для генерации рецепта
                fetch('{{ route('prescriptions.print') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                    .then(response => response.text()) // Ожидаем HTML-контент
                    .then(html => {
                        // Создаем скрытый iframe
                        const iframe = document.createElement('iframe');
                        iframe.style.display = 'none';
                        document.body.appendChild(iframe);

                        // Записываем HTML в iframe и вызываем печать
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        iframeDoc.open();
                        iframeDoc.write(html);
                        iframeDoc.close();

                        // Ждем загрузки содержимого, затем вызываем печать
                        iframe.onload = function () {
                            iframe.contentWindow.print();
                            // Удаляем iframe после печати
                            document.body.removeChild(iframe);
                        };
                    })
                    .catch(error => {
                        console.error('Ошибка при печати рецепта:', error);
                        alert('Произошла ошибка при попытке распечатать рецепт.');
                    });
            });

            const printButton = document.getElementById('printRecipeButton');
            const formFields = [
                'patient_id',
                'drug_id',
                'drug_form',
                'dosage',
                'quantity',
                'standard',
                'taking_drug',
                'taking_count',
                'taking_time_meal'
            ];

            // Функция для проверки заполненности всех полей
            function checkFormCompletion() {
                let isComplete = true;

                // Проверяем, что все поля заполнены
                formFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (!field || !field.value) {
                        isComplete = false;
                    }
                });

                // Активируем или деактивируем кнопку в зависимости от заполненности
                printButton.disabled = !isComplete;
            }

            // Добавляем обработчики событий для всех полей формы
            formFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('change', checkFormCompletion);
                    field.addEventListener('input', checkFormCompletion); // Для текстовых полей
                }
            });

            // Проверяем форму при загрузке страницы
            checkFormCompletion()

            $('#drug_form').on('change', function() {
                const selectedForm = $(this).val();
                const filteredData = drugData.filter(item => item.form === selectedForm);

                $('#dosage').empty().prop('disabled', false);
                [...new Set(filteredData.map(item => item.dose))].forEach(dose => $('#dosage').append(new Option(dose, dose)));

                if (selectedForm === 'Ампулы') {
                    $('#ampule_volume').empty().prop('disabled', false);
                    [...new Set(filteredData.map(item => item.volume))].forEach(volume => $('#ampule_volume').append(new Option(volume, volume)));
                    $('#ampule_volume_block').show();
                } else {
                    $('#ampule_volume').empty().prop('disabled', true);
                    $('#ampule_volume_block').hide();
                }

                $('#quantity').empty().prop('disabled', false);
                [...new Set(filteredData.map(item => item.count))].forEach(count => $('#quantity').append(new Option(count, count)));
            });
        });
    </script>
@endsection
