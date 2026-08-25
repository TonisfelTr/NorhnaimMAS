@extends('layouts.welcome')
@section('title', "Доктор {$doctor->full_name}")
@section('body_class', 'nh-public-page nh-doctor-page nh-detail-page')
@section('assets')
    <script src="https://api-maps.yandex.ru/2.1/?apikey={{ config('yandex.yandex_key') }}&lang=ru_RU" type="text/javascript"></script>
    <script src="https://cdn.tiny.cloud/1/50lnun2el7cthnznyggpogomkmz8m7e51t8s7rkiuos53fjm/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
@endsection

@section('page_header')
    <section class="nh-public-hero nh-detail-hero">
        <div class="nh-public-shell nh-detail-hero__grid">
            <div>
                <div class="nh-public-kicker">Карточка специалиста</div>
                <h1>{{ $doctor->surname }}, {{ $doctor->name }} {{ $doctor->patronym }}</h1>
                <p class="nh-detail-hero__subtitle">{{ $doctor->status }}</p>
            </div>
            @if($doctor->rating() > 0)
                <div class="nh-score-card">
                    <span>Рейтинг</span>
                    <strong>{{ $doctor->rating() }}</strong>
                    <div class="nh-score-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star-fill @if($i <= round($doctor->rating())) is-active @endif"></i>
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
            <div class="nh-doctor-intro">
                <picture class="nh-doctor-photo">
                    <source srcset="{{ $doctor->getWebpPhoto() }}" type="image/webp">
                    <img src="{{ $doctor->photo }}" alt="{{ $doctor->full_name }}" loading="eager">
                </picture>
                <article class="nh-detail-summary">
                    <div class="nh-section-label"><span>О специалисте</span></div>
                    <dl class="nh-facts">
                        <div><dt>Стаж</dt><dd>{{ $doctor->experience }}</dd></div>
                        <div><dt>Адрес</dt><dd>{{ $doctor->address_job }}</dd></div>
                        <div>
                            <dt>Место работы</dt>
                            <dd>
                                @if($doctor->clinic->id != 0)
                                    <a href="{{ route('main.clinics.form', $doctor->clinic->id) }}">{{ $doctor->clinic->name }}</a>
                                @else
                                    {{ $doctor->clinic->name }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                    <div class="nh-prose nh-prose--compact">{!! $doctor->about !!}</div>
                </article>
            </div>

            <section class="nh-detail-section">
                <div class="nh-section-label"><span>Услуги</span></div>
                <div class="nh-table-card">
                    <table class="table nh-table">
                        <thead>
                            @if($dpTable->isNotEmpty())
                                <tr><th>Название услуги</th><th>Цена</th></tr>
                            @endif
                        </thead>
                        <tbody>
                            @if($dpTable->isNotEmpty())
                                @foreach ($dpTable as $group => $services)
                                    <tr class="nh-table__group"><td colspan="2">{{ $group }}</td></tr>
                                    @foreach ($services as $service)
                                        <tr>
                                            <td>{{ $service->name }}</td>
                                            <td class="nh-table__price">
                                                @if($service->discount_price)
                                                    <s>{{ $service->price }}</s> {{ $service->discount_price }} ₽
                                                @else
                                                    {{ $service->price }} ₽
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @else
                                <tr><td colspan="2" class="text-center"><i class="bi bi-info-circle"></i> Нет зарегистрированных услуг</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>

            @if($doctor->rating() > 0)
                <section class="nh-detail-section">
                    <div class="nh-section-label"><span>Отзывы</span></div>
                    <div class="nh-doctor-reviews">
                        <aside class="nh-rating-breakdown">
                            <div class="nh-rating-breakdown__overall">
                                <strong>{{ $doctor->rating() }}</strong><span>из 5</span>
                            </div>
                            @foreach($feedbackMarks as $index => $mark)
                                <div class="nh-rating-breakdown__row">
                                    <span>{{ $index }}</span>
                                    <div><i style="width: {{ $totalFeedbacks > 0 ? ($mark / $totalFeedbacks) * 100 : 0 }}%"></i></div>
                                    <b>{{ $mark }}</b>
                                </div>
                            @endforeach
                        </aside>
                        <div class="nh-review-stack">
                            @foreach ($feedbacks as $feedback)
                                <article class="nh-review-card">
                                    <div class="nh-review-card__head">
                                        <div><strong>{{ $feedback->user->login }}</strong><span>{{ $feedback->created_at }}</span></div>
                                        <div>
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="bi bi-star-fill @if($feedback->mark >= $i) is-active @endif"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    @if ($feedback->positive_feedback)
                                        <div><span>Понравилось</span><p>{{ $feedback->positive_feedback }}</p></div>
                                    @endif
                                    @if ($feedback->negative_feedback)
                                        <div><span>Не понравилось</span><p>{{ $feedback->negative_feedback }}</p></div>
                                    @endif
                                    @if ($feedback->description)
                                        <div class="nh-review-card__text">{!! $feedback->description !!}</div>
                                    @endif
                                </article>
                            @endforeach
                            <div class="nh-pagination">{{ $feedbacks->links('pagination::bootstrap-5') }}</div>
                        </div>
                    </div>
                </section>
            @endif

            @if(!$doctor->hasFeedback())
                <section class="nh-detail-section">
                    <form class="nh-feedback-card" method="post" enctype="multipart/form-data" action="{{ route('main.doctors.form.feedback-create', $doctor->id) }}">
                        @csrf
                        <div class="nh-section-label"><span>Оставить оценку</span></div>
                        <h2>Поделитесь впечатлением о специалисте</h2>
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
                <h2 class="nh-detail-section__title">Место приёма на карте</h2>
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
                hintContent: `{!! nl2br(e($doctor->address_job)) !!}`,
                balloonContent: `{!! nl2br(e($doctor->address_job)) !!}`
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
