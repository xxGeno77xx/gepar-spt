<?php

namespace App\Filament\Resources\CarburantResource\Pages;

use App\Filament\Resources\CarburantResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCarburants extends ManageRecords
{
    protected static string $resource = CarburantResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajouter un type de carburant'),
        ];
    }
}
