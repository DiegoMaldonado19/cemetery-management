<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExhumationResource\Pages;
use App\Filament\Resources\ExhumationResource\RelationManagers;
use App\Models\Contract;
use App\Models\Exhumation;
use App\Models\ExhumationStatus;
use App\Models\HistoricalFigure;
use App\Models\Niche;
use App\Models\NicheStatus;
use App\Models\Person;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExhumationResource extends Resource
{
    protected static ?string $model = Exhumation::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-x-mark';

    protected static ?string $navigationGroup = 'Gestión de Nichos';

    protected static ?string $navigationLabel = 'Exhumaciones';

    protected static ?string $modelLabel = 'Exhumación';

    protected static ?string $pluralModelLabel = 'Exhumaciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Contrato')
                    ->schema([
                        Forms\Components\Select::make('contract_id')
                            ->label('Contrato')
                            ->relationship('contract', 'id', function (Builder $query) {
                                return $query->whereHas('status', function ($q) {
                                    $q->whereIn('name', ['Vigente', 'En Gracia']);
                                })
                                    ->whereDoesntHave('niche', function ($q) {
                                        $q->whereNotNull('historical_figure_id');
                                    })
                                    ->with(['niche.type', 'niche.street.block.section', 'niche.avenue.block.section', 'deceased.person', 'responsible']);
                            })
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                'Contrato #' . $record->id . ' - ' .
                                    'Nicho: ' . $record->niche->code . ' - ' .
                                    'Fallecido: ' . $record->deceased->person->first_name . ' ' . $record->deceased->person->last_name
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn($record) => $record !== null)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) return;

                                $contract = Contract::find($state);
                                if (!$contract) return;

                                // Pre-llenar el CUI del responsable como solicitante por defecto
                                $set('requester_cui', $contract->responsible_cui);
                            }),
                    ]),

                Forms\Components\Section::make('Datos de la Solicitud')
                    ->schema([
                        Forms\Components\Select::make('requester_cui')
                            ->label('Solicitante')
                            ->options(
                                Person::select(DB::raw("CONCAT(first_name, ' ', last_name) AS full_name"), 'cui')
                                    ->pluck('full_name', 'cui')
                            )
                            ->searchable()
                            ->required(),

                        Forms\Components\DatePicker::make('request_date')
                            ->label('Fecha de Solicitud')
                            ->required()
                            ->default(now())
                            ->disabled(fn($record) => $record !== null),

                        Forms\Components\DatePicker::make('exhumation_date')
                            ->label('Fecha de Exhumación')
                            ->nullable()
                            ->afterOrEqual('request_date')
                            ->visible(
                                fn($get) =>
                                $get('exhumation_status_id') == ExhumationStatus::where('name', 'Aprobada')->first()?->id ||
                                    $get('exhumation_status_id') == ExhumationStatus::where('name', 'Completada')->first()?->id
                            ),

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
                    ])->columns(2),

                Forms\Components\Section::make('Estado y Notas')
                    ->schema([
                        Forms\Components\Select::make('exhumation_status_id')
                            ->label('Estado de la Exhumación')
                            ->options(ExhumationStatus::pluck('name', 'id'))
                            ->required()
                            ->default(fn() => ExhumationStatus::where('name', 'Solicitada')->first()?->id)
                            ->reactive()
                            ->disabled(function ($record, $get) {
                                if (!$record) return false;

                                // Si ya está completada, no se puede cambiar
                                if ($record->status->name === 'Completada') return true;

                                // Si es un personaje histórico, no se puede aprobar
                                if (
                                    $get('contract_id') &&
                                    Contract::find($get('contract_id'))->niche->historical_figure_id
                                ) {

                                    $approvedStatus = ExhumationStatus::where('name', 'Aprobada')->first()?->id;
                                    $completedStatus = ExhumationStatus::where('name', 'Completada')->first()?->id;

                                    if (
                                        $get('exhumation_status_id') == $approvedStatus ||
                                        $get('exhumation_status_id') == $completedStatus
                                    ) {
                                        return true;
                                    }
                                }

                                return false;
                            }),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas Adicionales')
                            ->maxLength(65535),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('# Exhumación')
                    ->sortable(),

                Tables\Columns\TextColumn::make('contract.niche.code')
                    ->label('Código de Nicho')
                    ->searchable(),

                Tables\Columns\TextColumn::make('contract.deceased.person.first_name')
                    ->label('Fallecido a Exhumar')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->contract->deceased->person->first_name . ' ' . $record->contract->deceased->person->last_name
                    )
                    ->searchable(),

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

                Tables\Columns\TextColumn::make('contract.niche.historicalFigure.historical_reason')
                    ->label('Personaje Histórico')
                    ->formatStateUsing(function ($record) {
                        if (!$record->contract->niche->historical_figure_id) return null;

                        return 'Sí - No se puede exhumar';
                    })
                    ->badge()
                    ->color('danger')
                    ->visible(fn($record) => $record?->contract?->niche?->historical_figure_id !== null),

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

                Tables\Filters\Filter::make('historical')
                    ->label('Con restricción histórica')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('contract.niche', function ($q) {
                            $q->whereNotNull('historical_figure_id');
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
                Tables\Actions\Action::make('downloadAgreement')
                    ->label('Descargar Acuerdo')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn(Exhumation $record) => $record->agreement_file_path ? storage_url($record->agreement_file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn(Exhumation $record) => $record->agreement_file_path !== null),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExhumations::route('/'),
            'create' => Pages\CreateExhumation::route('/create'),
            'view' => Pages\ViewExhumation::route('/{record}'),
            'edit' => Pages\EditExhumation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'contract.niche.type',
                'contract.niche.street.block.section',
                'contract.niche.avenue.block.section',
                'contract.deceased.person',
                'contract.niche.historicalFigure',
                'requester',
                'status',
                'user'
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereHas('status', function ($query) {
            $query->where('name', 'Solicitada');
        })->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return static::getModel()::whereHas('status', function ($query) {
            $query->where('name', 'Solicitada');
        })->exists() ? 'warning' : 'success';
    }

    // Esta función se ejecuta antes de crear una nueva exhumación
    public static function beforeCreate(): void
    {
        static::creating(function (Exhumation $exhumation) {
            // Verificamos que el nicho no pertenezca a un personaje histórico
            $contract = Contract::with('niche')->find($exhumation->contract_id);

            if ($contract && $contract->niche->historical_figure_id) {
                // No permitimos crear exhumaciones para personajes históricos
                throw new \Exception('No se puede crear una exhumación para un personaje histórico.');
            }

            // Asignamos el usuario actual
            $exhumation->user_id = Auth::hasUser() && Auth::user() ? Auth::id() : null;
        });
    }

    // Esta función se ejecuta después de crear una nueva exhumación
    public static function afterCreate(): void
    {
        static::created(function (Exhumation $exhumation) {
            // Registramos la creación en el log
            DB::table('change_logs')->insert([
                'table_name' => 'exhumations',
                'record_id' => $exhumation->id,
                'changed_field' => 'creación',
                'old_value' => 'Ninguno',
                'new_value' => 'Nueva solicitud de exhumación registrada',
                'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    // Esta función se ejecuta después de actualizar una exhumación
    public static function afterUpdate(): void
    {
        static::updated(function (Exhumation $exhumation) {
            $oldValues = $exhumation->getOriginal();
            $newValues = $exhumation->getAttributes();

            // Registrar cambio de estado de exhumación
            if ($oldValues['exhumation_status_id'] != $newValues['exhumation_status_id']) {
                DB::table('change_logs')->insert([
                    'table_name' => 'exhumations',
                    'record_id' => $exhumation->id,
                    'changed_field' => 'estado_de_exhumación',
                    'old_value' => $oldValues['exhumation_status_id'],
                    'new_value' => $newValues['exhumation_status_id'],
                    'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Si se aprueba la exhumación, cambiar el estado del nicho
                if ($newValues['exhumation_status_id'] == ExhumationStatus::where('name', 'Aprobada')->first()?->id) {
                    $processStatus = NicheStatus::where('name', 'Proceso de Exhumación')->first();

                    if ($processStatus) {
                        Niche::where('id', $exhumation->contract->niche_id)->update([
                            'niche_status_id' => $processStatus->id,
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Si se completa la exhumación, cambiar el estado del nicho a disponible y finalizar el contrato
                if ($newValues['exhumation_status_id'] == ExhumationStatus::where('name', 'Completada')->first()?->id) {
                    $availableStatus = NicheStatus::where('name', 'Disponible')->first();
                    $finishedStatus = \App\Models\ContractStatus::where('name', 'Finalizado')->first();

                    if ($availableStatus) {
                        Niche::where('id', $exhumation->contract->niche_id)->update([
                            'niche_status_id' => $availableStatus->id,
                            'updated_at' => now(),
                        ]);
                    }

                    if ($finishedStatus) {
                        Contract::where('id', $exhumation->contract_id)->update([
                            'contract_status_id' => $finishedStatus->id,
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Registrar cambio en la fecha de exhumación
            if (($oldValues['exhumation_date'] === null && $newValues['exhumation_date'] !== null) ||
                ($oldValues['exhumation_date'] !== null && $newValues['exhumation_date'] === null) ||
                ($oldValues['exhumation_date'] !== null && $newValues['exhumation_date'] !== null && $oldValues['exhumation_date'] != $newValues['exhumation_date'])
            ) {

                DB::table('change_logs')->insert([
                    'table_name' => 'exhumations',
                    'record_id' => $exhumation->id,
                    'changed_field' => 'fecha_de_exhumación',
                    'old_value' => $oldValues['exhumation_date'] === null ? 'No definida' : $oldValues['exhumation_date'],
                    'new_value' => $newValues['exhumation_date'] === null ? 'No definida' : $newValues['exhumation_date'],
                    'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Registrar cambio en el motivo
            if ($oldValues['reason'] != $newValues['reason']) {
                DB::table('change_logs')->insert([
                    'table_name' => 'exhumations',
                    'record_id' => $exhumation->id,
                    'changed_field' => 'motivo',
                    'old_value' => $oldValues['reason'],
                    'new_value' => $newValues['reason'],
                    'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
