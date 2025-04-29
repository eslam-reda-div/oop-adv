<?php

namespace App\Filament\Resources\PathResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Bus;

class TripsRelationManager extends RelationManager
{
    protected static string $relationship = 'trips';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Trip Details')
                ->schema([
                    Forms\Components\TextInput::make('trip_code')
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->placeholder('Auto-generated if left empty'),
                    Forms\Components\Select::make('bus_id')
                        ->label('Bus')
                        ->options(Bus::query()->whereNotNull('bus_number')->get()->pluck('bus_number', 'id'))
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) =>
                            $set('available_seats', Bus::find($state)?->capacity ?? null)),
                    Forms\Components\TextInput::make('available_seats')
                        ->numeric()
                        ->label('Available Seats')
                        ->minValue(0),
                    Forms\Components\TextInput::make('booked_seats')
                        ->numeric()
                        ->default(0)
                        ->label('Booked Seats')
                        ->minValue(0),
                ]),

                Forms\Components\Section::make('Schedule & Price')
                ->schema([
                    Forms\Components\DateTimePicker::make('departure_time')
                        ->required()
                        ->seconds(false),
                    Forms\Components\DateTimePicker::make('arrival_time')
                        ->required()
                        ->seconds(false)
                        ->after('departure_time'),
                    Forms\Components\TextInput::make('price')
                        ->numeric()
                        ->required()
                        ->prefix('$'),
                    Forms\Components\Select::make('status')
                        ->options([
                            'scheduled' => 'Scheduled',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                            'delayed' => 'Delayed',
                        ])
                        ->default('scheduled')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state === 'delayed') {
                                $set('delay_reason', '');
                            } else if ($state === 'cancelled') {
                                $set('cancellation_reason', '');
                            }
                        }),
                ]),

                Forms\Components\Section::make('Trip Information')
                ->schema([
                    Forms\Components\TextInput::make('fuel_consumption')
                        ->numeric()
                        ->minValue(0)
                        ->label('Estimated Fuel Consumption'),
                    Forms\Components\Textarea::make('notes')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('trip_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bus.bus_number')
                    ->label('Bus')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departure_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('arrival_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'primary' => 'scheduled',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'warning' => 'delayed',
                    ]),
                Tables\Columns\TextColumn::make('available_seats')
                    ->numeric()
                    ->sortable()
                    ->label('Available'),
                Tables\Columns\TextColumn::make('booked_seats')
                    ->numeric()
                    ->sortable()
                    ->label('Booked'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'delayed' => 'Delayed',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('departure_time');
    }
}
