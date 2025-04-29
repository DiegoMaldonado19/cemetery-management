<?php

namespace App\Filament\Resources\ContractResource\RelationManagers;

use App\Models\PaymentStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $recordTitleAttribute = 'receipt_number';

    protected static ?string $title = 'Pagos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('receipt_number')
                    ->label('Número de Recibo')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
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
                    ->default(now()),
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Fecha de Pago')
                    ->nullable(),
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
                    ->reactive()
                    ->visible(function ($get) {
                        return $get('payment_status_id') == PaymentStatus::where('name', 'Pagado')->first()?->id;
                    }),
                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->maxLength(65535),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('receipt_number')
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label('Número de Recibo')
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
                    ->color(fn (string $state): string => 
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (RelationManager $livewire, array $data): mixed {
                        // Añadir usuario actual
                        $data['user_id'] = auth()->id();
                        
                        return $livewire->getRelationship()->create($data);
                    })
                    ->visible(fn () => auth()->user()->isAdmin() || auth()->user()->isHelper()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->isAdmin() || auth()->user()->isHelper()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->isAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->isAdmin()),
                ]),
            ]);
    }
}