<?php

namespace App\Filament\Resources\PlanningVoyageResource\Pages;

use App\Filament\Resources\PlanningVoyageResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlanningVoyages extends ListRecords
{
    protected static string $resource = PlanningVoyageResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
