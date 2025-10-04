<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Document</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
        }
        .prescription {
            margin: 0 auto;
        }
        .stamped {
            border: 2px dashed gray;
        }
        .border-dashed {
            border-bottom: 1px dashed gray;
        }
        .text-muted {
            color: black !important;
            text-align: center;
            font-size: 12px;
        }

        /* Стили для печати */
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                font-size: 12px;
                line-height: 1.2;
            }
            .container {
                transform: scale(0.95); /* Масштабирование для уменьшения размера */
                transform-origin: top left;
            }
            .btn,
            .text-muted,
            title,
            @page {
                margin: 0;
            }

            /* Убираем технические элементы */
            .text-muted,
            .btn,
            .navbar,
            .footer {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="prescription">
        <div class="container">
            <div class="d-inline-flex justify-content-between pt-5">
                <div class="w-50 text-left">
                    Министерство здравоохранения
                    Российской Федерации
                </div>
                <div class="w-50 text-right px-5">
                    Код формы по ОКУД<br>
                    Код учреждения по ОКНО
                </div>
            </div>
            <div class="row pt-3">
                <div class="col-md-6">
                    <p class="text-center h3">МЕДИЦИНСКАЯ ДОКУМЕНТАЦИЯ</p>
                </div>
            </div>
            <div class="d-inline-flex pb-3">
                <div class="w-50 p-2 stamped">
                    <p>[Наименование (штамп) медицинской организации]</p>
                    <p>[Наименование (штамп) индивидуального предпринимателя (указать адрес, номер и дату лицензии, наименование органа государственной власти, выдавшего лицензию)]</p>
                </div>
                <div class="px-3 w-50">
                    Форма №107-1/у<br>
                    Утверждена приказом Министерства здравоохранения Российской Федерации<br>
                    от 14 января 2019 г. №4н
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p class="text-center h3">РЕЦЕПТ</p>
                    <p class="text-center my-0">(взрослый, детский - нужное подчеркнуть)</p>
                    <p class="text-center my-0">«<u>{{ $dateAsDay ?? date('d') }}</u>» <u>{{ $dateAsMonth ?? $month }}</u> <u>{{ $dateAsYear ?? date('Y') }}</u> г.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p class="">Фамилия, инициалы имени и отчества (последнее - при наличии) пациента</p>
                    <p class="my-0 w-100 border-bottom border-dark">{{ $patientFullName }}</p>
                    <div class="d-inline-flex justify-content-between w-100">
                        <div class="py-2 col-md-2">Дата рождения</div>
                        <div class="col-md-10 border-bottom border-dark px-0 h-30">{{ $patientBirthday }}</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p class="">Фамилия, инициалы имени и отчества (последнее - при наличии) лечащего врача (фельдшера, акушерки)</p>
                    <p class="my-0 w-100 border-bottom border-dark">{{ $doctorFullName }}</p>
                </div>
            </div>
            <div class="row pt-4">
                <div class="col-md-6">
                    <p class="pb-2">руб. | коп. | Rp:</p>
                    <p class="pb-2 w-100 border-dark border-dashed">{{ $drugShortForm }}. {{ $drugLatinName }} {{ $drugDose }} mg N. {{ $drugQuantity }} in {{ $drugStandardCount }}</p>
                    <p class="pb-2 w-100 border-dark border-dashed">D.t.d N {{ $drugStandards }}</p>
                    <p class="pb-2 w-100 border-dark border-dashed">S. {{ $drugUsingSchema }}</p>
                    <p class="pt-2 w-100 border-dark border-dashed"></p>
                </div>
            </div>
            <div class="d-inline-flex justify-content-between pt-5">
                <div class="w-50 text-left">
                    Подпись и печать лечащего врача (подпись фельдшера, акушерки)
                </div>
                <div class="w-50 text-right px-5">
                    М. П.
                </div>
            </div>
            <div class="row pt-3">
                <div class="col-md-6 text-end">
                    <span>Рецепт действителен в течение 60 дней, 1 года (<span class="underlined">_____________</span>)</span><br>
                    <span class="text-muted">(нужное подчеркнуть либо ввести кол-во месяцев действия)</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
