<?php

namespace App\Filament\Resources\PathResource\Pages;

use App\Filament\Resources\PathResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPath extends ViewRecord
{
    protected static string $resource = PathResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}