<?php

namespace App\Filament\Resources\DeceasedResource\Pages;

use App\Filament\Resources\DeceasedResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeceased extends EditRecord
{
    protected static string $resource = DeceasedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
