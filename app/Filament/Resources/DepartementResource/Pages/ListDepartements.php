<?php

namespace App\Filament\Resources\DepartementResource\Pages;

use App\Filament\Resources\DepartementResource;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDepartements extends ListRecords
{
    protected static string $resource = DepartementResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajouter un département'),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        // $userPermission = $user->hasAnyPermission([PermissionsClass::departements_read()->value]);

        $userRole = $user->hasRole([RolesPermissionsSeeder::SuperAdmin]);

        abort_if(! $userRole, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()
            ->where('departements.state', StatesClass::Activated()->value);
    }
}
