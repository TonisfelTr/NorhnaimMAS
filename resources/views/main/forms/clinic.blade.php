@extends('layouts.welcome')
@section('title', "Клиника {$clinic->name}")
@section('assets')
    <script src="https://api-maps.yandex.ru/2.1/?apikey={{ env('YANDEX_MAPS_API_KEY') }}&lang=ru_RU" type="text/javascript"></script>
    @if($clinic->photo)
        <style>
            picture source,
            picture img {
                background-color: #8f8f8f;
                width: 310px;
                height: 230px;
                object-fit: cover;
            }
        </style>
    @endif
@endsection
@section('main')
    <section class="clinic-card">
        <div class="container">
            <h1>{{ $clinic->name }}</h1>
            <div class="pt-5 row">
                <div class="col-md-3">
                    <picture>
                        <source srcset="{{ $clinic->getWebpPhoto() }}" type="image/webp">
                        <img src="{{ $clinic->photo ?? asset('assets/images/backgrounds/feedback_placeholder.png') }}">
                    </picture>
                </div>
                <div class="col-md-6">
                    {!! $clinic->description !!}
                    <p><strong>Адрес: </strong>{{ $clinic->address }}</p>
                    <p><strong>Телефон приёмной: </strong><a href="tel:{{ $clinic->phone }}">{{ $clinic->phone }}</a></p>
                </div>
            </div>
            <div class="pt-5 row">
                <h2>Услуги</h2>
                <div class="col-md-12">
                    @foreach ($clinic->services() as $service)
                        <span class="badge text-bg-primary">{{ $service->name }}</span>
                    @endforeach
                </div>
            </div>
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
    </script>

@endsection
