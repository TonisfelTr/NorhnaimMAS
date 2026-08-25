@extends('layouts.welcome')
@section('title', "Клиника \"{$clinic->name}\"")
@section('body_class', 'nh-public-page nh-clinic-page nh-detail-page')
@section('assets')
    <script src="https://api-maps.yandex.ru/2.1/?apikey={{ env('YANDEX_MAPS_API_KEY') }}&lang=ru_RU" type="text/javascript"></script>
    <script src="https://cdn.tiny.cloud/1/50lnun2el7cthnznyggpogomkmz8m7e51t8s7rkiuos53fjm/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
@endsection

@section('page_header')
    <section class="nh-public-hero nh-detail-hero">
        <div class="nh-public-shell nh-detail-hero__grid">
            <div>
                <div class="nh-public-kicker">Карточка клиники</div>
                <h1>{{ $clinic->name }}</h1>
                <div class="nh-detail-hero__meta">
                    <span><i class="bi bi-geo-alt"></i> {{ $clinic->address }}</span>
                    @if($clinic->phone)
                        <a href="tel:{{ $clinic->phone }}"><i class="bi bi-telephone"></i> {{ $clinic->phone }}</a>
                    @endif
                </div>
            </div>
            @if($clinic->rating() > 0)
                <div class="nh-score-card">
                    <span>Рейтинг</span>
                    <strong>{{ $clinic->rating() }}</strong>
                    <div class="nh-score-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star-fill @if($i <= round($clinic->rating())) is-active @endif"></i>
                        @endfor
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('main')
    <section class="nh-public-section nh-detail-content">
        <div class="nh-public-shell">
            @if ($errors->any())
                <div class="alert alert-danger nh-alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success nh-alert">{{ session('success') }}</div>
            @endif

            <div class="nh-detail-intro">
                <div class="nh-media-card">
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
                            <button class="carousel-control-prev nh-carousel-control" type="button" data-bs-target="#clinicPhotosCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Предыдущее фото</span>
                            </button>
                            <button class="carousel-control-next nh-carousel-control" type="button" data-bs-target="#clinicPhotosCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Следующее фото</span>
                            </button>
                        </div>
                    </div>
                </div>

                <article class="nh-detail-summary">
                    <div class="nh-section-label"><span>О клинике</span></div>
                    <div class="nh-prose nh-prose--compact">{!! $clinic->description !!}</div>
                    <dl class="nh-facts">
                        <div><dt>Адрес</dt><dd>{{ $clinic->address }}</dd></div>
                        @if($clinic->phone)
                            <div><dt>Телефон приёмной</dt><dd><a href="tel:{{ $clinic->phone }}">{{ $clinic->phone }}</a></dd></div>
                        @endif
                    </dl>

                    @if($clinic->services()->isNotEmpty())
                        <div class="nh-detail-services">
                            <span class="nh-detail-services__label">Услуги</span>
                            <div class="nh-chip-list">
                                @foreach ($clinic->services() as $service)
                                    <span class="nh-chip">{{ $service->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </article>
            </div>

            @if ($clinic->rating() > 0)
                <section class="nh-detail-section">
                    <div class="nh-section-label"><span>Отзывы</span></div>
                    <div class="nh-rating-overview">
                        <div class="nh-rating-overview__score">
                            <strong>{{ $clinic->rating() }}</strong>
                            <span>из 5</span>
                        </div>
                        <div class="nh-rating-overview__bar">
                            <div class="nh-rating-overview__fill" style="width: {{ $clinic->rating() * 20 }}%"></div>
                        </div>
                    </div>

                    <div id="reviewsCarousel" class="carousel slide nh-reviews-carousel" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach ($feedbackChunks as $index => $chunk)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <div class="nh-review-grid">
                                        @foreach ($chunk as $feedback)
                                            <article class="nh-review-card">
                                                <div class="nh-review-card__head">
                                                    <strong>{{ $feedback->user->userable()?->trimmedName() ?? 'Аноним' }}</strong>
                                                    <div>
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="bi bi-star-fill @if($i <= $feedback->mark) is-active @endif"></i>
                                                        @endfor
                                                    </div>
                                                </div>
                                                @if($feedback->positive_feedback)
                                                    <div><span>Понравилось</span><p>{{ $feedback->positive_feedback }}</p></div>
                                                @endif
                                                @if($feedback->negative_feedback)
                                                    <div><span>Не понравилось</span><p>{{ $feedback->negative_feedback }}</p></div>
                                                @endif
                                                <div class="nh-review-card__text">
                                                    {!! $feedback->description ?? '<span class="text-secondary">Автор оставил оценку без отзыва.</span>' !!}
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="carousel-control-prev nh-carousel-control" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Предыдущие отзывы</span>
                        </button>
                        <button class="carousel-control-next nh-carousel-control" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Следующие отзывы</span>
                        </button>
                    </div>
                </section>
            @endif

            @if(!$clinic->hasFeedback())
                <section class="nh-detail-section">
                    <form class="nh-feedback-card" method="post" enctype="multipart/form-data" action="{{ route('main.clinics.form.feedback-create', $clinic->id) }}">
                        @csrf
                        <div class="nh-section-label"><span>Оставить оценку</span></div>
                        <h2>Поделитесь впечатлением о клинике</h2>
                        <input type="hidden" name="mark" id="mark" value="5">
                        <div class="nh-rating-input" aria-label="Оценка">
                            @for($i = 1; $i <= 5; $i++)
                                <button class="btn border-0 text-warning" data-mark="{{ $i }}" type="button"><span class="bi bi-star-fill"></span></button>
                            @endfor
                        </div>
                        <div class="nh-form">
                            <textarea class="form-control" name="positive_feedback" placeholder="Что Вам понравилось?"></textarea>
                            <textarea class="form-control" name="negative_feedback" placeholder="Что можно улучшить?"></textarea>
                            <textarea class="form-control" name="description" id="description" placeholder="Ваш отзыв"></textarea>
                            <button class="nh-public-button nh-public-button--primary" type="submit"><i class="bi bi-send"></i> Отправить отзыв</button>
                        </div>
                    </form>
                </section>
            @endif

            <section class="nh-detail-section">
                <div class="nh-section-label"><span>Местоположение</span></div>
                <h2 class="nh-detail-section__title">Клиника на карте</h2>
                <div class="map-block nh-map" id="map"></div>
            </section>
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
                hintContent: `{!! nl2br(e($address)) !!}`,
                balloonContent: `{!! nl2br(e($address)) !!}`
            });

            myMap.geoObjects.add(myPlacemark);
        });

        document.addEventListener('DOMContentLoaded', function () {
            let buttons = document.querySelectorAll('.nh-rating-input button[data-mark]');

            buttons.forEach(function (e) {
                e.addEventListener('click', function () {
                    let mark = e.dataset.mark;

                    buttons.forEach(function (button) {
                        if (button.dataset.mark > mark) {
                            button.classList.remove('text-warning');
                            button.classList.add('text-secondary');
                        } else {
                            button.classList.remove('text-secondary');
                            button.classList.add('text-warning');
                        }
                    });

                    document.querySelector('#mark').value = mark;
                });
            });
        });

        tinymce.init({
            selector: '#description',
            plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            height: 400,
            menubar: false,
            language: 'ru',
            language_url: '/assets/js/tinymce/langs/ru.js'
        });
    </script>
@endsection
