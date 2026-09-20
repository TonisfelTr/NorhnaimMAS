@extends('doctors.prescriptions')

@section('title', $pageTitle)
@section('prescription-title', $pageTitle)

@section('sub-main')
    @include('doctors.commissions.mse.partials.styles')

    <style>
        /*
         * Локальные стили формы 088/у.
         * Не меняем общий partial, чтобы уплотнение касалось только этой формы.
         */
        #mseReferralFormRoot.mse-form-compact .mse-form-card {
            padding: 18px 20px;
        }

        #mseReferralFormRoot.mse-form-compact .mse-card__head {
            margin-bottom: 14px;
        }

        #mseReferralFormRoot.mse-form-compact .mse-section-caption {
            margin-top: 16px;
            margin-bottom: 9px;
        }

        #mseReferralFormRoot.mse-form-compact .mse-grid {
            gap: 10px 12px;
        }

        #mseReferralFormRoot.mse-form-compact .mse-grid--4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        #mseReferralFormRoot.mse-form-compact .mse-field--span-2 {
            grid-column: span 2;
        }

        #mseReferralFormRoot.mse-form-compact .mse-field label {
            margin-bottom: 4px;
            font-size: 12px;
            line-height: 1.25;
        }

        #mseReferralFormRoot.mse-form-compact .mse-field > small,
        #mseReferralFormRoot.mse-form-compact .mse-field-error {
            margin-top: 4px;
            font-size: 11px;
            line-height: 1.3;
        }

        #mseReferralFormRoot.mse-form-compact .mse-field input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):not([data-patient-search]),
        #mseReferralFormRoot.mse-form-compact .mse-field select {
            min-height: 38px !important;
            height: 38px !important;
            padding: 6px 10px !important;
            border-radius: 10px !important;
            font-size: 13px !important;
            line-height: 1.25 !important;
        }

        #mseReferralFormRoot.mse-form-compact .mse-field textarea {
            min-height: 42px !important;
            padding: 7px 10px !important;
            border-radius: 10px !important;
            font-size: 13px !important;
            line-height: 1.35 !important;
        }

        #mseReferralFormRoot.mse-form-compact .mse-field textarea[rows="2"] {
            min-height: 56px !important;
        }

        #mseReferralFormRoot.mse-form-compact .mse-field textarea[rows="3"] {
            min-height: 72px !important;
        }

        #mseReferralFormRoot.mse-form-compact .mse-patient-search input {
            min-height: 42px !important;
            height: 42px !important;
        }

        #mseReferralFormRoot .mse-patient-autofill-note {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 8px;
            padding: 9px 11px;
            border: 1px solid #dbe8ea;
            border-radius: 11px;
            background: #f7fbfc;
            color: #4b6670;
            font-size: 11px;
            line-height: 1.35;
        }

        #mseReferralFormRoot .mse-patient-autofill-note i {
            margin-top: 1px;
            color: #087f8c;
        }

        #mseReferralFormRoot .mse-provider-line {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 11px;
            padding: 10px 12px;
            border: 1px solid #dfe8ec;
            border-radius: 12px;
            background: #f8fbfc;
        }

        #mseReferralFormRoot .mse-provider-line__icon {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            place-items: center;
            border-radius: 11px;
            background: #e8f8fa;
            color: #087f8c;
        }

        #mseReferralFormRoot .mse-provider-line strong,
        #mseReferralFormRoot .mse-provider-line span {
            display: block;
        }

        #mseReferralFormRoot .mse-provider-line strong {
            color: #213942;
            font-size: 13px;
        }

        #mseReferralFormRoot .mse-provider-line span {
            margin-top: 2px;
            color: #6b7f87;
            font-size: 11px;
        }

        #mseReferralFormRoot .mse-provider-line.is-private {
            border-color: #eadfbf;
            background: #fffcf3;
        }

        #mseReferralFormRoot .mse-provider-line.is-private .mse-provider-line__icon {
            background: #fff1c9;
            color: #9b6a00;
        }

        #mseReferralFormRoot .mse-address-control {
            position: relative;
        }

        #mseReferralFormRoot .mse-address-suggestions {
            position: absolute;
            z-index: 1200;
            top: calc(100% + 5px);
            left: 0;
            right: 0;
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid #d7e3e8;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 14px 34px rgba(24, 57, 68, .14);
        }

        #mseReferralFormRoot .mse-address-suggestion {
            display: block;
            width: 100%;
            padding: 9px 11px;
            border: 0;
            border-bottom: 1px solid #eef3f5;
            background: #fff;
            color: #263d46;
            text-align: left;
            cursor: pointer;
        }

        #mseReferralFormRoot .mse-address-suggestion:last-child {
            border-bottom: 0;
        }

        #mseReferralFormRoot .mse-address-suggestion:hover,
        #mseReferralFormRoot .mse-address-suggestion:focus {
            outline: 0;
            background: #eef9fa;
        }

        #mseReferralFormRoot .mse-address-suggestion strong,
        #mseReferralFormRoot .mse-address-suggestion span {
            display: block;
        }

        #mseReferralFormRoot .mse-address-suggestion strong {
            font-size: 12px;
            font-weight: 750;
        }

        #mseReferralFormRoot .mse-address-suggestion span {
            margin-top: 2px;
            color: #71858d;
            font-size: 10px;
        }

        #mseReferralFormRoot .mse-address-suggestions__state {
            padding: 10px 12px;
            color: #74868d;
            font-size: 11px;
        }

        #mseReferralFormRoot .mse-collapsible--compact summary {
            padding-top: 10px;
            padding-bottom: 10px;
        }

        #mseReferralFormRoot .mse-collapsible--compact .mse-collapsible__body {
            padding-top: 10px;
        }

        @media (max-width: 1180px) {
            #mseReferralFormRoot.mse-form-compact .mse-grid--4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            #mseReferralFormRoot.mse-form-compact .mse-grid--4,
            #mseReferralFormRoot.mse-form-compact .mse-grid--3,
            #mseReferralFormRoot.mse-form-compact .mse-grid--2 {
                grid-template-columns: minmax(0, 1fr);
            }

            #mseReferralFormRoot.mse-form-compact .mse-field--span-2,
            #mseReferralFormRoot.mse-form-compact .mse-field--full {
                grid-column: auto;
            }
        }

        /*
         * Поиск пациента.
         * Поле исключено из общего compact-правила выше. Иконку рисуем сами,
         * а input используем как type=text — это убирает браузерную search-иконку
         * Firefox/WebKit и делает отступы одинаковыми во всех браузерах.
         */
        #mseReferralFormRoot #mseSectionPatient .mse-field.mse-patient-search {
            position: relative;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-field.mse-patient-search .mse-control-wrap {
            position: relative;
            display: block;
            width: 100%;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-field.mse-patient-search .mse-control-wrap > .bi-search {
            position: absolute;
            z-index: 3;
            top: 50%;
            left: 16px;
            display: grid;
            width: 20px;
            height: 20px;
            place-items: center;
            transform: translateY(-50%);
            color: #7c919a;
            font-size: 16px;
            line-height: 1;
            pointer-events: none;
        }

        #mseReferralFormRoot.mse-form-compact #mseSectionPatient .mse-field.mse-patient-search .mse-control-wrap > input#msePatientSearch[data-patient-search] {
            display: block;
            width: 100%;
            min-height: 48px !important;
            height: 48px !important;
            padding-top: 0 !important;
            padding-right: 50px !important;
            padding-bottom: 0 !important;
            padding-left: 48px !important;
            box-sizing: border-box !important;
            border: 1px solid #cbdcdf !important;
            border-radius: 14px !important;
            background: #fff !important;
            color: #243b45 !important;
            font-size: 15px !important;
            line-height: 48px !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            box-shadow: 0 1px 2px rgba(21, 57, 68, .03) !important;
            transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }

        #mseReferralFormRoot.mse-form-compact #mseSectionPatient .mse-field.mse-patient-search .mse-control-wrap > input#msePatientSearch[data-patient-search]::placeholder {
            color: #8b9aa3;
            opacity: 1;
        }

        #mseReferralFormRoot.mse-form-compact #mseSectionPatient .mse-field.mse-patient-search .mse-control-wrap > input#msePatientSearch[data-patient-search]:hover {
            border-color: #adc7cc !important;
        }

        #mseReferralFormRoot.mse-form-compact #mseSectionPatient .mse-field.mse-patient-search .mse-control-wrap > input#msePatientSearch[data-patient-search]:focus {
            border-color: #0796a4 !important;
            outline: 0 !important;
            box-shadow: 0 0 0 3px rgba(7, 150, 164, .10) !important;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-field.mse-patient-search .mse-clear {
            position: absolute;
            z-index: 4;
            top: 50%;
            right: 9px;
            display: inline-grid;
            width: 32px;
            height: 32px;
            padding: 0 !important;
            place-items: center;
            transform: translateY(-50%);
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #81959d;
            cursor: pointer;
            transition: background .14s ease, color .14s ease;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-field.mse-patient-search .mse-clear:hover,
        #mseReferralFormRoot #mseSectionPatient .mse-field.mse-patient-search .mse-clear:focus-visible {
            outline: 0;
            background: #eef5f6;
            color: #31515c;
        }

        /* Компактная сводка после выбора пациента. */
        #mseReferralFormRoot #mseSectionPatient .mse-patient-summary-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
            gap: 10px;
            margin-top: 10px;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-selected-patient,
        #mseReferralFormRoot #mseSectionPatient .mse-live-diagnosis {
            min-height: 76px;
            margin: 0;
            padding: 12px 14px;
            border: 1px solid #dce8eb;
            border-radius: 14px;
            background: #f8fbfc;
            box-shadow: none;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-selected-patient {
            gap: 11px;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-selected-patient__avatar {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            border-radius: 12px;
            background: #fff;
            color: #0793a0;
            box-shadow: inset 0 0 0 1px #e4edef;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-selected-patient__copy strong {
            color: #2a414b;
            font-size: 14px;
            line-height: 1.25;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-selected-patient__copy span {
            margin-top: 3px;
            color: #82949c;
            font-size: 11px;
            line-height: 1.35;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-selected-patient.is-empty {
            opacity: 1;
            background: #fafcfd;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-live-diagnosis {
            display: flex;
            align-items: center;
            gap: 11px;
            color: #405d67;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-live-diagnosis > i {
            display: grid;
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            place-items: center;
            border-radius: 11px;
            background: #eaf8f9;
            color: #078b98;
            font-size: 17px;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-live-diagnosis span {
            color: #7d929b;
            font-size: 10px;
            letter-spacing: .055em;
            text-transform: uppercase;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-live-diagnosis strong {
            margin-top: 3px;
            color: #344f5a;
            font-size: 14px;
            line-height: 1.25;
        }

        #mseReferralFormRoot #mseSectionPatient .mse-patient-autofill-note {
            margin-top: 10px;
            padding: 8px 10px;
            border: 0;
            border-left: 3px solid #93ced4;
            border-radius: 0 9px 9px 0;
            background: #f7fbfc;
            color: #607982;
        }

        @media (max-width: 820px) {
            #mseReferralFormRoot #mseSectionPatient .mse-patient-summary-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        /*
         * Компактная полировка формы МСЭ.
         * Не увеличиваем блоки: режимы согласия, загрузка и подписи
         * должны занимать минимум высоты и оставаться легко читаемыми.
         */

        /* Заголовки подразделов: текст + аккуратная линия, без наложений. */
        #mseReferralFormRoot .mse-section-caption {
            display: flex;
            align-items: center;
            gap: 9px;
            width: 100%;
            margin-top: 17px;
            margin-bottom: 10px;
            color: #566d7c;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.2;
        }

        #mseReferralFormRoot .mse-section-caption::after {
            content: '';
            flex: 1 1 auto;
            height: 1px;
            background: #dbe6ea;
        }

        #mseReferralFormRoot .mse-section-caption i {
            flex: 0 0 auto;
            color: #607c8b;
        }

        /* Карточка текущей медицинской организации. */
        #mseReferralFormRoot #mseSectionCommission .mse-provider-line {
            margin: 7px 0 14px;
            padding: 11px 13px;
            border-radius: 14px;
            box-shadow: none;
        }

        #mseReferralFormRoot #mseSectionCommission .mse-provider-line__icon {
            width: 36px;
            height: 36px;
            flex-basis: 36px;
            border-radius: 11px;
        }

        #mseReferralFormRoot #mseSectionCommission .mse-provider-line strong {
            font-size: 13px;
            line-height: 1.2;
        }

        #mseReferralFormRoot #mseSectionCommission .mse-provider-line span span {
            margin-top: 2px;
            font-size: 11px;
            line-height: 1.3;
        }

        /* Верх блока согласия. */
        #mseReferralFormRoot .mse-consent-workflow__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px 18px;
            margin-bottom: 14px;
        }

        #mseReferralFormRoot .mse-consent-workflow__head > div {
            min-width: 0;
            flex: 1 1 auto;
        }

        #mseReferralFormRoot .mse-consent-workflow__head h3 {
            margin-top: 4px;
            margin-bottom: 4px;
            font-size: 18px;
            line-height: 1.25;
        }

        #mseReferralFormRoot .mse-consent-workflow__head p {
            max-width: 760px;
            margin: 0;
            font-size: 12px;
            line-height: 1.45;
        }

        #mseReferralFormRoot .mse-consent-upload__status {
            position: static !important;
            flex: 0 0 auto;
            min-height: 34px;
            padding: 7px 11px;
            border-radius: 999px;
            white-space: nowrap;
            font-size: 11px;
            line-height: 1.15;
        }

        /*
         * Два режима печати — компактные горизонтальные карточки.
         * Сбрасываем абсолютное позиционирование и большие размеры из общего partial.
         */
        #mseReferralFormRoot .mse-consent-mode-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 12px !important;
            align-items: stretch !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card {
            position: relative !important;
            display: grid !important;
            grid-template-columns: 54px minmax(0, 1fr) auto !important;
            grid-template-rows: auto auto !important;
            grid-template-areas:
                "icon copy badge"
                "icon copy button" !important;
            gap: 7px 13px !important;
            align-items: center !important;
            min-width: 0 !important;
            min-height: 0 !important;
            height: auto !important;
            padding: 15px !important;
            border-radius: 17px !important;
            overflow: hidden !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card::before,
        #mseReferralFormRoot .mse-consent-mode-card::after {
            display: none !important;
            content: none !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card__icon {
            position: static !important;
            grid-area: icon !important;
            display: grid !important;
            width: 54px !important;
            height: 54px !important;
            min-width: 54px !important;
            min-height: 54px !important;
            margin: 0 !important;
            padding: 0 !important;
            place-items: center !important;
            border-radius: 15px !important;
            font-size: 23px !important;
            line-height: 1 !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card__badge {
            position: static !important;
            inset: auto !important;
            grid-area: badge !important;
            justify-self: end !important;
            align-self: start !important;
            width: auto !important;
            max-width: none !important;
            min-height: 25px !important;
            margin: 0 !important;
            padding: 5px 9px !important;
            border-radius: 999px !important;
            white-space: nowrap !important;
            font-size: 10px !important;
            line-height: 1.1 !important;
            letter-spacing: .03em !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card__copy {
            position: static !important;
            grid-area: copy !important;
            align-self: center !important;
            width: auto !important;
            min-width: 0 !important;
            max-width: none !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card__copy strong {
            display: block !important;
            width: auto !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            font-size: 15px !important;
            line-height: 1.25 !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card__copy span {
            display: block !important;
            width: auto !important;
            max-width: none !important;
            margin: 4px 0 0 !important;
            padding: 0 !important;
            font-size: 11px !important;
            line-height: 1.35 !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card .mse-button {
            position: static !important;
            grid-area: button !important;
            justify-self: end !important;
            align-self: end !important;
            width: auto !important;
            min-width: 170px !important;
            min-height: 38px !important;
            height: 38px !important;
            margin: 0 !important;
            padding: 0 13px !important;
            border-radius: 11px !important;
            font-size: 11px !important;
            line-height: 1 !important;
            white-space: nowrap !important;
            box-shadow: none !important;
        }

        /* Пояснение между режимами и загрузкой — одна спокойная строка. */
        #mseReferralFormRoot .mse-consent-workflow__note {
            margin-top: 11px;
            padding: 9px 11px;
            border-radius: 12px;
            font-size: 11px;
            line-height: 1.4;
        }

        /* Загрузка подписанного файла: компактная строка вместо большой зоны. */
        #mseReferralFormRoot .mse-consent-upload-stage {
            margin-top: 12px !important;
            padding: 14px !important;
            border-radius: 17px !important;
        }

        #mseReferralFormRoot .mse-consent-upload-stage__head {
            display: flex !important;
            align-items: center !important;
            gap: 11px !important;
            margin-bottom: 11px !important;
        }

        #mseReferralFormRoot .mse-consent-upload-stage__icon {
            display: grid !important;
            width: 38px !important;
            height: 38px !important;
            flex: 0 0 38px !important;
            place-items: center !important;
            border-radius: 11px !important;
            font-size: 18px !important;
        }

        #mseReferralFormRoot .mse-consent-upload-stage__head strong {
            font-size: 13px !important;
            line-height: 1.25 !important;
        }

        #mseReferralFormRoot .mse-consent-upload-stage__head span {
            font-size: 11px !important;
            line-height: 1.35 !important;
        }

        #mseReferralFormRoot .mse-upload-dropzone {
            display: grid !important;
            grid-template-columns: 44px minmax(0, 1fr) auto !important;
            gap: 11px !important;
            align-items: center !important;
            min-height: 0 !important;
            padding: 11px 12px !important;
            border-width: 1px !important;
            border-radius: 14px !important;
        }

        #mseReferralFormRoot .mse-upload-dropzone__icon {
            display: grid !important;
            width: 44px !important;
            height: 44px !important;
            min-width: 44px !important;
            min-height: 44px !important;
            margin: 0 !important;
            place-items: center !important;
            border-radius: 12px !important;
            font-size: 20px !important;
        }

        #mseReferralFormRoot .mse-upload-dropzone__copy {
            min-width: 0 !important;
        }

        #mseReferralFormRoot .mse-upload-dropzone__copy strong {
            display: block !important;
            margin: 0 !important;
            font-size: 13px !important;
            line-height: 1.25 !important;
        }

        #mseReferralFormRoot .mse-upload-dropzone__copy span {
            display: block !important;
            margin-top: 3px !important;
            font-size: 10px !important;
            line-height: 1.3 !important;
        }

        #mseReferralFormRoot .mse-consent-upload-button {
            width: auto !important;
            min-width: 220px !important;
            min-height: 40px !important;
            height: 40px !important;
            padding: 0 15px !important;
            border-radius: 12px !important;
            white-space: nowrap !important;
            font-size: 11px !important;
            box-shadow: none !important;
        }

        /* Состав комиссии — без огромной табличной шапки. */
        #mseReferralFormRoot #mseSectionSignatures [data-repeater="commission-members"] {
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        #mseReferralFormRoot #mseSectionSignatures [data-repeater="commission-members"] table {
            width: 100% !important;
            min-width: 0 !important;
            border-collapse: collapse !important;
        }

        #mseReferralFormRoot #mseSectionSignatures [data-repeater="commission-members"] thead {
            display: none !important;
        }

        #mseReferralFormRoot #mseSectionSignatures [data-repeater="commission-members"] tbody tr {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) 40px !important;
            gap: 8px !important;
            align-items: center !important;
            margin-bottom: 8px !important;
        }

        #mseReferralFormRoot #mseSectionSignatures [data-repeater="commission-members"] tbody td {
            display: block !important;
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
        }

        #mseReferralFormRoot #mseSectionSignatures [data-repeater="commission-members"] input {
            width: 100% !important;
        }

        #mseReferralFormRoot #mseSectionSignatures [data-repeater="commission-members"] .mse-repeater__remove {
            width: 38px !important;
            height: 38px !important;
            border-radius: 10px !important;
        }

        #mseReferralFormRoot #mseSectionSignatures [data-repeater="commission-members"] + .mse-repeater__footer {
            display: flex !important;
            justify-content: flex-start !important;
            margin-top: 5px !important;
        }

        #mseReferralFormRoot #mseSectionSignatures [data-repeater="commission-members"] + .mse-repeater__footer .mse-button {
            min-height: 40px !important;
            height: 40px !important;
            padding: 0 14px !important;
            border-radius: 12px !important;
            font-size: 12px !important;
        }

        @media (max-width: 1120px) {
            #mseReferralFormRoot .mse-consent-mode-grid {
                grid-template-columns: minmax(0, 1fr) !important;
            }
        }

        @media (max-width: 720px) {
            #mseReferralFormRoot .mse-consent-workflow__head {
                flex-direction: column;
            }

            #mseReferralFormRoot .mse-consent-mode-card {
                grid-template-columns: 48px minmax(0, 1fr) !important;
                grid-template-areas:
                    "icon badge"
                    "icon copy"
                    "button button" !important;
            }

            #mseReferralFormRoot .mse-consent-mode-card .mse-button,
            #mseReferralFormRoot .mse-consent-upload-button {
                width: 100% !important;
                min-width: 0 !important;
            }

            #mseReferralFormRoot .mse-upload-dropzone {
                grid-template-columns: 44px minmax(0, 1fr) !important;
            }

            #mseReferralFormRoot .mse-consent-upload-button {
                grid-column: 1 / -1;
            }
        }


        /*
         * Баланс текста в карточках согласия.
         * Кнопка больше не занимает отдельную правую колонку —
         * поэтому заголовок и описание получают нормальную ширину.
         */
        #mseReferralFormRoot .mse-consent-mode-card {
            grid-template-columns: 58px minmax(0, 1fr) !important;
            grid-template-rows: auto auto auto !important;
            grid-template-areas:
                "icon badge"
                "icon copy"
                "icon button" !important;
            gap: 6px 14px !important;
            align-items: start !important;
            min-height: 0 !important;
            padding: 16px !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card__icon {
            grid-area: icon !important;
            align-self: center !important;
            width: 58px !important;
            height: 58px !important;
            min-width: 58px !important;
            min-height: 58px !important;
            border-radius: 16px !important;
            font-size: 24px !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card__badge {
            grid-area: badge !important;
            justify-self: start !important;
            align-self: start !important;
            white-space: nowrap !important;
            margin: 0 !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card__copy {
            grid-area: copy !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card__copy strong {
            max-width: none !important;
            font-size: 16px !important;
            line-height: 1.2 !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card__copy span {
            max-width: none !important;
            margin-top: 4px !important;
            font-size: 12px !important;
            line-height: 1.42 !important;
        }

        #mseReferralFormRoot .mse-consent-mode-card .mse-button {
            grid-area: button !important;
            justify-self: start !important;
            width: auto !important;
            min-width: 0 !important;
            max-width: 100% !important;
            min-height: 40px !important;
            height: 40px !important;
            padding: 0 15px !important;
            margin-top: 3px !important;
            font-size: 12px !important;
            white-space: nowrap !important;
        }

        @media (max-width: 720px) {
            #mseReferralFormRoot .mse-consent-mode-card {
                grid-template-columns: 50px minmax(0, 1fr) !important;
                grid-template-areas:
                    "icon badge"
                    "icon copy"
                    "button button" !important;
            }

            #mseReferralFormRoot .mse-consent-mode-card .mse-button {
                justify-self: stretch !important;
                width: 100% !important;
            }
        }



        /*
         * Карточка медицинской организации.
         * Уводим иконку от края и выравниваем содержимое по вертикали.
         */
        #mseReferralFormRoot #mseSectionCommission .mse-provider-line {
            display: grid !important;
            grid-template-columns: 48px minmax(0, 1fr) !important;
            gap: 14px !important;
            align-items: center !important;
            margin: 10px 0 16px !important;
            padding: 16px 18px !important;
            border: 1px solid #dfe8ec !important;
            border-radius: 17px !important;
            background: #f8fbfc !important;
            box-shadow: none !important;
        }

        #mseReferralFormRoot #mseSectionCommission .mse-provider-line__icon {
            display: grid !important;
            width: 48px !important;
            height: 48px !important;
            min-width: 48px !important;
            min-height: 48px !important;
            margin: 0 !important;
            place-items: center !important;
            border-radius: 15px !important;
            background: #eaf6f8 !important;
            color: #0a8591 !important;
            font-size: 20px !important;
            line-height: 1 !important;
        }

        #mseReferralFormRoot #mseSectionCommission .mse-provider-line > span:last-child {
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            min-width: 0 !important;
            margin: 0 !important;
        }

        #mseReferralFormRoot #mseSectionCommission .mse-provider-line > span:last-child > strong {
            display: block !important;
            margin: 0 !important;
            color: #243d4d !important;
            font-size: 14px !important;
            line-height: 1.25 !important;
        }

        #mseReferralFormRoot #mseSectionCommission .mse-provider-line > span:last-child > span {
            display: block !important;
            margin: 4px 0 0 !important;
            color: #6f8290 !important;
            font-size: 11px !important;
            line-height: 1.35 !important;
        }

        #mseReferralFormRoot #mseSectionCommission .mse-provider-line.is-private {
            border-color: #eadfbf !important;
            background: #fffcf3 !important;
        }

        #mseReferralFormRoot #mseSectionCommission .mse-provider-line.is-private .mse-provider-line__icon {
            background: #fff1c9 !important;
            color: #9b6a00 !important;
        }


        /*
         * Исправление inline-checkbox.
         *
         * В общем partial есть правило `.mse-field label { display: block; }`
         * и `.mse-field input { width: 100%; min-height: 46px; }`.
         * Они имеют большую/равную специфичность и ломают `.mse-inline-check`:
         * checkbox оказывается отдельной строкой, а текст съезжает вниз.
         */
        #mseReferralFormRoot .mse-field label.mse-inline-check {
            display: inline-flex !important;
            align-items: center !important;
            gap: 9px !important;
            width: auto !important;
            max-width: 100%;
            margin-bottom: 0 !important;
            vertical-align: middle;
            cursor: pointer;
        }

        #mseReferralFormRoot .mse-field label.mse-inline-check > input[type="checkbox"] {
            display: block !important;
            width: 18px !important;
            min-width: 18px !important;
            max-width: 18px !important;
            height: 18px !important;
            min-height: 18px !important;
            max-height: 18px !important;
            flex: 0 0 18px !important;
            margin: 0 !important;
            padding: 0 !important;
            border-radius: 5px;
            box-sizing: border-box !important;
            accent-color: var(--mse-accent);
        }

        #mseReferralFormRoot .mse-field label.mse-inline-check > span {
            display: inline-block !important;
            min-width: 0;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.3;
        }

        /*
         * Конкретно строка "место жительства отсутствует":
         * немного воздуха после адреса, но checkbox и подпись остаются одной строкой.
         */
        #mseReferralFormRoot #mseSectionPatient input[name="patient_homeless"] + label,
        #mseReferralFormRoot #mseSectionPatient label.mse-inline-check {
            color: #455b72;
        }

        /*
         * Справочник критериев МСЭ и контроль допуска к оформлению.
         * Это помощник врачу, а не автоматическое решение об инвалидности.
         */
        #mseReferralFormRoot .mse-criteria-panel {
            margin-top: 14px;
            padding: 15px 16px;
            border: 1px solid #d8e6e9;
            border-radius: 16px;
            background: #f8fbfc;
        }

        #mseReferralFormRoot .mse-criteria-panel__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 11px;
        }

        #mseReferralFormRoot .mse-criteria-panel__head strong {
            display: block;
            color: #294650;
            font-size: 14px;
            line-height: 1.3;
        }

        #mseReferralFormRoot .mse-criteria-panel__head small {
            display: block;
            margin-top: 3px;
            color: #71858d;
            font-size: 10px;
            line-height: 1.35;
        }

        #mseReferralFormRoot .mse-criteria-code {
            flex: 0 0 auto;
            padding: 5px 9px;
            border: 1px solid #cfe2e5;
            border-radius: 999px;
            background: #fff;
            color: #28707a;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        #mseReferralFormRoot .mse-criteria-intro {
            margin: 0 0 10px;
            color: #536b74;
            font-size: 11px;
            line-height: 1.45;
        }

        #mseReferralFormRoot .mse-criteria-rows {
            display: grid;
            gap: 7px;
        }

        #mseReferralFormRoot .mse-criteria-row {
            display: grid;
            grid-template-columns: 92px minmax(0, 1fr);
            gap: 10px;
            align-items: start;
            padding: 9px 10px;
            border: 1px solid #e0e9ec;
            border-radius: 12px;
            background: #fff;
        }

        #mseReferralFormRoot .mse-criteria-row__range {
            font-size: 12px;
            font-weight: 850;
            line-height: 1.3;
        }

        #mseReferralFormRoot .mse-criteria-row__copy strong,
        #mseReferralFormRoot .mse-criteria-row__copy span {
            display: block;
        }

        #mseReferralFormRoot .mse-criteria-row__copy strong {
            color: #334f59;
            font-size: 11px;
            line-height: 1.35;
        }

        #mseReferralFormRoot .mse-criteria-row__copy span {
            margin-top: 2px;
            color: #71838a;
            font-size: 10px;
            line-height: 1.4;
        }

        #mseReferralFormRoot .mse-criteria-panel__note,
        #mseReferralFormRoot .mse-criteria-exam {
            margin-top: 10px;
            padding: 9px 10px;
            border-left: 3px solid #91c7cd;
            border-radius: 0 9px 9px 0;
            background: #f2f8f9;
            color: #58717a;
            font-size: 10px;
            line-height: 1.45;
        }

        #mseReferralFormRoot .mse-criteria-exam {
            border-left-color: #c6a85a;
            background: #fffaf0;
        }

        #mseReferralFormRoot .mse-gate-confirmations {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        #mseReferralFormRoot .mse-gate-confirmation {
            display: flex;
            gap: 9px;
            align-items: flex-start;
            min-width: 0;
            padding: 11px 12px;
            border: 1px solid #dce7ea;
            border-radius: 13px;
            background: #fff;
            cursor: pointer;
        }

        #mseReferralFormRoot .mse-gate-confirmation input[type="checkbox"] {
            width: 18px !important;
            min-width: 18px !important;
            height: 18px !important;
            min-height: 18px !important;
            margin: 1px 0 0 !important;
            flex: 0 0 18px;
            accent-color: var(--mse-accent);
        }

        #mseReferralFormRoot .mse-gate-confirmation span {
            min-width: 0;
            color: #4b6570;
            font-size: 11px;
            line-height: 1.4;
        }

        #mseReferralFormRoot .mse-gate-confirmation strong {
            display: block;
            margin-bottom: 2px;
            color: #304d57;
            font-size: 11px;
        }

        #mseReferralFormRoot .mse-gate-card__status {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin: 8px 0 10px;
            padding: 9px 10px;
            border-radius: 11px;
            background: #fff7e7;
            color: #7a5b13;
            font-size: 11px;
            line-height: 1.35;
        }

        #mseReferralFormRoot .mse-gate-card.is-ready .mse-gate-card__status {
            background: #eef9f3;
            color: #2d6a4f;
        }

        #mseReferralFormRoot .mse-gate-list {
            display: grid;
            gap: 6px;
        }

        #mseReferralFormRoot .mse-gate-item {
            display: flex;
            gap: 7px;
            align-items: flex-start;
            color: #6a5660;
            font-size: 10px;
            line-height: 1.35;
        }

        #mseReferralFormRoot .mse-gate-item i {
            margin-top: 1px;
            color: #b76e79;
        }

        #mseReferralFormRoot .mse-gate-empty {
            color: #58736a;
            font-size: 10px;
            line-height: 1.4;
        }

        @media (max-width: 900px) {
            #mseReferralFormRoot .mse-gate-confirmations {
                grid-template-columns: minmax(0, 1fr);
            }
        }


        /* Возрастной профиль документов МСЭ. */
        #mseReferralFormRoot .mse-age-requirements {
            margin-top: 10px;
            padding: 13px 14px;
            border: 1px solid #d9e7eb;
            border-radius: 15px;
            background: #fbfdfe;
        }

        #mseReferralFormRoot .mse-age-requirements__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        #mseReferralFormRoot .mse-age-requirements__head > div {
            min-width: 0;
        }

        #mseReferralFormRoot .mse-age-requirements__head strong {
            display: block;
            color: #304c58;
            font-size: 13px;
            line-height: 1.25;
        }

        #mseReferralFormRoot .mse-age-requirements__head small {
            display: block;
            margin-top: 3px;
            color: #748a93;
            font-size: 10px;
            line-height: 1.35;
        }

        #mseReferralFormRoot .mse-age-requirements__badge {
            flex: 0 0 auto;
            padding: 5px 9px;
            border-radius: 999px;
            background: #e8f7f8;
            color: #087d89;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        #mseReferralFormRoot .mse-age-requirements__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        #mseReferralFormRoot .mse-age-requirement {
            min-width: 0;
            padding: 9px 10px;
            border: 1px solid #e4edef;
            border-radius: 11px;
            background: #fff;
        }

        #mseReferralFormRoot .mse-age-requirement strong,
        #mseReferralFormRoot .mse-age-requirement span {
            display: block;
        }

        #mseReferralFormRoot .mse-age-requirement strong {
            margin-bottom: 2px;
            color: #56717c;
            font-size: 9px;
            letter-spacing: .045em;
            text-transform: uppercase;
        }

        #mseReferralFormRoot .mse-age-requirement span {
            color: #344f5a;
            font-size: 11px;
            line-height: 1.4;
        }

        #mseReferralFormRoot .mse-age-requirement--wide {
            grid-column: 1 / -1;
        }

        #mseReferralFormRoot .mse-age-requirements__foot {
            margin-top: 9px;
            padding: 8px 10px;
            border-left: 3px solid #d2b464;
            border-radius: 0 9px 9px 0;
            background: #fffaf0;
            color: #6f5a22;
            font-size: 10px;
            line-height: 1.4;
        }

        #mseReferralFormRoot [data-under-three-fields].d-none {
            display: none !important;
        }

        #mseReferralFormRoot [data-under-three-fields] {
            margin-top: 10px;
            padding: 11px 12px;
            border: 1px solid #cfe5df;
            border-radius: 13px;
            background: #f5fbf9;
        }

        @media (max-width: 760px) {
            #mseReferralFormRoot .mse-age-requirements__grid {
                grid-template-columns: minmax(0, 1fr);
            }

            #mseReferralFormRoot .mse-age-requirement--wide {
                grid-column: auto;
            }
        }

    </style>

    @php
        $value = static function (string $name, mixed $default = '') use ($formValues): mixed {
            return old($name, data_get($formValues, $name, $default));
        };
        $checked = static fn (string $name): bool => (bool) old($name, data_get($formValues, $name, false));
        $invalid = static fn (string $name): string => $errors->has($name) ? 'is-invalid' : '';
        $selectedPurposes = collect($purposes ?? [])->map(fn ($item) => (string) $item)->all();
        $currentStatus = (string) $value('status', 'draft');
        $currentStatusLabel = (string) ($statusOptions[$currentStatus] ?? $currentStatus);
        $patientName = trim((string) data_get($initialPatient, 'full_name'));
        $patientBirth = trim((string) data_get($initialPatient, 'birth_at_display'));
        $patientDiagnosis = trim((string) data_get($initialPatient, 'diagnosis_label'));
        $patientMeta = collect([
            $patientBirth !== '' ? 'д. р. ' . $patientBirth : null,
            filled(data_get($initialPatient, 'phone')) ? data_get($initialPatient, 'phone') : null,
        ])->filter()->implode(' · ');
        $temporaryDisabilityRows = collect($temporaryDisabilityRows ?? [])->values()->all();
        $examinationRows = collect($examinationRows ?? [])->values()->all();
        $commissionMembers = collect($commissionMembers ?? [])->values()->all();
        $hasConsentDocument = $isEditing && $document->hasConsentDocument();
        $removeConsentDocument = (bool) old('remove_consent_document', false);

        /*
         * ИПРА относится к ранее признанной инвалидности / ранее разработанной программе.
         * При первичном направлении без предыдущей инвалидности блок не показываем.
         * Сохранённые данные не прячем насильно, чтобы старые документы не теряли сведения.
         */
        $hasPreviousIpraData = collect([
            $value('ipra_number'),
            $value('ipra_protocol_number'),
            $value('ipra_protocol_date'),
            $value('rehab_function_restoration'),
            $value('rehab_function_compensation'),
            $value('rehab_results_notes'),
        ])->contains(static fn (mixed $item): bool => filled($item));

        $showPreviousIpraInitial = (bool) data_get($initialPatient, 'disability', false)
            || filled($value('previous_disability_group'))
            || in_array('ipra', $selectedPurposes, true)
            || $hasPreviousIpraData;

        /*
         * Источник реквизитов медицинской организации.
         * Если у врача нет клиники, форма не показывает название приложения как организацию,
         * а явно помечает работу как частную практику.
         */
        $authDoctor = data_get(auth()->user(), 'doctor');
        $authDoctorFullName = collect([
            data_get($authDoctor, 'surname'),
            data_get($authDoctor, 'name'),
            data_get($authDoctor, 'patronym'),
        ])->filter()->implode(' ');
        $doctorClinicId = data_get($authDoctor, 'clinic_id');
        $doctorClinicName = data_get($authDoctor, 'clinic.0.name')
            ?? data_get($authDoctor, 'clinic.name');
        $doctorClinicAddress = data_get($authDoctor, 'clinic.0.address')
            ?? data_get($authDoctor, 'clinic.address');
        $doctorClinicOgrn = data_get($authDoctor, 'clinic.0.ogrn')
            ?? data_get($authDoctor, 'clinic.ogrn');
        $isPrivateDoctor = filled($authDoctor) && blank($doctorClinicId) && blank($doctorClinicName);
        $providerKindLabel = $isPrivateDoctor ? 'Частный врач' : 'Медицинская организация';
        $providerDescription = $isPrivateDoctor
            ? ($authDoctorFullName !== '' ? $authDoctorFullName . ' · частная практика' : 'Частная медицинская практика')
            : ($doctorClinicName ?: 'Реквизиты можно уточнить вручную');

        $organizationNameValue = trim((string) $value('organization_name'));
        if ($isPrivateDoctor && ($organizationNameValue === '' || (!$isEditing && $organizationNameValue === (string) config('app.name')))) {
            $organizationNameValue = 'Частный врач' . ($authDoctorFullName !== '' ? ' — ' . $authDoctorFullName : '');
        } elseif (!$isPrivateDoctor && $doctorClinicName && ($organizationNameValue === '' || (!$isEditing && $organizationNameValue === (string) config('app.name')))) {
            $organizationNameValue = (string) $doctorClinicName;
        }

        $organizationAddressValue = trim((string) $value('organization_address'));
        if ($organizationAddressValue === '' && $doctorClinicAddress) {
            $organizationAddressValue = (string) $doctorClinicAddress;
        }

        $organizationOgrnValue = trim((string) $value('organization_ogrn'));
        if ($organizationOgrnValue === '' && $doctorClinicOgrn) {
            $organizationOgrnValue = (string) $doctorClinicOgrn;
        }
    @endphp

    <main id="mseReferralFormRoot"
          class="mse-page mse-form-page mse-form-compact"
          data-patient-search-url="{{ route('api.patients.search') }}"
          data-patient-details-url-template="{{ route('api.patients.for.search', ['param' => '__ID__']) }}"
          data-address-suggest-url="{{ route('api-search-address') }}"
          data-consent-preview-url="{{ route('doctors.commissions.mse.consent-preview') }}"
          data-is-editing="{{ $isEditing ? '1' : '0' }}"
          data-has-consent-document="{{ $hasConsentDocument ? '1' : '0' }}"
          data-max-consent-file-size="15728640">
        <script id="mseInitialPatient" type="application/json">@json($initialPatient)</script>

        <div class="mse-container">
            <header class="mse-hero mse-form-hero">
                <div class="mse-hero__main">
                    <a class="mse-back" href="{{ route('doctors.commissions.mse.index') }}" title="Назад в журнал">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div class="mse-hero__icon"><i class="bi bi-universal-access"></i></div>
                    <div>
                        <div class="mse-eyebrow">МСЭ · форма 088/у · психиатрический профиль</div>
                        <h1>{{ $headingTitle }}</h1>
                        <p>Направление на МСЭ с акцентом на психические нарушения, их динамику и ограничения жизнедеятельности.</p>
                        <div class="mse-form-meta">
                            <span class="mse-form-chip"><i class="bi bi-file-earmark-medical"></i> Форма 088/у</span>
                            @if($isEditing && filled($document->document_number))
                                <span class="mse-form-chip"><i class="bi bi-hash"></i> {{ $document->document_number }}</span>
                            @endif
                            <span class="mse-form-chip mse-form-chip--accent" data-header-status>
                                <i class="bi bi-circle-fill"></i>
                                <span>{{ $currentStatusLabel }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mse-hero__actions">
                    <button class="mse-button mse-button--primary" type="submit" form="mseReferralForm" data-submit-button>
                        <i class="bi bi-check2-circle"></i> Сохранить
                    </button>
                    <button class="mse-button mse-button--consent" type="button"
                            data-consent-print data-consent-mode="auto">
                        <i class="bi bi-printer-fill"></i> Заполнить и распечатать
                    </button>
                    @if($isEditing)
                        <a class="mse-button mse-button--ghost"
                           href="{{ route('doctors.commissions.mse.show', $document) }}">
                            <i class="bi bi-eye"></i> Просмотр
                        </a>
                        <a class="mse-button mse-button--secondary" target="_blank" rel="noopener"
                           href="{{ route('doctors.commissions.mse.print', $document) }}">
                            <i class="bi bi-file-earmark-medical"></i> Печать 088/у
                        </a>
                    @endif
                </div>
            </header>

            <nav class="mse-progress" aria-label="Разделы формы">
                @foreach([
                    ['patient', '01', 'Пациент', 'Личные данные'],
                    ['commission', '02', 'Врачебная комиссия', 'Решение и цели'],
                    ['social', '03', 'Социальное функционирование', 'Инвалидность, учёба и работа'],
                    ['clinical', '04', 'Психиатрические данные', 'Анамнез и психический статус'],
                    ['rehabilitation', '05', 'Терапия и реабилитация', 'Прогноз и поддержка'],
                    ['signatures', '06', 'Подписи', 'Состав комиссии'],
                ] as [$key, $number, $label, $hint])
                    <a class="mse-progress__item {{ $loop->first ? 'is-active' : '' }}"
                       href="#mseSection{{ ucfirst($key) }}" data-progress-link="{{ $key }}">
                        <span class="mse-progress__number">{{ $number }}</span>
                        <span class="mse-progress__copy">
                            <strong>{{ $label }}</strong>
                            <small>{{ $hint }}</small>
                        </span>
                    </a>
                @endforeach
            </nav>

            @if($errors->any())
                <div class="mse-alert mse-alert--danger" role="alert">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                    <div>
                        <strong>Проверьте заполнение формы</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form id="mseReferralForm"
                  method="POST"
                  enctype="multipart/form-data"
                  action="{{ $isEditing ? route('doctors.commissions.mse.update', $document) : route('doctors.commissions.mse.store') }}"
                  data-mse-form>
                @csrf
                @if($isEditing)
                    @method('PUT')
                @endif

                <div class="mse-form-layout">
                    <div class="mse-form-main">
                        <section id="mseSectionPatient" class="mse-card mse-form-card" data-form-section="patient">
                            <div class="mse-card__head">
                                <span class="mse-card__icon"><i class="bi bi-person-vcard"></i></span>
                                <div>
                                    <h2>Пациент и персональные сведения</h2>
                                    <p>Пациент выбирается из картотеки; контактные и идентификационные данные можно уточнить.</p>
                                </div>
                                <span class="mse-step">01</span>
                            </div>

                            <div class="mse-field mse-field--full mse-patient-search">
                                <label for="msePatientSearch">Пациент <span class="required">*</span></label>
                                <div class="mse-control-wrap">
                                    <i class="bi bi-search"></i>
                                    <input id="msePatientSearch"
                                           class="{{ $invalid('patient_id') }}"
                                           type="text"
                                           value="{{ $patientName }}"
                                           autocomplete="off"
                                           placeholder="Начните вводить фамилию, имя или отчество"
                                           data-patient-search>
                                    <button class="mse-clear {{ $selectedPatientId ? '' : 'd-none' }}"
                                            type="button" title="Снять выбор" data-patient-clear>
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="patient_id" value="{{ $selectedPatientId }}" data-patient-id>
                                <div class="mse-suggestions d-none" data-patient-suggestions></div>
                                @error('patient_id')<small class="mse-field-error">{{ $message }}</small>@enderror
                                <small>Диагноз будет зафиксирован в направлении в момент сохранения.</small>
                            </div>

                            <div class="mse-patient-summary-grid">
                                <div class="mse-selected-patient {{ $selectedPatientId ? '' : 'is-empty' }}" data-patient-card>
                                    <span class="mse-selected-patient__avatar"><i class="bi bi-person-check"></i></span>
                                    <span class="mse-selected-patient__copy">
                                        <strong data-patient-name>{{ $patientName ?: 'Пациент не выбран' }}</strong>
                                        <span data-patient-meta>{{ $patientMeta ?: 'Выберите пациента из результатов поиска' }}</span>
                                    </span>
                                </div>

                                <div class="mse-live-diagnosis">
                                    <i class="bi bi-activity"></i>
                                    <div>
                                        <span>Основной диагноз</span>
                                        <strong data-patient-diagnosis>{{ $patientDiagnosis ?: 'не установлен' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="mse-patient-autofill-note" data-patient-autofill-note>
                                <i class="bi bi-stars"></i>
                                <span>
                                    После выбора пациента форма загружает подробные сведения из медицинской карты:
                                    пол, контакты, СНИЛС, ОМС, адреса, документ и основной диагноз. Поля остаются редактируемыми.
                                </span>
                            </div>

                            <article class="mse-age-requirements" data-mse-age-requirements>
                                <div class="mse-age-requirements__head">
                                    <div>
                                        <strong>Возрастные требования к документам МСЭ</strong>
                                        <small>Форма 088/у и перечень обследований отличаются по возрасту. Профиль пересчитывается автоматически после выбора пациента.</small>
                                    </div>
                                    <span class="mse-age-requirements__badge" data-mse-age-badge>Возраст не определён</span>
                                </div>
                                <div class="mse-age-requirements__grid" data-mse-age-body>
                                    <div class="mse-age-requirement mse-age-requirement--wide">
                                        <strong>Что нужно сделать</strong>
                                        <span>Выберите пациента — система покажет подходящий возрастной сценарий.</span>
                                    </div>
                                </div>
                                <div class="mse-age-requirements__foot">
                                    Это контроль комплектации документа, а не автоматическое решение о наличии инвалидности. Диагноз-зависимый перечень обследований врач подтверждает отдельно ниже.
                                </div>
                            </article>

                            <div class="mse-section-caption"><i class="bi bi-person-lines-fill"></i> Основные сведения</div>
                            <div class="mse-grid mse-grid--4">
                                <div class="mse-field">
                                    <label for="patientSex">Пол</label>
                                    <select id="patientSex" name="patient_sex" data-patient-field="sex">
                                        <option value="">Не указан</option>
                                        @foreach($sexOptions as $optionValue => $label)
                                            <option value="{{ $optionValue }}" @selected((string) $value('patient_sex') === (string) $optionValue)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mse-field">
                                    <label for="patientCitizenship">Гражданство</label>
                                    <select id="patientCitizenship" name="patient_citizenship"
                                            data-citizenship-select data-patient-field="citizenship">
                                        <option value="">Не указано</option>
                                        @foreach($citizenshipOptions as $optionValue => $label)
                                            <option value="{{ $optionValue }}" @selected((string) $value('patient_citizenship') === (string) $optionValue)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mse-field {{ $value('patient_citizenship') === 'foreign' ? '' : 'd-none' }}" data-foreign-country-wrap>
                                    <label for="patientCitizenshipCountry">Государство <span class="required">*</span></label>
                                    <input id="patientCitizenshipCountry" class="{{ $invalid('patient_citizenship_country') }}"
                                           name="patient_citizenship_country" value="{{ $value('patient_citizenship_country') }}"
                                           placeholder="Наименование государства" data-patient-field="citizenship_country">
                                    @error('patient_citizenship_country')<small class="mse-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="mse-field">
                                    <label for="patientMilitaryDuty">Воинская обязанность</label>
                                    <select id="patientMilitaryDuty" name="patient_military_duty" data-patient-field="military_duty">
                                        <option value="">Не указано</option>
                                        @foreach($militaryDutyOptions as $optionValue => $label)
                                            <option value="{{ $optionValue }}" @selected((string) $value('patient_military_duty') === (string) $optionValue)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mse-field mse-field--full">
                                    <label for="patientAddress">Адрес места жительства / пребывания</label>
                                    <div class="mse-address-control">
                                        <input id="patientAddress"
                                               name="patient_address"
                                               value="{{ $value('patient_address') }}"
                                               autocomplete="off"
                                               placeholder="Начните вводить населённый пункт, улицу, дом..."
                                               data-patient-field="address"
                                               data-address-autocomplete
                                               data-address-suggestions="#patientAddressSuggestions">
                                        <div id="patientAddressSuggestions" class="mse-address-suggestions d-none" data-address-suggestions-box></div>
                                    </div>
                                    <small>Подсказки адресов появляются автоматически после двух символов.</small>
                                </div>
                                <div class="mse-field mse-field--full">
                                    <input type="hidden" name="patient_homeless" value="0">
                                    <label class="mse-inline-check mb-0">
                                        <input type="checkbox" name="patient_homeless" value="1" @checked($checked('patient_homeless'))>
                                        <span>Определённое место жительства отсутствует</span>
                                    </label>
                                </div>
                            </div>

                            <div class="mse-section-caption"><i class="bi bi-telephone"></i> Контакты и документ</div>
                            <div class="mse-grid mse-grid--4">
                                <div class="mse-field">
                                    <label for="patientPhone">Телефон</label>
                                    <input id="patientPhone" name="patient_phone" value="{{ $value('patient_phone') }}" data-patient-field="phone">
                                </div>
                                <div class="mse-field">
                                    <label for="patientEmail">Электронная почта</label>
                                    <input id="patientEmail" class="{{ $invalid('patient_email') }}" type="email"
                                           name="patient_email" value="{{ $value('patient_email') }}" data-patient-field="email">
                                    @error('patient_email')<small class="mse-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="mse-field">
                                    <label for="patientSnils">СНИЛС</label>
                                    <input id="patientSnils" name="patient_snils" value="{{ $value('patient_snils') }}" data-patient-field="snils">
                                </div>
                                <div class="mse-field">
                                    <label for="patientOms">Полис ОМС</label>
                                    <input id="patientOms" name="patient_oms" value="{{ $value('patient_oms') }}" data-patient-field="oms">
                                </div>
                                <div class="mse-field mse-field--span-2">
                                    <label for="patientIdentityName">Документ, удостоверяющий личность</label>
                                    <input id="patientIdentityName" name="patient_identity_name"
                                           value="{{ $value('patient_identity_name') }}" data-patient-field="identity_name">
                                </div>
                                <div class="mse-field">
                                    <label for="patientIdentitySeries">Серия</label>
                                    <input id="patientIdentitySeries" name="patient_identity_series"
                                           value="{{ $value('patient_identity_series') }}" data-patient-field="identity_series">
                                </div>
                                <div class="mse-field">
                                    <label for="patientIdentityNumber">Номер</label>
                                    <input id="patientIdentityNumber" name="patient_identity_number"
                                           value="{{ $value('patient_identity_number') }}" data-patient-field="identity_number">
                                </div>
                                <div class="mse-field">
                                    <label for="patientIdentityIssueDate">Дата выдачи</label>
                                    <input id="patientIdentityIssueDate" type="date" name="patient_identity_issue_date"
                                           value="{{ $value('patient_identity_issue_date') }}" data-patient-field="identity_issue_date">
                                </div>
                                <div class="mse-field mse-field--span-2">
                                    <label for="patientIdentityIssuedBy">Кем выдан</label>
                                    <input id="patientIdentityIssuedBy" name="patient_identity_issued_by"
                                           value="{{ $value('patient_identity_issued_by') }}" data-patient-field="identity_issued_by">
                                </div>
                            </div>

                            <details class="mse-collapsible mse-collapsible--compact">
                                <summary><i class="bi bi-geo-alt"></i> Место нахождения пациента, если отличается от места проживания</summary>
                                <div class="mse-collapsible__body">
                                    <div class="mse-grid mse-grid--4">
                                        <div class="mse-field">
                                            <label for="patientLocationType">Тип места</label>
                                            <select id="patientLocationType" name="patient_location_type" data-patient-field="location_type">
                                                <option value="">Не указано</option>
                                                @foreach($locationTypeOptions as $optionValue => $label)
                                                    <option value="{{ $optionValue }}" @selected((string) $value('patient_location_type') === (string) $optionValue)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mse-field mse-field--span-2">
                                            <label for="patientLocationName">Организация / место</label>
                                            <input id="patientLocationName" name="patient_location_name"
                                                   value="{{ $value('patient_location_name') }}" data-patient-field="location_name">
                                        </div>
                                        <div class="mse-field">
                                            <label for="patientLocationOgrn">ОГРН</label>
                                            <input id="patientLocationOgrn" name="patient_location_ogrn"
                                                   value="{{ $value('patient_location_ogrn') }}" data-patient-field="location_ogrn">
                                        </div>
                                        <div class="mse-field mse-field--full">
                                            <label for="patientLocationAddress">Адрес места нахождения</label>
                                            <div class="mse-address-control">
                                                <input id="patientLocationAddress"
                                                       name="patient_location_address"
                                                       value="{{ $value('patient_location_address') }}"
                                                       autocomplete="off"
                                                       placeholder="Начните вводить адрес..."
                                                       data-patient-field="location_address"
                                                       data-address-autocomplete
                                                       data-address-suggestions="#patientLocationAddressSuggestions">
                                                <div id="patientLocationAddressSuggestions" class="mse-address-suggestions d-none" data-address-suggestions-box></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </details>

                            <details class="mse-collapsible mse-collapsible--compact"
                                     data-representative-block
                                     @if(old('representative_full_name') || $value('representative_full_name') || $value('guardian_organization_name')) open @endif>
                                <summary><i class="bi bi-people"></i> Законный представитель / организация, исполняющая обязанности представителя</summary>
                                <div class="mse-collapsible__body">
                                    <div class="mse-grid mse-grid--3">
                                        <div class="mse-field mse-field--span-2">
                                            <label for="representativeFullName">ФИО представителя</label>
                                            <input id="representativeFullName" name="representative_full_name"
                                                   value="{{ $value('representative_full_name') }}" data-patient-field="representative_full_name">
                                        </div>
                                        <div class="mse-field">
                                            <label for="representativeBirthDate">Дата рождения</label>
                                            <input id="representativeBirthDate" type="date" name="representative_birth_date"
                                                   value="{{ $value('representative_birth_date') }}" data-patient-field="representative_birth_date">
                                        </div>
                                        <div class="mse-field">
                                            <label for="representativePhone">Телефон</label>
                                            <input id="representativePhone" name="representative_phone"
                                                   value="{{ $value('representative_phone') }}" data-patient-field="representative_phone">
                                        </div>
                                        <div class="mse-field">
                                            <label for="representativeEmail">Электронная почта</label>
                                            <input id="representativeEmail" class="{{ $invalid('representative_email') }}" type="email"
                                                   name="representative_email" value="{{ $value('representative_email') }}" data-patient-field="representative_email">
                                            @error('representative_email')<small class="mse-field-error">{{ $message }}</small>@enderror
                                        </div>
                                        <div class="mse-field">
                                            <label for="representativeSnils">СНИЛС</label>
                                            <input id="representativeSnils" name="representative_snils"
                                                   value="{{ $value('representative_snils') }}" data-patient-field="representative_snils">
                                        </div>
                                        <div class="mse-field mse-field--full">
                                            <label for="representativeIdentity">Документ, удостоверяющий личность представителя</label>
                                            <input id="representativeIdentity" name="representative_identity"
                                                   value="{{ $value('representative_identity') }}" data-patient-field="representative_identity">
                                        </div>
                                        <div class="mse-field mse-field--full">
                                            <label for="representativeAuthorityDocument">Документ, подтверждающий полномочия</label>
                                            <input id="representativeAuthorityDocument" name="representative_authority_document"
                                                   value="{{ $value('representative_authority_document') }}" data-patient-field="representative_authority_document">
                                        </div>
                                    </div>

                                    <div class="mse-section-caption"><i class="bi bi-building"></i> Организация-представитель</div>
                                    <div class="mse-grid mse-grid--3">
                                        <div class="mse-field mse-field--span-2">
                                            <label for="guardianOrganizationName">Наименование</label>
                                            <input id="guardianOrganizationName" name="guardian_organization_name"
                                                   value="{{ $value('guardian_organization_name') }}" data-patient-field="guardian_organization_name">
                                        </div>
                                        <div class="mse-field">
                                            <label for="guardianOrganizationOgrn">ОГРН</label>
                                            <input id="guardianOrganizationOgrn" name="guardian_organization_ogrn"
                                                   value="{{ $value('guardian_organization_ogrn') }}" data-patient-field="guardian_organization_ogrn">
                                        </div>
                                        <div class="mse-field mse-field--full">
                                            <label for="guardianOrganizationAddress">Адрес</label>
                                            <div class="mse-address-control">
                                                <input id="guardianOrganizationAddress"
                                                       name="guardian_organization_address"
                                                       value="{{ $value('guardian_organization_address') }}"
                                                       autocomplete="off"
                                                       placeholder="Начните вводить адрес..."
                                                       data-patient-field="guardian_organization_address"
                                                       data-address-autocomplete
                                                       data-address-suggestions="#guardianOrganizationAddressSuggestions">
                                                <div id="guardianOrganizationAddressSuggestions" class="mse-address-suggestions d-none" data-address-suggestions-box></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </details>
                        </section>

                        <section id="mseSectionCommission" class="mse-card mse-form-card" data-form-section="commission">
                            <div class="mse-card__head">
                                <span class="mse-card__icon"><i class="bi bi-clipboard2-check"></i></span>
                                <div>
                                    <h2>Медицинская организация и решение врачебной комиссии</h2>
                                    <p>Реквизиты направления, протокола ВК, согласия и способа проведения экспертизы.</p>
                                </div>
                                <span class="mse-step">02</span>
                            </div>

                            <div class="mse-section-caption"><i class="bi bi-building"></i> Медицинская организация / частная практика</div>

                            <div class="mse-provider-line {{ $isPrivateDoctor ? 'is-private' : '' }}">
                                <span class="mse-provider-line__icon">
                                    <i class="bi {{ $isPrivateDoctor ? 'bi-person-badge' : 'bi-hospital' }}"></i>
                                </span>
                                <span>
                                    <strong>{{ $providerKindLabel }}</strong>
                                    <span>{{ $providerDescription }}</span>
                                </span>
                            </div>

                            <div class="mse-grid mse-grid--4">
                                <div class="mse-field mse-field--span-2">
                                    <label for="organizationName">Наименование</label>
                                    <input id="organizationName" name="organization_name"
                                           value="{{ $organizationNameValue }}"
                                           placeholder="{{ $isPrivateDoctor ? 'Частный врач — ФИО' : 'Наименование медицинской организации' }}">
                                </div>
                                <div class="mse-field">
                                    <label for="organizationOgrn">ОГРН</label>
                                    <input id="organizationOgrn" name="organization_ogrn"
                                           value="{{ $organizationOgrnValue }}"
                                           placeholder="{{ $isPrivateDoctor ? 'Не применяется / при наличии' : 'ОГРН' }}">
                                </div>
                                <div class="mse-field">
                                    <label for="bureauName">Бюро МСЭ</label>
                                    <input id="bureauName" name="bureau_name" value="{{ $value('bureau_name') }}"
                                           placeholder="Куда направляется пациент">
                                </div>
                                <div class="mse-field mse-field--full">
                                    <label for="organizationAddress">Адрес</label>
                                    <div class="mse-address-control">
                                        <input id="organizationAddress"
                                               name="organization_address"
                                               value="{{ $organizationAddressValue }}"
                                               autocomplete="off"
                                               placeholder="Начните вводить адрес..."
                                               data-address-autocomplete
                                               data-address-suggestions="#organizationAddressSuggestions">
                                        <div id="organizationAddressSuggestions" class="mse-address-suggestions d-none" data-address-suggestions-box></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mse-section-caption"><i class="bi bi-file-earmark-medical"></i> Реквизиты направления</div>
                            <div class="mse-grid mse-grid--3">
                                <div class="mse-field">
                                    <label for="documentDate">Дата направления <span class="required">*</span></label>
                                    <input id="documentDate" class="{{ $invalid('document_date') }}" type="date" required
                                           name="document_date" value="{{ $value('document_date') }}">
                                    @error('document_date')<small class="mse-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="mse-field">
                                    <label for="documentStatus">Статус <span class="required">*</span></label>
                                    <select id="documentStatus" name="status" required data-status-select>
                                        @foreach($statusOptions as $optionValue => $label)
                                            <option value="{{ $optionValue }}" @selected((string) $value('status') === (string) $optionValue)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mse-field">
                                    <label for="referralKind">Вид направления <span class="required">*</span></label>
                                    <select id="referralKind" name="referral_kind" required>
                                        @foreach($referralKindOptions as $optionValue => $label)
                                            <option value="{{ $optionValue }}" @selected((string) $value('referral_kind') === (string) $optionValue)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mse-field">
                                    <label for="protocolNumber">Номер протокола ВК <span class="required d-none" data-prepared-required>*</span></label>
                                    <input id="protocolNumber" class="{{ $invalid('protocol_number') }}"
                                           name="protocol_number" value="{{ $value('protocol_number') }}" data-required-when-prepared>
                                    @error('protocol_number')<small class="mse-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="mse-field">
                                    <label for="protocolDate">Дата протокола ВК <span class="required d-none" data-prepared-required>*</span></label>
                                    <input id="protocolDate" class="{{ $invalid('protocol_date') }}" type="date"
                                           name="protocol_date" value="{{ $value('protocol_date') }}" data-required-when-prepared>
                                    @error('protocol_date')<small class="mse-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="mse-field">
                                    <label for="consentDate">Дата письменного согласия <span class="required d-none" data-prepared-required>*</span></label>
                                    <input id="consentDate" class="{{ $invalid('consent_date') }}" type="date"
                                           name="consent_date" value="{{ $value('consent_date') }}" data-required-when-prepared>
                                    @error('consent_date')<small class="mse-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="mse-field">
                                    <label for="examinationFormat">Форма проведения МСЭ <span class="required">*</span></label>
                                    <select id="examinationFormat" name="examination_format" required>
                                        @foreach($examinationFormatOptions as $optionValue => $label)
                                            <option value="{{ $optionValue }}" @selected((string) $value('examination_format') === (string) $optionValue)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mse-field">
                                    <label for="notificationMethod">Предпочтительный способ уведомления</label>
                                    <select id="notificationMethod" name="notification_method">
                                        <option value="">Не указан</option>
                                        @foreach($notificationMethodOptions as $optionValue => $label)
                                            <option value="{{ $optionValue }}" @selected((string) $value('notification_method') === (string) $optionValue)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div id="mseConsentDocuments" class="mse-section-caption"><i class="bi bi-file-earmark-signature"></i> Согласие (разрешение) на направление и проведение МСЭ</div>
                            <div class="mse-consent-workflow {{ $errors->has('consent_document') ? 'has-error' : '' }}"
                                 data-consent-upload>
                                <div class="mse-consent-workflow__head">
                                    <div>
                                        <span class="mse-consent-workflow__eyebrow">Подготовка документа</span>
                                        <h3>Выберите способ подготовки согласия</h3>
                                        <p>Автоматический режим переносит сведения из текущей формы. Ручной режим печатает полностью пустой бланк для заполнения ручкой.</p>
                                    </div>
                                    <span class="mse-consent-upload__status {{ $hasConsentDocument && !$removeConsentDocument ? 'is-ready' : '' }}"
                                          data-consent-status>
                                        <i class="bi {{ $hasConsentDocument && !$removeConsentDocument ? 'bi-check-circle-fill' : 'bi-hourglass-split' }}"></i>
                                        <span>{{ $hasConsentDocument && !$removeConsentDocument ? 'Подписанное согласие загружено' : 'Ожидается подписанное согласие' }}</span>
                                    </span>
                                </div>

                                <div class="mse-consent-mode-grid" aria-label="Способ заполнения согласия">
                                    <article class="mse-consent-mode-card mse-consent-mode-card--auto">
                                        <span class="mse-consent-mode-card__icon"><i class="bi bi-magic"></i></span>
                                        <span class="mse-consent-mode-card__badge">Из формы</span>
                                        <div class="mse-consent-mode-card__copy">
                                            <strong>Автозаполнение</strong>
                                            <span>Подставит данные пациента, представителя, организации, цели и врача.</span>
                                        </div>
                                        <button class="mse-button mse-button--consent-auto"
                                                type="button"
                                                data-consent-print
                                                data-consent-mode="auto">
                                            <i class="bi bi-printer-fill"></i>
                                            Заполнить и распечатать
                                        </button>
                                    </article>

                                    <article class="mse-consent-mode-card mse-consent-mode-card--manual">
                                        <span class="mse-consent-mode-card__icon"><i class="bi bi-file-earmark-text"></i></span>
                                        <span class="mse-consent-mode-card__badge">Вручную</span>
                                        <div class="mse-consent-mode-card__copy">
                                            <strong>Пустой бланк</strong>
                                            <span>Распечатайте пустую форму и заполните её ручкой.</span>
                                        </div>
                                        <button class="mse-button mse-button--consent-manual"
                                                type="button"
                                                data-consent-print
                                                data-consent-mode="manual">
                                            <i class="bi bi-printer"></i>
                                            Распечатать пустой бланк
                                        </button>
                                    </article>
                                </div>

                                <div class="mse-consent-workflow__note">
                                    <i class="bi bi-printer"></i>
                                    <span><strong>Режимы не смешиваются.</strong> Автоматический бланк формируется из данных формы; ручной всегда остаётся пустым и предназначен только для заполнения на бумаге. Открытие печати не сохраняет направление.</span>
                                </div>

                                <article class="mse-consent-upload-stage">
                                    <div class="mse-consent-upload-stage__head">
                                        <span class="mse-consent-upload-stage__icon"><i class="bi bi-cloud-arrow-up"></i></span>
                                        <div>
                                            <strong>
                                                Загрузить заполненное и подписанное согласие
                                                <span class="required {{ $currentStatus === 'prepared' ? '' : 'd-none' }}" data-consent-required>*</span>
                                            </strong>
                                            <span>После подписи приложите PDF, JPG, PNG или WEBP размером не более 15 МБ.</span>
                                        </div>
                                    </div>

                                    <input type="hidden" name="remove_consent_document"
                                           value="{{ $removeConsentDocument ? '1' : '0' }}"
                                           data-consent-remove-input>

                                    <input id="consentDocument"
                                           class="mse-file-input"
                                           type="file"
                                           name="consent_document"
                                           accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                                           data-consent-file-input>

                                    <div class="mse-upload-dropzone" data-consent-dropzone>
                                        <span class="mse-upload-dropzone__icon"><i class="bi bi-file-earmark-arrow-up"></i></span>
                                        <span class="mse-upload-dropzone__copy">
                                            <strong data-consent-dropzone-title>{{ $hasConsentDocument && !$removeConsentDocument ? 'Заменить загруженный документ' : 'Файл ещё не выбран' }}</strong>
                                            <span>Нажмите кнопку или перетащите файл в эту область</span>
                                        </span>
                                        <label class="mse-button mse-button--primary mse-consent-upload-button"
                                               for="consentDocument">
                                            <i class="bi bi-upload"></i>
                                            <span data-consent-upload-button-label>{{ $hasConsentDocument && !$removeConsentDocument ? 'Заменить согласие' : 'Загрузить подписанное согласие' }}</span>
                                        </label>
                                    </div>
                                </article>

                                @if($hasConsentDocument)
                                    <div class="mse-uploaded-file {{ $removeConsentDocument ? 'd-none' : '' }}" data-consent-existing>
                                        <span class="mse-uploaded-file__icon">
                                            <i class="bi {{ $document->consent_document_mime_type === 'application/pdf' ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image' }}"></i>
                                        </span>
                                        <span class="mse-uploaded-file__copy">
                                            <strong>{{ $document->consentDocumentOriginalName() }}</strong>
                                            <span>
                                                {{ $document->consentDocumentSizeLabel() }}
                                                @if($document->consent_document_uploaded_at)
                                                    · загружен {{ $document->consent_document_uploaded_at->format('d.m.Y H:i') }}
                                                @endif
                                            </span>
                                        </span>
                                        <span class="mse-uploaded-file__actions">
                                            <a class="mse-uploaded-file__button" target="_blank" rel="noopener"
                                               href="{{ route('doctors.commissions.mse.consent-document', $document) }}">
                                                <i class="bi bi-eye"></i> Открыть
                                            </a>
                                            <a class="mse-uploaded-file__button" target="_blank" rel="noopener"
                                               href="{{ route('doctors.commissions.mse.consent-print', $document) }}">
                                                <i class="bi bi-pencil-square"></i> Открыть редактор
                                            </a>
                                            <button class="mse-uploaded-file__button mse-uploaded-file__button--danger"
                                                    type="button" data-consent-remove>
                                                <i class="bi bi-trash3"></i> Убрать
                                            </button>
                                        </span>
                                    </div>
                                @endif

                                <div class="mse-uploaded-file mse-uploaded-file--new d-none" data-consent-selected>
                                    <span class="mse-uploaded-file__icon"><i class="bi bi-file-earmark-check"></i></span>
                                    <span class="mse-uploaded-file__copy">
                                        <strong data-consent-selected-name>Новый файл</strong>
                                        <span data-consent-selected-meta></span>
                                    </span>
                                    <span class="mse-uploaded-file__actions">
                                        <button class="mse-uploaded-file__button mse-uploaded-file__button--danger"
                                                type="button" data-consent-clear-selection>
                                            <i class="bi bi-x-lg"></i> Отменить выбор
                                        </button>
                                    </span>
                                </div>

                                <div class="mse-upload-removal {{ $hasConsentDocument && $removeConsentDocument ? '' : 'd-none' }}"
                                     data-consent-removal>
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>
                                        <strong>Документ будет удалён после сохранения.</strong>
                                        Для статуса «Направление оформлено» необходимо загрузить другой файл.
                                    </span>
                                    <button type="button" data-consent-restore>Вернуть документ</button>
                                </div>

                                @error('consent_document')
                                <small class="mse-field-error">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mse-section-caption"><i class="bi bi-house-heart"></i> Особые условия проведения МСЭ</div>
                            <div class="mse-switch-row">
                                <label class="mse-switch-card">
                                    <input type="hidden" name="requires_home_examination" value="0">
                                    <input type="checkbox" name="requires_home_examination" value="1" @checked($checked('requires_home_examination'))>
                                    <span><strong>МСЭ необходимо провести на дому</strong><small>Психическое или общее состояние не позволяет пациенту явиться в бюро.</small></span>
                                </label>
                            </div>

                            {{-- Для психиатрического профиля эти узкосоматические опции не показываем, но явно сбрасываем. --}}
                            <input type="hidden" name="palliative_care" value="0">
                            <input type="hidden" name="primary_prosthetics" value="0">

                            <div class="mse-section-caption"><i class="bi bi-bullseye"></i> Цели проведения МСЭ</div>
                            <input type="hidden" name="purposes[]" value="">
                            <div class="mse-check-grid">
                                @foreach($purposeOptions as $optionValue => $label)
                                    @php
                                        $parts = preg_split('/^((?:5\.\d+\.)\s*)/u', $label, 2, PREG_SPLIT_DELIM_CAPTURE);
                                        $number = trim((string) ($parts[1] ?? ''));
                                        $purposeLabel = trim((string) ($parts[2] ?? $label));
                                    @endphp
                                    <label class="mse-check-card">
                                        <input type="checkbox" name="purposes[]" value="{{ $optionValue }}"
                                               @checked(in_array((string) $optionValue, $selectedPurposes, true)) data-purpose-checkbox>
                                        <span>
                                            <span>
                                                <strong>{{ $number }} {{ $purposeLabel }}</strong>
                                                <small>Отметьте, если эта цель указана в решении врачебной комиссии.</small>
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('purposes')<small class="mse-field-error">{{ $message }}</small>@enderror
                        </section>

                        <section id="mseSectionSocial" class="mse-card mse-form-card" data-form-section="social">
                            <div class="mse-card__head">
                                <span class="mse-card__icon"><i class="bi bi-briefcase"></i></span>
                                <div>
                                    <h2>Предыдущая МСЭ, образование и трудовая деятельность</h2>
                                    <p>Социально-профессиональные сведения, влияющие на оценку ограничений жизнедеятельности.</p>
                                </div>
                                <span class="mse-step">03</span>
                            </div>

                            <details class="mse-collapsible" open>
                                <summary><i class="bi bi-clock-history"></i> Результаты предыдущей медико-социальной экспертизы</summary>
                                <div class="mse-collapsible__body">
                                    <div class="mse-grid mse-grid--3">
                                        <div class="mse-field">
                                            <label for="previousDisabilityGroup">Ранее установленная группа / категория</label>
                                            <select id="previousDisabilityGroup" name="previous_disability_group">
                                                <option value="">Не устанавливалась / не указано</option>
                                                @foreach($disabilityGroupOptions as $optionValue => $label)
                                                    <option value="{{ $optionValue }}" @selected((string) $value('previous_disability_group') === (string) $optionValue)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mse-field">
                                            <label for="previousDisabilityUntil">Установлена до</label>
                                            <input id="previousDisabilityUntil" type="date" name="previous_disability_until" value="{{ $value('previous_disability_until') }}">
                                        </div>
                                        <div class="mse-field">
                                            <label for="previousDisabilityPeriod">Длительность инвалидности</label>
                                            <select id="previousDisabilityPeriod" name="previous_disability_period">
                                                <option value="">Не указано</option>
                                                @foreach($disabilityPeriodOptions as $optionValue => $label)
                                                    <option value="{{ $optionValue }}" @selected((string) $value('previous_disability_period') === (string) $optionValue)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mse-field">
                                            <label for="previousDisabilityCause">Причина инвалидности</label>
                                            <select id="previousDisabilityCause" name="previous_disability_cause">
                                                <option value="">Не указано</option>
                                                @foreach($disabilityCauseOptions as $optionValue => $label)
                                                    <option value="{{ $optionValue }}" @selected((string) $value('previous_disability_cause') === (string) $optionValue)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mse-field mse-field--full">
                                            <label for="previousDisabilityCauseOther">Уточнение иной причины</label>
                                            <textarea id="previousDisabilityCauseOther" name="previous_disability_cause_other" rows="2">{{ $value('previous_disability_cause_other') }}</textarea>
                                        </div>
                                        <div class="mse-field">
                                            <label for="previousProfessionalLossPercent">Утрата профессиональной трудоспособности, %</label>
                                            <input id="previousProfessionalLossPercent" type="number" min="0" max="100" step="0.1"
                                                   name="previous_professional_loss_percent" value="{{ $value('previous_professional_loss_percent') }}">
                                        </div>
                                        <div class="mse-field">
                                            <label for="previousProfessionalLossTerm">Срок</label>
                                            <input id="previousProfessionalLossTerm" name="previous_professional_loss_term" value="{{ $value('previous_professional_loss_term') }}">
                                        </div>
                                        <div class="mse-field">
                                            <label for="previousProfessionalLossUntil">Установлена до</label>
                                            <input id="previousProfessionalLossUntil" type="date" name="previous_professional_loss_until" value="{{ $value('previous_professional_loss_until') }}">
                                        </div>
                                        <div class="mse-field mse-field--full">
                                            <label for="previousProfessionalLossOther">Другие результаты предыдущей МСЭ</label>
                                            <textarea id="previousProfessionalLossOther" name="previous_professional_loss_other" rows="3">{{ $value('previous_professional_loss_other') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </details>

                            <div class="mse-section-caption"><i class="bi bi-mortarboard"></i> Образование</div>
                            <div class="mse-grid mse-grid--2">
                                <div class="mse-field mse-field--full">
                                    <label for="educationOrganization">Образовательная организация</label>
                                    <input id="educationOrganization" name="education_organization" value="{{ $value('education_organization') }}">
                                </div>
                                <div class="mse-field">
                                    <label for="educationLevel">Уровень образования</label>
                                    <input id="educationLevel" name="education_level" value="{{ $value('education_level') }}">
                                </div>
                                <div class="mse-field">
                                    <label for="educationProfession">Профессия / специальность / квалификация</label>
                                    <input id="educationProfession" name="education_profession" value="{{ $value('education_profession') }}">
                                </div>
                            </div>

                            <div class="mse-section-caption"><i class="bi bi-person-workspace"></i> Трудовая деятельность</div>
                            <div class="mse-grid mse-grid--3">
                                <div class="mse-field">
                                    <label for="employmentProfession">Основная профессия</label>
                                    <input id="employmentProfession" name="employment_profession" value="{{ $value('employment_profession') }}">
                                </div>
                                <div class="mse-field">
                                    <label for="employmentQualification">Квалификация</label>
                                    <input id="employmentQualification" name="employment_qualification" value="{{ $value('employment_qualification') }}">
                                </div>
                                <div class="mse-field">
                                    <label for="employmentExperience">Стаж</label>
                                    <input id="employmentExperience" name="employment_experience" value="{{ $value('employment_experience') }}">
                                </div>
                                <div class="mse-field mse-field--full">
                                    <label for="employmentCurrentWork">Выполняемая работа / должность</label>
                                    <textarea id="employmentCurrentWork" name="employment_current_work" rows="3">{{ $value('employment_current_work') }}</textarea>
                                </div>
                                <div class="mse-field mse-field--full">
                                    <label for="employmentConditions">Характер и условия труда</label>
                                    <textarea id="employmentConditions" name="employment_conditions" rows="3">{{ $value('employment_conditions') }}</textarea>
                                </div>
                                <div class="mse-field">
                                    <label for="employmentOrganization">Организация-работодатель</label>
                                    <input id="employmentOrganization" name="employment_organization" value="{{ $value('employment_organization') }}">
                                </div>
                                <div class="mse-field">
                                    <label for="employmentAddress">Адрес работодателя</label>
                                    <div class="mse-address-control">
                                        <input id="employmentAddress" name="employment_address"
                                               value="{{ $value('employment_address') }}"
                                               autocomplete="off"
                                               data-address-autocomplete
                                               data-address-suggestions="#employmentAddressSuggestions">
                                        <div id="employmentAddressSuggestions" class="mse-address-suggestions d-none" data-address-suggestions-box></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section id="mseSectionClinical" class="mse-card mse-form-card" data-form-section="clinical">
                            <div class="mse-card__head">
                                <span class="mse-card__icon"><i class="bi bi-heart-pulse"></i></span>
                                <div>
                                    <h2>Психиатрические клинико-функциональные данные</h2>
                                    <p>Анамнез, психический статус, динамика расстройства, обследования и клинический диагноз.</p>
                                </div>
                                <span class="mse-step">04</span>
                            </div>

                            <div class="mse-grid mse-grid--3">
                                <div class="mse-field">
                                    <label for="observedSinceDate">
                                        Дата начала наблюдения
                                    </label>
                                    <input id="observedSinceDate"
                                           class="{{ $invalid('observed_since_date') }}"
                                           type="date"
                                           name="observed_since_date"
                                           value="{{ $value('observed_since_date') }}"
                                           max="{{ $value('document_date', today()->format('Y-m-d')) }}"
                                           data-observed-since-date>
                                    <input type="hidden"
                                           name="observed_since_year"
                                           value="{{ $value('observed_since_year') }}"
                                           data-observed-since-year>
                                    @error('observed_since_date')<small class="mse-field-error">{{ $message }}</small>@enderror
                                    <small>Укажите начало наблюдения. Минимального обязательного срока наблюдения для направления на МСЭ нет; длительность нужна для оценки стойкости и документированной динамики.</small>
                                </div>
                                <div class="mse-note mse-field--full d-none"
                                     role="alert"
                                     aria-live="polite"
                                     data-observation-warning>
                                    <i class="bi bi-shield-exclamation"></i>
                                    <div>
                                        <strong>Короткий период наблюдения</strong>
                                        <span data-observation-warning-text></span>
                                    </div>
                                </div>
                                <div class="mse-field mse-field--full">
                                    <label for="diseaseHistory">Анамнез заболевания</label>
                                    <textarea id="diseaseHistory" name="disease_history" rows="6"
                                              placeholder="Начало, течение, обострения, госпитализации, динамика состояния...">{{ $value('disease_history') }}</textarea>
                                </div>
                                <div class="mse-field mse-field--full">
                                    <label for="lifeHistory">Анамнез жизни</label>
                                    <textarea id="lifeHistory" name="life_history" rows="5">{{ $value('life_history') }}</textarea>
                                </div>
                            </div>

                            <div class="mse-section-caption"><i class="bi bi-calendar2-week"></i> Периоды временной нетрудоспособности за последние 12 месяцев</div>
                            <div class="mse-repeater" data-repeater="temporary-disability">
                                <table>
                                    <thead>
                                    <tr>
                                        <th style="width: 145px">Начало</th>
                                        <th style="width: 145px">Окончание</th>
                                        <th style="width: 130px">Длительность</th>
                                        <th>Диагноз</th>
                                        <th style="width: 54px"></th>
                                    </tr>
                                    </thead>
                                    <tbody data-repeater-body>
                                    @foreach($temporaryDisabilityRows as $row)
                                        <tr data-repeater-row>
                                            <td><input type="date" name="temporary_disability[{{ $loop->index }}][start_date]" value="{{ data_get($row, 'start_date') }}"></td>
                                            <td><input type="date" name="temporary_disability[{{ $loop->index }}][end_date]" value="{{ data_get($row, 'end_date') }}"></td>
                                            <td><input name="temporary_disability[{{ $loop->index }}][duration]" value="{{ data_get($row, 'duration') }}" placeholder="Напр. 14 дней"></td>
                                            <td><textarea name="temporary_disability[{{ $loop->index }}][diagnosis]">{{ data_get($row, 'diagnosis') }}</textarea></td>
                                            <td><button class="mse-repeater__remove" type="button" data-remove-row title="Удалить строку"><i class="bi bi-trash3"></i></button></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mse-repeater__footer">
                                <button class="mse-button mse-button--secondary" type="button" data-add-row="temporary-disability">
                                    <i class="bi bi-plus-lg"></i> Добавить период
                                </button>
                            </div>

                            <div class="mse-grid mse-grid--2" style="margin-top: 16px">
                                <div class="mse-field">
                                    <input type="hidden" name="has_eln" value="0">
                                    <label class="mse-inline-check" style="margin-top: 34px">
                                        <input type="checkbox" name="has_eln" value="1" @checked($checked('has_eln')) data-eln-toggle>
                                        <span>Сформирован электронный листок нетрудоспособности</span>
                                    </label>
                                </div>
                                <div class="mse-field {{ $checked('has_eln') ? '' : 'd-none' }}" data-eln-number-wrap>
                                    <label for="elnNumber">Номер ЭЛН</label>
                                    <input id="elnNumber" name="eln_number" value="{{ $value('eln_number') }}">
                                </div>
                            </div>

                            <details class="mse-collapsible {{ $showPreviousIpraInitial ? '' : 'd-none' }}"
                                     data-previous-ipra-block
                                     @if($showPreviousIpraInitial) open @endif>
                                <summary><i class="bi bi-arrow-repeat"></i> Результаты предыдущей ИПРА</summary>
                                <div class="mse-collapsible__body">
                                    <div class="mse-note" style="margin-top: 0">
                                        <i class="bi bi-info-circle-fill"></i>
                                        <div>
                                            <strong>Заполняется только при наличии ранее разработанной ИПРА</strong>
                                            <span>При первичном установлении инвалидности без предыдущей ИПРА этот раздел не нужен. Он появляется при ранее установленной инвалидности, цели «Разработка или корректировка ИПРА» либо если в документе уже сохранены сведения ИПРА.</span>
                                        </div>
                                    </div>

                                    <div class="mse-grid mse-grid--3">
                                        <div class="mse-field">
                                            <label for="ipraNumber">Номер предыдущей ИПРА</label>
                                            <input id="ipraNumber" name="ipra_number" value="{{ $value('ipra_number') }}">
                                        </div>
                                        <div class="mse-field">
                                            <label for="ipraProtocolNumber">Номер протокола разработки ИПРА</label>
                                            <input id="ipraProtocolNumber" name="ipra_protocol_number" value="{{ $value('ipra_protocol_number') }}">
                                        </div>
                                        <div class="mse-field">
                                            <label for="ipraProtocolDate">Дата протокола</label>
                                            <input id="ipraProtocolDate" type="date" name="ipra_protocol_date" value="{{ $value('ipra_protocol_date') }}">
                                        </div>
                                        <div class="mse-field">
                                            <label for="rehabFunctionRestoration">Результат: восстановление нарушенных функций</label>
                                            <select id="rehabFunctionRestoration" name="rehab_function_restoration">
                                                <option value="">Не указано</option>
                                                @foreach($rehabilitationEffectOptions as $optionValue => $label)
                                                    <option value="{{ $optionValue }}" @selected((string) $value('rehab_function_restoration') === (string) $optionValue)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mse-field">
                                            <label for="rehabFunctionCompensation">Результат: компенсация нарушенных функций</label>
                                            <select id="rehabFunctionCompensation" name="rehab_function_compensation">
                                                <option value="">Не указано</option>
                                                @foreach($rehabilitationEffectOptions as $optionValue => $label)
                                                    <option value="{{ $optionValue }}" @selected((string) $value('rehab_function_compensation') === (string) $optionValue)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mse-field mse-field--full">
                                            <label for="rehabResultsNotes">Что было выполнено и какой получен результат</label>
                                            <textarea id="rehabResultsNotes" name="rehab_results_notes" rows="3"
                                                      placeholder="Какие мероприятия предыдущей ИПРА выполнены, их эффективность, причины невыполнения отдельных мероприятий...">{{ $value('rehab_results_notes') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </details>

                            {{--
                                П. 28 формы 088/у: рост, масса, ИМТ и телосложение остаются в форме даже для
                                психиатрического профиля. Узкоспециальные параметры показываем только при показаниях.
                            --}}
                            <details class="mse-collapsible mse-collapsible--compact">
                                <summary><i class="bi bi-rulers"></i> Обязательные физические параметры формы 088/у</summary>
                                <div class="mse-collapsible__body">
                                    <div class="mse-grid mse-grid--4">
                                        <div class="mse-field">
                                            <label for="heightCm">Рост, см</label>
                                            <input id="heightCm" type="number" min="20" max="250" step="0.1"
                                                   name="height_cm" value="{{ $value('height_cm') }}" data-height>
                                        </div>
                                        <div class="mse-field">
                                            <label for="weightKg">Масса тела, кг</label>
                                            <input id="weightKg" type="number" min="0.5" max="500" step="0.1"
                                                   name="weight_kg" value="{{ $value('weight_kg') }}" data-weight>
                                        </div>
                                        <div class="mse-field">
                                            <label for="bmi">ИМТ</label>
                                            <input id="bmi" type="number" min="1" max="150" step="0.1"
                                                   name="bmi" value="{{ $value('bmi') }}" data-bmi readonly>
                                            <small>Рассчитывается автоматически.</small>
                                        </div>
                                        <div class="mse-field">
                                            <label for="bodyBuild">Телосложение</label>
                                            <input id="bodyBuild" name="body_build" value="{{ $value('body_build') }}">
                                        </div>
                                    </div>
                                    <small>Рост, масса, ИМТ и телосложение относятся к общему п. 28 формы. Остальные поля показываются только когда они действительно применимы.</small>

                                    <div class="d-none" data-under-three-fields>
                                        <div class="mse-section-caption" style="margin-top:0"><i class="bi bi-balloon-heart"></i> Для ребёнка младше 3 лет</div>
                                        <div class="mse-grid mse-grid--3">
                                            <div class="mse-field">
                                                <label for="birthWeightG">Масса тела при рождении, г <span class="required">*</span></label>
                                                <input id="birthWeightG" type="number" min="100" max="10000" step="1"
                                                       name="birth_weight_g" value="{{ $value('birth_weight_g') }}">
                                                <small>П. 28.7 формы 088/у.</small>
                                            </div>
                                            <div class="mse-field mse-field--span-2">
                                                <label for="physicalDevelopment">Физическое развитие <span class="required">*</span></label>
                                                <textarea id="physicalDevelopment" name="physical_development" rows="2">{{ $value('physical_development') }}</textarea>
                                                <small>П. 28.8 формы 088/у.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <details class="mse-collapsible mse-collapsible--compact" style="margin-top: 10px">
                                        <summary><i class="bi bi-plus-circle"></i> Параметры только при специальных показаниях</summary>
                                        <div class="mse-collapsible__body">
                                            <div class="mse-grid mse-grid--3">
                                                <div class="mse-field">
                                                    <label for="dailyExcretionsMl">Суточные физиологические отправления, мл</label>
                                                    <input id="dailyExcretionsMl" type="number" min="0" max="20000"
                                                           name="daily_excretions_ml" value="{{ $value('daily_excretions_ml') }}">
                                                    <small>При нарушениях пищеварительной/мочевыделительной функции для подбора абсорбирующего белья.</small>
                                                </div>
                                                <div class="mse-field">
                                                    <label for="waistCm">Окружность талии, см</label>
                                                    <input id="waistCm" type="number" min="10" max="300" step="0.1"
                                                           name="waist_cm" value="{{ $value('waist_cm') }}">
                                                </div>
                                                <div class="mse-field">
                                                    <label for="hipCm">Окружность бёдер, см</label>
                                                    <input id="hipCm" type="number" min="10" max="300" step="0.1"
                                                           name="hip_cm" value="{{ $value('hip_cm') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </details>
                                </div>
                            </details>

                            <div class="mse-section-caption"><i class="bi bi-clipboard2-pulse"></i> Психическое состояние</div>
                            <div class="mse-grid mse-grid--2">
                                <div class="mse-field mse-field--full">
                                    <label for="healthStatus">Психический статус и стойкие нарушения психических функций</label>
                                    <textarea id="healthStatus" name="health_status" rows="8"
                                              placeholder="Сознание и ориентировка; контакт; мышление; настроение и аффект; восприятие; память и внимание; критика; воля и поведение; самообслуживание и социальное функционирование...">{{ $value('health_status') }}</textarea>
                                </div>
                                <div class="mse-field mse-field--full">
                                    <label for="complaints">Жалобы пациента и/или сведения со слов родственников</label>
                                    <textarea id="complaints" name="complaints" rows="4"
                                              placeholder="Жалобы, особенности поведения, сведения о снижении бытового, социального и профессионального функционирования...">{{ $value('complaints') }}</textarea>
                                </div>
                            </div>

                            <div class="mse-section-caption"><i class="bi bi-journal-medical"></i> Обследования, психодиагностика и консультации</div>
                            <div class="mse-repeater" data-repeater="examinations">
                                <table>
                                    <thead>
                                    <tr>
                                        <th style="width: 145px">Дата</th>
                                        <th style="width: 120px">Код</th>
                                        <th style="width: 260px">Наименование</th>
                                        <th>Результат</th>
                                        <th style="width: 54px"></th>
                                    </tr>
                                    </thead>
                                    <tbody data-repeater-body>
                                    @foreach($examinationRows as $row)
                                        <tr data-repeater-row>
                                            <td><input type="date" name="examinations[{{ $loop->index }}][date]" value="{{ data_get($row, 'date') }}"></td>
                                            <td><input name="examinations[{{ $loop->index }}][code]" value="{{ data_get($row, 'code') }}"></td>
                                            <td><textarea name="examinations[{{ $loop->index }}][name]">{{ data_get($row, 'name') }}</textarea></td>
                                            <td><textarea name="examinations[{{ $loop->index }}][result]">{{ data_get($row, 'result') }}</textarea></td>
                                            <td><button class="mse-repeater__remove" type="button" data-remove-row title="Удалить строку"><i class="bi bi-trash3"></i></button></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mse-repeater__footer">
                                <button class="mse-button mse-button--secondary" type="button" data-add-row="examinations">
                                    <i class="bi bi-plus-lg"></i> Добавить исследование
                                </button>
                            </div>

                            <div class="mse-live-diagnosis" style="margin-top: 20px">
                                <i class="bi bi-prescription2"></i>
                                <div>
                                    <span>Клинический диагноз</span>
                                    <strong data-patient-diagnosis>{{ $patientDiagnosis ?: 'не установлен' }}</strong>
                                </div>
                            </div>

                            <article class="mse-criteria-panel" data-mse-criteria-panel>
                                <div class="mse-criteria-panel__head">
                                    <div>
                                        <strong>Ориентиры МСЭ по основному диагнозу</strong>
                                        <small>Приказ Минтруда России № 374н. Справочник показывает нормативные диапазоны, но не определяет группу автоматически.</small>
                                    </div>
                                    <span class="mse-criteria-code" data-mse-criteria-code>МКБ не определён</span>
                                </div>
                                <div data-mse-criteria-body>
                                    <p class="mse-criteria-intro">Выберите пациента с установленным диагнозом — здесь появятся применимые критерии.</p>
                                </div>
                                <div class="mse-criteria-exam" data-mse-required-exams>
                                    Перечень обязательных обследований будет уточнён после определения кода МКБ.
                                </div>
                                <div class="mse-criteria-panel__note">
                                    Для взрослого сам диапазон 40–100% ещё не означает инвалидность: одновременно оцениваются ограничения жизнедеятельности. Окончательное решение принимает бюро МСЭ.
                                </div>
                            </article>

                            <div class="mse-section-caption"><i class="bi bi-shield-check"></i> Условия направления на МСЭ</div>
                            <div class="mse-gate-confirmations">
                                <label class="mse-gate-confirmation">
                                    <input type="checkbox" name="mse_gate_persistent" value="1" @checked($checked('mse_gate_persistent')) data-mse-gate-persistent>
                                    <span><strong>Стойкое нарушение функций подтверждено</strong>ВК располагает данными о стойком нарушении психических функций, обусловленном заболеванием.</span>
                                </label>
                                <label class="mse-gate-confirmation">
                                    <input type="checkbox" name="mse_gate_measures" value="1" @checked($checked('mse_gate_measures')) data-mse-gate-measures>
                                    <span><strong>Необходимые мероприятия проведены</strong>Диагностика, лечение и медицинская реабилитация выполнены в необходимом объёме, их результаты отражены в документации.</span>
                                </label>
                                <label class="mse-gate-confirmation">
                                    <input type="checkbox" name="mse_gate_exams" value="1" @checked($checked('mse_gate_exams')) data-mse-gate-exams>
                                    <span><strong>Возрастной и диагноз-зависимый перечень обследований проверен</strong>Применён нужный раздел приказа № 259н/238н (до 18 лет / 18+) с учётом кода МКБ, первичного или повторного направления и сроков действительности результатов.</span>
                                </label>
                            </div>
                            <small>Эти три отметки сохраняются вместе с направлением как внутренний контроль ВК. Черновик можно сохранить без них; статус «Оформлено» блокируется до подтверждения.</small>

                            <div class="mse-grid mse-grid--2">
                                <div class="mse-field mse-field--full">
                                    <label for="complications">Осложнения основного заболевания</label>
                                    <textarea id="complications" name="complications" rows="4">{{ $value('complications') }}</textarea>
                                </div>
                                <div class="mse-field">
                                    <label for="concomitantDiseases">Сопутствующие заболевания</label>
                                    <textarea id="concomitantDiseases" name="concomitant_diseases" rows="4">{{ $value('concomitant_diseases') }}</textarea>
                                </div>
                                <div class="mse-field">
                                    <label for="concomitantCodes">Коды МКБ сопутствующих заболеваний</label>
                                    <textarea id="concomitantCodes" name="concomitant_codes" rows="4">{{ $value('concomitant_codes') }}</textarea>
                                </div>
                            </div>
                        </section>

                        <section id="mseSectionRehabilitation" class="mse-card mse-form-card" data-form-section="rehabilitation">
                            <div class="mse-card__head">
                                <span class="mse-card__icon"><i class="bi bi-arrow-repeat"></i></span>
                                <div>
                                    <h2>Терапия, прогноз и психосоциальная реабилитация</h2>
                                    <p>Прогноз, психофармакотерапия, поддерживающее лечение и мероприятия по восстановлению социального функционирования.</p>
                                </div>
                                <span class="mse-step">05</span>
                            </div>

                            <div class="mse-grid mse-grid--3">
                                <div class="mse-field">
                                    <label for="clinicalPrognosis">Клинический прогноз</label>
                                    <select id="clinicalPrognosis" name="clinical_prognosis">
                                        <option value="">Не указан</option>
                                        @foreach($prognosisOptions as $optionValue => $label)
                                            <option value="{{ $optionValue }}" @selected((string) $value('clinical_prognosis') === (string) $optionValue)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mse-field">
                                    <label for="rehabilitationPotential">Реабилитационный потенциал</label>
                                    <select id="rehabilitationPotential" name="rehabilitation_potential">
                                        <option value="">Не указан</option>
                                        @foreach($rehabilitationPotentialOptions as $optionValue => $label)
                                            <option value="{{ $optionValue }}" @selected((string) $value('rehabilitation_potential') === (string) $optionValue)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mse-field">
                                    <label for="rehabilitationPrognosis">Реабилитационный прогноз</label>
                                    <select id="rehabilitationPrognosis" name="rehabilitation_prognosis">
                                        <option value="">Не указан</option>
                                        @foreach($prognosisOptions as $optionValue => $label)
                                            <option value="{{ $optionValue }}" @selected((string) $value('rehabilitation_prognosis') === (string) $optionValue)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mse-field mse-field--full">
                                    <label for="medicalRehabilitation">Психосоциальная и медицинская реабилитация</label>
                                    <textarea id="medicalRehabilitation" name="medical_rehabilitation" rows="5"
                                              placeholder="Психообразование, психотерапия, социально-бытовая адаптация, тренинг навыков, сопровождаемое трудоустройство, иные мероприятия...">{{ $value('medical_rehabilitation') }}</textarea>
                                </div>
                                <div class="mse-field mse-field--full">
                                    <label for="medicationsDevices">Психофармакотерапия и поддерживающее лечение</label>
                                    <textarea id="medicationsDevices" name="medications_devices" rows="4"
                                              placeholder="Текущая терапия, эффект, переносимость, приверженность, необходимость длительного поддерживающего лечения...">{{ $value('medications_devices') }}</textarea>
                                </div>
                                <div class="mse-field mse-field--full">
                                    <label for="specialMedicalCare">Специализированная психиатрическая помощь</label>
                                    <textarea id="specialMedicalCare" name="special_medical_care" rows="4"
                                              placeholder="Потребность в амбулаторном наблюдении, дневном/круглосуточном стационаре, психотерапии, медицинской реабилитации...">{{ $value('special_medical_care') }}</textarea>
                                </div>

                                {{-- Не относящиеся к психиатрическому профилю направления реабилитации сбрасываем. --}}
                                <input type="hidden" name="reconstructive_surgery" value="">
                                <input type="hidden" name="prosthetics_orthotics" value="">
                                <input type="hidden" name="sanatorium_treatment" value="">
                            </div>
                        </section>

                        <section id="mseSectionSignatures" class="mse-card mse-form-card" data-form-section="signatures">
                            <div class="mse-card__head">
                                <span class="mse-card__icon"><i class="bi bi-pen"></i></span>
                                <div>
                                    <h2>Дополнительные сведения и состав врачебной комиссии</h2>
                                    <p>ФИО председателя и членов врачебной комиссии для итогового документа.</p>
                                </div>
                                <span class="mse-step">06</span>
                            </div>

                            <div class="mse-field mse-field--full">
                                <label for="additionalNotes">Дополнительные сведения</label>
                                <textarea id="additionalNotes" name="additional_notes" rows="5">{{ $value('additional_notes') }}</textarea>
                            </div>

                            <div class="mse-section-caption"><i class="bi bi-person-badge"></i> Подписи врачебной комиссии</div>
                            <div class="mse-grid mse-grid--2">
                                <div class="mse-field mse-field--full">
                                    <label for="commissionChairman">Председатель врачебной комиссии</label>
                                    <input id="commissionChairman" name="commission_chairman" value="{{ $value('commission_chairman') }}"
                                           placeholder="Фамилия, имя, отчество">
                                </div>
                            </div>

                            <div class="mse-repeater" data-repeater="commission-members">
                                <table style="min-width: 620px">
                                    <thead>
                                    <tr>
                                        <th>Член врачебной комиссии</th>
                                        <th style="width: 54px"></th>
                                    </tr>
                                    </thead>
                                    <tbody data-repeater-body>
                                    @foreach($commissionMembers as $member)
                                        <tr data-repeater-row>
                                            <td><input name="commission_members[]" value="{{ $member }}" placeholder="Фамилия, имя, отчество"></td>
                                            <td><button class="mse-repeater__remove" type="button" data-remove-row title="Удалить строку"><i class="bi bi-trash3"></i></button></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mse-repeater__footer">
                                <button class="mse-button mse-button--secondary" type="button" data-add-row="commission-members">
                                    <i class="bi bi-plus-lg"></i> Добавить члена комиссии
                                </button>
                            </div>

                            <div class="mse-note">
                                <i class="bi bi-info-circle-fill"></i>
                                <div>
                                    <strong>Перед оформлением</strong>
                                    <span>Проверьте реквизиты протокола ВК, дату согласия, цели направления и актуальность диагноза в карточке пациента.</span>
                                </div>
                            </div>
                        </section>

                        <div class="mse-actions-bar">
                            <span class="mse-actions-bar__hint">
                                <i class="bi bi-cloud-check"></i>
                                {{ $isEditing ? 'Изменения будут сохранены в текущем направлении.' : 'Номер направления присвоится после первого сохранения.' }}
                            </span>
                            <span class="mse-actions-bar__buttons">
                                <a class="mse-button mse-button--ghost" href="{{ route('doctors.commissions.mse.index') }}">
                                    <i class="bi bi-x-lg"></i> Отмена
                                </a>
                                <button class="mse-button mse-button--primary" type="submit" data-submit-button>
                                    <i class="bi bi-check2-circle"></i> Сохранить направление
                                </button>
                            </span>
                        </div>
                    </div>

                    <aside class="mse-sidebar" aria-label="Проверка и сохранение направления">
                        <section class="mse-sidebar-card">
                            <h3>Готовность направления</h3>
                            <p><strong data-readiness-percent>0%</strong> обязательных и рекомендуемых сведений заполнено.</p>
                            <div class="mse-readiness">
                                @foreach([
                                    ['patient', 'Пациент выбран'],
                                    ['core', 'Реквизиты направления'],
                                    ['protocol', 'Протокол ВК и дата согласия'],
                                    ['consent_document', 'Подписанное разрешение'],
                                    ['purpose', 'Цель МСЭ'],
                                    ['observation_period', 'Период наблюдения указан'],
                                    ['diagnosis', 'Диагноз и код МКБ'],
                                    ['clinical', 'Клинические сведения'],
                                    ['commission', 'Состав комиссии'],
                                ] as [$key, $label])
                                    <div class="mse-readiness__item" data-readiness-item="{{ $key }}">
                                        <span class="mse-readiness__dot"><i class="bi bi-check-lg"></i></span>
                                        <span>{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="mse-sidebar-card mse-gate-card" data-mse-gate-card>
                            <h3><i class="bi bi-shield-check"></i> Допуск к оформлению</h3>
                            <div class="mse-gate-card__status">
                                <i class="bi bi-hourglass-split"></i>
                                <span data-mse-gate-status>Проверяем обязательные условия…</span>
                            </div>
                            <div class="mse-gate-list" data-mse-gate-list></div>
                        </section>

                        <section class="mse-sidebar-card mse-sidebar-card--notice">
                            <h3><i class="bi bi-shield-exclamation"></i> Контроль перед печатью</h3>
                            <p>Данные пациента и диагноз сохраняются снимком. Изменения в карточке после оформления не переписывают уже созданное направление.</p>
                        </section>

                        <section class="mse-sidebar-card mse-sidebar-actions">
                            <h3>Сохранение</h3>
                            <p>Текущий статус: <strong data-save-status>{{ $currentStatusLabel }}</strong>.</p>
                            <button class="mse-button mse-button--primary" type="submit" data-submit-button>
                                <i class="bi bi-check2-circle"></i> Сохранить
                            </button>
                            @if($isEditing)
                                <a class="mse-button mse-button--secondary" target="_blank"
                                   href="{{ route('doctors.commissions.mse.print', $document) }}">
                                    <i class="bi bi-printer"></i> Открыть печатную форму
                                </a>
                            @endif
                            <a class="mse-button mse-button--ghost" href="{{ route('doctors.commissions.mse.index') }}">
                                <i class="bi bi-arrow-left"></i> В журнал
                            </a>
                        </section>
                    </aside>
                </div>
            </form>
        </div>

        <template id="mseTemporaryDisabilityTemplate">
            <tr data-repeater-row>
                <td><input type="date" name="temporary_disability[__INDEX__][start_date]"></td>
                <td><input type="date" name="temporary_disability[__INDEX__][end_date]"></td>
                <td><input name="temporary_disability[__INDEX__][duration]" placeholder="Напр. 14 дней"></td>
                <td><textarea name="temporary_disability[__INDEX__][diagnosis]"></textarea></td>
                <td><button class="mse-repeater__remove" type="button" data-remove-row title="Удалить строку"><i class="bi bi-trash3"></i></button></td>
            </tr>
        </template>

        <template id="mseExaminationsTemplate">
            <tr data-repeater-row>
                <td><input type="date" name="examinations[__INDEX__][date]"></td>
                <td><input name="examinations[__INDEX__][code]"></td>
                <td><textarea name="examinations[__INDEX__][name]"></textarea></td>
                <td><textarea name="examinations[__INDEX__][result]"></textarea></td>
                <td><button class="mse-repeater__remove" type="button" data-remove-row title="Удалить строку"><i class="bi bi-trash3"></i></button></td>
            </tr>
        </template>

        <template id="mseCommissionMembersTemplate">
            <tr data-repeater-row>
                <td><input name="commission_members[]" placeholder="Фамилия, имя, отчество"></td>
                <td><button class="mse-repeater__remove" type="button" data-remove-row title="Удалить строку"><i class="bi bi-trash3"></i></button></td>
            </tr>
        </template>
    </main>

    <script>
        (() => {
            const root = document.getElementById('mseReferralFormRoot');
            if (!root) return;

            const form = root.querySelector('[data-mse-form]');
            const patientSearch = root.querySelector('[data-patient-search]');
            const patientId = root.querySelector('[data-patient-id]');
            const patientClear = root.querySelector('[data-patient-clear]');
            const patientSuggestions = root.querySelector('[data-patient-suggestions]');
            const patientCard = root.querySelector('[data-patient-card]');
            const patientNameNodes = [...root.querySelectorAll('[data-patient-name]')];
            const patientMetaNodes = [...root.querySelectorAll('[data-patient-meta]')];
            const patientDiagnosisNodes = [...root.querySelectorAll('[data-patient-diagnosis]')];
            const searchUrl = root.dataset.patientSearchUrl;
            const patientDetailsUrlTemplate = root.dataset.patientDetailsUrlTemplate || '';
            const addressSuggestUrl = root.dataset.addressSuggestUrl || '';
            const isEditing = root.dataset.isEditing === '1';
            const consentPreviewUrl = root.dataset.consentPreviewUrl;
            const statusSelect = root.querySelector('[data-status-select]');
            const referralKindSelect = root.querySelector('#referralKind');
            const previousDisabilityGroupSelect = root.querySelector('#previousDisabilityGroup');
            const previousIpraBlock = root.querySelector('[data-previous-ipra-block]');
            const previousIpraFields = previousIpraBlock
                ? [...previousIpraBlock.querySelectorAll('input, select, textarea')]
                : [];
            const documentDateInput = root.querySelector('#documentDate');
            const observedSinceDateInput = root.querySelector('[data-observed-since-date]');
            const observedSinceYearInput = root.querySelector('[data-observed-since-year]');
            const observationWarning = root.querySelector('[data-observation-warning]');
            const observationWarningText = root.querySelector('[data-observation-warning-text]');
            const disabilityEstablishmentPurposes = new Set([
                'disability_group',
                'disabled_child',
            ]);
            const preparedRequiredInputs = [...root.querySelectorAll('[data-required-when-prepared]')];
            const preparedRequiredMarks = [...root.querySelectorAll('[data-prepared-required]')];
            const purposeCheckboxes = [...root.querySelectorAll('[data-purpose-checkbox]')];
            const citizenshipSelect = root.querySelector('[data-citizenship-select]');
            const foreignCountryWrap = root.querySelector('[data-foreign-country-wrap]');
            const foreignCountryInput = foreignCountryWrap?.querySelector('input');
            const elnToggle = root.querySelector('[data-eln-toggle]');
            const elnNumberWrap = root.querySelector('[data-eln-number-wrap]');
            const heightInput = root.querySelector('[data-height]');
            const weightInput = root.querySelector('[data-weight]');
            const bmiInput = root.querySelector('[data-bmi]');
            const progressLinks = [...root.querySelectorAll('[data-progress-link]')];
            const sections = [...root.querySelectorAll('[data-form-section]')];
            const submitButtons = [...root.querySelectorAll('[data-submit-button]')];
            const initialPatientNode = document.getElementById('mseInitialPatient');
            const consentFileInput = root.querySelector('[data-consent-file-input]');
            const consentRemoveInput = root.querySelector('[data-consent-remove-input]');
            const consentExisting = root.querySelector('[data-consent-existing]');
            const consentSelected = root.querySelector('[data-consent-selected]');
            const consentSelectedName = root.querySelector('[data-consent-selected-name]');
            const consentSelectedMeta = root.querySelector('[data-consent-selected-meta]');
            const consentRemoval = root.querySelector('[data-consent-removal]');
            const consentDropzone = root.querySelector('[data-consent-dropzone]');
            const consentDropzoneTitle = root.querySelector('[data-consent-dropzone-title]');
            const consentStatus = root.querySelector('[data-consent-status]');
            const consentRequiredMarks = [...root.querySelectorAll('[data-consent-required]')];
            const consentPrintButtons = [...root.querySelectorAll('[data-consent-print]')];
            const consentUploadButtonLabel = root.querySelector('[data-consent-upload-button-label]');
            const hasStoredConsentDocument = root.dataset.hasConsentDocument === '1';
            const maxConsentFileSize = Number(root.dataset.maxConsentFileSize || 15728640);
            const mseCriteriaPanel = root.querySelector('[data-mse-criteria-panel]');
            const mseCriteriaCode = root.querySelector('[data-mse-criteria-code]');
            const mseCriteriaBody = root.querySelector('[data-mse-criteria-body]');
            const mseRequiredExams = root.querySelector('[data-mse-required-exams]');
            const mseGatePersistent = root.querySelector('[data-mse-gate-persistent]');
            const mseGateMeasures = root.querySelector('[data-mse-gate-measures]');
            const mseGateExams = root.querySelector('[data-mse-gate-exams]');
            const mseGateCard = root.querySelector('[data-mse-gate-card]');
            const mseGateStatus = root.querySelector('[data-mse-gate-status]');
            const mseGateList = root.querySelector('[data-mse-gate-list]');
            const mseAgeRequirements = root.querySelector('[data-mse-age-requirements]');
            const mseAgeBadge = root.querySelector('[data-mse-age-badge]');
            const mseAgeBody = root.querySelector('[data-mse-age-body]');
            const underThreeFields = root.querySelector('[data-under-three-fields]');

            let selectedPatient = null;
            let searchResults = [];
            let searchTimer = null;
            let searchController = null;

            try {
                selectedPatient = JSON.parse(initialPatientNode?.textContent || 'null');
            } catch (_) {
                selectedPatient = null;
            }

            const text = (value) => String(value ?? '').trim();

            const calculateAge = (isoDate) => {
                const match = text(isoDate).match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!match) return null;

                const birth = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
                if (Number.isNaN(birth.getTime())) return null;

                const today = new Date();
                let age = today.getFullYear() - birth.getFullYear();
                const monthDiff = today.getMonth() - birth.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) age -= 1;
                return age >= 0 ? age : null;
            };

            const calculateAgeMonths = (isoDate) => {
                const match = text(isoDate).match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!match) return null;

                const birth = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
                if (Number.isNaN(birth.getTime())) return null;

                const today = new Date();
                if (birth > today) return null;

                let months = (today.getFullYear() - birth.getFullYear()) * 12
                    + today.getMonth() - birth.getMonth();
                if (today.getDate() < birth.getDate()) months -= 1;
                return months >= 0 ? months : null;
            };

            const ageDisplay = (patient) => {
                const age = patient?.age;
                const months = patient?.age_months ?? calculateAgeMonths(patient?.birth_at);
                if (age === null || age === undefined) return '';
                if (Number(age) < 1 && months !== null) return `${months} полн. мес.`;
                return `${age} лет`;
            };

            const normalizePatient = (item) => {
                const diagnosis = item?.diagnosis && typeof item.diagnosis === 'object'
                    ? item.diagnosis
                    : (item?.diagnose && typeof item.diagnose === 'object' ? item.diagnose : {});
                const identity = item?.identity && typeof item.identity === 'object'
                    ? item.identity
                    : (item?.primary_identity_document && typeof item.primary_identity_document === 'object'
                        ? item.primary_identity_document
                        : {});
                const representative = item?.representative && typeof item.representative === 'object'
                    ? item.representative
                    : (item?.primary_representative && typeof item.primary_representative === 'object'
                        ? item.primary_representative
                        : {});
                const guardianOrganization = item?.guardian_organization && typeof item.guardian_organization === 'object'
                    ? item.guardian_organization
                    : {};

                const fullName = text(
                    item?.full_name
                    || [item?.surname, item?.name, item?.patronym].filter(Boolean).join(' ')
                    || item?.text
                );
                const birthAt = text(item?.birth_at || item?.birth_date);
                const rawAge = text(item?.age);
                const parsedAge = rawAge === '' ? Number.NaN : Number(rawAge);
                const age = Number.isFinite(parsedAge) ? parsedAge : calculateAge(birthAt);
                const rawAgeMonths = text(item?.age_months);
                const parsedAgeMonths = rawAgeMonths === '' ? Number.NaN : Number(rawAgeMonths);
                const ageMonths = Number.isFinite(parsedAgeMonths) ? parsedAgeMonths : calculateAgeMonths(birthAt);
                const diagnosisLabelSource = text(item?.diagnosis_label);
                const diagnosisCodeFromLabel = diagnosisLabelSource.match(/\b[A-Z]\d{2}(?:\.\d{1,3})?\b/i)?.[0] || '';
                const diagnosisCode = text(diagnosis?.code || item?.diagnosis_code || diagnosisCodeFromLabel).toUpperCase();
                const diagnosisTitle = text(diagnosis?.title || diagnosis?.name || item?.diagnosis_title);
                const diagnosisLabel = diagnosisLabelSource
                    || [diagnosisCode, diagnosisTitle].filter(Boolean).join(' — ')
                    || 'не установлен';

                const addressRegistration = text(
                    item?.address_registration
                    || item?.registration_address
                    || item?.address
                );
                const addressResidence = text(
                    item?.address_residence
                    || item?.residence_address
                    || item?.actual_address
                    || addressRegistration
                );

                let identityName = text(
                    identity?.document_name
                    || identity?.name
                    || item?.identity_document_name
                    || item?.patient_identity_name
                );

                if (!identityName && (text(identity?.number || item?.number || item?.passport_number) || age !== null)) {
                    identityName = age !== null && age < 14
                        ? 'Свидетельство о рождении'
                        : 'Паспорт гражданина Российской Федерации';
                }

                return {
                    id: Number(item?.id || item?.patient_id || 0),
                    full_name: fullName,
                    birth_at: birthAt,
                    birth_at_display: text(item?.birth_at_display || item?.birth_date_display || birthAt),
                    birth_place: text(item?.birth_place),
                    age,
                    age_months: ageMonths,
                    disability: Boolean(item?.disability),
                    medcard_number: text(item?.medcard_number),
                    diagnosis_code: diagnosisCode,
                    diagnosis_title: diagnosisTitle,
                    diagnosis_label: diagnosisLabel,
                    phone: text(item?.phone || item?.telephone || item?.mobile_phone),
                    email: text(item?.email),
                    snils: text(item?.snils || item?.SNILS),
                    oms: text(item?.oms || item?.insurance_policy || item?.policy || item?.oms_policy),
                    address: addressRegistration || addressResidence,
                    address_registration: addressRegistration,
                    address_residence: addressResidence,
                    sex: text(item?.sex || item?.gender),
                    citizenship: text(item?.citizenship?.type || item?.citizenship_type || item?.patient_citizenship),
                    citizenship_country: text(item?.citizenship?.country || item?.citizenship_country),
                    military_duty: text(item?.military_duty || item?.patient_military_duty),
                    identity: {
                        name: identityName,
                        series: text(identity?.series || item?.serial || item?.passport_series),
                        number: text(identity?.number || item?.number || item?.passport_number),
                        issued_by: text(identity?.issued_by || item?.issued_by || item?.passport_issued_by),
                        issue_date: text(identity?.issued_at || identity?.issue_date || item?.issued_at || item?.passport_issued_at),
                        department_code: text(identity?.department_code || item?.department_code || item?.passport_department_code),
                    },
                    representative: {
                        full_name: text(
                            representative?.full_name
                            || [representative?.surname, representative?.name, representative?.patronym].filter(Boolean).join(' ')
                        ),
                        birth_date: text(representative?.birth_date || representative?.birth_at),
                        phone: text(representative?.phone),
                        email: text(representative?.email),
                        snils: text(representative?.snils),
                        identity: text(representative?.identity || representative?.identity_document),
                        authority_document: text(representative?.authority_document),
                    },
                    guardian_organization: {
                        name: text(guardianOrganization?.name),
                        ogrn: text(guardianOrganization?.ogrn),
                        address: text(guardianOrganization?.address),
                    },
                };
            };

            const mergePatient = (base, detail) => normalizePatient({
                ...(base || {}),
                ...(detail || {}),
                identity: {
                    ...((base || {}).identity || {}),
                    ...((detail || {}).identity || {}),
                },
                representative: {
                    ...((base || {}).representative || {}),
                    ...((detail || {}).representative || {}),
                },
                guardian_organization: {
                    ...((base || {}).guardian_organization || {}),
                    ...((detail || {}).guardian_organization || {}),
                },
            });

            const patientField = (name) => root.querySelector(`[data-patient-field="${name}"]`);

            const setValue = (element, value, overwrite = true, clearWhenEmpty = false) => {
                if (!element) return;
                const normalizedValue = value === undefined || value === null ? '' : String(value);
                if (!overwrite && text(element.value) !== '') return;
                if (!clearWhenEmpty && text(normalizedValue) === '') return;

                element.value = normalizedValue;
                element.dispatchEvent(new Event('change', { bubbles: true }));
            };

            const normalizeSex = (value) => {
                const normalized = text(value).toLowerCase();
                if (['male', 'm', 'м', 'мужской', 'муж'].includes(normalized)) return 'male';
                if (['female', 'f', 'ж', 'женский', 'жен'].includes(normalized)) return 'female';
                return '';
            };

            const patientAutofillFieldNames = [
                'sex',
                'citizenship',
                'citizenship_country',
                'military_duty',
                'address',
                'location_address',
                'phone',
                'email',
                'snils',
                'oms',
                'identity_name',
                'identity_series',
                'identity_number',
                'identity_issue_date',
                'identity_issued_by',
                'representative_full_name',
                'representative_birth_date',
                'representative_phone',
                'representative_email',
                'representative_snils',
                'representative_identity',
                'representative_authority_document',
                'guardian_organization_name',
                'guardian_organization_ogrn',
                'guardian_organization_address',
            ];

            const clearPatientAutofillFields = () => {
                patientAutofillFieldNames.forEach((fieldName) => {
                    const field = patientField(fieldName);
                    if (!field) return;
                    field.value = '';
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                });
            };

            const buildPatientDetailsUrl = (id) => {
                if (!patientDetailsUrlTemplate || !id) return '';
                return patientDetailsUrlTemplate.replace('__ID__', encodeURIComponent(String(id)));
            };

            const loadPatientDetails = async (id) => {
                const url = buildPatientDetailsUrl(id);
                if (!url) return null;

                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            };

            const renderPatient = (patient) => {
                if (!patient?.id) {
                    patientCard?.classList.add('is-empty');
                    patientNameNodes.forEach((node) => node.textContent = 'Пациент не выбран');
                    patientMetaNodes.forEach((node) => node.textContent = 'Выберите пациента из результатов поиска');
                    patientDiagnosisNodes.forEach((node) => node.textContent = 'не установлен');
                    patientClear?.classList.add('d-none');
                    renderMseCriteria();
                    renderAgeDocumentationRequirements();
                    updateReadiness();
                    return;
                }

                patientCard?.classList.remove('is-empty');
                patientNameNodes.forEach((node) => node.textContent = patient.full_name || 'Пациент');
                const meta = [
                    patient.birth_at_display ? `д. р. ${patient.birth_at_display}` : '',
                    ageDisplay(patient),
                    patient.medcard_number ? `карта № ${patient.medcard_number}` : '',
                    patient.phone,
                ].filter(Boolean).join(' · ');
                patientMetaNodes.forEach((node) => node.textContent = meta || 'Основные сведения не указаны');
                patientDiagnosisNodes.forEach((node) => node.textContent = patient.diagnosis_label || 'не установлен');
                patientClear?.classList.remove('d-none');
                renderMseCriteria();
                renderAgeDocumentationRequirements();
                updateReadiness();
            };

            const applyPatientData = (patient, overwrite = true) => {
                if (!patient) return;

                setValue(patientField('phone'), patient.phone, overwrite, overwrite);
                setValue(patientField('email'), patient.email, overwrite, overwrite);
                setValue(patientField('snils'), patient.snils, overwrite, overwrite);
                setValue(patientField('oms'), patient.oms, overwrite, overwrite);
                setValue(patientField('address'), patient.address_registration || patient.address, overwrite, overwrite);
                setValue(patientField('location_address'), patient.address_residence || patient.address_registration || patient.address, overwrite, overwrite);
                setValue(patientField('sex'), normalizeSex(patient.sex), overwrite, overwrite);
                setValue(patientField('citizenship'), patient.citizenship, overwrite, overwrite);
                setValue(patientField('citizenship_country'), patient.citizenship_country, overwrite, overwrite);
                setValue(patientField('military_duty'), patient.military_duty, overwrite, overwrite);

                setValue(patientField('identity_name'), patient.identity?.name, overwrite, overwrite);
                setValue(patientField('identity_series'), patient.identity?.series, overwrite, overwrite);
                setValue(patientField('identity_number'), patient.identity?.number, overwrite, overwrite);
                setValue(patientField('identity_issue_date'), patient.identity?.issue_date, overwrite, overwrite);
                setValue(patientField('identity_issued_by'), patient.identity?.issued_by, overwrite, overwrite);

                setValue(patientField('representative_full_name'), patient.representative?.full_name, overwrite, overwrite);
                setValue(patientField('representative_birth_date'), patient.representative?.birth_date, overwrite, overwrite);
                setValue(patientField('representative_phone'), patient.representative?.phone, overwrite, overwrite);
                setValue(patientField('representative_email'), patient.representative?.email, overwrite, overwrite);
                setValue(patientField('representative_snils'), patient.representative?.snils, overwrite, overwrite);
                setValue(patientField('representative_identity'), patient.representative?.identity, overwrite, overwrite);
                setValue(patientField('representative_authority_document'), patient.representative?.authority_document, overwrite, overwrite);

                setValue(patientField('guardian_organization_name'), patient.guardian_organization?.name, overwrite, overwrite);
                setValue(patientField('guardian_organization_ogrn'), patient.guardian_organization?.ogrn, overwrite, overwrite);
                setValue(patientField('guardian_organization_address'), patient.guardian_organization?.address, overwrite, overwrite);

                const representativeBlock = root.querySelector('[data-representative-block]');
                if (representativeBlock && (patient.representative?.full_name || patient.guardian_organization?.name)) {
                    representativeBlock.open = true;
                }

                syncCitizenship();
            };

            const closeSuggestions = () => {
                patientSuggestions?.classList.add('d-none');
                patientSuggestions?.replaceChildren();
                searchResults = [];
            };

            const clearPatient = (clearSearch = true, clearFields = true) => {
                selectedPatient = null;
                if (patientId) patientId.value = '';
                if (clearSearch && patientSearch) patientSearch.value = '';
                if (clearFields) clearPatientAutofillFields();
                renderPatient(null);
                closeSuggestions();
            };

            const selectPatient = async (rawPatient) => {
                const previewPatient = normalizePatient(rawPatient);
                if (!previewPatient.id) return;

                selectedPatient = previewPatient;
                if (patientId) patientId.value = String(previewPatient.id);
                if (patientSearch) {
                    patientSearch.value = previewPatient.full_name || text(rawPatient?.text);
                    patientSearch.setCustomValidity('');
                    patientSearch.setAttribute('aria-busy', 'true');
                }

                renderPatient(previewPatient);
                closeSuggestions();

                let patient = previewPatient;

                try {
                    const detailPayload = await loadPatientDetails(previewPatient.id);
                    if (detailPayload) patient = mergePatient(previewPatient, detailPayload);
                } catch (error) {
                    console.warn('[MSE] Не удалось загрузить полную карточку пациента', error);
                } finally {
                    patientSearch?.removeAttribute('aria-busy');
                }

                if (!patient.full_name) patient.full_name = previewPatient.full_name || text(rawPatient?.text);
                selectedPatient = patient;

                clearPatientAutofillFields();
                applyPatientData(patient, true);
                if (patientSearch && patient.full_name) patientSearch.value = patient.full_name;
                renderPatient(patient);
            };

            const renderSearchMessage = (message) => {
                if (!patientSuggestions) return;
                const node = document.createElement('div');
                node.className = 'mse-suggestion-message';
                node.textContent = message;
                patientSuggestions.replaceChildren(node);
                patientSuggestions.classList.remove('d-none');
            };

            const renderSearchResults = (items) => {
                if (!patientSuggestions) return;
                searchResults = items.map(normalizePatient).filter((patient) => patient.id && patient.full_name);

                if (!searchResults.length) {
                    renderSearchMessage('Пациенты не найдены');
                    return;
                }

                const fragment = document.createDocumentFragment();
                searchResults.forEach((patient, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'mse-suggestion';
                    button.dataset.patientIndex = String(index);

                    const icon = document.createElement('span');
                    icon.className = 'mse-suggestion__icon';
                    icon.innerHTML = '<i class="bi bi-person"></i>';

                    const copy = document.createElement('span');
                    copy.className = 'mse-suggestion__copy';
                    const name = document.createElement('strong');
                    name.textContent = patient.full_name;
                    const meta = document.createElement('span');
                    meta.textContent = [
                        patient.birth_at_display ? `д. р. ${patient.birth_at_display}` : '',
                        patient.medcard_number ? `карта № ${patient.medcard_number}` : '',
                        patient.diagnosis_label && patient.diagnosis_label !== 'не установлен'
                            ? `Диагноз: ${patient.diagnosis_label}`
                            : '',
                    ].filter(Boolean).join(' · ');
                    copy.append(name, meta);
                    button.append(icon, copy);
                    fragment.append(button);
                });

                patientSuggestions.replaceChildren(fragment);
                patientSuggestions.classList.remove('d-none');
            };

            const searchPatients = async (query) => {
                searchController?.abort();
                searchController = new AbortController();
                renderSearchMessage('Поиск…');

                try {
                    const separator = searchUrl.includes('?') ? '&' : '?';
                    const response = await fetch(`${searchUrl}${separator}q=${encodeURIComponent(query)}`, {
                        headers: { Accept: 'application/json' },
                        credentials: 'same-origin',
                        signal: searchController.signal,
                    });
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    const payload = await response.json();
                    const items = Array.isArray(payload)
                        ? payload
                        : (Array.isArray(payload?.results) ? payload.results : (Array.isArray(payload?.data) ? payload.data : []));
                    renderSearchResults(items);
                } catch (error) {
                    if (error.name !== 'AbortError') renderSearchMessage('Не удалось выполнить поиск');
                }
            };

            const addressStates = new WeakMap();

            const hideAddressSuggestions = (box) => {
                if (!box) return;
                box.classList.add('d-none');
                box.replaceChildren();
            };

            const hideAllAddressSuggestions = (except = null) => {
                root.querySelectorAll('[data-address-suggestions-box]').forEach((box) => {
                    if (box !== except) hideAddressSuggestions(box);
                });
            };

            const renderAddressState = (box, message) => {
                if (!box) return;
                const state = document.createElement('div');
                state.className = 'mse-address-suggestions__state';
                state.textContent = message;
                box.replaceChildren(state);
                box.classList.remove('d-none');
            };

            const normalizeAddressSuggestions = (payload) => {
                const raw = Array.isArray(payload)
                    ? payload
                    : (Array.isArray(payload?.suggestions)
                        ? payload.suggestions
                        : (Array.isArray(payload?.items) ? payload.items : []));

                return raw.map((item) => {
                    if (typeof item === 'string') return { value: item, meta: '' };
                    const data = item?.data || {};
                    const value = text(item?.value || item?.unrestricted_value || item?.address || data?.result);
                    const meta = [
                        item?.postal_code || data?.postal_code,
                        item?.city || data?.city_with_type || data?.settlement_with_type,
                        item?.street || data?.street_with_type,
                    ].filter(Boolean).join(' · ');
                    return { value, meta };
                }).filter((item) => item.value);
            };

            const renderAddressSuggestions = (input, box, suggestions) => {
                if (!box) return;
                if (!suggestions.length) {
                    renderAddressState(box, 'Подходящие адреса не найдены');
                    return;
                }

                const fragment = document.createDocumentFragment();
                suggestions.slice(0, 8).forEach((item) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'mse-address-suggestion';

                    const valueNode = document.createElement('strong');
                    valueNode.textContent = item.value;
                    button.append(valueNode);

                    if (item.meta) {
                        const metaNode = document.createElement('span');
                        metaNode.textContent = item.meta;
                        button.append(metaNode);
                    }

                    button.addEventListener('mousedown', (event) => event.preventDefault());
                    button.addEventListener('click', () => {
                        input.value = item.value;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        hideAddressSuggestions(box);
                        input.focus();
                    });

                    fragment.append(button);
                });

                box.replaceChildren(fragment);
                box.classList.remove('d-none');
            };

            const requestAddressSuggestions = async (input, box, query) => {
                if (!addressSuggestUrl || query.length < 2) {
                    hideAddressSuggestions(box);
                    return;
                }

                const previous = addressStates.get(input) || {};
                previous.controller?.abort();
                const controller = new AbortController();
                addressStates.set(input, { ...previous, controller });
                renderAddressState(box, 'Ищем адрес…');

                try {
                    const separator = addressSuggestUrl.includes('?') ? '&' : '?';
                    const response = await fetch(
                        `${addressSuggestUrl}${separator}query=${encodeURIComponent(query)}`,
                        {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            signal: controller.signal,
                        }
                    );

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    const payload = await response.json();
                    renderAddressSuggestions(input, box, normalizeAddressSuggestions(payload));
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.warn('[MSE] Ошибка поиска адреса', error);
                        renderAddressState(box, 'Не удалось загрузить подсказки адреса');
                    }
                }
            };

            const bindAddressAutocomplete = (input) => {
                const boxSelector = input.dataset.addressSuggestions;
                const box = boxSelector ? root.querySelector(boxSelector) : null;
                if (!box) return;

                let state = addressStates.get(input) || {};
                addressStates.set(input, state);

                input.addEventListener('input', () => {
                    window.clearTimeout(state.timer);
                    state.controller?.abort();
                    const query = input.value.trim();

                    if (query.length < 2) {
                        hideAddressSuggestions(box);
                        return;
                    }

                    state.timer = window.setTimeout(() => {
                        requestAddressSuggestions(input, box, query);
                    }, 220);
                });

                input.addEventListener('focus', () => {
                    const query = input.value.trim();
                    if (query.length >= 2) requestAddressSuggestions(input, box, query);
                });

                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') hideAddressSuggestions(box);
                });
            };

            root.querySelectorAll('[data-address-autocomplete]').forEach(bindAddressAutocomplete);

            const openConsentPreview = async (mode = 'auto') => {
                mode = mode === 'manual' ? 'manual' : 'auto';

                if (!form || !consentPreviewUrl) {
                    window.alert('Маршрут печатной формы согласия не настроен.');
                    return;
                }

                if (mode === 'auto' && (!patientId?.value || !text(patientSearch?.value))) {
                    patientSearch?.setCustomValidity('Сначала выберите пациента для автоматического заполнения.');
                    patientSearch?.reportValidity();
                    patientSearch?.focus();
                    root.querySelector('#mseSectionPatient')?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                    return;
                }

                patientSearch?.setCustomValidity('');

                /*
                 * Окно создаём до fetch, иначе браузер может считать его
                 * всплывающим и заблокировать после асинхронного запроса.
                 */
                const previewWindow = window.open(
                    '',
                    '_blank',
                    'width=1120,height=900,scrollbars=yes,resizable=yes'
                );

                if (!previewWindow) {
                    window.alert('Браузер заблокировал окно редактора. Разрешите всплывающие окна для этого сайта.');
                    return;
                }

                const loadingTitle = mode === 'auto'
                    ? 'Автоматическое заполнение согласия'
                    : 'Печать пустого бланка согласия';
                const loadingText = mode === 'auto'
                    ? 'Переносим сведения из текущей формы…'
                    : 'Формируем пустой бланк без данных…';

                previewWindow.document.open();
                previewWindow.document.write(`<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>${loadingTitle}</title>
    <style>
        body { margin: 0; display: grid; min-height: 100vh; place-items: center; font: 14px Arial, sans-serif; color: #365364; background: #f3f7f8; }
        div { display: flex; align-items: center; gap: 10px; padding: 18px 22px; background: #fff; border: 1px solid #d8e5e8; border-radius: 14px; box-shadow: 0 14px 36px rgba(30, 67, 82, .1); }
        span { width: 18px; height: 18px; border: 2px solid #c6dadd; border-top-color: #087f8c; border-radius: 50%; animation: spin .75s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body><div><span></span>${loadingText}</div></body>
</html>`);
                previewWindow.document.close();

                const payload = new FormData(form);
                payload.delete('_method');
                payload.delete('consent_document');
                payload.delete('remove_consent_document');
                payload.set('mode', mode);
                payload.set('autoprint', '1');

                consentPrintButtons.forEach((button) => {
                    button.disabled = true;
                    button.setAttribute('aria-busy', 'true');
                });

                try {
                    const response = await fetch(consentPreviewUrl, {
                        method: 'POST',
                        body: payload,
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json, text/html;q=0.9',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        const contentType = response.headers.get('content-type') || '';
                        let message = mode === 'auto'
                            ? 'Не удалось сформировать заполненное согласие.'
                            : 'Не удалось сформировать пустой бланк для печати.';

                        if (contentType.includes('application/json')) {
                            const errorPayload = await response.json().catch(() => null);
                            const validationMessages = Object.values(errorPayload?.errors || {})
                                .flat()
                                .map((item) => text(item))
                                .filter(Boolean);

                            message = validationMessages[0] || text(errorPayload?.message) || message;
                        } else {
                            const responseText = text(await response.text());
                            if (responseText && responseText.length < 300) message = responseText;
                        }

                        throw new Error(message);
                    }

                    const html = await response.text();
                    previewWindow.document.open();
                    previewWindow.document.write(html);
                    previewWindow.document.close();
                    previewWindow.focus();
                } catch (error) {
                    previewWindow.close();
                    window.alert(error?.message || 'Не удалось открыть печатную форму согласия.');
                } finally {
                    consentPrintButtons.forEach((button) => {
                        button.disabled = false;
                        button.removeAttribute('aria-busy');
                    });
                }
            };

            const formatFileSize = (bytes) => {
                const value = Number(bytes || 0);
                if (!value) return 'Размер не указан';

                const units = ['Б', 'КБ', 'МБ', 'ГБ'];
                let current = value;
                let index = 0;

                while (current >= 1024 && index < units.length - 1) {
                    current /= 1024;
                    index += 1;
                }

                const precision = index === 0 ? 0 : (current >= 10 ? 1 : 2);
                return `${current.toFixed(precision).replace('.', ',')} ${units[index]}`;
            };

            const consentDocumentAvailable = () => {
                const selected = Boolean(consentFileInput?.files?.length);
                const retained = hasStoredConsentDocument && consentRemoveInput?.value !== '1';
                return selected || retained;
            };

            const setConsentStatus = (ready, textValue) => {
                if (!consentStatus) return;
                consentStatus.classList.toggle('is-ready', ready);
                const icon = consentStatus.querySelector('i');
                const label = consentStatus.querySelector('span');
                if (icon) icon.className = `bi ${ready ? 'bi-check-circle-fill' : 'bi-cloud-arrow-up'}`;
                if (label) label.textContent = textValue;
            };

            const syncConsentUpload = () => {
                const file = consentFileInput?.files?.[0] || null;
                const removed = consentRemoveInput?.value === '1';
                const retained = hasStoredConsentDocument && !removed && !file;

                consentExisting?.classList.toggle('d-none', !retained);
                consentSelected?.classList.toggle('d-none', !file);
                consentRemoval?.classList.toggle('d-none', !(hasStoredConsentDocument && removed && !file));
                consentDropzone?.classList.toggle('is-selected', Boolean(file));

                if (file) {
                    if (consentSelectedName) consentSelectedName.textContent = file.name;
                    if (consentSelectedMeta) consentSelectedMeta.textContent = `${formatFileSize(file.size)} · будет загружен после сохранения`;
                    if (consentDropzoneTitle) consentDropzoneTitle.textContent = 'Новый подписанный документ выбран';
                    if (consentUploadButtonLabel) consentUploadButtonLabel.textContent = 'Выбрать другой документ';
                    setConsentStatus(true, 'Новое согласие выбрано');
                } else if (retained) {
                    if (consentDropzoneTitle) consentDropzoneTitle.textContent = 'Подписанный документ уже загружен';
                    if (consentUploadButtonLabel) consentUploadButtonLabel.textContent = 'Заменить согласие';
                    setConsentStatus(true, 'Подписанное согласие загружено');
                } else {
                    if (consentDropzoneTitle) consentDropzoneTitle.textContent = removed ? 'Документ отмечен на удаление' : 'Файл ещё не выбран';
                    if (consentUploadButtonLabel) consentUploadButtonLabel.textContent = 'Загрузить подписанное согласие';
                    setConsentStatus(false, removed ? 'Документ отмечен на удаление' : 'Ожидается подписанный документ');
                }

                updateReadiness();
            };

            const validateConsentFile = () => {
                if (!consentFileInput) return true;

                const file = consentFileInput.files?.[0] || null;
                consentFileInput.setCustomValidity('');

                if (!file) return true;

                const extension = file.name.split('.').pop()?.toLowerCase() || '';
                const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

                if (!allowedExtensions.includes(extension)) {
                    consentFileInput.setCustomValidity('Выберите PDF, JPG, PNG или WEBP.');
                    return false;
                }

                if (file.size > maxConsentFileSize) {
                    consentFileInput.setCustomValidity('Размер файла не должен превышать 15 МБ.');
                    return false;
                }

                return true;
            };

            const parseIsoDate = (value) => {
                const match = text(value).match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!match) return null;

                const year = Number(match[1]);
                const month = Number(match[2]);
                const day = Number(match[3]);
                const timestamp = Date.UTC(year, month - 1, day);
                const parsed = new Date(timestamp);

                if (
                    parsed.getUTCFullYear() !== year
                    || parsed.getUTCMonth() !== month - 1
                    || parsed.getUTCDate() !== day
                ) {
                    return null;
                }

                return parsed;
            };

            const addCalendarYear = (date) => {
                const year = date.getUTCFullYear() + 1;
                const month = date.getUTCMonth();
                const day = date.getUTCDate();
                const lastDay = new Date(Date.UTC(year, month + 1, 0)).getUTCDate();

                return new Date(Date.UTC(year, month, Math.min(day, lastDay)));
            };

            const hasPreviousIpraFieldData = () => previousIpraFields.some((field) =>
                text(field?.value) !== ''
            );

            const previousIpraContextApplies = () => {
                const patientAlreadyDisabled = Boolean(selectedPatient?.disability);
                const previousDisability = text(previousDisabilityGroupSelect?.value) !== '';
                const ipraPurpose = purposeCheckboxes.some((checkbox) =>
                    checkbox.checked && checkbox.value === 'ipra'
                );

                return patientAlreadyDisabled
                    || previousDisability
                    || ipraPurpose
                    || hasPreviousIpraFieldData();
            };

            const syncPreviousIpraVisibility = () => {
                if (!previousIpraBlock) return;

                const visible = previousIpraContextApplies();
                previousIpraBlock.classList.toggle('d-none', !visible);

                if (visible && !previousIpraBlock.hasAttribute('open')) {
                    previousIpraBlock.setAttribute('open', '');
                }
            };

            const disabilityObservationRuleApplies = () => {
                if (referralKindSelect?.value !== 'initial') return false;

                return purposeCheckboxes.some((checkbox) =>
                    checkbox.checked
                    && disabilityEstablishmentPurposes.has(checkbox.value)
                );
            };

            const getObservationEligibility = () => {
                const observedSince = parseIsoDate(observedSinceDateInput?.value);
                const referralDate = parseIsoDate(documentDateInput?.value);
                const applies = disabilityObservationRuleApplies();
                let shortPeriod = false;

                if (applies && observedSince && referralDate) {
                    shortPeriod = addCalendarYear(observedSince).getTime() > referralDate.getTime();
                }

                return {
                    applies,
                    hasObservationDate: Boolean(observedSince),
                    shortPeriod,
                    message: shortPeriod
                        ? 'Наблюдение длится менее 12 месяцев. Это не является самостоятельным запретом на направление, но ВК должна особенно убедительно документировать стойкость нарушения, динамику, лечение и результаты реабилитации.'
                        : '',
                };
            };

            const syncObservationEligibility = () => {
                const result = getObservationEligibility();
                const prepared = statusSelect?.value === 'prepared';

                if (observedSinceDateInput) {
                    observedSinceDateInput.required = prepared;
                    observedSinceDateInput.max = documentDateInput?.value || '';
                    observedSinceDateInput.setCustomValidity('');
                }

                if (observedSinceYearInput) {
                    observedSinceYearInput.value = observedSinceDateInput?.value
                        ? observedSinceDateInput.value.slice(0, 4)
                        : '';
                }

                observationWarning?.classList.toggle('d-none', !result.shortPeriod);
                if (observationWarningText) observationWarningText.textContent = result.message;

                return result;
            };

            const syncStatus = () => {
                const prepared = statusSelect?.value === 'prepared';
                const label = statusSelect?.selectedOptions?.[0]?.textContent?.trim() || '';
                root.querySelectorAll('[data-header-status] span, [data-save-status]').forEach((node) => node.textContent = label);
                preparedRequiredInputs.forEach((input) => input.required = prepared);
                preparedRequiredMarks.forEach((mark) => mark.classList.toggle('d-none', !prepared));
                consentRequiredMarks.forEach((mark) => mark.classList.toggle('d-none', !prepared));
                if (!prepared) consentFileInput?.setCustomValidity('');
                syncObservationEligibility();
                updateReadiness();
            };

            const syncCitizenship = () => {
                const foreign = citizenshipSelect?.value === 'foreign';
                foreignCountryWrap?.classList.toggle('d-none', !foreign);
                if (foreignCountryInput) foreignCountryInput.required = foreign;
            };

            const syncEln = () => {
                const enabled = Boolean(elnToggle?.checked);
                elnNumberWrap?.classList.toggle('d-none', !enabled);
            };

            const calculateBmi = () => {
                const height = Number(heightInput?.value || 0);
                const weight = Number(weightInput?.value || 0);
                if (!height || !weight || height <= 0) return;
                if (bmiInput) bmiInput.value = (weight / ((height / 100) ** 2)).toFixed(1);
            };

            const getDiagnosisInfo = () => {
                const visibleLabel = text(patientDiagnosisNodes[0]?.textContent);
                const label = text(selectedPatient?.diagnosis_label || visibleLabel);
                const codeFromLabel = label.match(/\b[A-Z]\d{2}(?:\.\d{1,3})?\b/i)?.[0] || '';
                const code = text(selectedPatient?.diagnosis_code || codeFromLabel).toUpperCase();
                const normalizedLabel = label.toLowerCase();
                const hasDiagnosis = label !== ''
                    && normalizedLabel !== 'не установлен'
                    && normalizedLabel !== 'диагноз не указан';

                return { code, label, hasDiagnosis };
            };

            const groupForRange = (range, age) => {
                if (age !== null && age !== undefined && age < 18) {
                    return range === '10–30%'
                        ? 'Ниже порога категории «ребёнок-инвалид»'
                        : 'У ребёнка диапазон 40–100% может учитываться для категории «ребёнок-инвалид»';
                }

                if (range === '40–60%') return 'Количественный диапазон III группы';
                if (range === '70–80%') return 'Количественный диапазон II группы';
                if (range === '90–100%') return 'Количественный диапазон I группы';
                return 'Ниже количественного порога инвалидности у взрослого';
            };

            const psychiatricCriteria = (rawCode) => {
                const code = text(rawCode).toUpperCase();
                const rows = (...items) => items.map(([range, summary]) => ({ range, summary }));

                if (/^F25/.test(code) || /^F20\.\d3$/.test(code)) {
                    return {
                        title: /^F25/.test(code) ? 'Шизоаффективные расстройства (F25)' : 'Шизофрения: эпизодический ремиттирующий тип',
                        rows: rows(
                            ['10–30%', 'Приступы суммарно до 4 мес./год; устойчивая ремиссия, незначительная резидуальная симптоматика без существенной дезадаптации.'],
                            ['40–60%', 'Приступы 4–10 мес./год; неполная/нестабильная ремиссия, умеренная резистентная симптоматика, существенно затрудняющая адаптацию.'],
                            ['70–80%', 'Затяжные или частые приступы 10+ мес./год; выраженная резистентная симптоматика и частичная дезадаптация.']
                        ),
                        note: 'Для этого варианта таблица 5.3.1 приказа № 374н не содержит отдельной строки 90–100%; система её не достраивает.',
                        exam: 'Для класса F00–F99 предусмотрен осмотр психиатра (90 дней первично / 120 повторно). Для F20–F29 дополнительно B03.035.004 — 120 дней при первичном направлении; при повторном при наличии динамики — 180 дней. Невролог — по показаниям.',
                    };
                }

                if (/^F20\.\d1$/.test(code)) {
                    return {
                        title: 'Шизофрения: эпизодический тип с нарастающим дефектом',
                        rows: rows(
                            ['10–30%', 'До 4 мес. приступов в год; стойкая ремиссия с незначительными дефицитарными изменениями без существенного влияния на адаптацию.'],
                            ['40–60%', 'Приступы 4–6 мес./год; умеренные стабильные/нарастающие дефицитарные изменения, существенно затрудняющие адаптацию.'],
                            ['70–80%', 'Приступы 6–10 мес./год; выраженные дефицитарные изменения и резистентная симптоматика с частичной дезадаптацией.'],
                            ['90–100%', 'Приступы 10+ мес./год; значительно выраженный стойкий дефект, отсутствие критики, полная дезадаптация и потребность в постоянном уходе/надзоре.']
                        ),
                        exam: 'Для класса F00–F99 предусмотрен осмотр психиатра (90 дней первично / 120 повторно). Для F20–F29 дополнительно B03.035.004 — 120 дней при первичном направлении; при повторном при наличии динамики — 180 дней. Невролог — по показаниям.',
                    };
                }

                if (/^F20/.test(code)) {
                    return {
                        title: 'Шизофрения (F20): критерий зависит от типа течения',
                        rows: rows(
                            ['10–30%', 'Незначительные стойкие дефицитарные/продуктивные нарушения, существенно не влияющие на адаптацию.'],
                            ['40–60%', 'Умеренные стойкие нарушения, существенно затрудняющие адаптацию. Для эпизодических вариантов учитывается также суммарная длительность приступов.'],
                            ['70–80%', 'Выраженные стойкие, нередко резистентные нарушения с частичной дезадаптацией.'],
                            ['90–100%', 'Значительно выраженный стойкий дефект с полной дезадаптацией и необходимостью постоянного ухода/надзора — для предусмотренных таблицей типов течения.']
                        ),
                        note: 'Для точной строки 5.3 приказа № 374н нужно учитывать код типа течения (например, F20.x1/F20.x3), полноту ремиссии, резистентность и уровень адаптации.',
                        exam: 'Для класса F00–F99 предусмотрен осмотр психиатра (90 дней первично / 120 повторно). Для F20–F29 дополнительно B03.035.004 — 120 дней при первичном направлении; при повторном при наличии динамики — 180 дней. Невролог — по показаниям.',
                    };
                }

                if (/^F21/.test(code)) {
                    return {
                        title: 'Шизотипическое расстройство (F21)',
                        rows: rows(
                            ['10–30%', 'Незначительные стойкие дефицитарные изменения и непсихотическая симптоматика без существенного влияния на адаптацию.'],
                            ['40–60%', 'Умеренные стойкие нарушения, существенно затрудняющие адаптацию.'],
                            ['70–80%', 'Выраженные стойкие нарушения с частичной дезадаптацией.'],
                            ['90–100%', 'Значительно выраженные резистентные нарушения, отсутствие критики, полная дезадаптация и необходимость постоянного ухода/надзора.']
                        ),
                        exam: 'Для класса F00–F99 предусмотрен осмотр психиатра (90 дней первично / 120 повторно). Для F20–F29 дополнительно B03.035.004 — 120 дней при первичном направлении; при повторном при наличии динамики — 180 дней. Невролог — по показаниям.',
                    };
                }

                if (/^F3\d/.test(code)) {
                    return {
                        title: 'Расстройства настроения (F30–F39)',
                        rows: rows(
                            ['10–30%', 'Незначительные аффективные расстройства суммарно до 6 мес./год, купируются терапией, стойкая ремиссия, без существенной дезадаптации.'],
                            ['40–60%', 'Умеренные стойкие/затяжные/частые эпизоды суммарно 6–10 мес./год, существенно затрудняющие адаптацию.'],
                            ['70–80%', 'Выраженные стойкие/затяжные/частые эпизоды суммарно 10+ мес./год, приводящие к частичной дезадаптации.'],
                            ['90–100%', 'Значительно выраженные расстройства 10+ мес./год с полной дезадаптацией и необходимостью постоянного ухода/надзора.']
                        ),
                        exam: 'Для класса F00–F99 предусмотрен осмотр психиатра (90 дней первично / 120 повторно). Для F30–F39 дополнительно B03.035.004 — 120 дней при первичном направлении; при повторном при наличии динамики — 180 дней. Невролог — по показаниям.',
                    };
                }

                if (/^F4[0-8]/.test(code)) {
                    return {
                        title: 'Невротические, стрессовые и соматоформные расстройства (F40–F48)',
                        rows: rows(
                            ['10–30%', 'Незначительная симптоматика невротического круга без существенного влияния на адаптацию.'],
                            ['40–60%', 'Умеренные стойкие расстройства, существенно затрудняющие адаптацию.'],
                            ['70–80%', 'Выраженные стойкие, резистентные к терапии нарушения с частичной дезадаптацией.'],
                            ['90–100%', 'Предусмотренная таблицей крайне тяжёлая стойкая резистентная симптоматика с полной дезадаптацией и потребностью в постоянном уходе/надзоре.']
                        ),
                        exam: 'Для класса F00–F99 предусмотрен осмотр психиатра (90 дней первично / 120 повторно). Для F40–F48 дополнительно B03.035.004 — 120 дней при первичном направлении; при повторном при наличии динамики — 180 дней. Невролог — по показаниям.',
                    };
                }

                if (/^F0\d/.test(code)) {
                    return {
                        title: 'Органические, включая симптоматические, психические расстройства (F00–F09)',
                        rows: rows(
                            ['10–30%', 'Незначительные/лёгкие стойкие когнитивные, аффективные и иные нарушения без существенной дезадаптации.'],
                            ['40–60%', 'Умеренные когнитивные и иные стойкие нарушения, существенно затрудняющие адаптацию.'],
                            ['70–80%', 'Выраженные когнитивные/психопатологические нарушения с частичной дезадаптацией.'],
                            ['90–100%', 'Значительно выраженное снижение когнитивных процессов/интеллекта с полной дезадаптацией и потребностью в постоянном уходе/надзоре.']
                        ),
                        exam: 'Для класса F00–F99 предусмотрен осмотр психиатра (90 дней первично / 120 повторно). Для F00–F09 также осмотр невролога (90/120), нейропсихологическое обследование 120 дней при первичном направлении либо по направлению психиатра B03.035.004; дополнительные исследования — по показаниям.',
                    };
                }

                if (/^F70/.test(code)) {
                    return {
                        title: 'Лёгкая умственная отсталость (F70)',
                        rows: rows(
                            ['10–30%', 'IQ 65–69 при отсутствии или незначительных сопутствующих нарушениях, существенно не влияющих на адаптацию.'],
                            ['40–60%', 'IQ 50–64 без сопутствующей симптоматики либо IQ 50–69 с умеренными стойкими нарушениями, существенно затрудняющими адаптацию.'],
                            ['70–80%', 'IQ 50–69 с выраженными стойкими поведенческими/психическими нарушениями и дезадаптацией.']
                        ),
                        note: 'Для F70 отдельную строку 90–100% в соответствующем блоке таблицы не подставляем.',
                        exam: 'Для класса F00–F99 предусмотрен осмотр психиатра (90 дней первично / 120 повторно). Для F70–F79 дополнительно B03.035.004 — 90 дней при первичном направлении; при повторном при наличии динамики — 180 дней. Дополнительные обследования — по показаниям.',
                    };
                }

                if (/^F71/.test(code)) {
                    return {
                        title: 'Умеренная умственная отсталость (F71)',
                        rows: rows(
                            ['40–60%', 'IQ 35–49 при отсутствии либо незначительных сопутствующих нарушениях, существенно затрудняющих адаптацию.'],
                            ['70–80%', 'IQ 35–49 с более выраженными стойкими нарушениями и частичной дезадаптацией.'],
                            ['90–100%', 'IQ 35–49 с тяжёлыми некорригируемыми нарушениями, полной дезадаптацией и необходимостью постоянного ухода/надзора.']
                        ),
                        exam: 'Для класса F00–F99 предусмотрен осмотр психиатра (90 дней первично / 120 повторно). Для F70–F79 дополнительно B03.035.004 — 90 дней при первичном направлении; при повторном при наличии динамики — 180 дней. Дополнительные обследования — по показаниям.',
                    };
                }

                if (/^F7[23]/.test(code)) {
                    return {
                        title: 'Тяжёлая / глубокая умственная отсталость (F72–F73)',
                        rows: rows(
                            ['90–100%', 'Тяжёлая или глубокая интеллектуальная недостаточность с полной дезадаптацией и необходимостью постоянного ухода/надзора.']
                        ),
                        exam: 'Для класса F00–F99 предусмотрен осмотр психиатра (90 дней первично / 120 повторно). Для F70–F79 дополнительно B03.035.004 — 90 дней при первичном направлении; при повторном при наличии динамики — 180 дней. Дополнительные обследования — по показаниям.',
                    };
                }

                if (/^F84\.[0125]/.test(code)) {
                    return {
                        title: 'Расстройства аутистического спектра (F84.0/F84.1/F84.2/F84.5)',
                        rows: rows(
                            ['10–30%', 'Контакт в основном сохранён; нарушения коммуникации и поведения минимальны, в целом социальная адаптация сохранена.'],
                            ['40–60%', 'Существенные трудности социальной адаптации; требуется периодическое социальное сопровождение.'],
                            ['70–80%', 'Выраженный дефицит контакта/коммуникации и частичная дезадаптация; требуется постоянное социальное сопровождение.'],
                            ['90–100%', 'Резко выраженный дефицит контакта и полная дезадаптация с нуждаемостью в постоянном постороннем уходе и надзоре.']
                        ),
                        exam: 'Для класса F00–F99 предусмотрен осмотр психиатра (90 дней первично / 120 повторно). Для F84.0/F84.1/F84.2/F84.5 дополнительно B03.035.004 — 90 дней при первичном направлении; при повторном при наличии динамики — 180 дней. Дополнительные обследования — по показаниям.',
                    };
                }

                if (/^F\d{2}/.test(code)) {
                    return {
                        title: `Психическое расстройство ${code}`,
                        rows: [],
                        note: 'Для этого кода в подключённом справочнике нет отдельной код-специфической шкалы. Не подставляем проценты по аналогии: используйте общий критерий приказа № 374н и соответствующую строку его количественной системы.',
                        exam: 'Для класса F00–F99 предусмотрен общий набор обследований, а дополнительные исследования зависят от рубрики и клинических показаний. Проверьте актуальный перечень приказа № 259н/238н.',
                    };
                }

                return {
                    title: code ? `Основной диагноз ${code}` : 'Основной диагноз не определён',
                    rows: [],
                    note: code
                        ? 'Код не относится к психиатрическим рубрикам F00–F99. Психиатрический справочник критериев не применяется автоматически.'
                        : 'В карточке пациента отсутствует код основного диагноза. Для оформленной формы 088/у код МКБ основного заболевания обязателен.',
                    exam: 'Без корректного кода МКБ невозможно надёжно определить диагноз-зависимый перечень обследований.',
                };
            };

            const renderMseCriteria = () => {
                if (!mseCriteriaBody || !mseCriteriaCode) return;

                const { code, hasDiagnosis } = getDiagnosisInfo();
                const age = selectedPatient?.age ?? null;
                mseCriteriaCode.textContent = code || 'МКБ не определён';

                if (!hasDiagnosis) {
                    mseCriteriaBody.innerHTML = '<p class="mse-criteria-intro">Основной диагноз не установлен. Оформление направления на МСЭ будет заблокировано до появления диагноза и кода МКБ в карточке пациента.</p>';
                    if (mseRequiredExams) mseRequiredExams.textContent = 'Без основного диагноза нельзя определить обязательный перечень обследований.';
                    return;
                }

                if (age !== null && age < 18) {
                    mseCriteriaBody.innerHTML = '<p class="mse-criteria-intro">Пациент младше 18 лет. Не подставляем взрослую количественную шкалу из приложения № 1 приказа № 374н. Для ребёнка применяется отдельная количественная система приложения № 2 и категория «ребёнок-инвалид».</p>';
                    if (mseRequiredExams) {
                        mseRequiredExams.textContent = psychiatricExamRequirementsByAge(code, age, referralKindSelect?.value || 'initial');
                    }
                    return;
                }

                const criteria = psychiatricCriteria(code);
                const fragment = document.createDocumentFragment();
                const intro = document.createElement('p');
                intro.className = 'mse-criteria-intro';
                intro.textContent = criteria.title;
                fragment.append(intro);

                if (criteria.rows?.length) {
                    const list = document.createElement('div');
                    list.className = 'mse-criteria-rows';
                    criteria.rows.forEach((row) => {
                        const item = document.createElement('div');
                        item.className = 'mse-criteria-row';

                        const range = document.createElement('div');
                        range.className = 'mse-criteria-row__range';
                        range.textContent = row.range;

                        const copy = document.createElement('div');
                        copy.className = 'mse-criteria-row__copy';
                        const group = document.createElement('strong');
                        group.textContent = groupForRange(row.range, age);
                        const summary = document.createElement('span');
                        summary.textContent = row.summary;
                        copy.append(group, summary);
                        item.append(range, copy);
                        list.append(item);
                    });
                    fragment.append(list);
                }

                if (criteria.note) {
                    const note = document.createElement('div');
                    note.className = 'mse-criteria-panel__note';
                    note.textContent = criteria.note;
                    fragment.append(note);
                }

                mseCriteriaBody.replaceChildren(fragment);
                if (mseRequiredExams) {
                    mseRequiredExams.textContent = psychiatricExamRequirementsByAge(code, age, referralKindSelect?.value || 'initial');
                }
            };

            const psychiatricExamRequirementsByAge = (code, age, referralKind) => {
                code = text(code).toUpperCase();
                const repeated = referralKind === 'repeated';
                const child = age !== null && age < 18;

                if (age === null) {
                    return 'Возраст не определён: нельзя выбрать раздел I или II приказа № 259н/238н.';
                }

                const base = child
                    ? `Раздел II (до 18 лет): осмотр детского врача-психиатра${age >= 15 ? ' / подросткового психиатра по возрасту' : ''}; результаты психодиагностики и иных обследований — по строке соответствующего диагноза.`
                    : 'Раздел I (18+): осмотр врача-психиатра; результаты психодиагностики и иных обследований — по строке соответствующего диагноза.';

                if (!code) return `${base} Без кода МКБ диагноз-зависимый перечень определить нельзя.`;

                if (/^F(20|21|22|23|24|25|28|29|3\d|4\d)/.test(code)) {
                    return `${base} Для F20–F29, F30–F39 и F40–F48 обычно требуется B03.035.004 (патопсихологическое/экспериментально-психологическое исследование): ${repeated ? 'при повторном направлении ориентируйтесь на срок до 180 дней при наличии динамики' : 'при первичном направлении — результат давностью до 120 дней'}.`;
                }

                if (/^F7\d/.test(code)) {
                    const initialDays = child ? 120 : 90;
                    return `${base} Для F70–F79 требуется B03.035.004: при первичном направлении — до ${initialDays} дней; при повторном при наличии динамики — до 180 дней.`;
                }

                if (/^F84\.[0125]/.test(code)) {
                    const initialDays = child ? 120 : 90;
                    return `${base} Для F84.0/F84.1/F84.2/F84.5 предусмотрено B03.035.004: при первичном направлении — до ${initialDays} дней; при повторном при наличии динамики — до 180 дней.`;
                }

                if (/^F0\d/.test(code)) {
                    return `${base} Для F00–F09 дополнительно проверяются неврологический и нейро-/патопсихологический блоки по соответствующей строке приказа.`;
                }

                if (child && /^F9\d/.test(code)) {
                    return `${base} Для F90–F98 применяйте отдельные детские строки приказа: состав психодиагностики/инструментальных исследований зависит от конкретного кода.`;
                }

                return `${base} Для ${code} проверьте точную диагноз-зависимую строку приказа № 259н/238н и срок действия каждого результата.`;
            };

            const ageDocumentationProfile = () => {
                const age = selectedPatient?.age ?? null;
                const months = selectedPatient?.age_months ?? calculateAgeMonths(selectedPatient?.birth_at);
                const citizenship = text(citizenshipSelect?.value || selectedPatient?.citizenship);
                const code = getDiagnosisInfo().code;
                const referralKind = referralKindSelect?.value || 'initial';

                if (age === null || age === undefined) {
                    return {
                        badge: 'Возраст не определён',
                        title: 'Проверьте дату рождения пациента',
                        identity: 'Нельзя определить возрастной тип документа.',
                        consent: 'Нельзя определить возрастной сценарий согласия.',
                        section: 'Нельзя выбрать раздел I/II приказа № 259н/238н.',
                        specialist: 'Нельзя определить возрастную специализацию врача.',
                        special: 'Оформление будет заблокировано, пока возраст не определён.',
                        exams: psychiatricExamRequirementsByAge(code, null, referralKind),
                    };
                }

                const ageNumber = Number(age);
                const isRussian = citizenship === 'russian';
                const identity = isRussian
                    ? (ageNumber < 14
                        ? 'Гражданин РФ до 14 лет: свидетельство о рождении (либо иной предусмотренный документ).'
                        : 'Гражданин РФ с 14 лет: паспорт гражданина РФ (либо иной предусмотренный документ).')
                    : (citizenship
                        ? 'Иностранный гражданин / лицо без гражданства: применимый документ, удостоверяющий личность.'
                        : 'Сначала укажите гражданство — от него зависит допустимый документ, удостоверяющий личность.');

                let badge = `${ageNumber} лет`;
                let title = '';
                let consent = '';
                let section = '';
                let specialist = '';
                let special = [];

                if (ageNumber < 1) {
                    badge = months !== null ? `${months} полн. мес.` : 'до 1 года';
                    title = 'Ребёнок до 1 года';
                    consent = 'Психиатрический сценарий: согласие и защита прав — через родителя или иного законного представителя; п. 17 формы 088/у должен быть заполнен.';
                    section = 'Приказ № 259н/238н: раздел II для лиц до 18 лет.';
                    specialist = 'Детский врач-психиатр.';
                    special = ['В п. 7 формы 088/у возраст указывается в полных месяцах.', 'Обязательны масса тела при рождении (28.7) и физическое развитие (28.8).'];
                } else if (ageNumber < 3) {
                    title = 'Ребёнок 1–2 лет';
                    consent = 'Психиатрический сценарий: согласие и защита прав — через родителя или иного законного представителя; п. 17 формы 088/у должен быть заполнен.';
                    section = 'Приказ № 259н/238н: раздел II для лиц до 18 лет.';
                    specialist = 'Детский врач-психиатр.';
                    special = ['Обязательны масса тела при рождении (28.7) и физическое развитие (28.8).'];
                } else if (ageNumber < 14) {
                    title = 'Ребёнок 3–13 лет';
                    consent = 'До 15 лет в психиатрическом сценарии согласие/представительство — родитель или иной законный представитель.';
                    section = 'Приказ № 259н/238н: раздел II для лиц до 18 лет.';
                    specialist = 'Детский врач-психиатр.';
                    special = ['Сведения об образовании заполняются, если ребёнок обучается; для дошкольника — возрастная группа.'];
                } else if (ageNumber < 15) {
                    title = 'Подросток 14 лет';
                    consent = 'Пациент уже в паспортном возрасте, но до 15 лет психиатрический сценарий согласия/представительства остаётся через законного представителя.';
                    section = 'Приказ № 259н/238н: раздел II для лиц до 18 лет.';
                    specialist = 'Детский врач-психиатр.';
                    special = ['Для установления инвалидности выбирается категория «ребёнок-инвалид», не взрослая группа.'];
                } else if (ageNumber < 18) {
                    title = 'Подросток 15–17 лет';
                    consent = 'По одному лишь возрасту законный представитель для добровольной психиатрической помощи уже не обязателен; если представитель участвует, его блок формы 088/у заполняется полностью.';
                    section = 'Приказ № 259н/238н: раздел II для лиц до 18 лет.';
                    specialist = 'Детский/подростковый врач-психиатр в соответствии с возрастом и организацией.';
                    special = ['Для установления инвалидности выбирается категория «ребёнок-инвалид», не взрослая группа.'];
                } else {
                    title = 'Совершеннолетний пациент';
                    consent = 'Согласие оформляет сам гражданин; представитель — только при наличии законных/уполномоченных оснований.';
                    section = 'Приказ № 259н/238н: раздел I для лиц 18 лет и старше.';
                    specialist = 'Врач-психиатр взрослой сети.';
                    special = ['Для установления инвалидности используется взрослая цель «Установление группы инвалидности».'];
                }

                return {
                    badge,
                    title,
                    identity,
                    consent,
                    section,
                    specialist,
                    special: special.join(' '),
                    exams: psychiatricExamRequirementsByAge(code, ageNumber, referralKind),
                };
            };

            const renderAgeDocumentationRequirements = () => {
                if (!mseAgeRequirements || !mseAgeBody || !mseAgeBadge) return;

                const profile = ageDocumentationProfile();
                mseAgeBadge.textContent = profile.badge;

                const items = [
                    ['Возрастной сценарий', profile.title, false],
                    ['Документ личности', profile.identity, false],
                    ['Согласие / представитель', profile.consent, false],
                    ['Перечень обследований', profile.section, false],
                    ['Профиль психиатра', profile.specialist, false],
                    ['Особые поля формы 088/у', profile.special || 'Дополнительных возрастных полей нет.', false],
                    ['Что проверить по диагнозу', profile.exams, true],
                ];

                const fragment = document.createDocumentFragment();
                items.forEach(([title, value, wide]) => {
                    const item = document.createElement('div');
                    item.className = `mse-age-requirement${wide ? ' mse-age-requirement--wide' : ''}`;
                    const heading = document.createElement('strong');
                    heading.textContent = title;
                    const copy = document.createElement('span');
                    copy.textContent = value;
                    item.append(heading, copy);
                    fragment.append(item);
                });
                mseAgeBody.replaceChildren(fragment);

                const age = selectedPatient?.age ?? null;
                underThreeFields?.classList.toggle('d-none', !(age !== null && Number(age) < 3));
            };

            const getExaminationCompleteness = () => {
                const rows = [...root.querySelectorAll('[data-repeater="examinations"] [data-repeater-row]')];
                const nonEmpty = rows.filter((row) => [...row.querySelectorAll('input, textarea')].some((field) => text(field.value) !== ''));
                if (!nonEmpty.length) return { hasRows: false, complete: false };

                const complete = nonEmpty.every((row) => {
                    const date = row.querySelector('input[type="date"]');
                    const fields = [...row.querySelectorAll('input:not([type="date"]), textarea')];
                    const code = fields[0];
                    const name = fields[1];
                    const result = fields[2];
                    return Boolean(text(date?.value) && text(name?.value) && text(result?.value));
                });

                return { hasRows: true, complete };
            };

            const getMseGateBlockers = () => {
                const blockers = [];
                const { code, hasDiagnosis } = getDiagnosisInfo();
                const age = selectedPatient?.age ?? null;
                const selectedPurposeValues = purposeCheckboxes.filter((item) => item.checked).map((item) => item.value);
                const examinationState = getExaminationCompleteness();

                if (!patientId?.value) blockers.push({ key: 'patient', message: 'Не выбран пациент.' });
                if (!hasDiagnosis) blockers.push({ key: 'diagnosis', message: 'В карте не установлен основной диагноз.' });
                if (hasDiagnosis && !code) blockers.push({ key: 'diagnosis_code', message: 'У основного диагноза отсутствует код МКБ (п. 31.2 формы 088/у).' });

                if (age === null || age === undefined) {
                    blockers.push({ key: 'age', message: 'Не удалось определить возраст пациента. Проверьте дату рождения.' });
                }

                const citizenship = text(citizenshipSelect?.value);
                if (!citizenship) {
                    blockers.push({ key: 'citizenship', message: 'Не указано гражданство пациента.' });
                }

                if (!text(root.querySelector('#patientSex')?.value)) {
                    blockers.push({ key: 'patient_sex', message: 'Не указан пол пациента.' });
                }
                if (!text(root.querySelector('#patientSnils')?.value)) {
                    blockers.push({ key: 'patient_snils', message: 'Не указан СНИЛС пациента.' });
                }
                const homeless = root.querySelector('input[name="patient_homeless"][type="checkbox"]')?.checked;
                if (!homeless && !text(root.querySelector('#patientAddress')?.value)) {
                    blockers.push({ key: 'patient_address', message: 'Не указан адрес места жительства/пребывания пациента.' });
                }

                const identityMissing = [];
                const identityName = root.querySelector('#patientIdentityName');
                const identitySeries = root.querySelector('#patientIdentitySeries');
                const identityNumber = root.querySelector('#patientIdentityNumber');
                const identityIssuedBy = root.querySelector('#patientIdentityIssuedBy');
                const identityIssueDate = root.querySelector('#patientIdentityIssueDate');
                if (!text(identityName?.value)) identityMissing.push('наименование');
                if (citizenship === 'russian' && !text(identitySeries?.value)) identityMissing.push('серия');
                if (!text(identityNumber?.value)) identityMissing.push('номер');
                if (!text(identityIssuedBy?.value)) identityMissing.push('кем выдан');
                if (!text(identityIssueDate?.value)) identityMissing.push('дата выдачи');
                if (identityMissing.length) {
                    const kind = citizenship === 'russian' && age !== null && age < 14
                        ? 'свидетельства о рождении'
                        : (citizenship === 'russian' && age !== null && age >= 14 ? 'паспорта' : 'документа личности');
                    blockers.push({ key: 'identity', message: `Не полностью заполнены реквизиты ${kind}: ${identityMissing.join(', ')}.` });
                }

                const representativeFields = [
                    ['#representativeFullName', 'ФИО'],
                    ['#representativeBirthDate', 'дата рождения'],
                    ['#representativeAuthorityDocument', 'документ о полномочиях'],
                    ['#representativeIdentity', 'документ личности'],
                    ['#representativePhone', 'телефон'],
                    ['#representativeSnils', 'СНИЛС'],
                ];
                const representativeValues = representativeFields.map(([selector, label]) => ({
                    element: root.querySelector(selector),
                    label,
                }));
                const anyRepresentative = representativeValues.some(({ element }) => text(element?.value));
                if (age !== null && age < 15) {
                    const missing = representativeValues.filter(({ element }) => !text(element?.value)).map(({ label }) => label);
                    if (missing.length) blockers.push({ key: 'representative', message: `Для пациента младше 15 лет заполните блок законного представителя: ${missing.join(', ')}.` });
                } else if (anyRepresentative) {
                    const missing = representativeValues.filter(({ element }) => !text(element?.value)).map(({ label }) => label);
                    if (missing.length) blockers.push({ key: 'representative', message: `Блок представителя заполнен не полностью: ${missing.join(', ')}.` });
                }

                if (!text(root.querySelector('#patientPhone')?.value) && !text(root.querySelector('#representativePhone')?.value)) {
                    blockers.push({ key: 'contact', message: 'Не указан контактный телефон пациента или его представителя.' });
                }

                if (!text(documentDateInput?.value)) blockers.push({ key: 'document_date', message: 'Не указана дата направления.' });
                if (!text(referralKindSelect?.value)) blockers.push({ key: 'referral_kind', message: 'Не указан вид направления (первичное/повторное).' });
                if (!text(root.querySelector('#examinationFormat')?.value)) blockers.push({ key: 'format', message: 'Не выбрана форма проведения МСЭ.' });

                if (!text(root.querySelector('#protocolNumber')?.value) || !text(root.querySelector('#protocolDate')?.value)) {
                    blockers.push({ key: 'protocol', message: 'Нет номера и/или даты протокола врачебной комиссии.' });
                }
                if (!text(root.querySelector('#consentDate')?.value)) blockers.push({ key: 'consent_date', message: 'Не указана дата письменного согласия на направление и проведение МСЭ.' });
                if (!consentDocumentAvailable()) blockers.push({ key: 'consent_document', message: 'Не загружено подписанное согласие.' });
                if (!selectedPurposeValues.length) blockers.push({ key: 'purpose', message: 'Не выбрана цель проведения МСЭ.' });

                if (!text(observedSinceDateInput?.value)) blockers.push({ key: 'observation', message: 'Не указано, с какого времени пациент наблюдается в медицинской организации.' });
                if (!text(root.querySelector('#diseaseHistory')?.value)) blockers.push({ key: 'disease_history', message: 'Не заполнен анамнез заболевания.' });
                if (!text(root.querySelector('#lifeHistory')?.value)) blockers.push({ key: 'life_history', message: 'Не заполнен анамнез жизни.' });
                if (!text(root.querySelector('#healthStatus')?.value)) blockers.push({ key: 'mental_status', message: 'Не описан психический статус и стойкие нарушения функций.' });

                if (!text(root.querySelector('#heightCm')?.value)
                    || !text(root.querySelector('#weightKg')?.value)
                    || !text(root.querySelector('#bmi')?.value)
                    || !text(root.querySelector('#bodyBuild')?.value)) {
                    blockers.push({ key: 'physical', message: 'Не заполнены обязательные физические параметры п. 28 формы 088/у: рост, масса, ИМТ и телосложение.' });
                }

                if (age !== null && age < 3) {
                    const underThreeMissing = [];
                    if (!text(root.querySelector('#birthWeightG')?.value)) underThreeMissing.push('масса тела при рождении');
                    if (!text(root.querySelector('#physicalDevelopment')?.value)) underThreeMissing.push('физическое развитие');
                    if (underThreeMissing.length) {
                        blockers.push({ key: 'under_three', message: `Для ребёнка младше 3 лет заполните: ${underThreeMissing.join(', ')}.` });
                    }
                }

                if (!examinationState.hasRows) {
                    blockers.push({ key: 'examinations', message: 'Не внесены результаты обязательных обследований для МСЭ.' });
                } else if (!examinationState.complete) {
                    blockers.push({ key: 'examinations', message: 'Есть незаполненная строка обследования: нужны дата, наименование и результат.' });
                }

                if (!mseGatePersistent?.checked) blockers.push({ key: 'persistent', message: 'ВК не подтвердила наличие данных о стойком нарушении функций.' });
                if (!mseGateMeasures?.checked) blockers.push({ key: 'measures', message: 'Не подтверждено проведение необходимых диагностики, лечения и медицинской реабилитации.' });
                if (!mseGateExams?.checked) blockers.push({ key: 'required_exams', message: 'Не подтверждена проверка возрастного и диагноз-зависимого перечня обследований по приказу № 259н/238н.' });

                if (!text(root.querySelector('#commissionChairman')?.value)) blockers.push({ key: 'commission', message: 'Не указан председатель врачебной комиссии.' });

                if (age !== null && age >= 18 && selectedPurposeValues.includes('disabled_child')) {
                    blockers.push({ key: 'age_purpose', message: 'Для пациента 18+ выбрана цель, относящаяся к категории «ребёнок-инвалид».' });
                }
                if (age !== null && age < 18 && selectedPurposeValues.includes('disability_group')) {
                    blockers.push({ key: 'age_purpose', message: 'Для пациента младше 18 лет выбрана взрослая цель установления группы инвалидности.' });
                }

                return blockers;
            };

            const renderMseGate = () => {
                if (!mseGateCard || !mseGateStatus || !mseGateList) return;
                const blockers = getMseGateBlockers();
                const prepared = statusSelect?.value === 'prepared';
                const ready = blockers.length === 0;
                mseGateCard.classList.toggle('is-ready', ready);

                const statusIcon = mseGateCard.querySelector('.mse-gate-card__status i');
                if (statusIcon) statusIcon.className = `bi ${ready ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'}`;

                if (ready) {
                    mseGateStatus.textContent = prepared
                        ? 'Критических препятствий для оформления не найдено.'
                        : 'По текущим данным критических препятствий нет. Черновик можно сохранить.';
                } else {
                    mseGateStatus.textContent = prepared
                        ? `Оформление заблокировано: ${blockers.length} ${blockers.length === 1 ? 'условие не выполнено' : 'условий не выполнено'}.`
                        : `Черновик можно сохранить. До оформления осталось: ${blockers.length}.`;
                }

                const fragment = document.createDocumentFragment();
                if (!blockers.length) {
                    const empty = document.createElement('div');
                    empty.className = 'mse-gate-empty';
                    empty.textContent = 'Формальные и клинические условия, которые может проверить система, выполнены.';
                    fragment.append(empty);
                } else {
                    blockers.slice(0, 8).forEach((blocker) => {
                        const item = document.createElement('div');
                        item.className = 'mse-gate-item';
                        const icon = document.createElement('i');
                        icon.className = 'bi bi-x-circle';
                        const copy = document.createElement('span');
                        copy.textContent = blocker.message;
                        item.append(icon, copy);
                        fragment.append(item);
                    });
                    if (blockers.length > 8) {
                        const more = document.createElement('div');
                        more.className = 'mse-gate-item';
                        more.textContent = `И ещё ${blockers.length - 8}.`;
                        fragment.append(more);
                    }
                }
                mseGateList.replaceChildren(fragment);
            };

            const updateReadiness = () => {
                if (!form) return;
                const prepared = statusSelect?.value === 'prepared';
                const diagnosisInfo = getDiagnosisInfo();
                const readiness = {
                    patient: Boolean(patientId?.value),
                    core: Boolean(root.querySelector('#documentDate')?.value && root.querySelector('#referralKind')?.value && root.querySelector('#examinationFormat')?.value),
                    protocol: !prepared || Boolean(root.querySelector('#protocolNumber')?.value && root.querySelector('#protocolDate')?.value && root.querySelector('#consentDate')?.value),
                    consent_document: !prepared || consentDocumentAvailable(),
                    purpose: !prepared || purposeCheckboxes.some((checkbox) => checkbox.checked),
                    observation_period: Boolean(observedSinceDateInput?.value),
                    diagnosis: diagnosisInfo.hasDiagnosis && Boolean(diagnosisInfo.code),
                    clinical: Boolean(text(root.querySelector('#diseaseHistory')?.value) && text(root.querySelector('#healthStatus')?.value)),
                    commission: Boolean(text(root.querySelector('#commissionChairman')?.value)),
                };

                const values = Object.values(readiness);
                const percent = Math.round((values.filter(Boolean).length / values.length) * 100);
                const percentNode = root.querySelector('[data-readiness-percent]');
                if (percentNode) percentNode.textContent = `${percent}%`;

                Object.entries(readiness).forEach(([key, ready]) => {
                    root.querySelector(`[data-readiness-item="${key}"]`)?.classList.toggle('is-ready', ready);
                });

                renderMseGate();

                progressLinks.forEach((link) => {
                    const key = link.dataset.progressLink;
                    const complete = key === 'patient' ? readiness.patient
                        : key === 'commission' ? readiness.core && readiness.protocol && readiness.purpose && (!prepared || readiness.consent_document)
                            : key === 'clinical' ? readiness.diagnosis && readiness.clinical
                                : key === 'signatures' ? readiness.commission
                                    : true;
                    link.classList.toggle('is-complete', complete);
                });
            };

            const setActiveSection = (key) => {
                progressLinks.forEach((link) => link.classList.toggle('is-active', link.dataset.progressLink === key));
            };

            const repeaterConfig = {
                'temporary-disability': {
                    template: document.getElementById('mseTemporaryDisabilityTemplate'),
                    body: root.querySelector('[data-repeater="temporary-disability"] [data-repeater-body]'),
                },
                examinations: {
                    template: document.getElementById('mseExaminationsTemplate'),
                    body: root.querySelector('[data-repeater="examinations"] [data-repeater-body]'),
                },
                'commission-members': {
                    template: document.getElementById('mseCommissionMembersTemplate'),
                    body: root.querySelector('[data-repeater="commission-members"] [data-repeater-body]'),
                },
            };

            const nextRepeaterIndex = (config) => {
                const stored = Number(config.body.dataset.nextIndex);
                if (Number.isInteger(stored) && stored >= 0) {
                    config.body.dataset.nextIndex = String(stored + 1);
                    return stored;
                }

                const indexes = [...config.body.querySelectorAll('[name]')]
                    .map((field) => field.name.match(/\[(\d+)]/))
                    .filter(Boolean)
                    .map((match) => Number(match[1]));
                const next = indexes.length ? Math.max(...indexes) + 1 : 0;
                config.body.dataset.nextIndex = String(next + 1);
                return next;
            };

            const addRepeaterRow = (key) => {
                const config = repeaterConfig[key];
                if (!config?.template || !config?.body) return;
                const index = nextRepeaterIndex(config);
                const html = config.template.innerHTML.replaceAll('__INDEX__', String(index));
                config.body.insertAdjacentHTML('beforeend', html);
                config.body.lastElementChild?.querySelector('input, textarea')?.focus();
                updateReadiness();
            };

            root.addEventListener('click', (event) => {
                const addButton = event.target.closest('[data-add-row]');
                if (addButton) {
                    addRepeaterRow(addButton.dataset.addRow);
                    return;
                }

                const removeButton = event.target.closest('[data-remove-row]');
                if (removeButton) {
                    removeButton.closest('[data-repeater-row]')?.remove();
                    updateReadiness();
                    return;
                }

                const resultButton = event.target.closest('[data-patient-index]');
                if (resultButton) selectPatient(searchResults[Number(resultButton.dataset.patientIndex)]);
            });

            patientSearch?.addEventListener('input', () => {
                patientSearch.setCustomValidity('');
                const query = patientSearch.value.trim();

                if (selectedPatient && query !== selectedPatient.full_name) clearPatient(false);
                window.clearTimeout(searchTimer);

                if (query.length < 1) {
                    closeSuggestions();
                    return;
                }

                searchTimer = window.setTimeout(() => searchPatients(query), 250);
            });

            patientSearch?.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeSuggestions();
                if (event.key === 'Enter' && searchResults.length) {
                    event.preventDefault();
                    selectPatient(searchResults[0]);
                }
            });

            patientClear?.addEventListener('click', () => {
                clearPatient(true);
                patientSearch?.focus();
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('.mse-patient-search')) closeSuggestions();
                if (!event.target.closest('.mse-address-control')) hideAllAddressSuggestions();
            });

            progressLinks.forEach((link) => {
                link.addEventListener('click', (event) => {
                    const section = root.querySelector(link.getAttribute('href'));
                    if (!section) return;
                    event.preventDefault();
                    setActiveSection(link.dataset.progressLink);
                    section.scrollIntoView({
                        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                        block: 'start',
                    });
                });
            });

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    const visible = entries
                        .filter((entry) => entry.isIntersecting)
                        .sort((left, right) => right.intersectionRatio - left.intersectionRatio)[0];
                    if (visible?.target?.dataset?.formSection) setActiveSection(visible.target.dataset.formSection);
                }, { rootMargin: '-18% 0px -66% 0px', threshold: [0, .1, .25] });
                sections.forEach((section) => observer.observe(section));
            }

            consentFileInput?.addEventListener('change', () => {
                if (consentFileInput.files?.length && consentRemoveInput) {
                    consentRemoveInput.value = '0';
                }
                validateConsentFile();
                syncConsentUpload();
            });

            root.querySelector('[data-consent-clear-selection]')?.addEventListener('click', () => {
                if (consentFileInput) {
                    consentFileInput.value = '';
                    consentFileInput.setCustomValidity('');
                }
                syncConsentUpload();
            });

            root.querySelector('[data-consent-remove]')?.addEventListener('click', () => {
                if (consentRemoveInput) consentRemoveInput.value = '1';
                if (consentFileInput) {
                    consentFileInput.value = '';
                    consentFileInput.setCustomValidity('');
                }
                syncConsentUpload();
            });

            root.querySelector('[data-consent-restore]')?.addEventListener('click', () => {
                if (consentRemoveInput) consentRemoveInput.value = '0';
                syncConsentUpload();
            });

            consentDropzone?.addEventListener('click', (event) => {
                if (event.target.closest('label, button, a')) return;
                consentFileInput?.click();
            });

            ['dragenter', 'dragover'].forEach((eventName) => {
                consentDropzone?.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    consentDropzone.classList.add('is-dragover');
                });
            });

            ['dragleave', 'drop'].forEach((eventName) => {
                consentDropzone?.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    consentDropzone.classList.remove('is-dragover');
                });
            });

            consentDropzone?.addEventListener('drop', (event) => {
                const file = event.dataTransfer?.files?.[0];
                if (!file || !consentFileInput || typeof DataTransfer === 'undefined') return;

                const transfer = new DataTransfer();
                transfer.items.add(file);
                consentFileInput.files = transfer.files;
                consentFileInput.dispatchEvent(new Event('change', { bubbles: true }));
            });

            consentPrintButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    openConsentPreview(button.dataset.consentMode || 'auto');
                });
            });

            statusSelect?.addEventListener('change', syncStatus);
            referralKindSelect?.addEventListener('change', () => {
                syncObservationEligibility();
                syncPreviousIpraVisibility();
                renderAgeDocumentationRequirements();
                renderMseCriteria();
                updateReadiness();
            });
            previousDisabilityGroupSelect?.addEventListener('change', () => {
                syncPreviousIpraVisibility();
                updateReadiness();
            });
            previousIpraFields.forEach((field) => {
                field.addEventListener('input', syncPreviousIpraVisibility);
                field.addEventListener('change', syncPreviousIpraVisibility);
            });
            documentDateInput?.addEventListener('change', () => {
                syncObservationEligibility();
                updateReadiness();
            });
            observedSinceDateInput?.addEventListener('change', () => {
                if (observedSinceYearInput) {
                    observedSinceYearInput.value = observedSinceDateInput.value
                        ? observedSinceDateInput.value.slice(0, 4)
                        : '';
                }

                syncObservationEligibility();
                updateReadiness();
            });
            citizenshipSelect?.addEventListener('change', () => {
                syncCitizenship();
                renderAgeDocumentationRequirements();
                updateReadiness();
            });
            elnToggle?.addEventListener('change', syncEln);
            heightInput?.addEventListener('input', calculateBmi);
            weightInput?.addEventListener('input', calculateBmi);
            form?.addEventListener('input', updateReadiness);
            form?.addEventListener('change', updateReadiness);
            [mseGatePersistent, mseGateMeasures, mseGateExams].forEach((checkbox) => {
                checkbox?.addEventListener('change', updateReadiness);
            });

            form?.addEventListener('submit', (event) => {
                if (!patientId?.value) {
                    event.preventDefault();
                    patientSearch?.setCustomValidity('Выберите пациента из найденного списка.');
                    patientSearch?.reportValidity();
                    patientSearch?.focus();
                    return;
                }

                syncObservationEligibility();

                if (!validateConsentFile()) {
                    event.preventDefault();
                    consentFileInput?.reportValidity();
                    root.querySelector('[data-consent-upload]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                if (statusSelect?.value === 'prepared') {
                    const blockers = getMseGateBlockers();
                    if (blockers.length) {
                        event.preventDefault();
                        renderMseGate();

                        const first = blockers[0];
                        const focusMap = {
                            diagnosis: patientSearch,
                            diagnosis_code: patientSearch,
                            age: patientSearch,
                            citizenship: citizenshipSelect,
                            patient_sex: root.querySelector('#patientSex'),
                            patient_snils: root.querySelector('#patientSnils'),
                            patient_address: root.querySelector('#patientAddress'),
                            contact: root.querySelector('#patientPhone'),
                            identity: root.querySelector('#patientIdentityName'),
                            representative: root.querySelector('#representativeFullName'),
                            under_three: root.querySelector('#birthWeightG'),
                            document_date: documentDateInput,
                            referral_kind: referralKindSelect,
                            format: root.querySelector('#examinationFormat'),
                            protocol: root.querySelector('#protocolNumber'),
                            consent_date: root.querySelector('#consentDate'),
                            consent_document: consentFileInput,
                            purpose: purposeCheckboxes[0],
                            observation: observedSinceDateInput,
                            disease_history: root.querySelector('#diseaseHistory'),
                            life_history: root.querySelector('#lifeHistory'),
                            mental_status: root.querySelector('#healthStatus'),
                            physical: root.querySelector('#heightCm'),
                            examinations: root.querySelector('[data-repeater="examinations"] input, [data-repeater="examinations"] textarea'),
                            persistent: mseGatePersistent,
                            measures: mseGateMeasures,
                            required_exams: mseGateExams,
                            commission: root.querySelector('#commissionChairman'),
                            age_purpose: purposeCheckboxes.find((item) => item.checked),
                        };

                        const target = focusMap[first.key] || mseGateCard;
                        target?.focus?.();
                        (target?.closest?.('.mse-card, .mse-sidebar-card') || mseGateCard)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        window.alert(`Направление нельзя перевести в статус «Оформлено».\n\n${blockers.map((item) => '• ' + item.message).join('\n')}`);
                        return;
                    }
                }

                purposeCheckboxes.forEach((checkbox) => checkbox.setCustomValidity(''));
                submitButtons.forEach((button) => {
                    button.disabled = true;
                    button.setAttribute('aria-busy', 'true');
                });
            });

            purposeCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', () => {
                purposeCheckboxes.forEach((item) => item.setCustomValidity(''));
                syncObservationEligibility();
                syncPreviousIpraVisibility();
                renderAgeDocumentationRequirements();
                renderMseCriteria();
                updateReadiness();
            }));

            if (selectedPatient) {
                selectedPatient = normalizePatient(selectedPatient);
                renderPatient(selectedPatient);

                /*
                 * Для нового направления можно безопасно подтянуть свежую карточку.
                 * При редактировании сохранённого направления не перетираем исторический снимок пациента.
                 */
                if (!isEditing && selectedPatient.id) {
                    loadPatientDetails(selectedPatient.id)
                        .then((detailPayload) => {
                            if (!detailPayload || !selectedPatient?.id) return;
                            selectedPatient = mergePatient(selectedPatient, detailPayload);
                            applyPatientData(selectedPatient, false);
                            renderPatient(selectedPatient);
                        })
                        .catch((error) => console.warn('[MSE] Не удалось дополнить начальные данные пациента', error));
                }
            } else {
                renderPatient(null);
            }

            renderAgeDocumentationRequirements();
            renderMseCriteria();
            syncConsentUpload();
            syncObservationEligibility();
            syncPreviousIpraVisibility();
            syncStatus();
            syncCitizenship();
            syncEln();
            updateReadiness();
        })();
    </script>
@endsection
