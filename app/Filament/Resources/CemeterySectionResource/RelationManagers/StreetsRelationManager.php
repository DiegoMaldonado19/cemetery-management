<?php

namespace App\Filament\Resources\CemeterySectionResource\RelationManagers;

use App\Models\CemeteryStreet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StreetsRelationManager extends RelationManager
{
    protected static string $relationship = 'streets';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Calles';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->maxLength(100),
                Forms\Components\TextInput::make('street_number')
                    ->label('Número de Calle')
                    ->required()
                    ->numeric()
                    ->minValue(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->placeholder('Sin nombre'),
                Tables\Columns\TextColumn::make('street_number')
                    ->label('Número de Calle')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('niches_count')
                    ->label('Nichos')
                    ->counts('niches')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn() => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin()),
                ]),
            ]);
    }
}
