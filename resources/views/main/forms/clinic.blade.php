@extends('layouts.welcome')
@section('title', "Клиника \"{$clinic->name}\"")
@section('assets')
    <script src="https://api-maps.yandex.ru/2.1/?apikey={{ env('YANDEX_MAPS_API_KEY') }}&lang=ru_RU"
            type="text/javascript"></script>
    <script src="https://cdn.tiny.cloud/1/50lnun2el7cthnznyggpogomkmz8m7e51t8s7rkiuos53fjm/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
@endsection
@section('main')
    <section class="clinic-card">
        <div class="container">
            <h1>{{ $clinic->name }}</h1>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="pt-5 row justify-content-center">
                <div class="col-md-12 d-flex justify-content-center">
                    <div class="carousel-container" id="clinicPhotosCarousel__container">
                        <div id="clinicPhotosCarousel" class="carousel slide w-100" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                @forelse($clinic->photos as $index => $photo)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <picture>
                                            <source srcset="{{ asset('storage/' . $photo->photo) }}" type="image/webp">
                                            <img src="{{ asset('storage/' . $photo->photo) }}" class="d-block w-100" alt="Фото клиники" loading="lazy">
                                        </picture>
                                    </div>
                                @empty
                                    <div class="carousel-item active">
                                        <picture>
                                            <source srcset="{{ asset('assets/images/backgrounds/clinic_placeholder.webp') }}" type="image/webp">
                                            <img src="{{ asset('assets/images/backgrounds/clinic_placeholder.png') }}" class="d-block w-100" alt="Фото клиники" loading="lazy">
                                        </picture>
                                    </div>
                                @endforelse
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#clinicPhotosCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#clinicPhotosCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mt-4">
                    {!! $clinic->description !!}
                    <p><strong>Адрес: </strong>{{ $clinic->address }}</p>
                    @if($clinic->phone)
                        <p><strong>Телефон приёмной: </strong><a href="tel:{{ $clinic->phone }}">{{ $clinic->phone }}</a></p>
                    @endif
                </div>
            </div>
        @if($clinic->services()->isNotEmpty())
                <div class="pt-5 row">
                    <h2>Услуги</h2>
                    <div class="col-md-12">
                        @foreach ($clinic->services() as $service)
                            <span class="badge text-bg-primary">{{ $service->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($clinic->rating() > 0)
                <div class="pt-3 row">
                    <div class="col-md-12">
                        <h2 class="pt-4 pb-4">Рейтинг</h2>
                        <div class="progress mb-4" role="progressbar" aria-label="Warning example" aria-valuenow="{{ $clinic->rating() }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-warning text-dark" style="width: {{ $clinic->rating() * 20 }}%">{{ $clinic->rating() }}</div>
                        </div>
                        <div id="reviewsCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                @foreach ($feedbackChunks as $index => $chunk)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <div class="row justify-content-center">
                                            @foreach ($chunk as $feedback)
                                                <div class="col-md-4">
                                                    <div class="card d-flex flex-column">
                                                        <div class="card-body flex-grow-1">
                                                            <h5 class="card-title">{{ $feedback->user->userable()?->trimmedName() ?? 'Аноним' }}</h5>
                                                            <p>
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    <span class="bi bi-star-fill @if($i <= $feedback->mark) text-warning @else text-secondary @endif"></span>
                                                                @endfor
                                                            </p>
                                                            @if($feedback->positive_feedback)
                                                            <div class="card-text">
                                                                <strong>Понравилось:</strong>
                                                                <p>{{ $feedback->positive_feedback }}</p>
                                                            </div>
                                                            @endif
                                                            @if($feedback->negative_feedback)
                                                            <div class="card-text">
                                                                <strong>Не понравилось:</strong>
                                                                <p>{{ $feedback->negative_feedback }}</p>
                                                            </div>
                                                            @endif
                                                            <div class="card-text">
                                                                <p>{!! $feedback->description ?? '<span class="text-secondary">Автор оставил оценку без отзыва.</span>' !!}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Управление слайдером -->
                            <button class="carousel-control-prev" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
            @if(!$clinic->hasFeedback())
                <form class="pt-5 row" method="post" enctype="multipart/form-data" action="{{ route('main.clinics.form.feedback-create', $clinic->id) }}">
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
    </section>
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
                hintContent: `{!! nl2br(e($address)) !!}`, // Преобразование переноса строк в HTML
                balloonContent: `{!! nl2br(e($address)) !!}`
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

@endsection
