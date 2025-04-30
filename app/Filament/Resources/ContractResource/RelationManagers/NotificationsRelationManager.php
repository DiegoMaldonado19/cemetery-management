<?php

namespace App\Filament\Resources\ContractResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;

class NotificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'notifications';

    protected static ?string $recordTitleAttribute = 'message';

    protected static ?string $title = 'Notificaciones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('sent_at')
                    ->label('Fecha y Hora de Envío')
                    ->required()
                    ->default(now()),

                Forms\Components\Textarea::make('message')
                    ->label('Mensaje')
                    ->required()
                    ->maxLength(65535),

                Forms\Components\Toggle::make('is_sent')
                    ->label('Enviada')
                    ->default(true)
                    ->helperText('Indica si la notificación ha sido enviada.'),

                Forms\Components\DateTimePicker::make('read_at')
                    ->label('Fecha y Hora de Lectura')
                    ->nullable()
                    ->helperText('Fecha y hora en que la notificación fue leída por el destinatario.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Fecha de Envío')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('message')
                    ->label('Mensaje')
                    ->limit(50)
                    ->tooltip(fn($record): string => $record->message)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_sent')
                    ->label('Enviada')
                    ->boolean(),

                Tables\Columns\TextColumn::make('read_at')
                    ->label('Fecha de Lectura')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('No leída'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_sent')
                    ->label('Enviadas')
                    ->query(fn(Builder $query): Builder => $query->where('is_sent', true))
                    ->toggle(),

                Tables\Filters\Filter::make('is_read')
                    ->label('Leídas')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('read_at'))
                    ->toggle(),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('sent_from')
                            ->label('Enviadas desde'),
                        Forms\Components\DatePicker::make('sent_until')
                            ->label('Enviadas hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['sent_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('sent_at', '>=', $date),
                            )
                            ->when(
                                $data['sent_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('sent_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (RelationManager $livewire, array $data): mixed {
                        // Asignar contrato
                        $data['contract_id'] = $livewire->getOwnerRecord()->id;

                        return $livewire->getRelationship()->create($data);
                    })
                    ->visible(fn() => auth()->user()->isAdmin() || auth()->user()->isHelper()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => auth()->user()->isAdmin() || auth()->user()->isHelper()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()->isAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->isAdmin()),
                ]),
            ]);
    }
}
