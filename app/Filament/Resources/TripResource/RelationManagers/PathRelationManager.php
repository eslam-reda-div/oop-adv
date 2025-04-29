<?php

namespace App\Filament\Resources\TripResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Destination;

class PathRelationManager extends RelationManager
{
    protected static string $relationship = 'path';

    protected static ?string $recordTitleAttribute = 'path_code';

    protected static ?string $title = 'Trip Route';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Path Information')
                ->schema([
                    Forms\Components\TextInput::make('path_code')
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->placeholder('Auto-generated if left empty'),
                ]),

            Forms\Components\Section::make('Destinations')
                ->schema([
                    Forms\Components\Select::make('start_destination_id')
                        ->label('Start Destination')
                        ->options(Destination::query()->whereNotNull('name')->get()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('end_destination_id')
                        ->label('End Destination')
                        ->options(Destination::query()->whereNotNull('name')->get()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->different('start_destination_id'),
                ]),

            Forms\Components\Section::make('Path Details')
                ->schema([
                    Forms\Components\TextInput::make('total_distance')
                        ->label('Total Distance (km)')
                        ->numeric()
                        ->minValue(0)
                        ->suffix('km'),
                    Forms\Components\TextInput::make('total_duration')
                        ->label('Total Duration (minutes)')
                        ->numeric()
                        ->minValue(0),
                    Forms\Components\Toggle::make('is_circular')
                        ->label('Circular Route')
                        ->helperText('Route returns to starting point'),
                ]),

            Forms\Components\Section::make('Additional Information')
                ->schema([
                    Forms\Components\Textarea::make('route_description')
                        ->label('Route Description')
                        ->rows(3)
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('route_map_url')
                        ->label('Route Map URL')
                        ->url()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('notes')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('path_code')
                ->searchable()
                ->default('Route Info'),
            Tables\Columns\TextColumn::make('startDestination.name')
                ->label('Start'),
            Tables\Columns\TextColumn::make('endDestination.name')
                ->label('End'),
            Tables\Columns\TextColumn::make('number_of_stops')
                ->label('Stops'),
            Tables\Columns\TextColumn::make('total_distance')
                ->numeric(2)
                ->suffix(' km')
                ->label('Distance'),
            Tables\Columns\TextColumn::make('total_duration')
                ->formatStateUsing(fn ($state): string =>
                    $state ? floor($state / 60) . 'h ' . ($state % 60) . 'm' : '-')
                ->label('Duration'),
            Tables\Columns\IconColumn::make('is_circular')
                ->boolean()
                ->label('Circular'),
        ])
        ->filters([
            //
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make()
                ->visible(fn ($livewire): bool => !$livewire->ownerRecord->path()->exists()),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }
}
