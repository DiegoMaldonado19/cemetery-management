<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NicheResource\Pages;
use App\Filament\Resources\NicheResource\RelationManagers;
use App\Models\Niche;
use App\Models\NicheStatus;
use App\Models\NicheType;
use App\Models\CemeteryStreet;
use App\Models\CemeteryAvenue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class NicheResource extends Resource
{
    protected static ?string $model = Niche::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Gestión de Nichos';

    protected static ?string $navigationLabel = 'Nichos';

    protected static ?string $modelLabel = 'Nicho';

    protected static ?string $pluralModelLabel = 'Nichos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Nicho')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('niche_type_id')
                            ->label('Tipo de Nicho')
                            ->options(NicheType::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('niche_status_id')
                            ->label('Estado')
                            ->options(NicheStatus::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                    ])->columns(3),

                Forms\Components\Section::make('Ubicación')
                    ->schema([
                        Forms\Components\Select::make('street_id')
                            ->label('Calle')
                            ->options(function () {
                                return CemeteryStreet::with('block.section')
                                    ->get()
                                    ->mapWithKeys(function ($street) {
                                        return [
                                            $street->id => $street->block->section->name . ' - Bloque ' . $street->block->name . ', Calle ' . $street->street_number
                                        ];
                                    });
                            })
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('avenue_id')
                            ->label('Avenida')
                            ->options(function () {
                                return CemeteryAvenue::with('block.section')
                                    ->get()
                                    ->mapWithKeys(function ($avenue) {
                                        return [
                                            $avenue->id => $avenue->block->section->name . ' - Bloque ' . $avenue->block->name . ', Avenida ' . $avenue->avenue_number
                                        ];
                                    });
                            })
                            ->required()
                            ->searchable(),
                        Forms\Components\Textarea::make('location_reference')
                            ->label('Referencia de ubicación')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Personaje Histórico')
                    ->schema([
                        Forms\Components\Select::make('historical_figure_id')
                            ->label('Personaje Histórico')
                            ->relationship('historicalFigure', 'historical_reason', function (Builder $query) {
                                $query->with('person');
                                return $query;
                            })
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                if ($record->cui) {
                                    return $record->person->first_name . ' ' . $record->person->last_name . ' - ' . $record->historical_reason;
                                }
                                return ($record->historical_first_name ?? '') . ' ' . ($record->historical_last_name ?? '') . ' - ' . $record->historical_reason;
                            })
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->label('Tipo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status.name')
                    ->label('Estado')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string =>
                        match ($state) {
                            'Disponible' => 'success',
                            'Ocupado' => 'warning',
                            'Proceso de Exhumación' => 'danger',
                            default => 'gray',
                        }
                    ),
                Tables\Columns\TextColumn::make('street.street_number')
                    ->label('Calle')
                    ->formatStateUsing(fn ($record) => $record->street->street_number)
                    ->sortable(),
                Tables\Columns\TextColumn::make('avenue.avenue_number')
                    ->label('Avenida')
                    ->formatStateUsing(fn ($record) => $record->avenue->avenue_number)
                    ->sortable(),
                Tables\Columns\TextColumn::make('historicalFigure.historical_reason')
                    ->label('Personaje Histórico')
                    ->formatStateUsing(function ($record) {
                        if (!$record->historical_figure_id) return null;

                        if ($record->historicalFigure->cui) {
                            return $record->historicalFigure->person->first_name . ' ' . $record->historicalFigure->person->last_name;
                        }

                        return ($record->historicalFigure->historical_first_name ?? '') . ' ' . ($record->historicalFigure->historical_last_name ?? '');
                    })
                    ->badge()
                    ->color('danger')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('niche_type_id')
                    ->label('Tipo de Nicho')
                    ->options(NicheType::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('niche_status_id')
                    ->label('Estado')
                    ->options(NicheStatus::pluck('name', 'id')),
                Tables\Filters\Filter::make('historical')
                    ->label('Personaje Histórico')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('historical_figure_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::hasUser() && Auth::user()->isAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ContractsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNiches::route('/'),
            'create' => Pages\CreateNiche::route('/create'),
            'view' => Pages\ViewNiche::route('/{record}'),
            'edit' => Pages\EditNiche::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['type', 'status', 'street.block.section', 'avenue.block.section', 'historicalFigure.person']);

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
