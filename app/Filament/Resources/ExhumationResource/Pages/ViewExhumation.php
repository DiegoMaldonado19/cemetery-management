<?php

namespace App\Filament\Resources\ExhumationResource\Pages;

use App\Filament\Resources\ExhumationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExhumation extends ViewRecord
{
    protected static string $resource = ExhumationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => auth()->user()->isAdmin() || auth()->user()->isHelper()),
            Actions\Action::make('downloadAgreement')
                ->label('Descargar Acuerdo')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn() => $this->record->agreement_file_path ? storage_url($this->record->agreement_file_path) : null)
                ->openUrlInNewTab()
                ->visible(fn() => $this->record->agreement_file_path !== null),
        ];
    }
}
