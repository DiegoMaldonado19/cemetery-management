<?php

namespace App\Filament\Resources\HistoricalFigureResource\RelationManagers;

use App\Models\NicheStatus;
use App\Models\NicheType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class NichesRelationManager extends RelationManager
{
    protected static string $relationship = 'niches';

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $title = 'Nichos Asociados';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('niche_id')
                    ->label('Nicho')
                    ->options(function () {
                        // Obtener solo los nichos disponibles y los que ya están asociados
                        $query = \App\Models\Niche::query()
                            ->where(function ($query) {
                                $query->where('niche_status_id', NicheStatus::where('name', 'Disponible')->first()?->id)
                                    ->orWhere('historical_figure_id', $this->getOwnerRecord()->id);
                            })
                            ->with(['type', 'street.block.section', 'avenue.block.section']);

                        return $query->get()->mapWithKeys(function ($niche) {
                            $label = $niche->code . ' - ' .
                                $niche->type->name . ' - ' .
                                'Calle ' . $niche->street->street_number . ', ' .
                                'Avenida ' . $niche->avenue->avenue_number . ' - ' .
                                $niche->street->block->section->name;

                            return [$niche->id => $label];
                        });
                    })
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
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
                    ->color(
                        fn(string $state): string =>
                        match ($state) {
                            'Disponible' => 'success',
                            'Ocupado' => 'warning',
                            'Proceso de Exhumación' => 'danger',
                            default => 'gray',
                        }
                    ),
                Tables\Columns\TextColumn::make('street.street_number')
                    ->label('Calle')
                    ->formatStateUsing(fn($record) => $record->street->street_number),
                Tables\Columns\TextColumn::make('avenue.avenue_number')
                    ->label('Avenida')
                    ->formatStateUsing(fn($record) => $record->avenue->avenue_number),
                Tables\Columns\TextColumn::make('street.block.section.name')
                    ->label('Sección')
                    ->formatStateUsing(fn($record) => $record->street->block->section->name),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('niche_type_id')
                    ->label('Tipo de Nicho')
                    ->options(NicheType::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('niche_status_id')
                    ->label('Estado')
                    ->options(NicheStatus::pluck('name', 'id')),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->using(function (RelationManager $livewire, array $data): mixed {
                        // Modificar el nicho para asociarlo al personaje histórico
                        $niche = \App\Models\Niche::find($data['niche_id']);

                        if ($niche) {
                            $niche->update([
                                'historical_figure_id' => $livewire->getOwnerRecord()->id
                            ]);

                            return $niche;
                        }

                        return null;
                    })
                    ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin()),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->using(function (RelationManager $livewire, $record): mixed {
                        // Desasociar el nicho del personaje histórico
                        $record->update([
                            'historical_figure_id' => null
                        ]);

                        return $record;
                    })
                    ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin()),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin()),
                ]),
            ]);
    }
}
