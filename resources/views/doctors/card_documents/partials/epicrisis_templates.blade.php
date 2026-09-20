<div class="mcd-epicrisis-templates">
    <div class="small text-muted mb-2">Дополнительные структуры эпикриза</div>
    @foreach(['stage' => 'Этапный', 'transfer' => 'Переводной', 'discharge' => 'Выписной'] as $mcdKind => $mcdLabel)
        <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2"
                data-mcd-epicrisis-template="{{ $mcdKind }}">{{ $mcdLabel }} эпикриз</button>
    @endforeach
    <div class="small text-muted">Вид документа добавляется в текст. Сохранение — существующей кнопкой «Сохранить эпикриз».</div>
    <div class="small text-danger mt-2" data-mcd-epicrisis-error role="alert" hidden></div>
</div>
