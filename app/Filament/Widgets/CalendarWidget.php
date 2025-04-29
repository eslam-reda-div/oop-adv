<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TripResource;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Model;
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

class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Trip::class;

    public function getFormSchema(): array
    {
        return [
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
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return Trip::query()
            ->where('departure_time', '>=', $fetchInfo['start'])
            ->where('arrival_time', '<=', $fetchInfo['end'])
            ->get()
            ->map(
                fn (Trip $event) => [
                    'title' => $event->trip_code,
                    'start' => $event->departure_time,
                    'end' => $event->arrival_time,
                    'url' => TripResource::getUrl(name: 'view', parameters: ['record' => $event]),
                    'shouldOpenUrlInNewTab' => true
                ]
            )
            ->all();
    }
}
