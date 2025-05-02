<?php

namespace App\Filament\Resources\CemeterySectionResource\Pages;

use App\Filament\Resources\CemeterySectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCemeterySections extends ListRecords
{
    protected static string $resource = CemeterySectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
        ];
    }
}
