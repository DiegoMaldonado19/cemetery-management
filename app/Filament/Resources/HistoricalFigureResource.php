<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HistoricalFigureResource\Pages;
use App\Filament\Resources\HistoricalFigureResource\RelationManagers;
use App\Models\HistoricalFigure;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HistoricalFigureResource extends Resource
{
    protected static ?string $model = HistoricalFigure::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Personas y Usuarios';

    protected static ?string $navigationLabel = 'Personajes Históricos';

    protected static ?string $modelLabel = 'Personaje Histórico';

    protected static ?string $pluralModelLabel = 'Personajes Históricos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos del Personaje Histórico')
                    ->description('Un personaje histórico puede estar asociado a una persona existente o ser registrado como un personaje histórico sin registro personal.')
                    ->schema([
                        Forms\Components\Select::make('cui')
                            ->label('Persona Asociada (Opcional)')
                            ->options(
                                Person::select(DB::raw("CONCAT(first_name, ' ', last_name) AS full_name"), 'cui')
                                    ->pluck('full_name', 'cui')
                            )
                            ->searchable()
                            ->reactive()
                            ->nullable()
                            ->disabled(fn($record) => $record !== null && $record->cui !== null)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $person = Person::find($state);
                                    if ($person) {
                                        $set('historical_first_name', null);
                                        $set('historical_last_name', null);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('historical_first_name')
                            ->label('Nombres (Si no existe registro de la persona)')
                            ->maxLength(100)
                            ->nullable()
                            ->hidden(fn($get) => $get('cui') !== null),

                        Forms\Components\TextInput::make('historical_last_name')
                            ->label('Apellidos (Si no existe registro de la persona)')
                            ->maxLength(100)
                            ->nullable()
                            ->hidden(fn($get) => $get('cui') !== null),
                    ])->columns(2),

                Forms\Components\Section::make('Información Histórica')
                    ->schema([
                        Forms\Components\Textarea::make('historical_reason')
                            ->label('Razón o Motivo Histórico')
                            ->required()
                            ->maxLength(65535),

                        Forms\Components\DatePicker::make('declaration_date')
                            ->label('Fecha de Declaración como Personaje Histórico')
                            ->required()
                            ->default(now()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cui')
                    ->label('CUI/DPI')
                    ->formatStateUsing(fn($state) => $state ?: 'No registrado')
                    ->searchable(),

                Tables\Columns\TextColumn::make('person.first_name')
                    ->label('Nombre registrado')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->person
                            ? $record->person->first_name . ' ' . $record->person->last_name
                            : null
                    )
                    ->placeholder('No aplica')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('historical_first_name')
                    ->label('Nombre histórico')
                    ->formatStateUsing(
                        fn($record) => (!$record->cui && ($record->historical_first_name || $record->historical_last_name))
                            ? ($record->historical_first_name . ' ' . $record->historical_last_name)
                            : null
                    )
                    ->placeholder('No aplica')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('historical_reason')
                    ->label('Motivo Histórico')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('declaration_date')
                    ->label('Fecha de Declaración')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('niches_count')
                    ->label('Nichos Asociados')
                    ->counts('niches')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('with_person')
                    ->label('Con registro de persona')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('cui')),

                Tables\Filters\Filter::make('without_person')
                    ->label('Sin registro de persona')
                    ->query(fn(Builder $query): Builder => $query->whereNull('cui')),

                Tables\Filters\Filter::make('with_niches')
                    ->label('Con nichos asignados')
                    ->query(fn(Builder $query): Builder => $query->has('niches')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin())
                        ->before(function ($records) {
                            // Verificar si algún personaje tiene nichos asociados
                            $withNiches = $records->filter(function ($record) {
                                return $record->niches()->count() > 0;
                            });

                            if ($withNiches->isNotEmpty()) {
                                throw new \Exception('No se pueden eliminar personajes históricos con nichos asociados.');
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\NichesRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHistoricalFigures::route('/'),
            'create' => Pages\CreateHistoricalFigure::route('/create'),
            'view' => Pages\ViewHistoricalFigure::route('/{record}'),
            'edit' => Pages\EditHistoricalFigure::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['person', 'niches']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    // Esta función se ejecuta después de crear un nuevo personaje histórico
    public static function afterCreate(): void
    {
        static::created(function (HistoricalFigure $historicalFigure) {
            // Registramos la creación en el log
            DB::table('change_logs')->insert([
                'table_name' => 'historical_figures',
                'record_id' => $historicalFigure->id,
                'changed_field' => 'creación',
                'old_value' => 'Ninguno',
                'new_value' => 'Nuevo personaje histórico registrado',
                'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    // Esta función se ejecuta después de actualizar un personaje histórico
    public static function afterUpdate(): void
    {
        static::updated(function (HistoricalFigure $historicalFigure) {
            $oldValues = $historicalFigure->getOriginal();
            $newValues = $historicalFigure->getAttributes();

            // Registrar cambios en campos importantes
            if ($oldValues['historical_reason'] != $newValues['historical_reason']) {
                DB::table('change_logs')->insert([
                    'table_name' => 'historical_figures',
                    'record_id' => $historicalFigure->id,
                    'changed_field' => 'motivo_histórico',
                    'old_value' => $oldValues['historical_reason'],
                    'new_value' => $newValues['historical_reason'],
                    'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($oldValues['declaration_date'] != $newValues['declaration_date']) {
                DB::table('change_logs')->insert([
                    'table_name' => 'historical_figures',
                    'record_id' => $historicalFigure->id,
                    'changed_field' => 'fecha_de_declaración',
                    'old_value' => $oldValues['declaration_date'],
                    'new_value' => $newValues['declaration_date'],
                    'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
