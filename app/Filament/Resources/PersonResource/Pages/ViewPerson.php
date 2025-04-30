<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewPerson extends ViewRecord
{
    protected static string $resource = PersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin() || Auth::hasUser() && Auth::user()->isHelper()),
            Actions\Action::make('registerDeceased')
                ->label('Registrar Fallecimiento')
                ->icon('heroicon-o-document-plus')
                ->color('danger')
                ->url(fn() => route('filament.admin.resources.people.deceased.create', $this->record))
                ->visible(
                    fn() => (Auth::hasUser() && Auth::user()->isAdmin() || Auth::hasUser() && Auth::user()->isHelper()) &&
                        $this->record->deceased === null
                ),
            Actions\Action::make('registerHistorical')
                ->label('Registrar como Histórico')
                ->icon('heroicon-o-star')
                ->color('warning')
                ->url(fn() => route('filament.admin.resources.people.historical.create', $this->record))
                ->visible(
                    fn() =>
                    Auth::hasUser() && Auth::user()->isAdmin() &&
                        $this->record->historicalFigure === null
                ),
        ];
    }
}
