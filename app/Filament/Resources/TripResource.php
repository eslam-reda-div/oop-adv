<?php

namespace App\Filament\Resources;

use App\Models\Trip;
use App\Models\Bus;
use App\Models\Path;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $recordTitleAttribute = 'trip_code';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Card::make()->schema([
                Grid::make(2)->schema([
                    Section::make('Trip Details')->schema([
                        TextInput::make('trip_code')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Auto-generated if left empty'),
                        Select::make('bus_id')
                            ->label('Bus')
                            ->options(Bus::query()->whereNotNull('bus_number')->get()->pluck('bus_number', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->default(function() {
                                $busId = request()->query('bus_id');
                                return $busId ? (int) $busId : null;
                            })
                            ->afterStateHydrated(function ($state, callable $set) {
                                if ($state) {
                                    $set('available_seats', Bus::find($state)?->capacity ?? null);
                                }
                            })
                            ->afterStateUpdated(fn ($state, callable $set) =>
                                $set('available_seats', Bus::find($state)?->capacity ?? null)),
                        Select::make('path_id')
                            ->label('Path')
                            ->options(function () {
                                return Path::all()->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $path = Path::find($state);
                                    if ($path) {
                                        $set('distance', $path->total_distance);
                                        $set('estimated_duration', $path->total_duration);
                                    }
                                }
                            }),
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

    public static function table(Table $table): Table
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
                Tables\Columns\TextColumn::make('path.name')
                    ->label('Path')
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
                SelectFilter::make('bus_id')
                    ->relationship('bus', 'bus_number')
                    ->label('Bus'),
                SelectFilter::make('path_id')
                    ->relationship('path', 'name')
                    ->label('Path'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\TripResource\Pages\ListTrips::route('/'),
            'create' => \App\Filament\Resources\TripResource\Pages\CreateTrip::route('/create'),
            'edit'   => \App\Filament\Resources\TripResource\Pages\EditTrip::route('/{record}/edit'),
            'view'   => \App\Filament\Resources\TripResource\Pages\ViewTrip::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['bus', 'bus.driver', 'path']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'scheduled')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
