<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CemeteryAvenueResource\Pages;
use App\Models\CemeteryAvenue;
use App\Models\CemeteryBlock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\CemeteryAvenueResource\RelationManagers;

class CemeteryAvenueResource extends Resource
{
    protected static ?string $model = CemeteryAvenue::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3-bottom-left';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Avenidas del Cementerio';

    protected static ?string $modelLabel = 'Avenida';

    protected static ?string $pluralModelLabel = 'Avenidas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('block_id')
                    ->label('Bloque')
                    ->options(function () {
                        return CemeteryBlock::with('section')
                            ->get()
                            ->mapWithKeys(function ($block) {
                                return [
                                    $block->id => $block->section->name . ' - Bloque ' . $block->name
                                ];
                            });
                    })
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->maxLength(100),
                Forms\Components\TextInput::make('avenue_number')
                    ->label('Número de Avenida')
                    ->required()
                    ->numeric()
                    ->minValue(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('block.section.name')
                    ->label('Sección')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('block.name')
                    ->label('Bloque')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->placeholder('Sin nombre'),
                Tables\Columns\TextColumn::make('avenue_number')
                    ->label('Número de Avenida')
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
                Tables\Filters\SelectFilter::make('block_id')
                    ->label('Bloque')
                    ->options(function () {
                        return CemeteryBlock::with('section')
                            ->get()
                            ->mapWithKeys(function ($block) {
                                return [
                                    $block->id => $block->section->name . ' - Bloque ' . $block->name
                                ];
                            });
                    }),
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



    public static function getRelations(): array
    {
        return [
            RelationManagers\NichesRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCemeteryAvenues::route('/'),
            'create' => Pages\CreateCemeteryAvenue::route('/create'),
            'view' => Pages\ViewCemeteryAvenue::route('/{record}'),
            'edit' => Pages\EditCemeteryAvenue::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::hasUser() && Auth::user() && (Auth::user()->isAdmin() || Auth::user()->isHelper() || Auth::user()->isAuditor());
    }
}
