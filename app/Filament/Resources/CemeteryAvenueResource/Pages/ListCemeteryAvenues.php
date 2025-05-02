<?php

namespace App\Filament\Resources\CemeteryAvenueResource\Pages;

use App\Filament\Resources\CemeteryAvenueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCemeteryAvenues extends ListRecords
{
    protected static string $resource = CemeteryAvenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
        ];
    }
}
