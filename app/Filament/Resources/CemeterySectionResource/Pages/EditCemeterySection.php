<?php

namespace App\Filament\Resources\CemeterySectionResource\Pages;

use App\Filament\Resources\CemeterySectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditCemeterySection extends EditRecord
{
    protected static string $resource = CemeterySectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin()),
        ];
    }
}
