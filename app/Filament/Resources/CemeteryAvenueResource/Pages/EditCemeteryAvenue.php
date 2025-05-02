<?php

namespace App\Filament\Resources\CemeteryAvenueResource\Pages;

use App\Filament\Resources\CemeteryAvenueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditCemeteryAvenue extends EditRecord
{
    protected static string $resource = CemeteryAvenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin()),
        ];
    }
}
