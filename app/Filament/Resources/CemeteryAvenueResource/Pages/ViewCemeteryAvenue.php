<?php

namespace App\Filament\Resources\CemeteryAvenueResource\Pages;

use App\Filament\Resources\CemeteryAvenueResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewCemeteryAvenue extends ViewRecord
{
    protected static string $resource = CemeteryAvenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
        ];
    }
}
