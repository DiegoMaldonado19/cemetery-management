<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CemeterySectionResource\Pages;
use App\Filament\Resources\CemeterySectionResource\RelationManagers;
use App\Models\CemeterySection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CemeterySectionResource extends Resource
{
    protected static ?string $model = CemeterySection::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Secciones del Cementerio';

    protected static ?string $modelLabel = 'Sección';

    protected static ?string $pluralModelLabel = 'Secciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('blocks_count')
                    ->label('Bloques')
                    ->counts('blocks')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            RelationManagers\BlocksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCemeterySections::route('/'),
            'create' => Pages\CreateCemeterySection::route('/create'),
            'view' => Pages\ViewCemeterySection::route('/{record}'),
            'edit' => Pages\EditCemeterySection::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::hasUser() && Auth::user() && (Auth::user()->isAdmin() || Auth::user()->isHelper() || Auth::user()->isAuditor());
    }
}
