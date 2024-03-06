<?php

namespace App\Filament\Resources\CircuitResource\Pages;

use App\Filament\Resources\CircuitResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCircuits extends ListRecords
{
    protected static string $resource = CircuitResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
