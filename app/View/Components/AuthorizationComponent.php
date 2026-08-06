<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class AuthorizationComponent extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        private bool $isAuthorized = false
    )
    {
        $this->isAuthorized = (bool)Auth::user();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.authorization-component', [
            'authorized' => $this->isAuthorized
        ]);
    }
}
