<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DangerDialogComponent extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $title = '',
        public string $message = '',
        public string $button = '',
        public string $messageBox = '',
        public string $actionLink = ''
    )
    {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $title = $this->title;
        $message = $this->message;
        $button = $this->button;
        $messageBox = $this->messageBox;
        $actionLink = $this->actionLink;

        return view('components.danger-dialog-component', compact('title', 'message', 'button', 'messageBox', 'actionLink'));
    }
}
