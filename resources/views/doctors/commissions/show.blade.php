@extends('doctors.prescriptions')

@section(
    'prescription-title',
    $commission->protocol_number ?: 'Врачебная комиссия'
)

@section('sub-main')

    @include('doctors.commissions.partials.styles')

    <main class="commission-page">
        <div class="commission-container">

            <div class="crud-toolbar">
                <div class="crud-toolbar__copy">
                    <a href="{{ route('doctors.commissions.index') }}"
                       class="commission-back mb-2"
                       title="К журналу ВК">
                        <i class="bi bi-arrow-left"></i>
                    </a>

                    <h1 class="crud-toolbar__title">
                        {{ $commission->protocol_number ?: 'Врачебная комиссия' }}
                    </h1>

                    <p class="crud-toolbar__subtitle">
                        Просмотр оформленной записи врачебной комиссии.
                    </p>
                </div>

                <div class="crud-toolbar__actions">

                    <a class="commission-button commission-button--secondary"
                       href="{{ route(
                           'doctors.commissions.print',
                           $commission
                       ) }}"
                       >
                        <i class="bi bi-printer"></i>
                        Протокол
                    </a>

                    <a class="commission-button commission-button--primary"
                       href="{{ route(
                           'doctors.commissions.edit',
                           $commission
                       ) }}">
                        <i class="bi bi-pencil"></i>
                        Редактировать
                    </a>
                </div>
            </div>

            @if(session('status') === 'success')
                <div class="commission-alert commission-alert--success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            <div class="commission-detail-grid">

                <div class="commission-detail-stack">

                    <section class="commission-card">
                        <div class="commission-card__head">
                            <span class="commission-card__icon">
                                <i class="bi bi-person-vcard"></i>
                            </span>

                            <div>
                                <h2>Пациент</h2>
                                <p>
                                    Сведения пациента и диагноз,
                                    зафиксированный в ВК.
                                </p>
                            </div>
                        </div>

                        @php
                            $patient = $commission->patient;

                            $patientName = trim(
                                ($patient?->surname ?? '')
                                . ' '
                                . ($patient?->name ?? '')
                                . ' '
                                . ($patient?->patronym ?? '')
                            );
                        @endphp

                        <div class="commission-detail-list">

                            <div class="commission-detail-item">
                                <div class="commission-detail-item__label">
                                    Пациент
                                </div>
                                <div class="commission-detail-item__value">
                                    {{ $patientName !== ''
                                        ? $patientName
                                        : '—'
                                    }}
                                </div>
                            </div>

                            <div class="commission-detail-item">
                                <div class="commission-detail-item__label">
                                    Дата рождения
                                </div>
                                <div class="commission-detail-item__value">
                                    {{ $patient?->birth_at
                                        ? \Illuminate\Support\Carbon::parse(
                                            $patient->birth_at
                                        )->format('d.m.Y')
                                        : '—'
                                    }}
                                </div>
                            </div>

                            <div class="commission-detail-item commission-detail-item--wide">
                                <div class="commission-detail-item__label">
                                    Диагноз
                                </div>
                                <div class="commission-detail-item__value">
                                    {{ trim(
                                        ($commission->diagnosis_code ?? '')
                                        . (
                                            $commission->diagnosis_code
                                            && $commission->diagnosis_title
                                                ? ' — '
                                                : ''
                                        )
                                        . ($commission->diagnosis_title ?? '')
                                    ) ?: '—' }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="commission-card">
                        <div class="commission-card__head">
                            <span class="commission-card__icon">
                                <i class="bi bi-calendar2-week"></i>
                            </span>

                            <div>
                                <h2>Заседание</h2>
                                <p>
                                    Параметры заседания и рассматриваемый вопрос.
                                </p>
                            </div>
                        </div>

                        <div class="commission-detail-list">

                            <div class="commission-detail-item">
                                <div class="commission-detail-item__label">
                                    Дата и время
                                </div>
                                <div class="commission-detail-item__value">
                                    {{ $commission->meeting_at
                                        ? \Illuminate\Support\Carbon::parse(
                                            $commission->meeting_at
                                        )->format('d.m.Y H:i')
                                        : '—'
                                    }}
                                </div>
                            </div>

                            <div class="commission-detail-item">
                                <div class="commission-detail-item__label">
                                    Вид заседания
                                </div>
                                <div class="commission-detail-item__value">
                                    {{ $meetingTypeLabel }}
                                </div>
                            </div>

                            <div class="commission-detail-item">
                                <div class="commission-detail-item__label">
                                    Статус
                                </div>
                                <div class="commission-detail-item__value">
                                    <span class="commission-status commission-status--{{ $commission->status }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                            </div>

                            <div class="commission-detail-item">
                                <div class="commission-detail-item__label">
                                    Категория
                                </div>
                                <div class="commission-detail-item__value">
                                    {{ $categoryLabel }}
                                </div>
                            </div>

                            @if($commission->unscheduled_basis)
                                <div class="commission-detail-item commission-detail-item--wide">
                                    <div class="commission-detail-item__label">
                                        Основание внепланового заседания
                                    </div>
                                    <div class="commission-detail-item__value">
                                        {{ $commission->unscheduled_basis }}
                                    </div>
                                </div>
                            @endif

                            <div class="commission-detail-item commission-detail-item--wide">
                                <div class="commission-detail-item__label">
                                    Вопрос ВК
                                </div>
                                <div class="commission-detail-item__value">
                                    {{ $commission->agenda ?: '—' }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="commission-card">
                        <div class="commission-card__head">
                            <span class="commission-card__icon">
                                <i class="bi bi-people"></i>
                            </span>

                            <div>
                                <h2>Состав комиссии</h2>
                                <p>
                                    Члены ВК, присутствие и результаты голосования.
                                </p>
                            </div>
                        </div>

                        @forelse($commission->members as $member)
                            @php
                                $doctor = $member->doctor;

                                $doctorName = trim(
                                    ($doctor?->surname ?? '')
                                    . ' '
                                    . ($doctor?->name ?? '')
                                    . ' '
                                    . ($doctor?->patronym ?? '')
                                );

                                $roleLabels = [
                                    'chairman' => 'Председатель',
                                    'deputy' => 'Заместитель',
                                    'member' => 'Член комиссии',
                                ];

                                $voteLabels = [
                                    'for' => 'За',
                                    'against' => 'Против',
                                    'abstained' => 'Воздержался',
                                ];
                            @endphp

                            <div class="commission-member-view">
                                <div>
                                    <div class="commission-member-view__name">
                                        {{ $doctorName ?: 'Врач не указан' }}
                                    </div>

                                    <div class="commission-member-view__meta">
                                        {{ $doctor?->status ?: 'Специальность не указана' }}
                                    </div>
                                </div>

                                <div class="commission-member-view__meta">
                                    {{ $roleLabels[$member->role]
                                        ?? $member->role
                                        ?? '—'
                                    }}
                                </div>

                                <div class="commission-member-view__meta">
                                    {{ $member->present
                                        ? 'Присутствовал'
                                        : 'Отсутствовал'
                                    }}
                                </div>

                                <div class="commission-member-view__meta">
                                    {{ $voteLabels[$member->vote]
                                        ?? $member->vote
                                        ?? '—'
                                    }}
                                </div>
                            </div>

                            @if($member->special_opinion)
                                <div class="commission-detail-item mt-2">
                                    <div class="commission-detail-item__label">
                                        Особое мнение
                                    </div>
                                    <div class="commission-detail-item__value">
                                        {{ $member->special_opinion }}
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="commission-empty py-4">
                                Состав комиссии не указан.
                            </div>
                        @endforelse
                    </section>

                    <section class="commission-card">
                        <div class="commission-card__head">
                            <span class="commission-card__icon">
                                <i class="bi bi-journal-check"></i>
                            </span>

                            <div>
                                <h2>Решение</h2>
                                <p>
                                    Итоговое решение, обоснование и дополнительные сведения.
                                </p>
                            </div>
                        </div>

                        <div class="commission-detail-list">

                            <div class="commission-detail-item commission-detail-item--wide">
                                <div class="commission-detail-item__label">
                                    Решение ВК
                                </div>
                                <div class="commission-detail-item__value">
                                    {{ $commission->decision ?: '—' }}
                                </div>
                            </div>

                            <div class="commission-detail-item commission-detail-item--wide">
                                <div class="commission-detail-item__label">
                                    Обоснование
                                </div>
                                <div class="commission-detail-item__value">
                                    {{ $commission->rationale ?: '—' }}
                                </div>
                            </div>

                            <div class="commission-detail-item">
                                <div class="commission-detail-item__label">
                                    Секретарь
                                </div>
                                <div class="commission-detail-item__value">
                                    {{ $commission->secretary_name ?: '—' }}
                                </div>
                            </div>

                            <div class="commission-detail-item">
                                <div class="commission-detail-item__label">
                                    Должность секретаря
                                </div>
                                <div class="commission-detail-item__value">
                                    {{ $commission->secretary_position ?: '—' }}
                                </div>
                            </div>

                            @if($commission->notes)
                                <div class="commission-detail-item commission-detail-item--wide">
                                    <div class="commission-detail-item__label">
                                        Дополнительные сведения
                                    </div>
                                    <div class="commission-detail-item__value">
                                        {{ $commission->notes }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>
                </div>

                <aside class="commission-workspace__aside">
                    <div class="commission-summary-card">
                        <div class="commission-summary-card__head">
                            <span>
                                <i class="bi bi-info-circle"></i>
                            </span>

                            <div>
                                <h3>Карточка ВК</h3>
                                <p>
                                    Системные сведения записи.
                                </p>
                            </div>
                        </div>

                        <div class="commission-metric">
                            <span>ID</span>
                            <strong>{{ $commission->id }}</strong>
                        </div>

                        <div class="commission-metric">
                            <span>Членов ВК</span>
                            <strong>{{ $commission->members->count() }}</strong>
                        </div>

                        <div class="commission-metric">
                            <span>Присутствовало</span>
                            <strong>
                                {{ $commission->members
                                    ->where('present', true)
                                    ->count()
                                }}
                            </strong>
                        </div>

                        <div class="commission-summary-divider"></div>

                        <div class="commission-summary-note">
                            <i class="bi bi-check2-circle"></i>
                            <div>
                                {{ $commission->medical_record_entered_at
                                    ? 'Решение внесено в медицинскую документацию.'
                                    : 'Решение ещё не отмечено как внесённое в медицинскую документацию.'
                                }}
                            </div>
                        </div>
                    </div>

                    <div class="commission-summary-card">
                        <div class="d-grid gap-2">

                            <a class="commission-button commission-button--primary"
                               href="{{ route(
                                   'doctors.commissions.edit',
                                   $commission
                               ) }}">
                                <i class="bi bi-pencil"></i>
                                Редактировать
                            </a>

                            <a class="commission-button commission-button--secondary"
                               href="{{ route(
                                   'doctors.commissions.print',
                                   $commission
                               ) }}"
                               >
                                <i class="bi bi-printer"></i>
                                Печать протокола
                            </a>

                            <form method="POST"
                                  action="{{ route(
                                      'doctors.commissions.destroy',
                                      $commission
                                  ) }}"
                                  onsubmit="return confirm(
                                      'Удалить эту врачебную комиссию?'
                                  );">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="commission-button commission-button--ghost w-100">
                                    <i class="bi bi-trash"></i>
                                    Удалить
                                </button>
                            </form>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

@endsection
@once
    @push('scripts')
        <script>
            (() => {
                const initializeCommissionPrint = () => {
                    const button = document.querySelector('[data-commission-print]');

                    if (!button || button.dataset.printReady === '1') {
                        return;
                    }

                    button.dataset.printReady = '1';

                    let frame = null;
                    let fallbackTimer = null;
                    let printing = false;

                    const cleanup = () => {
                        window.clearTimeout(fallbackTimer);
                        fallbackTimer = null;

                        frame?.remove();
                        frame = null;
                        printing = false;

                        button.removeAttribute('aria-busy');
                        button.removeAttribute('aria-disabled');
                    };

                    button.addEventListener('click', (event) => {
                        event.preventDefault();

                        if (printing) {
                            return;
                        }

                        printing = true;
                        button.setAttribute('aria-busy', 'true');
                        button.setAttribute('aria-disabled', 'true');

                        frame = document.createElement('iframe');
                        frame.title = 'Печатная версия протокола';
                        frame.tabIndex = -1;
                        frame.setAttribute('aria-hidden', 'true');

                        Object.assign(frame.style, {
                            position: 'fixed',
                            right: '0',
                            bottom: '0',
                            width: '1px',
                            height: '1px',
                            border: '0',
                            opacity: '0',
                            pointerEvents: 'none',
                        });

                        frame.addEventListener('load', () => {
                            const printWindow = frame.contentWindow;

                            if (!printWindow) {
                                cleanup();
                                window.alert('Не удалось подготовить документ к печати.');
                                return;
                            }

                            printWindow.addEventListener(
                                'afterprint',
                                cleanup,
                                { once: true }
                            );

                            window.setTimeout(() => {
                                try {
                                    printWindow.focus();
                                    printWindow.print();
                                } catch (error) {
                                    console.error(
                                        '[medical-commission] Print failed',
                                        error
                                    );

                                    cleanup();
                                    window.alert(
                                        'Не удалось открыть окно печати.'
                                    );
                                }
                            }, 150);
                        }, { once: true });

                        frame.addEventListener('error', () => {
                            cleanup();
                            window.alert('Не удалось загрузить печатную версию.');
                        }, { once: true });

                        frame.src = button.href;
                        document.body.append(frame);

                        fallbackTimer = window.setTimeout(cleanup, 120000);
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener(
                        'DOMContentLoaded',
                        initializeCommissionPrint,
                        { once: true }
                    );
                } else {
                    initializeCommissionPrint();
                }
            })();
        </script>
    @endpush
@endonce
