<?php

namespace App\Filament\Resources\CemeteryBlockResource\Pages;

use App\Filament\Resources\CemeteryBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewCemeteryBlock extends ViewRecord
{
    protected static string $resource = CemeteryBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
        ];
    }
}
