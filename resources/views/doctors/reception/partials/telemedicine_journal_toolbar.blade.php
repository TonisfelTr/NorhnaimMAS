<div class="mc-tm-toolbar">
    <div class="mc-tm-toolbar__tabs" aria-label="Записи телемедицины">
        <a href="{{ $telemedManager['active_url'] }}" class="mc-tm-filter {{ !$telemedManager['show_archive'] ? 'is-active' : '' }}"
           @if(!$telemedManager['show_archive']) aria-current="page" @endif>Действующие</a>
        <a href="{{ $telemedManager['archive_url'] }}" class="mc-tm-filter {{ $telemedManager['show_archive'] ? 'is-active' : '' }}"
           @if($telemedManager['show_archive']) aria-current="page" @endif>Архив удалённых</a>
    </div>
    <div class="mc-tm-toolbar__create">
        <button type="button" class="btn btn-primary mc-tm-create" data-telemed-create
                @disabled(!$telemedManager['can_create']) aria-describedby="mcTmCreateHint">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Создать сеанс
        </button>
        <small id="mcTmCreateHint">{{ $telemedManager['disabled_reason'] }}</small>
    </div>
</div>
@if($telemedManager['success'])
    <div class="alert alert-success" role="status">{{ $telemedManager['success'] }}</div>
@endif
@if($telemedManager['delete_errors'])
    <div class="alert alert-danger" role="alert">
        @foreach($telemedManager['delete_errors'] as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif
@if($telemedManager['create_errors'] && !$telemedManager['can_create'])
    <div class="alert alert-danger" role="alert">
        @foreach($telemedManager['create_errors'] as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif
