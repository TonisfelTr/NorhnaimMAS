@extends('layouts.welcome')
@section('title', 'Реестр лекарств')
@section('body_class', 'nh-public-page nh-medicines-page')

@section('page_header')
    <section class="nh-public-hero">
        <div class="nh-public-shell">
            <div class="nh-public-kicker">Лекарственный справочник</div>
            <h1>Зарегистрированные лекарства</h1>
            <p>
                Здесь перечислены зарегистрированные в системе лекарства. Вы можете найти нужное и почитать о нём, но не только инструкцию, но и
                информацию о их действенности, научные статьи о применении при различных расстройствах; также, здесь Вы можете узнать на какие
                рецепторы воздействует препарат.
            </p>
        </div>
    </section>
@endsection

@section('main')
    <section class="nh-public-section">
        <div class="nh-public-shell">
            <div class="nh-info-band">
                <div class="nh-info-band__icon"><i class="bi bi-capsule-pill"></i></div>
                <div>
                    <h2>Регистрация препарата</h2>
                    <p>
                        Если Вы являетесь представителем фармацевтической организации, которая хотела бы зарегистрировать свой препарат, свяжитесь с нами
                        в соответствующем разделе.
                    </p>
                </div>
                <a href="{{ route('main.feedback') }}" class="nh-public-button nh-public-button--outline">Связаться с нами</a>
            </div>

            <div class="nh-vue-panel">
                                <div id="medicine-table">
                    <medicine-table></medicine-table>
                </div>
            </div>
        </div>
    </section>
@endsection
