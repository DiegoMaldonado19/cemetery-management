<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => auth()->user()->isAdmin() || auth()->user()->isHelper()),
            Actions\Action::make('printReceipt')
                ->label('Imprimir Boleta')
                ->icon('heroicon-o-printer')
                ->url(fn() => route('filament.admin.resources.payments.print', $this->record))
                ->openUrlInNewTab(),
        ];
    }
}
