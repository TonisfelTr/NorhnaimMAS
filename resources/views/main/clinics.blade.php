@extends('layouts.welcome')
@section('title', 'Врачи и клиники')
@section('body_class', 'nh-public-page nh-directory-page nh-directory-page--nordic')

@section('page_header')
    @php
        $totalClinics = $clinics->total();
        $totalDoctors = $doctors->total();
    @endphp

    <section class="nordic-hero">
        <div class="nh-public-shell">
            <div class="nordic-hero__panel">
                <div class="nordic-hero__content">
                    <div class="nordic-kicker">Врачи и клиники</div>
                    <h1>Спокойный каталог клиник и специалистов в скандинавском стиле</h1>
                    <p>
                        Минималистичный, светлый и понятный интерфейс с акцентом на доверие,
                        комфорт и чистую подачу медицинской информации.
                    </p>

                    <div class="nordic-hero__stats">
                        <div class="nordic-stat">
                            <strong>{{ $totalClinics }}</strong>
                            <span>клиник</span>
                        </div>
                        <div class="nordic-stat">
                            <strong>{{ $totalDoctors }}</strong>
                            <span>врачей</span>
                        </div>
                    </div>
                </div>

                <div class="nordic-hero__visual" aria-hidden="true">
                    <div class="nordic-hero__visual-card">
                        <div class="nordic-hero__visual-badge">
                            <i class="bi bi-tree"></i>
                            <span>nordic care</span>
                        </div>

                        <img
                            src="{{ asset('assets/images/pages/nordic_clinic_psychiatry.png') }}"
                            alt=""
                            class="nordic-hero__image"
                            loading="eager"
                            fetchpriority="high"
                        >

                        <div class="nordic-hero__caption">
                            <strong>Скандинавская подача</strong>
                            <p>Воздух, мягкие оттенки, архитектурная простота и ощущение спокойствия.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('main')
    <style>
        .nh-directory-page--nordic {
            background:
                radial-gradient(circle at top right, rgba(225, 235, 232, 0.7), transparent 28%),
                linear-gradient(180deg, #f5f4ef 0%, #f1efea 100%);
            color: #25332f;
        }

        .nh-directory-page--nordic .nh-public-shell {
            position: relative;
            z-index: 2;
        }

        .nordic-hero {
            padding: 18px 0 30px;
        }

        .nordic-hero__panel {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(340px, 520px);
            gap: 34px;
            align-items: center;
            padding: 40px;
            border-radius: 34px;
            background: rgba(255,255,255,0.72);
            border: 1px solid rgba(52, 68, 62, 0.08);
            box-shadow: 0 24px 60px rgba(54, 62, 58, 0.08);
            backdrop-filter: blur(10px);
        }

        .nordic-kicker {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border-radius: 999px;
            background: #e8e3da;
            color: #62706b;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .nordic-hero h1 {
            margin: 0;
            font-size: clamp(2.25rem, 4.5vw, 4rem);
            line-height: 1.05;
            letter-spacing: -0.045em;
            color: #1f2b27;
            max-width: 780px;
        }

        .nordic-hero p {
            margin: 18px 0 0;
            max-width: 620px;
            color: #5d6d67;
            font-size: 1.04rem;
            line-height: 1.8;
        }

        .nordic-hero__stats {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .nordic-stat {
            min-width: 150px;
            padding: 18px 20px;
            border-radius: 22px;
            background: rgba(250, 248, 244, 0.92);
            border: 1px solid rgba(52, 68, 62, 0.08);
        }

        .nordic-stat strong {
            display: block;
            font-size: 2rem;
            line-height: 1;
            color: #23312d;
            font-weight: 700;
        }

        .nordic-stat span {
            display: block;
            margin-top: 8px;
            color: #74817b;
            font-size: 0.96rem;
        }

        .nordic-hero__visual-card {
            position: relative;
            overflow: hidden;
            border-radius: 28px;
            background: linear-gradient(180deg, #e8efec 0%, #dde6e1 100%);
            border: 1px solid rgba(52, 68, 62, 0.08);
            padding: 20px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.45);
        }

        .nordic-hero__visual-badge {
            position: absolute;
            top: 18px;
            left: 18px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.78);
            color: #5b6a65;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            z-index: 2;
        }

        .nordic-hero__image {
            display: block;
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            object-position: center;
            border-radius: 22px;
            box-shadow: 0 14px 30px rgba(49, 62, 56, 0.10);
        }

        .nordic-hero__caption {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 16px 6px 2px;
        }

        .nordic-hero__caption strong {
            color: #23312d;
            font-size: 1.04rem;
        }

        .nordic-hero__caption p {
            margin: 0;
            font-size: 0.96rem;
            line-height: 1.7;
            color: #62716c;
        }

        .nordic-section {
            padding: 14px 0 8px;
        }

        .nordic-section__head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
        }

        .nordic-section__head h2 {
            margin: 0;
            font-size: clamp(1.75rem, 3vw, 2.4rem);
            letter-spacing: -0.04em;
            color: #1f2b27;
        }

        .nordic-section__head p {
            margin: 10px 0 0;
            color: #6a7873;
            line-height: 1.75;
            max-width: 680px;
        }

        .nordic-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 11px 18px;
            border-radius: 999px;
            background: rgba(255,255,255,0.82);
            border: 1px solid rgba(52, 68, 62, 0.08);
            color: #60706a;
            font-weight: 600;
        }

        .nordic-pill strong {
            color: #23312d;
        }

        .nordic-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
        }

        .nordic-card {
            display: grid;
            grid-template-columns: minmax(230px, 250px) minmax(0, 1fr);
            border-radius: 28px;
            overflow: hidden;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(52, 68, 62, 0.08);
            box-shadow: 0 18px 40px rgba(54, 62, 58, 0.07);
            transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
        }

        .nordic-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 24px 54px rgba(54, 62, 58, 0.1);
            border-color: rgba(52, 68, 62, 0.14);
        }

        .nordic-card__media {
            position: relative;
            min-height: 100%;
            background: linear-gradient(135deg, #dae6e1 0%, #bfd1ca 100%);
        }

        .nordic-card__media img {
            display: block;
            width: 100%;
            height: 100%;
            min-height: 100%;
            object-fit: cover;
        }

        .nordic-card__body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 24px;
            padding: 28px;
        }

        .nordic-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }

        .nordic-chip {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            background: #edf1eb;
            color: #697872;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .nordic-rating {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #fbf7ef;
            border: 1px solid rgba(199, 174, 111, 0.24);
            color: #8b7446;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .nordic-card h3 {
            margin: 0 0 14px;
            font-size: 1.6rem;
            line-height: 1.24;
            letter-spacing: -0.035em;
            color: #20302b;
        }

        .nordic-address,
        .nordic-text,
        .nordic-clinic {
            margin: 0;
            color: #66746f;
            line-height: 1.8;
        }

        .nordic-address {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 14px;
        }

        .nordic-address i {
            margin-top: 4px;
            color: #8aa096;
        }

        .nordic-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 0 0 14px;
            padding: 0;
            list-style: none;
        }

        .nordic-meta li {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 16px;
            background: #f5f2ec;
            color: #66746f;
            font-size: 0.94rem;
        }

        .nordic-meta strong {
            color: #23312d;
            font-weight: 700;
        }

        .nordic-clinic a {
            color: #42554f;
            text-decoration: none;
            border-bottom: 1px solid rgba(66, 85, 79, 0.2);
            font-weight: 600;
        }

        .nordic-clinic a:hover {
            border-bottom-color: rgba(66, 85, 79, 0.55);
        }

        .nordic-actions {
            display: flex;
            justify-content: flex-start;
        }

        a.nordic-button,
        .nordic-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 19px;
            border-radius: 16px;
            background: #33453f;
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid transparent;
            box-shadow: 0 10px 24px rgba(51, 69, 63, 0.16);
            transition: transform 0.24s ease, background 0.24s ease, box-shadow 0.24s ease, color 0.24s ease;
        }

        .nh-directory-page--nordic a.nordic-button:hover,
        .nh-directory-page--nordic a.nordic-button:focus,
        .nh-directory-page--nordic a.nordic-button:active {
            color: #ffffff !important;
            background: #25352f;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(37, 53, 47, 0.18);
        }

        .nordic-divider {
            padding: 6px 0 8px;
        }

        .nordic-divider__inner {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 22px 26px;
            border-radius: 24px;
            background: rgba(255,255,255,0.72);
            border: 1px solid rgba(52, 68, 62, 0.08);
            color: #62716c;
        }

        .nordic-divider__inner i {
            color: #7e968d;
            font-size: 1.2rem;
        }

        .nordic-divider__inner p {
            margin: 0;
            line-height: 1.7;
        }

        .nordic-empty {
            text-align: center;
            padding: 52px 24px;
            border-radius: 28px;
            background: rgba(255,255,255,0.82);
            border: 1px solid rgba(52, 68, 62, 0.08);
            box-shadow: 0 18px 40px rgba(54, 62, 58, 0.06);
        }

        .nordic-empty i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            margin-bottom: 18px;
            border-radius: 22px;
            background: #edf1ec;
            color: #6c7c76;
            font-size: 1.75rem;
        }

        .nordic-empty h2 {
            margin: 0 0 12px;
            color: #20302b;
            letter-spacing: -0.03em;
        }

        .nordic-empty p {
            margin: 0;
            color: #66746f;
        }

        .nh-directory-page--nordic .nh-pagination {
            margin-top: 28px;
        }

        .nh-directory-page--nordic .pagination {
            gap: 8px;
        }

        .nh-directory-page--nordic .page-link {
            padding: 10px 14px;
            border-radius: 14px;
            border: 1px solid rgba(52, 68, 62, 0.08);
            background: rgba(255,255,255,0.82);
            color: #4b5d57;
            box-shadow: none;
        }

        .nh-directory-page--nordic .page-item.active .page-link {
            background: #33453f;
            border-color: #33453f;
            color: #fff;
        }

        .nh-directory-page--nordic .page-item.disabled .page-link {
            background: rgba(255,255,255,0.5);
            color: #a4b0ab;
        }

        @media (max-width: 1240px) {
            .nordic-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 1080px) {
            .nordic-hero__panel {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .nordic-card {
                grid-template-columns: 1fr;
            }

            .nordic-card__media {
                min-height: 280px;
            }
        }

        @media (max-width: 767.98px) {
            .nordic-hero {
                padding: 10px 0 18px;
            }

            .nordic-hero__panel,
            .nordic-card__body {
                padding: 22px;
            }

            .nordic-hero h1 {
                font-size: 2.2rem;
            }

            .nordic-section__head {
                flex-direction: column;
                align-items: flex-start;
            }

            .nordic-card__top {
                flex-direction: column;
                align-items: flex-start;
            }

            .nordic-meta {
                flex-direction: column;
            }
        }
    </style>

    @if ($clinics->isNotEmpty())
        <section class="nh-public-section nordic-section">
            <div class="nh-public-shell">
                <div class="nordic-section__head">
                    <div>
                        <h2>Клиники</h2>
                        <p>Сдержанные карточки с акцентом на атмосферу, адрес и основные преимущества клиники.</p>
                    </div>
                    <div class="nordic-pill">
                        <span>Всего</span>
                        <strong>{{ $clinics->total() }}</strong>
                    </div>
                </div>

                <div class="nordic-grid">
                    @foreach ($clinics as $clinic)
                        <article class="nordic-card">
                            <picture class="nordic-card__media">
                                <source srcset="{{ $clinic->getCoverWebpPhoto() ?? asset('assets/images/backgrounds/feedback_placeholder.webp') }}" type="image/webp">
                                <img src="{{ $clinic->coverPhoto() }}" alt="{{ $clinic->name }}" loading="lazy">
                            </picture>

                            <div class="nordic-card__body">
                                <div>
                                    <div class="nordic-card__top">
                                        <span class="nordic-chip">Клиника</span>
                                        @if($clinic->rating() > 0)
                                            <span class="nordic-rating"><i class="bi bi-star-fill"></i> {{ $clinic->rating() }}</span>
                                        @endif
                                    </div>

                                    <h3>{{ $clinic->name }}</h3>
                                    <p class="nordic-address"><i class="bi bi-geo-alt"></i><span>{{ $clinic->address }}</span></p>
                                    <p class="nordic-text">{{ Str::limit(strip_tags($clinic->description), 300) }}</p>
                                </div>

                                <div class="nordic-actions">
                                    <a href="{{ route('main.clinics.form', $clinic->id) }}" class="nordic-button">
                                        Карточка клиники <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="nh-pagination">{{ $clinics->links('pagination::bootstrap-5') }}</div>
            </div>
        </section>
    @endif

    @if ($doctors->isNotEmpty() && $clinics->isNotEmpty())
        <section class="nh-directory-transition nordic-divider">
            <div class="nh-public-shell">
                <div class="nordic-divider__inner">
                    <i class="bi bi-person-heart"></i>
                    <p>Если клиника выбрана, переходите к специалистам — интерфейс останется таким же чистым и спокойным.</p>
                </div>
            </div>
        </section>
    @endif

    @if ($doctors->isNotEmpty())
        <section class="nh-public-section nordic-section">
            <div class="nh-public-shell">
                <div class="nordic-section__head">
                    <div>
                        <h2>Врачи</h2>
                        <p>Карточки врачей с ключевыми параметрами: специализация, рейтинг, стаж, стоимость и место приёма.</p>
                    </div>
                    <div class="nordic-pill">
                        <span>Всего</span>
                        <strong>{{ $doctors->total() }}</strong>
                    </div>
                </div>

                <div class="nordic-grid">
                    @foreach ($doctors as $doctor)
                        @php($lowPrice = $doctor->lowestPrice())
                        <article class="nordic-card">
                            <picture class="nordic-card__media">
                                <source srcset="{{ $doctor->getWebpPhoto() }}" type="image/webp">
                                <img src="{{ $doctor->photo }}" alt="{{ $doctor->full_name }}" loading="lazy">
                            </picture>

                            <div class="nordic-card__body">
                                <div>
                                    <div class="nordic-card__top">
                                        <span class="nordic-chip">{{ $doctor->status }}</span>
                                        @if($doctor->rating() > 0)
                                            <span class="nordic-rating"><i class="bi bi-star-fill"></i> {{ $doctor->rating() }}</span>
                                        @endif
                                    </div>

                                    <h3>{{ $doctor->full_name }}</h3>

                                    <ul class="nordic-meta">
                                        @if($lowPrice)
                                            <li>
                                                <span>Стоимость</span>
                                                <strong>
                                                    от {{ $lowPrice }} ₽
                                                    @if($doctor->highPrice() > $lowPrice)
                                                        до {{ $doctor->highPrice() }} ₽
                                                    @endif
                                                </strong>
                                            </li>
                                        @endif

                                        @if($doctor->experience)
                                            <li>
                                                <span>Стаж</span>
                                                <strong>{{ $doctor->experience }}</strong>
                                            </li>
                                        @endif
                                    </ul>

                                    <p class="nordic-address"><i class="bi bi-geo-alt"></i><span>{{ $doctor->address_job }}</span></p>
                                    <p class="nordic-clinic"><a href="{{ route('main.clinics.form', $doctor->clinic_id) }}">{{ $doctor->clinic->name }}</a></p>
                                    <p class="nordic-text">{{ Str::limit(strip_tags($doctor->about), 260) }}</p>
                                </div>

                                <div class="nordic-actions">
                                    <a href="{{ route('main.doctors.form', $doctor->id) }}" class="nordic-button">
                                        Карточка врача <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="nh-pagination">{{ $doctors->links('pagination::bootstrap-5') }}</div>
            </div>
        </section>
    @endif

    @if ($doctors->isEmpty() && $clinics->isEmpty())
        <section class="nh-public-section nordic-section">
            <div class="nh-public-shell">
                <div class="nordic-empty">
                    <i class="bi bi-search"></i>
                    <h2>Ничего не найдено</h2>
                    <p>Попробуйте изменить параметры поиска или вернитесь позже.</p>
                </div>
            </div>
        </section>
    @endif
@endsection
