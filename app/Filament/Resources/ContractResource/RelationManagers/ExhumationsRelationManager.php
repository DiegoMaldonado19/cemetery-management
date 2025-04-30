<?php

namespace App\Filament\Resources\ContractResource\RelationManagers;

use App\Models\ExhumationStatus;
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

class ExhumationsRelationManager extends RelationManager
{
    protected static string $relationship = 'exhumations';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Exhumaciones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('requester_cui')
                    ->label('Solicitante')
                    ->options(function () {
                        // Por defecto, mostrar al responsable del contrato como solicitante
                        $contract = $this->getOwnerRecord();

                        $options = \App\Models\Person::select(
                            DB::raw("CONCAT(first_name, ' ', last_name) AS full_name"),
                            'cui'
                        )->pluck('full_name', 'cui');

                        return $options;
                    })
                    ->default(function () {
                        // Establecer por defecto al responsable del contrato
                        $contract = $this->getOwnerRecord();
                        return $contract->responsible_cui;
                    })
                    ->searchable()
                    ->required(),

                Forms\Components\DatePicker::make('request_date')
                    ->label('Fecha de Solicitud')
                    ->required()
                    ->default(now()),

                Forms\Components\DatePicker::make('exhumation_date')
                    ->label('Fecha de Exhumación')
                    ->nullable()
                    ->afterOrEqual('request_date'),

                Forms\Components\Textarea::make('reason')
                    ->label('Motivo de la Exhumación')
                    ->required()
                    ->maxLength(65535),

                Forms\Components\FileUpload::make('agreement_file_path')
                    ->label('Documento de Acuerdo de Exhumación')
                    ->directory('exhumation_agreements')
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(5120) // 5MB
                    ->required(),

                Forms\Components\Select::make('exhumation_status_id')
                    ->label('Estado de la Exhumación')
                    ->options(ExhumationStatus::pluck('name', 'id'))
                    ->required()
                    ->default(fn() => ExhumationStatus::where('name', 'Solicitada')->first()?->id),

                Forms\Components\Textarea::make('notes')
                    ->label('Notas Adicionales')
                    ->maxLength(65535),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('# Exhumación')
                    ->sortable(),

                Tables\Columns\TextColumn::make('requester.first_name')
                    ->label('Solicitante')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->requester->first_name . ' ' . $record->requester->last_name
                    )
                    ->searchable(),

                Tables\Columns\TextColumn::make('request_date')
                    ->label('Fecha de Solicitud')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('exhumation_date')
                    ->label('Fecha de Exhumación')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('No programada'),

                Tables\Columns\TextColumn::make('status.name')
                    ->label('Estado')
                    ->badge()
                    ->color(
                        fn(string $state): string =>
                        match ($state) {
                            'Solicitada' => 'warning',
                            'Aprobada' => 'success',
                            'Rechazada' => 'danger',
                            'Completada' => 'gray',
                            default => 'gray',
                        }
                    ),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Registrado Por')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exhumation_status_id')
                    ->label('Estado')
                    ->options(ExhumationStatus::pluck('name', 'id')),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('request_date_from')
                            ->label('Solicitadas desde'),
                        Forms\Components\DatePicker::make('request_date_until')
                            ->label('Solicitadas hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['request_date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('request_date', '>=', $date),
                            )
                            ->when(
                                $data['request_date_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('request_date', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (RelationManager $livewire, array $data): mixed {
                        // Asignar contrato y usuario actual
                        $data['contract_id'] = $livewire->getOwnerRecord()->id;
                        $data['user_id'] = Auth::hasUser() && Auth::user() ? Auth::id() : null;

                        // Verificar que el nicho no pertenezca a un personaje histórico
                        $contract = $livewire->getOwnerRecord();

                        if ($contract->niche->historical_figure_id) {
                            throw new \Exception('No se puede crear una exhumación para un personaje histórico.');
                        }

                        return $livewire->getRelationship()->create($data);
                    })
                    ->visible(function () {
                        // Verificar si el contrato tiene un nicho de personaje histórico
                        $contract = $this->getOwnerRecord();

                        if ($contract->niche->historical_figure_id) {
                            return false;
                        }

                        return Auth::hasUser() && Auth::user()->isAdmin() || Auth::hasUser() && Auth::user()->isHelper();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin() || Auth::hasUser() && Auth::user()->isHelper()),
                Tables\Actions\Action::make('downloadAgreement')
                    ->label('Descargar Acuerdo')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn($record) => $record->agreement_file_path ? storage_url($record->agreement_file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->agreement_file_path !== null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin()),
                ]),
            ]);
    }
}
