@extends('layouts.welcome')
@section('title', "Доктор {$doctor->fullName()}")
@section('assets')
    <script src="https://api-maps.yandex.ru/2.1/?apikey={{ config('yandex.yandex_key') }}&lang=ru_RU"
            type="text/javascript"></script>
    <script src="https://cdn.tiny.cloud/1/50lnun2el7cthnznyggpogomkmz8m7e51t8s7rkiuos53fjm/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
@endsection
@section('main')
    <section class="doctor-card">
        <div class="container">
            <div class="row">
                <h1><span>{{ $doctor->surname }}</span>, {{ $doctor->name }} {{ $doctor->patronym }}</h1>
                <h2>{{ $doctor->status }}</h2>
            </div>
            <div class="pt-3 row">
                <div class="col-md-3">
                    <picture>
                        <source srcset="{{ $doctor->getWebpPhoto() }}" type="image/webp">
                        <img src="{{ $doctor->photo }}">
                    </picture>
                </div>
                <div class="col-md-9">
                    <p><strong>Стаж:</strong> {{ $doctor->experience }}</p>
                    <p><strong>Адрес:</strong> {{ $doctor->address_job }}</p>
                    <p><strong>Место работы:</strong> @if($doctor->clinic->id != 0 )<a href="{{ route('main.clinics.form', $doctor->clinic->id) }}">{{ $doctor->clinic->name }}</a> @else {{ $doctor->clinic->name }} @endif</p>
                    <p>{!! $doctor->about !!}</p>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="2" class="text-center">Услуги</th>
                    </tr>
                    @if($dpTable->isNotEmpty())
                    <tr>
                        <th>Название услуги</th>
                        <th>Цена</th>
                    </tr>
                    @endif
                </thead>
                <tbody>
                @if($dpTable->isNotEmpty())
                    @foreach ($dpTable as $group => $services)
                        <tr>
                            <td colspan="2" class="fw-medium text-center">{{ $group }}</td>
                        </tr>

                        @foreach ($services as $service)
                            <tr>
                                <td>{{ $service->name }}</td>
                                <td>
                                    @if($service->discount_price)
                                        <s>{{ $service->price }}</s> {{ $service->discount_price }}₽
                                    @else
                                        {{ $service->price }}₽
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @else
                    <tr>
                        <td colspan="2" class="text-center">
                            <i class="bi bi-info-circle"></i> Нет зарегистрированных услуг
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
            @if($doctor->rating() > 0)
                <div class="pt-3 row">
                    <div class="col-md-12">
                        <h2 class="pt-4 pb-4">Рейтинг</h2>
                        <div class="row">
                            <div class="col-md-3 bg-light p-3">
                                @foreach($feedbackMarks as $index => $mark)
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="progress @if($index != 1) mb-3 @endif" role="progressbar" aria-label="Rating {{ $index }}" aria-valuenow="{{ $mark }}" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar bg-warning text-dark" style="width: {{ $totalFeedbacks > 0 ? ($mark / $totalFeedbacks) * 100 : 0 }}%">
                                                Оценка {{ $index }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        {{ $mark }}
                                    </div>
                                </div>
                                @endforeach
                                <p class="mt-3">Общий балл:</p>
                                <div class="progress mb-1" role="progressbar" aria-label="Warning example" aria-valuenow="{{ $doctor->rating() }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-warning text-dark" style="width: {{ $doctor->rating() * 20 }}%">{{ $doctor->rating() }}</div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                @foreach ($feedbacks as $feedback)
                                    <div class="row border mx-4 p-4">
                                        <h5>{{ $feedback->user->login }}</h5>
                                        <p>{{ $feedback->created_at }}</p>
                                        <p>
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="bi bi-star-fill @if($feedback->mark >= $i) text-warning @endif"></i>
                                            @endfor
                                        </p>
                                        @if ($feedback->positive_feedback)
                                            <p><strong>Понравилось:</strong></p>
                                            {{ $feedback->positive_feedback }}
                                        @endif
                                        @if ($feedback->negative_feedback)
                                            <p><strong>Не понравилось:</strong></p>
                                            {{ $feedback->negative_feedback }}
                                        @endif
                                        @if ($feedback->description)
                                            <p><strong>Отзыв:</strong></p>
                                            {!! $feedback->description !!}
                                        @endif
                                    </div>
                                @endforeach
                                {{ $feedbacks->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if(!$doctor->hasFeedback())
                <form class="pt-5 row" method="post" enctype="multipart/form-data" action="{{ route('main.doctors.form.feedback-create', $doctor->id) }}">
                    @csrf
                    <div class="col-md-12">
                        <h2><span>Оставить</span> оценку</h2>
                        <input type="hidden" name="mark" id="mark" value="5">
                        <p>
                            <button class="btn border-0 text-warning" data-mark="1" type="button"><span class="bi bi-star-fill"></span></button>
                            <button class="btn border-0 text-warning" data-mark="2" type="button"><span class="bi bi-star-fill"></span></button>
                            <button class="btn border-0 text-warning" data-mark="3" type="button"><span class="bi bi-star-fill"></span></button>
                            <button class="btn border-0 text-warning" data-mark="4" type="button"><span class="bi bi-star-fill"></span></button>
                            <button class="btn border-0 text-warning" data-mark="5" type="button"><span class="bi bi-star-fill"></span></button>
                        </p>
                        <div class="vertical-input-group">
                            <textarea class="form-control" name="positive_feedback" placeholder="Что Вам понравилось?"></textarea>
                            <textarea class="form-control" name="negative_feedback" placeholder="А что Вам не понравилось?"></textarea>
                            <textarea class="form-control" name="description" id="description" placeholder="Введите Ваш отзыв"></textarea>
                        </div>
                        <div class="btn-group pt-3 w-100">
                            <button class="btn btn-success" type="submit"><i class="bi bi-box-arrow-in-right"></i> Отправить</button>
                        </div>
                    </div>
                </form>
            @endif
            <div class="pt-5 row">
                <h2 class="pb-3"><span>Местоположение</span> на карте</h2>
                <div class="col-md-12 map-block" id="map"></div>
            </div>
        </div>
        <script>
            ymaps.ready(function () {
                var myMap = new ymaps.Map("map", {
                    center: [{{ $latitude }}, {{ $longitude }}],
                    zoom: 16,
                    controls: [],
                }, {
                    suppressMapOpenBlock: true
                });

                myMap.behaviors.disable(['rightMouseButtonMagnifier']);

                var myPlacemark = new ymaps.Placemark([{{ $latitude }}, {{ $longitude }}], {
                    hintContent: `{!! nl2br(e($doctor->address_job)) !!}`, // Преобразование переноса строк в HTML
                    balloonContent: `{!! nl2br(e($doctor->address_job)) !!}`
                });

                myMap.geoObjects.add(myPlacemark);
            });

            document.addEventListener('DOMContentLoaded', function () {
                let buttons = document.querySelectorAll('button.btn.border-0');

                buttons.forEach(function (e) {
                    e.addEventListener('click', function () {
                        let mark = e.dataset.mark;

                        buttons.forEach(function (button) {
                            if (button.dataset.mark > mark) {
                                button.classList.remove('text-warning');
                            } else {
                                button.classList.add('text-warning');
                            }
                        })

                        document.querySelector('#mark').value = mark;
                    })
                });
            });

            tinymce.init({
                selector: '#description', // Привязываем TinyMCE к текстовому полю
                plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
                toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                height: 400, // Высота редактора
                menubar: false, // Убираем верхнее меню (если нужно)
                language: 'ru', // Устанавливаем русский язык
                language_url: '/assets/js/tinymce/langs/ru.js' // Путь к файлу локализации
            });
        </script>
    </section>
@endsection
