<?php

namespace App\Filament\Resources\EngineResource\Pages;

use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\EngineResource;
use App\Filament\Resources\EngineResource\Widgets\EngineFuelConsumption;

class ViewEngines extends ViewRecord
{
    protected static string $resource = EngineResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::engines_read()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getFooterWidgets(): array
    {
        return [
            // EngineFuelConsumption::class
        ];
    }
}

