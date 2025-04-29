<?php

namespace App\Filament\Resources\BusResource\RelationManagers;

use App\Models\Bus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class TripsRelationManager extends RelationManager
{
    protected static string $relationship = 'trips';

    protected static ?string $recordTitleAttribute = 'trip_code';

    protected static ?string $title = 'Bus Trips';

    public function form(Form $form): Form
    {
        return $form->schema([
            Card::make()->schema([
                Grid::make(2)->schema([
                    Section::make('Trip Details')->schema([
                        TextInput::make('trip_code')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Auto-generated if left empty'),
                        TextInput::make('available_seats')
                            ->numeric()
                            ->label('Available Seats')
                            ->minValue(0),
                        TextInput::make('booked_seats')
                            ->numeric()
                            ->default(0)
                            ->label('Booked Seats')
                            ->minValue(0),
                    ]),

                    Section::make('Schedule & Price')->schema([
                        DateTimePicker::make('departure_time')
                            ->required()
                            ->seconds(false),
                        DateTimePicker::make('arrival_time')
                            ->required()
                            ->seconds(false)
                            ->after('departure_time'),
                        TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->prefix('$'),
                        Select::make('status')
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
                                // Show relevant fields based on status
                                if ($state === 'delayed') {
                                    $set('delay_reason', '');
                                } else if ($state === 'cancelled') {
                                    $set('cancellation_reason', '');
                                }
                            }),
                    ]),
                ]),

                Grid::make(2)->schema([
                    Section::make('Trip Information')->schema([
                        TextInput::make('distance')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('km')
                            ->label('Distance (km)'),
                        TextInput::make('estimated_duration')
                            ->numeric()
                            ->minValue(0)
                            ->label('Estimated Duration (minutes)'),
                        TextInput::make('fuel_consumption')
                            ->numeric()
                            ->minValue(0)
                            ->label('Estimated Fuel Consumption'),
                    ]),

                    Section::make('Status Information')->schema([
                        Textarea::make('delay_reason')
                            ->placeholder('Reason for delay, if applicable')
                            ->maxLength(65535)
                            ->disabled(fn (callable $get) => $get('status') !== 'delayed')
                            ->dehydrated(fn (callable $get) => $get('status') === 'delayed'),
                        Textarea::make('cancellation_reason')
                            ->placeholder('Reason for cancellation, if applicable')
                            ->maxLength(65535)
                            ->disabled(fn (callable $get) => $get('status') !== 'cancelled')
                            ->dehydrated(fn (callable $get) => $get('status') === 'cancelled'),
                    ]),
                ]),

                Section::make('Additional Notes')->schema([
                    Textarea::make('notes')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ]),
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('bus.driver.name')
                    ->label('Driver')
                    ->searchable(),
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
                        'secondary' => 'delayed',
                    ]),
                Tables\Columns\TextColumn::make('available_seats')
                    ->numeric()
                    ->sortable()
                    ->label('Available'),
                Tables\Columns\TextColumn::make('booked_seats')
                    ->numeric()
                    ->sortable()
                    ->label('Booked'),
                Tables\Columns\TextColumn::make('distance')
                    ->numeric()
                    ->suffix(' km')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estimated_duration')
                    ->label('Duration (min)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'delayed' => 'Delayed',
                    ]),
                Filter::make('departure_date')
                    ->form([
                        Forms\Components\DatePicker::make('departure_from'),
                        Forms\Components\DatePicker::make('departure_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['departure_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('departure_time', '>=', $date),
                            )
                            ->when(
                                $data['departure_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('departure_time', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}