<?php

namespace App\Filament\Resources\HistoricalFigureResource\Pages;

use App\Filament\Resources\HistoricalFigureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHistoricalFigure extends EditRecord
{
    protected static string $resource = HistoricalFigureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
