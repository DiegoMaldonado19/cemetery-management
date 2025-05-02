<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeceasedResource\Pages;
use App\Models\Deceased;
use App\Models\DeathCause;
use App\Models\Person;
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
                Forms\Components\Select::make('cui')
                    ->label('Persona')
                    ->options(Person::select(DB::raw("CONCAT(first_name, ' ', last_name, ' (', cui, ')') AS full_name"), 'cui')
                        ->whereNotIn('cui', function ($query) {
                            $query->select('cui')->from('deceased');
                        })
                        ->pluck('full_name', 'cui'))
                    ->searchable()
                    ->required(),
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
                    ->maxLength(65535),
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
