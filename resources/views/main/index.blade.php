@extends('layouts.welcome')

@section('title', 'Главная')
@section('body_class', 'nh-home-page')

@section('page_header')
    <section class="nh-hero">
        <div class="nh-shell">
            <div class="nh-hero__surface">
                <div class="nh-hero__content">
                    <div class="nh-hero__copy">
                        <div class="nh-brand-lockup nh-brand-lockup--hero">
                            <img
                                class="nh-brand-lockup__img"
                                src="{{ asset('assets/images/home/norhneim-viking-logo.png') }}"
                                alt="Норхнейм — клиническая система"
                                loading="eager"
                                fetchpriority="high"
                            >
                        </div>

                        <div class="nh-kicker">Спокойная цифровая среда для клинической практики</div>

                        <h1 class="nh-hero__title">
                            Психиатрическая практика.
                            <span>Спокойно и системно.</span>
                        </h1>

                        <p class="nh-hero__lead">
                            Инструменты для ведения пациента, анализа анамнеза,
                            работы с диагностическими критериями, назначениями
                            и медицинскими справочниками — в единой рабочей среде.
                        </p>

                        <div class="nh-hero__actions">
                            <a href="#nh-editorial" class="nh-btn nh-btn--primary">
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

                    <figure class="nh-hero__visual nh-hero__visual--large">
                        <img
                            src="{{ asset('assets/images/home/nordic-clinic.jpg') }}"
                            alt="Спокойное рабочее пространство в скандинавском стиле"
                            loading="eager"
                            fetchpriority="high"
                            onerror="this.parentElement.classList.add('is-fallback'); this.remove();"
                        >
                    </figure>
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
                        <div class="nh-principles__icon"><i class="bi bi-journal-text"></i></div>
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

        <section class="nh-editorial" id="nh-editorial">
            <div class="nh-shell nh-editorial__grid">
                <figure class="nh-editorial__visual">
                    <img
                        src="{{ asset('assets/images/home/editorial-soft.png') }}"
                        alt="Спокойное пространство для работы врача"
                        loading="lazy"
                        onerror="this.parentElement.classList.add('is-fallback'); this.remove();"
                    >
                </figure>

                <div class="nh-editorial__content">
                    <div class="nh-kicker">Создано для врачей</div>
                    <h2>Для ежедневной работы<br>в психиатрической практике</h2>
                    <p>
                        Норхнейм помогает структурировать информацию, принимать
                        обоснованные решения и сохранять время для самого важного —
                        для пациента.
                    </p>

                    <div class="nh-editorial__features">
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
        </section>

        <section class="nh-section nh-plans" id="nh-plans">
            <div class="nh-shell">
                <div class="nh-section-head">
                    <div>
                        <div class="nh-kicker">Тарифы</div>
                        <h2>Выберите подходящий сценарий работы.</h2>
                    </div>
                    <p>
                        Без игровых уровней и агрессивных бейджей. Разница между вариантами —
                        только в доступных рабочих инструментах.
                    </p>
                </div>

                <div class="nh-plan-grid">
                    @foreach($plans as $plan)
                        <article class="nh-plan {{ $plan['primary'] ? 'nh-plan--primary' : '' }}">
                            <div class="nh-plan__topline">{{ $plan['eyebrow'] }}</div>
                            <h3>{{ $plan['name'] }}</h3>
                            <p class="nh-plan__description">{{ $plan['description'] }}</p>
                            <div class="nh-plan__price">{{ $plan['price'] }} <small>/ месяц</small></div>

                            <ul>
                                @foreach($plan['features'] as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>

                            <a href="#" class="nh-btn {{ $plan['primary'] ? 'nh-btn--primary' : 'nh-btn--outline' }}">
                                {{ $plan['price'] === '0 ₽' ? 'Подробнее' : 'Попробовать' }}
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="nh-partners" id="nh-partners">
            <div class="nh-shell">
                <div class="nh-partners__head">
                    <div>
                        <div class="nh-kicker">Партнёры · демонстрационные данные</div>
                        <h2>Работаем в связке с клиниками и профильными организациями.</h2>
                    </div>
                    <p>
                        Сейчас здесь стоят тестовые названия. Позже этот блок можно подключить
                        к отдельной таблице партнёров без изменения шаблона страницы.
                    </p>
                </div>

                <div class="nh-partners__grid">
                    @foreach($partners as $partner)
                        <article class="nh-partner-card">
                            <div class="nh-partner-card__mark">{{ $partner['mark'] }}</div>
                            <div>
                                <strong>{{ $partner['name'] }}</strong>
                                <span>{{ $partner['type'] }}</span>
                            </div>
                        </article>
                    @endforeach
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
                        Стабильный список городов без случайной мозаики.
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

        <section class="nh-closing">
            <div class="nh-shell">
                <div class="nh-closing__inner">
                    <div>
                        <div class="nh-kicker">Норхнейм</div>
                        <h2>Рабочий инструмент для клинической практики.</h2>
                        <p>Начните с возможностей системы или перейдите к поиску клиники.</p>
                    </div>
                    <div class="nh-closing__actions">
                        <a href="#nh-editorial" class="nh-btn nh-btn--primary">Возможности</a>
                        <a href="#nh-plans" class="nh-btn nh-btn--ghost">Тарифы</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
