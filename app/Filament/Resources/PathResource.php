<?php

namespace App\Filament\Resources;

use App\Models\Path;
use App\Models\Destination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PathResource\Pages;
use App\Filament\Resources\PathResource\RelationManagers;

class PathResource extends Resource
{
    protected static ?string $model = Path::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $recordTitleAttribute = 'path_code';

    public static function getNavigationBadge(): ?string
    {
        return Path::count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Path Information')->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('path_code')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('name')
                        ->label('Path Name')
                        ->maxLength(255)
                        ->required(),
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
                        ->different('start_destination_id')
                        ->suffix('km'),
                    Forms\Components\TextInput::make('total_duration')
                        ->required()
                        ->numeric()
                        ->suffix('min'),
                    Forms\Components\TextInput::make('total_distance')
                        ->required()
                        ->numeric()
                        ->suffix('km'),
                ])
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('path_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('startDestination.name')
                    ->label('Start Point')
                    ->searchable(),
                Tables\Columns\TextColumn::make('endDestination.name')
                    ->label('End Point')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_duration')
                    ->numeric()
                    ->suffix(' min')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_distance')
                    ->numeric()
                    ->suffix(' km')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\TripsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaths::route('/'),
            'create' => Pages\CreatePath::route('/create'),
            'edit' => Pages\EditPath::route('/{record}/edit'),
            'view' => Pages\ViewPath::route('/{record}'),
        ];
    }
}
