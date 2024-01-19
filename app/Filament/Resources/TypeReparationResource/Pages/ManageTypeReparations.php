<?php

namespace App\Filament\Resources\TypeReparationResource\Pages;

use App\Filament\Resources\TypeReparationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTypeReparations extends ManageRecords
{
    protected static string $resource = TypeReparationResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
