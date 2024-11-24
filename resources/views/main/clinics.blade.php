@extends('layouts.welcome')
@section('title', 'Врачи и клиники')
@section('assets')
@endsection
@section('main')
    <section class="clinics">
        <div class="container pt-5">
            <h1><span>Подберите клинику</span> по Вашему вкусу!</h1>
            <p>Посмотрите рекомендуемые клиники:</p>
            @foreach ($clinics as $clinic)
                <div class="card mb-3 position-relative" style="max-width: 100%">
                    <div class="row g-0">
                        <div class="col-md-2 position-relative">
                            <picture>
                                <source srcset="{{ $clinic->getWebpPhoto() ?? asset('assets/images/backgrounds/feedback_placeholder.webp') }}"
                                        type="image/webp">
                                <img src="{{ $clinic->photo }}"
                                     class="img-fluid rounded-start" alt="Нет фотографии">
                            </picture>
                            <!-- Прогрессбар с отступом -->
                            <div class="progress position-absolute progress-image__in"
                                 role="progressbar"
                                 aria-label="Отзывы"
                                 aria-valuenow="{{ $clinic->rating() * 20 }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                <div class="progress-bar bg-warning text-dark"
                                     style="width: {{ $clinic->rating() * 20 }}%">
                                    {{ $clinic->rating() }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-10 d-flex flex-column">
                            <div class="card-body flex-grow-1">
                                <h3 class="card-title">{{ $clinic->name }}</h3>
                                <p class="card-text">{{ $clinic->address }}</p>
                                <p class="card-text">{{ $clinic->description }}</p>
                            </div>
                            <!-- Кнопка внизу карточки -->
                            <a href="{{ route('main.clinics.form', $clinic->id) }}"
                               class="btn btn-primary position-absolute doctor-card__button">
                                Посмотреть карточку клиники <span class="bi bi-chevron-double-right"></span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
            {{ $clinics->links('pagination::bootstrap-5') }}
        </div>
    </section>
    <section class="advertisement">
        <div class="container">
            <div class="row">
                <picture class="col-md-4">
                    <source srcset="{{ asset('assets/images/doctors/writing-doctor.webp') }}" type="image/webp">
                    <img src="{{ asset('assets/images/doctors/writing-doctor.png') }}">
                </picture>
                <div class="col-md-8">
                    <p>Не нашли подходящую клинику? Поищите специалиста по вкусу!</p>
                </div>
            </div>
        </div>
    </section>
    <section class="doctors">
        <div class="container pt-5">
            <h1 class="pb-4">Врачи</h1>
            @foreach ($doctors as $doctor)
                <div class="card mb-3 position-relative" style="max-width: 100%">
                    <div class="row g-0">
                        <div class="col-md-2 position-relative">
                            <picture>
                                <source srcset="{{ asset('assets/images/backgrounds/feedback_placeholder.webp') }}"
                                        type="image/webp">
                                <img src="{{ asset('assets/images/backgrounds/feedback_placeholder.png') }}"
                                     class="img-fluid rounded-start" alt="Нет фотографии">
                            </picture>
                            <!-- Прогрессбар с отступом -->
                            <div class="progress position-absolute progress-image__in"
                                 role="progressbar"
                                 aria-label="Отзывы"
                                 aria-valuenow="{{ $doctor->rating() * 20 }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                <div class="progress-bar bg-warning text-dark"
                                     style="width: {{ $doctor->rating() * 20 }}%">
                                    {{ $doctor->rating() }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-10 d-flex flex-column">
                            <div class="card-body flex-grow-1">
                                <h3 class="card-title">{{ $doctor->fullName() }}</h3>
                                <h5>{{ $doctor->status }}</h5>
                                <h6>
                                    @if($doctor->min_price > 0)
                                        от {{ $doctor->min_price }}₽
                                    @endif
                                    @if($doctor->max_price > 0 && $doctor->max_price > $doctor->min_price)
                                        до {{ $doctor->max_price }}₽
                                    @endif
                                </h6>
                                <p class="card-text">{{ $doctor->address_job }}</p>
                            </div>
                            <!-- Кнопка внизу карточки -->
                            <a href="..."
                               class="btn btn-primary position-absolute doctor-card__button">
                                Посмотреть карточку врача <span class="bi bi-chevron-double-right"></span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            {{ $doctors->links('pagination::bootstrap-5') }}
        </div>
    </section>
@endsection
