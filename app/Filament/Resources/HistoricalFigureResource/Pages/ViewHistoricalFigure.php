<?php

namespace App\Filament\Resources\HistoricalFigureResource\Pages;

use App\Filament\Resources\HistoricalFigureResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewHistoricalFigure extends ViewRecord
{
    protected static string $resource = HistoricalFigureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => Auth::hasUser() && Auth::user()->isAdmin()),
        ];
    }
}
