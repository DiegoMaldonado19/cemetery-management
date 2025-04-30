<?php

namespace App\Filament\Resources\PersonResource\RelationManagers;

use App\Models\ContractStatus;
use App\Models\Deceased;
use App\Models\Niche;
use App\Models\NicheStatus;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ResponsibleContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'responsibleContracts';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Contratos como Responsable';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('niche_id')
                    ->label('Nicho')
                    ->relationship('niche', 'code', function (Builder $query) {
                        return $query->where('niche_status_id', NicheStatus::where('name', 'Disponible')->first()?->id)
                            ->with(['type', 'street.block.section', 'avenue.block.section']);
                    })
                    ->getOptionLabelFromRecordUsing(
                        fn($record) =>
                        $record->code . ' - ' .
                            $record->type->name . ' - ' .
                            'Calle ' . $record->street->street_number . ', Avenida ' . $record->avenue->avenue_number . ' - ' .
                            $record->street->block->section->name
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('deceased_id')
                    ->label('Fallecido')
                    ->relationship('deceased.person', 'first_name', function (Builder $query) {
                        return $query->with('deceased');
                    })
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->first_name . ' ' . $record->last_name)
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('start_date')
                    ->label('Fecha de Inicio')
                    ->default(now())
                    ->required(),

                Forms\Components\DatePicker::make('end_date')
                    ->label('Fecha de Finalización')
                    ->default(
                        fn($get) =>
                        Carbon::parse($get('start_date'))->addYears(6)
                    )
                    ->required(),

                Forms\Components\DatePicker::make('grace_date')
                    ->label('Fecha de Gracia')
                    ->default(
                        fn($get) =>
                        Carbon::parse($get('end_date'))->addYear()
                    )
                    ->required(),

                Forms\Components\Select::make('contract_status_id')
                    ->label('Estado del Contrato')
                    ->options(ContractStatus::pluck('name', 'id'))
                    ->default(fn () => ContractStatus::where('name', 'Vigente')->first()?->id)
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->maxLength(65535),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('# Contrato')
                    ->sortable(),

                Tables\Columns\TextColumn::make('niche.code')
                    ->label('Código de Nicho')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('deceased.person.first_name')
                    ->label('Fallecido')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->deceased->person->first_name . ' ' . $record->deceased->person->last_name
                    )
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
                    ->color(
                        fn(string $state): string =>
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
                    ->using(function (RelationManager $livewire, array $data): mixed {
                        // Asignar el CUI del responsable (persona actual)
                        $data['responsible_cui'] = $livewire->getOwnerRecord()->cui;

                        return $livewire->getRelationship()->create($data);
                    })
                    ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin() || Auth::hasUser() && Auth::user()->isHelper()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
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
