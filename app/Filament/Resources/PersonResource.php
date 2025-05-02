<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Filament\Resources\PersonResource\RelationManagers;
use App\Models\Department;
use App\Models\Gender;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $recordTitleAttribute = 'full_name';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Personas y Usuarios';

    protected static ?string $navigationLabel = 'Personas';

    protected static ?string $modelLabel = 'Persona';

    protected static ?string $pluralModelLabel = 'Personas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cui')
                    ->label('CUI / DPI')
                    ->required()
                    ->maxLength(13)
                    ->unique(ignoreRecord: true)
                    ->disabled(fn ($record) => $record !== null),
                Forms\Components\TextInput::make('first_name')
                    ->label('Nombres')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('last_name')
                    ->label('Apellidos')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Select::make('gender_id')
                    ->label('Género')
                    ->options(Gender::pluck('name', 'id'))
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->email()
                    ->maxLength(100),
                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(20),

                Forms\Components\Section::make('Dirección Principal')
                    ->schema([
                        Forms\Components\Select::make('department_id')
                            ->label('Departamento')
                            ->options(Department::pluck('name', 'id'))
                            ->required(),
                        Forms\Components\TextInput::make('address_line')
                            ->label('Dirección')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('reference')
                            ->label('Referencia')
                            ->maxLength(65535),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cui')
                    ->label('CUI / DPI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Nombres')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Apellidos')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender.name')
                    ->label('Género')
                    ->badge(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable(),
                Tables\Columns\IconColumn::make('deceased')
                    ->label('Fallecido')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->deceased !== null)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\IconColumn::make('historicalFigure')
                    ->label('Personaje Histórico')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->historicalFigure !== null)
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender_id')
                    ->label('Género')
                    ->options(Gender::pluck('name', 'id')),
                Tables\Filters\Filter::make('deceased')
                    ->label('Fallecidos')
                    ->query(fn (Builder $query): Builder => $query->whereHas('deceased')),
                Tables\Filters\Filter::make('historical')
                    ->label('Personajes Históricos')
                    ->query(fn (Builder $query): Builder => $query->whereHas('historicalFigure')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
                Tables\Actions\Action::make('registerDeceased')
                    ->label('Registrar Fallecimiento')
                    ->icon('heroicon-o-document-plus')
                    ->color('danger')
                    ->url(fn (Person $record) => route('filament.admin.resources.people.deceased.create', $record))
                    ->visible(fn (Person $record) =>
                        (Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())) &&
                        $record->deceased === null
                    ),
                Tables\Actions\Action::make('registerHistorical')
                    ->label('Registrar como Histórico')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->url(fn (Person $record) => route('filament.admin.resources.people.historical.create', $record))
                    ->visible(fn (Person $record) =>
                        Auth::hasUser() && Auth::user()->isAdmin() &&
                        $record->historicalFigure === null
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::hasUser() && Auth::user()->isAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AddressesRelationManager::make(),
            RelationManagers\ResponsibleContractsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'view' => Pages\ViewPerson::route('/{record}'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
            'deceased.create' => Pages\RegisterDeceased::route('/{record}/deceased/create'),
            'historical.create' => Pages\RegisterHistorical::route('/{record}/historical/create'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['gender', 'addresses', 'deceased', 'historicalFigure']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function afterCreate(Person $record, array $data): void
    {
        if (isset($data['address_line']) && !empty($data['address_line'])) {
            $record->addresses()->create([
                'cui' => $record->cui,
                'department_id' => $data['department_id'] ?? 1,
                'address_line' => $data['address_line'],
                'reference' => $data['reference'] ?? null,
                'is_primary' => true,
            ]);
        }
    }
}
