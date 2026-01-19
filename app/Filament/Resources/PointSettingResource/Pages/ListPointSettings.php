<?php

namespace App\Filament\Resources\PointSettingResource\Pages;

use App\Filament\Resources\PointSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPointSettings extends ListRecords
{
    protected static string $resource = PointSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
