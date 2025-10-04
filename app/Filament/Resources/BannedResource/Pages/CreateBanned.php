<?php

namespace App\Filament\Resources\BannedResource\Pages;

use App\Filament\Resources\BannedResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBanned extends CreateRecord
{
    protected static string $resource = BannedResource::class;
}
