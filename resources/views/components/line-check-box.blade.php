<div class="line-checkbox">
    <input type="checkbox" class="visually-hidden" checked="{{ $checked ?? null }}" name="{{ $name ?? null }}">
    <div class="line-checkbox__switcher @if($checked) checked @endif">
        <div class="line-checkbox__check"></div>
    </div>
    <span class="{{ $span_class ?? '' }}">{{ $label ?? null }}</span>
</div>
