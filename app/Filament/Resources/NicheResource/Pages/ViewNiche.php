<?php

namespace App\Filament\Resources\NicheResource\Pages;

use App\Filament\Resources\NicheResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNiche extends ViewRecord
{
    protected static string $resource = NicheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => auth()->user()->isAdmin() || auth()->user()->isHelper()),
        ];
    }
}
