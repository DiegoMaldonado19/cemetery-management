<?php

namespace App\Filament\Resources\CemeteryStreetResource\Pages;

use App\Filament\Resources\CemeteryStreetResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewCemeteryStreet extends ViewRecord
{
    protected static string $resource = CemeteryStreetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
        ];
    }
}
