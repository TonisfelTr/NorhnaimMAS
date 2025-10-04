@extends('doctors.reception')
@section('title', 'Текущая регистратура')
@section('assets')
    <style>
        /* лёгкая “картонка” как на превью */
        .anamnesis-show .card.shadow-sm{box-shadow:0 .25rem .75rem rgba(0,0,0,.06)}
        /* совместимость, если нет text-bg-* в вашей версии Bootstrap */
        .badge.text-bg-primary{background:#0d6efd;color:#fff}
        .badge.text-bg-success{background:#198754;color:#fff}
        .badge.text-bg-warning{background:#f6c343;color:#000}
        .badge.text-bg-info{background:#0dcaf0;color:#000}
        /* SunEditor вывод */
        .sun-output{line-height:1.6}
        .sun-output p{margin-bottom:.8rem}
        .sun-output ul,.sun-output ol{padding-left:1.25rem;margin-bottom:.8rem}
    </style>
@endsection
@section('sub-main')
    <div class="container-xxl my-4 anamnesis-show">

        {{-- Крошки --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('doctors.reception') }}">Пациенты</a></li>
                <li class="breadcrumb-item">
                    <a href="{{ route('doctors.patients.medical_card', $patient->id) }}">{{ $patient->fullName ?? $patient->fio ?? ('Пациент #'.$patient->id) }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Анамнез #{{ $anamnesis->id }}</li>
            </ol>
        </nav>

        {{-- Заголовок + действия --}}
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <h1 class="h4 mb-2">Анамнез пациента: {{ $patient->fullName ?? $patient->fio ?? ('#'.$patient->id) }}</h1>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge bg-primary text-bg-primary">{{ $anamnesis->diagnose->code ?? 'Диагноз не поставлен' }}</span>

                    @if($anamnesis->is_current)
                        <span class="badge bg-success text-bg-success">Текущий</span>
                    @endif
                    <span class="badge bg-warning text-bg-warning">
                        @if ($anamnesis->source === 'ai')
                            <i class="bi bi-cpu me-1"></i> Диагностика через анализатор
                        @else
                            <i class="bi bi-hand-index-thumb"></i> Ручная диагностика
                        @endif
                    </span>
                    <span class="text-muted small">
                        • создан {{ optional($anamnesis->created_at)->format('d.m.Y H:i') }},
        </span>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a class="btn btn-light" href="{{ route('doctors.patients.medical_card', $patient->id) }}">← К карточке пациента</a>
            </div>
        </div>

        <div class="row g-3">
            {{-- Текст анамнеза --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3">
                        <strong>Текст анамнеза</strong>
                    </div>
                    <div class="card-body">
                        @php $html = $anamnesis->text ?? ''; @endphp
                        @if($html)
                            <div class="sun-output">{!! trim($html) !!}</div>
                        @else
                            <div class="text-muted">Текст отсутствует.</div>
                        @endif
                    </div>
                </div>

                {{-- Замечания анализатора (опционально) --}}
                @if(($anamnesis->source ?? null) === 'ai' && filled($anamnesis->ai_flags))
                    @php $flags = is_array($anamnesis->ai_flags) ? $anamnesis->ai_flags : json_decode($anamnesis->ai_flags, true); @endphp
                    @if(!empty($flags))
                        <div class="card shadow-sm border-0 mt-3">
                            <div class="card-header bg-white border-0 py-3"><strong>Замечания анализатора</strong></div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    @foreach($flags as $f) <li>{{ $f }}</li> @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            {{-- Сведения --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <strong>Сведения</strong>
                        @if(!is_null($anamnesis->ai_score ?? null))
                            <span class="badge bg-info text-bg-info" title="уверенность анализатора">{{ (int)$anamnesis->ai_score }}%</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Диагноз</div>
                            <div class="d-flex align-items-center gap-2 flex-wrap small">
                                @if($anamnesis->diagnose)
                                    <span class="badge bg-primary text-bg-primary">
                                        {{ $anamnesis->diagnose->code }}
                                    </span>
                                    {{ $anamnesis->diagnose->title }}
                                @else
                                    <span>Диагноз не поставлен</span>
                                @endif
                            </div>
                        </div>
{{--                        @dd($symptoms)--}}
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Симптомы</div>
                            @if(count($symptoms))
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($symptoms as $s)
                                        <span class="badge rounded-pill bg-light border text-bg-warning">{{ $s->title }}</span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted">Не указаны</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small mb-1">Источник заполнения</div>
                            <div>
                                @if(($anamnesis->source ?? null) === 'ai') <i class="bi bi-cpu"></i> Заполнение через анализатор
                                @else <i class="bi bi-pencil"></i>Ручное заполнение @endif
                            </div>
                        </div>

                        <div class="mb-0">
                            <div class="text-muted small mb-1">Автор</div>
                            <div>{{ $anamnesis->doctor->full_name }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
