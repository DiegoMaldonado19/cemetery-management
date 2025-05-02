<?php

namespace App\Filament\Resources\CemeteryBlockResource\Pages;

use App\Filament\Resources\CemeteryBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditCemeteryBlock extends EditRecord
{
    protected static string $resource = CemeteryBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin()),
        ];
    }
}
