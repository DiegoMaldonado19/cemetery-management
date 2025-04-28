<?php

namespace App\Filament\Resources\NicheResource\Pages;

use App\Filament\Resources\NicheResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNiches extends ListRecords
{
    protected static string $resource = NicheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
