<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeceasedResource\Pages;
use App\Models\Deceased;
use App\Models\DeathCause;
use App\Models\Person;
use App\Models\Department;
use App\Models\Gender;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DeceasedResource extends Resource
{
    protected static ?string $model = Deceased::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Personas y Usuarios';

    protected static ?string $navigationLabel = 'Fallecidos';

    protected static ?string $modelLabel = 'Fallecido';

    protected static ?string $pluralModelLabel = 'Fallecidos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Persona Existente')
                            ->schema([
                                Forms\Components\Select::make('cui')
                                    ->label('Persona')
                                    ->options(function () {
                                        // Asegúrate de que estos namespaces estén importados al principio del archivo
                                        // use App\Models\Person;
                                        // use Illuminate\Support\Facades\DB;

                                        return Person::whereNotIn('cui', function ($query) {
                                                $query->select('cui')->from('deceased');
                                            })
                                            ->get()
                                            ->mapWithKeys(function ($person) {
                                                return [$person->cui => "{$person->first_name} {$person->last_name} ({$person->cui})"];
                                            });
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, callable $set) => $set('use_existing_person', !empty($state)))
                                    ->required(fn(callable $get) => $get('use_existing_person') === true),
                            ]),
                        Forms\Components\Tabs\Tab::make('Nueva Persona')
                            ->schema([
                                Forms\Components\TextInput::make('new_cui')
                                    ->label('CUI / DPI')
                                    ->required(fn(callable $get) => $get('use_existing_person') === false)
                                    ->maxLength(13)
                                    ->unique(table: 'people', column: 'cui')
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, callable $set) => $set('use_existing_person', empty($state))),

                                Forms\Components\TextInput::make('first_name')
                                    ->label('Nombres')
                                    ->required(fn(callable $get) => $get('use_existing_person') === false)
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('last_name')
                                    ->label('Apellidos')
                                    ->required(fn(callable $get) => $get('use_existing_person') === false)
                                    ->maxLength(100),

                                Forms\Components\Select::make('gender_id')
                                    ->label('Género')
                                    ->options(Gender::pluck('name', 'id'))
                                    ->required(fn(callable $get) => $get('use_existing_person') === false),

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
                                            ->required(fn(callable $get) => $get('use_existing_person') === false),

                                        Forms\Components\TextInput::make('address_line')
                                            ->label('Dirección')
                                            ->required(fn(callable $get) => $get('use_existing_person') === false)
                                            ->maxLength(255),

                                        Forms\Components\Textarea::make('reference')
                                            ->label('Referencia')
                                            ->maxLength(65535),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull(),

                Forms\Components\Hidden::make('use_existing_person')
                    ->default(true),

                Forms\Components\Section::make('Información del Fallecimiento')
                    ->schema([
                        Forms\Components\DatePicker::make('death_date')
                            ->label('Fecha de Fallecimiento')
                            ->required(),

                        Forms\Components\Select::make('death_cause_id')
                            ->label('Causa de Muerte')
                            ->options(DeathCause::pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('origin')
                            ->label('Procedencia')
                            ->maxLength(100),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('person.first_name')
                    ->label('Nombre')
                    ->formatStateUsing(function ($record) {
                        return $record->person->first_name . ' ' . $record->person->last_name;
                    })
                    ->searchable(['people.first_name', 'people.last_name']),
                Tables\Columns\TextColumn::make('person.cui')
                    ->label('CUI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('death_date')
                    ->label('Fecha de Fallecimiento')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deathCause.name')
                    ->label('Causa de Muerte')
                    ->sortable(),
                Tables\Columns\TextColumn::make('origin')
                    ->label('Procedencia')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('death_cause_id')
                    ->label('Causa de Muerte')
                    ->options(DeathCause::pluck('name', 'id')),
                Tables\Filters\Filter::make('death_date')
                    ->form([
                        Forms\Components\DatePicker::make('death_date_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('death_date_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['death_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('death_date', '>=', $date),
                            )
                            ->when(
                                $data['death_date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('death_date', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => Auth::hasUser() && (Auth::user()->isAdmin())),
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
            'index' => Pages\ListDeceaseds::route('/'),
            'create' => Pages\CreateDeceased::route('/create'),
            'edit' => Pages\EditDeceased::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['person', 'deathCause']);
    }
}
