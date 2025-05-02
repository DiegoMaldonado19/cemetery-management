<?php

namespace App\Filament\Resources\CemeterySectionResource\Pages;

use App\Filament\Resources\CemeterySectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewCemeterySection extends ViewRecord
{
    protected static string $resource = CemeterySectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
        ];
    }
}
