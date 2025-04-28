<?php

namespace App\Filament\Resources\ExhumationResource\Pages;

use App\Filament\Resources\ExhumationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExhumation extends EditRecord
{
    protected static string $resource = ExhumationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
