@extends('doctors.prescriptions')

@section('prescription-title', $document->document_number ?: 'Документ ВВК')

@push('scripts')
    <script>
        (() => {
            const initializeMilitaryPrint = () => {
                const printButton = document.querySelector('[data-military-print]');

                if (!printButton || printButton.dataset.printReady === '1') return;

                printButton.dataset.printReady = '1';

                let printFrame = null;
                let loadingTimer = null;
                let printing = false;

                const resetPrint = () => {
                    window.clearTimeout(loadingTimer);
                    loadingTimer = null;
                    printFrame?.remove();
                    printFrame = null;
                    printing = false;
                    printButton.removeAttribute('aria-busy');
                    printButton.removeAttribute('aria-disabled');
                };

                const showPrintError = () => {
                    resetPrint();
                    window.alert('Не удалось подготовить документ к печати. Повторите попытку.');
                };

                printButton.addEventListener('click', (event) => {
                    event.preventDefault();

                    if (printing) return;

                    printing = true;
                    printButton.setAttribute('aria-busy', 'true');
                    printButton.setAttribute('aria-disabled', 'true');

                    const frame = document.createElement('iframe');
                    printFrame = frame;
                    frame.title = 'Печатная версия документа';
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
                        window.clearTimeout(loadingTimer);
                        loadingTimer = null;

                        const printWindow = frame.contentWindow;

                        if (!printWindow) {
                            showPrintError();
                            return;
                        }

                        let dialogHandled = false;

                        const finishPrint = () => {
                            if (dialogHandled) return;

                            dialogHandled = true;
                            window.setTimeout(resetPrint, 0);
                        };

                        printWindow.addEventListener('afterprint', finishPrint, { once: true });

                        window.setTimeout(() => {
                            try {
                                printWindow.focus();
                                printWindow.print();

                                // Firefox возвращает управление после закрытия окна печати.
                                finishPrint();
                            } catch (error) {
                                console.error('[military-document] print failed', error);
                                showPrintError();
                            }
                        }, 150);
                    }, { once: true });

                    frame.addEventListener('error', showPrintError, { once: true });
                    frame.src = printButton.href;
                    document.body.append(frame);

                    loadingTimer = window.setTimeout(() => {
                        if (printing) showPrintError();
                    }, 15000);
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeMilitaryPrint, { once: true });
            } else {
                initializeMilitaryPrint();
            }
        })();
    </script>
@endpush

@section('sub-main')
    @include('doctors.commissions.military.partials.styles')

    @php
        $creatorName = collect([
            $document->creator?->surname,
            $document->creator?->name,
            $document->creator?->patronym,
        ])->filter()->implode(' ');
    @endphp

    <main class="military-page">
        <div class="military-container">
            <header class="military-hero">
                <div class="military-hero__main">
                    <a class="military-back" href="{{ route('doctors.commissions.military.index') }}" title="Назад в журнал">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div class="military-hero__icon"><i class="bi bi-shield-check"></i></div>
                    <div>
                        <div class="military-eyebrow">{{ $document->documentTypeLabel() }}</div>
                        <h1>{{ $document->document_number ?: 'Документ без номера' }}</h1>
                        <p>{{ $document->patientFullName() }} · {{ $document->document_date?->format('d.m.Y') }}</p>
                    </div>
                </div>
                <div class="military-hero__actions">
                    <span class="military-badge military-badge--{{ $document->status === 'prepared' ? 'prepared' : 'draft' }}">
                        {{ $document->statusLabel() }}
                    </span>
                    <a class="military-button military-button--ghost"
                       href="{{ route('doctors.commissions.military.edit', $document) }}">
                        <i class="bi bi-pencil"></i> Редактировать
                    </a>
                    <a class="military-button military-button--primary" target="_blank"
                       data-military-print
                       href="{{ route('doctors.commissions.military.print', $document) }}">
                        <i class="bi bi-printer"></i> Печать
                    </a>
                </div>
            </header>

            @if(session('message'))
                <div class="military-alert military-alert--success">
                    <i class="bi bi-check-circle-fill"></i><span>{{ session('message') }}</span>
                </div>
            @endif

            <div class="military-details-grid">
                <section class="military-detail-card">
                    <h2><i class="bi bi-person-vcard"></i> Пациент</h2>
                    <dl class="military-detail-list">
                        <div><dt>ФИО</dt><dd>{{ $document->patientFullName() }}</dd></div>
                        <div><dt>Дата рождения</dt><dd>{{ $document->patientBirthDateLabel() }}</dd></div>
                        <div class="military-detail-card--wide"><dt>Диагноз на дату документа</dt><dd>{{ $document->diagnosisLabel() }}</dd></div>
                    </dl>
                </section>

                <section class="military-detail-card">
                    <h2><i class="bi bi-file-earmark-text"></i> Реквизиты</h2>
                    <dl class="military-detail-list">
                        <div><dt>Вид документа</dt><dd>{{ $document->documentTypeLabel() }}</dd></div>
                        <div><dt>Дата документа</dt><dd>{{ $document->document_date?->format('d.m.Y') ?: '—' }}</dd></div>
                        <div><dt>Организация</dt><dd>{{ $document->requesting_organization ?: 'Не указана' }}</dd></div>
                        <div><dt>Запрос</dt><dd>{{ $document->request_number ? '№ ' . $document->request_number : 'Без номера' }}{{ $document->request_date ? ' от ' . $document->request_date->format('d.m.Y') : '' }}</dd></div>
                        <div><dt>Подготовил</dt><dd>{{ $creatorName ?: 'Не указан' }}</dd></div>
                        <div><dt>Оформлен</dt><dd>{{ $document->prepared_at?->format('d.m.Y H:i') ?: 'Нет' }}</dd></div>
                    </dl>
                </section>

                @foreach([
                    'Основание и цель' => $document->purpose,
                    'Жалобы' => $document->complaints,
                    'Анамнез' => $document->anamnesis,
                    'Объективный статус' => $document->objective_status,
                    'Результаты обследований' => $document->examination_results,
                    'Сведения о лечении и динамике' => $document->treatment_history,
                    'Медицинское заключение' => $document->medical_conclusion,
                    'Рекомендации' => $document->recommendations,
                    'Примечания' => $document->notes,
                ] as $title => $value)
                    <section class="military-detail-card {{ in_array($title, ['Медицинское заключение', 'Сведения о лечении и динамике'], true) ? 'military-detail-card--wide' : '' }}">
                        <h2><i class="bi bi-clipboard2-pulse"></i> {{ $title }}</h2>
                        <div class="military-text-block {{ trim((string) $value) === '' ? 'is-empty' : '' }}">{{ trim((string) $value) !== '' ? $value : 'Не заполнено' }}</div>
                    </section>
                @endforeach
            </div>
        </div>
    </main>
@endsection
