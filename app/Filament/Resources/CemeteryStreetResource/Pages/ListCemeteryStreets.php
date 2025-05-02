<?php

namespace App\Filament\Resources\CemeteryStreetResource\Pages;

use App\Filament\Resources\CemeteryStreetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCemeteryStreets extends ListRecords
{
    protected static string $resource = CemeteryStreetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
        ];
    }
}
