<?php

namespace App\Observers;

use App\Services\ClinicalRecordOriginService;
use Illuminate\Database\Eloquent\Model;

class ClinicalRecordOriginObserver
{
    public function created(Model $record): void
    {
        app(ClinicalRecordOriginService::class)
            ->captureRecord($record);
    }
}
