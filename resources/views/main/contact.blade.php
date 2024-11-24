@extends('layouts.welcome')
@section('title', 'Обратная связь')
@section('main')
    <section class="main">
        <div class="container">
            <h3 class="block__header">Обратная связь</h3>
            <div class="block__body">
                <div class="block__text">
                    Вы можете связаться с нами для регистрации своей клиники, регистрации лекарств, либо по другим причинам.
                    Просим заполнить форму ниже; на указанный адрес электронной почты придёт письмо с ответом об одобрении,
                    либо отказе. Получить информацию о правилах регистрации препаратов и клиник, Вы можете ниже, под формой.
                </div>
            </div>
        </div>
    </section>
    <section class="feedback">
        <div class="container">
            <div class="d-inline-flex">
                <div class="col-sm-12 col-md-6 col-lg-7 col-xl-7 p-4">
                    <form class="vertical-input-group" enctype="multipart/form-data">
                        <div class="container">
                            @csrf
                            @recaptcha
                            <input class="form-control" name="full_name" type="text" placeholder="Ваше имя" required>
                            <select class="form-control" name="who" required>
                                <option value="" disabled selected>Кто Вы?</option>
                                <option value="1">Доктор</option>
                                <option value="2">Пациент</option>
                                <option value="3">Представитель компании</option>
                            </select>
                            <input class="form-control" name="email" type="email" placeholder="Адрес электронной почты" required>
                            <textarea class="form-control" name="feedback" placeholder="Что Вас интересует? (необязательно)"></textarea>
                        </div>
                        <div class="d-inline-flex justify-content-right p-2 pt-3">
                            <button class="btn btn-dark g-recaptcha" type="submit"><i class="bi bi-mailbox-flag"></i> Отправить</button>
                        </div>
                    </form>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-7 col-xl-7 pt-4">
                    <div class="subblock__body">
                        <h4 class="subblock__header">Как мы работаем?</h4>
                        <div class="subblock_body">
                            Все заявки обрабатываются в будние дни с 10:00 до 18:00 по московскому времени.
                            <p>Вы можете также отправить письмо на нашу электронную почту - <a href="mailto:reception@norhnaim.ru">reception@norhnaim.ru</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="rules">
        <div class="container">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <h4 class="subblock__header">Правила регистрации в системе</h4>
                <div class="block__body">
                    <div class="block__text">
                        Своим клиентам, большинство из которых составляют пациенты, мы предоставляем возможность назначать только те препараты, которые
                        были одобрены Всемирной Организацией Здравоохранения и по показаниям, которые были обозначены к каждому конкретному препарату.
                        Более того, каждый регистрирующийся препарат должен иметь доказательную базу своей эффективности в виде закрытого плацебо-контролируемого исследования
                        по тому или иному диагнозу. Мы не позволяем назначать препараты <abbr aria-describedby="tooltip" data-title="Не по показаниям">офф-лейбл</abbr>, не считая
                        случаев, когда препарат должен быть назначен для симптоматического или паллиативного лечения.
                    </div>
                    <div class="block__text">
                        Для регистрации клиники нашей системе необходимо иметь следующее:
                        <ul>
                            <li>Действующую лицензию клиники о разрешении ведения медицинской деятельности;</li>
                            <li>ИНН;</li>
                            <li>Регистрационный номер клиники;</li>
                            <li>Номер лицензии на осуществление медицинской деятельности</li>
                            <li>Адрес</li>
                        </ul>
                        Для регистрации врачей в нашей системе необходимо предоставить:
                        <ul>
                            <li>Номер диплома о высшем медицинском образовании;</li>
                            <li>Номер аккредитационной лицензии, для ординаторов - подтверждение нахождения в ординатуре;</li>
                            <li>Копии первой страницы паспорта с разворотом и копия страницы с пропиской;</li>
                            <li>Если врач является сотрудником клиники - копия трудовой книжки; если врач занимается частной практикой трудовая книжка не нужна;</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
