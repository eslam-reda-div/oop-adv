<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use App\Models\Driver;
use Filament\Actions;
use Filament\Forms\Components\Builder;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;

class ListDrivers extends ListRecords
{
    protected static string $resource = DriverResource::class;

    protected function getTableQuery(): EloquentBuilder
    {
        $query = "
            select
                `drivers`.*,
                (
                    select
                        count(*)
                    from
                        `buses`
                    where
                        `drivers`.`id` = `buses`.`driver_id`
                ) as `buses_count`
            from
            `drivers`
        ";

        $rawResults = DB::select($query);
        $driverIds = collect($rawResults)->pluck('id')->toArray();
        return Driver::query()->withCount('buses')->whereIn('id', $driverIds);
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
