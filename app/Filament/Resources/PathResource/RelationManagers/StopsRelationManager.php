<?php

namespace App\Filament\Resources\PathResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Destination;

class StopsRelationManager extends RelationManager
{
    protected static string $relationship = 'stops';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Stop Details')
                ->schema([
                    Forms\Components\Select::make('destination_id')
                        ->label('Destination')
                        ->options(Destination::query()->whereNotNull('name')->get()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('stop_order')
                        ->label('Stop Order')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                    Forms\Components\TimePicker::make('estimated_arrival_time')
                        ->label('Estimated Arrival Time')
                        ->seconds(false),
                    Forms\Components\TimePicker::make('estimated_departure_time')
                        ->label('Estimated Departure Time')
                        ->seconds(false)
                        ->after('estimated_arrival_time'),
                ]),

            Forms\Components\Section::make('Stop Information')
                ->schema([
                    Forms\Components\TextInput::make('stop_duration')
                        ->label('Stop Duration (minutes)')
                        ->numeric()
                        ->minValue(0)
                        ->hint('Time spent at this stop'),
                    Forms\Components\TextInput::make('distance_from_previous')
                        ->label('Distance from Previous (km)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->suffix('km'),
                    Forms\Components\TextInput::make('time_from_previous')
                        ->label('Time from Previous (minutes)')
                        ->numeric()
                        ->minValue(0),
                    Forms\Components\Textarea::make('stop_notes')
                        ->label('Notes')
                        ->rows(3)
                        ->maxLength(65535),
                ]),

            Forms\Components\Section::make('Pickup & Dropoff')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('is_pickup_point')
                                ->label('Is Pickup Point')
                                ->default(true),
                            Forms\Components\Toggle::make('is_dropoff_point')
                                ->label('Is Dropoff Point')
                                ->default(true),
                        ]),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('stop_order')
                ->label('Order')
                ->sortable(),
            Tables\Columns\TextColumn::make('destination.name')
                ->label('Destination')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('estimated_arrival_time')
                ->label('Arrival')
                ->time(),
            Tables\Columns\TextColumn::make('estimated_departure_time')
                ->label('Departure')
                ->time(),
            Tables\Columns\TextColumn::make('stop_duration')
                ->label('Duration (min)')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('distance_from_previous')
                ->label('Distance (km)')
                ->numeric(2)
                ->suffix(' km')
                ->sortable(),
            Tables\Columns\IconColumn::make('is_pickup_point')
                ->label('Pickup')
                ->boolean(),
            Tables\Columns\IconColumn::make('is_dropoff_point')
                ->label('Dropoff')
                ->boolean(),
        ])
        ->defaultSort('stop_order')
        ->filters([
            //
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make()
                ->after(function ($livewire) {
                    // Recalculate path metrics and update number of stops
                    $livewire->ownerRecord->updateNumberOfStops();
                    $livewire->ownerRecord->calculatePathMetrics();
                }),
        ])
        ->actions([
            Tables\Actions\EditAction::make()
                ->after(function ($livewire) {
                    // Recalculate path metrics after editing
                    $livewire->ownerRecord->calculatePathMetrics();
                }),
            Tables\Actions\DeleteAction::make()
                ->after(function ($livewire) {
                    // Recalculate path metrics and update number of stops
                    $livewire->ownerRecord->updateNumberOfStops();
                    $livewire->ownerRecord->calculatePathMetrics();
                }),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->after(function ($livewire) {
                        // Recalculate path metrics and update number of stops
                        $livewire->ownerRecord->updateNumberOfStops();
                        $livewire->ownerRecord->calculatePathMetrics();
                    }),
            ]),
        ]);
    }
}
