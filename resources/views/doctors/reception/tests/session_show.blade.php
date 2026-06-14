@extends('doctors.reception')
@section('title', 'Результаты тестирования')
@section('sub-main')
    <div class="sticky-top bg-body border-bottom" style="z-index: 1020;">
        <div class="container py-2">
            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <div class="fw-bold">Ответы пациента</div>

                <div class="d-flex gap-2">
                    @if($canRestart ?? false)
                        <form method="POST" action="{{ route('doctors.patients.sessions.restart', $session) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger"
                                    onclick="return confirm('Начать тест заново? Все ответы будут удалены.')">
                                Начать заново
                            </button>
                        </form>
                    @else
                        <a class="btn btn-outline-secondary" href="{{ route('doctors.patients.medical_card', $session->patient->id) }}">Назад</a>
                    @endif

                    @if($session->status === 'submitted')
                        @if(!data_get($session->context,'final_pin_ok'))
                            <button
                                class="btn btn-primary"
                                id="btn-final-pin"
                                data-url="{{ route('doctors.tests.final_pin.form', $session) }}"
                            >
                                Завершить и разблокировать
                            </button>
                        @else
                            <form method="POST" action="{{ route('doctors.patients.sessions.finish', $session->id) }}">
                                @csrf
                                <button class="btn btn-success">
                                    Завершить тест и перейти в карту
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @if($errors->any())
            <div class="container">
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>

    <div class="container my-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="mb-1">
                            {{ $test->name ?? 'Тест' }}
                            @if(!empty($test->code))
                                <span class="text-secondary fw-semibold">({{ $test->code }})</span>
                            @endif
                        </h4>
                        <div class="text-secondary small">
                            Назначено: {{ optional($session->created_at)->format('d.m.Y H:i') }}
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap justify-content-end">
                        <span class="badge text-bg-light border">Всего: {{ $total ?? 0 }}</span>
                        <span class="badge text-bg-success">Отвечено: {{ $answered ?? 0}}</span>
                        <span class="badge text-bg-warning">Пропущено: {{ $missed ?? 0}}</span>
                        <span class="badge text-bg-secondary">Статус: {{ $session->status }}</span>
                    </div>
                </div>
            </div>
        </div>

        @foreach($sections as $section)
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="fw-bold">{{ $section['title'] }}</div>
                    <span class="badge text-bg-light border">{{ count($section['items']) }} вопросов</span>
                </div>

                <div class="list-group list-group-flush">
                    @foreach($section['items'] as $i => $item)
                        <div class="list-group-item py-3">
                            <div class="d-flex gap-3">
                                <div class="text-secondary fw-bold" style="width: 28px;">
                                    {{ $i+1 }}
                                </div>

                                <div class="flex-grow-1">
                                    <div class="fw-semibold mb-2" style="line-height: 1.25;">
                                        {{ $item['text'] }}
                                    </div>

                                    @if(!empty($item['answer_label']))
                                        <div class="p-2 rounded border bg-light d-flex gap-2">
                                            <div class="text-secondary small" style="min-width: 52px;">Ответ</div>
                                            <div class="fw-semibold">{{ $item['answer_label'] }}</div>
                                        </div>
                                    @else
                                        <div class="p-2 rounded border border-warning-subtle bg-warning-subtle d-flex gap-2">
                                            <div class="text-secondary small" style="min-width: 52px;">Ответ</div>
                                            <div class="fw-semibold text-danger-emphasis">Нет ответа</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <script>
        async function finishAndUnlock(){
            if (!confirm('Завершить и разблокировать панель врача?')) return;

            const r = await fetch('{{ route('doctors.patients.sessions.finish', $session) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            if (r.ok) {
                location.href = '{{ route('doctors.main') }}';
            } else {
                alert('Не удалось завершить. Проверьте статус/финальный PIN.');
            }
        }

        document.getElementById('unlockBtn')?.addEventListener('click', finishAndUnlock);
    </script>
@endsection
