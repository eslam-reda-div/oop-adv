<?php

namespace App\Filament\Resources;

use App\Models\Domain;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

class DomainResource extends Resource
{
    protected static ?string $model = Domain::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

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
                        Textarea::make('description')
                            ->nullable()
                            ->rows(3),
                        TextInput::make('region')
                            ->maxLength(255),
                        TextInput::make('country')
                            ->maxLength(255),
                    ]),

                    Section::make('Visual Settings')->schema([
                        ColorPicker::make('color_code')
                            ->label('Color')
                            ->rgba(),
                        TextInput::make('icon')
                            ->maxLength(255)
                            ->placeholder('Enter icon name or class'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        FileUpload::make('image_path')
                            ->label('Domain Image')
                            ->image()
                            ->directory('domains-images'),
                    ]),
                ]),

                Section::make('Contact Information')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('contact_person')
                            ->maxLength(255),
                        TextInput::make('contact_email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('contact_phone')
                            ->tel()
                            ->maxLength(255),
                    ]),
                ]),

                Section::make('Additional Information')->schema([
                    Textarea::make('notes')
                        ->maxLength(65535)
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
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
            TextColumn::make('region')
                ->searchable()
                ->sortable(),
            TextColumn::make('country')
                ->searchable()
                ->sortable(),
            TextColumn::make('destination_count')
                ->label('Destinations')
                ->counts('destinations')
                ->sortable(),
            IconColumn::make('is_active')
                ->boolean()
                ->label('Active'),
            TextColumn::make('contact_person')
                ->searchable()
                ->toggleable(),
            TextColumn::make('contact_email')
                ->searchable()
                ->toggleable(),
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            TernaryFilter::make('is_active')
                ->label('Active'),
            SelectFilter::make('country')
                ->options(fn() => Domain::pluck('country', 'country')->filter()->unique()->toArray())
                ->searchable(),
            SelectFilter::make('region')
                ->options(fn() => Domain::pluck('region', 'region')->filter()->unique()->toArray())
                ->searchable(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
            Tables\Actions\ViewAction::make(),
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
            \App\Filament\Resources\DomainResource\RelationManagers\DestinationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\DomainResource\Pages\ListDomains::route('/'),
            'create' => \App\Filament\Resources\DomainResource\Pages\CreateDomain::route('/create'),
            'edit'   => \App\Filament\Resources\DomainResource\Pages\EditDomain::route('/{record}/edit'),
            'view'   => \App\Filament\Resources\DomainResource\Pages\ViewDomain::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('destinations');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }
}
