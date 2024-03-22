<?php

namespace App\Filament\Resources\EngineResource\Pages;

use Filament\Pages\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\EngineResource;
use App\Support\Database\PermissionsClass;
use App\Filament\Resources\EngineResource\Widgets\FraisReparationOveview;

class ViewEngines extends ViewRecord
{
    protected static string $resource = EngineResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::engines_read()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getActions(): array
    {
        if (auth()->user()->hasAnyPermission([PermissionsClass::Engines_update()->value])) {
            return [
                EditAction::make('Edit')->label('Modifier'),
            ];

        } else {
            return [];
        }
    }

    protected function getHeaderWidgets(): array{
        return [
            FraisReparationOveview::class
        ];
        
    }

}
