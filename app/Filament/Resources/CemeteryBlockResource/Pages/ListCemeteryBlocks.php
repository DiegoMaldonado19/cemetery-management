<?php

namespace App\Filament\Resources\CemeteryBlockResource\Pages;

use App\Filament\Resources\CemeteryBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCemeteryBlocks extends ListRecords
{
    protected static string $resource = CemeteryBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
        ];
    }
}
