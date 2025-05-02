<?php

namespace App\Filament\Resources\HistoricalFigureResource\Pages;

use App\Filament\Resources\HistoricalFigureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListHistoricalFigures extends ListRecords
{
    protected static string $resource = HistoricalFigureResource::class;

    protected function getHeaderActions(): array
{
    return [
        Actions\CreateAction::make()
            ->visible(fn () => Auth::hasUser() && (Auth::user()->isAdmin() || Auth::user()->isHelper())),
    ];
}
}
