@if(count($sources))
    <section class="mcd-source-appendix">
        <h2>Приложение: записи медицинской карты</h2>
        @foreach($sources as $section)
            <section class="mcd-source-section">
                <h3>{{ $section['label'] }} <small>({{ count($section['items']) }})</small></h3>
                @if($section['note'])<p class="mcd-muted">{{ $section['note'] }}</p>@endif
                @forelse($section['items'] as $item)
                    <article class="mcd-source-record">
                        <h4>{{ $item['date'] ?: 'Дата не указана' }} · {{ $item['title'] }}</h4>
                        @foreach($item['parts'] as $label => $value)
                            <div class="mcd-source-part"><strong>{{ $label }}</strong><div class="mcd-text">{{ $value }}</div></div>
                        @endforeach
                        @if(!count($item['parts']))<p>Текст записи отсутствует.</p>@endif
                    </article>
                @empty
                    <p class="mcd-muted">За выбранный период записей не найдено. Это не означает отсутствие заболевания или лечения.</p>
                @endforelse
            </section>
        @endforeach
    </section>
@else
    <p class="mcd-muted">Записи из медкарты не приложены.</p>
@endif
