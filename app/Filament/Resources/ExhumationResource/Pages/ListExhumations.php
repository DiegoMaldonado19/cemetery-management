<?php

namespace App\Filament\Resources\ExhumationResource\Pages;

use App\Filament\Resources\ExhumationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExhumations extends ListRecords
{
    protected static string $resource = ExhumationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
