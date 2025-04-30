<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Role;
use App\Models\User;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Personas y Usuarios';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Usuario')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de Usuario')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('cui')
                            ->label('Persona Asociada')
                            ->options(
                                Person::select(DB::raw("CONCAT(first_name, ' ', last_name, ' (', cui, ')') AS full_name"), 'cui')
                                    ->pluck('full_name', 'cui')
                            )
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('role_id')
                            ->label('Rol')
                            ->options(Role::pluck('name', 'id'))
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Usuario Activo')
                            ->default(true)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Contraseña')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirmar Contraseña')
                            ->password()
                            ->requiredWith('password')
                            ->same('password')
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre de Usuario')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable(),

                Tables\Columns\TextColumn::make('person.first_name')
                    ->label('Persona Asociada')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->person->first_name . ' ' . $record->person->last_name
                    )
                    ->searchable(),

                Tables\Columns\TextColumn::make('role.name')
                    ->label('Rol')
                    ->badge()
                    ->color(
                        fn(string $state): string =>
                        match ($state) {
                            'Administrador' => 'danger',
                            'Ayudante' => 'warning',
                            'Auditor' => 'success',
                            'Usuario de Consulta' => 'info',
                            default => 'gray',
                        }
                    ),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Último Acceso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_id')
                    ->label('Rol')
                    ->options(Role::pluck('name', 'id')),

                Tables\Filters\Filter::make('is_active')
                    ->label('Usuarios Activos')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true))
                    ->toggle(),

                Tables\Filters\Filter::make('is_inactive')
                    ->label('Usuarios Inactivos')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', false))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => Auth::hasUser() && Auth::user() && Auth::user()->role && Auth::user()->role->name === 'Administrador'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::hasUser() && Auth::user() && Auth::user()->role && Auth::user()->role->name === 'Administrador'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['person', 'role']);
    }

    // Solo los administradores pueden acceder a la gestión de usuarios
    public static function canAccess(): bool
    {
        return Auth::hasUser() && Auth::user() && Auth::user()->role && (Auth::user()->role->name === 'Administrador' || Auth::user()->role->name === 'Auditor');
    }
}
