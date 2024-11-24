@extends('layouts.welcome')
@section('title', 'Главная')
@section('assets')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
@endsection
@section('main')
    <section class="welcome">
        <div class="container pt-5">
            <h1 class="container__header"><span class="container__header-span">Мы те, кто заботится</span> о Вашем времени и здоровье</h1>
        </div>
    </section>
    <section class="cities">
        <div class="container">
            <p class="section-cities__p">Найдите своего врача, выбрав свой город</p>
            <div class="images-container">
                @if(rand(0, 100) > 50)
                    <div class="images-container__with-overlay images-container__big-left">
                        <div class="images-container__overlay d-none">
                            <p>Ростов-на-Дону</p>
                        </div>
                        <picture>
                            <source srcset="{{ url('assets/images/cities/rostov-na-donu.webp') }}" type="image/webp">
                            <img src="{{ url('assets/images/cities/rostov-na-donu.png') }}" loading="lazy">
                        </picture>
                    </div>
                @else
                    <div class="images-container__with-overlay images-container__big-left">
                        <div class="images-container__overlay d-none">
                            <p>Москва</p>
                        </div>
                        <picture>
                            <source srcset="{{ url('assets/images/cities/moscow.webp') }}" type="image/webp">
                            <img src="{{ url('assets/images/cities/moscow.png') }}" loading="lazy">
                        </picture>
                    </div>
                @endif
                <div class="images-container__part">
                    <div class="images-container__square-part">
                        @if(rand(0, 100) > 50)
                            <div class="images-container__with-overlay images-container__square-top">
                                <div class="images-container__overlay d-none">
                                    <p>Екатеринбург</p>
                                </div>
                                <picture>
                                    <source srcset="{{ url('assets/images/cities/ekaterinburg.webp') }}" type="image/webp">
                                    <img src="{{ url('assets/images/cities/ekaterinburg.png') }}" loading="lazy">
                                </picture>
                            </div>
                        @else
                            <div class="images-container__with-overlay images-container__square-top">
                                <div class="images-container__overlay d-none">
                                    <p>Челябинск</p>
                                </div>
                                <picture>
                                    <source srcset="{{ url('assets/images/cities/chelyabinsk.webp') }}" type="image/webp">
                                    <img src="{{ url('assets/images/cities/chelyabinsk.png') }}" loading="lazy">
                                </picture>
                            </div>
                        @endif
                        @if(rand(0, 100) > 50)
                            <div class="images-container__with-overlay images-container__square-top">
                                <div class="images-container__overlay d-none">
                                    <p>Красноярск</p>
                                </div>
                                <picture>
                                    <source srcset="{{ url('assets/images/cities/krasnoyarsk.webp') }}" type="image/webp">
                                    <img src="{{ url('assets/images/cities/krasnoyarsk.png') }}" loading="lazy">
                                </picture>
                            </div>
                        @else
                            <div class="images-container__with-overlay images-container__square-top">
                                <div class="images-container__overlay d-none">
                                    <p>Томск</p>
                                </div>
                                <picture>
                                    <source srcset="{{ url('assets/images/cities/tomsk.webp') }}" type="image/webp">
                                    <img src="{{ url('assets/images/cities/tomsk.png') }}" loading="lazy">
                                </picture>
                            </div>
                        @endif
                        @if(($kemerovo = rand(0, 100)) <= 25)
                            <div class="images-container__with-overlay images-container__square-top">
                                <div class="images-container__overlay d-none">
                                    <p>Кемерово</p>
                                </div>
                                <picture>
                                    <source srcset="{{ url('assets/images/cities/kemerovo.webp') }}" type="image/webp">
                                    <img src="{{ url('assets/images/cities/kemerovo.png') }}" loading="lazy">
                                </picture>
                            </div>
                        @elseif($kemerovo > 25 && $kemerovo <= 75)
                            <div class="images-container__with-overlay images-container__square-top">
                                <div class="images-container__overlay d-none">
                                    <p>Новосибирск</p>
                                </div>
                                <picture>
                                    <source srcset="{{ url('assets/images/cities/novosibirsk.webp') }}" type="image/webp">
                                    <img src="{{ url('assets/images/cities/novosibirsk.png') }}" loading="lazy">
                                </picture>
                            </div>
                        @elseif($kemerovo > 75)
                                <div class="images-container__with-overlay images-container__square-top">
                                    <div class="images-container__overlay d-none">
                                        <p>Краснодар</p>
                                    </div>
                                    <picture>
                                        <source srcset="{{ url('assets/images/cities/krasnodar.webp') }}" type="image/webp">
                                        <img src="{{ url('assets/images/cities/krasnodar.png') }}" loading="lazy">
                                    </picture>
                                </div>
                        @endif
                    </div>
                    <div class="images-container__with-overlay images-container__small-bottom">
                        <div class="images-container__overlay d-none">
                            <p>Санкт-Петербург</p>
                        </div>
                        <picture>
                            <source srcset="{{ url('assets/images/cities/saint-petersburg.webp') }}" type="image/webp">
                            <img src="{{ url('assets/images/cities/saint-petersburg.png') }}" loading="lazy">
                        </picture>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="information">
        <div class="container pt-5">
            <h1 class="container__header greater text-center"><span class="container__header-span">Наши</span> возможности</h1>
            <div class="row-cols-6 d-flex flex-wrap justify-content-between">
                <ul class="abilities__main">
                    <li class="abilities__bg-diagnostic">
                        <h1 class="abilities__header">Диагностика</h1>
                        <ul>
                            <li>Проведение тестов</li>
                            <li>Опрос пациента</li>
                            <li>Соблюдение критериев МКБ-10</li>
                        </ul>
                    </li>
                    <li class="abilities__bg-cure">
                        <h1 class="abilities__header">Лечение</h1>
                        <ul>
                            <li>Назначение препаратов по рекомендациям ВОЗ</li>
                            <li>Запись к врачу</li>
                            <li>Выбор клиники</li>
                        </ul>
                    </li>
                    <li class="abilities__bg-library">
                        <h1 class="abilities__header">Библиотека</h1>
                        <ul>
                            <li>Статьи</li>
                            <li>Исследования</li>
                        </ul>
                    </li>
                    <li class="abilities__bg-medicines">
                        <h1 class="abilities__header">Лекарственный справочник</h1>
                        <ul>
                            <li>Инструкции препаратов</li>
                            <li>Зарегистрированные формы</li>
                        </ul>
                    </li>
                    <li class="abilities__bg-law">
                        <h1 class="abilities__header">Юридическая защита</h1>
                        <ul>
                            <li>Закон РФ о психиатрической помощи</li>
                            <li>Советы по правовым прениям</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </section>
    <section class="advertisement">
        <div class="doctor-block">
            <div class="row d-inline-flex justify-content-center w-100">
                <div class="col-lg-1">
                    <img src="{{ asset('assets/images/doctors/doctor.png') }}">
                </div>
                <div class="col-lg-5 doctor-block__text">
                    Для Вас быстрая работа и максимально корректная диагностика
                </div>
            </div>
        </div>
        <div class="container pt-5">
            <h1 class="container__header"><span class="container__header-span">Подберите тариф</span> под Ваши нужды</h1>
            <p class="section-advertisement__p">Мы предлагаем квалифицированную помощь. Выберите тариф, который Вам подходит:</p>
            <div class="row align-items-stretch">
                <div class="col-sm-3">
                    <div class="card">
                        <h5 class="card-header text-center bg-info text-white">Бесплатно</h5>
                        <div class="card-body">
                            <h3 class="card-title text-center">Гость</h3>
                            <p class="card-text text-center tarrif-description">Тариф для ознакомления с системой</p>
                            <h1 class="text-center p-3 text-price">0 <span>₽\мес</span></h1>
                            <ul>
                                <li>Презентация возможностей</li>
                                <li>Диагностика</li>
                                <li>Панель пациента</li>
                            </ul>
                            <div class="btn-group w-100">
                                <a href="#" class="btn btn-primary text-center">Попробовать</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title text-center card-unhead">Ассистент</h3>
                            <p class="card-text text-center tarrif-description">Тариф для врачей с поддержкой системы</p>
                            <h1 class="text-center p-3 text-price">900 <span>₽\мес</span></h1>
                            <ul>
                                <li>Врачебная панель</li>
                                <li>Ведение анамнезов</li>
                                <li>Помощь с диагностикой</li>
                                <li>Выписка рецептов</li>
                                <li>Медикаментозный подбор</li>
                            </ul>
                            <div class="btn-group w-100">
                                <a href="#" class="btn btn-primary text-center">Попробовать</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title text-center card-unhead">Знахарь</h3>
                            <p class="card-text text-center tarrif-description">Тариф для пациентов, которым нужна анонимная помощь</p>
                            <h1 class="text-center p-3 text-price">1100 <span>₽\мес</span></h1>
                            <ul>
                                <li>Панель пациента</li>
                                <li>Диагностика</li>
                                <li>Медикаментозный подбор</li>
                                <li>Консультация с врачом</li>
                                <li>Полная анонимность</li>
                                <li>Юридическая помощь</li>
                            </ul>
                            <div class="btn-group w-100">
                                <a href="#" class="btn btn-primary">Попробовать</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card">
                        <h5 class="card-header text-center bg-info text-white">Лучший вариант!</h5>
                        <div class="card-body">
                            <h3 class="card-title text-center">Библиотекарь</h3>
                            <p class="card-text text-center tarrif-description">Тариф для тех, кто интересуется психиатрией в научном плане</p>
                            <h1 class="text-center p-3 text-price">1600 <span>₽\мес</span></h1>
                            <ul>
                                <li>Врачебная панель</li>
                                <li>Юридическая помощь</li>
                                <li>Доступ к библиотеке научных статей</li>
                                <li>Возможность стать <span class="text-decoration-underline">Администратором</span></li>
                            </ul>
                            <div class="btn-group w-100">
                                <a href="#" class="btn btn-primary">Попробовать</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @if (!$feedbacks->isEmpty())
    <section class="feedback">
        <div class="container pt-5">
            <h1 class="container__header"><span class="container__header-span">Отзывы специалистов</span> о нашей работе</h1>
            <div id="carousel-feedbacks" class="carousel slide">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carousel-feedbacks" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#carousel-feedbacks" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#carousel-feedbacks" data-bs-slide-to="2" aria-label="Slide 3"></button>
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="row p-3 pl-10 feedback-container">
                            <picture class="feedback-slider__image">
                                <source srcset="{{ url('assets/images/backgrounds/feedback_placeholder.webp') }}" type="image/webp">
                                <img src="{{ url('assets/images/backgrounds/feedback_placeholder.png') }}" alt="Нет фотографии" loading="lazy">
                            </picture>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="row p-3 pl-10 feedback-container">
                            <picture class="feedback-slider__image">
                                <source srcset="{{ url('assets/images/backgrounds/feedback_placeholder.webp') }}" type="image/webp">
                                <img src="{{ url('assets/images/backgrounds/feedback_placeholder.png') }}" alt="Нет фотографии" loading="lazy">
                            </picture>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="row p-3 pl-10">
                            <picture class="feedback-slider__image">
                                <source srcset="{{ url('assets/images/backgrounds/feedback_placeholder.webp') }}" type="image/webp">
                                <img src="{{ url('assets/images/backgrounds/feedback_placeholder.png') }}" alt="Нет фотографии" loading="lazy">
                            </picture>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev bs-carousel-dark-arrow" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                    <span class="bi bi-chevron-left font-35 color-arrow" aria-hidden="true"></span>
                    <span class="visually-hidden">Предыдущий</span>
                </button>
                <button class="carousel-control-next bs-carousel-dark-arrow " type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                    <span class="bi bi-chevron-right font-35 color-arrow" aria-hidden="true"></span>
                    <span class="visually-hidden">Следующий</span>
                </button>
            </div>
        </div>
    </section>
    <script>
        $(document).ready(function () {
            $('#carousel-feedbacks').carousel();
        });
    </script>
    @endif
@endsection
