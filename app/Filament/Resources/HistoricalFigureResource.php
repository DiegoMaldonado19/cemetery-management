<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HistoricalFigureResource\Pages;
use App\Filament\Resources\HistoricalFigureResource\RelationManagers;
use App\Models\HistoricalFigure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HistoricalFigureResource extends Resource
{
    protected static ?string $model = HistoricalFigure::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cui')
                    ->maxLength(13)
                    ->default(null),
                Forms\Components\TextInput::make('historical_first_name')
                    ->maxLength(100)
                    ->default(null),
                Forms\Components\TextInput::make('historical_last_name')
                    ->maxLength(100)
                    ->default(null),
                Forms\Components\Textarea::make('historical_reason')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('declaration_date')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cui')
                    ->searchable(),
                Tables\Columns\TextColumn::make('historical_first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('historical_last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('declaration_date')
                    ->date()
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
            'index' => Pages\ListHistoricalFigures::route('/'),
            'create' => Pages\CreateHistoricalFigure::route('/create'),
            'edit' => Pages\EditHistoricalFigure::route('/{record}/edit'),
        ];
    }
}
