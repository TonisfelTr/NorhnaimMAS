<div class="modal fade" id="{{ $messageBox }}" aria-hidden="true" aria-labelledby="title-h5" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title-h5">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <p>{{ $message }}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-danger" type="submit" @if($actionLink) formaction="{{ $actionLink }}" @endif>Удалить</button>
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>
