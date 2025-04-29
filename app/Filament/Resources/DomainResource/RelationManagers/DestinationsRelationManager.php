<?php

namespace App\Filament\Resources\DomainResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DestinationsRelationManager extends RelationManager
{
    protected static string $relationship = 'destinations';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Domain Destinations';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Basic Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(65535)
                        ->rows(3),
                ]),

            Forms\Components\Section::make('Location Information')
                ->schema([
                    Forms\Components\TextInput::make('address')
                        ->maxLength(255),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('city')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('state')
                                ->maxLength(255),
                        ]),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('country')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('postal_code')
                                ->maxLength(20),
                        ]),
                ]),

            Forms\Components\Section::make('Coordinates')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('latitude')
                                ->numeric()
                                ->minValue(-90)
                                ->maxValue(90)
                                ->step(0.0000001),
                            Forms\Components\TextInput::make('longitude')
                                ->numeric()
                                ->minValue(-180)
                                ->maxValue(180)
                                ->step(0.0000001),
                        ]),
                ]),

            Forms\Components\Section::make('Contact Information')
                ->schema([
                    Forms\Components\TextInput::make('contact_phone')
                        ->tel()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('contact_email')
                        ->email()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('opening_hours')
                        ->maxLength(255)
                        ->placeholder('e.g. Mon-Fri: 9am-5pm'),
                ]),

            Forms\Components\Section::make('Additional Information')
                ->schema([
                    Forms\Components\TagsInput::make('facilities')
                        ->placeholder('Add facilities like Restrooms, Food Court, Parking, etc.'),
                    Forms\Components\Textarea::make('notes')
                        ->maxLength(65535),
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Destination Image')
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('16:9')
                        ->imageResizeTargetWidth('1200')
                        ->imageResizeTargetHeight('675')
                        ->directory('destinations-images'),
                ]),
        ]);
    }

    public function table(Table $table): Table
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
            Tables\Filters\SelectFilter::make('city')
                ->options(fn ($livewire) => $livewire->ownerRecord->destinations()
                    ->pluck('city', 'city')
                    ->filter()
                    ->unique()
                    ->toArray())
                ->searchable(),
            Tables\Filters\SelectFilter::make('country')
                ->options(fn ($livewire) => $livewire->ownerRecord->destinations()
                    ->pluck('country', 'country')
                    ->filter()
                    ->unique()
                    ->toArray())
                ->searchable(),
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data) {
                    $data['domain_id'] = $this->ownerRecord->id;
                    return $data;
                }),
        ])
        ->actions([
            Tables\Actions\ViewAction::make()
                ->url(fn ($record) => route('filament.admin.resources.destinations.view', $record)),
            Tables\Actions\EditAction::make()
                ->url(fn ($record) => route('filament.admin.resources.destinations.edit', $record)),
            Tables\Actions\DeleteAction::make()
                ->after(function ($livewire) {
                    // Update destination count after deletion
                    $livewire->ownerRecord->updateDestinationCount();
                }),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->after(function ($livewire) {
                        // Update destination count after bulk deletion
                        $livewire->ownerRecord->updateDestinationCount();
                    }),
            ]),
        ])
        ->defaultSort('name');
    }

    protected function afterCreate(): void
    {
        // Update destination count after creation
        $this->ownerRecord->updateDestinationCount();
    }
}
