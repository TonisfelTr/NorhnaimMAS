<div class="mc-tm-row-actions">
    @if($call['journal']['can_delete'])
        <button type="button" class="btn btn-sm btn-outline-danger" data-telemed-delete
                data-delete-url="{{ $call['journal']['delete_url'] }}"
                data-visit-id="{{ $call['id'] }}" data-visit-date="{{ $call['date'] }}"
                data-visit-doctor="{{ $call['doctor'] }}">
            <i class="bi bi-trash3 me-1" aria-hidden="true"></i> Удалить сеанс
        </button>
    @elseif(!$call['deleted'])
        <span class="mc-tm-protected"><i class="bi bi-shield-lock me-1" aria-hidden="true"></i>{{ $call['journal']['reason'] }}</span>
    @endif
</div>
