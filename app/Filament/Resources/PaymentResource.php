<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use App\Models\PaymentStatus;
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

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Gestión de Nichos';

    protected static ?string $navigationLabel = 'Pagos';

    protected static ?string $modelLabel = 'Pago';

    protected static ?string $pluralModelLabel = 'Pagos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Pago')
                    ->schema([
                        Forms\Components\Select::make('contract_id')
                            ->label('Contrato')
                            ->relationship('contract', 'id', function (Builder $query) {
                                return $query->with(['niche', 'deceased.person', 'responsible']);
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
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('receipt_number')
                            ->label('Número de Recibo')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->disabled(fn($record) => $record !== null),

                        Forms\Components\TextInput::make('amount')
                            ->label('Monto')
                            ->required()
                            ->numeric()
                            ->prefix('Q')
                            ->default(600.00)
                            ->step(0.01),

                        Forms\Components\DatePicker::make('issue_date')
                            ->label('Fecha de Emisión')
                            ->required()
                            ->default(now())
                            ->disabled(fn($record) => $record !== null),

                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Fecha de Pago')
                            ->nullable()
                            ->afterOrEqual('issue_date'),
                    ])->columns(2),

                Forms\Components\Section::make('Estado y Comprobante')
                    ->schema([
                        Forms\Components\Select::make('payment_status_id')
                            ->label('Estado del Pago')
                            ->options(PaymentStatus::pluck('name', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state == PaymentStatus::where('name', 'Pagado')->first()?->id) {
                                    $set('payment_date', Carbon::today()->format('Y-m-d'));
                                } else {
                                    $set('payment_date', null);
                                }
                            }),

                        Forms\Components\FileUpload::make('receipt_file_path')
                            ->label('Comprobante de Pago')
                            ->directory('receipts')
                            ->visibility('public')
                            ->image()
                            ->imagePreviewHeight('250')
                            ->maxSize(2048) // 2MB
                            ->nullable()
                            ->visible(function ($get) {
                                return $get('payment_status_id') == PaymentStatus::where('name', 'Pagado')->first()?->id;
                            }),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label('Número de Recibo')
                    ->searchable(),

                Tables\Columns\TextColumn::make('contract.id')
                    ->label('# Contrato')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contract.niche.code')
                    ->label('Código de Nicho')
                    ->searchable(),

                Tables\Columns\TextColumn::make('contract.deceased.person.first_name')
                    ->label('Fallecido')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->contract->deceased->person->first_name . ' ' . $record->contract->deceased->person->last_name
                    )
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('GTQ')
                    ->sortable(),

                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Fecha de Emisión')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Fecha de Pago')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('No pagado'),

                Tables\Columns\TextColumn::make('status.name')
                    ->label('Estado')
                    ->badge()
                    ->color(
                        fn(string $state): string =>
                        match ($state) {
                            'Pagado' => 'success',
                            'No Pagado' => 'danger',
                            default => 'gray',
                        }
                    ),

                Tables\Columns\ImageColumn::make('receipt_file_path')
                    ->label('Comprobante')
                    ->visibility('public')
                    ->circular(),

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
                Tables\Filters\SelectFilter::make('payment_status_id')
                    ->label('Estado')
                    ->options(PaymentStatus::pluck('name', 'id')),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('issue_date_from')
                            ->label('Emitidos desde'),
                        Forms\Components\DatePicker::make('issue_date_until')
                            ->label('Emitidos hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['issue_date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('issue_date', '>=', $date),
                            )
                            ->when(
                                $data['issue_date_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('issue_date', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('paid_range')
                    ->form([
                        Forms\Components\DatePicker::make('payment_date_from')
                            ->label('Pagados desde'),
                        Forms\Components\DatePicker::make('payment_date_until')
                            ->label('Pagados hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['payment_date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('payment_date', '>=', $date),
                            )
                            ->when(
                                $data['payment_date_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('payment_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin() || Auth::hasUser() && Auth::user()->isHelper()),
                Tables\Actions\Action::make('printReceipt')
                    ->label('Imprimir Boleta')
                    ->icon('heroicon-o-printer')
                    ->url(fn(Payment $record) => route('filament.admin.resources.payments.print', $record))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
            'print' => Pages\PrintPayment::route('/{record}/print'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['contract.niche', 'contract.deceased.person', 'contract.responsible', 'status', 'user']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('payment_status_id', PaymentStatus::where('name', 'No Pagado')->first()?->id)->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return static::getModel()::where('payment_status_id', PaymentStatus::where('name', 'No Pagado')->first()?->id)->exists()
            ? 'danger'
            : 'success';
    }

    // Esta función se ejecuta después de crear un nuevo pago
    public static function afterCreate(): void
    {
        static::created(function (Payment $payment) {
            // Registramos la creación en el log
            DB::table('change_logs')->insert([
                'table_name' => 'payments',
                'record_id' => $payment->id,
                'changed_field' => 'creación',
                'old_value' => 'Ninguno',
                'new_value' => 'Nuevo pago registrado por monto: Q' . number_format($payment->amount, 2),
                'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    // Esta función se ejecuta después de actualizar un pago
    public static function afterUpdate(): void
    {
        static::updated(function (Payment $payment) {
            $oldValues = $payment->getOriginal();
            $newValues = $payment->getAttributes();

            // Si el pago cambia de estado a "Pagado" y es una renovación (monto = 600), actualizar fecha de fin del contrato
            if (
                $oldValues['payment_status_id'] != $newValues['payment_status_id'] &&
                $newValues['payment_status_id'] == PaymentStatus::where('name', 'Pagado')->first()?->id &&
                $payment->amount == 600.00
            ) {

                $contract = $payment->contract;

                // Actualizamos fechas del contrato
                $contract->update([
                    'end_date' => Carbon::parse(max(Carbon::today(), $contract->end_date))->addYears(6),
                    'grace_date' => Carbon::parse(max(Carbon::today(), $contract->end_date))->addYears(7),
                    'contract_status_id' => \App\Models\ContractStatus::where('name', 'Vigente')->first()?->id,
                    'updated_at' => now(),
                ]);

                // Registramos el cambio
                DB::table('change_logs')->insert([
                    'table_name' => 'contracts',
                    'record_id' => $contract->id,
                    'changed_field' => 'renovación',
                    'old_value' => $oldValues['payment_status_id'],
                    'new_value' => 'Contrato renovado por pago #' . $payment->id,
                    'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Registramos cambio de estado de pago
            if ($oldValues['payment_status_id'] != $newValues['payment_status_id']) {
                DB::table('change_logs')->insert([
                    'table_name' => 'payments',
                    'record_id' => $payment->id,
                    'changed_field' => 'estado_de_pago',
                    'old_value' => $oldValues['payment_status_id'],
                    'new_value' => $newValues['payment_status_id'],
                    'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Registramos cambio en la fecha de pago
            if (($oldValues['payment_date'] === null && $newValues['payment_date'] !== null) ||
                ($oldValues['payment_date'] !== null && $newValues['payment_date'] === null) ||
                ($oldValues['payment_date'] !== null && $newValues['payment_date'] !== null && $oldValues['payment_date'] != $newValues['payment_date'])
            ) {

                DB::table('change_logs')->insert([
                    'table_name' => 'payments',
                    'record_id' => $payment->id,
                    'changed_field' => 'fecha_de_pago',
                    'old_value' => $oldValues['payment_date'] === null ? 'No definida' : $oldValues['payment_date'],
                    'new_value' => $newValues['payment_date'] === null ? 'No definida' : $newValues['payment_date'],
                    'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Registramos cambio en la ruta del comprobante
            if (($oldValues['receipt_file_path'] === null && $newValues['receipt_file_path'] !== null) ||
                ($oldValues['receipt_file_path'] !== null && $newValues['receipt_file_path'] === null) ||
                ($oldValues['receipt_file_path'] !== null && $newValues['receipt_file_path'] !== null && $oldValues['receipt_file_path'] != $newValues['receipt_file_path'])
            ) {

                DB::table('change_logs')->insert([
                    'table_name' => 'payments',
                    'record_id' => $payment->id,
                    'changed_field' => 'ruta_comprobante',
                    'old_value' => $oldValues['receipt_file_path'] === null ? 'No definida' : $oldValues['receipt_file_path'],
                    'new_value' => $newValues['receipt_file_path'] === null ? 'No definida' : $newValues['receipt_file_path'],
                    'user_id' => Auth::hasUser() && Auth::user() ? Auth::id() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
