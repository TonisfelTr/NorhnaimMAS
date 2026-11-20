@extends('layouts.patient')

@section('title', 'Результат тестирования')

@section('assets')
    <style>
        .patient-test-result-page { max-width:900px; margin:0 auto; padding:28px 28px 56px; }
        .patient-test-result-card { background:#fff; border:1px solid #e6eeea; border-radius:20px; padding:clamp(20px,3vw,32px); box-shadow:0 8px 30px rgba(31,67,60,.045); }
        .patient-test-result-eyebrow { color:#168879; font-size:11px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; margin-bottom:9px; }
        .patient-test-result-card h1 { color:#1d433d; font-size:clamp(22px,3vw,30px); font-weight:800; letter-spacing:-.03em; margin:0 0 8px; }
        .patient-test-result-muted { color:#778782; font-size:13px; line-height:1.6; }
        .patient-test-result-score-list { display:grid; gap:12px; margin:24px 0; }
        .patient-test-result-score { display:flex; justify-content:space-between; align-items:center; gap:15px; background:#f6faf8; border:1px solid #e6efeb; border-radius:14px; padding:15px 17px; }
        .patient-test-result-score span { color:#60736d; font-size:13px; font-weight:700; }
        .patient-test-result-score strong { color:#1c6f63; font-size:23px; }
        .patient-test-result-notice { background:#f3f8f6; border:1px solid #e1ece7; color:#60736d; border-radius:13px; padding:14px 16px; font-size:12px; line-height:1.6; }
        .patient-test-result-actions { display:flex; justify-content:flex-end; margin-top:22px; }
        .patient-test-result-button { display:inline-flex; align-items:center; gap:8px; border-radius:10px; padding:10px 15px; background:#168879; color:#fff; text-decoration:none; font-weight:800; font-size:13px; }
        .patient-test-result-button:hover { background:#116f64; color:#fff; }
        @media(max-width:640px){.patient-test-result-page{padding:18px 14px 36px}.patient-test-result-card{border-radius:16px;padding:19px}.patient-test-result-score{padding:13px}}
    </style>
@endsection

@section('content')
    <div class="patient-test-result-page">
        <article class="patient-test-result-card">
            <div class="patient-test-result-eyebrow">
                {{ $isSelfTest ? 'Самостоятельное тестирование' : 'Назначено врачом' }}
            </div>
            <h1>{{ $session->test?->name ?: 'Результат тестирования' }}</h1>
            <p class="patient-test-result-muted">
                Завершено:
                {{ optional($session->completed_at)->format('d.m.Y H:i') ?: optional($session->updated_at)->format('d.m.Y H:i') }}
            </p>

            @if($results->isNotEmpty())
                <div class="patient-test-result-score-list">
                    @foreach($results as $result)
                        <div class="patient-test-result-score">
                            <span>{{ $result->key?->title ?: 'Суммарный балл' }}</span>
                            <strong>{{ $result->score }}</strong>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="patient-test-result-notice">
                    Тестирование завершено. Для этого типа теста автоматический числовой подсчёт не предусмотрен. Ваши ответы сохранены.
                </div>
            @endif

            <div class="patient-test-result-notice">
                @if($isSelfTest)
                    Это результат самостоятельного опросника. Он доступен только в вашем личном кабинете и не передаётся врачу. Результат скринингового теста сам по себе не является диагнозом и не заменяет консультацию специалиста.
                @else
                    Результат относится к тестированию, назначенному врачом. Если требуется дополнительная интерпретация, ориентируйтесь на заключение лечащего врача.
                @endif
            </div>

            <div class="patient-test-result-actions">
                <a class="patient-test-result-button" href="{{ $returnUrl }}">
                    <i class="bi bi-arrow-left"></i> Вернуться к тестированиям
                </a>
            </div>
        </article>
    </div>
@endsection
