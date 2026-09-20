<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $printData['pageTitle'] }}</title>
    <style>
        :root {
            --ink: #24364a;
            --muted: #6f7f8f;
            --line: #c9d3dc;
            --line-strong: #9fadb9;
            --surface: #f5f8fa;
            --accent: #49687f;
            --success: #27745f;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--ink);
            background: #edf1f4;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            line-height: 1.45;
        }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 14px auto;
            padding: 16mm 17mm;
            background: #fff;
            box-shadow: 0 8px 30px rgba(36, 54, 74, .12);
        }

        .header {
            margin-bottom: 17px;
            padding-bottom: 14px;
            border-bottom: 2px solid var(--accent);
            text-align: center;
        }

        .header__org {
            margin-bottom: 11px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 7px;
            font-family: "Times New Roman", Times, serif;
            font-size: 21px;
            line-height: 1.2;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .header__number {
            color: #3f5366;
            font-size: 14px;
            font-weight: 700;
        }

        .meta,
        .members {
            width: 100%;
            border: 1px solid var(--line-strong);
            border-collapse: collapse;
        }

        .meta { margin: 0 0 18px; }

        .meta td,
        .members th,
        .members td {
            padding: 7px 9px;
            vertical-align: top;
            border: 1px solid var(--line);
        }

        .meta td:first-child {
            width: 30%;
            color: #3f5366;
            background: var(--surface);
            font-size: 11px;
            font-weight: 700;
        }

        .section {
            margin: 16px 0;
            page-break-inside: avoid;
        }

        .section h2 {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0 0 7px;
            color: #3f5366;
            font-size: 11px;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .section h2::before {
            width: 3px;
            height: 14px;
            flex: 0 0 3px;
            background: var(--accent);
            border-radius: 2px;
            content: "";
        }

        .section__text {
            color: #202d3a;
            font-family: "Times New Roman", Times, serif;
            font-size: 14px;
            line-height: 1.5;
            text-align: justify;
            white-space: pre-wrap;
        }

        .members th {
            color: #3f5366;
            background: var(--surface);
            font-size: 10px;
            letter-spacing: .025em;
            text-align: left;
            text-transform: uppercase;
        }

        .members tr { page-break-inside: avoid; }
        .members__number { width: 6%; text-align: center; }
        .members__role { width: 22%; }
        .members__presence { width: 15%; }
        .members__vote { width: 14%; }
        .muted { color: var(--muted); }

        .quorum {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            margin-top: 9px;
            border: 1px solid var(--line);
            page-break-inside: avoid;
        }

        .quorum__item {
            padding: 8px 9px;
            border-right: 1px solid var(--line);
        }

        .quorum__item:last-child { border-right: 0; }
        .quorum__label { color: var(--muted); font-size: 9px; text-transform: uppercase; }
        .quorum__value { margin-top: 2px; font-weight: 700; }
        .quorum__value--ok { color: var(--success); }

        .vote-summary {
            margin-top: 7px;
            color: #3f5366;
            font-size: 11px;
            text-align: right;
        }

        .opinion {
            margin-top: 8px;
            padding: 8px 10px;
            background: var(--surface);
            border-left: 3px solid var(--line-strong);
            page-break-inside: avoid;
        }

        .opinion strong { display: block; margin-bottom: 3px; }

        .service-note {
            margin-top: 20px;
            padding: 9px 11px;
            color: #4f6070;
            background: var(--surface);
            border-left: 3px solid var(--accent);
            font-size: 10px;
            page-break-inside: avoid;
        }

        .signature {
            margin-top: 24px;
            border: 1px solid var(--line-strong);
            page-break-inside: avoid;
        }

        .signature__title {
            padding: 7px 10px;
            color: #3f5366;
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .signature__row {
            display: grid;
            grid-template-columns: 23% 20% 39% 18%;
            min-height: 45mm;
        }

        .signature__cell {
            display: flex;
            min-width: 0;
            padding: 7px 9px 6px;
            flex-direction: column;
            justify-content: flex-end;
            border-right: 1px solid var(--line);
        }

        .signature__cell:last-child { border-right: 0; }
        .signature__space { flex: 1 1 auto; }

        .signature__value {
            min-height: 23px;
            padding: 0 3px 3px;
            overflow-wrap: anywhere;
            border-bottom: 1px solid #536474;
            font-family: "Times New Roman", Times, serif;
            font-size: 13px;
            text-align: center;
        }

        .signature__caption {
            min-height: 15px;
            padding-top: 3px;
            color: var(--muted);
            font-size: 9px;
            text-align: center;
        }

        .signature__seal {
            display: flex;
            flex: 1 1 auto;
            align-items: center;
            justify-content: center;
            color: #607181;
            font-family: "Times New Roman", Times, serif;
            font-size: 14px;
            font-weight: 700;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding-top: 7px;
            color: var(--muted);
            border-top: 1px solid var(--line);
            font-size: 9px;
        }

        @page { size: A4; margin: 13mm 15mm; }

        @media print {
            body { background: #fff; }
            .sheet {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
<article class="sheet">
    <header class="header">
        <div class="header__org">Медицинская организация</div>
        <h1>Протокол заседания врачебной комиссии</h1>
        <div class="header__number">{{ $printData['protocolNumber'] }} · {{ $printData['meetingAt'] }}</div>
    </header>

    <table class="meta">
        <tr><td>Пациент</td><td>{{ $printData['patientFullName'] }}</td></tr>
        <tr><td>Дата рождения</td><td>{{ $printData['patientBirthDate'] }}</td></tr>
        <tr><td>Диагноз</td><td>{{ $printData['diagnosis'] }}</td></tr>
        <tr><td>Вид заседания</td><td>{{ $printData['meetingType'] }}</td></tr>
        <tr><td>Категория вопроса</td><td>{{ $printData['category'] }}</td></tr>
        <tr><td>Статус</td><td>{{ $printData['status'] }}</td></tr>
        <tr><td>Секретарь</td><td>{{ $printData['secretaryName'] ?: 'Не указан' }}{{ $printData['secretaryPosition'] ? ', ' . $printData['secretaryPosition'] : '' }}</td></tr>
    </table>

    @if($printData['unscheduledBasis'] !== '')
        <section class="section">
            <h2>Основание внепланового заседания</h2>
            <div class="section__text">{{ $printData['unscheduledBasis'] }}</div>
        </section>
    @endif

    <section class="section">
        <h2>Вопрос, вынесенный на заседание</h2>
        <div class="section__text">{{ $printData['agenda'] ?: 'Не указан' }}</div>
    </section>

    <section class="section">
        <h2>Состав врачебной комиссии</h2>
        <table class="members">
            <thead>
            <tr>
                <th class="members__number">№</th>
                <th>Врач</th>
                <th class="members__role">Роль</th>
                <th class="members__presence">Участие</th>
                <th class="members__vote">Голос</th>
            </tr>
            </thead>
            <tbody>
            @forelse($printData['members'] as $member)
                <tr>
                    <td class="members__number">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $member['name'] }}</strong>
                        @if($member['speciality'] !== '')
                            <div class="muted">{{ $member['speciality'] }}</div>
                        @endif
                    </td>
                    <td>{{ $member['role'] }}</td>
                    <td>{{ $member['present'] ? 'Присутствовал' : 'Отсутствовал' }}</td>
                    <td>{{ $member['vote'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Состав комиссии не указан.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="quorum">
            <div class="quorum__item"><div class="quorum__label">Всего членов</div><div class="quorum__value">{{ $printData['totalMembers'] }}</div></div>
            <div class="quorum__item"><div class="quorum__label">Присутствовало</div><div class="quorum__value">{{ $printData['presentMembers'] }}</div></div>
            <div class="quorum__item"><div class="quorum__label">Требуется</div><div class="quorum__value">{{ $printData['quorumRequired'] }}</div></div>
            <div class="quorum__item"><div class="quorum__label">Кворум</div><div class="quorum__value {{ $printData['hasQuorum'] ? 'quorum__value--ok' : '' }}">{{ $printData['hasQuorum'] ? 'Имеется' : 'Отсутствует' }}</div></div>
        </div>
        <div class="vote-summary">За — {{ $printData['votesFor'] }} · Против — {{ $printData['votesAgainst'] }} · Воздержались — {{ $printData['votesAbstain'] }}</div>
    </section>

    @foreach($printData['members'] as $member)
        @if($member['specialOpinion'] !== '')
            <div class="opinion">
                <strong>Особое мнение: {{ $member['name'] }}</strong>
                <div class="section__text">{{ $member['specialOpinion'] }}</div>
            </div>
        @endif
    @endforeach

    <section class="section">
        <h2>Решение врачебной комиссии</h2>
        <div class="section__text">{{ $printData['decision'] ?: 'Не заполнено' }}</div>
    </section>

    @if($printData['rationale'] !== '')
        <section class="section">
            <h2>Обоснование решения</h2>
            <div class="section__text">{{ $printData['rationale'] }}</div>
        </section>
    @endif

    @if($printData['notes'] !== '')
        <section class="section">
            <h2>Примечания</h2>
            <div class="section__text">{{ $printData['notes'] }}</div>
        </section>
    @endif

    <div class="service-note">
        Решение {{ $printData['decisionAdopted'] ? 'принято необходимым числом голосов' : 'не отмечено как принятое необходимым числом голосов' }}.
        @if($printData['protocolReadyAt']) Протокол оформлен {{ $printData['protocolReadyAt'] }}. @endif
        @if($printData['medicalRecordEnteredAt']) Запись внесена в медицинскую документацию {{ $printData['medicalRecordEnteredAt'] }}. @endif
    </div>

    <div class="signature">
        <div class="signature__title">Подписи и печать</div>
        <div class="signature__row">
            <div class="signature__cell"><div class="signature__space"></div><div class="signature__value">Председатель</div><div class="signature__caption">роль</div></div>
            <div class="signature__cell"><div class="signature__space"></div><div class="signature__value"></div><div class="signature__caption">подпись</div></div>
            <div class="signature__cell"><div class="signature__space"></div><div class="signature__value">{{ $printData['chairName'] }}</div><div class="signature__caption">фамилия, имя, отчество</div></div>
            <div class="signature__cell"><div class="signature__seal">М.П.</div><div class="signature__caption">место печати</div></div>
        </div>
    </div>

    <footer class="footer">
        <span>Подготовил: {{ $printData['creatorName'] ?: 'не указан' }}</span>
        <span>{{ $printData['protocolNumber'] }}</span>
    </footer>
</article>

</body>
</html>
