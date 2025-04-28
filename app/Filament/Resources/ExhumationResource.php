<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExhumationResource\Pages;
use App\Filament\Resources\ExhumationResource\RelationManagers;
use App\Models\Exhumation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExhumationResource extends Resource
{
    protected static ?string $model = Exhumation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('contract_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('requester_cui')
                    ->required()
                    ->maxLength(13),
                Forms\Components\DatePicker::make('request_date')
                    ->required(),
                Forms\Components\DatePicker::make('exhumation_date'),
                Forms\Components\Textarea::make('reason')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('agreement_file_path')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('exhumation_status_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requester_cui')
                    ->searchable(),
                Tables\Columns\TextColumn::make('request_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('exhumation_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('agreement_file_path')
                    ->searchable(),
                Tables\Columns\TextColumn::make('exhumation_status_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
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
            'index' => Pages\ListExhumations::route('/'),
            'create' => Pages\CreateExhumation::route('/create'),
            'edit' => Pages\EditExhumation::route('/{record}/edit'),
        ];
    }
}
