<?php

namespace App\Filament\Resources\TopicsCategoryResource\Pages;

use App\Filament\Resources\TopicsCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTopicsCategory extends EditRecord
{
    protected static string $resource = TopicsCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
