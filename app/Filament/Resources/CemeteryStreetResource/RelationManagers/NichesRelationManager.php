<?php

namespace App\Filament\Resources\CemeteryStreetResource\RelationManagers;

use App\Models\Niche;
use App\Models\NicheStatus;
use App\Models\NicheType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class NichesRelationManager extends RelationManager
{
    protected static string $relationship = 'niches';

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $title = 'Nichos de esta Calle';

    public function form(Form $form): Form
    {
        return $form
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
                Forms\Components\Textarea::make('location_reference')
                    ->label('Referencia de ubicación')
                    ->maxLength(65535),
            ]);
    }

    public function table(Table $table): Table
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
                Tables\Columns\TextColumn::make('avenue.avenue_number')
                    ->label('Avenida')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creación')
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
