@extends('layouts.welcome')
@section('title', 'Реестр лекарств')
@section('main')
<section class="main">
    <div class="container">
        <h3 class="block__header">Зарегистрированные лекарства</h3>
        <div class="block__body">
            <div class="block__text">
                Здесь перечислены зарегистрированные в системе лекарства. Вы можете найти нужное и почитать о нём, но не только инструкцию, но и
                информацию о их действенности, научные статьи о применении при различных расстройствах; также, здесь Вы можете узнать на какие
                рецепторы воздействует препарат.
            </div>
            <div class="block__text">
                Если Вы являетесь представителем фармацевтической организации, которая хотела бы зарегистрировать свой препарат, свяжитесь с нами
                в соответствующем разделе.
            </div>
        </div>
    </div>
</section>
<section class="medicine-table">
    <div class="container">
        <h3 class="block__header">Таблица препаратов</h3>
        <div id="medicine-table">
            <medicine-table></medicine-table>
        </div>
    </div>
</section>
@endsection
