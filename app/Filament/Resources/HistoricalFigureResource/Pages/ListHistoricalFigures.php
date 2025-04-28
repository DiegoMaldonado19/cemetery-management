<?php

namespace App\Filament\Resources\HistoricalFigureResource\Pages;

use App\Filament\Resources\HistoricalFigureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHistoricalFigures extends ListRecords
{
    protected static string $resource = HistoricalFigureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
