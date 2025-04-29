<?php

namespace App\Filament\Resources\DestinationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Destination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PathsAsStartRelationManager extends RelationManager
{
    protected static string $relationship = 'pathsAsStart';

    protected static ?string $recordTitleAttribute = 'path_code';

    protected static ?string $title = 'Paths Starting Here';

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('path_code')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('endDestination.name')
                ->label('Destination')
                ->searchable(),
            Tables\Columns\TextColumn::make('trip.trip_code')
                ->label('Trip')
                ->searchable(),
            Tables\Columns\TextColumn::make('trip.departure_time')
                ->label('Departure')
                ->dateTime()
                ->sortable(),
            Tables\Columns\TextColumn::make('total_distance')
                ->numeric(2)
                ->suffix(' km')
                ->sortable(),
            Tables\Columns\TextColumn::make('total_duration')
                ->formatStateUsing(fn ($state): string =>
                    $state ? floor($state / 60) . 'h ' . ($state % 60) . 'm' : '-')
                ->label('Duration'),
            Tables\Columns\TextColumn::make('number_of_stops')
                ->label('Stops')
                ->sortable(),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\ViewAction::make()
                ->url(fn ($record) => route('filament.admin.resources.paths.view', $record)),
        ])
        ->bulkActions([
            // No bulk actions needed for this view
        ])
        ->defaultSort('trip.departure_time');
    }
}
