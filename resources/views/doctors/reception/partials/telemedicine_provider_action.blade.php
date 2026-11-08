@if(!empty($call['provider_session']))
    <div class="mc-audit-session__action-bar mb-3">
        <a class="btn btn-sm btn-primary" href="{{ $call['provider_session']['url'] }}">
            <i class="bi bi-camera-video me-1" aria-hidden="true"></i>{{ $call['provider_session']['label'] }}
        </a>
        @if($call['provider_session']['provider'] === 'telemost')
            <span class="small text-muted ms-2">Яндекс Телемост · результат приёма подтверждает врач</span>
        @endif
    </div>
@endif
