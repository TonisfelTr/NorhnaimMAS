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
                <div class="card mb-3" style="max-width: 100%">
                    <div class="row g-0">
                        <div class="col-md-2">
                            <picture>
                                <source srcset="{{ asset('assets/images/backgrounds/feedback_placeholder.webp') }}"
                                        type="image/webp">
                                <img src="{{ asset('assets/images/backgrounds/feedback_placeholder.png') }}"
                                     class="img-fluid rounded-start" alt="Нет фотографии">
                            </picture>
                        </div>
                        <div class="col-md-10">
                            <div class="card-body">
                                <h5 class="card-title">{{ $clinic->name }}</h5>
                                <p class="card-text">{{ $clinic->description }}</p>
                                <div class="progress col-md-2" role="progressbar" aria-label="Отзывы" aria-valuenow="{{ $clinic->rating() * 20 }}" aria-valuemin="0" aria-valuemax="50">
                                    <div class="progress-bar bg-warning text-dark" style="width: {{ $clinic->rating() * 20 }}%">{{ $clinic->rating() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            {{ $clinics->links('pagination::bootstrap-5') }}
        </div>
    </section>
    <section class="advertisement">
        <div class="container p-5">

        </div>
    </section>
    <section class="doctors">
        <div class="container pt-5">
            <h1>Врачи</h1>
            @foreach ($doctors as $doctor)
                <div class="card mb-3" style="max-width: 100%">
                    <div class="row g-0">
                        <div class="col-md-2">
                            <picture>
                                <source srcset="{{ asset('assets/images/backgrounds/feedback_placeholder.webp') }}"
                                        type="image/webp">
                                <img src="{{ asset('assets/images/backgrounds/feedback_placeholder.png') }}"
                                     class="img-fluid rounded-start" alt="Нет фотографии">
                            </picture>
                        </div>
                        <div class="col-md-10">
                            <div class="card-body">
                                <h3 class="card-title">{{ $doctor->fullName() }}</h3>
                                <h5 class="pb-4">{{ $doctor->status }}</h5>
                                <p class="card-text">{{ $doctor->address_job }}</p>
                                <div class="progress col-md-2" role="progressbar" aria-label="Отзывы" aria-valuenow="{{ $doctor->rating() * 20 }}" aria-valuemin="0" aria-valuemax="50">
                                    <div class="progress-bar bg-warning text-dark" style="width: {{ $doctor->rating() * 20 }}%">{{ $doctor->rating() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            {{ $doctors->links('pagination::bootstrap-5') }}
        </div>
    </section>
@endsection
