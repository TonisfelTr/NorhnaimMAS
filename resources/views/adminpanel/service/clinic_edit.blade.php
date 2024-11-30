@extends('layouts.admin')
@section('title', "Редактирование клиники {$clinic->name}")
@section('assets')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/imask/6.4.2/imask.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/50lnun2el7cthnznyggpogomkmz8m7e51t8s7rkiuos53fjm/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
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
        <h1>Редактирование клиники {{ $clinic->name }}</h1>
        {{ Breadcrumbs::render('admin.dictionary.clinics.edit') }}
        <form class="row" action="{{ route('admin.dictionary.clinics.save', $clinic->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="col-md-6">
                <div class="pt-3">
                    <label for="name" class="form-label">Название</label>
                    <input id="name" name="name" class="form-control" type="text" value="{{ old('name') ?? $clinic->name }}">
                </div>
                <div class="pt-3">
                    <label for="address" class="form-label">Адрес</label>
                    <input id="address" name="address" class="form-control" type="text" value="{{ old('address') ?? $clinic->address }}">
                </div>
                <div class="pt-3">
                    <label for="phone" class="form-label">Телефон приёмной</label>
                    <input id="phone" name="phone" class="form-control" type="text" value="{{ old('phone') ?? $clinic->phone }}" placeholder="+7 (999) 999 99-99">
                </div>
                <div class="pt-3">
                    <label for="description" class="form-label">Описание</label>
                    <textarea id="description" name="description" class="form-control">
                        {{ old('description') ?? trim($clinic->description) }}
                    </textarea>
                </div>
                <div class="pt-3">
                    <label for="services" class="form-label">Услуги</label>
                    <select id="services" name="services[]" class="form-control" multiple>
                        @foreach($services as $service)
                            <option value="{{ $service->name }}" selected>{{ $service->name }}</option>
                        @endforeach
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
                    <label for="photos" class="form-label">Фотографии клиники</label>
                    <div id="clinicPhotosCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @forelse($clinic->photos as $index => $photo)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <picture class="d-block w-100">
                                        <source srcset="{{ asset('storage/' . $photo->photo) }}" type="image/webp">
                                        <img src="{{ asset('storage/' . $photo->photo) }}" class="d-block w-100" alt="Фото клиники">
                                    </picture>
                                </div>
                            @empty
                                <div class="carousel-item active">
                                    <picture>
                                        <source srcset="{{ asset('assets/images/backgrounds/clinic_placeholder.webp') }}" type="image/webp">
                                        <img src="{{ asset('assets/images/backgrounds/clinic_placeholder.png') }}" class="d-block w-100" alt="Фото клиники">
                                    </picture>
                                </div>
                            @endforelse
                        </div>
                        <!-- Навигация карусели -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#clinicPhotosCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#clinicPhotosCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>

                    <div class="pt-3">
                        <label for="photos" class="form-label">Добавить фотографии</label>
                        <input id="photos" name="photos[]" class="form-control" type="file" accept=".jpeg, .jpg, .png, .webp" multiple>
                    </div>

                    <!-- Поле для выбора обложки -->
                    @if($clinic->photos->count())
                        <div class="pt-3">
                            <label for="cover_photo_index" class="form-label">Выберите обложку</label>
                            <select id="cover_photo_index" name="cover_photo_index" class="form-control">
                                @foreach($clinic->photos as $index => $photo)
                                    <option value="{{ $index }}" {{ $photo->is_cover ? 'selected' : '' }}>
                                        Фото {{ $index + 1 }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
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

        $('#services').selectize({
            plugins: ["clear_button", "remove_button"],
            create: true,        // Разрешает ввод новых значений
            persist: false,      // Убирает созданные элементы из поля, если они удалены
            maxItems: null,      // Позволяет множественный выбор, можно установить число для ограничения
            placeholder: 'Введите или выберите значение',
        });
    </script>
@endsection
