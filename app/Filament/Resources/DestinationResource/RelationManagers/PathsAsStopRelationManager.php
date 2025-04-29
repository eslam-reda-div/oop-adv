<?php

namespace App\Filament\Resources\DestinationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Path;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PathsAsStopRelationManager extends RelationManager
{
    protected static string $relationship = 'pathsAsStop';

    protected static ?string $recordTitleAttribute = 'path_code';

    protected static ?string $title = 'Paths With This Stop';

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('path_code')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('startDestination.name')
                ->label('Origin')
                ->searchable(),
            Tables\Columns\TextColumn::make('endDestination.name')
                ->label('Destination')
                ->searchable(),
            Tables\Columns\TextColumn::make('pivot.stop_order')
                ->label('Stop #')
                ->sortable(),
            Tables\Columns\TextColumn::make('pivot.estimated_arrival_time')
                ->label('Arrival')
                ->time(),
            Tables\Columns\TextColumn::make('pivot.estimated_departure_time')
                ->label('Departure')
                ->time(),
            Tables\Columns\TextColumn::make('pivot.stop_duration')
                ->label('Stop Duration')
                ->formatStateUsing(fn ($state): string =>
                    $state ? $state . ' min' : '-'),
            Tables\Columns\IconColumn::make('pivot.is_pickup_point')
                ->boolean()
                ->label('Pickup'),
            Tables\Columns\IconColumn::make('pivot.is_dropoff_point')
                ->boolean()
                ->label('Dropoff'),
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
        ->defaultSort('pivot.stop_order');
    }
}
