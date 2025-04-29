<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDriver extends EditRecord
{
    protected static string $resource = DriverResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $query = "UPDATE drivers SET ";
        $params = [];

        foreach ($data as $column => $value) {
            $query .= "$column = ?, ";
            $params[] = $value;
        }

        $query = rtrim($query, ', ');

        $query .= " WHERE id = ?";
        $params[] = $record->id;

        \Illuminate\Support\Facades\DB::statement($query, $params);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
