<?php

namespace App\Filament\Resources\ParametreResource\Pages;

use App\Filament\Resources\ParametreResource;
use App\Support\Database\PermissionsClass;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParametres extends ListRecords
{
    protected static string $resource = ParametreResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::parametre_read()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
