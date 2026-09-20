<aside class="hospital-history-card">
    <div class="hospital-history-card__head">
        <div>
            <div class="hospital-section-eyebrow">История</div>
            <h2>Последние направления</h2>
            <p>
                {{ $selectedPatient ? 'Показаны записи выбранного пациента.' : 'Показаны последние записи по текущему типу.' }}
            </p>
        </div>
        <span class="hospital-history-card__count">{{ $referrals->total() }}</span>
    </div>

    <div class="hospital-history-list">
        @forelse($referrals as $referral)
            @php
                $patientName = collect([
                    $referral->patient?->surname,
                    $referral->patient?->name,
                    $referral->patient?->patronym,
                ])->filter()->implode(' ');
                $statusClass = match($referral->status) {
                    'sent', 'admitted' => 'is-success',
                    'cancelled' => 'is-danger',
                    'prepared' => 'is-primary',
                    default => 'is-muted',
                };
            @endphp

            <article class="hospital-history-item">
                <div class="hospital-history-item__top">
                    <span class="hospital-history-item__status {{ $statusClass }}">
                        {{ $referral->status_label }}
                    </span>
                    <time datetime="{{ $referral->created_at?->toAtomString() }}">
                        {{ $referral->created_at?->format('d.m.Y H:i') }}
                    </time>
                </div>

                <h3>{{ $patientName !== '' ? $patientName : 'Пациент #' . $referral->patient_id }}</h3>

                <div class="hospital-history-item__diagnosis" title="Предварительный диагноз">
                    @if($referral->diagnosis_code)
                        <span>{{ $referral->diagnosis_code }}</span>
                    @endif
                    {{ $referral->diagnosis_title ?: 'Предварительный диагноз не указан' }}
                </div>

                <p>
                    {{ \Illuminate\Support\Str::limit(
                        trim((string) ($referral->reason ?: $referral->clinical_summary)),
                        170
                    ) ?: 'Клиническое обоснование пока не заполнено.' }}
                </p>

                <div class="hospital-history-item__footer">
                    <span>
                        <i class="bi bi-building"></i>
                        {{ $referral->destination_organization ?: $referral->department ?: 'Организация не указана' }}
                    </span>
                    <span>
                        <i class="bi bi-geo-alt"></i>
                        {{ $referral->destination_address ?: 'Адрес стационара не указан' }}
                    </span>
                    <span>
                        <i class="bi bi-magic"></i>
                        {{ $referral->source_mode_label }}
                    </span>
                </div>
            </article>
        @empty
            <div class="hospital-history-empty">
                <div class="hospital-history-empty__icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h3>Записей пока нет</h3>
                <p>После сохранения направление появится в этой колонке.</p>
            </div>
        @endforelse
    </div>

    @if($referrals->hasPages())
        <div class="hospital-history-pagination">
            {{ $referrals->links() }}
        </div>
    @endif
</aside>
