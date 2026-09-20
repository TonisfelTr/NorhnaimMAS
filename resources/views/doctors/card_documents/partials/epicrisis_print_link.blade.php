@if(\Illuminate\Support\Facades\Route::has('doctors.card_documents.epicrisis.print'))
    <a class="btn btn-outline-secondary btn-sm"
       href="{{ route('doctors.card_documents.epicrisis.print', ['patient' => $patient->id, 'epicrisisId' => $epicrisis->id]) }}"
       target="_blank" rel="noopener" title="Печать эпикриза">
        <i class="bi bi-printer"></i> Печать
    </a>
@endif
