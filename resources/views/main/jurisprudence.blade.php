@extends('layouts.welcome')
@section('title', 'Юриспруденция')
@section('assets')
@endsection
@section('main')
    <section class="jurisprudence-header">
        <div class="container">
            <div class="row py-3">
                <div class="col-md-12">
                    <h1><span>Юриспруденция</span></h1>
                    <p>
                        Этот раздел создан для тех, кому нужна юридическая помощь для родственников больных и их самих.
                        Вы можете обратиться к юристам, которые всегда помогут Вам в тяжёлой ситуации.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="solutions">
        <div class="container">
            <div class="row py-4">
                <div class="col-md-12">
                    <h4 class="pb-5">
                        В нашей базе есть {{ $lawyers->total() }} {{ getPluralForm($lawyers->total(), 'юрист', 'юриста', 'юристов') }}
                    </h4>
                    <div class="row">
                        @foreach($lawyers->take(2) as $lawyer)
                            <div class="col-md-6">
                                <div class="Lawyer__card">
                                    <div class="row">
                                        <picture class="corner">
                                            <source srcset="{{ asset('assets/images/backgrounds/angle_placeholder.webp') }}" type="image/webp">
                                            <img src="{{ asset('assets/images/background/angle_placeholder.png') }}">
                                        </picture>
                                        <div class="Lawyer__card-author-block">
                                            <h3>{{ $lawyer->name }} {{ $lawyer->surname }}</h3>
                                            <p>{{ $lawyer->profession }}</p>
                                        </div>
                                    </div>
                                    <div class="row p-5 Lawyer__card-solutions">
                                        <div class="col-md-6">
                                            <h4>Занимается:</h4>
                                            <ul>
                                                @foreach(json_decode($lawyer->skills) as $skill)
                                                    <li>{{ $skill }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h4>Оплата</h4>
                                            <p class="font-35">{{ $lawyer->base_price }} <span>₽\приём*</span></p>
                                            <p class="text-danger font-12">* Стоимость услуг уточняйте у юриста.</p>
                                        </div>
                                    </div>
                                    <p class="Lawyer__card-description"><strong>Контактный номер:</strong> {{ $lawyer->phone }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($lawyers->total() > 2)
        <section class="advertisment">
            <div class="container-fluid position-relative">
                <picture>
                    <source srcset="{{ asset('assets/images/backgrounds/jurisprudence.webp') }}" type="image/webp">
                    <img src="{{ asset('assets/images/backgrounds/jurisprudence.png') }}" loading="lazy" class="w-100">
                </picture>
                <div class="overlay-text">
                    <p>Мы помогаем вам решать юридические вопросы с комфортом и профессионализмом.</p>
                </div>
            </div>
        </section>

        <section class="solutions">
            <div class="container">
                <div class="row py-4">
                    <div class="col-md-12">
                        @foreach($lawyers->slice(2) as $index => $lawyer)
                            @if($index % 2 == 0)
                                <div class="row pt-4">
                                    @endif
                                    <div class="col-md-6">
                                        <div class="Lawyer__card">
                                            <div class="row">
                                                <picture class="corner">
                                                    <source srcset="{{ asset('assets/images/backgrounds/angle_placeholder.webp') }}" type="image/webp">
                                                    <img src="{{ asset('assets/images/backgrounds/angle_placeholder.png') }}">
                                                </picture>
                                                <div class="Lawyer__card-author-block">
                                                    <h3>{{ $lawyer->name }} {{ $lawyer->surname }}</h3>
                                                    <p>{{ $lawyer->profession }}</p>
                                                </div>
                                            </div>
                                            <div class="row p-5 Lawyer__card-solutions">
                                                <div class="col-md-6">
                                                    <h4>Занимается:</h4>
                                                    <ul>
                                                        @foreach(json_decode($lawyer->skills) as $skill)
                                                            <li>{{ $skill }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <h4>Оплата</h4>
                                                    <p class="font-35">{{ $lawyer->base_price }} <span>₽\приём*</span></p>
                                                    <p class="text-danger font-12">* Стоимость услуг уточняйте у юриста.</p>
                                                </div>
                                            </div>
                                            <p class="Lawyer__card-description"><strong>Контактный номер:</strong> {{ $lawyer->phone }}</p>
                                        </div>
                                    </div>
                                    @if($index % 2 == 1 || $loop->last)
                                </div>
                            @endif
                        @endforeach

                        <div class="pagination pt-5 d-flex justify-content-center">
                            {{ $lawyers->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="feedback">

    </section>
@endsection
