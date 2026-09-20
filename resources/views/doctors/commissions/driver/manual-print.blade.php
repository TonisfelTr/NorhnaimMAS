<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Пустой бланк — психиатрическое обследование водителя</title>
    <style>
        * { box-sizing: border-box; }
        html { background: #e9edef; }
        body {
            margin: 0;
            color: #111;
            background: #e9edef;
            font-family: "Times New Roman", Times, serif;
            font-size: 10.8pt;
            line-height: 1.24;
        }
        .toolbar {
            width: 210mm;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin: 12px auto 0;
            font-family: Arial, sans-serif;
        }
        .toolbar button {
            border: 0;
            border-radius: 8px;
            padding: 9px 16px;
            color: #fff;
            background: #087f8e;
            font-weight: 700;
            cursor: pointer;
        }
        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 10px auto;
            padding: 11mm 14mm 12mm;
            background: #fff;
            box-shadow: 0 5px 22px rgba(0,0,0,.12);
            page-break-after: always;
        }
        .sheet:last-child { page-break-after: auto; }
        .topline {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 8px;
            font-size: 9pt;
        }
        .topline__field { min-height: 27px; border-bottom: 1px solid #111; }
        .topline__caption { margin-top: 2px; text-align: center; font-size: 7.5pt; }
        .header { text-align: center; margin: 6px 0 12px; }
        .header h1 { margin: 0; font-size: 13.5pt; line-height: 1.2; text-transform: uppercase; }
        .header p { margin: 4px 0 0; font-size: 9pt; }
        .manual-mark {
            display: inline-block;
            margin-top: 6px;
            padding: 2px 8px;
            border: 1px solid #777;
            border-radius: 9px;
            font-size: 8pt;
            font-weight: 700;
        }
        .part-title {
            margin: 10px 0 6px;
            padding: 5px 7px;
            border: 1px solid #333;
            background: #f3f3f3;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }
        .section-title {
            margin: 9px 0 4px;
            font-weight: 700;
            page-break-after: avoid;
        }
        .field-row {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 6px;
            align-items: end;
            margin: 4px 0;
            page-break-inside: avoid;
        }
        .field-row--3 { grid-template-columns: auto 1fr auto 1fr auto 1fr; }
        .identity-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 4px 10px;
            margin: 4px 0;
            page-break-inside: avoid;
        }
        .identity-field {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 5px;
            align-items: end;
            min-width: 0;
        }
        .identity-field--span-2 { grid-column: span 2; }
        .identity-field--wide { grid-column: 1 / -1; }
        .line { min-height: 18px; border-bottom: 1px solid #111; }
        .line--tall { min-height: 24px; }
        .write-box {
            min-height: 48px;
            border: 1px solid #777;
            padding: 5px;
        }
        .write-box--sm { min-height: 36px; }
        .write-box--lg { min-height: 86px; }
        .write-box--xl { min-height: 126px; }
        .checks {
            display: flex;
            flex-wrap: wrap;
            gap: 5px 12px;
            margin: 4px 0;
        }
        .check {
            display: inline-flex;
            align-items: flex-start;
            gap: 4px;
            page-break-inside: avoid;
        }
        .box {
            width: 12px;
            height: 12px;
            flex: 0 0 12px;
            margin-top: 1px;
            border: 1px solid #111;
        }
        .categories {
            display: grid;
            grid-template-columns: repeat(8, minmax(0, 1fr));
            gap: 5px;
            margin: 5px 0 7px;
        }
        .category {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-height: 27px;
            border: 1px solid #777;
            border-radius: 4px;
            font-weight: 700;
        }
        .category .box { margin-top: 0; }
        .two-col {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .risk-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 5px 10px;
            margin: 5px 0;
        }
        .signature-row {
            display: grid;
            grid-template-columns: 170px 110px 1fr;
            gap: 10px;
            align-items: end;
            margin-top: 13px;
            page-break-inside: avoid;
        }
        .signature-line { min-height: 22px; border-bottom: 1px solid #111; }
        .signature-caption { margin-top: 2px; font-size: 7.5pt; text-align: center; }
        .seal-row {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .seal-box {
            width: 145px;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed #777;
            border-radius: 4px;
            color: #555;
            font-size: 7.5pt;
            line-height: 1.25;
            text-align: center;
            padding: 6px;
        }
        .seal-box--wide { width: 185px; }
        .small { font-size: 8.2pt; }
        .muted { color: #444; }
        .note {
            margin-top: 8px;
            padding: 5px 7px;
            border: 1px solid #999;
            font-size: 8pt;
        }
        .page-number { margin-top: 7px; font-size: 7.5pt; text-align: right; }
        .no-break { page-break-inside: avoid; }
        /* Первая страница стала содержательнее из-за паспортных данных,
           поэтому слегка уплотняем только свободные поля пациента. */
        .patient-sheet .write-box--lg { min-height: 68px; }
        .patient-sheet .write-box--sm { min-height: 31px; }
        .patient-sheet .part-title { margin-top: 8px; }
        .patient-sheet .section-title { margin-top: 7px; }
        @page { size: A4; margin: 0; }
        @media print {
            html, body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <button type="button" onclick="window.print()">Печать пустого бланка</button>
</div>

{{-- СТРАНИЦА 1: ПАЦИЕНТ --}}
<article class="sheet patient-sheet">
    @if(filled($organizationName ?? null) || filled($organizationAddress ?? null))
        <div class="organization-header">
            @if(filled($organizationName ?? null))
                <div class="organization-header__name">{{ $organizationName }}</div>
            @endif
            @if(filled($organizationAddress ?? null))
                <div class="organization-header__address">{{ $organizationAddress }}</div>
            @endif
        </div>
    @endif

    <header class="header">
        <h1>Психиатрическое обследование водителя / кандидата в водители</h1>
        <p>Рабочий бланк для ручного заполнения</p>
        <span class="manual-mark">Часть I — заполняет пациент / законный представитель</span>
    </header>

    <div class="part-title">1. Сведения о пациенте</div>

    <div class="field-row"><span>Фамилия, имя, отчество:</span><span class="line"></span></div>
    <div class="field-row--3 field-row">
        <span>Дата рождения:</span><span class="line"></span>
        <span>Возраст:</span><span class="line"></span>
        <span>Телефон:</span><span class="line"></span>
    </div>
    <div class="field-row"><span>СНИЛС / иной идентификатор (при необходимости):</span><span class="line"></span></div>

    <div class="section-title">Паспортные данные и регистрация</div>
    <div class="identity-grid">
        <div class="identity-field">
            <span>Документ:</span><span class="line"></span>
        </div>
        <div class="identity-field">
            <span>Серия:</span><span class="line"></span>
        </div>
        <div class="identity-field">
            <span>Номер:</span><span class="line"></span>
        </div>
        <div class="identity-field">
            <span>Дата выдачи:</span><span class="line"></span>
        </div>
        <div class="identity-field identity-field--span-2">
            <span>Код подразделения:</span><span class="line"></span>
        </div>
        <div class="identity-field identity-field--wide">
            <span>Кем выдан:</span><span class="line"></span>
        </div>
        <div class="identity-field identity-field--wide">
            <span>Адрес регистрации (прописка):</span><span class="line"></span>
        </div>
        <div class="identity-field identity-field--wide">
            <span>Адрес фактического проживания (если отличается):</span><span class="line"></span>
        </div>
    </div>

    <div class="section-title">Цель обращения</div>
    <div class="checks">
        @foreach($purposeOptions as $label)
            <span class="check"><span class="box"></span><span>{{ $label }}</span></span>
        @endforeach
    </div>
    <div class="field-row"><span>Иная цель:</span><span class="line"></span></div>

    <div class="section-title">Категории транспортных средств</div>
    <div class="categories">
        @foreach(array_keys($vehicleCategoryOptions) as $category)
            <span class="category"><span class="box"></span>{{ $category }}</span>
        @endforeach
    </div>

    <div class="part-title">2. Сведения со слов пациента</div>

    <div class="section-title">Жалобы на момент обследования</div>
    <div class="write-box write-box--lg"></div>

    <div class="section-title">Ранее обращался за психиатрической помощью / наблюдался у врача-психиатра</div>
    <div class="checks">
        <span class="check"><span class="box"></span><span>Нет</span></span>
        <span class="check"><span class="box"></span><span>Да</span></span>
        <span class="check"><span class="box"></span><span>Не помню / затрудняюсь ответить</span></span>
    </div>
    <div class="write-box write-box--sm"></div>

    <div class="section-title">Психиатрические госпитализации</div>
    <div class="checks">
        <span class="check"><span class="box"></span><span>Нет</span></span>
        <span class="check"><span class="box"></span><span>Да — указать период, организацию и причину</span></span>
    </div>
    <div class="write-box write-box--sm"></div>

    <div class="section-title">Принимаемые лекарственные препараты, в том числе психотропные</div>
    <div class="checks">
        <span class="check"><span class="box"></span><span>Не принимаю</span></span>
        <span class="check"><span class="box"></span><span>Принимаю — перечислить препараты и дозы</span></span>
    </div>
    <div class="write-box write-box--sm"></div>

    <div class="section-title">Иные сведения, которые пациент считает важными для обследования</div>
    <div class="write-box write-box--sm"></div>

    <div class="signature-row">
        <div>Сведения сообщил(а)</div>
        <div><div class="signature-line"></div><div class="signature-caption">подпись</div></div>
        <div><div class="signature-line"></div><div class="signature-caption">ФИО пациента / законного представителя</div></div>
    </div>
    <div class="field-row" style="margin-top:7px"><span>Дата заполнения:</span><span class="line" style="max-width:170px"></span></div>

    <div class="note">
        Эта страница используется как рабочая анкета. Медицинское заключение принимает врач на основании обследования и медицинской документации, а не только ответов пациента.
    </div>
    <div class="page-number">Страница 1 из 3</div>
</article>

{{-- СТРАНИЦА 2: ВРАЧ-ПСИХИАТР --}}
<article class="sheet">
    <header class="header">
        <h1>Психиатрическое обследование водителя / кандидата в водители</h1>
        <span class="manual-mark">Часть II — заполняет врач-психиатр</span>
    </header>

    <div class="field-row"><span>Пациент:</span><span class="line"></span></div>
    <div class="field-row--3 field-row">
        <span>Дата обследования:</span><span class="line"></span>
        <span>Цель:</span><span class="line"></span>
        <span>Категории ТС:</span><span class="line"></span>
    </div>

    <div class="part-title">3. Медицинская документация и анамнез</div>

    <div class="checks">
        <span class="check"><span class="box"></span><span>Медицинская карта проверена</span></span>
        <span class="check"><span class="box"></span><span>Дополнительные медицинские документы представлены</span></span>
    </div>

    <div class="section-title">Диагноз / сведения о психическом расстройстве в медицинской карте (если имеются)</div>
    <div class="write-box write-box--sm"></div>

    <div class="section-title">Жалобы и анамнез текущего состояния</div>
    <div class="write-box write-box--lg"></div>

    <div class="two-col">
        <div>
            <div class="section-title">Психиатрический анамнез</div>
            <div class="write-box write-box--lg"></div>
        </div>
        <div>
            <div class="section-title">Госпитализации</div>
            <div class="write-box write-box--lg"></div>
        </div>
    </div>

    <div class="section-title">Текущая терапия, приверженность, значимые нежелательные эффекты</div>
    <div class="write-box write-box--sm"></div>

    <div class="part-title">4. Психиатрическое обследование</div>

    <div class="section-title">Психический статус</div>
    <div class="write-box write-box--xl"></div>

    <div class="section-title">Когнитивные и поведенческие особенности</div>
    <div class="write-box write-box--lg"></div>

    <div class="section-title">Клинически значимые настораживающие признаки</div>
    <div class="risk-grid">
        @foreach($riskFlagOptions as $label)
            <span class="check"><span class="box"></span><span>{{ $label }}</span></span>
        @endforeach
        <span class="check"><span class="box"></span><span>Иные клинически значимые признаки</span></span>
    </div>
    <div class="write-box write-box--sm"></div>

    <div class="part-title">5. Результат обследования врачом-психиатром</div>

    <div class="checks" style="display:grid;grid-template-columns:1fr;gap:5px">
        <span class="check"><span class="box"></span><span>Медицинские противопоказания психиатрического профиля не выявлены.</span></span>
        <span class="check"><span class="box"></span><span>Выявлены симптомы / синдромы, требующие психиатрического освидетельствования врачебной комиссией.</span></span>
        <span class="check"><span class="box"></span><span>Пациент отказался от психиатрического освидетельствования врачебной комиссией.</span></span>
    </div>

    <div class="section-title">Клиническое обоснование</div>
    <div class="write-box write-box--lg"></div>

    <div class="section-title">Заключение / рекомендации</div>
    <div class="write-box write-box--lg"></div>

    <div class="signature-row">
        <div>Врач-психиатр</div>
        <div><div class="signature-line"></div><div class="signature-caption">подпись</div></div>
        <div><div class="signature-line"></div><div class="signature-caption">ФИО</div></div>
    </div>
    <div class="field-row" style="margin-top:7px"><span>Дата:</span><span class="line" style="max-width:170px"></span></div>

    <div class="seal-row">
        <div class="seal-box">Личная печать<br>врача-психиатра</div>
        <div class="seal-box seal-box--wide">М.П. медицинской организации<br>(при наличии)</div>
        <div class="seal-box seal-box--wide">Штамп медицинской организации<br>(при наличии)</div>
    </div>

    <div class="note">
        При необходимости психиатрического освидетельствования врачебной комиссией заполняется часть III. Психиатр-нарколог является отдельным этапом водительского медицинского освидетельствования.
    </div>
    <div class="page-number">Страница 2 из 3</div>
</article>

{{-- СТРАНИЦА 3: ВРАЧЕБНАЯ КОМИССИЯ / ОТКАЗ --}}
<article class="sheet">
    <header class="header">
        <h1>Психиатрическое освидетельствование врачебной комиссией</h1>
        <p>Заполняется только при направлении пациента на психиатрическую врачебную комиссию</p>
        <span class="manual-mark">Часть III — врачебная комиссия / пациент при отказе</span>
    </header>

    <div class="field-row"><span>Пациент:</span><span class="line"></span></div>
    <div class="field-row--3 field-row">
        <span>Дата рождения:</span><span class="line"></span>
        <span>Дата направления:</span><span class="line"></span>
        <span>Категории ТС:</span><span class="line"></span>
    </div>

    <div class="part-title">6. Основание направления на ВК</div>
    <div class="write-box write-box--lg"></div>

    <div class="part-title">7. Психиатрическая врачебная комиссия</div>
    <div class="field-row--3 field-row">
        <span>Протокол №:</span><span class="line"></span>
        <span>Дата:</span><span class="line"></span>
        <span>Время:</span><span class="line"></span>
    </div>

    <div class="section-title">Дополнительные сведения, исследованные комиссией</div>
    <div class="write-box write-box--lg"></div>

    <div class="section-title">Решение врачебной комиссии</div>
    <div class="checks" style="display:grid;grid-template-columns:1fr;gap:6px">
        <span class="check"><span class="box"></span><span>Медицинские противопоказания психиатрического профиля к управлению транспортными средствами не подтверждены.</span></span>
        <span class="check"><span class="box"></span><span>Медицинские противопоказания психиатрического профиля к управлению транспортными средствами подтверждены.</span></span>
    </div>

    <div class="section-title">Обоснование решения / заключение комиссии</div>
    <div class="write-box write-box--xl"></div>

    <div class="section-title">Состав врачебной комиссии</div>
    <div class="signature-row">
        <div>Председатель ВК</div>
        <div><div class="signature-line"></div><div class="signature-caption">подпись</div></div>
        <div><div class="signature-line"></div><div class="signature-caption">ФИО</div></div>
    </div>
    <div class="signature-row">
        <div>Член ВК</div>
        <div><div class="signature-line"></div><div class="signature-caption">подпись</div></div>
        <div><div class="signature-line"></div><div class="signature-caption">ФИО</div></div>
    </div>
    <div class="signature-row">
        <div>Член ВК</div>
        <div><div class="signature-line"></div><div class="signature-caption">подпись</div></div>
        <div><div class="signature-line"></div><div class="signature-caption">ФИО</div></div>
    </div>
    <div class="signature-row">
        <div>Член ВК</div>
        <div><div class="signature-line"></div><div class="signature-caption">подпись</div></div>
        <div><div class="signature-line"></div><div class="signature-caption">ФИО</div></div>
    </div>

    <div class="seal-row">
        <div class="seal-box seal-box--wide">М.П. медицинской организации<br>(при наличии)</div>
        <div class="seal-box seal-box--wide">Штамп медицинской организации<br>(при наличии)</div>
    </div>

    <div class="part-title" style="margin-top:18px">8. Отказ пациента от психиатрического освидетельствования ВК</div>
    <p class="small muted" style="margin:4px 0 6px">Заполняется только при фактическом отказе пациента от прохождения врачебной комиссии.</p>

    <div class="checks">
        <span class="check"><span class="box"></span><span>Факт отказа от психиатрического освидетельствования врачебной комиссией зафиксирован.</span></span>
    </div>
    <div class="field-row"><span>Комментарий / обстоятельства отказа:</span><span class="line line--tall"></span></div>

    <div class="signature-row">
        <div>Пациент / законный представитель</div>
        <div><div class="signature-line"></div><div class="signature-caption">подпись</div></div>
        <div><div class="signature-line"></div><div class="signature-caption">ФИО</div></div>
    </div>
    <div class="signature-row">
        <div>Врач, зафиксировавший отказ</div>
        <div><div class="signature-line"></div><div class="signature-caption">подпись</div></div>
        <div><div class="signature-line"></div><div class="signature-caption">ФИО</div></div>
    </div>
    <div class="field-row" style="margin-top:7px"><span>Дата:</span><span class="line" style="max-width:170px"></span></div>

    <div class="note">
        Рабочий бумажный бланк не является медицинским заключением по форме 003-В/у. Он предназначен для ручной фиксации психиатрического этапа обследования и, при необходимости, решения психиатрической врачебной комиссии. Для выдаваемых пациенту бумажных справок и заключений используются печати и штампы без указания психиатрического профиля медицинской организации.
    </div>
    <div class="page-number">Страница 3 из 3</div>
</article>
</body>
</html>
