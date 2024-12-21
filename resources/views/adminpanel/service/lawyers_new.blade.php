@extends('layouts.admin')
@section('title', 'Создание записи юриста')
@section('assets')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/imask/6.4.2/imask.min.js"></script>
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
        <h1>Создание записи юриста
        </h1>
        {{ Breadcrumbs::render('admin.jurisprudence.lawyers.new') }}
        <form class="row" action="{{ route('admin.jurisprudence.lawyers.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="col-md-6">
                <div class="pt-3">
                    <label for="name" class="form-label">Имя</label>
                    <input required id="name" name="name" class="form-control" type="text" value="{{ old('name') }}">
                </div>
                <div class="pt-3">
                    <label for="surname" class="form-label">Фамилия</label>
                    <input required id="surname" name="surname" class="form-control" type="text" value="{{ old('surname') }}">
                </div>
                <div class="pt-3">
                    <label for="phone" class="form-label">Телефон</label>
                    <input required id="phone" name="phone" class="form-control" type="text" value="{{ old('phone') }}" placeholder="+7 (999) 999 99-99">
                </div>
                <div class="pt-3">
                    <label for="profession" class="form-label">Профессия</label>
                    <input required id="profession" name="profession" class="form-control" type="text" value="{{ old('profession') }}">
                </div>
                <div class="pt-3">
                    <label for="skills" class="form-label">Услуги</label>
                    <select required id="skills" name="skills[]" class="form-control" multiple>

                    </select>
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3">
                    <button class="btn btn-outline-success btn-sm" type="submit"><i
                            class="bi bi-box-arrow-down"></i> Сохранить изменения
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="pt-3">
                    <label for="base_price" class="form-label">Базовая цена</label>
                    <input class="form-control" name="base_price" id="base_price" type="number" min="1" value="{{ old('base_price') }}">
                </div>
                <div class="pt-3">
                    <label for="experience" class="form-label">Опыт</label>
                    <input class="form-control" name="experience" id="experience" type="number" min="0" value="{{ old('experience') }}">
                </div>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var phoneMask = IMask(document.getElementById('phone'), {
                mask: '+7 (000) 000-00-00'
            });

            tinymce.init({
                selector: '#description', // Привязываем TinyMCE к текстовому полю
                plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
                toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                height: 400, // Высота редактора
                menubar: false, // Убираем верхнее меню (если нужно)
            });
        });

        $('#skills').selectize({
            plugins: ["clear_button", "remove_button"],
            create: true,        // Разрешает ввод новых значений
            persist: false,      // Убирает созданные элементы из поля, если они удалены
            maxItems: null,      // Позволяет множественный выбор, можно установить число для ограничения
            placeholder: 'Введите или выберите значение',
        });
    </script>
@endsection
