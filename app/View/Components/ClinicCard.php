<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ClinicCard extends Component
{
    public function __construct(public string $clinic_name = '', public string $clinic_description = '')
    {
    }

    public function render(): View|Closure|string {
        return view('components.clinic-card', [
            'clinic_name' => $this->clinic_name,
            'clinic_description' => $this->clinic_description
        ]);
    }
}
