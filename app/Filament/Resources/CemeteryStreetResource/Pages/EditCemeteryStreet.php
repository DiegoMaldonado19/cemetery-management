<?php

namespace App\Filament\Resources\CemeteryStreetResource\Pages;

use App\Filament\Resources\CemeteryStreetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditCemeteryStreet extends EditRecord
{
    protected static string $resource = CemeteryStreetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin()),
        ];
    }
}
