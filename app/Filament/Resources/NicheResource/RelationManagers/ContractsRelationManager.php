<?php

namespace App\Filament\Resources\NicheResource\RelationManagers;

use App\Models\ContractStatus;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Contratos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('deceased_id')
                    ->label('Fallecido')
                    ->relationship('deceased.person', 'first_name', function (Builder $query) {
                        $query->with('deceased');
                        return $query;
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                    ->searchable()
                    ->preload()
                    ->required(),
                    Forms\Components\Select::make('responsible_cui')
                    ->label('Responsable')
                    ->options(function () {
                        // Filtrar personas vivas (que no estén en la tabla deceased)
                        return Person::whereNotIn('cui', function ($query) {
                                $query->select('cui')->from('deceased');
                            })
                            ->get()
                            ->mapWithKeys(function ($person) {
                                return [$person->cui => "{$person->first_name} {$person->last_name} ({$person->cui})"];
                            });
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Fecha de Inicio')
                    ->default(now())
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Fecha de Finalización')
                    ->default(now()->addYears(6))
                    ->required(),
                Forms\Components\DatePicker::make('grace_date')
                    ->label('Fecha de Gracia')
                    ->default(now()->addYears(7))
                    ->required(),
                Forms\Components\Select::make('contract_status_id')
                    ->label('Estado del Contrato')
                    ->options(ContractStatus::pluck('name', 'id'))
                    ->default(fn () => ContractStatus::where('name', 'Vigente')->first()?->id)
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->maxLength(65535),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('# Contrato')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deceased.person.first_name')
                    ->label('Fallecido')
                    ->formatStateUsing(fn ($record) => $record->deceased->person->first_name . ' ' . $record->deceased->person->last_name)
                    ->searchable(),
                Tables\Columns\TextColumn::make('responsible.first_name')
                    ->label('Responsable')
                    ->formatStateUsing(fn ($record) => $record->responsible->first_name . ' ' . $record->responsible->last_name)
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Finalización')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status.name')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string =>
                        match ($state) {
                            'Vigente' => 'success',
                            'En Gracia' => 'warning',
                            'Vencido' => 'danger',
                            'Finalizado' => 'gray',
                            default => 'gray',
                        }
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('contract_status_id')
                    ->label('Estado')
                    ->options(ContractStatus::pluck('name', 'id')),
                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Próximos a vencer (90 días)')
                    ->query(function (Builder $query): Builder {
                        $today = Carbon::today();
                        $in90Days = $today->copy()->addDays(90);

                        return $query
                            ->where('end_date', '>=', $today)
                            ->where('end_date', '<=', $in90Days);
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn () => Auth::hasUser() && Auth::user()->isAdmin() || Auth::hasUser() && Auth::user()->isHelper()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => Auth::hasUser() && Auth::user()->isAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::hasUser() && Auth::user()->isAdmin()),
                ]),
            ]);
    }
}
