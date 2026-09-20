@if(\Illuminate\Support\Facades\Route::has('doctors.card_documents.create'))
    <section class="mcd-launcher" aria-label="Заключения и документы">
        <div class="mcd-head">
            <div>
                <div class="mcd-eyebrow">Медицинские документы</div>
                <h2>Заключения и документы</h2>
                <p>Создание документов по данным карты. Загруженные файлы остаются ниже.</p>
            </div>
            <a class="mcd-button mcd-button--light"
               href="{{ route('doctors.card_documents.index', ['patient_id' => $patient->id]) }}">
                <i class="bi bi-clock-history"></i> Журнал документов
            </a>
        </div>
        <div class="mcd-menu-grid">
            @foreach(\App\Support\CardDocuments\DocumentCatalog::menu() as $mcdType => $mcdItem)
                <a class="mcd-menu-item"
                   href="{{ route('doctors.card_documents.create', ['type' => $mcdType, 'patient_id' => $patient->id]) }}">
                    <span class="mcd-menu-icon"><i class="bi {{ $mcdItem['icon'] }}"></i></span>
                    <span><strong>{{ $mcdItem['title'] }}</strong><small>{{ $mcdItem['hint'] }}</small></span>
                </a>
            @endforeach
        </div>
    </section>
@endif
