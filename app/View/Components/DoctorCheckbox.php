<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DoctorCheckbox extends Component
{
    public string $id;
    public string $label;
    public string $name;
    public string $wrapperClass;
    public bool $checked;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $id = '',
        string $label = '',
        string $name = '',
        string $wrapperClass = '',
        bool $checked = false
    ) {
        $this->id = $id ?: uniqid('checkbox_');
        $this->label = $label;
        $this->name = $name;
        $this->wrapperClass = $wrapperClass;
        $this->checked = $checked;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.doctor-checkbox');
    }
}
