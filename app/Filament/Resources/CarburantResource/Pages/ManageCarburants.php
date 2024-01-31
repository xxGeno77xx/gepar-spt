<?php

namespace App\Filament\Resources\CarburantResource\Pages;

use App\Filament\Resources\CarburantResource;
use App\Support\Database\PermissionsClass;
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

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([
            PermissionsClass::Carburant_create()->value,
            PermissionsClass::Carburant_read()->value,
            PermissionsClass::Carburant_update()->value,
        ]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
