<?php

namespace App\Filament\Resources;

use App\Models\Driver;
use App\Models\User;
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
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

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
                        TextInput::make('license_number')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ]),

                    Section::make('Additional Information')->schema([
                        TextInput::make('address')
                            ->maxLength(255),
                        DatePicker::make('date_of_birth'),
                        DatePicker::make('license_expiry_date')
                            ->label('License Expiry Date'),
                        TextInput::make('years_of_experience')
                            ->numeric()
                            ->minValue(0),
                    ]),
                ]),

                Grid::make(2)->schema([
                    Section::make('Emergency Contact')->schema([
                        TextInput::make('emergency_contact_name')
                            ->maxLength(255),
                        TextInput::make('emergency_contact_phone')
                            ->tel()
                            ->maxLength(255),
                    ]),

                    Section::make('Status')->schema([
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'on_leave' => 'On Leave',
                                'terminated' => 'Terminated'
                            ])
                            ->default('active')
                            ->required(),
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => auth()?->id())
                            ->dehydrated(true)
                            ->required(),
                    ]),
                ]),

                Section::make('Notes & Image')->schema([
                    Textarea::make('notes')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    FileUpload::make('image_path')
                        ->label('Driver Photo')
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('1:1')
                        ->imageResizeTargetWidth('300')
                        ->imageResizeTargetHeight('300')
                        ->directory('drivers-photos')
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
                ->circular(),
            Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('license_number')
                ->searchable(),
            Tables\Columns\TextColumn::make('phone')
                ->searchable(),
            Tables\Columns\TextColumn::make('email')
                ->searchable(),
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'success' => 'active',
                    'danger' => 'terminated',
                    'warning' => 'inactive',
                    'secondary' => 'on_leave',
                ]),
            Tables\Columns\TextColumn::make('license_expiry_date')
                ->date()
                ->sortable()
                ->label('License Expiry'),
            Tables\Columns\TextColumn::make('company.name')
                ->label('Company')
                ->searchable(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            SelectFilter::make('status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'on_leave' => 'On Leave',
                    'terminated' => 'Terminated'
                ]),
            SelectFilter::make('user_id')
                ->relationship('company', 'name')
                ->label('Company'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('Delete')
                ->action(function (Driver $record) {



                    DB::delete("DELETE FROM drivers WHERE id = ?", [$record->id]);



                })
                ->requiresConfirmation()
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->label('Delete'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\DriverResource\Pages\ListDrivers::route('/'),
            'create' => \App\Filament\Resources\DriverResource\Pages\CreateDriver::route('/create'),
            'edit'   => \App\Filament\Resources\DriverResource\Pages\EditDriver::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('buses');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
