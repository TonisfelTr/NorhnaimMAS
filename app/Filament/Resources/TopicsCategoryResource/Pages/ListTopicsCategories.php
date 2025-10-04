<?php

namespace App\Filament\Resources\TopicsCategoryResource\Pages;

use App\Filament\Resources\TopicsCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTopicsCategories extends ListRecords
{
    protected static string $resource = TopicsCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
