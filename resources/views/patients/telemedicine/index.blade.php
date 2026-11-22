@extends('layouts.patient')

@section('title', 'Телемедицина')

@section('assets')
    <style>
        .ptm-page { max-width: 1280px; margin: 0 auto; padding: 28px 28px 48px; color: #183b36; }
        .ptm-header { display:flex; align-items:flex-start; justify-content:space-between; gap:20px; margin-bottom:22px; }
        .ptm-eyebrow { color:#168879; font-size:10px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; margin-bottom:6px; }
        .ptm-header h1 { color:#173d37; font-size:29px; font-weight:800; letter-spacing:-.035em; margin:0 0 6px; }
        .ptm-header p { color:#71817d; font-size:13px; line-height:1.6; margin:0; max-width:720px; }
        .ptm-help-link { display:inline-flex; align-items:center; gap:8px; flex:0 0 auto; padding:10px 14px; border:1px solid #dcece6; border-radius:12px; background:#eff8f4; color:#147f72; text-decoration:none; font-size:11px; font-weight:800; transition:background .15s ease,border-color .15s ease; }
        .ptm-help-link:hover { color:#116e63; background:#e5f4ee; border-color:#c7e5d9; }
        .ptm-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:13px; margin-bottom:22px; }
        .ptm-stat { display:flex; align-items:center; gap:14px; min-height:82px; padding:15px 17px; border:1px solid #e3ece7; border-radius:16px; background:#fff; box-shadow:0 7px 22px rgba(31,67,60,.035); }
        .ptm-stat.is-primary { border-color:#8bcfba; box-shadow:0 0 0 2px rgba(22,136,121,.07),0 7px 22px rgba(31,67,60,.035); }
        .ptm-stat__icon { width:48px; height:48px; display:flex; align-items:center; justify-content:center; flex:0 0 auto; border-radius:14px; background:#e8f6f1; color:#168879; font-size:21px; }
        .ptm-stat.is-neutral .ptm-stat__icon { background:#f0f5f3; color:#49736a; }
        .ptm-stat.is-amber .ptm-stat__icon { background:#fff5e3; color:#c58a17; }
        .ptm-stat strong { display:block; color:#173d37; font-size:24px; line-height:1.05; font-weight:800; }
        .ptm-stat span { display:block; color:#778680; font-size:11px; margin-top:5px; }
        .ptm-tabs { display:flex; align-items:center; gap:27px; border-bottom:1px solid #dfe9e4; margin-bottom:18px; }
        .ptm-tabs a { display:inline-flex; align-items:center; gap:8px; color:#71817d; text-decoration:none; font-size:13px; font-weight:800; padding:0 3px 12px; border-bottom:2px solid transparent; margin-bottom:-1px; }
        .ptm-tabs a:hover,.ptm-tabs a.is-active { color:#168879; }
        .ptm-tabs a.is-active { border-color:#168879; }
        .ptm-tabs__count { display:inline-flex; align-items:center; justify-content:center; min-width:20px; height:20px; padding:0 6px; border-radius:20px; background:#eef5f2; color:#6d817a; font-size:10px; font-weight:800; }
        .ptm-panel { background:#fff; border:1px solid #e4ece8; border-radius:18px; padding:20px; box-shadow:0 7px 24px rgba(31,67,60,.04); }
        .ptm-feature-grid { display:grid; grid-template-columns:minmax(0,1.65fr) minmax(280px,.9fr); gap:16px; margin-bottom:17px; }
        .ptm-panel-title { color:#193f39; font-size:17px; font-weight:800; margin:0 0 14px; letter-spacing:-.015em; }
        .ptm-next { background:linear-gradient(145deg,#f4fbf8 0%,#f9fcfa 100%); border:1px solid #e0f0e9; border-radius:16px; padding:18px; }
        .ptm-next__head { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; margin-bottom:15px; }
        .ptm-next__date { display:flex; align-items:center; gap:13px; min-width:0; }
        .ptm-next__date-icon { width:58px; height:58px; display:flex; align-items:center; justify-content:center; flex:0 0 auto; border-radius:16px; background:#ddf5ec; color:#13977f; font-size:25px; }
        .ptm-next__date strong { display:block; color:#163c36; font-size:19px; line-height:1.3; font-weight:800; }
        .ptm-next__date span { display:block; color:#71817c; font-size:12px; margin-top:3px; }
        .ptm-status { display:inline-flex; align-items:center; gap:6px; max-width:250px; border-radius:30px; padding:7px 11px; font-size:10px; line-height:1.35; font-weight:800; text-align:center; white-space:normal; }
        .ptm-status.is-success { color:#13785f; background:#e2f7ee; }
        .ptm-status.is-warning { color:#94651a; background:#fff4dd; }
        .ptm-status.is-danger { color:#a53d3d; background:#fff0ef; }
        .ptm-status.is-info { color:#176d87; background:#eaf5f9; }
        .ptm-status.is-muted { color:#687873; background:#eff3f1; }
        .ptm-doctor { display:flex; align-items:center; gap:12px; margin:4px 0 15px; }
        .ptm-doctor__photo { width:56px; height:56px; display:flex; align-items:center; justify-content:center; flex:0 0 auto; overflow:hidden; border-radius:50%; background:#e7f1ed; color:#628179; font-size:21px; }
        .ptm-doctor__photo img { width:100%; height:100%; object-fit:cover; }
        .ptm-doctor strong { display:block; color:#193f39; font-size:14px; font-weight:800; }
        .ptm-doctor span { display:block; color:#72827c; font-size:12px; margin-top:3px; }
        .ptm-meta { display:flex; flex-wrap:wrap; align-items:center; gap:9px 18px; color:#506d65; font-size:11px; margin:0 0 14px; }
        .ptm-meta span { display:inline-flex; align-items:center; gap:7px; }
        .ptm-meta i { color:#168879; font-size:13px; }
        .ptm-meta__divider { width:1px; height:18px; background:#d7e7e0; }
        .ptm-status-note { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:11px 13px; border:1px solid #dcefe7; border-radius:12px; background:#eaf8f2; color:#456b60; font-size:11px; line-height:1.5; }
        .ptm-status-note__text { display:flex; align-items:flex-start; gap:8px; }
        .ptm-status-note__text i { color:#168879; font-size:14px; margin-top:1px; }
        .ptm-equipment-toggle { display:inline-flex; align-items:center; gap:7px; flex:0 0 auto; padding:8px 10px; border:1px solid #cde9dd; border-radius:10px; background:#f5fcf8; color:#246d5f; font-size:10px; font-weight:800; }
        .ptm-equipment-toggle:hover { background:#e4f5ed; }
        .ptm-equipment { margin-top:10px; padding:13px; border:1px solid #dcebe4; border-radius:12px; background:#fff; }
        .ptm-equipment[hidden] { display:none !important; }
        .ptm-equipment__body { display:grid; grid-template-columns:minmax(0,1fr) 180px; gap:14px; align-items:center; }
        .ptm-equipment video { width:100%; max-height:130px; border-radius:10px; background:#172e2a; object-fit:cover; }
        .ptm-equipment__message { color:#61766f; font-size:11px; line-height:1.5; }
        .ptm-equipment__stop { margin-top:8px; border:0; padding:0; background:none; color:#168879; font-size:11px; font-weight:800; text-decoration:underline; }
        .ptm-next__actions { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:12px; }
        .ptm-provider { color:#82918b; font-size:10px; }
        .ptm-join { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:42px; padding:10px 17px; border:0; border-radius:10px; background:#15977f; color:#fff; font-size:12px; font-weight:800; text-decoration:none; box-shadow:0 4px 10px rgba(21,151,127,.12); }
        .ptm-join:hover { color:#fff; background:#107d6b; }
        .ptm-join.is-disabled { background:#b8d7cd; color:#f8fffc; cursor:not-allowed; box-shadow:none; }
        .ptm-countdown { color:#8a681e; font-size:11px; font-weight:800; text-align:right; }
        .ptm-next__footnote { color:#82918b; font-size:10px; text-align:center; margin-top:8px; }
        .ptm-prep { background:#fbfdfc; border:1px solid #e7efeb; border-radius:16px; padding:18px; }
        .ptm-prep h3 { color:#1b413a; font-size:15px; font-weight:800; margin:0 0 15px; }
        .ptm-prep-list { display:grid; gap:13px; margin:0; padding:0; list-style:none; }
        .ptm-prep-list li { display:flex; align-items:flex-start; gap:10px; color:#60736c; font-size:11px; line-height:1.5; }
        .ptm-prep-list i { display:flex; align-items:center; justify-content:center; width:30px; height:30px; flex:0 0 auto; border-radius:9px; background:#edf6f2; color:#3f7165; font-size:14px; }
        .ptm-docs-link { display:inline-flex; align-items:center; gap:7px; margin-top:15px; color:#168879; font-size:11px; font-weight:800; text-decoration:none; }
        .ptm-docs-link:hover { color:#116e63; text-decoration:underline; }
        .ptm-other { margin-top:17px; }
        .ptm-other__title { color:#244840; font-size:13px; font-weight:800; margin:0 0 9px; }
        .ptm-mini-row { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:11px 0; border-top:1px solid #e9f0ec; }
        .ptm-mini-row:first-of-type { border-top:0; }
        .ptm-mini-row__main { display:flex; align-items:center; gap:10px; min-width:0; }
        .ptm-mini-row__icon { width:34px; height:34px; display:flex; align-items:center; justify-content:center; flex:0 0 auto; border-radius:10px; background:#edf6f2; color:#397469; }
        .ptm-mini-row strong { display:block; color:#25473f; font-size:11px; font-weight:800; }
        .ptm-mini-row span { display:block; color:#87938e; font-size:10px; margin-top:3px; }
        .ptm-history-preview { margin-top:17px; }
        .ptm-section-head { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:8px; }
        .ptm-section-head h2 { color:#193f39; font-size:17px; font-weight:800; margin:0 0 4px; }
        .ptm-section-head p { color:#7a8984; font-size:11px; margin:0; }
        .ptm-view-all { display:inline-flex; align-items:center; gap:7px; padding:9px 12px; border-radius:10px; background:#edf7f3; color:#167b6d; text-decoration:none; font-size:10px; font-weight:800; white-space:nowrap; }
        .ptm-view-all:hover { color:#116e63; background:#e2f2eb; }
        .ptm-history-list { border-top:1px solid #e9f0ec; margin-top:13px; }
        .ptm-history-row { display:grid; grid-template-columns:minmax(210px,1fr) minmax(170px,.9fr) auto auto; align-items:center; gap:14px; padding:13px 4px; border-bottom:1px solid #edf2ef; }
        .ptm-history-row:last-child { border-bottom:0; }
        .ptm-history-date { display:flex; align-items:center; gap:10px; min-width:0; }
        .ptm-history-date__icon { width:36px; height:36px; display:flex; align-items:center; justify-content:center; flex:0 0 auto; border-radius:10px; background:#eff6f3; color:#416f64; }
        .ptm-history-date strong { display:block; color:#25473f; font-size:11px; font-weight:800; }
        .ptm-history-date span { display:block; color:#87938e; font-size:10px; margin-top:3px; }
        .ptm-history-doctor strong { display:block; color:#294a42; font-size:11px; font-weight:800; }
        .ptm-history-doctor span { display:block; color:#84918b; font-size:10px; margin-top:3px; }
        .ptm-result { min-width:0; }
        .ptm-history-row .ptm-result[open] { grid-column:1 / -1; }
        .ptm-result summary { display:inline-flex; align-items:center; gap:7px; padding:8px 10px; border-radius:9px; background:#edf7f3; color:#246d5f; cursor:pointer; font-size:10px; font-weight:800; list-style:none; white-space:nowrap; }
        .ptm-result summary::-webkit-details-marker { display:none; }
        .ptm-result[open] summary { background:#dff2e9; }
        .ptm-result__body { grid-column:1 / -1; margin-top:9px; padding:13px 15px; border:1px solid #e2eee8; border-radius:11px; background:#f8fbf9; color:#526b63; font-size:11px; line-height:1.65; white-space:normal; }
        .ptm-result__body h4 { color:#23473f; font-size:11px; font-weight:800; margin:0 0 4px; }
        .ptm-result__body p { margin:0 0 10px; white-space:pre-line; }
        .ptm-result__body p:last-child { margin-bottom:0; }
        .ptm-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:210px; padding:28px 20px; border:1px dashed #dce9e3; border-radius:15px; background:#fbfdfc; text-align:center; }
        .ptm-empty__icon { width:52px; height:52px; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; border-radius:15px; background:#edf6f2; color:#71958a; font-size:22px; }
        .ptm-empty h2 { color:#244840; font-size:15px; font-weight:800; margin:0 0 6px; }
        .ptm-empty p { max-width:480px; color:#7a8984; font-size:11px; line-height:1.6; margin:0; }
        .ptm-history-range { color:#84918b; font-size:10px; margin-top:12px; }
        .ptm-pagination { display:flex; justify-content:flex-end; margin-top:14px; }
        .ptm-pagination .pagination { margin:0; gap:4px; }
        .ptm-pagination .page-link { border-radius:8px !important; border-color:#e3ece7; color:#397469; font-size:11px; }
        .ptm-pagination .page-item.active .page-link { border-color:#168879; background:#168879; }
        .ptm-footer-note { display:flex; align-items:flex-start; gap:9px; margin-top:17px; padding:11px 13px; border:1px solid #e4eee9; border-radius:12px; background:#f6faf8; color:#7a8984; font-size:10px; line-height:1.6; }
        .ptm-footer-note i { color:#168879; font-size:13px; margin-top:1px; }
        .ptm-history-page .ptm-history-list { margin-top:14px; }
        .ptm-history-page .ptm-history-row { grid-template-columns:minmax(220px,1fr) minmax(180px,.9fr) auto auto; }
        @media (max-width: 1050px) {
            .ptm-feature-grid { grid-template-columns:minmax(0,1.3fr) minmax(260px,.9fr); }
            .ptm-history-row,.ptm-history-page .ptm-history-row { grid-template-columns:minmax(180px,1fr) minmax(150px,.8fr) auto; }
            .ptm-history-row .ptm-result { grid-column:2 / -1; }
        }
        @media (max-width: 767px) {
            .ptm-page { padding:18px 14px 34px; }
            .ptm-header { align-items:flex-start; }
            .ptm-header h1 { font-size:23px; }
            .ptm-header p { font-size:12px; }
            .ptm-help-link { padding:8px 10px; font-size:0; }
            .ptm-help-link i { font-size:14px; }
            .ptm-stats { grid-template-columns:1fr 1fr; gap:9px; }
            .ptm-stat { min-height:72px; padding:11px; gap:9px; }
            .ptm-stat:last-child { grid-column:1 / -1; }
            .ptm-stat__icon { width:38px; height:38px; font-size:17px; }
            .ptm-stat strong { font-size:20px; }
            .ptm-stat span { font-size:10px; }
            .ptm-tabs { gap:18px; }
            .ptm-tabs a { font-size:11px; }
            .ptm-panel { padding:14px; }
            .ptm-feature-grid { grid-template-columns:1fr; }
            .ptm-next { padding:13px; }
            .ptm-next__head { display:block; }
            .ptm-next__head .ptm-status { margin-top:10px; }
            .ptm-next__date-icon { width:46px; height:46px; font-size:20px; }
            .ptm-next__date strong { font-size:15px; }
            .ptm-doctor__photo { width:46px; height:46px; }
            .ptm-status-note { align-items:flex-start; flex-direction:column; }
            .ptm-equipment__body { grid-template-columns:1fr; }
            .ptm-equipment video { max-height:180px; }
            .ptm-next__actions { align-items:stretch; flex-direction:column; }
            .ptm-join { width:100%; }
            .ptm-countdown { text-align:left; }
            .ptm-section-head { align-items:flex-start; }
            .ptm-section-head h2 { font-size:15px; }
            .ptm-view-all { font-size:0; padding:9px; }
            .ptm-view-all i { font-size:14px; }
            .ptm-history-row,.ptm-history-page .ptm-history-row { grid-template-columns:1fr; gap:8px; padding:13px 0; }
            .ptm-history-row .ptm-result { grid-column:auto; }
            .ptm-history-row .ptm-status { justify-self:start; }
            .ptm-history-range { font-size:9px; }
            .ptm-pagination { justify-content:center; }
        }
    </style>
@endsection

@section('content')
    <div class="patient-page ptm-page">
        <header class="ptm-header">
            <div>
                <div class="ptm-eyebrow">Личный кабинет пациента</div>
                <h1>Телемедицина</h1>
                <p>Дистанционные консультации с вашим лечащим врачом.</p>
            </div>
            <a class="ptm-help-link" href="#telemedicine-help">
                <i class="bi bi-info-circle"></i>
                Как проходит консультация?
            </a>
        </header>

        <div class="ptm-stats">
            <div class="ptm-stat is-primary">
                <div class="ptm-stat__icon"><i class="bi bi-calendar-event"></i></div>
                <div>
                    <strong>{{ $upcomingCount }}</strong>
                    <span>Предстоящие консультации</span>
                </div>
            </div>
            <div class="ptm-stat is-neutral">
                <div class="ptm-stat__icon"><i class="bi bi-camera-video"></i></div>
                <div>
                    <strong>{{ $historyCount }}</strong>
                    <span>Консультации в истории</span>
                </div>
            </div>
            <div class="ptm-stat is-amber">
                <div class="ptm-stat__icon"><i class="bi bi-file-earmark-text"></i></div>
                <div>
                    <strong>{{ $resultCount }}</strong>
                    <span>Заключения и рекомендации</span>
                </div>
            </div>
        </div>

        <nav class="ptm-tabs" aria-label="Разделы телемедицины">
            <a href="{{ route('patients.telemedicine.index', ['tab' => 'upcoming']) }}"
               class="{{ $activeTab === 'upcoming' ? 'is-active' : '' }}"
               @if($activeTab === 'upcoming') aria-current="page" @endif>
                <i class="bi bi-camera-video"></i>
                Предстоящие
                <span class="ptm-tabs__count">{{ $upcomingCount }}</span>
            </a>
            <a href="{{ route('patients.telemedicine.index', ['tab' => 'history']) }}"
               class="{{ $activeTab === 'history' ? 'is-active' : '' }}"
               @if($activeTab === 'history') aria-current="page" @endif>
                <i class="bi bi-clock-history"></i>
                История
                <span class="ptm-tabs__count">{{ $historyCount }}</span>
            </a>
        </nav>

        @if($activeTab === 'upcoming')
            @if($nearestUpcoming)
                <section class="ptm-feature-grid">
                    <div class="ptm-panel">
                        <h2 class="ptm-panel-title">Ближайшая консультация</h2>
                        <article class="ptm-next">
                            <div class="ptm-next__head">
                                <div class="ptm-next__date">
                                    <div class="ptm-next__date-icon"><i class="bi bi-camera-video"></i></div>
                                    <div>
                                        <strong>{{ $nearestUpcoming['date'] }} · {{ $nearestUpcoming['time'] }}</strong>
                                        <span>Дистанционная консультация (видео)</span>
                                    </div>
                                </div>
                                <span class="ptm-status is-{{ $nearestUpcoming['status_type'] }}">
                                    @if($nearestUpcoming['status_type'] === 'success')<i class="bi bi-check-circle"></i>@endif
                                    {{ $nearestUpcoming['status'] }}
                                </span>
                            </div>

                            <div class="ptm-doctor">
                                <div class="ptm-doctor__photo">
                                    @if($nearestUpcoming['doctor_photo_url'])
                                        <img src="{{ $nearestUpcoming['doctor_photo_url'] }}" alt="Фото врача" loading="lazy">
                                    @else
                                        <i class="bi bi-person"></i>
                                    @endif
                                </div>
                                <div>
                                    <strong>{{ $nearestUpcoming['doctor_name'] }}</strong>
                                    <span>{{ $nearestUpcoming['doctor_speciality'] }}</span>
                                </div>
                            </div>

                            <div class="ptm-meta">
                                <span><i class="bi bi-hospital"></i>{{ $nearestUpcoming['clinic_name'] }}</span>
                                <span class="ptm-meta__divider" aria-hidden="true"></span>
                                <span><i class="bi bi-hourglass-split"></i>{{ $nearestUpcoming['duration'] }} минут</span>
                                @if($nearestUpcoming['timezone_label'] && $nearestUpcoming['timezone_label'] !== 'часовой пояс не определён')
                                    <span><i class="bi bi-globe2"></i>{{ $nearestUpcoming['timezone_label'] }}</span>
                                @endif
                            </div>

                            <div class="ptm-status-note">
                                <div class="ptm-status-note__text">
                                    <i class="bi bi-info-circle"></i>
                                    <span>{{ $nearestUpcoming['status_hint'] }}</span>
                                </div>
                                <button type="button" class="ptm-equipment-toggle" data-ptm-equipment-toggle>
                                    <i class="bi bi-gear"></i> Проверить оборудование
                                </button>
                            </div>

                            <div class="ptm-equipment" data-ptm-equipment hidden>
                                <div class="ptm-equipment__body">
                                    <div>
                                        <div class="ptm-equipment__message" data-ptm-equipment-message>
                                            Проверим, доступны ли камера и микрофон в этом браузере. Видео и звук не записываются и не отправляются в клинику.
                                        </div>
                                        <button type="button" class="ptm-equipment__stop" data-ptm-equipment-stop hidden>Завершить проверку</button>
                                    </div>
                                    <video data-ptm-equipment-video autoplay muted playsinline hidden></video>
                                </div>
                            </div>

                            <div class="ptm-next__actions">
                                <span class="ptm-provider"><i class="bi bi-camera-video me-1"></i>{{ $nearestUpcoming['provider_label'] }}</span>
                                @if($nearestUpcoming['can_join'])
                                    <form method="POST" action="{{ route('patients.telemedicine.join', ['record' => $nearestUpcoming['id']]) }}">
                                        @csrf
                                        <button type="submit" class="ptm-join">
                                            <i class="bi bi-camera-video"></i>
                                            Подключиться к консультации
                                        </button>
                                    </form>
                                @elseif($nearestUpcoming['join_opens_at'])
                                    <span class="ptm-countdown" data-ptm-countdown
                                          data-opens-at="{{ $nearestUpcoming['join_opens_at'] }}"
                                          data-opens-label="{{ $nearestUpcoming['join_opens_label'] }}">
                                        Подключение откроется в {{ $nearestUpcoming['join_opens_label'] }}
                                    </span>
                                @else
                                    <button type="button" class="ptm-join is-disabled" disabled>
                                        <i class="bi bi-camera-video"></i>
                                        Подключиться к консультации
                                    </button>
                                @endif
                            </div>
                            @if(!$nearestUpcoming['can_join'] && $nearestUpcoming['join_opens_at'])
                                <div class="ptm-next__footnote">Кнопка подключения станет доступна в установленное время.</div>
                            @endif
                        </article>

                        @if($otherUpcoming->isNotEmpty())
                            <div class="ptm-other">
                                <h3 class="ptm-other__title">Другие предстоящие консультации</h3>
                                @foreach($otherUpcoming as $appointment)
                                    <div class="ptm-mini-row">
                                        <div class="ptm-mini-row__main">
                                            <div class="ptm-mini-row__icon"><i class="bi bi-calendar-event"></i></div>
                                            <div>
                                                <strong>{{ $appointment['date'] }} · {{ $appointment['time'] }}</strong>
                                                <span>{{ $appointment['doctor_name'] }} · {{ $appointment['clinic_name'] }}</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                                            <span class="ptm-status is-{{ $appointment['status_type'] }}">{{ $appointment['status'] }}</span>
                                            @if($appointment['can_join'])
                                                <form method="POST" action="{{ route('patients.telemedicine.join', ['record' => $appointment['id']]) }}">
                                                    @csrf
                                                    <button type="submit" class="ptm-join" style="min-height:32px;padding:7px 10px;font-size:10px;">Подключиться</button>
                                                </form>
                                            @elseif($appointment['join_opens_at'])
                                                <span class="ptm-countdown" data-ptm-countdown
                                                      data-opens-at="{{ $appointment['join_opens_at'] }}"
                                                      data-opens-label="{{ $appointment['join_opens_label'] }}">
                                                    Откроется в {{ $appointment['join_opens_label'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <aside class="ptm-prep" id="telemedicine-help">
                        <h3>Как подготовиться к консультации</h3>
                        <ul class="ptm-prep-list">
                            <li><i class="bi bi-camera-video"></i><span>Убедитесь, что камера и микрофон работают.</span></li>
                            <li><i class="bi bi-wifi"></i><span>Используйте стабильное интернет-соединение.</span></li>
                            <li><i class="bi bi-file-earmark-medical"></i><span>При необходимости подготовьте результаты анализов и другие документы.</span></li>
                            <li><i class="bi bi-house-check"></i><span>Выберите тихое и комфортное место, где вас не будут отвлекать.</span></li>
                        </ul>
                        <a class="ptm-docs-link" href="{{ route('patients.documents.index') }}">
                            <i class="bi bi-folder2-open"></i> Перейти к медицинским документам
                        </a>
                    </aside>
                </section>
            @else
                <section class="ptm-panel ptm-empty">
                    <div class="ptm-empty__icon"><i class="bi bi-camera-video"></i></div>
                    <h2>Предстоящих онлайн-консультаций пока нет</h2>
                    <p>Когда клиника назначит вам дистанционный приём, он появится здесь. Обычные очные записи в этот раздел не включаются.</p>
                </section>
            @endif

            <section class="ptm-panel ptm-history-preview">
                <div class="ptm-section-head">
                    <div>
                        <h2>История консультаций</h2>
                        <p>Ранее проведённые телемедицинские консультации.</p>
                    </div>
                    <a class="ptm-view-all" href="{{ route('patients.telemedicine.index', ['tab' => 'history']) }}">
                        Смотреть всю историю <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                @if($recentHistory->isNotEmpty())
                    <div class="ptm-history-list">
                        @foreach($recentHistory as $appointment)
                            <article class="ptm-history-row">
                                <div class="ptm-history-date">
                                    <div class="ptm-history-date__icon"><i class="bi bi-camera-video"></i></div>
                                    <div>
                                        <strong>{{ $appointment['date'] }} · {{ $appointment['time'] }}</strong>
                                        <span>{{ $appointment['duration'] }} минут · {{ $appointment['clinic_name'] }}</span>
                                    </div>
                                </div>
                                <div class="ptm-history-doctor">
                                    <strong>{{ $appointment['doctor_name'] }}</strong>
                                    <span>{{ $appointment['doctor_speciality'] }}</span>
                                </div>
                                <span class="ptm-status is-{{ $appointment['status_type'] }}">{{ $appointment['status'] }}</span>
                                @if($appointment['has_result'])
                                    <details class="ptm-result">
                                        <summary><i class="bi bi-file-earmark-text"></i> Заключение</summary>
                                        <div class="ptm-result__body">
                                            @if($appointment['result_conclusion'])
                                                <h4>Заключение врача</h4>
                                                <p>{{ $appointment['result_conclusion'] }}</p>
                                            @endif
                                            @if($appointment['result_recommendations'])
                                                <h4>Рекомендации</h4>
                                                <p>{{ $appointment['result_recommendations'] }}</p>
                                            @endif
                                            @if($appointment['result_completed_at'])
                                                <span>Оформлено: {{ $appointment['result_completed_at'] }}</span>
                                            @endif
                                        </div>
                                    </details>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="ptm-empty" style="min-height:140px; margin-top:13px;">
                        <div class="ptm-empty__icon"><i class="bi bi-clock-history"></i></div>
                        <h2>История пока пуста</h2>
                        <p>Завершённые, отменённые и прошедшие дистанционные приёмы появятся здесь.</p>
                    </div>
                @endif
            </section>
        @else
            <section class="ptm-panel ptm-history-page">
                <div class="ptm-section-head">
                    <div>
                        <h2>История консультаций</h2>
                        <p>Все ваши дистанционные приёмы, их статусы и оформленные врачом рекомендации.</p>
                    </div>
                    <span class="ptm-tabs__count">{{ $historyCount }}</span>
                </div>

                @if($historyPage->count())
                    <div class="ptm-history-list">
                        @foreach($historyPage as $appointment)
                            <article class="ptm-history-row">
                                <div class="ptm-history-date">
                                    <div class="ptm-history-date__icon"><i class="bi bi-camera-video"></i></div>
                                    <div>
                                        <strong>{{ $appointment['date'] }} · {{ $appointment['time'] }}</strong>
                                        <span>{{ $appointment['duration'] }} минут · {{ $appointment['clinic_name'] }}</span>
                                    </div>
                                </div>
                                <div class="ptm-history-doctor">
                                    <strong>{{ $appointment['doctor_name'] }}</strong>
                                    <span>{{ $appointment['doctor_speciality'] }}</span>
                                </div>
                                <span class="ptm-status is-{{ $appointment['status_type'] }}">{{ $appointment['status'] }}</span>
                                @if($appointment['has_result'])
                                    <details class="ptm-result">
                                        <summary><i class="bi bi-file-earmark-text"></i> Заключение</summary>
                                        <div class="ptm-result__body">
                                            @if($appointment['result_conclusion'])
                                                <h4>Заключение врача</h4>
                                                <p>{{ $appointment['result_conclusion'] }}</p>
                                            @endif
                                            @if($appointment['result_recommendations'])
                                                <h4>Рекомендации</h4>
                                                <p>{{ $appointment['result_recommendations'] }}</p>
                                            @endif
                                            @if($appointment['result_completed_at'])
                                                <span>Оформлено: {{ $appointment['result_completed_at'] }}</span>
                                            @endif
                                        </div>
                                    </details>
                                @endif
                            </article>
                        @endforeach
                    </div>
                    <div class="ptm-history-range">
                        Показаны {{ $historyPage->firstItem() }}–{{ $historyPage->lastItem() }} из {{ $historyPage->total() }} консультаций
                    </div>
                    @if($historyPage->hasPages())
                        <div class="ptm-pagination">
                            {{ $historyPage->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                @else
                    <div class="ptm-empty">
                        <div class="ptm-empty__icon"><i class="bi bi-clock-history"></i></div>
                        <h2>История консультаций пока пуста</h2>
                        <p>Завершённые, отменённые и прошедшие дистанционные приёмы будут отображаться здесь.</p>
                    </div>
                @endif
            </section>
        @endif

        <div class="ptm-footer-note">
            <i class="bi bi-shield-check"></i>
            <span>Ссылка на видеоконсультацию не показывается заранее. Перед подключением система повторно проверяет вашу запись, подготовку сеанса, клинику и разрешённое время входа. Факт проведения консультации подтверждает врач.</span>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (() => {
            const countdowns = Array.from(document.querySelectorAll('[data-ptm-countdown]'));
            let reloadScheduled = false;

            const updateCountdowns = () => {
                const now = Math.floor(Date.now() / 1000);
                countdowns.forEach((element) => {
                    const opensAt = Number(element.dataset.opensAt || 0);
                    const remaining = opensAt - now;

                    if (remaining <= 0) {
                        element.textContent = 'Подключение открывается…';
                        if (!reloadScheduled) {
                            reloadScheduled = true;
                            window.setTimeout(() => window.location.reload(), 1200);
                        }
                        return;
                    }

                    const hours = Math.floor(remaining / 3600);
                    const minutes = Math.floor((remaining % 3600) / 60);
                    const seconds = remaining % 60;
                    const parts = [];
                    if (hours > 0) parts.push(`${hours} ч.`);
                    if (minutes > 0 || hours > 0) parts.push(`${minutes} мин.`);
                    parts.push(`${seconds} сек.`);
                    element.textContent = `Подключение откроется через ${parts.join(' ')}`;
                });
            };

            if (countdowns.length) {
                updateCountdowns();
                window.setInterval(updateCountdowns, 1000);
            }

            const toggle = document.querySelector('[data-ptm-equipment-toggle]');
            const panel = document.querySelector('[data-ptm-equipment]');
            const video = document.querySelector('[data-ptm-equipment-video]');
            const message = document.querySelector('[data-ptm-equipment-message]');
            const stopButton = document.querySelector('[data-ptm-equipment-stop]');
            let equipmentStream = null;

            const stopEquipmentTest = () => {
                if (equipmentStream) {
                    equipmentStream.getTracks().forEach((track) => track.stop());
                    equipmentStream = null;
                }
                if (video) {
                    video.pause();
                    video.srcObject = null;
                    video.hidden = true;
                }
                if (stopButton) stopButton.hidden = true;
            };

            if (toggle && panel && video && message && stopButton) {
                toggle.addEventListener('click', async () => {
                    panel.hidden = false;
                    message.textContent = 'Запрашиваем доступ к камере и микрофону…';
                    stopButton.hidden = true;

                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        message.textContent = 'Браузер не поддерживает проверку камеры и микрофона. Откройте сайт в актуальном браузере и проверьте разрешения устройств.';
                        return;
                    }

                    stopEquipmentTest();
                    try {
                        equipmentStream = await navigator.mediaDevices.getUserMedia({audio: true, video: true});
                        video.srcObject = equipmentStream;
                        video.hidden = false;
                        message.textContent = 'Камера и микрофон доступны. Видео не записывается и не отправляется врачу.';
                        stopButton.hidden = false;
                    } catch (error) {
                        const denied = error && (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError');
                        message.textContent = denied
                            ? 'Доступ запрещён. Разрешите браузеру использовать камеру и микрофон в настройках сайта.'
                            : 'Не удалось получить доступ к камере или микрофону. Проверьте подключение устройств и повторите попытку.';
                    }
                });

                stopButton.addEventListener('click', () => {
                    stopEquipmentTest();
                    message.textContent = 'Проверка завершена. Камера и микрофон отключены.';
                });

                window.addEventListener('pagehide', stopEquipmentTest);
            }
        })();
    </script>
@endsection
