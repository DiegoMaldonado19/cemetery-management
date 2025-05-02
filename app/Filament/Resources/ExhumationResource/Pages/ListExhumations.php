<?php

namespace App\Filament\Resources\ExhumationResource\Pages;

use App\Filament\Resources\ExhumationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListExhumations extends ListRecords
{
    protected static string $resource = ExhumationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
        ];
    }
}
