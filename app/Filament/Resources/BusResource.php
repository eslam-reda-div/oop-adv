<?php

namespace App\Filament\Resources;

use App\Models\Bus;
use App\Models\User;
use App\Models\Driver;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TagsInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class BusResource extends Resource
{
    protected static ?string $model = Bus::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $recordTitleAttribute = 'bus_number';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Card::make()->schema([
                Grid::make(2)->schema([
                    Section::make('Basic Information')->schema([
                        TextInput::make('bus_number')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('capacity')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        TextInput::make('model')
                            ->maxLength(255),
                        TextInput::make('manufacturer')
                            ->maxLength(255),
                    ]),

                    Section::make('Registration & Identification')->schema([
                        TextInput::make('license_plate')
                            ->maxLength(255),
                        TextInput::make('year_of_manufacture')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y') + 1),
                        DatePicker::make('registration_expiry')
                            ->label('Registration Expiry Date'),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'maintenance' => 'Under Maintenance',
                                'out_of_service' => 'Out of Service',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
                ]),

                Grid::make(2)->schema([
                    Section::make('Maintenance Information')->schema([
                        DatePicker::make('last_maintenance_date')
                            ->label('Last Maintenance Date'),
                        DatePicker::make('next_maintenance_date')
                            ->label('Next Maintenance Date')
                            ->afterOrEqual('last_maintenance_date'),
                    ]),

                    Section::make('Fuel Information')->schema([
                        Select::make('fuel_type')
                            ->options([
                                'diesel' => 'Diesel',
                                'petrol' => 'Petrol/Gasoline',
                                'electric' => 'Electric',
                                'hybrid' => 'Hybrid',
                                'cng' => 'Compressed Natural Gas (CNG)',
                                'lpg' => 'Liquefied Petroleum Gas (LPG)',
                            ]),
                        TextInput::make('fuel_efficiency')
                            ->numeric()
                            ->minValue(0)
                            ->label('Fuel Efficiency (km/l)'),
                    ]),
                ]),

                Section::make('Assignment')->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => auth()->id()),
                        Select::make('driver_id')
                            ->label('Driver')
                            ->columnSpanFull()
                            ->options(Driver::query()->whereNotNull('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ]),
                ]),

                Section::make('Features & Notes')->schema([
                    TagsInput::make('features')
                        ->placeholder('Add features like WiFi, AC, USB Charging, etc.')
                        ->columnSpanFull(),
                    Textarea::make('notes')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    FileUpload::make('image_path')
                        ->label('Bus Photo')
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('16:9')
                        ->imageResizeTargetWidth('1200')
                        ->imageResizeTargetHeight('675')
                        ->directory('buses-photos')
                        ->columnSpanFull(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image_path')
                ->label('Photo')
                ->square(),
            Tables\Columns\TextColumn::make('bus_number')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('model')
                ->searchable(),
            Tables\Columns\TextColumn::make('manufacturer')
                ->searchable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('capacity')
                ->numeric()
                ->sortable(),
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'success' => 'active',
                    'warning' => 'maintenance',
                    'danger' => 'out_of_service',
                ]),
            Tables\Columns\TextColumn::make('driver.name')
                ->label('Driver')
                ->searchable(),
            Tables\Columns\TextColumn::make('company.name')
                ->label('Company')
                ->searchable(),
            Tables\Columns\TextColumn::make('registration_expiry')
                ->date()
                ->sortable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('next_maintenance_date')
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            SelectFilter::make('status')
                ->options([
                    'active' => 'Active',
                    'maintenance' => 'Under Maintenance',
                    'out_of_service' => 'Out of Service',
                ]),
            SelectFilter::make('driver_id')
                ->relationship('driver', 'name')
                ->label('Driver'),
            SelectFilter::make('user_id')
                ->relationship('company', 'name')
                ->label('Company'),
        ])
        ->actions([
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
            \App\Filament\Resources\BusResource\RelationManagers\TripsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\BusResource\Pages\ListBuses::route('/'),
            'create' => \App\Filament\Resources\BusResource\Pages\CreateBus::route('/create'),
            'edit'   => \App\Filament\Resources\BusResource\Pages\EditBus::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('trips');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
