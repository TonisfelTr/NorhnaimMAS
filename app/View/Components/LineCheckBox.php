<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LineCheckBox extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        private string $class = '',
        private bool $checked = false,
        private string $label = '',
        private string $name = ''
    )
    {

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $span_class = $this->class;
        $checked = $this->checked;
        $label = $this->label;
        $name = $this->name;
        return view('components.line-check-box', compact('span_class', 'checked', 'label', 'name'));
    }
}
