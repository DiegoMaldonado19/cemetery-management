<?php

namespace App\Filament\Resources\NicheResource\Pages;

use App\Filament\Resources\NicheResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListNiches extends ListRecords
{
    protected static string $resource = NicheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
        ];
    }
}
