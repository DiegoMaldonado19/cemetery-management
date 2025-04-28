<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NicheResource\Pages;
use App\Filament\Resources\NicheResource\RelationManagers;
use App\Models\Niche;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NicheResource extends Resource
{
    protected static ?string $model = Niche::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('street_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('avenue_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('location_reference')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('niche_type_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('niche_status_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('historical_figure_id')
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('street_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('avenue_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('niche_type_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('niche_status_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('historical_figure_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListNiches::route('/'),
            'create' => Pages\CreateNiche::route('/create'),
            'edit' => Pages\EditNiche::route('/{record}/edit'),
        ];
    }
}
