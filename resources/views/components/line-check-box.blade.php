<div class="line-checkbox">
    <input
        type="checkbox"
        class="visually-hidden"
        name="{{ $name ?? null }}"
        value="1"
        @checked((bool) ($checked ?? false))
    >
    <div class="line-checkbox__switcher @if($checked ?? false) checked @endif">
        <div class="line-checkbox__check"></div>
    </div>
    <span class="{{ $span_class ?? '' }}">{{ $label ?? null }}</span>
</div>
