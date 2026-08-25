@extends('layouts.welcome')
@section('title', 'Обратная связь')
@section('body_class', 'nh-public-page nh-contact-page')

@section('page_header')
    <section class="nh-public-hero">
        <div class="nh-public-shell">
            <div class="nh-public-kicker">Связь с Норхнеймом</div>
            <h1>Обратная связь</h1>
            <p>
                Вы можете связаться с нами для регистрации своей клиники, регистрации лекарств, либо по другим причинам.
                Просим заполнить форму ниже; на указанный адрес электронной почты придёт письмо с ответом об одобрении,
                либо отказе. Получить информацию о правилах регистрации препаратов и клиник, Вы можете ниже, под формой.
            </p>
        </div>
    </section>
@endsection

@section('main')
    <section class="nh-public-section">
        <div class="nh-public-shell nh-contact-grid">
            <div class="nh-form-card">
                <div class="nh-section-label"><span>Новое обращение</span></div>
                <form class="nh-form" enctype="multipart/form-data">
                    @csrf
                    @recaptcha
                    <label>
                        <span>Ваше имя</span>
                        <input class="form-control" name="full_name" type="text" placeholder="Ваше имя" required>
                    </label>
                    <label>
                        <span>Кто Вы?</span>
                        <select class="form-select" name="who" required>
                            <option value="" disabled selected>Кто Вы?</option>
                            <option value="1">Доктор</option>
                            <option value="2">Пациент</option>
                            <option value="3">Представитель компании</option>
                        </select>
                    </label>
                    <label>
                        <span>Адрес электронной почты</span>
                        <input class="form-control" name="email" type="email" placeholder="Адрес электронной почты" required>
                    </label>
                    <label>
                        <span>Сообщение</span>
                        <textarea class="form-control" name="feedback" rows="5" placeholder="Что Вас интересует? (необязательно)"></textarea>
                    </label>
                    <button class="nh-public-button nh-public-button--primary g-recaptcha" type="submit">
                        <i class="bi bi-mailbox-flag"></i> Отправить
                    </button>
                </form>
            </div>

            <aside class="nh-info-card">
                <div class="nh-info-card__icon"><i class="bi bi-clock"></i></div>
                <div class="nh-public-kicker">Как мы работаем?</div>
                <h2>Как мы работаем?</h2>
                <p>Все заявки обрабатываются в будние дни с 10:00 до 18:00 по московскому времени.</p>
                <p>Вы можете также отправить письмо на нашу электронную почту - <a href="mailto:reception@norhnaim.ru">reception@norhnaim.ru</a></p>
            </aside>
        </div>
    </section>

    <section class="nh-public-section nh-public-section--paper">
        <div class="nh-public-shell">
            <div class="nh-section-label"><span>Правила регистрации в системе</span></div>
            <div class="nh-rule-grid">
                <article class="nh-rule-card">
                    <div class="nh-rule-card__icon"><i class="bi bi-capsule"></i></div>
                    <h2>Лекарственные препараты</h2>
                    <p>
                        Своим клиентам, большинство из которых составляют пациенты, мы предоставляем возможность назначать только те препараты, которые
                        были одобрены Всемирной Организацией Здравоохранения и по показаниям, которые были обозначены к каждому конкретному препарату.
                        Более того, каждый регистрирующийся препарат должен иметь доказательную базу своей эффективности в виде закрытого плацебо-контролируемого исследования
                        по тому или иному диагнозу. Мы не позволяем назначать препараты <abbr aria-describedby="tooltip" data-title="Не по показаниям">офф-лейбл</abbr>, не считая
                        случаев, когда препарат должен быть назначен для симптоматического или паллиативного лечения.
                    </p>
                </article>
                <article class="nh-rule-card">
                    <div class="nh-rule-card__icon"><i class="bi bi-building-check"></i></div>
                    <h2>Для регистрации клиники</h2>
                    <ul>
                        <li>Действующую лицензию клиники о разрешении ведения медицинской деятельности;</li>
                        <li>ИНН;</li>
                        <li>Регистрационный номер клиники;</li>
                        <li>Номер лицензии на осуществление медицинской деятельности</li>
                        <li>Адрес</li>
                    </ul>
                </article>
                <article class="nh-rule-card">
                    <div class="nh-rule-card__icon"><i class="bi bi-person-badge"></i></div>
                    <h2>Для регистрации врачей</h2>
                    <ul>
                        <li>Номер диплома о высшем медицинском образовании;</li>
                        <li>Номер аккредитационной лицензии, для ординаторов - подтверждение нахождения в ординатуре;</li>
                        <li>Копии первой страницы паспорта с разворотом и копия страницы с пропиской;</li>
                        <li>Если врач является сотрудником клиники - копия трудовой книжки; если врач занимается частной практикой трудовая книжка не нужна;</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>
@endsection
