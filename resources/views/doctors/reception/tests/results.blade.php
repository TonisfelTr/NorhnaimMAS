@extends('doctors.reception')
@section('title', 'Результаты теста ' . $test->name)

@section('sub-main')
    <div class="container py-4">

        <div class="d-flex align-items-start justify-content-between mb-3">
            <div>
                <h3 class="mb-1">{{ $test->name }} <span class="text-muted">({{ $test->code }})</span></h3>
                <div class="text-muted">
                    Сессия #{{ $session->id }} • Статус:
                    <span class="badge bg-secondary">{{ $session->status }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Назад</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body d-flex flex-wrap gap-2">
                <span class="badge bg-light text-dark border">Всего: <b>{{ $total }}</b></span>
                <span class="badge bg-success">Отвечено: <b>{{ $answered }}</b></span>
                <span class="badge bg-warning text-dark">Пропущено: <b>{{ $missed }}</b></span>
            </div>
        </div>

        <div class="row g-3">
            {{-- LEFT --}}
            <div class="col-lg-4">
                {{-- Chart --}}
                @if(!$showChart)
                    <div class="alert alert-info">
                        Для этого теста есть только одна шкала — круговая диаграмма неинформативна.
                    </div>
                @else
                    <div class="card mb-3">
                        <div class="card-header fw-semibold">Шкалы</div>
                        <div class="card-body">
                            <canvas id="scalesPie" height="220"></canvas>
                        </div>
                    </div>
                @endif

                {{-- Overall --}}
                <div class="card mb-3">
                    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                        <span>Общая оценка</span>
                        @if($overview['max'])
                            <span class="badge bg-light text-dark border">{{ $overview['score'] }} / {{ $overview['max'] }}</span>
                        @else
                            <span class="badge bg-light text-dark border">{{ $overview['score'] }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(!is_null($overview['percent']))
                            <div class="progress" style="height: 14px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $overview['percent'] }}%;"
                                     aria-valuenow="{{ $overview['percent'] }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100"></div>
                            </div>
                            <div class="small text-muted mt-2">
                                {{ $overview['percent'] }}% от максимума
                            </div>
                        @else
                            <div class="text-muted">Не удалось определить максимум шкалы (нет диапазона).</div>
                        @endif
                    </div>
                </div>

                {{-- Totals --}}
                <div class="card mb-3">
                    <div class="card-header fw-semibold">Итоги</div>
                    <div class="list-group list-group-flush">
                        @foreach($resultCards as $row)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="me-2">
                                        <div class="fw-semibold">{{ $row['label'] }}</div>
                                        @if(!empty($row['range_text']))
                                            <div class="text-muted small">Диапазон: {{ $row['range_text'] }}</div>
                                        @endif
                                    </div>
                                    <div class="fs-5 fw-bold">{{ $row['score'] }}</div>
                                </div>

                                @if(!is_null($row['percent']))
                                    <div class="progress mt-2" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width: {{ $row['percent'] }}%;"
                                             aria-valuenow="{{ $row['percent'] }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100"></div>
                                    </div>
                                    <div class="small text-muted mt-1">{{ $row['percent'] }}%</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Interpretation --}}
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <strong>{{ $interpretation['title'] ?? 'Трактовка' }}</strong>
                        @if(!empty($interpretation['badge']))
                            <span class="badge bg-{{ $interpretation['badge']['class'] }}">
                {{ $interpretation['badge']['text'] }}
            </span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-2">{{ $interpretation['text'] ?? '' }}</div>

                        @if(!empty($interpretation['items']))
                            <ul class="mb-0">
                                @foreach($interpretation['items'] as $li)
                                    <li>{{ $li }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            {{-- RIGHT --}}
            <div class="col-lg-8">
                @foreach($sections as $sec)
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between">
                            <div class="fw-semibold">{{ $sec['title'] }}</div>
                            <div class="badge bg-light text-dark border">{{ count($sec['items']) }} вопросов</div>
                        </div>

                        <div class="card-body">
                            <div class="list-group">
                                @foreach($sec['items'] as $idx => $it)
                                    <div class="list-group-item">
                                        <div class="fw-semibold mb-1">
                                            {{ $idx+1 }}. {{ $it['text'] }}
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="badge bg-light text-dark border">Ответ</span>
                                            @if(!empty($it['answer']))
                                                <span class="fw-semibold">{{ $it['answer'] }}</span>
                                            @else
                                                <span class="text-muted">Нет ответа</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if($showChart)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                const data = @json($chart ?? []);
                const labels = data.map(x => x.label);
                const values = data.map(x => x.value);

                const ctx = document.getElementById('scalesPie');
                if (!ctx) return;

                new Chart(ctx, {
                    type: 'pie',
                    data: { labels, datasets: [{ data: values }] },
                    options: {
                        plugins: {
                            legend: { position: 'bottom' },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `${ctx.label}: ${ctx.raw}`
                                }
                            }
                        }
                    }
                });
            })();
        </script>
    @endif
@endpush
