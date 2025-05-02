<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Resources\Pages\Page;

class PrintPayment extends Page
{
    protected static string $resource = PaymentResource::class;

    protected static string $view = 'filament.payment-resource.pages.print-payment';

    public Payment $record;

    public function mount(Payment $record): void
    {
        $this->record = $record->load([
            'contract.niche',
            'contract.deceased.person',
            'contract.responsible',
            'status',
            'user'
        ]);
    }
}
