<?php

namespace App\Filament\Resources\TypeReparationResource\Pages;

use App\Filament\Resources\TypeReparationResource;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;

class ManageTypeReparations extends ManageRecords
{
    protected static string $resource = TypeReparationResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajouter un type de réparation'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()->where('state', StatesClass::Activated()->value);
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::TypesCarburant_create()->value,
            PermissionsClass::TypesCarburant_read()->value,
            PermissionsClass::TypesCarburant_update()->value,
        ]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
