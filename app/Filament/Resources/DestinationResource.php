<?php

namespace App\Filament\Resources;

use App\Models\Destination;
use App\Models\Domain;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TagsInput;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Set;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class DestinationResource extends Resource
{
    protected static ?string $model = Destination::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Card::make()->schema([
                Grid::make(2)->schema([
                    Section::make('Basic Information')->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('domain_id')
                            ->label('Domain')
                            ->options(Domain::query()->whereNotNull('name')->get()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Textarea::make('description')
                            ->maxLength(65535)
                            ->rows(3),
                    ]),

                    Section::make('Location Information')->schema([
                        TextInput::make('address')
                            ->maxLength(255),
                        Grid::make(2)->schema([
                            TextInput::make('city')
                                ->maxLength(255),
                            TextInput::make('state')
                                ->maxLength(255),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('country')
                                ->maxLength(255),
                            TextInput::make('postal_code')
                                ->maxLength(20),
                        ]),
                    ]),
                ]),

                Grid::make(2)->schema([
                    Section::make('Coordinates')->schema([
                        Map::make('Map')
                            ->label('Location')
                            ->columnSpanFull()
                            ->draggable(true)
                            ->clickable(true) // click to move marker
                            ->zoom(15)
                            ->minZoom(0)
                            ->maxZoom(28)
                            ->tilesUrl("https://tile.openstreetmap.de/{z}/{x}/{y}.png")
                            // ->detectRetina(true)
                            ->showMarker(true)
                            ->markerColor("#3b82f6")
                            ->markerIconSize([36, 36])
                            ->markerIconAnchor([18, 36])
                            ->showFullscreenControl(true)
                            ->showZoomControl(true)
                            // ->liveLocation(true, true, 5000)
                            ->showMyLocationButton(true)
                            // ->rangeSelectField('distance')
                            // ->geoMan(true)
                            // ->geoManEditable(true)
                            // ->geoManPosition('topleft')
                            // ->drawCircleMarker(true)
                            // ->rotateMode(true)
                            // ->drawMarker(true)
                            // ->drawPolygon(true)
                            // ->drawPolyline(true)
                            // ->drawCircle(true)
                            // ->drawRectangle(true)
                            // ->drawText(true)
                            // ->dragMode(true)
                            // ->cutPolygon(true)
                            // ->editPolygon(true)
                            // ->deleteLayer(true)
                            // ->setColor('#3388ff')
                            // ->setFilledColor('#cad9ec')
                            // ->snappable(true, 20)
                            ->extraStyles([
                                'min-height: 150vh',
                            ])
                            ->afterStateUpdated(function (Set $set, ?array $state): void {
                                $set('latitude', $state['lat']);
                                $set('longitude', $state['lng']);
                                // $set('geojson', json_encode($state['geojson']));
                            })
                            // ->afterStateHydrated(function ($state, $record, Set $set): void {
                            //     $set('location', [
                            //         'lat' => $record->latitude,
                            //         'lng' => $record->longitude,
                            //         'geojson' => json_decode(strip_tags($record->description))
                            //     ]);
                            // })
                            ,
                        TextInput::make('latitude'),
                        TextInput::make('longitude'),
                    ]),
                    Section::make('Contact Information')->schema([
                        TextInput::make('contact_phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('contact_email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('opening_hours')
                            ->maxLength(255)
                            ->placeholder('e.g. Mon-Fri: 9am-5pm'),
                    ]),
                ]),

                Section::make('Additional Information')->schema([
                    TagsInput::make('facilities')
                        ->placeholder('Add facilities like Restrooms, Food Court, Parking, etc.')
                        ->columnSpanFull(),
                    Textarea::make('notes')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    FileUpload::make('image_path')
                        ->label('Destination Image')
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('16:9')
                        ->imageResizeTargetWidth('1200')
                        ->imageResizeTargetHeight('675')
                        ->directory('destinations-images')
                        ->columnSpanFull(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image_path')
                ->label('Image')
                ->square(),
            Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('city')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('country')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('domain.name')
                ->label('Domain')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('address')
                ->searchable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('contact_phone')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('contact_email')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            SelectFilter::make('domain_id')
                ->label('Domain')
                ->relationship('domain', 'name'),
            SelectFilter::make('country')
                ->options(fn() => Destination::pluck('country', 'country')->filter()->unique()->toArray())
                ->searchable(),
            SelectFilter::make('city')
                ->options(fn() => Destination::pluck('city', 'city')->filter()->unique()->toArray())
                ->searchable(),
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
        return [
            \App\Filament\Resources\DestinationResource\RelationManagers\PathsAsStartRelationManager::class,
            \App\Filament\Resources\DestinationResource\RelationManagers\PathsAsEndRelationManager::class,
            // \App\Filament\Resources\DestinationResource\RelationManagers\PathsAsStopRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\DestinationResource\Pages\ListDestinations::route('/'),
            'create' => \App\Filament\Resources\DestinationResource\Pages\CreateDestination::route('/create'),
            'edit'   => \App\Filament\Resources\DestinationResource\Pages\EditDestination::route('/{record}/edit'),
            'view'   => \App\Filament\Resources\DestinationResource\Pages\ViewDestination::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
