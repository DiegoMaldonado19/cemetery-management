<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CemeteryBlockResource\Pages;
use App\Filament\Resources\CemeteryBlockResource\RelationManagers;
use App\Models\CemeteryBlock;
use App\Models\CemeterySection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CemeteryBlockResource extends Resource
{
    protected static ?string $model = CemeteryBlock::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Bloques del Cementerio';

    protected static ?string $modelLabel = 'Bloque';

    protected static ?string $pluralModelLabel = 'Bloques';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('section_id')
                    ->label('Sección')
                    ->options(CemeterySection::pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre/Número')
                    ->required()
                    ->maxLength(50),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('section.name')
                    ->label('Sección')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre/Número')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('streets_count')
                    ->label('Calles')
                    ->counts('streets')
                    ->sortable(),
                Tables\Columns\TextColumn::make('avenues_count')
                    ->label('Avenidas')
                    ->counts('avenues')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('section_id')
                    ->label('Sección')
                    ->options(CemeterySection::pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StreetsRelationManager::class,
            RelationManagers\AvenuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCemeteryBlocks::route('/'),
            'create' => Pages\CreateCemeteryBlock::route('/create'),
            'view' => Pages\ViewCemeteryBlock::route('/{record}'),
            'edit' => Pages\EditCemeteryBlock::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::hasUser() && Auth::user() && (Auth::user()->isAdmin() || Auth::user()->isHelper() || Auth::user()->isAuditor());
    }
}
