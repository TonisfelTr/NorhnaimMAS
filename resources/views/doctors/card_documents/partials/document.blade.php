<article class="mcd-paper">
    @if($document->status === 'draft')<div class="mcd-draft-label">ЧЕРНОВИК · НЕ ОФОРМЛЕН</div>@endif
    <header class="mcd-paper-heading">
        <div class="mcd-organization">{{ $document->organization ?: 'Медицинская организация не указана' }}</div>
        @if($document->organization_details)<div class="mcd-text mcd-muted">{{ $document->organization_details }}</div>@endif
        <h1>{{ $config['title'] }}</h1>
        <p>№ {{ $document->number }} от {{ $document->issued_at->format('d.m.Y') }}</p>
    </header>
    <div class="mcd-paper-patient">
        <p><strong>Пациент:</strong> {{ $document->patient_snapshot['name'] ?? '—' }}</p>
        <p><strong>Дата рождения:</strong> {{ ($document->patient_snapshot['birth_at'] ?? '') ?: '—' }}<span class="mcd-paper-gap"></span><strong>Медкарта №</strong> {{ ($document->patient_snapshot['medcard_number'] ?? '') ?: '—' }}</p>
        @if($document->recipient)<p><strong>Куда предоставляется:</strong> {{ $document->recipient }}</p>@endif
        @if($document->purpose)<p><strong>Цель выдачи:</strong> {{ $document->purpose }}</p>@endif
        @if($document->type === 'extract' || count($document->source_options ?? []))
            <p><strong>Период:</strong>
                @if($document->period_mode === 'range')
                    с {{ $document->period_from?->format('d.m.Y') }} по {{ $document->period_to?->format('d.m.Y') }} включительно
                @else
                    все имеющиеся записи по {{ $document->issued_at->format('d.m.Y') }} включительно
                @endif
            </p>
        @endif
    </div>
    @if($document->include_diagnosis && ($document->diagnosis_code || $document->diagnosis_title))
        <section class="mcd-paper-section"><h2>Диагноз</h2><div class="mcd-text">{{ trim(($document->diagnosis_code ?? '') . ' ' . ($document->diagnosis_title ?? '')) }}</div></section>
    @endif
    @foreach($config['fields'] as $key => $label)
        @if(filled($document->fields[$key] ?? null))
            <section class="mcd-paper-section"><h2>{{ $label }}</h2><div class="mcd-text">{{ $document->fields[$key] }}</div></section>
        @endif
    @endforeach
    @if(count($document->source_options ?? []))
        @include('doctors.card_documents.partials.sources', ['sources' => $document->source_snapshot ?? []])
    @endif
    <footer class="mcd-paper-signature">
        <div>{{ $document->author_position ?: 'Врач' }}</div>
        <div>___________________<small>подпись</small></div>
        <div>{{ $document->author_name }}</div>
    </footer>
</article>
