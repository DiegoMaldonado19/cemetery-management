<?php

namespace App\Filament\Resources\NicheResource\Pages;

use App\Filament\Resources\NicheResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewNiche extends ViewRecord
{
    protected static string $resource = NicheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin() || Auth::hasUser() && Auth::user()->isHelper()),
        ];
    }
}
