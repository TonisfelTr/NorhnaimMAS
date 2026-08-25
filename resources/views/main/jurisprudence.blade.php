@extends('layouts.welcome')
@section('title', 'Юридическая поддержка в психиатрии')
@section('body_class', 'nh-public-page nh-law-page nh-law-page--nordic')

@section('page_header')
    <section class="psy-law-hero">
        <div class="nh-public-shell">
            <div class="psy-law-hero__panel">
                <div class="psy-law-hero__content">
                    <div class="psy-law-kicker">
                        <i class="bi bi-shield-heart"></i>
                        <span>Правовая поддержка</span>
                    </div>

                    <h1>Юридическая помощь в вопросах психиатрии — спокойно и по существу</h1>

                    <p class="psy-law-hero__lead">
                        Для пациентов, их близких и законных представителей. Здесь можно найти специалиста,
                        который поможет разобраться в сложной ситуации бережно, конфиденциально и без лишнего давления.
                    </p>

                    <div class="psy-law-hero__topics" aria-label="Основные направления">
                        <span><i class="bi bi-lock"></i> Конфиденциальность</span>
                        <span><i class="bi bi-file-earmark-text"></i> Документы и права</span>
                        <span><i class="bi bi-people"></i> Поддержка семьи</span>
                    </div>

                    <div class="psy-law-hero__stat">
                        <strong>{{ $lawyers->total() }}</strong>
                        <span>{{ getPluralForm($lawyers->total(), 'юрист', 'юриста', 'юристов') }} в каталоге</span>
                    </div>
                </div>

                <div class="psy-law-hero__visual" aria-hidden="true">
                    <div class="psy-law-illustration">
                        <div class="psy-law-illustration__tag">
                            <span class="psy-law-illustration__dot"></span>
                            nordic mental health law
                        </div>

                        <img
                            src="{{ asset('assets/images/pages/nordic_psychiatry_legal.png') }}"
                            alt=""
                            class="psy-law-illustration__image"
                            loading="eager"
                            fetchpriority="high"
                        >

                        <div class="psy-law-illustration__caption">
                            <strong>Права, диалог, защита</strong>
                            <span>Юридическая помощь без лишнего визуального шума и стигматизации.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('main')
    <style>
        .nh-law-page--nordic {
            background:
                radial-gradient(circle at 14% 8%, rgba(228, 235, 231, .7), transparent 28%),
                radial-gradient(circle at 92% 18%, rgba(230, 224, 215, .55), transparent 24%),
                linear-gradient(180deg, #f5f3ee 0%, #f2f0eb 100%);
            color: #26342f;
        }

        .psy-law-hero {
            padding: 18px 0 34px;
        }

        .psy-law-hero__panel {
            display: grid;
            grid-template-columns: minmax(0, 1.12fr) minmax(380px, .88fr);
            gap: 38px;
            align-items: center;
            padding: 42px;
            border-radius: 36px;
            background: rgba(255, 255, 255, .76);
            border: 1px solid rgba(45, 61, 55, .08);
            box-shadow: 0 24px 64px rgba(57, 65, 61, .08);
            backdrop-filter: blur(12px);
        }

        .psy-law-kicker {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 18px;
            padding: 8px 14px;
            border-radius: 999px;
            background: #e9e5dd;
            color: #66736e;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .psy-law-kicker i {
            color: #789087;
        }

        .psy-law-hero h1 {
            margin: 0;
            max-width: 800px;
            color: #1f2c27;
            font-size: clamp(2.35rem, 4.3vw, 4.25rem);
            line-height: 1.05;
            letter-spacing: -.048em;
        }

        .psy-law-hero__lead {
            max-width: 690px;
            margin: 20px 0 0;
            color: #5e6e68;
            font-size: 1.05rem;
            line-height: 1.82;
        }

        .psy-law-hero__topics {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 26px;
        }

        .psy-law-hero__topics span {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 10px 14px;
            border-radius: 16px;
            background: #f5f2ec;
            border: 1px solid rgba(45, 61, 55, .07);
            color: #5e6d68;
            font-size: .93rem;
        }

        .psy-law-hero__topics i {
            color: #7c938a;
        }

        .psy-law-hero__stat {
            display: inline-flex;
            align-items: baseline;
            gap: 10px;
            margin-top: 28px;
            padding: 16px 19px;
            border-radius: 20px;
            background: rgba(236, 241, 238, .78);
            border: 1px solid rgba(45, 61, 55, .07);
        }

        .psy-law-hero__stat strong {
            color: #26362f;
            font-size: 2rem;
            line-height: 1;
        }

        .psy-law-hero__stat span {
            color: #697771;
            font-size: .94rem;
        }

        .psy-law-illustration {
            position: relative;
            overflow: hidden;
            padding: 18px;
            border-radius: 30px;
            background: #e3ebe7;
            border: 1px solid rgba(45, 61, 55, .08);
        }

        .psy-law-illustration__tag {
            position: absolute;
            top: 18px;
            left: 18px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.82);
            color: #68766f;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            box-shadow: 0 8px 20px rgba(51, 63, 58, .06);
        }

        .psy-law-illustration__dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #7e9a8e;
        }

        .psy-law-illustration__image {
            display: block;
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            object-position: center;
            border-radius: 24px;
            box-shadow: 0 14px 30px rgba(49, 62, 56, .10);
        }

        .psy-law-illustration__caption {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 16px 6px 4px;
        }

        .psy-law-illustration__caption strong {
            color: #26352f;
            font-size: 1.06rem;
        }

        .psy-law-illustration__caption span {
            color: #68766f;
            line-height: 1.65;
            font-size: .94rem;
        }

        .psy-law-section {
            padding: 22px 0 10px;
        }

        .psy-law-section__head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .psy-law-section__head h2 {
            margin: 0;
            color: #1f2c27;
            font-size: clamp(1.8rem, 3vw, 2.55rem);
            letter-spacing: -.04em;
        }

        .psy-law-section__head p {
            max-width: 690px;
            margin: 10px 0 0;
            color: #68766f;
            line-height: 1.75;
        }

        .psy-law-count {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            white-space: nowrap;
            padding: 11px 17px;
            border-radius: 999px;
            background: rgba(255,255,255,.8);
            border: 1px solid rgba(45, 61, 55, .08);
            color: #66746e;
            font-weight: 600;
        }

        .psy-law-count strong {
            color: #26352f;
        }

        .psy-law-principles {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .psy-law-principle {
            display: grid;
            grid-template-columns: 58px minmax(0, 1fr);
            gap: 16px;
            align-items: center;
            padding: 18px;
            border-radius: 22px;
            background: rgba(255,255,255,.7);
            border: 1px solid rgba(45,61,55,.07);
        }

        .psy-law-principle__visual {
            display: grid;
            place-items: center;
            width: 58px;
            height: 58px;
            border-radius: 18px;
            background: #e7eee9;
            color: #768f84;
            font-size: 1.35rem;
        }

        .psy-law-principle strong {
            display: block;
            margin-bottom: 4px;
            color: #2a3833;
            font-size: .98rem;
        }

        .psy-law-principle span {
            display: block;
            color: #71807a;
            font-size: .88rem;
            line-height: 1.5;
        }

        .psy-lawyer-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .psy-lawyer-card {
            display: flex;
            flex-direction: column;
            min-height: 100%;
            padding: 26px;
            border-radius: 28px;
            background: rgba(255,255,255,.9);
            border: 1px solid rgba(45,61,55,.08);
            box-shadow: 0 18px 42px rgba(55, 64, 60, .07);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }

        .psy-lawyer-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 54px rgba(55,64,60,.1);
            border-color: rgba(45,61,55,.14);
        }

        .psy-lawyer-card__header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(45,61,55,.08);
        }

        .psy-lawyer-card__avatar {
            display: grid;
            place-items: center;
            flex: 0 0 66px;
            width: 66px;
            height: 66px;
            border-radius: 22px;
            background: linear-gradient(145deg, #dbe5df 0%, #c9d7d0 100%);
            color: #425750;
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: .02em;
            border: 1px solid rgba(45,61,55,.06);
        }

        .psy-lawyer-card__identity {
            min-width: 0;
        }

        .psy-lawyer-card__identity h3 {
            margin: 0;
            color: #22312c;
            font-size: 1.35rem;
            line-height: 1.25;
            letter-spacing: -.025em;
        }

        .psy-lawyer-card__identity p {
            margin: 6px 0 0;
            color: #6c7a74;
            line-height: 1.5;
        }

        .psy-lawyer-card__body {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 22px;
            flex: 1;
            padding: 22px 0;
        }

        .psy-lawyer-card__label {
            display: block;
            margin-bottom: 12px;
            color: #8a9691;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .psy-lawyer-card__skills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .psy-lawyer-card__skills li {
            padding: 8px 11px;
            border-radius: 13px;
            background: #f2f4f0;
            border: 1px solid rgba(45,61,55,.06);
            color: #5f6e68;
            font-size: .9rem;
            line-height: 1.35;
        }

        .psy-lawyer-card__price {
            align-self: start;
            min-width: 148px;
            padding: 16px;
            border-radius: 18px;
            background: #f5f2ec;
            border: 1px solid rgba(45,61,55,.06);
        }

        .psy-lawyer-card__price span,
        .psy-lawyer-card__price small {
            display: block;
            color: #78857f;
            font-size: .8rem;
        }

        .psy-lawyer-card__price strong {
            display: block;
            margin: 5px 0 3px;
            color: #26352f;
            font-size: 1.4rem;
            line-height: 1.1;
        }

        .psy-lawyer-card__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding-top: 18px;
            border-top: 1px solid rgba(45,61,55,.08);
        }

        .psy-lawyer-card__footer small {
            color: #8a9691;
            line-height: 1.45;
        }

        a.psy-lawyer-card__phone {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            flex: 0 0 auto;
            padding: 11px 15px;
            border-radius: 15px;
            background: #334740;
            color: #fff !important;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 9px 20px rgba(51,71,64,.14);
            transition: transform .2s ease, background .2s ease, box-shadow .2s ease;
        }

        .nh-law-page--nordic a.psy-lawyer-card__phone:hover,
        .nh-law-page--nordic a.psy-lawyer-card__phone:focus,
        .nh-law-page--nordic a.psy-lawyer-card__phone:active {
            color: #fff !important;
            background: #263a33;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(38,58,51,.18);
        }

        .psy-law-callout {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: 64px minmax(0, 1fr);
            gap: 18px;
            align-items: center;
            margin-top: 28px;
            padding: 22px 24px;
            border-radius: 24px;
            background: linear-gradient(135deg, #dde8e2 0%, #eaece5 100%);
            border: 1px solid rgba(45,61,55,.08);
        }

        .psy-law-callout__icon {
            display: grid;
            place-items: center;
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: rgba(255,255,255,.72);
            color: #6f897e;
            font-size: 1.45rem;
        }

        .psy-law-callout strong {
            display: block;
            margin-bottom: 5px;
            color: #26352f;
        }

        .psy-law-callout p {
            margin: 0;
            color: #66746e;
            line-height: 1.65;
        }

        .nh-law-page--nordic .nh-pagination {
            margin-top: 28px;
        }

        .nh-law-page--nordic .pagination {
            gap: 8px;
        }

        .nh-law-page--nordic .page-link {
            padding: 10px 14px;
            border-radius: 14px;
            border: 1px solid rgba(45,61,55,.08);
            background: rgba(255,255,255,.82);
            color: #4d5f58;
            box-shadow: none;
        }

        .nh-law-page--nordic .page-item.active .page-link {
            background: #334740;
            border-color: #334740;
            color: #fff;
        }

        .nh-law-page--nordic .page-item.disabled .page-link {
            background: rgba(255,255,255,.5);
            color: #a5b0ab;
        }

        @media (max-width: 1100px) {
            .psy-law-hero__panel {
                grid-template-columns: 1fr;
            }

            .psy-lawyer-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 840px) {
            .psy-law-principles {
                grid-template-columns: 1fr;
            }

            .psy-lawyer-card__body {
                grid-template-columns: 1fr;
            }

            .psy-lawyer-card__price {
                min-width: 0;
            }
        }

        @media (max-width: 720px) {
            .psy-law-hero {
                padding-top: 10px;
            }

            .psy-law-hero__panel,
            .psy-lawyer-card {
                padding: 22px;
            }

            .psy-law-hero h1 {
                font-size: 2.25rem;
            }

            .psy-law-section__head,
            .psy-lawyer-card__footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .psy-lawyer-card__phone {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <section class="nh-public-section psy-law-section">
        <div class="nh-public-shell">
            <div class="psy-law-section__head">
                <div>
                    <h2>Юристы</h2>
                    <p>
                        Выберите специалиста по нужному направлению. Навыки, базовая стоимость и контактные данные
                        собраны в одной спокойной и понятной карточке.
                    </p>
                </div>

                <div class="psy-law-count">
                    <span>Всего</span>
                    <strong>{{ $lawyers->total() }}</strong>
                </div>
            </div>

            <div class="psy-law-principles">
                <div class="psy-law-principle">
                    <div class="psy-law-principle__visual"><i class="bi bi-chat-square-heart"></i></div>
                    <div>
                        <strong>Деликатная коммуникация</strong>
                        <span>Спокойная подача для сложных и чувствительных вопросов.</span>
                    </div>
                </div>

                <div class="psy-law-principle">
                    <div class="psy-law-principle__visual"><i class="bi bi-shield-lock"></i></div>
                    <div>
                        <strong>Конфиденциальность</strong>
                        <span>Визуально подчёркнутая приватность и уважение к личной ситуации.</span>
                    </div>
                </div>

                <div class="psy-law-principle">
                    <div class="psy-law-principle__visual"><i class="bi bi-file-earmark-check"></i></div>
                    <div>
                        <strong>Понятные действия</strong>
                        <span>Навыки, стоимость и контакт — без перегруженного интерфейса.</span>
                    </div>
                </div>
            </div>

            <div class="psy-lawyer-grid">
                @foreach($lawyers as $lawyer)
                    <article class="psy-lawyer-card">
                        <div class="psy-lawyer-card__header">
                            <div class="psy-lawyer-card__avatar">
                                {{ mb_substr($lawyer->name, 0, 1) }}{{ mb_substr($lawyer->surname, 0, 1) }}
                            </div>

                            <div class="psy-lawyer-card__identity">
                                <h3>{{ $lawyer->name }} {{ $lawyer->surname }}</h3>
                                <p>{{ $lawyer->profession }}</p>
                            </div>
                        </div>

                        <div class="psy-lawyer-card__body">
                            <div>
                                <span class="psy-lawyer-card__label">Направления</span>
                                <ul class="psy-lawyer-card__skills">
                                    @foreach($lawyer->skills as $skill)
                                        <li>{{ $skill }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="psy-lawyer-card__price">
                                <span>Базовая стоимость</span>
                                <strong>{{ $lawyer->base_price }} ₽</strong>
                                <small>за приём*</small>
                            </div>
                        </div>

                        <div class="psy-lawyer-card__footer">
                            <small>* Стоимость услуг уточняйте у специалиста.</small>
                            <a class="psy-lawyer-card__phone" href="tel:{{ $lawyer->phone }}">
                                <i class="bi bi-telephone"></i>
                                {{ $lawyer->phone }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($lawyers->total() > 2)
                <div class="psy-law-callout">
                    <div class="psy-law-callout__icon"><i class="bi bi-shield-check"></i></div>
                    <div>
                        <strong>Спокойная юридическая опора</strong>
                        <p>Выберите специалиста, чьи направления ближе всего к вашей ситуации, и уточните детали напрямую.</p>
                    </div>
                </div>
            @endif

            <div class="nh-pagination">{{ $lawyers->links('pagination::bootstrap-5') }}</div>
        </div>
    </section>
@endsection
