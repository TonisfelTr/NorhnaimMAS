@extends('layouts.welcome')

@section('title', 'Главная')
@section('body_class', 'nh-home-page')

@section('page_header')
    <section class="nh-hero">
        <div class="nh-shell">
            <div class="nh-hero__surface">
                <div class="nh-hero__content">
                    <div class="nh-hero__copy">
                        <div
                            class="nh-hero__brand-logo"
                            role="img"
                            aria-label="Норхнейм — клиническая система"
                        ></div>

                        <h1 class="nh-hero__title">
                            Психиатрическая практика.<br>
                            <span>Спокойно и системно.</span>
                        </h1>

                        <p class="nh-hero__lead">
                            Инструменты для ведения пациента, анализа анамнеза, работы с диагностическими
                            критериями, назначениями и медицинскими справочниками — в единой рабочей среде.
                        </p>

                        <div class="nh-hero__actions">
                            <a href="#nh-capabilities" class="nh-btn nh-btn--primary">
                                Возможности системы
                                <i class="bi bi-arrow-down-right"></i>
                            </a>
                            <a href="#nh-cities" class="nh-btn nh-btn--ghost">Найти клинику</a>
                        </div>

                        <div class="nh-hero__meta" aria-label="Ключевые особенности">
                            <span><i class="bi bi-check2"></i> Критерии МКБ-10</span>
                            <span><i class="bi bi-check2"></i> Панель врача</span>
                            <span><i class="bi bi-check2"></i> Клинические справочники</span>
                        </div>
                    </div>

                    <figure
                        class="nh-hero__visual--large nh-hero__visual--hero"
                        role="img"
                        aria-label="Светлый скандинавский интерьер для спокойной клинической практики"
                    ></figure>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('main')

    <div class="nh-home">
        <section class="nh-principles" aria-label="Принципы системы">
            <div class="nh-shell">
                <div class="nh-principles__grid">
                    <article>
                        <div class="nh-principles__icon"><i class="bi bi-clipboard2-check"></i></div>
                        <div>
                            <strong>Клиническая логика</strong>
                            <p>Интерфейс следует привычному пути работы с пациентом, а не заставляет изучать новый процесс.</p>
                        </div>
                        <span>01</span>
                    </article>

                    <article>
                        <div class="nh-principles__icon"><i class="bi bi-database"></i></div>
                        <div>
                            <strong>Один контекст</strong>
                            <p>Анамнез, диагностика, назначения и справочные данные собраны в одной системе.</p>
                        </div>
                        <span>02</span>
                    </article>

                    <article>
                        <div class="nh-principles__icon"><i class="bi bi-check2-circle"></i></div>
                        <div>
                            <strong>Минимум отвлечений</strong>
                            <p>Спокойная визуальная среда помогает сосредоточиться на клинической задаче.</p>
                        </div>
                        <span>03</span>
                    </article>
                </div>
            </div>
        </section>

        <section class="nh-editorial nh-editorial--doctor" aria-labelledby="nh-doctor-title">
            <div class="nh-shell">
                <div class="nh-editorial__grid">
                    <figure
                        class="nh-editorial__visual nh-editorial__visual--doctor"
                        role="img"
                        aria-label="Спокойное рабочее пространство врача"
                    ></figure>

                    <div class="nh-editorial__content">
                        <div class="nh-kicker">Создано для врачей</div>
                        <h2 id="nh-doctor-title">Для ежедневной работы в психиатрической практике</h2>
                        <p>
                            Норхнейм помогает структурировать информацию, принимать обоснованные решения
                            и сохранять время для самого важного — для пациента.
                        </p>

                        <div class="nh-editorial__features" aria-label="Возможности для врача">
                            <article>
                                <div class="nh-editorial__feature-icon"><i class="bi bi-folder2-open"></i></div>
                                <span>Анамнез<br>и документы</span>
                            </article>
                            <article>
                                <div class="nh-editorial__feature-icon"><i class="bi bi-activity"></i></div>
                                <span>Диагностика<br>и критерии</span>
                            </article>
                            <article>
                                <div class="nh-editorial__feature-icon"><i class="bi bi-capsule"></i></div>
                                <span>Назначения<br>и препараты</span>
                            </article>
                            <article>
                                <div class="nh-editorial__feature-icon"><i class="bi bi-book"></i></div>
                                <span>Справочники<br>и статьи</span>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="nh-patient" aria-labelledby="nh-patient-title">
            <div class="nh-shell">
                <div class="nh-patient__grid">
                    <div class="nh-patient__content">
                        <div class="nh-kicker">Создано для пациентов</div>
                        <h2 id="nh-patient-title">Ваше лечение —<br>всегда под рукой</h2>
                        <p class="nh-patient__lead">
                            Личный кабинет Норхнейм собирает важную информацию о лечении в одном месте:
                            медицинскую карту, назначения, результаты, документы и назначенные врачом тестирования.
                            Нужное не теряется между чатами, файлами и бумажными заключениями.
                        </p>

                        <div class="nh-patient__features" aria-label="Возможности личного кабинета пациента">
                            <article class="nh-patient-feature">
                                <div class="nh-patient-feature__icon"><i class="bi bi-person-vcard"></i></div>
                                <div>
                                    <strong>Медицинская карта</strong>
                                    <span>История лечения и заключения</span>
                                </div>
                            </article>

                            <article class="nh-patient-feature">
                                <div class="nh-patient-feature__icon"><i class="bi bi-capsule-pill"></i></div>
                                <div>
                                    <strong>Назначения</strong>
                                    <span>Препараты и рекомендации врача</span>
                                </div>
                            </article>

                            <article class="nh-patient-feature">
                                <div class="nh-patient-feature__icon"><i class="bi bi-ui-checks-grid"></i></div>
                                <div>
                                    <strong>Тесты и результаты</strong>
                                    <span>Назначенные тестирования в кабинете</span>
                                </div>
                            </article>

                            <article class="nh-patient-feature">
                                <div class="nh-patient-feature__icon"><i class="bi bi-chat-square-heart"></i></div>
                                <div>
                                    <strong>Консультации</strong>
                                    <span>История обращений и связь со специалистом</span>
                                </div>
                            </article>
                        </div>
                    </div>

                    <figure
                        class="nh-patient__visual"
                        role="img"
                        aria-label="Личный кабинет пациента Норхнейм на планшете в светлом скандинавском интерьере"
                    ></figure>
                </div>
            </div>
        </section>

        <section class="nh-patient-cabinet" id="nh-patient-cabinet" aria-labelledby="nh-patient-cabinet-title">
            <div class="nh-shell">
                <div class="nh-patient-cabinet__head">
                    <div>
                        <div class="nh-kicker">Личный кабинет пациента</div>
                        <h2 id="nh-patient-cabinet-title">Всё важное о лечении — в одном пространстве.</h2>
                    </div>
                    <p>
                        Кабинет помогает видеть текущий план лечения, возвращаться к предыдущим данным
                        и понимать, какие действия назначены дальше.
                    </p>
                </div>

                <div class="nh-patient-cabinet__grid">
                    <article class="nh-patient-card">
                        <span>01</span>
                        <div class="nh-patient-card__icon"><i class="bi bi-person-vcard"></i></div>
                        <h3>Моя медицинская карта</h3>
                        <p>Анамнезы, заключения, история обращений и динамика лечения собраны в одном месте.</p>
                    </article>

                    <article class="nh-patient-card">
                        <span>02</span>
                        <div class="nh-patient-card__icon"><i class="bi bi-prescription2"></i></div>
                        <h3>Назначения</h3>
                        <p>Актуальные препараты, схема приёма, рекомендации и выписанные врачом рецепты.</p>
                    </article>

                    <article class="nh-patient-card">
                        <span>03</span>
                        <div class="nh-patient-card__icon"><i class="bi bi-ui-checks"></i></div>
                        <h3>Тестирования</h3>
                        <p>Назначенные врачом тесты, прохождение в личном кабинете и сохранённые результаты.</p>
                    </article>

                    <article class="nh-patient-card">
                        <span>04</span>
                        <div class="nh-patient-card__icon"><i class="bi bi-clipboard2-pulse"></i></div>
                        <h3>Исследования</h3>
                        <p>Результаты анализов и исследований рядом с остальной клинической информацией.</p>
                    </article>

                    <article class="nh-patient-card">
                        <span>05</span>
                        <div class="nh-patient-card__icon"><i class="bi bi-file-earmark-medical"></i></div>
                        <h3>Документы</h3>
                        <p>Заключения, направления, рецепты и другие медицинские документы в личном архиве.</p>
                    </article>

                    <article class="nh-patient-card">
                        <span>06</span>
                        <div class="nh-patient-card__icon"><i class="bi bi-chat-square-text"></i></div>
                        <h3>Консультации</h3>
                        <p>Текущие и завершённые обращения к специалистам без потери контекста лечения.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="nh-section nh-capabilities" id="nh-capabilities">
            <div class="nh-shell">
                <div class="nh-section-head">
                    <div>
                        <div class="nh-kicker">Возможности</div>
                        <h2>Основные инструменты — без лишних уровней интерфейса.</h2>
                    </div>
                    <p>
                        Норхнейм объединяет ключевые задачи специалиста в понятную рабочую среду.
                        Каждый раздел решает конкретную задачу и не конкурирует за внимание.
                    </p>
                </div>

                <div class="nh-feature-grid">
                    <article class="nh-feature nh-feature--large">
                        <div class="nh-feature__icon"><i class="bi bi-clipboard2-heart"></i></div>
                        <div>
                            <span class="nh-feature__number">01</span>
                            <h3>Диагностика</h3>
                            <p>Тесты, анамнез, симптомы и диагностические критерии МКБ-10 в одном клиническом контексте.</p>
                        </div>
                    </article>

                    <article class="nh-feature nh-feature--large">
                        <div class="nh-feature__icon"><i class="bi bi-capsule-pill"></i></div>
                        <div>
                            <span class="nh-feature__number">02</span>
                            <h3>Лечение и назначения</h3>
                            <p>Ведение назначений, рецепты, лекарственные формы и поддержка медикаментозного подбора.</p>
                        </div>
                    </article>

                    <article class="nh-feature">
                        <div class="nh-feature__icon"><i class="bi bi-building"></i></div>
                        <div>
                            <span class="nh-feature__number">03</span>
                            <h3>Клиники и врачи</h3>
                            <p>Поиск специалистов и клиник с привязкой к городу.</p>
                        </div>
                    </article>

                    <article class="nh-feature">
                        <div class="nh-feature__icon"><i class="bi bi-journal-medical"></i></div>
                        <div>
                            <span class="nh-feature__number">04</span>
                            <h3>Библиотека</h3>
                            <p>Статьи, исследования и профессиональные материалы.</p>
                        </div>
                    </article>

                    <article class="nh-feature">
                        <div class="nh-feature__icon"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <span class="nh-feature__number">05</span>
                            <h3>Правовые материалы</h3>
                            <p>Справочная информация по законодательству в сфере психиатрической помощи.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="nh-section nh-cities" id="nh-cities">
            <div class="nh-shell">
                <div class="nh-section-head">
                    <div>
                        <div class="nh-kicker">Клиники</div>
                        <h2>Найдите специалиста в своём городе.</h2>
                    </div>
                    <p>
                        Вместо случайно меняющейся фотомозаики — стабильный список городов.
                        Пользователь сразу понимает, куда нажать и что произойдёт дальше.
                    </p>
                </div>

                <div class="nh-city-list">
                    @foreach($cities as $city)
                        <a href="{{ route('main.clinics.filters.city', $city['slug']) }}" class="nh-city-list__item">
                            <span>{{ $city['name'] }}</span>
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="nh-quote-section">
            <div class="nh-shell">
                <div class="nh-quote">
                    <span class="nh-quote__label">Подход</span>
                    <p>
                        Хорошая медицинская система не пытается удивить врача.
                        <strong>Она освобождает его внимание для пациента.</strong>
                    </p>
                </div>
            </div>
        </section>

        <section class="nh-section nh-plans" id="nh-plans">
            <div class="nh-shell">
                <div class="nh-section-head">
                    <div>
                        <div class="nh-kicker">Доступ</div>
                        <h2>Понятные варианты для разных сценариев работы.</h2>
                    </div>
                    <p>
                        Без «лучшего выбора», игровых названий и визуального давления.
                        Сравнивайте планы по содержанию и выбирайте подходящий сценарий.
                    </p>
                </div>

                <div class="nh-plan-grid">
                    <article class="nh-plan">
                        <div class="nh-plan__topline">Ознакомление</div>
                        <h3>Гостевой</h3>
                        <p class="nh-plan__description">Для первого знакомства с системой.</p>
                        <div class="nh-plan__price">0 ₽ <small>/ месяц</small></div>
                        <ul>
                            <li>Презентация возможностей</li>
                            <li>Диагностический раздел</li>
                            <li>Панель пациента</li>
                        </ul>
                        <a href="#nh-capabilities" class="nh-btn nh-btn--outline">Подробнее</a>
                    </article>

                    <article class="nh-plan nh-plan--primary">
                        <div class="nh-plan__topline">Клиническая работа</div>
                        <h3>Для врача</h3>
                        <p class="nh-plan__description">Рабочий набор для специалиста, ведущего пациентов.</p>
                        <div class="nh-plan__price">900 ₽ <small>/ месяц</small></div>
                        <ul>
                            <li>Врачебная панель</li>
                            <li>Ведение анамнезов</li>
                            <li>Поддержка диагностики</li>
                            <li>Выписка рецептов</li>
                            <li>Медикаментозный подбор</li>
                        </ul>
                        <a href="#" class="nh-btn nh-btn--primary">Попробовать</a>
                    </article>

                    <article class="nh-plan">
                        <div class="nh-plan__topline">Персональный доступ</div>
                        <h3>Для пациента</h3>
                        <p class="nh-plan__description">Персональные функции и доступ к профильной помощи.</p>
                        <div class="nh-plan__price">1100 ₽ <small>/ месяц</small></div>
                        <ul>
                            <li>Панель пациента</li>
                            <li>Диагностические инструменты</li>
                            <li>Медикаментозный раздел</li>
                            <li>Консультация с врачом</li>
                            <li>Правовые материалы</li>
                        </ul>
                        <a href="#" class="nh-btn nh-btn--outline">Попробовать</a>
                    </article>

                    <article class="nh-plan">
                        <div class="nh-plan__topline">Профессиональный доступ</div>
                        <h3>Расширенный</h3>
                        <p class="nh-plan__description">Для углублённой работы с профессиональными материалами.</p>
                        <div class="nh-plan__price">1600 ₽ <small>/ месяц</small></div>
                        <ul>
                            <li>Врачебная панель</li>
                            <li>Правовые материалы</li>
                            <li>Библиотека научных статей</li>
                            <li>Расширенные разделы системы</li>
                        </ul>
                        <a href="#" class="nh-btn nh-btn--outline">Попробовать</a>
                    </article>
                </div>
            </div>
        </section>

        <section class="nh-closing">
            <div class="nh-shell">
                <div class="nh-closing__inner">
                    <div>
                        <div class="nh-kicker">Норхнейм</div>
                        <h2>Рабочий инструмент для клинической практики.</h2>
                        <p>Начните с возможностей системы или перейдите к поиску клиники.</p>
                    </div>
                    <div class="nh-closing__actions">
                        <a href="#nh-capabilities" class="nh-btn nh-btn--primary">Возможности</a>
                        <a href="#nh-cities" class="nh-btn nh-btn--ghost">Выбрать город</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
