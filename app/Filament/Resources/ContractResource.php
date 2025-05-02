<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
use App\Models\Contract;
use App\Models\ContractStatus;
use App\Models\Deceased;
use App\Models\Niche;
use App\Models\NicheStatus;
use App\Models\PaymentStatus;
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

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Gestión de Nichos';

    protected static ?string $navigationLabel = 'Contratos';

    protected static ?string $modelLabel = 'Contrato';

    protected static ?string $pluralModelLabel = 'Contratos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Contrato')
                    ->schema([
                        Forms\Components\Select::make('niche_id')
                            ->label('Nicho')
                            ->relationship('niche', 'code', function (Builder $query) {
                                return $query->where('niche_status_id', NicheStatus::where('name', 'Disponible')->first()?->id)
                                    ->with(['type', 'street.block.section', 'avenue.block.section']);
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) =>
                                $record->code . ' - ' .
                                $record->type->name . ' - ' .
                                'Calle ' . $record->street->street_number . ', Avenida ' . $record->avenue->avenue_number . ' - ' .
                                $record->street->block->section->name
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($record) => $record !== null)
                            ->createOptionForm([
                                // Formulario para crear un nuevo nicho si es necesario
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Select::make('deceased_id')
                            ->label('Fallecido')
                            ->relationship('deceased.person', 'first_name', function (Builder $query) {
                                return $query->with('deceased');
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($record) => $record !== null)
                            ->createOptionForm([
                                // Formulario para crear un nuevo fallecido si es necesario
                            ]),

                        Forms\Components\Select::make('responsible_cui')
                            ->label('Responsable')
                            ->options(
                                Person::select(DB::raw("CONCAT(first_name, ' ', last_name) AS full_name"), 'cui')
                                    ->pluck('full_name', 'cui')
                            )
                            ->searchable()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Fechas del Contrato')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->default(now())
                            ->disabled(fn ($record) => $record !== null),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Fecha de Finalización')
                            ->required()
                            ->default(fn ($get) =>
                                Carbon::parse($get('start_date'))->addYears(6)
                            )
                            ->beforeOrEqual('grace_date')
                            ->afterOrEqual('start_date'),

                        Forms\Components\DatePicker::make('grace_date')
                            ->label('Fecha de Gracia')
                            ->required()
                            ->default(fn ($get) =>
                                Carbon::parse($get('end_date'))->addYear()
                            )
                            ->afterOrEqual('end_date'),
                    ])->columns(3),

                Forms\Components\Section::make('Estado y Notas')
                    ->schema([
                        Forms\Components\Select::make('contract_status_id')
                            ->label('Estado del Contrato')
                            ->options(ContractStatus::pluck('name', 'id'))
                            ->required()
                            ->default(fn () => ContractStatus::where('name', 'Vigente')->first()?->id),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
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
                    ->formatStateUsing(fn ($record) =>
                        $record->deceased->person->first_name . ' ' . $record->deceased->person->last_name
                    )
                    ->searchable(),

                Tables\Columns\TextColumn::make('responsible.first_name')
                    ->label('Responsable')
                    ->formatStateUsing(fn ($record) =>
                        $record->responsible->first_name . ' ' . $record->responsible->last_name
                    )
                    ->searchable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Fecha Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fecha Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn ($record) =>
                        Carbon::parse($record->end_date)->isPast()
                            ? 'Vencido hace ' . Carbon::parse($record->end_date)->diffForHumans()
                            : 'Vence en ' . Carbon::parse($record->end_date)->diffForHumans()
                    ),

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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    })
                    ->toggle(),

                Tables\Filters\Filter::make('in_grace')
                    ->label('En período de gracia')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('status', function($q) {
                            $q->where('name', 'En Gracia');
                        });
                    })
                    ->toggle(),

                Tables\Filters\Filter::make('expired')
                    ->label('Vencidos')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('status', function($q) {
                            $q->where('name', 'Vencido');
                        });
                    })
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => Auth::hasUser() && Auth::user()->isAdmin() || Auth::user()->isHelper()),
                Tables\Actions\Action::make('renovate')
                    ->label('Generar Renovación')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->action(function (Contract $record) {
                        // Verificar si ya hay un pago pendiente para renovación
                        $pendingRenewal = \App\Models\Payment::where('contract_id', $record->id)
                            ->whereHas('status', function($query) {
                                $query->where('name', 'No Pagado');
                            })
                            ->where('amount', 600.00)
                            ->where('issue_date', '>', $record->payments()->latest('issue_date')->first()->issue_date ?? '1900-01-01')
                            ->first();

                        if ($pendingRenewal) {
                            return;
                        }

                        // Generar número de recibo
                        $lastReceipt = \App\Models\Payment::orderBy('id', 'desc')->first();
                        $receiptNumber = $lastReceipt ? 'REC-' . str_pad((intval(substr($lastReceipt->receipt_number, 4)) + 1), 6, '0', STR_PAD_LEFT) : 'REC-000001';

                        // Crear boleta de pago para renovación
                        $unpaidStatus = PaymentStatus::where('name', 'No Pagado')->first();

                        \App\Models\Payment::create([
                            'contract_id' => $record->id,
                            'receipt_number' => $receiptNumber,
                            'amount' => 600.00, // Monto fijo según requerimientos
                            'issue_date' => Carbon::today(),
                            'payment_date' => null,
                            'payment_status_id' => $unpaidStatus->id,
                            'receipt_file_path' => null,
                            'notes' => 'Pago de renovación de contrato',
                            'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                        ]);

                        // Registro en change_logs
                        DB::table('change_logs')->insert([
                            'table_name' => 'payments',
                            'record_id' => \App\Models\Payment::where('receipt_number', $receiptNumber)->first()->id,
                            'changed_field' => 'creación',
                            'old_value' => 'Ninguno',
                            'new_value' => 'Nuevo pago de renovación registrado por monto: Q600.00',
                            'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Generar boleta de renovación')
                    ->modalDescription('¿Está seguro de generar una boleta de renovación para este contrato? Se creará una boleta por Q600.00')
                    ->modalSubmitActionLabel('Sí, generar boleta')
                    ->visible(fn (Contract $record) =>
                        (Auth::hasUser() && (Auth::user()->isAdmin())) &&
                        $record->status->name !== 'Finalizado'
                    ),
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
            RelationManagers\PaymentsRelationManager::make(),
            RelationManagers\ExhumationsRelationManager::make(),
            RelationManagers\NotificationsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'view' => Pages\ViewContract::route('/{record}'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['niche.type', 'niche.street.block.section', 'niche.avenue.block.section',
                    'deceased.person', 'responsible', 'status']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    // Esta función se ejecuta antes de crear un nuevo contrato
    public static function beforeCreate(): void
    {
        static::creating(function (Contract $contract) {
            // Calculamos las fechas si no están definidas
            if (!$contract->end_date) {
                $contract->end_date = Carbon::parse($contract->start_date)->addYears(6);
            }

            if (!$contract->grace_date) {
                $contract->grace_date = Carbon::parse($contract->end_date)->addYear();
            }

            // Si no tiene estado, establecemos "Vigente" por defecto
            if (!$contract->contract_status_id) {
                $contract->contract_status_id = ContractStatus::where('name', 'Vigente')->first()?->id;
            }
        });
    }

    // Esta función se ejecuta después de crear un nuevo contrato
    public static function afterCreate(): void
    {
        static::created(function (Contract $contract) {
            // Actualizamos el estado del nicho a "Ocupado"
            $occupiedStatus = NicheStatus::where('name', 'Ocupado')->first();

            if ($occupiedStatus) {
                Niche::where('id', $contract->niche_id)->update([
                    'niche_status_id' => $occupiedStatus->id,
                    'updated_at' => now(),
                ]);
            }

            // Generamos el pago inicial
            $unpaidStatus = PaymentStatus::where('name', 'No Pagado')->first();

            if ($unpaidStatus) {
                // Generamos número de recibo
                $lastReceipt = \App\Models\Payment::orderBy('id', 'desc')->first();
                $receiptNumber = $lastReceipt ? 'REC-' . str_pad((intval(substr($lastReceipt->receipt_number, 4)) + 1), 6, '0', STR_PAD_LEFT) : 'REC-000001';

                \App\Models\Payment::create([
                    'contract_id' => $contract->id,
                    'receipt_number' => $receiptNumber,
                    'amount' => 600.00, // Monto fijo según requerimientos
                    'issue_date' => Carbon::today(),
                    'payment_date' => null,
                    'payment_status_id' => $unpaidStatus->id,
                    'receipt_file_path' => null,
                    'notes' => 'Pago inicial del contrato',
                    'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                ]);
            }

            // Registramos la creación en el log
            DB::table('change_logs')->insert([
                'table_name' => 'contracts',
                'record_id' => $contract->id,
                'changed_field' => 'creación',
                'old_value' => 'Ninguno',
                'new_value' => 'Nuevo contrato creado',
                'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}
