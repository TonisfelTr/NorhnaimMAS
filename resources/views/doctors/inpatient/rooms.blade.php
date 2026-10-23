@extends('layouts.doctor')

@section('title', 'Палаты и койки стационара')

@section('assets')
    @parent

    <style>
        :root {
            --ward-primary: #078f9f;
            --ward-primary-dark: #05727e;
            --ward-primary-soft: #e8f8fa;
            --ward-bg: #f4f7fb;
            --ward-card: #fff;
            --ward-border: #dfe7ed;
            --ward-text: #1f2b3d;
            --ward-muted: #758295;
            --ward-danger: #d84f65;
        }

        .ward-topbar {
            min-height: 68px;
            padding: 0;
            background: linear-gradient(90deg, var(--ward-primary), #10aaba);
            box-shadow: 0 10px 28px rgba(7, 143, 159, .18);
        }

        .ward-topbar__inner {
            min-height: 68px;
            padding: 10px 24px;
            gap: 18px;
        }

        .ward-brand,
        .ward-nav-link {
            color: #fff !important;
            text-decoration: none;
        }

        .ward-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 17px;
            font-weight: 850;
            white-space: nowrap;
        }

        .ward-brand__icon {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 13px;
            background: rgba(255, 255, 255, .18);
        }

        .ward-nav {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ward-nav-link {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            gap: 7px;
            padding: 9px 13px;
            border-radius: 12px;
            color: rgba(255, 255, 255, .9) !important;
            font-size: 13px;
            font-weight: 750;
            transition: .16s ease;
            white-space: nowrap;
        }

        .ward-nav-link:hover,
        .ward-nav-link.active {
            background: rgba(255, 255, 255, .18);
            color: #fff !important;
        }

        .ward-context {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-left: auto;
            padding: 7px 11px;
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
        }

        .ward-page {
            min-height: calc(100vh - 68px);
            padding: 24px 28px 44px;
            background: var(--ward-bg);
        }

        .ward-container {
            width: min(1600px, 100%);
            margin: 0 auto;
        }

        .ward-page-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 16px;
        }

        .ward-kicker {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 8px;
            padding: 5px 10px;
            border-radius: 999px;
            background: var(--ward-primary-soft);
            color: var(--ward-primary-dark);
            font-size: 11px;
            font-weight: 850;
        }

        .ward-title {
            margin: 0;
            color: var(--ward-text);
            font-size: clamp(27px, 3vw, 35px);
            font-weight: 880;
            letter-spacing: -.035em;
        }

        .ward-description {
            max-width: 880px;
            margin: 7px 0 0;
            color: var(--ward-muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .ward-settings-link {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            gap: 7px;
            padding: 9px 13px;
            border: 1px solid #d4e0e6;
            border-radius: 12px;
            background: #fff;
            color: #405064;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        .ward-settings-link:hover {
            border-color: #afd5da;
            color: var(--ward-primary-dark);
        }

        .ward-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .ward-stat {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 82px;
            padding: 14px 16px;
            border: 1px solid var(--ward-border);
            border-radius: 17px;
            background: #fff;
            box-shadow: 0 9px 24px rgba(15, 23, 42, .035);
        }

        .ward-stat__icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 13px;
            background: var(--ward-primary-soft);
            color: var(--ward-primary-dark);
            font-size: 18px;
        }

        .ward-stat__value {
            color: var(--ward-text);
            font-size: 22px;
            font-weight: 900;
            line-height: 1;
        }

        .ward-stat__label {
            margin-top: 4px;
            color: var(--ward-muted);
            font-size: 10px;
            font-weight: 750;
        }

        .ward-layout {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 16px;
            align-items: start;
        }

        .ward-sidebar,
        .ward-workspace {
            border: 1px solid var(--ward-border);
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 13px 36px rgba(15, 23, 42, .045);
        }

        .ward-sidebar {
            padding: 14px;
        }

        .ward-sidebar__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
            color: var(--ward-text);
            font-size: 13px;
            font-weight: 850;
        }

        .ward-count {
            display: inline-flex;
            min-width: 24px;
            height: 24px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: var(--ward-primary-soft);
            color: var(--ward-primary-dark);
            font-size: 10px;
            font-weight: 900;
        }

        .ward-label {
            display: block;
            margin-bottom: 6px;
            color: #516072;
            font-size: 10px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .ward-control {
            width: 100%;
            min-height: 40px;
            padding: 8px 10px;
            border: 1px solid #d8e1e7;
            border-radius: 11px;
            background: #fff;
            color: #28364a;
            font-size: 12px;
        }

        .ward-room-list {
            display: grid;
            gap: 7px;
            margin-top: 12px;
        }

        .ward-room {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr) auto;
            gap: 9px;
            align-items: center;
            padding: 10px;
            border: 1px solid #e1e8ed;
            border-radius: 13px;
            background: #fff;
            color: #28364a;
            text-decoration: none;
            transition: .15s ease;
        }

        .ward-room:hover,
        .ward-room.active {
            border-color: #70c9d2;
            background: #f0fbfc;
            color: #174e57;
        }

        .ward-room__icon {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border-radius: 10px;
            background: #eef7f9;
            color: var(--ward-primary-dark);
        }

        .ward-room strong,
        .ward-room small {
            display: block;
        }

        .ward-room strong {
            font-size: 11px;
            font-weight: 850;
        }

        .ward-room small {
            margin-top: 2px;
            color: #8190a0;
            font-size: 8px;
            line-height: 1.35;
        }

        .ward-room__beds {
            padding: 4px 7px;
            border-radius: 999px;
            background: #eef2f6;
            color: #637083;
            font-size: 8px;
            font-weight: 900;
        }

        .ward-pager {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 11px;
            padding-top: 10px;
            border-top: 1px solid #edf1f4;
        }

        .ward-pager__info {
            color: #7c8998;
            font-size: 9px;
            font-weight: 750;
        }

        .ward-pager__buttons {
            display: flex;
            gap: 5px;
        }

        .ward-pager__button {
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border: 1px solid #dce5ea;
            border-radius: 9px;
            background: #fff;
            color: #526274;
            text-decoration: none;
        }

        .ward-pager__button.disabled {
            opacity: .35;
            pointer-events: none;
        }

        .ward-workspace {
            min-width: 0;
            padding: 16px;
        }

        .ward-room-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
        }

        .ward-room-head__eyebrow {
            color: #7e8b9a;
            font-size: 9px;
            font-weight: 800;
        }

        .ward-room-head h2 {
            margin: 3px 0 0;
            color: var(--ward-text);
            font-size: 21px;
            font-weight: 900;
        }

        .ward-room-head__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 12px;
            margin-top: 5px;
            color: #778596;
            font-size: 9px;
        }

        .ward-room-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .ward-room-badge {
            display: inline-flex;
            min-height: 30px;
            align-items: center;
            padding: 5px 9px;
            border-radius: 10px;
            background: #f3f6f8;
            color: #5f6e80;
            font-size: 9px;
            font-weight: 850;
        }

        .ward-map-wrap {
            display: flex;
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
            padding: 32px;
            align-items: flex-start;
            justify-content: center;
            border: 1px solid #e3eaef;
            border-radius: 17px;
            background: #f8fafb;
        }

        .ward-map {
            position: relative;
            box-sizing: border-box;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            overflow: visible;
            border: 3px solid #5c6878;
            border-radius: 8px;
            background: #fff;
            user-select: none;
        }

        .ward-map__grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(#edf1f4 1px, transparent 1px),
                linear-gradient(90deg, #edf1f4 1px, transparent 1px);
            background-size: 24px 24px;
            opacity: .65;
            pointer-events: none;
        }

        .ward-object {
            position: absolute;
            z-index: 2;
            display: flex;
            box-sizing: border-box;
            align-items: center;
            justify-content: center;
            transform-origin: center;
        }

        .ward-furniture {
            gap: 5px;
            border: 2px solid #8795a6;
            border-radius: 6px;
            background: #eef2f5;
            color: #526174;
            font-size: 8px;
            font-weight: 850;
        }

        .ward-furniture.is-cabinet { background: #e7ebef; }
        .ward-furniture.is-bedside_table { border-radius: 8px; background: #f4f6f8; }
        .ward-furniture.is-toilet {
            overflow: visible;
            border: 0;
            background: transparent;
            color: #526174;
            box-shadow: none;
        }
        .ward-furniture.is-toilet > i { display: none; }
        .ward-furniture.is-toilet > span {
            position: absolute;
            bottom: 8%;
            left: 50%;
            z-index: 3;
            transform: translateX(-50%);
            padding: 1px 4px;
            border-radius: 999px;
            background: rgba(255,255,255,.9);
            color: #59697a;
            font-size: 7px;
            font-weight: 900;
            letter-spacing: .04em;
        }
        .ward-furniture.is-toilet::before {
            content: '';
            position: absolute;
            top: 4%;
            left: 18%;
            width: 64%;
            height: 27%;
            box-sizing: border-box;
            border: 2px solid #8795a6;
            border-radius: 6px;
            background: linear-gradient(180deg, #fff, #eef3f6);
            box-shadow: inset 0 -2px 0 rgba(82,97,116,.08), 0 2px 5px rgba(15,23,42,.08);
        }
        .ward-furniture.is-toilet::after {
            content: '';
            position: absolute;
            bottom: 6%;
            left: 23%;
            width: 54%;
            height: 64%;
            box-sizing: border-box;
            border: 2px solid #8795a6;
            border-radius: 46% 46% 52% 52% / 40% 40% 60% 60%;
            background: radial-gradient(ellipse at 50% 42%, #d9e5ea 0 22%, #f9fcfd 24% 56%, #e8eef2 58% 100%);
            box-shadow: inset 0 0 0 3px rgba(255,255,255,.72), 0 3px 8px rgba(15,23,42,.10);
        }
        .ward-furniture.is-column {
            z-index: 4;
            border-color: #c52f46;
            border-radius: 4px;
            background-color: rgba(220, 66, 89, .12);
            background-image: repeating-linear-gradient(135deg, rgba(180, 35, 59, .34) 0, rgba(180, 35, 59, .34) 2px, transparent 2px, transparent 8px);
            color: #a52138;
        }
        .ward-furniture.is-entrance { border-color: #1598a7; border-radius: 4px; background: #e8fafc; color: #076976; }
        .ward-furniture.is-window { border-color: #4a9fd2; border-radius: 3px; background: #eaf6ff; color: #246a93; }
        .ward-furniture.is-socket { border-color: #c58a2d; border-radius: 999px; background: #fff8e9; color: #9a650f; }
        .ward-furniture.is-socket span { display: none; }

        .ward-bed {
            flex-direction: column;
            border: 2px solid #1598a7;
            border-radius: 8px;
            background: #dff7fa;
            color: #075d68;
            font-family: inherit;
            text-align: center;
        }

        button.ward-bed {
            padding: 0;
            cursor: pointer;
        }

        button.ward-bed:hover {
            box-shadow: 0 0 0 4px rgba(216, 79, 101, .11), 0 8px 22px rgba(15, 23, 42, .13);
        }

        .ward-bed.is-occupied {
            border-color: var(--ward-danger);
            background: #fff0f2;
            color: #9d2c3d;
        }

        .ward-bed__pillow {
            position: absolute;
            top: 10%;
            left: 7%;
            width: 23%;
            height: 80%;
            border-radius: 5px;
            background: rgba(255, 255, 255, .78);
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .08);
        }

        .ward-bed__number {
            font-size: 8px;
            font-weight: 900;
        }

        .ward-bed__patient {
            max-width: 90%;
            margin-top: 2px;
            overflow: hidden;
            font-size: 7px;
            font-weight: 900;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ward-bed__state {
            margin-top: 2px;
            font-size: 6px;
            font-weight: 800;
            opacity: .72;
        }

        .ward-bed-tooltip {
            position: absolute;
            bottom: calc(100% + 10px);
            left: 50%;
            z-index: 30;
            display: none;
            min-width: 235px;
            padding: 10px 11px;
            transform: translateX(-50%) rotate(calc(-1 * var(--object-rotation, 0deg)));
            border: 1px solid #f0c9cf;
            border-radius: 11px;
            background: #fff;
            color: #293849;
            box-shadow: 0 14px 36px rgba(15, 23, 42, .18);
            text-align: left;
            pointer-events: none;
        }

        .ward-bed:hover .ward-bed-tooltip { display: block; }
        .ward-bed-tooltip strong,
        .ward-bed-tooltip span,
        .ward-bed-tooltip em { display: block; }
        .ward-bed-tooltip strong { font-size: 13px; }
        .ward-bed-tooltip span { margin-top: 3px; color: #748194; font-size: 10px; }
        .ward-bed-tooltip em { margin-top: 6px; color: #a13b4b; font-size: 10px; font-style: normal; font-weight: 800; }

        .ward-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 10px;
            color: #7d8998;
            font-size: 9px;
        }

        .ward-legend span { display: inline-flex; align-items: center; gap: 5px; }
        .ward-legend i { width: 11px; height: 11px; border-radius: 4px; border: 1px solid; }
        .ward-legend .free { border-color: #1598a7; background: #dff7fa; }
        .ward-legend .occupied { border-color: var(--ward-danger); background: #fff0f2; }
        .ward-legend .furniture { border-color: #8795a6; background: #eef2f5; }

        .ward-empty {
            display: grid;
            min-height: 480px;
            place-content: center;
            justify-items: center;
            color: #8b97a5;
            text-align: center;
        }

        .ward-empty i { font-size: 42px; color: #9dcfd5; }
        .ward-empty strong { margin-top: 10px; color: #455568; font-size: 14px; }
        .ward-empty span { margin-top: 5px; font-size: 10px; }

        .ward-release-button {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 14px;
            border: 1px solid #efc4ca;
            border-radius: 12px;
            background: #fff5f6;
            color: #b4233a;
            font-size: 12px;
            font-weight: 850;
            transition: .15s ease;
        }
        .ward-release-button:hover {
            border-color: #df8795;
            background: #ffecef;
            color: #991b32;
        }
        .ward-release-button:disabled { opacity: .55; cursor: wait; }

        .ward-patient-modal .modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 22px;
            box-shadow: 0 28px 80px rgba(15, 23, 42, .22);
        }

        .ward-patient-modal .modal-header {
            padding: 18px 20px;
            border-bottom: 1px solid #e7edf1;
            background: linear-gradient(180deg, #fff, #fbfcfd);
        }

        .ward-patient-modal .modal-body {
            padding: 20px;
            background: #f7f9fb;
        }

        .ward-patient-card {
            padding: 16px;
            border: 1px solid #e1e8ed;
            border-radius: 16px;
            background: #fff;
        }

        .ward-patient-card__name {
            color: var(--ward-text);
            font-size: 18px;
            font-weight: 900;
        }

        .ward-patient-card__birth {
            margin-top: 3px;
            color: var(--ward-muted);
            font-size: 11px;
        }

        .ward-patient-info {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 14px;
        }

        .ward-patient-info__item {
            padding: 12px;
            border: 1px solid #e8edf1;
            border-radius: 13px;
            background: #fbfcfd;
        }

        .ward-patient-info__label {
            color: #8793a2;
            font-size: 8px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: .035em;
        }

        .ward-patient-info__value {
            margin-top: 5px;
            color: #2f3e51;
            font-size: 12px;
            font-weight: 850;
        }

        .ward-modal-link {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 14px;
            border: 1px solid var(--ward-primary);
            border-radius: 11px;
            background: var(--ward-primary);
            color: #fff;
            font-size: 12px;
            font-weight: 850;
            text-decoration: none;
        }

        .ward-modal-link:hover { background: var(--ward-primary-dark); color: #fff; }



        .ward-filter-stack {
            display: grid;
            gap: 10px;
        }

        button.ward-bed:not(.is-occupied):hover {
            border-color: #078f9f;
            background: #ecfbfc;
            box-shadow: 0 0 0 4px rgba(7, 143, 159, .10), 0 8px 22px rgba(15, 23, 42, .10);
        }

        .ward-assign-modal .modal-content {
            overflow: visible;
            border: 0;
            border-radius: 22px;
            box-shadow: 0 28px 80px rgba(15, 23, 42, .22);
        }

        .ward-assign-modal .modal-header {
            padding: 18px 20px;
            border-bottom: 1px solid #e7edf1;
            background: linear-gradient(180deg, #fff, #fbfcfd);
        }

        .ward-assign-modal .modal-body {
            padding: 20px;
            background: #f7f9fb;
        }

        .ward-assign-card {
            padding: 16px;
            border: 1px solid #e1e8ed;
            border-radius: 16px;
            background: #fff;
        }

        .ward-assign-title {
            color: var(--ward-text);
            font-size: 13px;
            font-weight: 900;
        }

        .ward-assign-hint {
            margin-top: 3px;
            color: var(--ward-muted);
            font-size: 10px;
            line-height: 1.45;
        }

        .ward-patient-picker {
            position: relative;
            margin-top: 13px;
        }

        .ward-patient-picker__icon {
            position: absolute;
            top: 50%;
            left: 13px;
            z-index: 2;
            transform: translateY(-50%);
            color: #8b98a8;
            pointer-events: none;
        }

        .ward-patient-picker__input {
            width: 100%;
            min-height: 44px;
            padding: 9px 40px 9px 39px;
            border: 1px solid #cfdce4;
            border-radius: 12px;
            outline: 0;
            color: #27364a;
            font-size: 12px;
        }

        .ward-patient-picker__input:focus {
            border-color: var(--ward-primary);
            box-shadow: 0 0 0 3px rgba(7, 143, 159, .10);
        }

        .ward-patient-picker__clear {
            position: absolute;
            top: 50%;
            right: 8px;
            z-index: 3;
            display: grid;
            width: 29px;
            height: 29px;
            place-items: center;
            transform: translateY(-50%);
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #8794a4;
        }

        .ward-patient-picker__clear:hover {
            background: #eef5f7;
            color: #075f6b;
        }

        .ward-patient-results {
            position: absolute;
            z-index: 1095;
            top: calc(100% + 7px);
            right: 0;
            left: 0;
            max-height: 310px;
            overflow-y: auto;
            padding: 6px;
            border: 1px solid #dbe5eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 20px 54px rgba(15, 23, 42, .18);
        }

        .ward-patient-result {
            display: grid;
            width: 100%;
            grid-template-columns: 38px minmax(0, 1fr) 18px;
            gap: 10px;
            align-items: center;
            padding: 9px 10px;
            border: 0;
            border-radius: 11px;
            background: transparent;
            color: #27364a;
            text-align: left;
        }

        .ward-patient-result:hover {
            background: #eefafb;
        }

        .ward-patient-result__avatar {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 11px;
            background: #e7f8fa;
            color: var(--ward-primary-dark);
        }

        .ward-patient-result strong,
        .ward-patient-result small {
            display: block;
        }

        .ward-patient-result strong {
            font-size: 11px;
            font-weight: 850;
        }

        .ward-patient-result small {
            margin-top: 2px;
            color: #7c8999;
            font-size: 9px;
        }

        .ward-patient-results__state {
            padding: 18px 12px;
            color: #8793a2;
            font-size: 10px;
            text-align: center;
        }

        .ward-selected-patient {
            display: none;
            grid-template-columns: 40px minmax(0, 1fr) 30px;
            gap: 10px;
            align-items: center;
            margin-top: 12px;
            padding: 11px;
            border: 1px solid #cfe7ea;
            border-radius: 13px;
            background: #f1fafb;
        }

        .ward-selected-patient.is-visible {
            display: grid;
        }

        .ward-selected-patient__avatar,
        .ward-selected-patient__check {
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: #def4f6;
            color: var(--ward-primary-dark);
        }

        .ward-selected-patient__avatar {
            width: 40px;
            height: 40px;
        }

        .ward-selected-patient__check {
            width: 28px;
            height: 28px;
        }

        .ward-selected-patient small,
        .ward-selected-patient strong,
        .ward-selected-patient span {
            display: block;
        }

        .ward-selected-patient small {
            color: #768496;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .ward-selected-patient strong {
            margin-top: 2px;
            color: #27364a;
            font-size: 11px;
            font-weight: 900;
        }

        .ward-selected-patient span {
            margin-top: 2px;
            color: #7c8999;
            font-size: 9px;
        }

        .ward-assign-error {
            display: none;
            margin-bottom: 12px;
            padding: 10px 12px;
            border: 1px solid #f2c6cd;
            border-radius: 11px;
            background: #fff2f4;
            color: #a52d42;
            font-size: 10px;
            font-weight: 750;
        }

        .ward-assign-error.is-visible {
            display: block;
        }

        /* Независимое изменение ширины/высоты только при просмотре стационара. */
        .ward-view-size-toolbar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin: 0 0 14px;
            padding: 11px 12px;
            border: 1px solid #dce9ed;
            border-radius: 12px;
            background: #f5fafb;
        }

        .ward-view-size-toolbar__title {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 175px;
            color: #235563;
        }

        .ward-view-size-toolbar__icon {
            display: grid;
            width: 33px;
            height: 33px;
            flex: 0 0 33px;
            place-items: center;
            border-radius: 9px;
            background: #e2f5f7;
            color: #078c9b;
            font-size: 15px;
        }

        .ward-view-size-toolbar__title strong,
        .ward-view-size-toolbar__title small { display: block; }
        .ward-view-size-toolbar__title strong { font-size: 11px; font-weight: 850; }
        .ward-view-size-toolbar__title small { margin-top: 2px; color: #738596; font-size: 9px; line-height: 1.35; }

        .ward-view-size-field {
            display: flex;
            align-items: center;
            gap: 9px;
            flex: 1 1 180px;
            min-width: 160px;
            margin: 0;
        }
        .ward-view-size-field > span { flex: 0 0 45px; color: #526577; font-size: 10px; font-weight: 800; }
        .ward-view-size-field input[type="range"] {
            flex: 1 1 auto;
            width: 100%;
            min-width: 64px;
            height: 26px;
            margin: 0;
            accent-color: #078c9b;
            cursor: pointer;
        }
        .ward-view-size-field output {
            min-width: 42px;
            color: #255865;
            font-size: 11px;
            font-weight: 850;
            font-variant-numeric: tabular-nums;
            text-align: right;
        }
        .ward-view-size-toolbar__pixels {
            color: #718394;
            font-size: 10px;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .ward-view-size-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 34px;
            padding: 6px 11px;
            border: 1px solid #bbdfe3;
            border-radius: 9px;
            background: #fff;
            color: #087887;
            font-size: 10px;
            font-weight: 850;
            cursor: pointer;
        }
        .ward-view-size-reset:hover:not(:disabled) { background: #e4f7f9; border-color: #078c9b; }
        .ward-view-size-reset:disabled { opacity: .48; cursor: default; }

        /* Только карта прокручивается при увеличении; страница и меню не расширяются. */
        .ward-map-wrap.is-view-resizable {
            overflow: auto;
            justify-content: flex-start;
        }
        .ward-map-wrap.is-view-resizable .ward-map {
            max-width: none;
            flex: 0 0 auto;
            margin-right: auto;
            margin-left: auto;
        }
        /* Оставляем существующую текстуру 24 × 24 пикселя без изменений. */
        .ward-map.is-view-resized .ward-furniture,
        .ward-map.is-view-resized .ward-bed__number {
            font-size: calc(8px * var(--ward-view-label-scale, 1));
        }
        .ward-map.is-view-resized .ward-bed__patient,
        .ward-map.is-view-resized .ward-furniture.is-toilet > span {
            font-size: calc(7px * var(--ward-view-label-scale, 1));
        }
        .ward-map.is-view-resized .ward-bed__state {
            font-size: calc(6px * var(--ward-view-label-scale, 1));
        }

        @media (max-width: 700px) {
            .ward-view-size-toolbar { gap: 10px; }
            .ward-view-size-toolbar__title { width: 100%; }
            .ward-view-size-field { flex-basis: 100%; }
            .ward-view-size-reset { margin-left: auto; }
        }


        /* Прогресс загрузки палаты: оформление такое же, как в редакторе настроек. */
        .ward-page { position: relative; }
        .ward-page.ward-is-loading > .ward-container { visibility: hidden; }
        .ward-loading-layer {
            position: absolute;
            z-index: 25;
            inset: 0 0 auto;
            display: grid;
            min-height: calc(100vh - 68px);
            padding: 24px;
            place-items: center;
            background: var(--ward-bg);
            transition: opacity .18s ease;
        }
        .ward-loading-layer[hidden] { display: none !important; }
        .ward-loading-layer.is-hiding { opacity: 0; pointer-events: none; }
        .ward-loading-card {
            display: grid;
            width: min(520px, 100%);
            min-height: 145px;
            place-items: center;
            padding: 28px 40px;
            border: 1px solid var(--ward-border);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 12px 34px rgba(15, 23, 42, .04);
        }
        .ward-loading-content { width: 100%; text-align: center; }
        .ward-loading-title { color: #536276; font-size: 12px; font-weight: 750; }
        .ward-loading-track {
            height: 8px;
            margin-top: 14px;
            overflow: hidden;
            border-radius: 999px;
            background: #e7eef2;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, .06);
        }
        .ward-loading-bar {
            width: 10%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--ward-primary), #35b8c5);
            transition: width .18s ease;
        }
        .ward-loading-percent {
            margin-top: 7px;
            color: #8390a0;
            font-size: 10px;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
        }
        .ward-loading-retry {
            display: none;
            margin-top: 15px;
            padding: 7px 12px;
            border: 1px solid #de9ea9;
            border-radius: 9px;
            background: #fff5f6;
            color: #a52d42;
            font-size: 11px;
            font-weight: 800;
        }
        .ward-loading-layer.is-error .ward-loading-title { color: #a52d42; }
        .ward-loading-layer.is-error .ward-loading-retry { display: inline-flex; }
        @media (prefers-reduced-motion: reduce) {
            .ward-loading-layer, .ward-loading-bar { transition: none; }
        }

        @media (max-width: 1100px) {
            .ward-layout { grid-template-columns: 1fr; }
            .ward-sidebar { order: 2; }
            .ward-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 767px) {
            .ward-page { padding: 18px 12px 32px; }
            .ward-page-head { flex-direction: column; }
            .ward-stats { grid-template-columns: 1fr; }
            .ward-map-wrap { padding: 22px; }
            .ward-patient-info { grid-template-columns: 1fr; }
            .ward-topbar__inner { align-items: stretch !important; flex-direction: column; }
            .ward-nav { flex-wrap: wrap; }
            .ward-context { width: fit-content; margin-left: 0; }
        }
    </style>
@endsection

@section('main')
    <nav class="ward-topbar">
        <div class="container-fluid ward-topbar__inner d-flex align-items-center">
            <a class="ward-brand" href="{{ route('doctors.inpatient.discharges.index') }}">
                <span class="ward-brand__icon"><i class="bi bi-hospital"></i></span>
                <span>Стационар</span>
            </a>

            <div class="ward-nav">
                <a class="ward-nav-link" href="{{ route('doctors.inpatient.discharges.index') }}">
                    <i class="bi bi-file-earmark-medical"></i>
                    Выписки
                </a>

                <a class="ward-nav-link active" href="{{ route('doctors.inpatient.rooms.index') }}">
                    <i class="bi bi-door-open"></i>
                    Палаты и койки
                </a>

                <a class="ward-nav-link" href="{{ route('doctors.prescriptions.index') }}">
                    <i class="bi bi-capsule-pill"></i>
                    Лекарственные назначения
                </a>
            </div>

            <div class="ward-context">
                <i class="bi bi-building"></i>
                {{ $clinic->name }}
            </div>
        </div>
    </nav>

    <main class="ward-page ward-is-loading" id="wardPage" aria-busy="true">
        <!-- Прогресс рендеринга только страницы стационара, не редактора схем. -->
        <div class="ward-loading-layer" id="wardLoadingLayer" role="status" aria-live="polite" aria-atomic="true">
            <div class="ward-loading-card">
                <div class="ward-loading-content">
                    <div class="ward-loading-title" id="wardLoadingTitle">Подготавливаем карту палат…</div>
                    <div class="ward-loading-track" id="wardLoadingTrack" role="progressbar"
                         aria-label="Загрузка палат" aria-valuemin="0" aria-valuemax="100" aria-valuenow="10">
                        <div class="ward-loading-bar" id="wardLoadingBar"></div>
                    </div>
                    <div class="ward-loading-percent" id="wardLoadingPercent">10%</div>
                    <button class="ward-loading-retry" type="button" id="wardLoadingRetry" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Повторить
                    </button>
                </div>
            </div>
        </div>
        <noscript>
            <style>
                #wardLoadingLayer { display: none !important; }
                #wardPage > .ward-container { visibility: visible !important; }
            </style>
        </noscript>
        <script>
            /* Этапы отображения, а не скачанные проценты от ответа сервера. */
            window.wardRoomProgress = (function () {
                const page = document.getElementById('wardPage');
                const layer = document.getElementById('wardLoadingLayer');
                const title = document.getElementById('wardLoadingTitle');
                const track = document.getElementById('wardLoadingTrack');
                const bar = document.getElementById('wardLoadingBar');
                const percent = document.getElementById('wardLoadingPercent');
                let value = 10;
                let startedAt = performance.now();
                let finished = false;
                let progressTimer = null;
                let timeoutTimer = null;
                let hideTimer = null;
                let fadeTimer = null;

                function update(next, message) {
                    if (finished) return;
                    value = Math.max(value, Math.min(100, Math.round(Number(next) || 0)));
                    bar.style.width = value + '%';
                    percent.textContent = value + '%';
                    track.setAttribute('aria-valuenow', String(value));
                    if (message) title.textContent = message;
                }
                function clearTimers() {
                    window.clearInterval(progressTimer);
                    window.clearTimeout(timeoutTimer);
                    window.clearTimeout(hideTimer);
                    window.clearTimeout(fadeTimer);
                    progressTimer = null;
                    timeoutTimer = null;
                    hideTimer = null;
                    fadeTimer = null;
                }
                function fail(error) {
                    if (finished) return;
                    clearTimers();
                    layer.classList.add('is-error');
                    title.textContent = 'Не удалось подготовить схему палат. Повторите попытку.';
                    if (error) console.error('[ward] Ошибка инициализации карты палат:', error);
                }
                function start(message) {
                    clearTimers();
                    finished = false;
                    value = 0;
                    startedAt = performance.now();
                    layer.hidden = false;
                    layer.classList.remove('is-hiding', 'is-error');
                    page.classList.add('ward-is-loading');
                    page.setAttribute('aria-busy', 'true');
                    update(10, message || 'Подготавливаем карту палат…');
                    progressTimer = window.setInterval(() => {
                        if (value < 68) update(Math.min(68, value + 3));
                    }, 180);
                    timeoutTimer = window.setTimeout(() => {
                        fail(new Error('Превышено время ожидания загрузки страницы.'));
                    }, 30000);
                }
                function dismiss() {
                    clearTimers();
                    finished = true;
                    layer.hidden = true;
                    layer.classList.remove('is-hiding', 'is-error');
                    page.classList.remove('ward-is-loading');
                    page.setAttribute('aria-busy', 'false');
                }
                function finish() {
                    if (finished) return;
                    update(100, 'Готово');
                    window.clearInterval(progressTimer);
                    window.clearTimeout(timeoutTimer);
                    const remaining = Math.max(0, 360 - (performance.now() - startedAt));
                    hideTimer = window.setTimeout(() => {
                        if (finished) return;
                        finished = true;
                        // Показываем готовую схему, а затем плавно убираем слой.
                        page.classList.remove('ward-is-loading');
                        page.setAttribute('aria-busy', 'false');
                        layer.classList.add('is-hiding');
                        fadeTimer = window.setTimeout(() => {
                            layer.hidden = true;
                            layer.classList.remove('is-hiding');
                        }, 210);
                    }, remaining);
                }
                function navigate(url, message) {
                    start(message || 'Загружаем выбранную палату…');
                    // Дать браузеру хотя бы один кадр, чтобы показать индикатор.
                    window.requestAnimationFrame(() => {
                        window.setTimeout(() => window.location.assign(url), 75);
                    });
                }
                function submitForm(form) {
                    if (!form) return;
                    start('Загружаем палаты выбранного отделения…');
                    window.requestAnimationFrame(() => {
                        window.setTimeout(() => form.submit(), 75);
                    });
                }
                start();
                window.addEventListener('pageshow', event => {
                    if (event.persisted) dismiss();
                });
                return { update, start, finish, fail, navigate, submitForm };
            })();
        </script>
        <div class="ward-container">
            <header class="ward-page-head">
                <div>
                    <div class="ward-kicker">
                        <i class="bi bi-grid-3x3-gap"></i>
                        Схема размещения стационара
                    </div>
                    <h1 class="ward-title">Палаты и койки</h1>
                    <p class="ward-description">
                        Схема берётся из настроек стационара. На занятых койках показаны пациенты.
                        Нажмите на пациента, чтобы увидеть начало госпитализации, срок нахождения и перейти в медицинскую карту.
                    </p>
                </div>

                <a class="ward-settings-link" href="{{ route('doctors.settings.rooms') }}">
                    <i class="bi bi-gear"></i>
                    Настройка схемы
                </a>
            </header>

            <section class="ward-stats">
                <article class="ward-stat">
                    <span class="ward-stat__icon"><i class="bi bi-door-open"></i></span>
                    <div><div class="ward-stat__value">{{ $roomsCount }}</div><div class="ward-stat__label">палат</div></div>
                </article>
                <article class="ward-stat">
                    <span class="ward-stat__icon"><i class="bi bi-hospital"></i></span>
                    <div><div class="ward-stat__value">{{ $bedsCount }}</div><div class="ward-stat__label">коек всего</div></div>
                </article>
                <article class="ward-stat">
                    <span class="ward-stat__icon"><i class="bi bi-check2-circle"></i></span>
                    <div><div class="ward-stat__value">{{ $freeBedsCount }}</div><div class="ward-stat__label">свободно</div></div>
                </article>
                <article class="ward-stat">
                    <span class="ward-stat__icon"><i class="bi bi-person-fill"></i></span>
                    <div><div class="ward-stat__value">{{ $occupiedBedsCount }}</div><div class="ward-stat__label">занято</div></div>
                </article>
            </section>

            <div class="ward-layout">
                <aside class="ward-sidebar">
                    <div class="ward-sidebar__head">
                        <span>Палаты</span>
                        <span class="ward-count">{{ $rooms->total() }}</span>
                    </div>

                    <form method="GET" action="{{ route('doctors.inpatient.rooms.index') }}" class="ward-filter-stack">
                        <div>
                            <label class="ward-label" for="wardBuilding">Корпус</label>
                            <select
                                id="wardBuilding"
                                class="ward-control"
                                name="building_id"
                                onchange="document.getElementById('wardDepartment').value = '0'; window.wardRoomProgress.submitForm(this.form)"
                            >
                                <option value="0">Все корпуса</option>
                                @foreach($buildings as $building)
                                    <option value="{{ $building->id }}" @selected((int) $buildingId === (int) $building->id)>
                                        Корпус@if($building->number) №{{ $building->number }}@endif @if($building->name) — {{ $building->name }}@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="ward-label" for="wardDepartment">Отделение</label>
                            <select id="wardDepartment" class="ward-control" name="department_id" onchange="window.wardRoomProgress.submitForm(this.form)">
                                <option value="0">Все отделения</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @selected((int) $departmentId === (int) $department->id)>
                                        @if($department->number)№{{ $department->number }} · @endif{{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    <div class="ward-room-list">
                        @forelse($rooms as $room)
                            <a
                                class="ward-room {{ $selectedRoom && (int) $selectedRoom['id'] === (int) $room->id ? 'active' : '' }}"
                                href="{{ route('doctors.inpatient.rooms.index', array_filter([
                                    'building_id' => $buildingId ?: null,
                                    'department_id' => $departmentId ?: null,
                                    'room_id' => $room->id,
                                    'rooms_page' => $rooms->currentPage(),
                                ])) }}"
                            >
                                <span class="ward-room__icon"><i class="bi bi-door-open"></i></span>
                                <span>
                                    <strong>Палата №{{ $room->number }}@if($room->name) — {{ $room->name }}@endif</strong>
                                    <small>{{ $room->department?->name ?? 'Отделение не указано' }}</small>
                                </span>
                                <span class="ward-room__beds">{{ $room->occupied_beds_count }}/{{ $room->beds_count }}</span>
                            </a>
                        @empty
                            <div class="ward-empty" style="min-height:180px">
                                <i class="bi bi-door-closed"></i>
                                <strong>Палат нет</strong>
                                <span>Для выбранного отделения палат пока нет.</span>
                            </div>
                        @endforelse
                    </div>

                    @if($rooms->lastPage() > 1)
                        <div class="ward-pager">
                            <span class="ward-pager__info">
                                Палаты {{ $rooms->firstItem() }}–{{ $rooms->lastItem() }} из {{ $rooms->total() }}
                            </span>
                            <span class="ward-pager__buttons">
                                <a class="ward-pager__button {{ $rooms->onFirstPage() ? 'disabled' : '' }}" href="{{ $rooms->previousPageUrl() ?: '#' }}"><i class="bi bi-chevron-left"></i></a>
                                <a class="ward-pager__button {{ !$rooms->hasMorePages() ? 'disabled' : '' }}" href="{{ $rooms->nextPageUrl() ?: '#' }}"><i class="bi bi-chevron-right"></i></a>
                            </span>
                        </div>
                    @endif
                </aside>

                <section class="ward-workspace">
                    @if($selectedRoom)
                        <header class="ward-room-head">
                            <div>
                                <div class="ward-room-head__eyebrow">{{ $selectedRoom['building_label'] }}</div>
                                <h2>
                                    Палата №{{ $selectedRoom['number'] }}
                                    @if($selectedRoom['name']) — {{ $selectedRoom['name'] }} @endif
                                </h2>
                                <div class="ward-room-head__meta">
                                    <span>{{ $selectedRoom['department_name'] }}</span>
                                    @if($selectedRoom['floor'])<span>этаж {{ $selectedRoom['floor'] }}</span>@endif
                                    <span>{{ $selectedRoom['stats']['free'] }} свободно</span>
                                    <span>{{ $selectedRoom['stats']['occupied'] }} занято</span>
                                </div>
                            </div>

                            <div class="ward-room-badges">
                                <span class="ward-room-badge">{{ $selectedRoom['stats']['beds'] }} коек</span>
                                <span class="ward-room-badge">{{ $selectedRoom['stats']['occupied'] }} пациентов</span>
                            </div>
                        </header>

                        <!-- WARD_VIEW_SIZE_CONTROLS_V2_ACTIVE: panel is rendered by rooms.blade.php -->
                        <!-- Размеры меняют только отображение в стационаре, исходный план не сохраняется. -->
                        <div class="ward-view-size-toolbar" data-ward-controls-version="v2" role="group" aria-label="Размер схемы палаты на экране">
                            <div class="ward-view-size-toolbar__title">
                                <span class="ward-view-size-toolbar__icon"><i class="bi bi-aspect-ratio"></i></span>
                                <div>
                                    <strong>Размер схемы</strong>
                                    <small>Только отображение, исходный план не меняется</small>
                                </div>
                            </div>

                            <label class="ward-view-size-field" for="wardViewWidth">
                                <span>Ширина</span>
                                <input id="wardViewWidth" type="range" min="65" max="135" step="5" value="100"
                                       aria-label="Ширина схемы на экране">
                                <output id="wardViewWidthOutput" for="wardViewWidth">100%</output>
                            </label>

                            <label class="ward-view-size-field" for="wardViewHeight">
                                <span>Высота</span>
                                <input id="wardViewHeight" type="range" min="65" max="135" step="5" value="100"
                                       aria-label="Высота схемы на экране">
                                <output id="wardViewHeightOutput" for="wardViewHeight">100%</output>
                            </label>

                            <span class="ward-view-size-toolbar__pixels" id="wardViewDimensions" aria-live="off">
                                {{ $selectedRoom['canvas_width'] }} × {{ $selectedRoom['canvas_height'] }} px
                            </span>
                            <button type="button" class="ward-view-size-reset" id="wardViewFit" title="Вписать схему в доступную область">
                                <i class="bi bi-arrows-fullscreen"></i> Вписать
                            </button>
                        </div>

                        <div class="ward-map-wrap" id="wardMapViewport">
                            <div
                                class="ward-map" id="wardRoomMap"
                                data-clinic-id="{{ $clinic->id }}"
                                data-room-id="{{ $selectedRoom['id'] }}"
                                data-canvas-width="{{ $selectedRoom['canvas_width'] }}"
                                data-canvas-height="{{ $selectedRoom['canvas_height'] }}"
                                style="aspect-ratio: {{ $selectedRoom['canvas_width'] }} / {{ $selectedRoom['canvas_height'] }}"
                            >
                                <div class="ward-map__grid"></div>

                                @foreach($selectedRoom['items'] as $item)
                                    <div class="ward-object ward-furniture is-{{ $item['type'] }}" style="{{ $item['style'] }}">
                                        @switch($item['type'])
                                            @case('cabinet') <i class="bi bi-archive"></i> @break
                                            @case('column') <i class="bi bi-square-fill"></i> @break
                                            @case('entrance') <i class="bi bi-door-open"></i> @break
                                            @case('window') <i class="bi bi-window"></i> @break
                                            @case('socket') <i class="bi bi-plug"></i> @break
                                            @case('toilet') <i class="bi bi-badge-wc"></i> @break
                                            @default <i class="bi bi-square"></i>
                                        @endswitch
                                        <span>{{ $item['label'] ?: match($item['type']) {
                                            'cabinet' => 'Шкаф',
                                            'column' => 'Колонна',
                                            'entrance' => 'Вход',
                                            'window' => 'Окно',
                                            'socket' => 'Розетка',
                                            'toilet' => 'Туалет',
                                            default => 'Тумбочка',
                                        } }}</span>
                                    </div>
                                @endforeach

                                @foreach($selectedRoom['beds'] as $bed)
                                    @if($bed['occupied'] && $bed['patient'])
                                        <button
                                            type="button"
                                            class="ward-object ward-bed is-occupied js-ward-patient"
                                            style="{{ $bed['style'] }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#wardPatientModal"
                                            data-patient-name="{{ $bed['patient']['name'] }}"
                                            data-patient-birth="{{ $bed['patient']['birth_at'] }}"
                                            data-medical-card-url="{{ $bed['patient']['medical_card_url'] }}"
                                            data-release-url="{{ route('doctors.inpatient.rooms.beds.release', ['bed' => $bed['id']]) }}"
                                            data-assigned-at="{{ $bed['assigned_at'] }}"
                                            data-stay-label="{{ $bed['stay_label'] }}"
                                            data-room="{{ $selectedRoom['number'] }}"
                                            data-bed="{{ $bed['number'] }}"
                                            data-department="{{ $selectedRoom['department_name'] }}"
                                        >
                                            <span class="ward-bed__pillow"></span>
                                            <span class="ward-bed__number">Койка {{ $bed['number'] }}</span>
                                            <span class="ward-bed__patient">{{ $bed['patient']['short_name'] }}</span>
                                            <span class="ward-bed__state">{{ $bed['stay_days'] }} дн.</span>

                                            <span class="ward-bed-tooltip">
                                                <strong>{{ $bed['patient']['name'] }}</strong>
                                                <span>Начало стационара: {{ $bed['assigned_at'] }}</span>
                                                <span>Находится: {{ $bed['stay_label'] }}</span>
                                                <em>Нажмите, чтобы открыть сведения и медкарту</em>
                                            </span>
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            class="ward-object ward-bed js-ward-assign"
                                            style="{{ $bed['style'] }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#wardAssignModal"
                                            data-bed-id="{{ $bed['id'] }}"
                                            data-bed-number="{{ $bed['number'] }}"
                                            data-room-number="{{ $selectedRoom['number'] }}"
                                            data-department-name="{{ $selectedRoom['department_name'] }}"
                                            data-age-group="{{ $selectedRoom['department_age_group'] ?? '' }}"
                                            data-age-group-label="{{ $selectedRoom['department_age_group_label'] ?? '' }}"
                                            data-assign-url="{{ route('doctors.inpatient.rooms.beds.assign', ['bed' => $bed['id']]) }}"
                                        >
                                            <span class="ward-bed__pillow"></span>
                                            <span class="ward-bed__number">Койка {{ $bed['number'] }}</span>
                                            <span class="ward-bed__state">Свободна · нажмите для размещения</span>
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="ward-legend">
                            <span><i class="free"></i>Свободная койка</span>
                            <span><i class="occupied"></i>Занятая койка</span>
                            <span><i class="furniture"></i>Мебель и элементы палаты</span>
                        </div>
                    @else
                        <div class="ward-empty">
                            <i class="bi bi-grid"></i>
                            <strong>Палата не выбрана</strong>
                            <span>Создайте палаты в настройках или выберите отделение, в котором они уже есть.</span>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </main>

    <div class="modal fade ward-patient-modal" id="wardPatientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div class="ward-kicker mb-1"><i class="bi bi-person-heart"></i> Пациент на койке</div>
                        <h5 class="modal-title" id="wardPatientModalTitle">Пациент</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="ward-patient-card">
                        <div class="ward-patient-card__name" id="wardPatientName">—</div>
                        <div class="ward-patient-card__birth" id="wardPatientBirth">—</div>

                        <div class="ward-patient-info">
                            <div class="ward-patient-info__item">
                                <div class="ward-patient-info__label">Начало стационара</div>
                                <div class="ward-patient-info__value" id="wardPatientAssigned">—</div>
                            </div>
                            <div class="ward-patient-info__item">
                                <div class="ward-patient-info__label">Срок нахождения</div>
                                <div class="ward-patient-info__value" id="wardPatientStay">—</div>
                            </div>
                            <div class="ward-patient-info__item">
                                <div class="ward-patient-info__label">Размещение</div>
                                <div class="ward-patient-info__value" id="wardPatientLocation">—</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ward-release-button me-auto" id="wardPatientRelease">
                        <i class="bi bi-person-dash"></i>
                        Освободить койку
                    </button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Закрыть</button>
                    <a class="ward-modal-link" id="wardPatientCardLink" href="#">
                        <i class="bi bi-journal-medical"></i>
                        Открыть медицинскую карту
                    </a>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade ward-assign-modal" id="wardAssignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div class="ward-kicker mb-1"><i class="bi bi-person-plus"></i> Размещение пациента</div>
                        <h5 class="modal-title" id="wardAssignModalTitle">Назначить пациента на койку</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <div class="ward-assign-error" id="wardAssignError"></div>

                    <div class="ward-assign-card">
                        <div class="ward-assign-title">Выберите пациента</div>
                        <div class="ward-assign-hint" id="wardAssignHint">
                            Начните вводить фамилию, имя или отчество. Пациенты, уже размещённые на койках, не показываются.
                        </div>

                        <div class="ward-patient-picker">
                            <i class="bi bi-search ward-patient-picker__icon"></i>
                            <input
                                type="search"
                                class="ward-patient-picker__input"
                                id="wardAssignSearch"
                                placeholder="Введите ФИО пациента"
                                autocomplete="off"
                            >
                            <button type="button" class="ward-patient-picker__clear" id="wardAssignClear" title="Очистить">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <div class="ward-patient-results d-none" id="wardAssignResults"></div>
                        </div>

                        <div class="ward-selected-patient" id="wardSelectedPatient">
                            <div class="ward-selected-patient__avatar"><i class="bi bi-person-check"></i></div>
                            <div>
                                <small>Выбран пациент</small>
                                <strong id="wardSelectedPatientName">—</strong>
                                <span id="wardSelectedPatientBirth">—</span>
                            </div>
                            <div class="ward-selected-patient__check"><i class="bi bi-check2"></i></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <span class="me-auto text-muted small" id="wardAssignLocation">—</span>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="ward-modal-link border-0" id="wardAssignSubmit" disabled>
                        <i class="bi bi-check2-circle"></i>
                        Разместить
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('click', function (event) {
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.altKey || event.shiftKey) return;
            const link = event.target.closest('a.ward-room, a.ward-pager__button');
            if (!link || link.classList.contains('disabled') || link.target === '_blank') return;
            const url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin || url.href === window.location.href || url.hash && url.pathname === window.location.pathname && url.search === window.location.search) return;
            event.preventDefault();
            window.wardRoomProgress?.navigate(url.href, 'Загружаем схему выбранной палаты…');
        });

        document.addEventListener('DOMContentLoaded', function () {
            try {
                window.wardRoomProgress?.update(75, 'Формируем схему палаты…');
            /*
             * Просмотр схемы стационара: W/H независимы, но геометрия каждого
             * предмета меняется одним коэффициентом. Исходные left/top/width/
             * height серверной схемы никогда не отправляются обратно в БД.
             */
            (function initWardViewSize() {
                const viewport = document.getElementById('wardMapViewport');
                const map = document.getElementById('wardRoomMap');
                const widthInput = document.getElementById('wardViewWidth');
                const heightInput = document.getElementById('wardViewHeight');
                const widthOutput = document.getElementById('wardViewWidthOutput');
                const heightOutput = document.getElementById('wardViewHeightOutput');
                const dimensionsOutput = document.getElementById('wardViewDimensions');
                const fitButton = document.getElementById('wardViewFit');
                if (!viewport || !map || !widthInput || !heightInput) return;

                const sourceWidth = Math.max(1, Number(map.dataset.canvasWidth) || 900);
                const sourceHeight = Math.max(1, Number(map.dataset.canvasHeight) || 560);
                const storageKey = 'inpatient-ward-canvas-view:v1:' +
                    String(map.dataset.clinicId || 'clinic') + ':' +
                    String(map.dataset.roomId || 'room');
                const clampScale = value => {
                    const numeric = Number(value);
                    if (!Number.isFinite(numeric)) return 100;
                    return Math.max(65, Math.min(135, Math.round(numeric / 5) * 5));
                };
                const objects = Array.from(map.querySelectorAll('.ward-object')).map(element => ({
                    element,
                    sourceStyle: element.getAttribute('style') || '',
                    base: null,
                }));

                let widthPercent = 100;
                let heightPercent = 100;
                let fittedWidth = sourceWidth;
                let fittedHeight = sourceHeight;
                let resizeObserver = null;
                let resizeFrame = 0;

                try {
                    const saved = JSON.parse(window.localStorage.getItem(storageKey) || 'null');
                    if (saved && typeof saved === 'object' && !Array.isArray(saved)) {
                        widthPercent = clampScale(saved.width);
                        heightPercent = clampScale(saved.height);
                    }
                } catch (_) {
                    // Ограничения браузера на localStorage не мешают менять схему.
                }

                function persistSize() {
                    try {
                        window.localStorage.setItem(storageKey, JSON.stringify({
                            width: widthPercent,
                            height: heightPercent,
                        }));
                    } catch (_) {}
                }

                function renderSize() {
                    const ratioX = widthPercent / 100;
                    const ratioY = heightPercent / 100;
                    const elementRatio = Math.min(ratioX, ratioY);
                    const targetWidth = Math.max(1, Math.round(fittedWidth * ratioX));
                    const targetHeight = Math.max(1, Math.round(fittedHeight * ratioY));

                    map.style.width = targetWidth + 'px';
                    map.style.height = targetHeight + 'px';
                    map.style.aspectRatio = 'auto';
                    map.style.maxWidth = 'none';
                    map.style.flex = '0 0 auto';

                    // Для позиций применяются два коэффициента (X/Y), для
                    // размеров предметов один (min). Кровати не растягиваются.
                    objects.forEach(record => {
                        if (!record.base) return;
                        const { left, top, width, height } = record.base;
                        const element = record.element;
                        element.style.left = (left * ratioX) + 'px';
                        element.style.top = (top * ratioY) + 'px';
                        element.style.width = (width * elementRatio) + 'px';
                        element.style.height = (height * elementRatio) + 'px';
                    });

                    map.style.setProperty('--ward-view-label-scale',
                        String(Math.max(0.85, Math.min(1.15, elementRatio))));
                    map.classList.add('is-view-resized');
                    viewport.classList.add('is-view-resizable');

                    widthInput.value = String(widthPercent);
                    heightInput.value = String(heightPercent);
                    if (widthOutput) widthOutput.textContent = widthPercent + '%';
                    if (heightOutput) heightOutput.textContent = heightPercent + '%';
                    if (dimensionsOutput) {
                        dimensionsOutput.textContent = targetWidth + ' × ' + targetHeight + ' px';
                        dimensionsOutput.title = 'Фактический размер отображения. ' +
                            'Исходный план: ' + sourceWidth + ' × ' + sourceHeight + ' px';
                    }
                    if (fitButton) fitButton.disabled = widthPercent === 100 && heightPercent === 100;
                }

                function calculateFittedCanvas() {
                    const viewportStyle = window.getComputedStyle(viewport);
                    const paddingX = parseFloat(viewportStyle.paddingLeft) +
                        parseFloat(viewportStyle.paddingRight);
                    const paddingY = parseFloat(viewportStyle.paddingTop) +
                        parseFloat(viewportStyle.paddingBottom);
                    const borderX = parseFloat(viewportStyle.borderLeftWidth) +
                        parseFloat(viewportStyle.borderRightWidth);
                    const borderY = parseFloat(viewportStyle.borderTopWidth) +
                        parseFloat(viewportStyle.borderBottomWidth);
                    // Внешняя ширина не зависит от появившейся полосы прокрутки.
                    const availableWidth = Math.max(120,
                        viewport.getBoundingClientRect().width - paddingX - borderX);

                    // Высота страницы учитывается, чтобы высокая палата
                    // не растягивалась на несколько экранов по вертикали.
                    const documentTop = viewport.getBoundingClientRect().top + window.scrollY;
                    const fitHeightLimit = Math.max(340, Math.min(760,
                        window.innerHeight - documentTop - 24));
                    const scale = Math.min(1, availableWidth / sourceWidth,
                        fitHeightLimit / sourceHeight);
                    fittedWidth = Math.max(1, Math.round(sourceWidth * scale));
                    fittedHeight = Math.max(1, Math.round(sourceHeight * scale));

                    // Ограничение применяется к видимой области прокрутки,
                    // а не к размерам самой схемы.
                    viewport.style.maxHeight = (fitHeightLimit + paddingY + borderY + 5) + 'px';
                    map.style.width = fittedWidth + 'px';
                    map.style.height = fittedHeight + 'px';
                    map.style.maxWidth = 'none';
                    map.style.aspectRatio = 'auto';

                    objects.forEach(record => {
                        // Возвращаем сохранённый сервером стиль перед новым
                        // замером; не накапливаем ошибки при resize окна.
                        record.element.setAttribute('style', record.sourceStyle);
                        const style = window.getComputedStyle(record.element);
                        record.base = {
                            left: parseFloat(style.left) || 0,
                            top: parseFloat(style.top) || 0,
                            width: parseFloat(style.width) || 0,
                            height: parseFloat(style.height) || 0,
                        };
                    });
                    renderSize();
                }

                widthInput.addEventListener('input', () => {
                    widthPercent = clampScale(widthInput.value);
                    renderSize();
                    persistSize();
                });
                heightInput.addEventListener('input', () => {
                    heightPercent = clampScale(heightInput.value);
                    renderSize();
                    persistSize();
                });
                fitButton?.addEventListener('click', () => {
                    widthPercent = 100;
                    heightPercent = 100;
                    calculateFittedCanvas();
                    viewport.scrollTop = 0;
                    viewport.scrollLeft = 0;
                    persistSize();
                });

                function scheduleRefit() {
                    if (resizeFrame) window.cancelAnimationFrame(resizeFrame);
                    resizeFrame = window.requestAnimationFrame(() => {
                        resizeFrame = 0;
                        calculateFittedCanvas();
                    });
                }
                window.addEventListener('resize', scheduleRefit, { passive: true });
                // Размеры соседней панели могут меняться без window.resize.
                if (typeof ResizeObserver !== 'undefined') {
                    resizeObserver = new ResizeObserver(entries => {
                        if (entries.length && map.isConnected) scheduleRefit();
                    });
                    resizeObserver.observe(viewport.parentElement);
                }
                calculateFittedCanvas();
            })();

            const patientModal = document.getElementById('wardPatientModal');
            const patientReleaseButton = document.getElementById('wardPatientRelease');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let patientReleaseUrl = '';
            let patientReleaseName = '';

            patientModal?.addEventListener('show.bs.modal', function (event) {
                const trigger = event.relatedTarget;

                if (!trigger) {
                    return;
                }

                const name = trigger.dataset.patientName || 'Пациент';
                const birth = trigger.dataset.patientBirth || '';
                const assigned = trigger.dataset.assignedAt || '—';
                const stay = trigger.dataset.stayLabel || '—';
                const department = trigger.dataset.department || '—';
                const room = trigger.dataset.room || '—';
                const bed = trigger.dataset.bed || '—';
                const url = trigger.dataset.medicalCardUrl || '#';
                patientReleaseUrl = trigger.dataset.releaseUrl || '';
                patientReleaseName = name;
                if (patientReleaseButton) {
                    patientReleaseButton.disabled = !patientReleaseUrl;
                    patientReleaseButton.innerHTML = '<i class="bi bi-person-dash"></i> Освободить койку';
                }

                document.getElementById('wardPatientModalTitle').textContent = name;
                document.getElementById('wardPatientName').textContent = name;
                document.getElementById('wardPatientBirth').textContent = birth
                    ? 'Дата рождения: ' + birth
                    : 'Дата рождения не указана';
                document.getElementById('wardPatientAssigned').textContent = assigned;
                document.getElementById('wardPatientStay').textContent = stay;
                document.getElementById('wardPatientLocation').textContent =
                    department + ' · палата ' + room + ' · койка ' + bed;
                document.getElementById('wardPatientCardLink').href = url;
            });

            patientReleaseButton?.addEventListener('click', async function () {
                if (!patientReleaseUrl) {
                    return;
                }

                const confirmed = window.confirm(
                    'Освободить койку' + (patientReleaseName ? ' пациента «' + patientReleaseName + '»' : '') + '?'
                );

                if (!confirmed) {
                    return;
                }

                patientReleaseButton.disabled = true;
                patientReleaseButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Освобождаем…';

                try {
                    const response = await fetch(patientReleaseUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const firstValidationError = payload.errors
                            ? Object.values(payload.errors).flat()[0]
                            : null;
                        throw new Error(firstValidationError || payload.message || 'Не удалось освободить койку.');
                    }
                    window.wardRoomProgress?.start('Обновляем размещение пациентов…');
                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || 'Не удалось освободить койку.');
                    patientReleaseButton.disabled = false;
                    patientReleaseButton.innerHTML = '<i class="bi bi-person-dash"></i> Освободить койку';
                }
            });

            const assignModal = document.getElementById('wardAssignModal');
            const searchInput = document.getElementById('wardAssignSearch');
            const clearButton = document.getElementById('wardAssignClear');
            const results = document.getElementById('wardAssignResults');
            const selected = document.getElementById('wardSelectedPatient');
            const selectedName = document.getElementById('wardSelectedPatientName');
            const selectedBirth = document.getElementById('wardSelectedPatientBirth');
            const submit = document.getElementById('wardAssignSubmit');
            const errorBox = document.getElementById('wardAssignError');
            const hint = document.getElementById('wardAssignHint');
            const location = document.getElementById('wardAssignLocation');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const patientSearchUrl = @json(route('doctors.inpatient.rooms.patients.search'));

            let assignUrl = '';
            let currentBedId = 0;
            let ageGroupLabel = '';
            let selectedPatient = null;
            let searchTimer = null;
            let searchController = null;

            function resetPicker() {
                window.clearTimeout(searchTimer);
                searchController?.abort();
                searchController = null;
                selectedPatient = null;
                searchInput.value = '';
                results.innerHTML = '';
                results.classList.add('d-none');
                selected.classList.remove('is-visible');
                submit.disabled = true;
                errorBox.textContent = '';
                errorBox.classList.remove('is-visible');
            }

            function showError(message) {
                errorBox.textContent = message || 'Не удалось выполнить действие.';
                errorBox.classList.add('is-visible');
            }

            function updateAgeGroup(group, label) {
                const restricted = group === 'children' || group === 'adolescents';
                ageGroupLabel = restricted && typeof label === 'string' ? label.trim() : '';

                if (hint) {
                    hint.textContent = ageGroupLabel
                        ? ageGroupLabel + '. Показываются пациенты подходящего возраста. Уже размещённые пациенты исключаются из списка.'
                        : 'Начните вводить фамилию, имя или отчество. Пациенты, уже размещённые на койках, не показываются.';
                }
            }

            function choosePatient(patient) {
                searchController?.abort();
                searchController = null;
                selectedPatient = patient;
                searchInput.value = patient.name || '';
                results.classList.add('d-none');
                results.innerHTML = '';
                selectedName.textContent = patient.name || 'Пациент';
                selectedBirth.textContent = patient.birth_at
                    ? 'Дата рождения: ' + patient.birth_at
                    : 'Дата рождения не указана';
                selected.classList.add('is-visible');
                submit.disabled = false;
                errorBox.classList.remove('is-visible');
            }

            function renderResults(items) {
                results.innerHTML = '';
                results.classList.remove('d-none');

                if (!items.length) {
                    results.innerHTML = '<div class="ward-patient-results__state"><i class="bi bi-person-x me-1"></i><span></span></div>';
                    results.querySelector('span').textContent = ageGroupLabel
                        ? 'Подходящие пациенты не найдены. ' + ageGroupLabel + '.'
                        : 'Пациенты не найдены';
                    return;
                }

                items.forEach(function (patient) {
                    const row = document.createElement('button');
                    row.type = 'button';
                    row.className = 'ward-patient-result';
                    row.innerHTML =
                        '<span class="ward-patient-result__avatar"><i class="bi bi-person"></i></span>' +
                        '<span><strong></strong><small></small></span>' +
                        '<i class="bi bi-chevron-right text-muted"></i>';
                    row.querySelector('strong').textContent = patient.name || 'Пациент';
                    row.querySelector('small').textContent = patient.birth_at
                        ? 'Дата рождения: ' + patient.birth_at
                        : 'Дата рождения не указана';
                    row.addEventListener('click', function () {
                        choosePatient(patient);
                    });
                    results.appendChild(row);
                });
            }

            async function searchPatients(query) {
                searchController?.abort();
                const controller = new AbortController();
                searchController = controller;
                const bedId = currentBedId;
                const isCurrentRequest = () => searchController === controller
                    && !controller.signal.aborted
                    && currentBedId === bedId
                    && searchInput.value.trim() === query
                    && !selectedPatient;
                results.classList.remove('d-none');
                results.innerHTML = '<div class="ward-patient-results__state"><span class="spinner-border spinner-border-sm me-1"></span> Ищем пациента…</div>';

                try {
                    const url = new URL(patientSearchUrl, window.location.origin);
                    url.searchParams.set('q', query);
                    if (bedId > 0) {
                        url.searchParams.set('bed_id', String(bedId));
                    }
                    const response = await fetch(url.toString(), {
                        headers: { 'Accept': 'application/json' },
                        signal: controller.signal,
                    });
                    const payload = await response.json();
                    if (!isCurrentRequest()) {
                        return;
                    }
                    if (!response.ok) {
                        throw new Error(payload.message || 'Не удалось найти пациента.');
                    }
                    if (Object.prototype.hasOwnProperty.call(payload, 'age_group_label')) {
                        updateAgeGroup(payload.age_group, payload.age_group_label);
                    }
                    renderResults(Array.isArray(payload.items) ? payload.items : []);
                } catch (error) {
                    if (error.name !== 'AbortError' && isCurrentRequest()) {
                        showError(error.message || 'Не удалось найти пациента.');
                    }
                }
            }

            assignModal?.addEventListener('show.bs.modal', function (event) {
                const trigger = event.relatedTarget;
                resetPicker();
                assignUrl = trigger?.dataset.assignUrl || '';
                currentBedId = Number(trigger?.dataset.bedId || 0);
                const bedNumber = trigger?.dataset.bedNumber || '—';
                const roomNumber = trigger?.dataset.roomNumber || '—';
                const departmentName = trigger?.dataset.departmentName || '—';

                document.getElementById('wardAssignModalTitle').textContent = 'Назначить пациента на койку ' + bedNumber;
                location.textContent = departmentName + ' · палата ' + roomNumber + ' · койка ' + bedNumber;

                updateAgeGroup(trigger?.dataset.ageGroup, trigger?.dataset.ageGroupLabel);
            });

            assignModal?.addEventListener('shown.bs.modal', function () {
                searchInput?.focus();
            });

            searchInput?.addEventListener('input', function () {
                selectedPatient = null;
                selected.classList.remove('is-visible');
                submit.disabled = true;
                errorBox.classList.remove('is-visible');
                window.clearTimeout(searchTimer);
                searchController?.abort();
                searchController = null;
                const query = searchInput.value.trim();
                if (query.length < 2) {
                    results.classList.add('d-none');
                    results.innerHTML = '';
                    return;
                }
                searchTimer = window.setTimeout(function () {
                    searchPatients(query);
                }, 250);
            });

            clearButton?.addEventListener('click', function () {
                resetPicker();
                searchInput?.focus();
            });

            submit?.addEventListener('click', async function () {
                if (!assignUrl || !selectedPatient?.id) {
                    return;
                }

                submit.disabled = true;
                submit.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Размещаем…';
                errorBox.classList.remove('is-visible');

                try {
                    const response = await fetch(assignUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ patient_id: selectedPatient.id }),
                    });
                    const payload = await response.json();
                    if (!response.ok) {
                        const firstValidationError = payload.errors
                            ? Object.values(payload.errors).flat()[0]
                            : null;
                        throw new Error(firstValidationError || payload.message || 'Не удалось разместить пациента.');
                    }
                    window.wardRoomProgress?.start('Обновляем размещение пациентов…');
                    window.location.reload();
                } catch (error) {
                    showError(error.message || 'Не удалось разместить пациента.');
                    submit.disabled = false;
                    submit.innerHTML = '<i class="bi bi-check2-circle"></i> Разместить';
                }
            });

                window.wardRoomProgress?.update(92, 'Отображаем схему палаты…');
                window.requestAnimationFrame(() => {
                    window.requestAnimationFrame(() => window.wardRoomProgress?.finish());
                });
            } catch (error) {
                window.wardRoomProgress?.fail(error);
            }
        });
    </script>
@endsection
