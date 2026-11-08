<div class="nh-card p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <div class="fw-semibold">Направления <span class="text-secondary fw-normal">({{ $researches->total() }})</span></div>
        <small class="text-secondary">Страница {{ $researches->currentPage() }} из {{ max(1, $researches->lastPage()) }}</small>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>№</th>
                <th>Пациент</th>
                <th>Исследование</th>
                <th>План</th>
                <th>Статус</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($researches as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>
                        {{ trim(($item->patient->surname ?? '') . ' ' . ($item->patient->name ?? '') . ' ' . ($item->patient->patronym ?? '')) }}
                        <small class="text-secondary">#{{ $item->patient_id }}</small>
                    </td>
                    <td>{{ $item->laboratory ?: 'Лабораторный анализ' }}</td>
                    <td>{{ $item->planned_at?->format('d.m.Y') ?? '—' }}</td>
                    <td>
                        @switch($item->status)
                            @case('ordered') <span class="badge text-bg-secondary">Назначено</span> @break
                            @case('processing') <span class="badge text-bg-warning">В работе</span> @break
                            @case('ready') <span class="badge text-bg-success">Готово</span> @break
                            @default {{ $item->status }}
                        @endswitch
                    </td>
                    <td>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('laboratory.researches.edit', $item->id) }}">
                            {{ $item->status === 'ready' ? 'Посмотреть' : 'Внести данные' }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-secondary py-4">Исследования не найдены.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $researches->links() }}
</div>
