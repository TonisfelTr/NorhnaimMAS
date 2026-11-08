<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Рецепт {{ $document['recipe']['series'] }} № {{ $document['recipe']['number'] }}</title>
    <link rel="stylesheet" href="{{ asset('css/benefit-prescription-print.css') }}">
    <script src="{{ asset('js/benefit-prescription-print.js') }}" defer></script>
</head>
<body class="bp-print-document">
    <header class="bp-toolbar">
        <div class="bp-toolbar__heading">
            <div>
                <h1>Рецепт № 148-1/у-04(л)</h1>
                <p>Серия {{ $document['recipe']['series'] }}, № {{ $document['recipe']['number'] }}
                    от {{ $document['recipe']['issued_date'] }}. Два экземпляра с одинаковыми реквизитами.</p>
            </div>
            <button type="button" class="bp-print-button" id="benefitPrintButton" disabled>Печать комплекта</button>
        </div>
        <div class="bp-print-settings">
            <label for="benefitPrintLayout">Размещение</label>
            <select id="benefitPrintLayout">
                <option value="compact">Два бланка на листе А4</option>
                <option value="large">Один бланк на листе А4</option>
            </select>
            <p id="benefitPrintInstructions">А4, альбомная ориентация, масштаб 100%, без колонтитулов.
                Двусторонняя печать с переворотом по короткой стороне, один комплект.
                После печати разрежьте лист пополам.</p>
        </div>
        <p class="bp-toolbar__note">Первый экземпляр предназначен для аптечной организации, второй - для медицинской карты.
            На бумаге нужны подпись врача и предусмотренные печати. Поля отпуска заполняет аптека.</p>
        @if ($document['recipe']['companion_form'] !== 'none')
            <p class="bp-toolbar__note">Этот льготный бланк выдается вместе с дополнительным рецептом на препарат,
                указанным при оформлении назначения.</p>
        @endif
        <p id="benefitPrintNotice" class="bp-print-notice" role="status" hidden></p>
        <noscript><p class="bp-print-notice">Для проверки размещения текста и печати комплекта включите JavaScript.</p></noscript>
    </header>

    <div class="bp-print-blocked-message" role="alert">
        Печать бланка остановлена: текст не помещается на странице или проверка размещения ещё не завершена.
        Вернитесь к предпросмотру и проверьте сообщение над бланком.
    </div>

    <main id="benefitPrintPages" class="bp-pages" aria-label="Два экземпляра рецептурного бланка">
        <section class="bp-sheet bp-sheet--front" aria-label="Лицевые стороны двух экземпляров">
            @foreach ([1, 2] as $copy)
                <article class="bp-form bp-form--front" data-copy="{{ $copy }}" data-side="front"
                         aria-label="Лицевая сторона, экземпляр {{ $copy }}">
                    <div class="bp-form__content">
                        <div class="bp-heading">
                            <div>Министерство здравоохранения<br>Российской Федерации</div>
                            <div class="bp-barcode-space">МЕСТО ДЛЯ<br>ШТРИХКОДА*</div>
                            <div class="bp-approval">УТВЕРЖДЕНА<br>приказом Министерства здравоохранения<br>
                                Российской Федерации<br>от 24 ноября 2021 г. № 1094н</div>
                        </div>

                        @foreach (['organization' => 'медицинской организации', 'individual' => 'индивидуального предпринимателя'] as $kind => $stampLabel)
                            <div class="bp-stamp-block {{ $document['organization']['kind'] === $kind ? 'bp-stamp-block--active' : '' }}">
                                <div class="bp-stamp-line">
                                    <span>Штамп {{ $stampLabel }}</span>
                                    <span class="bp-stamp-code">Код
                                        <span class="bp-cells bp-cells--small">
                                            @foreach (range(0, max(4, strlen((string) ($document['organization']['code'] ?? '')) - 1)) as $position)
                                                <span class="bp-cell">{{ $document['organization']['kind'] === $kind ? (($document['organization']['code'] ?? '')[$position] ?? '') : '' }}</span>
                                            @endforeach
                                        </span>
                                    </span>
                                </div>
                                @if ($document['organization']['kind'] === $kind)
                                    <div class="bp-organization-name">{{ $document['organization']['name'] }}</div>
                                    <div class="bp-organization-details">{{ $document['organization']['address'] }}</div>
                                    <div class="bp-organization-details">Тел. {{ $document['organization']['phone'] }};
                                        дата оформления {{ $document['recipe']['issued_date'] }}</div>
                                    @if ($kind === 'individual')
                                        <div class="bp-organization-details">Лицензия {{ $document['organization']['license_number'] }}
                                            от {{ $document['organization']['license_date'] }};
                                            {{ $document['organization']['license_authority'] }}</div>
                                    @endif
                                @endif
                                <div class="bp-ogrn-line">
                                    <span class="bp-ogrn-label">{{ $kind === 'individual' ? 'ОГРНИП' : 'ОГРН' }}</span>
                                    <span class="bp-cells bp-cells--small">
                                        @foreach (range(0, 14) as $position)
                                            <span class="bp-cell">{{ $document['organization']['kind'] === $kind ? (($document['organization']['ogrn'] ?? '')[$position] ?? '') : '' }}</span>
                                        @endforeach
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        <div class="bp-form-code">Код формы по ОКУД 3108805<br><strong>Форма № 148-1/у-04(л)</strong></div>

                        <table class="bp-benefit-codes" aria-label="Кодирование рецепта">
                            <colgroup><col class="bp-col-category"><col class="bp-col-diagnosis"><col class="bp-col-funding"><col class="bp-col-payment"></colgroup>
                            <tbody>
                                <tr>
                                    <td>
                                        <div>Код категории граждан</div>
                                        <div class="bp-cells bp-cells--category">
                                            @foreach (range(0, 2) as $position)
                                                <span class="bp-cell">{{ $document['recipe']['category_code'][$position] ?? '' }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <div>Код нозологической формы (по МКБ)</div>
                                        <div class="bp-cells bp-cells--diagnosis">
                                            @foreach (range(0, max(5, strlen($document['recipe']['diagnosis_code']) - 1)) as $position)
                                                <span class="bp-cell">{{ $document['recipe']['diagnosis_code'][$position] ?? '' }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <div>Источник финансирования:<br>(подчеркнуть)</div>
                                        <div class="{{ (int) $document['recipe']['funding'] === 1 ? 'bp-underlined' : '' }}">1. Федеральный бюджет</div>
                                        <div class="{{ (int) $document['recipe']['funding'] === 2 ? 'bp-underlined' : '' }}">2. Бюджет субъекта Российской Федерации</div>
                                        <div class="{{ (int) $document['recipe']['funding'] === 3 ? 'bp-underlined' : '' }}">3. Муниципальный бюджет</div>
                                    </td>
                                    <td>
                                        <div>% оплаты:<br>(подчеркнуть)</div>
                                        <div class="{{ (int) $document['recipe']['payment'] === 1 ? 'bp-underlined' : '' }}">1. Бесплатно</div>
                                        <div class="{{ (int) $document['recipe']['payment'] === 2 ? 'bp-underlined' : '' }}">2. 50%</div>
                                        <div class="{{ (int) $document['recipe']['payment'] === 3 ? 'bp-underlined' : '' }}">3. иной %{{ (int) $document['recipe']['payment'] === 3 ? ' - ' . $document['recipe']['payment_percent'] . '%' : '' }}</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="bp-recipe-number">
                            <strong>РЕЦЕПТ</strong>
                            <span>Серия <b class="bp-line-value">{{ $document['recipe']['series'] }}</b></span>
                            <span>№ <b class="bp-line-value">{{ $document['recipe']['number'] }}</b></span>
                            <span>Дата оформления: <b class="bp-line-value">{{ $document['recipe']['issued_date'] }}</b></span>
                        </div>

                        <div class="bp-patient-line">
                            <div class="bp-patient-name">
                                Фамилия, инициалы имени и отчества (последнее - при наличии) пациента
                                <strong class="bp-value-line">{{ $document['patient']['name'] }}</strong>
                            </div>
                            <div class="bp-birth-date">Дата рождения
                                <strong class="bp-value-line">{{ $document['patient']['birth_date'] }}</strong>
                            </div>
                        </div>
                        <div class="bp-inline-field">
                            <span>СНИЛС</span>
                            <span class="bp-cells">
                                @foreach (range(0, 10) as $position)
                                    <span class="bp-cell">{{ preg_replace('/\D/u', '', $document['patient']['snils'] ?? '')[$position] ?? '' }}</span>
                                @endforeach
                            </span>
                        </div>
                        <div class="bp-inline-field">
                            <span>№ полиса обязательного<br>медицинского страхования:</span>
                            <span class="bp-cells bp-cells--oms">
                                @foreach (range(0, max(15, mb_strlen($document['patient']['oms']) - 1)) as $position)
                                    <span class="bp-cell">{{ mb_substr($document['patient']['oms'], $position, 1) }}</span>
                                @endforeach
                            </span>
                        </div>
                        <div class="bp-demographic-line">Номер медицинской карты пациента, получающего медицинскую помощь
                            в амбулаторных условиях <strong class="bp-line-value">{{ $document['patient']['card_number'] }}</strong></div>
                        <div class="bp-demographic-line">Фамилия, инициалы имени и отчества (последнее - при наличии)
                            лечащего врача (фельдшера, акушерки)
                            <strong class="bp-value-line">{{ $document['doctor']['name'] }}</strong></div>

                        <div class="bp-prescription-body">
                            <div class="bp-price-columns" aria-label="Стоимость, заполняется аптекой"><span>Руб.</span><span>Коп.</span></div>
                            <div class="bp-prescription-text">
                                <div class="bp-prescription-line bp-prescription-line--rp"><b>Rp:</b><div>{{ $document['recipe']['rp'] }}</div></div>
                                <div class="bp-prescription-line"><b>D.t.d.</b><div>{{ $document['recipe']['dtd'] }}</div></div>
                                <div class="bp-prescription-line bp-prescription-line--signa"><b>Signa:</b><div>{{ $document['recipe']['signa'] }}</div></div>
                            </div>
                        </div>

                        <div class="bp-signatures">
                            <div>Подпись и печать лечащего врача<br>(подпись фельдшера, акушерки)<span class="bp-signature-line"></span></div>
                            <div class="bp-seal">М.П.</div>
                        </div>
                        <div class="bp-validity">Рецепт действителен в течение
                            <span class="{{ (int) $document['recipe']['validity_days'] === 15 ? 'bp-underlined' : '' }}">15 дней</span>,
                            <span class="{{ (int) $document['recipe']['validity_days'] === 30 ? 'bp-underlined' : '' }}">30 дней</span>,
                            <span class="{{ (int) $document['recipe']['validity_days'] === 90 ? 'bp-underlined' : '' }}">90 дней</span>
                            <span class="bp-validity-hint">(нужное подчеркнуть)</span>
                        </div>

                        <div class="bp-divider"><span>Заполняется специалистом аптечной организации</span></div>
                        <div class="bp-pharmacy">
                            <div>Отпущено по рецепту:<span class="bp-blank-line"></span></div>
                            <div>Торговое наименование и дозировка:<span class="bp-blank-line"></span></div>
                            <div>Дата отпуска: «___» __________ 20___ г.</div>
                            <div>Количество:<span class="bp-blank-line"></span></div>
                        </div>
                        <div class="bp-pharmacy-signatures"><span>Приготовил: __________</span><span>Проверил: __________</span><span>Отпустил: __________</span></div>

                        <div class="bp-divider bp-divider--tear"><span>линия отрыва</span></div>
                        <div class="bp-stub">
                            <div>
                                <strong>Корешок рецептурного бланка</strong>
                                <div>Наименование лекарственного препарата:</div>
                                <div class="bp-stub-value">{{ $document['recipe']['medicine_name'] }}</div>
                                <div>Дозировка: <b>{{ $document['recipe']['dosage'] }}</b></div>
                            </div>
                            <div>
                                <div>Способ применения:</div>
                                <div class="bp-stub-value bp-stub-value--signa">{{ $document['recipe']['signa'] }}</div>
                                <div>Продолжительность <b>{{ $document['recipe']['treatment_days'] }}</b> дней</div>
                                <div>Количество приемов в день: <b>{{ $document['recipe']['doses_per_day'] }}</b> раз</div>
                                <div>На 1 прием: <b>{{ $document['recipe']['units_per_dose'] }}</b> ед.</div>
                            </div>
                        </div>
                        <div class="bp-footnote">* В случае изготовления рецептурного бланка с использованием компьютерных технологий.</div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="bp-sheet bp-sheet--back" aria-label="Оборотные стороны двух экземпляров">
            @foreach ([1, 2] as $copy)
                <article class="bp-form bp-form--back" data-copy="{{ $copy }}" data-side="back"
                         aria-label="Оборотная сторона, экземпляр {{ $copy }}">
                    <div class="bp-form__content">
                        <div class="bp-back-title">Оборотная сторона</div>
                        <table class="bp-back-table" aria-label="Отметки врачебной комиссии и аптечной организации">
                            <tbody>
                                <tr>
                                    <td class="bp-back-empty"></td>
                                    <td colspan="2" class="bp-commission-note">
                                        Отметка о назначении лекарственного препарата по решению врачебной комиссии
                                    </td>
                                </tr>
                                <tr class="bp-back-signature-headings">
                                    <td>Приготовил</td><td>Проверил</td><td>Отпустил</td>
                                </tr>
                                <tr class="bp-back-signature-spaces"><td></td><td></td><td></td></tr>
                            </tbody>
                        </table>
                    </div>
                </article>
            @endforeach
        </section>
    </main>
</body>
</html>
